@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@section('content')
<div class="container-page">
  <div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">Dashboard</h1>
    <div class="text-sm text-gray-500">{{ Auth::user()->name ?? 'Admin' }} <i class="fas fa-bell ml-3"></i></div>
  </div>

  @if(session('status'))
    <x-alert type="success">{{ session('status') }}</x-alert>
  @endif

  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <x-card-metric color="orange" value="{{ $totalPedidos ?? 0 }}" label="Total de Pedidos" />
    <x-card-metric color="green" value="R$ {{ number_format($faturamento ?? 0, 2, ',', '.') }}" label="Faturamento" />
    <x-card-metric color="blue" value="{{ $novosClientes ?? 0 }}" label="Novos Clientes" />
    <x-card-metric color="purple" value="R$ {{ number_format($ticketMedio ?? 0, 2, ',', '.') }}" label="Ticket Médio" />
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <x-card>
      <h3 class="text-lg font-semibold mb-4">Pedidos por Status</h3>
      <div class="space-y-2">
        @foreach($statusPedidos ?? [] as $status => $quantidade)
          <div class="flex justify-between">
            <span>{{ ucfirst($status) }}</span>
            <x-badge type="info">{{ $quantidade }}</x-badge>
          </div>
        @endforeach
      </div>
    </x-card>

    <x-card>
      <h3 class="text-lg font-semibold mb-4">Pedidos de Hoje</h3>
      @if(isset($pedidosHoje) && count($pedidosHoje) > 0)
        <ul class="space-y-2">
          @foreach($pedidosHoje as $pedido)
            <li class="text-sm text-gray-700">#{{ $pedido->id }} - {{ $pedido->cliente->nome ?? 'Cliente' }} - R$ {{ number_format($pedido->total ?? 0, 2, ',', '.') }}</li>
          @endforeach
        </ul>
      @else
        <p class="text-center text-gray-500">Nenhum pedido hoje</p>
      @endif
    </x-card>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
    <x-card>
      <h3 class="text-lg font-semibold mb-4">Produtos Mais Vendidos</h3>
      @if(isset($maisVendidos) && count($maisVendidos) > 0)
        <ul class="space-y-2">
          @foreach($maisVendidos as $produto)
            <li class="text-sm text-gray-700">{{ $produto->nome ?? $produto->name }} - {{ $produto->quantidade ?? $produto->sales_count ?? 0 }} vendidos</li>
          @endforeach
        </ul>
      @else
        <p class="text-center text-gray-500">Nenhum dado disponível</p>
      @endif
    </x-card>

    <x-card>
      <h3 class="text-lg font-semibold mb-4">Clientes Ativos</h3>
      @if(isset($clientesAtivos) && count($clientesAtivos) > 0)
        <ul class="space-y-2">
          @foreach($clientesAtivos as $cliente)
            <li class="text-sm text-gray-700">{{ $cliente->nome ?? $cliente->name }}</li>
          @endforeach
        </ul>
      @else
        <p class="text-center text-gray-500">Nenhum cliente ativo</p>
      @endif
    </x-card>
  </div>
</div>
@endsection