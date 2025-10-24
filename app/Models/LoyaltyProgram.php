<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoyaltyProgram extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'points_per_real',
        'real_per_point',
        'minimum_points_to_redeem',
        'points_expiry_days',
        'is_active',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'points_per_real' => 'decimal:2',
        'real_per_point' => 'decimal:4',
        'is_active' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Relacionamento com transações de fidelidade
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(LoyaltyTransaction::class);
    }

    /**
     * Scope para programas ativos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where('start_date', '<=', now())
                    ->where(function ($q) {
                        $q->whereNull('end_date')
                          ->orWhere('end_date', '>=', now());
                    });
    }

    /**
     * Verifica se o programa está ativo
     */
    public function isActive(): bool
    {
        return $this->is_active && 
               $this->start_date <= now() && 
               ($this->end_date === null || $this->end_date >= now());
    }

    /**
     * Calcula pontos para um valor
     */
    public function calculatePoints(float $amount): int
    {
        return (int) floor($amount * $this->points_per_real);
    }

    /**
     * Calcula valor para pontos
     */
    public function calculateValue(int $points): float
    {
        return $points * $this->real_per_point;
    }
}
