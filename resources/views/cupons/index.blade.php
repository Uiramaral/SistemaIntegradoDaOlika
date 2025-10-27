@extends('layouts.dashboard')

@section('title','Cupons')

@section('page-title','Cupons de Desconto')

@section('page-actions')
  <a href="{{ route('cupons.create') }}" class="btn-primary">Novo Cupom</a>
@endsection

@section('stat-cards')
  <x-stat-card label="Total" :value="$stats['total'] ?? 0" />
  <x-stat-card label="Ativos" :value="$stats['ativos'] ?? 0" />
  <x-stat-card label="Válidos hoje" :value="$stats['validos_hoje'] ?? 0" />
@endsection

@section('content')
  <div class="card overflow-hidden">
    <div class="px-4 py-3 border-b flex items-center justify-between">
      <div class="font-semibold">Lista de cupons</div>
      <form method="GET" class="flex items-center gap-2">
        <select name="status" class="pill">
          <option value="">Todos</option>
          <option value="ativo" @selected(request('status')==='ativo')>Ativo</option>
          <option value="inativo" @selected(request('status')==='inativo')>Inativo</option>
          <option value="validos" @selected(request('status')==='validos')>Válidos</option>
          <option value="expirados" @selected(request('status')==='expirados')>Expirados</option>
          <option value="futuros" @selected(request('status')==='futuros')>Futuros</option>
        </select>
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Buscar por código/descrição…" class="pill" />
        <button class="pill">Filtrar</button>
      </form>
    </div>
    @include('cupons._table', ['cupons' => $cupons])
  </div>
@endsection
