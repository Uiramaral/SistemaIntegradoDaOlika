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
            ->purchasable()
            ->featured()
            ->with(['images']) // Eager load images
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
            ->purchasable()
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
            ->purchasable()
            ->with(['images']) // Eager load images
            ->ordered()
            ->get();

        // 5) Combina: destaques no topo + demais; garante unicidade e reindexa
        $products = $featuredProducts
            ->concat($categoryProducts)
            ->unique('id')
            ->values();
        
        // 6) Buscar produtos novos (últimos 15 dias) para categoria "Novidades !!"
        // Excluir produtos que já estão em featured ou categories para evitar duplicação
        $allShownIds = $products->pluck('id')->unique();
        $newProductsThreshold = now()->subDays(15);
        $newProducts = Product::query()
            ->select('products.*')
            ->where('created_at', '>=', $newProductsThreshold)
            ->whereNotIn('products.id', $allShownIds) // Excluir produtos já mostrados
            ->active()
            ->available()
            ->purchasable()
            ->with(['images']) // Eager load images
            ->ordered()
            ->limit(8) // Limitar a 8 itens iniciais
            ->get();
        
        // Logs de diagnóstico (opcional)
        \Log::info('Featured IDs: ' . json_encode($featuredIds));
        \Log::info('NonFeatured IDs: ' . json_encode($nonFeaturedIds));
        \Log::info('Totais => featured: ' . $featuredProducts->count() . ' | demais: ' . $categoryProducts->count() . ' | final: ' . $products->count());
        \Log::info('Novidades: ' . $newProducts->count() . ' produtos');

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

        return view('pedido.index', compact('store', 'categories', 'products', 'newProducts'));
    }

    /**
     * Exibe produtos de uma categoria específica
     */
    public function category(Category $category)
    {
        $products = $category->products()
            ->active()
            ->available()
            ->purchasable()
            ->with(['images']) // Eager load images
            ->ordered()
            ->get();
        
        // Buscar categorias para sidebar
        $categories = Category::query()
            ->select('categories.*')
            ->active()
            ->ordered()
            ->get();

        return view('pedido.category', compact('category', 'products', 'categories'));
    }

    /**
     * Exibe detalhes de um produto
     */
    public function product(Product $product)
    {
        // Bloquear acesso direto a produtos indisponíveis/inativos/não compráveis
        $isPurchasable = ($product->price > 0) || $product->variants()->where('is_active', true)->where('price', '>', 0)->exists();
        if (!$product->is_active || !$product->is_available || !$isPurchasable) {
            abort(404);
        }
        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->active()
            ->available()
            ->purchasable()
            ->with(['images']) // Eager load images
            ->ordered()
            ->limit(4)
            ->get();

        // Buscar categorias para sidebar
        $categories = Category::query()
            ->select('categories.*')
            ->active()
            ->ordered()
            ->get();

        return view('pedido.product', compact('product', 'relatedProducts', 'categories'));
    }

    /**
     * Quick-view JSON público
     */
    public function productJson(Product $product, Request $request)
    {
        $product->load(['category','images','variants' => function($q){ 
            $q->where('is_active', true)->orderBy('sort_order'); 
        }]);
        $variantsCol = ($product->variants ?? collect())->map(function($v){
            return [
                'id' => $v->id,
                'name' => $v->name,
                'price' => (float)($v->price ?? 0),
                'is_active' => (bool)$v->is_active,
                'sort_order' => (int)$v->sort_order,
                'weight_grams' => (int)($v->weight_grams ?? 0),
            ];
        })->values();

        $basePrice = (float)($product->price ?? 0);
        if ($basePrice <= 0 && $variantsCol->count() > 0) {
            $minPos = $variantsCol->pluck('price')->filter(function($p){ return is_numeric($p) && $p > 0; })->min();
            $basePrice = $minPos !== null ? (float)$minPos : 0.0;
        }

        \Log::info('Menu.productJson', [
            'product_id' => $product->id,
            'product_price' => (float)$product->price,
            'variants_count' => $variantsCol->count(),
            'first_variant' => $variantsCol->first(),
            'basePrice' => $basePrice,
        ]);

        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'description' => $product->description,
            'category' => $product->category ? ['id'=>$product->category->id,'name'=>$product->category->name] : null,
            'image_url' => $product->image_url,
            'cover_image' => $product->cover_image,
            'images' => ($product->images ?? collect())->map(fn($i)=>['id'=>$i->id,'path'=>$i->path,'is_primary'=>(bool)$i->is_primary])->values(),
            'price' => $basePrice,
            'weight_grams' => (int)($product->weight_grams ?? 0),
            'has_variants' => $variantsCol->count() > 0,
            'variants' => $variantsCol,
        ]);
    }

    /**
     * Busca produtos
     */
    public function search(Request $request)
    {
        $query = $request->get('q');
        
        if (empty($query)) {
            return redirect()->route('pedido.index');
        }

        $products = Product::where(function ($q) use ($query) {
            $q->where('name', 'like', "%{$query}%")
              ->orWhere('description', 'like', "%{$query}%");
        })
        ->active()
        ->available()
        ->with(['images']) // Eager load images
        ->ordered()
        ->get();

        return view('pedido.search', compact('products', 'query'));
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
