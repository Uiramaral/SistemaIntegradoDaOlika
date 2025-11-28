<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function home()
    {
        try {
            $today = today();
            $cacheKey = 'dashboard_home_' . $today->format('Y-m-d');
            $cacheTime = 60; // Cache por 60 segundos
            
            // Usar cache para dados que não mudam frequentemente
            $data = Cache::remember($cacheKey, $cacheTime, function () use ($today) {
                // Unificar queries de estatísticas gerais (1 query ao invés de múltiplas)
                $stats = DB::table('orders')
                    ->selectRaw('
                        COUNT(*) as total_pedidos,
                        COALESCE(SUM(final_amount), 0) as faturamento,
                        COUNT(CASE WHEN status = "pending" THEN 1 END) as pending_count,
                        COUNT(CASE WHEN status = "confirmed" THEN 1 END) as confirmed_count,
                        COUNT(CASE WHEN status = "preparing" THEN 1 END) as preparing_count,
                        COUNT(CASE WHEN status = "delivered" THEN 1 END) as delivered_count
                    ')
                    ->first();
                
                // Dados de HOJE - unificar em 1 query
                $todayStats = DB::table('orders')
                    ->whereDate('created_at', $today)
                    ->selectRaw('
                        COUNT(*) as pedidos_hoje,
                        COALESCE(SUM(CASE WHEN payment_status IN ("approved", "paid") THEN final_amount ELSE 0 END), 0) as receita_hoje,
                        COUNT(CASE WHEN payment_status IN ("approved", "paid") THEN 1 END) as pagos_hoje,
                        COUNT(CASE WHEN payment_status NOT IN ("approved", "paid") AND payment_status IS NOT NULL THEN 1 END) as pendentes_pagamento
                    ')
                    ->first();
                
                // Novos clientes hoje
                $novosClientes = DB::table('customers')
                    ->whereDate('created_at', $today)
                    ->count();
                
                // Calcular ticket médio
                $ticketMedio = $stats->total_pedidos > 0 
                    ? ($stats->faturamento / $stats->total_pedidos) 
                    : 0;
                
                // Pedidos agendados - otimizado
                $scheduledStats = DB::table('orders')
                    ->whereNotNull('scheduled_delivery_at')
                    ->selectRaw('
                        COUNT(CASE WHEN DATE(scheduled_delivery_at) = ? THEN 1 END) as scheduled_today,
                        COUNT(CASE WHEN scheduled_delivery_at BETWEEN ? AND ? THEN 1 END) as scheduled_next_7_days
                    ', [
                        $today->format('Y-m-d'),
                        now()->startOfDay(),
                        now()->copy()->addDays(7)->endOfDay()
                    ])
                    ->first();
                
                return [
                    'stats' => $stats,
                    'todayStats' => $todayStats,
                    'novosClientes' => $novosClientes,
                    'ticketMedio' => $ticketMedio,
                    'scheduledStats' => $scheduledStats,
                ];
            });
            
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
            $topProducts = DB::table('order_items')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->where('orders.created_at', '>=', now()->subDays(7))
                ->whereIn('orders.payment_status', ['approved', 'paid'])
                ->select(
                    'products.id',
                    'products.name',
                    'products.image',
                    DB::raw('SUM(order_items.quantity) as total_quantity'),
                    DB::raw('SUM(order_items.total_price) as total_revenue')
                )
                ->groupBy('products.id', 'products.name', 'products.image')
                ->orderByDesc('total_quantity')
                ->limit(5)
                ->get()
                ->map(function($item) {
                    return [
                        'product' => (object)[
                            'id' => $item->id,
                            'name' => $item->name,
                            'image' => $item->image,
                        ],
                        'quantity' => $item->total_quantity,
                        'revenue' => $item->total_revenue,
                    ];
                });
            
            // Pedidos de hoje (apenas contagem, não carregar todos)
            $todayOrdersCount = $data['todayStats']->pedidos_hoje;
            
            // Extrair dados do cache
            $totalPedidos = $data['stats']->total_pedidos;
            $faturamento = $data['stats']->faturamento;
            $novosClientes = $data['novosClientes'];
            $ticketMedio = $data['ticketMedio'];
            
            $statusCount = [
                'pending' => $data['stats']->pending_count,
                'confirmed' => $data['stats']->confirmed_count,
                'preparing' => $data['stats']->preparing_count,
                'delivered' => $data['stats']->delivered_count,
            ];
            
            $scheduledTodayCount = $data['scheduledStats']->scheduled_today;
            $scheduledNext7Days = $data['scheduledStats']->scheduled_next_7_days;
            
            $receitaHoje = $data['todayStats']->receita_hoje;
            $pedidosHoje = $data['todayStats']->pedidos_hoje;
            $pagosHoje = $data['todayStats']->pagos_hoje;
            $pendentesPagamento = $data['todayStats']->pendentes_pagamento;
            
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