<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Coupon;
use App\Services\CacheService;
use App\Services\OrderStatusService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    protected $cacheService;

    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * Dashboard principal
     */
    public function index(Request $request)
    {
        $period = $request->get('period', 'today');
        
        $stats = $this->getDashboardStats($period);
        $recentOrders = $this->getRecentOrders();
        $topProducts = $this->getTopProducts($period);
        $couponStats = $this->getCouponStats();

        return view('admin.dashboard', compact(
            'stats',
            'recentOrders',
            'topProducts',
            'couponStats',
            'period'
        ));
    }

    /**
     * Estatísticas do dashboard
     */
    private function getDashboardStats(string $period)
    {
        $dateFilter = $this->getDateFilter($period);

        return [
            'total_orders' => Order::whereBetween('created_at', $dateFilter)->count(),
            'total_revenue' => Order::whereBetween('created_at', $dateFilter)
                ->where('payment_status', 'paid')
                ->sum('final_amount'),
            'new_customers' => Customer::whereBetween('created_at', $dateFilter)->count(),
            'average_order_value' => Order::whereBetween('created_at', $dateFilter)
                ->where('payment_status', 'paid')
                ->avg('final_amount'),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'confirmed_orders' => Order::where('status', 'confirmed')->count(),
            'preparing_orders' => Order::where('status', 'preparing')->count(),
            'ready_orders' => Order::where('status', 'ready')->count(),
        ];
    }

    /**
     * Pedidos recentes
     */
    private function getRecentOrders()
    {
        return Order::with(['customer', 'items.product'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
    }

    /**
     * Produtos mais vendidos
     */
    private function getTopProducts(string $period)
    {
        $dateFilter = $this->getDateFilter($period);

        return DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->whereBetween('orders.created_at', $dateFilter)
            ->where('orders.payment_status', 'paid')
            ->select(
                'products.name',
                'products.id',
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.total_price) as total_revenue')
            )
            ->groupBy('products.id', 'products.name')
            ->orderBy('total_quantity', 'desc')
            ->limit(10)
            ->get();
    }

    /**
     * Estatísticas de cupons
     */
    private function getCouponStats()
    {
        return [
            'total_coupons' => Coupon::count(),
            'active_coupons' => Coupon::active()->count(),
            'used_coupons' => Coupon::where('used_count', '>', 0)->count(),
            'public_coupons' => Coupon::public()->active()->count(),
            'private_coupons' => Coupon::private()->active()->count(),
            'targeted_coupons' => Coupon::targeted()->active()->count(),
        ];
    }

    /**
     * Filtro de data baseado no período
     */
    private function getDateFilter(string $period): array
    {
        $now = now();
        
        switch ($period) {
            case 'today':
                return [$now->startOfDay(), $now->endOfDay()];
            case 'week':
                return [$now->startOfWeek(), $now->endOfWeek()];
            case 'month':
                return [$now->startOfMonth(), $now->endOfMonth()];
            case 'year':
                return [$now->startOfYear(), $now->endOfYear()];
            default:
                return [$now->startOfDay(), $now->endOfDay()];
        }
    }

    /**
     * API: Estatísticas em tempo real
     */
    public function getStats(Request $request)
    {
        $period = $request->get('period', 'today');
        $stats = $this->getDashboardStats($period);

        return response()->json([
            'success' => true,
            'stats' => $stats,
            'period' => $period,
        ]);
    }

    /**
     * API: Gráfico de vendas
     */
    public function getSalesChart(Request $request)
    {
        $days = $request->get('days', 30);
        
        $sales = DB::table('orders')
            ->where('payment_status', 'paid')
            ->where('created_at', '>=', now()->subDays($days))
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as orders_count'),
                DB::raw('SUM(final_amount) as total_revenue')
            )
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();

        return response()->json([
            'success' => true,
            'sales' => $sales,
            'days' => $days,
        ]);
    }

    /**
     * Mudar status de um pedido
     */
    public function orderChangeStatus(Request $r, Order $order, OrderStatusService $oss)
    {
        $data = $r->validate([
            'status_code' => 'required|exists:order_statuses,code',
            'note'        => 'nullable|string|max:255',
        ]);

        $oss->changeStatus($order, $data['status_code'], $data['note'], optional($r->user())->id);

        return back()->with('ok', 'Status atualizado e notificações enviadas (se configurado).');
    }
}
