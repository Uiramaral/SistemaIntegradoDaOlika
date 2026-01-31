<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductWholesalePrice;
use Illuminate\Support\Facades\DB;

class WholesalePricesController extends Controller
{
    public function index(Request $request)
    {
        // Filtrar preços de revenda pelos produtos do estabelecimento atual
        $query = ProductWholesalePrice::whereHas('product', function ($q) {
            // Product já filtra por client_id automaticamente via Global Scope
        })->with(['product', 'variant'])
            ->orderBy('product_id')
            ->orderBy('variant_id')
            ->orderBy('min_quantity');

        // Busca por produto
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        // Busca textual (q)
        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->whereHas('product', function ($subQ) use ($search) {
                    $subQ->where('name', 'like', "%{$search}%");
                })->orWhereHas('variant', function ($subQ) use ($search) {
                    $subQ->where('name', 'like', "%{$search}%");
                });
            });
        }

        // Filtro por status
        if ($request->has('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $prices = $query->paginate(20)->withQueryString();

        // Preparar lista de produtos para o Modal (mesma lógica do create)
        $productsList = collect();
        $rawProducts = Product::where('is_active', true)->orderBy('name')->get();

        foreach ($rawProducts as $product) {
            $variants = ProductVariant::where('product_id', $product->id)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get();

            if ($variants->count() === 0) {
                $productsList->push([
                    'product_id' => $product->id,
                    'variant_id' => null,
                    'display_name' => $product->name,
                    'price' => (float) $product->price,
                ]);
            } else {
                foreach ($variants as $variant) {
                    $productsList->push([
                        'product_id' => $product->id,
                        'variant_id' => $variant->id,
                        'display_name' => $product->name . ' (' . $variant->name . ')',
                        'price' => (float) $variant->price,
                    ]);
                }
            }
        }
        $productsList = $productsList->sortBy('display_name')->values();

        return view('dashboard.wholesale-prices.index', compact('prices', 'productsList'));
    }

    public function create()
    {
        $productsList = collect();

        $products = Product::where('is_active', true)
            ->orderBy('name')
            ->get();

        foreach ($products as $product) {
            // Buscar variantes diretamente do banco
            $variants = ProductVariant::where('product_id', $product->id)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get();

            if ($variants->count() === 0) {
                // Produto sem variantes: adicionar como opção única
                $productsList->push([
                    'product_id' => $product->id,
                    'variant_id' => null,
                    'display_name' => $product->name,
                    'price' => (float) $product->price,
                ]);
            } else {
                // Produto com variantes: adicionar cada variante como opção separada
                foreach ($variants as $variant) {
                    $productsList->push([
                        'product_id' => $product->id,
                        'variant_id' => $variant->id,
                        'display_name' => $product->name . ' (' . $variant->name . ')',
                        'price' => (float) $variant->price,
                    ]);
                }
            }
        }

        // Ordenar alfabeticamente pelo display_name
        $productsList = $productsList->sortBy('display_name')->values();

        return view('dashboard.wholesale-prices.create', compact('productsList'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'variant_id' => 'nullable|exists:product_variants,id',
            'wholesale_price' => 'required|numeric|min:0',
            'min_quantity' => 'nullable|integer|min:1',
            'is_active' => 'nullable|boolean',
        ]);

        // Verificar se já existe preço para este produto/variante/quantidade
        $existing = ProductWholesalePrice::where('product_id', $validated['product_id'])
            ->where('variant_id', $validated['variant_id'] ?? null)
            ->where('min_quantity', $validated['min_quantity'] ?? 1)
            ->first();

        if ($existing) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Já existe um preço de revenda cadastrado para este produto/variante com esta quantidade mínima.');
        }

        ProductWholesalePrice::create([
            'product_id' => $validated['product_id'],
            'variant_id' => $validated['variant_id'] ?? null,
            'wholesale_price' => $validated['wholesale_price'],
            'min_quantity' => $validated['min_quantity'] ?? 1,
            'is_active' => $request->has('is_active') ? 1 : 1,
        ]);

        return redirect()->route('dashboard.wholesale-prices.index')
            ->with('success', 'Preço de revenda cadastrado com sucesso!');
    }

    public function edit($id)
    {
        $wholesalePrice = ProductWholesalePrice::with(['product', 'variant'])->findOrFail($id);

        $productsList = collect();

        $products = Product::where('is_active', true)
            ->orderBy('name')
            ->get();

        foreach ($products as $product) {
            // Buscar variantes diretamente do banco
            $variants = ProductVariant::where('product_id', $product->id)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get();

            if ($variants->count() === 0) {
                // Produto sem variantes: adicionar como opção única
                $productsList->push([
                    'product_id' => $product->id,
                    'variant_id' => null,
                    'display_name' => $product->name,
                    'price' => (float) $product->price,
                ]);
            } else {
                // Produto com variantes: adicionar cada variante como opção separada
                foreach ($variants as $variant) {
                    $productsList->push([
                        'product_id' => $product->id,
                        'variant_id' => $variant->id,
                        'display_name' => $product->name . ' (' . $variant->name . ')',
                        'price' => (float) $variant->price,
                    ]);
                }
            }
        }

        // Ordenar alfabeticamente pelo display_name
        $productsList = $productsList->sortBy('display_name')->values();

        return view('dashboard.wholesale-prices.edit', compact('wholesalePrice', 'productsList'));
    }

    public function update(Request $request, $id)
    {
        $wholesalePrice = ProductWholesalePrice::findOrFail($id);

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'variant_id' => 'nullable|exists:product_variants,id',
            'wholesale_price' => 'required|numeric|min:0',
            'min_quantity' => 'nullable|integer|min:1',
            'is_active' => 'nullable|boolean',
        ]);

        // Verificar se já existe outro preço para este produto/variante/quantidade (exceto o atual)
        $existing = ProductWholesalePrice::where('product_id', $validated['product_id'])
            ->where('variant_id', $validated['variant_id'] ?? null)
            ->where('min_quantity', $validated['min_quantity'] ?? 1)
            ->where('id', '!=', $wholesalePrice->id)
            ->first();

        if ($existing) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Já existe outro preço de revenda cadastrado para este produto/variante com esta quantidade mínima.');
        }

        $wholesalePrice->update([
            'product_id' => $validated['product_id'],
            'variant_id' => $validated['variant_id'] ?? null,
            'wholesale_price' => $validated['wholesale_price'],
            'min_quantity' => $validated['min_quantity'] ?? 1,
            'is_active' => $request->has('is_active') ? 1 : 0,
        ]);

        return redirect()->route('dashboard.wholesale-prices.index')
            ->with('success', 'Preço de revenda atualizado com sucesso!');
    }

    public function destroy($id)
    {
        $wholesalePrice = ProductWholesalePrice::findOrFail($id);
        $wholesalePrice->delete();

        return redirect()->route('dashboard.wholesale-prices.index')
            ->with('success', 'Preço de revenda removido com sucesso!');
    }
    public function toggleStatus($id)
    {
        $wholesalePrice = ProductWholesalePrice::findOrFail($id);
        $wholesalePrice->is_active = !$wholesalePrice->is_active;
        $wholesalePrice->save();

        return response()->json([
            'success' => true,
            'message' => 'Status atualizado com sucesso!',
            'is_active' => $wholesalePrice->is_active
        ]);
    }
}

