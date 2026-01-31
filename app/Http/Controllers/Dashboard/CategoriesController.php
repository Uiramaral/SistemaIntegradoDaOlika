<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CategoriesController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::withCount('products');

        // Busca
        $searchTerm = $request->input('q');
        if (!empty($searchTerm)) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                    ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }

        $categories = $query->orderBy('sort_order')->orderBy('name')->paginate(20)->withQueryString();

        // Se for requisição AJAX, retornar JSON sem paginação
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'categories' => $categories->map(function ($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'description' => $category->description ?? '',
                        'products_count' => $category->products_count ?? 0,
                    ];
                })
            ]);
        }

        // Buscar produtos para cada categoria (para gerenciamento)
        $allProducts = \App\Models\Product::orderBy('name')->get(['id', 'name', 'category_id']);
        $allCategories = Category::orderBy('sort_order')->orderBy('name')->get(['id', 'name']);

        return view('dashboard.categories.index', compact('categories', 'allProducts', 'allCategories'));
    }

    public function updateProductCategory(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        $product = \App\Models\Product::findOrFail($validated['product_id']);
        $product->category_id = $validated['category_id'] ?? null;
        $product->save();

        return response()->json([
            'success' => true,
            'message' => 'Categoria do produto atualizada com sucesso!'
        ]);
    }

    public function create()
    {
        return view('dashboard.categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'image_url' => 'nullable|url|max:500',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
            'display_type' => 'nullable|in:grid,list_horizontal,list_vertical',
        ]);

        $validated['is_active'] = (bool) ($request->input('is_active', true));
        $validated['sort_order'] = (int) ($request->input('sort_order', 0));
        $validated['display_type'] = $request->input('display_type', 'grid');

        Category::create($validated);

        return redirect()->route('dashboard.categories.index')
            ->with('success', 'Categoria criada com sucesso!');
    }

    public function edit(Category $category)
    {
        return view('dashboard.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'image_url' => 'nullable|url|max:500',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
            'display_type' => 'nullable|in:grid,list_horizontal,list_vertical',
        ]);

        $validated['is_active'] = (bool) ($request->input('is_active', false));
        $validated['sort_order'] = (int) ($request->input('sort_order', 0));
        $validated['display_type'] = $request->input('display_type', 'grid');

        $category->update($validated);

        return redirect()->route('dashboard.categories.index')
            ->with('success', 'Categoria atualizada com sucesso!');
    }

    public function destroy(Category $category)
    {
        // Verificar se há produtos associados
        if ($category->products()->count() > 0) {
            return redirect()->route('dashboard.categories.index')
                ->with('error', 'Não é possível excluir categoria com produtos associados.');
        }

        $category->delete();

        return redirect()->route('dashboard.categories.index')
            ->with('success', 'Categoria removida com sucesso!');
    }

    public function toggleStatus(Category $category)
    {
        $category->is_active = !$category->is_active;
        $category->save();

        return redirect()->route('dashboard.categories.index')
            ->with('success', 'Status da categoria atualizado!');
    }
}