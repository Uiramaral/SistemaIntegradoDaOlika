@extends('layouts.app')

@section('title', $category->name . ' - Olika')

@section('content')
<div class="py-8">
    <div class="container">
        <!-- Breadcrumb -->
        <nav class="mb-6">
            <ol class="flex items-center space-x-2 text-sm text-gray-600">
                <li><a href="{{ route('menu.index') }}" class="hover:text-orange-600">Cardápio</a></li>
                <li><i class="fas fa-chevron-right text-xs"></i></li>
                <li class="text-gray-900 font-medium">{{ $category->name }}</li>
            </ol>
        </nav>

        <!-- Category Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">
                {{ $category->name }}
            </h1>
            @if($category->description)
            <p class="text-gray-600">{{ $category->description }}</p>
            @endif
        </div>

        <!-- Products Grid -->
        @if($products->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($products as $product)
                @include('components.product-card', ['product' => $product])
            @endforeach
        </div>
        @else
        <!-- Empty State -->
        <div class="text-center py-12">
            <i class="fas fa-utensils text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-600 mb-2">
                Nenhum produto nesta categoria
            </h3>
            <p class="text-gray-500 mb-6">
                Em breve teremos produtos deliciosos nesta categoria!
            </p>
            <a href="{{ route('menu.index') }}" 
               class="btn-primary inline-flex items-center">
                <i class="fas fa-arrow-left mr-2"></i>
                Voltar ao Cardápio
            </a>
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
