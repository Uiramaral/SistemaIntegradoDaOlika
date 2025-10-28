@extends('layouts.dashboard')
@section('content')
<div class="page">
  <div class="page-header">
    <div>
      <h1>Pedidos</h1>
      <p class="muted">Gerencie todos os pedidos do restaurante</p>
    </div>
    <div class="actions">
      <a href="{{ route('dashboard.pdv') }}" class="btn btn-primary">+ Novo Pedido</a>
    </div>
  </div>

  <div class="card">
    <form class="search-wrapper" method="get">
      <input class="input" type="search" name="q" value="{{ $q }}" placeholder="Buscar por cliente, número do pedido...">
    </form>

    <div class="table-wrapper">
      <table class="table">
        <thead>
          <tr>
            <th style="width:72px">#</th>
            <th>Cliente</th>
            <th class="t-right">Total</th>
            <th>Status</th>
            <th>Pagamento</th>
            <th>Quando</th>
            <th class="t-right">Ações</th>
          </tr>
        </thead>
        <tbody>
          @forelse($orders as $o)
            <tr>
              <td>{{ $o->order_number }}</td>
              <td>{{ $o->customer_name ?? '—' }}</td>
              <td class="t-right">R$ {{ number_format($o->final_amount,2,',','.') }}</td>
              <td>
                @php
                  $map = [
                    'pending'    => ['Pendente','badge-soft'],
                    'confirmed'  => ['Confirmado','badge-info'],
                    'preparing'  => ['Preparando','badge-warn'],
                    'delivered'  => ['Entregue','badge-success'],
                    'canceled'   => ['Cancelado','badge-danger'],
                  ];
                  [$label,$cls] = $map[$o->status] ?? [$o->status,'badge-soft'];
                @endphp
                <span class="badge {{ $cls }}">{{ $label }}</span>
              </td>
              <td>
                @php
                  $pmap = [
                    'pending' => ['Pendente','badge-soft'],
                    'paid'    => ['Pago','badge-info'],
                    'refunded'=> ['Estornado','badge-danger'],
                  ];
                  [$plabel,$pcls] = $pmap[$o->payment_status] ?? [$o->payment_status,'badge-soft'];
                @endphp
                <span class="badge {{ $pcls }}">{{ $plabel }}</span>
              </td>
              <td>{{ \Carbon\Carbon::parse($o->created_at)->format('d/m/Y H:i') }}</td>
              <td class="t-right"><a class="btn btn-soft" href="{{ route('dashboard.orders.show',$o->id) }}">Ver detalhes</a></td>
            </tr>
          @empty
            <tr><td colspan="7" class="t-center muted">Nenhum pedido encontrado.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="pagination mt-3">
      {{ $orders->links() }}
    </div>
  </div>
</div>
@endsection
