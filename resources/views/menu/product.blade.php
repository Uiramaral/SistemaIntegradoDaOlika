@extends('layouts.app')

@section('title', $product->name . ' - Olika')

@section('content')
<div class="py-8">
    <div class="container">
        <!-- Breadcrumb -->
        <nav class="mb-6">
            <ol class="flex items-center space-x-2 text-sm text-gray-600">
                <li><a href="{{ route('menu.index') }}" class="hover:text-orange-600">Cardápio</a></li>
                <li><i class="fas fa-chevron-right text-xs"></i></li>
                <li><a href="{{ route('menu.category', $product->category) }}" class="hover:text-orange-600">{{ $product->category->name }}</a></li>
                <li><i class="fas fa-chevron-right text-xs"></i></li>
                <li class="text-gray-900 font-medium">{{ $product->name }}</li>
            </ol>
        </nav>

        <div class="grid lg:grid-cols-2 gap-8">
            <!-- Product Image -->
            <div>
                @if($product->image_url)
                <img src="{{ $product->image_url }}" 
                     alt="{{ $product->name }}" 
                     class="w-full h-96 object-cover rounded-lg shadow-lg">
                @else
                <div class="w-full h-96 bg-gray-200 rounded-lg shadow-lg flex items-center justify-center">
                    <i class="fas fa-image text-6xl text-gray-400"></i>
                </div>
                @endif
            </div>

            <!-- Product Details -->
            <div class="space-y-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">
                        {{ $product->name }}
                    </h1>
                    
                    <div class="flex items-center space-x-4 mb-4">
                        <span class="text-3xl font-bold text-orange-600">
                            R$ {{ number_format($product->price, 2, ',', '.') }}
                        </span>
                        
                        @if($product->is_featured)
                        <span class="bg-yellow-100 text-yellow-800 text-sm px-3 py-1 rounded-full">
                            ⭐ Produto em Destaque
                        </span>
                        @endif
                    </div>
                    
                    @if($product->preparation_time)
                    <div class="flex items-center text-gray-600 mb-4">
                        <i class="fas fa-clock mr-2"></i>
                        <span>Tempo de preparo: {{ $product->preparation_time }} minutos</span>
                    </div>
                    @endif
                </div>

                @if($product->description)
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Descrição</h3>
                    <p class="text-gray-600 leading-relaxed">{{ $product->description }}</p>
                </div>
                @endif

                @if($product->allergens)
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Alérgenos</h3>
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <div class="flex items-start">
                            <i class="fas fa-exclamation-triangle text-yellow-600 mr-2 mt-0.5"></i>
                            <p class="text-yellow-800">{{ $product->allergens }}</p>
                        </div>
                    </div>
                </div>
                @endif

                @if($product->nutritional_info)
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Informações Nutricionais</h3>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <pre class="text-sm text-gray-600">{{ json_encode($product->nutritional_info, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                </div>
                @endif

                <!-- Add to Cart -->
                <div class="border-t pt-6">
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center border border-gray-300 rounded-lg">
                            <button onclick="decreaseQuantity()" 
                                    class="px-4 py-2 hover:bg-gray-100" id="decrease-btn">
                                <i class="fas fa-minus"></i>
                            </button>
                            <span class="px-4 py-2 border-x border-gray-300 min-w-[3rem] text-center" id="quantity">
                                1
                            </span>
                            <button onclick="increaseQuantity()" 
                                    class="px-4 py-2 hover:bg-gray-100" id="increase-btn">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                        
                        <button onclick="addToCart()" 
                                class="flex-1 btn-primary">
                            <i class="fas fa-plus mr-2"></i>
                            Adicionar ao Carrinho
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Related Products -->
        @if($relatedProducts->count() > 0)
        <div class="mt-16">
            <h2 class="text-2xl font-bold text-gray-900 mb-8">
                Produtos Relacionados
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($relatedProducts as $relatedProduct)
                    @include('components.product-card', ['product' => $relatedProduct])
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    let quantity = 1;
    
    function increaseQuantity() {
        quantity++;
        document.getElementById('quantity').textContent = quantity;
    }
    
    function decreaseQuantity() {
        if (quantity > 1) {
            quantity--;
            document.getElementById('quantity').textContent = quantity;
        }
    }
    
    function addToCart() {
        // Usar função melhorada se disponível
        if (typeof window.addToCartImproved === 'function') {
            window.addToCartImproved({{ $product->id }}, quantity);
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
                product_id: {{ $product->id }},
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
