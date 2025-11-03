<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Customer;
use App\Models\Address;
use App\Models\DeliverySchedule;
use App\Models\CustomerCashback;

class OrderController extends Controller
{
    /**
     * Exibir página de checkout
     */
    public function checkout(Request $request)
    {
        $cart = session('cart', []);
        
        if (empty($cart)) {
            return redirect()->route('pedido.cart.index')
                ->with('error', 'Seu carrinho está vazio.');
        }

        // Resumo do carrinho
        $cartController = new CartController();
        [$count, $subtotal, $items] = $cartController->cartSummary($cart);
        
        $cartData = [
            'count' => $count,
            'subtotal' => $subtotal,
            'items' => $items,
        ];

        // Dados de pré-preenchimento do cliente (se existirem na sessão)
        $prefill = [
            'customer_name' => session('checkout.customer_name'),
            'customer_phone' => session('checkout.customer_phone'),
            'customer_email' => session('checkout.customer_email'),
            'address' => session('checkout.address'),
            'number' => session('checkout.number'),
            'complement' => session('checkout.complement'),
            'neighborhood' => session('checkout.neighborhood'),
            'city' => session('checkout.city'),
            'state' => session('checkout.state'),
            'zip_code' => session('checkout.zip_code'),
        ];

        // Buscar configurações de agendamento
        $advanceDays = 2; // padrão: 2 dias
        try {
            if (Schema::hasTable('settings')) {
                if (Schema::hasColumn('settings', 'advance_order_days')) {
                    $advanceDays = (int) (DB::table('settings')->value('advance_order_days') ?? 2);
                } else {
                    $keyCol = collect(['key','name','config_key'])->first(fn($c)=>Schema::hasColumn('settings',$c));
                    $valCol = collect(['value','val','config_value'])->first(fn($c)=>Schema::hasColumn('settings',$c));
                    if ($keyCol && $valCol) {
                        $val = DB::table('settings')->where($keyCol, 'advance_order_days')->value($valCol);
                        if ($val !== null) $advanceDays = (int) $val;
                    }
                }
            }
        } catch (\Exception $e) {
            // Mantém padrão se houver erro
        }

        // Buscar horários de entrega disponíveis
        $deliverySchedules = DeliverySchedule::where('is_active', true)
            ->get()
            ->groupBy('day_of_week');

        // Buscar cupons públicos elegíveis
        $customerEmail = $prefill['customer_email'] ?? null;
        $customerPhone = preg_replace('/\D/', '', $prefill['customer_phone'] ?? '');
        $customerId = null;
        $isFirstOrder = false;
        
        // Tentar identificar cliente por email ou telefone
        $identifiedCustomer = null;
        if ($customerEmail || $customerPhone) {
            $customerQuery = Customer::query();
            if ($customerEmail) {
                $customerQuery->where('email', $customerEmail);
            }
            if ($customerPhone && strlen($customerPhone) >= 10) {
                $customerQuery->orWhereRaw("REPLACE(REPLACE(REPLACE(REPLACE(phone,'(',''),')',''),'-',''),' ','') = ?", [$customerPhone]);
            }
            $identifiedCustomer = $customerQuery->first();
            if ($identifiedCustomer) {
                $customerId = $identifiedCustomer->id;
                $isFirstOrder = !Order::where('customer_id', $customerId)->exists();
                
                // Se cliente tem endereço salvo, usar para pré-preencher (sobrescrever dados da sessão se cliente identificado)
                if ($identifiedCustomer->zip_code || $identifiedCustomer->address) {
                    // Separar rua e número do campo address se existir
                    $addressParts = $identifiedCustomer->address ? explode(',', $identifiedCustomer->address, 2) : [null, null];
                    $street = trim($addressParts[0] ?? '');
                    $number = trim($addressParts[1] ?? '');
                    
                    // Se não conseguir separar, verificar se tem complemento separado
                    if (empty($street) && $identifiedCustomer->address) {
                        $street = $identifiedCustomer->address;
                        $number = '';
                    }
                    
                    // Pré-preencher com dados do cliente identificado
                    $prefill = array_merge($prefill, [
                        'customer_name' => $identifiedCustomer->name ?? $prefill['customer_name'],
                        'customer_phone' => $identifiedCustomer->phone ?? $prefill['customer_phone'],
                        'customer_email' => $identifiedCustomer->email ?? $prefill['customer_email'],
                        'address' => $street ?: $prefill['address'],
                        'number' => $number ?: $prefill['number'],
                        'neighborhood' => $identifiedCustomer->neighborhood ?: $prefill['neighborhood'],
                        'city' => $identifiedCustomer->city ?: $prefill['city'],
                        'state' => $identifiedCustomer->state ?: $prefill['state'],
                        'zip_code' => $identifiedCustomer->zip_code ?: $prefill['zip_code'],
                    ]);
                    
                }
            }
        }
        
        // Calcular frete estimado (0 por enquanto, será calculado depois)
        $estimatedDeliveryFee = 0;
        
        // Verificar se já há frete grátis por valor mínimo
        $freeShippingMin = 0;
        try {
            if (Schema::hasTable('settings')) {
                $keyCol = collect(['key','name','config_key'])->first(fn($c)=>Schema::hasColumn('settings',$c));
                $valCol = collect(['value','val','config_value'])->first(fn($c)=>Schema::hasColumn('settings',$c));
                if ($keyCol && $valCol) {
                    $val = DB::table('settings')->where($keyCol, 'free_shipping_min_total')->value($valCol);
                    if ($val !== null) {
                        $freeShippingMin = (float)str_replace(',', '.', (string)$val);
                    }
                }
            }
        } catch (\Exception $e) {
            // Ignorar erro
        }
        
        $hasFreeShippingByValue = $freeShippingMin > 0 && $subtotal >= $freeShippingMin;
        
        // Buscar cupons públicos elegíveis
        $eligibleCoupons = collect(); // Inicializar como collection vazia para garantir que sempre existe
        try {
            $allPublicCoupons = \App\Models\Coupon::query()
                ->where('visibility', 'public')
                ->where('is_active', true)
                ->valid()
                ->available()
                ->get();
            
            $eligibleCoupons = $allPublicCoupons->filter(function($coupon) use ($customerId, $subtotal, $estimatedDeliveryFee, $isFirstOrder, $hasFreeShippingByValue) {
                    // Cupons de frete grátis (free_shipping_only) NÃO serão mais exibidos
                    // O sistema agora aplica desconto progressivo automaticamente no frete
                    if ($coupon->free_shipping_only) {
                        return false; // Não mostrar cupons de frete grátis
                    }
                    // Verificar elegibilidade geral do cupom
                    if (!$coupon->isEligibleFor($customerId, $subtotal, $estimatedDeliveryFee, $isFirstOrder)) {
                        return false;
                    }
                    // Verificar se pode ser usado pelo cliente (limites de uso)
                    return $coupon->canBeUsedBy($customerId);
                })
                ->values();
            
            \Log::info('OrderController:checkout - Cupons elegíveis', [
                'total_public_coupons' => $allPublicCoupons->count(),
                'eligible_count' => $eligibleCoupons->count(),
                'customer_id' => $customerId,
                'is_first_order' => $isFirstOrder,
                'subtotal' => $subtotal,
                'delivery_fee' => $estimatedDeliveryFee,
                'has_free_shipping_by_value' => $hasFreeShippingByValue,
            ]);
        } catch (\Exception $e) {
            \Log::error('OrderController:checkout - Erro ao buscar cupons elegíveis', [
                'error' => $e->getMessage()
            ]);
            // Manter como collection vazia em caso de erro
            $eligibleCoupons = collect();
        }
        
        // Buscar saldo de cashback do cliente (se identificado)
        $cashbackBalance = 0;
        $cashbackCustomer = null;
        if ($customerId) {
            $cashbackCustomer = Customer::find($customerId);
            if ($cashbackCustomer) {
                $cashbackBalance = $cashbackCustomer->cashback_balance;
            }
        }
        
        // Calcular datas disponíveis (mínimo 2 dias à frente)
        $availableDates = [];
        $today = now()->startOfDay();
        $minDate = $today->copy()->addDays($advanceDays);
        
        // Capacidade por slot
        $slotCapacity = 2; // padrão
        try {
            if (Schema::hasTable('settings')) {
                if (Schema::hasColumn('settings', 'delivery_slot_capacity')) {
                    $slotCapacity = (int) (DB::table('settings')->value('delivery_slot_capacity') ?? 2);
                } else {
                    $keyCol = collect(['key','name','config_key'])->first(fn($c)=>Schema::hasColumn('settings',$c));
                    $valCol = collect(['value','val','config_value'])->first(fn($c)=>Schema::hasColumn('settings',$c));
                    if ($keyCol && $valCol) {
                        $val = DB::table('settings')->where($keyCol, 'delivery_slot_capacity')->value($valCol);
                        if ($val !== null) $slotCapacity = (int) $val;
                    }
                }
            }
        } catch (\Exception $e) {
            // Mantém padrão se houver erro
        }
        $slotCapacity = max(1, $slotCapacity);

        for ($i = $advanceDays; $i <= $advanceDays + 13; $i++) { // 2 semanas à frente
            $checkDate = $today->copy()->addDays($i);
            $dayOfWeek = strtolower($checkDate->format('l')); // monday, tuesday, etc
            
            if ($deliverySchedules->has($dayOfWeek)) {
                $schedules = $deliverySchedules[$dayOfWeek]->filter(fn($s) => $s->is_active);
                if ($schedules->count() > 0) {
                    // Gerar slots de 30min com capacidade fixa por slot
                    $slots = [];
                    foreach ($schedules as $schedule) {
                        // start_time e end_time já são objetos Carbon (cast datetime)
                        $start = \Carbon\Carbon::today()->setTimeFromTimeString($schedule->start_time->format('H:i'));
                        $end = \Carbon\Carbon::today()->setTimeFromTimeString($schedule->end_time->format('H:i'));
                        
                        while ($start < $end) {
                            $slotStart = $start->copy();
                            $slotEnd = $start->copy()->addMinutes(30);
                            
                            // Verificar quantos pedidos já estão agendados neste slot
                            $used = Order::whereDate('scheduled_delivery_at', $checkDate->toDateString())
                                ->whereTime('scheduled_delivery_at', $slotStart->format('H:i:00'))
                                ->count();
                            
                            $available = max(0, $slotCapacity - $used);
                            
                            if ($available > 0) {
                                $slotKey = $checkDate->format('Y-m-d') . ' ' . $slotStart->format('H:i');
                                $slots[] = [
                                    'value' => $slotKey,
                                    'label' => $slotStart->format('H:i') . ' - ' . $slotEnd->format('H:i'),
                                    'available' => $available,
                                ];
                            }
                            
                            $start->addMinutes(30);
                        }
                    }
                    
                    if (!empty($slots)) {
                        $availableDates[] = [
                            'date' => $checkDate->format('Y-m-d'),
                            'label' => $checkDate->format('d/m/Y'),
                            'day_name' => $checkDate->locale('pt_BR')->dayName,
                            'slots' => $slots,
                        ];
                    }
                }
            }
        }

        return view('pedido.checkout', compact('cartData', 'availableDates', 'advanceDays', 'prefill', 'eligibleCoupons', 'cashbackBalance', 'cashbackCustomer'));
    }

