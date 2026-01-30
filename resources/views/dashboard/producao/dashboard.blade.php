@extends('dashboard.layouts.app')

@section('page_title', 'Dashboard de Produção')

@section('content')
<div class="space-y-6">
    <div class="grid gap-4 md:grid-cols-4">
        <div class="bg-card rounded-xl border border-border p-6">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-lg bg-primary/10 flex items-center justify-center">
                    <i data-lucide="book-open" class="h-6 w-6 text-primary"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold">{{ $totalRecipes }}</p>
                    <p class="text-sm text-muted-foreground">Total de Receitas</p>
                </div>
            </div>
        </div>
        <div class="bg-card rounded-xl border border-border p-6">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-lg bg-green-500/10 flex items-center justify-center">
                    <i data-lucide="check-circle" class="h-6 w-6 text-green-600"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold">{{ $activeRecipes }}</p>
                    <p class="text-sm text-muted-foreground">Receitas Ativas</p>
                </div>
            </div>
        </div>
        <div class="bg-card rounded-xl border border-border p-6">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-lg bg-blue-500/10 flex items-center justify-center">
                    <i data-lucide="clipboard-list" class="h-6 w-6 text-blue-600"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold">{{ $totalProductionRecords }}</p>
                    <p class="text-sm text-muted-foreground">Registros de Produção</p>
                </div>
            </div>
        </div>
        <div class="bg-card rounded-xl border border-border p-6">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-lg bg-orange-500/10 flex items-center justify-center">
                    <i data-lucide="scale" class="h-6 w-6 text-orange-600"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold">{{ number_format($todayProduction, 0, ',', '.') }}g</p>
                    <p class="text-sm text-muted-foreground">Produção Hoje</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid gap-6 md:grid-cols-2">
        <div class="bg-card rounded-xl border border-border p-6">
            <h3 class="font-semibold text-lg mb-4">Itens Mais Produzidos (30 dias)</h3>
            <div class="space-y-2">
                @foreach($mostProduced->take(5) as $item)
                <div class="flex justify-between items-center p-2 border-b border-border">
                    <span>{{ $item->recipe_name }}</span>
                    <span class="font-semibold">{{ $item->total_quantity }}x</span>
                </div>
                @endforeach
            </div>
        </div>
        <div class="bg-card rounded-xl border border-border p-6">
            <h3 class="font-semibold text-lg mb-4">Produção Diária (7 dias)</h3>
            <div class="space-y-2">
                @foreach($dailyProduction as $day)
                <div class="flex justify-between items-center p-2 border-b border-border">
                    <span>{{ \Carbon\Carbon::parse($day->date)->format('d/m') }}</span>
                    <span class="font-semibold">{{ number_format($day->total, 0, ',', '.') }}g</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
