<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Coupon;
use App\Models\Setting;
use App\Services\MercadoPagoService;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Controllers\LoyaltyController;
use App\Http\Controllers\ReferralController;
use App\Services\DeliveryFeeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    protected $mercadoPagoService;
    protected $deliveryFeeService;

    public function __construct(MercadoPagoService $mercadoPagoService, DeliveryFeeService $deliveryFeeService)
    {
        $this->mercadoPagoService = $mercadoPagoService;
        $this->deliveryFeeService = $deliveryFeeService;
    }

    /**
     * Exibe o formulário de checkout por etapas
     */
    public function checkout()
    {
        $cart = Session::get('cart', []);
        
        if (empty($cart)) {
            return redirect()->route('menu.index')->with('error', 'Carrinho vazio');
        }

        // Preparar dados do carrinho para a view
        $cartItems = [];
        $productIds = array_keys($cart);
        $products = \App\Models\Product::whereIn('id', $productIds)->get()->keyBy('id');
        
        foreach ($cart as $productId => $item) {
            $product = $products->get($productId);
            if ($product) {
                $cartItems[] = [
                    'product' => $product,
                    'quantity' => $item['qty'] ?? 0,
                    'unit_price' => $item['price'] ?? 0,
                ];
            }
        }

        $total = $this->calculateCartTotal($cart);
        $settings = Setting::getSettings();
        
        // Dados do cliente dos cookies
        $customerData = $this->getCustomerDataFromCookies();
        
        // Cupons disponíveis para o cliente
        $availableCoupons = $this->getAvailableCoupons($customerData['phone'] ?? null);

        return view('checkout.steps.index', compact(
            'cartItems', 
            'total', 
            'settings', 
            'customerData', 
            'availableCoupons'
        ));
    }

    /**
     * Processa o pedido
     */
    public function store(StoreOrderRequest $request)
    {

        $cart = Session::get('cart', []);
        
        if (empty($cart)) {
            return redirect()->route('menu.index')->with('error', 'Carrinho vazio');
        }

        DB::beginTransaction();

        try {
            // Busca ou cria o cliente
            $customer = $this->findOrCreateCustomer($request);
            
            // Cria o pedido
            $order = $this->createOrder($request, $customer, $cart);
            
            // Cria os itens do pedido
            $this->createOrderItems($order, $cart);
            
            // Aplica cupom se fornecido
            if ($request->coupon_code) {
                $this->applyCoupon($order, $request->coupon_code);
            }

            // Processa pagamento
            $paymentData = $this->processPayment($order, $request);

            DB::commit();

            // Limpa o carrinho
            // Salvar taxa de entrega no banco
            $this->deliveryFeeService->saveDeliveryFee($order);
            
            // Integrar com sistema de fidelidade
            $loyaltyController = new LoyaltyController();
            $loyaltyController->addPoints($order);
            
            // Integrar com sistema de indicação
            $referralController = new ReferralController();
            $referralController->processReferral($order);

            Session::forget('cart');

            return redirect()->route('order.success', $order->id)
                ->with('success', 'Pedido realizado com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->with('error', 'Erro ao processar pedido: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Exibe página de sucesso
     */
    public function success(Order $order)
    {
        return view('checkout.success', compact('order'));
    }

    /**
     * Busca ou cria cliente
     */
    private function findOrCreateCustomer(Request $request)
    {
        $customer = Customer::where('phone', $request->customer_phone)->first();

        if (!$customer) {
            $customer = Customer::create([
                'name' => $request->customer_name,
                'phone' => $request->customer_phone,
                'email' => $request->customer_email,
                'visitor_id' => Str::uuid(),
            ]);
        } else {
            // Atualiza dados se necessário
            $customer->update([
                'name' => $request->customer_name,
                'email' => $request->customer_email,
            ]);
        }

        return $customer;
    }

    /**
     * Cria o pedido
     */
    private function createOrder(Request $request, Customer $customer, array $cart)
    {
        $total = $this->calculateCartTotal($cart);
        $deliveryFee = $request->delivery_type === 'delivery' ? 5.00 : 0.00; // Taxa fixa por enquanto

        $order = Order::create([
            'customer_id' => $customer->id,
            'visitor_id' => $customer->visitor_id,
            'order_number' => $this->generateOrderNumber(),
            'status' => 'pending',
            'total_amount' => $total,
            'delivery_fee' => $deliveryFee,
            'final_amount' => $total + $deliveryFee,
            'payment_method' => $request->payment_method,
            'delivery_type' => $request->delivery_type,
            'delivery_address' => $request->delivery_address,
            'delivery_neighborhood' => $request->delivery_neighborhood,
            'delivery_complement' => $request->delivery_complement,
            'delivery_instructions' => $request->delivery_instructions,
            'observations' => $request->observations,
        ]);

        return $order;
    }

    /**
     * Cria itens do pedido
     */
    private function createOrderItems(Order $order, array $cart)
    {
        foreach ($cart as $productId => $item) {
            // Buscar produto do banco para obter dados completos
            $product = \App\Models\Product::find($productId);
            
            if ($product) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $productId,
                    'quantity' => $item['qty'] ?? 0,
                    'unit_price' => $item['price'] ?? 0,
                    'total_price' => ($item['qty'] ?? 0) * ($item['price'] ?? 0),
                ]);
            }
        }
    }

    /**
     * Aplica cupom
     */
    private function applyCoupon(Order $order, string $couponCode)
    {
        $coupon = Coupon::where('code', $couponCode)
            ->active()
            ->valid()
            ->available()
            ->first();

        if ($coupon) {
            $discount = $coupon->calculateDiscount($order->total_amount);
            
            if ($discount > 0) {
                $order->update([
                    'coupon_code' => $couponCode,
                    'discount_amount' => $discount,
                    'final_amount' => $order->final_amount - $discount,
                ]);

                // Incrementa uso do cupom
                $coupon->increment('used_count');
            }
        }
    }

    /**
     * Processa pagamento
     */
    private function processPayment(Order $order, Request $request)
    {
        if ($request->payment_method === 'pix') {
            return $this->mercadoPagoService->createPixPayment($order);
        }

        if ($request->payment_method === 'credit_card') {
            return $this->mercadoPagoService->createCardPayment($order);
        }

        return null;
    }

    /**
     * Calcula total do carrinho
     */
    private function calculateCartTotal(array $cart)
    {
        $total = 0;
        
        foreach ($cart as $item) {
            $qty = $item['qty'] ?? 0;
            $price = $item['price'] ?? 0;
            $total += $qty * $price;
        }

        return $total;
    }

    /**
     * Gera número do pedido
     */
    private function generateOrderNumber()
    {
        $lastOrder = Order::orderBy('id', 'desc')->first();
        $nextId = $lastOrder ? $lastOrder->id + 1 : 1;
        
        return 'PED' . str_pad($nextId, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Obtém dados do cliente dos cookies
     */
    private function getCustomerDataFromCookies()
    {
        return [
            'name' => request()->cookie('customer_name', ''),
            'phone' => request()->cookie('customer_phone', ''),
            'email' => request()->cookie('customer_email', ''),
            'address' => request()->cookie('customer_address', ''),
            'neighborhood' => request()->cookie('customer_neighborhood', ''),
            'complement' => request()->cookie('customer_complement', ''),
        ];
    }

    /**
     * Obtém cupons disponíveis para o cliente
     */
    private function getAvailableCoupons($customerPhone = null)
    {
        $customerId = null;
        if ($customerPhone) {
            $customer = Customer::where('phone', $customerPhone)->first();
            $customerId = $customer ? $customer->id : null;
        }

        return Coupon::visibleFor($customerId)
            ->active()
            ->valid()
            ->available()
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Salva dados do cliente em cookies
     */
    private function saveCustomerDataToCookies($data)
    {
        $cookies = [];
        
        if (!empty($data['name'])) {
            $cookies[] = cookie('customer_name', $data['name'], 60 * 24 * 30); // 30 dias
        }
        if (!empty($data['phone'])) {
            $cookies[] = cookie('customer_phone', $data['phone'], 60 * 24 * 30);
        }
        if (!empty($data['email'])) {
            $cookies[] = cookie('customer_email', $data['email'], 60 * 24 * 30);
        }
        if (!empty($data['address'])) {
            $cookies[] = cookie('customer_address', $data['address'], 60 * 24 * 30);
        }
        if (!empty($data['neighborhood'])) {
            $cookies[] = cookie('customer_neighborhood', $data['neighborhood'], 60 * 24 * 30);
        }
        if (!empty($data['complement'])) {
            $cookies[] = cookie('customer_complement', $data['complement'], 60 * 24 * 30);
        }

        return $cookies;
    }

    /**
     * API: Valida cupom
     */
    public function validateCoupon(Request $request)
    {
        $request->validate([
            'coupon_code' => 'required|string',
            'customer_phone' => 'nullable|string',
        ]);

        $coupon = Coupon::where('code', strtoupper($request->coupon_code))->first();
        
        if (!$coupon) {
            return response()->json([
                'success' => false,
                'message' => 'Cupom não encontrado',
            ], 404);
        }

        $customerId = null;
        if ($request->customer_phone) {
            $customer = Customer::where('phone', $request->customer_phone)->first();
            $customerId = $customer ? $customer->id : null;
        }

        if (!$coupon->canBeUsedBy($customerId)) {
            return response()->json([
                'success' => false,
                'message' => 'Cupom não pode ser usado',
            ], 400);
        }

        $cartTotal = $this->calculateCartTotal(Session::get('cart', []));
        $discount = $coupon->calculateDiscount($cartTotal);

        return response()->json([
            'success' => true,
            'coupon' => [
                'id' => $coupon->id,
                'code' => $coupon->code,
                'name' => $coupon->name,
                'description' => $coupon->description,
                'type' => $coupon->type,
                'value' => $coupon->value,
                'formatted_value' => $coupon->formatted_value,
                'minimum_amount' => $coupon->minimum_amount,
            ],
            'discount' => $discount,
            'formatted_discount' => 'R$ ' . number_format($discount, 2, ',', '.'),
        ]);
    }

    /**
     * API: Salva dados do cliente
     */
    public function saveCustomerData(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
        ]);

        $customerData = [
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
        ];

        $cookies = $this->saveCustomerDataToCookies($customerData);

        return response()->json([
            'success' => true,
            'message' => 'Dados salvos com sucesso',
        ])->withCookies($cookies);
    }

    /**
     * API: Salva endereço de entrega
     */
    public function saveDeliveryAddress(Request $request)
    {
        $request->validate([
            'address' => 'required|string|max:500',
            'neighborhood' => 'nullable|string|max:100',
            'complement' => 'nullable|string|max:100',
            'instructions' => 'nullable|string|max:500',
        ]);

        $addressData = [
            'address' => $request->address,
            'neighborhood' => $request->neighborhood,
            'complement' => $request->complement,
        ];

        $cookies = $this->saveCustomerDataToCookies($addressData);

        return response()->json([
            'success' => true,
            'message' => 'Endereço salvo com sucesso',
        ])->withCookies($cookies);
    }
}
