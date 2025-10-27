{{-- resources/views/dashboard/statuses.blade.php --}}
@extends('layouts.app')

@section('title', 'Visão Geral')

@section('content')
  <div class="px-6 py-6">

    {{-- Título + subtítulo --}}
    <div class="mb-6">
      <h1 class="text-3xl font-semibold tracking-tight text-gray-900">Visão Geral</h1>
      <p class="text-gray-500">Acompanhe suas métricas e desempenho em tempo real</p>
    </div>

    {{-- Cards de métricas --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-6">
      <div class="rounded-xl border border-gray-200 bg-white p-4">
        <div class="text-sm text-gray-500 mb-1">Total Hoje</div>
        <div class="text-3xl font-semibold text-gray-900">R$ {{ number_format($stats['total_hoje'] ?? 0,2,',','.') }}</div>
      </div>
      <div class="rounded-xl border border-gray-200 bg-white p-4">
        <div class="text-sm text-gray-500 mb-1">Pedidos Hoje</div>
        <div class="text-3xl font-semibold text-gray-900">{{ $stats['pedidos_hoje'] ?? 0 }}</div>
      </div>
      <div class="rounded-xl border border-gray-200 bg-white p-4">
        <div class="text-sm text-gray-500 mb-1">Pagos Hoje</div>
        <div class="text-3xl font-semibold text-gray-900">{{ $stats['pagos_hoje'] ?? 0 }}</div>
      </div>
      <div class="rounded-xl border border-gray-200 bg-white p-4">
        <div class="text-sm text-gray-500 mb-1">Pendentes Pgto</div>
        <div class="text-3xl font-semibold text-gray-900">{{ $stats['pendentes'] ?? 0 }}</div>
      </div>
    </div>

    {{-- Painéis principais --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
      {{-- Pedidos Recentes --}}
      <div class="rounded-2xl border border-gray-200 bg-white">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
          <div>
            <h2 class="text-xl font-semibold text-gray-900">Pedidos Recentes</h2>
            <p class="text-sm text-gray-500 -mt-0.5">Últimos pedidos realizados</p>
          </div>
          <a href="{{ route('dashboard.orders') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">Ver todos</a>
        </div>

        <div class="px-5 py-12">
          @if(isset($pedidos_recentes) && $pedidos_recentes->count() > 0)
            <div class="grid gap-3">
              @foreach($pedidos_recentes as $p)
              <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50">
                <div>
                  <div class="font-semibold text-gray-900">#{{ $p->id }}</div>
                  <div class="text-sm text-gray-500">{{ $p->customer->name ?? 'Cliente' }}</div>
                </div>
                <div class="text-right">
                  <div class="font-semibold text-gray-900">R$ {{ number_format($p->final_amount ?? $p->total ?? 0,2,',','.') }}</div>
                  <div class="text-sm text-gray-500">{{ $p->status }}</div>
                </div>
              </div>
              @endforeach
            </div>
          @else
            <div class="flex flex-col items-center justify-center text-gray-400">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mb-3" viewBox="0 0 24 24" fill="currentColor"><path d="M19 7h-4V3H9v4H5v14h14V7Zm-6 0H11V5h2v2Z"/></svg>
              <div class="text-sm text-gray-500">Nenhum pedido registrado ainda</div>
            </div>
          @endif
        </div>
      </div>

      {{-- Top Produtos --}}
      <div class="rounded-2xl border border-gray-200 bg-white">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
          <div>
            <h2 class="text-xl font-semibold text-gray-900">Top Produtos</h2>
            <p class="text-sm text-gray-500 -mt-0.5">Últimos 7 dias</p>
          </div>
        </div>

        <div class="px-5 py-12">
          @if(isset($top_produtos) && $top_produtos->count() > 0)
            <div class="grid gap-3">
              @foreach($top_produtos as $prod)
              <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50">
                <div>
                  <div class="font-semibold text-gray-900">{{ $prod->product->name ?? $prod->nome ?? 'Produto' }}</div>
                  <div class="text-sm text-gray-500">{{ $prod->qtd ?? 0 }} vendidos</div>
                </div>
                <div class="text-right">
                  <div class="font-semibold text-gray-900">R$ {{ number_format($prod->receita ?? 0,2,',','.') }}</div>
                </div>
              </div>
              @endforeach
            </div>
          @else
            <div class="flex flex-col items-center justify-center text-gray-400">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mb-3" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2 1 21h22L12 2Zm0 4.84L19.53 19H4.47L12 6.84Z"/></svg>
              <div class="text-sm text-gray-500">Nenhum produto vendido ainda</div>
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>
@endsection