<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Produto;

class ProdutosController extends Controller
{
    public function index(Request $req)
    {
        $q = $req->string('q')->toString();
        $query = Produto::query();
        if ($q) {
            $query->where('nome','like',"%{$q}%")
                  ->orWhere('sku','like',"%{$q}%");
        }
        $produtos = $query->orderBy('nome')->paginate(20);

        $stats = [
            'total' => Produto::count(),
            'ativos' => Produto::where('ativo',true)->count(),
        ];

        return view('produtos.index', compact('produtos','stats'));
    }

    public function create()
    {
        return view('produtos.create');
    }

    public function store(Request $req)
    {
        $data = $req->validate([
            'nome' => ['required','string','max:150'],
            'sku' => ['nullable','string','max:60'],
            'preco' => ['required','numeric','min:0'],
            'ativo' => ['nullable','boolean'],
        ]);
        $data['ativo'] = (bool)($data['ativo'] ?? true);
        $produto = Produto::create($data);
        return redirect()->route('produtos.show',$produto)->with('ok','Produto criado.');
    }

    public function show(Produto $produto)
    {
        return view('produtos.show', compact('produto'));
    }

    public function edit(Produto $produto)
    {
        return view('produtos.edit', compact('produto'));
    }

    public function update(Request $req, Produto $produto)
    {
        $data = $req->validate([
            'nome' => ['required','string','max:150'],
            'sku' => ['nullable','string','max:60'],
            'preco' => ['required','numeric','min:0'],
            'ativo' => ['nullable','boolean'],
        ]);
        $data['ativo'] = (bool)($data['ativo'] ?? $produto->ativo);
        $produto->update($data);
        return redirect()->route('produtos.show',$produto)->with('ok','Produto atualizado.');
    }

    public function destroy(Produto $produto)
    {
        $produto->delete();
        return redirect()->route('produtos.index')->with('ok','Produto removido.');
    }
}
