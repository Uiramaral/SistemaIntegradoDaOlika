@extends('layouts.dashboard')

@section('title','Relatórios — Dashboard Olika')

@section('content')

<div class="card">

  <h1 class="text-xl" style="font-weight:800;margin-bottom:10px">Relatórios (últimos 30 dias)</h1>

  <div class="kpi">

    <div class="card"><div>Receita Total</div><div style="font-size:24px;font-weight:800">R$ {{ number_format($sales,2,',','.') }}</div></div>

    <div class="card"><div>Pedidos</div><div style="font-size:24px;font-weight:800">{{ $count }}</div></div>

    <div class="card"><div>Ticket Médio</div><div style="font-size:24px;font-weight:800">R$ {{ number_format($aov,2,',','.') }}</div></div>

  </div>

</div>



<div class="card" style="margin-top:16px">

  <h2 style="font-weight:600;margin-bottom:8px">Top Produtos Vendidos</h2>

  <table>

    <thead><tr><th>Produto</th><th>Quantidade</th></tr></thead>

    <tbody>

      @foreach($top as $t)

        <tr><td>{{ $t->product_name }}</td><td>{{ $t->qty }}</td></tr>

      @endforeach

    </tbody>

  </table>

</div>

@endsection

