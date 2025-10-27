<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'custom_name',
        'quantity',
        'unit_price',
        'total_price',
        'special_instructions',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    /**
     * Relacionamento com pedido
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Relacionamento com produto
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Accessor para preço unitário formatado
     */
    public function getFormattedUnitPriceAttribute()
    {
        return 'R$ ' . number_format($this->unit_price, 2, ',', '.');
    }

    /**
     * Accessor para preço total formatado
     */
    public function getFormattedTotalPriceAttribute()
    {
        return 'R$ ' . number_format($this->total_price, 2, ',', '.');
    }
}
