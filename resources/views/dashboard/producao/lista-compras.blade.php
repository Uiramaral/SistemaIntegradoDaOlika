@extends('dashboard.layouts.app')

@section('page_title', 'Lista de Compras')
@section('page_subtitle', 'Ingredientes necessários para produção')

@section('content')
<div class="bg-card rounded-xl border border-border p-6">
    <h3 class="font-semibold text-lg mb-6">Lista de Compras – {{ \Carbon\Carbon::parse($date)->translatedFormat('d/m/Y') }}</h3>
    
    @if(count($shoppingList) > 0)
    <div class="space-y-4">
        @foreach($shoppingList as $item)
        <div class="flex items-center justify-between p-4 border border-border rounded-lg">
            <div class="flex-1">
                <h4 class="font-semibold">{{ $item['ingredient']->name }}</h4>
                <p class="text-sm text-muted-foreground">
                    Necessário: {{ number_format($item['needed'], 2, ',', '.') }}kg
                    | Estoque: {{ number_format($item['current_stock'], 2, ',', '.') }}kg
                    @if($item['current_stock'] < $item['needed'])
                        <span class="text-red-600 font-semibold">(Faltando)</span>
                    @endif
                </p>
            </div>
            <span class="status-badge {{ $item['current_stock'] >= $item['needed'] ? 'status-badge-completed' : 'status-badge-pending' }}">
                {{ $item['current_stock'] >= $item['needed'] ? 'OK' : 'Comprar' }}
            </span>
        </div>
        @endforeach
    </div>
    @else
    <div class="text-center py-12 text-muted-foreground">
        <i data-lucide="shopping-cart" class="w-12 h-12 mx-auto mb-4 opacity-50"></i>
        <p>Nenhum ingrediente necessário</p>
    </div>
    @endif
</div>
@endsection
