<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionListItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'production_list_id',
        'recipe_id',
        'order_item_id',
        'recipe_name',
        'quantity',
        'weight',
        'is_produced',
        'produced_at',
        'observation',
        'mark_for_print',
        'sort_order',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'weight' => 'decimal:2',
        'is_produced' => 'boolean',
        'produced_at' => 'datetime',
        'mark_for_print' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function booted()
    {
        static::updating(function ($item) {
            if ($item->is_produced && !$item->produced_at) {
                $item->produced_at = now();
            }
            if (!$item->is_produced && $item->produced_at) {
                $item->produced_at = null;
            }
        });
    }

    public function productionList(): BelongsTo
    {
        return $this->belongsTo(ProductionList::class);
    }

    public function list(): BelongsTo
    {
        return $this->productionList();
    }

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(\App\Models\OrderItem::class);
    }

    public function getTotalWeightAttribute()
    {
        return $this->quantity * $this->weight;
    }
}
