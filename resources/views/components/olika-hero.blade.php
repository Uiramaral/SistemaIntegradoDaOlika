{{-- HERO full-width com logo central e carrinho dentro do banner --}}
<section class="header-hero full-bleed"
  style="--hero:url('{{ $cover ?? asset('images/hero-breads.jpg') }}'); background-image:var(--hero);">

  <div class="hero-inner">

    {{-- Carrinho dentro do banner (canto direito) --}}
    <div class="hero-topbar">
      <a href="{{ route('cart.index') }}" class="cart-link" style="position:relative;">
        <span>Meu Carrinho</span>
        <span class="cart-badge" data-cart-count="{{ session('cart_count', 0) }}">{{ session('cart_count', 0) }}</span>
      </a>
    </div>

    {{-- Cabeça central --}}
    <div class="hero-head">
      <div class="hero-logo">
        <img src="{{ asset('images/logo-olika.png') }}" alt="Olika" style="height:70px;width:auto;display:block;">
      </div>
      <div class="hero-title">{{ $store_name ?? 'Olika' }}</div>
      <div class="hero-sub">{{ $category_label ?? 'Pães • Artesanais' }}</div>
      <div class="status-green">{{ ($is_open ?? true) ? 'Aberto Agora' : 'Fechado' }}</div>
    </div>

    {{-- Superfície clara SOMENTE com cupons (card de informações REMOVIDO) --}}
    <div class="soft-surface single">
      <h3 style="margin:0 0 12px 0;font-size:1.05rem;">Cupons Disponíveis</h3>
      <div class="coupons-grid">
        <div class="card-soft" style="padding:14px;">
          <div class="badge-soft" style="margin-bottom:6px;">🎉 BEM-VINDO</div>
          <div class="meta"><b>10% OFF</b> na primeira compra</div>
        </div>
        <div class="card-soft" style="padding:14px;">
          <div class="badge-soft" style="margin-bottom:6px;">🚚 Frete Grátis</div>
          <div class="meta">Em pedidos acima de R$ 100</div>
        </div>
      </div>

      {{-- Categorias e toolbar --}}
      <div class="cat-toolbar">
        <div class="pills">
          <a href="{{ url()->current() }}" class="pill active">Todos</a>
          @if(!empty($categories))
            @foreach($categories as $cat)
              <a href="{{ $cat['url'] ?? '#' }}" class="pill {{ !empty($cat['active']) ? 'active' : '' }}">{{ $cat['name'] }}</a>
            @endforeach
          @endif
        </div>

        <div class="toolbar">
          <button type="button" class="tool-chip">Download</button>
          <span class="meta" style="margin-left:6px;">Visualização:</span>
          <button type="button" class="tool-btn js-grid-3">3 col</button>
          <button type="button" class="tool-btn js-grid-4 active">4 col</button>
          <button type="button" class="tool-btn js-grid-list">Lista</button>
        </div>
      </div>
    </div>

  </div>
</section>
