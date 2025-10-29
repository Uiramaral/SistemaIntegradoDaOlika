<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;

class PDVController extends Controller
{
    public function index()
    {
        return view('dash.pages.pdv.index');
    }

    public function calculate(Request $request)
    {
        $total = collect($request->items)->sum('price');
        return response()->json(['total' => $total]);
    }

    public function store(Request $request)
    {
        // Criar pedido fictício
        Order::create($request->all());

        return redirect()->route('dashboard.pdv.index')->with('success', 'Pedido gerado com sucesso!');
    }
}