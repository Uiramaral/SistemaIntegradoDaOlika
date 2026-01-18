<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NatalMenuController extends Controller
{
    /**
     * Exibe o cardápio de Natal
     */
    public function index()
    {
        // Verificar se o tema natal está ativo
        $temaNatalAtivo = $this->isTemaNatalAtivo();

        // SEMPRE verificar se o tema está ativo, mesmo no subdomínio natal
        // Se não estiver ativo, redirecionar para o cardápio normal
        if (!$temaNatalAtivo) {
            return redirect()->route('pedido.index');
        }

        // Mesma lógica do MenuController, mas com tema natal
        $featuredProducts = Product::query()
            ->select('products.*')
            ->active()
            ->showInCatalog()
            ->available()
            ->purchasable()
            ->featured()
            ->with(['images', 'category'])
            ->ordered()
            ->get();

        $featuredIds = $featuredProducts->pluck('id')->unique()->values();

        $newProductsThreshold = now()->subDays(15);
        $newProducts = Product::query()
            ->select('products.*')
            ->where('created_at', '>=', $newProductsThreshold)
            ->whereNotIn('products.id', $featuredIds->toArray())
            ->active()
            ->showInCatalog()
            ->available()
            ->purchasable()
            ->with(['images', 'category'])
            ->inRandomOrder()
            ->limit(8)
            ->get();

        $categories = Category::query()
            ->select('categories.*')
            ->active()
            ->ordered()
            ->get()
            ->map(function ($category) {
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
                $categoryName = strtolower($category->name);
                if (strpos($categoryName, 'novidades') !== false || strpos($categoryName, 'novidade') !== false) {
                    return false;
                }
                return $category->products->count() > 0;
            });
        
        if ($newProducts->count() > 0) {
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
                'sort_order' => -1,
                'display_type' => $novidadesFromDB ? ($novidadesFromDB->display_type ?? 'list_horizontal') : 'list_horizontal',
                'products' => $newProducts,
            ];
            
            $categories->push($novidadesCategory);
        }
        
        $categories = $categories->sortBy(function($category) {
            if (is_string($category->id) && $category->id === 'novidades') {
                return -1;
            }
            return strtolower($category->name);
        })->values();

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

        return view('natal.index', compact('store', 'categories', 'featuredProducts'));
    }

    /**
     * Exibe produtos de uma categoria específica (tema natal)
     */
    public function category(Category $category)
    {
        $temaNatalAtivo = $this->isTemaNatalAtivo();

        if (!$temaNatalAtivo) {
            return redirect()->route('pedido.menu.category', $category);
        }

        $products = $category->products()
            ->active()
            ->showInCatalog()
            ->available()
            ->purchasable()
            ->with(['images'])
            ->ordered()
            ->get();
        
        $categories = Category::query()
            ->select('categories.*')
            ->active()
            ->ordered()
            ->get();

        return view('natal.category', compact('category', 'products', 'categories'));
    }

    /**
     * Exibe detalhes de um produto (tema natal)
     */
    public function product(Product $product)
    {
        $temaNatalAtivo = $this->isTemaNatalAtivo();

        if (!$temaNatalAtivo) {
            return redirect()->route('pedido.menu.product', $product);
        }

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
            ->with(['images'])
            ->ordered()
            ->limit(4)
            ->get();

        $categories = Category::query()
            ->select('categories.*')
            ->active()
            ->ordered()
            ->get();

        return view('natal.product', compact('product', 'relatedProducts', 'categories'));
    }

    /**
     * Busca produtos (tema natal)
     */
    public function search(Request $request)
    {
        $temaNatalAtivo = $this->isTemaNatalAtivo();

        if (!$temaNatalAtivo) {
            return redirect()->route('pedido.menu.search', $request->all());
        }

        $query = $request->get('q');
        
        if (empty($query)) {
            return redirect()->route('natal.index');
        }

        $products = Product::where(function ($q) use ($query) {
            $q->where('name', 'like', "%{$query}%")
              ->orWhere('description', 'like', "%{$query}%");
        })
        ->active()
        ->showInCatalog()
        ->available()
        ->with(['images'])
        ->ordered()
        ->get();

        return view('natal.search', compact('products', 'query'));
    }

    /**
     * Verifica se o tema natal está ativo
     * Cria o registro se não existir
     */
    private function isTemaNatalAtivo(): bool
    {
        try {
            $value = DB::table('payment_settings')
                ->where('key', 'tema_natal_ativo')
                ->value('value');
            
            // Se não existir o registro, criar com valor '0' (desativado)
            if ($value === null) {
                DB::table('payment_settings')->updateOrInsert(
                    ['key' => 'tema_natal_ativo'],
                    [
                        'value' => '0',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
                return false;
            }
            
            return $value === '1' || $value === 1 || $value === true;
        } catch (\Exception $e) {
            // Se houver erro (tabela não existe, etc), retornar false
            Log::warning('Erro ao verificar tema natal ativo: ' . $e->getMessage());
            return false;
        }
    }
}

