<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Scopes\ClientScope;

class Recipe extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'product_id',
        'variant_id',
        'name',
        'category',
        'total_weight',
        'hydration',
        'levain',
        'notes',
        'is_active',
        'use_milk_instead_of_water',
        'is_fermented',
        'is_bread',
        'include_notes_in_print',
        'uses_baker_percentage',
        'packaging_cost',
        'packaging_id',
        'final_price',
        'resale_price',
        'cost',
    ];

    protected $casts = [
        'total_weight' => 'decimal:2',
        'hydration' => 'decimal:2',
        'levain' => 'decimal:2',
        'is_active' => 'boolean',
        'use_milk_instead_of_water' => 'boolean',
        'is_fermented' => 'boolean',
        'is_bread' => 'boolean',
        'include_notes_in_print' => 'boolean',
        'uses_baker_percentage' => 'boolean',
        'packaging_cost' => 'decimal:2',
        'final_price' => 'decimal:2',
        'resale_price' => 'decimal:2',
        'cost' => 'decimal:2',
        'product_id' => 'integer',
        'variant_id' => 'integer',
    ];

    protected static function booted()
    {
        static::addGlobalScope(new ClientScope());

        static::saving(function ($recipe) {
            // Calcular custo total quando salvar
            $recipe->calculateCost();
        });
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function steps(): HasMany
    {
        return $this->hasMany(RecipeStep::class)->orderBy('sort_order');
    }

    public function productionRecords(): HasMany
    {
        return $this->hasMany(ProductionRecord::class);
    }

    public function productionListItems(): HasMany
    {
        return $this->hasMany(ProductionListItem::class);
    }

    public function packaging(): BelongsTo
    {
        return $this->belongsTo(Packaging::class)->withoutGlobalScope(\App\Models\Scopes\ClientScope::class);
    }

    /**
     * Soma total das porcentagens (baker's): ingredientes + hidratação + levain.
     * Todas as % são sobre a farinha (ou soma de farinhas).
     */
    public function getTotalBakerPercentage(): float
    {
        $steps = $this->relationLoaded('steps')
            ? $this->steps
            : $this->steps()->with('ingredients')->get();
        $sum = (float) ($this->hydration ?? 0) + (float) ($this->levain ?? 0);
        foreach ($steps as $step) {
            foreach ($step->ingredients as $ri) {
                if ($ri->percentage !== null) {
                    $sum += (float) $ri->percentage;
                }
            }
        }
        return $sum;
    }

    /**
     * Peso da farinha (ou soma de farinhas) para um total de massa dado.
     * total_weight = farinha * (totalBakerPct / 100) => farinha = total_weight * 100 / totalBakerPct.
     */
    public function getFlourWeight(?float $totalWeight = null): float
    {
        $total = $totalWeight ?? (float) ($this->total_weight ?? 0);
        if ($total <= 0) {
            return 0.0;
        }
        $pct = $this->getTotalBakerPercentage();
        if ($pct <= 0) {
            return 0.0;
        }
        return round($total * 100 / $pct, 2);
    }

    /**
     * Retorna pesos calculados por ingrediente (e opcionalmente água/levain).
     * totalWeight: peso total da massa (ex.: 700g). Se null, usa total_weight da receita.
     */
    public function calculateIngredientWeights(?float $totalWeight = null): array
    {
        $flour = $this->getFlourWeight($totalWeight);

        \Log::debug('calculateIngredientWeights iniciado', [
            'recipe_id' => $this->id,
            'recipe_name' => $this->name,
            'total_weight' => $totalWeight,
            'flour_weight' => $flour,
            'steps_loaded' => $this->relationLoaded('steps')
        ]);

        $steps = $this->relationLoaded('steps')
            ? $this->steps
            : $this->steps()->with([
                'ingredients' => function ($query) {
                    $query->with([
                        'ingredient' => function ($q) {
                            $q->withoutGlobalScope(\App\Models\Scopes\ClientScope::class);
                        }
                    ]);
                }
            ])->get();

        \Log::debug('Steps carregadas', [
            'steps_count' => $steps->count(),
            'steps_data' => $steps->map(function ($step) {
                return [
                    'id' => $step->id,
                    'name' => $step->name,
                    'ingredients_count' => $step->ingredients->count(),
                    'ingredients' => $step->ingredients->map(function ($ri) {
                        return [
                            'id' => $ri->id,
                            'ingredient_id' => $ri->ingredient_id,
                            'percentage' => $ri->percentage,
                            'has_ingredient' => $ri->ingredient !== null,
                            'ingredient_name' => $ri->ingredient->name ?? 'N/A'
                        ];
                    })->toArray()
                ];
            })->toArray()
        ]);

        $out = [];
        foreach ($steps as $step) {
            foreach ($step->ingredients as $ri) {
                if ($ri->percentage === null) {
                    \Log::debug('Pulando ingrediente sem porcentagem', [
                        'recipe_ingredient_id' => $ri->id,
                        'ingredient_id' => $ri->ingredient_id
                    ]);
                    continue;
                }

                // Garantir que o relacionamento ingredient está carregado (sem ClientScope)
                if (!$ri->relationLoaded('ingredient')) {
                    $ri->load([
                        'ingredient' => function ($query) {
                            $query->withoutGlobalScope(\App\Models\Scopes\ClientScope::class);
                        }
                    ]);
                }

                if (!$ri->ingredient) {
                    \Log::warning('Ingrediente não encontrado no relacionamento', [
                        'recipe_ingredient_id' => $ri->id,
                        'ingredient_id' => $ri->ingredient_id
                    ]);
                    continue;
                }

                $w = round($flour * (float) $ri->percentage / 100, 2);
                $id = $ri->ingredient_id;
                if (!isset($out[$id])) {
                    $out[$id] = ['ingredient' => $ri->ingredient, 'weight' => 0.0];
                }
                $out[$id]['weight'] += $w;

                \Log::debug('Ingrediente adicionado ao cálculo', [
                    'ingredient_id' => $id,
                    'ingredient_name' => $ri->ingredient->name,
                    'percentage' => $ri->percentage,
                    'weight' => $w,
                    'total_weight_for_id' => $out[$id]['weight']
                ]);
            }
        }
        $h = (float) ($this->hydration ?? 0);
        $l = (float) ($this->levain ?? 0);
        if ($h > 0) {
            $out['_water'] = ['ingredient' => null, 'weight' => round($flour * $h / 100, 2), 'label' => 'Água (hidratação)'];
            \Log::debug('Água adicionada', ['weight' => $out['_water']['weight']]);
        }
        if ($l > 0) {
            $out['_levain'] = ['ingredient' => null, 'weight' => round($flour * $l / 100, 2), 'label' => 'Levain'];
            \Log::debug('Levain adicionado', ['weight' => $out['_levain']['weight']]);
        }

        \Log::debug('calculateIngredientWeights finalizado', [
            'total_ingredients' => count($out),
            'ingredient_ids' => array_keys(array_filter($out, function ($key) {
                return strpos($key, '_') !== 0;
            }, ARRAY_FILTER_USE_KEY))
        ]);

        return $out;
    }

    public function calculateCost()
    {
        // Custo dos ingredientes
        $totalCost = 0;
        $flour = $this->getFlourWeight();
        $steps = $this->relationLoaded('steps')
            ? $this->steps
            : $this->steps()->with([
                'ingredients' => function ($query) {
                    $query->with([
                        'ingredient' => function ($q) {
                            $q->withoutGlobalScope(\App\Models\Scopes\ClientScope::class);
                        }
                    ]);
                }
            ])->get();

        foreach ($steps as $step) {
            foreach ($step->ingredients as $ri) {
                if (!$ri->ingredient) {
                    continue;
                }
                $ingredientCost = (float) ($ri->ingredient->cost_per_gram ?? 0);
                $weight = ($ri->weight ?? 0) > 0
                    ? (float) $ri->weight
                    : ($ri->percentage && $flour > 0
                        ? round($flour * (float) $ri->percentage / 100, 2)
                        : 0);
                $totalCost += $ingredientCost * $weight;
            }
        }

        // Custo da embalagem (prioridade: packaging_id > packaging_cost)
        $packagingCost = 0;
        if ($this->packaging_id && $this->packaging) {
            $packagingCost = (float) $this->packaging->cost;
        } elseif ($this->packaging_cost) {
            $packagingCost = (float) $this->packaging_cost;
        }

        $totalCost += $packagingCost;

        $this->cost = round($totalCost, 2);
        return $this->cost;
    }

    /**
     * Calcula o preço de venda sugerido usando multiplicador
     */
    public function calculateSuggestedSalePrice(?float $multiplier = null): float
    {
        $cost = $this->calculateCost();
        $settings = \App\Models\Setting::getSettings($this->client_id);
        $multiplier = $multiplier ?? (float) ($settings->sales_multiplier ?? 3.5);

        return round($cost * $multiplier, 2);
    }

    /**
     * Calcula o preço de revenda sugerido usando multiplicador
     */
    public function calculateSuggestedResalePrice(?float $multiplier = null): float
    {
        $cost = $this->calculateCost();
        $settings = \App\Models\Setting::getSettings($this->client_id);
        $multiplier = $multiplier ?? (float) ($settings->resale_multiplier ?? 2.5);

        return round($cost * $multiplier, 2);
    }

    /**
     * Calcula o preço final considerando custos fixos, impostos e taxas
     */
    public function calculateFinalPrice(?float $salePrice = null): array
    {
        $ingredientCost = $this->calculateCost();
        $salePrice = $salePrice ?? $this->final_price ?? $this->calculateSuggestedSalePrice();

        $settings = \App\Models\Setting::getSettings($this->client_id);
        $fixedCost = (float) ($settings->fixed_cost ?? 0);
        $taxPercentage = (float) ($settings->tax_percentage ?? 0);
        $cardFeePercentage = (float) ($settings->card_fee_percentage ?? 6.0);

        // Calcular custos fixos proporcionalmente (30% do custo de ingredientes é um padrão comum)
        $fixedCostProportion = $ingredientCost * 0.30;

        // Custo total = ingredientes + embalagem + custos fixos
        $totalCost = $ingredientCost + $fixedCostProportion;

        // Calcular margem
        $margin = $salePrice > 0 ? (($salePrice - $totalCost) / $salePrice) * 100 : 0;

        // Calcular com taxa de cartão
        $priceWithCardFee = $salePrice * (1 + ($cardFeePercentage / 100));

        return [
            'ingredient_cost' => $ingredientCost,
            'fixed_cost' => $fixedCostProportion,
            'total_cost' => $totalCost,
            'sale_price' => $salePrice,
            'price_with_card_fee' => $priceWithCardFee,
            'margin' => round($margin, 2),
            'margin_percentage' => round($margin, 1),
        ];
    }

    public function getTotalCostAttribute()
    {
        return $this->calculateCost();
    }

    public function getMarginAttribute()
    {
        if ($this->final_price && $this->cost > 0) {
            return (($this->final_price - $this->cost) / $this->final_price) * 100;
        }
        return 0;
    }
}