    /**
     * Processar checkout e criar pedido
     */
    public function store(Request $request)
    {
        $cart = session('cart', []);
        
        if (empty($cart)) {
            return redirect()->route('pedido.cart.index')
                ->with('error', 'Seu carrinho está vazio.');
        }

        // Validação
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:30',
            'customer_email' => 'nullable|email|max:255',
            'street' => 'required|string|max:255',
            'number' => 'required|string|max:30',
            'complement' => 'nullable|string|max:255',
            'neighborhood' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:2',
            'zip_code' => 'required|string|max:20',
            'payment_method' => 'required|in:pix,mercadopago,credit_card',
            'scheduled_delivery_date' => 'required|date',
            'scheduled_delivery_slot' => 'required|string',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            // 1. Buscar ou criar/atualizar cliente (usar telefone como chave única)
            // Usar updateOrCreate para atomicidade e evitar duplicatas
            $phoneNormalized = trim($validated['customer_phone']);
            
            // Montar endereço completo para salvar no cliente
            $fullAddress = trim($validated['street'] . ', ' . $validated['number']);
            if (!empty($validated['complement'])) {
                $fullAddress .= ' - ' . $validated['complement'];
            }
            
            // updateOrCreate: busca pelo primeiro array, atualiza/cria com o segundo
            $customer = Customer::updateOrCreate(
                ['phone' => $phoneNormalized], // Chave de busca (única na tabela)
                [
                    'name' => $validated['customer_name'],
                    'email' => $validated['customer_email'] ?? null,
                    // Salvar endereço completo no cliente para agilizar próximos pedidos
                    'address' => $fullAddress,
                    'neighborhood' => $validated['neighborhood'],
                    'city' => $validated['city'],
                    'state' => $validated['state'],
                    'zip_code' => preg_replace('/\D/', '', $validated['zip_code']), // Apenas números
                ]
            );
            
            // Garantir que os dados estão atualizados (incluindo endereço)
            $customer->name = $validated['customer_name'];
            if (!empty($validated['customer_email'])) {
                $customer->email = $validated['customer_email'];
            }
            // Sempre atualizar endereço (pode mudar)
            $customer->address = $fullAddress;
            $customer->neighborhood = $validated['neighborhood'];
            $customer->city = $validated['city'];
            $customer->state = $validated['state'];
            $customer->zip_code = preg_replace('/\D/', '', $validated['zip_code']);
            $customer->save();

            // 2. Buscar ou criar endereço
            $address = Address::where('customer_id', $customer->id)
                ->where('street', $validated['street'])
                ->where('number', $validated['number'])
                ->where('cep', $validated['zip_code'])
                ->first();

            if (!$address) {
                $address = Address::create([
                    'customer_id' => $customer->id,
                    'street' => $validated['street'],
                    'number' => $validated['number'],
                    'complement' => $validated['complement'] ?? null,
                    'neighborhood' => $validated['neighborhood'],
                    'city' => $validated['city'],
                    'state' => $validated['state'],
                    'cep' => $validated['zip_code'],
                ]);
            }

            // 3. Calcular totais do carrinho
            $cartController = new CartController();
            [$count, $subtotal, $items] = $cartController->cartSummary($cart);

            // 4. Calcular frete (validar se foi calculado)
            $deliveryFee = 0.00;
            $fretePendente = false;
            
            // Se o frete não foi fornecido na request, tentar calcular automaticamente
            $requestDeliveryFee = $request->input('delivery_fee');
            if ($requestDeliveryFee !== null && $requestDeliveryFee !== '') {
                $deliveryFee = (float)$requestDeliveryFee;
            } else {
                // Tentar calcular automaticamente usando o CEP fornecido
                $destinationZipcode = preg_replace('/\D/', '', $validated['zip_code']);
                if (strlen($destinationZipcode) === 8) {
                    try {
                        $customerPhone = preg_replace('/\D/', '', $validated['customer_phone']);
                        $customerEmail = $validated['customer_email'] ?? null;
                        
                        $deliveryFeeService = new \App\Services\DeliveryFeeService();
                        $result = $deliveryFeeService->calculateDeliveryFee(
                            $destinationZipcode,
                            (float)$subtotal,
                            $customerPhone ?: null,
                            $customerEmail
                        );
                        
                        if ($result['success']) {
                            $deliveryFee = $result['delivery_fee'];
                        } else {
                            // Se não conseguiu calcular, marcar como pendente
                            $fretePendente = true;
                            \Log::warning('OrderController: Não foi possível calcular frete automaticamente', [
                                'zipcode' => $destinationZipcode,
                                'subtotal' => $subtotal,
                                'message' => $result['message'] ?? 'Erro desconhecido'
                            ]);
                        }
                    } catch (\Exception $e) {
                        \Log::error('OrderController: Erro ao calcular frete automaticamente', [
                            'error' => $e->getMessage(),
                            'zipcode' => $destinationZipcode
                        ]);
                        $fretePendente = true;
                    }
                } else {
                    // CEP inválido, marcar como pendente
                    $fretePendente = true;
                }
            }
            
            // Se o frete estiver pendente, ainda permitir finalizar, mas marcar no pedido
            // Por segurança, vamos validar se há frete calculado antes de permitir finalizar
            if ($fretePendente && $deliveryFee <= 0) {
                // Se o pedido for para entrega, exige cálculo de frete
                if ($request->input('delivery_type', 'delivery') === 'delivery') {
                    return redirect()->route('pedido.checkout.index')
                        ->with('error', 'Por favor, aguarde o cálculo do frete de entrega antes de finalizar o pedido.');
                }
            }

            // 4.5. Calcular descontos (cupom e cashback)
            $discountAmount = 0.00;
            $cashbackUsed = 0.00;
            $cashbackEarned = 0.00;

            // Aplicar cupom se informado (ANTES do cashback)
            $couponCode = trim((string)($request->input('applied_coupon_code') ?: $request->input('coupon_code', '')));
            $appliedCoupon = null;
            
            if ($couponCode !== '') {
                // Usar scopes do modelo Coupon para garantir que está ativo, válido e disponível
                $coupon = \App\Models\Coupon::where('code', strtoupper($couponCode))
                    ->active()
                    ->valid()
                    ->available()
                    ->first();
                
                if ($coupon) {
                    // Verificar se é primeiro pedido (apenas pedidos pagos contam)
                    $isFirstOrder = !Order::where('customer_id', $customer->id)
                        ->whereIn('payment_status', ['approved', 'paid'])
                        ->exists();
                    
                    // Validar elegibilidade
                    if ($coupon->isEligibleFor($customer->id, $subtotal, $deliveryFee, $isFirstOrder)) {
                        // Verificar se pode ser usado pelo cliente
                        if ($coupon->canBeUsedBy($customer->id)) {
                            // Se for cupom de frete grátis, aplicar desconto no frete
                            if ($coupon->free_shipping_only && $deliveryFee > 0) {
                                $discountAmount = $coupon->calculateDiscount($subtotal) + $deliveryFee;
                            } else {
                                $discountAmount = $coupon->calculateDiscount($subtotal);
                            }
                            $appliedCoupon = $coupon;
                        } else {
                            return redirect()->route('pedido.checkout.index')
                                ->with('error', 'Cupom não pode ser usado. Limite de uso atingido.');
                        }
                    } else {
                        return redirect()->route('pedido.checkout.index')
                            ->with('error', 'Cupom não é elegível para este pedido.');
                    }
                } else {
                    return redirect()->route('pedido.checkout.index')
                        ->with('error', 'Cupom inválido ou não encontrado.');
                }
            }

            // Calcular subtotal após desconto do cupom
            $subtotalAfterCoupon = max(0, $subtotal - $discountAmount);
            
            // Aplicar cashback automaticamente se cliente tiver saldo disponível
            $cashbackBalance = CustomerCashback::getBalance($customer->id);
            $cashbackUsed = 0;
            if ($cashbackBalance > 0) {
                // Usar cashback disponível, limitado ao valor restante do pedido
                $cashbackUsed = min($cashbackBalance, $subtotalAfterCoupon);
            }
            
            // Calcular cashback gerado (sobre o valor final após abatimento do cashback usado)
            // O cashback gerado é calculado sobre o valor que o cliente realmente vai pagar
            // Buscar percentual de cashback das payment_settings (chave: cashback_percentage)
            $cashbackPercent = 5.0; // padrão
            try {
                if (Schema::hasTable('payment_settings')) {
                    $val = DB::table('payment_settings')->where('key', 'cashback_percentage')->value('value');
                    if ($val !== null && $val !== '') {
                        $cashbackPercent = (float)$val;
                    }
                }
            } catch (\Exception $e) {
                // Mantém padrão se houver erro
            }
            // Cashback gerado é calculado sobre o valor final após abatimento do cashback usado
            // Exemplo: subtotal R$30, cashback usado R$1,25 → cashback ganho sobre R$28,75
            $finalSubtotalForCashback = max(0, $subtotalAfterCoupon - $cashbackUsed);
            $cashbackEarned = round($finalSubtotalForCashback * max(0, $cashbackPercent) / 100, 2);

            $finalAmount = max(0, $subtotal + $deliveryFee - $discountAmount - $cashbackUsed);

            // 5. Processar agendamento de entrega (obrigatório)
            $scheduledDeliveryAt = null;
            if (empty($validated['scheduled_delivery_slot'])) {
                throw new \Illuminate\Validation\ValidationException(
                    validator([], []),
                    ['scheduled_delivery_slot' => ['O horário de entrega é obrigatório.']]
                );
            }
            
            // O slot já vem no formato 'Y-m-d H:i' (ex: '2025-11-05 18:30')
            try {
                $slot = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $validated['scheduled_delivery_slot']);
                    
                    // Valida que o slot ainda possui capacidade
                    $used = Order::whereDate('scheduled_delivery_at', $slot->toDateString())
                        ->whereTime('scheduled_delivery_at', $slot->format('H:i:00'))
                        ->count();
                    
                    // Capacidade por slot (configurável) - leitura flexível da tabela settings
                    $slotCapacity = 2; // padrão
                    try {
                        if (Schema::hasTable('settings')) {
                            if (Schema::hasColumn('settings', 'delivery_slot_capacity')) {
                                $slotCapacity = (int) (DB::table('settings')->value('delivery_slot_capacity') ?? 2);
                            } else {
                                $keyCol = collect(['key','name','config_key'])->first(fn($c)=>Schema::hasColumn('settings',$c));
                                $valCol = collect(['value','val','config_value'])->first(fn($c)=>Schema::hasColumn('settings',$c));
                                if ($keyCol && $valCol) {
                                    $val = DB::table('settings')->where($keyCol, 'delivery_slot_capacity')->value($valCol);
                                    if ($val !== null) $slotCapacity = (int) $val;
                                }
                            }
                        }
                    } catch (\Exception $e) {
                        // Mantém padrão se houver erro
                    }
                    $slotCapacity = max(1, $slotCapacity);
                    
                    if ($used < $slotCapacity) {
                        $scheduledDeliveryAt = $slot;
                    } else {
                        \Log::warning('OrderController:store - Slot esgotado', [
                            'slot' => $validated['scheduled_delivery_slot'],
                            'used' => $used,
                            'capacity' => $slotCapacity
                        ]);
                    }
                } catch (\Exception $e) {
                    \Log::error('OrderController:store - Erro ao processar slot de agendamento', [
                        'slot' => $validated['scheduled_delivery_slot'],
                        'error' => $e->getMessage()
                    ]);
                    throw new \Illuminate\Validation\ValidationException(
                        validator([], []),
                        ['scheduled_delivery_slot' => ['Erro ao processar horário de entrega. Por favor, tente novamente.']]
                    );
                }
            
