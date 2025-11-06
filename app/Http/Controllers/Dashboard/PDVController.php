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
use App\Services\MercadoPagoApi;
use App\Services\BotConversaService;
use App\Services\DistanceCalculatorService;
use App\Models\DeliveryFee;
use Carbon\Carbon;

class PDVController extends Controller
{
    public function index()
    {
        // Carregar produtos ativos com preços de revenda
        $products = Product::where('is_active', true)
            ->with(['variants', 'wholesalePrices'])
            ->orderBy('name')
            ->get();

        // Carregar clientes recentes (últimos 50)
        $recentCustomers = Customer::orderBy('created_at', 'desc')
            ->limit(50)
            ->get(['id', 'name', 'phone', 'email', 'is_wholesale']);

        return view('dashboard.pdv.index', compact('products', 'recentCustomers'));
    }

    public function searchCustomers(Request $request)
    {
        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return response()->json(['customers' => []]);
        }

        $customers = Customer::where('name', 'like', "%{$query}%")
            ->orWhere('phone', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->limit(20)
            ->get(['id', 'name', 'phone', 'email', 'address', 'neighborhood', 'city', 'state', 'zip_code', 'custom_delivery_fee', 'is_wholesale']);

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
        
        $productsQuery = Product::where('is_active', true);
        
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
        ]);

        $customer = Customer::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'is_wholesale' => $request->has('is_wholesale') && $request->is_wholesale ? 1 : 0,
        ]);

        return response()->json(['customer' => $customer]);
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
            'notes' => 'nullable|string|max:1000',
            'send_payment_link' => 'nullable|boolean', // Se deve enviar link de pagamento ao cliente
            'payment_method' => 'nullable|in:pix,credit_card,debit_card', // Método se enviar link
        ]);

        try {
            DB::beginTransaction();

            $customer = Customer::findOrFail($request->customer_id);

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

            // Criar pedido
            $order = Order::create([
                'customer_id' => $customer->id,
                'address_id' => $request->address_id,
                'order_number' => $orderNumber,
                'status' => 'pending',
                'total_amount' => $subtotal,
                'delivery_fee' => $deliveryFee,
                'discount_amount' => $discountAmount,
                'coupon_code' => $couponCode,
                'final_amount' => $finalAmount,
                'payment_method' => $request->payment_method ?? 'pix',
                'payment_status' => 'pending', // Sempre definir como 'pending' inicialmente
                'delivery_type' => $request->delivery_type,
                'notes' => $request->notes,
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

                    // Sempre usar createPaymentLinkFromOrder que permite cartão E PIX
                    // Isso permite que o cliente escolha no link do Mercado Pago
                    $pref = $mpApi->createPaymentLinkFromOrder($order, $customer, $mpItems);

                    $order->update([
                        'payment_provider' => 'mercadopago',
                        'preference_id' => $pref['preference_id'] ?? null,
                        'payment_link' => $pref['checkout_url'] ?? null,
                        'pix_qr_base64' => $pref['qr_base64'] ?? null,
                        'pix_copia_cola' => $pref['copia_cola'] ?? null,
                        'pix_expires_at' => !empty($pref['expires_at']) ? Carbon::parse($pref['expires_at']) : null,
                        'payment_status' => 'pending',
                    ]);

                    // Enviar mensagem ao cliente via BotConversa (se configurado)
                    if ($customer->phone) {
                        try {
                            $botConversa = new BotConversaService();
                            if ($botConversa->isConfigured()) {
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

                                // Enviar mensagem simples (não usar sendPaidOrderJson ainda pois não está pago)
                                // BotConversaService pode ter um método sendMessage simples, ou usar outro serviço
                                // Por enquanto, apenas log
                                Log::info('PDV: Mensagem preparada para envio ao cliente', [
                                    'order_id' => $order->id,
                                    'customer_phone' => $customer->phone,
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
                } catch (\Exception $e) {
                    Log::error('PDV: Erro ao criar link de pagamento', [
                        'order_id' => $order->id,
                        'error' => $e->getMessage(),
                    ]);
                    // Continuar mesmo se falhar - o pedido já foi criado
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
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
            ]);

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

            // Criar pedido (sem link de pagamento ainda - cliente vai escolher depois)
            $order = Order::create([
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

            // Gerar link único para o cliente finalizar o pedido
            // Usar pedido.menuolika.com.br ao invés de dashboard
            // Gerar token de segurança
            $token = md5($order->id . $order->order_number . config('app.key'));
            $completeUrl = 'https://pedido.menuolika.com.br/pdv/complete/' . $order->order_number . '?token=' . urlencode($token);

            // Enviar mensagem via BotConversa
            try {
                $botConversa = new BotConversaService();
                if ($botConversa->isConfigured()) {
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

                    // Enviar mensagem simples via BotConversa
                    $phoneE164 = $botConversa->normalizePhoneBR($customer->phone);
                    
                    if ($phoneE164) {
                        $botConversa->sendTextMessage($phoneE164, $message);
                    }

                    Log::info('PDV: Pedido enviado ao cliente via BotConversa', [
                        'order_id' => $order->id,
                        'order_number' => $orderNumber,
                        'customer_phone' => $customer->phone,
                        'complete_url' => $completeUrl,
                    ]);
                } else {
                    Log::warning('PDV: BotConversa não configurado, mensagem não enviada', [
                        'order_id' => $order->id,
                        'order_number' => $orderNumber,
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('PDV: Erro ao enviar mensagem ao cliente', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);
                // Não falhar o processo se o envio de mensagem falhar
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
        $date = Carbon::now();
        $prefix = 'OLK';
        $dateStr = $date->format('Ymd');
        $timeStr = $date->format('His');
        $random = str_pad((string)rand(0, 999), 3, '0', STR_PAD_LEFT);
        
        return $prefix . $dateStr . $timeStr . $random;
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
}
