<?php

namespace App\Services;

use App\Models\PaymentSetting;
use App\Models\Order;
use App\Models\Customer;
use App\Services\OrderStatusService;
use App\Services\BotConversaService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MercadoPagoApiService
{
    protected $accessToken;
    protected $publicKey;
    protected $environment;
    protected $baseUrl;
    private const REVIEW_STATUSES = ['in_process', 'pending'];
    private const REVIEW_DETAILS = [
        'pending_review_manual',
        'pending_contingency',
        'pending_challenge',
        'pending_waiting_transfer',
        'pending_user_confirmation',
        'pending_manual_verification',
    ];

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
            $mappedStatus = self::mapPaymentStatus($data['status'] ?? 'pending');
            $jsonFlags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;

            // Atualizar pedido com dados do pagamento
            $order->update([
                'payment_provider' => 'mercadopago',
                'payment_id' => $data['id'],
                'payment_status' => $mappedStatus,
                'payment_raw_response' => json_encode($data, $jsonFlags),
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
     * Busca o pagamento mais recente utilizando a referência externa
     */
    public function searchPaymentByExternalReference(string $externalReference): array
    {
        $queryParams = [
            'external_reference' => $externalReference,
            'sort' => 'date_created',
            'criteria' => 'desc',
            'limit' => 1,
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->accessToken,
            'Content-Type' => 'application/json',
        ])->get("{$this->baseUrl}/v1/payments/search", $queryParams);

        if ($response->successful()) {
            $json = $response->json();
            $results = $json['results'] ?? [];

            if (!empty($results)) {
                $payment = $results[0];

                // Alguns ambientes retornam em results[n]['collection']
                if (isset($payment['collection']) && is_array($payment['collection'])) {
                    $payment = $payment['collection'];
                }

                if (isset($payment['id'])) {
                    Log::info('MercadoPagoApiService: Pagamento localizado via external_reference', [
                        'external_reference' => $externalReference,
                        'payment_id' => $payment['id'],
                        'status' => $payment['status'] ?? null,
                    ]);

                    return [
                        'success' => true,
                        'payment' => $payment,
                    ];
                }
            }

            Log::warning('MercadoPagoApiService: Nenhum pagamento encontrado via external_reference', [
                'external_reference' => $externalReference,
                'results_count' => is_countable($results) ? count($results) : 0,
            ]);
            return [
                'success' => false,
                'error' => 'Nenhum pagamento encontrado para a referência informada.',
            ];
        }

        Log::error('MercadoPagoApiService: Erro ao buscar pagamento por external_reference', [
            'external_reference' => $externalReference,
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        return [
            'success' => false,
            'error' => 'Erro ao consultar pagamentos no Mercado Pago.',
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

    private function logWebhook(string $level, string $message, array $context, ?array $fingerprintParts = null, int $ttlSeconds = 300): void
    {
        if (empty($fingerprintParts) || $ttlSeconds <= 0) {
            Log::{$level}($message, $context);
            return;
        }

        $normalized = array_map(function ($part) {
            if (is_null($part)) {
                return 'null';
            }

            if ($part instanceof \Stringable) {
                return (string) $part;
            }

            if (is_bool($part)) {
                return $part ? 'true' : 'false';
            }

            if (is_scalar($part)) {
                return (string) $part;
            }

            return md5(json_encode($part));
        }, $fingerprintParts);

        $fingerprint = $message . '|' . implode('|', $normalized);
        $cacheKey = 'mercadopago:webhook_log:' . sha1($fingerprint);
        $existing = Cache::get($cacheKey);

        if (!$existing) {
            Cache::put($cacheKey, ['count' => 1], now()->addSeconds($ttlSeconds));
            Log::{$level}($message, $context);
            return;
        }

        $existing['count'] = ($existing['count'] ?? 1) + 1;
        Cache::put($cacheKey, $existing, now()->addSeconds($ttlSeconds));
    }

    /**
     * Processa webhook do MercadoPago
     */
    public function processWebhook(array $data, bool $skipNotifications = false): array
    {
        $topic = $data['topic'] ?? $data['type'] ?? null;
        $rawIdentifier = $data['data']['id'] ?? $data['data_id'] ?? $data['resource'] ?? $data['id'] ?? null;

        $this->logWebhook('info', 'MercadoPagoApiService: Webhook recebido', [
            'data_keys' => array_keys($data),
            'data_id' => $data['data']['id'] ?? null,
            'data_id_alt' => $data['data_id'] ?? null,
            'resource' => $data['resource'] ?? null,
            'id' => $data['id'] ?? null,
            'type' => $data['type'] ?? null,
            'action' => $data['action'] ?? null,
            'topic' => $topic,
        ], [
            'webhook_received',
            $topic ?? 'unknown',
            $rawIdentifier ?? 'unknown',
        ], 180);

        // Extrair payment_id usando função helper que suporta todos os formatos
        $paymentId = self::extractPaymentId($data);
        
        if (!$paymentId) {
            // Se for merchant_order, informar que foi ignorado
            if ($topic === 'merchant_order' || (isset($data['data']['topic']) && $data['data']['topic'] === 'merchant_order')) {
                $this->logWebhook('info', 'MercadoPagoApiService: Notificação de merchant_order ignorada', ['data' => $data], [
                    'merchant_order_ignored',
                    $rawIdentifier ?? 'unknown',
                ], 600);
                return [
                    'success' => false,
                    'error' => 'Notificação de merchant_order ignorada',
                    'notifications_skipped' => $skipNotifications,
                ];
            }
            
            $this->logWebhook('error', 'MercadoPagoApiService: ID do pagamento não encontrado no webhook', [
                'data' => $data,
                'data_keys' => array_keys($data),
                'topic' => $topic,
            ], [
                'missing_payment_id',
                $topic ?? 'unknown',
                $rawIdentifier ?? 'unknown',
            ], 300);
            return [
                'success' => false,
                'error' => 'ID do pagamento não encontrado',
                'notifications_skipped' => $skipNotifications,
            ];
        }

        $this->logWebhook('info', 'MercadoPagoApiService: Payment ID extraído', [
            'payment_id' => $paymentId,
            'topic' => $topic,
            'has_data' => isset($data['data']),
            'has_resource' => isset($data['resource']) || isset($data['data']['resource']),
        ], [
            'payment_id_extracted',
            $topic ?? 'unknown',
            $paymentId,
        ], 180);
        $paymentStatus = $this->getPaymentStatus($paymentId);

        if (!$paymentStatus['success']) {
            Log::error('MercadoPagoApiService: Erro ao consultar status do pagamento', [
                'payment_id' => $paymentId,
                'response' => $paymentStatus,
            ]);
            return [
                'success' => false,
                'error' => 'Erro ao consultar pagamento',
                'notifications_skipped' => $skipNotifications,
            ];
        }

        $payment = $paymentStatus['payment'];
        $externalReference = $payment['external_reference'] ?? null;
        $metadata = $payment['metadata'] ?? [];
        
        $this->logWebhook('info', 'MercadoPagoApiService: Dados do pagamento consultado', [
            'payment_id' => $paymentId,
            'external_reference' => $externalReference,
            'status' => $payment['status'] ?? null,
            'payment_method_id' => $payment['payment_method_id'] ?? null,
            'metadata' => $metadata,
        ], [
            'payment_data',
            $paymentId,
            $payment['status'] ?? null,
        ], 300);

        // Tentar encontrar o pedido de diferentes formas
        $order = null;
        
        // Formato 1: metadata.order_id (mais confiável)
        if (isset($metadata['order_id'])) {
            $order = Order::find($metadata['order_id']);
            $this->logWebhook('info', 'MercadoPagoApiService: Tentando buscar pedido via metadata.order_id', [
                'order_id' => $metadata['order_id'],
                'found' => $order !== null,
            ], [
                'lookup_metadata_order_id',
                $paymentId,
                $metadata['order_id'],
            ], 300);
        }
        
        // Formato 2: metadata.order_number
        if (!$order && isset($metadata['order_number'])) {
            $order = Order::where('order_number', $metadata['order_number'])->first();
            $this->logWebhook('info', 'MercadoPagoApiService: Tentando buscar pedido via metadata.order_number', [
                'order_number' => $metadata['order_number'],
                'found' => $order !== null,
            ], [
                'lookup_metadata_order_number',
                $paymentId,
                $metadata['order_number'],
            ], 300);
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
                $this->logWebhook('info', 'MercadoPagoApiService: Tentando buscar pedido via external_reference (customerId/orderId)', [
                    'customer_id' => $customerId,
                    'order_id' => $orderId,
                    'found' => $order !== null,
                ], [
                    'lookup_external_customer_order',
                    $paymentId,
                    $customerId,
                    $orderId,
                ], 300);
            }
        }
        
        // Formato 4: order_number direto (external_reference)
        if (!$order && $externalReference) {
            $order = Order::where('order_number', $externalReference)->first();
            $this->logWebhook('info', 'MercadoPagoApiService: Tentando buscar pedido via external_reference (order_number)', [
                'external_reference' => $externalReference,
                'found' => $order !== null,
            ], [
                'lookup_external_order_number',
                $paymentId,
                $externalReference,
            ], 300);
        }
        
        // Formato 5: apenas order_id numérico (external_reference)
        if (!$order && $externalReference && is_numeric($externalReference)) {
            $order = Order::find($externalReference);
            $this->logWebhook('info', 'MercadoPagoApiService: Tentando buscar pedido via external_reference (numeric)', [
                'external_reference' => $externalReference,
                'found' => $order !== null,
            ], [
                'lookup_external_numeric',
                $paymentId,
                $externalReference,
            ], 300);
        }

        if (!$order) {
            $this->logWebhook('error', 'MercadoPagoApiService: Pedido não encontrado no webhook', [
                'external_reference' => $externalReference,
                'metadata' => $metadata,
                'payment_id' => $paymentId,
                'payment_status' => $payment['status'] ?? null,
                'payment_method' => $payment['payment_method_id'] ?? null,
            ], [
                'order_not_found',
                $paymentId,
                $externalReference ?? 'null',
            ], 600);
            return [
                'success' => false,
                'error' => 'Pedido não encontrado',
                'notifications_skipped' => $skipNotifications,
            ];
        }

        // Verificar se o status já foi atualizado (idempotência)
        // IMPORTANTE: Verificar também se já foi notificado para evitar duplicatas
        $mappedPaymentStatus = self::mapPaymentStatus($payment['status'] ?? 'pending');
        $alreadyProcessed = ($order->payment_status === $mappedPaymentStatus && $order->payment_id === $paymentId);
        
        if ($alreadyProcessed) {
            // Se já foi notificado, não processar novamente mesmo se outros campos mudaram
            if (!empty($order->notified_paid_at)) {
                $this->logWebhook('info', 'MercadoPagoApiService: Webhook já processado e notificado (idempotência completa)', [
                    'order_id' => $order->id,
                    'payment_status' => $payment['status'],
                    'notified_paid_at' => $order->notified_paid_at,
                ], [
                    'webhook_processed_notified',
                    $paymentId,
                    $order->status,
                    $payment['status'] ?? null,
                ], 600);
                return [
                    'success' => true,
                    'order_id' => $order->id,
                    'payment_status' => $payment['status'],
                    'order_status' => $order->status,
                    'already_processed' => true,
                    'already_notified' => true,
                    'notifications_skipped' => $skipNotifications,
                ];
            }
            
            // Verificar se o pedido foi confirmado e se precisa enviar notificação
            $approvedStatuses = ['approved', 'paid', 'authorized'];
            if (in_array($payment['status'], $approvedStatuses) && $order->status !== 'paid' && $order->status !== 'confirmed') {
                // Payment_status atualizado mas order.status não, processar notificação
                $this->logWebhook('info', 'MercadoPagoApiService: Payment status já atualizado, mas order.status não - processando notificação', [
                    'order_id' => $order->id,
                    'payment_status' => $payment['status'],
                    'order_status' => $order->status,
                ], [
                    'webhook_status_pending_notification',
                    $paymentId,
                    $payment['status'] ?? null,
                    $order->status,
                ], 300);
                $alreadyProcessed = false; // Processar para atualizar order.status e enviar notificação
            } else {
                $this->logWebhook('info', 'MercadoPagoApiService: Webhook já processado (idempotência)', [
                    'order_id' => $order->id,
                    'payment_status' => $payment['status'],
                ], [
                    'webhook_processed',
                    $paymentId,
                    $payment['status'] ?? null,
                ], 600);
                return [
                    'success' => true,
                    'order_id' => $order->id,
                    'payment_status' => $payment['status'],
                    'order_status' => $order->status,
                    'already_processed' => true,
                    'notifications_skipped' => $skipNotifications,
                ];
            }
        }

        // Atualizar status do pagamento
        // Usar o status mapeado já calculado acima
        $jsonFlags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;

        $mpStatus = strtolower((string)($payment['status'] ?? ''));
        $mpStatusDetail = strtolower((string)($payment['status_detail'] ?? ''));
        $underReview = self::isPaymentUnderReviewState($mappedPaymentStatus, $mpStatus, $mpStatusDetail);

        $order->update([
            'payment_id' => (string)$paymentId,
            'payment_status' => $mappedPaymentStatus,
            'payment_raw_response' => json_encode($payment, $jsonFlags),
        ]);

        if ($underReview) {
            $this->notifyPaymentUnderReview($order, $payment);
        }

        // Atualizar status do pedido baseado no pagamento usando OrderStatusService
        // Para PIX, o status pode ser 'approved' quando aprovado
        // Também tratar 'pending' como pagamento em processamento (PIX pode ter delay)
        $approvedStatuses = ['approved', 'paid', 'authorized'];
        
        $this->logWebhook('info', 'MercadoPagoApiService: Processando atualização de status', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'current_payment_status' => $order->payment_status,
            'new_payment_status' => $payment['status'],
            'payment_method' => $payment['payment_method_id'] ?? null,
            'is_approved' => in_array($payment['status'], $approvedStatuses),
        ], [
            'process_status',
            $paymentId,
            $payment['status'] ?? null,
            $order->payment_status,
        ], 300);
        
        if (in_array($payment['status'], $approvedStatuses)) {
            try {
                $orderStatusService = app(OrderStatusService::class);
                $orderStatusService->changeStatus(
                    $order, 
                    'paid', 
                    'Pagamento aprovado via webhook Mercado Pago',
                    null, // userId
                    false, // skipHistory
                    $skipNotifications
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
                
                $this->logWebhook('info', 'MercadoPagoApiService: Pagamento aprovado e pedido confirmado', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'payment_status' => $payment['status'],
                ], [
                    'payment_approved',
                    $paymentId,
                    $payment['status'] ?? null,
                ], 600);
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
            
            $this->logWebhook('info', 'MercadoPagoApiService: Status do pagamento atualizado (não aprovado)', [
                'order_id' => $order->id,
                'payment_status' => $payment['status'],
                'payment_method' => $payment['payment_method_id'] ?? null,
            ], [
                'payment_status_update',
                $paymentId,
                $payment['status'] ?? null,
            ], 300);
        }

        // Recarregar o pedido para ter os dados atualizados
        $order->refresh();

        $this->logWebhook('info', 'MercadoPagoApiService: Webhook processado com sucesso', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'payment_status' => $order->payment_status,
            'order_status' => $order->status,
            'payment_method' => $payment['payment_method_id'] ?? null,
        ], [
            'webhook_processed_success',
            $paymentId,
            $order->payment_status,
            $order->status,
        ], 300);

        return [
            'success' => true,
            'order_id' => $order->id,
            'payment_status' => $payment['status'],
            'order_status' => $order->status,
            'notifications_skipped' => $skipNotifications,
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
     * Verifica se o pagamento está em análise pelo Mercado Pago
     */
    public static function isPaymentUnderReviewState(string $mappedPaymentStatus, ?string $mpStatus, ?string $mpStatusDetail): bool
    {
        $mappedPaymentStatus = strtolower($mappedPaymentStatus);
        $mpStatus = strtolower((string)$mpStatus);
        $mpStatusDetail = strtolower((string)$mpStatusDetail);

        if ($mappedPaymentStatus !== 'pending') {
            return false;
        }

        if (!in_array($mpStatus, self::REVIEW_STATUSES, true)) {
            return false;
        }

        if ($mpStatusDetail === '') {
            return true;
        }

        return in_array($mpStatusDetail, self::REVIEW_DETAILS, true);
    }

    /**
     * Notifica o cliente sobre pagamento em análise (apenas uma vez)
     */
    private function notifyPaymentUnderReview(Order $order, array $payment): void
    {
        if (!empty($order->payment_review_notified_at)) {
            return;
        }

        try {
            $order->loadMissing('customer');
        } catch (\Throwable $e) {
            Log::warning('MercadoPagoApiService: Falha ao carregar cliente para notificação de análise', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }

        $customer = $order->customer;
        $sent = false;

        if ($customer && $customer->phone) {
            try {
                /** @var BotConversaService $bot */
                $bot = app(BotConversaService::class);

                $customerName = trim($customer->name ?? '');
                $firstName = $customerName !== '' ? explode(' ', $customerName)[0] : 'cliente';
                $message = "Olá, {$firstName}! Recebemos o pagamento do pedido #{$order->order_number}, mas o Mercado Pago colocou a transação em análise de segurança. Esse processo é normal e pode levar alguns minutos. Vamos acompanhar e avisaremos você assim que houver novidades. Qualquer dúvida, estamos à disposição!";

                if ($bot->isConfigured()) {
                    $sent = $bot->sendTextMessage($customer->phone, $message);
                } else {
                    Log::warning('MercadoPagoApiService: BotConversa não configurado para notificar análise', [
                        'order_id' => $order->id,
                    ]);
                }
            } catch (\Throwable $e) {
                Log::error('MercadoPagoApiService: Erro ao enviar mensagem de análise via BotConversa', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);
            }
        } else {
            Log::warning('MercadoPagoApiService: Pedido sem cliente ou telefone para notificar análise', [
                'order_id' => $order->id,
            ]);
        }

        $order->forceFill([
            'payment_review_notified_at' => now(),
        ])->save();

        Log::info('MercadoPagoApiService: Pagamento em análise notificado', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'notification_sent' => $sent,
            'customer_id' => $customer->id ?? null,
            'payment_status_detail' => $payment['status_detail'] ?? null,
        ]);
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
