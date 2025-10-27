{{-- PÁGINA: Status & Produção (Gerenciamento de Pedidos) --}}
@extends('layouts.dashboard')

@section('title', 'Status & Produção')

@section('content')
<div class="px-6 py-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold tracking-tight">Status & Produção</h1>

        <div class="flex items-center gap-2">
            {{-- Botão "Baixar Layout" REMOVIDO por solicitação --}}
            <a href="{{ route('order-status.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-orange-500 text-white px-4 py-2 hover:bg-orange-600">
                + Novo Status
            </a>
        </div>
    </div>

    <div class="mt-4 grid grid-cols-1 gap-4">
        <div class="rounded-xl border border-gray-200 bg-white">
            <div class="px-5 py-4 flex items-center justify-between border-b border-gray-100">
                <h2 class="text-base font-semibold">Pedidos Recentes</h2>

                <form method="GET" action="{{ route('orders.index') }}" class="flex items-center gap-2">
                    <input type="text" name="q" placeholder="Buscar cliente..." value="{{ request('q') }}"
                           class="h-9 w-56 rounded-lg border border-gray-200 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-400" />
                    <button class="h-9 rounded-lg border border-gray-200 px-3 text-sm hover:bg-gray-50">Buscar</button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500">
                            <th class="px-5 py-3 font-medium">#</th>
                            <th class="px-5 py-3 font-medium">Cliente</th>
                            <th class="px-5 py-3 font-medium">Status</th>
                            <th class="px-5 py-3 font-medium">Total</th>
                            <th class="px-5 py-3 font-medium">Criado</th>
                            <th class="px-5 py-3 font-medium">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse(($pedidosRecentes ?? []) as $order)
                            <tr>
                                <td class="px-5 py-3 whitespace-nowrap">
                                    #{{ $order->order_number ?? ('OLK'.str_pad($order->id, 5, '0', STR_PAD_LEFT)) }}
                                </td>
                                <td class="px-5 py-3 whitespace-nowrap">
                                    <span class="font-medium">{{ optional($order->customer)->name ?? 'Cliente' }}</span>
                                </td>
                                <td class="px-5 py-3 whitespace-nowrap">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium
                                        @switch($order->status)
                                            @case('pending') bg-gray-100 text-gray-700 @break
                                            @case('confirmed') bg-blue-100 text-blue-700 @break
                                            @case('preparing') bg-yellow-100 text-yellow-700 @break
                                            @case('delivered') bg-emerald-100 text-emerald-700 @break
                                            @default bg-gray-100 text-gray-700
                                        @endswitch
                                    ">
                                        {{ ucfirst($order->status ?? 'pending') }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 whitespace-nowrap">
                                    R$ {{ number_format($order->final_amount ?? 0, 2, ',', '.') }}
                                </td>
                                <td class="px-5 py-3 whitespace-nowrap">
                                    {{ $order->created_at?->format('d/m H:i') }}
                                </td>
                                <td class="px-5 py-3 whitespace-nowrap">
                                    <a href="{{ route('orders.show', $order->id) }}" class="rounded-lg border border-gray-200 px-3 py-1.5 hover:bg-gray-50">Ver</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-10 text-center text-gray-500">
                                    Nenhum pedido encontrado
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