            if (!$scheduledDeliveryAt) {
                throw new \Illuminate\Validation\ValidationException(
                    validator([], []),
                    ['scheduled_delivery_slot' => ['O horário selecionado não está mais disponível. Por favor, escolha outro.']]
                );
            }

            // 6. Gerar número do pedido
            $orderNumber = $this->generateOrderNumber();
            \Log::info('OrderController:store - Número gerado', ['order_number' => $orderNumber]);

            // 7. Preparar notas do pedido (incluindo aviso se frete estiver pendente)
            $orderNotes = trim((string)($request->input('notes', '') ?: ''));
            if ($fretePendente && $deliveryFee <= 0) {
                $fretePendenteNote = '⚠️ ATENÇÃO: Frete de entrega pendente de cálculo.';
                $orderNotes = $orderNotes ? ($orderNotes . "\n\n" . $fretePendenteNote) : $fretePendenteNote;
            }

            // 8. Criar pedido
            $order = Order::create([
                'customer_id' => $customer->id,
                'address_id' => $address->id,
                'order_number' => $orderNumber,
                'status' => 'pending',
                'total_amount' => $subtotal,
                'delivery_fee' => $fretePendente ? null : $deliveryFee, // Salvar null se pendente para indicar que precisa ser calculado depois
                'discount_amount' => $discountAmount,
                'coupon_code' => $appliedCoupon->code ?? null,
                'discount_type' => $appliedCoupon ? 'coupon' : null,
                'discount_original_value' => $appliedCoupon->value ?? null,
                'cashback_used' => $cashbackUsed,
                'cashback_earned' => $cashbackEarned,
                'final_amount' => $finalAmount,
                'payment_method' => $validated['payment_method'] ?? 'pix',
                'delivery_type' => 'delivery',
                'scheduled_delivery_at' => $scheduledDeliveryAt,
                'notes' => !empty($orderNotes) ? $orderNotes : ($validated['notes'] ?? null),
            ]);

