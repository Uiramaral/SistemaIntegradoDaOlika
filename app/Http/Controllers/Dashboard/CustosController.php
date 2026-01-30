<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Recipe;
use App\Models\Packaging;
use App\Models\Setting;
use Illuminate\Http\Request;

class CustosController extends Controller
{
    public function index(Request $request)
    {
        $clientId = currentClientId();
        $settings = Setting::getSettings($clientId);
        
        // Buscar receitas com custos calculados
        $recipes = Recipe::withoutGlobalScope(\App\Models\Scopes\ClientScope::class)
            ->where(function($q) use ($clientId) {
                $q->where('client_id', $clientId)->orWhereNull('client_id');
            })
            ->where('is_active', true)
            ->with('packaging')
            ->get()
            ->map(function($recipe) {
                $recipe->calculateCost();
                return [
                    'id' => $recipe->id,
                    'name' => $recipe->name,
                    'cost' => $recipe->cost,
                    'final_price' => $recipe->final_price,
                    'resale_price' => $recipe->resale_price,
                    'margin' => $recipe->final_price > 0 ? (($recipe->final_price - $recipe->cost) / $recipe->final_price) * 100 : 0,
                ];
            });
        
        // Estatísticas
        $totalMonthlyCost = $recipes->sum('cost') * 30; // Estimativa mensal
        $averageMargin = $recipes->avg('margin') ?? 0;
        $averageCost = $recipes->avg('cost') ?? 0;
        
        return view('dashboard.producao.custos', compact(
            'recipes',
            'totalMonthlyCost',
            'averageMargin',
            'averageCost',
            'settings'
        ));
    }

    public function calculate(Request $request)
    {
        \Log::info('CustosController::calculate chamado', [
            'request_data' => $request->all(),
            'method' => $request->method(),
            'content_type' => $request->header('Content-Type')
        ]);
        
        try {
            $validated = $request->validate([
                'recipe_id' => 'required|exists:recipes,id',
                'weight' => 'nullable|numeric|min:0',
                'packaging_id' => 'nullable|exists:packagings,id',
                'sales_multiplier' => 'nullable|numeric|min:0',
                'resale_multiplier' => 'nullable|numeric|min:0',
            ]);
            
            \Log::info('Validação passou', ['validated' => $validated]);

            $clientId = currentClientId();
            $recipe = Recipe::withoutGlobalScope(\App\Models\Scopes\ClientScope::class)
                ->where(function($q) use ($clientId) {
                    $q->where('client_id', $clientId)->orWhereNull('client_id');
                })
                ->with(['steps.ingredients.ingredient', 'packaging'])
                ->findOrFail($validated['recipe_id']);

        // Se peso foi informado, usar; senão usar o peso padrão da receita
        $weight = $validated['weight'] ?? $recipe->total_weight;
        
        // Se embalagem foi informada, atualizar temporariamente
        if ($validated['packaging_id'] ?? null) {
            $packaging = Packaging::withoutGlobalScope(\App\Models\Scopes\ClientScope::class)
                ->find($validated['packaging_id']);
            if ($packaging) {
                $recipe->packaging_id = $packaging->id;
                $recipe->setRelation('packaging', $packaging);
            }
        }

        // Calcular custo dos ingredientes para o peso especificado
        // Usar calculateIngredientWeights para obter os pesos corretos
        $calculated = $recipe->calculateIngredientWeights($weight);
        
        \Log::debug('Cálculo de custos iniciado', [
            'recipe_id' => $recipe->id,
            'recipe_name' => $recipe->name,
            'weight' => $weight,
            'calculated_keys' => array_keys($calculated),
            'calculated_count' => count($calculated)
        ]);
        
        $ingredientCost = 0;
        $ingredientDetails = [];
        
        foreach ($calculated as $key => $row) {
            // Pular chaves virtuais (água e levain não têm custo direto)
            if (strpos($key, '_') === 0) {
                continue;
            }
            
            $ing = $row['ingredient'] ?? null;
            if (!$ing) {
                \Log::debug('Ingrediente não encontrado', ['key' => $key]);
                continue;
            }
            
            $ingredientWeight = (float) ($row['weight'] ?? 0);
            
            // Calcular custo por grama: se tem package_weight e cost, calcular; senão usar 0
            $costPerGram = 0;
            if ($ing->package_weight && $ing->package_weight > 0 && $ing->cost && $ing->cost > 0) {
                $costPerGram = (float) $ing->cost / (float) $ing->package_weight;
            }
            
            $ingredientItemCost = $ingredientWeight * $costPerGram;
            $ingredientCost += $ingredientItemCost;
            
            $ingredientDetails[] = [
                'name' => $ing->name,
                'weight' => $ingredientWeight,
                'cost_per_gram' => $costPerGram,
                'item_cost' => $ingredientItemCost
            ];
        }
        
        \Log::debug('Custo de ingredientes calculado', [
            'total_ingredient_cost' => $ingredientCost,
            'ingredients_count' => count($ingredientDetails),
            'ingredients' => $ingredientDetails
        ]);
        
        // Custo da embalagem
        $packagingCost = 0;
        if ($recipe->packaging) {
            $packagingCost = (float) $recipe->packaging->cost;
        } elseif ($recipe->packaging_cost) {
            $packagingCost = (float) $recipe->packaging_cost;
        }
        
        $totalIngredientCost = $ingredientCost + $packagingCost;

        // Obter multiplicadores
        $settings = Setting::getSettings($clientId);
        $salesMultiplier = $validated['sales_multiplier'] ?? (float) ($settings->sales_multiplier ?? 3.5);
        $resaleMultiplier = $validated['resale_multiplier'] ?? (float) ($settings->resale_multiplier ?? 2.5);
        $fixedCost = (float) ($settings->fixed_cost ?? 0);
        $taxPercentage = (float) ($settings->tax_percentage ?? 0);
        $cardFeePercentage = (float) ($settings->card_fee_percentage ?? 6.0);

        // Calcular custos fixos (30% do custo de ingredientes)
        $fixedCostProportion = $totalIngredientCost * 0.30;
        $totalCost = $totalIngredientCost + $fixedCostProportion;

        // Calcular preços sugeridos
        $suggestedSalePrice = $totalCost * $salesMultiplier;
        $suggestedResalePrice = $totalCost * $resaleMultiplier;

        // Calcular margem
        $margin = $suggestedSalePrice > 0 ? (($suggestedSalePrice - $totalCost) / $suggestedSalePrice) * 100 : 0;

        // Calcular com taxa de cartão
        $priceWithCardFee = $suggestedSalePrice * (1 + ($cardFeePercentage / 100));

        return response()->json([
            'ingredient_cost' => round($ingredientCost, 2),
            'packaging_cost' => round($packagingCost, 2),
            'total_ingredient_cost' => round($totalIngredientCost, 2),
            'fixed_cost' => round($fixedCostProportion, 2),
            'total_cost' => round($totalCost, 2),
            'suggested_sale_price' => round($suggestedSalePrice, 2),
            'suggested_resale_price' => round($suggestedResalePrice, 2),
            'price_with_card_fee' => round($priceWithCardFee, 2),
            'margin' => round($margin, 2),
            'margin_percentage' => round($margin, 1),
            'weight' => $weight,
        ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Dados inválidos',
                'messages' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Erro ao calcular custos', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            
            return response()->json([
                'error' => 'Erro ao calcular custos: ' . $e->getMessage()
            ], 500);
        }
    }
}
