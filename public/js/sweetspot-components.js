/*
 * SweetSpot Components Library
 * Componentes reutilizáveis para o tema SweetSpot
 */

// Componente de Produto
class SweetSpotProductCard {
    constructor(product, onClickHandler) {
        this.product = product;
        this.onClick = onClickHandler;
        this.element = this.createElement();
    }
    
    createElement() {
        const card = document.createElement('div');
        card.className = 'sweetspot-product-card sweetspot-animated';
        card.dataset.productId = this.product.id;
        card.dataset.productName = this.product.name;
        card.dataset.productPrice = this.product.price;
        card.dataset.category = this.product.category || 'outros';
        card.dataset.hasVariants = this.product.hasVariants ? 'true' : 'false';
        card.dataset.variants = JSON.stringify(this.product.variants || []);
        
        card.innerHTML = `
            <div class="sweetspot-product-image">
                <i data-lucide="${this.getIconForCategory(this.product.category)}" class="w-6 h-6"></i>
            </div>
            <div class="sweetspot-product-name">${this.product.name}</div>
            <div class="sweetspot-product-category">${this.product.categoryName || this.product.category || 'Outros'}</div>
            <div class="sweetspot-product-price">
                ${this.product.hasVariants 
                    ? `A partir de R$ ${this.formatPrice(this.product.price)}` 
                    : `R$ ${this.formatPrice(this.product.price)}`}
            </div>
        `;
        
        if (this.onClick) {
            card.addEventListener('click', () => this.onClick(this.product));
        }
        
        return card;
    }
    
    getIconForCategory(category) {
        const icons = {
            'bolos': 'cake',
            'paes': 'bread',
            'doces': 'cookie',
            'salgados': 'croissant',
            'bebidas': 'coffee',
            'default': 'package'
        };
        return icons[category?.toLowerCase()] || icons.default;
    }
    
    formatPrice(price) {
        return parseFloat(price).toFixed(2).replace('.', ',');
    }
    
    getElement() {
        return this.element;
    }
    
    update(product) {
        this.product = product;
        this.element.dataset.productName = product.name;
        this.element.dataset.productPrice = product.price;
        this.element.querySelector('.sweetspot-product-name').textContent = product.name;
        this.element.querySelector('.sweetspot-product-price').innerHTML = 
            product.hasVariants 
                ? `A partir de R$ ${this.formatPrice(product.price)}` 
                : `R$ ${this.formatPrice(product.price)}`;
    }
}

// Componente de Item do Carrinho
class SweetSpotCartItem {
    constructor(item, onUpdateQuantity, onRemove) {
        this.item = item;
        this.onUpdateQuantity = onUpdateQuantity;
        this.onRemove = onRemove;
        this.element = this.createElement();
    }
    
    createElement() {
        const itemEl = document.createElement('div');
        itemEl.className = 'sweetspot-cart-item';
        
        itemEl.innerHTML = `
            <div class="sweetspot-item-info">
                <div class="sweetspot-item-name">${this.item.name}</div>
                <div class="sweetspot-item-price">R$ ${this.formatPrice(this.item.price)} un</div>
            </div>
            <div class="sweetspot-item-controls">
                <button class="sweetspot-quantity-btn" data-action="decrease">
                    <i data-lucide="minus" class="w-3 h-3"></i>
                </button>
                <span class="sweetspot-quantity-display">${this.item.quantity}</span>
                <button class="sweetspot-quantity-btn" data-action="increase">
                    <i data-lucide="plus" class="w-3 h-3"></i>
                </button>
            </div>
            <div class="sweetspot-item-total">R$ ${this.formatPrice(this.item.price * this.item.quantity)}</div>
            <div class="sweetspot-item-actions">
                <button class="sweetspot-remove-btn">
                    <i data-lucide="trash-2" class="w-3 h-3"></i>
                </button>
            </div>
        `;
        
        // Event listeners
        itemEl.querySelectorAll('.sweetspot-quantity-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const action = btn.dataset.action;
                const delta = action === 'increase' ? 1 : -1;
                if (this.onUpdateQuantity) {
                    this.onUpdateQuantity(this.item, delta);
                }
            });
        });
        
        itemEl.querySelector('.sweetspot-remove-btn').addEventListener('click', () => {
            if (this.onRemove) {
                this.onRemove(this.item);
            }
        });
        
        return itemEl;
    }
    
    formatPrice(price) {
        return parseFloat(price).toFixed(2).replace('.', ',');
    }
    
    update(item) {
        this.item = item;
        this.element.querySelector('.sweetspot-quantity-display').textContent = item.quantity;
        this.element.querySelector('.sweetspot-item-total').textContent = 
            `R$ ${this.formatPrice(item.price * item.quantity)}`;
    }
    
    getElement() {
        return this.element;
    }
}

