<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductsController extends Controller
{
    public function index()
    {
        $products = DB::table('products')
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->select('products.*', 'categories.name as category_name')
            ->orderBy('products.name')
            ->paginate(30);

        return view('dashboard.products', compact('products'));
    }

    public function create()
    {
        $categories = DB::table('categories')->where('is_active', 1)->orderBy('name')->get();
        return view('dashboard.products_form', ['categories' => $categories, 'product' => null]);
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'category_id' => 'nullable|integer|exists:categories,id',
            'is_active' => 'nullable|boolean',
        ]);

        // Campo SKU opcional
        if($r->has('sku')) $data['sku'] = $r->get('sku');

        $data['is_active'] = (int)($data['is_active'] ?? 1);
        $data['created_at'] = now();
        $data['updated_at'] = now();

        // Remove vazios
        $data = array_filter($data, fn($v) => $v !== '' && $v !== null);

        DB::table('products')->insert($data);

        return redirect()->route('dashboard.products')->with('ok', 'Produto criado com sucesso!');
    }

    public function edit($id)
    {
        $product = DB::table('products')->find($id);
        if (!$product) {
            return redirect()->route('dashboard.products')->with('error', 'Produto não encontrado');
        }

        $categories = DB::table('categories')->where('is_active', 1)->orderBy('name')->get();
        return view('dashboard.products_form', ['product' => $product, 'categories' => $categories]);
    }

    public function update(Request $r, $id)
    {
        $product = DB::table('products')->find($id);
        if (!$product) {
            return redirect()->route('dashboard.products')->with('error', 'Produto não encontrado');
        }

        $data = $r->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'category_id' => 'nullable|integer|exists:categories,id',
            'is_active' => 'nullable|boolean',
        ]);

        // SKU opcional
        if($r->has('sku')) $data['sku'] = $r->get('sku');

        $data['is_active'] = (int)($data['is_active'] ?? 1);
        $data['updated_at'] = now();

        // Remove vazios
        $data = array_filter($data, fn($v) => $v !== '' && $v !== null);

        DB::table('products')->where('id', $id)->update($data);

        return redirect()->route('dashboard.products')->with('ok', 'Produto atualizado!');
    }

    public function destroy($id)
    {
        DB::table('products')->where('id', $id)->delete();
        return redirect()->route('dashboard.products')->with('ok', 'Produto excluído!');
    }

    public function toggleStatus($id)
    {
        $product = DB::table('products')->find($id);
        if (!$product) {
            return back()->with('error', 'Produto não encontrado');
        }

        DB::table('products')->where('id', $id)->update([
            'is_active' => (int)!$product->is_active,
            'updated_at' => now(),
        ]);

        return back()->with('ok', 'Status atualizado!');
    }
}

