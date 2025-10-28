@extends('layouts.dashboard')

@section('content')
<div class="orders-page" data-new-url="{{ route('dashboard.pdv') }}">
  <div class="op-header">
    <h1>Pedidos</h1>
    <p>Gerencie todos os pedidos do restaurante</p>
    <div class="op-actions">
      <a href="{{ route('dashboard.pdv') }}" class="btn btn-primary">+ Novo Pedido</a>
    </div>
  </div>

  <div class="card op-card">
    <div class="op-search">
      <span class="ico">🔍</span>
      <input id="order-search" type="text" class="input" placeholder="Buscar por cliente, número do pedido...">
    </div>

    <div class="table-wrapper">
      <table class="table" id="orders-table">
        <thead>
          <tr>
            <th style="width:100px">#</th>
            <th>Cliente</th>
            <th style="width:140px" class="t-right">Total</th>
            <th style="width:140px">Status</th>
            <th style="width:140px">Pagamento</th>
            <th style="width:160px">Quando</th>
            <th style="width:120px" class="t-right">Ações</th>
          </tr>
        </thead>
        <tbody>
          @forelse($orders as $o)
            @php
              $statusMap = [
                'pending' => ['Pendente', 'gray'],
                'confirmed' => ['Confirmado', 'blue'],
                'preparing' => ['Preparando', 'gray'],
                'delivered' => ['Entregue', 'green'],
              ];
              $payMap = [
                'pending' => ['Pendente', 'gray'],
                'paid' => ['Pago', 'blue'],
              ];
              [$statusLabel, $statusClass] = $statusMap[$o->status] ?? [$o->status, 'gray'];
              [$payLabel, $payClass] = $payMap[$o->payment_status] ?? [$o->payment_status, 'gray'];
            @endphp
            <tr data-search="{{ strtolower($o->order_number . ' ' . ($o->customer_name ?? '') . ' ' . $statusLabel . ' ' . $payLabel) }}">
              <td>{{ $o->order_number }}</td>
              <td>{{ $o->customer_name ?? '—' }}</td>
              <td class="t-right">R$ {{ number_format($o->final_amount, 2, ',', '.') }}</td>
              <td><span class="badge badge-{{ $statusClass }}">{{ $statusLabel }}</span></td>
              <td><span class="badge badge-{{ $payClass }}">{{ $payLabel }}</span></td>
              <td>{{ \Carbon\Carbon::parse($o->created_at)->isoFormat('DD/MM/YYYY HH:mm') }}</td>
              <td class="t-right">
                <a class="link" href="{{ route('dashboard.orders.show', $o->id) }}">Ver detalhes</a>
              </td>
            </tr>
          @empty
            <tr><td colspan="7" class="empty">Nenhum pedido encontrado</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
  
  <div data-debug="lovable-orders-v2"></div>
</div>
@endsection