<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Scopes\ClientScope;
use Illuminate\Support\Str;

class Ingredient extends Model
{
    use HasFactory;

    protected $table = 'ingredients';
    
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $incrementing = true;
    public $timestamps = true;

    protected $fillable = [
        'client_id',
        'name',
        'slug',
        'weight',
        'percentage',
        'is_flour',
        'has_hydration',
        'hydration_percentage',
        'category',
        'package_weight',
        'cost',
        'cost_history',
        'unit',
        'stock',
        'min_stock',
        'is_active',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'percentage' => 'decimal:2',
        'is_flour' => 'boolean',
        'has_hydration' => 'boolean',
        'hydration_percentage' => 'decimal:2',
        'package_weight' => 'decimal:2',
        'cost' => 'decimal:2',
        'cost_history' => 'array',
        'stock' => 'decimal:2',
        'min_stock' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    protected static function booted()
    {
        static::addGlobalScope(new ClientScope());
        
        static::creating(function ($ingredient) {
            if (empty($ingredient->slug)) {
                $ingredient->slug = Str::slug($ingredient->name);
            }
        });
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_ingredient')
            ->withPivot('percentage')
            ->withTimestamps();
    }

    public function recipeIngredients()
    {
        return $this->hasMany(RecipeIngredient::class);
    }

    public function getCostPerGramAttribute()
    {
        if ($this->package_weight && $this->package_weight > 0 && $this->cost > 0) {
            return $this->cost / $this->package_weight;
        }
        return 0;
    }

    public function getStockStatusAttribute()
    {
        if ($this->stock <= 0) {
            return 'out_of_stock';
        }
        if ($this->min_stock > 0 && $this->stock <= $this->min_stock) {
            return 'low_stock';
        }
        return 'in_stock';
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
