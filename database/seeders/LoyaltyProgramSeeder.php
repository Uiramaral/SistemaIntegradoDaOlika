<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LoyaltyProgram;

class LoyaltyProgramSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        LoyaltyProgram::create([
            'name' => 'Programa Olika',
            'description' => 'Ganhe pontos a cada compra e troque por recompensas',
            'points_per_real' => 1.00,
            'real_per_point' => 0.0100,
            'minimum_points_to_redeem' => 100,
            'points_expiry_days' => 365,
            'is_active' => true,
            'start_date' => now(),
            'end_date' => null,
        ]);
    }
}
