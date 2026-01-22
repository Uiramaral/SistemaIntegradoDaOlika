<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Scopes\ClientScope;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function home(Request $request)
    {
        try {
            // Obter período selecionado (padrão: últimos 7 dias)
            $period = $request->get('period', '7days');
            $clientId = currentClientId();
            $selectedMonth = (int) $request->get('month', now()->month);
            $selectedYear = (int) $request->get('year', now()->year);
            
            // Calcular período baseado no filtro
            $periodDays = match($period) {
                '3months' => 90,
                '30days' => 30,
                '7days' => 7,
                default => 7
            };
            if ($period === 'month') {
                $periodStart = Carbon::createFromDate($selectedYear, $selectedMonth, 1)->startOfDay();
                $periodEnd = Carbon::createFromDate($selectedYear, $selectedMonth, 1)->endOfMonth();
            } else {
                $periodStart = now()->subDays($periodDays)->startOfDay();
                $periodEnd = now()->endOfDay();
            }
            $todayStart = now()->startOfDay();
            $todayEnd = now()->endOfDay();

            $applyClientFilter = function ($query, string $column) use ($clientId) {
                if (!$clientId) {
                    return; // Sem clientId, não aplicar filtro (global scope já filtra)
                }
                // Filtrar pelo client_id correto OU null (para dados antigos/migração)
                $query->where(function ($q) use ($clientId, $column) {
                    $q->where($column, $clientId)
                      ->orWhereNull($column);
                });
            };

            // 1. Estatísticas Principais (Faturamento e Pedidos)
            $validOrdersBaseQuery = Order::withoutGlobalScope(ClientScope::class)
                ->whereBetween('created_at', [$periodStart, $periodEnd])
                ->where('status', '!=', 'cancelled')
                ->where(function($q) {
                    // Pedidos válidos: entregues, confirmados, em preparo, prontos ou agendados
                    $q->whereIn('status', ['delivered', 'confirmed', 'preparing', 'ready'])
                      ->orWhere(function($subQ) {
                          $subQ->where('status', 'pending')
                               ->where('payment_status', 'paid');
                      })
                      ->orWhereNotNull('scheduled_delivery_at');
                });

            $applyClientFilter($validOrdersBaseQuery, 'client_id');

            $stats = $validOrdersBaseQuery->selectRaw('
                    COUNT(*) as total_pedidos,
                    COALESCE(SUM(COALESCE(final_amount, total_amount, 0)), 0) as faturamento,
                    SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending_count,
                    SUM(CASE WHEN status = "confirmed" THEN 1 ELSE 0 END) as confirmed_count,
                    SUM(CASE WHEN status = "preparing" THEN 1 ELSE 0 END) as preparing_count,
                    SUM(CASE WHEN status = "delivered" THEN 1 ELSE 0 END) as delivered_count
                ')->first();

            // Log de debug para entender a discrepância
            Log::info('Dashboard Stats Debug', [
                'period' => $period,
                'periodStart' => $periodStart->toDateTimeString(),
                'periodEnd' => $periodEnd->toDateTimeString(),
                'clientId' => $clientId,
                'total_pedidos' => $stats->total_pedidos,
                'faturamento' => $stats->faturamento,
                'sql' => $validOrdersBaseQuery->toSql(),
                'bindings' => $validOrdersBaseQuery->getBindings(),
            ]);

            // 2. Clientes no período (distintos por compras válidas)
            $clientesPeriodoQuery = DB::table('orders')
                ->join('customers', 'orders.customer_id', '=', 'customers.id')
                ->whereBetween('orders.created_at', [$periodStart, $periodEnd])
                ->where('orders.status', '!=', 'cancelled')
                ->whereNotNull('orders.customer_id')
                ->where(function($q) {
                    $q->whereIn('orders.status', ['delivered', 'confirmed', 'preparing', 'ready'])
                      ->orWhere(function($subQ) {
                          $subQ->where('orders.status', 'pending')
                               ->where('orders.payment_status', 'paid');
                      })
                      ->orWhereNotNull('orders.scheduled_delivery_at');
                });
            $applyClientFilter($clientesPeriodoQuery, 'orders.client_id');
            $applyClientFilter($clientesPeriodoQuery, 'customers.client_id');
            $novosClientes = $clientesPeriodoQuery->distinct('customers.id')->count('customers.id');

            // 3. Estatísticas de Hoje
            $todayStatsQuery = Order::withoutGlobalScope(ClientScope::class)
                ->whereBetween('created_at', [$todayStart, $todayEnd]);
            
            $applyClientFilter($todayStatsQuery, 'client_id');
            
            $todayStats = $todayStatsQuery->selectRaw('
                    COUNT(*) as pedidos_hoje,
                    COALESCE(SUM(CASE WHEN payment_status = "paid" THEN COALESCE(final_amount, total_amount, 0) ELSE 0 END), 0) as receita_hoje,
                    COUNT(CASE WHEN payment_status = "paid" THEN 1 END) as pagos_hoje,
                    COUNT(CASE WHEN payment_status != "paid" AND payment_status IS NOT NULL AND payment_status != "refunded" THEN 1 END) as pendentes_pagamento
                ')->first();

            // 4. Pedidos Recentes (Apenas os que compõem o faturamento no período)
            $recentOrders = (clone $validOrdersBaseQuery)
                ->with(['customer' => function ($query) {
                    // Remover scope para carregar customer mesmo se não pertencer ao mesmo client_id
                    // (pedido já está filtrado, então customer pode ser de outro tenant em casos legados)
                    $query->withoutGlobalScope(ClientScope::class);
                }])
                ->select('id', 'order_number', 'status', 'payment_status', 'final_amount', 'total_amount', 'created_at', 'customer_id', 'scheduled_delivery_at', 'client_id')
                ->latest()
                ->limit(20)
                ->get();

            // 5. Top Compradores (Clientes que mais compraram no período)
            $topBuyers = Order::withoutGlobalScope(ClientScope::class)
                ->join('customers', 'orders.customer_id', '=', 'customers.id')
                ->whereBetween('orders.created_at', [$periodStart, $periodEnd])
                ->where('orders.status', '!=', 'cancelled')
                ->where(function($q) {
                    $q->whereIn('orders.status', ['delivered', 'confirmed', 'preparing', 'ready'])
                      ->orWhere(function($subQ) {
                          $subQ->where('orders.status', 'pending')
                               ->where('orders.payment_status', 'paid');
                      });
                });

            $applyClientFilter($topBuyers, 'orders.client_id');
            $applyClientFilter($topBuyers, 'customers.client_id');

            $topBuyers = $topBuyers->select(
                    'customers.id',
                    'customers.name',
                    DB::raw('COUNT(DISTINCT orders.id) as total_compras'),
                    DB::raw('SUM(COALESCE(orders.final_amount, orders.total_amount, 0)) as total_valor')
                )
                ->groupBy('customers.id', 'customers.name')
                ->orderByDesc('total_compras')
                ->limit(5)
                ->get();

            // 6. Top Produtos Vendidos
            $topProductsQuery = OrderItem::query()
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
                ->whereBetween('orders.created_at', [$periodStart, $periodEnd])
                ->where('orders.status', '!=', 'cancelled')
                ->where(function($q) {
                    $q->whereIn('orders.status', ['delivered', 'confirmed', 'preparing', 'ready'])
                      ->orWhere(function($subQ) {
                          $subQ->where('orders.status', 'pending')
                               ->where('orders.payment_status', 'paid');
                      });
                });

            $applyClientFilter($topProductsQuery, 'orders.client_id');

            $topProducts = $topProductsQuery->select(
                    'products.id',
                    'products.name',
                    'categories.name as category_name',
                    DB::raw('SUM(order_items.quantity) as total_quantity'),
                    DB::raw('SUM(order_items.total_price) as total_revenue')
                )
                ->groupBy('products.id', 'products.name', 'categories.name')
                ->orderByDesc('total_quantity')
                ->limit(5)
                ->get()
                ->map(function($item) {
                    return [
                        'product' => (object)[
                            'id' => $item->id,
                            'name' => $item->name,
                            'category_name' => $item->category_name ?? 'Sem categoria',
                        ],
                        'quantity' => $item->total_quantity,
                        'revenue' => $item->total_revenue,
                    ];
                });

            // Fallbacks e cálculos finais
            $totalPedidos = (int)($stats->total_pedidos ?? 0);
            $faturamento = (float)($stats->faturamento ?? 0);
            $ticketMedio = $totalPedidos > 0 ? ($faturamento / $totalPedidos) : 0;
            
            $statusCount = [
                'pending' => (int)($stats->pending_count ?? 0),
                'confirmed' => (int)($stats->confirmed_count ?? 0),
                'preparing' => (int)($stats->preparing_count ?? 0),
                'delivered' => (int)($stats->delivered_count ?? 0),
            ];

            // Próximos agendamentos
            $nextScheduled = Order::withoutGlobalScope(ClientScope::class)
                ->whereNotNull('scheduled_delivery_at')
                ->where('scheduled_delivery_at', '>=', now())
                ->where('status', '!=', 'cancelled');
            $applyClientFilter($nextScheduled, 'client_id');
            $nextScheduled = $nextScheduled->orderBy('scheduled_delivery_at')->limit(8)->get(['id', 'order_number', 'scheduled_delivery_at', 'status', 'customer_id']);

            return view('dashboard.dashboard.index', compact(
                'totalPedidos', 'faturamento', 'novosClientes', 'ticketMedio', 'statusCount',
                'recentOrders', 'topProducts', 'topBuyers', 'period', 'todayStats', 'nextScheduled',
                'selectedMonth', 'selectedYear'
            ));

        } catch (\Exception $e) {
            Log::error('Erro no DashboardController::home', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Erro ao carregar dashboard.');
        }
    }

    public function compact()
    {
        $clientId = currentClientId();
        $todayOrders = Order::with(['customer:id,name,phone'])
            ->select('id', 'order_number', 'status', 'payment_status', 'final_amount', 'created_at', 'customer_id')
            ->whereDate('created_at', today());
        if ($clientId) $todayOrders->where('client_id', $clientId);
        $todayOrders = $todayOrders->latest()->limit(50)->get();
        return view('dashboard.dashboard.compact', compact('todayOrders'));
    }

    public function reports()
    {
        return view('dashboard.reports.index');
    }

    public function settings()
    {
        return view('dashboard.settings.index');
    }
}
