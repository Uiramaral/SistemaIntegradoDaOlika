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

    public function search(Request $req)
    {
        $q = trim($req->get('q', ''));
        if ($q === '') return response()->json([]);

        $items = \App\Models\Customer::query()
            ->select(['id','name','phone','email'])
            ->when($q, function($sql) use ($q) {
                $like = "%{$q}%";
                $sql->where(function($w) use ($like) {
                    $w->where('name','like',$like)
                      ->orWhere('phone','like',$like)
                      ->orWhere('email','like',$like);
                });
            })
            ->orderBy('name')
            ->limit(20)
            ->get()
            ->map(fn($c) => [
                'id'    => $c->id,
                'label' => $c->name,
                'phone' => $c->phone,
                'email' => $c->email,
            ]);

        return response()->json($items);
    }
}
