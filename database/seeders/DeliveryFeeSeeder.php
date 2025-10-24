<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DeliveryFee;

class DeliveryFeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DeliveryFee::create([
            'name' => 'Entrega Padrão',
            'description' => 'Entrega padrão para toda a cidade',
            'base_fee' => 8.00,
            'fee_per_km' => 2.50,
            'minimum_order_value' => 50.00,
            'free_delivery_threshold' => 100.00,
            'max_distance_km' => 15.00,
            'is_active' => true,
            'delivery_time_minutes' => 60,
        ]);

        DeliveryFee::create([
            'name' => 'Entrega Expressa',
            'description' => 'Entrega rápida para pedidos urgentes',
            'base_fee' => 15.00,
            'fee_per_km' => 3.00,
            'minimum_order_value' => 80.00,
            'free_delivery_threshold' => 150.00,
            'max_distance_km' => 10.00,
            'is_active' => true,
            'delivery_time_minutes' => 30,
        ]);
    }
}
