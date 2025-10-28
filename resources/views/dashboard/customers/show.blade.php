@extends('layouts.app')
@section('content')
<div class="ol-card">
  <div class="ol-card__title">Cliente — Perfil</div>
  <form action="{{ route('dashboard.customers.update',$c->id) }}" method="post" class="ol-grid ol-grid--4">
    @csrf
    <input name="name" class="ol-input" value="{{ $c->name }}" placeholder="Nome">
    <input name="email" class="ol-input" value="{{ $c->email }}" placeholder="E-mail">
    <input name="phone" class="ol-input" value="{{ $c->phone }}" placeholder="Telefone (E164)">
    <input name="exclusive_coupon_code" class="ol-input" value="{{ $c->exclusive_coupon_code }}" placeholder="Cupom exclusivo">
    <input name="cashback_balance" class="ol-input" value="{{ $c->cashback_balance }}" placeholder="Cashback (R$)">
    <input name="credit_balance" class="ol-input" value="{{ $c->credit_balance }}" placeholder="Fiado em aberto (R$)">
    <input name="credit_limit" class="ol-input" value="{{ $c->credit_limit }}" placeholder="Limite de fiado (R$)">
    <div style="grid-column:1/-1; display:flex; justify-content:flex-end">
      <button class="ol-cta">Salvar</button>
    </div>
  </form>

  <div class="ol-card__title mt-3">Últimos pedidos</div>
  <table class="ol-table">
    <thead><tr><th>#</th><th>Total</th><th>Status</th><th>Pagamento</th><th>Quando</th><th></th></tr></thead>
    <tbody>
      @foreach($orders as $o)
      <tr>
        <td>{{ $o->order_number }}</td>
        <td>R$ {{ number_format($o->final_amount,2,',','.') }}</td>
        <td>{{ $o->status }}</td>
        <td>{{ $o->payment_status }}</td>
        <td>{{ \Carbon\Carbon::parse($o->created_at)->format('d/m/Y H:i') }}</td>
        <td><a href="{{ route('dashboard.orders.show',$o->id) }}" class="ol-btn">Ver</a></td>
      </tr>
      @endforeach
    </tbody>
  </table>
</div>
@endsection
