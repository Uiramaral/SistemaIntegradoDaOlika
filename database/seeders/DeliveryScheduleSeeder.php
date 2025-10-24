<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DeliverySchedule;

class DeliveryScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $schedules = [
            [
                'name' => 'Segunda-feira - Manhã',
                'day_of_week' => 'monday',
                'start_time' => '08:00',
                'end_time' => '12:00',
                'max_orders' => 20,
                'delivery_lead_time_days' => 1,
                'cutoff_time' => '18:00',
                'is_active' => true,
            ],
            [
                'name' => 'Segunda-feira - Tarde',
                'day_of_week' => 'monday',
                'start_time' => '14:00',
                'end_time' => '18:00',
                'max_orders' => 20,
                'delivery_lead_time_days' => 1,
                'cutoff_time' => '18:00',
                'is_active' => true,
            ],
            [
                'name' => 'Terça-feira - Manhã',
                'day_of_week' => 'tuesday',
                'start_time' => '08:00',
                'end_time' => '12:00',
                'max_orders' => 20,
                'delivery_lead_time_days' => 1,
                'cutoff_time' => '18:00',
                'is_active' => true,
            ],
            [
                'name' => 'Terça-feira - Tarde',
                'day_of_week' => 'tuesday',
                'start_time' => '14:00',
                'end_time' => '18:00',
                'max_orders' => 20,
                'delivery_lead_time_days' => 1,
                'cutoff_time' => '18:00',
                'is_active' => true,
            ],
            [
                'name' => 'Quarta-feira - Manhã',
                'day_of_week' => 'wednesday',
                'start_time' => '08:00',
                'end_time' => '12:00',
                'max_orders' => 20,
                'delivery_lead_time_days' => 1,
                'cutoff_time' => '18:00',
                'is_active' => true,
            ],
            [
                'name' => 'Quarta-feira - Tarde',
                'day_of_week' => 'wednesday',
                'start_time' => '14:00',
                'end_time' => '18:00',
                'max_orders' => 20,
                'delivery_lead_time_days' => 1,
                'cutoff_time' => '18:00',
                'is_active' => true,
            ],
            [
                'name' => 'Quinta-feira - Manhã',
                'day_of_week' => 'thursday',
                'start_time' => '08:00',
                'end_time' => '12:00',
                'max_orders' => 20,
                'delivery_lead_time_days' => 1,
                'cutoff_time' => '18:00',
                'is_active' => true,
            ],
            [
                'name' => 'Quinta-feira - Tarde',
                'day_of_week' => 'thursday',
                'start_time' => '14:00',
                'end_time' => '18:00',
                'max_orders' => 20,
                'delivery_lead_time_days' => 1,
                'cutoff_time' => '18:00',
                'is_active' => true,
            ],
            [
                'name' => 'Sexta-feira - Manhã',
                'day_of_week' => 'friday',
                'start_time' => '08:00',
                'end_time' => '12:00',
                'max_orders' => 25,
                'delivery_lead_time_days' => 1,
                'cutoff_time' => '18:00',
                'is_active' => true,
            ],
            [
                'name' => 'Sexta-feira - Tarde',
                'day_of_week' => 'friday',
                'start_time' => '14:00',
                'end_time' => '18:00',
                'max_orders' => 25,
                'delivery_lead_time_days' => 1,
                'cutoff_time' => '18:00',
                'is_active' => true,
            ],
            [
                'name' => 'Sábado - Manhã',
                'day_of_week' => 'saturday',
                'start_time' => '08:00',
                'end_time' => '12:00',
                'max_orders' => 30,
                'delivery_lead_time_days' => 1,
                'cutoff_time' => '18:00',
                'is_active' => true,
            ],
            [
                'name' => 'Sábado - Tarde',
                'day_of_week' => 'saturday',
                'start_time' => '14:00',
                'end_time' => '18:00',
                'max_orders' => 30,
                'delivery_lead_time_days' => 1,
                'cutoff_time' => '18:00',
                'is_active' => true,
            ],
        ];

        foreach ($schedules as $schedule) {
            DeliverySchedule::create($schedule);
        }
    }
}
