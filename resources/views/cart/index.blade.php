@extends('layouts.app')

@section('title', 'Meu Carrinho')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Meu Carrinho</h1>
                    <p class="text-gray-600 mt-2">Revise seus itens antes de finalizar</p>
                </div>
                <a href="{{ route('menu.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Continuar Comprando
                </a>
            </div>

            <!-- Empty Cart (será controlado pelo JS) -->
            <div id="cart-empty" class="text-center py-12" style="display: none;">
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

            <!-- Cart Items (será preenchido pelo JS) -->
            <div id="cart-items-container" class="grid lg:grid-cols-3 gap-8" style="display: none;">
                <!-- Cart Items List -->
                <div class="lg:col-span-2">
                    <div id="cart-items" class="space-y-4">
                        <!-- Itens serão renderizados aqui pelo JavaScript -->
                    </div>
                    
                    <!-- Cart Actions -->
                    <div class="mt-6">
                        <form action="{{ route('cart.clear') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" 
                                    class="text-red-600 hover:text-red-700 font-medium"
                                    onclick="return confirm('Deseja limpar todo o carrinho?')">
                                <i class="fas fa-trash mr-2"></i>
                                Limpar Carrinho
                            </button>
                        </form>
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
                                <span id="cart-subtotal" class="font-medium">R$ 0,00</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Taxa de entrega:</span>
                                <span class="font-medium">R$ 5,00</span>
                            </div>
                            <hr class="border-gray-200">
                            <div class="flex justify-between text-lg font-bold">
                                <span>Total:</span>
                                <span id="cart-total" class="text-blue-600">R$ 0,00</span>
                            </div>
                        </div>
                        
                        <a href="{{ route('checkout.index') }}" 
                           class="btn-primary w-full mb-3 text-center block">
                            <i class="fas fa-credit-card mr-2"></i>
                            Finalizar Pedido
                        </a>
                        
                        <p class="text-xs text-gray-500 text-center mt-3">
                            * Taxa de entrega pode variar conforme a distância
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const BASE_URL = window.location.origin;

    // Função para fazer POST JSON com headers corretos
    async function postJSON(url, payload) {
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',             // <- força Laravel a responder JSON
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',     // <- marca como AJAX
                'X-CSRF-TOKEN': window.csrfToken
            },
            credentials: 'same-origin',                    // cookies / sessão
            body: JSON.stringify(payload),
        });

        const contentType = res.headers.get('content-type') || '';
        if (!contentType.includes('application/json')) {
            const text = await res.text();
            throw new Error('Servidor retornou HTML em vez de JSON: ' + text.slice(0, 120));
        }

        return res.json();
    }

    // Função para fazer GET JSON
    async function getJSON(url) {
        const res = await fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        });

        const contentType = res.headers.get('content-type') || '';
        if (!contentType.includes('application/json')) {
            const text = await res.text();
            throw new Error('Servidor retornou HTML em vez de JSON: ' + text.slice(0, 120));
        }

        return res.json();
    }

    // Hidratar a página a partir do servidor na carga do carrinho
    async function hydrateCart() {
        try {
            const data = await getJSON(`${BASE_URL}/cart/items`);
            
            console.log('✅ Hidratando carrinho do servidor:', data);
            
            // ZERA o localStorage e copia o estado do servidor (fonte da verdade)
            localStorage.setItem('olika_cart', JSON.stringify(data.items || []));
            renderCart(data);
            
            // Atualizar badge global
            if (typeof window.updateCartCount === 'function') {
                window.updateCartCount(data.cart_count);
            }

        } catch (e) {
            console.error('❌ Falha ao hidratar carrinho do servidor:', e);
        }
    }

    // Renderizar carrinho a partir dos dados do servidor
    function renderCart(data) {
        console.log('🎯 Renderizando carrinho:', data);

        const items = data.items || [];
        const emptyEl = document.getElementById('cart-empty');
        const containerEl = document.getElementById('cart-items-container');
        const itemsEl = document.getElementById('cart-items');
        const subtotalEl = document.getElementById('cart-subtotal');
        const totalEl = document.getElementById('cart-total');

        // Atualizar badge global
        if (typeof window.updateCartCount === 'function') {
            window.updateCartCount(data.cart_count);
        }

        // total
        if (subtotalEl) subtotalEl.textContent = `R$ ${(Number(data.total)||0).toFixed(2).replace('.', ',')}`;
        if (totalEl) totalEl.textContent = `R$ ${(Number(data.total)||0 + 5).toFixed(2).replace('.', ',')}`;

        // vazio vs lista
        if (items.length === 0) {
            if (emptyEl) emptyEl.style.display = '';
            if (containerEl) containerEl.style.display = 'none';
            return;
        }

        if (emptyEl) emptyEl.style.display = 'none';
        if (containerEl) containerEl.style.display = '';

        if (itemsEl) {
            itemsEl.innerHTML = items.map(it => `
                <div class="card flex items-center space-x-4" data-product-id="${it.product_id}">
                    <div class="flex-shrink-0">
                        <div class="w-20 h-20 bg-gray-200 rounded-lg flex items-center justify-center">
                            <i class="fas fa-image text-gray-400"></i>
                        </div>
                    </div>
                    
                    <div class="flex-1">
                        <h3 class="font-semibold text-gray-900">
                            ${it.name || 'Produto #' + it.product_id}
                        </h3>
                        <p class="text-sm text-gray-600">
                            R$ ${(Number(it.price)||0).toFixed(2).replace('.', ',')} cada
                        </p>
                    </div>
                    
                    <div class="flex items-center space-x-3">
                        <!-- Quantity Controls -->
                        <div class="flex items-center border border-gray-300 rounded-lg">
                            <button onclick="updateQuantity(${it.product_id}, ${(it.qty||0)-1})" 
                                    class="px-3 py-2 hover:bg-gray-100">
                                <i class="fas fa-minus text-sm"></i>
                            </button>
                            
                            <span class="px-4 py-2 border-x border-gray-300 min-w-[3rem] text-center">
                                ${it.qty || 0}
                            </span>
                            
                            <button onclick="updateQuantity(${it.product_id}, ${(it.qty||0)+1})" 
                                    class="px-3 py-2 hover:bg-gray-100">
                                <i class="fas fa-plus text-sm"></i>
                            </button>
                        </div>
                        
                        <!-- Total Price -->
                        <div class="text-right">
                            <div class="font-semibold text-lg text-gray-900">
                                R$ ${((Number(it.price)||0) * (it.qty||0)).toFixed(2).replace('.', ',')}
                            </div>
                        </div>
                        
                        <!-- Remove Button -->
                        <button onclick="removeItem(${it.product_id})" 
                                class="text-red-500 hover:text-red-700 p-2">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            `).join('');
        }
    }

    // Atualizar quantidade
    async function updateQuantity(productId, newQuantity) {
        if (newQuantity < 0) return;

        console.log('Atualizando quantidade:', productId, '->', newQuantity);

        try {
            const data = await postJSON(`${BASE_URL}/cart/update`, { 
                product_id: productId, 
                qty: newQuantity 
            });
            
            console.log('✅ API funcionou:', data);
            // Atualizar localStorage com dados do servidor (fonte da verdade)
            localStorage.setItem('olika_cart', JSON.stringify(data.items || []));
            renderCart(data);
            
        } catch (error) {
            console.error('❌ API falhou:', error);
            // Só usar localStorage se realmente não conseguir conectar
            updateLocalStorageFromCart(productId, newQuantity);
            updateCartInterface(productId, newQuantity);
        }
    }
    
    // Remover item
    function removeItem(productId) {
        if (confirm('Deseja remover este item do carrinho?')) {
            updateQuantity(productId, 0);
        }
    }
    

    // Função para mostrar notificações
    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg text-white z-50 ${
            type === 'error' ? 'bg-red-500' : 
            type === 'success' ? 'bg-green-500' : 'bg-blue-500'
        }`;
        notification.textContent = message;

        document.body.appendChild(notification);

        setTimeout(() => {
            notification.remove();
        }, 3000);
    }

    // Inicialização quando a página carrega
    document.addEventListener('DOMContentLoaded', function() {
        // Aguardar um pouco para garantir que tudo carregou
        setTimeout(function() {
            // Hidratar do servidor (fonte da verdade)
            hydrateCart();
        }, 200);
    });
</script>
@endpush