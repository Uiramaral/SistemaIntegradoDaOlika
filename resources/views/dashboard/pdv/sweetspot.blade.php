@extends('dashboard.layouts.app')

@section('page_title', 'PDV - Ponto de Venda')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/sweetspot-theme.css') }}">
<style>
    /* Estilos adicionais para cart toggle mobile */
    .sweetspot-cart-toggle {
        display: none;
    }
    
    .sweetspot-cart-total {
        font-size: 1.125rem;
        font-weight: 700;
        color: white;
    }
    
    @media (max-width: 768px) {
        .sweetspot-cart-toggle {
            display: flex !important;
        }
    }
</style>
@endpush

@section('content')
<div class="sweetspot-theme sweetspot-pdv-container">
    <!-- Header -->
    <header class="sweetspot-pdv-header">
        <div class="sweetspot-header-content">
            <div class="sweetspot-brand">
                <div class="sweetspot-logo">BK</div>
                <h1 class="sweetspot-brand-text">SweetSpot Bakery</h1>
            </div>
            <div class="sweetspot-user-info">
                <span>Bem-vindo, {{ Auth::user()->name }}</span>
                <i data-lucide="user" class="w-4 h-4"></i>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="sweetspot-pdv-main">
        <!-- Products Panel -->
        <div class="sweetspot-products-panel">
            <!-- Search and Filters -->
            <div class="sweetspot-search-container">
                <div class="relative">
                    <i data-lucide="search" class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400"></i>
                    <input
                        type="text"
                        id="product-search"
                        class="sweetspot-search-input pl-10"
                        placeholder="Buscar produtos..."
                        autocomplete="off"
                    >
                </div>
                
                <div class="sweetspot-categories">
                    <button type="button" class="sweetspot-category-btn active" data-category="all">
                        Todos
                    </button>
                    <button type="button" class="sweetspot-category-btn" data-category="bolos">
                        Bolos
                    </button>
                    <button type="button" class="sweetspot-category-btn" data-category="paes">
                        Pães
                    </button>
                    <button type="button" class="sweetspot-category-btn" data-category="doces">
                        Doces
                    </button>
                    <button type="button" class="sweetspot-category-btn" data-category="salgados">
                        Salgados
                    </button>
                    <button type="button" class="sweetspot-category-btn" data-category="bebidas">
                        Bebidas
                    </button>
                </div>
            </div>

            <!-- Products Grid -->
            <div class="sweetspot-products-grid" id="products-grid">
                @foreach($products as $product)
                    @php
                        $variantsActive = $product->variants()->where('is_active', true)->orderBy('sort_order')->get();
                        $hasVariants = $variantsActive->count() > 0;
                        $displayPrice = $hasVariants ? $variantsActive->first()->price : $product->price;
                        $categoryName = $product->category?->name ?? 'Outros';
                    @endphp
                    
                    <div 
                        class="sweetspot-product-card sweetspot-animated"
                        data-product-id="{{ $product->id }}"
                        data-product-name="{{ $product->name }}"
                        data-product-price="{{ $displayPrice }}"
                        data-category="{{ $product->category?->slug ?? 'outros' }}"
                        data-has-variants="{{ $hasVariants ? 'true' : 'false' }}"
                        data-variants="{{ json_encode($variantsActive->map(fn($v) => ['id' => $v->id, 'name' => $v->name, 'price' => (float)$v->price])) }}"
                    >
                        <div class="sweetspot-product-image">
                            <i data-lucide="package" class="w-6 h-6"></i>
                        </div>
                        <div class="sweetspot-product-name">{{ $product->name }}</div>
                        <div class="sweetspot-product-category">{{ $categoryName }}</div>
                        <div class="sweetspot-product-price">
                            @if($hasVariants)
                                A partir de R$ {{ number_format($displayPrice, 2, ',', '.') }}
                            @else
                                R$ {{ number_format($displayPrice, 2, ',', '.') }}
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Cart Panel -->
        <div class="sweetspot-cart-panel" id="cart-panel">
            <!-- Mobile Cart Toggle -->
            <div class="sweetspot-cart-toggle" id="cart-toggle" style="display: none;">
                <div class="sweetspot-cart-summary">
                    <i data-lucide="shopping-cart" class="w-5 h-5"></i>
                    <span id="cart-toggle-text">Carrinho vazio</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="sweetspot-cart-badge-mobile" id="cart-toggle-badge">0</span>
                    <span class="sweetspot-cart-total" id="cart-toggle-total">R$ 0,00</span>
                    <i data-lucide="chevron-up" class="w-5 h-5 transition-transform" id="cart-chevron"></i>
                </div>
            </div>
            
            <div class="sweetspot-cart-header">
                <div class="sweetspot-cart-title">
                    <i data-lucide="shopping-cart" class="w-5 h-5"></i>
                    Pedido
                    <span class="sweetspot-cart-badge" id="order-items-badge">0</span>
                </div>
            </div>
            
            <div class="sweetspot-cart-body">
                <!-- Customer Section -->
                <div class="sweetspot-customer-section">
                    <div class="sweetspot-customer-search">
                        <div class="relative flex-1">
                            <i data-lucide="user" class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                            <input 
                                type="text" 
                                id="customer-search" 
                                class="sweetspot-customer-input pl-10"
                                placeholder="Buscar cliente..."
                            >
                        </div>
                        <button type="button" id="btn-new-customer" class="sweetspot-new-customer-btn">
                            <i data-lucide="plus" class="w-4 h-4"></i>
                        </button>
                    </div>
                    <div id="customer-results" class="hidden mt-2 max-h-40 overflow-y-auto border rounded-lg bg-white shadow-lg"></div>
                    <input type="hidden" id="customer-id">
                    
                    <div id="selected-customer" class="sweetspot-selected-customer hidden">
                        <div class="flex justify-between items-center">
                            <div>
                                <div class="font-semibold" id="selected-customer-name"></div>
                                <div class="text-xs text-gray-600" id="selected-customer-info"></div>
                            </div>
                            <button type="button" id="btn-clear-customer" class="text-gray-400 hover:text-red-500">
                                <i data-lucide="x" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Order Items -->
                <div class="sweetspot-order-items" id="order-items-list">
                    <div class="sweetspot-empty-cart">
                        <div class="sweetspot-empty-cart-icon">
                            <i data-lucide="shopping-cart" class="w-12 h-12"></i>
                        </div>
                        <div>Carrinho vazio</div>
                        <div class="text-xs mt-1">Adicione produtos para começar</div>
                    </div>
                </div>

                <!-- Delivery Section -->
                <div class="sweetspot-delivery-section">
                    <div class="sweetspot-delivery-toggle">
                        <button type="button" id="btn-pickup" class="sweetspot-delivery-btn">
                            Retirada
                        </button>
                        <button type="button" id="btn-delivery" class="sweetspot-delivery-btn active">
                            Entrega
                        </button>
                    </div>
                    
                    <div id="delivery-address-section" class="sweetspot-delivery-address">
                        <input type="text" id="destination-cep" maxlength="9" class="sweetspot-address-input" placeholder="CEP">
                        <input type="text" id="destination-number" maxlength="10" class="w-20 sweetspot-address-input" placeholder="Nº">
                        <button type="button" id="btn-calculate-fee" class="sweetspot-calc-btn">
                            Calc
                        </button>
                    </div>
                    
                    <div class="sweetspot-fee-display">
                        <span class="sweetspot-fee-label">Taxa de entrega:</span>
                        <span class="sweetspot-fee-value" id="delivery-fee-display">R$ 0,00</span>
                    </div>
                    <div id="delivery-fee-info" class="hidden text-xs text-gray-500 mt-1"></div>
                </div>

                <!-- Coupon Section -->
                <div class="sweetspot-coupon-section">
                    <div class="sweetspot-coupon-input">
                        <input type="text" id="coupon-code" class="sweetspot-coupon-field" placeholder="Cupom de desconto">
                        <button type="button" id="btn-apply-coupon" class="sweetspot-apply-coupon-btn">
                            Aplicar
                        </button>
                    </div>
                    <div id="coupon-info" class="hidden mt-2 p-2 bg-green-50 border border-green-200 rounded text-xs text-green-700"></div>
                </div>

                <!-- Summary -->
                <div class="sweetspot-summary">
                    <div class="sweetspot-summary-row">
                        <span class="sweetspot-summary-label">Subtotal (<span id="order-items-count">0</span> itens)</span>
                        <span class="sweetspot-summary-value" id="summary-subtotal">R$ 0,00</span>
                    </div>
                    <div class="sweetspot-summary-row">
                        <span class="sweetspot-summary-label">Entrega</span>
                        <span class="sweetspot-summary-value" id="summary-delivery-fee">R$ 0,00</span>
                    </div>
                    <div class="sweetspot-summary-row hidden" id="discount-row">
                        <span class="sweetspot-summary-label text-green-600">Desconto</span>
                        <span class="sweetspot-summary-value text-green-600" id="summary-discount">- R$ 0,00</span>
                    </div>
                    <div class="sweetspot-total-row">
                        <span class="sweetspot-total-label">Total</span>
                        <span class="sweetspot-total-value" id="summary-total">R$ 0,00</span>
                    </div>
                    
                    <button type="button" id="btn-finalize-order" class="sweetspot-finalize-btn" disabled>
                        Finalizar Pedido
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- New Customer Modal -->
<div id="new-customer-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-gray-800">Novo Cliente</h3>
                <button type="button" id="btn-close-new-customer-modal" class="text-gray-400 hover:text-gray-600">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
            
            <form id="new-customer-form">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nome *</label>
                        <input type="text" id="new-customer-name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Telefone</label>
                        <input type="text" id="new-customer-phone" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" id="new-customer-email" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    </div>
                </div>
                
                <div class="flex gap-3 mt-6">
                    <button type="button" id="btn-cancel-new-customer" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Cancelar
                    </button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                        Criar Cliente
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Estado do PDV
const pdvState = {
    customer: null,
    items: [],
    coupon: null,
    deliveryType: 'delivery',
    deliveryFee: 0
};

