{{-- PÁGINA: Dashboard Principal (Visão Geral) --}}
@extends('layouts.dashboard')

@section('title', 'Visão Geral')

@section('content')
<div class="px-6 py-6">
    {{-- Header --}}
    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-3xl font-semibold tracking-tight">Visão Geral</h1>
            <p class="text-sm text-gray-500 mt-1">Acompanhe suas métricas e desempenho em tempo real</p>
        </div>

        <div class="flex items-center gap-2">
            {{-- Botão "Baixar Layout" REMOVIDO por solicitação --}}
        </div>
    </div>

    {{-- Métricas em cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-6">
        {{-- Total Hoje --}}
        <div class="rounded-xl border border-gray-200 bg-white p-5">
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-500">Total Hoje</span>
                <span class="text-gray-400">💲</span>
            </div>
            <div class="mt-2 text-2xl font-semibold">
                R$ {{ number_format($totalHoje ?? 0, 2, ',', '.') }}
            </div>
        </div>

        {{-- Pedidos Hoje --}}
        <div class="rounded-xl border border-gray-200 bg-white p-5">
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-500">Pedidos Hoje</span>
                <span class="text-gray-400">🧾</span>
            </div>
            <div class="mt-2 text-2xl font-semibold">
                {{ $pedidosHoje ?? 0 }}
            </div>
        </div>

        {{-- Pagos Hoje --}}
        <div class="rounded-xl border border-gray-200 bg-white p-5">
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-500">Pagos Hoje</span>
                <span class="text-gray-400">🔄</span>
            </div>
            <div class="mt-2 text-2xl font-semibold">
                {{ $pagosHoje ?? 0 }}
            </div>
        </div>

        {{-- Pendentes Pgto --}}
        <div class="rounded-xl border border-gray-200 bg-white p-5">
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-500">Pendentes Pgto</span>
                <span class="text-gray-400">⏳</span>
            </div>
            <div class="mt-2 text-2xl font-semibold">
                {{ $pendentesPgtoHoje ?? 0 }}
            </div>
        </div>
    </div>

    {{-- Cards: Pedidos Recentes / Top Produtos --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-6">
        {{-- Pedidos Recentes --}}
        <div class="rounded-xl border border-gray-200 bg-white">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <div>
                    <h2 class="text-lg font-semibold">Pedidos Recentes</h2>
                    <p class="text-xs text-gray-500">Últimos pedidos realizados</p>
                </div>
                @if(!empty($linkVerTodosPedidos))
                    <a href="{{ $linkVerTodosPedidos }}" class="text-sm font-medium text-gray-500 hover:text-gray-700">Ver todos</a>
                @endif
            </div>

            @if(($pedidosRecentes ?? collect())->count())
                <div class="divide-y divide-gray-100">
                    @foreach($pedidosRecentes as $order)
                        <div class="px-5 py-4 flex items-center justify-between">
                            <div class="min-w-0">
                                <div class="text-sm">
                                    <span class="text-gray-400">#</span>
                                    <span class="font-medium">{{ $order->order_number ?? ('OLK'.str_pad($order->id, 5, '0', STR_PAD_LEFT)) }}</span>
                                    <span class="text-gray-500"> • </span>
                                    <span class="font-medium text-gray-800">
                                        {{ optional($order->customer)->name ?? 'Cliente' }}
                                    </span>
                                </div>
                                <div class="text-xs text-gray-500 mt-0.5">
                                    {{ $order->created_at?->format('d/m H:i') }}
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium
                                    @switch($order->status)
                                        @case('pending') bg-yellow-50 text-yellow-700 border border-yellow-200 @break
                                        @case('confirmed') bg-blue-50 text-blue-700 border border-blue-200 @break
                                        @case('preparing') bg-purple-50 text-purple-700 border border-purple-200 @break
                                        @case('delivered') bg-emerald-50 text-emerald-700 border border-emerald-200 @break
                                        @default bg-gray-50 text-gray-700 border border-gray-200
                                    @endswitch
                                ">
                                    {{ ucfirst($order->status ?? 'pending') }}
                                </span>

                                <div class="text-sm font-semibold">
                                    R$ {{ number_format($order->final_amount ?? 0, 2, ',', '.') }}
                                </div>

                                <a href="{{ route('orders.show', $order->id) }}"
                                   class="text-sm rounded-lg px-3 py-1.5 border border-gray-200 hover:bg-gray-50">
                                    Ver
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="px-5 py-10 text-center text-sm text-gray-500">
                    <div class="text-3xl mb-2">🛍️</div>
                    Nenhum pedido registrado ainda
                </div>
            @endif
        </div>

        {{-- Top Produtos (últimos 7 dias) --}}
        <div class="rounded-xl border border-gray-200 bg-white">
            <div class="px-5 py-4 border-b border-gray-100">
                <h2 class="text-lg font-semibold">Top Produtos</h2>
                <p class="text-xs text-gray-500">Últimos 7 dias</p>
            </div>

            @if(($topProdutos ?? collect())->count())
                <ul class="divide-y divide-gray-100">
                    @foreach($topProdutos as $row)
                        <li class="px-5 py-4 flex items-center justify-between">
                            <div class="flex items-center gap-3 min-w-0">
                                @php $p = $row->product ?? null; @endphp
                                <div class="h-9 w-9 rounded-lg bg-gray-100 overflow-hidden flex items-center justify-center">
                                    @if($p && !empty($p->image_url))
                                        <img src="{{ $p->image_url }}" class="h-full w-full object-cover" alt="{{ $p->name }}">
                                    @else
                                        <span class="text-lg">🍞</span>
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <div class="text-sm font-medium truncate">{{ $p->name ?? 'Produto' }}</div>
                                    <div class="text-xs text-gray-500">R$ {{ number_format($p->price ?? 0, 2, ',', '.') }}</div>
                                </div>
                            </div>

                            <div class="text-sm font-semibold">
                                {{ $row->qty }} un.
                            </div>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="px-5 py-10 text-center text-sm text-gray-500">
                    <div class="text-3xl mb-2">💲</div>
                    Nenhum produto vendido ainda
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
