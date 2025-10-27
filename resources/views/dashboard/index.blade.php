@extends('layouts.dashboard')

@section('title', 'Dashboard')
@section('page-title', 'Status & Produção')
@section('page-subtitle', 'Resumo geral de pedidos, entregas e cupons ativos')

@section('quick-filters')
  <form method="GET" action="{{ route('dashboard.index') }}" style="display:flex;gap:8px">
    <select name="periodo" class="pill" style="padding:6px 12px">
      <option value="hoje" @selected(request('periodo')==='hoje')>Hoje</option>
      <option value="semana" @selected(request('periodo')==='semana')>Semana</option>
      <option value="mes" @selected(request('periodo')==='mes')>Mês</option>
    </select>
    <select name="status" class="pill" style="padding:6px 12px">
      <option value="">Todos</option>
      <option value="waiting_payment" @selected(request('status')==='waiting_payment')>Aguardando</option>
      <option value="paid" @selected(request('status')==='paid')>Pago</option>
      <option value="preparing" @selected(request('status')==='preparing')>Preparando</option>
      <option value="delivered" @selected(request('status')==='delivered')>Entregue</option>
    </select>
    <button type="submit" class="pill" style="padding:6px 12px;background:var(--color-primary);color:#fff;border:none">Aplicar</button>
  </form>
@endsection

@section('page-actions')
  <a href="{{ route('dashboard.pdv') }}" class="btn-primary">➕ Novo Pedido</a>
  <a href="{{ route('relatorios.index') }}" class="pill">📊 Relatórios</a>
  <a href="{{ route('dashboard.orders') }}" class="pill">Ver Todos</a>
@endsection

@section('stat-cards')
  <x-stat-card label="Pedidos Hoje" :value="$kpis['orders_today'] ?? 0" hint="Hoje" :delta="'+8%'" />
  <x-stat-card label="Pagos Hoje" :value="$kpis['paid_today'] ?? 0" hint="Confirmados" />
  <x-stat-card label="Receita Hoje" :value="'R$ '.number_format($kpis['revenue_today'] ?? 0,2,',','.')" hint="Total" />
  <x-stat-card label="Aguardando" :value="$kpis['waiting_payment'] ?? 0" hint="Pagamento" />
@endsection

@section('content')
  {{-- Tabela compacta estilo status-templates --}}
  <div class="card" style="padding:0">
    <div style="padding:16px;border-bottom:1px solid var(--color-border);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
      <div style="font-weight:600">Pedidos Recentes</div>
      <form method="GET" style="display:flex;align-items:center;gap:8px">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Buscar cliente…" style="padding:6px 12px;border-radius:8px;border:1px solid var(--color-border);font-size:14px;min-width:180px" />
        <button type="submit" class="pill" style="padding:6px 12px">Buscar</button>
      </form>
    </div>
    <div class="table-wrapper">
      <table class="table-compact">
        <thead>
          <tr>
            <th>#</th>
            <th>Cliente</th>
            <th>Status</th>
            <th>Total</th>
            <th>Criado</th>
            <th style="text-align:right">Ações</th>
          </tr>
        </thead>
        <tbody>
          @forelse($recentOrders as $order)
          <tr>
            <td>#{{ $order->number ?? $order->id }}</td>
            <td><strong>{{ $order->customer_name ?? '-' }}</strong></td>
            <td><span class="pill">{{ ucfirst($order->status ?? 'pending') }}</span></td>
            <td>R$ {{ number_format($order->total ?? 0,2,',','.') }}</td>
            <td>{{ $order->created_at ? \Carbon\Carbon::parse($order->created_at)->format('d/m H:i') : '-' }}</td>
            <td style="text-align:right">
              <a href="{{ route('dashboard.orders.show', $order->id) }}" class="pill" style="padding:4px 8px">Ver</a>
              <x-map-link :href="optional($order->customer)->maps_url" mode="icon" size="sm" />
            </td>
          </tr>
          @empty
          <tr><td colspan="6" class="empty-state">Sem registros no período.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
@endsection