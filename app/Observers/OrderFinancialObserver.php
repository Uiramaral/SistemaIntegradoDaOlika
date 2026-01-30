<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\FinancialTransaction;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class OrderFinancialObserver
{
    public function created(Order $order): void
    {
        $this->maybeRegisterRevenue($order);
    }

    /**
     * Quando o pedido é pago (payment_status = paid/approved), registrar receita em Finanças.
     */
    public function updated(Order $order): void
    {
        // Se o pedido foi cancelado, remover transação financeira
        if ($order->wasChanged('status') && in_array($order->status, ['cancelled', 'canceled'])) {
            FinancialTransaction::where('order_id', $order->id)->where('type', 'revenue')->delete();
            return;
        }

        // Se o status do pagamento mudou para algo que não seja pago/aprovado, remover a receita (caso existia)
        $paymentStatus = strtolower((string) ($order->payment_status ?? ''));
        if ($order->wasChanged('payment_status') && !in_array($paymentStatus, ['paid', 'approved'])) {
            FinancialTransaction::where('order_id', $order->id)->where('type', 'revenue')->delete();
        }

        // Se mudou status do pagamento ou status do pedido
        if (!$order->wasChanged('payment_status') && !$order->wasChanged('status')) {
            return;
        }

        $this->maybeRegisterRevenue($order);
    }

    private function maybeRegisterRevenue(Order $order): void
    {
        // Verificar status do pagamento
        $paymentStatus = strtolower((string) ($order->payment_status ?? ''));
        $isPaid = in_array($paymentStatus, ['paid', 'approved'], true);

        // Se não for pago, ignorar (Fiado só entra quando for baixado/pago)
        if (!$isPaid) {
            return;
        }

        // 1. Validar que temos um client_id válido
        $clientId = $order->client_id;
        $clientIdValid = false;

        if ($clientId && is_numeric($clientId)) {
            $clientIdInt = (int) $clientId;
            if ($clientIdInt > 0) {
                $clientId = $clientIdInt;
                $clientIdValid = true;
            }
        }

        // 2. Robustez: Se client_id inválido/nulo, tentar resolver de outras fontes
        if (!$clientIdValid) {
            // Tentar usuario logado (admin/pdv)
            if (auth()->check() && auth()->user()->client_id) {
                $clientId = auth()->user()->client_id;
                $clientIdValid = true;
            }
            // Tentar via helper multi-tenant (sessão, headers)
            elseif (class_exists(\App\Models\Traits\BelongsToClient::class) && ($cid = \App\Models\Traits\BelongsToClient::getCurrentClientId())) {
                $clientId = $cid;
                $clientIdValid = true;
            }
            // Tentar via customer do pedido chumbado (caso de webhook)
            elseif ($order->customer && !empty($order->customer->client_id)) {
                $clientId = $order->customer->client_id;
                $clientIdValid = true;
            }
        }

        if (!$clientIdValid) {
            Log::warning('OrderFinancialObserver: client_id nulo e impossível de resolver. Receita ignorada.', [
                'order_id' => $order->id,
            ]);
            return;
        }

        if (!Schema::hasTable('financial_transactions') || !Schema::hasColumn('financial_transactions', 'order_id')) {
            return;
        }

        // Evitar duplicidade
        $already = FinancialTransaction::withoutGlobalScopes()
            ->where('order_id', $order->id)
            ->where('type', 'revenue')
            ->exists();

        if ($already) {
            return;
        }

        // Valor bruto inicial
        $amount = (float) ($order->final_amount ?? $order->total_amount ?? 0);

        // 3. Tentar calcular valor líquido (descontando taxas do MP)
        $netAmount = null;
        if (!empty($order->payment_raw_response)) {
            $raw = $order->payment_raw_response;
            if (is_string($raw)) {
                $raw = json_decode($raw, true);
            }

            if (is_array($raw)) {
                if (isset($raw['transaction_details']['net_received_amount'])) {
                    $netAmount = (float) $raw['transaction_details']['net_received_amount'];
                } elseif (isset($raw['fee_details']) && is_array($raw['fee_details'])) {
                    $fee = collect($raw['fee_details'])->sum('amount');
                    $orderTotal = (float) ($order->final_amount ?? $order->total_amount ?? 0);
                    $netAmount = max(0, $orderTotal - $fee);
                }
            }
        }

        // Usar valor líquido se disponível
        $finalAmountToRegister = $netAmount !== null ? $netAmount : $amount;

        if ($finalAmountToRegister <= 0) {
            return;
        }

        // Data da transação
        $date = $order->created_at ?? $order->updated_at ?? now();
        $dateStr = $date instanceof \DateTimeInterface ? $date->format('Y-m-d') : now()->format('Y-m-d');

        try {
            $transaction = new FinancialTransaction();
            $transaction->client_id = $clientId;
            $transaction->type = 'revenue';
            $transaction->amount = $finalAmountToRegister;
            $transaction->description = 'Pedido ' . ($order->order_number ?? '#' . $order->id);
            $transaction->transaction_date = $dateStr;
            $transaction->category = 'Pedidos';
            $transaction->order_id = $order->id;
            $transaction->save();

            Log::info('OrderFinancialObserver: Receita criada com sucesso', [
                'order_id' => $order->id,
                'amount' => $finalAmountToRegister,
                'net_used' => $netAmount !== null
            ]);

        } catch (\Throwable $e) {
            Log::error('OrderFinancialObserver: erro ao criar receita de pedido', [
                'order_id' => $order->id,
                'client_id' => $clientId,
                'amount' => $finalAmountToRegister,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
