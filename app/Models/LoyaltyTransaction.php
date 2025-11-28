<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoyaltyTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'order_id',
        'type',
        'points',
        'value',
        'description',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'expires_at' => 'date',
        'is_active' => 'boolean',
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

    /**
     * Scope para transações ativas
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para transações por tipo
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope para transações não expiradas
     */
    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Accessor para tipo em português
     */
    public function getTypeLabelAttribute()
    {
        $types = [
            'earned' => 'Ganho',
            'redeemed' => 'Resgatado',
            'expired' => 'Expirado',
            'bonus' => 'Bônus',
            'adjustment' => 'Ajuste',
        ];

        return $types[$this->type] ?? $this->type;
    }

    /**
     * Accessor para valor formatado
     */
    public function getFormattedValueAttribute()
    {
        return $this->value ? 'R$ ' . number_format($this->value, 2, ',', '.') : null;
    }
}
