<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Address;
use App\Models\CustomerDebt;
use App\Models\Coupon;
use App\Models\Setting;
use App\Models\OrderDeliveryFee;
use App\Models\DeliverySchedule;
use App\Services\MercadoPagoApi;
use App\Services\WhatsAppService;
use App\Services\DistanceCalculatorService;
use App\Models\DeliveryFee;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class PDVController extends Controller
{
    public function index()
    {
        // Carregar produtos ativos com preços de revenda
        // Ordenar por mais vendidos (últimos 90 dias)
        $products = Product::where('products.is_active', true)
            ->with(['variants', 'wholesalePrices'])
            ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
            ->leftJoin('orders', function($join) {
                $join->on('order_items.order_id', '=', 'orders.id')
                     ->where('orders.payment_status', 'paid')
                     ->where('orders.created_at', '>=', now()->subDays(90));
            })
            ->select('products.*', DB::raw('COALESCE(SUM(order_items.quantity), 0) as total_sold'))
            ->groupBy('products.id')
            ->orderBy('total_sold', 'desc')
            ->orderBy('products.name', 'asc') // Ordenação secundária por nome
            ->get();

        // Carregar clientes recentes (últimos 50)
        $recentCustomers = Customer::orderBy('created_at', 'desc')
            ->limit(50)
            ->get(['id', 'name', 'phone', 'email', 'is_wholesale']);

        $availableDates = $this->getAvailableDeliveryDates();

        return view('dashboard.pdv.index', compact('products', 'recentCustomers', 'availableDates'));
    }

    private function getAvailableDeliveryDates(): array
    {
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
            // Mantém padrão
        }

        $deliverySchedules = DeliverySchedule::where('is_active', true)
            ->get()
            ->groupBy('day_of_week');

        $availableDates = [];
        $slotCapacity = 2;
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

        $today = Carbon::today();
        for ($i = $advanceDays; $i <= $advanceDays + 13; $i++) {
            $checkDate = $today->copy()->addDays($i);
            $dayOfWeek = strtolower($checkDate->format('l'));

            if ($deliverySchedules->has($dayOfWeek)) {
                $schedules = $deliverySchedules[$dayOfWeek]->filter(fn($s) => $s->is_active);
                if ($schedules->count() > 0) {
                    $slots = [];
                    foreach ($schedules as $schedule) {
                        $start = Carbon::today()->setTimeFromTimeString($schedule->start_time->format('H:i'));
                        $end = Carbon::today()->setTimeFromTimeString($schedule->end_time->format('H:i'));

                        while ($start < $end) {
                            $slotStart = $start->copy();
                            $slotEnd = $start->copy()->addMinutes(30);

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

        return $availableDates;
    }

    public function searchCustomers(Request $request)
    {
        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return response()->json(['customers' => []]);
        }

        $customers = Customer::with('addresses')
            ->where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('phone', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%");
            })
            ->orderBy('name')
            ->distinct('id')
            ->limit(20)
            ->get(['id', 'name', 'phone', 'email', 'address', 'neighborhood', 'city', 'state', 'zip_code', 'custom_delivery_fee', 'is_wholesale']);

        // Adicionar endereço principal (primeiro endereço da tabela addresses) se existir
        $customers->each(function($customer) {
            if ($customer->addresses && $customer->addresses->isNotEmpty()) {
                $mainAddress = $customer->addresses->first();
                // Se o cliente não tem endereço nos campos diretos, usar o da tabela addresses
                if (empty($customer->address) && $mainAddress->street) {
                    $customer->address = $mainAddress->street . ($mainAddress->number ? ', ' . $mainAddress->number : '');
                }
                if (empty($customer->neighborhood) && $mainAddress->neighborhood) {
                    $customer->neighborhood = $mainAddress->neighborhood;
                }
                if (empty($customer->city) && $mainAddress->city) {
                    $customer->city = $mainAddress->city;
                }
                if (empty($customer->state) && $mainAddress->state) {
                    $customer->state = $mainAddress->state;
                }
                if (empty($customer->zip_code) && $mainAddress->cep) {
                    $customer->zip_code = $mainAddress->cep;
                }
                // Adicionar address_id para uso posterior
                $customer->address_id = $mainAddress->id;
            }
        });

        return response()->json(['customers' => $customers]);
    }

    public function searchProducts(Request $request)
    {
        $query = $request->get('q', '');
        $customerId = $request->get('customer_id');
        $productId = $request->get('product_id'); // Para buscar produto específico
        $isWholesale = false;
        
        // Verificar se o cliente é de revenda
        if ($customerId) {
            $customer = Customer::find($customerId);
            $isWholesale = $customer && $customer->is_wholesale;
        }
        
        $productsQuery = Product::where('products.is_active', true);
        
        // Se foi passado product_id, buscar produto específico
        if ($productId) {
            $productsQuery->where('id', $productId);
        } elseif ($query) {
            // Caso contrário, buscar por nome/descrição
            $productsQuery->where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%");
            });
        }
        
        $products = $productsQuery
            ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
            ->leftJoin('orders', function($join) {
                $join->on('order_items.order_id', '=', 'orders.id')
                     ->where('orders.payment_status', 'paid')
                     ->where('orders.created_at', '>=', now()->subDays(90));
            })
            ->select('products.*', DB::raw('COALESCE(SUM(order_items.quantity), 0) as total_sold'))
            ->groupBy('products.id')
            ->orderBy('total_sold', 'desc')
            ->orderBy('products.name', 'asc')
            ->with(['variants' => function($q) {
                $q->where('is_active', true)->orderBy('sort_order');
            }, 'wholesalePrices' => function($q) {
                $q->where('is_active', true)->orderBy('min_quantity');
            }])
            ->limit(20)
            ->get(['id', 'name', 'price', 'description']);

        // Formatar produtos com variantes e preços diferenciados
        $formattedProducts = $products->map(function($product) use ($isWholesale) {
            $variants = $product->variants->map(function($variant) use ($product, $isWholesale) {
                $price = (float)$variant->price;
                
                // Se for wholesale, buscar preço diferenciado
                if ($isWholesale) {
                    $wholesalePrice = \App\Models\ProductWholesalePrice::getWholesalePrice($product->id, $variant->id, 1);
                    if ($wholesalePrice !== null) {
                        $price = $wholesalePrice;
                    }
                }
                
                return [
                    'id' => $variant->id,
                    'name' => $variant->name,
                    'price' => $price,
                ];
            })->toArray();
            
            $price = (float)$product->price;
            
            // Se for wholesale, buscar preço diferenciado do produto
            if ($isWholesale) {
                $wholesalePrice = \App\Models\ProductWholesalePrice::getWholesalePrice($product->id, null, 1);
                if ($wholesalePrice !== null) {
                    $price = $wholesalePrice;
                }
            }
            
            return [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $price,
                'description' => $product->description,
                'has_variants' => count($variants) > 0,
                'variants' => $variants,
            ];
        });

        return response()->json(['products' => $formattedProducts]);
    }

    public function storeCustomer(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'is_wholesale' => 'nullable|boolean',
            'address' => 'nullable|array',
            'address.zip_code' => 'nullable|string|max:10',
            'address.street' => 'nullable|string|max:255',
            'address.number' => 'nullable|string|max:30',
            'address.complement' => 'nullable|string|max:255',
            'address.neighborhood' => 'nullable|string|max:255',
            'address.city' => 'nullable|string|max:255',
            'address.state' => 'nullable|string|max:2',
        ]);

        try {
            DB::beginTransaction();

            // Preparar dados do cliente
            $customerData = [
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'is_wholesale' => $request->has('is_wholesale') && $request->is_wholesale ? 1 : 0,
            ];

            // Se houver dados de endereço, atualizar também no cliente
            if ($request->filled('address')) {
                $addressData = $request->address;
                $zipCode = !empty($addressData['zip_code']) ? preg_replace('/\D/', '', $addressData['zip_code']) : null;
                
                if ($zipCode) {
                    $customerData['zip_code'] = $zipCode;
                }
                
                // Montar endereço completo para salvar no cliente
                if (!empty($addressData['street'])) {
                    $fullAddress = trim($addressData['street']);
                    if (!empty($addressData['number'])) {
                        $fullAddress .= ', ' . $addressData['number'];
                    }
                    if (!empty($addressData['complement'])) {
                        $fullAddress .= ' - ' . $addressData['complement'];
                    }
                    $customerData['address'] = $fullAddress;
                }
                
                if (!empty($addressData['neighborhood'])) {
                    $customerData['neighborhood'] = $addressData['neighborhood'];
                }
                if (!empty($addressData['city'])) {
                    $customerData['city'] = $addressData['city'];
                }
                if (!empty($addressData['state'])) {
                    $customerData['state'] = strtoupper($addressData['state']);
                }
            }

            $customer = Customer::create($customerData);

            // Criar endereço na tabela addresses se fornecido
            if ($request->filled('address') && !empty($request->address['street'])) {
                $addressData = $request->address;
                $zipCode = !empty($addressData['zip_code']) ? preg_replace('/\D/', '', $addressData['zip_code']) : '';
                
                // Validar se tem dados mínimos para criar endereço
                if (!empty($addressData['street']) && !empty($zipCode) && !empty($addressData['city']) && !empty($addressData['state'])) {
                    Address::create([
                        'customer_id' => $customer->id,
                        'cep' => $zipCode,
                        'street' => $addressData['street'],
                        'number' => $addressData['number'] ?? '',
                        'complement' => $addressData['complement'] ?? null,
                        'neighborhood' => $addressData['neighborhood'] ?? null,
                        'city' => $addressData['city'],
                        'state' => strtoupper($addressData['state']),
                    ]);
                    
                    Log::info('PDV: Endereço criado para novo cliente', [
                        'customer_id' => $customer->id,
                        'zip_code' => $zipCode,
                    ]);
                }
            }

            DB::commit();

            // Carregar relacionamentos para retornar dados completos
            $customer->load('addresses');

            return response()->json(['customer' => $customer]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PDV: Erro ao criar cliente', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Erro ao criar cliente: ' . $e->getMessage()
            ], 500);
        }
    }

    public function validateCoupon(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'subtotal' => 'required|numeric|min:0',
        ]);

        // Usar scopes do modelo Coupon (valid e available usam starts_at e expires_at)
        $coupon = Coupon::where('code', strtoupper($request->code))
            ->active()
            ->valid()
            ->available()
            ->first();

        if (!$coupon) {
            return response()->json([
                'valid' => false,
                'message' => 'Cupom inválido ou expirado'
            ]);
        }

        $discount = $coupon->calculateDiscount($request->subtotal);

        return response()->json([
            'valid' => true,
            'coupon' => [
                'code' => $coupon->code,
                'name' => $coupon->name,
                'type' => $coupon->type,
                'value' => $coupon->value,
            ],
            'discount' => $discount,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|integer|exists:customers,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|integer|exists:products,id',
            'items.*.name' => 'required|string|max:255',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.quantity' => 'required|integer|min:1',
            'delivery_type' => 'required|in:delivery,pickup',
            'address_id' => 'nullable|integer|exists:addresses,id',
            'delivery_fee' => 'nullable|numeric|min:0',
            'coupon_code' => 'nullable|string|max:64',
            'discount_amount' => 'nullable|numeric|min:0',
            'manual_discount_fixed' => 'nullable|numeric|min:0',
            'manual_discount_percent' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string|max:1000',
            'send_payment_link' => 'nullable|boolean', // Se deve enviar link de pagamento ao cliente
            'payment_method' => 'nullable|in:pix,credit_card,debit_card', // Método se enviar link
            'create_as_paid' => 'nullable|boolean', // Criar pedido já como pago
            'skip_notification' => 'nullable|boolean', // Pular notificações
            'scheduled_delivery_slot' => 'required|string', // Agendamento (obrigatório)
        ]);

        try {
            DB::beginTransaction();

            $customer = Customer::findOrFail($request->customer_id);

            // Calcular totais
            $subtotal = collect($request->items)->sum(function($item) {
                return (float)$item['price'] * (int)$item['quantity'];
            });

            $deliveryFee = (float)($request->delivery_fee ?? 0);
            
            // Desconto do cupom
            $couponDiscount = 0;
            $couponCode = $request->coupon_code;
            if ($couponCode) {
                // Usar scopes do modelo Coupon (valid e available usam starts_at e expires_at)
                $coupon = Coupon::where('code', strtoupper($couponCode))
                    ->active()
                    ->valid()
                    ->available()
                    ->first();
                
                if ($coupon && $coupon->canBeUsedBy($customer->id)) {
                    $couponDiscount = $coupon->calculateDiscount($subtotal);
                    $couponCode = $coupon->code;
                } else {
                    $couponCode = null;
                }
            }
            
            // Desconto manual (fixo e porcentagem)
            $manualDiscountFixed = (float)($request->manual_discount_fixed ?? 0);
            $manualDiscountPercent = (float)($request->manual_discount_percent ?? 0);
            $manualDiscountFromPercent = $subtotal * ($manualDiscountPercent / 100);
            
            // Total de desconto (cupom + manual fixo + manual porcentagem)
            // Se discount_amount foi enviado diretamente, usar ele (já calculado no frontend)
            $totalDiscount = (float)($request->discount_amount ?? 0);
            if ($totalDiscount == 0) {
                // Se não foi enviado, calcular aqui
                $totalDiscount = $couponDiscount + $manualDiscountFixed + $manualDiscountFromPercent;
            }
            
            $finalAmount = max(0, $subtotal + $deliveryFee - $totalDiscount);

            // Gerar número do pedido
            $orderNumber = $this->generateOrderNumber();
            
            // Determinar status inicial e payment_status
            $createAsPaid = $request->has('create_as_paid') && $request->create_as_paid;
            $initialStatus = $createAsPaid ? 'confirmed' : 'pending';
            $initialPaymentStatus = $createAsPaid ? 'paid' : 'pending';

            // Processar agendamento
            $scheduledDeliveryAt = null;
            try {
                $scheduledDeliveryAt = Carbon::createFromFormat('Y-m-d H:i', $request->scheduled_delivery_slot);
            } catch (\Exception $e) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'scheduled_delivery_slot' => ['Horário de entrega inválido.']
                ]);
            }

            $clientId = currentClientId();
            // Criar pedido
            $order = Order::create([
                'client_id' => $clientId,
                'customer_id' => $customer->id,
                'address_id' => $request->address_id,
                'order_number' => $orderNumber,
                'status' => $initialStatus,
                'total_amount' => $subtotal,
                'delivery_fee' => $deliveryFee,
                'discount_amount' => $totalDiscount,
                'coupon_code' => $couponCode,
                'final_amount' => $finalAmount,
                'payment_method' => $request->payment_method ?? 'pix',
                'payment_status' => $initialPaymentStatus,
                'delivery_type' => $request->delivery_type,
                'scheduled_delivery_at' => $scheduledDeliveryAt,
                'notes' => $request->notes,
            ]);
            
            // Se criar como pago, marcar como já notificado e solicitar impressão automática
            if ($createAsPaid) {
                $order->notified_paid_at = now();
                $order->print_requested_at = now(); // Solicitar impressão automática
                $order->save();
                
                Log::info('PDV: Pedido criado como pago - impressão automática solicitada', [
                    'order_id' => $order->id,
                    'order_number' => $orderNumber,
                ]);
            }

            // Criar itens
            foreach ($request->items as $itemData) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $itemData['product_id'] ?? null,
                    'quantity' => (int)$itemData['quantity'],
                    'unit_price' => (float)$itemData['price'],
                    'total_price' => (float)$itemData['price'] * (int)$itemData['quantity'],
                    'custom_name' => $itemData['name'],
                    'special_instructions' => $itemData['special_instructions'] ?? null,
                ]);
            }

            try {
                OrderDeliveryFee::updateOrCreate(
                    ['order_id' => $order->id],
                    [
                        'calculated_fee' => $deliveryFee,
                        'final_fee' => $deliveryFee,
                        'distance_km' => null,
                        'order_value' => $subtotal,
                        'is_free_delivery' => $deliveryFee <= 0,
                        'is_manual_adjustment' => true,
                    ]
                );
            } catch (\Exception $e) {
                Log::warning('PDV: Erro ao registrar taxa de entrega (store)', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);
            }

            // Se não enviar link de pagamento, criar como débito
            if (!$request->send_payment_link) {
                CustomerDebt::create([
                    'customer_id' => $customer->id,
                    'order_id' => $order->id,
                    'amount' => $finalAmount,
                    'type' => 'debit',
                    'status' => 'open',
                    'description' => "Pedido #{$orderNumber} - Venda fiado",
                ]);

                Log::info('PDV: Pedido criado como débito', [
                    'order_id' => $order->id,
                    'order_number' => $orderNumber,
                    'customer_id' => $customer->id,
                    'amount' => $finalAmount,
                ]);
            } else {
                // Criar link de pagamento via Mercado Pago
                try {
                    $mpApi = new MercadoPagoApi();
                    
                    // Preparar itens para Mercado Pago
                    $mpItems = collect($request->items)->map(function($item) {
                        return [
                            'title' => $item['name'],
                            'quantity' => (int)$item['quantity'],
                            'unit_price' => (float)$item['price'],
                        ];
                    })->toArray();

                    // Verificar opção PIX (display_qr ou send_whatsapp)
                    $pixOption = $request->input('pix_option'); // null, 'display_qr', ou 'send_whatsapp'
                    $paymentMethod = $request->payment_method ?? 'pix';
                    
                    // Se PIX, criar pagamento PIX diretamente
                    if ($paymentMethod === 'pix' && ($pixOption === 'display_qr' || $pixOption === 'send_whatsapp')) {
                        // Criar pagamento PIX diretamente (não link genérico)
                        $pixPayment = $mpApi->createPixPreference($order, $customer, $mpItems);
                        
                        $order->update([
                            'payment_provider' => 'mercadopago',
                            'payment_id' => $pixPayment['preference_id'] ?? null,
                            'pix_qr_base64' => $pixPayment['qr_base64'] ?? null,
                            'pix_copy_paste' => $pixPayment['copia_cola'] ?? null,
                            'pix_expires_at' => !empty($pixPayment['expires_at']) ? Carbon::parse($pixPayment['expires_at']) : null,
                            'payment_status' => 'pending',
                        ]);
                        
                        // Se opção for send_whatsapp, enviar QR Code via WhatsApp
                        if ($pixOption === 'send_whatsapp' && $customer->phone) {
                            $this->sendPixViaWhatsApp($order, $customer, $finalAmount, $orderNumber);
                        }
                        
                        Log::info('PDV: Pagamento PIX criado', [
                            'order_id' => $order->id,
                            'order_number' => $orderNumber,
                            'pix_option' => $pixOption,
                        ]);
                    } else {
                        // Usar link genérico do Mercado Pago (permite escolha de método)
                        $pref = $mpApi->createPaymentLinkFromOrder($order, $customer, $mpItems);

                        $order->update([
                            'payment_provider' => 'mercadopago',
                            'preference_id' => $pref['preference_id'] ?? null,
                            'payment_link' => $pref['checkout_url'] ?? null,
                            'pix_qr_base64' => $pref['qr_base64'] ?? null,
                            'pix_copy_paste' => $pref['copia_cola'] ?? null,
                            'pix_expires_at' => !empty($pref['expires_at']) ? Carbon::parse($pref['expires_at']) : null,
                            'payment_status' => 'pending',
                        ]);

                        // Enviar mensagem ao cliente via WhatsApp (se configurado)
                        if ($customer->phone) {
                            try {
                                $whatsappService = new WhatsAppService();
                                if ($whatsappService->isEnabled()) {
                                    $message = "Olá, {$customer->name}! 🛒\n\n";
                                    $message .= "Seu pedido #{$orderNumber} foi criado!\n\n";
                                    $message .= "Total: R$ " . number_format($finalAmount, 2, ',', '.') . "\n\n";
                                    
                                    if ($request->payment_method === 'pix') {
                                        $message .= "Para pagar via PIX, acesse:\n";
                                    } else {
                                        $message .= "Para finalizar o pagamento, acesse:\n";
                                    }
                                    
                                    $phoneParam = urlencode(preg_replace('/\D/', '', $customer->phone));
                                    $paymentUrl = route('customer.orders.show', [
                                        'order' => $orderNumber,
                                        'phone' => $phoneParam
                                    ]);
                                    
                                    $message .= $paymentUrl . "\n\n";
                                    $message .= "Após pagar, você poderá escolher o agendamento de entrega e finalizar o pedido.";

                                    // Normalizar telefone antes de enviar
                                    $phoneNormalized = preg_replace('/\D/', '', $customer->phone);
                                    if (strlen($phoneNormalized) >= 10 && !str_starts_with($phoneNormalized, '55')) {
                                        $phoneNormalized = '55' . $phoneNormalized;
                                    }
                                    
                                    // Enviar mensagem via WhatsApp
                                    $result = $whatsappService->sendText($phoneNormalized, $message);
                                    
                                    if (isset($result['success']) && $result['success']) {
                                        Log::info('PDV: Mensagem enviada ao cliente via WhatsApp', [
                                            'order_id' => $order->id,
                                            'customer_phone_original' => $customer->phone,
                                            'phone_normalized' => $phoneNormalized,
                                        ]);
                                    } else {
                                        Log::warning('PDV: Falha ao enviar mensagem via WhatsApp', [
                                            'order_id' => $order->id,
                                            'customer_phone_original' => $customer->phone,
                                            'phone_normalized' => $phoneNormalized,
                                            'error' => $result['error'] ?? 'Erro desconhecido',
                                        ]);
                                    }
                                } else {
                                    Log::warning('PDV: WhatsApp não configurado, mensagem não enviada', [
                                        'order_id' => $order->id,
                                    ]);
                                }
                            } catch (\Exception $e) {
                                Log::warning('PDV: Erro ao enviar mensagem ao cliente', [
                                    'order_id' => $order->id,
                                    'error' => $e->getMessage(),
                                ]);
                            }
                        }

                        Log::info('PDV: Link de pagamento criado', [
                            'order_id' => $order->id,
                            'order_number' => $orderNumber,
                            'payment_link' => $order->payment_link,
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error('PDV: Erro ao criar link de pagamento', [
                        'order_id' => $order->id,
                        'error' => $e->getMessage(),
                    ]);
                    // Continuar mesmo se falhar - o pedido já foi criado
                }
            }

            DB::commit();

            // Preparar resposta com order_id para PIX display_qr
            $responseData = [
                'success' => true,
                'order_id' => $order->id,
                'order' => [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'total' => $finalAmount,
                    'payment_link' => $order->payment_link,
                    'is_debt' => !$request->send_payment_link,
                ],
                'message' => $request->send_payment_link 
                    ? 'Pedido criado e link de pagamento enviado ao cliente!' 
                    : 'Pedido criado como débito!',
            ];
            
            // Se PIX display_qr, incluir flag
            if ($paymentMethod === 'pix' && $pixOption === 'display_qr') {
                $responseData['pix_display_qr'] = true;
            }
            
            return response()->json($responseData);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PDV: Erro ao criar pedido', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar pedido: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Envia pedido ao cliente via WhatsApp
     * Cria o pedido e envia resumo + link para cliente escolher data/hora e pagamento
     */
    public function send(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|integer|exists:customers,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|integer|exists:products,id',
            'items.*.name' => 'required|string|max:255',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.quantity' => 'required|integer|min:1',
            'delivery_type' => 'required|in:delivery,pickup',
            'address_id' => 'nullable|integer|exists:addresses,id',
            'delivery_fee' => 'nullable|numeric|min:0',
            'coupon_code' => 'nullable|string|max:64',
            'discount_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
            'zip_code' => 'nullable|string|max:10', // CEP do destino
            'address' => 'nullable|array', // Dados de endereço completos
        ]);

        try {
            DB::beginTransaction();

            $customer = Customer::findOrFail($request->customer_id);

            // Verificar se cliente tem telefone
            if (!$customer->phone) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cliente não possui telefone cadastrado para receber o pedido.',
                ], 400);
            }
            
            // Atualizar CEP e endereço do cliente se fornecido
            if ($request->filled('zip_code')) {
                $zipCode = preg_replace('/\D/', '', $request->zip_code);
                $customer->zip_code = $zipCode;
                
                // Se houver dados de endereço, atualizar também
                if ($request->filled('address')) {
                    $addressData = $request->address;
                    if (!empty($addressData['street'])) {
                        $customer->address = trim(($addressData['street'] ?? '') . ', ' . ($addressData['number'] ?? ''));
                    }
                    if (!empty($addressData['neighborhood'])) {
                        $customer->neighborhood = $addressData['neighborhood'];
                    }
                    if (!empty($addressData['city'])) {
                        $customer->city = $addressData['city'];
                    }
                    if (!empty($addressData['state'])) {
                        $customer->state = $addressData['state'];
                    }
                }
                
                $customer->save();
                
                // Criar ou atualizar endereço na tabela addresses se fornecido
                if ($request->filled('address') && !empty($request->address['street'])) {
                    $addressData = $request->address;
                    $zipCodeClean = preg_replace('/\D/', '', $request->zip_code);
                    
                    $address = \App\Models\Address::updateOrCreate(
                        [
                            'customer_id' => $customer->id,
                            'cep' => $zipCodeClean,
                        ],
                        [
                            'street' => $addressData['street'] ?? '',
                            'number' => $addressData['number'] ?? '',
                            'complement' => $addressData['complement'] ?? null,
                            'neighborhood' => $addressData['neighborhood'] ?? '',
                            'city' => $addressData['city'] ?? '',
                            'state' => $addressData['state'] ?? '',
                        ]
                    );
                }
            }

            // Calcular totais
            $subtotal = collect($request->items)->sum(function($item) {
                return (float)$item['price'] * (int)$item['quantity'];
            });

            $deliveryFee = (float)($request->delivery_fee ?? 0);
            $discountAmount = (float)($request->discount_amount ?? 0);
            $finalAmount = max(0, $subtotal + $deliveryFee - $discountAmount);

            // Validar cupom se fornecido
            $couponCode = $request->coupon_code;
            if ($couponCode) {
                // Usar scopes do modelo Coupon (valid e available usam starts_at e expires_at)
                $coupon = Coupon::where('code', strtoupper($couponCode))
                    ->active()
                    ->valid()
                    ->available()
                    ->first();
                
                if ($coupon && $coupon->canBeUsedBy($customer->id)) {
                    $discountFromCoupon = $coupon->calculateDiscount($subtotal);
                    $discountAmount = max($discountAmount, $discountFromCoupon);
                    $couponCode = $coupon->code;
                } else {
                    $couponCode = null;
                    $discountAmount = 0;
                }
            }

            // Gerar número do pedido
            $orderNumber = $this->generateOrderNumber();
            
            // Buscar address_id se foi criado acima ou usar o fornecido
            $addressId = $request->address_id;
            if (empty($addressId) && isset($address)) {
                $addressId = $address->id;
            }

            $clientId = currentClientId();
            // Criar pedido (sem link de pagamento ainda - cliente vai escolher depois)
            $order = Order::create([
                'client_id' => $clientId,
                'customer_id' => $customer->id,
                'address_id' => $addressId,
                'order_number' => $orderNumber,
                'status' => 'pending',
                'total_amount' => $subtotal,
                'delivery_fee' => $deliveryFee,
                'discount_amount' => $discountAmount,
                'coupon_code' => $couponCode,
                'final_amount' => $finalAmount,
                'payment_method' => 'pix', // Padrão - cliente escolherá depois
                'delivery_type' => $request->delivery_type,
                'notes' => $request->notes,
                'payment_status' => 'pending', // Status inicial - aguardando cliente finalizar
            ]);

            // Criar itens
            foreach ($request->items as $itemData) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $itemData['product_id'] ?? null,
                    'quantity' => (int)$itemData['quantity'],
                    'unit_price' => (float)$itemData['price'],
                    'total_price' => (float)$itemData['price'] * (int)$itemData['quantity'],
                    'custom_name' => $itemData['name'],
                    'special_instructions' => $itemData['special_instructions'] ?? null,
                ]);
            }

            try {
                OrderDeliveryFee::updateOrCreate(
                    ['order_id' => $order->id],
                    [
                        'calculated_fee' => $deliveryFee,
                        'final_fee' => $deliveryFee,
                        'distance_km' => null,
                        'order_value' => $subtotal,
                        'is_free_delivery' => $deliveryFee <= 0,
                        'is_manual_adjustment' => true,
                    ]
                );
            } catch (\Exception $e) {
                Log::warning('PDV: Erro ao registrar taxa de entrega (send)', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);
            }

            // Gerar link único para o cliente finalizar o pedido
            // Usar pedido.menuolika.com.br ao invés de dashboard
            // Gerar token de segurança
            $token = md5($order->id . $order->order_number . config('app.key'));
            $completeUrl = 'https://pedido.menuolika.com.br/pdv/complete/' . $order->order_number . '?token=' . urlencode($token);

            // Enviar mensagem via WhatsApp
            try {
                $whatsappService = new WhatsAppService();
                
                // Verificar se há instâncias configuradas
                $instancesCount = \App\Models\WhatsappInstance::whereNotNull('api_url')->count();
                Log::info('PDV: Verificando WhatsApp antes de enviar', [
                    'order_id' => $order->id,
                    'customer_phone' => $customer->phone,
                    'instances_count' => $instancesCount,
                    'is_enabled' => $whatsappService->isEnabled(),
                ]);
                
                if ($whatsappService->isEnabled()) {
                    // Construir resumo do pedido
                    $message = "Olá, {$customer->name}! 🛒\n\n";
                    $message .= "Seu pedido foi criado!\n\n";
                    $message .= "*Pedido #{$orderNumber}*\n\n";
                    
                    // Lista de itens
                    $message .= "*Itens:*\n";
                    foreach ($request->items as $item) {
                        $message .= "👉 {$item['quantity']}x {$item['name']} - R$ " . number_format($item['price'] * $item['quantity'], 2, ',', '.') . "\n";
                    }
                    
                    $message .= "\n";
                    $message .= "Subtotal: R$ " . number_format($subtotal, 2, ',', '.') . "\n";
                    if ($deliveryFee > 0) {
                        $message .= "Taxa de entrega: R$ " . number_format($deliveryFee, 2, ',', '.') . "\n";
                    }
                    if ($discountAmount > 0) {
                        $message .= "Desconto: -R$ " . number_format($discountAmount, 2, ',', '.') . "\n";
                    }
                    $message .= "*Total: R$ " . number_format($finalAmount, 2, ',', '.') . "*\n\n";
                    
                    $message .= "Para finalizar seu pedido, escolher data/hora de entrega e forma de pagamento, acesse:\n";
                    $message .= $completeUrl . "\n\n";
                    $message .= "Após finalizar, você será direcionado para o pagamento.";

                    // VALIDAÇÃO CRÍTICA: Verificar se o cliente tem telefone válido
                    if (empty($customer->phone) || trim($customer->phone) === '') {
                        Log::error('PDV: Cliente não possui telefone cadastrado', [
                            'order_id' => $order->id,
                            'customer_id' => $customer->id,
                            'customer_name' => $customer->name,
                        ]);
                        return response()->json([
                            'success' => true,
                            'order' => [
                                'id' => $order->id,
                                'order_number' => $order->order_number,
                                'total' => $finalAmount,
                            ],
                            'message' => 'Pedido criado com sucesso, mas o cliente não possui telefone cadastrado para receber via WhatsApp.',
                            'whatsapp_error' => true,
                        ]);
                    }
                    
                    // Log do telefone original do cliente - VALIDAR que é o correto
                    Log::info('PDV: Preparando envio WhatsApp', [
                        'order_id' => $order->id,
                        'customer_id' => $customer->id,
                        'customer_name' => $customer->name,
                        'customer_phone_original' => $customer->phone,
                        'customer_phone_length' => strlen($customer->phone),
                    ]);
                    
                    // Normalizar telefone (adicionar código do país se necessário)
                    // IMPORTANTE: Usar o telefone do cliente do banco, não alterar
                    $phoneNormalized = preg_replace('/\D/', '', $customer->phone);
                    if (strlen($phoneNormalized) >= 10 && !str_starts_with($phoneNormalized, '55')) {
                        $phoneNormalized = '55' . $phoneNormalized;
                    }
                    
                    // VALIDAÇÃO: Garantir que o número normalizado não está vazio
                    if (empty($phoneNormalized) || strlen($phoneNormalized) < 10) {
                        Log::error('PDV: Telefone normalizado inválido', [
                            'order_id' => $order->id,
                            'customer_id' => $customer->id,
                            'customer_phone_original' => $customer->phone,
                            'phone_normalized' => $phoneNormalized,
                        ]);
                        return response()->json([
                            'success' => true,
                            'order' => [
                                'id' => $order->id,
                                'order_number' => $order->order_number,
                                'total' => $finalAmount,
                            ],
                            'message' => 'Pedido criado com sucesso, mas o telefone do cliente está em formato inválido.',
                            'whatsapp_error' => true,
                        ]);
                    }
                    
                    // Log do telefone normalizado - VALIDAR antes de enviar
                    Log::info('PDV: Telefone normalizado para envio', [
                        'order_id' => $order->id,
                        'customer_id' => $customer->id,
                        'customer_phone_original' => $customer->phone,
                        'phone_normalized' => $phoneNormalized,
                        'phone_will_be_sent' => $phoneNormalized, // Este é o número que SERÁ enviado
                    ]);

                    // Enviar mensagem via WhatsApp - GARANTIR que usa o número correto do cliente
                    // IMPORTANTE: $phoneNormalized deve ser o número do cliente, não outro
                    $result = $whatsappService->sendText($phoneNormalized, $message);
                    
                    if (isset($result['success']) && $result['success']) {
                        Log::info('PDV: Pedido enviado ao cliente via WhatsApp', [
                            'order_id' => $order->id,
                            'order_number' => $orderNumber,
                            'customer_phone' => $customer->phone,
                            'phone_normalized' => $phoneNormalized,
                            'complete_url' => $completeUrl,
                        ]);
                    } else {
                        $errorMsg = $result['error'] ?? 'Erro desconhecido';
                        Log::warning('PDV: Falha ao enviar mensagem via WhatsApp', [
                            'order_id' => $order->id,
                            'order_number' => $orderNumber,
                            'customer_phone' => $customer->phone,
                            'phone_normalized' => $phoneNormalized,
                            'error' => $errorMsg,
                            'result' => $result,
                        ]);
                        // Retornar erro para o frontend saber que falhou
                        return response()->json([
                            'success' => true,
                            'order' => [
                                'id' => $order->id,
                                'order_number' => $order->order_number,
                                'total' => $finalAmount,
                            ],
                            'message' => 'Pedido criado com sucesso, mas houve um problema ao enviar a mensagem via WhatsApp: ' . $errorMsg,
                            'whatsapp_error' => true,
                        ]);
                    }
                } else {
                    Log::warning('PDV: WhatsApp não configurado ou sem instâncias conectadas', [
                        'order_id' => $order->id,
                        'order_number' => $orderNumber,
                        'instances_count' => $instancesCount,
                    ]);
                    // Retornar aviso mas não falhar o pedido
                    return response()->json([
                        'success' => true,
                        'order' => [
                            'id' => $order->id,
                            'order_number' => $order->order_number,
                            'total' => $finalAmount,
                        ],
                        'message' => 'Pedido criado com sucesso, mas não foi possível enviar via WhatsApp. Verifique se há instâncias de WhatsApp configuradas e conectadas.',
                        'whatsapp_error' => true,
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('PDV: Erro ao enviar mensagem ao cliente', [
                    'order_id' => $order->id,
                    'order_number' => $orderNumber,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                // Não falhar o processo se o envio de mensagem falhar, mas avisar
                return response()->json([
                    'success' => true,
                    'order' => [
                        'id' => $order->id,
                        'order_number' => $order->order_number,
                        'total' => $finalAmount,
                    ],
                    'message' => 'Pedido criado com sucesso, mas houve um erro ao enviar via WhatsApp: ' . $e->getMessage(),
                    'whatsapp_error' => true,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'order' => [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'total' => $finalAmount,
                ],
                'message' => 'Pedido criado e enviado ao cliente com sucesso!',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PDV: Erro ao enviar pedido', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao enviar pedido: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function generateOrderNumber(): string
    {
        $prefix = 'OLK';
        
        // Buscar o último número sequencial usado (formato OLK-0144-XXXXXX)
        // Extrair o número sequencial do segundo segmento (após OLK-)
        $lastOrder = \App\Models\Order::where('order_number', 'like', 'OLK-%')
            ->orderByRaw('CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(order_number, "-", 2), "-", -1) AS UNSIGNED) DESC')
            ->first();
        
        $sequenceNumber = 144; // Último pedido do sistema antigo
        
        if ($lastOrder && preg_match('/OLK-(\d+)-/', $lastOrder->order_number, $matches)) {
            $lastSequence = (int)$matches[1];
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
        $orderNumber = $prefix . '-' . str_pad((string)$sequenceNumber, 4, '0', STR_PAD_LEFT) . '-' . $randomSuffix;
        
        // Verificar se já existe (muito improvável, mas por segurança)
        while (\App\Models\Order::where('order_number', $orderNumber)->exists()) {
            $randomSuffix = '';
            for ($i = 0; $i < 6; $i++) {
                $randomSuffix .= $characters[rand(0, strlen($characters) - 1)];
            }
            $orderNumber = $prefix . '-' . str_pad((string)$sequenceNumber, 4, '0', STR_PAD_LEFT) . '-' . $randomSuffix;
        }
        
        return $orderNumber;
    }

    /**
     * Calcula taxa de entrega baseada na distância (CEP)
     * Usa DeliveryFeeService centralizado
     */
    public function calculateDeliveryFee(Request $request)
    {
        $request->validate([
            'cep' => 'required|string|min:8|max:10',
            'subtotal' => 'required|numeric|min:0',
            'customer_id' => 'nullable|integer|exists:customers,id',
        ]);

        try {
            $destinationCep = preg_replace('/\D/', '', $request->cep);
            $subtotal = (float)$request->subtotal;

            // Buscar dados do cliente se fornecido
            $customerPhone = null;
            $customerEmail = null;
            if ($request->filled('customer_id')) {
                $customer = Customer::find($request->customer_id);
                if ($customer) {
                    $customerPhone = $customer->phone;
                    $customerEmail = $customer->email;
                }
            }

            // Usar serviço centralizado
            $deliveryFeeService = new \App\Services\DeliveryFeeService();
            $result = $deliveryFeeService->calculateDeliveryFee(
                $destinationCep,
                $subtotal,
                $customerPhone ? preg_replace('/\D/', '', $customerPhone) : null,
                $customerEmail
            );

            // Formatar resposta (compatível com formato esperado pelo PDV)
            return response()->json([
                'success' => $result['success'],
                'delivery_fee' => $result['delivery_fee'],
                'distance_km' => $result['distance_km'],
                'free_delivery' => $result['free'] ?? false,
                'custom' => $result['custom'] ?? false,
                'message' => $result['message'] ?? null,
            ], $result['success'] ? 200 : 400);

        } catch (\Exception $e) {
            Log::error('PDV: Erro ao calcular taxa de entrega', [
                'cep' => $request->cep ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao calcular taxa de entrega: ' . $e->getMessage(),
                'delivery_fee' => 0.00,
            ], 500);
        }
    }

    /**
     * Obtém o CEP da loja (store_zip_code)
     */
    /**
     * @deprecated Use DeliveryFeeService::getStoreZipCode() instead
     */
    private function getStoreZipCode(): ?string
    {
        $service = new \App\Services\DeliveryFeeService();
        return $service->getStoreZipCode();
    }

    /**
     * Busca pedido por número para confirmação de pagamento
     */
    public function searchOrder(Request $request)
    {
        $request->validate([
            'order_number' => 'required|string|max:50',
        ]);

        $order = Order::with(['customer', 'items.product'])
            ->where('order_number', $request->order_number)
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Pedido não encontrado',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'customer_name' => $order->customer->name ?? 'N/A',
                'customer_phone' => $order->customer->phone ?? 'N/A',
                'status' => $order->status,
                'payment_status' => $order->payment_status,
                'final_amount' => $order->final_amount ?? $order->total_amount ?? 0,
                'created_at' => $order->created_at->format('d/m/Y H:i'),
            ],
        ]);
    }

    /**
     * Confirma pagamento de pedido sem enviar notificação
     * Para pedidos migrados entre plataformas
     */
    public function confirmPaymentSilent(Request $request, Order $order)
    {
        try {
            DB::beginTransaction();

            $order->refresh();

            // Atualizar payment_status para 'paid'
            $order->payment_status = 'paid';
            
            // Atualizar status para 'confirmed' (aceito na produção)
            // O status "Pago/Confirmado" é representado por payment_status='paid' + status='confirmed'
            $order->status = 'confirmed';
            
            // Marcar como já notificado para evitar notificações futuras
            if (empty($order->notified_paid_at)) {
                $order->notified_paid_at = now();
            }
            
            $order->save();

            // Usar OrderStatusService para atualizar histórico, mas SEM notificações
            $orderStatusService = new \App\Services\OrderStatusService();
            $orderStatusService->changeStatus(
                $order->fresh(),
                'paid', // Código do status "Pago/Confirmado"
                'Pagamento confirmado via PDV (migração) - sem notificação',
                auth()->check() ? auth()->id() : null,
                false, // Não pular histórico
                true   // PULAR NOTIFICAÇÕES
            );

            // Processar fidelidade e cashback se necessário
            try {
                if (class_exists(\App\Http\Controllers\LoyaltyController::class)) {
                    app(\App\Http\Controllers\LoyaltyController::class)->addPoints($order);
                }
            } catch (\Throwable $e) {
                Log::warning('PDV: Falha ao creditar pontos de fidelidade', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage()
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pagamento confirmado com sucesso (sem notificação ao cliente)',
                'order' => [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'status' => $order->status,
                    'payment_status' => $order->payment_status,
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PDV: Erro ao confirmar pagamento silencioso', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao confirmar pagamento: ' . $e->getMessage(),
            ], 500);
        }
    }
}

