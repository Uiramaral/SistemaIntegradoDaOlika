<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Loyalty;

class LoyaltyController extends Controller
{
    public function index()
    {
        $loyalties = Loyalty::with('customer')->latest()->paginate(20);
        return view('dash.pages.loyalty.index', compact('loyalties'));
    }

    public function update(Request $request, Loyalty $loyalty)
    {
        $loyalty->update(['pontos' => $request->pontos]);
        return redirect()->back()->with('success', 'Pontos de fidelidade atualizados!');
    }
}
