<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;
use App\Models\Customer;
use App\Models\OrderItem;
use App\Models\AnalyticsEvent;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportsController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->get('start_date') ? Carbon::parse($request->get('start_date')) : now()->startOfMonth();
        $endDate = $request->get('end_date') ? Carbon::parse($request->get('end_date')) : now()->endOfMonth();

        // Pedidos pagos no período (usar apenas 'paid' conforme estrutura do banco)
        $orders = Order::whereBetween('created_at', [$startDate, $endDate])
            ->where('payment_status', 'paid')
            ->get();
        
        $totalOrders = $orders->count();
        $totalAmount = $orders->sum('final_amount') ?? 0;
        $averageTicket = $totalOrders > 0 ? $totalAmount / $totalOrders : 0;
        
        // Período anterior para comparação
        $periodDays = $startDate->diffInDays($endDate) + 1;
        $previousStartDate = $startDate->copy()->subDays($periodDays);
        $previousEndDate = $startDate->copy()->subDay();
        
        $previousOrders = Order::whereBetween('created_at', [$previousStartDate, $previousEndDate])
            ->where('payment_status', 'paid')
            ->get();
        
        $previousTotalAmount = $previousOrders->sum('final_amount') ?? 0;
        $previousTotalOrders = $previousOrders->count();
        $previousNewCustomers = Customer::whereBetween('created_at', [$previousStartDate, $previousEndDate])->count();
        
        // Comparações percentuais
        $revenueChange = $previousTotalAmount > 0 
            ? (($totalAmount - $previousTotalAmount) / $previousTotalAmount) * 100 
            : ($totalAmount > 0 ? 100 : 0);
        
        $ordersChange = $previousTotalOrders > 0 
            ? (($totalOrders - $previousTotalOrders) / $previousTotalOrders) * 100 
            : ($totalOrders > 0 ? 100 : 0);
        
        // Novos clientes no período
        $newCustomers = Customer::whereBetween('created_at', [$startDate, $endDate])->count();
        $customersChange = $previousNewCustomers > 0 
            ? (($newCustomers - $previousNewCustomers) / $previousNewCustomers) * 100 
            : ($newCustomers > 0 ? 100 : 0);
        
        // Produtos vendidos (quantidade de itens)
        $productsSold = OrderItem::whereHas('order', function($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [$startDate, $endDate])
                  ->where('payment_status', 'paid');
            })
            ->sum('quantity') ?? 0;
        
        $previousProductsSold = OrderItem::whereHas('order', function($q) use ($previousStartDate, $previousEndDate) {
                $q->whereBetween('created_at', [$previousStartDate, $previousEndDate])
                  ->where('payment_status', 'paid');
            })
            ->sum('quantity') ?? 0;
        
        $productsChange = $previousProductsSold > 0 
            ? (($productsSold - $previousProductsSold) / $previousProductsSold) * 100 
            : ($productsSold > 0 ? 100 : 0);
        
        $statusSummary = $orders->groupBy('status')->map->count();
        
        // Métricas de Analytics
        // Contar visitas únicas: 1 sessão por dia (mesma sessão no mesmo dia = 1 visita)
        // Usar groupBy para contar combinações únicas de data + session_id
        $pageViews = DB::table('analytics_events')
            ->where('event_type', 'page_view')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('session_id')
            ->select(DB::raw('DATE(created_at) as visit_date'), 'session_id')
            ->groupBy(DB::raw('DATE(created_at)'), 'session_id')
            ->count();
        
        $previousPageViews = DB::table('analytics_events')
            ->where('event_type', 'page_view')
            ->whereBetween('created_at', [$previousStartDate, $previousEndDate])
            ->whereNotNull('session_id')
            ->select(DB::raw('DATE(created_at) as visit_date'), 'session_id')
            ->groupBy(DB::raw('DATE(created_at)'), 'session_id')
            ->count();
        
        $pageViewsChange = $previousPageViews > 0 
            ? (($pageViews - $previousPageViews) / $previousPageViews) * 100 
            : ($pageViews > 0 ? 100 : 0);
        
        // Contar sessões únicas que adicionaram ao carrinho (não quantidade de produtos)
        $addToCartEvents = DB::table('analytics_events')
            ->where('event_type', 'add_to_cart')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('session_id')
            ->select(DB::raw('DATE(created_at) as visit_date'), 'session_id')
            ->groupBy(DB::raw('DATE(created_at)'), 'session_id')
            ->count();
        
        $previousAddToCart = DB::table('analytics_events')
            ->where('event_type', 'add_to_cart')
            ->whereBetween('created_at', [$previousStartDate, $previousEndDate])
            ->whereNotNull('session_id')
            ->select(DB::raw('DATE(created_at) as visit_date'), 'session_id')
            ->groupBy(DB::raw('DATE(created_at)'), 'session_id')
            ->count();
        
        $addToCartChange = $previousAddToCart > 0 
            ? (($addToCartEvents - $previousAddToCart) / $previousAddToCart) * 100 
            : ($addToCartEvents > 0 ? 100 : 0);
        
        // Contar sessões únicas que iniciaram checkout
        $checkoutStarted = DB::table('analytics_events')
            ->where('event_type', 'checkout_started')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('session_id')
            ->select(DB::raw('DATE(created_at) as visit_date'), 'session_id')
            ->groupBy(DB::raw('DATE(created_at)'), 'session_id')
            ->count();
        
        $previousCheckoutStarted = DB::table('analytics_events')
            ->where('event_type', 'checkout_started')
            ->whereBetween('created_at', [$previousStartDate, $previousEndDate])
            ->whereNotNull('session_id')
            ->select(DB::raw('DATE(created_at) as visit_date'), 'session_id')
            ->groupBy(DB::raw('DATE(created_at)'), 'session_id')
            ->count();
        
        $checkoutStartedChange = $previousCheckoutStarted > 0 
            ? (($checkoutStarted - $previousCheckoutStarted) / $previousCheckoutStarted) * 100 
            : ($checkoutStarted > 0 ? 100 : 0);
        
        // Contar sessões únicas que realizaram compra
        $purchases = DB::table('analytics_events')
            ->where('event_type', 'purchase')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('session_id')
            ->select(DB::raw('DATE(created_at) as visit_date'), 'session_id')
            ->groupBy(DB::raw('DATE(created_at)'), 'session_id')
            ->count();
        
        $previousPurchases = DB::table('analytics_events')
            ->where('event_type', 'purchase')
            ->whereBetween('created_at', [$previousStartDate, $previousEndDate])
            ->whereNotNull('session_id')
            ->select(DB::raw('DATE(created_at) as visit_date'), 'session_id')
            ->groupBy(DB::raw('DATE(created_at)'), 'session_id')
            ->count();
        
        $purchasesChange = $previousPurchases > 0 
            ? (($purchases - $previousPurchases) / $previousPurchases) * 100 
            : ($purchases > 0 ? 100 : 0);
        
        // Taxa de conversão (baseada em visitas únicas)
        // Limitar entre 0 e 100% para evitar valores impossíveis
        $conversionRate = $pageViews > 0 ? min(100, max(0, ($purchases / $pageViews) * 100)) : 0;
        $previousConversionRate = $previousPageViews > 0 ? min(100, max(0, ($previousPurchases / $previousPageViews) * 100)) : 0;
        $conversionRateChange = $previousConversionRate > 0 
            ? (($conversionRate - $previousConversionRate) / $previousConversionRate) * 100 
            : ($conversionRate > 0 ? 100 : 0);
        
        // Taxa de abandono de carrinho (adicionou ao carrinho mas não iniciou checkout)
        // Garantir que não seja negativo: se checkoutStarted > addToCartEvents, pode ser que alguns pedidos foram criados diretamente (PDV)
        // Nesse caso, considerar abandono como 0% (todos que adicionaram ao carrinho iniciaram checkout ou mais)
        $cartAbandonment = $addToCartEvents > 0 
            ? min(100, max(0, (($addToCartEvents - $checkoutStarted) / $addToCartEvents) * 100))
            : 0;
        
        // Taxa de conclusão de checkout (iniciou checkout e comprou)
        // Limitar a 100%: se purchases > checkoutStarted, pode ser que alguns pedidos foram criados diretamente (PDV)
        // Nesse caso, considerar como 100% (todos que iniciaram checkout compraram ou mais)
        $checkoutCompletionRate = $checkoutStarted > 0 
            ? min(100, max(0, ($purchases / $checkoutStarted) * 100))
            : 0;
        
        $chartData = $this->getChartData($startDate, $endDate);

        return view('dashboard.reports.index', compact(
            'totalOrders', 
            'totalAmount', 
            'averageTicket', 
            'statusSummary',
            'chartData',
            'startDate',
            'endDate',
            'revenueChange',
            'ordersChange',
            'newCustomers',
            'customersChange',
            'productsSold',
            'productsChange',
            'pageViews',
            'pageViewsChange',
            'addToCartEvents',
            'addToCartChange',
            'checkoutStarted',
            'checkoutStartedChange',
            'purchases',
            'purchasesChange',
            'conversionRate',
            'conversionRateChange',
            'cartAbandonment',
            'checkoutCompletionRate'
        ));
    }

    public function export(Request $request)
    {
        // Implementar exportação de relatórios
        return response()->json(['message' => 'Exportação em desenvolvimento']);
    }

    private function getChartData($startDate, $endDate)
    {
        $orders = Order::whereBetween('created_at', [$startDate, $endDate])
            ->where('payment_status', 'paid')
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'labels' => $orders->pluck('date')->map(fn($date) => \Carbon\Carbon::parse($date)->format('d/m')),
            'data' => $orders->pluck('count')
        ];
    }
}
