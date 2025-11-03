<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\CustomerCashback;

class CustomersController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('q', '');
        
        $query = DB::table('customers');
        
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        
        $customers = $query->select('customers.*', 
                DB::raw('(SELECT COUNT(*) FROM orders WHERE orders.customer_id = customers.id) as total_orders'),
                DB::raw('(SELECT COALESCE(SUM(final_amount), 0) FROM orders WHERE orders.customer_id = customers.id AND payment_status = "paid") as total_spent')
            )
            ->orderByDesc('id')
            ->paginate(30);

        return view('dashboard.customers.index', compact('customers', 'search'));
    }

    public function create()
    {
        return view('dashboard.customers.create');
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|max:20',
            'cpf' => 'nullable|string|max:14',
            'birth_date' => 'nullable|date',
            'cashback_balance' => 'nullable|numeric|min:0',
        ]);

        $cashbackBalance = $r->input('cashback_balance', 0);
        
        $data['created_at'] = now();
        $data['updated_at'] = now();
        unset($data['cashback_balance']); // Remover do array de dados do cliente

        $customerId = DB::table('customers')->insertGetId($data);

        // Criar transação de cashback inicial se o valor foi informado
        if ($cashbackBalance > 0) {
            \App\Models\CustomerCashback::create([
                'customer_id' => $customerId,
                'order_id' => null,
                'amount' => (float)$cashbackBalance,
                'type' => 'credit',
                'description' => 'Saldo inicial de cashback',
            ]);
        }

        return redirect()->route('dashboard.customers.index')->with('success', 'Cliente criado com sucesso!');
    }

    public function show($id)
    {
        $customer = DB::table('customers')->find($id);
        if (!$customer) {
            return redirect()->route('dashboard.customers.index')->with('error', 'Cliente não encontrado');
        }

        $orders = DB::table('orders')
            ->where('customer_id', $id)
            ->orderByDesc('id')
            ->paginate(10);

        return view('dashboard.customers.show', compact('customer', 'orders'));
    }

    public function edit($id)
    {
        $customer = DB::table('customers')->find($id);
        if (!$customer) {
            return redirect()->route('dashboard.customers.index')->with('error', 'Cliente não encontrado');
        }

        return view('dashboard.customers.edit', compact('customer'));
    }

    public function update(Request $r, $id)
    {
        $customer = DB::table('customers')->find($id);
        if (!$customer) {
            return redirect()->route('dashboard.customers.index')->with('error', 'Cliente não encontrado');
        }

        $data = $r->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|max:20',
            'cpf' => 'nullable|string|max:14',
            'birth_date' => 'nullable|date',
            'cashback_balance' => 'nullable|numeric|min:0',
        ]);

        $targetCashbackBalance = (float)($r->input('cashback_balance', 0) ?? 0);
        
        $data['updated_at'] = now();
        unset($data['cashback_balance']); // Remover do array de dados do cliente
        
        DB::table('customers')->where('id', $id)->update($data);

        // Ajustar cashback se o valor foi informado e é diferente do saldo atual
        $currentBalance = \App\Models\CustomerCashback::getBalance($id);
        $difference = $targetCashbackBalance - $currentBalance;
        
        if (abs($difference) > 0.01) { // Tolerância de 1 centavo para diferenças de arredondamento
            if ($difference > 0) {
                // Adicionar cashback
                \App\Models\CustomerCashback::create([
                    'customer_id' => $id,
                    'order_id' => null,
                    'amount' => $difference,
                    'type' => 'credit',
                    'description' => 'Ajuste manual de cashback',
                ]);
            } else {
                // Remover cashback (débito)
                \App\Models\CustomerCashback::create([
                    'customer_id' => $id,
                    'order_id' => null,
                    'amount' => abs($difference),
                    'type' => 'debit',
                    'description' => 'Ajuste manual de cashback',
                ]);
            }
        }

        return redirect()->route('dashboard.customers.index')->with('success', 'Cliente atualizado com sucesso!');
    }

    public function destroy($id)
    {
        DB::table('customers')->where('id', $id)->delete();
        return redirect()->route('dashboard.customers.index')->with('success', 'Cliente excluído com sucesso!');
    }
}

