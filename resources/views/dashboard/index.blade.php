{{-- PÁGINA: Dashboard Principal (Visão Geral) --}}
@extends('layouts.dashboard')

@section('title', 'Visão Geral')

@section('content')

<div class="overview-page" 
     data-stats-url="{{ \Illuminate\Support\Facades\Route::has('admin.dashboard.stats') ? route('admin.dashboard.stats') : '' }}">

  {{-- Título + botão (como no Lovable) --}}
  <div class="ov-header">
    <div>
      <h1>Visão Geral</h1>
      <p>Acompanhe suas métricas e desempenho em tempo real</p>
    </div>
    <div class="ov-actions">
      @if (\Illuminate\Support\Facades\Route::has('dashboard.layout.download'))
        <a class="btn btn-soft" href="{{ route('dashboard.layout.download') }}">Baixar Layout</a>
      @endif
    </div>
  </div>

  {{-- GRID de 4 cards (Total, Pedidos Hoje, Pagos Hoje, Pendentes) --}}
  <div class="ov-kpis">
    <div class="kpi-card">
      <div class="kpi-title">Total Hoje</div>
      <div class="kpi-value" id="kpi-total">{{ isset($stats['totalHoje']) ? 'R$ '.number_format($stats['totalHoje'],2,',','.') : 'R$ '.number_format($totalHoje ?? 0,2,',','.') }}</div>
    </div>

    <div class="kpi-card">
      <div class="kpi-title">Pedidos Hoje</div>
      <div class="kpi-value" id="kpi-pedidos">{{ $stats['pedidosHoje'] ?? $pedidosHoje ?? 0 }}</div>
    </div>

    <div class="kpi-card">
      <div class="kpi-title">Pagos Hoje</div>
      <div class="kpi-value" id="kpi-pagos">{{ $stats['pagosHoje'] ?? $pagosHoje ?? 0 }}</div>
    </div>

    <div class="kpi-card">
      <div class="kpi-title">Pendentes Pgto</div>
      <div class="kpi-value" id="kpi-pendentes">{{ $stats['pendentesHoje'] ?? $pendentesPg ?? 0 }}</div>
    </div>
  </div>

  {{-- 2 colunas: Pedidos Recentes (esq) | Top Produtos (dir) --}}
  <div class="ov-panels">
    {{-- Pedidos Recentes --}}
    <section class="panel">
      <div class="panel-head">
        <div>
          <h3>Pedidos Recentes</h3>
          <span class="muted">Últimos pedidos realizados</span>
        </div>
        @if (\Illuminate\Support\Facades\Route::has('dashboard.orders'))
          <a href="{{ route('dashboard.orders') }}" class="link">Ver todos</a>
        @endif
      </div>

      @if(!empty($pedidosRecentes) && count($pedidosRecentes))
        <ul class="orders-list">
          @foreach($pedidosRecentes as $o)
            <li class="order-item">
              <div class="order-id">#{{ $o->order_number ?? $o->id }}</div>
              <div class="order-customer">{{ $o->customer_name ?? optional($o->customer)->name ?? '—' }}</div>
              <div class="order-status">{{ $o->status_label ?? $o->status ?? '—' }}</div>
              <div class="order-total">R$ {{ number_format($o->final_amount ?? 0,2,',','.') }}</div>
            </li>
          @endforeach
        </ul>
      @else
        <div class="empty">
          <div class="empty-ico">🛍️</div>
          <div class="empty-text">Nenhum pedido registrado ainda</div>
        </div>
      @endif
    </section>

    {{-- Top Produtos --}}
    <section class="panel">
      <div class="panel-head">
        <div>
          <h3>Top Produtos</h3>
          <span class="muted">Últimos 7 dias</span>
        </div>
      </div>

      @if(!empty($topProdutos) && count($topProdutos))
        <ul class="tops-list">
          @foreach($topProdutos as $tp)
            @php $p = $tp->product ?? null; @endphp
            <li class="top-item">
              <div class="top-img">
                @if($p && !empty($p->image_url))
                  <img src="{{ $p->image_url }}" alt="{{ $p->name }}">
                @else
                  <div class="ph"></div>
                @endif
              </div>
              <div class="top-info">
                <div class="top-name">{{ $p->name ?? 'Produto' }}</div>
                <div class="top-meta">
                  Qtd: {{ $tp->qty ?? 0 }} • Receita: R$ {{ number_format($tp->revenue ?? 0,2,',','.') }}
                </div>
              </div>
              <div class="top-price">R$ {{ number_format(($p->price ?? 0),2,',','.') }}</div>
            </li>
          @endforeach
        </ul>
      @else
        <div class="empty">
          <div class="empty-ico">💲</div>
          <div class="empty-text">Nenhum produto vendido ainda</div>
        </div>
      @endif
    </section>
  </div>
</div>

@endsection

@push('scripts')
<script>
(function(){
  const root = document.querySelector('.overview-page');
  const url  = root?.dataset?.statsUrl;

  if(!url) return;

  const money = v => (new Intl.NumberFormat('pt-BR',{style:'currency',currency:'BRL'})).format(Number(v||0));

  fetch(url, {headers:{'Accept':'application/json'}})
    .then(r => r.ok ? r.json() : null)
    .then(j => {
      if(!j) return;
      if(j.totalHoje !== undefined) document.getElementById('kpi-total').textContent = money(j.totalHoje);
      if(j.pedidosHoje !== undefined) document.getElementById('kpi-pedidos').textContent = j.pedidosHoje;
      if(j.pagosHoje !== undefined) document.getElementById('kpi-pagos').textContent = j.pagosHoje;
      if(j.pendentesHoje !== undefined) document.getElementById('kpi-pendentes').textContent = j.pendentesHoje;
    })
    .catch(()=>{});
})();
</script>
@endpush
