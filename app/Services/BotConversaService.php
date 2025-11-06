<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class BotConversaService
{
    private ?string $webhookUrl;
    private ?string $token;

    public function __construct()
    {
        // Prioridade: 1) payment_settings do banco (se válido), 2) Config (.env), 3) null
        $dbWebhookUrl = $this->getSetting('botconversa_webhook_url');
        $this->webhookUrl = $dbWebhookUrl
            ?: config('services.botconversa.webhook_url')
            ?: null;
        
        $dbToken = $this->getSetting('botconversa_token');
        $this->token = $dbToken
            ?: config('services.botconversa.token')
            ?: null;
        
        // Para paid_webhook, usar método específico
        $this->paidWebhookUrl = $this->getPaidWebhookUrl();
    }
    
    /**
     * Obtém a URL do webhook para pedidos pagos
     * Prioridade: payment_settings -> .env (paid_webhook) -> .env (webhook_url) -> null
     */
    private function getPaidWebhookUrl(): ?string
    {
        // Tentar buscar do banco
        $dbPaidUrl = $this->getSetting('botconversa_paid_webhook_url');
        if ($dbPaidUrl) {
            return $dbPaidUrl;
        }
        
        // Fallback: .env paid_webhook
        $envPaidUrl = config('services.botconversa.paid_webhook');
        if ($envPaidUrl) {
            return $envPaidUrl;
        }
        
        // Fallback final: usar webhook_url padrão
        return $this->webhookUrl;
    }
    
    /**
     * URL específica para pedidos pagos (pode ser diferente da webhook_url padrão)
     */
    private ?string $paidWebhookUrl = null;
    
    /**
     * Busca uma configuração da tabela payment_settings (chave-valor) ou .env
     */
    private function getSetting(string $key): ?string
    {
        try {
            // Prioridade: payment_settings -> .env
            if (Schema::hasTable('payment_settings')) {
                $row = DB::table('payment_settings')->where('key', $key)->first();
                if ($row && !empty($row->value)) {
                    $value = $row->value;
                    // Se for email (inválido), retornar null para usar .env
                    if (strpos($value, '@') !== false && !filter_var($value, FILTER_VALIDATE_URL)) {
                        Log::info("BotConversaService: Valor inválido (email) encontrado para {$key}, usando .env");
                        return null;
                    }
                    return (string)$value;
                }
            }
            
            // Fallback: tentar settings se tiver estrutura chave-valor (improvável)
            if (Schema::hasTable('settings')) {
                $keyCol = collect(['key','name','config_key','setting_key','option','option_name'])
                    ->first(fn($c) => Schema::hasColumn('settings', $c));
                $valCol = collect(['value','val','config_value','content','data','option_value'])
                    ->first(fn($c) => Schema::hasColumn('settings', $c));
                
                if ($keyCol && $valCol) {
                    $value = DB::table('settings')
                        ->where($keyCol, $key)
                        ->value($valCol);
                    
                    if ($value) {
                        // Se for email (inválido), retornar null para usar .env
                        if (strpos($value, '@') !== false && !filter_var($value, FILTER_VALIDATE_URL)) {
                            Log::info("BotConversaService: Valor inválido (email) encontrado para {$key}, usando .env");
                            return null;
                        }
                        return (string)$value;
                    }
                }
            }
            
            return null;
        } catch (\Exception $e) {
            Log::warning('Erro ao ler setting do BotConversa', ['key' => $key, 'error' => $e->getMessage()]);
            return null;
        }
    }

    public function isConfigured(): bool
    {
        return !empty($this->webhookUrl);
    }

    public function send(array $payload): bool
    {
        if (!$this->isConfigured()) {
            Log::warning('BotConversa webhook não configurado', ['url' => $this->webhookUrl]);
            return false;
        }

        Log::info('BotConversa: Enviando webhook', [
            'url' => $this->webhookUrl,
            'payload_size' => strlen(json_encode($payload)),
            'has_token' => !empty($this->token)
        ]);

        $headers = ['Accept' => 'application/json'];
        if (!empty($this->token)) { 
            $headers['Authorization'] = 'Bearer '.$this->token; 
        }

        try {
            // Usar asJson() para garantir envio como JSON puro
            $res = Http::withHeaders($headers)
                ->asJson()
                ->post($this->webhookUrl, $payload);
            
            Log::info('BotConversa: Resposta do webhook', [
                'status' => $res->status(),
                'success' => $res->successful(),
                'body_preview' => substr($res->body(), 0, 200)
            ]);
            
            if ($res->failed()) {
                Log::error('Falha ao enviar para BotConversa', [
                    'status' => $res->status(),
                    'body' => $res->body(),
                    'url' => $this->webhookUrl
                ]);
                return false;
            }
            return true;
        } catch (\Exception $e) {
            Log::error('Exceção ao enviar para BotConversa', [
                'error' => $e->getMessage(),
                'url' => $this->webhookUrl
            ]);
            return false;
        }
    }

    /**
     * Normaliza telefone brasileiro para +55DDDNÚMERO (mantendo somente dígitos) conforme exemplo fornecido.
     */
    public function normalizePhoneBR(?string $raw): ?string
    {
        if (!$raw) return null;
        $digits = preg_replace('/\D+/', '', $raw);
        if ($digits === '') return null;
        // Remove prefixo 55 se já veio
        if (str_starts_with($digits, '55')) {
            $digits = substr($digits, 2);
        }
        // Garante DDD + número (10 ou 11 dígitos). Não tentamos inferir/padarizar além do pedido.
        return '+55'.$digits;
    }

    /**
     * Envia mensagem de texto simples via BotConversa
     * @param string $phone E.164 format (ex: +5511999999999)
     * @param string $message Texto da mensagem
     * @return bool
     */
    public function sendTextMessage(string $phone, string $message): bool
    {
        if (!$this->isConfigured()) {
            Log::warning('BotConversa não configurado para envio de mensagem de texto');
            return false;
        }

        // Normalizar telefone se necessário
        if (!str_starts_with($phone, '+55')) {
            $phone = $this->normalizePhoneBR($phone) ?? $phone;
        }

        // Construir payload conforme formato esperado pelo BotConversa
        // Assumindo formato básico: { "phone": "+5511999999999", "message": "texto" }
        $payload = [
            'phone' => $phone,
            'message' => $message,
        ];

        return $this->send($payload);
    }

    /**
     * Monta o payload JSON estruturado para pedido pago, com telefone como chave principal.
     */
    public function buildPaidJson(\App\Models\Order $order): array
    {
        $order->loadMissing('items.product','customer','address');
        $phone = $this->normalizePhoneBR(optional($order->customer)->phone);

        $items = [];
        foreach (($order->items ?? []) as $it) {
            $items[] = [
                'name' => $it->custom_name ?? optional($it->product)->name ?? 'Item',
                'quantity' => (int)($it->quantity ?? $it->qty ?? 1),
                'unit_price' => (float)($it->unit_price ?? $it->price ?? 0),
                'total' => (float)($it->total_price ?? (($it->unit_price ?? $it->price ?? 0) * (int)($it->quantity ?? 1))),
            ];
        }

        $addr = $order->address;
        $address = $addr ? array_filter([
            'street' => $addr->street ?? null,
            'number' => isset($addr->number) ? (string)$addr->number : null,
            'neighborhood' => $addr->neighborhood ?? null,
            'city' => $addr->city ?? null,
            'state' => $addr->state ?? null,
            'zipcode' => $addr->zipcode ?? null,
        ]) : null;

        // Link de acompanhamento
        $trackingUrl = null;
        try {
            if ($order->customer && $order->customer->phone) {
                $phoneParam = urlencode(preg_replace('/\D/', '', $order->customer->phone));
                // Usar pedido.menuolika.com.br ao invés de dashboard
                $trackingUrl = 'https://pedido.menuolika.com.br/customer/orders/' . $order->order_number . '?phone=' . $phoneParam;
            }
        } catch (\Throwable $e) {
            \Log::warning('Erro ao gerar link de acompanhamento', ['order_id' => $order->id]);
        }

        // Data e hora agendada
        $scheduledDelivery = null;
        if ($order->scheduled_delivery_at) {
            $scheduledDelivery = [
                'date' => $order->scheduled_delivery_at->format('d/m/Y'),
                'time' => $order->scheduled_delivery_at->format('H:i'),
                'datetime' => $order->scheduled_delivery_at->format('d/m/Y H:i'),
            ];
        }

        return [
            'phone' => $phone,
            'order' => [
                'id' => (int)$order->id,
                'number' => (string)($order->order_number ?? $order->id),
                'status' => (string)($order->status ?? 'confirmed'),
                'payment_status' => (string)($order->payment_status ?? 'paid'),
                'payment_method' => (string)($order->payment_method ?? ''),
                'subtotal' => (float)($order->total_amount ?? $order->subtotal ?? 0),
                'delivery_fee' => (float)($order->delivery_fee ?? 0),
                'discount_amount' => (float)($order->discount_amount ?? 0),
                'cashback_used' => (float)($order->cashback_used ?? 0),
                'cashback_earned' => (float)($order->cashback_earned ?? 0),
                'coupon_code' => $order->coupon_code ?? null,
                'coupon_discount' => $order->coupon_code ? (float)($order->discount_amount ?? 0) : 0,
                'total' => (float)($order->final_amount ?? $order->total_amount ?? 0),
                'scheduled_delivery' => $scheduledDelivery,
                'tracking_url' => $trackingUrl,
            ],
            'customer' => [
                'name' => optional($order->customer)->name,
                'email' => optional($order->customer)->email,
                'phone_raw' => optional($order->customer)->phone,
                'phone_e164' => $phone,
            ],
            'items' => $items,
            'delivery' => [
                'method' => $order->delivery_type ?? 'delivery',
                'address' => $address,
            ],
        ];
    }

    /**
     * Envia o JSON de pedido pago para o webhook. Permite URL custom nesta chamada.
     * Agora também envia a mensagem formatada junto com o JSON.
     */
    public function sendPaidOrderJson(\App\Models\Order $order, ?string $overrideWebhookUrl = null): bool
    {
        try {
            $payload = $this->buildPaidJson($order);
            
            // Adicionar mensagem formatada ao payload
            $payload['message'] = $this->buildPaidMessage($order);
            
            // Priorizar override, depois paidWebhookUrl (já configurado com fallback), depois webhook padrão
            $url = $overrideWebhookUrl 
                ?: $this->paidWebhookUrl 
                ?: $this->webhookUrl;
            
            Log::info('BotConversa: Preparando envio de pedido pago', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'url' => $url,
                'override_url' => $overrideWebhookUrl,
                'default_url' => $this->webhookUrl,
                'payload_keys' => array_keys($payload),
                'payload_size' => strlen(json_encode($payload))
            ]);
            
            if (empty($url)) {
                Log::warning('BotConversa webhook vazio ao enviar pedido pago', [
                    'order_id' => $order->id,
                    'override_url' => $overrideWebhookUrl,
                    'default_url' => $this->webhookUrl
                ]);
                return false;
            }
            
            $headers = ['Accept' => 'application/json', 'Content-Type' => 'application/json'];
            if (!empty($this->token)) { 
                $headers['Authorization'] = 'Bearer '.$this->token; 
            }
            
            Log::info('BotConversa: Enviando JSON para webhook', [
                'url' => $url,
                'headers' => array_keys($headers),
                'payload_preview' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            ]);
            
            // Usar asJson() para garantir envio como JSON puro
            $res = Http::withHeaders($headers)
                ->asJson()
                ->post($url, $payload);
            
            Log::info('BotConversa: Resposta do webhook de pedido pago', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $res->status(),
                'success' => $res->successful(),
                'body_preview' => substr($res->body(), 0, 500),
                'body_full' => $res->body(),
                'headers' => $res->headers(),
                'has_message' => isset($payload['message']),
                'message_length' => isset($payload['message']) ? strlen($payload['message']) : 0,
                'phone' => $payload['phone'] ?? null
            ]);
            
            if ($res->failed()) {
                Log::error('Falha ao enviar pedido pago (JSON) para BotConversa', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'status' => $res->status(),
                    'body' => $res->body(),
                    'url' => $url,
                    'payload_preview' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                ]);
                return false;
            }
            
            Log::info('Recibo enviado para BotConversa com sucesso', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'url' => $url
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Exceção ao enviar pedido pago para BotConversa', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    public function buildPaidMessage(\App\Models\Order $order): string
    {
        // Se houver um template salvo em settings, usa ele (opcional)
        $template = $this->getSetting('whatsapp_paid_template');

        $customerName = trim((string)($order->customer->name ?? ''));
        $orderNum     = (string)($order->order_number ?? $order->id);
        $deliveryType = $order->delivery_method === 'pickup' ? 'Retirada' : 'Entrega';
        $paymentLabel = match ($order->payment_method) {
            'pix' => 'PIX',
            'credit', 'debit', 'card' => 'Crédito/Débito',
            default => ucfirst((string)$order->payment_method)
        };

        $addressLine = null;
        if ($order->address) {
            $addr = $order->address;
            // Montar endereço no formato: Rua, Número – Cidade, Estado
            $streetParts = array_filter([
                $addr->street ?? null,
                isset($addr->number) ? (string)$addr->number : null,
            ]);
            $locationParts = array_filter([
                $addr->city ?? null,
                $addr->state ?? null,
            ]);
            
            $streetLine = !empty($streetParts) ? implode(', ', $streetParts) : null;
            $locationLine = !empty($locationParts) ? implode(', ', $locationParts) : null;
            
            if ($streetLine && $locationLine) {
                $addressLine = $streetLine.' – '.$locationLine;
            } elseif ($streetLine) {
                $addressLine = $streetLine;
            }
        }

        // Monta o resumo de itens
        $items = [];
        foreach (($order->items ?? []) as $it) {
            $q = (int)($it->quantity ?? $it->qty ?? 1);
            $name = $it->custom_name ?? optional($it->product)->name ?? 'Item';
            $total = (float)($it->total_price ?? (($it->unit_price ?? $it->price ?? 0) * $q));
            $items[] = sprintf('👉 %dx %s  R$ %s', $q, $name, number_format($total, 2, ',', '.'));
        }

        $subtotal     = (float)($order->total_amount ?? $order->subtotal ?? 0);
        $deliveryFee  = (float)($order->delivery_fee ?? 0);
        $discount     = (float)($order->discount_amount ?? 0);
        $cashbackUsed = (float)($order->cashback_used ?? 0);
        $final        = (float)($order->final_amount ?? $order->total_amount ?? 0);
        $couponCode   = $order->coupon_code ?? null;

        // Link de acompanhamento (rota do cliente)
        $trackingUrl = null;
        try {
            if ($order->customer && $order->customer->phone) {
                $phoneParam = urlencode(preg_replace('/\D/', '', $order->customer->phone));
                // Usar pedido.menuolika.com.br ao invés de dashboard
                $trackingUrl = 'https://pedido.menuolika.com.br/customer/orders/' . $order->order_number . '?phone=' . $phoneParam;
            }
        } catch (\Throwable $e) {
            \Log::warning('Erro ao gerar link de acompanhamento', ['order_id' => $order->id]);
        }

        // Data e hora agendada
        $scheduledDeliveryText = '';
        if ($order->scheduled_delivery_at) {
            $scheduledDeliveryText = "\n*Data e Hora de Entrega Agendada*: " . $order->scheduled_delivery_at->format('d/m/Y \à\s H:i');
        }

        if ($template) {
            // Placeholder simples
            $map = [
                '{ORDER_NUMBER}' => $orderNum,
                '{CUSTOMER_NAME}' => $customerName,
                '{PAYMENT_METHOD}' => $paymentLabel,
                '{DELIVERY_TYPE}' => $deliveryType,
                '{DELIVERY_ADDRESS}' => $addressLine ?: '—',
                '{ITEMS}' => implode("\n\n", $items),
                '{DELIVERY_FEE}' => 'R$ '.number_format($deliveryFee,2,',','.'),
                '{DISCOUNT}' => $discount > 0 ? ('R$ '.number_format($discount,2,',','.')) : 'R$ 0,00',
                '{CASHBACK_USED}' => $cashbackUsed > 0 ? ('R$ '.number_format($cashbackUsed,2,',','.')) : 'R$ 0,00',
                '{COUPON_CODE}' => $couponCode ?: '—',
                '{SCHEDULED_DELIVERY}' => $scheduledDeliveryText,
                '{TOTAL}' => 'R$ '.number_format($final,2,',','.'),
                '{TRACKING_URL}' => $trackingUrl ?: '',
            ];
            return strtr($template, $map);
        }

        // Template padrão conforme solicitado
        $lines = [];

        // Cabeçalho
        $lines[] = '✅ PAGAMENTO CONFIRMADO! ✅';
        $lines[] = '';
        $lines[] = 'Olá, '.($customerName ?: 'Cliente').'! 😄';
        $lines[] = '';
        $lines[] = 'Seu pedido foi confirmado e já está na nossa produção artesanal! 🥖✨';
        $lines[] = '';

        // Informações do pedido
        $lines[] = '📦 PEDIDO: '.$orderNum;
        
        // Endereço de entrega
        if ($addressLine) {
            $lines[] = '📍 Entrega: '.$addressLine;
        }
        
        // Data e hora agendada
        if ($order->scheduled_delivery_at) {
            $scheduledDate = $order->scheduled_delivery_at->format('d/m/Y');
            $scheduledTime = $order->scheduled_delivery_at->format('H\hi');
            $lines[] = '📅 Agendado para: '.$scheduledDate.' às '.$scheduledTime;
        }
        
        $lines[] = '';
        $lines[] = '🧾 Resumo do Pedido';
        $lines[] = '';

        // Itens do pedido
        if (!empty($items)) {
            foreach ($order->items ?? [] as $it) {
                $q = (int)($it->quantity ?? $it->qty ?? 1);
                $name = $it->custom_name ?? optional($it->product)->name ?? 'Item';
                $unitPrice = (float)($it->unit_price ?? $it->price ?? 0);
                $total = (float)($it->total_price ?? ($unitPrice * $q));
                $lines[] = $q.'x '.$name.' – R$ '.number_format($total, 2, ',', '.');
            }
        }
        
        $lines[] = '';
        $lines[] = '💳 Pagamento via '.$paymentLabel;
        $lines[] = '💰 Total: R$ '.number_format($final, 2, ',', '.');

        // Cashback liberado (se houver)
        $cashbackEarned = (float)($order->cashback_earned ?? 0);
        if ($cashbackEarned > 0) {
            $lines[] = '🔁 Cashback liberado: R$ '.number_format($cashbackEarned, 2, ',', '.');
        }

        $lines[] = '';
        
        // Link de acompanhamento
        if ($trackingUrl) {
            $lines[] = '📲 Acompanhe seu pedido:';
            $lines[] = $trackingUrl;
            $lines[] = '';
        }

        $lines[] = 'Obrigado por escolher nossos produtos — feitos à mão e com muito carinho! 💚';
        
        return implode("\n", $lines);
    }

    public function buildUnpaidReminder(\App\Models\Order $order): string
    {
        $lines = [];
        $lines[] = '👋 Olá! Seu pedido #'.$order->order_number.' ainda aguarda pagamento.';
        $final = (float)($order->final_amount ?? $order->total ?? 0);
        $lines[] = 'Total: R$ '.number_format($final,2,',','.');
        $lines[] = '';
        $lines[] = 'Pague agora:';
        $lines[] = url(route('pedido.payment.checkout', $order, false));
        $lines[] = 'PIX: '.url(route('pedido.payment.pix', $order, false));
        return implode("\n", $lines);
    }
}
