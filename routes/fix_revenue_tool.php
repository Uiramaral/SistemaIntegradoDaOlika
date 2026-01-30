<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

// ROTA DE DIAGNÓSTICO E CORREÇÃO FINANCEIRA - VERSÃO CORRIGIDA
// Sobrescreve a rota anterior se carregado depois
Route::get('/admin/fix-revenue', function () {
    $days = request()->get('days', 30);
    $startDate = now()->subDays((int) $days);

    // PARTE 1: LIMPEZA
    // Remover transações de pedidos que foram cancelados/excluídos
    $invalidTransactions = \App\Models\FinancialTransaction::whereHas('order', function ($q) {
        $q->whereIn('status', ['canceled', 'trash']);
    })->get();

    $deletedCount = 0;
    foreach ($invalidTransactions as $trans) {
        $trans->delete();
        $deletedCount++;
    }

    // PARTE 2: RECUPERAÇÃO
    // Buscar pedidos recentes, EXCLUINDO cancelados e lixo
    $orders = \App\Models\Order::where('created_at', '>=', $startDate)
        ->whereNotIn('status', ['canceled', 'trash'])
        ->orderBy('created_at', 'desc')
        ->with('customer')
        ->get();

    $results = [];
    $processedCount = 0;

    foreach ($orders as $order) {
        $exists = \App\Models\FinancialTransaction::withoutGlobalScopes()
            ->where('order_id', $order->id)
            ->where('type', 'revenue')
            ->exists();

        $paymentStatus = strtolower($order->payment_status ?? 'vazio');

        // Critério de Venda Válida: Pago/Aprovado OU Fiado (não cancelado)
        $isValidSale = in_array($paymentStatus, ['paid', 'approved'])
            || ($order->payment_method === 'fiado');

        $action = 'Ignorado';
        $reason = '';

        // Tentar identificar o client_id
        $resolvedClientId = $order->client_id;
        if (!$resolvedClientId && $order->customer)
            $resolvedClientId = $order->customer->client_id;

        if ($exists) {
            $reason = 'Já existe no financeiro';
        } elseif (!$isValidSale) {
            $reason = "Aguardando pagto ({$paymentStatus})";
        } else {
            // Tentar criar
            if (!$resolvedClientId) {
                $admin = DB::table('users')->where('role', 'admin')->whereNotNull('client_id')->first();
                if ($admin)
                    $resolvedClientId = $admin->client_id;
            }

            if ($resolvedClientId) {
                try {
                    // Valores
                    $amount = (float) ($order->final_amount ?? $order->total_amount ?? 0);
                    $netAmount = null;

                    // Se for MP, tenta pegar o líquido. Se for Fiado, é o valor cheio.
                    if ($order->payment_method !== 'fiado' && !empty($order->payment_raw_response)) {
                        $raw = $order->payment_raw_response;
                        if (is_string($raw))
                            $raw = json_decode($raw, true);
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

                    // Data
                    $date = $order->created_at ?? now();
                    // Se pagou depois, atualiza data. Se for fiado, usa data do pedido mesmo.
                    if ($order->updated_at > $order->created_at && in_array($paymentStatus, ['paid', 'approved'])) {
                        $date = $order->updated_at;
                    }

                    $t = new \App\Models\FinancialTransaction();
                    $t->client_id = $resolvedClientId;
                    $t->type = 'revenue';
                    $t->amount = $finalAmount;
                    $t->description = 'Pedido ' . ($order->order_number ?? '#' . $order->id) . ($order->payment_method === 'fiado' ? ' (Fiado)' : '');
                    $t->transaction_date = $date->format('Y-m-d');
                    $t->category = 'Pedidos';
                    $t->order_id = $order->id;
                    $t->save();

                    $action = 'CRIADO';
                    $reason = 'Recuperado com sucesso';
                    $processedCount++;
                } catch (\Exception $e) {
                    $action = 'ERRO';
                    $reason = $e->getMessage();
                }
            } else {
                $reason = 'Sem client_id definido';
            }
        }

        $results[] = [
            'id' => $order->id,
            'number' => $order->order_number ?? $order->id,
            'date' => $order->created_at ? $order->created_at->format('d/m H:i') : '-',
            'customer' => $order->customer ? $order->customer->name : 'N/A',
            'amount' => $order->final_amount ?? $order->total_amount,
            'payment_status' => $paymentStatus,
            'method' => $order->payment_method,
            'status' => $order->status,
            'resolved_client_id' => $resolvedClientId,
            'action' => $action,
            'reason' => $reason
        ];
    }

    // Gerar tabela HTML
    $html = '<html><head><style>body{font-family:sans-serif;padding:20px;} table{border-collapse:collapse;width:100%;} th,td{border:1px solid #ddd;padding:8px;text-align:left;} th{background:#f4f4f4;} .CRIADO{background:#dff0d8;color:green;font-weight:bold;} .ERRO{background:#f2dede;color:red;}</style></head><body>';
    $html .= "<h1>Relatório de Diagnóstico Financeiro (Últimos {$days} dias)</h1>";
    $html .= "<p>Itens Removidos (Cancelados): <strong>{$deletedCount}</strong> | Total Recuperado Agora: <strong>{$processedCount}</strong></p>";
    $html .= "<p><em>Pedidos Cancelados/Lixo foram ocultados e suas transações removidas. Pedidos FIADO considerados válidos.</em></p>";
    $html .= "<table><thead><tr><th>Pedido</th><th>Data</th><th>Cliente</th><th>Valor</th><th>Método</th><th>Status Pagto</th><th>St. Pedido</th><th>Ação</th><th>Motivo</th></tr></thead><tbody>";

    foreach ($results as $row) {
        $class = $row['action'] === 'CRIADO' ? 'CRIADO' : ($row['action'] === 'ERRO' ? 'ERRO' : '');
        $html .= "<tr class='{$class}'>";
        $html .= "<td>#{$row['number']}</td>";
        $html .= "<td>{$row['date']}</td>";
        $html .= "<td>{$row['customer']}</td>";
        $html .= "<td>R$ " . number_format((float) $row['amount'], 2, ',', '.') . "</td>";
        $html .= "<td>{$row['method']}</td>";
        $html .= "<td>{$row['payment_status']}</td>";
        $html .= "<td>{$row['status']}</td>";
        $html .= "<td>{$row['action']}</td>";
        $html .= "<td>{$row['reason']}</td>";
        $html .= "</tr>";
    }
    $html .= "</tbody></table></body></html>";

    return $html;
});
