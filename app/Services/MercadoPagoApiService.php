<?php

namespace App\Services;

use App\Models\PaymentSetting;
use App\Models\Order;
use App\Models\Customer;
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
            'notification_url' => route('api.webhooks.mercadopago'),
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
     * Processa webhook do MercadoPago
     */
    public function processWebhook(array $data): array
    {
        if (!isset($data['data']['id'])) {
            return ['success' => false, 'error' => 'ID do pagamento não encontrado'];
        }

        $paymentId = $data['data']['id'];
        $paymentStatus = $this->getPaymentStatus($paymentId);

        if (!$paymentStatus['success']) {
            return ['success' => false, 'error' => 'Erro ao consultar pagamento'];
        }

        $payment = $paymentStatus['payment'];
        $externalReference = $payment['external_reference'] ?? null;

        if (!$externalReference) {
            return ['success' => false, 'error' => 'Referência externa não encontrada'];
        }

        // Quebrar referência externa: customerId/orderId
        $referenceParts = explode('/', $externalReference);
        if (count($referenceParts) !== 2) {
            return ['success' => false, 'error' => 'Formato de referência inválido'];
        }

        $customerId = $referenceParts[0];
        $orderId = $referenceParts[1];

        // Buscar pedido
        $order = Order::where('id', $orderId)
            ->where('customer_id', $customerId)
            ->first();

        if (!$order) {
            return ['success' => false, 'error' => 'Pedido não encontrado'];
        }

        // Atualizar status do pagamento
        $order->update([
            'payment_id' => $paymentId,
            'payment_status' => $payment['status'],
            'payment_raw_response' => $payment,
        ]);

        // Atualizar status do pedido baseado no pagamento
        $this->updateOrderStatus($order, $payment['status']);

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
     * Atualiza status do pedido baseado no pagamento
     */
    private function updateOrderStatus(Order $order, string $paymentStatus): void
    {
        $statusMap = [
            'approved' => 'confirmed',
            'pending' => 'pending',
            'rejected' => 'cancelled',
            'cancelled' => 'cancelled',
            'refunded' => 'cancelled',
        ];

        $newStatus = $statusMap[$paymentStatus] ?? 'pending';
        $order->update(['status' => $newStatus]);
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
