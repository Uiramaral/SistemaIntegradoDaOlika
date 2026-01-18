<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class MasterSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
    ];

    const TYPE_STRING = 'string';
    const TYPE_INTEGER = 'integer';
    const TYPE_DECIMAL = 'decimal';
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_JSON = 'json';

    /**
     * Obtém valor tipado
     */
    public function getTypedValueAttribute()
    {
        return match($this->type) {
            self::TYPE_INTEGER => (int) $this->value,
            self::TYPE_DECIMAL => (float) $this->value,
            self::TYPE_BOOLEAN => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            self::TYPE_JSON => json_decode($this->value, true),
            default => $this->value,
        };
    }

    /**
     * Obtém configuração por chave
     */
    public static function get(string $key, $default = null)
    {
        return Cache::remember("master_setting:{$key}", 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            return $setting ? $setting->typed_value : $default;
        });
    }

    /**
     * Define configuração
     */
    public static function set(string $key, $value, string $type = null, string $description = null): self
    {
        // Detectar tipo automaticamente se não informado
        if ($type === null) {
            $type = match(true) {
                is_int($value) => self::TYPE_INTEGER,
                is_float($value) => self::TYPE_DECIMAL,
                is_bool($value) => self::TYPE_BOOLEAN,
                is_array($value) => self::TYPE_JSON,
                default => self::TYPE_STRING,
            };
        }

        // Converter valor para string
        $stringValue = match($type) {
            self::TYPE_BOOLEAN => $value ? '1' : '0',
            self::TYPE_JSON => json_encode($value),
            default => (string) $value,
        };

        $setting = static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $stringValue,
                'type' => $type,
                'description' => $description ?? static::where('key', $key)->value('description'),
            ]
        );

        // Limpar cache
        Cache::forget("master_setting:{$key}");
        Cache::forget('master_settings:all');

        return $setting;
    }

    /**
     * Obtém todas as configurações como array chave-valor
     * NOTA: Renomeado de all() para evitar conflito com Eloquent
     */
    public static function getAllSettings(): array
    {
        return Cache::remember('master_settings:all', 3600, function () {
            return static::query()->pluck('value', 'key')->toArray();
        });
    }

    /**
     * Configurações comuns
     */
    public static function getWhatsAppInstancePrice(): float
    {
        return (float) static::get('whatsapp_instance_price', 15.00);
    }

    public static function getTrialDays(): int
    {
        return (int) static::get('trial_days', 7);
    }

    public static function getExpiryNotificationDays(): array
    {
        $days = static::get('expiry_notification_days', '7,3,1');
        return array_map('intval', explode(',', $days));
    }

    public static function getDefaultPlanSlug(): string
    {
        return static::get('default_plan_slug', 'basico');
    }

    public static function getBillingCurrency(): string
    {
        return static::get('billing_currency', 'BRL');
    }
}
