<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;
use App\Models\Customer;

class ReportsController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfMonth());

        $orders = Order::whereBetween('created_at', [$startDate, $endDate])->get();
        
        $totalOrders = $orders->count();
        $totalAmount = $orders->sum('total');
        $averageTicket = $totalOrders > 0 ? $totalAmount / $totalOrders : 0;
        
        $statusSummary = $orders->groupBy('status')->map->count();
        
        $chartData = $this->getChartData($startDate, $endDate);

        return view('dash.pages.reports.index', compact(
            'totalOrders', 
            'totalAmount', 
            'averageTicket', 
            'statusSummary',
            'chartData',
            'startDate',
            'endDate'
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
