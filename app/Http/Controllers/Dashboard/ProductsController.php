<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;

class ProductsController extends Controller
{
    public function index()
    {
        $products = Product::with('category')->latest()->paginate(20);
        return view('dash.pages.products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('dash.pages.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'active' => 'boolean',
        ]);

        Product::create($data);
        return redirect()->route('dashboard.products.index')->with('success', 'Produto criado com sucesso.');
    }

    public function edit(Product $product)
    {
        $categories = Category::all();
        return view('dash.pages.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'active' => 'boolean',
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