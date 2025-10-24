<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Referral extends Model
{
    use HasFactory;

    protected $fillable = [
        'referrer_id',
        'referred_id',
        'code',
        'status',
        'reward_amount',
        'reward_type',
        'expires_at',
        'used_at',
    ];

    protected $casts = [
        'reward_amount' => 'decimal:2',
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    /**
     * Relacionamento com quem indicou
     */
    public function referrer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'referrer_id');
    }

    /**
     * Relacionamento com quem foi indicado
     */
    public function referred(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'referred_id');
    }

    /**
     * Scope para indicações ativas
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope para indicações usadas
     */
    public function scopeUsed($query)
    {
        return $query->where('status', 'used');
    }

    /**
     * Scope para indicações expiradas
     */
    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    /**
     * Scope para indicações não expiradas
     */
    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Gera código único de indicação
     */
    public static function generateCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (static::where('code', $code)->exists());

        return $code;
    }

    /**
     * Cria nova indicação
     */
    public static function createReferral(int $referrerId, int $referredId, int $expiryDays = 30): self
    {
        return static::create([
            'referrer_id' => $referrerId,
            'referred_id' => $referredId,
            'code' => static::generateCode(),
            'expires_at' => now()->addDays($expiryDays),
        ]);
    }

    /**
     * Marca indicação como usada
     */
    public function markAsUsed(): void
    {
        $this->update([
            'status' => 'used',
            'used_at' => now(),
        ]);
    }

    /**
     * Verifica se a indicação é válida
     */
    public function isValid(): bool
    {
        return $this->status === 'active' && 
               ($this->expires_at === null || $this->expires_at > now());
    }

    /**
     * Accessor para status em português
     */
    public function getStatusLabelAttribute()
    {
        $statuses = [
            'active' => 'Ativa',
            'used' => 'Usada',
            'expired' => 'Expirada',
        ];

        return $statuses[$this->status] ?? $this->status;
    }

    /**
     * Accessor para tipo de recompensa em português
     */
    public function getRewardTypeLabelAttribute()
    {
        $types = [
            'points' => 'Pontos',
            'cashback' => 'Cashback',
            'discount' => 'Desconto',
        ];

        return $types[$this->reward_type] ?? $this->reward_type;
    }
}
