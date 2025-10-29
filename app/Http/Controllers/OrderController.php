<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $r){
        $q = trim((string)$r->get('q',''));
        $orders = \DB::table('orders')
            ->leftJoin('customers','customers.id','=','orders.customer_id')
            ->when($q!=='', function($qb) use ($q){
                $qb->where(function($w) use ($q){
                    $w->where('orders.order_number','like',"%{$q}%")
                      ->orWhere('customers.name','like',"%{$q}%");
                });
            })
            ->select('orders.*','customers.name as customer_name')
            ->orderByDesc('orders.id')
            ->paginate(12)
            ->withQueryString();
        return view('dash.pages.orders', compact('orders','q'));
    }

    public function show($orderId)
    {
        $order = DB::table('orders')
            ->leftJoin('customers','customers.id','=','orders.customer_id')
            ->select('orders.*','customers.name as customer_name','customers.phone','customers.email')
            ->where('orders.id', $orderId)
            ->first();

        abort_unless($order, 404);

        $items = DB::table('order_items')
            ->leftJoin('products','products.id','=','order_items.product_id')
            ->select('order_items.*','products.name as product_name')
            ->where('order_items.order_id', $orderId)
            ->get();

        // Buscar endereço do cliente para exibir na view simplificada
        $address = DB::table('addresses')
            ->where('customer_id', $order->customer_id)
            ->orderByDesc('is_default')
            ->first();

        // Formatar endereço como string
        $order->address = $address ? 
            "{$address->street}, {$address->number} {$address->complement} - {$address->district}, {$address->city}-{$address->state}" : 
            'Endereço não informado';

        return view('dash.pages.orders', compact('order','items'));
    }

    // Atualiza meta: status, pagamento, data/hora de entrega e observação.
    public function updateMeta(Request $r, $orderId)
    {
        $data = $r->validate([
            'status'          => 'nullable|string',
            'payment_status'  => 'nullable|string',
            'delivery_date'   => 'nullable|date',
            'delivery_time'   => 'nullable',
            'note'            => 'nullable|string',
        ]);

        DB::table('orders')->where('id',$orderId)->update($data + ['updated_at'=>now()]);

        return back()->with('ok','Atualizado');
    }

    public function addItem(Request $r, $orderId)
    {
        $payload = $r->validate([
            'product_id' => 'required|integer|exists:products,id',
            'qty'        => 'required|numeric|min:1',
            'price'      => 'nullable|numeric|min:0',
        ]);

        $product = DB::table('products')->find($payload['product_id']);

        DB::table('order_items')->insert([
            'order_id'   => $orderId,
            'product_id' => $product->id,
            'name'       => $product->name,
            'qty'        => $payload['qty'],
            'price'      => $payload['price'] ?? $product->price,
            'total'      => ($payload['price'] ?? $product->price) * $payload['qty'],
            'created_at' => now(), 'updated_at'=>now(),
        ]);

        $this->recalcOrder($orderId);

        return back()->with('ok','Item adicionado');
    }

    public function updateItem(Request $r, $orderId, $itemId)
    {
        $data = $r->validate([
            'qty'   => 'required|numeric|min:1',
            'price' => 'required|numeric|min:0',
        ]);

        $data['total'] = $data['qty'] * $data['price'];
        $data['updated_at'] = now();

        DB::table('order_items')->where('id',$itemId)->where('order_id',$orderId)->update($data);
        $this->recalcOrder($orderId);

        return back()->with('ok','Item atualizado');
    }

    public function removeItem($orderId, $itemId)
    {
        DB::table('order_items')->where('id',$itemId)->where('order_id',$orderId)->delete();
        $this->recalcOrder($orderId);

        return back()->with('ok','Item removido');
    }

    private function recalcOrder($orderId): void
    {
        $sum = DB::table('order_items')->where('order_id',$orderId)->sum('total');
        // aqui pode aplicar cupom/entrega etc. por enquanto, mantém final_amount = subtotal
        DB::table('orders')->where('id',$orderId)->update([
            'subtotal'     => $sum,
            'final_amount' => $sum,
            'updated_at'   => now()
        ]);
    }
}