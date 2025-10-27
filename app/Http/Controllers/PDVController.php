<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Customer;
use App\Models\Address;
use Carbon\Carbon;

class PDVController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->all();

        // Criar/atualizar cliente
        $customer = null;
        if (!empty($data['customer']['id'])) {
            $customer = Customer::find($data['customer']['id']);
        } else if (!empty($data['customer']['nome'])) {
            $customer = Customer::create([
                'name' => $data['customer']['nome'],
                'phone' => $data['customer']['telefone'] ?? null,
                'email' => $data['customer']['email'] ?? null,
            ]);
        }

        if (!$customer) {
            return response()->json(['ok'=>false,'message'=>'Cliente não informado'], 422);
        }

        // Criar/atualizar endereço
        $address = null;
        if (!empty($data['address'])) {
            $addrData = $data['address'];
            if (!empty($addrData['rua']) && !empty($addrData['numero'])) {
                $address = Address::firstOrCreate(
                    [
                        'customer_id' => $customer->id,
                        'street' => $addrData['rua'],
                        'number' => $addrData['numero'],
                        'is_primary' => 1,
                    ],
                    [
                        'zip_code' => $addrData['cep'] ?? null,
                        'complement' => $addrData['complemento'] ?? null,
                        'neighborhood' => $addrData['bairro'] ?? null,
                        'city' => $addrData['cidade'] ?? null,
                        'state' => $addrData['uf'] ?? null,
                        'is_primary' => 1,
                    ]
                );
            }
        }

        // Calcular totais
        $subtotal = 0;
        $items = $data['items'] ?? [];
        
        // Se pagamento é fiado, lançar débito
        $isFiado = ($data['pagamento'] ?? '') === 'fiado';
        
        // Gerar número do pedido
        $orderNumber = 'P'.date('Ymd').str_pad(Order::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);
        
        // Criar pedido
        $order = Order::create([
            'order_number' => $orderNumber,
            'customer_id' => $customer->id,
            'address_id' => $address?->id,
            'subtotal' => 0, // será recalculado abaixo
            'discount_amount' => $data['desconto'] ?? 0,
            'delivery_fee' => $data['entrega'] ?? 0,
            'final_amount' => 0, // será recalculado
            'payment_method' => $data['pagamento'] ?? 'pix',
            'payment_status' => $isFiado ? 'pending' : 'pending',
            'delivery_type' => 'delivery',
            'notes' => $data['observacoes'] ?? '',
            'status' => 'pending',
        ]);

        // Adicionar itens
        foreach ($items as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['id'] ?? null,
                'custom_name' => $item['nome'] ?? null,
                'quantity' => $item['qty'] ?? 1,
                'price' => $item['price'] ?? 0,
                'total_price' => ($item['price'] ?? 0) * ($item['qty'] ?? 1),
            ]);
            $subtotal += ($item['price'] ?? 0) * ($item['qty'] ?? 1);
        }

        // Atualizar totais do pedido
        $deliveryFee = $data['entrega'] ?? 0;
        $discountAmount = $data['desconto'] ?? 0;
        $finalAmount = max(0, $subtotal - $discountAmount + $deliveryFee);
        
        $order->update([
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'delivery_fee' => $deliveryFee,
            'final_amount' => $finalAmount,
        ]);

        // Se pagamento é fiado, criar lançamento em customer_debts
        if ($isFiado) {
            DB::table('customer_debts')->insert([
                'customer_id' => $customer->id,
                'order_id' => $order->id,
                'amount' => $finalAmount,
                'type' => 'debit',
                'status' => 'open',
                'description' => "Pedido #{$orderNumber}",
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return response()->json([
            'ok' => true,
            'order_id' => $order->id,
            'order_number' => $orderNumber,
            'message' => 'Pedido criado com sucesso',
        ]);
    }
}
