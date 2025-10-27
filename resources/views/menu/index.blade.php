@extends('layouts.app')
@section('title', 'Cardápio')

@section('content')

{{-- HERO full-width usando componente --}}
@include('components.olika-hero', [
  'cover'          => $store->cover_url ?? asset('images/hero-breads.jpg'),
  'category_label' => $store->category_label ?? 'Pães • Artesanais',
  'store_name'     => $store->name ?? 'Olika',
  'reviews_count'  => $store->reviews_count ?? '250+',
  'is_open'        => $store->is_open ?? true,
  'hours'          => $store->hours ?? null,
  'address'        => $store->address ?? null,
  'phone'          => $store->phone ?? null,
  'bio'            => $store->bio ?? null,
  'categories'     => isset($categories) ? $categories->map(function($c){
                        return [
                          'name' => $c->name,
                          'url' => route('menu.category', $c->id),
                          'active' => (isset($currentCategory) && $currentCategory->id === $c->id)
                        ];
                      })->toArray() : []
])

<div class="container-narrow section-after-hero">
  <h3 style="margin:0 0 10px 2px;">Nossos Produtos</h3>
  <section class="products-grid" data-view="{{ $defaultView ?? 'two' }}">
    @foreach($products as $product)
      <article class="product-card js-product"
               data-id="{{ $product->id }}"
               data-name="{{ $product->name }}"
               data-desc="{{ $product->description ?? '' }}"
               data-price="{{ number_format($product->price, 2, ',', '.') }}"
               data-image="{{ $product->image_url ?? asset('images/placeholder-product.jpg') }}">
        <div class="product-thumb js-open-modal">
          <img src="{{ $product->image_url ?? asset('images/placeholder-product.jpg') }}" alt="{{ $product->name }}">
        </div>
        <div class="product-body js-open-modal">
          <div class="product-name">{{ $product->name }}</div>
          <div class="product-price">R$ {{ number_format($product->price, 2, ',', '.') }}</div>
        </div>

        {{-- Fallback (sem JS) oculto --}}
        <form class="add-to-cart-fallback" action="{{ route('cart.add') }}" method="POST" style="display:none;">
          @csrf
          <input type="hidden" name="product_id" value="{{ $product->id }}">
          <input type="hidden" name="qty" value="1">
          <button type="submit">hidden</button>
        </form>

        {{-- Botão AJAX (não abre modal) --}}
        <button type="button"
                class="btn-add js-add-to-cart"
                data-product-id="{{ $product->id }}"
                data-qty="1"
                aria-label="Adicionar {{ $product->name }}">+</button>
      </article>
    @endforeach
  </section>

  {{-- MODAL DE PRODUTO --}}
  <div id="product-modal" class="pmask">
    <div class="pdialog">
      <button type="button" class="pclose" aria-label="Fechar">×</button>
      <div class="pmedia">
        <img id="pm-img" src="" alt="" />
          </div>
      <div class="pbody">
        <h3 id="pm-name">Nome do produto</h3>
        <p id="pm-desc" class="meta">Descrição breve</p>
        <div class="pm-price" id="pm-price">R$ 0,00</div>
        <div class="pm-qty">
          <button type="button" class="pm-qty-dec">−</button>
          <span id="pm-qty">1</span>
          <button type="button" class="pm-qty-inc">+</button>
            </div>
        <button type="button" class="pm-add" id="pm-add">Adicionar</button>
            </div>
          </div>
            </div>

  @if(method_exists($products, 'links'))
    <div style="margin-top:20px;">{{ $products->links() }}</div>
                  @endif
        </div>

@endsection
