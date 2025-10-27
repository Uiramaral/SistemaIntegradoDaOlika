{{-- PÁGINA: Dashboard Principal (Visão Geral) --}}
@extends('layouts.dashboard')

@section('title', 'Visão Geral')

@section('content')

<div class="container-fluid">
  {{-- Título + descrição (sem botão Baixar Layout) --}}
  <div class="d-flex align-items-start justify-content-between mb-3">
    <div>
      <h2 class="mb-1" style="font-weight: 700;">Visão Geral</h2>
      <div class="text-muted">Acompanhe suas métricas e desempenho em tempo real</div>
    </div>
  </div>

  {{-- Cards do topo (4 colunas) --}}
  <div class="row g-3 mb-4">
    <div class="col-12 col-md-3">
      <div class="card h-100 shadow-sm border-0" style="border-radius: 14px;">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="text-muted small">Total Hoje</div>
            <span class="small">💵</span>
          </div>
          <div class="fs-3 fw-bold">R$ {{ number_format($totalHoje ?? 0, 2, ',', '.') }}</div>
        </div>
      </div>
    </div>
    <div class="col-12 col-md-3">
      <div class="card h-100 shadow-sm border-0" style="border-radius: 14px;">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="text-muted small">Pedidos Hoje</div>
            <span class="small">🧾</span>
          </div>
          <div class="fs-3 fw-bold">{{ $pedidosHoje ?? 0 }}</div>
        </div>
      </div>
    </div>
    <div class="col-12 col-md-3">
      <div class="card h-100 shadow-sm border-0" style="border-radius: 14px;">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="text-muted small">Pagos Hoje</div>
            <span class="small">✔️</span>
          </div>
          <div class="fs-3 fw-bold">{{ $pagosHoje ?? 0 }}</div>
        </div>
      </div>
    </div>
    <div class="col-12 col-md-3">
      <div class="card h-100 shadow-sm border-0" style="border-radius: 14px;">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="text-muted small">Pendentes Pgto</div>
            <span class="small">🕒</span>
          </div>
          <div class="fs-3 fw-bold">{{ $pendentesPg ?? 0 }}</div>
        </div>
      </div>
    </div>
  </div>

  {{-- Caixas: Pedidos Recentes / Top Produtos --}}
  <div class="row g-3">
    <div class="col-12 col-lg-6">
      <div class="card h-100 shadow-sm border-0" style="border-radius: 14px;">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <div>
              <h5 class="mb-0 fw-bold">Pedidos Recentes</h5>
              <div class="text-muted small">Últimos pedidos realizados</div>
            </div>
            <a href="{{ route('dashboard.orders') }}" class="text-decoration-none small">Ver todos</a>
          </div>

          @if(empty($pedidosRecentes) || $pedidosRecentes->isEmpty())
            <div class="text-center text-muted py-4">
              <div class="fs-2 mb-2">🛍️</div>
              Nenhum pedido registrado ainda
            </div>
          @else
            <div class="list-group list-group-flush">
              @foreach($pedidosRecentes as $o)
                <div class="list-group-item px-0 d-flex justify-content-between">
                  <div>
                    <div class="fw-semibold">#{{ $o->order_number ?? $o->id }} • {{ optional($o->customer)->name ?? 'Cliente' }}</div>
                    <div class="text-muted small">{{ $o->created_at?->format('d/m/Y H:i') }} • {{ ucfirst($o->payment_status ?? 'pending') }}</div>
                  </div>
                  <div class="fw-bold">R$ {{ number_format($o->final_amount ?? 0,2,',','.') }}</div>
                </div>
              @endforeach
            </div>
          @endif
        </div>
      </div>
    </div>
    <div class="col-12 col-lg-6">
      <div class="card h-100 shadow-sm border-0" style="border-radius: 14px;">
        <div class="card-body">
          <div class="mb-2">
            <h5 class="mb-0 fw-bold">Top Produtos</h5>
            <div class="text-muted small">Últimos 7 dias</div>
          </div>

          @if(empty($topProdutos) || $topProdutos->isEmpty())
            <div class="text-center text-muted py-4">
              <div class="fs-2 mb-2">💲</div>
              Nenhum produto vendido ainda
            </div>
          @else
            <div class="list-group list-group-flush">
              @foreach($topProdutos as $tp)
                <div class="list-group-item px-0 d-flex justify-content-between align-items-center">
                  <div class="d-flex align-items-center gap-3">
                    @php $p = $tp->product; @endphp
                    @if($p && isset($p->image_url) && !empty($p->image_url))
                      <img src="{{ $p->image_url }}" alt="{{ $p->name }}" width="40" height="40" class="rounded" style="object-fit:cover;">
                    @else
                      <div class="bg-gray-200 d-flex align-items-center justify-content-center" style="width:40px;height:40px;border-radius:8px;">
                        <span>🍞</span>
                      </div>
                    @endif
                    <div>
                      <div class="fw-semibold">{{ $p->name ?? 'Produto' }}</div>
                      <div class="text-muted small">Qtd: {{ $tp->qty }} • Receita: R$ {{ number_format($tp->revenue,2,',','.') }}</div>
                    </div>
                  </div>
                  <div class="text-muted small">R$ {{ isset($p) ? number_format($p->price,2,',','.') : '-' }}</div>
                </div>
              @endforeach
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>

@endsection
