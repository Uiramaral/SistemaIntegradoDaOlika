@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <x-card class="text-center">
        <div class="text-3xl font-bold text-orange-600 mb-2">{{ $totalPedidos }}</div>
        <div class="text-sm text-gray-600">Total de Pedidos</div>
    </x-card>
    
    <x-card class="text-center">
        <div class="text-3xl font-bold text-green-600 mb-2">R$ {{ number_format($faturamento, 2, ',', '.') }}</div>
        <div class="text-sm text-gray-600">Faturamento</div>
    </x-card>
    
    <x-card class="text-center">
        <div class="text-3xl font-bold text-blue-600 mb-2">{{ $novosClientes }}</div>
        <div class="text-sm text-gray-600">Novos Clientes</div>
    </x-card>
    
    <x-card class="text-center">
        <div class="text-3xl font-bold text-purple-600 mb-2">R$ {{ number_format($ticketMedio, 2, ',', '.') }}</div>
        <div class="text-sm text-gray-600">Ticket Médio</div>
    </x-card>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <x-card title="Pedidos por Status">
        <div class="space-y-3">
            @foreach($statusCount as $status => $count)
                <div class="flex justify-between items-center">
                    <span class="capitalize">{{ $status }}</span>
                    <span class="badge badge-info">{{ $count }}</span>
                </div>
            @endforeach
        </div>
    </x-card>
    
    <x-card title="Pedidos de Hoje">
        @if($todayOrders->count() > 0)
            <div class="space-y-2">
                @foreach($todayOrders->take(5) as $order)
                    <div class="flex justify-between items-center p-2 bg-gray-50 rounded">
                        <span class="text-sm">#OLK{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</span>
                        <span class="text-sm font-medium">R$ {{ number_format($order->total ?? 0, 2, ',', '.') }}</span>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-500 text-center py-4">Nenhum pedido hoje</p>
        @endif
    </x-card>
</div>
@endsection