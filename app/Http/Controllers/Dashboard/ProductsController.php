<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;

class ProductsController extends Controller
{
    public function index()
    {
        $produtos = Product::with('categoria')->latest()->paginate(20);
        return view('dash.pages.products.index', compact('produtos'));
    }

    public function create()
    {
        return view('dash.pages.products.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
        ]);

        Product::create($data);
        return redirect()->route('dashboard.products.index')->with('success', 'Produto criado com sucesso.');
    }

    public function edit(Product $product)
    {
        return view('dash.pages.products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
        ]);

        $product->update($data);
        return redirect()->route('dashboard.products.index')->with('success', 'Produto atualizado com sucesso.');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('dashboard.products.index')->with('success', 'Produto removido com sucesso.');
    }
}