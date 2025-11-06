<?php

namespace App\Services;

use App\Models\PaymentSetting;
use App\Models\Order;
use App\Models\Customer;
use App\Services\OrderStatusService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MercadoPagoApiService
{
    protected $accessToken;
    protected $publicKey;
    protected $environment;
    protected $baseUrl;

    public function __construct()
    {
        $this->accessToken = PaymentSetting::getMercadoPagoToken();
        $this->publicKey = PaymentSetting::getMercadoPagoPublicKey();
        $this->environment = PaymentSetting::getMercadoPagoEnvironment();
        $this->baseUrl = $this->environment === 'production' 
            ? 'https://api.mercadopago.com' 
            : 'https://api.mercadopago.com';
    }

    /**
     * Cria cobrança PIX
     */
    public function createPixPayment(Order $order): array
    {
        $amount = $this->getTestAmount($order->final_amount);
        $externalReference = $this->generateExternalReference($order);

        $payload = [
            'transaction_amount' => $amount,
            'description' => "Pedido #{$order->order_number} - Olika",
            'payment_method_id' => 'pix',
            'external_reference' => $externalReference,
            'notification_url' => route('webhooks.mercadopago'),
            'metadata' => [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'customer_id' => $order->customer_id,
            ],
            'additional_info' => [
                'items' => $this->buildItems($order),
                'payer' => $this->buildPayer($order->customer),
            ],
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->accessToken,
            'Content-Type' => 'application/json',
        ])->post("{$this->baseUrl}/v1/payments", $payload);

        if ($response->successful()) {
            $data = $response->json();
            
            // Atualizar pedido com dados do pagamento
            $order->update([
                'payment_provider' => 'mercadopago',
                'payment_id' => $data['id'],
                'payment_status' => $data['status'],
                'payment_raw_response' => $data,
            ]);

            return [
                'success' => true,
                'payment_id' => $data['id'],
                'status' => $data['status'],
                'pix_qr_code' => $data['point_of_interaction']['transaction_data']['qr_code'] ?? null,
                'pix_qr_code_base64' => $data['point_of_interaction']['transaction_data']['qr_code_base64'] ?? null,
                'pix_copy_paste' => $data['point_of_interaction']['transaction_data']['qr_code'] ?? null,
                'expires_at' => now()->addMinutes(PaymentSetting::getPixExpirationMinutes()),
            ];
        }

        Log::error('Erro ao criar PIX no MercadoPago', [
            'order_id' => $order->id,
            'response' => $response->json(),
        ]);

        return [
            'success' => false,
            'error' => 'Erro ao criar cobrança PIX',
            'details' => $response->json(),
        ];
    }

    /**
     * Cria preferência de pagamento (cartão)
     */
    public function createPaymentPreference(Order $order): array
    {
        $amount = $this->getTestAmount($order->final_amount);
        $externalReference = $this->generateExternalReference($order);

        $payload = [
            'items' => $this->buildItems($order),
            'payer' => $this->buildPayer($order->customer),
            'back_urls' => [
                'success' => route('order.success', $order->id),
                'failure' => route('order.failure', $order->id),
                'pending' => route('order.pending', $order->id),
            ],
            'auto_return' => 'approved',
            'external_reference' => $externalReference,
            'notification_url' => route('api.webhooks.mercadopago'),
            'payment_methods' => [
                'excluded_payment_methods' => [],
                'excluded_payment_types' => [],
                'installments' => 12,
            ],
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->accessToken,
            'Content-Type' => 'application/json',
        ])->post("{$this->baseUrl}/checkout/preferences", $payload);

        if ($response->successful()) {
            $data = $response->json();
            
            // Atualizar pedido com dados da preferência
            $order->update([
                'payment_provider' => 'mercadopago',
                'preference_id' => $data['id'],
                'payment_link' => $data['init_point'],
                'payment_raw_response' => $data,
            ]);

            return [
                'success' => true,
                'preference_id' => $data['id'],
                'payment_link' => $data['init_point'],
                'sandbox_init_point' => $data['sandbox_init_point'] ?? null,
            ];
        }

        Log::error('Erro ao criar preferência no MercadoPago', [
            'order_id' => $order->id,
            'response' => $response->json(),
        ]);

        return [
            'success' => false,
            'error' => 'Erro ao criar preferência de pagamento',
            'details' => $response->json(),
        ];
    }

    /**
     * Consulta status de um pagamento
     */
    public function getPaymentStatus(string $paymentId): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->accessToken,
        ])->get("{$this->baseUrl}/v1/payments/{$paymentId}");

        if ($response->successful()) {
            return [
                'success' => true,
                'payment' => $response->json(),
            ];
        }

        return [
            'success' => false,
            'error' => 'Erro ao consultar pagamento',
            'details' => $response->json(),
        ];
    }

    /**
     * Extrai o payment_id do webhook do MercadoPago em diferentes formatos
     * 
     * @param array $data Dados do webhook
     * @return string|null ID do pagamento ou null se não encontrado
     */
    public static function extractPaymentId(array $data): ?string
    {
        $topic = $data['topic'] ?? $data['type'] ?? null;
        
        // Ignorar notificações de merchant_order
        if ($topic === 'merchant_order' || (isset($data['data']['topic']) && $data['data']['topic'] === 'merchant_order')) {
            return null;
        }
        
        // 1. Formato novo: data.id
        if (isset($data['data']['id']) && is_numeric($data['data']['id'])) {
            return (string)$data['data']['id'];
        }
        // 2. Formato alternativo: data_id
        if (isset($data['data_id']) && is_numeric($data['data_id'])) {
            return (string)$data['data_id'];
        }
        // 3. Formato PIX antigo: resource direto
        if (isset($data['resource'])) {
            if (is_numeric($data['resource'])) {
                return (string)$data['resource'];
            }
            if (is_string($data['resource']) && preg_match('/\/(\d+)(?:\?|$)/', $data['resource'], $matches)) {
                return $matches[1];
            }
        }
        // 4. Formato PIX antigo dentro de data: data.resource
        if (isset($data['data']['resource'])) {
            if (is_numeric($data['data']['resource'])) {
                return (string)$data['data']['resource'];
            }
            if (is_string($data['data']['resource']) && preg_match('/\/(\d+)(?:\?|$)/', $data['data']['resource'], $matches)) {
                return $matches[1];
            }
        }
        // 5. Formato PIX antigo: id direto
        if (isset($data['id']) && is_numeric($data['id'])) {
            return (string)$data['id'];
        }
        
        return null;
    }

    /**
     * Mapeia status do MercadoPago para valores válidos do ENUM payment_status
     * 
     * @param string $mpStatus Status retornado pelo MercadoPago
     * @return string Status válido para o ENUM: pending, paid, failed, refunded
     */
    public static function mapPaymentStatus(string $mpStatus): string
    {
        $status = strtolower(trim($mpStatus));
        
        // Mapear status do MercadoPago para valores do ENUM
        $statusMap = [
            'approved' => 'paid',
            'authorized' => 'paid',
            'paid' => 'paid',
            'pending' => 'pending',
            'in_process' => 'pending',
            'in_mediation' => 'pending',
            'cancelled' => 'failed',
            'rejected' => 'failed',
            'failed' => 'failed',
            'refunded' => 'refunded',
            'charged_back' => 'refunded',
        ];
        
        return $statusMap[$status] ?? 'pending';
    }

    /**
     * Processa webhook do MercadoPago
     */
    public function processWebhook(array $data): array
    {
        Log::info('MercadoPagoApiService: Webhook recebido', [
            'data_keys' => array_keys($data),
            'data_id' => $data['data']['id'] ?? null,
            'data_id_alt' => $data['data_id'] ?? null,
            'resource' => $data['resource'] ?? null,
            'id' => $data['id'] ?? null,
            'type' => $data['type'] ?? null,
            'action' => $data['action'] ?? null,
            'topic' => $data['topic'] ?? null,
        ]);

        // Extrair payment_id usando função helper que suporta todos os formatos
        $paymentId = self::extractPaymentId($data);
        
        if (!$paymentId) {
            $topic = $data['topic'] ?? $data['type'] ?? null;
            
            // Se for merchant_order, informar que foi ignorado
            if ($topic === 'merchant_order' || (isset($data['data']['topic']) && $data['data']['topic'] === 'merchant_order')) {
                Log::info('MercadoPagoApiService: Notificação de merchant_order ignorada', ['data' => $data]);
                return ['success' => false, 'error' => 'Notificação de merchant_order ignorada'];
            }
            
            Log::error('MercadoPagoApiService: ID do pagamento não encontrado no webhook', [
                'data' => $data,
                'data_keys' => array_keys($data),
                'topic' => $topic,
            ]);
            return ['success' => false, 'error' => 'ID do pagamento não encontrado'];
        }
        
        $topic = $data['topic'] ?? $data['type'] ?? null;
        Log::info('MercadoPagoApiService: Payment ID extraído', [
            'payment_id' => $paymentId,
            'topic' => $topic,
            'has_data' => isset($data['data']),
            'has_resource' => isset($data['resource']) || isset($data['data']['resource']),
        ]);
        $paymentStatus = $this->getPaymentStatus($paymentId);

        if (!$paymentStatus['success']) {
            Log::error('MercadoPagoApiService: Erro ao consultar status do pagamento', [
                'payment_id' => $paymentId,
                'response' => $paymentStatus,
            ]);
            return ['success' => false, 'error' => 'Erro ao consultar pagamento'];
        }

        $payment = $paymentStatus['payment'];
        $externalReference = $payment['external_reference'] ?? null;
        $metadata = $payment['metadata'] ?? [];
        
        Log::info('MercadoPagoApiService: Dados do pagamento consultado', [
            'payment_id' => $paymentId,
            'external_reference' => $externalReference,
            'status' => $payment['status'] ?? null,
            'payment_method_id' => $payment['payment_method_id'] ?? null,
            'metadata' => $metadata,
        ]);

        // Tentar encontrar o pedido de diferentes formas
        $order = null;
        
        // Formato 1: metadata.order_id (mais confiável)
        if (isset($metadata['order_id'])) {
            $order = Order::find($metadata['order_id']);
            Log::info('MercadoPagoApiService: Tentando buscar pedido via metadata.order_id', [
                'order_id' => $metadata['order_id'],
                'found' => $order !== null,
            ]);
        }
        
        // Formato 2: metadata.order_number
        if (!$order && isset($metadata['order_number'])) {
            $order = Order::where('order_number', $metadata['order_number'])->first();
            Log::info('MercadoPagoApiService: Tentando buscar pedido via metadata.order_number', [
                'order_number' => $metadata['order_number'],
                'found' => $order !== null,
            ]);
        }
        
        // Formato 3: customerId/orderId (external_reference)
        if (!$order && $externalReference) {
            $referenceParts = explode('/', $externalReference);
            if (count($referenceParts) === 2) {
                $customerId = $referenceParts[0];
                $orderId = $referenceParts[1];
                $order = Order::where('id', $orderId)
                    ->where('customer_id', $customerId)
                    ->first();
                Log::info('MercadoPagoApiService: Tentando buscar pedido via external_reference (customerId/orderId)', [
                    'customer_id' => $customerId,
                    'order_id' => $orderId,
                    'found' => $order !== null,
                ]);
            }
        }
        
        // Formato 4: order_number direto (external_reference)
        if (!$order && $externalReference) {
            $order = Order::where('order_number', $externalReference)->first();
            Log::info('MercadoPagoApiService: Tentando buscar pedido via external_reference (order_number)', [
                'external_reference' => $externalReference,
                'found' => $order !== null,
            ]);
        }
        
        // Formato 5: apenas order_id numérico (external_reference)
        if (!$order && $externalReference && is_numeric($externalReference)) {
            $order = Order::find($externalReference);
            Log::info('MercadoPagoApiService: Tentando buscar pedido via external_reference (numeric)', [
                'external_reference' => $externalReference,
                'found' => $order !== null,
            ]);
        }

        if (!$order) {
            Log::error('MercadoPagoApiService: Pedido não encontrado no webhook', [
                'external_reference' => $externalReference,
                'metadata' => $metadata,
                'payment_id' => $paymentId,
                'payment_status' => $payment['status'] ?? null,
                'payment_method' => $payment['payment_method_id'] ?? null,
            ]);
            return ['success' => false, 'error' => 'Pedido não encontrado'];
        }

        // Verificar se o status já foi atualizado (idempotência)
        // IMPORTANTE: Verificar também se já foi notificado para evitar duplicatas
        $mappedPaymentStatus = self::mapPaymentStatus($payment['status'] ?? 'pending');
        $alreadyProcessed = ($order->payment_status === $mappedPaymentStatus && $order->payment_id === $paymentId);
        
        if ($alreadyProcessed) {
            // Se já foi notificado, não processar novamente mesmo se outros campos mudaram
            if (!empty($order->notified_paid_at)) {
                Log::info('MercadoPagoApiService: Webhook já processado e notificado (idempotência completa)', [
                    'order_id' => $order->id,
                    'payment_status' => $payment['status'],
                    'notified_paid_at' => $order->notified_paid_at,
                ]);
                return [
                    'success' => true,
                    'order_id' => $order->id,
                    'payment_status' => $payment['status'],
                    'order_status' => $order->status,
                    'already_processed' => true,
                    'already_notified' => true,
                ];
            }
            
            // Verificar se o pedido foi confirmado e se precisa enviar notificação
            $approvedStatuses = ['approved', 'paid', 'authorized'];
            if (in_array($payment['status'], $approvedStatuses) && $order->status !== 'paid' && $order->status !== 'confirmed') {
                // Payment_status atualizado mas order.status não, processar notificação
                Log::info('MercadoPagoApiService: Payment status já atualizado, mas order.status não - processando notificação', [
                    'order_id' => $order->id,
                    'payment_status' => $payment['status'],
                    'order_status' => $order->status,
                ]);
                $alreadyProcessed = false; // Processar para atualizar order.status e enviar notificação
            } else {
                Log::info('MercadoPagoApiService: Webhook já processado (idempotência)', [
                    'order_id' => $order->id,
                    'payment_status' => $payment['status'],
                ]);
                return [
                    'success' => true,
                    'order_id' => $order->id,
                    'payment_status' => $payment['status'],
                    'order_status' => $order->status,
                    'already_processed' => true,
                ];
            }
        }

        // Atualizar status do pagamento
        // Usar o status mapeado já calculado acima
        $order->update([
            'payment_id' => (string)$paymentId,
            'payment_status' => $mappedPaymentStatus,
            'payment_raw_response' => json_encode($payment),
        ]);

        // Atualizar status do pedido baseado no pagamento usando OrderStatusService
        // Para PIX, o status pode ser 'approved' quando aprovado
        // Também tratar 'pending' como pagamento em processamento (PIX pode ter delay)
        $approvedStatuses = ['approved', 'paid', 'authorized'];
        
        Log::info('MercadoPagoApiService: Processando atualização de status', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'current_payment_status' => $order->payment_status,
            'new_payment_status' => $payment['status'],
            'payment_method' => $payment['payment_method_id'] ?? null,
            'is_approved' => in_array($payment['status'], $approvedStatuses),
        ]);
        
        if (in_array($payment['status'], $approvedStatuses)) {
            try {
                $orderStatusService = app(OrderStatusService::class);
                $orderStatusService->changeStatus(
                    $order, 
                    'paid', 
                    'Pagamento aprovado via webhook Mercado Pago',
                    null, // userId
                    false // skipHistory
                );
                
                // Registrar uso do cupom se houver
                if ($order->coupon_code && $order->customer_id) {
                    try {
                        $coupon = \App\Models\Coupon::where('code', $order->coupon_code)->first();
                        if ($coupon) {
                            \App\Models\CouponUsage::firstOrCreate([
                                'coupon_id' => $coupon->id,
                                'customer_id' => $order->customer_id,
                                'order_id' => $order->id,
                            ], [
                                'used_at' => now(),
                            ]);
                        }
                    } catch (\Exception $e) {
                        Log::warning('MercadoPagoApiService: Erro ao registrar uso de cupom', [
                            'order_id' => $order->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
                
                Log::info('MercadoPagoApiService: Pagamento aprovado e pedido confirmado', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'payment_status' => $payment['status'],
                ]);
            } catch (\Exception $e) {
                Log::error('MercadoPagoApiService: Erro ao atualizar status do pedido', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                // Continuar mesmo se houver erro no OrderStatusService
                $this->updateOrderStatus($order, $payment['status']);
            }
        } else {
            // Para outros status (pending, rejected, etc), usar método simples
            $this->updateOrderStatus($order, $payment['status']);
            
            Log::info('MercadoPagoApiService: Status do pagamento atualizado (não aprovado)', [
                'order_id' => $order->id,
                'payment_status' => $payment['status'],
                'payment_method' => $payment['payment_method_id'] ?? null,
            ]);
        }

        // Recarregar o pedido para ter os dados atualizados
        $order->refresh();

        Log::info('MercadoPagoApiService: Webhook processado com sucesso', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'payment_status' => $order->payment_status,
            'order_status' => $order->status,
            'payment_method' => $payment['payment_method_id'] ?? null,
        ]);

        return [
            'success' => true,
            'order_id' => $order->id,
            'payment_status' => $payment['status'],
            'order_status' => $order->status,
        ];
    }

    /**
     * Gera referência externa no formato customerId/orderId
     */
    private function generateExternalReference(Order $order): string
    {
        return $order->customer_id . '/' . $order->id;
    }

    /**
     * Obtém valor para teste (1-10 centavos)
     */
    private function getTestAmount(float $originalAmount): float
    {
        if (!PaymentSetting::isTestModeEnabled()) {
            return $originalAmount;
        }

        // Gerar valor aleatório entre 0.01 e 0.10
        return round(mt_rand(1, 10) / 100, 2);
    }

    /**
     * Constrói itens do pedido
     */
    private function buildItems(Order $order): array
    {
        $items = [];
        
        foreach ($order->items as $item) {
            $items[] = [
                'id' => $item->product_id,
                'title' => $item->product_name,
                'description' => "Quantidade: {$item->quantity}",
                'quantity' => $item->quantity,
                'unit_price' => $this->getTestAmount($item->price),
                'currency_id' => 'BRL',
            ];
        }

        // Adicionar taxa de entrega se houver
        if ($order->delivery_fee > 0) {
            $items[] = [
                'id' => 'delivery_fee',
                'title' => 'Taxa de Entrega',
                'description' => 'Taxa de entrega do pedido',
                'quantity' => 1,
                'unit_price' => $this->getTestAmount($order->delivery_fee),
                'currency_id' => 'BRL',
            ];
        }

        return $items;
    }

    /**
     * Constrói dados do pagador
     */
    private function buildPayer(Customer $customer): array
    {
        return [
            'name' => $customer->name,
            'email' => $customer->email,
            'phone' => [
                'number' => $customer->phone,
            ],
        ];
    }

    /**
     * Atualiza status do pedido baseado no pagamento (método simples, sem notificações)
     */
    private function updateOrderStatus(Order $order, string $paymentStatus): void
    {
        $statusMap = [
            'approved' => 'confirmed',
            'paid' => 'confirmed',
            'pending' => 'pending',
            'rejected' => 'cancelled',
            'cancelled' => 'cancelled',
            'refunded' => 'cancelled',
        ];

        $newStatus = $statusMap[$paymentStatus] ?? 'pending';
        
        // Só atualizar se for diferente
        if ($order->status !== $newStatus) {
            $order->update(['status' => $newStatus]);
        }
    }

    /**
     * Estorna/reembolsa um pagamento no Mercado Pago
     * 
     * @param string $paymentId ID do pagamento no Mercado Pago
     * @param float|null $amount Valor a reembolsar (null para reembolso integral)
     * @return array
     */
    public function refundPayment(string $paymentId, ?float $amount = null): array
    {
        try {
            $payload = [];
            
            // Se informado valor, fazer reembolso parcial
            if ($amount !== null && $amount > 0) {
                $payload['amount'] = $amount;
            }
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/v1/payments/{$paymentId}/refunds", $payload);

            if ($response->successful()) {
                $data = $response->json();
                
                Log::info('Estorno realizado no Mercado Pago', [
                    'payment_id' => $paymentId,
                    'amount' => $amount,
                    'response' => $data,
                ]);

                return [
                    'success' => true,
                    'refund_id' => $data['id'] ?? null,
                    'status' => $data['status'] ?? null,
                    'amount' => $data['amount'] ?? $amount,
                    'response' => $data,
                ];
            }

            Log::error('Erro ao estornar pagamento no Mercado Pago', [
                'payment_id' => $paymentId,
                'amount' => $amount,
                'status' => $response->status(),
                'response' => $response->json(),
            ]);

            return [
                'success' => false,
                'error' => 'Erro ao estornar pagamento no Mercado Pago',
                'details' => $response->json(),
                'status_code' => $response->status(),
            ];
        } catch (\Exception $e) {
            Log::error('Exceção ao estornar pagamento no Mercado Pago', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => 'Erro ao processar estorno: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Obtém configurações públicas para o frontend
     */
    public function getPublicConfig(): array
    {
        return [
            'public_key' => $this->publicKey,
            'environment' => $this->environment,
            'test_mode' => PaymentSetting::isTestModeEnabled(),
        ];
    }
}
