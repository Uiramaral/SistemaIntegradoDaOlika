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
        $query = ProductWholesalePrice::with(['product', 'variant'])
            ->orderBy('product_id')
            ->orderBy('variant_id')
            ->orderBy('min_quantity');

        // Busca por produto
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
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
        $products = Product::where('is_active', true)->orderBy('name')->get();

        return view('dashboard.wholesale-prices.index', compact('prices', 'products'));
    }

    public function create()
    {
        $products = Product::where('is_active', true)
            ->with(['variants' => function($q) {
                $q->where('is_active', true)->orderBy('sort_order');
            }])
            ->orderBy('name')
            ->get()
            ->map(function($product) {
                // Garantir que variants seja uma collection, mesmo que vazia
                if (!$product->relationLoaded('variants') || $product->variants === null) {
                    $product->setRelation('variants', collect([]));
                }
                return $product;
            });

        return view('dashboard.wholesale-prices.create', compact('products'));
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
        
        $products = Product::where('is_active', true)
            ->with(['variants' => function($q) {
                $q->where('is_active', true)->orderBy('sort_order');
            }])
            ->orderBy('name')
            ->get()
            ->map(function($product) {
                // Garantir que variants seja uma collection, mesmo que vazia
                if (!$product->relationLoaded('variants') || $product->variants === null) {
                    $product->setRelation('variants', collect([]));
                }
                return $product;
            });

        return view('dashboard.wholesale-prices.edit', compact('wholesalePrice', 'products'));
    }

    public function update(Request $request, $id)
    {
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
}

