@extends('pedido.layout')

@section('title', 'Buscar - Olika')

@section('content')
    <!-- Search Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold mb-2">Resultados da Busca</h1>
        <p class="text-gray-600">Você pesquisou por: <strong>"{{ $query }}"</strong></p>
        <p class="text-sm text-gray-500 mt-1">{{ $products->count() }} produto(s) encontrado(s)</p>
    </div>

    <!-- Search Form -->
    <div class="mb-8">
        <form method="GET" action="{{ route('pedido.menu.search') }}" class="flex gap-2">
            <input type="text" name="q" value="{{ $query }}" placeholder="Buscar produtos..."
                class="flex-1 border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500">
            <button type="submit"
                class="bg-orange-500 text-white px-6 py-2 rounded-lg hover:bg-orange-600 transition-colors">
                Buscar
            </button>
        </form>
    </div>

    <!-- Results -->
    @if($products->count() > 0)
        <div class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-3 gap-2 md:gap-4 lg:gap-6">
            @foreach($products as $product)
                @php
                    // Mesma lógica de imagem e preço
                    $img = $product->image_url;
                    if (!$img && $product->cover_image) {
                        $img = asset('storage/' . $product->cover_image);
                    } elseif (!$img && $product->images && $product->images->count() > 0) {
                        $img = asset('storage/' . $product->images->first()->path);
                    }
                    $img = $img ?? asset('images/produto-placeholder.jpg');

                    $minVariantPrice = $product->variants()->where('is_active', true)->min('price');
                    $hasActiveVariants = $minVariantPrice !== null;
                    $displayPrice = ($product->price > 0) ? (float) $product->price : ((float) $minVariantPrice ?: 0);
                @endphp
                <div
                    class="rounded-lg border bg-white shadow-sm overflow-hidden group hover:shadow-md transition-all duration-300 flex flex-col h-full">
                    <div class="relative overflow-hidden aspect-square">
                        <img src="{{ $img }}" alt="{{ $product->name }}"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300 cursor-pointer"
                            onclick="openQuickView({{ $product->id }})">
                        @if($product->category)
                            <div
                                class="inline-flex items-center rounded-full border px-2 py-0.5 font-semibold transition-colors absolute top-1.5 right-1.5 bg-orange-500 text-white text-xs leading-tight">
                                {{ $product->category->name }}
                            </div>
                        @endif
                    </div>
                    <div class="p-2 md:p-3 flex-1 flex flex-col">
                        <div class="flex-1 min-h-0 mb-1">
                            <h3 class="font-semibold mb-0.5 text-xs md:text-sm line-clamp-2 leading-tight">{{ $product->name }}</h3>
                            <p class="text-gray-600 mb-1 text-xs line-clamp-2 hidden md:block">
                                {{ $product->description ? \Illuminate\Support\Str::limit($product->description, 80) : '' }}</p>
                        </div>
                        <div class="flex items-center justify-between gap-1 md:gap-2 mt-auto">
                            <span class="font-bold text-orange-500 text-sm md:text-lg leading-tight">
                                @if($hasActiveVariants)
                                    <span class="hidden md:inline">a partir de </span>R$ {{ number_format($displayPrice, 2, ',', '.') }}
                                @else
                                    R$ {{ number_format($displayPrice, 2, ',', '.') }}
                                @endif
                            </span>
                            @if($hasActiveVariants)
                                <button onclick="openQuickView({{ $product->id }})"
                                    class="inline-flex items-center justify-center gap-1 whitespace-nowrap font-medium transition-colors bg-orange-500 text-white hover:bg-orange-600 h-7 md:h-9 rounded-md px-2 md:px-3 text-xs">Escolher</button>
                            @else
                                <button onclick="addToCart({{ $product->id }}, '{{ addslashes($product->name) }}', {{ $displayPrice }})"
                                    class="inline-flex items-center justify-center gap-1 whitespace-nowrap font-medium transition-colors bg-orange-500 text-white hover:bg-orange-600 h-7 md:h-9 rounded-md px-2 md:px-3 text-xs">+</button>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-12">
            <p class="text-gray-600 mb-4">Nenhum produto encontrado para "{{ $query }}".</p>
            <a href="{{ route('pedido.index') }}" class="inline-block text-orange-500 hover:text-orange-600">Voltar ao
                cardápio</a>
        </div>
    @endif
@endsection

@push('scripts')
    <script>
        async function addToCart(productId, productName, price) {
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
                const response = await fetch('{{ route("pedido.cart.add") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        product_id: productId,
                        qty: 1,
                        price: price
                    })
                });

                const data = await response.json();

                if (data.ok || data.success) {
                    const badge = document.querySelector('a[href*="cart"] .absolute');
                    if (badge) {
                        badge.textContent = data.cart_count || parseInt(badge.textContent) + 1;
                    } else {
                        const cartLink = document.querySelector('a[href*="cart"]');
                        if (cartLink) {
                            const newBadge = document.createElement('div');
                            newBadge.className = 'absolute -right-2 -top-2 h-5 w-5 rounded-full bg-orange-500 text-white text-xs flex items-center justify-center font-semibold';
                            newBadge.textContent = data.cart_count || 1;
                            cartLink.appendChild(newBadge);
                        }
                    }

                    showNotification('Produto adicionado ao carrinho!');
                }
            } catch (error) {
                console.error('Erro:', error);
                showNotification('Erro ao adicionar produto', 'error');
            }
        }


    </script>
@endpush