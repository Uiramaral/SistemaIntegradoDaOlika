<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerDebt;
use App\Models\Order;
use App\Services\BotConversaService;
use App\Services\MercadoPagoApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class DebtsController extends Controller
{
    /**
     * Enviar resumos de pedidos pendentes para um cliente via WhatsApp
     */
    public function sendPendingOrdersSummary(Request $request, Customer $customer)
    {
        try {
            // Buscar todos os débitos abertos do cliente
            $debts = CustomerDebt::where('customer_id', $customer->id)
                ->where('type', 'debit')
                ->where('status', 'open')
                ->with(['order' => function($query) {
                    $query->with(['items.product', 'address']);
                }])
                ->get();

            if ($debts->isEmpty()) {
                return redirect()->back()->with('error', 'Cliente não possui pedidos pendentes de pagamento.');
            }

            // Agrupar pedidos únicos
            $orders = $debts->map(function($debt) {
                return $debt->order;
            })->filter(function($order) {
                return $order !== null;
            })->unique('id');

            if ($orders->isEmpty()) {
                return redirect()->back()->with('error', 'Não foi possível encontrar os pedidos associados aos débitos.');
            }

            $botService = new BotConversaService();
            
            if (!$botService->isConfigured()) {
                return redirect()->back()->with('error', 'BotConversa não está configurado.');
            }

            $phone = $botService->normalizePhoneBR($customer->phone);
            
            if (!$phone) {
                return redirect()->back()->with('error', 'Cliente não possui telefone cadastrado.');
            }

            $totalAmount = 0;
            $sentCount = 0;

            // Enviar resumo de cada pedido
            foreach ($orders as $order) {
                if (!$order) continue;
                
                $message = $this->buildOrderSummaryMessage($order);
                
                if ($botService->sendTextMessage($phone, $message)) {
                    $totalAmount += $order->final_amount ?? $order->total_amount ?? 0;
                    $sentCount++;
                    
                    // Pequeno delay entre mensagens para evitar rate limit
                    usleep(500000); // 0.5 segundos
                } else {
                    Log::warning('Erro ao enviar resumo de pedido pendente', [
                        'customer_id' => $customer->id,
                        'order_id' => $order->id,
                    ]);
                }
            }

            // Gerar PIX para o total
            $pixData = null;
            try {
                $mpService = new MercadoPagoApiService();
                
                // Criar payload direto para PIX
                $payload = [
                    'transaction_amount' => $totalAmount,
                    'description' => "Pagamento de {$orders->count()} pedido(s) pendente(s) - {$customer->name}",
                    'payment_method_id' => 'pix',
                    'external_reference' => "DEBT-{$customer->id}-" . now()->format('YmdHis'),
                    'notification_url' => route('api.webhooks.mercadopago'),
                    'additional_info' => [
                        'payer' => [
                            'name' => $customer->name,
                            'email' => $customer->email ?? 'noemail@dummy.com',
                            'phone' => [
                                'number' => preg_replace('/\D/', '', $customer->phone ?? ''),
                            ],
                        ],
                    ],
                ];

                $accessToken = \App\Models\PaymentSetting::getMercadoPagoToken();
                
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                ])->post('https://api.mercadopago.com/v1/payments', $payload);

                if ($response->successful()) {
                    $data = $response->json();
                    $pixCopyPaste = $data['point_of_interaction']['transaction_data']['qr_code'] ?? null;
                    
                    if ($pixCopyPaste) {
                        $pixData = [
                            'qr_code' => $pixCopyPaste,
                            'copy_paste' => $pixCopyPaste,
                            'qr_base64' => $data['point_of_interaction']['transaction_data']['qr_code_base64'] ?? null,
                            'amount' => $totalAmount,
                        ];
                    }
                } else {
                    Log::warning('Erro ao gerar PIX - resposta do Mercado Pago', [
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Erro ao gerar PIX para total de pedidos pendentes', [
                    'customer_id' => $customer->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }

            // Enviar mensagem final com total e PIX
            $finalMessage = $this->buildFinalSummaryMessage($orders->count(), $totalAmount, $pixData);
            
            if (!$botService->sendTextMessage($phone, $finalMessage)) {
                Log::warning('Erro ao enviar mensagem final com total', [
                    'customer_id' => $customer->id,
                ]);
            }

            return redirect()->back()->with('success', 
                "Enviados {$sentCount} resumos de pedido e mensagem final com total para {$customer->name}."
            );

        } catch (\Exception $e) {
            Log::error('Erro ao enviar resumos de pedidos pendentes', [
                'customer_id' => $customer->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()->with('error', 'Erro ao enviar resumos: ' . $e->getMessage());
        }
    }

    /**
     * Marcar um débito como quitado (baixa manual)
     */
    public function settleDebt(Request $request, CustomerDebt $debt)
    {
        if ($debt->status !== 'open' || $debt->type !== 'debit') {
            return $request->wantsJson()
                ? response()->json(['ok' => false, 'message' => 'Este lançamento já foi quitado ou não é um débito.'], 422)
                : redirect()->back()->with('error', 'Este lançamento já foi quitado ou não é um débito.');
        }

        try {
            DB::transaction(function () use ($debt) {
                $debt->status = 'settled';
                $debt->save();

                CustomerDebt::create([
                    'customer_id' => $debt->customer_id,
                    'order_id' => $debt->order_id,
                    'amount' => $debt->amount,
                    'type' => 'credit',
                    'status' => 'settled',
                    'description' => 'Pagamento de fiado ref. #' . $debt->id,
                ]);
            });
        } catch (\Throwable $e) {
            Log::error('Erro ao quitar débito', [
                'debt_id' => $debt->id,
                'error' => $e->getMessage(),
            ]);

            return $request->wantsJson()
                ? response()->json(['ok' => false, 'message' => 'Erro ao registrar pagamento.'], 500)
                : redirect()->back()->with('error', 'Erro ao registrar pagamento do fiado.');
        }

        return $request->wantsJson()
            ? response()->json(['ok' => true])
            : redirect()->back()->with('success', 'Pagamento de fiado registrado com sucesso!');
    }

    /**
     * Construir mensagem de resumo de um pedido
     */
    private function buildOrderSummaryMessage(Order $order): string
    {
        $lines = [];
        $lines[] = "🧾 *PEDIDO #{$order->order_number}*";
        $lines[] = "";
        
        // Data do pedido
        $lines[] = "📅 Data: " . $order->created_at->format('d/m/Y H:i');
        $lines[] = "";

        // Itens
        $lines[] = "*Itens:*";
        foreach ($order->items as $item) {
            $qty = $item->quantity ?? 1;
            $name = $item->custom_name ?? ($item->product->name ?? 'Item');
            $unitPrice = $item->unit_price ?? 0;
            $total = $item->total_price ?? ($unitPrice * $qty);
            $lines[] = "• {$qty}x {$name} - R$ " . number_format($total, 2, ',', '.');
        }
        $lines[] = "";

        // Subtotal
        $subtotal = $order->total_amount ?? 0;
        $lines[] = "💰 Subtotal: R$ " . number_format($subtotal, 2, ',', '.');

        // Taxa de entrega
        if ($order->delivery_fee > 0) {
            $lines[] = "🚚 Taxa de Entrega: R$ " . number_format($order->delivery_fee, 2, ',', '.');
        }

        // Desconto
        if ($order->discount_amount > 0) {
            $lines[] = "🎫 Desconto: - R$ " . number_format($order->discount_amount, 2, ',', '.');
        }

        // Cashback usado
        if ($order->cashback_used > 0) {
            $lines[] = "💵 Cashback usado: - R$ " . number_format($order->cashback_used, 2, ',', '.');
        }

        $lines[] = "";
        $lines[] = "*TOTAL: R$ " . number_format($order->final_amount ?? $subtotal, 2, ',', '.') . "*";
        $lines[] = "";

        // Endereço de entrega (se houver)
        if ($order->address) {
            $addr = $order->address;
            $lines[] = "📍 Endereço:";
            $addressParts = array_filter([
                $addr->street ?? null,
                $addr->number ?? null,
                $addr->complement ?? null,
            ]);
            if (!empty($addressParts)) {
                $lines[] = implode(', ', $addressParts);
            }
            if ($addr->neighborhood) {
                $lines[] = $addr->neighborhood;
            }
            if ($addr->city || $addr->state) {
                $cityState = array_filter([$addr->city, $addr->state]);
                $lines[] = implode(' - ', $cityState);
            }
            if ($addr->zipcode) {
                $lines[] = "CEP: " . $addr->zipcode;
            }
            $lines[] = "";
        }

        return implode("\n", $lines);
    }

    /**
     * Construir mensagem final com total e PIX
     */
    private function buildFinalSummaryMessage(int $ordersCount, float $totalAmount, ?array $pixData = null): string
    {
        $lines = [];
        $lines[] = "━━━━━━━━━━━━━━━━━━━━";
        $lines[] = "📊 *RESUMO GERAL*";
        $lines[] = "━━━━━━━━━━━━━━━━━━━━";
        $lines[] = "";
        $lines[] = "📦 Total de pedidos: {$ordersCount}";
        $lines[] = "";
        $lines[] = "*💰 VALOR TOTAL: R$ " . number_format($totalAmount, 2, ',', '.') . "*";
        $lines[] = "";

        if ($pixData && isset($pixData['copy_paste'])) {
            $lines[] = "━━━━━━━━━━━━━━━━━━━━";
            $lines[] = "💳 *PAGAMENTO PIX*";
            $lines[] = "━━━━━━━━━━━━━━━━━━━━";
            $lines[] = "";
            $lines[] = "Valor: R$ " . number_format($totalAmount, 2, ',', '.');
            $lines[] = "";
            $lines[] = "*Código PIX (Copiar e Colar):*";
            $lines[] = "";
            $lines[] = "```";
            $lines[] = $pixData['copy_paste'];
            $lines[] = "```";
            $lines[] = "";
            $lines[] = "📱 Instruções:";
            $lines[] = "1. Abra o app do seu banco";
            $lines[] = "2. Escolha a opção PIX";
            $lines[] = "3. Cole o código acima";
            $lines[] = "4. Confirme o pagamento";
            $lines[] = "";
            $lines[] = "✅ Após o pagamento, seus pedidos serão confirmados automaticamente!";
        } else {
            $lines[] = "⚠️ Entre em contato para finalizar o pagamento.";
        }

        return implode("\n", $lines);
    }
}

