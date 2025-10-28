<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    public function show($customerId){
        $c = DB::table('customers')->where('id',$customerId)->first();
        abort_if(!$c,404);
        $orders = DB::table('orders')->where('customer_id',$customerId)->orderByDesc('id')->limit(25)->get();
        return view('dashboard.customers.show', ['c'=>$c,'orders'=>$orders]);
    }

    public function update($customerId, Request $r){
        $data = $r->validate([
            'name'  => 'nullable|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:30',
            'cashback_balance' => 'nullable|numeric',
            'credit_balance'   => 'nullable|numeric',
            'credit_limit'     => 'nullable|numeric',
            'exclusive_coupon_code' => 'nullable|string|max:60',
        ]);
        DB::table('customers')->where('id',$customerId)->update(array_merge($data,['updated_at'=>now()]));
        return back()->with('ok', true);
    }
}
