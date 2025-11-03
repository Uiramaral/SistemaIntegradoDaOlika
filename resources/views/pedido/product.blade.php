@extends('pedido.layout')

@section('title', $product->name . ' - Olika')

@section('content')
<div class="max-w-4xl mx-auto overflow-x-hidden">
    <!-- Breadcrumb -->
    <nav class="mb-6 text-sm text-gray-600">
        <a href="{{ route('pedido.index') }}" class="hover:text-primary">Cardápio</a>
        @if($product->category)
        <span class="mx-2">/</span>
        <a href="{{ route('pedido.menu.category', $product->category->id) }}" class="hover:text-primary">{{ $product->category->name }}</a>
        @endif
        <span class="mx-2">/</span>
        <span class="text-gray-900 break-words">{{ $product->name }}</span>
    </nav>

    <!-- Product Details -->
    <div class="grid md:grid-cols-2 gap-6 md:gap-8 mb-12">
        <!-- Image -->
        <div class="aspect-square rounded-lg overflow-hidden bg-gray-100">
            @php
                $img = $product->image_url;
                if (!$img && $product->cover_image) { $img = asset('storage/'.$product->cover_image); }
                elseif(!$img && $product->images && $product->images->count()>0){ $img = asset('storage/'.$product->images->first()->path); }
            @endphp
            <img src="{{ $img ?? asset('images/produto-placeholder.jpg') }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
        </div>

        <!-- Info -->
        <div class="min-w-0">
            @if($product->category)
            <div class="inline-flex items-center rounded-full border px-3 py-1 font-semibold bg-primary text-primary-foreground text-sm mb-4">
                {{ $product->category->name }}
            </div>
            @endif
            
            <h1 class="text-2xl md:text-3xl font-bold mb-4 break-words">{{ $product->name }}</h1>
            
            @if($product->description)
            <p class="text-gray-600 mb-6 break-words">{{ $product->description }}</p>
            @endif

            <div class="mb-6">
                <div class="flex items-baseline gap-3 mb-4">
                    @php
                        $variantsActive = $product->variants()->where('is_active', true)->orderBy('sort_order')->get();
                        $initialPrice = ($variantsActive->count() > 0) ? (float)optional($variantsActive->first())->price : (float)$product->price;
                    @endphp
                    <span id="priceDisplay" class="text-3xl md:text-4xl font-bold text-primary">R$ {{ number_format($initialPrice, 2, ',', '.') }}</span>
                </div>

                @if($variantsActive->count() > 0)
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Escolha uma opção</label>
                    <select id="variantSelect" class="w-full border rounded px-3 py-2">
                        @foreach($variantsActive as $v)
                        <option value="{{ $v->id }}" data-price="{{ (float)$v->price }}">{{ $v->name }} — R$ {{ number_format((float)$v->price,2,',','.') }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                <!-- Quantity Selector -->
                <div class="flex items-center gap-4 mb-6">
                    <label class="text-sm font-medium text-gray-700">Quantidade:</label>
                    <div class="flex items-center border rounded-lg">
                        <button onclick="changeQuantity(-1)" class="px-3 py-2 hover:bg-gray-100" id="decreaseBtn">-</button>
                        <input type="number" value="1" min="1" class="w-16 text-center border-x py-2" id="quantityInput">
                        <button onclick="changeQuantity(1)" class="px-3 py-2 hover:bg-gray-100" id="increaseBtn">+</button>
                    </div>
                </div>

                <!-- Observação do Item -->
                <div class="mb-6">
                    <label for="itemObservation" class="block text-sm font-medium text-gray-700 mb-2">Observação para este item (opcional)</label>
                    <textarea id="itemObservation" rows="3" maxlength="500" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary resize-none" placeholder="Ex: Sem cebola, bem passado, etc."></textarea>
                    <p class="text-xs text-gray-500 mt-1">Máximo 500 caracteres</p>
                </div>

                <button onclick="addToCart({{ $product->id }}, '{{ addslashes($product->name) }}', {{ (float)$initialPrice }})" class="w-full bg-primary text-primary-foreground py-3 rounded-lg font-semibold hover:bg-primary/90 transition-colors mb-4">
                    Adicionar ao Carrinho
                </button>
            </div>

            @if($product->allergens)
            <div class="border-t pt-6">
                <h3 class="font-semibold mb-2">Alérgenos</h3>
                <p class="text-sm text-gray-600 break-words">{{ $product->allergens }}</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Related Products -->
    @if(isset($relatedProducts) && $relatedProducts->count() > 0)
    <div class="border-t pt-12">
        <h2 class="text-2xl font-bold mb-6">Produtos Relacionados</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach($relatedProducts as $related)
            @php
                // Mesma lógica de imagem usada em outros lugares
                $img = $related->image_url;
                if (!$img && $related->cover_image) { $img = asset('storage/'.$related->cover_image); }
                elseif(!$img && $related->images && $related->images->count()>0){ $img = asset('storage/'.$related->images->first()->path); }
                $img = $img ?? asset('images/produto-placeholder.jpg');
                
                // Preço do produto ou da primeira variante ativa
                $minVariantPrice = $related->variants()->where('is_active', true)->min('price');
                $displayPrice = ($related->price > 0) ? (float)$related->price : ((float)$minVariantPrice ?: 0);
            @endphp
            <a href="{{ route('pedido.menu.product', $related->id) }}" class="rounded-lg border bg-white shadow-sm overflow-hidden group hover:shadow-md transition-all">
                <div class="aspect-square">
                    <img src="{{ $img }}" alt="{{ $related->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform">
                </div>
                <div class="p-3">
                    <h3 class="font-semibold text-sm mb-1 break-words">{{ $related->name }}</h3>
                    <p class="text-primary font-bold text-sm">
                        @if($minVariantPrice !== null && $related->price <= 0)
                            a partir de R$ {{ number_format($displayPrice, 2, ',', '.') }}
                        @else
                            R$ {{ number_format($displayPrice, 2, ',', '.') }}
                        @endif
                    </p>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
let quantity = 1;

function changeQuantity(delta) {
    quantity = Math.max(1, quantity + delta);
    document.getElementById('quantityInput').value = quantity;
}

document.getElementById('quantityInput').addEventListener('change', function() {
    quantity = Math.max(1, parseInt(this.value) || 1);
    this.value = quantity;
});

async function addToCart(productId, productName, price) {
    const qty = parseInt(document.getElementById('quantityInput').value) || 1;
    const variantSelect = document.getElementById('variantSelect');
    const itemObservation = document.getElementById('itemObservation')?.value.trim() || '';
    let variantId = null; 
    let variantPrice = null;
    
    // Se existe select de variantes, usar o preço da variante selecionada
    if (variantSelect) {
        const selectedOption = variantSelect.options[variantSelect.selectedIndex];
        if (selectedOption) {
            variantId = parseInt(selectedOption.value) || null;
            const p = parseFloat(selectedOption.getAttribute('data-price') || '0');
            if (!isNaN(p) && p > 0) {
                variantPrice = p;
            }
        }
    }
    
    // Usar preço da variante se disponível, senão usar preço do produto
    const finalPrice = variantPrice || price;
    
    if (!finalPrice || finalPrice <= 0) { 
        showNotification('Selecione uma opção com preço para adicionar.','error'); 
        return; 
    }
    
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
                variant_id: variantId,
                qty: qty,
                price: finalPrice,
                special_instructions: itemObservation
            })
        });

        const data = await response.json();
        
        if (data.ok || data.success) {
            const badge = document.querySelector('a[href*="cart"] .absolute');
            if (badge) {
                badge.textContent = data.cart_count || parseInt(badge.textContent) + qty;
            } else {
                const cartLink = document.querySelector('a[href*="cart"]');
                if (cartLink) {
                    const newBadge = document.createElement('div');
                    newBadge.className = 'absolute -right-2 -top-2 h-5 w-5 rounded-full bg-primary text-primary-foreground text-xs flex items-center justify-center font-semibold';
                    newBadge.textContent = data.cart_count || qty;
                    cartLink.appendChild(newBadge);
                }
            }
            
            // Limpar campo de observação após adicionar
            if (document.getElementById('itemObservation')) {
                document.getElementById('itemObservation').value = '';
            }
            
            showNotification(`${qty}x ${productName} adicionado${qty > 1 ? 's' : ''} ao carrinho!`);
        } else {
            showNotification(data.message || 'Não foi possível adicionar este produto agora.', 'error');
        }
    } catch (error) {
        console.error('Erro:', error);
        showNotification('Erro ao adicionar produto', 'error');
    }
}

