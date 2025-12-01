<?php
namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Models\Order;
use App\Models\Customer;
use App\Models\WhatsappInstance;

class WhatsAppService
{
    /**
     * Obtém a instância correta para um número de telefone
     */
    private function getInstanceForPhone(string $phone): ?WhatsappInstance
    {
        // Tenta encontrar ou criar cliente temporário para roteamento
        // Fornecer um nome padrão caso precise criar
        $customer = Customer::firstOrCreate(
            ['phone' => $phone],
            ['name' => 'Cliente WhatsApp', 'is_active' => true] 
        );
        
        // Usa o roteador para decidir qual instância usar
        $instance = WhatsAppRouter::getInstanceForCustomer($customer);
        
        if (!$instance) {
            Log::warning("WhatsAppService: Nenhuma instância disponível para o telefone {$phone}");
            return null;
        }
        
        return $instance;
    }

    /**
     * Prepara headers e URL base
     */
    private function prepareRequest(WhatsappInstance $instance)
    {
        $baseUrl = rtrim($instance->api_url, '/');
        $token = $instance->api_token ?? env('API_SECRET');
        
        return [
            'baseUrl' => $baseUrl,
            'headers' => ['X-Olika-Token' => $token]
        ];
    }

    /**
     * Verifica se há alguma instância conectada
     * Se não houver no banco, tenta checar a API diretamente para atualizar status
     */
    public function isEnabled(): bool
    {
        // 1. Verificação rápida no banco
        $count = WhatsappInstance::where('status', 'CONNECTED')->count();
        if ($count > 0) return true;

        // 2. Se banco diz que não, verificar API de cada instância (Auto-Recovery)
        $instances = WhatsappInstance::whereNotNull('api_url')->get();
        $foundConnected = false;

        foreach ($instances as $instance) {
            try {
                $url = rtrim($instance->api_url, '/');
                $token = $instance->api_token ?? env('API_SECRET');
                
                // Timeout curto para não travar o request
                $response = Http::timeout(2)
                    ->withHeaders(['X-Olika-Token' => $token])
                    ->get("{$url}/api/whatsapp/status");
                
                if ($response->successful()) {
                    $data = $response->json();
                    if (isset($data['connected']) && $data['connected'] === true) {
                        // Opa! Estava conectado mas o banco não sabia. Atualizar!
                        $instance->update(['status' => 'CONNECTED']);
                        Log::info("WhatsAppService: Status da instância '{$instance->name}' corrigido para CONNECTED via auto-check.");
                        $foundConnected = true;
                    }
                }
            } catch (\Exception $e) {
                // Ignora erro de conexão na verificação rápida
            }
        }

        if ($foundConnected) return true;

        // 3. Último recurso: permitir tentar enviar mesmo desconectado se houver config
        // Isso garante que o erro apareça no envio e não aqui
        $anyInstance = $instances->count() > 0;
        
        if ($anyInstance) {
            Log::info("WhatsAppService::isEnabled - Nenhuma instância confirmada como CONNECTED, mas forçando tentativa.");
            return true;
        }
        
        Log::warning("WhatsAppService::isEnabled - Nenhuma instância configurada encontrada.");
        return false;
    }

