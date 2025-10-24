<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Obtém valor de uma configuração
     */
    public static function getValue(string $key, $default = null)
    {
        $setting = static::where('key', $key)->where('is_active', true)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Define valor de uma configuração
     */
    public static function setValue(string $key, $value, string $description = null): void
    {
        static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'description' => $description,
                'is_active' => true,
            ]
        );
    }

    /**
     * Verifica se modo de teste está ativado
     */
    public static function isTestModeEnabled(): bool
    {
        return static::getValue('test_mode_enabled', 'false') === 'true';
    }

    /**
     * Ativa/desativa modo de teste
     */
    public static function setTestMode(bool $enabled): void
    {
        static::setValue('test_mode_enabled', $enabled ? 'true' : 'false', 'Modo de teste');
    }

    /**
     * Obtém token do MercadoPago
     */
    public static function getMercadoPagoToken(): string
    {
        return static::getValue('mercadopago_access_token', '');
    }

    /**
     * Obtém chave pública do MercadoPago
     */
    public static function getMercadoPagoPublicKey(): string
    {
        return static::getValue('mercadopago_public_key', '');
    }

    /**
     * Obtém ambiente do MercadoPago
     */
    public static function getMercadoPagoEnvironment(): string
    {
        return static::getValue('mercadopago_environment', 'sandbox');
    }

    /**
     * Obtém tempo de expiração do PIX
     */
    public static function getPixExpirationMinutes(): int
    {
        return (int) static::getValue('pix_expiration_minutes', 30);
    }
}
