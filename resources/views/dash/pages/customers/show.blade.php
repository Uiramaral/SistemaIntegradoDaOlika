@extends('layouts.admin')

@section('title', 'Cliente: ' . $customer->nome)
@section('page_title', 'Cliente: ' . $customer->nome)

@section('content')
<div class="container-page">
  <div class="flex justify-between items-center mb-6">
    <div>
      <h1 class="text-2xl font-bold">{{ $customer->nome }}</h1>
      <p class="text-sm text-gray-500">Telefone: {{ $customer->telefone }}</p>
    </div>
    <a href="{{ route('dashboard.customers.edit', $customer) }}" class="btn btn-secondary">
      <i class="fas fa-edit mr-2"></i> Editar
    </a>
  </div>

  <x-card class="mb-6">
    <h2 class="text-lg font-semibold mb-4">Dados do Cliente</h2>
    <ul class="text-sm text-gray-700 space-y-2">
      <li><strong>Nome:</strong> {{ $customer->nome }}</li>
      <li><strong>Telefone:</strong> {{ $customer->telefone ?? '—' }}</li>
      <li><strong>Endereço:</strong> {{ $customer->endereco ?? '—' }}</li>
      <li><strong>Fiado:</strong>
        @if(($customer->fiado ?? 0) > 0)
          <x-badge type="danger">R$ {{ number_format($customer->fiado, 2, ',', '.') }}</x-badge>
        @else
          <x-badge type="success">R$ 0,00</x-badge>
        @endif
      </li>
    </ul>
  </x-card>

  <x-card>
    <h2 class="text-lg font-semibold mb-4">Últimos Pedidos</h2>
    @if(isset($customer->orders) && $customer->orders->isEmpty())
      <div class="text-center py-8">
        <i class="fas fa-shopping-cart text-4xl text-gray-400 mb-4"></i>
        <p class="text-gray-500 text-lg">Nenhum pedido encontrado.</p>
        <p class="text-gray-400 text-sm mt-2">Este cliente ainda não fez nenhum pedido.</p>
      </div>
    @else
      <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
          <thead>
            <tr class="bg-gray-50 text-left text-sm font-medium text-gray-600">
              <th class="px-4 py-3">Pedido</th>
              <th class="px-4 py-3">Data</th>
              <th class="px-4 py-3">Total</th>
              <th class="px-4 py-3">Status</th>
            </tr>
          </thead>
          <tbody class="text-sm text-gray-700">
            @foreach($customer->orders ?? [] as $order)
              <tr class="border-t hover:bg-gray-50">
                <td class="px-4 py-3 font-medium">#{{ $order->id }}</td>
                <td class="px-4 py-3">{{ $order->created_at->format('d/m/Y H:i') }}</td>
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
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </x-card>
</div>
@endsection