<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class IngredientsController extends Controller
{
    public function index(Request $request)
    {
        // Desabilitar ClientScope temporariamente para mostrar todos os ingredientes
        // (incluindo os antigos sem client_id)
        $query = Ingredient::withoutGlobalScope(\App\Models\Scopes\ClientScope::class);
        
        // Se houver client_id do usuário, buscar ingredientes do cliente OU sem client_id
        if (auth()->check() && auth()->user()->client_id) {
            $clientId = auth()->user()->client_id;
            $query->where(function($q) use ($clientId) {
                $q->where('client_id', $clientId)
                  ->orWhereNull('client_id'); // Incluir ingredientes antigos sem client_id
            });
        } else {
            // Se não houver client_id, mostrar apenas os sem client_id
            $query->whereNull('client_id');
        }
        
        $search = $request->input('q');
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }
        
        if ($request->ajax() || $request->wantsJson()) {
            $ingredients = $query->orderBy('name')->get();
            return response()->json([
                'ingredients' => $ingredients->map(function($ingredient) {
                    return [
                        'id' => $ingredient->id,
                        'name' => $ingredient->name,
                        'category' => $ingredient->category,
                        'cost' => (float)$ingredient->cost,
                        'stock' => (float)$ingredient->stock,
                        'unit' => $ingredient->unit,
                        'is_active' => $ingredient->is_active,
                    ];
                })
            ]);
        }
        
        $ingredients = $query->orderBy('name')->paginate(30)->withQueryString();
        $categories = Ingredient::withoutGlobalScope(\App\Models\Scopes\ClientScope::class)
            ->distinct()
            ->pluck('category')
            ->filter()
            ->sort()
            ->values();
        
        return view('dashboard.producao.ingredientes', compact('ingredients', 'categories'));
    }

    public function create()
    {
        $categories = Ingredient::withoutGlobalScope(\App\Models\Scopes\ClientScope::class)
            ->distinct()
            ->pluck('category')
            ->filter()
            ->sort()
            ->values();
        return view('dashboard.producao.ingredientes.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:120|unique:ingredients,name',
            'category' => 'nullable|string|max:50',
            'weight' => 'nullable|numeric|min:0',
            'percentage' => 'nullable|numeric|min:0',
            'is_flour' => 'boolean',
            'has_hydration' => 'boolean',
            'hydration_percentage' => 'nullable|numeric|min:0|max:100',
            'package_weight' => 'nullable|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'unit' => 'nullable|string|max:20',
            'stock' => 'nullable|numeric|min:0',
            'min_stock' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $ingredient = Ingredient::create([
            'client_id' => auth()->check() && auth()->user()->client_id ? auth()->user()->client_id : null,
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'category' => $validated['category'] ?? 'outro',
            'weight' => $validated['weight'] ?? 0,
            'percentage' => $validated['percentage'] ?? null,
            'is_flour' => $validated['is_flour'] ?? false,
            'has_hydration' => $validated['has_hydration'] ?? false,
            'hydration_percentage' => $validated['hydration_percentage'] ?? 0,
            'package_weight' => $validated['package_weight'] ?? null,
            'cost' => $validated['cost'] ?? 0,
            'unit' => $validated['unit'] ?? 'g',
            'stock' => $validated['stock'] ?? 0,
            'min_stock' => $validated['min_stock'] ?? 0,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return redirect()->route('dashboard.producao.ingredientes.index')
            ->with('success', 'Ingrediente criado com sucesso!');
    }

    public function edit($id)
    {
        // Desabilitar scope para buscar o ingrediente mesmo sem client_id
        $ingredient = Ingredient::withoutGlobalScope(\App\Models\Scopes\ClientScope::class)
            ->findOrFail($id);
            
        $categories = Ingredient::withoutGlobalScope(\App\Models\Scopes\ClientScope::class)
            ->distinct()
            ->pluck('category')
            ->filter()
            ->sort()
            ->values();
        return view('dashboard.producao.ingredientes.edit', compact('ingredient', 'categories'));
    }

    public function update(Request $request, $id)
    {
        // Desabilitar scope para buscar o ingrediente mesmo sem client_id
        $ingredient = Ingredient::withoutGlobalScope(\App\Models\Scopes\ClientScope::class)
            ->findOrFail($id);
            
        $validated = $request->validate([
            'name' => 'required|string|max:120|unique:ingredients,name,' . $ingredient->id,
            'category' => 'nullable|string|max:50',
            'weight' => 'nullable|numeric|min:0',
            'percentage' => 'nullable|numeric|min:0',
            'is_flour' => 'boolean',
            'has_hydration' => 'boolean',
            'hydration_percentage' => 'nullable|numeric|min:0|max:100',
            'package_weight' => 'nullable|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'unit' => 'nullable|string|max:20',
            'stock' => 'nullable|numeric|min:0',
            'min_stock' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $updateData = [
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'category' => $validated['category'] ?? 'outro',
            'weight' => $validated['weight'] ?? 0,
            'percentage' => $validated['percentage'] ?? null,
            'is_flour' => $validated['is_flour'] ?? false,
            'has_hydration' => $validated['has_hydration'] ?? false,
            'hydration_percentage' => $validated['hydration_percentage'] ?? 0,
            'package_weight' => $validated['package_weight'] ?? null,
            'cost' => $validated['cost'] ?? 0,
            'unit' => $validated['unit'] ?? 'g',
            'stock' => $validated['stock'] ?? 0,
            'min_stock' => $validated['min_stock'] ?? 0,
            'is_active' => $validated['is_active'] ?? true,
        ];
        
        // Se o ingrediente não tem client_id e o usuário tem, atribuir
        if (!$ingredient->client_id && auth()->check() && auth()->user()->client_id) {
            $updateData['client_id'] = auth()->user()->client_id;
        }
        
        $ingredient->update($updateData);

        return redirect()->route('dashboard.producao.ingredientes.index')
            ->with('success', 'Ingrediente atualizado com sucesso!');
    }

    public function destroy($id)
    {
        // Desabilitar scope para encontrar o ingrediente mesmo sem client_id
        $ingredient = Ingredient::withoutGlobalScope(\App\Models\Scopes\ClientScope::class)
            ->findOrFail($id);
            
        if ($ingredient->recipeIngredients()->count() > 0) {
            return redirect()->route('dashboard.producao.ingredientes.index')
                ->with('error', 'Não é possível excluir ingrediente usado em receitas.');
        }

        $ingredient->delete();
        return redirect()->route('dashboard.producao.ingredientes.index')
            ->with('success', 'Ingrediente excluído com sucesso!');
    }

    public function updateStock(Request $request, $id)
    {
        // Desabilitar scope para buscar o ingrediente mesmo sem client_id
        $ingredient = Ingredient::withoutGlobalScope(\App\Models\Scopes\ClientScope::class)
            ->findOrFail($id);
        
        $validated = $request->validate([
            'stock' => 'required|numeric|min:0',
        ]);

        $ingredient->update([
            'stock' => $validated['stock'],
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Estoque atualizado com sucesso!',
                'stock' => $ingredient->stock,
                'stock_status' => $ingredient->stock_status,
            ]);
        }

        return redirect()->route('dashboard.producao.ingredientes.index')
            ->with('success', 'Estoque atualizado com sucesso!');
    }
}