// Inicialização
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar ícones
    if (window.lucide) {
        window.lucide.createIcons();
    }
    
    // Inicializar responsividade
    initializeResponsive();
    
    // Inicializar cart toggle mobile
    initializeCartToggle();
    
    // Event listeners para categorias
    document.querySelectorAll('.sweetspot-category-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            // Remover classe active de todos
            document.querySelectorAll('.sweetspot-category-btn').forEach(b => {
                b.classList.remove('active');
            });
            // Adicionar active ao clicado
            this.classList.add('active');
            
            const category = this.dataset.category;
            filterProducts(category);
        });
    });
    
    // Event listeners para produtos
    document.querySelectorAll('.sweetspot-product-card').forEach(card => {
        card.addEventListener('click', function() {
            const productId = this.dataset.productId;
            const productName = this.dataset.productName;
            const productPrice = parseFloat(this.dataset.productPrice);
            const hasVariants = this.dataset.hasVariants === 'true';
            
            if (hasVariants) {
                // Mostrar modal de variantes
                showVariantSelection(productId, productName, JSON.parse(this.dataset.variants));
            } else {
                // Adicionar diretamente
                addItem({
                    product_id: productId,
                    name: productName,
                    price: productPrice,
                    quantity: 1
                });
            }
        });
    });
    
    // Toggle entrega/retirada
    document.getElementById('btn-pickup').addEventListener('click', function() {
        setDeliveryType('pickup');
    });
    
    document.getElementById('btn-delivery').addEventListener('click', function() {
        setDeliveryType('delivery');
    });
    
    // Botão finalizar
    document.getElementById('btn-finalize-order').addEventListener('click', finalizeOrder);
    
    // Busca de clientes
    document.getElementById('customer-search').addEventListener('input', function(e) {
        searchCustomers(e.target.value);
    });
    
    // Novo cliente
    document.getElementById('btn-new-customer').addEventListener('click', function() {
        document.getElementById('new-customer-modal').classList.remove('hidden');
    });
    
    document.getElementById('btn-close-new-customer-modal').addEventListener('click', function() {
        document.getElementById('new-customer-modal').classList.add('hidden');
    });
    
    document.getElementById('btn-cancel-new-customer').addEventListener('click', function() {
        document.getElementById('new-customer-modal').classList.add('hidden');
    });
    
    // Form submit
    document.getElementById('new-customer-form').addEventListener('submit', function(e) {
        e.preventDefault();
        createCustomer();
    });
});

