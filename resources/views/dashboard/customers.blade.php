@extends('layouts.dashboard')

@section('title','Clientes — Dashboard Olika')

@section('page-title','Clientes')

@section('page-subtitle','Gerencie sua base de clientes')

@section('content')

@if(session('ok'))<div class="badge" style="background:#d1fae5;color:#065f46;margin-bottom:12px">{{ session('ok') }}</div>@endif
@if(session('error'))<div class="badge" style="background:#fee2e2;color:#991b1b;margin-bottom:12px">{{ session('error') }}</div>@endif

<div class="toolbar">
  <div class="grow">
    <h1 class="page-title">Clientes</h1>
    <p class="page-sub">Gerencie sua base de clientes</p>
  </div>
  <div class="actions">
    <a class="btn ghost" href="{{ route('dashboard.index') }}">Voltar</a>
    <a class="btn primary" href="{{ route('dashboard.customers.create') }}">+ Novo Cliente</a>
  </div>
</div>

<div class="card">
  <div class="input-search" style="margin-bottom:12px;">
    <svg class="icon" width="18" height="18" viewBox="0 0 24 24"><path d="M21 21l-4.35-4.35" stroke="#999" stroke-width="1.5" fill="none"/><circle cx="10.5" cy="10.5" r="7" stroke="#999" stroke-width="1.5" fill="none"/></svg>
    <input class="inp" placeholder="Buscar clientes..." name="q" value="{{ request('q') }}">
  </div>

  <table class="table">
    <thead>
      <tr>
        <th>Cliente</th>
        <th>Contato</th>
        <th class="cell-right">Pedidos</th>
        <th class="cell-right">Total Gasto</th>
        <th class="cell-right">Ações</th>
      </tr>
    </thead>
    <tbody>
      @forelse($customers as $c)
      <tr>
        <td>
          <div style="display:flex; align-items:center; gap:10px;">
            <div class="avatar">{{ strtoupper(substr($c->name ?? 'N/A', 0, 2)) }}</div>
            <div>
              <div style="font-weight:800;">{{ $c->name ?? 'N/A' }}</div>
              <div class="muted">{{ $c->email ?? '—' }}</div>
            </div>
          </div>
        </td>
        <td class="muted">{{ $c->phone ?? '—' }}</td>
        <td class="cell-right" style="font-weight:800;">{{ $c->total_orders ?? $c->orders_count ?? 0 }}</td>
        <td class="cell-right" style="font-weight:800;">R$ {{ number_format($c->total_spent ?? 0,2,',','.') }}</td>
        <td class="cell-right">
          <a class="link" href="{{ route('dashboard.customers.show', $c->id) }}">Ver perfil</a>
        </td>
      </tr>
      @empty
      <tr>
        <td colspan="5" class="muted" style="text-align:center; padding:40px;">
          Nenhum cliente cadastrado
        </td>
      </tr>
      @endforelse
    </tbody>
  </table>

  @if(method_exists($customers, 'links'))
  <div style="margin-top:12px;">{{ $customers->links() }}</div>
  @endif
</div>

@endsection