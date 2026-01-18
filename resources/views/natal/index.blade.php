@extends('natal.layout')

@section('title', 'Olika - Cardápio de Natal 🎄 | Pães Artesanais')

@section('content')
@php
    $cartCount = session('cart_count', 0);
    $cart = session('cart', []);
    if (empty($cartCount) && !empty($cart)) {
        $cartCount = array_sum(array_column($cart, 'qty'));
    }
    
    $activeCategoryId = null;
    $categoryTitle = 'Todos';
    $productsCount = isset($products) ? $products->count() : 0;
    
    $preloadCount = 0;
    $preloadLimit = 20;
@endphp

<!-- Preload das primeiras imagens -->
@if(isset($categories) && $categories->count() > 0)
    @foreach($categories as $category)
        @if(isset($category->products) && $category->products->count() > 0 && $preloadCount < $preloadLimit)
            @php
                $displayType = $category->display_type ?? 'grid';
                $thumbnailSize = match($displayType) {
                    'grid' => 'thumb',
                    'list_horizontal' => 'small',
                    'list_vertical' => 'small',
                    default => 'thumb'
                };
            @endphp
            @foreach($category->products->take($preloadLimit - $preloadCount) as $product)
                @php
                    $imageUrls = $product->getOptimizedImageUrls($thumbnailSize);
                    $img = $imageUrls['webp'] ?? $imageUrls['jpg'] ?? asset('images/produto-placeholder.jpg');
                    $preloadCount++;
                @endphp
                <link rel="preload" as="image" href="{{ $img }}" fetchpriority="{{ $preloadCount <= 12 ? 'high' : 'auto' }}" type="{{ strpos($img, '.webp') !== false ? 'image/webp' : 'image/jpeg' }}">
            @endforeach
        @endif
        @break($preloadCount >= $preloadLimit)
    @endforeach
@endif

<!-- Hero Section Natal -->
<div class="text-white shadow-lg py-12 mb-6" style="background: linear-gradient(135deg, #C41E3A 0%, #228B22 100%);">
    <div class="container mx-auto px-4">
        <div class="flex items-center justify-center gap-4">
            <span class="text-5xl">🎄</span>
            <div class="text-center">
                <h1 class="text-4xl font-bold leading-tight">Olika Natal</h1>
                <p class="text-lg opacity-90 mt-2">Cardápio Especial de Natal</p>
                <p class="text-sm opacity-80 mt-1">Delícias artesanais para sua ceia</p>
            </div>
            <span class="text-5xl">🎅</span>
        </div>
    </div>
</div>

<!-- Filtros de Categoria - Layout pixel-perfect -->
<div class="bg-white border-b sticky top-0 z-10 shadow-sm">
    <div class="container mx-auto px-4 py-4">
        <div class="flex gap-2 overflow-x-auto scrollbar-hide pb-2 -mx-4 px-4">
            <button class="px-4 py-2 rounded-full text-white font-medium whitespace-nowrap flex-shrink-0 transition-all" style="background-color: #C41E3A;" onmouseover="this.style.backgroundColor='#a0182e'" onmouseout="this.style.backgroundColor='#C41E3A'" data-category="all">Todos</button>
            @if(isset($categories))
                @foreach($categories as $category)
                    <button class="px-4 py-2 rounded-full bg-gray-100 text-gray-700 hover:bg-gray-200 font-medium whitespace-nowrap flex-shrink-0 transition-colors" data-category="{{ $category->id }}">
                        {{ $category->name }}
                    </button>
                @endforeach
            @endif
        </div>
    </div>
</div>

<!-- Busca - Layout pixel-perfect -->
<div class="container mx-auto px-4 py-4">
    <div class="flex items-center gap-3 bg-white rounded-lg border border-gray-200 p-3 shadow-sm">
        <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
        </svg>
        <input type="text" placeholder="Buscar produtos..." class="flex-1 outline-none text-sm bg-transparent" id="searchInput">
    </div>
</div>

