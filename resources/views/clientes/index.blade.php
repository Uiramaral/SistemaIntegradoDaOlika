@extends('layouts.dashboard')

@section('title','Clientes')

@section('page-title','Clientes')

@section('page-actions')
  <a href="{{ route('clientes.create') }}" class="btn-primary">Novo Cliente</a>
@endsection

@section('stat-cards')
  <x-stat-card label="Total" :value="$stats['total'] ?? 0" />
  <x-stat-card label="Com pedidos (30d)" :value="$stats['pedidos_30d'] ?? 0" />
@endsection

@section('content')
  <div class="card overflow-hidden">
    <div class="px-4 py-3 border-b flex items-center justify-between">
      <div class="font-semibold">Lista de clientes</div>
      <form method="GET" class="flex items-center gap-2">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Buscar por nome, telefone ou e-mail…" class="pill" />
        <button class="pill">Buscar</button>
      </form>
    </div>
    @include('clientes._table', ['clientes' => $clientes])
  </div>
@endsection
