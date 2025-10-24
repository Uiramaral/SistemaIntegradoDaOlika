@extends('layouts.app')

@section('title', 'Carrinho - Olika')

@section('content')
<div class="py-8">
    <div class="container">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <h1 class="text-3xl font-bold text-gray-900">
                    🛒 Meu Carrinho
                </h1>
                <a href="{{ route('menu.index') }}" 
                   class="text-orange-600 hover:text-orange-700 font-medium">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Continuar Comprando
                </a>
            </div>

            @if(empty($cart))
            <!-- Empty Cart -->
            <div class="text-center py-12">
                <i class="fas fa-shopping-cart text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-600 mb-2">
                    Seu carrinho está vazio
                </h3>
                <p class="text-gray-500 mb-6">
                    Adicione alguns produtos deliciosos ao seu carrinho!
                </p>
                <a href="{{ route('menu.index') }}" 
                   class="btn-primary inline-flex items-center">
                    <i class="fas fa-utensils mr-2"></i>
                    Ver Cardápio
                </a>
            </div>
            @else
            <!-- Cart Items -->
            <div class="grid lg:grid-cols-3 gap-8">
                <!-- Cart Items List -->
                <div class="lg:col-span-2">
                    <div class="space-y-4">
                        @foreach($cart as $item)
                        <div class="card flex items-center space-x-4" id="cart-item-{{ $item['product']->id }}">
                            @if($item['product']->image_url)
                            <img src="{{ $item['product']->image_url }}" 
                                 alt="{{ $item['product']->name }}" 
                                 class="w-20 h-20 object-cover rounded-lg">
                            @else
                            <div class="w-20 h-20 bg-gray-200 rounded-lg flex items-center justify-center">
                                <i class="fas fa-image text-gray-400"></i>
                            </div>
                            @endif
                            
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-900">
                                    {{ $item['product']->name }}
                                </h3>
                                <p class="text-sm text-gray-600">
                                    R$ {{ number_format($item['unit_price'], 2, ',', '.') }} cada
                                </p>
                            </div>
                            
                            <div class="flex items-center space-x-3">
                                <!-- Quantity Controls -->
                                <div class="flex items-center border border-gray-300 rounded-lg">
                                    <button onclick="updateQuantity({{ $item['product']->id }}, {{ $item['quantity'] - 1 }})" 
                                            class="px-3 py-2 hover:bg-gray-100">
                                        <i class="fas fa-minus text-sm"></i>
                                    </button>
                                    <span class="px-4 py-2 border-x border-gray-300 min-w-[3rem] text-center">
                                        {{ $item['quantity'] }}
                                    </span>
                                    <button onclick="updateQuantity({{ $item['product']->id }}, {{ $item['quantity'] + 1 }})" 
                                            class="px-3 py-2 hover:bg-gray-100">
                                        <i class="fas fa-plus text-sm"></i>
                                    </button>
                                </div>
                                
                                <!-- Total Price -->
                                <div class="text-right">
                                    <div class="font-semibold text-lg text-gray-900">
                                        R$ {{ number_format($item['quantity'] * $item['unit_price'], 2, ',', '.') }}
                                    </div>
                                </div>
                                
                                <!-- Remove Button -->
                                <button onclick="removeItem({{ $item['product']->id }})" 
                                        class="text-red-500 hover:text-red-700 p-2">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    
                    <!-- Clear Cart Button -->
                    <div class="mt-6">
                        <button onclick="clearCart()" 
                                class="text-red-600 hover:text-red-700 font-medium">
                            <i class="fas fa-trash mr-2"></i>
                            Limpar Carrinho
                        </button>
                    </div>
                </div>
                
                <!-- Order Summary -->
                <div class="lg:col-span-1">
                    <div class="card sticky top-24">
                        <h3 class="text-xl font-bold text-gray-900 mb-4">
                            Resumo do Pedido
                        </h3>
                        
                        <div class="space-y-3 mb-6">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Subtotal:</span>
                                <span class="font-medium">R$ {{ number_format($total, 2, ',', '.') }}</span>
                            </div>
                            
                            <div class="flex justify-between">
                                <span class="text-gray-600">Taxa de entrega:</span>
                                <span class="font-medium">R$ 5,00</span>
                            </div>
                            
                            <hr class="border-gray-200">
                            
                            <div class="flex justify-between text-lg font-bold">
                                <span>Total:</span>
                                <span class="text-orange-600">R$ {{ number_format($total + 5, 2, ',', '.') }}</span>
                            </div>
                        </div>
                        
                        <a href="{{ route('checkout.index') }}" 
                           class="w-full btn-primary text-center block">
                            <i class="fas fa-credit-card mr-2"></i>
                            Finalizar Pedido
                        </a>
                        
                        <p class="text-xs text-gray-500 text-center mt-3">
                            * Taxa de entrega pode variar conforme a distância
                        </p>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Atualizar quantidade
    function updateQuantity(productId, newQuantity) {
        if (newQuantity < 0) return;

        console.log('Atualizando quantidade:', productId, '->', newQuantity);

        // Primeiro tentar API
        fetch('/cart/update', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.csrfToken
            },
            body: JSON.stringify({
                product_id: productId,
                quantity: newQuantity
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('API funcionou:', data);
                // Tentar atualizar localStorage com dados da API
                updateLocalStorageFromAPI(data);
                updateCartInterface(productId, newQuantity, data.total);
            } else {
                throw new Error('API retornou erro');
            }
        })
        .catch(error => {
            console.warn('API falhou, usando localStorage:', error);
            // Fallback: usar localStorage
            updateLocalStorageFromCart(productId, newQuantity);
            updateCartInterface(productId, newQuantity);
        });
    }

    // Atualizar interface do carrinho
    function updateCartInterface(productId, newQuantity, newTotal) {
        if (newQuantity === 0) {
            // Remove item da interface
            const itemElement = document.getElementById(`cart-item-${productId}`);
            if (itemElement) {
                itemElement.remove();

                // Verifica se carrinho está vazio
                if (document.querySelectorAll('[id^="cart-item-"]').length === 0) {
                    location.reload();
                }
            }
        } else {
            // Atualiza quantidade na interface
            const quantitySpan = document.querySelector(`#cart-item-${productId} .min-w-\\[3rem\\]`);
            if (quantitySpan) {
                quantitySpan.textContent = newQuantity;
            }

            // Atualiza preço total do item
            const currentItem = document.getElementById(`cart-item-${productId}`);
            if (currentItem) {
                const unitPriceText = currentItem.querySelector('.text-gray-600').textContent;
                const unitPrice = parseFloat(unitPriceText.replace('R$ ', '').replace(',', '.'));
                const totalPrice = newQuantity * unitPrice;

                const priceElement = currentItem.querySelector('.font-semibold.text-lg');
                if (priceElement) {
                    priceElement.textContent = `R$ ${totalPrice.toFixed(2).replace('.', ',')}`;
                }
            }
        }

        // Atualiza total se fornecido
        if (newTotal !== undefined) {
            updateTotal(newTotal);
        }

        // Atualiza total se não foi fornecido
        if (newTotal === undefined) {
            recalculateTotalFromPage();
        }

        // Atualiza contador do carrinho
        if (typeof window.updateCartCount === 'function') {
            window.updateCartCount();
        }
    }

    // Atualizar localStorage com dados da API (SMART)
    function updateLocalStorageFromAPI(data) {
        try {
            // Se a API retornou os dados do carrinho completo, usar eles
            if (data.cart) {
                localStorage.setItem('cart', JSON.stringify(data.cart));
                console.log('✅ localStorage atualizado com dados da API:', data.cart);
            } else {
                // Para operações simples (add/update), não precisamos buscar dados completos
                // Apenas confiar que a API atualizou o servidor corretamente
                console.log('✅ API atualizada, mantendo localStorage atual');
            }
        } catch (e) {
            console.error('Erro ao atualizar localStorage com API:', e);
        }
    }

    // Atualizar localStorage a partir dos dados do carrinho
    function updateLocalStorageFromCart(productId, newQuantity) {
        try {
            let cart = JSON.parse(localStorage.getItem('cart') || '{}');

            if (newQuantity === 0) {
                // Remove item
                delete cart[productId];
            } else {
                // Atualiza ou adiciona item
                if (cart[productId]) {
                    cart[productId].quantity = parseInt(newQuantity);
                } else {
                    // Se não existe, precisa buscar do servidor ou estimar
                    cart[productId] = {
                        product_id: productId,
                        quantity: parseInt(newQuantity),
                        added_at: new Date().toISOString()
                    };
                }
            }

            localStorage.setItem('cart', JSON.stringify(cart));
            console.log('localStorage atualizado (fallback):', cart);
        } catch (e) {
            console.error('Erro ao atualizar localStorage:', e);
        }
    }
    
    // Remover item
    function removeItem(productId) {
        if (confirm('Deseja remover este item do carrinho?')) {
            updateQuantity(productId, 0);
        }
    }
    
    // Limpar carrinho
    function clearCart() {
        if (confirm('Deseja limpar todo o carrinho?')) {
            fetch('{{ route("cart.clear") }}', {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': window.csrfToken
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                showNotification('Erro ao limpar carrinho', 'error');
            });
        }
    }
    
    // Atualizar total
    function updateTotal(newTotal) {
        // Atualizar subtotal
        const subtotalElement = document.querySelector('.font-medium');
        if (subtotalElement && !subtotalElement.textContent.includes('5,00')) {
            subtotalElement.textContent = `R$ ${newTotal.toFixed(2).replace('.', ',')}`;
        }

        // Atualizar total (subtotal + entrega)
        const totalElement = document.querySelector('.text-orange-600');
        if (totalElement) {
            const deliveryFee = 5.00;
            const finalTotal = newTotal + deliveryFee;
            totalElement.textContent = `R$ ${finalTotal.toFixed(2).replace('.', ',')}`;
        }
    }

    // Função para recalcular total da página
    function recalculateTotalFromPage() {
        let total = 0;
        const cartItems = document.querySelectorAll('[id^="cart-item-"]');

        cartItems.forEach(item => {
            const priceText = item.querySelector('.text-gray-600').textContent;
            const unitPrice = parseFloat(priceText.replace('R$ ', '').replace(',', '.'));
            const quantitySpan = item.querySelector('.min-w-\\[3rem\\]');
            const quantity = parseInt(quantitySpan.textContent) || 0;

            total += unitPrice * quantity;
        });

        console.log('Total recalculado:', total);
        updateTotal(total);
        return total;
    }
    
    // Função para sincronizar localStorage com dados do servidor
    function syncLocalStorageWithServer() {
        // Verificar se a API está funcionando primeiro
        fetch('/cart/count', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (response.ok) {
                // API funcionando, sincronizar
                return response.json();
            } else {
                throw new Error('API não disponível');
            }
        })
        .then(data => {
            console.log('API funcionando, dados da API:', data);

            // Buscar dados completos do carrinho
            return fetch('/cart', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });
        })
        .then(response => response.json())
        .then(cartData => {
            if (cartData.cart) {
                localStorage.setItem('cart', JSON.stringify(cartData.cart));
                console.log('✅ localStorage sincronizado com servidor:', cartData.cart);

                // Atualizar contador
                if (typeof window.updateCartCount === 'function') {
                    window.updateCartCount();
                }
            }
        })
        .catch(error => {
            console.warn('API não disponível, mantendo localStorage atual:', error);
            // Não fazer nada, manter localStorage como está
        });
    }

    // Inicialização quando a página carrega
    document.addEventListener('DOMContentLoaded', function() {
        // Aguardar um pouco para garantir que tudo carregou
        setTimeout(function() {
            // Tentar sincronizar localStorage com dados do servidor (se API disponível)
            syncLocalStorageWithServer();

            // Atualizar contador do carrinho
            if (typeof window.updateCartCount === 'function') {
                window.updateCartCount();
            }
        }, 200);
    });

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
