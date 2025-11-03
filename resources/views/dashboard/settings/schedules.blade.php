@extends('dashboard.layouts.app')

@section('title', 'Dias e horários de entrega')

@section('content')
<div class="space-y-6">
    @if ($errors->any())
    <div class="rounded-md border border-red-200 bg-red-50 text-red-700 p-4">
        <div class="font-semibold mb-1">Não foi possível salvar:</div>
        <ul class="list-disc list-inside text-sm">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif
    <div>
        <h1 class="text-3xl font-bold tracking-tight">Dias e horários de entrega</h1>
        <p class="text-muted-foreground">Cadastre as janelas por dia da semana</p>
    </div>

    <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
        <form action="{{ route('dashboard.settings.delivery.schedules.store') }}" method="POST" class="p-6 grid md:grid-cols-6 gap-4">
            @csrf
            <div>
                <label class="text-sm font-medium">Dia</label>
                <select name="day_of_week" class="w-full border rounded-md px-3 py-2 text-sm">
                    @foreach($weekdays as $k=>$v)
                        <option value="{{ $k }}">{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="text-sm font-medium">Nome (opcional)</label>
                <input name="name" class="w-full border rounded-md px-3 py-2 text-sm" placeholder="Manhã, Tarde...">
            </div>
            <div>
                <label class="text-sm font-medium">Início</label>
                <input type="time" name="start_time" required class="w-full border rounded-md px-3 py-2 text-sm">
            </div>
            <div>
                <label class="text-sm font-medium">Fim</label>
                <input type="time" name="end_time" required class="w-full border rounded-md px-3 py-2 text-sm">
            </div>
            <div>
                <label class="text-sm font-medium">Cutoff (opcional)</label>
                <input type="time" name="cutoff_time" class="w-full border rounded-md px-3 py-2 text-sm">
            </div>
            <div class="md:col-span-6 grid md:grid-cols-3 gap-4">
                <div>
                    <label class="text-sm font-medium">Dias de antecedência</label>
                    <input type="number" min="0" max="14" name="delivery_lead_time_days" class="w-full border rounded-md px-3 py-2 text-sm" placeholder="Ex.: 2">
                </div>
                <div>
                    <label class="text-sm font-medium">Capacidade (janela)</label>
                    <input type="number" min="0" name="max_orders" class="w-full border rounded-md px-3 py-2 text-sm" placeholder="0 = ilimitado">
                </div>
                <div class="flex items-end">
                    <label class="inline-flex items-center gap-2 text-sm font-medium"><input type="checkbox" name="is_active" value="1" checked> Ativo</label>
                </div>
            </div>
            <div class="md:col-span-6">
                <button class="inline-flex items-center justify-center gap-2 rounded-md text-sm font-medium bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4">Adicionar horário</button>
            </div>
        </form>
    </div>

    @foreach($weekdays as $dk=>$dl)
    <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
        <div class="p-4 border-b"><h3 class="font-semibold">{{ $dl }}</h3></div>
        <div class="divide-y">
            @forelse(($schedules[$dk] ?? collect()) as $sch)
            <form action="{{ route('dashboard.settings.delivery.schedules.update', $sch->id) }}" method="POST" class="p-4 grid md:grid-cols-6 gap-3 items-end">
                @csrf
                @method('PUT')
                <div class="md:col-span-2">
                    <label class="text-sm font-medium">Nome</label>
                    <input name="name" value="{{ $sch->name }}" class="w-full border rounded-md px-3 py-2 text-sm">
                    @if(isset($sch->slots_info))
                    <div class="mt-1 text-xs text-muted-foreground">
                        Slots: {{ $sch->slots_info['total'] }} períodos ({{ $sch->slots_info['total_available'] }}/{{ $sch->slots_info['total_capacity'] }} vagas disponíveis)
                    </div>
                    @endif
                </div>
                <div>
                    <label class="text-sm font-medium">Início</label>
                    <input type="time" name="start_time" value="{{ $sch->start_time->format('H:i') }}" class="w-full border rounded-md px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="text-sm font-medium">Fim</label>
                    <input type="time" name="end_time" value="{{ $sch->end_time->format('H:i') }}" class="w-full border rounded-md px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="text-sm font-medium">Cutoff</label>
                    <input type="time" name="cutoff_time" value="{{ optional($sch->cutoff_time)->format('H:i') }}" class="w-full border rounded-md px-3 py-2 text-sm">
                </div>
                <div class="md:col-span-6 grid md:grid-cols-4 gap-3 items-center">
                    <div>
                        <label class="text-sm font-medium">Dias antecedência</label>
                        <input type="number" name="delivery_lead_time_days" value="{{ (int)($sch->delivery_lead_time_days ?? 0) }}" class="w-full border rounded-md px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="text-sm font-medium">Capacidade (janela)</label>
                        <input type="number" name="max_orders" value="{{ (int)($sch->max_orders ?? 0) }}" class="w-full border rounded-md px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="inline-flex items-center gap-2 text-sm font-medium"><input type="checkbox" name="is_active" value="1" {{ $sch->is_active ? 'checked' : '' }}> Ativo</label>
                    </div>
                    <div class="flex gap-2 justify-end">
                        <button class="inline-flex items-center justify-center gap-2 rounded-md text-sm font-medium bg-primary text-primary-foreground hover:bg-primary/90 h-9 px-3">Salvar</button>
                        <button form="del-{{ $sch->id }}" type="submit" class="inline-flex items-center justify-center gap-2 rounded-md text-sm font-medium border h-9 px-3">Excluir</button>
                    </div>
                </div>
            </form>
            <form id="del-{{ $sch->id }}" action="{{ route('dashboard.settings.delivery.schedules.destroy', $sch->id) }}" method="POST" onsubmit="return confirm('Excluir este horário?');">
                @csrf
                @method('DELETE')
            </form>
            @empty
            <div class="p-4 text-sm text-muted-foreground">Nenhum horário cadastrado.</div>
            @endforelse
        </div>
    </div>
    @endforeach
</div>
@endsection


