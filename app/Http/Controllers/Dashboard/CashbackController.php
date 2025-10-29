<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Cashback;
use Illuminate\Http\Request;

class CashbackController extends Controller
{
    public function index()
    {
        $cashbacks = Cashback::all();
        return view('dash.pages.cashback.index', compact('cashbacks'));
    }

    public function create()
    {
        return view('dash.pages.cashback.create');
    }

    public function store(Request $request)
    {
        Cashback::create($request->all());
        return redirect()->route('dashboard.cashback.index')->with('success', 'Cashback criado com sucesso!');
    }

    public function edit(Cashback $cashback)
    {
        return view('dash.pages.cashback.edit', compact('cashback'));
    }

    public function update(Request $request, Cashback $cashback)
    {
        $cashback->update($request->all());
        return redirect()->route('dashboard.cashback')->with('success', 'Cashback atualizado com sucesso!');
    }

    public function destroy(Cashback $cashback)
    {
        $cashback->delete();
        return redirect()->route('dashboard.cashback')->with('success', 'Cashback removido com sucesso!');
    }
}