// Funções auxiliares
function filterProducts(category) {
    const products = document.querySelectorAll('.sweetspot-product-card');
    products.forEach(product => {
        if (category === 'all' || product.dataset.category === category) {
            product.style.display = 'block';
        } else {
            product.style.display = 'none';
        }
    });
}

function showVariantSelection(productId, productName, variants) {
    // Implementar modal de seleção de variantes
    const variant = variants[0]; // Por enquanto pega a primeira
    addItem({
        product_id: productId,
        name: `${productName} - ${variant.name}`,
        price: variant.price,
        quantity: 1
    });
}

function addItem(item) {
    // Verificar se item já existe
    const existingItem = pdvState.items.find(i => 
        i.product_id === item.product_id && 
        Math.abs(i.price - item.price) < 0.01
    );
    
    if (existingItem) {
        existingItem.quantity += item.quantity;
    } else {
        pdvState.items.push({
            product_id: item.product_id,
            name: item.name,
            price: item.price,
            quantity: item.quantity
        });
    }
    
    renderCart();
    updateSummary();
    updateFinalizeButton();
    updateCartToggle();
    expandCartOnMobile();
}

function renderCart() {
    const container = document.getElementById('order-items-list');
    const badge = document.getElementById('order-items-badge');
    
    const totalItems = pdvState.items.reduce((sum, item) => sum + item.quantity, 0);
    
    // Atualizar badge
    if (badge) {
        badge.textContent = totalItems;
        badge.classList.toggle('hidden', totalItems === 0);
    }
    
    if (pdvState.items.length === 0) {
        container.innerHTML = `
            <div class="sweetspot-empty-cart">
                <div class="sweetspot-empty-cart-icon">
                    <i data-lucide="shopping-cart" class="w-12 h-12"></i>
                </div>
                <div>Carrinho vazio</div>
                <div class="text-xs mt-1">Adicione produtos para começar</div>
            </div>
        `;
        if (window.lucide) window.lucide.createIcons();
        return;
    }
    
    container.innerHTML = pdvState.items.map((item, index) => `
        <div class="sweetspot-cart-item">
            <div class="sweetspot-item-info">
                <div class="sweetspot-item-name">${item.name}</div>
                <div class="sweetspot-item-price">R$ ${item.price.toFixed(2).replace('.', ',')} un</div>
            </div>
            <div class="sweetspot-item-controls">
                <button class="sweetspot-quantity-btn" onclick="updateQuantity(${index}, -1)">
                    <i data-lucide="minus" class="w-3 h-3"></i>
                </button>
                <span class="sweetspot-quantity-display">${item.quantity}</span>
                <button class="sweetspot-quantity-btn" onclick="updateQuantity(${index}, 1)">
                    <i data-lucide="plus" class="w-3 h-3"></i>
                </button>
            </div>
            <div class="sweetspot-item-total">R$ ${(item.price * item.quantity).toFixed(2).replace('.', ',')}</div>
            <div class="sweetspot-item-actions">
                <button class="sweetspot-remove-btn" onclick="removeItem(${index})">
                    <i data-lucide="trash-2" class="w-3 h-3"></i>
                </button>
            </div>
        </div>
    `).join('');
    
    if (window.lucide) window.lucide.createIcons();
}

