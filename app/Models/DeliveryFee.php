<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'use_dynamic_ranges', // Nova flag para usar faixas dinâmicas
    ];

    protected $casts = [
        'base_fee' => 'decimal:2',
        'fee_per_km' => 'decimal:2',
        'minimum_order_value' => 'decimal:2',
        'free_delivery_threshold' => 'decimal:2',
        'max_distance_km' => 'decimal:2',
        'is_active' => 'boolean',
        'use_dynamic_ranges' => 'boolean',
    ];

    /**
     * Relacionamento com faixas dinâmicas
     */
    public function ranges(): HasMany
    {
        return $this->hasMany(DeliveryFeeRange::class)->orderBy('min_distance_km');
    }

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

        // Se usar faixas dinâmicas, calcular baseado nas faixas
        if ($this->use_dynamic_ranges && $this->ranges()->count() > 0) {
            return $this->calculateFeeFromRanges($distance);
        }

        // Método antigo: fórmula baseada
        // Verifica distância máxima
        if ($this->max_distance_km && $distance > $this->max_distance_km) {
            return $this->base_fee + ($this->fee_per_km * $this->max_distance_km);
        }

        return $this->base_fee + ($this->fee_per_km * $distance);
    }

    /**
     * Calcula taxa usando faixas dinâmicas
     */
    private function calculateFeeFromRanges(float $distance): float
    {
        $ranges = $this->ranges()->orderBy('min_distance_km')->get();

        foreach ($ranges as $range) {
            if ($range->matchesDistance($distance)) {
                return $range->calculateFee($distance);
            }
        }

        // Se não encontrou faixa, usar a última (maior distância) ou retornar 0
        $lastRange = $ranges->last();
        if ($lastRange && $distance >= $lastRange->min_distance_km) {
            return $lastRange->calculateFee($distance);
        }

        // Fallback: retornar 0 se não houver faixa correspondente
        return 0.00;
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
