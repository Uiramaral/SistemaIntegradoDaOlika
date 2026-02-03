<?php

namespace App\Models;

use App\Models\Traits\BelongsToClient;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory, BelongsToClient;

    protected $fillable = [
        'client_id',
        'business_name',
        'business_description',
        'business_phone',
        'business_email',
        'business_address',
        'business_full_address',
        'business_latitude',
        'business_longitude',
        'is_open',
        'primary_color',
        'logo_url',
        'header_image_url',
        'min_delivery_value',
        'free_delivery_threshold',
        'delivery_fee_per_km',
        'max_delivery_distance',
        'mercadopago_access_token',
        'mercadopago_public_key',
        'mercadopago_env',
        'google_maps_api_key',
        'openai_api_key',
        'whatsapp_api_url',
        'whatsapp_api_key',
        'loyalty_enabled',
        'loyalty_points_per_real',
        'cashback_percentage',
        'order_cutoff_time',
        'advance_order_days',
        // Campos de personalização de tema (SaaS)
        'theme_primary_color',
        'theme_secondary_color',
        'theme_accent_color',
        'theme_background_color',
        'theme_text_color',
        'theme_border_color',
        'theme_logo_url',
        'theme_favicon_url',
        'theme_brand_name',
        'theme_font_family',
        'theme_border_radius',
        'theme_shadow_style',
        'sales_multiplier',
        'resale_multiplier',
        'fixed_cost',
        'tax_percentage',
        'card_fee_percentage',
    ];

    protected $casts = [
        'is_open' => 'boolean',
        'business_latitude' => 'decimal:8',
        'business_longitude' => 'decimal:8',
        'min_delivery_value' => 'decimal:2',
        'free_delivery_threshold' => 'decimal:2',
        'delivery_fee_per_km' => 'decimal:2',
        'max_delivery_distance' => 'decimal:2',
        'loyalty_enabled' => 'boolean',
        'loyalty_points_per_real' => 'decimal:2',
        'cashback_percentage' => 'decimal:2',
        'order_cutoff_time' => 'datetime:H:i',
        'sales_multiplier' => 'decimal:2',
        'resale_multiplier' => 'decimal:2',
        'fixed_cost' => 'decimal:2',
        'tax_percentage' => 'decimal:2',
        'card_fee_percentage' => 'decimal:2',
    ];

    /**
     * Busca configurações do sistema (respeitando multi-tenant)
     * 
     * @param int|null $clientId ID do cliente (opcional, usa o atual se não informado)
     * @return static
     */
    public static function getSettings(?int $clientId = null): static
    {
        // Se não informou client_id, usar o do contexto atual (helper centralizado)
        if ($clientId === null) {
            $clientId = currentClientId();
        }

        // Buscar settings do cliente específico
        if ($clientId) {
            $settings = static::where('client_id', $clientId)->first();
            if ($settings) {
                return $settings;
            }
        }

        return new static(['client_id' => $clientId]);
    }

    /**
     * Busca configurações de qualquer cliente (para super admin)
     */
    public static function getSettingsFor(int $clientId): static
    {
        return static::withoutGlobalScope(\App\Models\Scopes\ClientScope::class)
            ->where('client_id', $clientId)
            ->first() ?? new static(['client_id' => $clientId]);
    }

    /**
     * Verifica se o estabelecimento está aberto
     */
    public function isOpen(): bool
    {
        return $this->is_open;
    }

    /**
     * Verifica se a entrega é gratuita para o valor
     */
    public function isFreeDelivery(float $amount): bool
    {
        return $amount >= $this->free_delivery_threshold;
    }

    /**
     * Calcula taxa de entrega
     */
    public function calculateDeliveryFee(float $distance): float
    {
        if ($distance <= 0) {
            return 0;
        }

        return $this->delivery_fee_per_km * $distance;
    }

    /**
     * Accessor para endereço completo
     */
    public function getFullAddressAttribute()
    {
        return $this->business_full_address ?: $this->business_address;
    }

    /**
     * Obter configurações de tema do estabelecimento
     */
    public function getThemeSettings(): array
    {
        return [
            'theme_primary_color' => $this->theme_primary_color ?? '#f59e0b',
            'theme_secondary_color' => $this->theme_secondary_color ?? '#8b5cf6',
            'theme_accent_color' => $this->theme_accent_color ?? '#10b981',
            'theme_background_color' => $this->theme_background_color ?? '#ffffff',
            'theme_text_color' => $this->theme_text_color ?? '#1f2937',
            'theme_border_color' => $this->theme_border_color ?? '#e5e7eb',
            'theme_logo_url' => $this->theme_logo_url ?? $this->logo_url ?? '/images/logo-default.png',
            'theme_favicon_url' => $this->theme_favicon_url ?? '/favicon.ico',
            'theme_brand_name' => $this->theme_brand_name ?? $this->business_name ?? 'PDV',
            'theme_font_family' => $this->theme_font_family ?? "'Inter', -apple-system, BlinkMacSystemFont, sans-serif",
            'theme_border_radius' => $this->theme_border_radius ?? '12px',
            'theme_shadow_style' => $this->theme_shadow_style ?? '0 4px 12px rgba(0,0,0,0.08)',
        ];
    }

    /**
     * Atualizar configurações de tema
     */
    public function updateThemeSettings(array $themeData): bool
    {
        return $this->update($themeData);
    }
}
