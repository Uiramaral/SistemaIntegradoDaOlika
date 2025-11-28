<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Coupon;
use App\Models\CouponUsage;

class CouponController extends Controller
{
    // Lista cupons elegíveis (por cliente; regras simples)
    public function eligible(Request $req)
    {
        $customerId = (int)$req->input('customer_id');

        $list = Coupon::query()
            ->where('is_active', 1)
            ->when($customerId, fn($q) => $q->where(function($w) use ($customerId) {
                $w->whereNull('target_customer_id')
                  ->orWhere('target_customer_id', $customerId);
            }))
            ->orderBy('name')
            ->limit(50)
            ->get()
            ->map(fn($c) => [
                'code'  => $c->code,
                'label' => $c->name ?? $c->code,
            ]);

        return response()->json(['list' => $list]);
    }

    // Valida e calcula o desconto (percentual/valor fixo)
    public function validateCode(Request $req)
    {
        $code = strtoupper(trim($req->input('code','')));
        $customerId = (int)$req->input('customer_id');
        $items = collect($req->input('items', []));

        if (!$code) return response()->json(['valid'=>false, 'discount_value'=>0]);

        $coupon = Coupon::where('code', $code)->where('is_active',1)->first();
        if (!$coupon) return response()->json(['valid'=>false, 'discount_value'=>0]);

        // Regra simples de alvo
        if ($coupon->target_customer_id && $coupon->target_customer_id != $customerId) {
            return response()->json(['valid'=>false, 'discount_value'=>0]);
        }

        $subtotal = $items->reduce(fn($s,$i) => $s + ((float)$i['price'] * (int)$i['qty']), 0.0);

        $discount = 0.0;
        if ($coupon->type === 'percent' || $coupon->type === 'percentage') {
            $discount = round($subtotal * ((float)$coupon->value / 100), 2);
        } else {
            $discount = min($subtotal, (float)$coupon->value);
        }

        return response()->json(['valid'=>true, 'discount_value'=>$discount]);
    }
}