// Componente de Toggle de Entrega
class SweetSpotDeliveryToggle {
    constructor(onChange) {
        this.onChange = onChange;
        this.currentType = 'delivery';
        this.element = this.createElement();
    }
    
    createElement() {
        const container = document.createElement('div');
        container.className = 'sweetspot-delivery-toggle';
        
        container.innerHTML = `
            <button type="button" class="sweetspot-delivery-btn" data-type="pickup">
                Retirada
            </button>
            <button type="button" class="sweetspot-delivery-btn active" data-type="delivery">
                Entrega
            </button>
        `;
        
        container.querySelectorAll('.sweetspot-delivery-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                this.setType(btn.dataset.type);
            });
        });
        
        return container;
    }
    
    setType(type) {
        this.currentType = type;
        
        // Update UI
        this.element.querySelectorAll('.sweetspot-delivery-btn').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.type === type);
        });
        
        // Callback
        if (this.onChange) {
            this.onChange(type);
        }
    }
    
    getType() {
        return this.currentType;
    }
    
    getElement() {
        return this.element;
    }
}

// Componente de Busca de Cliente
class SweetSpotCustomerSearch {
    constructor(onSelectCustomer, onNewCustomer) {
        this.onSelectCustomer = onSelectCustomer;
        this.onNewCustomer = onNewCustomer;
        this.element = this.createElement();
    }
    
    createElement() {
        const container = document.createElement('div');
        container.className = 'sweetspot-customer-section';
        
        container.innerHTML = `
            <div class="sweetspot-customer-search">
                <div class="relative flex-1">
                    <i data-lucide="user" class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                    <input 
                        type="text" 
                        class="sweetspot-customer-input pl-10"
                        placeholder="Buscar cliente..."
                    >
                </div>
                <button type="button" class="sweetspot-new-customer-btn">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                </button>
            </div>
            <div class="hidden mt-2 max-h-40 overflow-y-auto border rounded-lg bg-white shadow-lg"></div>
            <input type="hidden" class="customer-id">
            
            <div class="sweetspot-selected-customer hidden">
                <div class="flex justify-between items-center">
                    <div>
                        <div class="font-semibold" id="selected-customer-name"></div>
                        <div class="text-xs text-gray-600" id="selected-customer-info"></div>
                    </div>
                    <button type="button" class="text-gray-400 hover:text-red-500">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>
        `;
        
        // Event listeners
        const searchInput = container.querySelector('.sweetspot-customer-input');
        const newCustomerBtn = container.querySelector('.sweetspot-new-customer-btn');
        const clearBtn = container.querySelector('.sweetspot-selected-customer button');
        
        if (searchInput) {
            let searchTimeout;
            searchInput.addEventListener('input', (e) => {
                clearTimeout(searchTimeout);
                const query = e.target.value.trim();
                
                if (query.length >= 2) {
                    searchTimeout = setTimeout(() => {
                        this.searchCustomers(query);
                    }, 300);
                } else {
                    this.hideResults();
                }
            });
        }
        
        if (newCustomerBtn) {
            newCustomerBtn.addEventListener('click', () => {
                if (this.onNewCustomer) {
                    this.onNewCustomer();
                }
            });
        }
        
        if (clearBtn) {
            clearBtn.addEventListener('click', () => {
                this.clearSelection();
            });
        }
        
        return container;
    }
    
    searchCustomers(query) {
        // Mock search - implement real API call
        const results = [
            { id: 1, name: 'Maria Silva', phone: '(11) 99999-9999', email: 'maria@email.com' },
            { id: 2, name: 'João Santos', phone: '(11) 88888-8888', email: 'joao@email.com' },
            { id: 3, name: 'Ana Costa', phone: '(11) 77777-7777', email: 'ana@email.com' }
        ].filter(c => c.name.toLowerCase().includes(query.toLowerCase()));
        
        this.showResults(results);
    }
    
    showResults(customers) {
        const resultsContainer = this.element.querySelector('.max-h-40');
        if (!resultsContainer) return;
        
        if (customers.length > 0) {
            resultsContainer.innerHTML = customers.map(customer => `
                <div class="p-2 hover:bg-gray-100 cursor-pointer customer-result-option"
                     data-id="${customer.id}" 
                     data-name="${customer.name}"
                     data-phone="${customer.phone}"
                     data-email="${customer.email}">
                    <div class="font-medium">${customer.name}</div>
                    <div class="text-sm text-gray-500">${customer.phone}</div>
                    ${customer.email ? `<div class="text-xs text-gray-400">${customer.email}</div>` : ''}
                </div>
            `).join('');
            
            resultsContainer.classList.remove('hidden');
            
            // Add event listeners to results
            resultsContainer.querySelectorAll('.customer-result-option').forEach(option => {
                option.addEventListener('click', () => {
                    const customer = {
                        id: option.dataset.id,
                        name: option.dataset.name,
                        phone: option.dataset.phone,
                        email: option.dataset.email
                    };
                    this.selectCustomer(customer);
                });
            });
        } else {
            resultsContainer.innerHTML = '<div class="p-2 text-gray-500">Nenhum cliente encontrado</div>';
            resultsContainer.classList.remove('hidden');
        }
    }
    
