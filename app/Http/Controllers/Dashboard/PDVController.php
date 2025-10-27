<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Customer;
use App\Models\Address;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Services\MercadoPagoApi;
use App\Services\AppSettings;
use App\Services\OrderStatusService;
use App\Services\WhatsAppService;

class PDVController extends Controller
{
    public function index()
    {
        // pré-carrega listas enxutas (o resto vem via busca)
        $customers = DB::table('customers')->orderByDesc('id')->limit(20)->get();
        $products  = DB::table('products')->where('is_active', 1)->orderBy('name')->limit(50)->get();
        $coupons   = DB::table('coupons')->where('is_active', 1)->orderByDesc('id')->limit(50)->get();

        // janelas e dias de entrega
        $schedules = DB::table('delivery_schedules')->where('active', 1)->orderBy('weekday')->get();
        // regras simples de frete
        $fees = DB::table('delivery_fees')->where('active', 1)->get();

        return view('dashboard.pdv', compact('customers', 'products', 'coupons', 'schedules', 'fees'));
    }

    public function searchCustomers(Request $r)
    {
        $q = trim($r->get('q', ''));
        $rows = DB::table('customers')
            ->when($q, fn($qq) => $qq->where(function ($w) use ($q) {
                $w->where('name', 'like', "%$q%")
                  ->orWhere('phone', 'like', "%$q%")
                  ->orWhere('email', 'like', "%$q%");
            }))
            ->orderBy('name')
            ->limit(30)
            ->get();
        return response()->json($rows);
    }

    public function searchProducts(Request $r)
    {
        $q = trim($r->get('q', ''));
        $rows = DB::table('products')->where('is_active', 1)
            ->when($q, fn($qq) => $qq->where(function ($w) use ($q) {
                $w->where('name', 'like', "%$q%")->orWhere('sku', 'like', "%$q%");
            }))
            ->orderBy('name')
            ->limit(60)
            ->get();
        return response()->json($rows);
    }

    public function validateCoupon(Request $r)
    {
        $data = $r->validate([
            'code' => 'required|string',
            'customer_id' => 'nullable|integer',
            'subtotal' => 'required|numeric|min:0'
        ]);

        $code = strtoupper($data['code']);
        $coupon = DB::table('coupons')->where('code', $code)->where('is_active', 1)->first();

        if (!$coupon) {
            return response()->json(['ok' => false, 'msg' => 'Cupom inválido ou inativo']);
        }

        $now = now();

        if ($coupon->starts_at && $now->lt($coupon->starts_at)) {
            return response()->json(['ok' => false, 'msg' => 'Cupom ainda não disponível']);
        }

        if ($coupon->expires_at && $now->gt($coupon->expires_at)) {
            return response()->json(['ok' => false, 'msg' => 'Cupom expirado']);
        }

        if ($coupon->minimum_amount && $data['subtotal'] < $coupon->minimum_amount) {
            return response()->json(['ok' => false, 'msg' => 'Subtotal insuficiente']);
        }

        // direcionado?
        if (($coupon->visibility ?? null) === 'targeted' && ($coupon->target_customer_id ?? null) && $data['customer_id']) {
            if ((int)$coupon->target_customer_id !== (int)$data['customer_id']) {
                return response()->json(['ok' => false, 'msg' => 'Cupom não disponível para este cliente']);
            }
        }

        // limites por uso
        $usedGlobal = DB::table('coupon_usages')->where('coupon_id', $coupon->id)->count();
        if (($coupon->usage_limit ?? null) && $usedGlobal >= $coupon->usage_limit) {
            return response()->json(['ok' => false, 'msg' => 'Limite de uso atingido']);
        }

        if ($data['customer_id']) {
            $usedByCust = DB::table('coupon_usages')->where('coupon_id', $coupon->id)->where('customer_id', $data['customer_id'])->count();
            if (($coupon->usage_limit_per_customer ?? null) && $usedByCust >= $coupon->usage_limit_per_customer) {
                return response()->json(['ok' => false, 'msg' => 'Cliente já utilizou este cupom']);
            }
        }

        // calcula desconto
        $subtotal = (float)$data['subtotal'];
        $discount = ($coupon->type === 'percent') ? round($subtotal * ($coupon->value / 100), 2) : min($coupon->value, $subtotal);

        return response()->json(['ok' => true, 'discount' => $discount, 'coupon_id' => $coupon->id]);
    }

