@extends('layouts.admin')

@section('title', 'PDV')
@section('page_title', 'Ponto de Venda')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <x-card title="Buscar Produtos">
            <div class="mb-4">
                <input type="text" id="product-search" class="input" placeholder="Digite o nome ou código do produto" autofocus>
            </div>
            
            <div class="h-96 overflow-y-auto border rounded-lg">
                <div id="products-list" class="p-4">
                    <p class="text-gray-500 text-center">Digite para buscar produtos...</p>
                </div>
            </div>
        </x-card>
        
        <x-card title="Itens do Pedido" class="mt-6">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-sm font-medium text-gray-700 border-b">Produto</th>
                            <th class="px-4 py-3 text-sm font-medium text-gray-700 border-b">Qtd</th>
                            <th class="px-4 py-3 text-sm font-medium text-gray-700 border-b">Preço</th>
                            <th class="px-4 py-3 text-sm font-medium text-gray-700 border-b">Total</th>
                            <th class="px-4 py-3 text-sm font-medium text-gray-700 border-b"></th>
                        </tr>
                    </thead>
                    <tbody id="cart-items">
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                Nenhum item adicionado
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </x-card>
    </div>
    
    <div class="space-y-6">
        <x-card title="Cliente">
            <div class="mb-4">
                <input type="text" id="customer-search" class="input" placeholder="Buscar cliente...">
            </div>
            <select id="customer-select" class="input w-full">
                <option value="">Cliente Avulso</option>
                @foreach($customers ?? [] as $customer)
                    <option value="{{ $customer->id }}">{{ $customer->nome ?? 'Cliente' }}</option>
                @endforeach
            </select>
            <div id="customer-info" class="mt-2 text-sm text-gray-600 hidden">
                <div class="p-2 bg-gray-50 rounded">
                    <div class="font-medium" id="customer-name"></div>
                    <div class="text-gray-500" id="customer-details"></div>
                </div>
            </div>
        </x-card>
        
        <x-card title="Resumo do Pedido">
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span>Subtotal:</span>
                    <span id="subtotal">R$ 0,00</span>
                </div>
                <div class="flex justify-between">
                    <span>Taxa de Serviço:</span>
                    <span id="service-fee">R$ 0,00</span>
                </div>
                <div class="flex justify-between">
                    <span>Taxa de Entrega:</span>
                    <span id="delivery-fee">R$ 0,00</span>
                </div>
                <div class="flex justify-between font-bold text-lg border-t pt-3">
                    <span>Total:</span>
                    <span id="total">R$ 0,00</span>
                </div>
            </div>
            
            <div class="mt-6 space-y-2">
                <x-button variant="success" size="lg" class="w-full" onclick="finalizeOrder()">
                    <i class="fas fa-check"></i> Finalizar Venda
                </x-button>
                <x-button variant="secondary" class="w-full" onclick="clearCart()">
                    <i class="fas fa-trash"></i> Limpar Carrinho
                </x-button>
            </div>
        </x-card>
        
        <x-card title="Atalhos">
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span>Buscar produto:</span>
                    <span class="font-mono bg-gray-100 px-2 py-1 rounded">Enter</span>
                </div>
                <div class="flex justify-between">
                    <span>Limpar carrinho:</span>
                    <span class="font-mono bg-gray-100 px-2 py-1 rounded">Esc</span>
                </div>
                <div class="flex justify-between">
                    <span>Finalizar venda:</span>
                    <span class="font-mono bg-gray-100 px-2 py-1 rounded">F2</span>
                </div>
            </div>
        </x-card>
    </div>
</div>

