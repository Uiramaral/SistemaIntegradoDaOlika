@extends('layouts.dashboard')

@section('title','Produtos')

@section('content')
<div class="page products-page">

  <div class="pp-header">
    <div>
      <h1>Produtos</h1>
      <p class="muted">Gerencie o cardápio da Olika</p>
    </div>
    <a href="{{ route('dashboard.products.create') }}" class="btn btn-primary">+ Novo Produto</a>
  </div>

  <div class="pp-search">
    <span class="ico">🔍</span>
    <input id="pp-q" class="input" type="text" placeholder="Buscar por nome ou SKU…"
           value="{{ request('q') }}" data-live-search>
  </div>

  @if($products->count())
    <div class="pp-grid">
      @foreach($products as $p)
        <article class="pp-card">

          {{-- IMAGEM QUADRADA GRANDE --}}
          <div class="pp-thumb"
               style="background-image:url('{{ $p->cover_image ? asset('storage/'.$p->cover_image) : asset('img/product-fallback.svg') }}')"></div>

          <div class="pp-body">
            <div class="pp-top">
              <h3 title="{{ $p->name }}">{{ Str::limit($p->name, 28) }}</h3>
              <span class="pp-badge {{ $p->is_active ? 'ok' : 'off' }}">
                {{ $p->is_active ? 'Ativo' : 'Inativo' }}
              </span>
            </div>

            <div class="pp-tags">
              @if($p->category)
                <span class="tag cat">{{ $p->category->name }}</span>
              @endif

              {{-- Selo "Sem glúten" com ícone --}}
              @if($p->gluten_free)
                <span class="tag gluten">
                  <svg class="ico-gf" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M12 2a1 1 0 0 1 .9.56l1.3 2.6-2.6 2.6L9.44 5.2 11.1 2.6A1 1 0 0 1 12 2Zm7.78 3.17a1 1 0 0 1 0 1.42l-13 13a1 1 0 1 1-1.42-1.42l13-13a1 1 0 0 1 1.42 0ZM7.5 8.5l2.6 2.6-2.6 2.6a6.5 6.5 0 0 1 0-5.2Zm9 0a6.5 6.5 0 0 1 0 5.2l-2.6-2.6 2.6-2.6Z"/>
                  </svg>
                  Sem glúten
                </span>
              @endif
            </div>

            <div class="pp-price">
              <strong>R$ {{ number_format($p->price,2,',','.') }}</strong>
              <span class="muted">Estoque: {{ $p->stock ?? 0 }}</span>
            </div>

            {{-- Aviso de contaminação cruzada (mostra se não estiver explicitamente desativado) --}}
            @if(($p->contamination_risk ?? 1) == 1)
              <small class="warn">⚠️ Pode conter traços de glúten devido ao ambiente de produção.</small>
            @endif

            {{-- Descrição automática de alérgenos --}}
            @if($p->allergen_text)
              <div class="allergen-info">
                <small>{{ $p->allergen_text }}</small>
              </div>
            @endif
          </div>

          <footer class="pp-actions">
            <a href="{{ route('dashboard.products.edit',$p->id) }}" class="btn btn-soft">Editar</a>

            {{-- Toggle rápido Ativar/Inativar --}}
            <form method="POST" action="{{ route('dashboard.products.toggle',$p->id) }}" class="inline-form">
              @csrf
              @method('PATCH')
              <button type="submit" class="btn {{ $p->is_active ? 'btn-gray' : 'btn-primary' }}">
                {{ $p->is_active ? 'Desativar' : 'Ativar' }}
              </button>
            </form>
          </footer>
        </article>
      @endforeach
    </div>

    <div class="pp-paginate">
      {{ $products->onEachSide(1)->withQueryString()->links() }}
    </div>
  @else
    <div class="pp-empty">
      <p>Nenhum produto encontrado.</p>
      <a href="{{ route('dashboard.products.create') }}" class="btn btn-primary">Cadastrar primeiro produto</a>
    </div>
  @endif

</div>
@endsection