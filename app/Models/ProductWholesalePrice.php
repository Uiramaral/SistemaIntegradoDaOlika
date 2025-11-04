<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductWholesalePrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'variant_id',
        'wholesale_price',
        'min_quantity',
        'is_active',
    ];

    protected $casts = [
        'wholesale_price' => 'decimal:2',
        'min_quantity' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Relacionamento com produto
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Relacionamento com variante
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    /**
     * Scope para preços ativos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Buscar preço de revenda para um produto/variante
     */
    public static function getWholesalePrice(int $productId, ?int $variantId = null, int $quantity = 1): ?float
    {
        $query = self::where('product_id', $productId)
            ->where('is_active', true)
            ->where('min_quantity', '<=', $quantity);

        if ($variantId) {
            $query->where(function($q) use ($variantId) {
                $q->where('variant_id', $variantId)
                  ->orWhereNull('variant_id');
            });
        } else {
            $query->whereNull('variant_id');
        }

        $price = $query->orderBy('min_quantity', 'desc')
            ->first();

        return $price ? (float)$price->wholesale_price : null;
    }
}

