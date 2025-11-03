<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryFeeRange extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_fee_id',
        'min_distance_km',
        'max_distance_km',
        'fee_amount',
        'fee_type',
        'delivery_time_minutes',
        'order',
    ];

    protected $casts = [
        'min_distance_km' => 'decimal:2',
        'max_distance_km' => 'decimal:2',
        'fee_amount' => 'decimal:2',
        'delivery_time_minutes' => 'integer',
        'order' => 'integer',
    ];

    /**
     * Relacionamento com DeliveryFee
     */
    public function deliveryFee(): BelongsTo
    {
        return $this->belongsTo(DeliveryFee::class);
    }

    /**
     * Verifica se uma distância está dentro desta faixa
     */
    public function matchesDistance(float $distance): bool
    {
        if ($distance < $this->min_distance_km) {
            return false;
        }

        if ($this->max_distance_km !== null && $distance > $this->max_distance_km) {
            return false;
        }

        return true;
    }

    /**
     * Calcula o valor da taxa para esta faixa
     */
    public function calculateFee(float $distance): float
    {
        if ($this->fee_type === 'per_km') {
            // Se for por km, calcular baseado na distância dentro da faixa
            $distanceInRange = min($distance, $this->max_distance_km ?? $distance) - $this->min_distance_km;
            return $this->fee_amount * max(0, $distanceInRange);
        }

        // Taxa fixa
        return (float)$this->fee_amount;
    }
}