@push('scripts')
<script>
    let cart = [];
    let currentCustomer = null;
    
    // Configurações
    const settings = {
        serviceFee: {{ $settings['service_fee'] ?? 0 }},
        deliveryFee: {{ $settings['delivery_fee'] ?? 0 }}
    };
    
    // Buscar produtos
    document.getElementById('product-search').addEventListener('input', function() {
        const query = this.value;
        if (query.length < 2) {
            document.getElementById('products-list').innerHTML = '<p class="text-gray-500 text-center">Digite pelo menos 2 caracteres...</p>';
            return;
        }
        
        // Simular busca de produtos
        const products = [
            { id: 1, name: 'Hambúrguer Clássico', price: 25.90, code: 'HB001' },
            { id: 2, name: 'Pizza Margherita', price: 35.90, code: 'PZ001' },
            { id: 3, name: 'Batata Frita', price: 12.90, code: 'BF001' },
            { id: 4, name: 'Refrigerante', price: 6.90, code: 'RF001' }
        ];
        
        const filteredProducts = products.filter(p => 
            p.name.toLowerCase().includes(query.toLowerCase()) || 
            p.code.toLowerCase().includes(query.toLowerCase())
        );
        
        if (filteredProducts.length === 0) {
            document.getElementById('products-list').innerHTML = '<p class="text-gray-500 text-center">Nenhum produto encontrado</p>';
            return;
        }
        
        let html = '<div class="space-y-2">';
        filteredProducts.forEach(product => {
            html += `
                <div class="flex justify-between items-center p-3 border rounded-lg hover:bg-gray-50 cursor-pointer" onclick="addToCart(${product.id}, '${product.name}', ${product.price})">
                    <div>
                        <div class="font-medium">${product.name}</div>
                        <div class="text-sm text-gray-500">${product.code}</div>
                    </div>
                    <div class="text-green-600 font-medium">R$ ${product.price.toFixed(2).replace('.', ',')}</div>
                </div>
            `;
        });
        html += '</div>';
        
        document.getElementById('products-list').innerHTML = html;
    });
    
    // Adicionar ao carrinho
    function addToCart(id, name, price) {
        const existingItem = cart.find(item => item.id === id);
        
        if (existingItem) {
            existingItem.quantity += 1;
        } else {
            cart.push({ id, name, price, quantity: 1 });
        }
        
        updateCartDisplay();
        updateTotals();
    }
    
    // Remover do carrinho
    function removeFromCart(id) {
        cart = cart.filter(item => item.id !== id);
        updateCartDisplay();
        updateTotals();
    }
    
    // Atualizar quantidade
    function updateQuantity(id, quantity) {
        if (quantity <= 0) {
            removeFromCart(id);
            return;
        }
        
        const item = cart.find(item => item.id === id);
        if (item) {
            item.quantity = quantity;
            updateCartDisplay();
            updateTotals();
        }
    }
    
    // Atualizar exibição do carrinho
    function updateCartDisplay() {
        const tbody = document.getElementById('cart-items');
        
        if (cart.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">Nenhum item adicionado</td></tr>';
            return;
        }
        
        let html = '';
        cart.forEach(item => {
            const total = item.price * item.quantity;
            html += `
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 border-b font-medium">${item.name}</td>
                    <td class="px-4 py-3 border-b">
                        <div class="flex items-center">
                            <button onclick="updateQuantity(${item.id}, ${item.quantity - 1})" class="w-6 h-6 rounded-full bg-gray-200 flex items-center justify-center">-</button>
                            <span class="mx-2">${item.quantity}</span>
                            <button onclick="updateQuantity(${item.id}, ${item.quantity + 1})" class="w-6 h-6 rounded-full bg-gray-200 flex items-center justify-center">+</button>
                        </div>
                    </td>
                    <td class="px-4 py-3 border-b">R$ ${item.price.toFixed(2).replace('.', ',')}</td>
                    <td class="px-4 py-3 border-b font-medium">R$ ${total.toFixed(2).replace('.', ',')}</td>
                    <td class="px-4 py-3 border-b text-right">
                        <button onclick="removeFromCart(${item.id})" class="text-red-600 hover:text-red-800">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        });
        
        tbody.innerHTML = html;
    }
    
    // Atualizar totais
    function updateTotals() {
        const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        const serviceFeeAmount = subtotal * (settings.serviceFee / 100);
        const total = subtotal + serviceFeeAmount + settings.deliveryFee;
        
        document.getElementById('subtotal').textContent = `R$ ${subtotal.toFixed(2).replace('.', ',')}`;
        document.getElementById('service-fee').textContent = `R$ ${serviceFeeAmount.toFixed(2).replace('.', ',')}`;
        document.getElementById('delivery-fee').textContent = `R$ ${settings.deliveryFee.toFixed(2).replace('.', ',')}`;
        document.getElementById('total').textContent = `R$ ${total.toFixed(2).replace('.', ',')}`;
    }
    
    // Limpar carrinho
    function clearCart() {
        if (confirm('Limpar carrinho?')) {
            cart = [];
            updateCartDisplay();
            updateTotals();
        }
    }
    
    // Finalizar pedido
    function finalizeOrder() {
        if (cart.length === 0) {
            alert('Adicione pelo menos um item ao carrinho');
            return;
        }
        
        const customerId = document.getElementById('customer-select').value;
        const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        
        // Simular finalização
        if (confirm(`Finalizar pedido de R$ ${total.toFixed(2).replace('.', ',')}?`)) {
            alert('Pedido finalizado com sucesso!');
            clearCart();
        }
    }
    
    // Atalhos de teclado
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            clearCart();
        } else if (e.key === 'F2') {
            e.preventDefault();
            finalizeOrder();
        }
    });
    
    // Buscar cliente
    document.getElementById('customer-search').addEventListener('input', function() {
        const query = this.value.toLowerCase();
        const select = document.getElementById('customer-select');
        const options = select.querySelectorAll('option');
        
        options.forEach(option => {
            if (option.value === '') return;
            const text = option.textContent.toLowerCase();
            option.style.display = text.includes(query) ? '' : 'none';
        });
    });
    
    // Selecionar cliente
    document.getElementById('customer-select').addEventListener('change', function() {
        const customerId = this.value;
        const customerInfo = document.getElementById('customer-info');
        
        if (customerId) {
            // Simular dados do cliente
            document.getElementById('customer-name').textContent = this.options[this.selectedIndex].textContent;
            document.getElementById('customer-details').textContent = 'Cliente cadastrado';
            customerInfo.classList.remove('hidden');
        } else {
            customerInfo.classList.add('hidden');
        }
    });
</script>
@endpush
@endsection