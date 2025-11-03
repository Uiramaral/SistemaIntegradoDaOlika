@extends('pedido.layout')

@section('title', $category->name . ' - Olika')

@section('content')
@php
    $cartCount = session('cart_count', 0);
    $cart = session('cart', []);
    if (empty($cartCount) && !empty($cart)) {
        $cartCount = array_sum(array_column($cart, 'qty'));
    }
    
    $categoryTitle = $category->name;
    $productsCount = isset($products) ? $products->count() : 0;
@endphp

<!-- Grid de Produtos (mesmo design do index) -->
@if(isset($products) && $products && $products->count() > 0)
<div class="grid grid-cols-3 gap-3 md:gap-4">
    @foreach($products as $product)
    @php
        $minVariantPrice = $product->variants()->where('is_active', true)->min('price');
        $hasActiveVariants = $minVariantPrice !== null;
        $displayPrice = ($product->price > 0) ? (float)$product->price : ((float)$minVariantPrice ?: 0);
        $isPurchasable = $displayPrice > 0;
        if (!$isPurchasable) { continue; }
        
        // Imagem
        $img = $product->image_url;
        if (!$img && $product->cover_image) { $img = asset('storage/'.$product->cover_image); }
        elseif(!$img && $product->images && $product->images->count()>0){ $img = asset('storage/'.$product->images->first()->path); }
        $img = $img ?? asset('images/produto-placeholder.jpg');
    @endphp
    <div class="product-item text-card-foreground group overflow-hidden border shadow-sm hover:shadow-xl transition-all duration-300 bg-card rounded-2xl cursor-pointer" data-category-id="{{ $product->category_id ?? '0' }}" onclick="openQuickView({{ $product->id }})">
        <div class="aspect-square overflow-hidden bg-muted relative">
            <img src="{{ $img }}" alt="{{ $product->name }}" class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-110 pointer-events-none">
            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
        </div>
        <div class="p-2.5 sm:p-3 flex flex-col gap-1.5">
            <h3 class="font-medium text-xs sm:text-sm line-clamp-2 leading-tight text-gray-900">{{ $product->name }}</h3>
            <div class="flex items-center justify-between mt-auto pt-1">
                <span class="text-sm sm:text-base font-bold text-primary">R$ {{ number_format($displayPrice, 2, ',', '.') }}</span>
                @if($hasActiveVariants)
                    <button onclick="event.stopPropagation(); openQuickView({{ $product->id }})" class="inline-flex items-center justify-center gap-1 whitespace-nowrap text-xs font-medium bg-primary text-white hover:bg-primary/90 rounded-lg h-8 w-8 sm:h-9 sm:w-9 shadow-sm hover:shadow transition-all duration-200 flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 12h14"></path>
                            <path d="M12 5v14"></path>
                        </svg>
                    </button>
                @else
                    <button onclick="event.stopPropagation(); addToCart({{ $product->id }}, '{{ addslashes($product->name) }}', {{ $displayPrice }})" class="inline-flex items-center justify-center gap-1 whitespace-nowrap text-xs font-medium bg-primary text-white hover:bg-primary/90 rounded-lg h-8 w-8 sm:h-9 sm:w-9 shadow-sm hover:shadow transition-all duration-200 flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 12h14"></path>
                            <path d="M12 5v14"></path>
                        </svg>
                    </button>
                @endif
            </div>
        </div>
    </div>
    @endforeach
</div>
@else
<div class="text-center py-12">
    <p class="text-muted-foreground">Nenhum produto disponível nesta categoria no momento.</p>
</div>
@endif

@endsection

@push('scripts')
<script>
// Popular sidebar com categorias e atualizar título
document.addEventListener('DOMContentLoaded', function() {
    const categoriesList = document.getElementById('categoriesList');
    const categoryTitle = document.getElementById('categoryTitle');
    const productsCount = document.getElementById('productsCount');
    
    // Atualizar título e contagem
    if (categoryTitle) {
        categoryTitle.textContent = '{{ $categoryTitle }}';
    }
    if (productsCount) {
        productsCount.textContent = '{{ $productsCount }} produtos disponíveis';
    }
    
    @if(isset($categories) && $categories->count() > 0)
    // Determinar categoria ativa
    const currentPath = window.location.pathname;
    const activeCategoryId = {{ $category->id }};
    
    // Adicionar botão "Todos"
    const todosBtn = document.createElement('button');
    todosBtn.className = 'inline-flex items-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 px-4 py-2 w-full justify-start gap-3 h-11 hover:bg-accent hover:text-accent-foreground';
    todosBtn.innerHTML = `
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4">
            <path d="M15 21v-8a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v8"></path>
            <path d="M3 10a2 2 0 0 1 .709-1.528l7-5.999a2 2 0 0 1 2.582 0l7 5.999A2 2 0 0 1 21 10v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
        </svg>
        <span>Todos</span>
    `;
    todosBtn.addEventListener('click', () => {
        window.location.href = '{{ route('pedido.index') }}';
    });
    categoriesList.appendChild(todosBtn);
    
    // Adicionar categorias
    @foreach($categories as $cat)
    const catBtn{{ $cat->id }} = document.createElement('button');
    catBtn{{ $cat->id }}.className = activeCategoryId === {{ $cat->id }} ?
        'inline-flex items-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 px-4 py-2 w-full justify-start gap-3 h-11 bg-primary text-primary-foreground hover:bg-primary/90' :
        'inline-flex items-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 px-4 py-2 w-full justify-start gap-3 h-11 hover:bg-accent hover:text-accent-foreground';
    catBtn{{ $cat->id }}.innerHTML = `
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4">
            <path d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z"></path>
            <path d="M12 22V12"></path>
            <path d="m3.3 7 7.703 4.734a2 2 0 0 0 1.994 0L20.7 7"></path>
            <path d="m7.5 4.27 9 5.15"></path>
        </svg>
        <span>{{ $cat->name }}</span>
    `;
    catBtn{{ $cat->id }}.addEventListener('click', () => {
        window.location.href = '{{ route('pedido.menu.category', $cat->id) }}';
    });
    categoriesList.appendChild(catBtn{{ $cat->id }});
    @endforeach
    @endif
    
    // Busca
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            const query = e.target.value.trim();
            if (query.length >= 2) {
                searchTimeout = setTimeout(() => {
                    window.location.href = '{{ route('pedido.menu.search') }}?q=' + encodeURIComponent(query);
                }, 500);
            }
        });
    }
});

// Funções de carrinho (mesmas do index)
function addToCart(productId, productName, price) {
    fetch('{{ route('pedido.cart.add') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            product_id: productId,
            qty: 1,
            price: price
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.updateCartBadge(data.cart_count || 0);
            // Recarregar drawer se aberto
            if (typeof loadCartIntoDrawer === 'function') {
                loadCartIntoDrawer();
            }
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao adicionar produto ao carrinho');
    });
}

function openQuickView(productId) {
    window.location.href = '{{ route('pedido.menu.product', ['product' => ':id']) }}'.replace(':id', productId);
}
</script>
@endpush

