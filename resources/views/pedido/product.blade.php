@extends('pedido.layout')

@php
    $img = $product->image_url;
    if (!$img && $product->cover_image) { $img = asset('storage/'.$product->cover_image); }
    elseif(!$img && $product->images && $product->images->count()>0){ $img = asset('storage/'.$product->images->first()->path); }
    $img = $img ?? asset('images/produto-placeholder.jpg');
@endphp

@section('title', ($product->seo_title ?? $product->name) . ($product->seo_title ? '' : ' - Olika'))
@section('description', $product->seo_description ?? ($product->description ? mb_substr(strip_tags($product->description), 0, 160) : 'Pães artesanais com fermentação natural. Peça online 24h por dia. Tradição e qualidade em cada fornada.'))
@section('og_type', 'product')
@section('og_image', $img)

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Botão Voltar -->
    <button onclick="window.history.back()" class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 hover:bg-accent hover:text-accent-foreground mb-6">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="[&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0">
            <path d="m12 19-7-7 7-7"></path>
            <path d="M19 12H5"></path>
        </svg>
        Voltar
    </button>

    <!-- Product Details -->
    <div class="grid md:grid-cols-2 gap-8 mb-12">
        <!-- Image -->
        <div class="rounded-lg overflow-hidden bg-muted relative">
            @php
                $img = $product->image_url;
                if (!$img && $product->cover_image) { $img = asset('storage/'.$product->cover_image); }
                elseif(!$img && $product->images && $product->images->count()>0){ $img = asset('storage/'.$product->images->first()->path); }
                $img = $img ?? asset('images/produto-placeholder.jpg');
            @endphp
            <link rel="preload" as="image" href="{{ $img }}" fetchpriority="high">
            <div class="absolute inset-0 bg-gradient-to-br from-gray-200 via-gray-100 to-gray-200 animate-pulse" id="product-image-placeholder"></div>
            <img 
                src="{{ $img }}" 
                alt="{{ $product->name }}" 
                class="w-full h-full object-cover relative z-10"
                style="opacity: 0; transition: opacity 0.4s;"
                onload="this.style.opacity='1'; document.getElementById('product-image-placeholder').style.display='none';"
                onerror="this.onerror=null; this.src='{{ asset('images/produto-placeholder.jpg') }}'; this.style.opacity='1'; document.getElementById('product-image-placeholder').style.display='none';"
                width="600"
                height="600"
                fetchpriority="high"
            >
        </div>

        <!-- Info -->
        <div class="flex flex-col gap-6">
            <div>
                @if($product->category)
                <div class="text-sm text-muted-foreground mb-2">{{ $product->category->name }}</div>
                @endif
                <h1 class="text-3xl font-serif font-bold text-foreground mb-2">{{ $product->name }}</h1>
                @php
                    $variantsActive = $product->variants()->where('is_active', true)->orderBy('sort_order')->get();
                    $initialPrice = ($variantsActive->count() > 0) ? (float)optional($variantsActive->first())->price : (float)$product->price;
                @endphp
                <p class="text-2xl font-bold text-primary">R$ {{ number_format($initialPrice, 2, ',', '.') }}</p>
            </div>
            
            @if($product->description || ($product->ingredients ?? null))
            <div class="space-y-6">
                @if($product->description)
                <div>
                    <h3 class="text-lg font-semibold text-foreground mb-2">Descrição</h3>
                    <p class="text-muted-foreground">{{ $product->description }}</p>
                </div>
                @endif
                
                @if($product->ingredients ?? null)
                <div>
                    <h3 class="text-lg font-semibold text-foreground mb-2">Ingredientes</h3>
                    <p class="text-muted-foreground">{{ $product->ingredients }}</p>
                </div>
                @endif
            </div>
            @endif

            <div class="space-y-6">
                @if($variantsActive->count() > 0)
                <div class="mb-6">
                    <div class="flex items-center gap-2 mb-3">
                        <label class="block text-sm font-medium text-gray-700">Escolha uma das opções</label>
                        <span class="inline-flex items-center rounded-full bg-red-100 text-red-700 text-xs font-medium px-2 py-0.5">Obrigatório</span>
                    </div>
                    <div class="space-y-2">
                        @foreach($variantsActive as $index => $v)
                        <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer hover:bg-gray-50 transition-colors {{ $index === 0 ? 'border-primary bg-primary/5' : 'border-gray-300' }}">
                            <input 
                                type="radio" 
                                name="variantSelect" 
                                value="{{ $v->id }}" 
                                data-price="{{ (float)$v->price }}"
                                class="w-4 h-4 text-primary border-gray-300 focus:ring-primary focus:ring-2"
                                {{ $index === 0 ? 'checked' : '' }}
                                required
                            >
                            <span class="flex-1 text-sm font-medium text-gray-900">{{ $v->name }}</span>
                            <span class="text-sm font-semibold text-primary">R$ {{ number_format((float)$v->price, 2, ',', '.') }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Quantity Selector -->
                <div>
                    <label class="block text-sm font-medium text-foreground mb-2">Quantidade:</label>
                    <div class="flex items-center gap-2">
                        <button onclick="changeQuantity(-1)" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 w-10" id="decreaseBtn">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="[&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0">
                                <path d="M5 12h14"></path>
                            </svg>
                        </button>
                        <span class="px-4 text-lg font-semibold" id="quantityDisplay">1</span>
                        <button onclick="changeQuantity(1)" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 w-10" id="increaseBtn">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="[&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0">
                                <path d="M5 12h14"></path>
                                <path d="M12 5v14"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <button onclick="addToCart({{ $product->id }}, '{{ addslashes($product->name) }}', {{ (float)$initialPrice }})" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-11 rounded-md px-8 w-full gap-2 shadow-warm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="[&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0">
                        <path d="M5 12h14"></path>
                        <path d="M12 5v14"></path>
                    </svg>
                    Adicionar ao carrinho - <span id="priceDisplay">R$ {{ number_format($initialPrice, 2, ',', '.') }}</span>
                </button>
                
                <button onclick="window.location.href='{{ route('pedido.cart.index') }}'" class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-11 rounded-md px-8 w-full">
                    Ver carrinho
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
    document.getElementById('quantityDisplay').textContent = quantity;
    updatePriceDisplay();
}

function updatePriceDisplay() {
    const variantRadio = document.querySelector('input[name="variantSelect"]:checked');
    let currentPrice = {{ (float)$initialPrice }};
    
    if (variantRadio) {
        const variantPrice = parseFloat(variantRadio.getAttribute('data-price') || '0');
        if (!isNaN(variantPrice) && variantPrice > 0) {
            currentPrice = variantPrice;
        }
    }
    
    const totalPrice = currentPrice * quantity;
    const priceDisplay = document.getElementById('priceDisplay');
    if (priceDisplay) {
        priceDisplay.textContent = 'R$ ' + totalPrice.toFixed(2).replace('.', ',');
    }
}

// Atualizar preço quando variante mudar
document.addEventListener('DOMContentLoaded', function() {
    const variantRadios = document.querySelectorAll('input[name="variantSelect"]');
    variantRadios.forEach(radio => {
        radio.addEventListener('change', updatePriceDisplay);
    });
    updatePriceDisplay();
});

async function addToCart(productId, productName, price) {
    const qty = quantity;
    const variantRadio = document.querySelector('input[name="variantSelect"]:checked');
    const itemObservation = document.getElementById('itemObservation')?.value.trim() || '';
    let variantId = null; 
    let variantPrice = null;
    
    // Se existe radio de variantes, usar o preço da variante selecionada
    if (variantRadio) {
        variantId = parseInt(variantRadio.value) || null;
        const p = parseFloat(variantRadio.getAttribute('data-price') || '0');
        if (!isNaN(p) && p > 0) {
            variantPrice = p;
        }
    }
    
    // Usar preço da variante se disponível, senão usar preço do produto
    const finalPrice = variantPrice || price;
    
    if (!finalPrice || finalPrice <= 0) { 
        showNotification('Selecione uma opção com preço para adicionar.','error'); 
        return; 
    }
    
    // Validar se variante foi selecionada quando há variantes
    const hasVariants = document.querySelectorAll('input[name="variantSelect"]').length > 0;
    if (hasVariants && !variantRadio) {
        showNotification('Por favor, selecione uma opção antes de adicionar ao carrinho.','error'); 
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
            
            // Redirecionar para o cardápio após adicionar
            setTimeout(() => {
                window.location.href = '{{ route("pedido.index") }}';
            }, 1000);
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
    const variantRadios = document.querySelectorAll('input[name="variantSelect"]');
    if (variantRadios.length > 0) {
        // Event listener para mudança de variante
        variantRadios.forEach(function(radio) {
            radio.addEventListener('change', function(){
                if (this.checked) {
                    const price = parseFloat(this.getAttribute('data-price') || '0');
                    if (!isNaN(price) && price > 0) {
                        const priceDisplay = document.getElementById('priceDisplay');
                        if (priceDisplay) {
                            priceDisplay.textContent = 'R$ ' + price.toFixed(2).replace('.', ',');
                        }
                        
                        // Atualizar visual do radio selecionado
                        variantRadios.forEach(function(r) {
                            const label = r.closest('label');
                            if (label) {
                                if (r === radio) {
                                    label.classList.add('border-primary', 'bg-primary/5');
                                    label.classList.remove('border-gray-300');
                                } else {
                                    label.classList.remove('border-primary', 'bg-primary/5');
                                    label.classList.add('border-gray-300');
                                }
                            }
                        });
                    }
                }
            });
        });
        
        // Trigger inicial para garantir que o preço está atualizado no carregamento
        const checkedRadio = document.querySelector('input[name="variantSelect"]:checked');
        if (checkedRadio) {
            setTimeout(function() {
                checkedRadio.dispatchEvent(new Event('change', { bubbles: true }));
            }, 100);
        }
    }
});


</script>
@endpush
