<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Coupon;
use App\Models\OrderCoupon;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\MercadoPagoApi;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class OrdersController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['customer', 'address', 'payment'])
            ->orderBy('created_at', 'desc');

        // Busca por cliente ou número do pedido
        if ($request->has('q') && $request->q) {
            $search = $request->q;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($c) use ($search) {
                      $c->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        // Filtro por status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $orders = $query->paginate(20)->withQueryString();

        return view('dash.pages.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load([
            'customer',
            'address',
            'items.product',
            'payment',
            'orderDeliveryFee'
        ]);

        // Histórico de status
        $statusHistory = DB::table('order_status_history')
            ->where('order_id', $order->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Status disponíveis
        $availableStatuses = DB::table('order_statuses')
            ->where('active', 1)
            ->orderBy('id')
            ->get(['id', 'code', 'name']);

        // Cupons ativos e válidos
        // Filtrar cupons de frete grátis se o pedido não tiver entrega ou não tiver taxa de entrega
        $isDelivery = $order->delivery_type === 'delivery' || $order->address_id !== null;
        $hasDeliveryFee = ($order->delivery_fee ?? 0) > 0;
        
        $availableCoupons = Coupon::active()
            ->valid()
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'type', 'value', 'minimum_amount', 'description'])
            ->filter(function($coupon) use ($isDelivery, $hasDeliveryFee) {
                // Verificar se é cupom de frete grátis
                $isFreeDeliveryCoupon = stripos($coupon->name ?? '', 'frete') !== false && 
                                       (stripos($coupon->name ?? '', 'grátis') !== false || 
                                        stripos($coupon->name ?? '', 'gratis') !== false ||
                                        stripos($coupon->description ?? '', 'frete grátis') !== false ||
                                        stripos($coupon->description ?? '', 'frete gratis') !== false);
                
                // Se for cupom de frete grátis, só mostrar se o pedido tiver entrega E tiver taxa de entrega > 0
                if ($isFreeDeliveryCoupon) {
                    return $isDelivery && $hasDeliveryFee;
                }
                
                // Outros cupons sempre disponíveis
                return true;
            })
            ->values();

        // Produtos disponíveis para adicionar ao pedido
        $availableProducts = Product::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'price']);

        // Buscar configurações do sistema
        $settings = \App\Models\Setting::getSettings();

        return view('dash.pages.orders.show', compact(
            'order',
            'statusHistory',
            'availableStatuses',
            'availableCoupons',
            'availableProducts',
            'settings'
        ));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|string',
            'note' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $oldStatus = $order->status;
            $order->status = $request->status;
            $order->save();

            // Registrar no histórico
            DB::table('order_status_history')->insert([
                'order_id' => $order->id,
                'old_status' => $oldStatus,
                'new_status' => $request->status,
                'note' => $request->note,
                'user_id' => auth()->check() ? auth()->id() : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Status do pedido atualizado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Erro ao atualizar status: ' . $e->getMessage());
        }
    }

    public function update(Request $request, Order $order)
    {
        $request->validate([
            'notes' => 'nullable|string',
            'observations' => 'nullable|string',
            'delivery_instructions' => 'nullable|string',
            'create_payment' => 'nullable|boolean',
            'payment_method' => 'nullable|in:pix,credit_card,debit_card',
            'send_whatsapp' => 'nullable|boolean',
            'whatsapp_message' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            // Atualizar informações do pedido
            $order->update($request->only(['notes', 'observations', 'delivery_instructions']));

            $paymentLink = null;
            $pixCode = null;

            // Criar cobrança se solicitado
            if ($request->has('create_payment') && $request->create_payment) {
                $paymentMethod = $request->payment_method ?? 'pix';
                
                if ($paymentMethod === 'pix') {
                    // Criar PIX via Mercado Pago
                    $pixData = $this->createPixPayment($order);
                    if ($pixData && isset($pixData['qr_code'])) {
                        $paymentLink = route('payment.pix', ['order' => $order->id]);
                        
                        // Atualizar pedido com dados do PIX
                        $order->update([
                            'payment_method' => 'pix',
                            'payment_id' => $pixData['payment_id'] ?? null,
                            'pix_copy_paste' => $pixData['qr_code'] ?? null,
                            'pix_qr_base64' => $pixData['qr_code_base64'] ?? null,
                            'pix_expires_at' => isset($pixData['expires_at']) ? $pixData['expires_at'] : now()->addMinutes(30),
                            'payment_status' => 'pending',
                            'payment_link' => $paymentLink,
                        ]);
                    }
                } else {
                    // Criar link de pagamento para cartão via Mercado Pago
                    $paymentLink = $this->createCardPaymentLink($order, $paymentMethod);
                    if ($paymentLink) {
                        $order->update([
                            'payment_method' => $paymentMethod,
                            'payment_status' => 'pending',
                            'payment_link' => $paymentLink,
                        ]);
                    }
                }
            }

            // Enviar WhatsApp se solicitado
            if ($request->has('send_whatsapp') && $request->send_whatsapp) {
                $this->sendOrderUpdateWhatsApp($order, $request->whatsapp_message, $paymentLink);
            }

            DB::commit();

            $message = 'Pedido atualizado com sucesso!';
            if ($paymentLink) {
                $message .= ' Link de pagamento gerado.';
            }
            if ($request->has('send_whatsapp') && $request->send_whatsapp) {
                $message .= ' Mensagem enviada via WhatsApp.';
            }

            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Erro ao atualizar pedido e criar cobrança: " . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao processar: ' . $e->getMessage());
        }
    }

    /**
     * Cria pagamento PIX via Mercado Pago
     */
    private function createPixPayment(Order $order)
    {
        try {
            $mpApi = new MercadoPagoApi();
            
            // Construir descrição detalhada
            $description = $this->buildDetailedDescription($order);
            
            // Preparar dados do pedido com descrição completa
            $orderData = [
                'number' => $order->order_number,
                'total' => floatval($order->final_amount ?? $order->total_amount),
                'description' => $description,
                'items' => $order->items->map(function($item) {
                    return [
                        'title' => !$item->product_id && $item->custom_name 
                            ? 'Item Avulso - ' . $item->custom_name 
                            : ($item->custom_name ?? ($item->product->name ?? 'Produto')),
                        'quantity' => $item->quantity,
                        'unit_price' => floatval($item->unit_price),
                    ];
                })->toArray(),
            ];
            
            $payer = [
                'email' => $order->customer->email ?? 'cliente@email.com',
                'first_name' => explode(' ', $order->customer->name ?? 'Cliente')[0],
                'last_name' => (explode(' ', $order->customer->name ?? 'Cliente', 2)[1] ?? ''),
            ];
            
            $result = $mpApi->createPixPayment($orderData, $payer);
            
            if ($result && isset($result['ok']) && $result['ok']) {
                return [
                    'qr_code' => $result['qr_code'] ?? null,
                    'qr_code_base64' => $result['qr_code_base64'] ?? null,
                    'expires_at' => now()->addMinutes(30), // Mercado Pago geralmente expira em 30 minutos
                    'payment_id' => $result['id'] ?? null,
                ];
            }

            throw new \Exception('Falha ao criar pagamento PIX');
        } catch (\Exception $e) {
            Log::error("Erro ao criar PIX: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Cria link de pagamento para cartão
     */
    private function createCardPaymentLink(Order $order, string $method)
    {
        try {
            $mpApi = new MercadoPagoApi();
            
            // Preparar dados do pedido com descrição detalhada
            $description = $this->buildDetailedDescription($order);
            
            $orderData = [
                'number' => $order->order_number,
                'total' => floatval($order->final_amount ?? $order->total_amount),
                'description' => $description,
                'items' => $order->items->map(function($item) {
                    return [
                        'title' => !$item->product_id && $item->custom_name 
                            ? 'Item Avulso - ' . $item->custom_name 
                            : ($item->custom_name ?? ($item->product->name ?? 'Produto')),
                        'quantity' => $item->quantity,
                        'unit_price' => floatval($item->unit_price),
                    ];
                })->toArray(),
                'notification_url' => route('webhooks.mercadopago'),
            ];
            
            $payer = [
                'email' => $order->customer->email ?? 'cliente@email.com',
                'first_name' => explode(' ', $order->customer->name ?? 'Cliente')[0],
                'last_name' => (explode(' ', $order->customer->name ?? 'Cliente', 2)[1] ?? ''),
            ];
            
            $result = $mpApi->createPaymentLink($orderData, $payer);
            
            if ($result && isset($result['init_point'])) {
                return $result['init_point'];
            }
            
            // Fallback: retorna rota local que redireciona para Mercado Pago
            return route('payment.checkout', ['order' => $order->id, 'method' => $method]);
        } catch (\Exception $e) {
            Log::error("Erro ao criar link de pagamento: " . $e->getMessage());
            // Fallback
            return route('payment.checkout', ['order' => $order->id, 'method' => $method]);
        }
    }

    /**
     * Constrói descrição detalhada do pedido para cobrança
     */
    private function buildDetailedDescription(Order $order): string
    {
        $description = "Pedido #{$order->order_number} - OLIKA\n\n";
        
        // Itens do pedido
        $description .= "📦 ITENS:\n";
        foreach ($order->items as $item) {
            $itemName = !$item->product_id && $item->custom_name 
                ? 'Item Avulso - ' . $item->custom_name 
                : ($item->custom_name ?? ($item->product->name ?? 'Produto'));
            $description .= "• {$item->quantity}x {$itemName} - R$ " . number_format($item->unit_price, 2, ',', '.') . " un.\n";
        }
        
        // Subtotal
        $subtotal = $order->total_amount ?? 0;
        $description .= "\n💰 SUBTOTAL: R$ " . number_format($subtotal, 2, ',', '.') . "\n";
        
        // Taxa de entrega (se houver)
        if ($order->delivery_fee > 0) {
            $description .= "🚚 TAXA DE ENTREGA: R$ " . number_format($order->delivery_fee, 2, ',', '.') . "\n";
        }
        
        // Descontos
        if ($order->discount_amount > 0) {
            if ($order->coupon_code) {
                $description .= "🎟️ DESCONTO (Cupom {$order->coupon_code}): -R$ " . number_format($order->discount_amount, 2, ',', '.') . "\n";
            } else {
                $description .= "🎟️ DESCONTO APLICADO: -R$ " . number_format($order->discount_amount, 2, ',', '.') . "\n";
            }
        }
        
        // Total
        $total = $order->final_amount ?? $order->total_amount ?? 0;
        $description .= "\n✅ TOTAL: R$ " . number_format($total, 2, ',', '.') . "\n";
        
        return $description;
    }

    /**
     * Envia notificação WhatsApp sobre atualização do pedido
     */
    private function sendOrderUpdateWhatsApp(Order $order, ?string $customMessage = null, ?string $paymentLink = null)
    {
        try {
            $customer = $order->customer;
            
            if (!$customer || !$customer->phone) {
                throw new \Exception('Cliente sem telefone cadastrado');
            }

            $whatsappService = new WhatsAppService();

            // Mensagem padrão ou personalizada
            if ($customMessage) {
                $message = $customMessage;
            } else {
                $message = "Olá {$customer->name}! 👋\n\n";
                $message .= "Seu pedido *{$order->order_number}* foi atualizado.\n\n";
                $message .= "📦 *Resumo do pedido:*\n";
                foreach ($order->items as $item) {
                    $productName = !$item->product_id && $item->custom_name 
                        ? 'Item Avulso - ' . $item->custom_name 
                        : ($item->custom_name ?? ($item->product->name ?? 'Produto'));
                    $message .= "• {$item->quantity}x {$productName}";
                    if ($item->special_instructions) {
                        $message .= " ({$item->special_instructions})";
                    }
                    $message .= "\n";
                }
                $message .= "\n💰 *Total: R$ " . number_format($order->final_amount ?? $order->total_amount, 2, ',', '.') . "*\n\n";
                
                if ($paymentLink) {
                    $message .= "💳 Para efetuar o pagamento, acesse:\n{$paymentLink}\n\n";
                    if (str_contains($paymentLink, 'pix')) {
                        $message .= "📱 Ou copie e cole a chave PIX diretamente no app do seu banco!";
                    }
                } else {
                    $message .= "Aguardando pagamento.";
                }
            }

            // Adicionar link de pagamento se existir e não estiver na mensagem
            if ($paymentLink && !str_contains($message, $paymentLink)) {
                $message .= "\n\n🔗 Link de pagamento: {$paymentLink}";
            }

            // Enviar via WhatsApp
            $result = $whatsappService->sendText($customer->phone, $message);
            
            if (!$result) {
                Log::warning("Falha ao enviar WhatsApp para {$customer->phone}");
            } else {
                Log::info("WhatsApp enviado com sucesso para {$customer->phone}");
            }
            
            return true;
        } catch (\Exception $e) {
            Log::error("Erro ao enviar WhatsApp: " . $e->getMessage());
            return false;
        }
    }

    public function applyCoupon(Request $request, Order $order)
    {
        $request->validate([
            'coupon_code' => 'required|string|exists:coupons,code',
        ]);

        try {
            DB::beginTransaction();

            $coupon = Coupon::where('code', $request->coupon_code)->first();

            if (!$coupon) {
                return redirect()->back()->with('error', 'Cupom não encontrado.');
            }

            // Validar cupom
            if (!$coupon->isValid($order->customer_id)) {
                \Log::warning('Cupom inválido', [
                    'coupon_code' => $coupon->code,
                    'order_id' => $order->id,
                    'customer_id' => $order->customer_id,
                ]);
                return redirect()->back()->with('error', 'Cupom inválido ou expirado.');
            }

            try {
                if (!$coupon->canBeUsedBy($order->customer_id)) {
                    \Log::warning('Limite de uso do cupom atingido', [
                        'coupon_code' => $coupon->code,
                        'order_id' => $order->id,
                        'customer_id' => $order->customer_id,
                    ]);
                    return redirect()->back()->with('error', 'Você já atingiu o limite de uso deste cupom.');
                }
            } catch (\Exception $e) {
                // Se houver erro na verificação (tabela não existe), apenas logar e continuar
                \Log::warning('Erro ao verificar canBeUsedBy, continuando...', [
                    'coupon_code' => $coupon->code,
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                    'trace' => substr($e->getTraceAsString(), 0, 500),
                ]);
                // Continuar com a aplicação do cupom mesmo se a verificação falhar
            }

            if ($coupon->minimum_amount && ($order->total_amount ?? 0) < $coupon->minimum_amount) {
                return redirect()->back()->with('error', "Valor mínimo para usar este cupom: R$ " . number_format($coupon->minimum_amount, 2, ',', '.'));
            }

            // Verificar se é cupom de frete grátis
            $isFreeDeliveryCoupon = stripos($coupon->name ?? '', 'frete') !== false && 
                                   (stripos($coupon->name ?? '', 'grátis') !== false || 
                                    stripos($coupon->name ?? '', 'gratis') !== false || 
                                    stripos($coupon->description ?? '', 'frete grátis') !== false ||
                                    stripos($coupon->description ?? '', 'frete gratis') !== false);

            // Se for cupom de frete grátis, validar se o pedido tem entrega
            if ($isFreeDeliveryCoupon) {
                // Verificar se tem entrega: se tem endereço de entrega E não é retirada
                // E se a taxa de entrega atual é > 0 (caso contrário não faz sentido aplicar cupom de frete grátis)
                $isDelivery = $order->delivery_type === 'delivery' || $order->address_id !== null;
                $hasDeliveryFee = ($order->delivery_fee ?? 0) > 0;
                
                if (!$isDelivery || !$hasDeliveryFee) {
                    DB::rollBack();
                    \Log::warning('Tentativa de aplicar cupom de frete grátis em pedido sem entrega ou sem taxa de entrega', [
                        'coupon_code' => $coupon->code,
                        'order_id' => $order->id,
                        'delivery_type' => $order->delivery_type,
                        'address_id' => $order->address_id,
                        'delivery_fee' => $order->delivery_fee,
                    ]);
                    return redirect()->back()->with('error', 'Este cupom de frete grátis não é válido para pedidos sem entrega ou sem taxa de entrega.');
                }
            }

            // Calcular desconto
            // IMPORTANTE: Cupons e descontos NÃO afetam a taxa de entrega
            // A menos que seja um cupom explicitamente de "frete grátis"
            $discount = $coupon->calculateDiscount($order->total_amount ?? 0);

            // Se for cupom de frete grátis, zerar taxa de entrega
            $deliveryFee = $order->delivery_fee ?? 0;
            if ($isFreeDeliveryCoupon && $deliveryFee > 0) {
                $discount += $deliveryFee; // Adicionar taxa de entrega ao desconto
                $deliveryFee = 0;
                $order->delivery_fee = 0;
            }

            // Atualizar pedido
            // Cálculo: Subtotal + Taxa de Entrega - Desconto = Total Final
            // O desconto é aplicado APENAS sobre o subtotal (NÃO inclui taxa de entrega)
            // Exceto quando for cupom de frete grátis
            $order->coupon_code = $coupon->code;
            $order->discount_amount = $discount;
            $order->discount_type = 'coupon'; // Indicar que é cupom
            $order->discount_original_value = $coupon->value; // Salvar valor original do cupom
            $order->final_amount = ($order->total_amount ?? 0) + $deliveryFee - $discount;
            $order->save();

            // Incrementar contador de uso
            $coupon->increment('used_count');

            // Registrar uso do cupom no pedido (se a tabela existir)
            try {
                if (Schema::hasTable('order_coupons')) {
                    OrderCoupon::create([
                        'order_id' => $order->id,
                        'code' => $coupon->code,
                        'type' => $coupon->type,
                        'value' => $coupon->value,
                        'meta' => $coupon->toArray(), // Salva todos os dados do cupom no momento do uso
                    ]);
                }
            } catch (\Exception $e) {
                // Se a tabela não existir, apenas logar e continuar
                \Log::warning('Erro ao criar OrderCoupon, continuando...', [
                    'coupon_code' => $coupon->code,
                    'error' => $e->getMessage(),
                ]);
            }

            DB::commit();

            return redirect()->back()->with('success', "Cupom aplicado com desconto de " . $coupon->formatted_value);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Erro ao aplicar cupom: ' . $e->getMessage());
        }
    }

    public function removeCoupon(Order $order)
    {
        try {
            DB::beginTransaction();

            // Recuperar cupom se existir
            if ($order->coupon_code) {
                $couponCode = $order->coupon_code; // Salvar código antes de limpar
                $coupon = Coupon::where('code', $couponCode)->first();
                
                // Remover registro do cupom do pedido antes de limpar o código (se a tabela existir)
                try {
                    if (Schema::hasTable('order_coupons')) {
                        OrderCoupon::where('order_id', $order->id)
                            ->where('code', $couponCode)
                            ->delete();
                    }
                } catch (\Exception $e) {
                    // Se a tabela não existir, apenas logar e continuar
                    \Log::warning('Erro ao remover OrderCoupon, continuando...', [
                        'error' => $e->getMessage(),
                    ]);
                }
                
                // Reverter desconto
                $order->coupon_code = null;
                $order->discount_amount = 0;
                $order->final_amount = ($order->total_amount ?? 0) + ($order->delivery_fee ?? 0);
                $order->save();

                // Decrementar contador se existir
                if ($coupon) {
                    $coupon->decrement('used_count');
                }
            }

            DB::commit();

            return redirect()->back()->with('success', 'Cupom removido com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Erro ao remover cupom: ' . $e->getMessage());
        }
    }

    public function adjustDeliveryFee(Request $request, Order $order)
    {
        $request->validate([
            'delivery_fee' => 'required|numeric|min:0',
            'reason' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $oldFee = $order->delivery_fee ?? 0;
            $newFee = $request->delivery_fee;
            
            // Se a nova taxa é 0 e há um cupom aplicado, verificar se é cupom de frete grátis
            if ($newFee == 0 && $order->coupon_code) {
                $coupon = Coupon::where('code', $order->coupon_code)->first();
                if ($coupon) {
                    // Verificar se é cupom de frete grátis
                    $isFreeDeliveryCoupon = stripos($coupon->name ?? '', 'frete') !== false && 
                                           (stripos($coupon->name ?? '', 'grátis') !== false || 
                                            stripos($coupon->name ?? '', 'gratis') !== false ||
                                            stripos($coupon->description ?? '', 'frete grátis') !== false ||
                                            stripos($coupon->description ?? '', 'frete gratis') !== false);
                    
                    // Se for cupom de frete grátis, removê-lo automaticamente
                    if ($isFreeDeliveryCoupon) {
                        \Log::info('Removendo cupom de frete grátis automaticamente ao zerar taxa de entrega', [
                            'order_id' => $order->id,
                            'coupon_code' => $coupon->code,
                        ]);
                        
                        // Remover registro do cupom do pedido (se a tabela existir)
                        try {
                            if (Schema::hasTable('order_coupons')) {
                                OrderCoupon::where('order_id', $order->id)
                                    ->where('code', $order->coupon_code)
                                    ->delete();
                            }
                        } catch (\Exception $e) {
                            \Log::warning('Erro ao remover OrderCoupon ao ajustar taxa, continuando...', [
                                'error' => $e->getMessage(),
                            ]);
                        }
                        
                        // Limpar cupom e recálculo do desconto
                        $couponCode = $order->coupon_code;
                        $order->coupon_code = null;
                        $order->discount_amount = 0;
                        $coupon->decrement('used_count');
                    }
                }
            }
            
            $order->delivery_fee = $newFee;
            $totalAmount = $order->total_amount ?? 0;
            $discountAmount = $order->discount_amount ?? 0;
            $order->final_amount = $totalAmount + $order->delivery_fee - $discountAmount;
            $order->save();

            // Atualizar ou criar registro de ajuste manual
            $deliveryFee = $order->orderDeliveryFee;
            if (!$deliveryFee) {
                $deliveryFee = new \App\Models\OrderDeliveryFee();
                $deliveryFee->order_id = $order->id;
                $deliveryFee->calculated_fee = $oldFee;
                $deliveryFee->order_value = $order->total_amount;
            } else {
                $deliveryFee->calculated_fee = $oldFee;
            }

            $deliveryFee->final_fee = $request->delivery_fee;
            $deliveryFee->is_manual_adjustment = true;
            $deliveryFee->adjustment_reason = $request->reason;
            $deliveryFee->adjusted_by = auth()->check() ? (auth()->user()->name ?? 'Sistema') : 'Sistema';
            $deliveryFee->save();

            DB::commit();

            return redirect()->back()->with('success', 'Taxa de entrega ajustada com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Erro ao ajustar taxa de entrega: ' . $e->getMessage());
        }
    }

    public function applyDiscount(Request $request, Order $order)
    {
        $request->validate([
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $discount = 0;
            
            $totalAmount = $order->total_amount ?? 0;
            $deliveryFee = $order->delivery_fee ?? 0;
            
            // Calcular desconto do cupom se existir
            $couponDiscount = 0;
            if ($order->coupon_code) {
                $coupon = Coupon::where('code', $order->coupon_code)->first();
                if ($coupon) {
                    $couponDiscount = $coupon->calculateDiscount($totalAmount);
                }
            }

            // Calcular novo desconto manual
            if ($request->discount_type === 'percentage') {
                $discount = ($totalAmount * $request->discount_value) / 100;
                $discount = min($discount, $totalAmount); // Não pode ser maior que o total
            } else {
                $discount = min($request->discount_value, $totalAmount);
            }

            // Total de desconto = desconto do cupom + desconto manual
            $totalDiscount = $couponDiscount + $discount;
            
            $order->discount_amount = $totalDiscount;
            $order->manual_discount_type = $request->discount_type; // Salvar tipo do desconto manual
            $order->manual_discount_value = $request->discount_value; // Salvar valor do desconto manual
            $order->final_amount = $totalAmount + $deliveryFee - $totalDiscount;
            $order->save();

            DB::commit();

            $discountLabel = $request->discount_type === 'percentage' 
                ? number_format($request->discount_value, 2) . '%' 
                : 'R$ ' . number_format($request->discount_value, 2, ',', '.');

            return redirect()->back()->with('success', "Desconto de {$discountLabel} aplicado com sucesso!");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Erro ao aplicar desconto: ' . $e->getMessage());
        }
    }

    public function removeDiscount(Order $order)
    {
        try {
            DB::beginTransaction();

            $totalAmount = $order->total_amount ?? 0;
            $deliveryFee = $order->delivery_fee ?? 0;
            
            // Calcular desconto do cupom se existir
            $couponDiscount = 0;
            if ($order->coupon_code) {
                $coupon = Coupon::where('code', $order->coupon_code)->first();
                if ($coupon) {
                    $couponDiscount = $coupon->calculateDiscount($totalAmount);
                }
            }

            // Remover apenas o desconto manual, manter o cupom
            $order->discount_amount = $couponDiscount;
            $order->manual_discount_type = null;
            $order->manual_discount_value = null;
            $order->final_amount = $totalAmount + $deliveryFee - $couponDiscount;
            $order->save();

            DB::commit();

            return redirect()->back()->with('success', 'Desconto manual removido com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Erro ao remover desconto: ' . $e->getMessage());
        }
    }

    /**
     * Atualizar quantidade de um item do pedido
     */
    public function updateItemQuantity(Request $request, Order $order, OrderItem $item)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        try {
            DB::beginTransaction();

            $item->quantity = $request->quantity;
            $item->total_price = $item->unit_price * $request->quantity;
            $item->save();

            $this->recalculateOrderTotals($order);

            DB::commit();

            return redirect()->back()->with('success', 'Quantidade do item atualizada com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Erro ao atualizar quantidade: ' . $e->getMessage());
        }
    }

    /**
     * Atualizar quantidade de um item via AJAX
     */
    public function updateItemQuantityAjax(Request $request, Order $order, OrderItem $item)
    {
        try {
            DB::beginTransaction();

            $delta = (int) $request->get('delta', 0);
            $newQuantity = $item->quantity + $delta;

            if ($newQuantity <= 0) {
                $item->delete();
                $removed = true;
            } else {
                $item->quantity = $newQuantity;
                $item->total_price = $item->unit_price * $newQuantity;
                $item->save();
                $removed = false;
            }

            $this->recalculateOrderTotals($order);
            $order->refresh();

            DB::commit();

            return response()->json([
                'success' => true,
                'removed' => $removed,
                'item' => $removed ? null : [
                    'id' => $item->id,
                    'quantity' => $item->quantity,
                    'total_price' => number_format($item->total_price, 2, ',', '.'),
                ],
                'order' => [
                    'total_amount' => number_format($order->total_amount ?? 0, 2, ',', '.'),
                    'delivery_fee' => number_format($order->delivery_fee ?? 0, 2, ',', '.'),
                    'discount_amount' => number_format($order->discount_amount ?? 0, 2, ',', '.'),
                    'final_amount' => number_format($order->final_amount ?? 0, 2, ',', '.'),
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Erro ao atualizar quantidade: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Erro ao atualizar quantidade: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Adicionar quantidade a um item existente
     */
    public function addItemQuantity(Order $order, OrderItem $item)
    {
        try {
            DB::beginTransaction();

            $item->quantity += 1;
            $item->total_price = $item->unit_price * $item->quantity;
            $item->save();

            $this->recalculateOrderTotals($order);

            DB::commit();

            if (request()->wantsJson() || request()->ajax()) {
                $order->refresh();
                return response()->json([
                    'success' => true,
                    'item' => [
                        'id' => $item->id,
                        'quantity' => $item->quantity,
                        'total_price' => number_format($item->total_price, 2, ',', '.'),
                    ],
                    'order' => [
                        'total_amount' => number_format($order->total_amount ?? 0, 2, ',', '.'),
                        'delivery_fee' => number_format($order->delivery_fee ?? 0, 2, ',', '.'),
                        'discount_amount' => number_format($order->discount_amount ?? 0, 2, ',', '.'),
                        'final_amount' => number_format($order->final_amount ?? 0, 2, ',', '.'),
                    ],
                ]);
            }

            return redirect()->back()->with('success', 'Quantidade aumentada com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Erro ao aumentar quantidade: ' . $e->getMessage(),
                ], 500);
            }
            return redirect()->back()->with('error', 'Erro ao aumentar quantidade: ' . $e->getMessage());
        }
    }

    /**
     * Reduzir quantidade de um item (remove 1 unidade)
     */
    public function reduceItemQuantity(Order $order, OrderItem $item)
    {
        try {
            DB::beginTransaction();

            $removed = false;
            if ($item->quantity > 1) {
                $item->quantity -= 1;
                $item->total_price = $item->unit_price * $item->quantity;
                $item->save();
            } else {
                // Se for a última unidade, remove o item completamente
                $item->delete();
                $removed = true;
            }

            $this->recalculateOrderTotals($order);
            $order->refresh();

            DB::commit();

            if (request()->wantsJson() || request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'removed' => $removed,
                    'item_id' => $removed ? $item->id : null,
                    'item' => $removed ? null : [
                        'id' => $item->id,
                        'quantity' => $item->quantity,
                        'total_price' => number_format($item->total_price, 2, ',', '.'),
                    ],
                    'order' => [
                        'total_amount' => number_format($order->total_amount ?? 0, 2, ',', '.'),
                        'delivery_fee' => number_format($order->delivery_fee ?? 0, 2, ',', '.'),
                        'discount_amount' => number_format($order->discount_amount ?? 0, 2, ',', '.'),
                        'final_amount' => number_format($order->final_amount ?? 0, 2, ',', '.'),
                    ],
                ]);
            }

            return redirect()->back()->with('success', 'Quantidade reduzida com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Erro ao reduzir quantidade: ' . $e->getMessage(),
                ], 500);
            }
            return redirect()->back()->with('error', 'Erro ao reduzir quantidade: ' . $e->getMessage());
        }
    }

    /**
     * Remover item completamente do pedido
     */
    public function removeItem(Order $order, OrderItem $item)
    {
        try {
            DB::beginTransaction();

            $itemId = $item->id;
            $item->delete();

            $this->recalculateOrderTotals($order);
            $order->refresh();

            DB::commit();

            if (request()->wantsJson() || request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'removed' => true,
                    'item_id' => $itemId,
                    'order' => [
                        'total_amount' => number_format($order->total_amount ?? 0, 2, ',', '.'),
                        'delivery_fee' => number_format($order->delivery_fee ?? 0, 2, ',', '.'),
                        'discount_amount' => number_format($order->discount_amount ?? 0, 2, ',', '.'),
                        'final_amount' => number_format($order->final_amount ?? 0, 2, ',', '.'),
                    ],
                ]);
            }

            return redirect()->back()->with('success', 'Item removido do pedido com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Erro ao remover item: ' . $e->getMessage(),
                ], 500);
            }
            return redirect()->back()->with('error', 'Erro ao remover item: ' . $e->getMessage());
        }
    }

    /**
     * Adicionar novo item ao pedido
     */
    public function addItem(Request $request, Order $order)
    {
        try {
            // Debug: Log todos os dados recebidos com mais detalhes
            $allInput = $request->all();
            $customName = $request->input('custom_name');
            $unitPrice = $request->input('unit_price');
            
            \Log::info('=== ADD ITEM - INÍCIO ===', [
                'order_id' => $order->id,
                'all_input' => $allInput,
                'all_input_keys' => array_keys($allInput),
                'product_id' => $request->input('product_id'),
                'product_id_type' => gettype($request->input('product_id')),
                'custom_name' => $customName,
                'custom_name_type' => gettype($customName),
                'custom_name_empty' => empty($customName),
                'custom_name_null' => is_null($customName),
                'custom_name_raw' => $request->get('custom_name'),
                'unit_price' => $unitPrice,
                'unit_price_type' => gettype($unitPrice),
                'unit_price_empty' => empty($unitPrice),
                'unit_price_null' => is_null($unitPrice),
                'unit_price_raw' => $request->get('unit_price'),
                'quantity' => $request->input('quantity'),
                'quantity_type' => gettype($request->input('quantity')),
                'request_method' => $request->method(),
                'is_ajax' => $request->ajax(),
                'is_json' => $request->wantsJson(),
                'content_type' => $request->header('Content-Type'),
                'has_custom_name' => $request->has('custom_name'),
                'has_unit_price' => $request->has('unit_price'),
            ]);
            
            // Verificar se é item avulso - produto_id vazio, null, string vazia, ou 'loose_item'
            $productId = $request->input('product_id');
            // Considerar item avulso se: vazio, null, string vazia, 'loose_item', ou não é numérico
            $isLooseItem = empty($productId) || 
                          $productId === '' || 
                          $productId === 'loose_item' || 
                          $productId === null || 
                          $productId === 'null' ||
                          (!is_numeric($productId) && $productId !== '0');
            
            \Log::info('=== ADD ITEM - TIPO IDENTIFICADO ===', [
                'isLooseItem' => $isLooseItem,
                'productId' => $productId,
                'productId_type' => gettype($productId),
            ]);
            
            // Validação baseada no tipo de item
            if ($isLooseItem) {
                // Validação para item avulso
                $request->validate([
                    'custom_name' => 'required|string|max:255',
                    'quantity' => 'required|integer|min:1',
                    'unit_price' => 'required|numeric|min:0.01',
                    'special_instructions' => 'nullable|string|max:500',
                ], [
                    'custom_name.required' => 'O nome do item é obrigatório.',
                    'unit_price.required' => 'O valor do item é obrigatório.',
                    'unit_price.numeric' => 'O valor deve ser um número válido.',
                    'unit_price.min' => 'O valor deve ser maior que zero.',
                    'quantity.required' => 'A quantidade é obrigatória.',
                    'quantity.integer' => 'A quantidade deve ser um número inteiro.',
                    'quantity.min' => 'A quantidade deve ser pelo menos 1.',
                ]);
            } else {
                // Validação para produto normal
                $request->validate([
                    'product_id' => 'required|exists:products,id',
                    'quantity' => 'required|integer|min:1',
                    'custom_name' => 'nullable|string|max:255',
                    'special_instructions' => 'nullable|string|max:500',
                    'unit_price' => 'nullable|numeric|min:0',
                ]);
            }

            DB::beginTransaction();

            if ($isLooseItem) {
                // Item avulso - não tem product_id
                $unitPrice = floatval($request->unit_price);
                $quantity = intval($request->quantity);
                $totalPrice = $unitPrice * $quantity;

                // Verificar se já existe um item avulso idêntico
                $existingItem = OrderItem::where('order_id', $order->id)
                    ->whereNull('product_id')
                    ->where('custom_name', $request->custom_name)
                    ->where('unit_price', $unitPrice)
                    ->first();

                if ($existingItem) {
                    // Se já existe, apenas aumenta a quantidade
                    $existingItem->quantity += $quantity;
                    $existingItem->total_price = $existingItem->unit_price * $existingItem->quantity;
                    if ($request->special_instructions) {
                        $existingItem->special_instructions = $request->special_instructions;
                    }
                    $existingItem->save();
                } else {
                    // Cria novo item avulso
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => null, // Item avulso não tem product_id
                        'custom_name' => $request->custom_name,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'total_price' => $totalPrice,
                        'special_instructions' => $request->special_instructions ?? null,
                    ]);
                }
            } else {
                // Produto normal
                $product = Product::findOrFail($request->product_id);
                $unitPrice = $request->unit_price ?? $product->price;
                $totalPrice = $unitPrice * $request->quantity;

                // Verificar se já existe um item igual no pedido (mesmo produto e mesmo preço)
                $existingItem = OrderItem::where('order_id', $order->id)
                    ->where('product_id', $product->id)
                    ->where('unit_price', $unitPrice)
                    ->first();

                if ($existingItem) {
                    // Se já existe, apenas aumenta a quantidade
                    $existingItem->quantity += $request->quantity;
                    $existingItem->total_price = $existingItem->unit_price * $existingItem->quantity;
                    
                    // Atualiza observações se fornecido
                    if ($request->special_instructions) {
                        $existingItem->special_instructions = $request->special_instructions;
                    }
                    if ($request->custom_name) {
                        $existingItem->custom_name = $request->custom_name;
                    }
                    
                    $existingItem->save();
                } else {
                    // Cria novo item
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'custom_name' => $request->custom_name ?? null,
                        'quantity' => $request->quantity,
                        'unit_price' => $unitPrice,
                        'total_price' => $totalPrice,
                        'special_instructions' => $request->special_instructions ?? null,
                    ]);
                }
            }

            $this->recalculateOrderTotals($order);

            DB::commit();

            if (request()->wantsJson() || request()->ajax()) {
                $order->refresh();
                return response()->json([
                    'success' => true,
                    'message' => 'Item adicionado com sucesso!',
                    'order' => [
                        'total_amount' => number_format($order->total_amount ?? 0, 2, ',', '.'),
                        'delivery_fee' => number_format($order->delivery_fee ?? 0, 2, ',', '.'),
                        'discount_amount' => number_format($order->discount_amount ?? 0, 2, ',', '.'),
                        'final_amount' => number_format($order->final_amount ?? 0, 2, ',', '.'),
                    ],
                ]);
            }

            \Log::info('=== ADD ITEM - SUCESSO ===', [
                'order_id' => $order->id,
                'isLooseItem' => $isLooseItem ?? false,
            ]);

            return redirect()->back()->with('success', 'Item adicionado ao pedido com sucesso!');
        } catch (ValidationException $e) {
            DB::rollBack();
            \Log::error('=== ADD ITEM - ERRO DE VALIDAÇÃO ===', [
                'order_id' => $order->id,
                'errors' => $e->errors(),
                'message' => $e->getMessage(),
            ]);
            
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => $e->errors(),
                    'error' => $e->getMessage(),
                ], 422);
            }
            
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('=== ADD ITEM - ERRO GERAL ===', [
                'order_id' => $order->id,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => substr($e->getTraceAsString(), 0, 1000),
            ]);
            
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Erro ao adicionar item: ' . $e->getMessage(),
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Erro ao adicionar item: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Exibe recibo/resumo do pedido
     */
    public function receipt(Order $order)
    {
        $order->load([
            'customer',
            'address',
            'items.product',
            'payment',
            'orderDeliveryFee'
        ]);
        
        // Histórico de status
        $statusHistory = DB::table('order_status_history')
            ->where('order_id', $order->id)
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Buscar configurações do sistema
        $settings = \App\Models\Setting::getSettings();
        
        // Se for requisição AJAX, retornar apenas o conteúdo HTML do modal
        if (request()->ajax() || request()->wantsJson()) {
            return view('dash.pages.orders.receipt-modal', compact('order', 'statusHistory', 'settings'));
        }
        
        return view('dash.pages.orders.receipt', compact('order', 'statusHistory', 'settings'));
    }

    /**
     * Recalcula os totais do pedido baseado nos itens
     */
    private function recalculateOrderTotals(Order $order)
    {
        // Recalcular total dos itens
        $itemsTotal = $order->items()->sum('total_price');
        
        $order->total_amount = $itemsTotal;
        
        // Recalcular valor final considerando desconto e entrega
        $deliveryFee = $order->delivery_fee ?? 0;
        $discountAmount = $order->discount_amount ?? 0;
        
        $order->final_amount = $itemsTotal + $deliveryFee - $discountAmount;
        
        // Garantir que o valor final não seja negativo
        if ($order->final_amount < 0) {
            $order->final_amount = 0;
        }
        
        $order->save();
    }
}
