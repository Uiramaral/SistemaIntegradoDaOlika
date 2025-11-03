<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function home()
    {
        try {
            $today = today();
            $todayOrders = Order::whereDate('created_at', $today)->get();
            
            // Dados para o dashboard com tratamento de erro
            $totalPedidos = Order::count();
            $faturamento = Order::sum('final_amount') ?? 0;
            $novosClientes = \App\Models\Customer::whereDate('created_at', $today)->count();
            $ticketMedio = $totalPedidos > 0 ? $faturamento / $totalPedidos : 0;
            
            // Dados de HOJE
            $receitaHoje = Order::whereDate('created_at', $today)
                ->whereIn('payment_status', ['approved', 'paid'])
                ->sum('final_amount') ?? 0;
            
            $pedidosHoje = $todayOrders->count();
            
            $pagosHoje = Order::whereDate('created_at', $today)
                ->whereIn('payment_status', ['approved', 'paid'])
                ->count();
            
            $pendentesPagamento = Order::whereDate('created_at', $today)
                ->whereNotIn('payment_status', ['approved', 'paid'])
                ->whereNotNull('payment_status')
                ->count();
            
            // Contagem por status
            $statusCount = [
                'pending' => Order::where('status', 'pending')->count(),
                'confirmed' => Order::where('status', 'confirmed')->count(),
                'preparing' => Order::where('status', 'preparing')->count(),
                'delivered' => Order::where('status', 'delivered')->count(),
            ];

            // Pedidos agendados
            $scheduledTodayCount = Order::whereNotNull('scheduled_delivery_at')
                ->whereDate('scheduled_delivery_at', $today)
                ->count();
            $scheduledNext7Days = Order::whereNotNull('scheduled_delivery_at')
                ->whereBetween('scheduled_delivery_at', [now()->startOfDay(), now()->copy()->addDays(7)->endOfDay()])
                ->count();
            $nextScheduled = Order::whereNotNull('scheduled_delivery_at')
                ->where('scheduled_delivery_at', '>=', now())
                ->orderBy('scheduled_delivery_at')
                ->limit(8)
                ->get();
            
            // Pedidos recentes (últimos 10)
            $recentOrders = Order::with(['customer'])
                ->latest()
                ->limit(10)
                ->get();
            
            // Top produtos (últimos 7 dias)
            $topProducts = \App\Models\OrderItem::select('product_id', DB::raw('SUM(quantity) as total_quantity'), DB::raw('SUM(total_price) as total_revenue'))
                ->whereHas('order', function($q) {
                    $q->where('created_at', '>=', now()->subDays(7))
                      ->whereIn('payment_status', ['approved', 'paid']);
                })
                ->groupBy('product_id')
                ->orderByDesc('total_quantity')
                ->limit(5)
                ->get()
                ->map(function($item) {
                    $product = \App\Models\Product::find($item->product_id);
                    return [
                        'product' => $product,
                        'quantity' => $item->total_quantity,
                        'revenue' => $item->total_revenue,
                    ];
                })
                ->filter(fn($item) => $item['product'] !== null);
            
            return view('dashboard.dashboard.index', compact(
                'todayOrders', 'totalPedidos', 'faturamento', 'novosClientes', 'ticketMedio', 'statusCount',
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
        $todayOrders = Order::whereDate('created_at', today())->get();
        return view('dashboard.dashboard.compact', compact('todayOrders'));
    }

    public function reports()
    {
        $reports = Order::latest()->take(10)->get();
        return view('dashboard.reports.index', compact('reports'));
    }

    public function settings()
    {
        return view('dashboard.settings.index');
    }
}