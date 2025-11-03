@php
// ---------- Dados de Cupons ----------
$coupons = collect();
try {
    if (class_exists(\App\Models\Coupon::class)) {
        $now = now();
        $coupons = \App\Models\Coupon::query()
            ->where('is_active', true)
            ->where(function($q) use ($now){
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function($q) use ($now){
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', $now);
            })
            ->orderByRaw('CASE WHEN expires_at IS NULL THEN 1 ELSE 0 END, expires_at ASC')
            ->limit(6)
            ->get(['code','name','description','expires_at']);
    }
} catch (\Throwable $e) {}

// ---------- Dias de Entrega ----------
$weekDaysPt = ['Segunda','Terça','Quarta','Quinta','Sexta','Sábado','Domingo'];
$activeDays = collect();
try {
    if (class_exists(\App\Models\DeliverySchedule::class)) {
        $activeDays = \App\Models\DeliverySchedule::query()
            ->where('is_active', true)
            ->pluck('day_of_week') // armazenado como texto (Segunda, Terça, ...)
            ->map(fn($d) => ucfirst($d));
    }
} catch (\Throwable $e) {}
@endphp

<div class="bg-white/90 backdrop-blur border-b">
    <div class="container mx-auto px-4 py-3">
        <div class="rounded-xl border bg-white shadow-sm p-3 md:p-4">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <!-- Cupons -->
                <div class="flex-1">
                    <div class="flex items-center gap-2 text-sm font-medium text-gray-800 mb-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-orange-500">
                            <path d="M3 3h18v6H3z"></path><path d="M16 21H8a2 2 0 0 1-2-2V9h12v10a2 2 0 0 1-2 2Z"></path>
                        </svg>
                        Cupons:
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @forelse($coupons as $c)
                            <span class="inline-flex items-center gap-2 rounded-md border border-orange-200 bg-orange-50 px-2 py-1">
                                <span class="text-[11px] font-semibold text-orange-800 tracking-wide">{{ strtoupper($c->code) }}</span>
                                @if($c->description)
                                    <span class="text-[11px] text-orange-700">{{ $c->description }}</span>
                                @elseif($c->name)
                                    <span class="text-[11px] text-orange-700">{{ $c->name }}</span>
                                @endif
                            </span>
                        @empty
                            <span class="text-sm text-gray-500">Sem cupons ativos no momento.</span>
                        @endforelse
                    </div>
                </div>

                <!-- Divisor -->
                <div class="hidden md:block w-px bg-gray-200"></div>

                <!-- Dias de entrega -->
                <div class="flex-1">
                    <div class="flex items-center gap-2 text-sm font-medium text-gray-800 mb-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-orange-500">
                            <path d="M3 5h18"></path><path d="M19 21H5a2 2 0 0 1-2-2V7h18v12a2 2 0 0 1-2 2Z"></path>
                        </svg>
                        Entrega:
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @foreach($weekDaysPt as $day)
                            @php $enabled = $activeDays->contains($day); @endphp
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs border {{ $enabled ? 'bg-orange-100 text-orange-800 border-orange-200' : 'bg-gray-100 text-gray-500 border-gray-200' }}">{{ $day }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
