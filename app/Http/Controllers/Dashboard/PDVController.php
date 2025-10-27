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
use Carbon\Carbon;

class PDVController extends Controller
{
    public function index()
    {
        // 🔗 MAPA DE ROTAS — USE OS NAMES QUE EXISTEM NO SEU PROJETO
        $pdvRoutes = [
            // === suas rotas (estão sob middleware auth) ===
            'customers_search' => route('api.customers.search'),          // GET /api/customers/search?q=
            'products_search'  => route('api.products.search'),           // GET /api/products/search?q=
            'coupons_eligible' => route('api.coupons.eligible'),          // GET /api/coupons/eligible?customer_id=&items=[]
            'coupons_validate' => route('api.coupons.validate'),          // POST /api/coupons/validate
            'fiado_balance'    => route('api.customers.fiado.balance'),   // GET /api/customers/fiado/balance?customer_id=
            'order_store'      => route('api.pdv.store'),                 // POST /api/pdv/store
        ];

        return view('dashboard.pdv', compact('pdvRoutes'));
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
                ->where('is_active', 1)
                ->first();
            if ($feeRow) {
                // Usa base_fee como valor padrão
                $delivery = (float)$feeRow->base_fee;
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
        $schedules = DB::table('delivery_schedules')->where('is_active', 1)->get();
        $options = [];
        $d = now();

        for ($i = 0; $i < 10; $i++) {
            $weekday = strtolower($d->format('l')); // 'monday', 'tuesday', etc
            $slot = $schedules->firstWhere('day_of_week', $weekday);
            if ($slot) {
                $options[] = [
                    'date' => $d->format('Y-m-d'),
                    'label' => $d->format('d/m') . ' (' . $this->weekdayLabel((int)$d->dayOfWeek) . ') ' . ($slot->name ?? '')
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

    /**
     * Remove caracteres não numéricos
     */
    private function digits($s)
    {
        return preg_replace('/\D+/', '', (string)$s);
    }

    /**
     * Calcula distância em km usando fórmula Haversine
     */
    private function haversineKm($lat1, $lon1, $lat2, $lon2)
    {
        if ($lat1 === null || $lon1 === null || $lat2 === null || $lon2 === null) return null;

        $R = 6371; // raio da Terra em km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat/2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $R * $c;
    }

    /**
     * Geocodifica endereço usando Google Maps (opcional)
     * Usa settings.google_maps_api_key do banco de dados
     */
    private function geocodeAddressIfNeeded(array &$addr)
    {
        if (!empty($addr['latitude']) && !empty($addr['longitude'])) return;

        $key = optional(DB::table('settings')->first())->google_maps_api_key;
        if (!$key) return;

        $parts = array_filter([
            $addr['street'] ?? null,
            $addr['number'] ?? null,
            $addr['neighborhood'] ?? null,
            $addr['city'] ?? null,
            $addr['state'] ?? null,
            $this->digits($addr['cep'] ?? '')
        ]);

        if (!$parts) return;

        $query = urlencode(implode(', ', $parts) . ', Brasil');
        $url = "https://maps.googleapis.com/maps/api/geocode/json?address={$query}&key={$key}";

        try {
            $json = json_decode(file_get_contents($url), true);
            if (($json['status'] ?? '') === 'OK') {
                $loc = $json['results'][0]['geometry']['location'];
                $addr['latitude']  = $loc['lat'];
                $addr['longitude'] = $loc['lng'];
            }
        } catch (\Throwable $e) {
            // silent fail
        }
    }

    /**
     * Calcula frete por distância (loja → cliente)
     * Usa as colunas do banco: business_latitude, business_longitude, delivery_fee_per_km,
     * free_delivery_threshold, max_delivery_distance
     */
    public function computeDeliveryFeeByDistance(?array $addr, float $subtotal): float
    {
        $s = DB::table('settings')->first();
        if (!$s) return 0.00;

        if (empty($addr)) return 0.00;

        // Garante lat/lng do cliente
        $this->geocodeAddressIfNeeded($addr);

        // Calcula distância (Haversine)
        $distKm = $this->haversineKm($s->business_latitude, $s->business_longitude, $addr['latitude'] ?? null, $addr['longitude'] ?? null);
        if ($distKm === null) return 0.00;

        // Raio máximo
        if ($s->max_delivery_distance !== null && $distKm > (float)$s->max_delivery_distance) {
            return 0.00; // Fora da área
        }

        // Grátis acima de X
        if ($s->free_delivery_threshold !== null && $subtotal >= (float)$s->free_delivery_threshold) {
            return 0.00;
        }

        // Taxa = (km arredondado p/ cima) * fee_per_km
        $perKm = (float)$s->delivery_fee_per_km;
        $fee = $perKm * ceil($distKm);

        // Se quiser um mínimo global de taxa
        $minFee = 0.00;
        if (property_exists($s, 'delivery_min_fee')) $minFee = (float)$s->delivery_min_fee;
        if ($fee < $minFee) $fee = $minFee;

        return round($fee, 2);
    }

    /**
     * Calcula frete baseado em delivery_rules (mantida para compatibilidade)
     */
    public function computeDeliveryFee(?string $cep, float $subtotal): float
    {
        if (!$cep) return 0.00;

        $cep = $this->digits($cep);
        if (strlen($cep) !== 8) return 0.00;

        // Tenta usar delivery_rules, se não existir usa delivery_fees como fallback
        $rule = DB::table('delivery_rules')
            ->where('is_active', 1)
            ->where(function($q) use($cep){
                $q->where(function($qq) use($cep){
                    $qq->whereNotNull('cep_from')->whereNotNull('cep_to')
                       ->whereRaw('? BETWEEN cep_from AND cep_to', [$cep]);
                })->orWhereNull('cep_from');
            })
            ->orderBy('sort_order')
            ->first();

        if ($rule) {
            if($rule->min_amount_free !== null && $subtotal >= (float)$rule->min_amount_free) {
                return 0.00;
            }
            return (float)$rule->fee;
        }

        // Fallback para delivery_fees
        $feeRow = DB::table('delivery_fees')->where('is_active', 1)->first();
        if ($feeRow) {
            return (float)$feeRow->base_fee;
        }

        return 0.00;
    }

    /**
     * Gera código CRC16 para PIX
     */
    private function pixCRC16($payload)
    {
        $polynomial = 0x1021;
        $result = 0xFFFF;
        $bytes = unpack('C*', $payload);
        
        foreach($bytes as $b){
            $result ^= ($b << 8);
            for($i=0;$i<8;$i++){
                $result = ($result & 0x8000) ? (($result << 1) ^ $polynomial) : ($result << 1);
                $result &= 0xFFFF;
            }
        }
        
        return strtoupper(str_pad(dechex($result), 4, '0', STR_PAD_LEFT));
    }

    /**
     * Cria campo EMV para PIX
     */
    private function emv(string $id, string $value)
    {
        $len = strlen($value);
        return $id . str_pad((string)$len, 2, '0', STR_PAD_LEFT) . $value;
    }

    /**
     * Constrói payload PIX completo
     */
    private function buildPixPayload(array $pix)
    {
        $gui  = $this->emv('00', 'br.gov.bcb.pix');
        $key  = $this->emv('01', $pix['chave']);
        $info = !empty($pix['info']) ? $this->emv('02', mb_substr($pix['info'], 0, 50)) : '';
        $mai  = $this->emv('26', $gui.$key.$info);

        $mcc  = $this->emv('52', '0000');
        $curr = $this->emv('53', '986');
        $amt  = $this->emv('54', $pix['valor']);
        $cty  = $this->emv('58', 'BR');
        $city = $this->emv('59', mb_substr($pix['nome'], 0, 25));
        $loc  = $this->emv('60', mb_substr($pix['cidade'], 0, 15));
        $txid = $this->emv('62', $this->emv('05', mb_substr($pix['txid'], 0, 25)));

        $base = $this->emv('00', '01')
              . $this->emv('01', '12')
              . $mai . $mcc . $curr . $amt . $cty . $city . $loc . $txid
              . '6304';

        $crc = $this->pixCRC16($base);
        return $base . $crc;
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

