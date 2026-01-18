<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model Plan - Planos de assinatura SaaS
 */
class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'billing_cycle',
        'features',
        'limits',
        'is_active',
        'is_featured',
        'sort_order',
        'trial_days',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'features' => 'array',
        'limits' => 'array',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'trial_days' => 'integer',
        'sort_order' => 'integer',
    ];

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    /**
     * Assinaturas deste plano
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Clientes que usam este plano
     */
    public function clients(): HasMany
    {
        return $this->hasManyThrough(Client::class, Subscription::class);
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    /**
     * Apenas planos ativos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Planos em destaque
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Ordenado por sort_order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    // =========================================================================
    // ACCESSORS
    // =========================================================================

    /**
     * Preço formatado em BRL
     */
    public function getFormattedPriceAttribute(): string
    {
        return 'R$ ' . number_format($this->price, 2, ',', '.');
    }

    /**
     * Lista de features como array
     */
    public function getFeaturesListAttribute(): array
    {
        if (is_array($this->features)) {
            return $this->features;
        }
        return json_decode($this->features ?? '[]', true) ?: [];
    }

    // =========================================================================
    // METHODS
    // =========================================================================

    /**
     * Buscar plano pelo slug
     */
    public static function findBySlug(string $slug): ?self
    {
        return static::where('slug', $slug)->first();
    }

    /**
     * Verificar se plano tem determinado limite
     */
    public function hasLimit(string $key): bool
    {
        $limits = is_array($this->limits) ? $this->limits : json_decode($this->limits ?? '{}', true);
        return isset($limits[$key]);
    }

    /**
     * Obter valor de um limite
     */
    public function getLimit(string $key, $default = null)
    {
        $limits = is_array($this->limits) ? $this->limits : json_decode($this->limits ?? '{}', true);
        return $limits[$key] ?? $default;
    }
}
