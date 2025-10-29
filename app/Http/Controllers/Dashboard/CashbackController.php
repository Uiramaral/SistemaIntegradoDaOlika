<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CashbackController extends Controller
{
    public function index()
    {
        // Renderiza apenas a view clonada (sem dependência de modelo)
        return view('dash.pages.cashback.index');
    }

    public function create()
    {
        return view('dash.pages.cashback.create');
    }

    public function store(Request $request)
    {
        // Implementar criação de cashback quando o modelo existir
        return redirect()->route('dashboard.cashback.index')->with('success', 'Funcionalidade em desenvolvimento!');
    }

    public function edit($id)
    {
        return view('dash.pages.cashback.edit');
    }

    public function update(Request $request, $id)
    {
        // Implementar atualização de cashback quando o modelo existir
        return redirect()->route('dashboard.cashback.index')->with('success', 'Funcionalidade em desenvolvimento!');
    }

    public function destroy($id)
    {
        // Implementar exclusão de cashback quando o modelo existir
        return redirect()->route('dashboard.cashback.index')->with('success', 'Funcionalidade em desenvolvimento!');
    }
}