@extends('layouts.app')

{{-- CSS desta página --}}
<link rel="stylesheet" href="{{ asset('css/pages/pedido-detalhe.css') }}?v=8">

@section('content')
<div class="order-page"><!-- RAIZ PARA ESCOPO DO CSS -->

  <div class="op-header">
    <div>
      <h1>Pedido <span>#{{ $order->order_number }}</span></h1>
      <p>Realizado: {{ \Carbon\Carbon::parse($order->created_at)->format('d/m/Y H:i') }}</p>
    </div>
    <div class="op-actions">
      <a href="{{ route('dashboard.orders.index') }}" class="btn-ghost">← Voltar</a>
      <a href="javascript:window.print()" class="btn-ghost">🖨️ Imprimir</a>
      <a target="_blank"
         href="https://wa.me/{{ preg_replace('/\D/','',$order->phone ?? '') }}?text={{ urlencode('Olá! Sobre o pedido '.$order->order_number) }}"
         class="btn-cta">WhatsApp</a>
    </div>
  </div>

  {{-- Itens --}}
  <div class="op-card">
    <div class="op-card-title">Itens do Pedido</div>
    <table class="op-table">
      <thead>
        <tr>
          <th>Produto</th>
          <th class="r">Qtd</th>
          <th class="r">Preço</th>
          <th class="r">Total</th>
        </tr>
      </thead>
      <tbody>
        @foreach($items as $it)
          <tr>
            <td>{{ $it->product_name ?? $it->name ?? '—' }}</td>
            <td class="r">{{ (float)$it->qty }}</td>
            <td class="r">R$ {{ number_format($it->price,2,',','.') }}</td>
            <td class="r"><strong>R$ {{ number_format($it->total,2,',','.') }}</strong></td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  {{-- Cliente & Endereço + Resumo --}}
  <div class="grid-2">
    <div class="op-card">
      <div class="op-card-title">Cliente & Endereço</div>
      <ul class="op-list">
        <li><strong>Cliente</strong><span>{{ $order->customer_name ?? '—' }}</span></li>
        <li><strong>Telefone</strong><span>{{ $order->phone ?? '—' }}</span></li>
        <li><strong>E-mail</strong><span>{{ $order->email ?? '—' }}</span></li>
        <li><strong>Endereço</strong><span>{{ $order->address ?? '—' }}</span></li>
      </ul>
    </div>

    <div class="op-card">
      <div class="op-card-title">Resumo</div>
      <ul class="op-summary">
        <li><span>Subtotal</span><strong>R$ {{ number_format($order->subtotal,2,',','.') }}</strong></li>
        <li><span>Desconto</span><strong>R$ {{ number_format($order->discount ?? 0,2,',','.') }}</strong></li>
        <li><span>Entrega</span><strong>R$ {{ number_format($order->delivery_fee ?? 0,2,',','.') }}</strong></li>
        <li class="total"><span>Total</span><strong>R$ {{ number_format($order->final_amount,2,',','.') }}</strong></li>
      </ul>
    </div>
  </div>

  {{-- Status & Pagamento --}}
  <div class="grid-2">
    <div class="op-card">
      <div class="op-card-title">Status</div>
      <div class="status-badges">
        @php
          $statusMap = ['pending'=>'Pendente','confirmed'=>'Confirmado','preparing'=>'Preparando','delivered'=>'Entregue','canceled'=>'Cancelado'];
          $payMap    = ['pending'=>'Pendente', 'paid'=>'Pago', 'refunded'=>'Estornado'];
        @endphp
        <span class="badge badge-gray">{{ $statusMap[$order->status] ?? ucfirst($order->status ?? '—') }}</span>
        <span class="badge badge-blue">{{ $payMap[$order->payment_status] ?? ucfirst($order->payment_status ?? '—') }}</span>
      </div>

      <form class="op-form" action="{{ route('dashboard.orders.meta',$order->id) }}" method="post">
        @csrf
        <label>Status do pedido</label>
        <select name="status" class="inp">
          @foreach($statusMap as $k=>$v)
            <option value="{{ $k }}" @selected($order->status===$k)>{{ $v }}</option>
          @endforeach
        </select>

        <label>Status do pagamento</label>
        <select name="payment_status" class="inp">
          @foreach($payMap as $k=>$v)
            <option value="{{ $k }}" @selected($order->payment_status===$k)>{{ $v }}</option>
          @endforeach
        </select>

        <button class="btn-cta">Atualizar</button>
      </form>
    </div>

    <div class="op-card">
      <div class="op-card-title">Pagamento</div>
      <ul class="op-list">
        <li><strong>Método</strong><span>{{ strtoupper($order->payment_method ?? '—') }}</span></li>
        <li><strong>Status</strong><span>{{ ucfirst($order->payment_status ?? '—') }}</span></li>
        @if(!empty($order->transaction_id))
          <li><strong>Transação</strong><span>{{ $order->transaction_id }}</span></li>
        @endif
      </ul>
    </div>
  </div>

</div>
@endsection