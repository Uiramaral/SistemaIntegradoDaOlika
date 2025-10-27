@extends('layouts.dashboard')

@section('title','Produtos')

@section('page-title','Produtos')

@section('page-actions')
  <a href="{{ route('produtos.create') }}" class="btn-primary">Novo Produto</a>
@endsection

@section('stat-cards')
  <x-stat-card label="Total" :value="$stats['total'] ?? 0" />
  <x-stat-card label="Ativos" :value="$stats['ativos'] ?? 0" />
@endsection

@section('content')
  <div class="card overflow-hidden">
    <div class="px-4 py-3 border-b flex items-center justify-between">
      <div class="font-semibold">Lista de produtos</div>
      <form method="GET" class="flex items-center gap-2">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Buscar por nome ou SKU…" class="pill" />
        <button class="pill">Buscar</button>
      </form>
    </div>
    @include('produtos._table', ['produtos' => $produtos])
  </div>
@endsection
