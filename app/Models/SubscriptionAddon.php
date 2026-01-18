<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionAddon extends Model
{
    use HasFactory;

    protected $fillable = [
        'subscription_id',
        'addon_type',
        'description',
        'quantity',
        'unit_price',
        'total_price',
        'prorated_price',
        'started_at',
        'is_active',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'prorated_price' => 'decimal:2',
        'started_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    const TYPE_WHATSAPP_INSTANCE = 'whatsapp_instance';
    const TYPE_AI_CREDITS = 'ai_credits';
    const TYPE_STORAGE = 'storage';
    const TYPE_CUSTOM = 'custom';

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
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('addon_type', $type);
    }

    public function scopeWhatsAppInstances($query)
    {
        return $query->ofType(self::TYPE_WHATSAPP_INSTANCE);
    }

    /**
     * Helpers
     */
    public function isWhatsAppInstance(): bool
    {
        return $this->addon_type === self::TYPE_WHATSAPP_INSTANCE;
    }

    /**
     * Label do tipo
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->addon_type) {
            self::TYPE_WHATSAPP_INSTANCE => 'Instância WhatsApp',
            self::TYPE_AI_CREDITS => 'Créditos I.A.',
            self::TYPE_STORAGE => 'Armazenamento',
            self::TYPE_CUSTOM => 'Personalizado',
            default => $this->addon_type,
        };
    }

    /**
     * Formata preço
     */
    public function getFormattedUnitPriceAttribute(): string
    {
        return 'R$ ' . number_format($this->unit_price, 2, ',', '.');
    }

    public function getFormattedTotalPriceAttribute(): string
    {
        return 'R$ ' . number_format($this->total_price, 2, ',', '.');
    }

    public function getFormattedProratedPriceAttribute(): string
    {
        return 'R$ ' . number_format($this->prorated_price ?? 0, 2, ',', '.');
    }
}
