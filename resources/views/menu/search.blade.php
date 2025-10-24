@extends('layouts.app')

@section('title', 'Busca - Olika')

@section('content')
<div class="py-8">
    <div class="container">
        <!-- Search Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">
                🔍 Resultados da Busca
            </h1>
            <p class="text-gray-600">
                Resultados para: <span class="font-semibold">"{{ $query }}"</span>
            </p>
        </div>

        <!-- Search Form -->
        <div class="mb-8">
            <form action="{{ route('menu.search') }}" method="GET" class="max-w-md">
                <div class="relative">
                    <input type="text" 
                           name="q" 
                           placeholder="Buscar produtos..." 
                           class="w-full px-4 py-3 pl-12 pr-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                           value="{{ $query }}">
                    <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                </div>
            </form>
        </div>

        <!-- Results -->
        @if($products->count() > 0)
        <div class="mb-6">
            <p class="text-gray-600">
                {{ $products->count() }} produto(s) encontrado(s)
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($products as $product)
                @include('components.product-card', ['product' => $product])
            @endforeach
        </div>
        @else
        <!-- No Results -->
        <div class="text-center py-12">
            <i class="fas fa-search text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-600 mb-2">
                Nenhum produto encontrado
            </h3>
            <p class="text-gray-500 mb-6">
                Tente buscar com outros termos ou navegue pelo nosso cardápio
            </p>
            <div class="space-x-4">
                <a href="{{ route('menu.index') }}" 
                   class="btn-primary inline-flex items-center">
                    <i class="fas fa-utensils mr-2"></i>
                    Ver Cardápio Completo
                </a>
                <button onclick="document.querySelector('input[name=q]').focus()" 
                        class="bg-gray-100 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-200 transition">
                    <i class="fas fa-search mr-2"></i>
                    Nova Busca
                </button>
            </div>
        </div>
        @endif

        <!-- Search Suggestions -->
        @if($products->count() == 0)
        <div class="mt-12">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                Sugestões de busca:
            </h3>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('menu.search', ['q' => 'pão']) }}" 
                   class="bg-gray-100 text-gray-700 px-4 py-2 rounded-full hover:bg-gray-200 transition">
                    Pão
                </a>
                <a href="{{ route('menu.search', ['q' => 'doce']) }}" 
                   class="bg-gray-100 text-gray-700 px-4 py-2 rounded-full hover:bg-gray-200 transition">
                    Doce
                </a>
                <a href="{{ route('menu.search', ['q' => 'integral']) }}" 
                   class="bg-gray-100 text-gray-700 px-4 py-2 rounded-full hover:bg-gray-200 transition">
                    Integral
                </a>
                <a href="{{ route('menu.search', ['q' => 'artesanal']) }}" 
                   class="bg-gray-100 text-gray-700 px-4 py-2 rounded-full hover:bg-gray-200 transition">
                    Artesanal
                </a>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Adicionar ao carrinho
    function addToCart(productId, quantity = 1) {
        // Usar função melhorada se disponível
        if (typeof window.addToCartImproved === 'function') {
            window.addToCartImproved(productId, quantity);
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
                showNotification(data.message, 'success');
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
