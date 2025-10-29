<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;

class DashboardController extends Controller
{
    public function home()
    {
        try {
            $todayOrders = Order::whereDate('created_at', today())->get();
            
            // Dados para o dashboard com tratamento de erro
            $totalPedidos = Order::count();
            $faturamento = Order::sum('final_amount') ?? 0;
            $novosClientes = \App\Models\Customer::whereDate('created_at', today())->count();
            $ticketMedio = $totalPedidos > 0 ? $faturamento / $totalPedidos : 0;
            
            // Contagem por status
            $statusCount = [
                'pending' => Order::where('status', 'pending')->count(),
                'confirmed' => Order::where('status', 'confirmed')->count(),
                'preparing' => Order::where('status', 'preparing')->count(),
                'delivered' => Order::where('status', 'delivered')->count(),
            ];
            
            return view('dash.pages.dashboard.index', compact(
                'todayOrders', 'totalPedidos', 'faturamento', 'novosClientes', 'ticketMedio', 'statusCount'
            ));
        } catch (\Exception $e) {
            // Em caso de erro, retornar valores padrão
            return view('dash.pages.dashboard.index', [
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
                ]
            ]);
        }
    }

    public function compact()
    {
        $todayOrders = Order::whereDate('created_at', today())->get();
        return view('dash.pages.dashboard.compact', compact('todayOrders'));
    }

    public function reports()
    {
        $reports = Order::latest()->take(10)->get();
        return view('dash.pages.reports.index', compact('reports'));
    }

    public function settings()
    {
        return view('dash.pages.settings.index');
    }
}