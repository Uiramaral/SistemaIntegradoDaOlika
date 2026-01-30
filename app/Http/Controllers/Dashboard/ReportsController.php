<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;
use App\Models\Customer;
use App\Models\OrderItem;
use App\Models\AnalyticsEvent;
use App\Models\FinancialTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class ReportsController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->get('start_date') ? Carbon::parse($request->get('start_date')) : now()->startOfMonth();
        $endDate = $request->get('end_date') ? Carbon::parse($request->get('end_date')) : now()->endOfMonth();
        $clientId = currentClientId();

        // CORREÇÃO: Usar FinancialTransaction como fonte de verdade para receita
        // Isso captura pedidos fiados que foram pagos no período, mesmo que criados antes
        $hasFinancialTransactions = Schema::hasTable('financial_transactions');
        
        if ($hasFinancialTransactions) {
            // Receita total do período baseada em transações financeiras
            $revenueQuery = FinancialTransaction::where('type', 'revenue')
                ->whereBetween('transaction_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
            
            if ($clientId) {
                $revenueQuery->where('client_id', $clientId);
            }
            
            $totalAmount = $revenueQuery->sum('amount') ?? 0;
            
            // Pedidos únicos que geraram receita no período
            $orderIds = FinancialTransaction::where('type', 'revenue')
                ->whereBetween('transaction_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->whereNotNull('order_id');
            
            if ($clientId) {
                $orderIds->where('client_id', $clientId);
            }
            
            $orderIds = $orderIds->pluck('order_id')->unique()->toArray();
            $totalOrders = count($orderIds);
            
            // Buscar pedidos para outras métricas
            $orders = Order::whereIn('id', $orderIds)->get();
            
            // Período anterior
            $periodDays = $startDate->diffInDays($endDate) + 1;
            $previousStartDate = $startDate->copy()->subDays($periodDays);
            $previousEndDate = $startDate->copy()->subDay();
            
            $previousRevenueQuery = FinancialTransaction::where('type', 'revenue')
                ->whereBetween('transaction_date', [$previousStartDate->format('Y-m-d'), $previousEndDate->format('Y-m-d')]);
            
            if ($clientId) {
                $previousRevenueQuery->where('client_id', $clientId);
            }
            
            $previousTotalAmount = $previousRevenueQuery->sum('amount') ?? 0;
            
            $previousOrderIds = FinancialTransaction::where('type', 'revenue')
                ->whereBetween('transaction_date', [$previousStartDate->format('Y-m-d'), $previousEndDate->format('Y-m-d')])
                ->whereNotNull('order_id');
            
            if ($clientId) {
                $previousOrderIds->where('client_id', $clientId);
            }
            
            $previousOrderIds = $previousOrderIds->pluck('order_id')->unique()->toArray();
            $previousTotalOrders = count($previousOrderIds);
            
            // Produtos vendidos: itens dos pedidos que geraram receita no período
            $productsSold = OrderItem::whereIn('order_id', $orderIds)
                ->sum('quantity') ?? 0;
            
            $previousProductsSold = OrderItem::whereIn('order_id', $previousOrderIds)
                ->sum('quantity') ?? 0;
        } else {
            // Fallback: usar método antigo se a tabela não existir
            $orders = Order::whereBetween('created_at', [$startDate, $endDate])
                ->where('payment_status', 'paid');
            
            if ($clientId) {
                $orders->where('client_id', $clientId);
            }
            
            $orders = $orders->get();
            
            $totalOrders = $orders->count();
            $totalAmount = $orders->sum('final_amount') ?? 0;
            
            $periodDays = $startDate->diffInDays($endDate) + 1;
            $previousStartDate = $startDate->copy()->subDays($periodDays);
            $previousEndDate = $startDate->copy()->subDay();
            
            $previousOrders = Order::whereBetween('created_at', [$previousStartDate, $previousEndDate])
                ->where('payment_status', 'paid');
            
            if ($clientId) {
                $previousOrders->where('client_id', $clientId);
            }
            
            $previousOrders = $previousOrders->get();
            
            $previousTotalAmount = $previousOrders->sum('final_amount') ?? 0;
            $previousTotalOrders = $previousOrders->count();
            
            $productsSold = OrderItem::whereHas('order', function($q) use ($startDate, $endDate, $clientId) {
                    $q->whereBetween('created_at', [$startDate, $endDate])
                      ->where('payment_status', 'paid');
                    if ($clientId) {
                        $q->where('client_id', $clientId);
                    }
                })
                ->sum('quantity') ?? 0;
            
            $previousProductsSold = OrderItem::whereHas('order', function($q) use ($previousStartDate, $previousEndDate, $clientId) {
                    $q->whereBetween('created_at', [$previousStartDate, $previousEndDate])
                      ->where('payment_status', 'paid');
                    if ($clientId) {
                        $q->where('client_id', $clientId);
                    }
                })
                ->sum('quantity') ?? 0;
        }
        
        $averageTicket = $totalOrders > 0 ? $totalAmount / $totalOrders : 0;
        $previousNewCustomers = Customer::whereBetween('created_at', [$previousStartDate, $previousEndDate]);
        if ($clientId) {
            $previousNewCustomers->where('client_id', $clientId);
        }
        $previousNewCustomers = $previousNewCustomers->count();
        
        // Comparações percentuais
        $revenueChange = $previousTotalAmount > 0 
            ? (($totalAmount - $previousTotalAmount) / $previousTotalAmount) * 100 
            : ($totalAmount > 0 ? 100 : 0);
        
        $ordersChange = $previousTotalOrders > 0 
            ? (($totalOrders - $previousTotalOrders) / $previousTotalOrders) * 100 
            : ($totalOrders > 0 ? 100 : 0);
        
        // Novos clientes no período
        $newCustomers = Customer::whereBetween('created_at', [$startDate, $endDate]);
        if ($clientId) {
            $newCustomers->where('client_id', $clientId);
        }
        $newCustomers = $newCustomers->count();
        
        $customersChange = $previousNewCustomers > 0 
            ? (($newCustomers - $previousNewCustomers) / $previousNewCustomers) * 100 
            : ($newCustomers > 0 ? 100 : 0);
        
        $productsChange = $previousProductsSold > 0 
            ? (($productsSold - $previousProductsSold) / $previousProductsSold) * 100 
            : ($productsSold > 0 ? 100 : 0);
        
        // Status summary: usar pedidos que geraram receita no período
        if (isset($orders) && $orders->count() > 0) {
            $statusSummary = $orders->groupBy('status')->map->count();
        } else {
            $statusSummary = collect();
        }
        
        // Métricas de Analytics
        // Contar visitas únicas: 1 sessão por dia (mesma sessão no mesmo dia = 1 visita)
        // Usar groupBy para contar combinações únicas de data + session_id
        // Filtrar por client_id do estabelecimento atual
        // $clientId já foi definido acima
        
        $pageViewsQuery = AnalyticsEvent::where('event_type', 'page_view')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('session_id');
        
        if ($clientId) {
            $pageViewsQuery->where('client_id', $clientId);
        }
        
        $pageViews = $pageViewsQuery->select(DB::raw('DATE(created_at) as visit_date'), 'session_id')
            ->groupBy(DB::raw('DATE(created_at)'), 'session_id')
            ->count();
        
        $previousPageViewsQuery = AnalyticsEvent::where('event_type', 'page_view')
            ->whereBetween('created_at', [$previousStartDate, $previousEndDate])
            ->whereNotNull('session_id');
        
        if ($clientId) {
            $previousPageViewsQuery->where('client_id', $clientId);
        }
        
        $previousPageViews = $previousPageViewsQuery->select(DB::raw('DATE(created_at) as visit_date'), 'session_id')
            ->groupBy(DB::raw('DATE(created_at)'), 'session_id')
            ->count();
        
        $pageViewsChange = $previousPageViews > 0 
            ? (($pageViews - $previousPageViews) / $previousPageViews) * 100 
            : ($pageViews > 0 ? 100 : 0);
        
        // Contar sessões únicas que adicionaram ao carrinho (não quantidade de produtos)
        $addToCartQuery = AnalyticsEvent::where('event_type', 'add_to_cart')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('session_id');
        
        if ($clientId) {
            $addToCartQuery->where('client_id', $clientId);
        }
        
        $addToCartEvents = $addToCartQuery->select(DB::raw('DATE(created_at) as visit_date'), 'session_id')
            ->groupBy(DB::raw('DATE(created_at)'), 'session_id')
            ->count();
        
        $previousAddToCartQuery = AnalyticsEvent::where('event_type', 'add_to_cart')
            ->whereBetween('created_at', [$previousStartDate, $previousEndDate])
            ->whereNotNull('session_id');
        
        if ($clientId) {
            $previousAddToCartQuery->where('client_id', $clientId);
        }
        
        $previousAddToCart = $previousAddToCartQuery->select(DB::raw('DATE(created_at) as visit_date'), 'session_id')
            ->groupBy(DB::raw('DATE(created_at)'), 'session_id')
            ->count();
        
        $addToCartChange = $previousAddToCart > 0 
            ? (($addToCartEvents - $previousAddToCart) / $previousAddToCart) * 100 
            : ($addToCartEvents > 0 ? 100 : 0);
        
        // Contar sessões únicas que iniciaram checkout
        $checkoutStartedQuery = AnalyticsEvent::where('event_type', 'checkout_started')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('session_id');
        
        if ($clientId) {
            $checkoutStartedQuery->where('client_id', $clientId);
        }
        
        $checkoutStarted = $checkoutStartedQuery->select(DB::raw('DATE(created_at) as visit_date'), 'session_id')
            ->groupBy(DB::raw('DATE(created_at)'), 'session_id')
            ->count();
        
        $previousCheckoutStartedQuery = AnalyticsEvent::where('event_type', 'checkout_started')
            ->whereBetween('created_at', [$previousStartDate, $previousEndDate])
            ->whereNotNull('session_id');
        
        if ($clientId) {
            $previousCheckoutStartedQuery->where('client_id', $clientId);
        }
        
        $previousCheckoutStarted = $previousCheckoutStartedQuery->select(DB::raw('DATE(created_at) as visit_date'), 'session_id')
            ->groupBy(DB::raw('DATE(created_at)'), 'session_id')
            ->count();
        
        $checkoutStartedChange = $previousCheckoutStarted > 0 
            ? (($checkoutStarted - $previousCheckoutStarted) / $previousCheckoutStarted) * 100 
            : ($checkoutStarted > 0 ? 100 : 0);
        
        // Contar sessões únicas que realizaram compra
        $purchasesQuery = AnalyticsEvent::where('event_type', 'purchase')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('session_id');
        
        if ($clientId) {
            $purchasesQuery->where('client_id', $clientId);
        }
        
        $purchases = $purchasesQuery->select(DB::raw('DATE(created_at) as visit_date'), 'session_id')
            ->groupBy(DB::raw('DATE(created_at)'), 'session_id')
            ->count();
        
        $previousPurchasesQuery = AnalyticsEvent::where('event_type', 'purchase')
            ->whereBetween('created_at', [$previousStartDate, $previousEndDate])
            ->whereNotNull('session_id');
        
        if ($clientId) {
            $previousPurchasesQuery->where('client_id', $clientId);
        }
        
        $previousPurchases = $previousPurchasesQuery->select(DB::raw('DATE(created_at) as visit_date'), 'session_id')
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
        $clientId = currentClientId();
        $hasFinancialTransactions = Schema::hasTable('financial_transactions');
        
        if ($hasFinancialTransactions) {
            // Usar transações financeiras para contar pedidos por data de receita
            $revenueQuery = FinancialTransaction::where('type', 'revenue')
                ->whereBetween('transaction_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->whereNotNull('order_id');
            
            if ($clientId) {
                $revenueQuery->where('client_id', $clientId);
            }
            
            $orders = $revenueQuery->selectRaw('transaction_date as date, COUNT(DISTINCT order_id) as count')
                ->groupBy('transaction_date')
                ->orderBy('transaction_date')
                ->get();
        } else {
            // Fallback: método antigo
            $ordersQuery = Order::whereBetween('created_at', [$startDate, $endDate])
                ->where('payment_status', 'paid');
            
            if ($clientId) {
                $ordersQuery->where('client_id', $clientId);
            }
            
            $orders = $ordersQuery->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->orderBy('date')
                ->get();
        }

        return [
            'labels' => $orders->pluck('date')->map(fn($date) => \Carbon\Carbon::parse($date)->format('d/m')),
            'data' => $orders->pluck('count')
        ];
    }
}