            // Verificar se order_number foi salvo corretamente
            $order->refresh();
            \Log::info('OrderController:store - Pedido criado', ['order_id' => $order->id, 'order_number' => $order->order_number]);
            
            if (empty($order->order_number)) {
                \Log::error('OrderController:store - order_number não foi salvo!', ['order_id' => $order->id]);
                // Tentar salvar novamente
                $order->order_number = $orderNumber;
                $order->save();
                $order->refresh();
            }

            // 8. Criar itens do pedido
            foreach ($cart as $key => $row) {
                // Extrair productId e variantId, removendo possível hash de observação
                $keyParts = explode(':', (string)$key);
                $productIdStr = $keyParts[0] ?? '0';
                $variantIdStr = $keyParts[1] ?? '0';
                // Se houver 'obs:' na chave, ignorar essas partes
                
                $productId = (int)$productIdStr;
                $variantId = (int)$variantIdStr ?: null;
                $qty = (int)($row['qty'] ?? 1);
                $price = (float)($row['price'] ?? 0);
                $specialInstructions = $row['special_instructions'] ?? null;

                $product = \App\Models\Product::find($productId);
                $productName = $product ? $product->name : "Produto #{$productId}";

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $productId ?: null,
                    'variant_id' => $variantId,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'total_price' => $qty * $price,
                    'custom_name' => $productName,
                    'special_instructions' => !empty($specialInstructions) ? trim($specialInstructions) : null,
                ]);
            }

            DB::commit();

            \Log::info('OrderController:store - Pedido criado com sucesso', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'payment_method' => $validated['payment_method'],
            ]);

            // Limpar carrinho apenas após commit bem-sucedido
            session()->forget('cart');
            session()->forget('cart_count');

            // Salvar dados na sessão para próxima compra
            session([
                'checkout.customer_name' => $validated['customer_name'],
                'checkout.customer_phone' => $validated['customer_phone'],
                'checkout.customer_email' => $validated['customer_email'] ?? '',
                'checkout.address' => $validated['street'],
                'checkout.number' => $validated['number'],
                'checkout.complement' => $validated['complement'] ?? '',
                'checkout.neighborhood' => $validated['neighborhood'],
                'checkout.city' => $validated['city'],
                'checkout.state' => $validated['state'],
                'checkout.zip_code' => $validated['zip_code'],
            ]);

            // Salvar order_id na sessão para uso no payment controller
            session(['order_id' => $order->id]);

            \Log::info('OrderController:store - Redirecionando para pagamento', [
                'order_id' => $order->id,
                'payment_method' => $validated['payment_method'],
            ]);

            // Redirecionar para página de pagamento
            if ($validated['payment_method'] === 'pix') {
                return redirect()->route('pedido.payment.pix', ['order' => $order->id]);
            } else {
                return redirect()->route('pedido.payment.checkout', ['order' => $order->id]);
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            \Log::error('OrderController:store - Erro de validação', [
                'errors' => $e->errors(),
                'request_data' => $request->except(['_token'])
            ]);
            return redirect()->route('pedido.checkout.index')
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('OrderController:store - Erro ao processar checkout', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->route('pedido.checkout.index')
                ->with('error', 'Erro ao processar pedido. Por favor, tente novamente. Se o problema persistir, entre em contato com o suporte.')
                ->withInput();
        }
    }

    /**
     * Finalizar pedido criado pelo PDV
     * Permite ao cliente escolher data/hora de entrega e forma de pagamento
     */
    public function completePdvOrder(Request $request, $orderNumber)
    {
        $token = $request->get('token');
        
        // Buscar pedido pelo número
        $order = Order::where('order_number', $orderNumber)
            ->with(['customer', 'items.product', 'address'])
            ->firstOrFail();
        
        // Validar token (simples, baseado em hash do ID + número + app key)
        $appKey = config('app.key');
        if (empty($appKey)) {
            \Log::error('OrderController: APP_KEY não configurada', [
                'order_number' => $orderNumber
            ]);
            abort(500, 'Erro de configuração do sistema.');
        }
        
        $expectedToken = md5($order->id . $order->order_number . $appKey);
        
        // Log para debug (remover em produção se necessário)
        \Log::debug('OrderController: Validação de token PDV', [
            'order_id' => $order->id,
            'order_number' => $orderNumber,
            'token_recebido' => $token,
            'token_esperado' => $expectedToken,
            'app_key_length' => strlen($appKey),
            'match' => ($token === $expectedToken)
        ]);
        
        if ($token !== $expectedToken) {
            abort(403, 'Token inválido ou link expirado.');
        }
        
        // Verificar se o pedido já foi finalizado
        if ($order->payment_status !== null && $order->payment_status !== 'pending') {
            return redirect()->route('pedido.payment.success', ['order' => $order->id])
                ->with('info', 'Este pedido já foi finalizado.');
        }
        
        // Calcular subtotal e totais do pedido
        $subtotal = (float)$order->total_amount;
        $deliveryFee = (float)($order->delivery_fee ?? 0);
        $discountAmount = (float)($order->discount_amount ?? 0);
        $finalAmount = (float)$order->final_amount;
        
        // Preparar dados para a view (similar ao checkout)
        $cartData = [
            'count' => $order->items->sum('quantity'),
            'subtotal' => $subtotal,
            'items' => $order->items->map(function($item) {
                return [
                    'product_id' => $item->product_id,
                    'variant_id' => null,
                    'qty' => $item->quantity,
                    'price' => (float)$item->unit_price,
                    'subtotal' => (float)$item->total_price,
                    'name' => $item->custom_name ?? optional($item->product)->name ?? 'Item',
                    'variant' => null,
                    'image_url' => optional($item->product)->image_url ?? null,
                ];
            })->toArray(),
        ];
        
        // Pré-preencher dados do cliente
        // Se não houver endereço na tabela addresses, usar dados do cliente
        $address = $order->address;
        $prefill = [
            'customer_name' => $order->customer->name ?? '',
            'customer_phone' => $order->customer->phone ?? '',
            'customer_email' => $order->customer->email ?? '',
            'street' => $address->street ?? ($order->customer->address ? explode(',', $order->customer->address)[0] ?? '' : ''),
            'number' => $address->number ?? ($order->customer->address ? (explode(',', $order->customer->address)[1] ?? '') : ''),
            'complement' => $address->complement ?? '',
            'neighborhood' => $address->neighborhood ?? $order->customer->neighborhood ?? '',
            'city' => $address->city ?? $order->customer->city ?? '',
            'state' => $address->state ?? $order->customer->state ?? '',
            'zip_code' => $address->cep ?? $order->customer->zip_code ?? '',
        ];
        
        // Extrair número do endereço completo do cliente se necessário
        if (empty($prefill['number']) && !empty($order->customer->address)) {
            $addressParts = explode(',', $order->customer->address);
            if (count($addressParts) >= 2) {
                $prefill['street'] = trim($addressParts[0]);
                $prefill['number'] = trim($addressParts[1]);
            }
        }
        
        // Cupom aplicado (se houver)
        $appliedCouponCode = $order->coupon_code ?? null;
        
        // Buscar configurações de agendamento (mesma lógica do checkout)
        $advanceDays = 2;
        try {
            if (Schema::hasTable('settings')) {
                if (Schema::hasColumn('settings', 'advance_order_days')) {
                    $advanceDays = (int) (DB::table('settings')->value('advance_order_days') ?? 2);
                } else {
                    $keyCol = collect(['key','name','config_key'])->first(fn($c)=>Schema::hasColumn('settings',$c));
                    $valCol = collect(['value','val','config_value'])->first(fn($c)=>Schema::hasColumn('settings',$c));
                    if ($keyCol && $valCol) {
                        $val = DB::table('settings')->where($keyCol, 'advance_order_days')->value($valCol);
                        if ($val !== null) $advanceDays = (int) $val;
                    }
                }
            }
        } catch (\Exception $e) {
            // Mantém padrão se houver erro
        }
        
        // Buscar horários de entrega disponíveis (mesma lógica do checkout)
        $deliverySchedules = DeliverySchedule::where('is_active', true)
            ->get()
            ->groupBy('day_of_week');
        
        \Log::warning('completePdvOrder: Horários de entrega carregados', [
            'schedules_count' => $deliverySchedules->count(),
            'days' => $deliverySchedules->keys()->toArray(),
        ]);
        
        $availableDates = [];
        $slotCapacity = 2; // padrão
        try {
            if (Schema::hasTable('settings')) {
                if (Schema::hasColumn('settings', 'delivery_slot_capacity')) {
                    $slotCapacity = (int) (DB::table('settings')->value('delivery_slot_capacity') ?? 2);
                } else {
                    $keyCol = collect(['key','name','config_key'])->first(fn($c)=>Schema::hasColumn('settings',$c));
                    $valCol = collect(['value','val','config_value'])->first(fn($c)=>Schema::hasColumn('settings',$c));
                    if ($keyCol && $valCol) {
                        $val = DB::table('settings')->where($keyCol, 'delivery_slot_capacity')->value($valCol);
                        if ($val !== null) $slotCapacity = (int) $val;
                    }
                }
            }
        } catch (\Exception $e) {
            // Mantém padrão
        }
        
        // Gerar slots disponíveis (mesma lógica do checkout)
        $today = \Carbon\Carbon::today();
        $maxDate = $today->copy()->addDays($advanceDays + 6);
        
        // Usar a mesma lógica do método checkout (iterar por datas e verificar se existe schedule para o dia)
        for ($i = $advanceDays; $i <= $advanceDays + 13; $i++) { // 2 semanas à frente
            $checkDate = $today->copy()->addDays($i);
            $dayOfWeek = strtolower($checkDate->format('l')); // monday, tuesday, etc
            
            if ($deliverySchedules->has($dayOfWeek)) {
                $schedules = $deliverySchedules[$dayOfWeek]->filter(fn($s) => $s->is_active);
                if ($schedules->count() > 0) {
                    // Gerar slots de 30min com capacidade fixa por slot
                    $slots = [];
                    foreach ($schedules as $schedule) {
                        // start_time e end_time já são objetos Carbon (cast datetime)
                        $start = \Carbon\Carbon::today()->setTimeFromTimeString($schedule->start_time->format('H:i'));
                        $end = \Carbon\Carbon::today()->setTimeFromTimeString($schedule->end_time->format('H:i'));
                        
                        while ($start < $end) {
                            $slotStart = $start->copy();
                            $slotEnd = $start->copy()->addMinutes(30);
                            
                            // Verificar quantos pedidos já estão agendados neste slot
                            $used = Order::whereDate('scheduled_delivery_at', $checkDate->toDateString())
                                ->whereTime('scheduled_delivery_at', $slotStart->format('H:i:00'))
                                ->count();
                            
                            $available = max(0, $slotCapacity - $used);
                            
                            if ($available > 0) {
                                $slotKey = $checkDate->format('Y-m-d') . ' ' . $slotStart->format('H:i');
                                $slots[] = [
                                    'value' => $slotKey,
                                    'label' => $slotStart->format('H:i') . ' - ' . $slotEnd->format('H:i'),
                                    'available' => $available,
                                ];
                            }
                            
                            $start->addMinutes(30);
                        }
                    }
                    
                    if (!empty($slots)) {
                        $availableDates[] = [
                            'date' => $checkDate->format('Y-m-d'),
                            'label' => $checkDate->format('d/m/Y'),
                            'day_name' => $checkDate->locale('pt_BR')->dayName,
                            'slots' => $slots,
                        ];
                    }
                }
            }
        }
        
        \Log::warning('completePdvOrder: Datas disponíveis geradas', [
            'available_dates_count' => count($availableDates),
            'dates' => array_map(fn($d) => $d['date'], $availableDates),
        ]);
        
        // Buscar cupons públicos elegíveis
        $customerId = $order->customer_id;
        $isFirstOrder = !Order::where('customer_id', $customerId)
            ->whereIn('payment_status', ['approved', 'paid'])
            ->where('id', '!=', $order->id)
            ->exists();
        
        // Buscar cupons públicos elegíveis (usando colunas corretas: starts_at, expires_at, visibility)
        $eligibleCoupons = collect(); // Inicializar como collection vazia para garantir que sempre existe
        try {
            $eligibleCoupons = \App\Models\Coupon::query()
                ->where('visibility', 'public')
                ->active()
                ->valid()
                ->available()
                ->get()
                ->filter(function($coupon) use ($isFirstOrder, $subtotal, $deliveryFee, $customerId) {
                    // Verificar se é elegível para o pedido
                    if (!$coupon->isEligibleFor($customerId, $subtotal, $deliveryFee, $isFirstOrder)) {
                        return false;
                    }
                    // Verificar se pode ser usado pelo cliente
                    return $coupon->canBeUsedBy($customerId);
                })
                ->values();
        } catch (\Exception $e) {
            \Log::error('OrderController:completePdvOrder - Erro ao buscar cupons elegíveis', [
                'error' => $e->getMessage()
            ]);
            // Manter como collection vazia em caso de erro
            $eligibleCoupons = collect();
        }
        
        // Cashback
        $cashbackBalance = \App\Models\CustomerCashback::getBalance($customerId);
        $cashbackCustomer = $order->customer;
        
        // Cupom aplicado (se houver)
        $appliedCouponCode = $order->coupon_code ?? null;
        
        return view('pedido.checkout', compact(
            'cartData', 
            'availableDates', 
            'advanceDays', 
            'prefill', 
            'eligibleCoupons', 
            'cashbackBalance', 
            'cashbackCustomer',
            'appliedCouponCode',
            'order' // Passar o pedido para identificar que é do PDV
        ));
    }

    /**
     * Calcular descontos em tempo real (cupom e cashback)
     * Também retorna dados do cliente se identificado (para preencher endereço)
     */
    public function calculateDiscounts(Request $request)
    {
        \Log::warning('========== calculateDiscounts: MÉTODO CHAMADO ==========', [
            'coupon_code' => $request->input('coupon_code'),
            'customer_phone' => $request->input('customer_phone'),
            'customer_email' => $request->input('customer_email'),
            'delivery_fee' => $request->input('delivery_fee'),
        ]);
        
        $cart = session('cart', []);
        $subtotal = 0;
        $productIds = [];
        
        // Se carrinho estiver vazio, tentar buscar pedido do PDV
        if (empty($cart)) {
            $orderNumber = $request->input('order_number');
            $orderId = $request->input('order_id');
            
            \Log::warning('calculateDiscounts: Carrinho vazio, tentando buscar pedido PDV', [
                'order_number' => $orderNumber,
                'order_id' => $orderId,
            ]);
            
            if ($orderNumber || $orderId) {
                $orderQuery = Order::query()->with('items');
                
                if ($orderNumber) {
                    $orderQuery->where('order_number', $orderNumber);
                } elseif ($orderId) {
                    $orderQuery->where('id', $orderId);
                }
                
                $order = $orderQuery->first();
                
                if ($order && $order->items) {
                    \Log::warning('calculateDiscounts: Pedido PDV encontrado, usando itens do pedido', [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'items_count' => $order->items->count(),
                    ]);
                    
                    // Calcular subtotal dos itens do pedido
                    foreach ($order->items as $item) {
                        $subtotal += (float)$item->total_price;
                        if ($item->product_id) {
                            $productIds[] = $item->product_id;
                        }
                    }
                } else {
                    \Log::warning('calculateDiscounts: Pedido não encontrado, retornando zeros');
                    return response()->json([
                        'subtotal' => 0,
                        'delivery_fee' => 0,
                        'coupon_discount' => 0,
                        'coupon_message' => null,
                        'cashback_used' => 0,
                        'cashback_earned' => 0,
                        'total' => 0,
                        'customer' => null,
                    ]);
                }
            } else {
                \Log::warning('calculateDiscounts: Carrinho vazio e sem order_number/order_id, retornando zeros');
                return response()->json([
                    'subtotal' => 0,
                    'delivery_fee' => 0,
                    'coupon_discount' => 0,
                    'coupon_message' => null,
                    'cashback_used' => 0,
                    'cashback_earned' => 0,
                    'total' => 0,
                    'customer' => null,
                ]);
            }
        } else {
            \Log::warning('calculateDiscounts: Carrinho não está vazio, usando carrinho da sessão', [
                'cart_items_count' => count($cart),
            ]);

            // Calcular subtotal do carrinho
            foreach ($cart as $key => $row) {
                [$productIdStr] = array_pad(explode(':', (string)$key, 2), 2, '0');
                $productIds[] = (int)$productIdStr;
                $qty = (int)($row['qty'] ?? 1);
                $price = (float)($row['price'] ?? 0);
                $subtotal += $qty * $price;
            }
        }
        
        \Log::warning('calculateDiscounts: Subtotal calculado', [
            'subtotal' => $subtotal,
            'product_ids' => $productIds,
        ]);

        // Identificar cliente se fornecido (busca única, usada para tudo)
        $customerData = null;
        $customerId = null;
        $cashbackBalance = 0;
        $customerPhone = preg_replace('/\D/', '', (string)$request->input('customer_phone', ''));
        $customerEmail = trim((string)$request->input('customer_email', ''));
        
        if ($customerPhone || $customerEmail) {
            $customerQuery = Customer::query();
            if ($customerEmail) {
                $customerQuery->where('email', $customerEmail);
            }
            if ($customerPhone && strlen($customerPhone) >= 10) {
                if ($customerEmail) {
                    $customerQuery->orWhereRaw("REPLACE(REPLACE(REPLACE(REPLACE(phone,'(',''),')',''),'-',''),' ','') = ?", [$customerPhone]);
                } else {
                    $customerQuery->whereRaw("REPLACE(REPLACE(REPLACE(REPLACE(phone,'(',''),')',''),'-',''),' ','') = ?", [$customerPhone]);
                }
            }
            
            $customer = $customerQuery->first();
            if ($customer) {
                $customerId = $customer->id;
                $cashbackBalance = CustomerCashback::getBalance($customer->id);
                
                // Separar rua e número do campo address
                $addressParts = $customer->address ? explode(',', $customer->address, 2) : [null, null];
                $street = trim($addressParts[0] ?? '');
                $number = trim($addressParts[1] ?? '');
                
                if (empty($street) && $customer->address) {
                    $street = $customer->address;
                    $number = '';
                }
                
                $customerData = [
                    'name' => $customer->name,
                    'phone' => $customer->phone,
                    'email' => $customer->email,
                    'address' => $street,
                    'number' => $number,
                    'neighborhood' => $customer->neighborhood,
                    'city' => $customer->city,
                    'state' => $customer->state,
                    'zip_code' => $customer->zip_code,
                ];
            }
        }

        // Calcular frete (estimado, usar o que vier na request ou 0)
        $deliveryFee = (float)($request->input('delivery_fee', 0));

        // Aplicar cupom se informado
        $couponCode = trim((string)($request->input('coupon_code', '')));
        $couponDiscount = 0;
        $couponMessage = null;
        
        if ($couponCode !== '') {
            \Log::warning('========== calculateDiscounts: INÍCIO VALIDAÇÃO CUPOM ==========', [
                'code' => $couponCode,
                'subtotal' => $subtotal,
                'delivery_fee' => $deliveryFee,
                'customer_id' => $customerId,
            ]);
            
            $coupon = \App\Models\Coupon::where('code', strtoupper($couponCode))
                ->active()
                ->valid()
                ->available()
                ->first();
            
            if ($coupon) {
                \Log::warning('calculateDiscounts: Cupom encontrado', [
                    'coupon_id' => $coupon->id,
                    'code' => $coupon->code,
                    'type' => $coupon->type,
                    'value' => $coupon->value,
                    'minimum_amount' => $coupon->minimum_amount,
                    'first_order_only' => $coupon->first_order_only,
                    'free_shipping_only' => $coupon->free_shipping_only,
                    'visibility' => $coupon->visibility,
                    'usage_limit_per_customer' => $coupon->usage_limit_per_customer,
                    'is_active' => $coupon->is_active,
                ]);
                // Verificar se precisa de cliente identificado
                $needsCustomer = $coupon->first_order_only || $coupon->visibility === 'targeted' || ($coupon->usage_limit_per_customer > 0);
                
                if ($needsCustomer && !$customerId) {
                    // Cupom requer cliente identificado
                    $couponMessage = 'Identifique-se com telefone ou email para usar este cupom.';
                } elseif ($customerId) {
                    // Cliente identificado, validar elegibilidade
                    // Verificar se é primeiro pedido (apenas pedidos pagos contam)
                    $isFirstOrder = !Order::where('customer_id', $customerId)
                        ->whereIn('payment_status', ['approved', 'paid'])
                        ->exists();
                    
                    if (!$coupon->isEligibleFor($customerId, $subtotal, $deliveryFee, $isFirstOrder)) {
                        // Mensagem mais específica baseada na validação
                        if ($coupon->minimum_amount && $subtotal < $coupon->minimum_amount) {
                            $couponMessage = "O valor mínimo do pedido para este cupom é R$ " . number_format($coupon->minimum_amount, 2, ',', '.') . ". Seu pedido atual é de R$ " . number_format($subtotal, 2, ',', '.') . ".";
                        } elseif ($coupon->first_order_only && !$isFirstOrder) {
                            $couponMessage = 'Este cupom é válido apenas para primeiro pedido.';
                        } elseif ($coupon->free_shipping_only && $deliveryFee <= 0) {
                            $couponMessage = 'Este cupom é válido apenas para pedidos com taxa de entrega.';
                        } else {
                            $couponMessage = 'Cupom não é elegível para este pedido.';
                        }
                        \Log::warning('calculateDiscounts: Cupom não elegível (cliente identificado)', [
                            'minimum_amount' => $coupon->minimum_amount,
                            'subtotal' => $subtotal,
                            'first_order_only' => $coupon->first_order_only,
                            'isFirstOrder' => $isFirstOrder,
                            'free_shipping_only' => $coupon->free_shipping_only,
                            'delivery_fee' => $deliveryFee,
                        ]);
                    } elseif (!$coupon->canBeUsedBy($customerId)) {
                        $couponMessage = 'Cupom não pode ser usado. Limite de uso atingido.';
                        \Log::warning('calculateDiscounts: Cupom não pode ser usado - limite atingido', [
                            'code' => $coupon->code,
                            'customerId' => $customerId,
                            'usage_limit_per_customer' => $coupon->usage_limit_per_customer,
                        ]);
                    } else {
                        // Cupom válido, aplicar desconto
                        // Cupons de frete grátis não são mais aplicados (desconto progressivo substitui)
                        // Aplicar apenas desconto no subtotal dos produtos
                        $couponDiscount = $coupon->calculateDiscount($subtotal);
                        \Log::info('calculateDiscounts: Cupom aplicado com sucesso', [
                            'coupon_discount' => $couponDiscount,
                        ]);
                    }
                } else {
                    // Cupom público que não requer cliente identificado
                    // Verificar apenas elegibilidade básica (valor mínimo, etc)
                    $isEligible = $coupon->isEligibleFor(null, $subtotal, $deliveryFee, false);
                    \Log::info('calculateDiscounts: Verificando elegibilidade (público)', [
                        'is_eligible' => $isEligible,
                        'minimum_amount' => $coupon->minimum_amount,
                        'subtotal' => $subtotal,
                    ]);
                    
                    if ($isEligible) {
                        // Cupons de frete grátis não são mais aplicados (desconto progressivo substitui)
                        // Aplicar apenas desconto no subtotal dos produtos
                        $couponDiscount = $coupon->calculateDiscount($subtotal);
                        \Log::info('calculateDiscounts: Cupom público aplicado', [
                            'coupon_discount' => $couponDiscount,
                        ]);
                    } else {
                        // Mensagem mais específica baseada na validação
                        if ($coupon->minimum_amount && $subtotal < $coupon->minimum_amount) {
                            $couponMessage = "O valor mínimo do pedido para este cupom é R$ " . number_format($coupon->minimum_amount, 2, ',', '.') . ". Seu pedido atual é de R$ " . number_format($subtotal, 2, ',', '.') . ".";
                        } elseif ($coupon->first_order_only) {
                            $couponMessage = 'Este cupom é válido apenas para primeiro pedido.';
                        } elseif ($coupon->free_shipping_only && $deliveryFee <= 0) {
                            $couponMessage = 'Este cupom é válido apenas para pedidos com taxa de entrega.';
                        } else {
                            $couponMessage = 'Cupom não é elegível para este pedido.';
                        }
                        \Log::warning('calculateDiscounts: Cupom não elegível', [
                            'reason' => 'isEligibleFor retornou false',
                            'minimum_amount' => $coupon->minimum_amount,
                            'subtotal' => $subtotal,
                            'first_order_only' => $coupon->first_order_only,
                            'free_shipping_only' => $coupon->free_shipping_only,
                            'delivery_fee' => $deliveryFee,
                        ]);
                    }
                }
            } else {
                $couponMessage = 'Cupom inválido ou não encontrado.';
                \Log::warning('calculateDiscounts: Cupom não encontrado', [
                    'code' => $couponCode,
                ]);
            }
        }

        // Calcular subtotal após cupom
        $subtotalAfterCoupon = max(0, $subtotal - $couponDiscount);
        
        // Aplicar cashback automaticamente se houver saldo disponível
        $cashbackUsed = 0;
        if ($cashbackBalance > 0 && $customerId) {
            $cashbackUsed = min($cashbackBalance, $subtotalAfterCoupon);
        }

        // Calcular cashback ganho
        // Buscar percentual de cashback das payment_settings (chave: cashback_percentage)
        $cashbackPercent = 5.0;
        try {
            if (Schema::hasTable('payment_settings')) {
                $val = DB::table('payment_settings')->where('key', 'cashback_percentage')->value('value');
                if ($val !== null && $val !== '') {
                    $cashbackPercent = (float)$val;
                }
            }
        } catch (\Exception $e) {}
        
        // Cashback gerado é calculado sobre o valor final após abatimento do cashback usado
        // Exemplo: subtotal R$30, cashback usado R$1,25 → cashback ganho sobre R$28,75
        // É apenas informativo até o pagamento ser confirmado
        $finalSubtotalForCashback = max(0, $subtotalAfterCoupon - $cashbackUsed);
        $cashbackEarned = round($finalSubtotalForCashback * max(0, $cashbackPercent) / 100, 2);

        $total = max(0, $subtotal + $deliveryFee - $couponDiscount - $cashbackUsed);

        // Garantir que coupon_message seja sempre uma string (não null)
        $couponMessageFinal = ($couponMessage !== null && trim($couponMessage) !== '') 
            ? trim($couponMessage) 
            : null;
        
        \Log::warning('========== calculateDiscounts: RETORNANDO RESPOSTA ==========', [
            'coupon_code' => $couponCode,
            'coupon_discount' => round($couponDiscount, 2),
            'coupon_message' => $couponMessageFinal,
            'coupon_message_length' => $couponMessageFinal ? strlen($couponMessageFinal) : 0,
            'coupon_message_type' => gettype($couponMessageFinal),
            'coupon_message_is_null' => is_null($couponMessageFinal),
            'subtotal' => $subtotal,
            'delivery_fee' => $deliveryFee,
            'customer_id' => $customerId,
        ]);
        
        $jsonResponse = [
            'subtotal' => round($subtotal, 2),
            'delivery_fee' => round($deliveryFee, 2),
            'coupon_discount' => round($couponDiscount, 2),
            'coupon_message' => $couponMessageFinal, // Mensagem de erro ou aviso sobre o cupom (sempre string ou null)
            'cashback_used' => round($cashbackUsed, 2),
            'cashback_earned' => round($cashbackEarned, 2),
            'cashback_balance' => round($cashbackBalance, 2),
            'total' => round($total, 2),
            'customer' => $customerData, // Dados do cliente para preencher endereço
        ];
        
        \Log::warning('calculateDiscounts: JSON final antes de retornar', [
            'coupon_message_in_response' => $jsonResponse['coupon_message'],
            'coupon_message_type_in_response' => gettype($jsonResponse['coupon_message']),
        ]);
        
        return response()->json($jsonResponse);
    }

    /**
     * Gerar número único do pedido
     */
    private function generateOrderNumber(): string
    {
        // Formato: OLK + timestamp invertido + sequencial
        $prefix = 'OLK';
        $timestamp = now()->format('YmdHis');
        $random = str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
        
        // Verificar se já existe
        $number = $prefix . $timestamp . $random;
        while (Order::where('order_number', $number)->exists()) {
            $random = str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
            $number = $prefix . $timestamp . $random;
        }
        
        return $number;
    }
}