// Atualiza preço exibido conforme variante
document.addEventListener('DOMContentLoaded', function(){
    const variantSelect = document.getElementById('variantSelect');
    if (variantSelect) {
        // Event listener para mudança de variante
        variantSelect.addEventListener('change', function(){
            const selectedIndex = this.selectedIndex;
            const selectedOption = this.options[selectedIndex];
            if (selectedOption) {
                const price = parseFloat(selectedOption.getAttribute('data-price') || '0');
                if (!isNaN(price) && price > 0) {
                    const priceDisplay = document.getElementById('priceDisplay');
                    if (priceDisplay) {
                        priceDisplay.textContent = 'R$ ' + price.toFixed(2).replace('.', ',');
                    }
                }
            }
        });
        
        // Trigger inicial para garantir que o preço está atualizado no carregamento
        if (variantSelect.options.length > 0) {
            // Pequeno delay para garantir que o DOM está pronto
            setTimeout(function() {
                variantSelect.dispatchEvent(new Event('change', { bubbles: true }));
            }, 100);
        }
    }
});

function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `fixed bottom-4 right-4 px-4 py-3 rounded-lg shadow-lg z-50 ${type === 'success' ? 'bg-green-500' : 'bg-red-500'} text-white`;
    notification.textContent = message;
    document.body.appendChild(notification);
    setTimeout(() => notification.remove(), 3000);
}
</script>
@endpush
