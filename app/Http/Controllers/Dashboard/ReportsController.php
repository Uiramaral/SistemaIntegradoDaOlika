<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;
use App\Models\Customer;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportsController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->get('start_date') ? Carbon::parse($request->get('start_date')) : now()->startOfMonth();
        $endDate = $request->get('end_date') ? Carbon::parse($request->get('end_date')) : now()->endOfMonth();

        // Pedidos pagos no período
        $orders = Order::whereBetween('created_at', [$startDate, $endDate])
            ->whereIn('payment_status', ['approved', 'paid'])
            ->get();
        
        $totalOrders = $orders->count();
        $totalAmount = $orders->sum('final_amount') ?? 0;
        $averageTicket = $totalOrders > 0 ? $totalAmount / $totalOrders : 0;
        
        // Período anterior para comparação
        $periodDays = $startDate->diffInDays($endDate) + 1;
        $previousStartDate = $startDate->copy()->subDays($periodDays);
        $previousEndDate = $startDate->copy()->subDay();
        
        $previousOrders = Order::whereBetween('created_at', [$previousStartDate, $previousEndDate])
            ->whereIn('payment_status', ['approved', 'paid'])
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
                  ->whereIn('payment_status', ['approved', 'paid']);
            })
            ->sum('quantity') ?? 0;
        
        $previousProductsSold = OrderItem::whereHas('order', function($q) use ($previousStartDate, $previousEndDate) {
                $q->whereBetween('created_at', [$previousStartDate, $previousEndDate])
                  ->whereIn('payment_status', ['approved', 'paid']);
            })
            ->sum('quantity') ?? 0;
        
        $productsChange = $previousProductsSold > 0 
            ? (($productsSold - $previousProductsSold) / $previousProductsSold) * 100 
            : ($productsSold > 0 ? 100 : 0);
        
        $statusSummary = $orders->groupBy('status')->map->count();
        
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
            'productsChange'
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
            ->whereIn('payment_status', ['approved', 'paid'])
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
