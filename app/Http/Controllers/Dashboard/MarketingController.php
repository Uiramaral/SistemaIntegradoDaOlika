<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\MarketingCampaign;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MarketingController extends Controller
{
    /**
     * Listar todas as campanhas
     */
    public function index()
    {
        $campaigns = MarketingCampaign::with('creator')
            ->latest()
            ->paginate(15);

        return view('dashboard.marketing.index', compact('campaigns'));
    }

    /**
     * Mostrar formulário de criação
     */
    public function create()
    {
        $variables = MarketingCampaign::AVAILABLE_VARIABLES;
        
        // Estatísticas rápidas para ajudar nos filtros
        $stats = [
            'total_customers' => Customer::count(),
            'with_cashback' => Customer::where('cashback_balance', '>', 0)->count(),
            'with_orders' => Customer::has('orders')->count(),
        ];

        return view('dashboard.marketing.form', compact('variables', 'stats'));
    }

    /**
     * Salvar nova campanha
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'message_template_a' => 'required|string',
            'message_template_b' => 'nullable|string',
            'message_template_c' => 'nullable|string',
            'use_ab_testing' => 'boolean',
            'scheduled_at' => 'nullable|date|after:now',
            'send_immediately' => 'boolean',
            'interval_seconds' => 'required|integer|min:3|max:300',
            
            // Filtros
            'filter_min_orders' => 'nullable|integer|min:0',
            'filter_max_orders' => 'nullable|integer|min:0',
            'filter_has_cashback' => 'boolean',
            'filter_min_cashback' => 'nullable|numeric|min:0',
            'filter_no_orders_days' => 'nullable|integer|min:0',
        ]);

        // Montar filtros
        $filters = array_filter([
            'min_orders' => $request->filter_min_orders,
            'max_orders' => $request->filter_max_orders,
            'has_cashback' => $request->filter_has_cashback,
            'min_cashback' => $request->filter_min_cashback,
            'no_orders_days' => $request->filter_no_orders_days,
        ]);

        // Calcular audiência
        $targetCount = $this->calculateAudienceCount($filters);

        $campaign = MarketingCampaign::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'message_template_a' => $validated['message_template_a'],
            'message_template_b' => $validated['message_template_b'] ?? null,
            'message_template_c' => $validated['message_template_c'] ?? null,
            'use_ab_testing' => $validated['use_ab_testing'] ?? false,
            'target_filter' => $filters,
            'target_count' => $targetCount,
            'scheduled_at' => $validated['scheduled_at'] ?? null,
            'send_immediately' => $validated['send_immediately'] ?? false,
            'interval_seconds' => $validated['interval_seconds'],
            'status' => $validated['send_immediately'] ? 'running' : 'draft',
            'created_by' => Auth::id(),
        ]);

        if ($campaign->send_immediately) {
            // Disparar job de envio
            \App\Jobs\SendMarketingCampaignJob::dispatch($campaign);
        }

        return redirect()
            ->route('dashboard.marketing.show', $campaign)
            ->with('success', 'Campanha criada com sucesso!');
    }

    /**
     * Mostrar detalhes da campanha
     */
    public function show(MarketingCampaign $campaign)
    {
        $campaign->load(['logs' => function($query) {
            $query->latest()->limit(50);
        }]);

        $stats = [
            'progress' => $campaign->getProgressPercentage(),
            'success_rate' => $campaign->getSuccessRate(),
            'pending' => $campaign->logs()->pending()->count(),
            'sent' => $campaign->logs()->sent()->count(),
            'delivered' => $campaign->logs()->delivered()->count(),
            'failed' => $campaign->logs()->failed()->count(),
        ];

        return view('dashboard.marketing.show', compact('campaign', 'stats'));
    }

    /**
     * Mostrar formulário de edição
     */
    public function edit(MarketingCampaign $campaign)
    {
        // Só pode editar se estiver em rascunho
        if (!in_array($campaign->status, ['draft', 'scheduled'])) {
            return back()->with('error', 'Não é possível editar uma campanha em andamento.');
        }

        $variables = MarketingCampaign::AVAILABLE_VARIABLES;
        
        return view('dashboard.marketing.form', compact('campaign', 'variables'));
    }

    /**
     * Atualizar campanha
     */
    public function update(Request $request, MarketingCampaign $campaign)
    {
        // Validar se pode atualizar
        if (!in_array($campaign->status, ['draft', 'scheduled'])) {
            return back()->with('error', 'Não é possível atualizar uma campanha em andamento.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'message_template_a' => 'required|string',
            'message_template_b' => 'nullable|string',
            'message_template_c' => 'nullable|string',
            'use_ab_testing' => 'boolean',
            'scheduled_at' => 'nullable|date|after:now',
            'interval_seconds' => 'required|integer|min:3|max:300',
        ]);

        $campaign->update($validated);

        return redirect()
            ->route('dashboard.marketing.show', $campaign)
            ->with('success', 'Campanha atualizada com sucesso!');
    }

    /**
     * Deletar campanha
     */
    public function destroy(MarketingCampaign $campaign)
    {
        // Não pode deletar se estiver rodando
        if ($campaign->status === 'running') {
            return back()->with('error', 'Não é possível deletar uma campanha em andamento. Pause primeiro.');
        }

        $campaign->delete();

        return redirect()
            ->route('dashboard.marketing.index')
            ->with('success', 'Campanha deletada com sucesso!');
    }

    /**
     * Iniciar campanha
     */
    public function start(MarketingCampaign $campaign)
    {
        if (!$campaign->canStart()) {
            return back()->with('error', 'Esta campanha não pode ser iniciada.');
        }

        $campaign->update([
            'status' => 'running',
            'started_at' => now(),
        ]);

        // Disparar job de envio
        \App\Jobs\SendMarketingCampaignJob::dispatch($campaign);

        return back()->with('success', 'Campanha iniciada!');
    }

    /**
     * Pausar campanha
     */
    public function pause(MarketingCampaign $campaign)
    {
        if (!$campaign->canPause()) {
            return back()->with('error', 'Esta campanha não pode ser pausada.');
        }

        $campaign->update(['status' => 'paused']);

        return back()->with('success', 'Campanha pausada!');
    }

    /**
     * Cancelar campanha
     */
    public function cancel(MarketingCampaign $campaign)
    {
        if (!$campaign->canCancel()) {
            return back()->with('error', 'Esta campanha não pode ser cancelada.');
        }

        $campaign->update([
            'status' => 'cancelled',
            'completed_at' => now(),
        ]);

        return back()->with('success', 'Campanha cancelada!');
    }

    /**
     * Preview da audiência
     */
    public function previewAudience(Request $request)
    {
        $filters = array_filter([
            'min_orders' => $request->filter_min_orders,
            'max_orders' => $request->filter_max_orders,
            'has_cashback' => $request->filter_has_cashback,
            'min_cashback' => $request->filter_min_cashback,
            'no_orders_days' => $request->filter_no_orders_days,
        ]);

        $count = $this->calculateAudienceCount($filters);
        
        $customers = $this->getAudienceQuery($filters)
            ->select('id', 'name', 'phone', 'cashback_balance')
            ->limit(10)
            ->get();

        return response()->json([
            'count' => $count,
            'sample' => $customers,
        ]);
    }

    /**
     * Calcular quantidade de pessoas na audiência
     */
    private function calculateAudienceCount(array $filters): int
    {
        return $this->getAudienceQuery($filters)->count();
    }

    /**
     * Montar query da audiência baseado nos filtros
     */
    private function getAudienceQuery(array $filters)
    {
        $query = Customer::query();

        if (isset($filters['min_orders'])) {
            $query->has('orders', '>=', $filters['min_orders']);
        }

        if (isset($filters['max_orders'])) {
            $query->has('orders', '<=', $filters['max_orders']);
        }

        if (isset($filters['has_cashback']) && $filters['has_cashback']) {
            $query->where('cashback_balance', '>', 0);
        }

        if (isset($filters['min_cashback'])) {
            $query->where('cashback_balance', '>=', $filters['min_cashback']);
        }

        if (isset($filters['no_orders_days'])) {
            $date = now()->subDays($filters['no_orders_days']);
            $query->whereDoesntHave('orders', function($q) use ($date) {
                $q->where('created_at', '>=', $date);
            });
        }

        return $query;
    }
}
