<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CouponController extends Controller
{
    // LISTA de cupons elegíveis para o cliente e itens
    public function eligible(Request $r)
    {
        $customerId = (int) $r->query('customer_id');
        $items = json_decode($r->query('items','[]'), true) ?: [];

        // subtotal atual
        $subtotal = collect($items)->reduce(fn($s,$i)=> $s + ((float)$i['price']*(int)$i['qty']), 0);

        // base: cupons ativos e dentro da validade
        $q = Coupon::query()->where('is_active',1);
        $today = Carbon::now();

        $q->where(function($w) use ($today){
            $w->whereNull('start_date')->orWhere('start_date','<=',$today);
        });

        $q->where(function($w) use ($today){
            $w->whereNull('end_date')->orWhere('end_date','>=',$today);
        });

        // cupons públicos ou direcionados ao cliente
        if ($customerId) {
            $q->where(function($w) use ($customerId){
                $w->where('is_public', 1);
            });
        } else {
            $q->where('is_public',1);
        }

        $cupons = $q->limit(20)->get();

        // filtrar por "primeira compra"
        $isFirst = false;
        if ($customerId) {
            $isFirst = !Order::where('customer_id',$customerId)->exists();
        }

        $list = [];
        foreach ($cupons as $c) {
            if ($c->first_order_only && !$isFirst) continue;

            $label = $c->name ?: $c->code;
            $list[] = [
                'code' => $c->code,
                'label'=> $label,
                'desconto_preview' => null,
            ];
        }

        return response()->json($list);
    }

    public function validateCode(Request $request)
    {
        $code  = strtoupper(trim($request->input('code','')));
        $items = $request->input('items', []); // [{id,qty,price}]

        if ($code === '' || empty($items)) {
            return response()->json(['valido'=>false,'mensagem'=>'Informe o cupom e adicione itens.']);
        }

        $subtotal = collect($items)->reduce(fn($s,$i)=> $s + ((float)$i['price'] * (int)$i['qty']), 0);

        $cupom = Coupon::whereRaw('UPPER(code) = ?', [$code])->first();
        if (!$cupom) {
            return response()->json(['valido'=>false,'mensagem'=>'Cupom não encontrado.']);
        }

        if ($cupom->is_active != 1) {
            return response()->json(['valido'=>false,'mensagem'=>'Cupom inativo.']);
        }

        $hoje = Carbon::now();
        if ($cupom->start_date && $hoje->lt($cupom->start_date)) {
            return response()->json(['valido'=>false,'mensagem'=>'Cupom ainda não está válido.']);
        }
        if ($cupom->end_date && $hoje->gt($cupom->end_date)) {
            return response()->json(['valido'=>false,'mensagem'=>'Cupom expirado.']);
        }

        // Verificar valor mínimo
        if ($cupom->minimum_amount && $subtotal < (float)$cupom->minimum_amount) {
            return response()->json(['valido'=>false,'mensagem'=>'Valor mínimo não atingido.']);
        }

        // Calcular desconto: tipo percentage ou fixed
        $desconto = 0;
        if ($cupom->type === 'percentage') {
            $desconto = round($subtotal * ((float)$cupom->value/100), 2);
        } else {
            $desconto = min($subtotal, (float)$cupom->value);
        }

        return response()->json([
            'valido'   => true,
            'mensagem' => 'Cupom aplicado.',
            'desconto' => $desconto,
        ]);
    }
}
