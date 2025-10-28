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
        return view('dashboard.orders.index', compact('orders','q'));
    }

    public function show($orderId){
        $order = DB::table('orders')->where('id',$orderId)->first();
        abort_if(!$order,404);
        $items = DB::table('order_items')->where('order_id',$orderId)->get();
        return view('dashboard.orders.show', compact('order','items'));
    }

    public function update($orderId, Request $r){
        $data = $r->validate([
            'delivery_date' => 'nullable|date',
            'delivery_time' => 'nullable|date_format:H:i',
            'status'        => 'nullable|string|max:40',
            'payment_status'=> 'nullable|string|max:40',
            'note'          => 'nullable|string'
        ]);
        DB::table('orders')->where('id',$orderId)->update(array_merge($data,['updated_at'=>now()]));
        return back()->with('ok', true);
    }

    public function addItem($orderId, Request $r){
        $data = $r->validate([
            'name'  => 'required|string',
            'qty'   => 'required|integer|min:1',
            'price' => 'required|numeric|min:0'
        ]);
        DB::table('order_items')->insert([
            'order_id'=>$orderId,
            'product_id'=>0,
            'quantity'=>$data['qty'],
            'unit_price'=>$data['price'],
            'total_price'=>$data['qty']*$data['price'],
            'created_at'=>now(),'updated_at'=>now(),
        ]);
        // recalc total
        $total = DB::table('order_items')->where('order_id',$orderId)->sum(DB::raw('quantity*unit_price'));
        DB::table('orders')->where('id',$orderId)->update(['total_amount'=>$total,'final_amount'=>$total,'updated_at'=>now()]);
        return back()->with('ok', true);
    }

    public function removeItem($orderId, $itemId){
        DB::table('order_items')->where(['order_id'=>$orderId,'id'=>$itemId])->delete();
        $total = DB::table('order_items')->where('order_id',$orderId)->sum(DB::raw('quantity*unit_price'));
        DB::table('orders')->where('id',$orderId)->update(['total_amount'=>$total,'final_amount'=>$total,'updated_at'=>now()]);
        return back()->with('ok', true);
    }
}