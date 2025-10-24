<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderDeliveryFee extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'delivery_fee_id',
        'calculated_fee',
        'final_fee',
        'distance_km',
        'order_value',
        'is_free_delivery',
        'is_manual_adjustment',
        'adjustment_reason',
        'adjusted_by',
    ];

    protected $casts = [
        'calculated_fee' => 'decimal:2',
        'final_fee' => 'decimal:2',
        'distance_km' => 'decimal:2',
        'order_value' => 'decimal:2',
        'is_free_delivery' => 'boolean',
        'is_manual_adjustment' => 'boolean',
    ];

    /**
     * Relacionamento com pedido
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Relacionamento com taxa de entrega
     */
    public function deliveryFee(): BelongsTo
    {
        return $this->belongsTo(DeliveryFee::class);
    }

    /**
     * Scope para taxas calculadas automaticamente
     */
    public function scopeCalculated($query)
    {
        return $query->where('is_manual_adjustment', false);
    }

    /**
     * Scope para taxas ajustadas manualmente
     */
    public function scopeManual($query)
    {
        return $query->where('is_manual_adjustment', true);
    }

    /**
     * Scope para entregas gratuitas
     */
    public function scopeFree($query)
    {
        return $query->where('is_free_delivery', true);
    }

    /**
     * Calcula a diferença entre taxa calculada e final
     */
    public function getDifferenceAttribute()
    {
        return $this->final_fee - $this->calculated_fee;
    }

    /**
     * Verifica se houve ajuste manual
     */
    public function hasManualAdjustment(): bool
    {
        return $this->is_manual_adjustment;
    }

    /**
     * Aplica ajuste manual na taxa
     */
    public function applyManualAdjustment(float $newFee, string $reason = null, string $adjustedBy = 'admin'): void
    {
        $this->update([
            'final_fee' => $newFee,
            'is_manual_adjustment' => true,
            'adjustment_reason' => $reason,
            'adjusted_by' => $adjustedBy,
        ]);
    }

    /**
     * Reverte para taxa calculada automaticamente
     */
    public function revertToCalculated(): void
    {
        $this->update([
            'final_fee' => $this->calculated_fee,
            'is_manual_adjustment' => false,
            'adjustment_reason' => null,
            'adjusted_by' => null,
        ]);
    }

    /**
     * Accessor para taxa formatada
     */
    public function getFormattedFinalFeeAttribute()
    {
        return 'R$ ' . number_format($this->final_fee, 2, ',', '.');
    }

    /**
     * Accessor para taxa calculada formatada
     */
    public function getFormattedCalculatedFeeAttribute()
    {
        return 'R$ ' . number_format($this->calculated_fee, 2, ',', '.');
    }

    /**
     * Accessor para diferença formatada
     */
    public function getFormattedDifferenceAttribute()
    {
        $difference = $this->difference;
        $formatted = 'R$ ' . number_format(abs($difference), 2, ',', '.');
        
        if ($difference > 0) {
            return "+{$formatted}";
        } elseif ($difference < 0) {
            return "-{$formatted}";
        }
        
        return $formatted;
    }
}
