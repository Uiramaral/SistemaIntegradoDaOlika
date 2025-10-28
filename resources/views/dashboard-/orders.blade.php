@extends('layouts.dashboard')

@section('title', 'Pedidos')

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

              $code   = $o->order_number ?? $o->id;

              $cliente= $o->customer_name ?? optional($o->customer)->name ?? '—';

              $total  = number_format($o->final_amount ?? $o->total_amount ?? 0, 2, ',', '.');

              $status = $o->status_label ?? $o->status ?? '—';

              $pgto   = $o->payment_status_label ?? $o->payment_status ?? '—';

              $quando = isset($o->created_at)

                        ? (\Carbon\Carbon::parse($o->created_at)->isToday()

                            ? 'Hoje, '.\Carbon\Carbon::parse($o->created_at)->format('H:i')

                            : \Carbon\Carbon::parse($o->created_at)->format('d/m/Y H:i'))

                        : '—';



              $clsStatus = match(strtolower($status)){

                'entregue','delivered'   => 'badge-green',

                'em preparo','preparing' => 'badge-orange',

                'confirmado','confirmed' => 'badge-blue',

                default => 'badge-gray',

              };

              $clsPay = match(strtolower($pgto)){

                'pago','paid' => 'badge-blue',

                'pendente','pending' => 'badge-gray',

                default => 'badge-gray',

              };

            @endphp



            <tr data-search="{{ Str::slug($code.' '.$cliente.' '.$status.' '.$pgto,' ') }}">

              <td>{{ str_pad($loop->iteration,3,'0',STR_PAD_LEFT) }}</td>

              <td>{{ $cliente }}</td>

              <td class="t-right">R$ {{ $total }}</td>

              <td><span class="badge {{ $clsStatus }}">{{ ucfirst($status) }}</span></td>

              <td><span class="badge {{ $clsPay }}">{{ ucfirst($pgto) }}</span></td>

              <td>{{ $quando }}</td>

              <td class="t-right">

                <a class="link" href="{{ route('dashboard.orders.show', $code) }}">Ver detalhes</a>

              </td>

        </tr>

          @empty

            <tr><td colspan="7">

              <div class="empty">

                <div class="empty-ico">🛒</div>

                <div class="empty-text">Nenhum pedido registrado ainda</div>

              </div>

            </td></tr>

          @endforelse

    </tbody>

  </table>

    </div>

  </div>



  {{-- marcador rápido para conferir no código-fonte --}}

  <div data-debug="lovable-orders-v2"></div>

</div>

@endsection



@push('scripts')

<script>

(function(){

  const input = document.getElementById('order-search');

  const rows  = Array.from(document.querySelectorAll('#orders-table tbody tr'));

  const norm  = s => (s||'').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,'');

  input?.addEventListener('input', (e)=>{

    const q = norm(e.target.value.trim());

    rows.forEach(tr=>{

      const v = norm(tr.getAttribute('data-search') || tr.textContent);

      tr.style.display = v.indexOf(q) >= 0 ? '' : 'none';

    });

  });

})();

</script>

@endpush

