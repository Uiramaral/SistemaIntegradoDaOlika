@extends('layouts.app')

@section('title', 'Cardápio - Olika')

@section('content')
<div class="py-8">
    <div class="container">
        <!-- Hero Section -->
        <div class="text-center mb-12">
            <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">
                🍞 Cardápio Olika
            </h1>
            <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                Pães artesanais feitos com amor e ingredientes de primeira qualidade
            </p>
        </div>

        <!-- Search Bar -->
        <div class="mb-8">
            <form action="{{ route('menu.search') }}" method="GET" class="max-w-md mx-auto">
                <div class="relative">
                    <input type="text" 
                           name="q" 
                           placeholder="Buscar produtos..." 
                           class="w-full px-4 py-3 pl-12 pr-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                           value="{{ request('q') }}">
                    <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                </div>
            </form>
        </div>

        <!-- Featured Products -->
        @if($featuredProducts->count() > 0)
        <section class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">
                ⭐ Produtos em Destaque
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($featuredProducts as $product)
                    @include('components.product-card', ['product' => $product])
                @endforeach
            </div>
        </section>
        @endif

        <!-- Categories -->
        @foreach($categories as $category)
        <section class="mb-12">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-gray-900">
                    {{ $category->name }}
                </h2>
                <a href="{{ route('menu.category', $category) }}" 
                   class="text-orange-600 hover:text-orange-700 font-medium">
                    Ver todos <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
            
            @if($category->description)
            <p class="text-gray-600 mb-6">{{ $category->description }}</p>
            @endif
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($category->products as $product)
                    @include('components.product-card', ['product' => $product])
                @endforeach
            </div>
        </section>
        @endforeach

        <!-- Empty State -->
        @if($categories->count() == 0)
        <div class="text-center py-12">
            <i class="fas fa-utensils text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-600 mb-2">
                Nenhum produto disponível
            </h3>
            <p class="text-gray-500">
                Em breve teremos produtos deliciosos para você!
            </p>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Adicionar ao carrinho
    function addToCart(productId, quantity = 1, price = 0) {
        // Usar função melhorada se disponível
        if (typeof window.addToCartImproved === 'function') {
            window.addToCartImproved(productId, quantity, price);
            return;
        }
        
        // Fallback para método original
        fetch('/cart/add', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.csrfToken
            },
            body: JSON.stringify({
                product_id: productId,
                quantity: quantity
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Mostrar notificação
                showNotification(data.message, 'success');
                
                // Atualizar contador do carrinho
                if (typeof window.updateCartCount === 'function') {
                    window.updateCartCount();
                }
            } else {
                showNotification('Erro ao adicionar produto', 'error');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            showNotification('Erro ao adicionar produto', 'error');
        });
    }
    
    // Função para mostrar notificações
    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg text-white z-50 ${
            type === 'success' ? 'bg-green-500' : 
            type === 'error' ? 'bg-red-500' : 'bg-blue-500'
        }`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }
</script>
@endpush
