<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use App\Models\ProductionList;
use App\Models\ProductionListItem;
use App\Models\ProductionRecord;
use App\Models\Recipe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProductionController extends Controller
{
    public function dashboard()
    {
        $clientId = currentClientId();

        // Estatísticas gerais
        $totalRecipes = Recipe::count();
        $activeRecipes = Recipe::where('is_active', true)->count();
        $totalProductionRecords = ProductionRecord::count();
        $todayProduction = ProductionRecord::whereDate('production_date', today())->sum('total_produced');

        // Itens mais produzidos (últimos 30 dias)
        $mostProduced = ProductionRecord::select('recipe_id', 'recipe_name', DB::raw('SUM(quantity) as total_quantity'))
            ->where('production_date', '>=', Carbon::now()->subDays(30))
            ->groupBy('recipe_id', 'recipe_name')
            ->orderBy('total_quantity', 'desc')
            ->limit(10)
            ->get();

        // Produção por dia (últimos 7 dias)
        $dailyProduction = ProductionRecord::select(
            DB::raw('DATE(production_date) as date'),
            DB::raw('SUM(total_produced) as total')
        )
            ->where('production_date', '>=', Carbon::now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('dashboard.producao.dashboard', compact(
            'totalRecipes',
            'activeRecipes',
            'totalProductionRecords',
            'todayProduction',
            'mostProduced',
            'dailyProduction'
        ));
    }

    public function listaProducao(Request $request)
    {
        \Carbon\Carbon::setLocale('pt_BR');
        $clientId = currentClientId();
        $date = $request->input('date', today()->format('Y-m-d'));

        $list = ProductionList::withoutGlobalScope(\App\Models\Scopes\ClientScope::class)
            ->where('client_id', $clientId)
            ->whereDate('production_date', $date)
            ->with([
                'items' => function ($query) {
                    $query->orderBy('sort_order');
                },
                'items.recipe.steps.ingredients' => function ($query) {
                    $query->with([
                        'ingredient' => function ($q) {
                            $q->withoutGlobalScope(\App\Models\Scopes\ClientScope::class);
                        }
                    ]);
                }
            ])
            ->first();

        $recipes = Recipe::withoutGlobalScope(\App\Models\Scopes\ClientScope::class)
            ->where(function ($q) use ($clientId) {
                $q->where('client_id', $clientId)->orWhereNull('client_id');
            })
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Lógica para o carrossel de datas (D-3 a D+3)
        $selectedDate = Carbon::parse($date);
        $startDate = $selectedDate->copy()->subDays(3);
        $endDate = $selectedDate->copy()->addDays(3);

        $availableDates = [];
        $tempDate = $startDate->copy();
        while ($tempDate <= $endDate) {
            $dateStr = $tempDate->format('Y-m-d');

            // Contar itens para cada dia
            $count = \App\Models\ProductionListItem::whereHas('productionList', function ($q) use ($clientId, $dateStr) {
                $q->withoutGlobalScope(\App\Models\Scopes\ClientScope::class)
                    ->where('client_id', $clientId)
                    ->whereDate('production_date', $dateStr);
            })->count();

            $availableDates[] = [
                'date' => $dateStr,
                'day' => $tempDate->day,
                'month' => ucfirst(str_replace('.', '', $tempDate->translatedFormat('M'))),
                'weekday' => ucfirst(str_replace('-feira', '', $tempDate->translatedFormat('l'))),
                'is_selected' => $dateStr === $date,
                'count' => $count
            ];
            $tempDate->addDay();
        }

        // Consolidação e Divisão por Lotes (3kg)
        $items = $list ? $this->consolidateAndSplitItems($list->items) : collect();

        return view('dashboard.producao.lista-producao', compact('list', 'recipes', 'date', 'availableDates', 'items'));
    }

    public function printProductionList(Request $request, $id)
    {
        $clientId = currentClientId();
        $replaceLevain = $request->boolean('replace_levain', false);

        $list = ProductionList::withoutGlobalScope(\App\Models\Scopes\ClientScope::class)
            ->where('client_id', $clientId)
            ->with([
                'items' => function ($query) {
                    $query->orderBy('sort_order');
                }
            ])
            ->findOrFail($id);

        if ($list->items->isEmpty()) {
            \Log::warning('Lista de produção vazia', [
                'list_id' => $list->id,
                'client_id' => $clientId
            ]);

            $date = $list->production_date->format('Y-m-d');
            $recipes = [];
            $totalLevain = 0;
            $totalRecipes = 0;

            return view('dashboard.producao.print-queue', compact('recipes', 'totalLevain', 'totalRecipes', 'date', 'replaceLevain'));
        }

        // CONSOLIDAÇÃO E DIVISÃO POR LOTES (3kg)
        $itemsToPrint = $list->items->filter(fn($i) => $i->mark_for_print ?? true);
        $batchItems = $this->consolidateAndSplitItems($itemsToPrint);

        if ($batchItems->isEmpty()) {
            $date = $list->production_date->format('Y-m-d');
            $recipes = [];
            $totalLevain = 0;
            $totalRecipes = 0;
            return view('dashboard.producao.print-queue', compact('recipes', 'totalLevain', 'totalRecipes', 'date', 'replaceLevain'));
        }

        $recipes = [];
        $totalLevain = 0;
        $totalRecipes = 0;

        foreach ($batchItems as $item) {
            $recipe = $item->recipe;

            if ($recipe) {
                $itemWeight = (float) $item->weight;
                $itemQuantity = (int) $item->quantity;
                $totalWeight = $itemWeight * $itemQuantity;
                $totalRecipes += $itemQuantity;

                // Garantir relacionamentos para o cálculo
                if (!$recipe->relationLoaded('steps')) {
                    $recipe->load('steps.ingredients.ingredient');
                }

                $calculated = $recipe->calculateIngredientWeights($itemWeight);
                $ingredients = [];

                foreach ($calculated as $key => $row) {
                    $w = (float) ($row['weight'] ?? 0) * $itemQuantity;
                    if ($w <= 0)
                        continue;

                    if ($key === '_water') {
                        $waterWeight = $w * 0.6; // 60% água gelada
                        $iceWeight = $w * 0.4;   // 40% gelo
                        $ingredients[] = ['ingredient' => (object) ['name' => 'Água gelada'], 'weight' => $waterWeight];
                        $ingredients[] = ['ingredient' => (object) ['name' => 'Gelo'], 'weight' => $iceWeight];
                        continue;
                    }

                    if ($key === '_levain') {
                        if ($replaceLevain)
                            continue;
                        $totalLevain += $w;
                        $name = $row['label'] ?? 'Levain';
                        $ingredients[] = ['ingredient' => (object) ['name' => $name], 'weight' => $w];
                        continue;
                    }

                    if (strpos($key, '_') === 0)
                        continue;

                    $ing = $row['ingredient'] ?? null;
                    if (!$ing)
                        continue;

                    if ($replaceLevain && stripos($ing->name, 'levain') !== false)
                        continue;

                    $ingredients[] = ['ingredient' => $ing, 'weight' => $w];
                }

                if ($replaceLevain) {
                    $fermento = Ingredient::withoutGlobalScope(\App\Models\Scopes\ClientScope::class)
                        ->where(function ($q) {
                            $q->where('name', 'like', '%fermento%liofilizado%')
                                ->orWhere('name', 'like', '%fermento%seco%')
                                ->orWhere('name', 'like', '%fermento biológico%');
                        })
                        ->first();
                    if ($fermento) {
                        $flour = $recipe->getFlourWeight($itemWeight);
                        $fermentoWeight = ($flour * $itemQuantity * 1.5) / 100;
                        $ingredients[] = ['ingredient' => $fermento, 'weight' => $fermentoWeight];
                    }
                }

                usort($ingredients, function ($a, $b) {
                    $nameA = is_object($a['ingredient']) ? $a['ingredient']->name : '';
                    $nameB = is_object($b['ingredient']) ? $b['ingredient']->name : '';
                    return strcmp($nameA, $nameB);
                });

                $recipes[] = [
                    'recipe' => $recipe,
                    'recipe_name' => $item->recipe_name,
                    'quantity' => $itemQuantity,
                    'weight' => $itemWeight,
                    'total_weight' => $totalWeight,
                    'observation' => $item->observation,
                    'ingredients' => $ingredients,
                ];
            }
        }

        $date = $list->production_date->format('Y-m-d');

        \Log::info('Impressão de lista de produção', [
            'list_id' => $list->id,
            'items_count' => $list->items->count(),
            'recipes_count' => count($recipes),
            'total_recipes' => $totalRecipes,
            'total_levain' => $totalLevain
        ]);

        return view('dashboard.producao.print-queue', compact('recipes', 'totalLevain', 'totalRecipes', 'date', 'replaceLevain'));
    }

    public function createList(Request $request)
    {
        $clientId = currentClientId();

        $validated = $request->validate([
            'production_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        // Verificar se já existe lista para esta data
        $existingList = ProductionList::withoutGlobalScope(\App\Models\Scopes\ClientScope::class)
            ->where('client_id', $clientId)
            ->whereDate('production_date', $validated['production_date'])
            ->first();

        if ($existingList) {
            return redirect()->route('dashboard.producao.lista-producao.index', ['date' => $validated['production_date']])
                ->with('info', 'Lista já existe para esta data!');
        }

        $list = ProductionList::create([
            'client_id' => $clientId,
            'production_date' => $validated['production_date'],
            'status' => 'active',
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('dashboard.producao.lista-producao.index', ['date' => $validated['production_date']])
            ->with('success', 'Lista de produção criada!');
    }

    public function addItemToList(Request $request, $listId)
    {
        $clientId = currentClientId();

        $validated = $request->validate([
            'recipe_id' => 'required|integer',
            'quantity' => 'required|integer|min:1',
            'weight' => 'nullable|numeric|min:0',
            'observation' => 'nullable|string',
        ]);

        $list = ProductionList::withoutGlobalScope(\App\Models\Scopes\ClientScope::class)
            ->where('client_id', $clientId)
            ->findOrFail($listId);

        $recipe = Recipe::withoutGlobalScope(\App\Models\Scopes\ClientScope::class)
            ->find($validated['recipe_id']);

        if (!$recipe) {
            return back()->with('error', 'Receita não encontrada!');
        }

        // Verificar se já existe o item na lista
        $existingItem = $list->items()
            ->where('recipe_id', $validated['recipe_id'])
            ->where('weight', $validated['weight'] ?? $recipe->total_weight)
            ->first();

        if ($existingItem) {
            // Atualizar quantidade
            $newObservation = $validated['observation'] ?? null;
            $existingObservation = $existingItem->observation;

            if ($newObservation) {
                $finalObservation = $existingObservation ?
                    $existingObservation . ' | ' . $newObservation :
                    $newObservation;
            } else {
                $finalObservation = $existingObservation;
            }

            $existingItem->update([
                'quantity' => $existingItem->quantity + $validated['quantity'],
                'observation' => $finalObservation
            ]);
        } else {
            // Criar novo item
            $list->items()->create([
                'recipe_id' => $validated['recipe_id'],
                'recipe_name' => $recipe->name,
                'quantity' => $validated['quantity'],
                'weight' => $validated['weight'] ?? $recipe->total_weight,
                'observation' => $validated['observation'] ?? null,
                'mark_for_print' => true,
                'sort_order' => ($list->items()->max('sort_order') ?? 0) + 1,
            ]);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Item adicionado à lista!'
            ]);
        }

        return back()->with('success', 'Item adicionado à lista!');
    }

    public function toggleMarkForPrint(Request $request, $id)
    {
        $clientId = currentClientId();
        $ids = explode(',', $id);

        $items = ProductionListItem::whereHas('productionList', fn($q) => $q->where('client_id', $clientId))
            ->whereIn('id', $ids)
            ->get();

        if ($items->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Itens não encontrados'], 404);
        }

        // Toggle baseado no primeiro item
        $target = !$items->first()->mark_for_print;
        foreach ($items as $item) {
            $item->update(['mark_for_print' => $target]);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'mark_for_print' => $target,
                'message' => $target ? 'Marcado para impressão' : 'Desmarcado da impressão',
            ]);
        }

        return back()->with('success', 'Itens atualizados!');
    }

    public function addRecipeToTodayList(Request $request)
    {
        $clientId = currentClientId();
        $date = today()->format('Y-m-d');

        $validated = $request->validate([
            'recipe_id' => 'required|integer',
            'quantity' => 'required|integer|min:1',
            'weight' => 'nullable|numeric|min:0',
            'observation' => 'nullable|string',
        ]);

        // Buscar ou criar lista para hoje
        $list = ProductionList::withoutGlobalScope(\App\Models\Scopes\ClientScope::class)
            ->where('client_id', $clientId)
            ->whereDate('production_date', $date)
            ->first();

        if (!$list) {
            $list = ProductionList::create([
                'client_id' => $clientId,
                'production_date' => $date,
                'status' => 'active',
            ]);
        }

        $recipe = Recipe::withoutGlobalScope(\App\Models\Scopes\ClientScope::class)
            ->find($validated['recipe_id']);

        if (!$recipe) {
            return response()->json([
                'success' => false,
                'message' => 'Receita não encontrada!'
            ], 404);
        }

        // Verificar se já existe o item na lista
        $existingItem = $list->items()
            ->where('recipe_id', $validated['recipe_id'])
            ->where(function ($q) use ($validated, $recipe) {
                $itemWeight = $validated['weight'] ?? $recipe->total_weight;
                $q->whereRaw('ABS(weight - ?) < 0.01', [$itemWeight]);
            })
            ->first();

        if ($existingItem) {
            // Atualizar quantidade
            $newObservation = $validated['observation'] ?? null;
            $existingObservation = $existingItem->observation;

            if ($newObservation) {
                $finalObservation = $existingObservation ?
                    $existingObservation . ' | ' . $newObservation :
                    $newObservation;
            } else {
                $finalObservation = $existingObservation;
            }

            $existingItem->update([
                'quantity' => $existingItem->quantity + $validated['quantity'],
                'observation' => $finalObservation
            ]);
        } else {
            // Criar novo item (padrão: marcar para impressão)
            $list->items()->create([
                'recipe_id' => $validated['recipe_id'],
                'recipe_name' => $recipe->name,
                'quantity' => $validated['quantity'],
                'weight' => $validated['weight'] ?? $recipe->total_weight,
                'observation' => $validated['observation'] ?? null,
                'mark_for_print' => true,
                'sort_order' => (($list->items()->max('sort_order')) ?? 0) + 1,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Item adicionado à lista de produção!',
            'list_id' => $list->id
        ]);
    }

    public function markItemProduced(Request $request, $id)
    {
        try {
            $clientId = currentClientId();

            // Suporte a múltiplos IDs separados por vírgula (para lotes consolidados)
            $ids = explode(',', $id);

            $items = \App\Models\ProductionListItem::whereIn('id', $ids)
                ->whereHas('productionList', function ($q) use ($clientId) {
                    $q->where('client_id', $clientId);
                })
                ->get();

            if ($items->isEmpty()) {
                if ($request->ajax())
                    return response()->json(['success' => false, 'message' => 'Itens não encontrados'], 404);
                return back()->with('error', 'Itens não encontrados');
            }

            // O status final será o oposto do status do primeiro item (toggle)
            $targetStatus = !$items->first()->is_produced;

            foreach ($items as $item) {
                $wasProduced = $item->is_produced;

                $item->update([
                    'is_produced' => $targetStatus,
                    'produced_at' => $targetStatus ? now() : null,
                ]);

                // Criar registro de produção apenas quando marcar como produzido
                if ($item->is_produced && !$wasProduced) {
                    $list = $item->productionList;
                    $recipe = $item->recipe;

                    ProductionRecord::create([
                        'client_id' => $clientId,
                        'recipe_id' => $item->recipe_id,
                        'recipe_name' => $item->recipe_name,
                        'quantity' => $item->quantity,
                        'weight' => $item->weight,
                        'total_produced' => $item->quantity * $item->weight,
                        'production_date' => $list ? $list->production_date : today(),
                        'observation' => $item->observation,
                        'cost' => $recipe ? $recipe->cost * $item->quantity : 0,
                    ]);

                    // Abater estoque dos ingredientes usados na receita
                    if ($recipe) {
                        $this->deductIngredientStock($recipe, $item->quantity);
                    }
                }
            }

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => count($items) > 1 ? 'Lote atualizado!' : 'Item atualizado!',
                    'is_produced' => $targetStatus
                ]);
            }

            return back()->with('success', count($items) > 1 ? 'Lote atualizado!' : 'Item atualizado!');
        } catch (\Exception $e) {
            if ($request->ajax())
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            return back()->with('error', $e->getMessage());
        }
    }

    public function removeItemFromList(Request $request, $id)
    {
        $clientId = currentClientId();
        $ids = explode(',', $id);

        \App\Models\ProductionListItem::whereHas('productionList', function ($q) use ($clientId) {
            $q->where('client_id', $clientId);
        })
            ->whereIn('id', $ids)
            ->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Item(ns) removido(s) da lista!'
            ]);
        }

        return back()->with('success', 'Item(s) removido(s) da lista!');
    }

    public function listaCompras(Request $request)
    {
        $date = $request->input('date', today()->format('Y-m-d'));

        $list = ProductionList::whereDate('production_date', $date)
            ->with(['items.recipe.steps.ingredients.ingredient'])
            ->first();

        $shoppingList = [];

        if ($list) {
            foreach ($list->items as $item) {
                if (!$item->is_produced && $item->recipe) {
                    foreach ($item->recipe->steps as $step) {
                        foreach ($step->ingredients as $ri) {
                            if ($ri->ingredient) {
                                $ingredientId = $ri->ingredient->id;
                                $needed = ($ri->calculated_weight / 1000) * $item->quantity; // Converter para kg

                                if (!isset($shoppingList[$ingredientId])) {
                                    $shoppingList[$ingredientId] = [
                                        'ingredient' => $ri->ingredient,
                                        'needed' => 0,
                                        'current_stock' => $ri->ingredient->stock ?? 0,
                                        'min_stock' => $ri->ingredient->min_stock ?? 0,
                                    ];
                                }

                                $shoppingList[$ingredientId]['needed'] += $needed;
                            }
                        }
                    }
                }
            }
        }

        return view('dashboard.producao.lista-compras', compact('shoppingList', 'date'));
    }

    public function relatorios(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->input('end_date', today()->format('Y-m-d'));

        // Itens mais produzidos
        $mostProduced = ProductionRecord::select('recipe_id', 'recipe_name', DB::raw('SUM(quantity) as total_quantity'), DB::raw('SUM(total_produced) as total_weight'))
            ->whereBetween('production_date', [$startDate, $endDate])
            ->groupBy('recipe_id', 'recipe_name')
            ->orderBy('total_quantity', 'desc')
            ->get();

        // Produção por dia
        $dailyProduction = ProductionRecord::select(
            DB::raw('DATE(production_date) as date'),
            DB::raw('SUM(quantity) as total_quantity'),
            DB::raw('SUM(total_produced) as total_weight'),
            DB::raw('SUM(cost) as total_cost')
        )
            ->whereBetween('production_date', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Totalizadores
        $totalQuantity = $mostProduced->sum('total_quantity');
        $totalWeight = $dailyProduction->sum('total_weight');
        $totalCost = $dailyProduction->sum('total_cost');

        return view('dashboard.producao.relatorios', compact(
            'mostProduced',
            'dailyProduction',
            'totalQuantity',
            'totalWeight',
            'totalCost',
            'startDate',
            'endDate'
        ));
    }

    // =============================================================================
    // FILA DE IMPRESSÃO (Print Queue)
    // =============================================================================

    /**
     * Retorna a fila de impressão a partir da Lista de Produção de hoje (itens marcados para imprimir).
     */
    public function getPrintQueueFromList(Request $request)
    {
        $clientId = currentClientId();
        $date = today()->format('Y-m-d');

        $list = ProductionList::withoutGlobalScope(\App\Models\Scopes\ClientScope::class)
            ->where('client_id', $clientId)
            ->whereDate('production_date', $date)
            ->with(['items' => fn($q) => $q->orderBy('sort_order')])
            ->first();

        $queue = [];
        $totalLevain = 0;
        $listId = null;

        if ($list) {
            $itemsToPrint = $list->items->filter(fn($i) => $i->mark_for_print ?? true);
            $listId = $list->id;

            foreach ($itemsToPrint as $item) {
                $queue[] = [
                    'recipe_id' => $item->recipe_id,
                    'recipe_name' => $item->recipe_name,
                    'quantity' => (int) $item->quantity,
                    'weight' => (float) $item->weight,
                    'observation' => $item->observation ?? '',
                ];
            }
        }

        return response()->json([
            'list_id' => $listId,
            'date' => $date,
            'queue' => $queue,
            'total_items' => count($queue),
            'total_levain' => round($totalLevain, 1),
        ]);
    }

    public function addToPrintQueue(Request $request)
    {
        try {
            $validated = $request->validate([
                'recipe_id' => 'required|integer',
                'quantity' => 'required|integer|min:1',
                'weight' => 'nullable|numeric|min:0',
                'observation' => 'nullable|string|max:500',
            ]);

            $recipe = Recipe::withoutGlobalScope(\App\Models\Scopes\ClientScope::class)
                ->with(['steps.ingredients.ingredient'])
                ->find($validated['recipe_id']);

            if (!$recipe) {
                throw new \Exception('Receita não encontrada');
            }

            $queue = session('print_queue', []);

            // Verificar se já existe na fila (mesma receita e mesmo peso)
            $existingIndex = null;
            foreach ($queue as $index => $item) {
                $itemWeight = $item['weight'] ?? $recipe->total_weight;
                $newWeight = $validated['weight'] ?? $recipe->total_weight;

                if (
                    $item['recipe_id'] == $validated['recipe_id'] &&
                    abs($itemWeight - $newWeight) < 0.01
                ) { // Comparação com tolerância para decimais
                    $existingIndex = $index;
                    break;
                }
            }

            if ($existingIndex !== null) {
                // Atualizar quantidade se já existe
                $queue[$existingIndex]['quantity'] += $validated['quantity'];
                if (!empty($validated['observation'])) {
                    $existingObservation = $queue[$existingIndex]['observation'] ?? '';
                    if ($existingObservation) {
                        $queue[$existingIndex]['observation'] = $existingObservation . ' | ' . $validated['observation'];
                    } else {
                        $queue[$existingIndex]['observation'] = $validated['observation'];
                    }
                }
            } else {
                // Adicionar novo item
                $queue[] = [
                    'recipe_id' => $recipe->id,
                    'recipe_name' => $recipe->name,
                    'quantity' => $validated['quantity'],
                    'weight' => $validated['weight'] ?? $recipe->total_weight,
                    'observation' => $validated['observation'] ?? null,
                ];
            }

            session(['print_queue' => $queue]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Receita adicionada à fila!',
                    'queue_count' => count($queue)
                ]);
            }

            return back()->with('success', 'Receita adicionada à fila de impressão!');
        } catch (\Exception $e) {
            \Log::error('Erro ao adicionar à fila de impressão', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao adicionar receita: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Erro ao adicionar receita à fila: ' . $e->getMessage());
        }
    }

    public function getPrintQueue()
    {
        $queue = session('print_queue', []);
        $recipes = [];
        $totalLevain = 0;

        foreach ($queue as $index => $item) {
            $recipe = Recipe::withoutGlobalScope(\App\Models\Scopes\ClientScope::class)
                ->with(['steps.ingredients.ingredient'])
                ->find($item['recipe_id']);

            if ($recipe) {
                $itemWeight = (float) ($item['weight'] ?? $recipe->total_weight);
                $qty = (int) ($item['quantity'] ?? 1);
                $calculated = $recipe->calculateIngredientWeights($itemWeight);
                if (isset($calculated['_levain']['weight'])) {
                    $totalLevain += $calculated['_levain']['weight'] * $qty;
                }

                $recipes[] = [
                    'index' => $index,
                    'recipe_id' => $recipe->id,
                    'recipe_name' => $item['recipe_name'],
                    'quantity' => $qty,
                    'weight' => $itemWeight,
                    'observation' => $item['observation'] ?? '',
                ];
            }
        }

        return response()->json([
            'queue' => $recipes,
            'total_items' => count($queue),
            'total_levain' => round($totalLevain, 1),
        ]);
    }

    public function removeFromPrintQueue(Request $request, $index)
    {
        try {
            $queue = session('print_queue', []);

            if (isset($queue[$index])) {
                unset($queue[$index]);
                $queue = array_values($queue); // Reindexar
                session(['print_queue' => $queue]);

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Item removido da fila!',
                        'queue_count' => count($queue)
                    ]);
                }

                return back()->with('success', 'Item removido da fila!');
            }

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item não encontrado na fila!'
                ], 404);
            }

            return back()->with('error', 'Item não encontrado na fila!');
        } catch (\Exception $e) {
            \Log::error('Erro ao remover da fila de impressão', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao remover item: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Erro ao remover item da fila!');
        }
    }

    public function clearPrintQueue()
    {
        try {
            session()->forget('print_queue');

            return response()->json([
                'success' => true,
                'message' => 'Fila limpa!'
            ]);
        } catch (\Exception $e) {
            \Log::error('Erro ao limpar fila de impressão', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao limpar fila: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updatePrintQueueItem(Request $request, $index)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
            'observation' => 'nullable|string|max:500',
        ]);

        $queue = session('print_queue', []);

        if (isset($queue[$index])) {
            $queue[$index]['quantity'] = $validated['quantity'];
            $queue[$index]['observation'] = $validated['observation'] ?? null;
            session(['print_queue' => $queue]);

            return response()->json([
                'success' => true,
                'message' => 'Item atualizado!'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Item não encontrado na fila!'
        ], 404);
    }

    public function printQueue(Request $request)
    {
        $queue = session('print_queue', []);
        $replaceLevain = $request->boolean('replace_levain', false);
        $date = $request->input('date', today()->format('Y-m-d'));

        // CONSOLIDAÇÃO E DIVISÃO POR LOTES (3kg)
        $batchItems = $this->consolidateAndSplitItems($queue);

        $recipes = [];
        $totalLevain = 0;
        $totalRecipes = 0;

        foreach ($batchItems as $item) {
            $recipe = $item->recipe;

            if ($recipe) {
                $itemWeight = (float) $item->weight;
                $itemQuantity = (int) $item->quantity;
                $totalWeight = $itemWeight * $itemQuantity;
                $totalRecipes += $itemQuantity;

                $calculated = $recipe->calculateIngredientWeights($itemWeight);
                $ingredients = [];

                foreach ($calculated as $key => $row) {
                    $w = (float) ($row['weight'] ?? 0) * $itemQuantity;
                    if ($w <= 0)
                        continue;

                    if ($key === '_levain') {
                        if ($replaceLevain)
                            continue;
                        $totalLevain += $w;
                    }

                    if ($key === '_water') {
                        // Divisão de água/gelo apenas para PÃES
                        $waterWeight = $w * 0.6; // 60% água gelada
                        $iceWeight = $w * 0.4;   // 40% gelo
                        $ingredients[] = ['ingredient' => (object) ['name' => 'Água gelada'], 'weight' => $waterWeight];
                        $ingredients[] = ['ingredient' => (object) ['name' => 'Gelo'], 'weight' => $iceWeight];
                        continue;
                    }

                    if ($key === '_levain') {
                        $name = $row['label'] ?? 'Levain';
                        $ingredients[] = ['ingredient' => (object) ['name' => $name], 'weight' => $w];
                        continue;
                    }

                    $ing = $row['ingredient'] ?? null;
                    if (!$ing)
                        continue;

                    if ($replaceLevain && stripos($ing->name, 'levain') !== false)
                        continue;

                    $ingredients[] = ['ingredient' => $ing, 'weight' => $w];
                }

                if ($replaceLevain) {
                    $fermento = Ingredient::withoutGlobalScope(\App\Models\Scopes\ClientScope::class)
                        ->where(function ($q) {
                            $q->where('name', 'like', '%fermento%liofilizado%')
                                ->orWhere('name', 'like', '%fermento%seco%')
                                ->orWhere('name', 'like', '%fermento biológico%');
                        })
                        ->first();
                    if ($fermento) {
                        $flour = $recipe->getFlourWeight($itemWeight);
                        $fermentoWeight = ($flour * $itemQuantity * 1.5) / 100;
                        $ingredients[] = ['ingredient' => $fermento, 'weight' => $fermentoWeight];
                    }
                }

                usort($ingredients, fn($a, $b) => strcmp($a['ingredient']->name, $b['ingredient']->name));

                $recipes[] = [
                    'recipe' => $recipe,
                    'recipe_name' => $item->recipe_name,
                    'quantity' => $itemQuantity,
                    'weight' => $itemWeight,
                    'total_weight' => $totalWeight,
                    'observation' => $item->observation,
                    'ingredients' => $ingredients,
                ];
            }
        }

        return view('dashboard.producao.print-queue', compact('recipes', 'totalLevain', 'totalRecipes', 'date', 'replaceLevain'));
    }

    /**
     * Abater estoque dos ingredientes quando uma receita é produzida
     */
    private function deductIngredientStock(Recipe $recipe, int $quantity)
    {
        try {
            // Calcular ingredientes necessários para a quantidade produzida
            $calculated = $recipe->calculateIngredientWeights($recipe->total_weight * $quantity);

            foreach ($calculated as $key => $row) {
                // Pular água e levain (não são ingredientes com estoque)
                if ($key === '_water' || $key === '_levain') {
                    continue;
                }

                $ingredient = $row['ingredient'] ?? null;
                if (!$ingredient || !isset($ingredient->id)) {
                    continue;
                }

                $weightUsed = (float) ($row['weight'] ?? 0);
                if ($weightUsed <= 0) {
                    continue;
                }

                // Buscar ingrediente sem ClientScope para garantir que encontre
                $ingredientModel = Ingredient::withoutGlobalScope(\App\Models\Scopes\ClientScope::class)
                    ->find($ingredient->id);

                if ($ingredientModel) {
                    // Abater estoque (não permitir negativo)
                    $newStock = max(0, (float) $ingredientModel->stock - $weightUsed);
                    $ingredientModel->update(['stock' => $newStock]);

                    \Log::info('Estoque abatido após produção', [
                        'ingredient_id' => $ingredientModel->id,
                        'ingredient_name' => $ingredientModel->name,
                        'weight_used' => $weightUsed,
                        'old_stock' => $ingredientModel->stock,
                        'new_stock' => $newStock,
                        'recipe_id' => $recipe->id,
                        'quantity' => $quantity,
                    ]);
                }
            }
        } catch (\Exception $e) {
            \Log::warning('Erro ao abater estoque de ingredientes', [
                'recipe_id' => $recipe->id,
                'quantity' => $quantity,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Exibir página de configurações de custos de produção
     */
    public function configuracoesCustos()
    {
        $clientId = currentClientId();
        $settings = \App\Models\Setting::getSettings($clientId);
        $productionSettings = [
            'sales_multiplier' => $settings->sales_multiplier ?? 3.5,
            'resale_multiplier' => $settings->resale_multiplier ?? 2.5,
            'fixed_cost' => $settings->fixed_cost ?? 0,
            'tax_percentage' => $settings->tax_percentage ?? 0,
            'card_fee_percentage' => $settings->card_fee_percentage ?? 6.0,
        ];

        // Converter para objeto para compatibilidade com a view
        $productionSettings = (object) $productionSettings;

        return view('dashboard.producao.configuracoes-custos', compact('productionSettings'));
    }

    /**
     * Salvar configurações de custos de produção
     */
    public function salvarConfiguracoesCustos(Request $request)
    {
        $validated = $request->validate([
            'sales_multiplier' => 'required|numeric|min:0',
            'resale_multiplier' => 'required|numeric|min:0',
            'fixed_cost' => 'nullable|numeric|min:0',
            'tax_percentage' => 'nullable|numeric|min:0|max:100',
            'card_fee_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        $clientId = currentClientId();
        $settings = \App\Models\Setting::getSettings($clientId);

        $settings->update([
            'sales_multiplier' => $validated['sales_multiplier'],
            'resale_multiplier' => $validated['resale_multiplier'],
            'fixed_cost' => $validated['fixed_cost'] ?? 0,
            'tax_percentage' => $validated['tax_percentage'] ?? 0,
            'card_fee_percentage' => $validated['card_fee_percentage'] ?? 6.0,
        ]);

        return back()->with('success', 'Configurações de custos de produção salvas com sucesso!');
    }

    /**
     * Consolida itens da mesma receita e divide em lotes de no máximo 3kg (3000g).
     */
    private function consolidateAndSplitItems($items)
    {
        if (!$items)
            return collect();
        if (!($items instanceof \Illuminate\Support\Collection)) {
            $items = collect($items);
        }
        if ($items->isEmpty())
            return collect();

        $limitGrams = 3000;
        $consolidatedRows = [];

        // Agrupar por receita e peso base unitário
        $groups = $items->groupBy(function ($item) {
            $isModel = $item instanceof \Illuminate\Database\Eloquent\Model;
            $recipeId = $isModel ? $item->recipe_id : ($item['recipe_id'] ?? 'no-recipe');

            // Tentar obter o peso unitário
            if ($isModel) {
                $w = (float) ($item->weight ?? ($item->recipe ? $item->recipe->total_weight : 0));
            } else {
                $w = (float) ($item['weight'] ?? 0);
                if ($w <= 0) {
                    $recipe = Recipe::withoutGlobalScope(\App\Models\Scopes\ClientScope::class)->find($recipeId);
                    if ($recipe)
                        $w = (float) $recipe->total_weight;
                }
            }

            return $recipeId . '-' . number_format($w, 2, '.', '');
        });

        foreach ($groups as $group) {
            $first = $group->first();
            $isModel = $first instanceof \Illuminate\Database\Eloquent\Model;
            $recipeId = $isModel ? $first->recipe_id : ($first['recipe_id'] ?? 0);

            if ($isModel) {
                $unitWeight = (float) ($first->weight ?? ($first->recipe ? $first->recipe->total_weight : 0));
            } else {
                $unitWeight = (float) ($first['weight'] ?? 0);
                if ($unitWeight <= 0) {
                    $recipe = Recipe::withoutGlobalScope(\App\Models\Scopes\ClientScope::class)->find($recipeId);
                    if ($recipe)
                        $unitWeight = (float) $recipe->total_weight;
                }
            }

            if ($unitWeight <= 0) {
                $qty = $group->sum(fn($i) => $isModel ? $i->quantity : ($i['quantity'] ?? 0));
                $consolidatedRows[] = $this->createBatchRow($group, $qty, $unitWeight);
                continue;
            }

            $totalQty = $group->sum(fn($i) => $isModel ? $i->quantity : ($i['quantity'] ?? 0));
            $maxPerBatch = floor($limitGrams / $unitWeight);

            if ($maxPerBatch <= 0) {
                // Caso o peso unitário seja > 3kg, cada unidade é um lote
                foreach ($group as $item) {
                    $q = $isModel ? $item->quantity : ($item['quantity'] ?? 0);
                    for ($i = 0; $i < $q; $i++) {
                        $consolidatedRows[] = $this->createBatchRow(collect([$item]), 1, $unitWeight);
                    }
                }
                continue;
            }

            // Dividir em lotes equilibrados
            $totalBatches = (int) ceil($totalQty / $maxPerBatch);
            $baseQtyPerBatch = (int) floor($totalQty / $totalBatches);
            $remainder = $totalQty % $totalBatches;

            $batchIndex = 1;
            for ($i = 0; $i < $totalBatches; $i++) {
                $currentBatchQty = $baseQtyPerBatch + ($i < $remainder ? 1 : 0);
                if ($currentBatchQty <= 0)
                    continue;

                $consolidatedRows[] = $this->createBatchRow($group, $currentBatchQty, $unitWeight, $totalBatches > 1 ? $batchIndex : null);
                $batchIndex++;
            }
        }

        return collect($consolidatedRows);
    }

    private function createBatchRow($group, $qty, $weight, $batchNumber = null)
    {
        $first = $group->first();
        $isModel = $first instanceof \Illuminate\Database\Eloquent\Model;

        // Um lote é considerado produzido apenas se TODOS os itens originais dele estiverem produzidos
        $isProduced = $isModel ? ($group->count() === 1 ? $first->is_produced : $group->every('is_produced', true)) : false;

        $ids = $isModel ? $group->pluck('id')->implode(',') : '';
        $observations = $isModel
            ? $group->pluck('observation')->filter()->unique()->implode(' | ')
            : $group->pluck('observation', null)->filter()->unique()->implode(' | '); // Fallback para array

        if (!$isModel && is_array($first)) {
            $observations = collect($group)->pluck('observation')->filter()->unique()->implode(' | ');
        }

        $recipeName = $isModel ? $first->recipe_name : ($first['recipe_name'] ?? '');
        if (empty($recipeName)) {
            $recipeId = $isModel ? $first->recipe_id : ($first['recipe_id'] ?? 0);
            $recipe = $isModel ? $first->recipe : Recipe::withoutGlobalScope(\App\Models\Scopes\ClientScope::class)->find($recipeId);
            if ($recipe)
                $recipeName = $recipe->name;
        }

        return (object) [
            'ids' => $ids,
            'recipe_id' => $isModel ? $first->recipe_id : ($first['recipe_id'] ?? null),
            'recipe' => $isModel ? $first->recipe : Recipe::withoutGlobalScope(\App\Models\Scopes\ClientScope::class)->find($first['recipe_id'] ?? 0),
            'original_recipe_name' => $recipeName,
            'recipe_name' => $batchNumber ? "{$recipeName} (Lote {$batchNumber})" : $recipeName,
            'quantity' => $qty,
            'weight' => $weight,
            'total_weight' => $qty * $weight,
            'is_produced' => $isProduced,
            'observation' => $observations,
            'batch_number' => $batchNumber,
            'is_batch' => $batchNumber !== null,
            'mark_for_print' => $isModel ? $group->every('mark_for_print', true) : true,
            'original_items' => $group
        ];
    }
}
