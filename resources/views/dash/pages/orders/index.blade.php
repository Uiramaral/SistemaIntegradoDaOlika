@extends('layouts.admin')

@section('title', 'Pedidos')
@section('page_title', 'Pedidos')

@section('content')
<x-card title="Lista de Pedidos">
    <x-table :headers="['#Pedido', 'Cliente', 'Total', 'Status', 'Data']" :actions="true">
        @forelse($orders ?? [] as $order)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 border-b">#OLK{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</td>
                <td class="px-4 py-3 border-b">{{ $order->customer->nome ?? '—' }}</td>
                <td class="px-4 py-3 border-b font-medium">R$ {{ number_format($order->total ?? 0, 2, ',', '.') }}</td>
                <td class="px-4 py-3 border-b">
                    <span class="badge badge-warning">{{ ucfirst($order->status ?? 'pending') }}</span>
                </td>
                <td class="px-4 py-3 border-b">{{ $order->created_at->format('d/m/Y') }}</td>
                <td class="px-4 py-3 border-b text-right">
                    <x-button href="/orders/{{ $order->id }}" variant="secondary" size="sm">
                        <i class="fas fa-eye"></i> Ver
                    </x-button>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                    Nenhum pedido encontrado
                </td>
            </tr>
        @endforelse
    </x-table>
</x-card>
@endsection