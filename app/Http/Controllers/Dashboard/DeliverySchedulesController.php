<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\DeliverySchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DeliverySchedulesController extends Controller
{
    public function index()
    {
        $schedules = DeliverySchedule::orderByRaw("FIELD(day_of_week, 'monday','tuesday','wednesday','thursday','friday','saturday','sunday')")
            ->orderBy('start_time')
            ->get()
            ->groupBy('day_of_week');

        // Calcular disponibilidade de slots por janela - leitura flexível
        $slotCapacity = 2; // padrão
        try {
            if (Schema::hasTable('settings')) {
                if (Schema::hasColumn('settings', 'delivery_slot_capacity')) {
                    $slotCapacity = (int) (DB::table('settings')->value('delivery_slot_capacity') ?? 2);
                } else {
                    $keyCol = collect(['key','name','config_key'])->first(fn($c)=>Schema::hasColumn('settings',$c));
                    $valCol = collect(['value','val','config_value'])->first(fn($c)=>Schema::hasColumn('settings',$c));
                    if ($keyCol && $valCol) {
                        $val = DB::table('settings')->where($keyCol, 'delivery_slot_capacity')->value($valCol);
                        if ($val !== null) $slotCapacity = (int) $val;
                    }
                }
            }
        } catch (\Exception $e) {
            // Mantém padrão se houver erro
        }
        $slotCapacity = max(1, $slotCapacity);
        
        foreach ($schedules as $day => $daySchedules) {
            foreach ($daySchedules as $sch) {
                // Gerar todos os slots de 30min dentro da janela
                $slotTime = \Carbon\Carbon::today()->setTimeFromTimeString($sch->start_time->format('H:i'));
                $endTime = \Carbon\Carbon::today()->setTimeFromTimeString($sch->end_time->format('H:i'));
                $totalSlots = 0;
                $totalCapacity = 0;
                $totalAvailable = 0;
                $slotsDetail = [];
                
                while ($slotTime->lt($endTime)) {
                    $totalSlots++;
                    $slotHourMin = $slotTime->format('H:i:00');
                    
                    // Contar pedidos já agendados neste slot específico (horário exato)
                    $used = \App\Models\Order::whereTime('scheduled_delivery_at', $slotHourMin)
                        ->whereNotNull('scheduled_delivery_at')
                        ->whereDate('scheduled_delivery_at', '>=', now()->toDateString())
                        ->count();
                    
                    $available = max(0, $slotCapacity - $used);
                    $totalCapacity += $slotCapacity;
                    $totalAvailable += $available;
                    
                    $slotsDetail[] = [
                        'time' => $slotTime->format('H:i'),
                        'used' => $used,
                        'available' => $available,
                        'capacity' => $slotCapacity,
                    ];
                    
                    $slotTime->addMinutes(30);
                }
                
                $sch->slots_info = [
                    'total' => $totalSlots,
                    'capacity_per_slot' => $slotCapacity,
                    'total_capacity' => $totalCapacity,
                    'total_available' => $totalAvailable,
                    'slots' => $slotsDetail,
                ];
            }
        }

        $weekdays = [
            'monday' => 'Segunda',
            'tuesday' => 'Terça',
            'wednesday' => 'Quarta',
            'thursday' => 'Quinta',
            'friday' => 'Sexta',
            'saturday' => 'Sábado',
            'sunday' => 'Domingo',
        ];

        return view('dashboard.settings.schedules', compact('schedules','weekdays'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'day_of_week' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'name' => 'nullable|string|max:100',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'is_active' => 'nullable|boolean',
            'cutoff_time' => 'nullable|date_format:H:i',
            'delivery_lead_time_days' => 'nullable|integer|min:0|max:14',
            'max_orders' => 'nullable|integer|min:0',
        ]);

        // Impedir sobreposição de janelas no mesmo dia
        $start = $data['start_time'];
        $end   = $data['end_time'];
        $overlaps = DeliverySchedule::where('day_of_week', $data['day_of_week'])
            ->where(function($q) use ($start, $end) {
                // Sobreposição quando existente.start < novo.end E existente.end > novo.start
                $q->where('start_time', '<', $end)
                  ->where('end_time', '>', $start);
            })
            ->exists();
        if ($overlaps) {
            return back()->withErrors(['start_time' => 'Já existe uma janela que se sobrepõe a este período.'])
                         ->withInput();
        }

        $schedule = new DeliverySchedule();
        $schedule->day_of_week = $data['day_of_week'];
        $schedule->name = $data['name'] ?: ($data['start_time'] . ' - ' . $data['end_time']);
        $schedule->start_time = $data['start_time'];
        $schedule->end_time = $data['end_time'];
        $schedule->cutoff_time = $data['cutoff_time'] ?? null;
        $schedule->delivery_lead_time_days = $data['delivery_lead_time_days'] ?? null;
        $schedule->max_orders = isset($data['max_orders']) ? (int)$data['max_orders'] : ($schedule->max_orders ?? 0);
        $schedule->current_orders = 0;
        $schedule->is_active = (bool)($data['is_active'] ?? true);
        $schedule->save();

        return redirect()->route('dashboard.settings.delivery.schedules.index')
            ->with('success', 'Horário adicionado com sucesso.');
    }

    public function update(Request $request, DeliverySchedule $schedule)
    {
        $data = $request->validate([
            'name' => 'nullable|string|max:100',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'is_active' => 'nullable|boolean',
            'cutoff_time' => 'nullable|date_format:H:i',
            'delivery_lead_time_days' => 'nullable|integer|min:0|max:14',
            'max_orders' => 'nullable|integer|min:0',
        ]);
        // Impedir sobreposição ao atualizar (exclui a própria)
        $start = $data['start_time'];
        $end   = $data['end_time'];
        $overlaps = DeliverySchedule::where('day_of_week', $schedule->day_of_week)
            ->where('id', '!=', $schedule->id)
            ->where(function($q) use ($start, $end) {
                $q->where('start_time', '<', $end)
                  ->where('end_time', '>', $start);
            })
            ->exists();
        if ($overlaps) {
            return back()->withErrors(['start_time' => 'Esta atualização cria sobreposição com outra janela do mesmo dia.'])
                         ->withInput();
        }
        $schedule->name = $data['name'] ?: ($data['start_time'] . ' - ' . $data['end_time']);
        $schedule->start_time = $data['start_time'];
        $schedule->end_time = $data['end_time'];
        $schedule->is_active = (bool)($data['is_active'] ?? false);
        $schedule->cutoff_time = $data['cutoff_time'] ?? null;
        $schedule->delivery_lead_time_days = $data['delivery_lead_time_days'] ?? null;
        if (isset($data['max_orders'])) { $schedule->max_orders = (int)$data['max_orders']; }
        $schedule->save();

        return redirect()->route('dashboard.settings.delivery.schedules.index')
            ->with('success', 'Horário atualizado.');
    }

    public function destroy(DeliverySchedule $schedule)
    {
        $schedule->delete();
        return redirect()->route('dashboard.settings.delivery.schedules.index')
            ->with('success', 'Horário removido.');
    }
}


