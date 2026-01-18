<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model Subscription - Assinaturas de clientes
 */
class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'plan_id',
        'status',
        'price',
        'started_at',
        'ends_at',
        'cancelled_at',
        'current_period_start',
        'current_period_end',
        'trial_ends_at',
        'payment_method',
        'external_id',
        'metadata',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'started_at' => 'datetime',
        'ends_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'current_period_start' => 'datetime',
        'current_period_end' => 'datetime',
        'trial_ends_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Status possíveis da assinatura
     */
    public const STATUS_ACTIVE = 'active';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_TRIAL = 'trial';
    public const STATUS_PAST_DUE = 'past_due';
    public const STATUS_SUSPENDED = 'suspended';

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    /**
     * Cliente desta assinatura
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Plano desta assinatura
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Addons (adicionais) desta assinatura
     */
    public function addons()
    {
        return $this->hasMany(SubscriptionAddon::class);
    }

    /**
     * Faturas desta assinatura
     */
    public function invoices()
    {
        return $this->hasMany(SubscriptionInvoice::class);
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    /**
     * Apenas assinaturas ativas
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Assinaturas em trial
     */
    public function scopeTrial($query)
    {
        return $query->where('status', self::STATUS_TRIAL);
    }

    /**
     * Assinaturas que expiram em X dias
     */
    public function scopeExpiringIn($query, int $days)
    {
        return $query->where('status', self::STATUS_ACTIVE)
                     ->whereBetween('current_period_end', [now(), now()->addDays($days)]);
    }

    /**
     * Assinaturas que expiram em breve (alias para expiringIn)
     */
    public function scopeExpiringSoon($query, int $days = 7)
    {
        return $query->where('status', self::STATUS_ACTIVE)
                     ->whereBetween('current_period_end', [now(), now()->addDays($days)]);
    }

    /**
     * Assinaturas vencidas
     */
    public function scopeExpired($query)
    {
        return $query->where('current_period_end', '<', now())
                     ->whereIn('status', [self::STATUS_ACTIVE, self::STATUS_TRIAL]);
    }

    // =========================================================================
    // ACCESSORS
    // =========================================================================

    /**
     * Verifica se assinatura está ativa
     */
    public function getIsActiveAttribute(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Verifica se está em trial
     */
    public function getIsTrialAttribute(): bool
    {
        return $this->status === self::STATUS_TRIAL;
    }

    /**
     * Dias até expirar
     */
    public function getDaysUntilExpirationAttribute(): ?int
    {
        if (!$this->current_period_end) {
            return null;
        }
        return max(0, now()->diffInDays($this->current_period_end, false));
    }

    /**
     * Alias para getDaysUntilExpirationAttribute
     * Método usado em views antigas
     */
    public function daysUntilExpiry(): ?int
    {
        return $this->days_until_expiration;
    }

    /**
     * Verifica se está expirando em breve (7 dias)
     */
    public function isExpiringSoon(int $days = 7): bool
    {
        if (!$this->current_period_end) {
            return false;
        }
        
        $daysUntil = $this->days_until_expiration;
        return $daysUntil !== null && $daysUntil <= $days && $daysUntil >= 0;
    }

    /**
     * Verifica se expirou
     */
    public function getIsExpiredAttribute(): bool
    {
        if (!$this->current_period_end) {
            return false;
        }
        return $this->current_period_end->isPast();
    }

    /**
     * Status traduzido
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => 'Ativa',
            self::STATUS_CANCELLED => 'Cancelada',
            self::STATUS_EXPIRED => 'Expirada',
            self::STATUS_TRIAL => 'Trial',
            self::STATUS_PAST_DUE => 'Pagamento Pendente',
            self::STATUS_SUSPENDED => 'Suspensa',
            default => ucfirst($this->status),
        };
    }

    // =========================================================================
    // METHODS
    // =========================================================================

    /**
     * Ativar assinatura
     */
    public function activate(): bool
    {
        $this->status = self::STATUS_ACTIVE;
        return $this->save();
    }

    /**
     * Cancelar assinatura
     */
    public function cancel(?string $reason = null): bool
    {
        $this->status = self::STATUS_CANCELLED;
        $this->cancelled_at = now();
        
        if ($reason) {
            $metadata = $this->metadata ?? [];
            $metadata['cancellation_reason'] = $reason;
            $this->metadata = $metadata;
        }
        
        return $this->save();
    }

    /**
     * Renovar período (30 dias por padrão)
     */
    public function renewPeriod(int $days = 30): bool
    {
        $this->current_period_start = now();
        $this->current_period_end = now()->addDays($days);
        $this->status = self::STATUS_ACTIVE;
        
        return $this->save();
    }

    /**
     * Calcular preço proporcional
     */
    public function calculateProratedPrice(float $amount): float
    {
        if (!$this->current_period_end || $this->current_period_end->isPast()) {
            return $amount;
        }

        $totalDays = now()->diffInDays($this->current_period_end);
        $remainingDays = max(0, now()->diffInDays($this->current_period_end, false));
        
        if ($totalDays <= 0) {
            return $amount;
        }

        return ($amount / 30) * $remainingDays;
    }

    /**
     * Alias para renewPeriod (compatibilidade)
     */
    public function renew(int $days = 30): bool
    {
        return $this->renewPeriod($days);
    }

    /**
     * Suspender assinatura
     */
    public function suspend(?string $reason = null): bool
    {
        $this->status = self::STATUS_SUSPENDED;
        
        if ($reason) {
            $metadata = $this->metadata ?? [];
            $metadata['suspension_reason'] = $reason;
            $this->metadata = $metadata;
        }
        
        return $this->save();
    }

    /**
     * Verificar se cliente pode usar o sistema
     */
    public function canOperate(): bool
    {
        // Ativa ou em trial e não expirado
        if (in_array($this->status, [self::STATUS_ACTIVE, self::STATUS_TRIAL])) {
            return !$this->is_expired;
        }
        
        return false;
    }
}
