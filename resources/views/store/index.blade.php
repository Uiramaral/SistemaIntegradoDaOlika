{{-- resources/views/store/index.blade.php --}}
@extends('layouts.app')

@section('title', $store->name ?? 'Olika')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/olika-store.css') }}">
@endpush

@section('content')
<div class="header-bar">
  <a href="{{ url('/') }}"><img src="{{ asset('images/logo-olika.png') }}" alt="Olika" style="height:28px;width:auto"></a>
  <a href="{{ route('cart.index') }}" class="header-cart">Meu Carrinho<span class="badge">{{ $cartCount ?? 0 }}</span></a>
</div>

<div class="store-shell" data-default-view="{{ $defaultView ?? 'grid2' }}" @if(isset($category)) data-category-id="{{ $category->id }}" @endif>
  <div class="store-toolbar">
    <div class="store-chips">
      <a class="store-chip {{ request('category') ? '' : 'active' }}" href="{{ route('menu.index') }}">Todos</a>
      @foreach($categories as $cat)
        <a class="store-chip {{ request('category') == $cat->id ? 'active' : '' }}" href="{{ route('menu.category', $cat->id) }}">{{ $cat->name }}</a>
      @endforeach
    </div>
    <div class="view-switch">
      <button class="view-btn" data-mode="grid2">2 col</button>
      <button class="view-btn" data-mode="list">Lista</button>
    </div>
  </div>

  {{-- GRID 2 COL --}}
  <div class="products-grid">
    @foreach($products as $p)
    <article class="product-card"
      data-id="{{ $p->id }}"
      data-title="{{ $p->name }}"
      data-price-formatted="R$ {{ number_format($p->price, 2, ',', '.') }}"
      data-image="{{ $p->image_url ?? asset('images/placeholder.jpg') }}"
      data-description="{{ $p->description }}">
      <div class="product-media">
        <img src="{{ $p->image_url ?? asset('images/placeholder.jpg') }}" alt="{{ $p->name }}">
      </div>
      <div class="product-content">
        <h3 class="product-title">{{ $p->name }}</h3>
        @if(!empty($p->short_description))
          <div class="product-desc">{{ $p->short_description }}</div>
        @endif
        <div class="card-actions">
          <span class="product-price">R$ {{ number_format($p->price, 2, ',', '.') }}</span>
          <button class="add-btn" onclick="window.addToCartQuick({{ $p->id }})">+</button>
        </div>
      </div>
    </article>
    @endforeach
  </div>

  {{-- LISTA --}}
  <div class="products-list" style="display:none">
    @foreach($products as $p)
    <article class="product-card"
      data-id="{{ $p->id }}"
      data-title="{{ $p->name }}"
      data-price-formatted="R$ {{ number_format($p->price, 2, ',', '.') }}"
      data-image="{{ $p->image_url ?? asset('images/placeholder.jpg') }}"
      data-description="{{ $p->description }}">
      <div class="product-media">
        <img src="{{ $p->image_url ?? asset('images/placeholder.jpg') }}" alt="{{ $p->name }}">
      </div>
      <div class="product-content">
        <h3 class="product-title">{{ $p->name }}</h3>
        @if(!empty($p->short_description))
          <div class="product-desc">{{ $p->short_description }}</div>
        @endif
        <div class="card-actions">
          <span class="product-price">R$ {{ number_format($p->price, 2, ',', '.') }}</span>
          <div>
            <button class="add-btn" onclick="window.addToCartQuick({{ $p->id }})">Adicionar</button>
          </div>
        </div>
      </div>
    </article>
    @endforeach
  </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/olika-store.js') }}"></script>
<script>
window.addToCartQuick = async function(productId){
  try {
    const res = await fetch(`/pedido/cart/add`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      body: JSON.stringify({ product_id: productId, quantity: 1 })
    });
    if(!res.ok) throw new Error('404');
    const data = await res.json().catch(()=>({}));
    const badge = document.querySelector('.header-cart .badge');
    if(badge && typeof data.cart_count !== 'undefined') badge.textContent = data.cart_count;
  } catch(e){
    console.error(e);
    alert('Rota pedido/cart/add indisponível. Verifique as rotas.');
  }
};
</script>
@endpush
