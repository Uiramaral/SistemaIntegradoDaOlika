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
        // 1) Destaques primeiro (escopos do seu Model)
        $featuredProducts = Product::query()
            ->select('products.*')
            ->active()
            ->available()
            ->featured()
            ->ordered()
            ->get();

        $featuredIds = $featuredProducts->pluck('id')->unique()->values();

        // 2) Categorias (para UI/pills). Aqui não precisamos carregar os produtos ainda.
        $categories = Category::query()
            ->select('categories.*')
            ->active()
            ->ordered()
            ->get();

        // 3) Pegar IDs de produtos ligado às categorias, sem repetir, e já excluindo os destaques
        //    Como a relação é 1:N (products.category_id), buscamos diretamente:
        $categoryProductIds = Product::query()
            ->select('products.id')
            ->whereNotNull('category_id')
            ->active()
            ->available()
            ->pluck('id')
            ->unique()
            ->values();

        // Exclui destaques
        $nonFeaturedIds = $categoryProductIds->diff($featuredIds)->values();

        // 4) Busca única dos "demais" produtos (sem duplicata e já ordenados pelo seu escopo)
        $categoryProducts = Product::query()
            ->select('products.*')
            ->whereIn('products.id', $nonFeaturedIds)
            ->active()
                      ->available()
            ->ordered()
            ->get();

        // 5) Combina: destaques no topo + demais; garante unicidade e reindexa
        $products = $featuredProducts
            ->concat($categoryProducts)
            ->unique('id')
            ->values();
        
        // Logs de diagnóstico (opcional)
        \Log::info('Featured IDs: ' . json_encode($featuredIds));
        \Log::info('NonFeatured IDs: ' . json_encode($nonFeaturedIds));
        \Log::info('Totais => featured: ' . $featuredProducts->count() . ' | demais: ' . $categoryProducts->count() . ' | final: ' . $products->count());

        // Criar objeto store com valores padrão
        $store = (object) [
            'name' => 'Olika',
            'cover_url' => asset('images/hero-breads.jpg'),
            'category_label' => 'Pães • Artesanais',
            'reviews_count' => '250+',
            'is_open' => true,
            'hours' => 'Seg–Sex: 7h–19h · Sáb–Dom: 8h–14h',
            'address' => 'Rua dos Pães Artesanais, 123 Bairro Gourmet – São Paulo, SP',
            'phone' => '(11) 98765-4321',
            'bio' => 'Pães artesanais com fermentação natural. Tradição e qualidade em cada fornada.'
        ];

        return view('menu.index', compact('store', 'categories', 'products'));
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

    /**
     * Download do cardápio
     */
    public function download()
    {
        // Por enquanto, retorna uma resposta simples
        // Você pode implementar geração de PDF aqui
        return response()->json([
            'message' => 'Download do cardápio em desenvolvimento',
            'status' => 'info'
        ]);
    }
}
