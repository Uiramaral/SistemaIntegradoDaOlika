@extends('layouts.dashboard')

@section('title','Endereços')

@section('page-title','Endereços de Clientes')

@section('page-actions')
  <a href="{{ route('enderecos.create') }}" class="btn-primary">Novo Endereço</a>
@endsection

@section('stat-cards')
  <x-stat-card label="Total" :value="$stats['total'] ?? 0" />
  <x-stat-card label="Padrões" :value="$stats['padroes'] ?? 0" />
  <x-stat-card label="Clientes c/ endereço" :value="$stats['clientes_com_endereco'] ?? 0" />
@endsection

@section('content')
  <div class="card overflow-hidden">
    <div class="px-4 py-3 border-b flex items-center justify-between">
      <div class="font-semibold">Catálogo de endereços</div>
      <form method="GET" class="flex items-center gap-2">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Buscar cliente/destinatário/CEP…" class="pill" />
        <input type="text" name="bairro" value="{{ request('bairro') }}" placeholder="Bairro" class="pill" />
        <input type="text" name="cidade" value="{{ request('cidade') }}" placeholder="Cidade" class="pill" />
        <button class="pill">Filtrar</button>
      </form>
    </div>
    @include('enderecos._table', ['enderecos' => $enderecos])
  </div>
@endsection
