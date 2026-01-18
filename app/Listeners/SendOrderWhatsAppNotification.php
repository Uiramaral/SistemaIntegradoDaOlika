<?php

namespace App\Listeners;

use App\Events\OrderStatusUpdated;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Listener para envio de notificações WhatsApp via bot Railway
 * 
 * IMPORTANTE: Executa de forma SÍNCRONA (sem fila) para ambiente compartilhado
 * onde não há queue worker rodando continuamente.
 */
class SendOrderWhatsAppNotification
{
    /**
     * Número de tentativas em caso de falha
     */
    private const MAX_RETRIES = 3;
    
    /**
     * Intervalo entre tentativas (em milissegundos)
     */
    private const RETRY_DELAY_MS = 15000;

    /**
     * Handle the event.
     */
    public function handle(OrderStatusUpdated $event): void
    {
        // Listener desativado em favor do WhatsAppService (multi-instâncias)
        // O envio agora é gerenciado diretamente no OrderStatusService
        // para garantir o roteamento correto da instância.
        
        Log::debug('SendOrderWhatsAppNotification: Listener ignorado (usando OrderStatusService)', [
            'order_id' => $event->order->id,
            'event' => $event->event
        ]);
    }

    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);
        $country = config('notifications.wa_default_country', '55');

        if (empty($digits)) {
            return $digits;
        }

        if (Str::startsWith($digits, $country)) {
            return $digits;
        }

        return $country . $digits;
    }
}

