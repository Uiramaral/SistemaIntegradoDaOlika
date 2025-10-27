<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AppSettings 
{
    public static function get(string $key, $default = null)
    {
        return Cache::remember("ps_{$key}", 300, function() use ($key, $default) {
            $row = DB::table('payment_settings')->where('key', $key)->first();
            
            if ($row && isset($row->value) && $row->value !== '') {
                return $row->value;
            }
            
            // fallback .env
            if ($key === 'mercadopago_access_token') {
                return env('MP_FALLBACK_ACCESS_TOKEN', $default);
            }
            
            if ($key === 'mercadopago_public_key') {
                return env('MP_FALLBACK_PUBLIC_KEY', $default);
            }
            
            if ($key === 'mercadopago_webhook_url') {
                return env('MP_WEBHOOK_URL', $default);
            }
            
            return $default;
        });
    }
}

