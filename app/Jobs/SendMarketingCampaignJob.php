<?php

namespace App\Jobs;

use App\Models\MarketingCampaign;
use App\Models\MarketingCampaignLog;
use App\Models\Customer;
use App\Services\WhatsAppService;
use App\Services\GeminiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendMarketingCampaignJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600; // 1 hora
    public $tries = 1; // Não retry automático

    protected MarketingCampaign $campaign;

    /**
     * Create a new job instance.
     */
    public function __construct(MarketingCampaign $campaign)
    {
        $this->campaign = $campaign;
    }

    /**
     * Execute the job.
     */
    public function handle(WhatsAppService $whatsAppService, GeminiService $geminiService): void
    {
        Log::info("Iniciando campanha de marketing #{$this->campaign->id}: {$this->campaign->name}");

        // Verificar se WhatsApp está configurado
        if (!$whatsAppService->isEnabled()) {
            $this->campaign->update([
                'status' => 'cancelled',
                'completed_at' => now(),
            ]);
            Log::error("Campanha #{$this->campaign->id} cancelada: WhatsApp não configurado");
            return;
        }

        // Verificar se campanha ainda está ativa
        if (!in_array($this->campaign->status, ['running', 'scheduled'])) {
            Log::warning("Campanha #{$this->campaign->id} não está em execução, abortan do.");
            return;
        }

        // Marcar como iniciada
        if (!$this->campaign->started_at) {
            $this->campaign->update(['started_at' => now()]);
        }

        // Buscar audiência baseado nos filtros
        $customers = $this->getTargetCustomers();

        Log::info("Campanha #{$this->campaign->id}: {$customers->count()} destinatários encontrados");

        $sentCount = 0;
        $deliveredCount = 0;
        $failedCount = 0;

        foreach ($customers as $customer) {
            // Verificar se campanha foi pausada/cancelada durante o envio
            $this->campaign->refresh();
            if ($this->campaign->status === 'paused') {
                Log::info("Campanha #{$this->campaign->id} pausada durante envio. Enviados: {$sentCount}");
                break;
            }
            if ($this->campaign->status === 'cancelled') {
                Log::info("Campanha #{$this->campaign->id} cancelada durante envio. Enviados: {$sentCount}");
                break;
            }

            // Verificar se já foi enviado para este cliente
            $alreadySent = MarketingCampaignLog::where('campaign_id', $this->campaign->id)
                ->where('customer_id', $customer->id)
                ->exists();

            if ($alreadySent) {
                continue;
            }

            // Verificar se cliente tem telefone válido
            if (empty($customer->phone)) {
                Log::warning("Cliente #{$customer->id} sem telefone. Pulando.");
                continue;
            }

            // Selecionar template (A/B/C testing)
            $templateData = $this->campaign->getRandomTemplate();
            $template = $templateData['template'];
            $version = $templateData['version'];

            // Processar variáveis do template
            $message = $this->campaign->processMessage($template, $customer);

            // ✨ PERSONALIZAR COM GEMINI AI (se habilitado)
            if ($geminiService->isEnabled()) {
                $context = [
                    'cashback' => $customer->cashback_balance ?? 0,
                    'total_pedidos' => $customer->orders()->count(),
                    'ultimo_pedido' => $customer->orders()->latest()->first()?->created_at?->format('d/m/Y'),
                ];
                
                $message = $geminiService->personalizeMarketingMessage($message, $customer->name, $context);
                
                Log::info("Mensagem personalizada com Gemini para {$customer->name}");
            }

            // Criar log pendente
            $log = MarketingCampaignLog::create([
                'campaign_id' => $this->campaign->id,
                'customer_id' => $customer->id,
                'phone' => $customer->phone,
                'customer_name' => $customer->name,
                'message_sent' => $message,
                'template_version' => $version,
                'status' => 'pending',
            ]);

            // Enviar mensagem via WhatsApp
            try {
                $result = $whatsAppService->sendText($customer->phone, $message);

                if ($result && isset($result['key']['id'])) {
                    // Sucesso
                    $log->update([
                        'status' => 'sent',
                        'sent_at' => now(),
                        'whatsapp_message_id' => $result['key']['id'] ?? null,
                    ]);
                    $sentCount++;
                    $deliveredCount++; // Evolution API considera sent = delivered
                    
                    Log::info("Mensagem enviada para {$customer->name} ({$customer->phone}) - Template {$version}");
                } else {
                    // Falha
                    $log->update([
                        'status' => 'failed',
                        'error_message' => 'Resposta inválida da API: ' . json_encode($result),
                    ]);
                    $failedCount++;
                    
                    Log::warning("Falha ao enviar para {$customer->name}: resposta inválida");
                }
            } catch (\Exception $e) {
                // Erro durante envio
                $log->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                ]);
                $failedCount++;
                
                Log::error("Erro ao enviar para {$customer->name}: " . $e->getMessage());
            }

            // Atualizar estatísticas da campanha
            $this->campaign->increment('sent_count');
            if ($log->status === 'sent') {
                $this->campaign->increment('delivered_count');
            } else {
                $this->campaign->increment('failed_count');
            }

            // Intervalo entre envios (evitar bloqueios)
            if ($this->campaign->interval_seconds > 0) {
                sleep($this->campaign->interval_seconds);
            }
        }

        // Marcar campanha como concluída
        $this->campaign->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        Log::info("Campanha #{$this->campaign->id} concluída. Enviados: {$sentCount}, Entregues: {$deliveredCount}, Falhas: {$failedCount}");
    }

    /**
     * Buscar clientes da audiência baseado nos filtros
     */
    private function getTargetCustomers()
    {
        $filters = $this->campaign->target_filter ?? [];
        $query = Customer::query();

        // Filtro: Mínimo de pedidos
        if (isset($filters['min_orders']) && $filters['min_orders'] > 0) {
            $query->has('orders', '>=', $filters['min_orders']);
        }

        // Filtro: Máximo de pedidos
        if (isset($filters['max_orders']) && $filters['max_orders'] > 0) {
            $query->has('orders', '<=', $filters['max_orders']);
        }

        // Filtro: Tem cashback
        if (isset($filters['has_cashback']) && $filters['has_cashback']) {
            $query->where('cashback_balance', '>', 0);
        }

        // Filtro: Cashback mínimo
        if (isset($filters['min_cashback']) && $filters['min_cashback'] > 0) {
            $query->where('cashback_balance', '>=', $filters['min_cashback']);
        }

        // Filtro: Sem pedidos nos últimos X dias
        if (isset($filters['no_orders_days']) && $filters['no_orders_days'] > 0) {
            $date = now()->subDays($filters['no_orders_days']);
            $query->whereDoesntHave('orders', function($q) use ($date) {
                $q->where('created_at', '>=', $date);
            });
        }

        // Ordenar por última compra (reativar clientes inativos primeiro)
        $query->withCount('orders')
            ->orderBy('orders_count', 'asc');

        return $query->get();
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Falha crítica na campanha #{$this->campaign->id}: " . $exception->getMessage());
        
        $this->campaign->update([
            'status' => 'cancelled',
            'completed_at' => now(),
        ]);
    }
}
