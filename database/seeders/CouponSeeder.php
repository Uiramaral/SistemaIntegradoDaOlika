<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Coupon;

class CouponSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $coupons = [
            [
                'code' => 'BEMVINDO',
                'name' => 'Bem-vindo',
                'description' => 'Desconto de boas-vindas para novos clientes',
                'type' => 'percentage',
                'value' => 10.00,
                'minimum_amount' => 50.00,
                'usage_limit' => 1000,
                'usage_limit_per_customer' => 1,
                'visibility' => 'public',
                'starts_at' => now(),
                'expires_at' => now()->addYear(),
                'is_active' => true,
            ],
            [
                'code' => 'FRETE10',
                'name' => 'Frete Grátis',
                'description' => 'Frete grátis para pedidos acima de R$ 80',
                'type' => 'fixed',
                'value' => 10.00,
                'minimum_amount' => 80.00,
                'usage_limit' => 500,
                'usage_limit_per_customer' => 1,
                'visibility' => 'public',
                'starts_at' => now(),
                'expires_at' => now()->addMonths(6),
                'is_active' => true,
            ],
        ];

        foreach ($coupons as $coupon) {
            Coupon::updateOrCreate(
                ['code' => $coupon['code']],
                $coupon
            );
        }
    }
}
