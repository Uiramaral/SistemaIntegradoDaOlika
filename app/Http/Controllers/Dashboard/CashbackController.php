<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CashbackController extends Controller
{
    public function index()
    {
        $cashbacks = DB::table('cashback as c')
            ->leftJoin('customers as cust', 'cust.id', '=', 'c.customer_id')
            ->select(
                'c.*',
                'cust.name as customer_name',
                'cust.phone as customer_phone'
            )
            ->orderByDesc('c.id')
            ->paginate(30);

        return view('dashboard.cashback', compact('cashbacks'));
    }

    public function create()
    {
        $customers = DB::table('customers')->orderBy('name')->get();
        return view('dashboard.cashback_form', ['customers' => $customers, 'cashback' => null]);
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'customer_id' => 'required|integer|exists:customers,id',
            'amount' => 'required|numeric|min:0',
            'type' => 'required|string|in:credit,manual,bonus',
            'description' => 'nullable|string|max:500',
            'expires_at' => 'nullable|date',
        ]);

        $data['status'] = 'pending';
        $data['created_at'] = now();
        $data['updated_at'] = now();

        DB::table('cashback')->insert($data);

        return redirect()->route('dashboard.cashback')->with('ok', 'Cashback criado!');
    }

    public function edit($id)
    {
        $cashback = DB::table('cashback')->find($id);
        if (!$cashback) {
            return redirect()->route('dashboard.cashback')->with('error', 'Cashback não encontrado');
        }

        $customers = DB::table('customers')->orderBy('name')->get();
        return view('dashboard.cashback_form', ['cashback' => $cashback, 'customers' => $customers]);
    }

    public function update(Request $r, $id)
    {
        $cashback = DB::table('cashback')->find($id);
        if (!$cashback) {
            return redirect()->route('dashboard.cashback')->with('error', 'Cashback não encontrado');
        }

        $data = $r->validate([
            'customer_id' => 'required|integer|exists:customers,id',
            'amount' => 'required|numeric|min:0',
            'type' => 'required|string|in:credit,manual,bonus',
            'status' => 'required|string|in:pending,active,expired,used',
            'description' => 'nullable|string|max:500',
            'expires_at' => 'nullable|date',
        ]);

        $data['updated_at'] = now();
        DB::table('cashback')->where('id', $id)->update($data);

        return redirect()->route('dashboard.cashback')->with('ok', 'Cashback atualizado!');
    }

    public function destroy($id)
    {
        DB::table('cashback')->where('id', $id)->delete();
        return redirect()->route('dashboard.cashback')->with('ok', 'Cashback excluído!');
    }
}

