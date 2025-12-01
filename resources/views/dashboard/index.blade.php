@extends('layouts.dashboard')

@section('title', 'Visão Geral')

@section('content')
<div class="grid gap-4 grid-cols-2 md:grid-cols-4">
  @include('components.dashboard.card', [
      'title' => 'Receita Hoje',
      'value' => 'R$ 0,00',
      'subtitle' => 'Pedidos pagos no dia',
      'icon' => 'wallet'
  ])
  @include('components.dashboard.card', [
      'title' => 'Pedidos Hoje',
      'value' => '0',
      'subtitle' => 'Totais criados nas últimas 24h',
      'icon' => 'shopping-bag'
  ])
  @include('components.dashboard.card', [
      'title' => 'Pagos Hoje',
      'value' => '0',
      'subtitle' => 'Sem pedidos hoje',
      'icon' => 'circle-check'
  ])
  @include('components.dashboard.card', [
      'title' => 'Pendentes de Pagamento',
      'value' => '0',
      'subtitle' => 'Acompanhe estes pedidos de perto',
      'icon' => 'clock'
  ])
</div>

<div class="grid gap-4 lg:grid-cols-2 mt-6">
  @include('components.dashboard.box', ['title' => 'Pedidos Recentes', 'subtitle' => 'Últimos pedidos criados na plataforma'])
  @include('components.dashboard.box', ['title' => 'Top Produtos', 'subtitle' => 'Desempenho nos últimos 7 dias'])
  @include('components.dashboard.box', ['title' => 'Pedidos Agendados', 'subtitle' => 'Próximas entregas com horário definido'])
  @include('components.dashboard.box', ['title' => 'Status dos Pedidos', 'subtitle' => 'Situação atual da operação'])
</div>
@endsection
