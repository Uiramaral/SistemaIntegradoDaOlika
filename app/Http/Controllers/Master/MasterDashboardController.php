<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Subscription;
use App\Models\Plan;
use App\Models\Order;
use App\Models\MasterSetting;
use Illuminate\Http\Request;

class MasterDashboardController extends Controller
{
    /**
     * Dashboard principal do Master
     */
    public function index()
    {
        // Estatísticas gerais
        $stats = [
            'total_clients' => Client::count(),
            'active_clients' => Client::where('active', true)->count(),
            'active_subscriptions' => Subscription::active()->count(),
            'expiring_subscriptions' => Subscription::expiringSoon(7)->count(),
            'total_revenue' => Subscription::active()->sum('price'),
            'orders_today' => Order::whereDate('created_at', today())->count(),
            'orders_this_month' => Order::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];

        // Últimos clientes cadastrados
        $recentClients = Client::with('subscription.plan')
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        // Assinaturas expirando
        $expiringSubscriptions = Subscription::with(['client', 'plan'])
            ->expiringSoon(7)
            ->orderBy('current_period_end')
            ->take(10)
            ->get();

        // Distribuição por plano
        $planDistribution = Plan::withCount(['subscriptions' => function($q) {
            $q->where('status', 'active');
        }])->active()->get();

        return view('master.dashboard.index', compact(
            'stats',
            'recentClients',
            'expiringSubscriptions',
            'planDistribution'
        ));
    }
}
