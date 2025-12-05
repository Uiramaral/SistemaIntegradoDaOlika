<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function home()
    {
        try {
            // Desabilitar cache temporariamente para debug
            // $today = today();
            // $cacheKey = 'dashboard_home_' . $today->format('Y-m-d');
            // $cacheTime = 60; // Cache por 60 segundos
            
            // Buscar dados diretamente sem cache para garantir dados atualizados
            // Unificar queries de estatísticas gerais (1 query ao invés de múltiplas)
            // Contar todos os pedidos (não cancelados) para estatísticas gerais
            $stats = DB::table('orders')
                ->where('status', '!=', 'cancelled')
                ->selectRaw('
                    COUNT(*) as total_pedidos,
                    COALESCE(SUM(final_amount), 0) as faturamento,
                    COUNT(CASE WHEN status = "pending" THEN 1 END) as pending_count,
                    COUNT(CASE WHEN status = "confirmed" THEN 1 END) as confirmed_count,
                    COUNT(CASE WHEN status = "preparing" THEN 1 END) as preparing_count,
                    COUNT(CASE WHEN status = "delivered" THEN 1 END) as delivered_count
                ')
                ->first();
            
            // Dados de HOJE - usar DATE() para ignorar timezone
            // Usar DATE() do MySQL para comparar apenas a data, ignorando hora e timezone
            $todayDate = now()->format('Y-m-d');
            
            $todayStats = DB::table('orders')
                ->whereRaw('DATE(created_at) = ?', [$todayDate])
                ->selectRaw('
                    COUNT(*) as pedidos_hoje,
                    COALESCE(SUM(CASE WHEN payment_status = "paid" THEN final_amount ELSE 0 END), 0) as receita_hoje,
                    COUNT(CASE WHEN payment_status = "paid" THEN 1 END) as pagos_hoje,
                    COUNT(CASE WHEN payment_status != "paid" AND payment_status IS NOT NULL AND payment_status != "refunded" THEN 1 END) as pendentes_pagamento
                ')
                ->first();
            
            // Novos clientes hoje - usar DATE() também
            $novosClientes = DB::table('customers')
                ->whereRaw('DATE(created_at) = ?', [$todayDate])
                ->count();
            
            // Calcular ticket médio
            $ticketMedio = $stats->total_pedidos > 0 
                ? ($stats->faturamento / $stats->total_pedidos) 
                : 0;
            
            // Pedidos agendados - usar DATE() para comparar apenas a data
            $next7DaysDate = now()->addDays(7)->format('Y-m-d');
            $scheduledStats = DB::table('orders')
                ->whereNotNull('scheduled_delivery_at')
                ->selectRaw('
                    COUNT(CASE WHEN DATE(scheduled_delivery_at) = ? THEN 1 END) as scheduled_today,
                    COUNT(CASE WHEN DATE(scheduled_delivery_at) >= ? AND DATE(scheduled_delivery_at) <= ? THEN 1 END) as scheduled_next_7_days
                ', [
                    $todayDate,
                    $todayDate,
                    $next7DaysDate
                ])
                ->first();
            
            $data = [
                'stats' => $stats,
                'todayStats' => $todayStats,
                'novosClientes' => $novosClientes,
                'ticketMedio' => $ticketMedio,
                'scheduledStats' => $scheduledStats,
            ];
            
            // Dados que precisam ser sempre atualizados (sem cache)
            // Pedidos agendados próximos
            $nextScheduled = Order::whereNotNull('scheduled_delivery_at')
                ->where('scheduled_delivery_at', '>=', now())
                ->orderBy('scheduled_delivery_at')
                ->limit(8)
                ->get(['id', 'order_number', 'scheduled_delivery_at', 'status', 'customer_id']);
            
            // Pedidos recentes (últimos 10) - com eager loading otimizado
            $recentOrders = Order::with(['customer:id,name,phone'])
                ->select('id', 'order_number', 'status', 'payment_status', 'final_amount', 'created_at', 'customer_id')
                ->latest()
                ->limit(10)
                ->get();
            
            // Top produtos (últimos 7 dias) - otimizado com join
            // Verificar se a coluna image existe antes de selecionar
            $hasImageColumn = Schema::hasColumn('products', 'image');
            $selectFields = [
                'products.id',
                'products.name',
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.total_price) as total_revenue')
            ];
            
            if ($hasImageColumn) {
                $selectFields[] = 'products.image';
            }
            
            $topProducts = DB::table('order_items')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->where('orders.created_at', '>=', now()->subDays(7))
                ->where('orders.payment_status', 'paid')
                ->select($selectFields)
                ->groupBy('products.id', 'products.name' . ($hasImageColumn ? ', products.image' : ''))
                ->orderByDesc('total_quantity')
                ->limit(5)
                ->get()
                ->map(function($item) {
                    return [
                        'product' => (object)[
                            'id' => $item->id,
                            'name' => $item->name,
                            'image' => $item->image ?? null,
                        ],
                        'quantity' => $item->total_quantity,
                        'revenue' => $item->total_revenue,
                    ];
                });
            
            // Extrair dados com proteção contra null
            $totalPedidos = (int)($data['stats']->total_pedidos ?? 0);
            $faturamento = (float)($data['stats']->faturamento ?? 0);
            $novosClientes = (int)($data['novosClientes'] ?? 0);
            $ticketMedio = (float)($data['ticketMedio'] ?? 0);
            
            $statusCount = [
                'pending' => (int)($data['stats']->pending_count ?? 0),
                'confirmed' => (int)($data['stats']->confirmed_count ?? 0),
                'preparing' => (int)($data['stats']->preparing_count ?? 0),
                'delivered' => (int)($data['stats']->delivered_count ?? 0),
            ];
            
            $scheduledTodayCount = (int)($data['scheduledStats']->scheduled_today ?? 0);
            $scheduledNext7Days = (int)($data['scheduledStats']->scheduled_next_7_days ?? 0);
            
            $receitaHoje = (float)($data['todayStats']->receita_hoje ?? 0);
            $pedidosHoje = (int)($data['todayStats']->pedidos_hoje ?? 0);
            $pagosHoje = (int)($data['todayStats']->pagos_hoje ?? 0);
            $pendentesPagamento = (int)($data['todayStats']->pendentes_pagamento ?? 0);
            
            // Pedidos de hoje (apenas contagem, não carregar todos)
            $todayOrdersCount = $pedidosHoje;
            
            // Log temporário para debug (remover depois)
            Log::info('Dashboard Stats', [
                'today_date' => $todayDate,
                'pedidos_hoje' => $pedidosHoje,
                'receita_hoje' => $receitaHoje,
                'pagos_hoje' => $pagosHoje,
                'pendentes_pagamento' => $pendentesPagamento,
                'scheduled_today' => $scheduledTodayCount,
                'total_pedidos' => $totalPedidos,
            ]);
            
            // Criar collection vazia para compatibilidade (não carregar todos os pedidos)
            $todayOrders = collect();
            
            return view('dashboard.dashboard.index', compact(
                'todayOrders', 'todayOrdersCount', 'totalPedidos', 'faturamento', 'novosClientes', 'ticketMedio', 'statusCount',
                'scheduledTodayCount', 'scheduledNext7Days', 'nextScheduled',
                'receitaHoje', 'pedidosHoje', 'pagosHoje', 'pendentesPagamento',
                'recentOrders', 'topProducts'
            ));
        } catch (\Exception $e) {
            Log::error('Erro no DashboardController::home', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            // Em caso de erro, retornar valores padrão
            return view('dashboard.dashboard.index', [
                'todayOrders' => collect(),
                'totalPedidos' => 0,
                'faturamento' => 0,
                'novosClientes' => 0,
                'ticketMedio' => 0,
                'statusCount' => [
                    'pending' => 0,
                    'confirmed' => 0,
                    'preparing' => 0,
                    'delivered' => 0,
                ],
                'scheduledTodayCount' => 0,
                'scheduledNext7Days' => 0,
                'nextScheduled' => collect(),
                'receitaHoje' => 0,
                'pedidosHoje' => 0,
                'pagosHoje' => 0,
                'pendentesPagamento' => 0,
                'recentOrders' => collect(),
                'topProducts' => collect(),
            ]);
        }
    }

    public function compact()
    {
        // Otimizado: apenas IDs e dados essenciais, com limite
        $todayOrders = Order::with(['customer:id,name,phone'])
            ->select('id', 'order_number', 'status', 'payment_status', 'final_amount', 'created_at', 'customer_id')
            ->whereDate('created_at', today())
            ->orderBy('created_at', 'desc')
            ->limit(50) // Limitar para não carregar todos
            ->get();
        return view('dashboard.dashboard.compact', compact('todayOrders'));
    }

    public function reports()
    {
        // Otimizado: apenas campos necessários
        $reports = Order::with(['customer:id,name'])
            ->select('id', 'order_number', 'status', 'payment_status', 'final_amount', 'created_at', 'customer_id')
            ->latest()
            ->take(10)
            ->get();
        return view('dashboard.reports.index', compact('reports'));
    }

    public function settings()
    {
        return view('dashboard.settings.index');
    }
}