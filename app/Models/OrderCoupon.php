<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderCoupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'code',
        'type',
        'value',
        'meta',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'meta' => 'array',
    ];

    /**
     * Relacionamento com pedido
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Accessor para valor formatado
     */
    public function getFormattedValueAttribute()
    {
        if ($this->type === 'percent') {
            return $this->value . '%';
        }

        return 'R$ ' . number_format($this->value, 2, ',', '.');
    }
}
