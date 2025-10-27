<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerDebt extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'order_id',
        'amount',
        'type',
        'status',
        'description',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    /**
     * Relacionamento com cliente
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Relacionamento com pedido
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}