<!-- Produtos -->
<main class="container mx-auto px-4 pb-20">
    @if(isset($categories) && $categories->count() > 0)
        @foreach($categories as $category)
            @if(isset($category->products) && $category->products->count() > 0)
                @php
                    $displayType = $category->display_type ?? 'grid';
                    $categoryId = is_string($category->id) ? $category->id : $category->id;
                    static $totalProductIndex = 0;
                    if (!isset($globalProductIndex)) {
                        $globalProductIndex = 0;
                    }
                @endphp
                
                <div class="mb-12 category-section" data-category-id="{{ $categoryId }}">
                    @if($displayType === 'grid')
                        <!-- Título e contagem - Layout pixel-perfect -->
                        <div class="mb-6">
                            <h2 class="text-3xl font-serif font-bold text-gray-900 leading-tight">{{ $category->name }}</h2>
                            <p class="text-gray-600 mt-1 text-sm">{{ $category->products->count() }} {{ $category->products->count() === 1 ? 'produto' : 'produtos' }}</p>
                        </div>
                        <!-- Grid de produtos - Layout pixel-perfect -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                            @foreach($category->products as $product)
                                @php
                                    $shouldLoadEager = $globalProductIndex < 12;
                                    $fetchPriority = $globalProductIndex < 6 ? 'high' : ($globalProductIndex < 12 ? 'auto' : 'low');
                                    $globalProductIndex++;
                                @endphp
                                @include('natal.partials.product-card', [
                                    'product' => $product, 
                                    'displayType' => 'grid',
                                    'loadEager' => $shouldLoadEager,
                                    'fetchPriority' => $fetchPriority
                                ])
                            @endforeach
                        </div>
                    @elseif($displayType === 'list_horizontal')
                        <!-- Lista horizontal - Layout pixel-perfect -->
                        <div class="mb-6">
                            <h2 class="text-3xl font-serif font-bold text-gray-900 leading-tight">{{ $category->name }}</h2>
                        </div>
                        <div class="overflow-x-auto scrollbar-hide pb-4 -mx-4 px-4">
                            <div class="flex gap-4" style="width: max-content;">
                                @foreach($category->products as $product)
                                    @php
                                        $shouldLoadEager = $globalProductIndex < 12;
                                        $fetchPriority = $globalProductIndex < 6 ? 'high' : ($globalProductIndex < 12 ? 'auto' : 'low');
                                        $globalProductIndex++;
                                    @endphp
                                    @include('natal.partials.product-card', [
                                        'product' => $product, 
                                        'displayType' => 'list_horizontal',
                                        'loadEager' => $shouldLoadEager,
                                        'fetchPriority' => $fetchPriority
                                    ])
                                @endforeach
                            </div>
                        </div>
                    @else
                        <!-- Lista vertical - Layout pixel-perfect -->
                        <div class="mb-6">
                            <h2 class="text-3xl font-serif font-bold text-gray-900 leading-tight">{{ $category->name }}</h2>
                        </div>
                        <div class="space-y-4">
                            @foreach($category->products as $product)
                                @php
                                    $shouldLoadEager = $globalProductIndex < 12;
                                    $fetchPriority = $globalProductIndex < 6 ? 'high' : ($globalProductIndex < 12 ? 'auto' : 'low');
                                    $globalProductIndex++;
                                @endphp
                                @include('natal.partials.product-card', [
                                    'product' => $product, 
                                    'displayType' => 'list_vertical',
                                    'loadEager' => $shouldLoadEager,
                                    'fetchPriority' => $fetchPriority
                                ])
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif
        @endforeach
    @endif
</main>

<!-- Menu inferior fixo -->
<nav class="fixed bottom-0 left-0 right-0 bg-white border-t shadow-lg z-20">
    <div class="container mx-auto px-4">
        <div class="flex items-center justify-around py-3">
            <a href="{{ route('natal.index') }}" class="flex flex-col items-center gap-1" style="color: #C41E3A;">
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

@push('scripts')
<script src="{{ asset('js/natal-theme.js') }}" defer></script>
@endpush

