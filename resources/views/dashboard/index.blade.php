@extends('layouts.dashboard')

@section('title','Visão Geral — Dashboard Olika')

@section('content')

<div class="card">

  <h1 class="text-xl" style="font-weight:800;margin-bottom:10px">Visão Geral</h1>

  <div class="kpi">

    <div class="card"><div>Total Hoje</div><div style="font-size:24px;font-weight:800">R$ {{ number_format($kpis['revenue_today'],2,',','.') }}</div></div>

    <div class="card"><div>Pedidos Hoje</div><div style="font-size:24px;font-weight:800">{{ $kpis['orders_today'] }}</div></div>

    <div class="card"><div>Pagos Hoje</div><div style="font-size:24px;font-weight:800">{{ $kpis['paid_today'] }}</div></div>

    <div class="card"><div>Pendentes Pgto</div><div style="font-size:24px;font-weight:800">{{ $kpis['waiting_payment'] }}</div></div>

  </div>

</div>



<div class="card" style="margin-top:16px">

  <div class="flex" style="justify-content:space-between">

    <h2 style="font-weight:600">Pedidos Recentes</h2>

    <a class="badge" href="{{ route('dashboard.orders') }}">ver todos</a>

  </div>

  <table>

    <thead><tr><th>#</th><th>Cliente</th><th>Total</th><th>Status</th><th>Pgto</th><th>Quando</th><th></th></tr></thead>

    <tbody>

      @foreach($recentOrders as $o)

        <tr>

          <td>#{{ $o->order_number ?? $o->number ?? $o->id }}</td>

          <td>{{ $o->customer_name }}</td>

          <td>R$ {{ number_format($o->total_amount ?? $o->total ?? 0,2,',','.') }}</td>

          <td><span class="badge">{{ $o->status }}</span></td>

          <td>{{ $o->payment_status ?? '—' }}</td>

          <td>{{ \Carbon\Carbon::parse($o->created_at)->format('d/m H:i') }}</td>

          <td><a class="badge" href="{{ route('dashboard.orders.show',$o->id) }}">abrir</a></td>

        </tr>

      @endforeach

    </tbody>

  </table>

</div>



<div class="card" style="margin-top:16px">

  <h2 style="font-weight:600;margin-bottom:8px">Top Produtos (últimos 7 dias)</h2>

  <table>

    <thead><tr><th>Produto</th><th>Qtd</th><th>Receita</th></tr></thead>

    <tbody>

      @foreach($topProducts as $p)

        <tr><td>{{ $p->product_name }}</td><td>{{ $p->qty }}</td><td>R$ {{ number_format($p->revenue,2,',','.') }}</td></tr>

      @endforeach

    </tbody>

  </table>

</div>

@endsection

