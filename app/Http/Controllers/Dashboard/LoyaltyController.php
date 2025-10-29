<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LoyaltyController extends Controller
{
    public function index()
    {
        // Renderiza apenas a view clonada (sem dependência de modelo)
        return view('dash.pages.loyalty.index');
    }

    public function update(Request $request)
    {
        return redirect()->back()->with('success', 'Configurações atualizadas!');
    }
}