function updateQuantity(index, delta) {
    pdvState.items[index].quantity = Math.max(1, pdvState.items[index].quantity + delta);
    renderCart();
    updateSummary();
    updateFinalizeButton();
    updateCartToggle();
}

function removeItem(index) {
    pdvState.items.splice(index, 1);
    renderCart();
    updateSummary();
    updateFinalizeButton();
    updateCartToggle();
}

function updateSummary() {
    const itemsCount = pdvState.items.reduce((sum, item) => sum + item.quantity, 0);
    const subtotal = pdvState.items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const deliveryFee = parseFloat(document.getElementById('delivery-fee-display')?.textContent?.replace('R$ ', '').replace(',', '.') || 0);
    const discount = pdvState.coupon ? (pdvState.coupon.discount || 0) : 0;
    const total = Math.max(0, subtotal + deliveryFee - discount);
    
    document.getElementById('order-items-count').textContent = itemsCount;
    document.getElementById('summary-subtotal').textContent = `R$ ${subtotal.toFixed(2).replace('.', ',')}`;
    document.getElementById('summary-delivery-fee').textContent = `R$ ${deliveryFee.toFixed(2).replace('.', ',')}`;
    document.getElementById('summary-total').textContent = `R$ ${total.toFixed(2).replace('.', ',')}`;
    
    const discountRow = document.getElementById('discount-row');
    const discountValue = document.getElementById('summary-discount');
    if (discount > 0) {
        discountRow.classList.remove('hidden');
        discountValue.textContent = `- R$ ${discount.toFixed(2).replace('.', ',')}`;
    } else {
        discountRow.classList.add('hidden');
    }
}

