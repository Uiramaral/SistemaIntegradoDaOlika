<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Subscription;
use App\Models\Plan;
use App\Models\Order;
use App\Models\MasterSetting;
use App\Models\AiUsageLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        // Estatísticas de IA do mês
        $aiStats = $this->getAiStats();

        // Lucro por cliente (Top 10)
        $aiProfitByClient = $this->getAiProfitByClient();

        // Uso por modelo
        $aiUsageByModel = $this->getAiUsageByModel();

        return view('master.dashboard.index', compact(
            'stats',
            'recentClients',
            'expiringSubscriptions',
            'planDistribution',
            'aiStats',
            'aiProfitByClient',
            'aiUsageByModel'
        ));
    }

    /**
     * Estatísticas gerais de IA do mês
     */
    private function getAiStats(): array
    {
        try {
            // Verificar se a tabela existe
            if (!\Schema::hasTable('ai_usage_logs')) {
                return $this->getEmptyAiStats();
            }

            $stats = DB::table('ai_usage_logs')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->selectRaw('
                    COUNT(*) as total_requests,
                    COUNT(DISTINCT client_id) as clients_with_ai,
                    COALESCE(SUM(input_tokens + output_tokens), 0) as total_tokens,
                    COALESCE(SUM(cost_usd), 0) as total_cost_usd,
                    COALESCE(SUM(cost_brl), 0) as total_cost_brl,
                    COALESCE(SUM(charged_brl), 0) as total_charged_brl,
                    COALESCE(SUM(profit_brl), 0) as total_profit_brl,
                    COALESCE(SUM(CASE WHEN success = 0 THEN 1 ELSE 0 END), 0) as total_errors
                ')
                ->first();

            $profitMargin = ($stats->total_cost_brl ?? 0) > 0 
                ? round((($stats->total_profit_brl ?? 0) / $stats->total_cost_brl) * 100, 2) 
                : 200; // Markup padrão

            return [
                'total_requests' => $stats->total_requests ?? 0,
                'clients_with_ai' => $stats->clients_with_ai ?? 0,
                'total_tokens' => $stats->total_tokens ?? 0,
                'total_cost_usd' => $stats->total_cost_usd ?? 0,
                'total_cost_brl' => $stats->total_cost_brl ?? 0,
                'total_charged_brl' => $stats->total_charged_brl ?? 0,
                'total_profit_brl' => $stats->total_profit_brl ?? 0,
                'total_errors' => $stats->total_errors ?? 0,
                'profit_margin' => $profitMargin,
            ];
        } catch (\Exception $e) {
            return $this->getEmptyAiStats();
        }
    }

    /**
     * Stats vazias para quando não há dados
     */
    private function getEmptyAiStats(): array
    {
        return [
            'total_requests' => 0,
            'clients_with_ai' => 0,
            'total_tokens' => 0,
            'total_cost_usd' => 0,
            'total_cost_brl' => 0,
            'total_charged_brl' => 0,
            'total_profit_brl' => 0,
            'total_errors' => 0,
            'profit_margin' => 200,
        ];
    }

    /**
     * Lucro de IA por cliente (Top 10)
     */
    private function getAiProfitByClient()
    {
        try {
            if (!\Schema::hasTable('ai_usage_logs')) {
                return collect();
            }

            return DB::table('ai_usage_logs as l')
                ->join('clients as c', 'c.id', '=', 'l.client_id')
                ->whereMonth('l.created_at', now()->month)
                ->whereYear('l.created_at', now()->year)
                ->where('l.success', true)
                ->select('c.id', 'c.name', 'c.slug')
                ->selectRaw('
                    COALESCE(c.ai_balance, 0) as ai_balance,
                    COUNT(*) as requests_count,
                    SUM(l.input_tokens + l.output_tokens) as total_tokens,
                    SUM(l.cost_brl) as total_cost_brl,
                    SUM(l.charged_brl) as total_charged_brl,
                    SUM(l.profit_brl) as total_profit_brl
                ')
                ->groupBy('c.id', 'c.name', 'c.slug', 'c.ai_balance')
                ->orderByDesc('total_profit_brl')
                ->limit(10)
                ->get();
        } catch (\Exception $e) {
            return collect();
        }
    }

    /**
     * Uso de IA por modelo
     */
    private function getAiUsageByModel()
    {
        try {
            if (!\Schema::hasTable('ai_usage_logs')) {
                return collect();
            }

            return DB::table('ai_usage_logs')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->where('success', true)
                ->select('model')
                ->selectRaw('
                    COUNT(*) as requests_count,
                    SUM(input_tokens + output_tokens) as total_tokens,
                    SUM(cost_brl) as total_cost_brl,
                    SUM(charged_brl) as total_charged_brl,
                    SUM(profit_brl) as total_profit_brl
                ')
                ->groupBy('model')
                ->orderByDesc('requests_count')
                ->get();
        } catch (\Exception $e) {
            return collect();
        }
    }
}
