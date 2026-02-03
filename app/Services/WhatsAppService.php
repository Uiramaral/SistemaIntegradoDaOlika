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
     * IMPORTANTE: Este método NÃO altera o número do telefone, apenas busca a instância
     * 
     * @param string $phone Número do telefone do destinatário (já normalizado)
     * @return array{instance: WhatsappInstance|null, correct_phone: string|null} Array com instância e telefone correto do banco
     */
    private function getInstanceForPhone(string $phone): array
    {
        // Log do número recebido - este é o número que será enviado
        Log::info('WhatsAppService::getInstanceForPhone - Início', [
            'phone_received_for_routing' => $phone,
        ]);

        // IMPORTANTE: Buscar cliente pelo telefone exato primeiro, sem criar novo
        // Isso evita criar clientes duplicados ou com números errados
        $customer = Customer::where('phone', $phone)->first();

        // Se não encontrou, tentar buscar por variações do número (com/sem código do país)
        if (!$customer) {
            // Tentar sem código do país
            $phoneWithoutCountry = preg_replace('/^55/', '', $phone);
            if ($phoneWithoutCountry !== $phone && strlen($phoneWithoutCountry) >= 10) {
                $customer = Customer::where('phone', $phoneWithoutCountry)->first();
                if (!$customer) {
                    $customer = Customer::where('phone', '55' . $phoneWithoutCountry)->first();
                }
            }

            // Se ainda não encontrou, tentar com código do país
            if (!$customer && !str_starts_with($phone, '55') && strlen($phone) >= 10) {
                $customer = Customer::where('phone', '55' . $phone)->first();
            }
        }

        // Se ainda não encontrou cliente, usar roteamento padrão SEM criar cliente
        if (!$customer) {
            Log::warning("WhatsAppService: Cliente não encontrado para o telefone {$phone}. Usando roteamento padrão.");
            // Buscar instância padrão sem cliente
            $instance = WhatsappInstance::where('status', 'CONNECTED')
                ->orWhere(function ($q) {
                    $q->whereNotNull('api_url');
                })
                ->orderBy('id')
                ->first();

            if ($instance) {
                Log::info('WhatsAppService: Usando instância padrão (cliente não encontrado)', [
                    'instance_name' => $instance->name,
                    'phone_requested' => $phone,
                ]);
                return ['instance' => $instance, 'correct_phone' => null];
            }

            return ['instance' => null, 'correct_phone' => null];
        }

        // Log do cliente encontrado - IMPORTANTE: verificar se o telefone bate
        Log::info('WhatsAppService: Cliente encontrado para roteamento', [
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'customer_phone_in_db' => $customer->phone,
            'phone_requested_for_routing' => $phone,
            'phones_match' => ($customer->phone === $phone),
        ]);

        // IMPORTANTE: Se o telefone do cliente no banco for diferente, usar o telefone do banco
        // O telefone do banco é o que está cadastrado no WhatsApp Business
        $correctPhone = $customer->phone;
        $phoneNormalized = preg_replace('/\D/', '', $correctPhone);
        $originalPhoneNormalized = $phoneNormalized;

        // Normalizar o telefone do banco para formato internacional (com 55)
        // A API do WhatsApp precisa do formato internacional para números brasileiros
        // Se o número não começa com 55 e tem 10 ou 11 dígitos, adicionar 55
        if (!str_starts_with($phoneNormalized, '55')) {
            $phoneLength = strlen($phoneNormalized);
            // Números brasileiros têm 10 ou 11 dígitos (sem código do país)
            if ($phoneLength >= 10 && $phoneLength <= 11) {
                $phoneNormalized = '55' . $phoneNormalized;
                Log::info('WhatsAppService: Adicionando código do país 55 ao telefone do banco', [
                    'customer_id' => $customer->id,
                    'phone_original_db' => $correctPhone,
                    'phone_original_normalized' => $originalPhoneNormalized,
                    'phone_normalized_with_55' => $phoneNormalized,
                    'phone_length' => $phoneLength,
                ]);
            } else {
                Log::warning('WhatsAppService: Telefone do banco tem formato inesperado', [
                    'customer_id' => $customer->id,
                    'phone_original_db' => $correctPhone,
                    'phone_normalized' => $phoneNormalized,
                    'phone_length' => $phoneLength,
                    'action' => 'Usando telefone como está (formato não padrão)',
                ]);
            }
        } else {
            Log::info('WhatsAppService: Telefone do banco já tem código do país 55', [
                'customer_id' => $customer->id,
                'phone_original_db' => $correctPhone,
                'phone_normalized' => $phoneNormalized,
            ]);
        }

        if ($customer->phone !== $phone) {
            Log::warning('WhatsAppService: Telefone do cliente no banco difere do solicitado - usando telefone do banco normalizado', [
                'customer_id' => $customer->id,
                'customer_phone_in_db' => $customer->phone,
                'phone_requested' => $phone,
                'phone_will_use' => $phoneNormalized,
                'action' => 'Usando telefone do banco de dados normalizado para formato internacional',
            ]);
        } else {
            Log::info('WhatsAppService: Telefone do banco corresponde ao solicitado', [
                'customer_id' => $customer->id,
                'phone' => $phoneNormalized,
            ]);
        }

        // Usa o roteador para decidir qual instância usar
        $instance = WhatsAppRouter::getInstanceForCustomer($customer);

        if (!$instance) {
            Log::warning("WhatsAppService: Nenhuma instância disponível para o telefone {$phone} (cliente ID: {$customer->id})");
            return ['instance' => null, 'correct_phone' => $phoneNormalized];
        }

        Log::info('WhatsAppService: Instância selecionada para roteamento', [
            'instance_name' => $instance->name,
            'instance_phone' => $instance->phone_number,
            'customer_phone_in_db' => $customer->phone,
            'phone_requested' => $phone,
            'phone_will_be_sent' => $phoneNormalized, // Usar telefone do banco normalizado
        ]);

        return ['instance' => $instance, 'correct_phone' => $phoneNormalized];
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
        if ($count > 0)
            return true;

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

        if ($foundConnected)
            return true;

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
     * 
     * @param string $phone Número do telefone do destinatário (já normalizado)
     * @param string $text Mensagem a ser enviada
     * @return array Resultado do envio
     */
    public function sendText(string $phone, string $text)
    {
        // Log do número recebido - IMPORTANTE: este é o número que DEVE ser enviado
        Log::info('WhatsAppService::sendText - Início', [
            'phone_received' => $phone,
            'phone_length' => strlen($phone),
            'phone_digits_only' => preg_replace('/\D/', '', $phone),
        ]);

        // IMPORTANTE: Guardar o número original que será enviado
        // Este número NÃO deve ser alterado
        $phoneToSend = preg_replace('/\D/', '', $phone);

        $result = $this->getInstanceForPhone($phoneToSend);
        $instance = $result['instance'] ?? null;
        $correctPhone = $result['correct_phone'] ?? null;

        // Se encontrou um telefone correto no banco, usar ele em vez do normalizado
        if ($correctPhone) {
            $phoneToSend = $correctPhone;
            Log::info('WhatsAppService: Usando telefone do banco de dados', [
                'phone_original' => $phone,
                'phone_from_db' => $correctPhone,
                'phone_will_send' => $phoneToSend,
            ]);
        }

        if (!$instance) {
            Log::warning("WhatsAppService: Nenhuma instância disponível para o telefone {$phoneToSend}. Verifique se há instâncias cadastradas no banco.");
            return ['success' => false, 'error' => 'Nenhuma instância disponível'];
        }

        $config = $this->prepareRequest($instance);
        $targetUrl = "{$config['baseUrl']}/api/whatsapp/send";

        // Log detalhado antes de enviar - GARANTIR que o número correto será enviado
        Log::info('WhatsAppService: Tentando enviar mensagem', [
            'target_url' => $targetUrl,
            'instance_name' => $instance->name,
            'instance_phone' => $instance->phone_number,
            'recipient_phone_ORIGINAL' => $phone,
            'recipient_phone_TO_SEND' => $phoneToSend,
            'phone_will_be_sent' => $phoneToSend,
        ]);

        try {
            // IMPORTANTE: Usar o número que foi guardado no início da função
            // NÃO usar $phone novamente, usar $phoneToSend que já foi normalizado
            // Log do payload que será enviado
            $payload = [
                'number' => $phoneToSend, // Número correto do destinatário
                'message' => $text
            ];

            Log::info('WhatsAppService: Payload que será enviado', [
                'target_url' => $targetUrl,
                'phone_received_original' => $phone,
                'phone_to_send_final' => $phoneToSend,
                'payload_number' => $payload['number'],
                'payload' => $payload,
            ]);

            $response = Http::withHeaders($config['headers'])
                ->timeout(30)
                ->post($targetUrl, $payload);

            // Verificar se a resposta foi bem-sucedida
            if (!$response->successful()) {
                $errorBody = $response->body();
                $errorMessage = 'Erro HTTP ' . $response->status();

                // Tentar extrair mensagem de erro do JSON
                try {
                    $errorJson = $response->json();
                    $errorMessage = $errorJson['error'] ?? $errorJson['message'] ?? $errorMessage;
                } catch (\Exception $e) {
                    // Se não for JSON, usar o body como está
                    $errorMessage = $errorBody ?: $errorMessage;
                }

                Log::error('WhatsAppService: Erro na resposta HTTP', [
                    'phone_received' => $phone,
                    'phone_sent' => $phoneToSend,
                    'response_status' => $response->status(),
                    'response_body' => $errorBody,
                    'error_message' => $errorMessage,
                    'target_url' => $targetUrl,
                ]);

                return [
                    'success' => false,
                    'error' => $errorMessage,
                    'http_status' => $response->status(),
                ];
            }

            $result = $response->json();

            // Verificar se a resposta JSON é válida
            if (!is_array($result)) {
                Log::error('WhatsAppService: Resposta JSON inválida', [
                    'phone_received' => $phone,
                    'phone_sent' => $phoneToSend,
                    'response_status' => $response->status(),
                    'response_body' => $response->body(),
                ]);

                return [
                    'success' => false,
                    'error' => 'Resposta inválida do gateway WhatsApp',
                ];
            }

            // Log da resposta com mais detalhes
            Log::info('WhatsAppService: Resposta da API', [
                'phone_received' => $phone,
                'phone_sent' => $phoneToSend,
                'response_status' => $response->status(),
                'response_success' => $result['success'] ?? false,
                'response_error' => $result['error'] ?? null,
                'message_id' => $result['messageId'] ?? null,
                'response_body' => $result,
            ]);

            // Verificar se há algum indicador de problema na entrega
            if (isset($result['success']) && $result['success'] === true) {
                // Mensagem foi aceita pela API
                if (isset($result['messageId'])) {
                    Log::info('WhatsAppService: Mensagem aceita pela API - aguardando entrega pelo WhatsApp', [
                        'phone_sent' => $phoneToSend,
                        'message_id' => $result['messageId'],
                        'note' => 'Se o destinatário não receber, pode ser: número não está no WhatsApp, bloqueou o contato, ou restrições de privacidade',
                    ]);
                }
            }

            return $result;
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            // Erro de conexão (timeout, DNS, etc)
            Log::error('WhatsAppService: Erro de conexão com o gateway', [
                'target_url' => $targetUrl,
                'phone_received' => $phone,
                'phone_sent' => $phoneToSend,
                'error' => $e->getMessage(),
            ]);
            return [
                'success' => false,
                'error' => 'Não foi possível conectar ao gateway WhatsApp. Verifique se o serviço está online.',
                'connection_error' => true,
            ];
        } catch (\Exception $e) {
            Log::error('WhatsAppService: Erro inesperado ao enviar mensagem', [
                'target_url' => $targetUrl,
                'phone_received' => $phone,
                'phone_sent' => $phoneToSend,
                'error' => $e->getMessage(),
                'error_class' => get_class($e),
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'success' => false,
                'error' => 'Erro ao enviar mensagem: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Envia atualização de pedido
     */
    public function sendOrderUpdate($order, $customer, string $event)
    {
        $phone = $customer->phone ?? $customer['phone'] ?? '';
        if (!$phone) {
            Log::warning('WhatsAppService::sendOrderUpdate - Telefone inválido', [
                'order_id' => $order->id ?? $order['id'] ?? null,
                'event' => $event,
            ]);
            return ['success' => false, 'error' => 'Telefone inválido'];
        }

        // Normalizar telefone
        $phoneNormalized = preg_replace('/\D/', '', $phone);
        if (strlen($phoneNormalized) >= 10 && !str_starts_with($phoneNormalized, '55')) {
            $phoneNormalized = '55' . $phoneNormalized;
        }

        $result = $this->getInstanceForPhone($phoneNormalized);
        $instance = $result['instance'] ?? null;
        $correctPhone = $result['correct_phone'] ?? null;

        // Se encontrou um telefone correto no banco, usar ele em vez do normalizado
        if ($correctPhone) {
            $phoneNormalized = $correctPhone;
        }

        if (!$instance) {
            Log::warning('WhatsAppService::sendOrderUpdate - Nenhuma instância disponível', [
                'order_id' => $order->id ?? $order['id'] ?? null,
                'phone_normalized' => $phoneNormalized,
                'event' => $event,
            ]);
            return ['success' => false, 'error' => 'Nenhuma instância disponível'];
        }

        $config = $this->prepareRequest($instance);

        try {
            $payload = [
                'phone' => $phoneNormalized, // Usar número correto do banco ou normalizado
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

            Log::info('WhatsAppService::sendOrderUpdate - Enviando atualização', [
                'order_id' => $order->id ?? $order['id'] ?? null,
                'phone_original' => $phone,
                'phone_normalized' => $phoneNormalized,
                'event' => $event,
            ]);

            $response = Http::withHeaders($config['headers'])
                ->timeout(30)
                ->post("{$config['baseUrl']}/api/notify", $payload);

            $result = $response->json();

            Log::info('WhatsAppService::sendOrderUpdate - Resposta', [
                'order_id' => $order->id ?? $order['id'] ?? null,
                'success' => $result['success'] ?? false,
                'error' => $result['error'] ?? null,
            ]);

            return $result;
        } catch (\Exception $e) {
            Log::error('WhatsAppService::sendOrderUpdate - Erro', [
                'order_id' => $order->id ?? $order['id'] ?? null,
                'phone_normalized' => $phoneNormalized,
                'event' => $event,
                'error' => $e->getMessage(),
            ]);
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
        foreach ($vars as $k => $v)
            $msg = str_replace('{' . $k . '}', $v, $msg);
        return $this->sendText($phone, $msg);
    }

    // Métodos de conveniência para pedidos
    public function sendPaymentConfirmed(Order $order)
    {
        if (!$order->customer || empty($order->customer->phone)) {
            return ['success' => false, 'error' => 'Cliente não possui telefone cadastrado'];
        }

        // Normalizar telefone
        $phoneNormalized = preg_replace('/\D/', '', $order->customer->phone);
        if (strlen($phoneNormalized) >= 10 && !str_starts_with($phoneNormalized, '55')) {
            $phoneNormalized = '55' . $phoneNormalized;
        }

        $msg = "✅ *Pagamento confirmado!*\n\nOlá, {$order->customer->name}!\nSeu pedido *#{$order->order_number}* foi confirmado.\n\n📦 Em breve entraremos em contato.";
        return $this->sendText($phoneNormalized, $msg);
    }

    public function sendOrderDelivered(Order $order, ?string $note = null)
    {
        if (!$order->customer || empty($order->customer->phone)) {
            return ['success' => false, 'error' => 'Cliente não possui telefone cadastrado'];
        }

        // Normalizar telefone
        $phoneNormalized = preg_replace('/\D/', '', $order->customer->phone);
        if (strlen($phoneNormalized) >= 10 && !str_starts_with($phoneNormalized, '55')) {
            $phoneNormalized = '55' . $phoneNormalized;
        }

        $msg = "🎉 *Pedido entregue!*\n\nOlá, {$order->customer->name}!\nSeu pedido *#{$order->order_number}* chegou.\n" . ($note ? "\n📝 Obs: $note" : "") . "\n\nObrigado pela preferência! 😋";
        return $this->sendText($phoneNormalized, $msg);
    }

    public function notifyAdmin(string $orderNumber, string $customerName, float $total, string $paymentMethod)
    {
        $adminPhone = env('WHATSAPP_ADMIN_NUMBER');
        if (!$adminPhone)
            return false;

        $msg = "💰 Pedido *#{$orderNumber}* pago.\nCliente: {$customerName}\nTotal: R$ " . number_format($total, 2, ',', '.') . "\nForma: " . strtoupper($paymentMethod);

        // Envia pela instância principal (ou qualquer uma disponível)
        return $this->sendText($adminPhone, $msg);
    }

    /**
     * Envia recibo de pedido pago via WhatsApp
     */
    public function sendReceipt(Order $order): array
    {
        if (!$order->customer || empty($order->customer->phone)) {
            Log::warning('WhatsAppService::sendReceipt - Cliente sem telefone', [
                'order_id' => $order->id,
                'customer_id' => $order->customer->id ?? null,
            ]);
            return ['success' => false, 'error' => 'Cliente não possui telefone cadastrado'];
        }

        // Log do telefone original do cliente
        Log::info('WhatsAppService::sendReceipt - Preparando envio', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'customer_id' => $order->customer->id,
            'customer_name' => $order->customer->name,
            'customer_phone_original' => $order->customer->phone,
        ]);

        // Normalizar telefone (adicionar código do país se necessário)
        $phoneNormalized = preg_replace('/\D/', '', $order->customer->phone);

        // Se já começar com 55, usar como está
        if (str_starts_with($phoneNormalized, '55')) {
            // Já está normalizado
        } elseif (strlen($phoneNormalized) >= 10) {
            // Se tiver 11 dígitos e começar com 0, remover o 0 antes de adicionar 55
            if (strlen($phoneNormalized) === 11 && $phoneNormalized[0] === '0') {
                $phoneNormalized = '55' . substr($phoneNormalized, 1);
            } else {
                $phoneNormalized = '55' . $phoneNormalized;
            }
        }

        // Log do telefone normalizado
        Log::info('WhatsAppService::sendReceipt - Telefone normalizado', [
            'order_id' => $order->id,
            'customer_phone_original' => $order->customer->phone,
            'phone_normalized' => $phoneNormalized,
            'phone_will_be_sent' => $phoneNormalized,
        ]);

        $message = $this->formatReceiptMessage($order);

        // IMPORTANTE: Usar o número normalizado, não o original
        return $this->sendText($phoneNormalized, $message);
    }

    /**
     * Formata mensagem de recibo de pedido pago
     */
    public function formatReceiptMessage(Order $order): string
    {
        $customerName = trim((string) ($order->customer->name ?? ''));
        $orderNum = (string) ($order->order_number ?? $order->id);
        $deliveryType = $order->delivery_method === 'pickup' ? 'Retirada' : 'Entrega';
        $paymentLabel = match ($order->payment_method) {
            'pix' => 'PIX',
            'credit', 'debit', 'card' => 'Crédito/Débito',
            default => ucfirst((string) $order->payment_method)
        };

        $addressLine = null;
        if ($order->address) {
            $addr = $order->address;
            $streetParts = array_filter([
                $addr->street ?? null,
                isset($addr->number) ? (string) $addr->number : null,
            ]);
            $locationParts = array_filter([
                $addr->city ?? null,
                $addr->state ?? null,
            ]);

            $streetLine = !empty($streetParts) ? implode(', ', $streetParts) : null;
            $locationLine = !empty($locationParts) ? implode(', ', $locationParts) : null;

            if ($streetLine && $locationLine) {
                $addressLine = $streetLine . ' – ' . $locationLine;
            } elseif ($streetLine) {
                $addressLine = $streetLine;
            }
        }

        // Monta o resumo de itens
        $items = [];
        foreach (($order->items ?? []) as $it) {
            $q = (int) ($it->quantity ?? $it->qty ?? 1);
            $name = $it->custom_name ?? ($it->product->name ?? 'Item');
            $total = (float) ($it->total_price ?? (($it->unit_price ?? $it->price ?? 0) * $q));
            $items[] = sprintf('👉 %dx %s  R$ %s', $q, $name, number_format($total, 2, ',', '.'));
        }

        $deliveryFee = (float) ($order->delivery_fee ?? 0);
        $final = (float) ($order->final_amount ?? $order->total_amount ?? 0);
        $cashbackEarned = (float) ($order->cashback_earned ?? 0);

        // Link de acompanhamento
        $trackingUrl = null;
        try {
            if ($order->customer && $order->customer->phone) {
                // Tenta obter o slug do cliente (loja) ou usa 'pedido' como fallback
                $slug = $order->client->slug ?? 'pedido';
                $baseDomain = 'menuolika.com.br'; // Poderia vir de config, mas mantendo padrão atual

                $phoneParam = urlencode(preg_replace('/\D/', '', $order->customer->phone));
                $trackingUrl = "https://{$slug}.{$baseDomain}/customer/orders/" . $order->order_number . '?phone=' . $phoneParam;
            }
        } catch (\Throwable $e) {
            Log::warning('Erro ao gerar link de acompanhamento', ['order_id' => $order->id, 'error' => $e->getMessage()]);
        }

        // Template padrão
        $lines = [];
        $lines[] = '✅ PAGAMENTO CONFIRMADO! ✅';
        $lines[] = '';
        $lines[] = 'Olá, ' . ($customerName ?: 'Cliente') . '! 😄';
        $lines[] = '';
        $lines[] = 'Seu pedido foi confirmado e já está na nossa produção artesanal! 🥖✨';
        $lines[] = '';

        $lines[] = '📦 PEDIDO: ' . $orderNum;

        if ($addressLine) {
            $lines[] = '📍 Entrega: ' . $addressLine;
        }

        if ($order->scheduled_delivery_at) {
            $scheduledDate = $order->scheduled_delivery_at->format('d/m/Y');
            $scheduledTime = $order->scheduled_delivery_at->format('H\hi');
            $lines[] = '📅 Agendado para: ' . $scheduledDate . ' às ' . $scheduledTime;
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
        $lines[] = '💳 Pagamento via ' . $paymentLabel;
        $lines[] = '💰 Total: R$ ' . number_format($final, 2, ',', '.');

        if ($cashbackEarned > 0) {
            $lines[] = '🔁 Cashback liberado: R$ ' . number_format($cashbackEarned, 2, ',', '.');
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