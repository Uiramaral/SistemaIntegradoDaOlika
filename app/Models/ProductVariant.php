<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    use HasFactory;

    protected $table = 'product_variants';

    protected $fillable = [
        'product_id',
        'name',            // ex: 500g, 1kg, Chocolate, Frutas
        'price',
        'sku',
        'stock',
        'is_active',
        'sort_order',
        'weight_grams',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'stock' => 'integer',
        'weight_grams' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Scope para variantes ativas
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}


