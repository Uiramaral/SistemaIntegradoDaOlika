<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Setting::create([
            'business_name' => 'Olika - Pães Artesanais',
            'business_description' => 'Pães e massas artesanais feitos com amor',
            'business_phone' => '(71) 98701-9420',
            'business_email' => 'contato@olika.com.br',
            'business_address' => 'Salvador, BA',
            'business_full_address' => 'Rua das Pães, 123, Salvador, BA, 40000-000',
            'business_latitude' => -12.97770000,
            'business_longitude' => -38.50160000,
            'is_open' => true,
            'primary_color' => '#F7941E',
            'min_delivery_value' => 100.00,
            'free_delivery_threshold' => 100.00,
            'delivery_fee_per_km' => 2.50,
            'max_delivery_distance' => 15.00,
            'mercadopago_env' => 'sandbox',
            'loyalty_enabled' => true,
            'loyalty_points_per_real' => 1.00,
            'cashback_percentage' => 5.00,
            'advance_order_days' => 1,
        ]);
    }
}
