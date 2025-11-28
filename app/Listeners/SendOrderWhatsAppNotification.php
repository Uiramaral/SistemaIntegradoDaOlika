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
        $webhookUrl = config('notifications.wa_webhook_url');

        if (empty($webhookUrl)) {
            Log::warning('⚠️ WhatsApp webhook URL não configurado! Configure WHATSAPP_WEBHOOK_URL no .env', [
                'event' => $event->event,
                'order_id' => $event->order->id,
                'order_number' => $event->order->order_number ?? null,
                'config_key' => 'notifications.wa_webhook_url',
                'env_var' => 'WHATSAPP_WEBHOOK_URL',
            ]);

            return;
        }
        
        // Log quando o listener é executado (para debug)
        Log::info('📤 SendOrderWhatsAppNotification executado', [
            'order_id' => $event->order->id,
            'event' => $event->event,
            'webhook_url' => $webhookUrl,
        ]);

        // Garantir que a URL termina com /api/notify se não especificado
        if (!str_ends_with($webhookUrl, '/api/notify') && !str_ends_with($webhookUrl, '/send-message')) {
            $webhookUrl = rtrim($webhookUrl, '/') . '/api/notify';
        }

        $order = $event->order->loadMissing(['customer', 'items.product', 'address']);
        $customer = $order->customer;

        if (!$customer || empty($customer->phone)) {
            Log::info('Pedido sem telefone de cliente. WhatsApp webhook ignorado.', [
                'order_id' => $order->id,
            ]);

            return;
        }

        $phone = $this->normalizePhone($customer->phone);

        $payload = [
            'event' => $event->event,
            'status' => $order->status,
            'note' => $event->note,
            'meta' => $event->meta,
            'order' => [
                'id' => $order->id,
                'number' => $order->order_number,
                'status' => $order->status,
                'payment_method' => $order->payment_method,
                'delivery_type' => $order->delivery_type,
                'total' => (float) ($order->final_amount ?? $order->total_amount ?? 0),
                'delivery_fee' => (float) ($order->delivery_fee ?? 0),
                'discount' => (float) ($order->discount_amount ?? 0),
                'scheduled_for' => optional($order->scheduled_delivery_at)->toIso8601String(),
                'notes' => $event->note ?? $order->notes ?? $order->observations,
                'items' => $order->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->custom_name ?? optional($item->product)->name,
                        'quantity' => (int) ($item->quantity ?? 1),
                        'unit_price' => (float) ($item->unit_price ?? 0),
                        'total' => (float) ($item->total_price ?? 0),
                    ];
                })->values()->all(),
            ],
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'phone' => $phone,
                'raw_phone' => $customer->phone,
                'email' => $customer->email,
            ],
            'address' => $order->address ? [
                'street' => $order->address->street,
                'number' => $order->address->number,
                'neighborhood' => $order->address->neighborhood,
                'city' => $order->address->city,
                'state' => $order->address->state,
                'zipcode' => $order->address->zipcode,
                'complement' => $order->address->complement,
                'reference' => $order->address->reference,
            ] : null,
        ];

        $headers = [
            'X-Source-System' => config('app.name', 'olika'),
            'Content-Type' => 'application/json',
        ];

        // O bot aceita x-api-token, x-webhook-token ou x-olika-token
        if ($token = config('notifications.wa_token')) {
            $headers['X-Olika-Token'] = $token;
            // Fallback para compatibilidade
            $headers['X-Webhook-Token'] = $token;
        }

        // Retry manual para ambiente compartilhado (sem filas)
        $lastError = null;
        $attempt = 0;
        
        while ($attempt < self::MAX_RETRIES) {
            $attempt++;
            
            try {
                // Log da tentativa (apenas na primeira)
                if ($attempt === 1) {
                    Log::info('📤 Tentando enviar para WhatsApp webhook', [
                        'order_id' => $order->id,
                        'event' => $event->event,
                        'webhook_url' => $webhookUrl,
                        'phone' => $phone,
                    ]);
                }
                
                $response = Http::timeout((int) config('notifications.wa_timeout', 10))
                    ->asJson()
                    ->withHeaders($headers)
                    ->post($webhookUrl, $payload);

                if ($response->failed()) {
                    $statusCode = $response->status();
                    $responseBody = $response->body();
                    
                    $lastError = [
                        'status' => $statusCode,
                        'body' => $responseBody,
                    ];
                    
                    // Tratamento específico para erro 502 (Bad Gateway)
                    if ($statusCode === 502) {
                        Log::error('❌ Bot WhatsApp não está respondendo (502 Bad Gateway)', [
                            'attempt' => $attempt,
                            'order_id' => $order->id,
                            'event' => $event->event,
                            'webhook_url' => $webhookUrl,
                            'message' => 'O bot no Railway pode estar offline, crashado ou demorando muito para responder',
                            'suggestion' => 'Verifique o status do bot no Railway dashboard',
                        ]);
                    } else {
                        Log::warning('WhatsApp webhook retorno de erro.', [
                            'attempt' => $attempt,
                            'status' => $statusCode,
                            'body' => $responseBody,
                            'order_id' => $order->id,
                        ]);
                    }
                    
                    // Se não for o último attempt, aguardar antes de tentar novamente
                    if ($attempt < self::MAX_RETRIES) {
                        $waitSeconds = self::RETRY_DELAY_MS / 1000;
                        Log::info("⏳ Aguardando {$waitSeconds} segundos antes de tentar novamente...", [
                            'order_id' => $order->id,
                            'next_attempt' => $attempt + 1,
                        ]);
                        usleep(self::RETRY_DELAY_MS * 1000); // Converter para microsegundos
                        continue;
                    }
                    
                    $response->throw();
                }

                Log::info('✅ WhatsApp webhook enviado com sucesso!', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'event' => $event->event,
                    'attempt' => $attempt,
                    'phone' => $phone,
                    'response_status' => $response->status(),
                ]);
                
                // Sucesso - sair do loop
                return;
                
            } catch (\Throwable $e) {
                $lastError = [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ];
                
                Log::warning('Falha ao enviar payload WhatsApp webhook (tentativa ' . $attempt . '/' . self::MAX_RETRIES . ').', [
                    'order_id' => $order->id,
                    'event' => $event->event,
                    'attempt' => $attempt,
                    'error' => $e->getMessage(),
                ]);
                
                // Se não for o último attempt, aguardar antes de tentar novamente
                if ($attempt < self::MAX_RETRIES) {
                    usleep(self::RETRY_DELAY_MS * 1000); // Converter para microsegundos
                    continue;
                }
            }
        }
        
        // Se chegou aqui, todas as tentativas falharam
        $errorMessage = 'Falha ao enviar payload WhatsApp webhook após ' . self::MAX_RETRIES . ' tentativas.';
        
        // Mensagem específica para erro 502
        if (isset($lastError['status']) && $lastError['status'] === 502) {
            $errorMessage .= ' O bot no Railway não está respondendo (502 Bad Gateway). Verifique se o bot está rodando.';
        }
        
        Log::error("❌ {$errorMessage}", [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'event' => $event->event,
            'webhook_url' => $webhookUrl,
            'phone' => $phone,
            'last_error' => $lastError,
            'troubleshooting' => [
                '1' => 'Verifique se o bot está rodando no Railway',
                '2' => 'Verifique os logs do Railway para erros',
                '3' => 'Teste a URL manualmente: curl ' . $webhookUrl,
                '4' => 'Verifique se o token está correto',
            ],
        ]);
        
        // Não lançar exceção para não quebrar o fluxo principal
        // Apenas logar o erro
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

