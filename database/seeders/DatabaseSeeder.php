<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CategorySeeder::class,
            ProductSeeder::class,
            SettingSeeder::class,
            CouponSeeder::class,
            LoyaltyProgramSeeder::class,
            DeliveryFeeSeeder::class,
            DeliveryScheduleSeeder::class,
            OrderDeliveryFeeSeeder::class,
            PaymentSettingsSeeder::class,
        ]);
    }
}
