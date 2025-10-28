<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Customer;
use App\Models\Address;
use App\Models\Payment;
use App\Services\MercadoPagoApi;
use Carbon\Carbon;

class PDVController extends Controller
{
    public function store(Request $req, MercadoPagoApi $mp)
    {
        $data = $req->validate([
            'customer.id'             => 'nullable|integer|exists:customers,id',
            'customer.name'           => 'required_without:customer.id|string|max:255',
            'customer.phone'          => 'nullable|string|max:30',
            'customer.email'          => 'nullable|email|max:255',
            'cep'                     => 'nullable|string|max:20',
            'address.street'          => 'nullable|string|max:255',
            'address.number'          => 'nullable|string|max:30',
            'address.complement'      => 'nullable|string|max:255',
            'address.district'        => 'nullable|string|max:255',
            'address.city'            => 'nullable|string|max:255',
            'address.state'           => 'nullable|string|max:2',
            'items'                   => 'required|array|min:1',
            'items.*.product_id'      => 'nullable|integer|exists:products,id',
            'items.*.name'            => 'required|string|max:255',
            'items.*.price'           => 'required|numeric|min:0',
            'items.*.qty'             => 'required|integer|min:1',
            'delivery_option'         => 'nullable|string|max:50',
            'notes'                   => 'nullable|string',
            'coupon'                  => 'nullable|string|max:64',
            'payment_method'          => 'required|in:pix,link_mp,fiado',
        ]);

        return DB::transaction(function() use ($data, $mp) {

            // 1) Cliente
            if (!empty($data['customer']['id'])) {
                $customer = Customer::find($data['customer']['id']);
            } else {
                $customer = Customer::create([
                    'name'  => $data['customer']['name'] ?? 'Cliente',
                    'phone' => $data['customer']['phone'] ?? null,
                    'email' => $data['customer']['email'] ?? null,
                ]);
            }

            // 2) Endereço (opcional)
            $address = null;
            if (!empty($data['address']['street'])) {
                $address = Address::create([
                    'customer_id' => $customer->id,
                    'cep'         => $data['cep'] ?? null,
                    'street'      => $data['address']['street'] ?? '',
                    'number'      => $data['address']['number'] ?? '',
                    'complement'  => $data['address']['complement'] ?? null,
                    'neighborhood'=> $data['address']['district'] ?? null,
                    'city'        => $data['address']['city'] ?? '',
                    'state'       => $data['address']['state'] ?? '',
                ]);
            }

            // 3) Totais
            $subtotal = collect($data['items'])->reduce(fn($s,$i)=> $s + ((float)$i['price']*(int)$i['qty']), 0.0);
            $delivery = 0.00; // calcule pelos seus critérios/opção
            $discount = 0.00; // valide cupom se necessário (já fizemos endpoint)
            $final    = max(0, $subtotal - $discount + $delivery);

            // 4) Pedido
            $order = Order::create([
                'customer_id'   => $customer->id,
                'address_id'    => $address?->id,
                'order_number'  => $this->makeNumber(),
                'status'        => 'pending',
                'total_amount'  => $subtotal,
                'delivery_fee'  => $delivery,
                'discount_amount'=> $discount,
                'coupon_code'   => $data['coupon'] ?? null,
                'final_amount'  => $final,
                'payment_method'=> $data['payment_method'],
                'notes'         => $data['notes'] ?? null,
            ]);

            foreach ($data['items'] as $it) {
                OrderItem::create([
                    'order_id'    => $order->id,
                    'product_id'  => $it['product_id'],
                    'quantity'    => (int)$it['qty'],
                    'unit_price'  => (float)$it['price'],
                    'total_price' => (float)$it['price'] * (int)$it['qty'],
                    'special_instructions' => null,
                ]);
            }

            // 5) Pagamento
            $payment = Payment::create([
                'order_id' => $order->id,
                'provider' => 'mercadopago',
                'status'   => 'pending',
            ]);

            $resp = [
                'ok'     => true,
                'number' => $order->order_number,
                'total'  => (float)$order->final_amount,
            ];

            if ($data['payment_method'] === 'pix') {
                $pref = $mp->createPixPreference($order, $customer, $data['items'], [
                    'exclude_payment_types' => ['ticket'], // boleto fora
                    'installments'          => 1,
                ]);
                // Persistir dados do PIX
                $order->update([
                    'payment_provider' => 'mercadopago',
                    'preference_id'    => $pref['preference_id'] ?? null,
                    'payment_link'     => $pref['checkout_url'] ?? null,
                    'pix_qr_base64'    => $pref['qr_base64'] ?? null,
                    'pix_copia_cola'   => $pref['copia_cola'] ?? null,
                    'pix_expires_at'   => !empty($pref['expires_at']) ? Carbon::parse($pref['expires_at']) : null,
                    'payment_raw_response' => json_encode($pref),
                    'payment_status'  => 'pending',
                ]);
                
                $resp['payment'] = [
                    'mode'       => 'pix',
                    'qr_base64'  => $pref['qr_base64'] ?? null,
                    'copia_cola' => $pref['copia_cola'] ?? null,
                    'expires_at' => $pref['expires_at'] ?? null,
                ];
            }
            elseif ($data['payment_method'] === 'link_mp') {
                $pref = $mp->createPaymentLink($order, $customer, $data['items'], [
                    'exclude_payment_types' => ['ticket'], // boleto fora
                    'installments'          => 1,          // sem parcelas na UI
                ]);
                $order->update([
                    'payment_provider' => 'mercadopago',
                    'preference_id'    => $pref['preference_id'] ?? null,
                    'payment_link'     => $pref['checkout_url'] ?? null,
                    'payment_raw_response' => json_encode($pref),
                    'payment_status'  => 'pending',
                ]);
                
                $resp['payment'] = [
                    'mode' => 'link',
                    'link' => $pref['checkout_url'] ?? null,
                ];
            }
            else { // fiado
                $order->update([
                    'payment_provider' => null,
                    'payment_status'   => 'pending',
                ]);
            }

            return response()->json($resp);
        });
    }

    private function makeNumber(): string
    {
        // OLK + 5 dígitos
        $seq = (int) (Order::max('id') ?? 0) + 1;
        return 'OLK' . str_pad((string)$seq, 5, '0', STR_PAD_LEFT);
    }
}