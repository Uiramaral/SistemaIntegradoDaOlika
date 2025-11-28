<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
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
    ];

    /**
     * Busca configurações do sistema
     */
    public static function getSettings()
    {
        return static::first() ?? new static();
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
}