    /**
     * Envia texto usando uma instância específica (Ignora roteamento automático)
     */
    public function sendFromInstance(WhatsappInstance $instance, string $phone, string $text)
    {
        $config = $this->prepareRequest($instance);
        $targetUrl = "{$config['baseUrl']}/api/whatsapp/send";

        try {
            $response = Http::withHeaders($config['headers'])
                ->timeout(30)
                ->post($targetUrl, [
                    'number' => $phone,
                    'message' => $text
                ]);

            return $response->json();
        } catch (\Exception $e) {
            Log::error('WhatsAppService sendFromInstance error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Envia texto simples (Newsletter/Avulso)
     */
    public function sendText(string $phone, string $text)
    {
        $instance = $this->getInstanceForPhone($phone);
        
        if (!$instance) {
            Log::warning("WhatsAppService: Nenhuma instância disponível para o telefone {$phone}. Verifique se há instâncias cadastradas no banco.");
            return ['success' => false, 'error' => 'Nenhuma instância disponível'];
        }

        $config = $this->prepareRequest($instance);
        $targetUrl = "{$config['baseUrl']}/api/whatsapp/send";

        Log::info('WhatsAppService: Tentando enviar mensagem', [
            'target_url' => $targetUrl,
            'instance_name' => $instance->name,
            'instance_phone' => $instance->phone_number,
            'recipient' => $phone
        ]);

        try {
            $response = Http::withHeaders($config['headers'])
                ->timeout(30)
                ->post($targetUrl, [
                    'number' => $phone,
                    'message' => $text
                ]);

            return $response->json();
        } catch (\Exception $e) {
            Log::error('WhatsAppService sendText error: ' . $e->getMessage(), [
                'target_url' => $targetUrl
            ]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Envia atualização de pedido
     */
    public function sendOrderUpdate($order, $customer, string $event)
    {
        $phone = $customer->phone ?? $customer['phone'] ?? '';
        if (!$phone) return ['success' => false, 'error' => 'Telefone inválido'];

        $instance = $this->getInstanceForPhone($phone);
        if (!$instance) return ['success' => false, 'error' => 'Nenhuma instância disponível'];

        $config = $this->prepareRequest($instance);

        try {
            $payload = [
                'phone' => $phone,
                'event' => $event,
                'order' => [
                    'id' => $order->id ?? $order['id'] ?? null,
                    'number' => $order->order_number ?? $order->number ?? $order['order_number'] ?? $order['number'] ?? null,
                    'total' => $order->total_amount ?? $order->total ?? $order['total_amount'] ?? $order['total'] ?? 0
                ],
                'customer' => [
                    'name' => $customer->name ?? $customer['name'] ?? 'Cliente'
                ]
            ];

            $response = Http::withHeaders($config['headers'])
                ->timeout(30)
                ->post("{$config['baseUrl']}/api/notify", $payload);

            return $response->json();
        } catch (\Exception $e) {
            Log::error('WhatsAppService sendOrderUpdate error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Compatibilidade: Send Newsletter (alias para sendText)
     */
    public function sendNewsletter(string $phone, string $messageText)
    {
        return $this->sendText($phone, $messageText);
    }

    /**
     * Template com placeholders
     */
    public function sendTemplate(string $phone, string $template, array $vars = [])
    {
        $msg = $template;
        foreach($vars as $k=>$v) $msg = str_replace('{'.$k.'}', $v, $msg);
        return $this->sendText($phone, $msg);
    }

    // Métodos de conveniência para pedidos
    public function sendPaymentConfirmed(Order $order)
    {
        $msg = "✅ *Pagamento confirmado!*\n\nOlá, {$order->customer->name}!\nSeu pedido *#{$order->order_number}* foi confirmado.\n\n📦 Em breve entraremos em contato.";
        return $this->sendText($order->customer->phone, $msg);
    }

    public function sendOrderDelivered(Order $order, ?string $note = null)
    {
        $msg = "🎉 *Pedido entregue!*\n\nOlá, {$order->customer->name}!\nSeu pedido *#{$order->order_number}* chegou.\n" . ($note ? "\n📝 Obs: $note" : "") . "\n\nObrigado pela preferência! 😋";
        return $this->sendText($order->customer->phone, $msg);
    }

    public function notifyAdmin(string $orderNumber, string $customerName, float $total, string $paymentMethod)
    {
        $adminPhone = env('WHATSAPP_ADMIN_NUMBER');
        if (!$adminPhone) return false;
        
        $msg = "💰 Pedido *#{$orderNumber}* pago.\nCliente: {$customerName}\nTotal: R$ " . number_format($total,2,',','.') . "\nForma: " . strtoupper($paymentMethod);
        
        // Envia pela instância principal (ou qualquer uma disponível)
        return $this->sendText($adminPhone, $msg);
    }

    /**
     * Envia recibo de pedido pago via WhatsApp
     */
    public function sendReceipt(Order $order): array
    {
        if (!$order->customer || empty($order->customer->phone)) {
            return ['success' => false, 'error' => 'Cliente não possui telefone cadastrado'];
        }

        $message = $this->formatReceiptMessage($order);
        return $this->sendText($order->customer->phone, $message);
    }

    /**
     * Formata mensagem de recibo de pedido pago
     */
    public function formatReceiptMessage(Order $order): string
    {
        $customerName = trim((string)($order->customer->name ?? ''));
        $orderNum = (string)($order->order_number ?? $order->id);
        $deliveryType = $order->delivery_method === 'pickup' ? 'Retirada' : 'Entrega';
        $paymentLabel = match ($order->payment_method) {
            'pix' => 'PIX',
            'credit', 'debit', 'card' => 'Crédito/Débito',
            default => ucfirst((string)$order->payment_method)
        };

        $addressLine = null;
        if ($order->address) {
            $addr = $order->address;
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
            $name = $it->custom_name ?? ($it->product->name ?? 'Item');
            $total = (float)($it->total_price ?? (($it->unit_price ?? $it->price ?? 0) * $q));
            $items[] = sprintf('👉 %dx %s  R$ %s', $q, $name, number_format($total, 2, ',', '.'));
        }

        $deliveryFee = (float)($order->delivery_fee ?? 0);
        $final = (float)($order->final_amount ?? $order->total_amount ?? 0);
        $cashbackEarned = (float)($order->cashback_earned ?? 0);

        // Link de acompanhamento
        $trackingUrl = null;
        try {
            if ($order->customer && $order->customer->phone) {
                $phoneParam = urlencode(preg_replace('/\D/', '', $order->customer->phone));
                $trackingUrl = 'https://pedido.menuolika.com.br/customer/orders/' . $order->order_number . '?phone=' . $phoneParam;
            }
        } catch (\Throwable $e) {
            Log::warning('Erro ao gerar link de acompanhamento', ['order_id' => $order->id]);
        }

        // Template padrão
        $lines = [];
        $lines[] = '✅ PAGAMENTO CONFIRMADO! ✅';
        $lines[] = '';
        $lines[] = 'Olá, '.($customerName ?: 'Cliente').'! 😄';
        $lines[] = '';
        $lines[] = 'Seu pedido foi confirmado e já está na nossa produção artesanal! 🥖✨';
        $lines[] = '';

        $lines[] = '📦 PEDIDO: '.$orderNum;
        
        if ($addressLine) {
            $lines[] = '📍 Entrega: '.$addressLine;
        }
        
        if ($order->scheduled_delivery_at) {
            $scheduledDate = $order->scheduled_delivery_at->format('d/m/Y');
            $scheduledTime = $order->scheduled_delivery_at->format('H\hi');
            $lines[] = '📅 Agendado para: '.$scheduledDate.' às '.$scheduledTime;
        }
        
        $lines[] = '';
        $lines[] = '🧾 Resumo do Pedido';
        $lines[] = '';

        if (!empty($items)) {
            foreach ($items as $item) {
                $lines[] = $item;
            }
        }
        
        $lines[] = '';
        $lines[] = '💳 Pagamento via '.$paymentLabel;
        $lines[] = '💰 Total: R$ '.number_format($final, 2, ',', '.');

        if ($cashbackEarned > 0) {
            $lines[] = '🔁 Cashback liberado: R$ '.number_format($cashbackEarned, 2, ',', '.');
        }

        $lines[] = '';
        
        if ($trackingUrl) {
            $lines[] = '📲 Acompanhe seu pedido:';
            $lines[] = $trackingUrl;
            $lines[] = '';
        }

        $lines[] = 'Obrigado por escolher nossos produtos — feitos à mão e com muito carinho! 💚';
        
        return implode("\n", $lines);
    }
}