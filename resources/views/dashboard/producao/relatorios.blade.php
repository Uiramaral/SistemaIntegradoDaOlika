@extends('dashboard.layouts.app')

@section('page_title', 'Relatórios de Produção')

@section('content')
<div class="space-y-6">
    <div class="bg-card rounded-xl border border-border p-6">
        <form method="GET" class="flex gap-4 mb-6">
            <input type="date" name="start_date" value="{{ $startDate }}" class="form-input">
            <input type="date" name="end_date" value="{{ $endDate }}" class="form-input">
            <button type="submit" class="btn-primary">Filtrar</button>
        </form>

        <div class="grid gap-4 md:grid-cols-3 mb-6">
            <div class="p-4 bg-muted/30 rounded-lg">
                <p class="text-sm text-muted-foreground">Total Produzido</p>
                <p class="text-2xl font-bold">{{ number_format($totalQuantity, 0, ',', '.') }} unidades</p>
            </div>
            <div class="p-4 bg-muted/30 rounded-lg">
                <p class="text-sm text-muted-foreground">Peso Total</p>
                <p class="text-2xl font-bold">{{ number_format($totalWeight, 2, ',', '.') }}g</p>
            </div>
            <div class="p-4 bg-muted/30 rounded-lg">
                <p class="text-sm text-muted-foreground">Custo Total</p>
                <p class="text-2xl font-bold">R$ {{ number_format($totalCost, 2, ',', '.') }}</p>
            </div>
        </div>

        <h3 class="font-semibold text-lg mb-4">Itens Mais Produzidos</h3>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-muted/50">
                    <tr>
                        <th class="text-left py-3 px-4">Receita</th>
                        <th class="text-left py-3 px-4">Quantidade</th>
                        <th class="text-left py-3 px-4">Peso Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($mostProduced as $item)
                    <tr class="border-b border-border">
                        <td class="py-3 px-4">{{ $item->recipe_name }}</td>
                        <td class="py-3 px-4">{{ number_format($item->total_quantity, 0, ',', '.') }}</td>
                        <td class="py-3 px-4">{{ number_format($item->total_weight, 2, ',', '.') }}g</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
