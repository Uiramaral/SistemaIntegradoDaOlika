<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EvolutionApiService
{
    private ?string $baseUrl;
    private ?string $token;
    private bool $enabled;

    public function __construct()
    {
        // Preferir payment_settings; fallback env
        $this->baseUrl = $this->getSetting('evolution_base_url') ?? env('EVOLUTION_BASE_URL');
        $this->token   = $this->getSetting('evolution_token') ?? env('EVOLUTION_TOKEN');
        $enabledValue  = $this->getSetting('evolution_enabled');
        $this->enabled = filter_var($enabledValue ?? env('EVOLUTION_ENABLED', false), FILTER_VALIDATE_BOOLEAN);
    }

    public function isConfigured(): bool
    {
        return $this->enabled && !empty($this->baseUrl) && !empty($this->token);
    }

    /**
     * Envia mensagem de texto simples para um número (E.164 sem sinais)
     */
    public function sendText(string $numberE164, string $message): bool
    {
        if (!$this->isConfigured()) {
            Log::debug('EvolutionApiService: não configurado');
            return false;
        }

        try {
            $url = rtrim($this->baseUrl, '/').'/message/sendText';
            $res = Http::withToken($this->token)
                ->asJson()
                ->post($url, [
                    'number' => $numberE164,
                    'text'   => $message,
                ]);

            Log::info('EvolutionApiService:sendText', [
                'status' => $res->status(),
                'ok' => $res->successful(),
            ]);

            return $res->successful();
        } catch (\Throwable $e) {
            Log::warning('EvolutionApiService: erro ao enviar', ['err' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Monta mensagem de pedido pago (reuso da formatação do BotConversa)
     */
    public function buildPaidMessage(\App\Models\Order $order): string
    {
        $order->loadMissing('items.product','customer','address');

        $lines = [];
        $lines[] = '✅ Pagamento confirmado!';
        $lines[] = 'Pedido '.$order->order_number;
        $lines[] = '';
        foreach ($order->items as $it) {
            $name = $it->custom_name ?: optional($it->product)->name ?: 'Item';
            $lines[] = sprintf('%dx %s - R$ %s', (int)$it->quantity, $name, number_format($it->total_price, 2, ',', '.'));
        }
        $lines[] = '';
        if ($order->discount_amount > 0) {
            $lines[] = 'Descontos: - R$ '.number_format($order->discount_amount, 2, ',', '.');
        }
        if ($order->delivery_fee > 0) {
            $lines[] = 'Entrega: R$ '.number_format($order->delivery_fee, 2, ',', '.');
        }
        if ($order->cashback_used > 0) {
            $lines[] = 'Cashback usado: - R$ '.number_format($order->cashback_used, 2, ',', '.');
        }
        $lines[] = 'TOTAL: R$ '.number_format($order->final_amount ?? $order->total_amount, 2, ',', '.');
        if ($order->scheduled_delivery_at) {
            $lines[] = 'Entrega agendada: '.$order->scheduled_delivery_at->format('d/m/Y H:i');
        }
        $lines[] = '';
        // Link de acompanhamento (mesma URL pública já usada)
        try {
            $phone = optional($order->customer)->phone;
            $trackUrl = route('customer.orders.show', ['order' => $order->order_number, 'phone' => preg_replace('/\D/','',$phone)]);
            $lines[] = 'Acompanhe seu pedido: '.$trackUrl;
        } catch (\Throwable $e) { /* ignore */ }

        return implode("\n", $lines);
    }

    /**
     * Envia mensagem de pedido pago para o cliente
     */
    public function sendPaidOrder(\App\Models\Order $order): bool
    {
        if (!$this->isConfigured()) return false;
        $phone = optional($order->customer)->phone;
        if (!$phone) return false;
        $numberE164 = '55'.preg_replace('/\D/','', $phone); // Brasil
        $msg = $this->buildPaidMessage($order);
        return $this->sendText($numberE164, $msg);
    }

    private function getSetting(string $key): ?string
    {
        try {
            $val = DB::table('payment_settings')->where('key', $key)->value('value');
            return $val !== '' ? $val : null;
        } catch (\Throwable $e) {
            return null;
        }
    }
}


