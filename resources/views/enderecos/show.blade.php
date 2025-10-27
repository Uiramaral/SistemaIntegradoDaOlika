@extends('layouts.dashboard')

@section('title','Endereço #'.$endereco->id)

@section('page-title','Endereço')

@section('page-subtitle', $endereco->nome_destinatario.' — '.$endereco->cliente->nome)

@section('page-actions')
  <form method="POST" action="{{ route('enderecos.destroy',$endereco) }}" onsubmit="return confirm('Remover endereço?');">
    @csrf @method('DELETE')
    <button class="pill">Remover</button>
  </form>
  <a href="{{ route('enderecos.edit',$endereco) }}" class="btn-primary">Editar</a>
@endsection

@section('stat-cards')
  <x-stat-card label="Padrão" :value="$endereco->padrao ? 'Sim' : 'Não'" />
  <x-stat-card label="Distância (km)" :value="$endereco->distancia_km ?? '—'" />
  <x-stat-card label="Taxa base" :value="$endereco->taxa_base ? ('R$ '.number_format($endereco->taxa_base,2,',','.')) : '—'" />
@endsection

@section('content')
  <div class="grid gap-4">
    <div class="card p-4">
      <div class="font-semibold mb-2">Endereço</div>
      <div class="text-sm text-neutral-500">{{ $endereco->endereco }} {{ $endereco->numero ? ', '.$endereco->numero : '' }} {{ $endereco->complemento ? ' — '.$endereco->complemento : '' }}</div>
      <div class="text-sm text-neutral-500">{{ $endereco->bairro }} — {{ $endereco->cidade }}/{{ $endereco->uf }} — CEP {{ $endereco->cep }}</div>
      <div class="mt-2">
        <a class="pill" target="_blank" href="https://www.google.com/maps/search/?api=1&query={{ urlencode($endereco->endereco.' '.$endereco->numero.' '.$endereco->bairro.' '.$endereco->cidade) }}">Abrir no Maps</a>
      </div>
    </div>
  </div>
@endsection
