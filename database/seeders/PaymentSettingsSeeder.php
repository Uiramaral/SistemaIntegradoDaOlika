<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentSetting;

class PaymentSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'key' => 'mercadopago_access_token',
                'value' => '',
                'description' => 'Token de acesso do MercadoPago',
                'is_active' => true,
            ],
            [
                'key' => 'mercadopago_public_key',
                'value' => '',
                'description' => 'Chave pública do MercadoPago',
                'is_active' => true,
            ],
            [
                'key' => 'mercadopago_environment',
                'value' => 'sandbox',
                'description' => 'Ambiente do MercadoPago (sandbox/production)',
                'is_active' => true,
            ],
            [
                'key' => 'test_mode_enabled',
                'value' => 'false',
                'description' => 'Modo de teste ativado (valores entre 1-10 centavos)',
                'is_active' => true,
            ],
            [
                'key' => 'pix_expiration_minutes',
                'value' => '30',
                'description' => 'Tempo de expiração do PIX em minutos',
                'is_active' => true,
            ],
        ];

        foreach ($settings as $setting) {
            PaymentSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
