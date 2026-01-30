<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecipeIngredient extends Model
{
    use HasFactory;

    protected $fillable = [
        'recipe_step_id',
        'ingredient_id',
        'type',
        'percentage',
        'weight',
        'sort_order',
    ];

    protected $primaryKey = 'id';
    public $incrementing = true;

    protected $casts = [
        'percentage' => 'decimal:2',
        'weight' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    public function recipeStep(): BelongsTo
    {
        return $this->belongsTo(RecipeStep::class);
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class)->withoutGlobalScope(\App\Models\Scopes\ClientScope::class);
    }

    /**
     * Peso calculado: % sobre farinha (baker's).
     * Farinha = total_weight * 100 / (sum(ingredient %) + hidratação + levain).
     */
    public function getCalculatedWeightAttribute()
    {
        if (($this->weight ?? 0) > 0) {
            return (float) $this->weight;
        }
        $this->loadMissing('recipeStep.recipe');
        $recipe = $this->recipeStep->recipe ?? null;
        if (!$recipe || $this->percentage === null) {
            return 0;
        }
        $flour = $recipe->getFlourWeight();
        if ($flour <= 0) {
            return 0;
        }
        return round($flour * (float) $this->percentage / 100, 2);
    }

    public function getCostAttribute()
    {
        if ($this->ingredient) {
            $costPerGram = $this->ingredient->cost_per_gram ?? 0;
            $weight = $this->calculated_weight;
            return $costPerGram * $weight;
        }
        return 0;
    }
}
