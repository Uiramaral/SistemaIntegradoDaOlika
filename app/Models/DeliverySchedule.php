<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliverySchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'day_of_week',
        'start_time',
        'end_time',
        'max_orders',
        'current_orders',
        'delivery_lead_time_days',
        'cutoff_time',
        'is_active',
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'cutoff_time' => 'datetime:H:i',
        'is_active' => 'boolean',
    ];

    /**
     * Scope para horários ativos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para dia da semana
     */
    public function scopeByDay($query, string $day)
    {
        return $query->where('day_of_week', $day);
    }

    /**
     * Scope para horários disponíveis
     */
    public function scopeAvailable($query)
    {
        return $query->whereRaw('current_orders < max_orders');
    }

    /**
     * Verifica se o horário está disponível
     */
    public function isAvailable(): bool
    {
        return $this->is_active && $this->current_orders < $this->max_orders;
    }

    /**
     * Verifica se está dentro do horário
     */
    public function isWithinTime(): bool
    {
        $now = now();
        $start = $now->copy()->setTimeFromTimeString($this->start_time->format('H:i'));
        $end = $now->copy()->setTimeFromTimeString($this->end_time->format('H:i'));

        return $now->between($start, $end);
    }

    /**
     * Incrementa contador de pedidos
     */
    public function incrementOrders(): void
    {
        $this->increment('current_orders');
    }

    /**
     * Decrementa contador de pedidos
     */
    public function decrementOrders(): void
    {
        if ($this->current_orders > 0) {
            $this->decrement('current_orders');
        }
    }

    /**
     * Accessor para dia da semana em português
     */
    public function getDayOfWeekLabelAttribute()
    {
        $days = [
            'monday' => 'Segunda-feira',
            'tuesday' => 'Terça-feira',
            'wednesday' => 'Quarta-feira',
            'thursday' => 'Quinta-feira',
            'friday' => 'Sexta-feira',
            'saturday' => 'Sábado',
            'sunday' => 'Domingo',
        ];

        return $days[$this->day_of_week] ?? $this->day_of_week;
    }

    /**
     * Accessor para horário formatado
     */
    public function getTimeRangeAttribute()
    {
        return $this->start_time->format('H:i') . ' - ' . $this->end_time->format('H:i');
    }

    /**
     * Accessor para disponibilidade
     */
    public function getAvailabilityAttribute()
    {
        return $this->max_orders - $this->current_orders;
    }
}
