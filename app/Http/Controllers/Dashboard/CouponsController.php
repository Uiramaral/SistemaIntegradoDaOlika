<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponsController extends Controller
{
    public function index()
    {
        $coupons = Coupon::all();
        return view('dash.pages.coupons.index', compact('coupons'));
    }

    public function create()
    {
        return view('dash.pages.coupons.create');
    }

    public function store(Request $request)
    {
        Coupon::create($request->all());
        return redirect()->route('dashboard.coupons.index')->with('success', 'Cupom criado com sucesso!');
    }

    public function edit(Coupon $coupon)
    {
        return view('dash.pages.coupons.edit', compact('coupon'));
    }

    public function update(Request $request, Coupon $coupon)
    {
        $coupon->update($request->all());
        return redirect()->route('dashboard.coupons')->with('success', 'Cupom atualizado com sucesso!');
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();
        return redirect()->route('dashboard.coupons')->with('success', 'Cupom removido com sucesso!');
    }

    public function toggleStatus($id)
    {
        $coupon = Coupon::findOrFail($id);
        $coupon->active = !$coupon->active;
        $coupon->save();

        return redirect()->route('dashboard.coupons')->with('success', 'Status do cupom atualizado!');
    }
}