<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryFee extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'base_fee',
        'fee_per_km',
        'minimum_order_value',
        'free_delivery_threshold',
        'max_distance_km',
        'is_active',
        'delivery_time_minutes',
    ];

    protected $casts = [
        'base_fee' => 'decimal:2',
        'fee_per_km' => 'decimal:2',
        'minimum_order_value' => 'decimal:2',
        'free_delivery_threshold' => 'decimal:2',
        'max_distance_km' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Scope para taxas ativas
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Calcula taxa de entrega
     */
    public function calculateFee(float $distance, float $orderValue): float
    {
        // Verifica se a entrega é gratuita
        if ($this->free_delivery_threshold && $orderValue >= $this->free_delivery_threshold) {
            return 0;
        }

        // Verifica valor mínimo do pedido
        if ($orderValue < $this->minimum_order_value) {
            return $this->base_fee;
        }

        // Verifica distância máxima
        if ($this->max_distance_km && $distance > $this->max_distance_km) {
            return $this->base_fee + ($this->fee_per_km * $this->max_distance_km);
        }

        return $this->base_fee + ($this->fee_per_km * $distance);
    }

    /**
     * Verifica se a entrega é gratuita
     */
    public function isFreeDelivery(float $orderValue): bool
    {
        return $this->free_delivery_threshold && $orderValue >= $this->free_delivery_threshold;
    }

    /**
     * Accessor para taxa base formatada
     */
    public function getFormattedBaseFeeAttribute()
    {
        return 'R$ ' . number_format($this->base_fee, 2, ',', '.');
    }

    /**
     * Accessor para taxa por km formatada
     */
    public function getFormattedFeePerKmAttribute()
    {
        return 'R$ ' . number_format($this->fee_per_km, 2, ',', '.');
    }
}
