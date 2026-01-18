@extends('natal.layout')

@section('title', 'Buscar: ' . $query . ' - Cardápio de Natal 🎄 | Olika')

@section('content')
@php
    $cartCount = session('cart_count', 0);
    $cart = session('cart', []);
    if (empty($cartCount) && !empty($cart)) {
        $cartCount = array_sum(array_column($cart, 'qty'));
    }
@endphp

<!-- Header com tema Natal -->
<header class="natal-gradient text-white shadow-lg natal-snow">
    <div class="container mx-auto px-4 py-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('natal.index') }}" class="text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </a>
                <div>
                    <h1 class="text-2xl font-bold">Buscar: {{ $query }}</h1>
                    <p class="text-sm opacity-90">{{ $products->count() }} {{ $products->count() === 1 ? 'resultado' : 'resultados' }}</p>
                </div>
            </div>
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

<!-- Resultados da busca -->
<main class="container mx-auto px-4 pb-20 pt-6">
    @if($products->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($products as $product)
                @php
                    $shouldLoadEager = $loop->index < 12;
                    $fetchPriority = $loop->index < 6 ? 'high' : ($loop->index < 12 ? 'auto' : 'low');
                @endphp
                @include('natal.partials.product-card', [
                    'product' => $product, 
                    'displayType' => 'grid',
                    'loadEager' => $shouldLoadEager,
                    'fetchPriority' => $fetchPriority
                ])
            @endforeach
        </div>
    @else
        <div class="text-center py-12">
            <p class="text-xl text-gray-600 mb-4">Nenhum produto encontrado para "{{ $query }}"</p>
            <a href="{{ route('natal.index') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-natal-red text-white rounded-lg hover:bg-natal-red/90 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Voltar ao cardápio
            </a>
        </div>
    @endif
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

