<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MercadoPagoService
{
    protected $accessToken;
    protected $publicKey;
    protected $environment;
    protected $baseUrl;

    public function __construct()
    {
        $settings = Setting::getSettings();

        $this->accessToken = $settings->mercadopago_access_token;
        $this->publicKey = $settings->mercadopago_public_key;
        $this->environment = $settings->mercadopago_env;

        $this->baseUrl = $this->environment === 'production'
            ? 'https://api.mercadopago.com'
            : 'https://api.mercadopago.com';
    }

    /**
     * Cria pagamento PIX
     */
    public function createPixPayment(Order $order)
    {
        try {
            $preference = [
                'items' => [
                    [
                        'title' => "Pedido #{$order->order_number}",
                        'description' => "Pedido de {$order->customer->name}",
                        'quantity' => 1,
                        'unit_price' => (float) $order->final_amount,
                        'currency_id' => 'BRL',
                    ]
                ],
                'payer' => [
                    'name' => $order->customer->name,
                    'email' => $order->customer->email,
                    'phone' => [
                        'number' => $order->customer->phone,
                    ],
                ],
                'payment_methods' => [
                    'excluded_payment_methods' => [],
                    'excluded_payment_types' => [],
                    'installments' => 1,
                ],
                'notification_url' => route('webhooks.mercadopago'),
                'external_reference' => $order->order_number,
                'auto_return' => 'approved',
                'back_urls' => [
                    'success' => route('order.success', $order->id),
                    'failure' => route('checkout.index'),
                    'pending' => route('order.success', $order->id),
                ],
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/checkout/preferences', $preference);

            if ($response->successful()) {
                $data = $response->json();

                $order->update([
                    'preference_id' => $data['id'],
                    'payment_link' => $data['init_point'],
                    'payment_raw_response' => $data,
                ]);

                return $data;
            }

            throw new \Exception('Erro ao criar preferência: ' . $response->body());

        } catch (\Exception $e) {
            Log::error('Erro MercadoPago PIX: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Cria pagamento com cartão
     */
    public function createCardPayment(Order $order)
    {
        try {
            $preference = [
                'items' => [
                    [
                        'title' => "Pedido #{$order->order_number}",
                        'description' => "Pedido de {$order->customer->name}",
                        'quantity' => 1,
                        'unit_price' => (float) $order->final_amount,
                        'currency_id' => 'BRL',
                    ]
                ],
                'payer' => [
                    'name' => $order->customer->name,
                    'email' => $order->customer->email,
                    'phone' => [
                        'number' => $order->customer->phone,
                    ],
                ],
                'payment_methods' => [
                    'excluded_payment_methods' => [
                        ['id' => 'pix'],
                    ],
                    'excluded_payment_types' => [],
                    'installments' => 12,
                ],
                'notification_url' => route('webhooks.mercadopago'),
                'external_reference' => $order->order_number,
                'auto_return' => 'approved',
                'back_urls' => [
                    'success' => route('order.success', $order->id),
                    'failure' => route('checkout.index'),
                    'pending' => route('order.success', $order->id),
                ],
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/checkout/preferences', $preference);

            if ($response->successful()) {
                $data = $response->json();

                $order->update([
                    'preference_id' => $data['id'],
                    'payment_link' => $data['init_point'],
                    'payment_raw_response' => $data,
                ]);

                return $data;
            }

            throw new \Exception('Erro ao criar preferência: ' . $response->body());

        } catch (\Exception $e) {
            Log::error('Erro MercadoPago Cartão: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Processa webhook do MercadoPago
     */
    public function processWebhook(array $data)
    {
        try {
            $externalReference = $data['external_reference'] ?? null;
            $status = $data['status'] ?? null;

            if (!$externalReference) {
                return false;
            }

            $order = Order::where('order_number', $externalReference)->first();

            if (!$order) {
                return false;
            }

            $paymentStatus = $this->mapStatus($status);

            // Buscar detalhes completos do pagamento para obter taxas e valor líquido
            $paymentDetails = $data;
            if (isset($data['id'])) {
                $fetchedPayment = $this->getPayment($data['id']);
                if ($fetchedPayment) {
                    $paymentDetails = $fetchedPayment;
                }
            }

            $order->update([
                'payment_status' => $paymentStatus,
                'payment_id' => $data['id'] ?? null,
                'payment_raw_response' => $paymentDetails,
            ]);

            // Se aprovado, confirma o pedido
            if ($paymentStatus === 'paid') {
                $order->update(['status' => 'confirmed']);
            }

            return true;

        } catch (\Exception $e) {
            Log::error('Erro processando webhook MercadoPago: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Mapeia status do MercadoPago
     */
    private function mapStatus(string $status): string
    {
        $statusMap = [
            'pending' => 'pending',
            'approved' => 'paid',
            'authorized' => 'paid',
            'in_process' => 'pending',
            'in_mediation' => 'pending',
            'rejected' => 'failed',
            'cancelled' => 'failed',
            'refunded' => 'refunded',
            'charged_back' => 'refunded',
        ];

        return $statusMap[$status] ?? 'pending';
    }

    /**
     * Obtém dados de um pagamento
     */
    public function getPayment(string $paymentId)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
            ])->get($this->baseUrl . "/v1/payments/{$paymentId}");

            if ($response->successful()) {
                return $response->json();
            }

            return null;

        } catch (\Exception $e) {
            Log::error('Erro ao buscar pagamento: ' . $e->getMessage());
            return null;
        }
    }
}
