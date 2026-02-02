@extends('dashboard.layouts.app')

@section('page_title', 'Extrato Mensal')
@section('page_subtitle', 'Finanças')

@section('page_actions')
    <a href="{{ route('dashboard.financas.index') }}" class="btn-outline gap-2 h-10 px-4 rounded-lg text-sm font-medium inline-flex items-center">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>
        Voltar
    </a>
@endsection

@section('content')
<div class="space-y-6">
    <div class="rounded-xl border border-border bg-card p-4 sm:p-5">
        <h3 class="text-sm font-semibold text-foreground mb-3">Filtrar por mês</h3>
        <form method="GET" action="{{ route('dashboard.financas.extrato-mensal') }}" class="flex flex-wrap items-center gap-2">
            <label class="text-sm font-medium text-muted-foreground sr-only sm:not-sr-only">Mês:</label>
            <input type="month" name="month" value="{{ $month }}" class="form-input h-10 rounded-lg border-border text-sm w-full sm:w-48" required>
            <button type="submit" class="btn-primary h-10 px-4 rounded-lg text-sm gap-2 inline-flex items-center">
                <i data-lucide="filter" class="w-4 h-4"></i>
                Filtrar
            </button>
        </form>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-card rounded-xl p-4 border border-border">
            <p class="text-sm text-muted-foreground">Receitas</p>
            <p class="text-xl font-bold text-green-600 dark:text-green-400">R$ {{ number_format($receitas ?? 0, 2, ',', '.') }}</p>
        </div>
        <div class="bg-card rounded-xl p-4 border border-border">
            <p class="text-sm text-muted-foreground">Despesas</p>
            <p class="text-xl font-bold text-red-600 dark:text-red-400">R$ {{ number_format($despesas ?? 0, 2, ',', '.') }}</p>
        </div>
        <div class="bg-card rounded-xl p-4 border border-border">
            <p class="text-sm text-muted-foreground">Lucro</p>
            <p class="text-xl font-bold {{ ($lucro ?? 0) >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">R$ {{ number_format($lucro ?? 0, 2, ',', '.') }}</p>
        </div>
    </div>

    <div class="bg-card rounded-xl border border-border overflow-hidden">
        <div class="p-4 border-b border-border">
            <h3 class="font-semibold text-foreground">Lançamentos de {{ \Carbon\Carbon::createFromFormat('Y-m', $month)->translatedFormat('F/Y') }}</h3>
        </div>
        <div class="divide-y divide-border">
            @forelse($transactions as $t)
                <div class="p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                        <p class="font-medium text-foreground">{{ $t->description ?: 'Sem descrição' }}</p>
                        <p class="text-sm text-muted-foreground">{{ $t->transaction_date->format('d/m/Y') }}@if($t->category) · {{ $t->category }}@endif</p>
                    </div>
                    <span class="font-semibold {{ $t->type === 'revenue' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                        {{ $t->type === 'revenue' ? '+' : '-' }} R$ {{ number_format($t->amount, 2, ',', '.') }}
                    </span>
                </div>
            @empty
                <div class="p-8 text-center text-muted-foreground">
                    Nenhum lançamento neste mês.
                </div>
            @endforelse
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() { if (typeof lucide !== 'undefined') lucide.createIcons(); });
</script>
@endpush
@endsection
