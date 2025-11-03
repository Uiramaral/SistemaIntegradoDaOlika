<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class MenuApiController extends Controller
{
    /**
     * Lista todas as categorias
     */
    public function categories()
    {
        $categories = Category::active()
            ->ordered()
            ->with(['products' => function ($query) {
                $query->active()->available()->purchasable()->ordered();
            }])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    /**
     * Lista produtos de uma categoria
     */
    public function categoryProducts(Category $category)
    {
        $products = $category->products()
            ->active()
            ->available()
            ->purchasable()
            ->ordered()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    /**
     * Lista todos os produtos
     */
    public function products(Request $request)
    {
        $query = Product::active()->available()->purchasable()->ordered();

        // Filtro por categoria
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filtro por destaque
        if ($request->has('featured')) {
            $query->featured();
        }

        // Busca por nome/descrição
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $products = $query->with('category')->get();

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    /**
     * Exibe detalhes de um produto
     */
    public function product(Product $product)
    {
        $product->load('category');

        return response()->json([
            'success' => true,
            'data' => $product,
        ]);
    }

    /**
     * Lista produtos em destaque
     */
    public function featured()
    {
        $products = Product::active()
            ->available()
            ->purchasable()
            ->featured()
            ->ordered()
            ->with('category')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    /**
     * Busca produtos
     */
    public function search(Request $request)
    {
        $query = $request->get('q');
        
        if (empty($query)) {
            return response()->json([
                'success' => false,
                'message' => 'Termo de busca é obrigatório',
            ], 400);
        }

        $products = Product::where(function ($q) use ($query) {
            $q->where('name', 'like', "%{$query}%")
              ->orWhere('description', 'like', "%{$query}%");
        })
        ->active()
        ->available()
        ->purchasable()
        ->ordered()
        ->with('category')
        ->get();

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }
}
