<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionNotification extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'subscription_id',
        'type',
        'days_before_expiry',
        'sent_at',
        'channel',
        'message',
        'read_at',
        'created_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    const TYPE_EXPIRING_SOON = 'expiring_soon';
    const TYPE_EXPIRED = 'expired';
    const TYPE_PAYMENT_FAILED = 'payment_failed';
    const TYPE_PAYMENT_RECEIVED = 'payment_received';
    const TYPE_PLAN_CHANGED = 'plan_changed';

    const CHANNEL_EMAIL = 'email';
    const CHANNEL_WHATSAPP = 'whatsapp';
    const CHANNEL_PUSH = 'push';
    const CHANNEL_IN_APP = 'in_app';

    /**
     * Assinatura
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * Scopes
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeInApp($query)
    {
        return $query->where('channel', self::CHANNEL_IN_APP);
    }

    /**
     * Helpers
     */
    public function isUnread(): bool
    {
        return $this->read_at === null;
    }

    public function markAsRead(): self
    {
        $this->update(['read_at' => now()]);
        return $this;
    }

    /**
     * Cria notificação de expiração
     */
    public static function createExpiringNotification(Subscription $subscription, int $daysBeforeExpiry): self
    {
        $daysText = $daysBeforeExpiry === 1 ? '1 dia' : "{$daysBeforeExpiry} dias";
        
        return static::create([
            'subscription_id' => $subscription->id,
            'type' => self::TYPE_EXPIRING_SOON,
            'days_before_expiry' => $daysBeforeExpiry,
            'channel' => self::CHANNEL_IN_APP,
            'message' => "Sua assinatura expira em {$daysText}. Renove agora para não perder acesso.",
            'created_at' => now(),
        ]);
    }

    /**
     * Type label
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            self::TYPE_EXPIRING_SOON => 'Expirando em breve',
            self::TYPE_EXPIRED => 'Expirada',
            self::TYPE_PAYMENT_FAILED => 'Pagamento falhou',
            self::TYPE_PAYMENT_RECEIVED => 'Pagamento recebido',
            self::TYPE_PLAN_CHANGED => 'Plano alterado',
            default => $this->type,
        };
    }

    /**
     * Type color
     */
    public function getTypeColorAttribute(): string
    {
        return match($this->type) {
            self::TYPE_EXPIRING_SOON => 'warning',
            self::TYPE_EXPIRED => 'destructive',
            self::TYPE_PAYMENT_FAILED => 'destructive',
            self::TYPE_PAYMENT_RECEIVED => 'success',
            self::TYPE_PLAN_CHANGED => 'primary',
            default => 'secondary',
        };
    }

    /**
     * Type icon
     */
    public function getTypeIconAttribute(): string
    {
        return match($this->type) {
            self::TYPE_EXPIRING_SOON => 'clock',
            self::TYPE_EXPIRED => 'alert-triangle',
            self::TYPE_PAYMENT_FAILED => 'x-circle',
            self::TYPE_PAYMENT_RECEIVED => 'check-circle',
            self::TYPE_PLAN_CHANGED => 'refresh-cw',
            default => 'bell',
        };
    }
}
