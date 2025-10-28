@extends('layouts.dashboard')

@section('title', 'Produtos')

@section('content')

<div id="products-page" class="page-products container-xl">

  <div class="lp-header">
    <div>
      <h1>Produtos</h1>
      <p>Gerencie o cardápio do seu restaurante</p>
    </div>
    <div class="lp-actions">
      <a href="{{ route('dashboard.products.create') }}" class="btn lp-primary">+ Novo Produto</a>
    </div>
  </div>

  <div class="board-card">
    <div class="lp-search">
      <span class="ico">🔍</span>
      <input class="lp-input" type="text" placeholder="Buscar produtos...">
    </div>

    {{-- GRADE DE CARDS --}}
    <div class="grid-cards">
      @forelse($products as $p)
        @php $ativo = (bool)($p->active ?? $p->is_active ?? true); @endphp
        <div class="card product-card">
          <div class="pc-head">
            <div class="pc-icon">🍞</div>
            @if($ativo)
              <span class="pc-badge">Ativo</span>
            @endif
          </div>
          <div class="pc-body">
            <div class="pc-title">{{ $p->name ?? $p->nome }}</div>
            <div class="pc-sub">{{ $p->category?->name ?? $p->categoria?->nome ?? '—' }}</div>
            <div class="pc-meta">
              <strong>R$ {{ number_format($p->price ?? $p->preco ?? 0, 2, ',', '.') }}</strong>
              <span>Estoque: {{ $p->stock ?? $p->estoque ?? 0 }}</span>
            </div>
          </div>
          <div class="pc-actions">
            <a href="{{ route('dashboard.products.edit', $p->id) }}" class="btn btn-soft">Editar</a>
          </div>
        </div>
      @empty
        <div class="empty">
          <div class="empty-ico">📦</div>
          <div class="empty-text">Nenhum produto cadastrado ainda</div>
        </div>
      @endforelse
    </div>
  </div>

</div>

@endsection