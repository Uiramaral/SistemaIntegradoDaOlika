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
        
        // Carregar campanhas salvas (rascunhos e concluídas)
        $savedCampaigns = MarketingCampaign::whereIn('status', ['draft', 'completed'])
            ->select('id', 'name', 'message_template_a', 'target_filter')
            ->latest()
            ->limit(20)
            ->get();
        
        // Estatísticas rápidas para ajudar nos filtros
        $stats = [
            'total_customers' => Customer::count(),
            'with_cashback' => DB::table('customer_cashback')
                ->whereIn('customer_id', Customer::pluck('id'))
                ->where('amount', '>', 0)
                ->distinct('customer_id')
                ->count('customer_id'),
            'with_orders' => Customer::has('orders')->count(),
        ];

        return view('dashboard.marketing.form', compact('variables', 'stats', 'savedCampaigns'));
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
            'send_type' => 'required|in:immediate,test,scheduled,specific_customer',
            'recurrence' => 'nullable|in:once,daily,weekly,monthly',
            'weekdays' => 'nullable|array',
            'interval_seconds' => 'required|integer|min:3|max:60',
            'save_as_draft' => 'boolean',
            'specific_customer_id' => 'nullable|exists:customers,id',
            
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

        // Calcular audiência (exceto para teste e cliente específico)
        if ($request->send_type === 'test') {
            $targetCount = 1; // Teste = 1 destinatário
        } elseif ($request->send_type === 'specific_customer') {
            $targetCount = 1; // Cliente específico = 1 destinatário
        } else {
            $targetCount = $this->calculateAudienceCount($filters);
        }
        
        // Determinar status baseado no tipo de envio e rascunho
        $status = 'draft';
        if ($request->save_as_draft) {
            $status = 'draft';
        } elseif ($request->send_type === 'scheduled') {
            $status = 'scheduled';
        } elseif ($request->send_type === 'immediate' || $request->send_type === 'test') {
            $status = 'running';
        }

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
            'send_immediately' => !$request->save_as_draft && $request->send_type === 'immediate',
            'interval_seconds' => $validated['interval_seconds'],
            'status' => $status,
            'created_by' => Auth::id(),
        ]);
        
        // Enviar teste
        if ($request->send_type === 'test' && !$request->save_as_draft) {
            return $this->sendTest($campaign);
        }
        
        // Enviar para cliente específico
        if ($request->send_type === 'specific_customer' && !$request->save_as_draft) {
            return $this->sendToSpecificCustomer($campaign, $request->specific_customer_id);
        }

        // Enviar imediatamente
        if ($request->send_type === 'immediate' && !$request->save_as_draft) {
            \App\Jobs\SendMarketingCampaignJob::dispatch($campaign);
            return redirect()
                ->route('dashboard.marketing.show', $campaign)
                ->with('success', 'Campanha iniciada! Enviando mensagens...');
        }
        
        // Salvar rascunho ou agendar
        $message = $request->save_as_draft 
            ? 'Campanha salva como rascunho!' 
            : ($request->send_type === 'scheduled' ? 'Campanha agendada com sucesso!' : 'Campanha criada!');

        return redirect()
            ->route('dashboard.marketing.show', $campaign)
            ->with('success', $message);
    }
    
    /**
     * Enviar teste para número de notificação admin
     */
    private function sendTest(MarketingCampaign $campaign)
    {
        // Buscar número de notificação nas configurações do WhatsApp
        $whatsappSettings = DB::table('whatsapp_settings')
            ->where('active', 1)
            ->first();
        
        $adminPhone = $whatsappSettings->admin_notification_phone ?? null;
        
        if (!$adminPhone) {
            return back()->with('error', 'Número de notificação admin não configurado! Configure em Integrações > WhatsApp');
        }
        
        // Criar cliente fake para teste
        $testCustomer = new \App\Models\Customer([
            'name' => 'Admin Teste',
            'phone' => $adminPhone,
            'email' => 'admin@teste.com',
            'cashback_balance' => 15.75,
        ]);
        $testCustomer->id = 999999; // ID fake para evitar conflitos
        
        // Processar mensagem com variáveis
        $message = $campaign->processMessage($campaign->message_template_a, $testCustomer);
        
        // ✨ Personalizar com Gemini (se habilitado)
        $gemini = app(\App\Services\OpenAIService::class);
        if ($gemini->isGeminiEnabled()) {
            try {
                $context = [
                    'cashback' => $testCustomer->cashback_balance,
                    'total_pedidos' => 3,
                    'ultimo_pedido' => now()->subDays(5)->format('d/m/Y'),
                ];
                
                $personalizedMessage = $gemini->personalizeMarketingMessage($message, $testCustomer->name, $context);
                
                // Só usa a mensagem personalizada se obteve sucesso
                if (!empty($personalizedMessage) && $personalizedMessage !== $message) {
                    $message = $personalizedMessage;
                }
            } catch (\Exception $e) {
                Log::warning('Falha ao personalizar mensagem de teste com Gemini: ' . $e->getMessage());
                // Continua com mensagem original
            }
        }
        
        // Enviar via WhatsApp
        try {
            $whatsapp = app(\App\Services\WhatsAppService::class);
            $finalMessage = "⚠️ *TESTE DE CAMPANHA*\n\n" . $message . "\n\n_Teste enviado em " . now()->format('d/m/Y H:i') . "_";
            
            $result = $whatsapp->sendText($adminPhone, $finalMessage);
            
            if ($result) {
                return redirect()
                    ->route('dashboard.marketing.show', $campaign)
                    ->with('success', 'Teste enviado para ' . $adminPhone . '!' . ($gemini->isGeminiEnabled() ? ' (Personalizado com IA)' : ''));
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao enviar teste: ' . $e->getMessage());
        }
        
        return back()->with('error', 'Falha ao enviar mensagem de teste.');
    }
    
    /**
     * Enviar para cliente específico
     */
    private function sendToSpecificCustomer(MarketingCampaign $campaign, $customerId)
    {
        if (!$customerId) {
            return back()->with('error', 'Nenhum cliente selecionado!');
        }
        
        $customer = \App\Models\Customer::find($customerId);
        
        if (!$customer) {
            return back()->with('error', 'Cliente não encontrado!');
        }
        
        if (empty($customer->phone)) {
            return back()->with('error', 'Cliente sem telefone cadastrado!');
        }
        
        // Processar mensagem
        $message = $campaign->processMessage($campaign->message_template_a, $customer);
        
        // ✨ Personalizar com Gemini (se habilitado)
        $gemini = app(\App\Services\OpenAIService::class);
        if ($gemini->isGeminiEnabled()) {
            try {
                $context = [
                    'cashback' => $customer->cashback_balance ?? 0,
                    'total_pedidos' => $customer->orders()->count(),
                    'ultimo_pedido' => $customer->orders()->latest()->first()?->created_at?->format('d/m/Y'),
                ];
                
                $personalizedMessage = $gemini->personalizeMarketingMessage($message, $customer->name, $context);
                
                if (!empty($personalizedMessage) && $personalizedMessage !== $message) {
                    $message = $personalizedMessage;
                }
            } catch (\Exception $e) {
                Log::warning('Falha ao personalizar mensagem com Gemini: ' . $e->getMessage());
            }
        }
        
        // Criar log
        \App\Models\MarketingCampaignLog::create([
            'campaign_id' => $campaign->id,
            'customer_id' => $customer->id,
            'phone' => $customer->phone,
            'customer_name' => $customer->name,
            'message_sent' => $message,
            'template_version' => 'A',
            'status' => 'pending',
        ]);
        
        // Enviar
        try {
            $whatsapp = app(\App\Services\WhatsAppService::class);
            $result = $whatsapp->sendText($customer->phone, $message);
            
            if ($result) {
                // Atualizar log
                $campaign->increment('sent_count');
                $campaign->increment('delivered_count');
                
                return redirect()
                    ->route('dashboard.marketing.show', $campaign)
                    ->with('success', 'Mensagem enviada para ' . $customer->name . '!' . ($gemini->isGeminiEnabled() ? ' (Personalizada com IA)' : ''));
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao enviar: ' . $e->getMessage());
        }
        
        return back()->with('error', 'Falha ao enviar mensagem.');
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
