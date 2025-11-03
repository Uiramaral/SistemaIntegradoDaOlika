<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Services\AppSettings;
use App\Services\MercadoPagoApi;

class PaymentController extends Controller
{
    public function createPix(Request $r)
    {
        $order = Order::findOrFail(session('order_id'));

        $order->payment_method = 'pix';
        $order->save();

        $mp = new MercadoPagoApi();

        // Preparar itens para o payload
        $items = $order->items->map(function($item) {
            return [
                "title" => $item->custom_name ?? ($item->product->name ?? 'Produto'),
                "quantity" => (int)$item->quantity,
                "unit_price" => (float)$item->unit_price,
            ];
        })->values()->all();

        $payload = [
            "number" => $order->order_number,
            "total" => (float)$order->final_amount,
            "description" => "Pedido #{$order->order_number}",
            "items" => $items,
            "discount_amount" => $order->discount_amount ?? 0,
            "coupon_code" => $order->coupon_code ?? null,
            "discount_type" => $order->discount_type ?? null,
            "delivery_fee" => $order->delivery_fee ?? 0,
            "notification_url"  => AppSettings::get('mercadopago_webhook_url', route('webhooks.mercadopago')),
        ];

        $payer = [
                "email" => optional($order->customer)->email ?: "noemail@dummy.com",
            "first_name" => explode(' ', optional($order->customer)->name ?? 'Cliente')[0],
            "last_name" => (explode(' ', optional($order->customer)->name ?? 'Cliente', 2)[1] ?? ''),
        ];

        $res = $mp->createPixPayment($payload, $payer);

        // O método retorna ['ok'=>true, 'qr_code'=>..., 'qr_code_base64'=>..., 'id'=>..., 'raw'=>...]
        $qrBase64   = $res['qr_code_base64'] ?? data_get($res, 'raw.point_of_interaction.transaction_data.qr_code_base64');
        $copiaCola  = $res['qr_code'] ?? data_get($res, 'raw.point_of_interaction.transaction_data.qr_code');
        $paymentId  = (string) ($res['id'] ?? data_get($res, 'raw.id'));
        $status     = (string) data_get($res, 'raw.status');
        $expiresAt  = data_get($res, 'raw.date_of_expiration');

        $order->payment_id        = $paymentId;
        $order->payment_status    = $status;
        $order->pix_qr_base64     = $qrBase64;
        $order->pix_copy_paste    = $copiaCola;
        $order->pix_expires_at     = $expiresAt;
        $order->payment_raw_response = json_encode($res);
        $order->save();

        return response()->json([
            'ok' => true,
            'qr_base64' => $qrBase64,
            'copia_cola' => $copiaCola
        ]);
    }

    public function createMpPreference(Request $r)
    {
        $order = Order::findOrFail(session('order_id'));

        $order->payment_method = 'mercadopago';
        $order->save();

        $items = $order->items->map(function($i) {
            return [
                "title" => $i->product_name ?? ($i->custom_name ?? 'Produto'),
                "quantity" => (int)($i->qty ?? $i->quantity ?? 1),
                "currency_id" => "BRL",
                "unit_price" => (float)($i->price ?? $i->unit_price ?? 0),
            ];
        })->values()->all();

        $mp = new MercadoPagoApi();

        $orderData = [
            "number" => $order->order_number,
            "items" => $items,
            "total" => (float)$order->final_amount,
            "discount_amount" => $order->discount_amount ?? 0,
            "coupon_code" => $order->coupon_code ?? null,
            "discount_type" => $order->discount_type ?? null,
            "delivery_fee" => $order->delivery_fee ?? 0,
            "metadata" => ["order_id" => $order->id, "order_number" => $order->order_number],
            "notification_url" => AppSettings::get('mercadopago_webhook_url', route('webhooks.mercadopago')),
            "back_urls" => [
                "success" => route('payment.success', $order),
                "pending" => route('payment.success', $order),
                "failure" => route('payment.failure', $order),
            ],
        ];

        $payer = [
            "email" => optional($order->customer)->email ?: "noemail@dummy.com",
            "first_name" => explode(' ', optional($order->customer)->name ?? 'Cliente')[0],
            "last_name" => (explode(' ', optional($order->customer)->name ?? 'Cliente', 2)[1] ?? ''),
            "phone" => optional($order->customer)->phone,
        ];

        $res = $mp->createPaymentLink($orderData, $payer);

        $order->preference_id = data_get($res, 'id');
        $order->payment_link  = data_get($res, 'init_point');
        $order->payment_raw_response = json_encode($res);
        $order->save();

        return response()->json(['ok' => true, 'init_point' => $order->payment_link]);
    }

