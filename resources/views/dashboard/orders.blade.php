{{-- PÁGINA: Pedidos (Listagem de Pedidos/Ordens) --}}
@extends('layouts.dashboard')

@section('title','Pedidos — Dashboard Olika')

@section('content')

<div class="card">

  <h1 class="text-xl" style="font-weight:800;margin-bottom:10px">Pedidos</h1>

  <table>

    <thead><tr><th>#</th><th>Cliente</th><th>Total</th><th>Status</th><th>Pgto</th><th>Quando</th><th></th></tr></thead>

    <tbody>

      @foreach($orders as $o)

        <tr>

          <td>#{{ $o->order_number ?? $o->number ?? $o->id }}</td>

          <td>{{ $o->customer_name ?? '—' }}</td>

          <td>R$ {{ number_format($o->final_amount ?? $o->total_amount ?? 0,2,',','.') }}</td>

          <td><span class="badge">{{ $o->status }}</span></td>

          <td>{{ $o->payment_status ?? '—' }}</td>

          <td>{{ \Carbon\Carbon::parse($o->created_at)->format('d/m H:i') }}</td>

          <td><a class="badge" href="{{ route('dashboard.orders.show',$o->order_number ?? $o->id) }}">abrir</a></td>

        </tr>

      @endforeach

    </tbody>

  </table>

  <div style="margin-top:10px">{{ $orders->links() }}</div>

</div>

@endsection

