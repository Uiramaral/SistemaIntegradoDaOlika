<?php

namespace App\Services;

use App\Models\DeliverySchedule;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DeliverySlotsService
{
    /**
     * Retorna dias e horários de entrega disponíveis com base em delivery_schedules e regras em configurações.
     *
     * @param int|null $clientId Filtrar por client_id (multi-tenant). Null = não filtrar.
     * @return array<array{date: string, label: string, day_name: string, slots: array}>
     */
    public static function buildAvailableDates(?int $clientId = null): array
    {
        $advanceDays = 2;
        try {
            if (Schema::hasTable('settings')) {
                if (Schema::hasColumn('settings', 'advance_order_days')) {
                    $advanceDays = (int) (DB::table('settings')->value('advance_order_days') ?? 2);
                } else {
                    $keyCol = collect(['key', 'name', 'config_key'])->first(fn ($c) => Schema::hasColumn('settings', $c));
                    $valCol = collect(['value', 'val', 'config_value'])->first(fn ($c) => Schema::hasColumn('settings', $c));
                    if ($keyCol && $valCol) {
                        $val = DB::table('settings')->where($keyCol, 'advance_order_days')->value($valCol);
                        if ($val !== null) {
                            $advanceDays = (int) $val;
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            // manter padrão
        }

        $q = DeliverySchedule::where('is_active', true);
        if ($clientId !== null && Schema::hasColumn('delivery_schedules', 'client_id')) {
            $q->where('client_id', $clientId);
        }
        $deliverySchedules = $q->get()->groupBy('day_of_week');

        $slotCapacity = 2;
        try {
            if (Schema::hasTable('settings')) {
                if (Schema::hasColumn('settings', 'delivery_slot_capacity')) {
                    $slotCapacity = (int) (DB::table('settings')->value('delivery_slot_capacity') ?? 2);
                } else {
                    $keyCol = collect(['key', 'name', 'config_key'])->first(fn ($c) => Schema::hasColumn('settings', $c));
                    $valCol = collect(['value', 'val', 'config_value'])->first(fn ($c) => Schema::hasColumn('settings', $c));
                    if ($keyCol && $valCol) {
                        $val = DB::table('settings')->where($keyCol, 'delivery_slot_capacity')->value($valCol);
                        if ($val !== null) {
                            $slotCapacity = (int) $val;
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            // manter padrão
        }
        $slotCapacity = max(1, $slotCapacity);

        $availableDates = [];
        $today = Carbon::today();
        for ($i = $advanceDays; $i <= $advanceDays + 13; $i++) {
            $checkDate = $today->copy()->addDays($i);
            $dayOfWeek = strtolower($checkDate->format('l'));

            if (! $deliverySchedules->has($dayOfWeek)) {
                continue;
            }

            $schedules = $deliverySchedules[$dayOfWeek]->filter(fn ($s) => $s->is_active);
            if ($schedules->isEmpty()) {
                continue;
            }

            $slots = [];
            foreach ($schedules as $schedule) {
                $start = Carbon::today()->setTimeFromTimeString($schedule->start_time->format('H:i'));
                $end = Carbon::today()->setTimeFromTimeString($schedule->end_time->format('H:i'));

                while ($start < $end) {
                    $slotStart = $start->copy();
                    $slotEnd = $start->copy()->addMinutes(30);

                    $used = Order::whereDate('scheduled_delivery_at', $checkDate->toDateString())
                        ->whereTime('scheduled_delivery_at', $slotStart->format('H:i:00'))
                        ->count();

                    $available = max(0, $slotCapacity - $used);
                    if ($available > 0) {
                        $slotKey = $checkDate->format('Y-m-d') . ' ' . $slotStart->format('H:i');
                        $slots[] = [
                            'value' => $slotKey,
                            'label' => $slotStart->format('H:i') . ' - ' . $slotEnd->format('H:i'),
                            'available' => $available,
                        ];
                    }

                    $start->addMinutes(30);
                }
            }

            if (! empty($slots)) {
                $availableDates[] = [
                    'date' => $checkDate->format('Y-m-d'),
                    'label' => $checkDate->format('d/m/Y'),
                    'day_name' => $checkDate->locale('pt_BR')->dayName,
                    'slots' => $slots,
                ];
            }
        }

        return $availableDates;
    }
}
