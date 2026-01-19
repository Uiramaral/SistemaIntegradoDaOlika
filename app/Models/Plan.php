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
        'active', // Corrigir: usar 'active' ao invés de 'is_active'
        'is_featured',
        'sort_order',
        'trial_days',
        'has_whatsapp',
        'has_ai',
        'max_products',
        'max_orders_per_month',
        'max_users',
        'max_whatsapp_instances',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'features' => 'array',
        'limits' => 'array',
        'active' => 'boolean', // Corrigir
        'is_featured' => 'boolean',
        'trial_days' => 'integer',
        'sort_order' => 'integer',
        'has_whatsapp' => 'boolean',
        'has_ai' => 'boolean',
        'max_products' => 'integer',
        'max_orders_per_month' => 'integer',
        'max_users' => 'integer',
        'max_whatsapp_instances' => 'integer',
    ];

    /**
     * Valores padrão para novos planos
     */
    protected $attributes = [
        'billing_cycle' => 'monthly',
    ];

    /**
     * Boot do modelo - garantir billing_cycle sempre como 'monthly'
     */
    protected static function boot()
    {
        parent::boot();

        // Antes de criar ou atualizar, garantir billing_cycle = 'monthly'
        static::saving(function ($plan) {
            if (empty($plan->billing_cycle)) {
                $plan->billing_cycle = 'monthly';
            }
            // Forçar sempre como monthly (não há planos anuais ainda)
            $plan->billing_cycle = 'monthly';
        });
    }

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
        return $query->where('active', true); // Corrigido: usar 'active' ao invés de 'is_active'
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
