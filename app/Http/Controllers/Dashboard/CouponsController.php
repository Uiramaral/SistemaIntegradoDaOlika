<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CouponsController extends Controller
{
    public function index()
    {
        $coupons = DB::table('coupons')->orderByDesc('id')->paginate(30);
        return view('dashboard.coupons', compact('coupons'));
    }

    public function create()
    {
        return view('dashboard.coupons_form', ['coupon' => null]);
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'code' => 'required|string|max:50|unique:coupons,code',
            'type' => 'required|string|in:fixed,percent',
            'value' => 'required|numeric|min:0',
            'minimum_amount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:0',
            'usage_limit_per_customer' => 'nullable|integer|min:0',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date',
            'visibility' => 'nullable|string|in:public,targeted',
            'is_active' => 'nullable|boolean',
        ]);

        $data['is_active'] = (int)($data['is_active'] ?? 1);
        $data['created_at'] = now();
        $data['updated_at'] = now();

        DB::table('coupons')->insert($data);

        return redirect()->route('dashboard.coupons')->with('ok', 'Cupom criado!');
    }

    public function edit($id)
    {
        $coupon = DB::table('coupons')->find($id);
        if (!$coupon) {
            return redirect()->route('dashboard.coupons')->with('error', 'Cupom não encontrado');
        }

        return view('dashboard.coupons_form', ['coupon' => $coupon]);
    }

    public function update(Request $r, $id)
    {
        $coupon = DB::table('coupons')->find($id);
        if (!$coupon) {
            return redirect()->route('dashboard.coupons')->with('error', 'Cupom não encontrado');
        }

        $data = $r->validate([
            'code' => 'required|string|max:50|unique:coupons,code,'.$id,
            'type' => 'required|string|in:fixed,percent',
            'value' => 'required|numeric|min:0',
            'minimum_amount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:0',
            'usage_limit_per_customer' => 'nullable|integer|min:0',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date',
            'visibility' => 'nullable|string|in:public,targeted',
            'is_active' => 'nullable|boolean',
        ]);

        $data['updated_at'] = now();
        DB::table('coupons')->where('id', $id)->update($data);

        return redirect()->route('dashboard.coupons')->with('ok', 'Cupom atualizado!');
    }

    public function destroy($id)
    {
        DB::table('coupons')->where('id', $id)->delete();
        return redirect()->route('dashboard.coupons')->with('ok', 'Cupom excluído!');
    }

    public function toggleStatus($id)
    {
        $coupon = DB::table('coupons')->find($id);
        if (!$coupon) {
            return back()->with('error', 'Cupom não encontrado');
        }

        DB::table('coupons')->where('id', $id)->update([
            'is_active' => (int)!$coupon->is_active,
            'updated_at' => now(),
        ]);

        return back()->with('ok', 'Status atualizado!');
    }
}

