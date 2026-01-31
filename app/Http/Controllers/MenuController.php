<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Product;
use App\Models\Client;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

class MenuController extends Controller
{
    /**
     * Exibe a página inicial do cardápio do tenant atual
     */
    public function index(Request $request)
    {
        // O tenant (client_id) já é gerenciado pelo middleware e Trait BelongsToClient.
        // Apenas buscamos os dados, e o escopo global filtra automaticamente pelo cliente.

        // Buscar categorias ativas e ordenadas, com produtos também ativos/disponíveis
        $categories = Category::with([
            'products' => function ($query) {
                $query->where('is_active', true)
                    ->where('is_available', true)
                    ->orderBy('sort_order')
                    ->orderBy('name');
            }
        ])
            ->active()   // Scope do model Category
            ->ordered()  // Scope do model Category
            ->get();

        // Lista plana de produtos ativos para contagem geral e busca rápida se necessário (opcional na view)
        $products = Product::where('is_active', true)
            ->where('is_available', true)
            ->get();

        return view('pedido.index', compact('categories', 'products'));
    }

    /**
     * Exibe uma categoria específica
     */
    public function category(Request $request, $category)
    {
        // Se category for slug ou ID
        $query = Category::with([
            'products' => function ($q) {
                $q->where('is_active', true)
                    ->where('is_available', true)
                    ->orderBy('sort_order')
                    ->orderBy('name');
            }
        ]);

        if (is_numeric($category)) {
            $cat = $query->find($category);
        } else {
            // Tentar buscar por slug se existir campo, ou ID string
            $cat = $query->where('id', $category)->first();
            // TODO: Se tiver slug, mudar para where('slug', $category)
        }

        if (!$cat) {
            abort(404, 'Categoria não encontrada.');
        }

        // Reutiliza a view index filtrada ou view específica
        // Se existir view category, usar. Senão index com filtro.
        if (View::exists('pedido.category')) {
            return view('pedido.category', ['category' => $cat, 'products' => $cat->products]);
        }

        $products = $cat->products()->where('is_active', true)->get();

        return view('pedido.index', [
            'categories' => collect([$cat]),
            'products' => $products
        ]);
    }

    /**
     * Exibe detalhes de um produto
     */
    public function product(Request $request, $product)
    {
        // Carregar produto com variações e imagens
        $prod = Product::with(['category', 'variants', 'images', 'allergens'])
            ->where('is_active', true) // Segurança extra
            ->find($product);

        if (!$prod) {
            // Tenta buscar pelo ID na URL se passed as object binding falhar ou se for integer
            $prod = Product::with(['category', 'variants', 'images', 'allergens'])
                ->where('is_active', true)
                ->find($product);
        }

        if (!$prod) {
            abort(404, 'Produto não encontrado.');
        }

        // Produtos relacionados (mesma categoria)
        $related = Product::where('category_id', $prod->category_id)
            ->where('id', '!=', $prod->id)
            ->where('is_active', true)
            ->where('is_available', true)
            ->limit(4)
            ->get();

        return view('pedido.product', [
            'product' => $prod,
            'related' => $related
        ]);
    }

    /**
     * Retorna JSON do produto (para modais/quickview)
     */
    /**
     * Retorna HTML do modal do produto
     */
    public function productModal($product)
    {
        $prod = Product::with([
            'category',
            'variants' => function ($q) {
                $q->where('is_active', true)->orderBy('sort_order');
            },
            'images',
            'allergens'
        ])
            ->where('is_active', true)
            ->find($product);

        if (!$prod) {
            return response()->json(['error' => 'Produto não encontrado'], 404);
        }

        return view('pedido.partials.product-modal-content', ['product' => $prod]);
    }

    /**
     * Retorna JSON do produto (para modais/quickview)
     */
    public function productJson($product)
    {
        $prod = Product::with(['variants', 'images', 'allergens'])
            ->where('is_active', true)
            ->find($product);

        if (!$prod) {
            return response()->json(['error' => 'Produto não encontrado'], 404);
        }

        return response()->json($prod);
    }

    /**
     * Busca produtos
     */
    public function search(Request $request)
    {
        $query = $request->input('q');

        if (empty($query)) {
            return redirect()->route('pedido.index');
        }

        $products = Product::where('is_active', true)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', '%' . $query . '%')
                    ->orWhere('description', 'like', "%{$query}%");
            })
            ->where('is_available', true)
            ->get();

        // Se existir view de search, usar (mas verificamos que search.blade.php existe e é pequena, talvez incompleta? Vamos usar index por segurança)
        if (View::exists('pedido.search')) {
            // return view('pedido.search', compact('products', 'query'));
        }

        // Fallback robusto: Encapsular resultados em uma Categoria fictícia para reutilizar a lógica da view pedido.index
        $searchCategory = new Category([
            'id' => 'search-results',
            'name' => 'Resultados para: "' . $query . '"',
            'display_type' => 'grid',
            'is_active' => true
        ]);

        // Associar coleção de produtos
        $searchCategory->setRelation('products', $products);

        return view('pedido.index', [
            'categories' => collect([$searchCategory]),
            'products' => $products,
            'searchQuery' => $query
        ]);
    }
}