    hideResults() {
        const resultsContainer = this.element.querySelector('.max-h-40');
        if (resultsContainer) {
            resultsContainer.classList.add('hidden');
        }
    }
    
    selectCustomer(customer) {
        // Update UI
        const nameEl = this.element.querySelector('#selected-customer-name');
        const infoEl = this.element.querySelector('#selected-customer-info');
        const selectedEl = this.element.querySelector('.sweetspot-selected-customer');
        const idInput = this.element.querySelector('.customer-id');
        
        if (nameEl) nameEl.textContent = customer.name;
        if (infoEl) infoEl.textContent = customer.phone;
        if (selectedEl) selectedEl.classList.remove('hidden');
        if (idInput) idInput.value = customer.id;
        
        // Clear search
        const searchInput = this.element.querySelector('.sweetspot-customer-input');
        if (searchInput) searchInput.value = '';
        
        this.hideResults();
        
        // Callback
        if (this.onSelectCustomer) {
            this.onSelectCustomer(customer);
        }
    }
    
    clearSelection() {
        const selectedEl = this.element.querySelector('.sweetspot-selected-customer');
        const idInput = this.element.querySelector('.customer-id');
        
        if (selectedEl) selectedEl.classList.add('hidden');
        if (idInput) idInput.value = '';
        
        if (this.onSelectCustomer) {
            this.onSelectCustomer(null);
        }
    }
    
    getElement() {
        return this.element;
    }
}

// Componente de Resumo do Pedido
class SweetSpotOrderSummary {
    constructor() {
        this.items = [];
        this.deliveryFee = 0;
        this.discount = 0;
        this.element = this.createElement();
    }
    
    createElement() {
        const container = document.createElement('div');
        container.className = 'sweetspot-summary';
        
        container.innerHTML = `
            <div class="sweetspot-summary-row">
                <span class="sweetspot-summary-label">Subtotal (<span id="items-count">0</span> itens)</span>
                <span class="sweetspot-summary-value" id="subtotal-value">R$ 0,00</span>
            </div>
            <div class="sweetspot-summary-row">
                <span class="sweetspot-summary-label">Entrega</span>
                <span class="sweetspot-summary-value" id="delivery-value">R$ 0,00</span>
            </div>
            <div class="sweetspot-summary-row hidden text-green-600" id="discount-row">
                <span class="sweetspot-summary-label">Desconto</span>
                <span class="sweetspot-summary-value" id="discount-value">- R$ 0,00</span>
            </div>
            <div class="sweetspot-total-row">
                <span class="sweetspot-total-label">Total</span>
                <span class="sweetspot-total-value" id="total-value">R$ 0,00</span>
            </div>
        `;
        
        return container;
    }
    
    update(items, deliveryFee = 0, discount = 0) {
        this.items = items;
        this.deliveryFee = deliveryFee;
        this.discount = discount;
        
        const itemsCount = items.reduce((sum, item) => sum + item.quantity, 0);
        const subtotal = items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        const total = Math.max(0, subtotal + deliveryFee - discount);
        
        // Update UI
        const itemsCountEl = this.element.querySelector('#items-count');
        const subtotalEl = this.element.querySelector('#subtotal-value');
        const deliveryEl = this.element.querySelector('#delivery-value');
        const discountRow = this.element.querySelector('#discount-row');
        const discountEl = this.element.querySelector('#discount-value');
        const totalEl = this.element.querySelector('#total-value');
        
        if (itemsCountEl) itemsCountEl.textContent = itemsCount;
        if (subtotalEl) subtotalEl.textContent = `R$ ${this.formatPrice(subtotal)}`;
        if (deliveryEl) deliveryEl.textContent = `R$ ${this.formatPrice(deliveryFee)}`;
        if (totalEl) totalEl.textContent = `R$ ${this.formatPrice(total)}`;
        
        if (discountRow && discountEl) {
            if (discount > 0) {
                discountRow.classList.remove('hidden');
                discountEl.textContent = `- R$ ${this.formatPrice(discount)}`;
            } else {
                discountRow.classList.add('hidden');
            }
        }
    }
    
    formatPrice(price) {
        return parseFloat(price).toFixed(2).replace('.', ',');
    }
    
    getElement() {
        return this.element;
    }
}

// Exportar componentes
window.SweetSpotComponents = {
    ProductCard: SweetSpotProductCard,
    CartItem: SweetSpotCartItem,
    DeliveryToggle: SweetSpotDeliveryToggle,
    CustomerSearch: SweetSpotCustomerSearch,
    OrderSummary: SweetSpotOrderSummary
};