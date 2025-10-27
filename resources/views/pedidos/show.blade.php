@extends('layouts.dashboard')

@section('title','Pedido #'.$pedido->id)

@section('page-title','Pedido #'.$pedido->id)

@section('page-subtitle', $pedido->cliente->nome)

@section('page-actions')
  <a href="{{ route('pedidos.edit',$pedido) }}" class="btn-primary">Editar</a>
@endsection

@section('stat-cards')
  <x-stat-card label="Status" :value="ucfirst($pedido->status)" />
  <x-stat-card label="Entrega" :value="optional($pedido->data_entrega)->format('d/m H:i') ?? '—'" />
  <x-stat-card label="Total" :value="'R$ '.number_format($pedido->total,2,',','.')" />
@endsection

@section('content')
  <div class="grid gap-4">
    <div class="card p-4">
      <div class="font-semibold mb-2">Cliente & Endereço</div>
      <div class="text-sm text-neutral-500">{{ $pedido->cliente->nome }} — {{ $pedido->cliente->telefone ?? '—' }}</div>
      <div class="flex items-center gap-2">
        <div class="text-sm text-neutral-500">{{ $pedido->cliente->endereco_formatado ?? $pedido->cliente->endereco ?? '—' }}</div>
        <x-map-link :href="optional($pedido->cliente)->maps_url" mode="full" label="Abrir no Maps" />
      </div>
    </div>

    <div class="card p-4">
      <div class="font-semibold mb-2">Linha do tempo</div>
      <ol class="text-sm grid gap-2">
        @foreach($timeline as $t)
          <li>
            <span class="pill">{{ $t['label'] }}</span>
            <span class="text-neutral-500 ml-2">{{ $t['at']?->format('d/m/Y H:i') ?? '—' }}</span>
          </li>
        @endforeach
      </ol>
    </div>

    <div class="card overflow-hidden">
      <div class="px-4 py-3 border-b font-semibold">Itens</div>
      @include('pedidos._itens', ['pedido'=>$pedido])
    </div>

    @if($pedido->observacoes)
    <div class="card p-4">
      <div class="font-semibold mb-2">Observações</div>
      <div class="text-sm text-neutral-500">{{ $pedido->observacoes }}</div>
    </div>
    @endif
  </div>
@endsection
