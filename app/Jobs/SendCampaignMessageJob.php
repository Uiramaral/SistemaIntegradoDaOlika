<?php

namespace App\Jobs;

use App\Models\WhatsappCampaign;
use App\Models\Customer;
use App\Services\WhatsAppRouter;
use App\Services\WhatsAppService;
use App\Models\WhatsappCampaignLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendCampaignMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $campaign;
    protected $customer;
    protected $message;

    public function __construct(WhatsappCampaign $campaign, Customer $customer, string $message)
    {
        $this->campaign = $campaign;
        $this->customer = $customer;
        $this->message = $message;
    }

    public function handle(WhatsAppService $waService)
    {
        // Verificar se a campanha ainda está ativa
        if ($this->campaign->status === 'paused' || $this->campaign->status === 'cancelled') {
            return;
        }

        $phone = $this->customer->phone;
        
        // Rotacionar instância (Round Robin)
        $instance = WhatsAppRouter::getRotatedInstance();
        
        if (!$instance) {
            Log::error("Campanha #{$this->campaign->id}: Nenhuma instância disponível para envio.");
            $this->logAttempt(null, 'failed', 'Nenhuma instância conectada');
            return;
        }

        // Substituir variáveis na mensagem
        $finalMessage = str_replace(
            ['{name}', '{nome}', '{phone}', '{telefone}'],
            [$this->customer->name, $this->customer->name, $this->customer->phone, $this->customer->phone],
            $this->message
        );

        // Enviar usando a instância selecionada especificamente
        $result = $waService->sendFromInstance($instance, $phone, $finalMessage);

        $status = (isset($result['success']) && $result['success']) ? 'sent' : 'failed';
        $error = isset($result['error']) ? $result['error'] : null;

        $this->logAttempt($instance->id, $status, $error);
        
        // Atualizar contador da campanha
        $this->campaign->increment('processed_count');
        
        // Se for o último, marcar como concluída (lógica simplificada)
        if ($this->campaign->processed_count >= $this->campaign->total_leads) {
            $this->campaign->update(['status' => 'completed']);
        }
    }

    private function logAttempt($instanceId, $status, $error)
    {
        WhatsappCampaignLog::create([
            'campaign_id' => $this->campaign->id,
            'customer_id' => $this->customer->id,
            'phone' => $this->customer->phone,
            'whatsapp_instance_id' => $instanceId,
            'status' => $status,
            'error' => $error
        ]);
    }
}




