<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    /**
     * Exibe o cardápio principal
     */
    public function index()
    {
        // Buscar produtos em destaque primeiro
        $featuredProducts = Product::active()
            ->available()
            ->featured()
            ->ordered()
            ->get();

        // Buscar categorias com todos os produtos
        $categories = Category::active()
            ->ordered()
            ->with(['products' => function ($query) {
                $query->active()
                      ->available()
                      ->ordered();
            }])
            ->get();

        // Filtrar produtos em destaque das categorias após carregar
        $featuredProductIds = $featuredProducts->pluck('id')->toArray();
        
        // Debug: verificar quantos produtos em destaque
        \Log::info('Produtos em destaque IDs: ' . json_encode($featuredProductIds));
        
        $categories->each(function ($category) use ($featuredProductIds) {
            $originalCount = $category->products->count();
            $category->products = $category->products->reject(function ($product) use ($featuredProductIds) {
                return in_array($product->id, $featuredProductIds);
            });
            $filteredCount = $category->products->count();
            
            // Debug: verificar se filtrou
            \Log::info("Categoria {$category->name}: {$originalCount} -> {$filteredCount} produtos");
        });

        return view('menu.index', compact('categories', 'featuredProducts'));
    }

    /**
     * Exibe produtos de uma categoria específica
     */
    public function category(Category $category)
    {
        $products = $category->products()
            ->active()
            ->available()
            ->ordered()
            ->get();

        return view('menu.category', compact('category', 'products'));
    }

    /**
     * Exibe detalhes de um produto
     */
    public function product(Product $product)
    {
        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->active()
            ->available()
            ->ordered()
            ->limit(4)
            ->get();

        return view('menu.product', compact('product', 'relatedProducts'));
    }

    /**
     * Busca produtos
     */
    public function search(Request $request)
    {
        $query = $request->get('q');
        
        if (empty($query)) {
            return redirect()->route('menu.index');
        }

        $products = Product::where(function ($q) use ($query) {
            $q->where('name', 'like', "%{$query}%")
              ->orWhere('description', 'like', "%{$query}%");
        })
        ->active()
        ->available()
        ->ordered()
        ->get();

        return view('menu.search', compact('products', 'query'));
    }
}
