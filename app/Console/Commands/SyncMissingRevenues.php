<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Models\FinancialTransaction;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncMissingRevenues extends Command
{
    protected $signature = 'revenue:sync {--days=90}';
    protected $description = 'Sincroniza receitas faltantes de pedidos pagos';

    public function handle()
    {
        $days = $this->option('days');
        $startDate = now()->subDays((int) $days);

        $this->info("Buscando pedidos pagos desde {$startDate->format('d/m/Y')}...");

        $orders = Order::whereIn('payment_status', ['paid', 'approved'])
            ->where('created_at', '>=', $startDate)
            ->get();

        $count = 0;
        $total = $orders->count();

        $this->info("Encontrados {$total} pedidos pagos. Verificando quais faltam no financeiro...");

        foreach ($orders as $order) {
            // Verificar se já tem transação
            $exists = FinancialTransaction::withoutGlobalScopes()
                ->where('order_id', $order->id)
                ->where('type', 'revenue')
                ->exists();

            if ($exists) {
                continue;
            }

            $this->processOrder($order);
            $count++;
        }

        $this->info("Concluído! {$count} receitas foram criadas/recuperadas.");
    }

    private function processOrder(Order $order)
    {
        $this->line("Processando Pedido #{$order->order_number}...");

        // Resolver client_id (Cópia da lógica robusta do Observer)
        $clientId = $order->client_id;

        if (!$clientId || !is_numeric($clientId) || $clientId <= 0) {
            if ($order->customer && !empty($order->customer->client_id)) {
                $clientId = $order->customer->client_id;
            } else {
                // Tenta pegar do primeiro usuário admin encontrado se tudo falhar (fallback de emergência)
                $admin = DB::table('users')->where('role', 'admin')->whereNotNull('client_id')->first();
                if ($admin) {
                    $clientId = $admin->client_id;
                }
            }
        }

        if (!$clientId) {
            $this->error("Pular Pedido #{$order->order_number}: Não foi possível determinar client_id.");
            return;
        }

        // Valor bruto
        $amount = (float) ($order->final_amount ?? $order->total_amount ?? 0);

        // Calcular líquido
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
                    $netAmount = max(0, $amount - $fee);
                }
            }
        }

        $finalAmount = $netAmount !== null ? $netAmount : $amount;

        if ($finalAmount <= 0) {
            $this->warn("Pedido #{$order->order_number}: Valor final zero ou negativo.");
            return;
        }

        // Data
        $date = $order->created_at ?? now();
        // Se for fiado pago depois, tentar achar a data do pagamento (não temos log fácil aqui, usar created ou updated como aproximação)
        if ($order->payment_method === 'fiado' && $order->updated_at > $order->created_at) {
            $date = $order->updated_at;
        }

        try {
            $t = new FinancialTransaction();
            $t->client_id = $clientId;
            $t->type = 'revenue';
            $t->amount = $finalAmount;
            $t->description = 'Pedido ' . ($order->order_number ?? '#' . $order->id);
            $t->transaction_date = $date->format('Y-m-d');
            $t->category = 'Pedidos';
            $t->order_id = $order->id;
            $t->save();

            $this->info(" [OK] Receita criada: R$ " . number_format($finalAmount, 2));

        } catch (\Exception $e) {
            $this->error("Erro ao salvar: " . $e->getMessage());
        }
    }
}
