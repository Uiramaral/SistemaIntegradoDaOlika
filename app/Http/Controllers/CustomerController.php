<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::latest()->paginate(20);
        return view('dash.pages.customers.index', compact('customers'));
    }

    public function create()
    {
        return view('dash.pages.customers.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
        ]);

        Customer::create($data);
        return redirect()->route('dashboard.customers')->with('success', 'Cliente criado com sucesso.');
    }

    public function edit(Customer $customer)
    {
        return view('dash.pages.customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
        ]);

        $customer->update($data);
        return redirect()->route('dashboard.customers')->with('success', 'Cliente atualizado com sucesso.');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();
        return redirect()->route('dashboard.customers')->with('success', 'Cliente removido com sucesso.');
    }
}