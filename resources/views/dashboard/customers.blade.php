@extends('layouts.dashboard')

@section('title', 'Clientes')

@section('content')

<div class="clients-page">

  <!-- Header no estilo Lovable: sem "Voltar", botão à direita -->

  <div class="lp-header">

    <div>

      <h1>Clientes</h1>

      <p>Gerencie sua base de clientes</p>

    </div>

    <div class="lp-actions">

      <a href="{{ route('dashboard.customers.create') }}" class="btn lp-primary">+ Novo Cliente</a>

    </div>

  </div>



  <div class="lp-card">

    <!-- Search dentro do card -->

    <div class="lp-search">

      <span class="ico">🔍</span>

      <input id="client-search" type="text" class="lp-input" placeholder="Buscar clientes...">

    </div>



    <div class="lp-table-wrap">

      <table class="lp-table" id="clients-table">

        <thead>

          <tr>

            <th>Cliente</th>

            <th style="width:220px">Contato</th>

            <th style="width:120px" class="t-center">Pedidos</th>

            <th style="width:160px" class="t-right">Total Gasto</th>

            <th style="width:160px" class="t-right">Ações</th>

          </tr>

        </thead>

        <tbody>

          @forelse($customers as $c)

            @php

              $name   = $c->name ?? '—';

              $email  = $c->email ?? '—';

              $phone  = $c->phone ?? $c->telefone ?? null;



              $ordersCount = (int)($c->orders_count ?? 0);

              $spent       = number_format((float)($c->total_spent ?? 0), 2, ',', '.');



              // saldo fiado/open (vindo do controller, ver seção 2)

              $debtOpen = (float)($c->debt_open ?? $c->fiado_balance ?? 0);



              // iniciais para o avatar

              $parts = preg_split('/\s+/', trim($name));

              $ini   = strtoupper(mb_substr($parts[0] ?? '',0,1) . mb_substr($parts[1] ?? '',0,1));



              $search = Str::slug($name.' '.$email.' '.$phone, ' ');

            @endphp



            <tr data-search="{{ $search }}">

              <td>

                <div class="lp-client">

                  <div class="lp-avatar">{{ $ini ?: 'C' }}</div>

                  <div class="lp-info">

                    <div class="lp-name">{{ $name }}</div>

                    <div class="lp-sub">{{ $email }}</div>

                  </div>

                </div>

              </td>

              <td>

                <span class="lp-sub">{{ $phone ? $phone : '—' }}</span>

              </td>

              <td class="t-center"><strong>{{ $ordersCount }}</strong></td>

              <td class="t-right"><strong>R$ {{ $spent }}</strong></td>

              <td class="t-right">

                <a class="lp-link" href="{{ route('dashboard.customers.show', $c->id) }}">Ver perfil</a>

                @if($debtOpen > 0)

                  <a class="btn lp-soft mini" href="{{ route('debts.index', $c->id) }}">Fiados</a>

                @endif

              </td>

            </tr>

          @empty

            <tr>

              <td colspan="5">

                <div class="lp-empty">

                  <div class="ico">👤</div>

                  <div class="txt">Nenhum cliente cadastrado ainda</div>

                </div>

              </td>

            </tr>

          @endforelse

        </tbody>

      </table>

    </div>

  </div>



</div>

@endsection

@push('scripts')

<script>

(function(){

  const input = document.getElementById('client-search');

  const rows  = Array.from(document.querySelectorAll('#clients-table tbody tr'));

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