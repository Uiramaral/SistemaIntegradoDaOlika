@extends('layouts.dashboard')

@section('title','Entregas')

@section('page-title','Entregas')

@section('page-subtitle','Agenda e status das entregas por pedido')

@section('quick-filters')
  <form method="GET" action="{{ route('entregas.index') }}" class="flex gap-2">
    <input type="date" name="dia" value="{{ request('dia', $dia->format('Y-m-d')) }}" class="pill" />
    <select name="janela" class="pill">
      <option value="">Todas janelas</option>
      <option value="manha" @selected(request('janela')==='manha')>Manhã</option>
      <option value="tarde" @selected(request('janela')==='tarde')>Tarde</option>
      <option value="noite" @selected(request('janela')==='noite')>Noite</option>
    </select>
    <select name="status" class="pill">
      <option value="">Todos</option>
      <option value="pendente" @selected(request('status')==='pendente')>Pendentes</option>
      <option value="entrega" @selected(request('status')==='entrega')>Em preparação/saída</option>
      <option value="rota" @selected(request('status')==='rota')>Em rota</option>
      <option value="concluido" @selected(request('status')==='concluido')>Entregues</option>
      <option value="atrasado" @selected(request('status')==='atrasado')>Atrasados</option>
    </select>
    <input type="search" name="q" value="{{ request('q') }}" placeholder="Buscar por cliente ou #pedido" class="pill" />
    <button class="pill">Filtrar</button>
  </form>
@endsection

@section('stat-cards')
  <x-stat-card label="A entregar" :value="$stats['a_entregar'] ?? 0" hint="Hoje" />
  <x-stat-card label="Em rota" :value="$stats['em_rota'] ?? 0" hint="Atual" />
  <x-stat-card label="Entregues" :value="$stats['entregues'] ?? 0" hint="Dia" />
  <x-stat-card label="Atrasados" :value="$stats['atrasados'] ?? 0" hint="Até agora" />
@endsection

@section('content')
  <div class="card overflow-hidden">
    <div class="px-4 py-3 border-b font-semibold">Agenda do dia — {{ $dia->format('d/m/Y') }}</div>
    @include('entregas._table', ['entregas' => $entregas])
  </div>
@endsection
