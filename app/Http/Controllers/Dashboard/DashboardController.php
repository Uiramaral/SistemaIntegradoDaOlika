<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Services\OrderStatusService;

class DashboardController extends Controller
{
    public function kpisBase()
    {
        return [
            'orders_today'    => DB::table('orders')->whereDate('created_at', today())->count(),
            'paid_today'      => DB::table('orders')->whereDate('created_at', today())->where('status', 'paid')->count(),
            'revenue_today'   => (float) DB::table('orders')->whereDate('created_at', today())->where('status', 'paid')->sum('final_amount'),
            'waiting_payment' => DB::table('orders')->where('status', 'waiting_payment')->count(),
        ];
    }

    public function home()
    {
        $today = \Carbon\Carbon::today();
        
        // Cards do topo
        $totalHoje = DB::table('orders')->whereDate('created_at', $today)->sum('final_amount');
        $pedidosHoje = DB::table('orders')->whereDate('created_at', $today)->count();
        $pagosHoje = DB::table('orders')->whereDate('created_at', $today)->where('payment_status','paid')->count();
        $pendentesPg = DB::table('orders')->whereDate('created_at', $today)->where('payment_status','pending')->count();
        
        // Pedidos recentes (com cliente)
        $pedidosRecentes = \App\Models\Order::with('customer')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();
        
        // Top produtos (últimos 7 dias, por quantidade)
        $desde = \Carbon\Carbon::now()->subDays(7);
        $topProdutos = \App\Models\OrderItem::select([
                'order_items.product_id',
                DB::raw('SUM(order_items.quantity) as qty'),
                DB::raw('SUM(order_items.total_price) as revenue')
            ])
            ->join('orders','orders.id','=','order_items.order_id')
            ->where('orders.created_at','>=',$desde)
            ->groupBy('order_items.product_id')
            ->orderByDesc('qty')
            ->with(['product' => function($q){
                $q->select('id','name','price','image_url');
            }])
            ->limit(5)
            ->get();

        return view('dashboard.index', [
            'totalHoje' => $totalHoje,
            'pedidosHoje' => $pedidosHoje,
            'pagosHoje' => $pagosHoje,
            'pendentesPg' => $pendentesPg,
            'pedidosRecentes' => $pedidosRecentes,
            'topProdutos' => $topProdutos,
        ]);
    }

    public function compact()
    {
        $kpis = $this->kpisBase();

        $todayOrders = DB::table('orders as o')
            ->leftJoin('customers as c', 'c.id', '=', 'o.customer_id')
            ->select('o.*', 'c.name as customer_name', 'c.phone as customer_phone')
            ->whereDate('o.created_at', today())
            ->orderBy('o.status')
            ->orderBy('o.id')
            ->get();

        $statuses = DB::table('order_statuses')->where('active', 1)->orderBy('id')->get();

        return view('dashboard.home_compact', compact('kpis', 'todayOrders', 'statuses'));
    }

    public function orders()
    {
        $orders = DB::table('orders as o')
            ->leftJoin('customers as c', 'c.id', '=', 'o.customer_id')
            ->select('o.*', 'c.name as customer_name', 'c.phone as customer_phone')
            ->orderByDesc('o.id')
            ->paginate(30);

        return view('dashboard.orders', compact('orders'));
    }

    public function orderShow(Order $order)
    {
        return view('dashboard.order_show', compact('order'));
    }

    public function orderChangeStatus(Request $r, Order $order, OrderStatusService $oss)
    {
        $data = $r->validate([
            'status_code' => 'required|exists:order_statuses,code',
            'note' => 'nullable|string|max:255',
        ]);

        $oss->changeStatus($order, $data['status_code'], $data['note'], optional($r->user())->id);

        return back()->with('ok', 'Status atualizado e notificações enviadas (se configurado).');
    }

    public function customers()
    {
        $customers = DB::table('customers')->orderByDesc('id')->paginate(30);

        return view('dashboard.customers', compact('customers'));
    }

    public function products()
    {
        $products = DB::table('products')->orderBy('name')->paginate(30);

        return view('dashboard.products', compact('products'));
    }

    public function categories()
    {
        $cats = DB::table('categories')->orderBy('name')->paginate(30);

        return view('dashboard.categories', compact('cats'));
    }

    public function coupons()
    {
        $coupons = DB::table('coupons')->orderByDesc('id')->paginate(30);

        return view('dashboard.coupons', compact('coupons'));
    }

    // Método removido - agora usa CashbackController

    public function loyalty()
    {
        $rows = DB::table('loyalty_programs')->orderByDesc('id')->paginate(30);

        return view('dashboard.loyalty', compact('rows'));
    }

    public function reports()
    {
        $last30 = now()->subDays(30);

        $sales = DB::table('orders')->where('status', 'paid')->where('created_at', '>=', $last30)->sum('final_amount');

        $count = DB::table('orders')->where('status', 'paid')->where('created_at', '>=', $last30)->count();

        $aov = $count ? $sales / $count : 0;

        $top = DB::table('order_items')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->select('products.name as product_name', DB::raw('SUM(order_items.quantity) as qty'))
            ->whereIn('order_items.order_id', function ($q) use ($last30) {
                $q->select('id')->from('orders')->where('created_at', '>=', $last30);
            })
            ->groupBy('products.name')
            ->orderByDesc('qty')
            ->limit(10)
            ->get();

        return view('dashboard.reports', compact('sales', 'count', 'aov', 'top'));
    }
}

