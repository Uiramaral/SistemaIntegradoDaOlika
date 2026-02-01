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
        // Verificar se há um pedido já pago na sessão
        $sessionOrderId = session('order_id');
        if ($sessionOrderId) {
            $existingOrder = Order::find($sessionOrderId);
            // IMPORTANTE: Só limpar o carrinho se o pedido estiver realmente pago/aprovado
            // Se estiver 'pending', manter o carrinho para permitir nova tentativa
            if ($existingOrder && in_array($existingOrder->payment_status, ['approved', 'paid'])) {
                // Pedido já foi pago, limpar sessão e redirecionar para página de sucesso
                session()->forget('cart');
                session()->forget('cart_count');
                session()->forget('order_id');
                return redirect()->route('pedido.payment.success', ['order' => $existingOrder->id])
                    ->with('info', 'Este pedido já foi finalizado e pago.');
            } elseif ($existingOrder && $existingOrder->payment_status === 'pending') {
                // Pedido ainda está pendente - NÃO limpar o carrinho, permitir nova tentativa
                // Mas limpar o order_id da sessão para não causar confusão
                session()->forget('order_id');
            }
        }

        $cart = session('cart', []);

        if (empty($cart)) {
            return redirect()->route('pedido.cart.index')
                ->with('error', 'Seu carrinho está vazio.');
        }

        // Rastrear início de checkout
        try {
            $customerId = null;
            $customerPhone = session('checkout.customer_phone');
            if ($customerPhone) {
                $customer = \App\Models\Customer::where('phone', $customerPhone)->first();
                $customerId = $customer->id ?? null;
            }

            $cartController = new CartController();
            [$count, $subtotal] = $cartController->cartSummary($cart, true);

            \App\Models\AnalyticsEvent::trackCheckoutStarted($customerId, [
                'cart_items_count' => $count,
                'subtotal' => $subtotal,
            ]);
        } catch (\Exception $e) {
            \Log::warning('Erro ao rastrear início de checkout', [
                'error' => $e->getMessage(),
            ]);
        }

        // Resumo do carrinho
        $cartController = new CartController();
        [$count, $subtotal, $items] = $cartController->cartSummary($cart, true);

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
                    $keyCol = collect(['key', 'name', 'config_key'])->first(fn($c) => Schema::hasColumn('settings', $c));
                    $valCol = collect(['value', 'val', 'config_value'])->first(fn($c) => Schema::hasColumn('settings', $c));
                    if ($keyCol && $valCol) {
                        $val = DB::table('settings')->where($keyCol, 'advance_order_days')->value($valCol);
                        if ($val !== null)
                            $advanceDays = (int) $val;
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
        $isFirstOrder = true; // Por padrão, assumir que é primeiro pedido (cliente novo)

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
                // Verificar se tem pedidos pagos (aprovados ou pagos)
                $hasPaidOrders = Order::where('customer_id', $customerId)
                    ->whereIn('payment_status', ['approved', 'paid'])
                    ->exists();
                $isFirstOrder = !$hasPaidOrders;

                // Se cliente tem endereço salvo, usar para pré-preencher
                if ($identifiedCustomer->zip_code || $identifiedCustomer->address) {
                    $addressParts = $identifiedCustomer->address ? explode(',', $identifiedCustomer->address, 2) : [null, null];
                    $street = trim($addressParts[0] ?? '');
                    $number = trim($addressParts[1] ?? '');

                    if (empty($street) && $identifiedCustomer->address) {
                        $street = $identifiedCustomer->address;
                        $number = '';
                    }

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
                $keyCol = collect(['key', 'name', 'config_key'])->first(fn($c) => Schema::hasColumn('settings', $c));
                $valCol = collect(['value', 'val', 'config_value'])->first(fn($c) => Schema::hasColumn('settings', $c));
                if ($keyCol && $valCol) {
                    $val = DB::table('settings')->where($keyCol, 'free_shipping_min_total')->value($valCol);
                    if ($val !== null) {
                        $freeShippingMin = (float) str_replace(',', '.', (string) $val);
                    }
                }
            }
        } catch (\Exception $e) {
            // Ignorar erro
        }

        $hasFreeShippingByValue = $freeShippingMin > 0 && $subtotal >= $freeShippingMin;

        // Buscar cupons públicos elegíveis
        $eligibleCoupons = collect();
        try {
            // Buscar cupons públicos E cupons direcionados ao cliente específico
            $couponsQuery = \App\Models\Coupon::query()
                ->where('is_active', true)
                ->valid()
                ->available();

            // Incluir cupons públicos OU cupons direcionados ao cliente
            $couponsQuery->where(function ($q) use ($customerId) {
                $q->where('visibility', 'public');
                if ($customerId) {
                    $q->orWhere(function ($subQ) use ($customerId) {
                        $subQ->where('visibility', 'targeted')
                            ->where('target_customer_id', $customerId);
                    });
                }
            });

            $allCoupons = $couponsQuery->get();

            $eligibleCoupons = $allCoupons->filter(function ($coupon) use ($customerId, $subtotal, $estimatedDeliveryFee, $isFirstOrder, $hasFreeShippingByValue) {
                // Cupons de frete grátis não são mais exibidos
                if ($coupon->free_shipping_only) {
                    return false;
                }

                // Verificar se é cupom direcionado e se o cliente tem direito
                if ($coupon->visibility === 'targeted') {
                    if (!$customerId || $coupon->target_customer_id !== $customerId) {
                        return false; // Cupom direcionado não é para este cliente
                    }
                }

                // Para cupons de primeiro pedido, verificar primeiro se é primeiro pedido
                // Cupons de primeiro pedido devem aparecer para clientes novos (mesmo sem customer_id)
                if ($coupon->first_order_only) {
                    if (!$isFirstOrder) {
                        return false; // Não é primeiro pedido, cupom não é elegível
                    }
                    // É primeiro pedido, verificar outras condições (valor mínimo, etc)
                    // Para cupons de primeiro pedido, verificar canBeUsedBy se cliente existe
                    if ($customerId && !$coupon->canBeUsedBy($customerId)) {
                        return false; // Cliente já usou este cupom de primeiro pedido
                    }
                    if (!$coupon->isValid($customerId)) {
                        return false; // Cupom não está ativo, válido ou disponível
                    }
                    // Verificar valor mínimo
                    if ($coupon->minimum_amount && $subtotal < $coupon->minimum_amount) {
                        return false;
                    }
                    // Cupom de primeiro pedido é elegível
                    return true;
                }

                // Para outros cupons (não first_order_only), verificar elegibilidade geral
                if (!$coupon->isEligibleFor($customerId, $subtotal, $estimatedDeliveryFee, $isFirstOrder)) {
                    return false;
                }

                // IMPORTANTE: Verificar se o cliente pode usar o cupom (inclui verificação de uso único)
                // Se há customerId, sempre verificar canBeUsedBy para garantir que não foi usado antes
                if ($customerId && !$coupon->canBeUsedBy($customerId)) {
                    return false; // Cliente não pode usar este cupom (já usou, limite atingido, etc)
                }

                return true;
            })->values();

            \Log::info('OrderController:checkout - Cupons elegíveis', [
                'total_public_coupons' => $allCoupons->count(),
                'eligible_count' => $eligibleCoupons->count(),
                'customer_id' => $customerId,
                'is_first_order' => $isFirstOrder,
                'subtotal' => $subtotal,
                'delivery_fee' => $estimatedDeliveryFee,
                'has_free_shipping_by_value' => $hasFreeShippingByValue
            ]);
        } catch (\Exception $e) {
            \Log::error('OrderController:checkout - Erro ao buscar cupons elegíveis', [
                'error' => $e->getMessage()
            ]);
            $eligibleCoupons = collect();
        }

        // Buscar saldo de cashback do cliente
        $cashbackBalance = 0;
        $cashbackCustomer = null;
        if ($customerId) {
            $cashbackCustomer = Customer::find($customerId);
            if ($cashbackCustomer) {
                $cashbackBalance = $cashbackCustomer->cashback_balance;
            }
        }

        // Calcular datas disponíveis
        $availableDates = [];
        $slotCapacity = 2; // padrão
        try {
            if (Schema::hasTable('settings')) {
                if (Schema::hasColumn('settings', 'delivery_slot_capacity')) {
                    $slotCapacity = (int) (DB::table('settings')->value('delivery_slot_capacity') ?? 2);
                } else {
                    $keyCol = collect(['key', 'name', 'config_key'])->first(fn($c) => Schema::hasColumn('settings', $c));
                    $valCol = collect(['value', 'val', 'config_value'])->first(fn($c) => Schema::hasColumn('settings', $c));
                    if ($keyCol && $valCol) {
                        $val = DB::table('settings')->where($keyCol, 'delivery_slot_capacity')->value($valCol);
                        if ($val !== null)
                            $slotCapacity = (int) $val;
                    }
                }
            }
        } catch (\Exception $e) {
            // Mantém padrão se houver erro
        }
        $slotCapacity = max(1, $slotCapacity);

        $today = now()->startOfDay();
        $minDate = $today->copy()->addDays($advanceDays);

        // Capacidade por slot
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

        // Validação
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:30',
            'customer_email' => 'nullable|email|max:255',
            'street' => 'required|string|max:255',
            'number' => 'required|string|max:30|regex:/^[0-9]+$/',
            'complement' => 'nullable|string|max:255',
            'neighborhood' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:2',
            'zip_code' => 'required|string|max:20',
            'payment_method' => 'required|in:pix,mercadopago,credit_card',
            'scheduled_delivery_date' => 'required|date',
            'scheduled_delivery_slot' => 'required|string',
            'notes' => 'nullable|string|max:1000',
            'order_id' => 'nullable|integer|exists:orders,id', // ID do pedido do PDV
            'order_number' => 'nullable|string|exists:orders,order_number', // Número do pedido do PDV
            'base_delivery_fee' => 'nullable|numeric|min:0',
            'delivery_discount_percent' => 'nullable|numeric|min:0',
            'delivery_discount_amount' => 'nullable|numeric',
            'delivery_fee_locked' => 'nullable|boolean',
        ]);

        // Se for um pedido do PDV e o carrinho estiver vazio, criar sessão do carrinho a partir dos itens do pedido
        if (empty($cart) && (($validated['order_id'] ?? null) || ($validated['order_number'] ?? null))) {
            $orderQuery = Order::query()->with('items.product');
            if ($validated['order_id']) {
                $orderQuery->where('id', $validated['order_id']);
            } else {
                $orderQuery->where('order_number', $validated['order_number']);
            }

            $pdvOrder = $orderQuery->first();

            // Verificar se o pedido PDV já foi pago
            if ($pdvOrder && in_array($pdvOrder->payment_status, ['approved', 'paid'])) {
                // Limpar carrinho e redirecionar para página de sucesso
                session()->forget('cart');
                session()->forget('cart_count');
                session()->forget('order_id');
                return redirect()->route('pedido.payment.success', ['order' => $pdvOrder->id])
                    ->with('info', 'Este pedido já foi finalizado e pago.');
            }

            if ($pdvOrder && $pdvOrder->items->count() > 0 && $pdvOrder->payment_status === 'pending') {
                // Criar carrinho a partir dos itens do pedido
                $cart = [];
                foreach ($pdvOrder->items as $item) {
                    $key = ($item->product_id ?? 0) . ':' . ($item->variant_id ?? 0);
                    $cart[$key] = [
                        'qty' => $item->quantity,
                        'price' => (float) $item->unit_price,
                        'special_instructions' => $item->special_instructions ?? null,
                    ];
                }
                session(['cart' => $cart]);
                \Log::info('OrderController:store - Carrinho criado a partir do pedido PDV', [
                    'order_id' => $pdvOrder->id,
                    'order_number' => $pdvOrder->order_number,
                    'items_count' => count($cart),
                ]);
            }
        }

        // Verificar novamente se o carrinho está vazio após tentar criar a partir do PDV
        $cart = session('cart', []);

        if (empty($cart)) {
            return redirect()->route('pedido.cart.index')
                ->with('error', 'Seu carrinho está vazio.');
        }

        // Normalizar CEP (mantém 8 dígitos ou generaliza sufixo)
        $zipDigits = preg_replace('/\D/', '', $validated['zip_code']);
        if (strlen($zipDigits) === 8) {
            // ok
        } elseif (strlen($zipDigits) >= 5) {
            $zipDigits = substr($zipDigits, 0, 5) . '000';
        } else {
            $zipDigits = $validated['zip_code']; // mantém como veio para validar posteriormente
        }
        $validated['zip_code'] = $zipDigits;

        // Verificar se há um pedido já pago na sessão (antes de criar novo)
        $sessionOrderId = session('order_id');
        if ($sessionOrderId) {
            $existingOrder = Order::find($sessionOrderId);
            // IMPORTANTE: Só limpar o carrinho se o pedido estiver realmente pago/aprovado
            if ($existingOrder && in_array($existingOrder->payment_status, ['approved', 'paid'])) {
                // Pedido já foi pago, limpar sessão e redirecionar para página de sucesso
                session()->forget('cart');
                session()->forget('cart_count');
                session()->forget('order_id');
                return redirect()->route('pedido.payment.success', ['order' => $existingOrder->id])
                    ->with('info', 'Este pedido já foi finalizado e pago.');
            } elseif ($existingOrder && $existingOrder->payment_status === 'pending') {
                // Pedido ainda está pendente - NÃO limpar o carrinho, permitir criar novo pedido
                // Apenas limpar o order_id da sessão para não causar confusão
                session()->forget('order_id');
            }
        }

        try {
            DB::beginTransaction();

            // 1. Buscar ou criar/atualizar cliente (usar telefone como chave única)
            // Usar updateOrCreate para atomicidade e evitar duplicatas
            $phoneNormalized = trim($validated['customer_phone']);

            // Normalizar telefone: remover caracteres não numéricos e garantir formato consistente
            $phoneDigits = preg_replace('/\D/', '', $phoneNormalized);
            // Se não começar com 55 (código do país), adicionar se tiver 10 ou 11 dígitos (formato brasileiro)
            if (strlen($phoneDigits) >= 10 && strlen($phoneDigits) <= 11 && !str_starts_with($phoneDigits, '55')) {
                $phoneDigits = '55' . $phoneDigits;
            }
            $phoneNormalized = $phoneDigits;

            // Filtrar apenas números do campo number
            $number = preg_replace('/\D/', '', $validated['number']);

            // Montar endereço completo para salvar no cliente
            $fullAddress = trim($validated['street'] . ', ' . $number);
            if (!empty($validated['complement'])) {
                $fullAddress .= ' - ' . $validated['complement'];
            }

            // Tentar encontrar cliente por telefone (com variações)
            $customer = Customer::where('phone', $phoneNormalized)->first();

            // Se não encontrou, tentar sem código do país
            if (!$customer && strlen($phoneNormalized) > 2 && str_starts_with($phoneNormalized, '55')) {
                $phoneWithoutCountry = substr($phoneNormalized, 2);
                $customer = Customer::where('phone', $phoneWithoutCountry)->first();
                if ($customer) {
                    // Atualizar telefone para incluir código do país
                    $customer->phone = $phoneNormalized;
                }
            }

            // Se ainda não encontrou, tentar com código do país
            if (!$customer && !str_starts_with($phoneNormalized, '55') && strlen($phoneNormalized) >= 10) {
                $phoneWithCountry = '55' . $phoneNormalized;
                $customer = Customer::where('phone', $phoneWithCountry)->first();
                if ($customer) {
                    // Cliente encontrado, manter telefone como está
                }
            }

            // Se não encontrou cliente existente, criar novo
            if (!$customer) {
                \Log::warning('OrderController:store - Cliente não encontrado, criando novo', [
                    'phone_normalized' => $phoneNormalized,
                    'phone_original' => $validated['customer_phone'],
                    'customer_name' => $validated['customer_name'],
                ]);

                // Verificar uma última vez se não existe cliente com telefone similar (busca mais ampla)
                $similarCustomer = Customer::whereRaw("REPLACE(REPLACE(REPLACE(REPLACE(phone,'(',''),')',''),'-',''),' ','') = ?", [preg_replace('/\D/', '', $phoneNormalized)])
                    ->orWhereRaw("REPLACE(REPLACE(REPLACE(REPLACE(phone,'(',''),')',''),'-',''),' ','') = ?", [preg_replace('/\D/', '', substr($phoneNormalized, 2))])
                    ->orWhereRaw("REPLACE(REPLACE(REPLACE(REPLACE(phone,'(',''),')',''),'-',''),' ','') = ?", ['55' . preg_replace('/\D/', '', $phoneNormalized)])
                    ->first();

                if ($similarCustomer) {
                    \Log::info('OrderController:store - Cliente similar encontrado, usando existente', [
                        'similar_customer_id' => $similarCustomer->id,
                        'similar_customer_phone' => $similarCustomer->phone,
                        'phone_searched' => $phoneNormalized,
                    ]);
                    $customer = $similarCustomer;
                    // Atualizar telefone para formato normalizado
                    $customer->phone = $phoneNormalized;
                } else {
                    $customer = Customer::create([
                        'name' => $validated['customer_name'],
                        'email' => $validated['customer_email'] ?? null,
                        'phone' => $phoneNormalized,
                        'address' => $fullAddress,
                        'neighborhood' => $validated['neighborhood'],
                        'city' => $validated['city'],
                        'state' => $validated['state'],
                        'zip_code' => preg_replace('/\D/', '', $validated['zip_code']),
                    ]);
                    \Log::info('OrderController:store - Novo cliente criado', [
                        'customer_id' => $customer->id,
                        'customer_phone' => $customer->phone,
                    ]);
                }
            } else {
                // Atualizar dados do cliente existente
                $customer->name = $validated['customer_name'];
                if (!empty($validated['customer_email'])) {
                    $customer->email = $validated['customer_email'];
                }
                $customer->address = $fullAddress;
                $customer->neighborhood = $validated['neighborhood'];
                $customer->city = $validated['city'];
                $customer->state = $validated['state'];
                $customer->zip_code = preg_replace('/\D/', '', $validated['zip_code']);
                $customer->save();
            }

            // Recarregar cliente do banco para garantir que temos todos os dados atualizados
            $customer->refresh();

            \Log::info('OrderController:store - Cliente processado', [
                'customer_id' => $customer->id,
                'customer_phone' => $customer->phone,
                'phone_normalized' => $phoneNormalized,
                'customer_created_at' => $customer->created_at,
            ]);

            // 2. Buscar ou criar endereço
            $address = Address::where('customer_id', $customer->id)
                ->where('street', $validated['street'])
                ->where('number', $number)
                ->where('cep', $validated['zip_code'])
                ->first();

            if (!$address) {
                $address = Address::create([
                    'customer_id' => $customer->id,
                    'street' => $validated['street'],
                    'number' => $number,
                    'complement' => $validated['complement'] ?? null,
                    'neighborhood' => $validated['neighborhood'],
                    'city' => $validated['city'],
                    'state' => $validated['state'],
                    'cep' => $validated['zip_code'],
                ]);
            }

            // 3. Calcular totais do carrinho
            $cartController = new CartController();
            [$count, $subtotal, $items] = $cartController->cartSummary($cart, true);

            // 4. Calcular frete (validar se foi calculado)
            $deliveryFee = 0.00;
            $fretePendente = false;
            $baseDeliveryFee = null;
            $deliveryDiscountPercent = null;
            $deliveryDiscountAmount = null;
            $deliveryDistanceKm = null;

            // Se o frete não foi fornecido na request, tentar calcular automaticamente
            $requestDeliveryFee = $request->input('delivery_fee');
            if ($requestDeliveryFee !== null && $requestDeliveryFee !== '') {
                $deliveryFee = (float) $requestDeliveryFee;
                // Se o frete veio da request, pode ter dados de desconto também
                $baseDeliveryFee = (float) ($request->input('base_delivery_fee') ?? $deliveryFee);
                $deliveryDiscountPercent = (float) ($request->input('delivery_discount_percent') ?? 0);
                $deliveryDiscountAmount = (float) ($request->input('delivery_discount_amount') ?? 0);
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
                            (float) $subtotal,
                            $customerPhone ?: null,
                            $customerEmail
                        );

                        if ($result['success']) {
                            $deliveryFee = $result['delivery_fee'];
                            $baseDeliveryFee = $result['base_delivery_fee'] ?? $deliveryFee;
                            $deliveryDiscountPercent = $result['discount_percent'] ?? 0;
                            $deliveryDiscountAmount = $result['discount_amount'] ?? 0.0;
                            $deliveryDistanceKm = $result['distance_km'] ?? null;
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
            // Tentar obter cupom da requisição, depois da sessão (para preservar em caso de erro)
            $couponCode = trim((string) (
                $request->input('applied_coupon_code')
                ?: $request->input('coupon_code', '')
                ?: session('checkout.applied_coupon_code', '')
            ));
            $appliedCoupon = null;

            if ($couponCode !== '') {
                // Usar scopes do modelo Coupon para garantir que está ativo, válido e disponível
                // Buscar cupom público OU direcionado ao cliente
                $couponQuery = \App\Models\Coupon::where('code', strtoupper($couponCode))
                    ->active()
                    ->valid()
                    ->available();

                // Incluir cupons públicos OU cupons direcionados ao cliente
                $couponQuery->where(function ($q) use ($customer) {
                    $q->where('visibility', 'public');
                    if ($customer->id) {
                        $q->orWhere(function ($subQ) use ($customer) {
                            $subQ->where('visibility', 'targeted')
                                ->where('target_customer_id', $customer->id);
                        });
                    }
                });

                $coupon = $couponQuery->first();

                if ($coupon) {
                    // Verificar se é cupom direcionado e se o cliente tem direito
                    if ($coupon->visibility === 'targeted' && $coupon->target_customer_id !== $customer->id) {
                        \Log::info('OrderController: Cupom direcionado não é para este cliente', [
                            'coupon_code' => $couponCode,
                            'customer_id' => $customer->id,
                            'target_customer_id' => $coupon->target_customer_id,
                        ]);
                        // Não aplicar desconto e continuar
                    } else {
                        // Verificar se é primeiro pedido (apenas pedidos pagos contam)
                        $isFirstOrder = !Order::where('customer_id', $customer->id)
                            ->whereIn('payment_status', ['approved', 'paid'])
                            ->exists();

                        // Validar elegibilidade
                        if ($coupon->isEligibleFor($customer->id, $subtotal, $deliveryFee, $isFirstOrder)) {
                            // Verificar se pode ser usado pelo cliente (inclui verificação de primeiro pedido)
                            if ($coupon->canBeUsedBy($customer->id)) {
                                // Se for cupom de frete grátis, aplicar desconto no frete
                                if ($coupon->free_shipping_only && $deliveryFee > 0) {
                                    $discountAmount = $coupon->calculateDiscount($subtotal) + $deliveryFee;
                                } else {
                                    $discountAmount = $coupon->calculateDiscount($subtotal);
                                }
                                $appliedCoupon = $coupon;
                            } else {
                                // Cupom não pode ser usado (limite atingido ou já usado) - ignorar e continuar sem cupom
                                \Log::info('OrderController: Cupom não pode ser usado - limite atingido ou já usado, continuando sem cupom', [
                                    'coupon_code' => $couponCode,
                                    'customer_id' => $customer->id,
                                ]);
                                // Não aplicar desconto e continuar
                            }
                        } else {
                            // Cupom não é elegível - ignorar e continuar sem cupom
                            \Log::info('OrderController: Cupom não é elegível, continuando sem cupom', [
                                'coupon_code' => $couponCode,
                                'customer_id' => $customer->id,
                                'subtotal' => $subtotal,
                                'delivery_fee' => $deliveryFee,
                                'is_first_order' => $isFirstOrder,
                            ]);
                            // Não aplicar desconto e continuar
                        }
                    }
                } else {
                    // Cupom não encontrado - ignorar e continuar sem cupom
                    \Log::info('OrderController: Cupom não encontrado, continuando sem cupom', [
                        'coupon_code' => $couponCode,
                    ]);
                    // Não aplicar desconto e continuar
                }
            }

            // Calcular subtotal após desconto do cupom
            $subtotalAfterCoupon = max(0, $subtotal - $discountAmount);

            // Aplicar cashback automaticamente se cliente tiver saldo disponível
            // IMPORTANTE: Recarregar o cliente do banco para garantir que temos o ID correto
            $customer->refresh();
            $cashbackBalance = CustomerCashback::getBalance($customer->id);
            $cashbackUsed = 0;

            \Log::info('OrderController:store - Verificando cashback do cliente', [
                'customer_id' => $customer->id,
                'customer_phone' => $customer->phone,
                'cashback_balance' => $cashbackBalance,
                'subtotal_after_coupon' => $subtotalAfterCoupon,
            ]);

            if ($cashbackBalance > 0 && $subtotalAfterCoupon > 0) {
                // Usar cashback disponível, limitado ao valor restante do pedido
                $cashbackUsed = min($cashbackBalance, $subtotalAfterCoupon);
                \Log::info('OrderController:store - Cashback será aplicado', [
                    'cashback_used' => $cashbackUsed,
                    'cashback_balance' => $cashbackBalance,
                    'subtotal_after_coupon' => $subtotalAfterCoupon,
                ]);
            } else {
                \Log::info('OrderController:store - Cashback não será aplicado', [
                    'reason' => $cashbackBalance <= 0 ? 'Sem saldo de cashback' : 'Subtotal após cupom é zero',
                    'cashback_balance' => $cashbackBalance,
                    'subtotal_after_coupon' => $subtotalAfterCoupon,
                ]);
            }

            // Calcular cashback gerado (sobre o valor final após abatimento do cashback usado)
            // O cashback gerado é calculado sobre o valor que o cliente realmente vai pagar
            // IMPORTANTE: Clientes de revenda (is_wholesale = 1) NÃO recebem cashback
            $isWholesale = $customer->is_wholesale ?? false;
            $cashbackEarned = 0;

            if (!$isWholesale) {
                // Buscar percentual de cashback das payment_settings (chave: cashback_percentage)
                $cashbackPercent = 5.0; // padrão
                try {
                    if (Schema::hasTable('payment_settings')) {
                        $val = DB::table('payment_settings')->where('key', 'cashback_percentage')->value('value');
                        if ($val !== null && $val !== '') {
                            $cashbackPercent = (float) $val;
                        }
                    }
                } catch (\Exception $e) {
                    // Mantém padrão se houver erro
                }
                // Cashback gerado é calculado sobre o valor final após abatimento do cashback usado
                // Exemplo: subtotal R$30, cashback usado R$1,25 → cashback ganho sobre R$28,75
                $finalSubtotalForCashback = max(0, $subtotalAfterCoupon - $cashbackUsed);
                $cashbackEarned = round($finalSubtotalForCashback * max(0, $cashbackPercent) / 100, 2);

                \Log::info('OrderController:store - Cálculo de cashback ganho', [
                    'subtotal' => $subtotal,
                    'subtotal_after_coupon' => $subtotalAfterCoupon,
                    'cashback_used' => $cashbackUsed,
                    'final_subtotal_for_cashback' => $finalSubtotalForCashback,
                    'cashback_percent' => $cashbackPercent,
                    'cashback_earned' => $cashbackEarned,
                ]);
            } else {
                \Log::info('OrderController:store - Cliente de revenda, cashback não aplicável', [
                    'customer_id' => $customer->id,
                    'is_wholesale' => $isWholesale,
                ]);
            }

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
                            $keyCol = collect(['key', 'name', 'config_key'])->first(fn($c) => Schema::hasColumn('settings', $c));
                            $valCol = collect(['value', 'val', 'config_value'])->first(fn($c) => Schema::hasColumn('settings', $c));
                            if ($keyCol && $valCol) {
                                $val = DB::table('settings')->where($keyCol, 'delivery_slot_capacity')->value($valCol);
                                if ($val !== null)
                                    $slotCapacity = (int) $val;
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
            $orderNotes = trim((string) ($request->input('notes', '') ?: ''));
            if ($fretePendente && $deliveryFee <= 0) {
                $fretePendenteNote = '⚠️ ATENÇÃO: Frete de entrega pendente de cálculo.';
                $orderNotes = $orderNotes ? ($orderNotes . "\n\n" . $fretePendenteNote) : $fretePendenteNote;
            }

            // 8. Criar pedido
            \Log::info('OrderController:store - Criando pedido com cashback', [
                'cashback_used' => $cashbackUsed,
                'cashback_earned' => $cashbackEarned,
                'subtotal' => $subtotal,
                'subtotal_after_coupon' => $subtotalAfterCoupon,
                'final_amount' => $finalAmount,
            ]);

            $order = Order::create([
                'client_id' => currentClientId() ?? 1, // Multi-tenant: salvar o client_id
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
                'payment_status' => 'pending', // Status inicial do pagamento
                'delivery_type' => 'delivery',
                'scheduled_delivery_at' => $scheduledDeliveryAt,
                'notes' => !empty($orderNotes) ? $orderNotes : ($validated['notes'] ?? null),
            ]);

            // Verificar se cashback foi salvo corretamente
            $order->refresh();
            \Log::info('OrderController:store - Pedido criado, verificando cashback salvo', [
                'order_id' => $order->id,
                'cashback_used_saved' => $order->cashback_used,
                'cashback_earned_saved' => $order->cashback_earned,
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
                $keyParts = explode(':', (string) $key);
                $productIdStr = $keyParts[0] ?? '0';
                $variantIdStr = $keyParts[1] ?? '0';
                // Se houver 'obs:' na chave, ignorar essas partes

                $productId = (int) $productIdStr;
                $variantId = (int) $variantIdStr ?: null;
                $qty = (int) ($row['qty'] ?? 1);
                $price = (float) ($row['price'] ?? 0);
                $specialInstructions = $row['special_instructions'] ?? null;

                $product = \App\Models\Product::find($productId);
                $productName = $product ? $product->name : "Produto #{$productId}";

                // Se tiver variante, incluir no nome
                if ($variantId) {
                    $variant = \App\Models\ProductVariant::find($variantId);
                    if ($variant) {
                        $productName .= ' (' . $variant->name . ')';
                    }
                }

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

            // 9. Salvar dados de desconto de frete no OrderDeliveryFee (se disponíveis)
            if (!$fretePendente && $deliveryFee > 0 && $baseDeliveryFee !== null) {
                try {
                    \App\Models\OrderDeliveryFee::updateOrCreate(
                        ['order_id' => $order->id],
                        [
                            'calculated_fee' => $baseDeliveryFee,
                            'final_fee' => $deliveryFee,
                            'distance_km' => $deliveryDistanceKm,
                            'order_value' => $subtotal,
                            'is_free_delivery' => ($deliveryFee <= 0 && $baseDeliveryFee > 0),
                            'is_manual_adjustment' => false,
                        ]
                    );
                    \Log::info('OrderController:store - Dados de desconto de frete salvos', [
                        'order_id' => $order->id,
                        'calculated_fee' => $baseDeliveryFee,
                        'final_fee' => $deliveryFee,
                        'discount_amount' => $deliveryDiscountAmount,
                        'discount_percent' => $deliveryDiscountPercent,
                    ]);
                } catch (\Exception $e) {
                    \Log::warning('OrderController:store - Erro ao salvar OrderDeliveryFee', [
                        'order_id' => $order->id,
                        'error' => $e->getMessage()
                    ]);
                    // Não bloquear o fluxo se falhar ao salvar
                }
            }

            DB::commit();

            \Log::info('OrderController:store - Pedido criado com sucesso', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'payment_method' => $validated['payment_method'],
            ]);

            // Rastrear compra finalizada
            try {
                \App\Models\AnalyticsEvent::trackPurchase($order->id, $customer->id, [
                    'order_number' => $order->order_number,
                    'final_amount' => $finalAmount,
                    'payment_method' => $validated['payment_method'],
                    'items_count' => count($cart),
                ]);
            } catch (\Exception $e) {
                \Log::warning('Erro ao rastrear compra', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);
            }

            // NÃO limpar o carrinho aqui - será limpo apenas após confirmação do pagamento
            // Isso permite que o usuário tente novamente se houver erro no checkout
            // session()->forget('cart');
            // session()->forget('cart_count');

            // Salvar dados na sessão para próxima compra
            session([
                'checkout.customer_name' => $validated['customer_name'],
                'checkout.customer_phone' => $validated['customer_phone'],
                'checkout.customer_email' => $validated['customer_email'] ?? '',
                'checkout.address' => $validated['street'],
                'checkout.number' => $number,
                'checkout.complement' => $validated['complement'] ?? '',
                'checkout.neighborhood' => $validated['neighborhood'],
                'checkout.city' => $validated['city'],
                'checkout.state' => $validated['state'],
                'checkout.zip_code' => $validated['zip_code'],
                // Preservar cupom aplicado na sessão para caso de erro
                'checkout.applied_coupon_code' => $appliedCoupon->code ?? null,
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

            // IMPORTANTE: NÃO limpar o carrinho em caso de erro - permite que o usuário tente novamente
            // O carrinho só deve ser limpo após confirmação do pagamento

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
            ->with(['customer', 'items.product', 'address', 'orderDeliveryFee'])
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
        $subtotal = (float) $order->total_amount;
        $deliveryFee = (float) ($order->delivery_fee ?? 0);
        $discountAmount = (float) ($order->discount_amount ?? 0);
        $finalAmount = (float) $order->final_amount;

        // Preparar dados para a view (similar ao checkout)
        $cartData = [
            'count' => $order->items->sum('quantity'),
            'subtotal' => $subtotal,
            'items' => $order->items->map(function ($item) {
                return [
                    'product_id' => $item->product_id,
                    'variant_id' => null,
                    'qty' => $item->quantity,
                    'price' => (float) $item->unit_price,
                    'subtotal' => (float) $item->total_price,
                    'name' => $item->custom_name ?? optional($item->product)->name ?? 'Item',
                    'variant' => null,
                    'image_url' => optional($item->product)->image_url ?? null,
                ];
            })->toArray(),
            'delivery_fee' => $deliveryFee,
        ];

        // Pré-preencher dados do cliente
        // Se não houver endereço na tabela addresses, usar dados do cliente
        $address = $order->address;
        $prefill = [
            'customer_name' => $order->customer->name ?? '',
            'customer_phone' => $order->customer->phone ?? '',
            'customer_email' => $order->customer->email ?? '',
            'street' => $address->street ?? ($order->customer->address ? explode(',', $order->customer->address)[0] ?? '' : ''),
            'number' => preg_replace('/\D/', '', $address->number ?? ($order->customer->address ? (explode(',', $order->customer->address)[1] ?? '') : '')),
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
                    $keyCol = collect(['key', 'name', 'config_key'])->first(fn($c) => Schema::hasColumn('settings', $c));
                    $valCol = collect(['value', 'val', 'config_value'])->first(fn($c) => Schema::hasColumn('settings', $c));
                    if ($keyCol && $valCol) {
                        $val = DB::table('settings')->where($keyCol, 'advance_order_days')->value($valCol);
                        if ($val !== null)
                            $advanceDays = (int) $val;
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

        $availableDates = [];
        $slotCapacity = 2; // padrão
        try {
            if (Schema::hasTable('settings')) {
                if (Schema::hasColumn('settings', 'delivery_slot_capacity')) {
                    $slotCapacity = (int) (DB::table('settings')->value('delivery_slot_capacity') ?? 2);
                } else {
                    $keyCol = collect(['key', 'name', 'config_key'])->first(fn($c) => Schema::hasColumn('settings', $c));
                    $valCol = collect(['value', 'val', 'config_value'])->first(fn($c) => Schema::hasColumn('settings', $c));
                    if ($keyCol && $valCol) {
                        $val = DB::table('settings')->where($keyCol, 'delivery_slot_capacity')->value($valCol);
                        if ($val !== null)
                            $slotCapacity = (int) $val;
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
                ->filter(function ($coupon) use ($isFirstOrder, $subtotal, $deliveryFee, $customerId) {
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

        $orderDeliveryFee = $order->orderDeliveryFee;
        $initialDeliveryFee = $order->delivery_fee;
        $initialBaseDeliveryFee = $orderDeliveryFee ? (float) ($orderDeliveryFee->calculated_fee ?? $orderDeliveryFee->final_fee ?? $initialDeliveryFee) : $initialDeliveryFee;
        $initialDeliveryDiscountAmount = 0.0;
        $initialDeliveryDiscountPercent = 0.0;

        if ($orderDeliveryFee) {
            $initialDeliveryDiscountAmount = max(0, (float) ($orderDeliveryFee->calculated_fee ?? 0) - (float) ($orderDeliveryFee->final_fee ?? 0));
            if (($orderDeliveryFee->calculated_fee ?? 0) > 0) {
                $initialDeliveryDiscountPercent = round(($initialDeliveryDiscountAmount / (float) $orderDeliveryFee->calculated_fee) * 100, 2);
            }
        }

        if ($initialBaseDeliveryFee === null && $initialDeliveryFee !== null) {
            $initialBaseDeliveryFee = $initialDeliveryFee;
        }

        return view('pedido.checkout', compact(
            'cartData',
            'availableDates',
            'advanceDays',
            'prefill',
            'eligibleCoupons',
            'cashbackBalance',
            'cashbackCustomer',
            'appliedCouponCode',
            'order', // Passar o pedido para identificar que é do PDV
            'initialDeliveryFee',
            'initialBaseDeliveryFee',
            'initialDeliveryDiscountAmount',
            'initialDeliveryDiscountPercent'
        ));
    }




    /**
     * Calcular descontos em tempo real (cupom e cashback)

     * Também retorna dados do cliente se identificado (para preencher endereço)
     */
    public function calculateDiscounts(Request $request)
    {
        $cart = session('cart', []);
        $subtotal = 0;
        $productIds = [];

        // Se carrinho estiver vazio, tentar buscar pedido do PDV
        if (empty($cart)) {
            $orderNumber = $request->input('order_number');
            $orderId = $request->input('order_id');

            if ($orderNumber || $orderId) {
                $orderQuery = Order::query()->with('items');

                if ($orderNumber) {
                    $orderQuery->where('order_number', $orderNumber);
                } elseif ($orderId) {
                    $orderQuery->where('id', $orderId);
                }

                $order = $orderQuery->first();

                if ($order && $order->items) {
                    // Calcular subtotal dos itens do pedido
                    foreach ($order->items as $item) {
                        $subtotal += (float) $item->total_price;
                        if ($item->product_id) {
                            $productIds[] = $item->product_id;
                        }
                    }
                } else {
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
                [$productIdStr] = array_pad(explode(':', (string) $key, 2), 2, '0');
                $productIds[] = (int) $productIdStr;
                $qty = (int) ($row['qty'] ?? 1);
                $price = (float) ($row['price'] ?? 0);
                $subtotal += $qty * $price;
            }
        }

        // Identificar cliente se fornecido (busca única, usada para tudo)
        $customerData = null;
        $customerId = null;
        $cashbackBalance = 0;
        $isFirstOrder = true; // Por padrão, assumir que é primeiro pedido (será redefinido se cliente identificado)
        $customerPhoneRaw = (string) $request->input('customer_phone', '');
        $customerPhone = preg_replace('/\D/', '', $customerPhoneRaw);
        $customerEmail = trim((string) $request->input('customer_email', ''));

        // Normalizar telefone: adicionar código do país se necessário
        if ($customerPhone && strlen($customerPhone) >= 10 && strlen($customerPhone) <= 11 && !str_starts_with($customerPhone, '55')) {
            $customerPhone = '55' . $customerPhone;
        }

        \Log::info('calculateDiscounts: Buscando cliente', [
            'customer_phone_raw' => $customerPhoneRaw,
            'customer_phone_normalized' => $customerPhone,
            'customer_email' => $customerEmail,
        ]);

        if ($customerPhone || $customerEmail) {
            $customer = null;

            // Tentar encontrar cliente por telefone normalizado primeiro
            if ($customerPhone && strlen($customerPhone) >= 10) {
                $customer = Customer::where('phone', $customerPhone)->first();

                // Se não encontrou, tentar sem código do país
                if (!$customer && strlen($customerPhone) > 2 && str_starts_with($customerPhone, '55')) {
                    $phoneWithoutCountry = substr($customerPhone, 2);
                    $customer = Customer::where('phone', $phoneWithoutCountry)->first();
                }

                // Se ainda não encontrou, tentar com código do país
                if (!$customer && !str_starts_with($customerPhone, '55') && strlen($customerPhone) >= 10) {
                    $phoneWithCountry = '55' . $customerPhone;
                    $customer = Customer::where('phone', $phoneWithCountry)->first();
                }
            }

            // Se não encontrou por telefone, tentar por email
            if (!$customer && $customerEmail) {
                $customer = Customer::where('email', $customerEmail)->first();
            }

            if ($customer) {
                $customerId = $customer->id;
                $cashbackBalance = CustomerCashback::getBalance($customer->id);

                \Log::info('calculateDiscounts: Cliente encontrado', [
                    'customer_id' => $customerId,
                    'customer_phone_db' => $customer->phone,
                    'cashback_balance' => $cashbackBalance,
                ]);

                // Verificar se é primeiro pedido (cliente existente com pedidos pagos)
                $hasPaidOrders = Order::where('customer_id', $customerId)
                    ->whereIn('payment_status', ['approved', 'paid'])
                    ->exists();
                $isFirstOrder = !$hasPaidOrders;

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

        // Calcular frete (estimado, usar o que vier na request ou calcular se cliente identificado)
        $deliveryFeeLocked = $request->boolean('delivery_fee_locked');
        $deliveryFee = (float) ($request->input('delivery_fee', 0));
        $baseDeliveryFee = (float) ($request->input('base_delivery_fee', $deliveryFee));
        $deliveryDiscountPercent = (float) ($request->input('delivery_discount_percent', 0));
        $deliveryDiscountAmount = (float) ($request->input('delivery_discount_amount', 0));

        if (!$deliveryFeeLocked) {
            $deliveryDiscountPercent = 0;
            $deliveryDiscountAmount = 0.0;
            $baseDeliveryFee = $deliveryFee;
        }

        // Se temos cliente identificado com endereço e o frete não está travado, calcular desconto progressivo
        if (!$deliveryFeeLocked && $customerData && isset($customerData['zip_code']) && !empty($customerData['zip_code'])) {
            try {
                $zipcode = preg_replace('/\D/', '', $customerData['zip_code']);
                if (strlen($zipcode) === 8) {
                    $deliveryFeeService = new \App\Services\DeliveryFeeService();
                    $feeResult = $deliveryFeeService->calculateDeliveryFee(
                        $zipcode,
                        (float) $subtotal,
                        $customerPhone ?: null,
                        $customerEmail ?: null
                    );

                    if ($feeResult['success']) {
                        $deliveryFee = $feeResult['delivery_fee'];
                        $baseDeliveryFee = $feeResult['base_delivery_fee'] ?? $feeResult['delivery_fee'];
                        $deliveryDiscountPercent = $feeResult['discount_percent'] ?? 0;
                        $deliveryDiscountAmount = $feeResult['discount_amount'] ?? 0.0;
                    }
                }
            } catch (\Exception $e) {
                // Manter valores originais se erro
            }
        }

        // Aplicar cupom se informado
        $couponCode = trim((string) ($request->input('coupon_code', '')));
        $couponDiscount = 0;
        $couponMessage = null;

        if ($couponCode !== '') {
            $coupon = \App\Models\Coupon::where('code', strtoupper($couponCode))
                ->active()
                ->valid()
                ->available()
                ->first();

            if ($coupon) {
                // Verificar se precisa de cliente identificado
                // Cupons de primeiro pedido não precisam de identificação obrigatória
                $needsCustomer = !$coupon->first_order_only && (
                    $coupon->visibility === 'targeted' ||
                    ($coupon->usage_limit_per_customer > 0 && $customerId === null)
                );

                if ($needsCustomer) {
                    // Cupom requer cliente identificado (targeted ou com limite por cliente não identificado)
                    $couponMessage = 'Identifique-se com telefone ou email para usar este cupom.';
                } else {
                    // Validar elegibilidade (cupons de primeiro pedido funcionam para clientes novos)
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
                    } elseif (!$coupon->canBeUsedBy($customerId)) {
                        // Verificar se pode ser usado (inclui verificação de primeiro pedido e limite por cliente)
                        // Para cupons de primeiro pedido, não verificar quando customer_id é null (cliente novo)
                        if ($coupon->first_order_only && $customerId === null) {
                            // Cupom de primeiro pedido para cliente novo, permitir sem verificar limite
                            $couponDiscount = $coupon->calculateDiscount($subtotal);
                        } else {
                            if ($coupon->first_order_only) {
                                $couponMessage = 'Este cupom é válido apenas para primeiro pedido e já foi utilizado.';
                            } else {
                                $couponMessage = 'Cupom não pode ser usado. Limite de uso atingido.';
                            }
                        }
                    } else {
                        // Cupom válido, aplicar desconto
                        // Cupons de frete grátis não são mais aplicados (desconto progressivo substitui)
                        // Aplicar apenas desconto no subtotal dos produtos
                        $couponDiscount = $coupon->calculateDiscount($subtotal);
                    }
                }
            } else {
                $couponMessage = 'Cupom inválido ou não encontrado.';
            }
        }

        // Calcular subtotal após cupom
        $subtotalAfterCoupon = max(0, $subtotal - $couponDiscount);

        // Buscar cupons elegíveis para mostrar no combobox (mesma lógica do checkout)
        $eligibleCouponsForDisplay = collect();
        try {
            // Buscar cupons públicos E cupons direcionados ao cliente específico
            $couponsQuery = \App\Models\Coupon::query()
                ->where('is_active', true)
                ->valid()
                ->available();

            // Incluir cupons públicos OU cupons direcionados ao cliente
            $couponsQuery->where(function ($q) use ($customerId) {
                $q->where('visibility', 'public');
                if ($customerId) {
                    $q->orWhere(function ($subQ) use ($customerId) {
                        $subQ->where('visibility', 'targeted')
                            ->where('target_customer_id', $customerId);
                    });
                }
            });

            $allCoupons = $couponsQuery->get();

            $eligibleCouponsForDisplay = $allCoupons->filter(function ($coupon) use ($customerId, $subtotal, $deliveryFee, $isFirstOrder) {
                // Cupons de frete grátis não são mais exibidos
                if ($coupon->free_shipping_only) {
                    return false;
                }

                // Verificar se é cupom direcionado e se o cliente tem direito
                if ($coupon->visibility === 'targeted') {
                    if (!$customerId || $coupon->target_customer_id !== $customerId) {
                        return false; // Cupom direcionado não é para este cliente
                    }
                }

                // Para cupons de primeiro pedido, verificar primeiro se é primeiro pedido
                // Cupons de primeiro pedido devem aparecer para clientes novos (mesmo sem customer_id)
                if ($coupon->first_order_only) {
                    if (!$isFirstOrder) {
                        return false; // Não é primeiro pedido, cupom não é elegível
                    }
                    // É primeiro pedido, verificar outras condições (valor mínimo, etc)
                    // Para cupons de primeiro pedido, verificar canBeUsedBy se cliente existe
                    if ($customerId && !$coupon->canBeUsedBy($customerId)) {
                        return false; // Cliente já usou este cupom de primeiro pedido
                    }
                    if (!$coupon->isValid($customerId)) {
                        return false; // Cupom não está ativo, válido ou disponível
                    }
                    // Verificar valor mínimo
                    if ($coupon->minimum_amount && $subtotal < $coupon->minimum_amount) {
                        return false;
                    }
                    // Cupom de primeiro pedido é elegível
                    return true;
                }

                // Para outros cupons (não first_order_only), verificar elegibilidade geral
                if (!$coupon->isEligibleFor($customerId, $subtotal, $deliveryFee, $isFirstOrder)) {
                    return false;
                }

                // IMPORTANTE: Verificar se o cliente pode usar o cupom (inclui verificação de uso único)
                // Se há customerId, sempre verificar canBeUsedBy para garantir que não foi usado antes
                if ($customerId && !$coupon->canBeUsedBy($customerId)) {
                    return false; // Cliente não pode usar este cupom (já usou, limite atingido, etc)
                }

                return true;
            })->values();
        } catch (\Exception $e) {
            \Log::error('calculateDiscounts: Erro ao filtrar cupons elegíveis', [
                'error' => $e->getMessage()
            ]);
            $eligibleCouponsForDisplay = collect();
        }

        // Buscar saldo de cashback do cliente (se não foi buscado antes)
        if (!isset($cashbackBalance)) {
            $cashbackBalance = 0;
            if ($customerId) {
                $cashbackBalance = CustomerCashback::getBalance($customerId);
                \Log::info('calculateDiscounts: Cashback balance buscado', [
                    'customer_id' => $customerId,
                    'cashback_balance' => $cashbackBalance,
                ]);
            }
        }

        // Aplicar cashback automaticamente se houver saldo disponível
        $cashbackUsed = 0;
        if ($cashbackBalance > 0 && $customerId && $subtotalAfterCoupon > 0) {
            $cashbackUsed = min($cashbackBalance, $subtotalAfterCoupon);
            \Log::info('calculateDiscounts: Cashback será aplicado', [
                'customer_id' => $customerId,
                'cashback_balance' => $cashbackBalance,
                'subtotal_after_coupon' => $subtotalAfterCoupon,
                'cashback_used' => $cashbackUsed,
            ]);
        } else {
            \Log::info('calculateDiscounts: Cashback não será aplicado', [
                'customer_id' => $customerId,
                'cashback_balance' => $cashbackBalance,
                'subtotal_after_coupon' => $subtotalAfterCoupon,
                'reason' => !$customerId ? 'Sem cliente identificado' : ($cashbackBalance <= 0 ? 'Sem saldo' : 'Subtotal zero'),
            ]);
        }

        // Calcular cashback ganho
        // Buscar percentual de cashback das payment_settings (chave: cashback_percentage)
        $cashbackPercent = 5.0;
        try {
            if (Schema::hasTable('payment_settings')) {
                $val = DB::table('payment_settings')->where('key', 'cashback_percentage')->value('value');
                if ($val !== null && $val !== '') {
                    $cashbackPercent = (float) $val;
                }
            }
        } catch (\Exception $e) {
        }

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

        $jsonResponse = [
            'subtotal' => round($subtotal, 2),
            'delivery_fee' => round($deliveryFee, 2),
            'base_delivery_fee' => round($baseDeliveryFee, 2),
            'delivery_discount_percent' => $deliveryDiscountPercent,
            'delivery_discount_amount' => round($deliveryDiscountAmount, 2),
            'coupon_discount' => round($couponDiscount, 2),
            'coupon_message' => $couponMessageFinal, // Mensagem de erro ou aviso sobre o cupom (sempre string ou null)
            'cashback_used' => round($cashbackUsed, 2),
            'cashback_earned' => round($cashbackEarned, 2),
            'cashback_balance' => round($cashbackBalance, 2),
            'total' => round($total, 2),
            'customer' => $customerData, // Dados do cliente para preencher endereço
            'eligible_coupons' => $eligibleCouponsForDisplay->map(function ($coupon) {
                return [
                    'code' => $coupon->code,
                    'name' => $coupon->name,
                    'formatted_value' => $coupon->formatted_value,
                    'minimum_amount' => $coupon->minimum_amount,
                ];
            })->toArray(),
        ];

        \Log::warning('calculateDiscounts: JSON final antes de retornar', [
            'coupon_message_in_response' => $jsonResponse['coupon_message'],
            'coupon_message_type_in_response' => gettype($jsonResponse['coupon_message']),
        ]);

        return response()->json($jsonResponse);
    }

    public function locateAddress(Request $request)
    {
        $validated = $request->validate([
            'street' => ['required', 'string', 'max:255'],
            'number' => ['required', 'string', 'max:50'],
            'complement' => ['nullable', 'string', 'max:255'],
            'neighborhood' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['required', 'string', 'max:10'],
            'zip_code' => ['nullable', 'string', 'max:20'],
            'customer_phone' => ['nullable', 'string', 'max:50'],
            'customer_email' => ['nullable', 'string', 'max:255'],
        ]);

        $address = [
            'street' => $validated['street'],
            'number' => $validated['number'],
            'complement' => $validated['complement'] ?? null,
            'neighborhood' => $validated['neighborhood'] ?? null,
            'city' => $validated['city'],
            'state' => strtoupper($validated['state']),
            'zip_code' => isset($validated['zip_code']) ? preg_replace('/\D/', '', $validated['zip_code']) : null,
        ];

        $cart = session('cart', []);
        $subtotal = 0;

        if (!empty($cart)) {
            $cartController = new CartController();
            [, $subtotal] = $cartController->cartSummary($cart, true);
        } else {
            $orderId = $request->input('order_id');
            $orderNumber = $request->input('order_number');
            if ($orderId || $orderNumber) {
                $orderQuery = Order::query()->with('items');
                if ($orderNumber) {
                    $orderQuery->where('order_number', $orderNumber);
                } else {
                    $orderQuery->where('id', $orderId);
                }
                $order = $orderQuery->first();
                if ($order && $order->items) {
                    foreach ($order->items as $item) {
                        $subtotal += (float) $item->total_price;
                    }
                }
            }
        }

        if ($subtotal <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Não foi possível identificar o subtotal do pedido.',
            ], 422);
        }

        $deliveryFeeService = new \App\Services\DeliveryFeeService();
        $result = $deliveryFeeService->calculateDeliveryFeeByAddress(
            $address,
            (float) $subtotal,
            $validated['customer_phone'] ?? null,
            $validated['customer_email'] ?? null
        );

        if ($result['success']) {
            $resolvedZip = $result['resolved_zip_code'] ?? $address['zip_code'];
            $normalizedZip = $this->normalizeZipToDistrict($resolvedZip) ?? $this->normalizeZipToDistrict($address['zip_code'] ?? null);

            session([
                'checkout.customer_phone' => $validated['customer_phone'] ?? session('checkout.customer_phone'),
                'checkout.customer_email' => $validated['customer_email'] ?? session('checkout.customer_email'),
                'checkout.address' => $validated['street'],
                'checkout.number' => $validated['number'],
                'checkout.complement' => $validated['complement'] ?? null,
                'checkout.neighborhood' => $validated['neighborhood'] ?? null,
                'checkout.city' => $validated['city'],
                'checkout.state' => strtoupper($validated['state']),
                'checkout.zip_code' => $normalizedZip ?? $address['zip_code'],
            ]);
        }

        return response()->json([
            'success' => $result['success'],
            'delivery_fee' => $result['delivery_fee'],
            'base_delivery_fee' => $result['base_delivery_fee'] ?? $result['delivery_fee'],
            'discount_percent' => $result['discount_percent'] ?? 0,
            'discount_amount' => $result['discount_amount'] ?? 0,
            'distance_km' => $result['distance_km'] ?? null,
            'free_shipping_applied' => $result['free'] ?? false,
            'resolved_zip_code' => $result['resolved_zip_code'] ?? null,
            'message' => $result['message'] ?? null,
            'error_code' => $result['error_code'] ?? null,
        ], $result['success'] ? 200 : 422);
    }

    public function lookupCustomer(Request $request)
    {
        $phone = preg_replace('/\D/', '', (string) $request->input('phone', ''));
        $email = trim((string) $request->input('email', ''));

        if ($phone === '' && $email === '') {
            return response()->json([
                'success' => false,
                'message' => 'Informe ao menos o telefone para localizar o cliente.',
            ], 422);
        }

        try {
            $customer = Customer::query()
                ->when($phone !== '', function ($query) use ($phone) {
                    $query->whereRaw("REPLACE(REPLACE(REPLACE(REPLACE(phone, '(', ''), ')', ''), '-', ''), ' ', '') = ?", [$phone]);
                })
                ->when($email !== '', function ($query) use ($email, $phone) {
                    if ($phone !== '') {
                        $query->orWhere('email', $email);
                    } else {
                        $query->where('email', $email);
                    }
                })
                ->orderByDesc('updated_at')
                ->first();

            if (!$customer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cliente não encontrado.',
                ], 404);
            }

            $addressParts = $customer->address ? explode(',', $customer->address, 2) : [null, null];
            $street = trim($addressParts[0] ?? '');
            $number = trim($addressParts[1] ?? '');

            if ($street === '' && !empty($customer->address)) {
                $street = $customer->address;
            }

            $prefill = [
                'customer_name' => $customer->name,
                'customer_phone' => $customer->phone,
                'customer_email' => $customer->email,
                'address' => $street,
                'number' => $number ?: $customer->number,
                'complement' => $customer->complement,
                'neighborhood' => $customer->neighborhood,
                'city' => $customer->city,
                'state' => $customer->state,
                'zip_code' => $customer->zip_code,
            ];

            session([
                'checkout.customer_name' => $prefill['customer_name'],
                'checkout.customer_phone' => $prefill['customer_phone'],
                'checkout.customer_email' => $prefill['customer_email'],
                'checkout.address' => $prefill['address'],
                'checkout.number' => $prefill['number'],
                'checkout.complement' => $prefill['complement'],
                'checkout.neighborhood' => $prefill['neighborhood'],
                'checkout.city' => $prefill['city'],
                'checkout.state' => $prefill['state'],
                'checkout.zip_code' => $prefill['zip_code'],
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'customer' => [
                        'id' => $customer->id,
                        'name' => $customer->name,
                        'phone' => $customer->phone,
                        'email' => $customer->email,
                        'zip_code' => $customer->zip_code,
                        'neighborhood' => $customer->neighborhood,
                        'city' => $customer->city,
                        'state' => $customer->state,
                    ],
                    'prefill' => $prefill,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('OrderController::lookupCustomer error', [
                'phone' => $phone,
                'email' => $email,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Não foi possível buscar o cliente. Tente novamente em instantes.',
            ], 500);
        }
    }

    /**
     * Gerar número único do pedido
     */
    private function generateOrderNumber(): string
    {
        $prefix = 'OLK';

        // Buscar o último número sequencial usado (formato OLK-0144-XXXXXX)
        // Extrair o número sequencial do segundo segmento (após OLK-)
        $lastOrder = Order::where('order_number', 'like', 'OLK-%')
            ->orderByRaw('CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(order_number, "-", 2), "-", -1) AS UNSIGNED) DESC')
            ->first();

        $sequenceNumber = 144; // Último pedido do sistema antigo

        if ($lastOrder && preg_match('/OLK-(\d+)-/', $lastOrder->order_number, $matches)) {
            $lastSequence = (int) $matches[1];
            if ($lastSequence >= 144) {
                $sequenceNumber = $lastSequence + 1;
            }
        }

        // Gerar 6 caracteres aleatórios (letras maiúsculas e números)
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $randomSuffix = '';
        for ($i = 0; $i < 6; $i++) {
            $randomSuffix .= $characters[rand(0, strlen($characters) - 1)];
        }

        // Formato: OLK-0145-ABC123
        $orderNumber = $prefix . '-' . str_pad((string) $sequenceNumber, 4, '0', STR_PAD_LEFT) . '-' . $randomSuffix;

        // Verificar se já existe (muito improvável, mas por segurança)
        while (Order::where('order_number', $orderNumber)->exists()) {
            $randomSuffix = '';
            for ($i = 0; $i < 6; $i++) {
                $randomSuffix .= $characters[rand(0, strlen($characters) - 1)];
            }
            $orderNumber = $prefix . '-' . str_pad((string) $sequenceNumber, 4, '0', STR_PAD_LEFT) . '-' . $randomSuffix;
        }

        return $orderNumber;
    }

    private function normalizeZipToDistrict(?string $zip): ?string
    {
        if (!$zip) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $zip);
        if (strlen($digits) >= 8) {
            return substr($digits, 0, 5) . '000';
        }

        if (strlen($digits) >= 5) {
            return substr($digits, 0, 5) . '000';
        }

        return null;
    }
}