    public function saveAddress(Request $r)
    {
        $data = $r->validate([
            'customer_id' => 'required|integer',
            'cep' => 'required|string',
            'street' => 'required|string',
            'number' => 'required|string',
            'complement' => 'nullable|string',
            'neighborhood' => 'nullable|string',
            'city' => 'required|string',
            'state' => 'required|string|size:2'
        ]);

        $addr = Address::create($data);
        return response()->json(['ok' => true, 'address_id' => $addr->id, 'address' => $addr]);
    }

    public function calculate(Request $r)
    {
        $data = $r->validate([
            'items' => 'required|array',
            'address' => 'nullable|array',
            'coupon_code' => 'nullable|string',
            'customer_id' => 'nullable|integer'
        ]);

        $subtotal = 0;
        foreach ($data['items'] as $row) {
            $subtotal += ((float)$row['price']) * ((int)$row['qty']);
        }

        // frete (exemplo): por cidade/estado, ou por CEP prefix
        $delivery = 0;
        if (isset($data['address']['city'])) {
            $feeRow = DB::table('delivery_fees')
                ->where('active', 1)
                ->where(function ($q) use ($data) {
                    $q->where('city', $data['address']['city'])
                      ->orWhereNull('city');
                })
                ->where(function ($q) use ($data) {
                    $q->where('state', $data['address']['state'])
                      ->orWhereNull('state');
                })
                ->orderByDesc('city')
                ->orderByDesc('state')
                ->first();
            if ($feeRow) {
                $delivery = (float)$feeRow->fee_value;
            }
        }

        // desconto de cupom (opcional – apenas cálculo)
        $discount = 0;
        if (!empty($data['coupon_code'])) {
            $resp = $this->validateCoupon(new Request([
                'code' => $data['coupon_code'],
                'customer_id' => $data['customer_id'],
                'subtotal' => $subtotal
            ]));
            $j = $resp->getData(true);
            if (($j['ok'] ?? false) === true) {
                $discount = (float)$j['discount'];
            }
        }

        $total = max(0, $subtotal - $discount + $delivery);

        // dias/horários de entrega disponíveis (próximos 10 dias)
        $schedules = DB::table('delivery_schedules')->where('active', 1)->orderBy('weekday')->get();
        $options = [];
        $d = now();

        for ($i = 0; $i < 10; $i++) {
            $weekday = (int)$d->dayOfWeek; // 0=domingo
            $slot = $schedules->firstWhere('weekday', $weekday);
            if ($slot) {
                $options[] = [
                    'date' => $d->format('Y-m-d'),
                    'label' => $d->format('d/m') . ' (' . $this->weekdayLabel($weekday) . ') ' . ($slot->window_label ?? '')
                ];
            }
            $d->addDay();
        }

        return response()->json([
            'ok' => true,
            'subtotal' => round($subtotal, 2),
            'discount' => round($discount, 2),
            'delivery' => round($delivery, 2),
            'total' => round($total, 2),
            'slots' => $options
        ]);
    }

