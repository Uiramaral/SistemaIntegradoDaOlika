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
            ->showInCatalog()
            ->available()
            ->purchasable()
            ->featured()
            ->with(['images', 'category']) // Eager load images e category para evitar N+1
            ->ordered()
            ->get();

        $featuredIds = $featuredProducts->pluck('id')->unique()->values();

        // 2) Buscar produtos novos (últimos 15 dias) para categoria dinâmica "Novidades"
        $newProductsThreshold = now()->subDays(15);
        $newProducts = Product::query()
            ->select('products.*')
            ->where('created_at', '>=', $newProductsThreshold)
            ->whereNotIn('products.id', $featuredIds->toArray()) // Excluir produtos em destaque
            ->active()
            ->showInCatalog()
            ->available()
            ->purchasable()
            ->with(['images', 'category'])
            ->inRandomOrder() // Ordenar aleatoriamente
            ->limit(8) // Limitar a 8 itens
            ->get();

        // 3) Categorias ordenadas com seus produtos agrupados
        // IMPORTANTE: Produtos podem aparecer tanto em categorias quanto em Novidades/Destaques
        $categories = Category::query()
            ->select('categories.*')
            ->active()
            ->ordered()
            ->get()
            ->map(function ($category) {
                // Buscar TODOS os produtos da categoria (sem excluir nada)
                $category->products = Product::query()
                    ->where('category_id', $category->id)
                    ->active()
                    ->showInCatalog()
                    ->available()
                    ->purchasable()
                    ->with(['images'])
                    ->ordered()
                    ->get();
                
                return $category;
            })
            ->filter(function ($category) {
                // Remover categorias sem produtos (mas manter "Novidades" que será tratada separadamente)
                $categoryName = strtolower($category->name);
                if (strpos($categoryName, 'novidades') !== false || strpos($categoryName, 'novidade') !== false) {
                    return false; // Remover categoria "Novidades" do banco, vamos criar dinamicamente
                }
                return $category->products->count() > 0;
            });
        
        // 4) Criar categoria dinâmica "Novidades" se houver produtos novos
        if ($newProducts->count() > 0) {
            // Buscar categoria "Novidades" do banco para pegar configurações (sort_order, display_type)
            $novidadesFromDB = Category::query()
                ->where(function($q) {
                    $q->whereRaw('LOWER(name) LIKE ?', ['%novidades%'])
                      ->orWhereRaw('LOWER(name) LIKE ?', ['%novidade%']);
                })
                ->first();
            
            $novidadesCategory = (object) [
                'id' => 'novidades',
                'name' => $novidadesFromDB ? $novidadesFromDB->name : 'Novidades !! 🎉',
                'description' => $novidadesFromDB->description ?? null,
                'image_url' => $novidadesFromDB->image_url ?? null,
                'is_active' => true,
                'sort_order' => -1, // Ordem negativa para garantir que seja sempre primeiro
                'display_type' => $novidadesFromDB ? ($novidadesFromDB->display_type ?? 'list_horizontal') : 'list_horizontal',
                'products' => $newProducts,
            ];
            
            // Adicionar "Novidades" à coleção
            $categories->push($novidadesCategory);
        }
        
        // 5) Ordenar categorias: Novidades sempre primeiro, depois ordem alfabética
        $categories = $categories->sortBy(function($category) {
            // Se for "Novidades", retorna ordem -1 (sempre primeiro)
            if (is_string($category->id) && $category->id === 'novidades') {
                return -1;
            }
            // Para outras categorias, ordenar alfabeticamente pelo nome
            return strtolower($category->name);
        })->values();

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

        return view('pedido.index', compact('store', 'categories', 'featuredProducts'));
    }

    /**
     * Exibe produtos de uma categoria específica
     */
    public function category(Category $category)
    {
        $products = $category->products()
            ->active()
            ->showInCatalog()
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
        // Bloquear acesso direto a produtos indisponíveis/inativos/não compráveis ou que não aparecem no catálogo
        $isPurchasable = ($product->price > 0) || $product->variants()->where('is_active', true)->where('price', '>', 0)->exists();
        if (!$product->is_active || !$product->show_in_catalog || !$product->is_available || !$isPurchasable) {
            abort(404);
        }
        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->active()
            ->showInCatalog()
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
        ->showInCatalog()
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