    /**
     * Exibe página de pagamento PIX
     */
    public function pixPayment(Order $order)
    {
        try {
            // Garantir que o pedido tem dados do PIX
            if (!$order->pix_copy_paste && $order->payment_status !== 'paid') {
                // Se não tiver PIX gerado, criar agora
                $mp = new MercadoPagoApi();
                
                // Preparar itens do pedido para o payload
                $items = $order->items->map(function($item) {
                    return [
                        "title" => $item->custom_name ?? ($item->product->name ?? 'Produto'),
                        "quantity" => (int)$item->quantity,
                        "unit_price" => (float)$item->unit_price,
                    ];
                })->values()->all();
                
                $payload = [
                    "number" => $order->order_number,
                    "total" => (float)$order->final_amount,
                    "description" => "Pedido #{$order->order_number}",
                    "items" => $items,
                    "discount_amount" => $order->discount_amount ?? 0,
                    "coupon_code" => $order->coupon_code ?? null,
                    "discount_type" => $order->discount_type ?? null,
                    "delivery_fee" => $order->delivery_fee ?? 0,
                    "notification_url" => AppSettings::get('mercadopago_webhook_url', route('webhooks.mercadopago')),
                ];

                $payer = [
                        "email" => optional($order->customer)->email ?: "noemail@dummy.com",
                    "first_name" => explode(' ', optional($order->customer)->name ?? 'Cliente')[0],
                    "last_name" => (explode(' ', optional($order->customer)->name ?? 'Cliente', 2)[1] ?? ''),
                ];

                $res = $mp->createPixPayment($payload, $payer);

                if (!empty($res['ok']) || !empty($res['raw'])) {
                    // O método retorna ['ok'=>true, 'qr_code'=>..., 'qr_code_base64'=>..., 'id'=>..., 'raw'=>...]
                    $qrBase64 = $res['qr_code_base64'] ?? data_get($res, 'raw.point_of_interaction.transaction_data.qr_code_base64');
                    $copiaCola = $res['qr_code'] ?? data_get($res, 'raw.point_of_interaction.transaction_data.qr_code');
                    $paymentId = (string) ($res['id'] ?? data_get($res, 'raw.id'));
                    $status = (string) data_get($res, 'raw.status');
                    $expiresAt = data_get($res, 'raw.date_of_expiration');

                    $order->payment_id = $paymentId;
                    $order->payment_status = $status ?? 'pending';
                    $order->pix_qr_base64 = $qrBase64;
                    $order->pix_copy_paste = $copiaCola;
                    $order->pix_expires_at = $expiresAt;
                    $order->payment_raw_response = json_encode($res);
                    $order->payment_method = 'pix';
                    $order->save();
                    
                    $order->refresh();
                } else {
                    \Log::error('PaymentController:pixPayment - Erro ao criar PIX', ['order_id' => $order->id, 'response' => $res]);
                    // Continuar mesmo se houver erro para não quebrar o fluxo
                }
            }

            return view('pedido.payment.pix', compact('order'));
        } catch (\Exception $e) {
            \Log::error('PaymentController:pixPayment - Exceção', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Não redirecionar de volta ao checkout - mostra a página mesmo com erro
            return view('pedido.payment.pix', compact('order'))
                ->with('error', 'Erro ao processar pagamento. Por favor, entre em contato com o suporte.');
        }
    }

    /**
     * Endpoint de status (JSON) para polling pelo front
     */
    public function status(Order $order)
    {
        // Se ainda pendente e houver payment_id, tenta atualizar consultando o provedor
        $wasPaid = in_array(strtolower((string)$order->payment_status), ['approved','paid']);
        if (!$wasPaid && !empty($order->payment_id)) {
            try {
                $svc = new \App\Services\MercadoPagoApiService();
                $res = $svc->getPaymentStatus((string)$order->payment_id);
                if (!empty($res['success']) && !empty($res['payment'])) {
                    $payment = $res['payment'];
                    $status = strtolower((string)($payment['status'] ?? 'pending'));
                    $order->payment_status = $status;
                    $order->payment_raw_response = $payment;
                    // Atualiza status de pedido básico
                    if (in_array($status, ['approved','paid'])) { $order->status = 'confirmed'; }
                    $order->save();
                    // Registrar transações de cashback quando pago
                    if (in_array($status, ['approved','paid'])) {
                        try {
                            // Débito: cashback usado
                            if ($order->cashback_used > 0) {
                                \App\Models\CustomerCashback::createDebit(
                                    $order->customer_id,
                                    $order->id,
                                    $order->cashback_used,
                                    "Uso de cashback no pedido #{$order->order_number}"
                                );
                            }
                            
                            // Crédito: cashback ganho
                            if ($order->cashback_earned > 0) {
                                \App\Models\CustomerCashback::createCredit(
                                    $order->customer_id,
                                    $order->id,
                                    $order->cashback_earned,
                                    "Cashback do pedido #{$order->order_number}"
                                );
                            }
                        } catch (\Throwable $e) {
                            \Log::warning('Falha ao registrar cashback no polling', ['order_id' => $order->id, 'err' => $e->getMessage()]);
                        }
                        
                        // Baixar débitos relacionados ao pedido (fiado)
                        try {
                            $debts = \App\Models\CustomerDebt::where('order_id', $order->id)
                                ->where('type', 'debit')
                                ->where('status', 'open')
                                ->get();
                            
                            foreach ($debts as $debt) {
                                // Marcar débito como quitado
                                $debt->status = 'settled';
                                $debt->save();
                                
                                // Criar crédito (baixa) do mesmo valor
                                \App\Models\CustomerDebt::create([
                                    'customer_id' => $debt->customer_id,
                                    'order_id' => $order->id,
                                    'amount' => $debt->amount,
                                    'type' => 'credit',
                                    'status' => 'settled',
                                    'description' => "Baixa de fiado - Pedido #{$order->order_number}",
                                ]);
                                
                                \Log::info('PDV: Débito baixado após pagamento (polling)', [
                                    'order_id' => $order->id,
                                    'debt_id' => $debt->id,
                                    'amount' => $debt->amount,
                                ]);
                            }
                        } catch (\Throwable $e) {
                            \Log::warning('Falha ao baixar débitos após pagamento (polling)', ['order_id' => $order->id, 'err' => $e->getMessage()]);
                        }
                        
                        // Dispara webhook para BotConversa quando pago
                        if (empty($order->notified_paid_at)) {
                            try {
                                $bot = new \App\Services\BotConversaService();
                                $ok = $bot->sendPaidOrderJson($order->loadMissing('items.product','customer','address'));
                                if ($ok) { $order->notified_paid_at = now(); $order->save(); }
                            } catch (\Throwable $e) { 
                                \Log::warning('Falha ao notificar BotConversa no polling', ['order_id' => $order->id, 'err' => $e->getMessage()]);
                            }
                        }
                    }
                }
            } catch (\Throwable $e) { /* silencioso no polling */ }
        }

        return response()->json([
            'success' => true,
            'order_id' => $order->id,
            'payment_status' => $order->payment_status,
            'status' => $order->status,
        ]);
    }

    /**
     * Checkout para cartão (redireciona para Mercado Pago)
     */
    public function checkout(Request $request, Order $order)
    {
        $method = $request->get('method', 'credit_card');
        
        // Criar preferência do Mercado Pago
        $items = $order->items->map(function($item) {
            return [
                "title" => $item->custom_name ?? ($item->product->name ?? 'Produto'),
                "quantity" => (int)$item->quantity,
                "currency_id" => "BRL",
                "unit_price" => (float)$item->unit_price,
            ];
        })->values()->all();

        $mp = new MercadoPagoApi();

        $orderData = [
            "number" => $order->order_number,
            "items" => $items,
            "total" => (float)$order->final_amount,
            "discount_amount" => $order->discount_amount ?? 0,
            "coupon_code" => $order->coupon_code ?? null,
            "discount_type" => $order->discount_type ?? null,
            "delivery_fee" => $order->delivery_fee ?? 0,
            "metadata" => ["order_id" => $order->id, "order_number" => $order->order_number],
            "notification_url" => AppSettings::get('mercadopago_webhook_url', route('webhooks.mercadopago')),
            "back_urls" => [
                "success" => route('pedido.payment.success', $order),
                "pending" => route('pedido.payment.success', $order),
                "failure" => route('pedido.payment.failure', $order),
            ],
        ];

        $payer = [
            "email" => optional($order->customer)->email ?: "noemail@dummy.com",
            "first_name" => explode(' ', optional($order->customer)->name ?? 'Cliente')[0],
            "last_name" => (explode(' ', optional($order->customer)->name ?? 'Cliente', 2)[1] ?? ''),
            "phone" => optional($order->customer)->phone,
        ];

        $res = $mp->createPaymentLink($orderData, $payer);

        $initPoint = data_get($res, 'init_point');
        
        if ($initPoint) {
            $order->preference_id = data_get($res, 'id');
            $order->payment_link = $initPoint;
            $order->payment_method = $method;
            // Se falhar, não setar payment_status vazio
            if (!empty($res['ok'])) {
                $order->payment_status = $order->payment_status ?: 'pending';
            } else {
                $order->payment_status = $order->payment_status ?: 'pending'; // mantém pending; evita null
            }
            $order->payment_raw_response = json_encode($res);
            $order->save();
            
            return redirect($initPoint);
        }

        return redirect()->back()->with('error', 'Erro ao gerar link de pagamento');
    }

    /**
     * Página de sucesso do pagamento
     */
    public function success(Order $order)
    {
        // Marcar como pago se ainda não marcado e alimentar fidelidade
        if ($order->payment_status !== 'paid') {
            $order->payment_status = 'paid';
            $order->status = 'confirmed';
            $order->save();
            try {
                app(\App\Http\Controllers\LoyaltyController::class)->addPoints($order);
            } catch (\Throwable $e) {
                \Log::warning('Falha ao creditar pontos de fidelidade', ['order_id' => $order->id, 'err' => $e->getMessage()]);
            }
            
            // Registrar transações de cashback
            try {
                // Débito: cashback usado
                if ($order->cashback_used > 0) {
                    \App\Models\CustomerCashback::createDebit(
                        $order->customer_id,
                        $order->id,
                        $order->cashback_used,
                        "Uso de cashback no pedido #{$order->order_number}"
                    );
                }
                
                // Crédito: cashback ganho
                if ($order->cashback_earned > 0) {
                    \App\Models\CustomerCashback::createCredit(
                        $order->customer_id,
                        $order->id,
                        $order->cashback_earned,
                        "Cashback do pedido #{$order->order_number}"
                    );
                }
            } catch (\Throwable $e) {
                \Log::warning('Falha ao registrar cashback', ['order_id' => $order->id, 'err' => $e->getMessage()]);
            }
            
            // Baixar débitos relacionados ao pedido (fiado)
            try {
                $debts = \App\Models\CustomerDebt::where('order_id', $order->id)
                    ->where('type', 'debit')
                    ->where('status', 'open')
                    ->get();
                
                foreach ($debts as $debt) {
                    // Marcar débito como quitado
                    $debt->status = 'settled';
                    $debt->save();
                    
                    // Criar crédito (baixa) do mesmo valor
                    \App\Models\CustomerDebt::create([
                        'customer_id' => $debt->customer_id,
                        'order_id' => $order->id,
                        'amount' => $debt->amount,
                        'type' => 'credit',
                        'status' => 'settled',
                        'description' => "Baixa de fiado - Pedido #{$order->order_number}",
                    ]);
                    
                    \Log::info('PDV: Débito baixado após pagamento', [
                        'order_id' => $order->id,
                        'debt_id' => $debt->id,
                        'amount' => $debt->amount,
                    ]);
                }
            } catch (\Throwable $e) {
                \Log::warning('Falha ao baixar débitos após pagamento', ['order_id' => $order->id, 'err' => $e->getMessage()]);
            }
        }

        // Notificar via BotConversa (evita duplicidade)
        try {
            if (empty($order->notified_paid_at)) {
                $bot = new \App\Services\BotConversaService();
                // URL custom não é mais necessário - o serviço já lê das settings
                $ok = $bot->sendPaidOrderJson($order->loadMissing('items.product','customer','address'));
                if (!$ok && $bot->isConfigured()) {
                    // Tenta fallback para configuração padrão
                    $ok = $bot->sendPaidOrderJson($order);
                }
                if ($ok) {
                    $order->notified_paid_at = now();
                    $order->save();
                }
            }
        } catch (\Throwable $e) {
            \Log::warning('Falha ao notificar BotConversa', ['order_id' => $order->id, 'err' => $e->getMessage()]);
        }

        // Evolution API (rodando em paralelo até a migração completa)
        try {
            $evo = new \App\Services\EvolutionApiService();
            if ($evo->isConfigured()) {
                $evo->sendPaidOrder($order);
            }
        } catch (\Throwable $e) {
            \Log::warning('Evolution API: falha ao enviar mensagem', ['order_id' => $order->id, 'err' => $e->getMessage()]);
        }

        return view('pedido.payment.success', compact('order'));
    }

    /**
     * Página de falha do pagamento
     */
    public function failure(Order $order)
    {
        return view('pedido.payment.failure', compact('order'));
    }
}
