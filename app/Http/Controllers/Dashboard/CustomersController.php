<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        return view('dash.pages.customers.index', compact('customers', 'search'));
    }

    public function create()
    {
        return view('dash.pages.customers.index', ['customer' => null]);
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|max:20',
            'cpf' => 'nullable|string|max:14',
            'birth_date' => 'nullable|date',
        ]);

        $data['created_at'] = now();
        $data['updated_at'] = now();

        DB::table('customers')->insert($data);

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

        return view('dash.pages.customers.show', compact('customer', 'orders'));
    }

    public function edit($id)
    {
        $customer = DB::table('customers')->find($id);
        if (!$customer) {
            return redirect()->route('dashboard.customers.index')->with('error', 'Cliente não encontrado');
        }

        return view('dash.pages.customers.edit', compact('customer'));
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
        ]);

        $data['updated_at'] = now();
        DB::table('customers')->where('id', $id)->update($data);

        return redirect()->route('dashboard.customers.index')->with('success', 'Cliente atualizado com sucesso!');
    }

    public function destroy($id)
    {
        DB::table('customers')->where('id', $id)->delete();
        return redirect()->route('dashboard.customers.index')->with('success', 'Cliente excluído com sucesso!');
    }
}

