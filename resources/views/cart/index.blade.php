{{-- resources/views/cart/index.blade.php --}}
@extends('layouts.app')
@section('title','Carrinho')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/olika-store.css') }}">
@endpush

@section('content')
<div class="header-bar">
  <a href="{{ route('menu.index') }}" style="text-decoration:none">←</a>
  <div class="header-cart">Carrinho <span class="badge">{{ $cartCount ?? 0 }}</span></div>
</div>

<div class="cart-shell">
  @if(($items ?? collect())->isEmpty())
    <div class="cart-empty">
      <div style="font-size:52px">🛍️</div>
      <div style="font-weight:800;font-size:18px">Seu carrinho está vazio</div>
      <div>Adicione produtos para começar</div>
      <a href="{{ route('menu.index') }}" class="add-btn" style="display:inline-block;text-decoration:none;margin-top:10px">Ver Produtos</a>
    </div>
  @else
    <div class="cart-items" style="display:grid;gap:12px">
      @foreach($items as $it)
        <div class="cart-item">
          <img src="{{ $it->product->image_url ?? asset('images/placeholder.jpg') }}" alt="{{ $it->product->name }}">
          <div>
            <div class="title">{{ $it->product->name }}</div>
            <div class="muted" style="color:#777">Qtd: {{ $it->quantity }}</div>
          </div>
          <div class="price">R$ {{ number_format($it->total_price, 2, ',', '.') }}</div>
        </div>
      @endforeach
    </div>
    <div class="cart-summary">
      <div class="total"><span>Total</span><span>R$ {{ number_format($total, 2, ',', '.') }}</span></div>
      <button class="cta" onclick="location.href='{{ route('checkout.index') }}'">Finalizar Pedido</button>
    </div>
  @endif
</div>
@endsection
