<?php

namespace App\Listeners;

use App\Events\OrderStatusUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SendOrderWhatsAppNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;
    public int $backoff = 15;

    /**
     * Handle the event.
     */
    public function handle(OrderStatusUpdated $event): void
    {
        $webhookUrl = config('notifications.wa_webhook_url');

        if (empty($webhookUrl)) {
            Log::debug('WhatsApp webhook URL não configurado, ignorando disparo.', [
                'event' => $event->event,
                'order_id' => $event->order->id,
            ]);

            return;
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
        ];

        if ($token = config('notifications.wa_token')) {
            $headers['X-Webhook-Token'] = $token;
        }

        try {
            $response = Http::timeout((int) config('notifications.wa_timeout', 10))
                ->asJson()
                ->withHeaders($headers)
                ->post($webhookUrl, $payload);

            if ($response->failed()) {
                Log::warning('WhatsApp webhook retorno de erro.', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'order_id' => $order->id,
                ]);

                $response->throw();
            }

            Log::info('WhatsApp webhook enviado com sucesso.', [
                'order_id' => $order->id,
                'event' => $event->event,
            ]);
        } catch (\Throwable $e) {
            Log::error('Falha ao enviar payload WhatsApp webhook.', [
                'order_id' => $order->id,
                'event' => $event->event,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
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

