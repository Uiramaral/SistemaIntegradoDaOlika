@extends('layouts.dashboard')

@section('title','Compacto — Dashboard Olika')

@section('content')

<div class="card">

  <h1 class="text-xl" style="font-weight:800;margin-bottom:10px">Hoje</h1>

  <div class="kpi">

    <div class="card"><div>Pedidos</div><div style="font-size:24px;font-weight:800">{{ $kpis['orders_today'] }}</div></div>

    <div class="card"><div>Total</div><div style="font-size:24px;font-weight:800">R$ {{ number_format($kpis['revenue_today'],2,',','.') }}</div></div>

    <div class="card"><div>Pagos</div><div style="font-size:24px;font-weight:800">{{ $kpis['paid_today'] }}</div></div>

    <div class="card"><div>À pagar</div><div style="font-size:24px;font-weight:800">{{ $kpis['waiting_payment'] }}</div></div>

  </div>

</div>



<div class="card" style="margin-top:16px">

  <div class="flex" style="justify-content:space-between">

    <h2 style="font-weight:600">Fila de Pedidos (hoje)</h2>

    <a class="badge" href="{{ route('dashboard.orders') }}">ver todos</a>

  </div>

  <table>

    <thead><tr><th>#</th><th>Cliente</th><th>Total</th><th>Status</th><th>Ação Rápida</th><th>WhatsApp</th></tr></thead>

    <tbody>

      @foreach($todayOrders as $o)

        <tr>

          <td>#{{ $o->order_number ?? $o->number ?? $o->id }}</td>

          <td>{{ $o->customer_name }}</td>

          <td>R$ {{ number_format($o->total_amount ?? $o->total ?? 0,2,',','.') }}</td>

          <td><span class="badge">{{ $o->status }}</span></td>

          <td>

            <form method="POST" action="{{ route('dashboard.orders.status',$o->id) }}" class="flex">

              @csrf

              <select name="status_code" style="max-width:200px">

                @foreach($statuses as $s)

                  <option value="{{ $s->code }}" {{ ($o->status ?? '')===$s->code?'selected':'' }}>{{ $s->name }}</option>

                @endforeach

              </select>

              <button class="btn">OK</button>

            </form>

          </td>

          <td>

            @if(isset($o->customer_phone) && $o->customer_phone)

              <a class="badge" target="_blank" href="https://wa.me/{{ preg_replace('/\D/','',$o->customer_phone) }}?text={{ urlencode('Olá! Aqui é da Olika. Sobre o pedido #'.($o->order_number ?? $o->number ?? $o->id).':') }}">abrir chat</a>

            @else — @endif

          </td>

        </tr>

      @endforeach

    </tbody>

  </table>

</div>

@endsection

