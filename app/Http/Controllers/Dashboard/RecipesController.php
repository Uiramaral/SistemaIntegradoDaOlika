<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Recipe;
use App\Models\Ingredient;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RecipesController extends Controller
{
    public function index(Request $request)
    {
        // Desabilitar ClientScope temporariamente para mostrar todas as receitas
        // (incluindo as antigas sem client_id)
        $query = Recipe::withoutGlobalScope(\App\Models\Scopes\ClientScope::class)
            ->with(['steps.ingredients.ingredient'])
            ->latest();

        // Se houver client_id do usuário, buscar receitas do cliente OU sem client_id
        if (auth()->check() && auth()->user()->client_id) {
            $clientId = auth()->user()->client_id;
            $query->where(function ($q) use ($clientId) {
                $q->where('client_id', $clientId)
                    ->orWhereNull('client_id'); // Incluir receitas antigas sem client_id
            });
        } else {
            // Se não houver client_id, mostrar apenas as sem client_id
            $query->whereNull('client_id');
        }

        $search = $request->input('q');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        if ($request->ajax() || $request->wantsJson()) {
            $recipes = $query->get();
            return response()->json([
                'recipes' => $recipes->map(function ($recipe) {
                    return [
                        'id' => $recipe->id,
                        'name' => $recipe->name,
                        'category' => $recipe->category,
                        'total_weight' => (float) $recipe->total_weight,
                        'cost' => (float) $recipe->cost,
                        'final_price' => $recipe->final_price ? (float) $recipe->final_price : null,
                        'is_active' => $recipe->is_active,
                    ];
                })
            ]);
        }

        $recipes = $query->paginate(20)->withQueryString();
        $categories = Recipe::withoutGlobalScope(\App\Models\Scopes\ClientScope::class)
            ->distinct()
            ->pluck('category')
            ->filter()
            ->sort()
            ->values();

        return view('dashboard.producao.receitas', compact('recipes', 'categories'));
    }

    public function create()
    {
        // Desabilitar ClientScope para buscar todos os ingredientes ativos
        // (incluindo os antigos sem client_id)
        $query = Ingredient::withoutGlobalScope(\App\Models\Scopes\ClientScope::class)
            ->where('is_active', true);

        // Se houver client_id do usuário, buscar ingredientes do cliente OU sem client_id
        if (auth()->check() && auth()->user()->client_id) {
            $clientId = auth()->user()->client_id;
            $query->where(function ($q) use ($clientId) {
                $q->where('client_id', $clientId)
                    ->orWhereNull('client_id'); // Incluir ingredientes antigos sem client_id
            });
        } else {
            // Se não houver client_id, mostrar apenas os sem client_id
            $query->whereNull('client_id');
        }

        $ingredients = $query->orderBy('name')->get();

        // Buscar produtos ativos com suas variantes para vincular à receita
        // O ClientScope já filtra automaticamente por client_id do usuário logado
        $products = Product::with([
            'category',
            'variants' => function ($q) {
                $q->orderBy('sort_order');
            }
        ])
            ->where('is_active', 1)
            ->orderBy('name')
            ->get()
            ->map(function ($product) {
                $vars = $product->getRelation('variants');
                if (($vars === null || $vars->isEmpty()) && !empty($product->getRawOriginal('variants'))) {
                    $legacyVariants = $product->getAttribute('variants');
                    if (is_array($legacyVariants)) {
                        $product->setRelation('variants', collect($legacyVariants));
                    }
                }
                return $product;
            });

        $categories = Recipe::distinct()->pluck('category')->filter()->sort()->values();

        // Buscar embalagens ativas
        $clientId = auth()->check() && auth()->user()->client_id ? auth()->user()->client_id : null;
        $packagings = \App\Models\Packaging::withoutGlobalScope(\App\Models\Scopes\ClientScope::class)
            ->where(function ($q) use ($clientId) {
                if ($clientId) {
                    $q->where('client_id', $clientId)->orWhereNull('client_id');
                } else {
                    $q->whereNull('client_id');
                }
            })
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('dashboard.producao.receitas.create', compact('ingredients', 'products', 'categories', 'packagings'));
    }

    /**
     * Criar ingrediente a partir de produto
     */
    public function createIngredientFromProduct(Request $request, $productId)
    {
        $product = Product::withoutGlobalScope(\App\Models\Scopes\ClientScope::class)
            ->findOrFail($productId);

        // Verificar se já existe ingrediente com mesmo nome
        $existingIngredient = Ingredient::withoutGlobalScope(\App\Models\Scopes\ClientScope::class)
            ->where('name', $product->name)
            ->where(function ($q) use ($product) {
                if ($product->client_id) {
                    $q->where('client_id', $product->client_id);
                } else {
                    $q->whereNull('client_id');
                }
            })
            ->first();

        if ($existingIngredient) {
            return response()->json([
                'success' => true,
                'ingredient' => [
                    'id' => $existingIngredient->id,
                    'name' => $existingIngredient->name,
                ],
                'message' => 'Ingrediente já existe'
            ]);
        }

        // Criar novo ingrediente (slug único com ID do produto)
        $ingredient = Ingredient::withoutGlobalScope(\App\Models\Scopes\ClientScope::class)->create([
            'client_id' => $product->client_id,
            'name' => $product->name,
            'slug' => 'produto-' . $product->id,
            'category' => optional($product->category)->name ?? 'Geral',
            'package_weight' => $product->weight_grams ?? null,
            'cost' => $product->price > 0 ? round($product->price * 0.30, 2) : 0,
            'unit' => 'g',
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'ingredient' => [
                'id' => $ingredient->id,
                'name' => $ingredient->name,
            ],
            'message' => 'Ingrediente criado com sucesso'
        ]);
    }

    /**
     * Importar ingredientes de product_ingredient para a receita.
     * Exclui autorreferências: ingrediente com mesmo nome do produto (receita não pode ter a si mesma como ingrediente).
     */
    public function importFromProductIngredient(Request $request, $productId)
    {
        $product = Product::findOrFail($productId);
        $productName = trim(mb_strtolower($product->name ?? ''));

        $productIngredients = DB::table('product_ingredient')
            ->where('product_id', $productId)
            ->join('ingredients', 'product_ingredient.ingredient_id', '=', 'ingredients.id')
            ->whereRaw('LOWER(TRIM(ingredients.name)) != ?', [$productName ?: ' '])
            ->select(
                'product_ingredient.ingredient_id',
                'product_ingredient.percentage',
                'ingredients.name'
            )
            ->orderBy('product_ingredient.ingredient_id')
            ->get();

        if ($productIngredients->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Nenhum ingrediente válido em product_ingredient para este produto (ingredientes com mesmo nome do produto são ignorados).'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'ingredients' => $productIngredients->map(function ($pi) {
                return [
                    'ingredient_id' => $pi->ingredient_id,
                    'percentage' => $pi->percentage !== null ? (float) $pi->percentage : 0,
                    'name' => $pi->name,
                ];
            })->values()->toArray(),
            'message' => 'Ingredientes importados com sucesso',
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'variant_id' => 'required|exists:product_variants,id',
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'total_weight' => 'required|numeric|min:0',
            'hydration' => 'nullable|numeric|min:0|max:200',
            'levain' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
            'use_milk_instead_of_water' => 'boolean',
            'is_fermented' => 'boolean',
            'is_bread' => 'boolean',
            'include_notes_in_print' => 'boolean',
            'uses_baker_percentage' => 'boolean',
            'packaging_cost' => 'nullable|numeric|min:0',
            'packaging_id' => 'nullable|exists:packagings,id',
            'final_price' => 'nullable|numeric|min:0',
            'resale_price' => 'nullable|numeric|min:0',
            'steps' => 'required|array|min:1',
            'steps.*.name' => 'required|string|max:255',
            'steps.*.ingredients' => 'required|array|min:1',
            'steps.*.ingredients.*.ingredient_id' => 'required|exists:ingredients,id',
            'steps.*.ingredients.*.type' => 'nullable|string|max:20',
            'steps.*.ingredients.*.percentage' => 'nullable|numeric|min:0',
            'steps.*.ingredients.*.weight' => 'nullable|numeric|min:0',
        ]);

        $clientId = auth()->check() && auth()->user()->client_id ? auth()->user()->client_id : null;
        $catName = $validated['category'] ?? 'Geral';

        DB::beginTransaction();
        try {

            $recipe = Recipe::withoutGlobalScope(\App\Models\Scopes\ClientScope::class)->create([
                'client_id' => $clientId,
                'product_id' => $validated['product_id'],
                'variant_id' => $validated['variant_id'],
                'name' => $validated['name'],
                'category' => $catName,
                'total_weight' => $validated['total_weight'],
                'hydration' => $validated['hydration'] ?? 70,
                'levain' => $validated['levain'] ?? 30,
                'notes' => $validated['notes'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
                'use_milk_instead_of_water' => $validated['use_milk_instead_of_water'] ?? false,
                'is_fermented' => $validated['is_fermented'] ?? true,
                'is_bread' => $validated['is_bread'] ?? true,
                'include_notes_in_print' => $validated['include_notes_in_print'] ?? false,
                'uses_baker_percentage' => $validated['uses_baker_percentage'] ?? true,
                'packaging_cost' => $validated['packaging_cost'] ?? 0.5,
                'packaging_id' => $validated['packaging_id'] ?? null,
                'final_price' => $validated['final_price'] ?? null,
                'resale_price' => $validated['resale_price'] ?? null,
            ]);

            foreach ($validated['steps'] as $stepIndex => $stepData) {
                $step = $recipe->steps()->create([
                    'name' => $stepData['name'],
                    'sort_order' => $stepIndex,
                ]);

                foreach ($stepData['ingredients'] as $ingIndex => $ingData) {
                    $ri = $step->ingredients()->create([
                        'ingredient_id' => $ingData['ingredient_id'],
                        'type' => $ingData['type'] ?? 'ingredient',
                        'percentage' => $ingData['percentage'] ?? null,
                        'weight' => $ingData['weight'] ?? null,
                        'sort_order' => $ingIndex,
                    ]);

                    DB::table('product_ingredient')->insertOrIgnore([
                        ['product_id' => $validated['product_id'], 'ingredient_id' => $ri->ingredient_id, 'percentage' => $ri->percentage],
                    ]);
                }
            }

            $recipe->refresh();
            $recipe->load(['steps.ingredients.ingredient']);
            $recipe->calculateCost();
            $recipe->save();

            DB::commit();

            \Log::info('Receita criada', [
                'recipe_id' => $recipe->id,
                'client_id' => $recipe->client_id,
                'name' => $recipe->name
            ]);

            return redirect()->route('dashboard.producao.receitas.index')
                ->with('success', 'Receita criada com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erro ao criar receita', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withInput()->with('error', 'Erro ao criar receita: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        // Desabilitar scope para buscar a receita mesmo sem client_id
        $recipe = Recipe::withoutGlobalScope(\App\Models\Scopes\ClientScope::class)
            ->findOrFail($id);
        $recipe->load(['steps.ingredients.ingredient']);
        return view('dashboard.producao.receitas.show', compact('recipe'));
    }

    public function edit($id)
    {
        // Desabilitar scope para buscar a receita mesmo sem client_id
        $recipe = Recipe::withoutGlobalScope(\App\Models\Scopes\ClientScope::class)
            ->findOrFail($id);
        $recipe->load(['steps.ingredients.ingredient', 'product.variants']);

        // Processar variantes legadas do produto da receita
        if ($recipe->product) {
            $vars = $recipe->product->getRelation('variants');
            if (($vars === null || $vars->isEmpty()) && !empty($recipe->product->getRawOriginal('variants'))) {
                $legacyVariants = $recipe->product->getAttribute('variants');
                if (is_array($legacyVariants)) {
                    $recipe->product->setRelation('variants', collect($legacyVariants));
                }
            }
        }

        // Desabilitar ClientScope para buscar todos os ingredientes ativos
        // (incluindo os antigos sem client_id)
        $query = Ingredient::withoutGlobalScope(\App\Models\Scopes\ClientScope::class)
            ->where('is_active', true);

        // Se houver client_id do usuário, buscar ingredientes do cliente OU sem client_id
        if (auth()->check() && auth()->user()->client_id) {
            $clientId = auth()->user()->client_id;
            $query->where(function ($q) use ($clientId) {
                $q->where('client_id', $clientId)
                    ->orWhereNull('client_id'); // Incluir ingredientes antigos sem client_id
            });
        } else {
            // Se não houver client_id, mostrar apenas os sem client_id
            $query->whereNull('client_id');
        }

        $ingredients = $query->orderBy('name')->get();

        // Buscar produtos ativos com suas variantes para vincular à receita
        // O ClientScope já filtra automaticamente por client_id do usuário logado
        $products = Product::with([
            'category',
            'variants' => function ($q) {
                $q->orderBy('sort_order');
            }
        ])
            ->where('is_active', 1)
            ->orderBy('name')
            ->get()
            ->map(function ($product) {
                $vars = $product->getRelation('variants');
                if (($vars === null || $vars->isEmpty()) && !empty($product->getRawOriginal('variants'))) {
                    $legacyVariants = $product->getAttribute('variants');
                    if (is_array($legacyVariants)) {
                        $product->setRelation('variants', collect($legacyVariants));
                    }
                }
                return $product;
            });

        $categories = Recipe::distinct()->pluck('category')->filter()->sort()->values();

        // Buscar embalagens ativas
        $clientId = auth()->check() && auth()->user()->client_id ? auth()->user()->client_id : null;
        $packagings = \App\Models\Packaging::withoutGlobalScope(\App\Models\Scopes\ClientScope::class)
            ->where(function ($q) use ($clientId) {
                if ($clientId) {
                    $q->where('client_id', $clientId)->orWhereNull('client_id');
                } else {
                    $q->whereNull('client_id');
                }
            })
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('dashboard.producao.receitas.edit', compact('recipe', 'ingredients', 'products', 'categories', 'packagings'));
    }

    public function update(Request $request, $id)
    {
        // Desabilitar scope para buscar a receita mesmo sem client_id
        $recipe = Recipe::withoutGlobalScope(\App\Models\Scopes\ClientScope::class)
            ->findOrFail($id);

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'variant_id' => 'required|exists:product_variants,id',
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'total_weight' => 'required|numeric|min:0',
            'hydration' => 'nullable|numeric|min:0|max:200',
            'levain' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
            'use_milk_instead_of_water' => 'boolean',
            'is_fermented' => 'boolean',
            'is_bread' => 'boolean',
            'include_notes_in_print' => 'boolean',
            'uses_baker_percentage' => 'boolean',
            'packaging_cost' => 'nullable|numeric|min:0',
            'packaging_id' => 'nullable|exists:packagings,id',
            'final_price' => 'nullable|numeric|min:0',
            'resale_price' => 'nullable|numeric|min:0',
            'steps' => 'required|array|min:1',
            'steps.*.name' => 'required|string|max:255',
            'steps.*.ingredients' => 'required|array|min:1',
            'steps.*.ingredients.*.ingredient_id' => 'required|exists:ingredients,id',
            'steps.*.ingredients.*.type' => 'nullable|string|max:20',
            'steps.*.ingredients.*.percentage' => 'nullable|numeric|min:0',
            'steps.*.ingredients.*.weight' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $updateData = [
                'product_id' => $validated['product_id'],
                'variant_id' => $validated['variant_id'],
                'name' => $validated['name'],
                'category' => $validated['category'] ?? null,
                'total_weight' => $validated['total_weight'],
                'hydration' => $validated['hydration'] ?? 70,
                'levain' => $validated['levain'] ?? 30,
                'notes' => $validated['notes'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
                'use_milk_instead_of_water' => $validated['use_milk_instead_of_water'] ?? false,
                'is_fermented' => $validated['is_fermented'] ?? true,
                'is_bread' => $validated['is_bread'] ?? true,
                'include_notes_in_print' => $validated['include_notes_in_print'] ?? false,
                'uses_baker_percentage' => $validated['uses_baker_percentage'] ?? true,
                'packaging_cost' => $validated['packaging_cost'] ?? 0.5,
                'packaging_id' => $validated['packaging_id'] ?? null,
                'final_price' => $validated['final_price'] ?? null,
                'resale_price' => $validated['resale_price'] ?? null,
            ];

            // Se a receita não tem client_id e o usuário tem, atribuir
            if (!$recipe->client_id && auth()->check() && auth()->user()->client_id) {
                $updateData['client_id'] = auth()->user()->client_id;
            }

            $recipe->update($updateData);

            // Remover steps antigos
            $recipe->steps()->delete();

            // Sincronizar product_ingredient: remover todos do produto
            DB::table('product_ingredient')->where('product_id', $recipe->product_id)->delete();

            // Criar novos steps e product_ingredient
            foreach ($validated['steps'] as $stepIndex => $stepData) {
                $step = $recipe->steps()->create([
                    'name' => $stepData['name'],
                    'sort_order' => $stepIndex,
                ]);

                foreach ($stepData['ingredients'] as $ingIndex => $ingData) {
                    $ri = $step->ingredients()->create([
                        'ingredient_id' => $ingData['ingredient_id'],
                        'type' => $ingData['type'] ?? 'ingredient',
                        'percentage' => $ingData['percentage'] ?? null,
                        'weight' => $ingData['weight'] ?? null,
                        'sort_order' => $ingIndex,
                    ]);

                    DB::table('product_ingredient')->insertOrIgnore([
                        ['product_id' => $recipe->product_id, 'ingredient_id' => $ri->ingredient_id, 'percentage' => $ri->percentage],
                    ]);
                }
            }

            $recipe->refresh();
            $recipe->load(['steps.ingredients.ingredient']);
            $recipe->calculateCost();
            $recipe->save();

            DB::commit();
            return redirect()->route('dashboard.producao.receitas.index')
                ->with('success', 'Receita atualizada com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Erro ao atualizar receita: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        // Desabilitar scope para encontrar a receita mesmo sem client_id
        $recipe = Recipe::withoutGlobalScope(\App\Models\Scopes\ClientScope::class)
            ->findOrFail($id);

        if ($recipe->productionRecords()->count() > 0) {
            return redirect()->route('dashboard.producao.receitas.index')
                ->with('error', 'Não é possível excluir receita com registros de produção.');
        }

        $recipe->delete();
        return redirect()->route('dashboard.producao.receitas.index')
            ->with('success', 'Receita excluída com sucesso!');
    }
}