function updateFinalizeButton() {
    const btn = document.getElementById('btn-finalize-order');
    const hasCustomer = pdvState.customer !== null;
    const hasItems = pdvState.items.length > 0;
    
    btn.disabled = !(hasCustomer && hasItems);
}

function setDeliveryType(type) {
    pdvState.deliveryType = type;
    
    // Atualizar botões
    document.getElementById('btn-pickup').classList.toggle('active', type === 'pickup');
    document.getElementById('btn-delivery').classList.toggle('active', type === 'delivery');
    
    // Mostrar/esconder campos de endereço
    const addressSection = document.getElementById('delivery-address-section');
    if (addressSection) {
        addressSection.classList.toggle('hidden', type === 'pickup');
    }
    
    // Resetar taxa de entrega para retirada
    if (type === 'pickup') {
        document.getElementById('delivery-fee-display').textContent = 'R$ 0,00';
        updateSummary();
    }
}

function searchCustomers(query) {
    if (query.length < 2) {
        document.getElementById('customer-results').classList.add('hidden');
        return;
    }
    
    // Simular busca - implementar chamada real
    const results = [
        { id: 1, name: 'Maria Silva', phone: '(11) 99999-9999' },
        { id: 2, name: 'João Santos', phone: '(11) 88888-8888' }
    ].filter(c => c.name.toLowerCase().includes(query.toLowerCase()));
    
    const resultsEl = document.getElementById('customer-results');
    if (results.length > 0) {
        resultsEl.innerHTML = results.map(c => `
            <div class="p-2 hover:bg-gray-100 cursor-pointer" onclick="selectCustomer(${c.id}, '${c.name}', '${c.phone}')">
                <div class="font-medium">${c.name}</div>
                <div class="text-sm text-gray-500">${c.phone}</div>
            </div>
        `).join('');
        resultsEl.classList.remove('hidden');
    } else {
        resultsEl.innerHTML = '<div class="p-2 text-gray-500">Nenhum cliente encontrado</div>';
        resultsEl.classList.remove('hidden');
    }
}

function selectCustomer(id, name, phone) {
    pdvState.customer = { id, name, phone };
    document.getElementById('customer-id').value = id;
    document.getElementById('selected-customer-name').textContent = name;
    document.getElementById('selected-customer-info').textContent = phone;
    document.getElementById('selected-customer').classList.remove('hidden');
    document.getElementById('customer-results').classList.add('hidden');
    document.getElementById('customer-search').value = '';
    updateFinalizeButton();
}

