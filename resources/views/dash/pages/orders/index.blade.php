@extends('layouts.admin')

@section('title', 'Pedidos')
@section('page_title', 'Pedidos')

@section('content')
<div class="container-page">
  <div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">Pedidos</h1>
    <div class="flex gap-2">
      <x-button variant="outline" size="sm">
        <i class="fas fa-download mr-2"></i> Exportar
      </x-button>
      <x-button variant="primary" size="sm">
        <i class="fas fa-plus mr-2"></i> Novo Pedido
      </x-button>
    </div>
  </div>

  @if(session('status'))
    <x-alert type="success">{{ session('status') }}</x-alert>
  @endif

  @if(session('error'))
    <x-alert type="error">{{ session('error') }}</x-alert>
  @endif

  @if(isset($orders) && $orders->isEmpty())
    <x-card>
      <div class="text-center py-8">
        <i class="fas fa-shopping-cart text-4xl text-gray-400 mb-4"></i>
        <p class="text-gray-500 text-lg">Nenhum pedido encontrado.</p>
        <p class="text-gray-400 text-sm mt-2">Os pedidos aparecerão aqui quando forem criados.</p>
      </div>
    </x-card>
  @else
    <div class="overflow-x-auto">
      <table class="min-w-full bg-white shadow-md rounded-lg">
        <thead>
          <tr class="bg-gray-100 text-left text-sm font-medium text-gray-600">
            <th class="px-4 py-3">ID</th>
            <th class="px-4 py-3">Cliente</th>
            <th class="px-4 py-3">Total</th>
            <th class="px-4 py-3">Status</th>
            <th class="px-4 py-3">Data</th>
            <th class="px-4 py-3 text-right">Ações</th>
          </tr>
        </thead>
        <tbody class="text-sm text-gray-700">
          @foreach($orders ?? [] as $order)
            <tr class="border-t hover:bg-gray-50 transition-colors">
              <td class="px-4 py-3 font-medium">#{{ $order->id }}</td>
              <td class="px-4 py-3">{{ $order->cliente->nome ?? $order->customer->nome ?? '—' }}</td>
              <td class="px-4 py-3 font-medium">R$ {{ number_format($order->total ?? 0, 2, ',', '.') }}</td>
              <td class="px-4 py-3">
                @php
                  $statusType = match($order->status ?? 'pending') {
                    'completed', 'delivered' => 'success',
                    'pending', 'processing' => 'warning',
                    'cancelled', 'rejected' => 'danger',
                    default => 'info'
                  };
                @endphp
                <x-badge type="{{ $statusType }}">{{ ucfirst($order->status ?? 'Pendente') }}</x-badge>
              </td>
              <td class="px-4 py-3">{{ $order->created_at->format('d/m/Y H:i') }}</td>
              <td class="px-4 py-3 text-right">
                <div class="flex justify-end gap-2">
                  <a href="{{ route('dashboard.orders.show', $order) }}" class="text-orange-600 hover:text-orange-800 hover:underline text-sm">
                    <i class="fas fa-eye mr-1"></i> Ver
                  </a>
                  <a href="{{ route('dashboard.orders.edit', $order) }}" class="text-blue-600 hover:text-blue-800 hover:underline text-sm">
                    <i class="fas fa-edit mr-1"></i> Editar
                  </a>
                </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    @if(isset($orders) && $orders->hasPages())
      <div class="mt-6">
        {{ $orders->links() }}
      </div>
    @endif
  @endif
</div>
@endsection