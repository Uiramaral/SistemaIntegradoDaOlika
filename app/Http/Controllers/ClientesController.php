<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cliente;

class ClientesController extends Controller
{
    public function index(Request $req)
    {
        $q = $req->string('q')->toString();
        $query = Cliente::query();
        if ($q) {
            $query->where('nome','like',"%{$q}%")
                  ->orWhere('telefone','like',"%{$q}%")
                  ->orWhere('email','like',"%{$q}%");
        }
        $clientes = $query->orderBy('nome')->paginate(20);

        $stats = [
            'total' => Cliente::count(),
            'pedidos_30d' => Cliente::whereHas('pedidos', function($p){
                $p->where('created_at','>=', now()->subDays(30));
            })->count(),
        ];

        return view('clientes.index', compact('clientes','stats'));
    }

    public function create()
    {
        return view('clientes.create');
    }

    public function store(Request $req)
    {
        $data = $req->validate([
            'nome' => ['required','string','max:120'],
            'telefone' => ['nullable','string','max:30'],
            'email' => ['nullable','email','max:120'],
            'endereco' => ['nullable','string','max:255'],
            'bairro' => ['nullable','string','max:120'],
            'cidade' => ['nullable','string','max:120'],
            'cep' => ['nullable','string','max:15'],
        ]);
        $cliente = Cliente::create($data);
        return redirect()->route('clientes.show', $cliente)->with('ok','Cliente criado.');
    }

    public function show(Cliente $cliente)
    {
        $cliente->loadCount('pedidos')->load(['pedidos'=>fn($q)=>$q->latest()->limit(10)]);
        return view('clientes.show', compact('cliente'));
    }

    public function edit(Cliente $cliente)
    {
        return view('clientes.edit', compact('cliente'));
    }

    public function update(Request $req, Cliente $cliente)
    {
        $data = $req->validate([
            'nome' => ['required','string','max:120'],
            'telefone' => ['nullable','string','max:30'],
            'email' => ['nullable','email','max:120'],
            'endereco' => ['nullable','string','max:255'],
            'bairro' => ['nullable','string','max:120'],
            'cidade' => ['nullable','string','max:120'],
            'cep' => ['nullable','string','max:15'],
        ]);
        $cliente->update($data);
        return redirect()->route('clientes.show', $cliente)->with('ok','Cliente atualizado.');
    }

    public function destroy(Cliente $cliente)
    {
        $cliente->delete();
        return redirect()->route('clientes.index')->with('ok','Cliente removido.');
    }
}