    private function weekdayLabel($i)
    {
        return ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'][$i] ?? $i;
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'customer_id' => 'required|integer',
            'address_id' => 'nullable|integer',
            'delivery_date' => 'nullable|date',
            'delivery_window' => 'nullable|string',
            'items' => 'required|array|min:1',
            'coupon_code' => 'nullable|string',
            'payment_method' => 'required|string', // 'pix','link','cash','card','pix_manual'
            'note' => 'nullable|string',
        ]);

        // calcula totais
        $calc = $this->calculate(new Request([
            'items' => $data['items'],
            'address' => $data['address_id'] ? (array) DB::table('addresses')->find($data['address_id']) : null,
            'coupon_code' => $data['coupon_code'] ?? null,
            'customer_id' => $data['customer_id']
        ]))->getData(true);

        if (($calc['ok'] ?? false) !== true) {
            return back()->withErrors(['pdv' => 'Falha no cálculo']);
        }

        // cria pedido
        $number = now()->format('YmdHisv') . mt_rand(100, 999);

        $orderId = DB::table('orders')->insertGetId([
            'order_number' => $number,
            'customer_id' => $data['customer_id'],
            'address_id'  => $data['address_id'] ?? null,
            'subtotal'    => $calc['subtotal'],
            'discount_amount' => $calc['discount'],
            'delivery_fee' => $calc['delivery'],
            'final_amount' => $calc['total'],
            'total_amount' => $calc['total'],
            'status'      => 'waiting_payment',
            'payment_method' => $data['payment_method'],
            'coupon_code' => $data['coupon_code'] ?? null,
            'delivery_type' => 'delivery',
            'scheduled_delivery_at' => $data['delivery_date'] ?? null,
            'observations' => $data['note'] ?? null,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        foreach ($data['items'] as $row) {
            DB::table('order_items')->insert([
                'order_id'    => $orderId,
                'product_id'  => (int)$row['product_id'],
                'product_name' => $row['name'],
                'price'       => (float)$row['price'],
                'quantity'         => (int)$row['qty'],
                'total_price'  => round(((float)$row['price'] * (int)$row['qty']), 2),
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        $order = Order::find($orderId);

        // caso pagamento seja imediato pelo PDV
        if (in_array($data['payment_method'], ['pix', 'link'])) {
            if ($data['payment_method'] === 'pix') {
                // criar pagamento PIX via API
                $mp = new MercadoPagoApi();

                $res = $mp->createPix([
                    "transaction_amount" => (float)$order->final_amount,
                    "description" => "Pedido #{$order->order_number}",
                    "payment_method_id" => "pix",
                    "notification_url"  => AppSettings::get('mercadopago_webhook_url', route('webhook.mercadopago')),
                    "payer" => [
                        "email" => optional($order->customer)->email ?: "noemail@dummy.com",
                        "first_name" => optional($order->customer)->name ?: "Cliente"
                    ],
                    "metadata" => ["order_id" => $order->id, "order_number" => $order->order_number],
                ]);

                DB::table('orders')->where('id', $order->id)->update([
                    'payment_id' => (string) data_get($res, 'id'),
                    'payment_status' => (string) data_get($res, 'status'),
                    'pix_copy_paste' => data_get($res, 'point_of_interaction.transaction_data.qr_code'),
                    'pix_qr_base64'  => data_get($res, 'point_of_interaction.transaction_data.qr_code_base64'),
                    'payment_raw_response' => json_encode($res),
                    'updated_at' => now(),
                ]);

                $order = Order::find($orderId);
            } else { // link
                $mp = new MercadoPagoApi();

                $items = DB::table('order_items')->where('order_id', $order->id)->get()->map(function ($i) {
                    return [
                        "title" => $i->product_name, "quantity" => (int)$i->quantity, "currency_id" => "BRL", "unit_price" => (float)$i->price
                    ];
                })->values()->all();

                $res = $mp->createPreference([
                    "items" => $items,
                    "metadata" => ["order_id" => $order->id, "order_number" => $order->order_number],
                    "notification_url" => AppSettings::get('mercadopago_webhook_url', route('webhook.mercadopago')),
                    "back_urls" => [
                        "success" => route('checkout.success', $order),
                        "pending" => route('checkout.success', $order),
                        "failure" => route('checkout.success', $order),
                    ],
                    "auto_return" => "approved",
                ]);

                DB::table('orders')->where('id', $order->id)->update([
                    'preference_id' => data_get($res, 'id'),
                    'payment_link'  => data_get($res, 'init_point'),
                    'payment_raw_response' => json_encode($res),
                    'updated_at' => now(),
                ]);

                $order = Order::find($orderId);
            }
        }

        // opcional: notificar cliente que o pedido foi criado
        try {
            $st = app(\App\Services\OrderStatusService::class);
            $st->changeStatus($order, 'pending', 'Criado manualmente no PDV');
        } catch (\Throwable $e) {
            \Log::error($e->getMessage());
        }

        return response()->json([
            'ok' => true,
            'order_id' => $order->id,
            'number' => $order->order_number,
            'payment_link' => $order->payment_link ?? null,
            'pix_qr_base64' => $order->pix_qr_base64 ?? null,
            'pix_copy_paste' => $order->pix_copy_paste ?? null,
        ]);
    }
}

