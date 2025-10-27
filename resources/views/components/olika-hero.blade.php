{{-- HERO full-width com logo central e carrinho dentro do banner --}}
<section class="header-hero full-bleed"
  style="--hero:url('{{ $cover ?? asset('images/hero-breads.jpg') }}'); background-image:var(--hero);">

  <div class="hero-inner">

    {{-- Carrinho no banner --}}
    <div class="hero-topbar">
      <a href="{{ route('cart.index') }}" class="cart-link">
        <span>Meu Carrinho</span>
        <span class="cart-badge" data-cart-count="{{ session('cart_count', 0) }}">{{ session('cart_count', 0) }}</span>
      </a>
    </div>

    {{-- Logo, nome e tagline vinda do BD --}}
    <div class="hero-head">
      <div class="hero-logo">
        <img src="{{ asset('images/logo-olika.png') }}" alt="Olika" style="height:70px;width:auto;display:block;">
      </div>
      <div class="hero-title">{{ $store_name ?? 'Olika' }}</div>
      <div class="hero-sub">{{ $store_tagline ?? 'Pães • Artesanais' }}</div>
      {{-- REMOVIDO: "Aberto Agora" --}}
    </div>

    {{-- Prateleira: metade cupons / metade dias de entrega --}}
    <div class="hero-info-shelf">
      <div class="shelf-grid">

        {{-- Seção Cupons --}}
        <section class="shelf-section">
          <h3 class="shelf-title">Cupons Disponíveis</h3>

          @php
            // Estrutura pensada para vir do BD (array de cupons).
            // Enquanto não tiver, deixo defaults.
            $coupons = $coupons ?? [
              ['icon' => '🎉', 'title' => 'BEM-VINDO',   'desc' => '10% OFF na primeira compra'],
              ['icon' => '🚚', 'title' => 'Frete Grátis','desc' => 'Em pedidos acima de R$ 100'],
              // Se houver um 3º, 4º... automaticamente vai para a próxima linha
              // ['icon'=>'🥖','title'=>'Dia do Pão','desc'=>'R$ 5,00 de desconto em baguetes']
            ];
          @endphp

          <div class="coupons-wrap">
            @foreach($coupons as $c)
              <div class="coupon-card">
                <span class="badge-soft">{{ $c['icon'] ?? '' }} {{ $c['title'] ?? '' }}</span>
                <div class="coupon-text">
                  <div class="coupon-line meta">
                    {{ $c['desc'] ?? '' }}
                  </div>
                </div>
              </div>
            @endforeach
          </div>
        </section>

        {{-- Divisória adaptativa (horizontal no mobile, vertical no ≥520px) --}}
        <div class="shelf-divider" aria-hidden="true"></div>

        {{-- Seção Dias de Entrega (BD) --}}
        <section class="shelf-section">
          <h3 class="shelf-title">Dias de Entrega</h3>
          @php
            // Exemplo até ligar no BD:
            $delivery_days = $delivery_days ?? ['Terça','Quinta','Sábado'];
          @endphp

          <div class="days">
            @forelse($delivery_days as $d)
              <span class="day-chip">{{ $d }}</span>
            @empty
              <span class="meta">Configurar no dashboard</span>
            @endforelse
          </div>
        </section>

      </div>
    </div>

  </div>
</section>
