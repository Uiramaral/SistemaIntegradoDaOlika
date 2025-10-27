{{-- PÁGINA: Detalhes do Cliente (Visualização Individual) --}}
@extends('layouts.dashboard')

@section('title','Cliente #'.$customer->id.' — Dashboard Olika')

@section('content')

<div class="card">
  <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
    <h1 class="text-xl" style="font-weight:800;margin:0">👤 Cliente #{{ $customer->id }}</h1>
    <a class="btn btn-outline" href="{{ route('debts.index', $customer->id) }}">Fiados</a>
  </div>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:20px">
    <div class="card" style="background:#f9fafb">
      <strong>Nome:</strong><br>{{ $customer->name }}
    </div>
    <div class="card" style="background:#f9fafb">
      <strong>Telefone:</strong><br>{{ $customer->phone }}
    </div>
    <div class="card" style="background:#f9fafb">
      <strong>Email:</strong><br>{{ $customer->email ?? '—' }}
    </div>
    <div class="card" style="background:#f9fafb">
      <strong>CPF:</strong><br>{{ $customer->cpf ?? '—' }}
    </div>
  </div>

  <h2 style="font-weight:600;margin:20px 0 12px">📦 Pedidos ({{ $orders->total() }})</h2>

  <table style="width:100%">
    <thead><tr><th>#</th><th>Total</th><th>Status</th><th>Data</th><th></th></tr></thead>
    <tbody>
      @forelse($orders as $o)
        <tr>
          <td>#{{ $o->order_number ?? $o->id }}</td>
          <td>R$ {{ number_format($o->final_amount ?? $o->total_amount ?? 0,2,',','.') }}</td>
          <td><span class="badge">{{ $o->status }}</span></td>
          <td>{{ \Carbon\Carbon::parse($o->created_at)->format('d/m/Y H:i') }}</td>
          <td><a href="{{ route('dashboard.orders.show', $o->order_number ?? $o->id) }}" class="badge">Ver Pedido</a></td>
        </tr>
      @empty
        <tr><td colspan="5" style="text-align:center;padding:20px">Nenhum pedido</td></tr>
      @endforelse
    </tbody>
  </table>

  <div style="margin-top:12px">{{ $orders->links() }}</div>

  <div style="margin-top:16px">
    <a href="{{ route('dashboard.customers') }}" class="btn">← Voltar</a>
  </div>
</div>

@endsection

