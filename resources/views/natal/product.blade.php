@extends('natal.layout')

@section('title', $product->name . ' - Cardápio de Natal 🎄 | Olika')

@section('content')
@php
    $cartCount = session('cart_count', 0);
    $cart = session('cart', []);
    if (empty($cartCount) && !empty($cart)) {
        $cartCount = array_sum(array_column($cart, 'qty'));
    }
    
    $minVariantPrice = $product->variants()->where('is_active', true)->min('price');
    $hasActiveVariants = $minVariantPrice !== null;
    $displayPrice = ($product->price > 0) ? (float)$product->price : ((float)$minVariantPrice ?: 0);
@endphp

<!-- Header com tema Natal -->
<header class="natal-gradient text-white shadow-lg natal-snow">
    <div class="container mx-auto px-4 py-6">
        <div class="flex items-center justify-between">
            <a href="{{ route('natal.index') }}" class="text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <a href="{{ route('pedido.cart.index') }}" class="relative">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                @if($cartCount > 0)
                <span class="absolute -top-2 -right-2 bg-natal-gold text-natal-red rounded-full w-5 h-5 flex items-center justify-center text-xs font-bold">{{ $cartCount }}</span>
                @endif
            </a>
        </div>
    </div>
</header>

<!-- Detalhes do produto -->
<main class="container mx-auto px-4 pb-20">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden border-2 border-natal-red/20">
            <div class="grid md:grid-cols-2 gap-6 p-6">
                <div>
                    @php
                        $imageUrls = $product->getOptimizedImageUrls('large');
                    @endphp
                    <picture>
                        <source srcset="{{ $imageUrls['webp'] }}" type="image/webp">
                        <img 
                            src="{{ $imageUrls['jpg'] }}" 
                            alt="{{ $product->name }}" 
                            class="w-full h-auto rounded-lg"
                            loading="eager"
                        >
                    </picture>
                </div>
                <div>
                    <div class="mb-4">
                        <span class="inline-block bg-natal-red text-white px-3 py-1 rounded-full text-xs font-bold mb-2">🎄 Natal</span>
                        <h1 class="text-3xl font-bold text-foreground mb-2">{{ $product->name }}</h1>
                        @if($product->description)
                            <p class="text-gray-600 mb-4">{{ $product->description }}</p>
                        @endif
                    </div>
                    
                    <div class="mb-6">
                        <p class="text-3xl font-bold text-natal-red mb-4">
                            R$ {{ number_format($displayPrice, 2, ',', '.') }}
                            @if($hasActiveVariants)
                                <span class="text-sm text-gray-500 font-normal">a partir de</span>
                            @endif
                        </p>
                        
                        @if($hasActiveVariants)
                            <button onclick="openQuickView({{ $product->id }})" class="w-full bg-natal-red text-white py-3 rounded-lg font-semibold hover:bg-natal-red/90 transition-colors">
                                Ver opções
                            </button>
                        @else
                            <button onclick="addToCart({{ $product->id }}, '{{ addslashes($product->name) }}', {{ $displayPrice }})" class="w-full bg-natal-red text-white py-3 rounded-lg font-semibold hover:bg-natal-red/90 transition-colors">
                                Adicionar ao carrinho
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        @if($relatedProducts && $relatedProducts->count() > 0)
            <div class="mt-12">
                <h2 class="text-2xl font-bold mb-6 flex items-center gap-2">
                    <span>🎄</span>
                    Produtos relacionados
                </h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    @foreach($relatedProducts as $relatedProduct)
                        @include('natal.partials.product-card', [
                            'product' => $relatedProduct, 
                            'displayType' => 'grid',
                            'loadEager' => false,
                            'fetchPriority' => 'low'
                        ])
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</main>

<!-- Menu inferior fixo -->
<nav class="fixed bottom-0 left-0 right-0 bg-white border-t shadow-lg z-20">
    <div class="container mx-auto px-4">
        <div class="flex items-center justify-around py-3">
            <a href="{{ route('natal.index') }}" class="flex flex-col items-center gap-1 text-natal-red">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                </svg>
                <span class="text-xs font-medium">Menu</span>
            </a>
            <a href="{{ route('pedido.cart.index') }}" class="flex flex-col items-center gap-1 text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <span class="text-xs font-medium">Carrinho</span>
            </a>
        </div>
    </div>
</nav>

@endsection