function createCustomer() {
    const form = document.getElementById('new-customer-form');
    const name = document.getElementById('new-customer-name').value;
    const phone = document.getElementById('new-customer-phone').value;
    const email = document.getElementById('new-customer-email').value;
    
    // Simular criação - implementar chamada real
    const newCustomer = {
        id: Date.now(),
        name: name,
        phone: phone,
        email: email
    };
    
    selectCustomer(newCustomer.id, newCustomer.name, newCustomer.phone);
    document.getElementById('new-customer-modal').classList.add('hidden');
    form.reset();
}

function finalizeOrder() {
    if (!pdvState.customer || pdvState.items.length === 0) {
        alert('Selecione um cliente e adicione itens ao pedido');
        return;
    }
    
    // Implementar finalização real
    alert('Pedido finalizado com sucesso!');
    console.log('Pedido:', {
        customer: pdvState.customer,
        items: pdvState.items,
        deliveryType: pdvState.deliveryType,
        total: document.getElementById('summary-total').textContent
    });
}

// Funções de Responsividade
function initializeResponsive() {
    function checkScreenSize() {
        const isMobile = window.innerWidth <= 768;
        const cartToggle = document.getElementById('cart-toggle');
        const cartPanel = document.getElementById('cart-panel');
        
        if (isMobile) {
            if (cartToggle) cartToggle.style.display = 'flex';
            if (cartPanel) cartPanel.classList.add('collapsed');
        } else {
            if (cartToggle) cartToggle.style.display = 'none';
            if (cartPanel) cartPanel.classList.remove('collapsed');
        }
    }
    
    // Verificar tamanho inicial
    checkScreenSize();
    
    // Listener para mudanças de tamanho
    window.addEventListener('resize', checkScreenSize);
}

// Funções do Carrinho Mobile
function initializeCartToggle() {
    const cartToggle = document.getElementById('cart-toggle');
    const cartPanel = document.getElementById('cart-panel');
    const cartChevron = document.getElementById('cart-chevron');
    
    if (cartToggle && cartPanel) {
        cartToggle.addEventListener('click', function() {
            cartPanel.classList.toggle('collapsed');
            const isCollapsed = cartPanel.classList.contains('collapsed');
            
            if (cartChevron) {
                cartChevron.style.transform = isCollapsed ? 'rotate(0deg)' : 'rotate(180deg)';
            }
        });
    }
}

// Atualizar display do toggle mobile
function updateCartToggle() {
    const totalItems = pdvState.items.reduce((sum, item) => sum + item.quantity, 0);
    const subtotal = pdvState.items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const deliveryFee = parseFloat(document.getElementById('delivery-fee-display')?.textContent?.replace('R$ ', '').replace(',', '.') || 0);
    const discount = pdvState.coupon ? (pdvState.coupon.discount || 0) : 0;
    const total = Math.max(0, subtotal + deliveryFee - discount);
    
    const toggleText = document.getElementById('cart-toggle-text');
    const toggleBadge = document.getElementById('cart-toggle-badge');
    const toggleTotal = document.getElementById('cart-toggle-total');
    
    if (toggleText) {
        toggleText.textContent = totalItems === 0 ? 'Carrinho vazio' : 
                                `${totalItems} ${totalItems === 1 ? 'item' : 'itens'}`;
    }
    
    if (toggleBadge) {
        toggleBadge.textContent = totalItems;
    }
    
    if (toggleTotal) {
        toggleTotal.textContent = `R$ ${total.toFixed(2).replace('.', ',')}`;
    }
}

// Expandir carrinho ao adicionar item no mobile
function expandCartOnMobile() {
    if (window.innerWidth <= 768) {
        const cartPanel = document.getElementById('cart-panel');
        if (cartPanel && cartPanel.classList.contains('collapsed')) {
            cartPanel.classList.remove('collapsed');
            const cartChevron = document.getElementById('cart-chevron');
            if (cartChevron) {
                cartChevron.style.transform = 'rotate(180deg)';
            }
        }
    }
}
</script>
@endpush

@endsection