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
            "metadata" => [
                "order_id" => $order->id,
                "order_number" => $order->order_number,
                "customer_id" => $order->customer_id,
            ],
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

        // Preparar itens para Mercado Pago
        $items = $order->items->map(function($i) {
            return [
                "title" => $i->product_name ?? ($i->custom_name ?? 'Produto'),
                "quantity" => (int)($i->qty ?? $i->quantity ?? 1),
                "unit_price" => (float)($i->price ?? $i->unit_price ?? 0),
            ];
        })->toArray();

        $mp = new MercadoPagoApi();
        
        // Usar createPaymentLinkFromOrder que permite PIX E cartão (crédito/débito)
        // Isso permite que o cliente escolha no link do Mercado Pago
        $customer = $order->customer;
        $res = $mp->createPaymentLinkFromOrder($order, $customer, $items);

        $order->preference_id = $res['preference_id'] ?? null;
        $order->payment_link = $res['checkout_url'] ?? null;
        $order->payment_method = 'mercadopago'; // Será definido pelo cliente no link
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
                    "metadata" => [
                        "order_id" => $order->id,
                        "order_number" => $order->order_number,
                        "customer_id" => $order->customer_id,
                    ],
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
                    
                    \Log::info('PaymentController:status - Status do pagamento consultado', [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'payment_id' => $order->payment_id,
                        'current_status' => $order->payment_status,
                        'new_status' => $status,
                        'payment_method' => $payment['payment_method_id'] ?? null,
                    ]);
                    
                    // Mapear status do MercadoPago para valores válidos do ENUM
                    $mappedStatus = \App\Services\MercadoPagoApiService::mapPaymentStatus($status);
                    $order->payment_status = $mappedStatus;
                    $order->payment_raw_response = $payment;
                    // Atualiza status de pedido básico
                    if (in_array($status, ['approved','paid'])) { 
                        $order->status = 'confirmed'; 
                        \Log::info('PaymentController:status - Pagamento aprovado, atualizando status do pedido', [
                            'order_id' => $order->id,
                            'order_number' => $order->order_number,
                            'status' => $status,
                        ]);
                    }
                    $order->save();
                    // Registrar transações de cashback quando pago
                    if (in_array($status, ['approved','paid'])) {
                        // Limpar sessão quando o pagamento é confirmado via polling
                        session()->forget('cart');
                        session()->forget('cart_count');
                        
                        // IMPORTANTE: Usar OrderStatusService para processar a confirmação completa
                        try {
                            $orderStatusService = app(\App\Services\OrderStatusService::class);
                            $orderStatusService->changeStatus(
                                $order, 
                                'paid', 
                                'Pagamento aprovado via polling (PIX)',
                                null, // userId
                                false // skipHistory
                            );
                            \Log::info('PaymentController (polling): OrderStatusService chamado para confirmar pagamento', [
                                'order_id' => $order->id,
                                'order_number' => $order->order_number,
                            ]);
                        } catch (\Exception $e) {
                            \Log::error('PaymentController (polling): Erro ao chamar OrderStatusService', [
                                'order_id' => $order->id,
                                'error' => $e->getMessage(),
                            ]);
                            // Continuar com processamento manual se OrderStatusService falhar
                        }
                        
                        // IMPORTANTE: Recarregar o pedido do banco para garantir que temos os valores corretos
                        $order->refresh();
                        
                        try {
                            \Log::info('PaymentController (polling): Registrando cashback', [
                                'order_id' => $order->id,
                                'order_number' => $order->order_number,
                                'cashback_used' => $order->cashback_used,
                                'cashback_earned' => $order->cashback_earned,
                                'customer_id' => $order->customer_id,
                            ]);
                            
                            // Verificar se já existe transação de cashback para este pedido (evitar duplicatas)
                            $existingCashback = \App\Models\CustomerCashback::where('order_id', $order->id)->first();
                            if ($existingCashback) {
                                \Log::warning('PaymentController (polling): Cashback já registrado para este pedido, pulando', [
                                    'order_id' => $order->id,
                                    'existing_cashback_id' => $existingCashback->id,
                                ]);
                            } else {
                                // Débito: cashback usado
                                if ($order->cashback_used > 0) {
                                    $debit = \App\Models\CustomerCashback::createDebit(
                                        $order->customer_id,
                                        $order->id,
                                        $order->cashback_used,
                                        "Uso de cashback no pedido #{$order->order_number}"
                                    );
                                    \Log::info('PaymentController (polling): Débito de cashback criado', [
                                        'debit_id' => $debit->id,
                                        'amount' => $debit->amount,
                                    ]);
                                }
                                
                                // Crédito: cashback ganho
                                if ($order->cashback_earned > 0) {
                                    $credit = \App\Models\CustomerCashback::createCredit(
                                        $order->customer_id,
                                        $order->id,
                                        $order->cashback_earned,
                                        "Cashback do pedido #{$order->order_number}"
                                    );
                                    \Log::info('PaymentController (polling): Crédito de cashback criado', [
                                        'credit_id' => $credit->id,
                                        'amount' => $credit->amount,
                                    ]);
                                } else {
                                    \Log::warning('PaymentController (polling): Cashback ganho é zero ou nulo', [
                                        'order_id' => $order->id,
                                        'cashback_earned' => $order->cashback_earned,
                                    ]);
                                }
                            }
                        } catch (\Throwable $e) {
                            \Log::error('PaymentController (polling): Falha ao registrar cashback', [
                                'order_id' => $order->id,
                                'err' => $e->getMessage(),
                                'trace' => $e->getTraceAsString()
                            ]);
                            \Log::warning('Falha ao registrar cashback no polling', ['order_id' => $order->id, 'err' => $e->getMessage()]);
                        }
                        
                        // Atualizar estatísticas do cliente
                        try {
                            if ($order->customer) {
                                $order->customer->updateStatsAfterPaidOrder();
                            }
                        } catch (\Throwable $e) {
                            \Log::warning('Falha ao atualizar estatísticas do cliente no polling', ['order_id' => $order->id, 'err' => $e->getMessage()]);
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
                                
                                // Enviar notificação para o número específico quando pedido for pago
                                if ($bot->isConfigured() && in_array($order->payment_status, ['paid', 'approved'])) {
                                    try {
                                        $notificationPhone = '+5571981750546'; // Número fixo para notificações
                                        $message = "🆕 *NOVO PEDIDO PAGO!*\n\n";
                                        $message .= "Pedido: #{$order->order_number}\n";
                                        $message .= "Cliente: " . ($order->customer->name ?? 'N/A') . "\n";
                                        $message .= "Valor: R$ " . number_format($order->final_amount ?? $order->total_amount ?? 0, 2, ',', '.') . "\n";
                                        $message .= "Status: " . ($order->status ?? 'confirmed') . "\n\n";
                                        $message .= "Acesse o dashboard para ver os detalhes.";
                                        
                                        $bot->sendTextMessage($notificationPhone, $message);
                                        
                                        \Log::info('PaymentController (polling): Notificação enviada para número de administrador', [
                                            'order_id' => $order->id,
                                            'order_number' => $order->order_number,
                                            'phone' => $notificationPhone
                                        ]);
                                    } catch (\Throwable $e) {
                                        \Log::warning('PaymentController (polling): Erro ao enviar notificação para administrador', [
                                            'order_id' => $order->id,
                                            'error' => $e->getMessage()
                                        ]);
                                    }
                                }
                            } catch (\Throwable $e) { 
                                \Log::warning('Falha ao notificar BotConversa no polling', ['order_id' => $order->id, 'err' => $e->getMessage()]);
                            }
                        }
                    }
                }
            } catch (\Throwable $e) { 
                \Log::error('PaymentController:status - Erro ao consultar status do pagamento', [
                    'order_id' => $order->id,
                    'payment_id' => $order->payment_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Recarregar o pedido para ter dados atualizados
        $order->refresh();

        \Log::info('PaymentController:status - Retornando status', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'payment_status' => $order->payment_status,
            'order_status' => $order->status,
            'payment_id' => $order->payment_id,
        ]);

        return response()->json([
            'success' => true,
            'order_id' => $order->id,
            'payment_status' => $order->payment_status,
            'status' => $order->status,
            'payment_id' => $order->payment_id,
        ]);
    }

    /**
     * Checkout para cartão (redireciona para Mercado Pago)
     * Permite escolher entre PIX e cartão (crédito/débito)
     */
    public function checkout(Request $request, Order $order)
    {
        try {
            $method = $request->get('method', 'credit_card');
            
            // Preparar itens para Mercado Pago
            $items = $order->items->map(function($item) {
                return [
                    "title" => $item->custom_name ?? ($item->product->name ?? 'Produto'),
                    "quantity" => (int)$item->quantity,
                    "unit_price" => (float)$item->unit_price,
                ];
            })->toArray();

            $mp = new MercadoPagoApi();

            // Usar createPaymentLinkFromOrder que permite PIX E cartão (crédito/débito)
            // Isso permite que o cliente escolha no link do Mercado Pago
            $customer = $order->customer;
            $res = $mp->createPaymentLinkFromOrder($order, $customer, $items);

            $initPoint = $res['checkout_url'] ?? null;
            
            if ($initPoint) {
                $order->preference_id = $res['preference_id'] ?? null;
                $order->payment_link = $initPoint;
                $order->payment_method = 'mercadopago'; // Será definido pelo cliente no link
                $order->payment_status = $order->payment_status ?: 'pending';
                $order->payment_raw_response = json_encode($res);
                $order->save();
                
                return redirect($initPoint);
            }

            \Log::error('PaymentController:checkout - Link de pagamento não gerado', [
                'order_id' => $order->id,
                'response' => $res,
            ]);
            
            // Redirecionar para checkout com mensagem de erro (sem usar back() para evitar CSRF)
            // Não passar order como parâmetro pois a rota não aceita
            return redirect()->route('pedido.checkout.index')
                ->with('error', 'Erro ao gerar link de pagamento. Por favor, tente novamente.');
        } catch (\Exception $e) {
            \Log::error('PaymentController:checkout - Erro ao gerar link de pagamento', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            // Não limpar a sessão em caso de erro - permite que o usuário tente novamente
            // Redirecionar para checkout com mensagem de erro (sem usar back() para evitar CSRF)
            // Não passar order como parâmetro pois a rota não aceita
            return redirect()->route('pedido.checkout.index')
                ->with('error', 'Erro ao gerar link de pagamento. Por favor, tente novamente.');
        }
    }

    /**
     * Página de sucesso do pagamento
     */
    public function success(Order $order)
    {
        // Marcar como pago se ainda não marcado e alimentar fidelidade
        // IMPORTANTE: Preservar notified_paid_at se já foi definido para evitar notificações duplicadas
        $wasAlreadyPaid = $order->payment_status === 'paid';
        
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
            // IMPORTANTE: Recarregar o pedido do banco para garantir que temos os valores corretos de cashback
            $order->refresh();
            
            try {
                \Log::info('PaymentController: Registrando cashback', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'cashback_used' => $order->cashback_used,
                    'cashback_earned' => $order->cashback_earned,
                    'customer_id' => $order->customer_id,
                ]);
                
                // Verificar se já existe transação de cashback para este pedido (evitar duplicatas)
                $existingCashback = \App\Models\CustomerCashback::where('order_id', $order->id)->first();
                if ($existingCashback) {
                    \Log::warning('PaymentController: Cashback já registrado para este pedido, pulando', [
                        'order_id' => $order->id,
                        'existing_cashback_id' => $existingCashback->id,
                    ]);
                } else {
                    // Débito: cashback usado
                    if ($order->cashback_used > 0) {
                        $debit = \App\Models\CustomerCashback::createDebit(
                            $order->customer_id,
                            $order->id,
                            $order->cashback_used,
                            "Uso de cashback no pedido #{$order->order_number}"
                        );
                        \Log::info('PaymentController: Débito de cashback criado', [
                            'debit_id' => $debit->id,
                            'amount' => $debit->amount,
                        ]);
                    }
                    
                    // Crédito: cashback ganho
                    if ($order->cashback_earned > 0) {
                        $credit = \App\Models\CustomerCashback::createCredit(
                            $order->customer_id,
                            $order->id,
                            $order->cashback_earned,
                            "Cashback do pedido #{$order->order_number}"
                        );
                        \Log::info('PaymentController: Crédito de cashback criado', [
                            'credit_id' => $credit->id,
                            'amount' => $credit->amount,
                        ]);
                    } else {
                        \Log::warning('PaymentController: Cashback ganho é zero ou nulo, não criando crédito', [
                            'order_id' => $order->id,
                            'cashback_earned' => $order->cashback_earned,
                        ]);
                    }
                }
            } catch (\Throwable $e) {
                \Log::error('Falha ao registrar cashback', [
                    'order_id' => $order->id,
                    'err' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
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
            
            // Atualizar estatísticas do cliente
            try {
                if ($order->customer) {
                    $order->customer->updateStatsAfterPaidOrder();
                }
            } catch (\Throwable $e) {
                \Log::warning('Falha ao atualizar estatísticas do cliente', ['order_id' => $order->id, 'err' => $e->getMessage()]);
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
                
                // Enviar notificação para o número específico quando pedido for pago
                if ($bot->isConfigured() && $order->payment_status === 'paid') {
                    try {
                        $notificationPhone = '+5571981750546'; // Número fixo para notificações
                        $message = "🆕 *NOVO PEDIDO PAGO!*\n\n";
                        $message .= "Pedido: #{$order->order_number}\n";
                        $message .= "Cliente: " . ($order->customer->name ?? 'N/A') . "\n";
                        $message .= "Valor: R$ " . number_format($order->final_amount ?? $order->total_amount ?? 0, 2, ',', '.') . "\n";
                        $message .= "Status: " . ($order->status ?? 'confirmed') . "\n\n";
                        $message .= "Acesse o dashboard para ver os detalhes.";
                        
                        $bot->sendTextMessage($notificationPhone, $message);
                        
                        \Log::info('PaymentController: Notificação enviada para número de administrador', [
                            'order_id' => $order->id,
                            'order_number' => $order->order_number,
                            'phone' => $notificationPhone
                        ]);
                    } catch (\Throwable $e) {
                        \Log::warning('PaymentController: Erro ao enviar notificação para administrador', [
                            'order_id' => $order->id,
                            'error' => $e->getMessage()
                        ]);
                    }
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

        // Limpar sessão quando o pagamento é confirmado para evitar refinalização
        session()->forget('cart');
        session()->forget('cart_count');
        
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
