@extends('dashboard.layouts.app')

@section('page_title', 'PDV - Ponto de Venda')

@push('styles')
<style>
    /* PDV Container - sem espaço extra */
    .pdv-container {
        display: flex;
        flex-direction: column;
        height: calc(100vh - 56px); /* Reduced from 64px to give more vertical space */
        overflow: hidden;
    }
    
    /* PDV Mobile Optimizations */
    @media (max-width: 1023px) {
        .pdv-cart-panel {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 40;
            max-height: 70vh;
            transition: transform 0.3s ease, max-height 0.3s ease;
            border-radius: 1rem 1rem 0 0;
            box-shadow: 0 -4px 20px rgba(0,0,0,0.15);
        }
        .pdv-cart-panel.collapsed {
            max-height: 60px;
        }
        .pdv-cart-panel.collapsed .cart-body {
            display: none;
        }
        .pdv-products-area {
            padding-bottom: 80px;
        }
    }
    
    /* Product table styles */
    .pdv-product-row {
        display: grid;
        grid-template-columns: 1fr auto auto;
        gap: 0.5rem;
        align-items: center;
        padding: 0.75rem 1rem;
        border-bottom: 1px solid hsl(var(--border));
        transition: background-color 0.15s;
    }
    .pdv-product-row:hover {
        background-color: #fff7ed;
    }
    @media (min-width: 640px) {
        .pdv-product-row {
            grid-template-columns: 1fr 140px 120px 60px;
        }
    }
    
    /* Products scroll container */
    .products-scroll-container {
        flex: 1;
        overflow-y: auto;
        max-height: calc(100vh - 320px);
    }
    
    /* Order items scroll container - will use flex instead of max-height on desktop */
    @media (max-width: 1023px) {
        #pdv-items-list {
            max-height: 300px !important;
            min-height: 120px !important;
        }
    }
    
    /* Desktop cart panel - improved scrolling */
    #desktop-cart-panel .cart-body {
        display: flex;
        flex-direction: column;
    }
    
    /* Ensure the entire cart panel scrolls properly on desktop */
    @media (min-width: 1024px) {
        #desktop-cart-panel {
            height: calc(100vh - 80px);
            overflow-y: hidden;
            display: flex;
            flex-direction: column;
        }
        
        #desktop-cart-panel > .rounded-lg {
            display: flex;
            flex-direction: column;
            height: 100%;
            overflow: hidden;
        }
        
        /* Seção de cliente fixa no topo (fora do cart-body) */
        #desktop-cart-panel > .rounded-lg > div:first-child {
            flex-shrink: 0;
        }
        
        #desktop-cart-panel .cart-body {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            min-height: 0;
        }
        
        /* Ocultar cliente duplicado dentro do cart-body no desktop */
        #desktop-cart-panel .cart-body > div:first-child {
            display: none;
        }
        
        /* Cabeçalho do pedido - fixo */
        #desktop-cart-panel #pdv-items-header {
            flex-shrink: 0;
        }
        
        /* Lista de itens - scrollável */
        #desktop-cart-panel #pdv-items-list {
            flex: 1;
            overflow-y: auto;
            min-height: 0;
        }
        
        /* Seções fixas no final */
        #desktop-cart-panel .cart-body .border-t,
        #desktop-cart-panel .cart-body > div:last-child {
            flex-shrink: 0;
        }
    }
    
    /* Style the order items list scrollbar specifically */
    #pdv-items-list {
        scrollbar-width: thin;
        scrollbar-color: #f97316 #f8fafc;
    }
    
    #pdv-items-list::-webkit-scrollbar {
        width: 6px;
    }
    
    #pdv-items-list::-webkit-scrollbar-track {
        background: #f8fafc;
        border-radius: 3px;
    }
    
    #pdv-items-list::-webkit-scrollbar-thumb {
        background: #f97316;
        border-radius: 3px;
    }
    
    #pdv-items-list::-webkit-scrollbar-thumb:hover {
        background: #ea580c;
    }
    
    /* Improve visual separation between sections */
    .cart-section-divider {
        border-top: 1px solid #e2e8f0;
        margin: 0.5rem 0;
    }
    
    /* Sticky header for order items when scrolling */
    #pdv-items-header {
        position: sticky;
        top: 0;
        background: white;
        z-index: 10;
        padding: 0.5rem 0.5rem 0.25rem 0.5rem;
        border-bottom: 1px solid #e2e8f0;
    }
    
    /* Cart toggle button */
    .cart-toggle-btn {
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
        padding: 0.75rem 1rem;
        background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
        color: white;
        font-weight: 600;
        cursor: pointer;
        border: none;
    }
    .cart-toggle-btn .cart-summary {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    /* Category filter active state */
    .category-filter.active {
        background-color: #f97316 !important;
        color: white !important;
    }
    
    /* Variant option selected */
    .variant-option.selected {
        background-color: #f97316;
        color: white;
        border-color: #f97316;
    }
</style>
@endpush

@section('content')
<div class="pdv-container" id="pdv-main-container">

    @if(session('success'))
    <div class="rounded-lg border bg-green-50 border-green-200 p-4 text-green-700 mb-4">
        {{ session('success') }}
    </div>
    @endif
    
    <!-- Error Display Area -->
    <div id="pdv-errors" class="hidden rounded-lg border border-red-200 bg-red-50 p-4 text-red-700 mb-4">
        <div class="font-medium">Erros encontrados:</div>
        <ul id="pdv-error-list" class="list-disc ml-4 mt-2"></ul>
    </div>

    @if(session('error'))
    <div class="rounded-lg border bg-red-50 border-red-200 p-4 text-red-700 mb-4">
        {{ session('error') }}
    </div>
    @endif

    <div class="flex flex-col lg:flex-row gap-4 lg:items-start">
        <!-- Coluna principal - Produtos (sempre visível, acima no mobile) -->
        <div class="flex-1 flex flex-col space-y-4 pdv-products-area">
            <div id="products-section" class="rounded-lg border bg-white text-card-foreground shadow-sm">
                <!-- Header com busca e filtros -->
                <div class="p-4 border-b">
                    <!-- Busca de produtos -->
                    <div class="relative mb-3">
                        <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400 z-10"></i>
                        <input
                            type="text"
                            id="product-search"
                            class="w-full pl-10 pr-4 rounded-lg border border-gray-200 bg-white text-sm py-2.5 focus:border-orange-500 focus:ring-1 focus:ring-orange-500 relative z-10"
                            placeholder="Buscar por nome ou código..."
                            autocomplete="off"
                        >
                        <div
                            id="product-results"
                            class="hidden absolute left-0 right-0 top-full mt-1 max-h-64 overflow-y-auto border rounded-lg bg-white shadow-lg z-50"
                        ></div>
                    </div>

                    <!-- Categorias -->
                    <div class="flex flex-wrap gap-2">
                        <button type="button" class="category-filter px-3 py-1.5 rounded-full text-xs sm:text-sm font-medium bg-orange-500 text-white shadow-sm active" data-category="all">
                            Todos
                        </button>
                        <button type="button" class="category-filter px-3 py-1.5 rounded-full text-xs sm:text-sm font-medium bg-gray-100 text-gray-600 hover:bg-gray-200" data-category="bolos">
                            Bolos
                        </button>
                        <button type="button" class="category-filter px-3 py-1.5 rounded-full text-xs sm:text-sm font-medium bg-gray-100 text-gray-600 hover:bg-gray-200" data-category="paes">
                            Pães
                        </button>
                        <button type="button" class="category-filter px-3 py-1.5 rounded-full text-xs sm:text-sm font-medium bg-gray-100 text-gray-600 hover:bg-gray-200" data-category="doces">
                            Doces
                        </button>
                        <button type="button" class="category-filter px-3 py-1.5 rounded-full text-xs sm:text-sm font-medium bg-gray-100 text-gray-600 hover:bg-gray-200" data-category="salgados">
                            Salgados
                        </button>
                        <button type="button" class="category-filter px-3 py-1.5 rounded-full text-xs sm:text-sm font-medium bg-gray-100 text-gray-600 hover:bg-gray-200" data-category="bebidas">
                            Bebidas
                        </button>
                    </div>
                </div>

                <!-- Tabela de produtos -->
                <div class="products-scroll-container">
                    <!-- Header da tabela (desktop) -->
                    <div class="hidden sm:grid grid-cols-[1fr_140px_120px_60px] gap-2 px-4 py-2 bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wider border-b sticky top-0">
                        <span>Produto</span>
                        <span>Categoria</span>
                        <span class="text-right">Preço</span>
                        <span class="text-center">Ação</span>
                    </div>
                    
                    <!-- Lista de produtos -->
                    <div id="products-list" class="divide-y divide-gray-100">
                        @foreach($products as $product)
                            @php
                                $variantsActive = $product->variants()->where('is_active', true)->orderBy('sort_order')->get();
                                $hasVariants = $variantsActive->count() > 0;
                                $displayPrice = $hasVariants ? $variantsActive->first()->price : $product->price;
                                $variantsData = $variantsActive->map(function($v) {
                                    return [
                                        'id' => $v->id,
                                        'name' => $v->name,
                                        'price' => (float)$v->price,
                                    ];
                                })->toArray();
                                $categoryName = $product->category?->name ?? 'Outros';
                            @endphp

                            <button
                                type="button"
                                class="product-quick-add pdv-product-row w-full text-left hover:bg-orange-50 transition-colors"
                                data-product-id="{{ $product->id }}"
                                data-product-name="{{ $product->name }}"
                                data-product-price="{{ $displayPrice }}"
                                data-has-variants="{{ $hasVariants ? 'true' : 'false' }}"
                                data-variants="{{ json_encode($variantsData) }}"
                                data-category="{{ $product->category?->slug ?? 'outros' }}"
                            >
                                <div class="min-w-0">
                                    <p class="font-medium text-sm text-gray-800 truncate">{{ $product->name }}</p>
                                    <p class="text-xs text-gray-400 sm:hidden">{{ $categoryName }}</p>
                                </div>
                                <span class="hidden sm:block text-xs text-gray-500">{{ $categoryName }}</span>
                                <span class="text-sm font-bold text-orange-600 text-right whitespace-nowrap">
                                    @if($hasVariants)
                                        a partir de R$ {{ number_format($displayPrice, 2, ',', '.') }}
                                    @else
                                        R$ {{ number_format($displayPrice, 2, ',', '.') }}
                                    @endif
                                </span>
                                <span class="hidden sm:flex justify-center">
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-orange-100 text-orange-600 hover:bg-orange-500 hover:text-white transition-colors">
                                        <i data-lucide="plus" class="h-4 w-4"></i>
                                    </span>
                                </span>
                            </button>
                        @endforeach
                    </div>
                    
                    <!-- Paginação -->
                    <div id="pagination" class="p-4 flex justify-center gap-2 border-t"></div>
                </div>
            </div>
        </div>

        <!-- Coluna Pedido (DIREITA em desktop, oculto em mobile) -->
        <div class="hidden lg:block w-full lg:w-[380px] flex-shrink-0 lg:sticky lg:top-20" id="desktop-cart-panel">
            <div class="rounded-lg border bg-white shadow-sm flex flex-col">
                
                <!-- Buscar Cliente -->
                <div class="p-4 border-b bg-white">
                    <div class="flex items-center gap-2 mb-3">
                        <i data-lucide="user" class="h-5 w-5 text-gray-500"></i>
                        <h3 class="font-semibold text-gray-800">Cliente</h3>
                    </div>
                    <div class="flex gap-2">
                        <div class="relative flex-1">
                            <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400"></i>
                            <input type="text" id="customer-search-desktop" class="w-full pl-10 pr-4 rounded-lg border border-gray-300 bg-white text-sm py-2.5 focus:border-orange-500 focus:ring-1 focus:ring-orange-500" placeholder="Buscar cliente...">
                        </div>
                        <button type="button" id="btn-new-customer-desktop" class="px-3 py-2 rounded-lg bg-orange-500 text-white hover:bg-orange-600">
                            <i data-lucide="user-plus" class="h-5 w-5"></i>
                        </button>
                    </div>
                    <div id="customer-results-desktop" class="mt-2 hidden max-h-40 overflow-y-auto border rounded-lg bg-white shadow-lg"></div>
                    <input type="hidden" id="customer-id" name="customer_id" required>
                    <div id="desktop-selected-customer" class="mt-3 hidden p-3 rounded-lg bg-orange-50 border border-orange-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-semibold text-gray-800" id="desktop-customer-name"></p>
                                <p class="text-sm text-gray-600" id="desktop-customer-info"></p>
                            </div>
                            <button type="button" id="btn-clear-customer-desktop" class="text-gray-400 hover:text-red-500">
                                <i data-lucide="x" class="h-5 w-5"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Conteúdo do carrinho (colapsável no mobile) -->
                <div class="cart-body">
                    <!-- Buscar Cliente -->
                    <div class="p-3 border-b bg-white">
                        <div class="flex items-center gap-1 mb-2">
                            <i data-lucide="user" class="h-4 w-4 text-gray-500"></i>
                            <span class="text-sm font-medium text-gray-700">Cliente</span>
                        </div>
                        <div class="flex gap-2">
                            <div class="relative flex-1">
                                <i data-lucide="search" class="absolute left-2 top-1/2 transform -translate-y-1/2 h-3 w-3 text-gray-400"></i>
                                <input type="text" id="customer-search" class="w-full pl-7 rounded-lg border border-gray-300 bg-white text-sm px-3 py-2" placeholder="Buscar cliente..." autocomplete="off">
                            </div>
                            <button type="button" id="btn-new-customer" class="px-3 py-2 rounded-lg text-sm font-medium bg-orange-500 text-white hover:bg-orange-600 flex items-center gap-1">
                                <i data-lucide="user-plus" class="h-4 w-4"></i>
                            </button>
                        </div>
                        <div id="customer-results" class="mt-2 hidden max-h-40 overflow-y-auto border rounded-lg bg-white shadow-lg z-50 relative"></div>
                        <input type="hidden" id="customer-id" name="customer_id" required>
                        <div id="selected-customer" class="mt-2 hidden p-2 rounded-lg bg-orange-50 border border-orange-200">
                            <div class="flex items-center justify-between gap-2">
                                <div class="min-w-0 flex-1">
                                    <p class="font-semibold text-sm truncate text-gray-800" id="selected-customer-name"></p>
                                    <p class="text-xs truncate text-gray-500" id="selected-customer-info"></p>
                                </div>
                                <button type="button" id="btn-clear-customer" class="text-gray-400 hover:text-gray-600 flex-shrink-0">
                                    <i data-lucide="x" class="h-4 w-4"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Cabeçalho do pedido -->
                    <div id="pdv-items-header" class="flex items-center justify-between bg-gray-50">
                        <div class="flex items-center gap-2">
                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-orange-100">
                                <i data-lucide="shopping-cart" class="h-3.5 w-3.5 text-orange-600"></i>
                            </span>
                            <span class="font-semibold text-sm text-gray-800">Pedido</span>
                            <span id="order-items-badge" class="hidden bg-orange-500 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center">0</span>
                        </div>
                        <button type="button" id="btn-clear-order" class="text-xs font-medium text-gray-500 hover:text-red-500">
                            Limpar
                        </button>
                    </div>

                    <!-- Itens do pedido (com scroll) -->
                    <div id="pdv-items-list" class="flex-1 overflow-y-auto overflow-x-hidden p-2 space-y-1">
                        <div class="flex flex-col items-center justify-center py-6 text-center text-gray-400">
                            <i data-lucide="shopping-cart" class="h-6 w-6 mb-1 opacity-50"></i>
                            <p class="text-xs">Carrinho vazio</p>
                        </div>
                    </div>

                    <!-- Seção de Entrega -->
                    <div class="border-t p-2 space-y-2 bg-gray-50 overflow-x-hidden">
                        <!-- Toggle Retirada/Entrega -->
                        <div class="flex rounded-lg border border-gray-300 overflow-hidden">
                            <button type="button" id="btn-pickup" class="flex-1 py-1.5 text-xs font-medium text-gray-600 bg-white hover:bg-gray-100 transition-colors delivery-toggle">
                                Retirada
                            </button>
                            <button type="button" id="btn-delivery" class="flex-1 py-1.5 text-xs font-medium text-white bg-orange-500 hover:bg-orange-600 transition-colors delivery-toggle active">
                                Entrega
                            </button>
                        </div>

                        <!-- Endereço (CEP + Número) -->
                        <div id="delivery-address-section" class="flex gap-1">
                            <input type="text" id="destination-cep" maxlength="9" class="flex-1 min-w-0 rounded-lg border border-gray-300 bg-white text-xs px-2 py-1.5" placeholder="CEP">
                            <input type="text" id="destination-number" maxlength="10" class="w-12 rounded-lg border border-gray-300 bg-white text-xs px-2 py-1.5" placeholder="Nº">
                            <button type="button" id="btn-calculate-fee" class="px-2 py-1 rounded-lg text-xs font-medium border border-gray-300 bg-white hover:bg-gray-100">
                                Calc
                            </button>
                        </div>

                        <!-- Taxa Manual -->
                        <div class="flex items-center gap-1">
                            <span class="text-xs text-gray-600">Taxa:</span>
                            <input type="number" id="delivery-fee-input" step="0.01" min="0" value="0" class="w-14 rounded-lg border border-gray-300 bg-white text-xs px-1.5 py-1 text-right">
                            <button type="button" id="btn-set-fee" class="px-2 py-1 rounded-lg text-xs font-medium border border-gray-300 bg-white hover:bg-gray-100">
                                OK
                            </button>
                            <span id="delivery-fee-display" class="text-sm font-bold text-orange-600 ml-auto">R$ 0,00</span>
                        </div>
                        <div id="delivery-fee-info" class="hidden text-xs text-gray-500"></div>
                    </div>

                    <!-- Cupom -->
                    <div class="border-t p-2 bg-white">
                        <div class="flex gap-1">
                            <input type="text" id="coupon-code" class="flex-1 rounded-lg border border-gray-300 bg-white text-xs px-2 py-1.5" placeholder="Cupom de desconto">
                            <button type="button" id="btn-apply-coupon" class="px-3 py-1.5 rounded-lg text-xs font-medium border border-gray-300 bg-white hover:bg-gray-100">
                                Aplicar
                            </button>
                        </div>
                        <div id="coupon-info" class="mt-1 hidden p-1.5 bg-green-50 border border-green-200 rounded text-xs text-green-700"></div>
                    </div>

                    <!-- Resumo de Totais -->
                    <div class="border-t p-3 bg-white space-y-1">
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-gray-600">Subtotal (<span id="order-items-count">0</span> itens)</span>
                            <span id="summary-subtotal" class="font-medium text-gray-800">R$ 0,00</span>
                        </div>
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-gray-600">Entrega</span>
                            <span id="summary-delivery-fee" class="font-medium text-gray-800">R$ 0,00</span>
                        </div>
                        <div class="flex justify-between items-center text-xs text-green-600 hidden" id="discount-row">
                            <span>Desconto</span>
                            <span id="summary-discount" class="font-medium">- R$ 0,00</span>
                        </div>
                        <div class="border-t pt-2 flex justify-between items-center">
                            <span class="text-sm font-bold text-gray-800">Total</span>
                            <span id="summary-total" class="text-lg font-bold text-orange-600">R$ 0,00</span>
                        </div>
                    </div>

                    <!-- Botão Finalizar -->
                    <div class="p-3 pt-0">
                        <button type="button" id="btn-finalize-order" 
                                class="w-full py-2.5 rounded-lg text-sm font-bold bg-orange-500 text-white hover:bg-orange-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors shadow-lg flex items-center justify-center gap-2"
                                disabled>
                            <i data-lucide="check-circle" class="h-4 w-4"></i>
                            Finalizar Pedido
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Novo Cliente -->
<div id="new-customer-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/75">
    <div class="bg-white rounded-lg shadow-2xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto relative">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-semibold">Novo Cliente</h3>
                <button type="button" id="btn-close-new-customer-modal" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x">
                        <path d="M18 6 6 18"></path>
                        <path d="M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <form id="new-customer-form">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-2">Nome *</label>
                        <input type="text" id="new-customer-name" required class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">Telefone</label>
                        <input type="text" id="new-customer-phone" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">Email</label>
                        <input type="email" id="new-customer-email" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input 
                                type="checkbox" 
                                id="new-customer-is-wholesale" 
                                class="rounded border-gray-300 text-primary focus:ring-primary"
                            >
                            <span class="text-sm font-medium">Cliente de Revenda/Restaurante</span>
                        </label>
                        <p class="text-xs text-muted-foreground mt-1 ml-6">Marque esta opção se o cliente é revenda, restaurante ou similar. Eles terão acesso a preços diferenciados.</p>
                    </div>
                    
                    <!-- Endereço de Entrega -->
                    <div class="pt-4 border-t">
                        <h4 class="text-sm font-semibold mb-3">Endereço de Entrega (Opcional)</h4>
                        <div class="space-y-3">
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-sm font-medium mb-2">CEP</label>
                                    <input type="text" id="new-customer-zip-code" maxlength="9" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm" placeholder="00000-000">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2">Estado</label>
                                    <input type="text" id="new-customer-state" maxlength="2" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm" placeholder="BA">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2">Rua</label>
                                <input type="text" id="new-customer-street" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm" placeholder="Nome da rua">
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-sm font-medium mb-2">Número</label>
                                    <input type="text" id="new-customer-number" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm" placeholder="123">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2">Complemento</label>
                                    <input type="text" id="new-customer-complement" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm" placeholder="Apto, Bloco, etc">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2">Bairro</label>
                                <input type="text" id="new-customer-neighborhood" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm" placeholder="Nome do bairro">
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2">Cidade</label>
                                <input type="text" id="new-customer-city" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm" placeholder="Nome da cidade">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex gap-3 mt-6">
                    <button type="button" id="btn-cancel-new-customer" class="flex-1 inline-flex items-center justify-center rounded-md text-sm font-medium border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4">
                        Cancelar
                    </button>
                    <button type="submit" class="flex-1 inline-flex items-center justify-center rounded-md text-sm font-medium bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4">
                        Criar Cliente
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Confirmação de Finalização -->
<div id="finalize-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/75">
    <div class="bg-white rounded-lg shadow-2xl w-full max-w-md mx-4 relative">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-semibold">Finalizar Pedido</h3>
                <button type="button" id="btn-close-finalize-modal" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x">
                        <path d="M18 6 6 18"></path>
                        <path d="M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <div class="space-y-4">
                <div>
                    <p class="text-sm text-muted-foreground mb-4">Como deseja processar este pedido?</p>
                    
                    <div class="space-y-3">
                        <label class="flex items-center gap-3 p-3 border rounded-md cursor-pointer hover:bg-accent">
                            <input type="radio" name="payment_option" value="send_link" class="h-4 w-4 text-primary" checked>
                            <div class="flex-1">
                                <p class="font-medium">Enviar link de pagamento ao cliente</p>
                                <p class="text-xs text-muted-foreground">O cliente receberá os dados do pedido e link para finalizar, agendar entrega e pagar</p>
                            </div>
                        </label>
                        
                        <label class="flex items-center gap-3 p-3 border rounded-md cursor-pointer hover:bg-accent">
                            <input type="radio" name="payment_option" value="debt" class="h-4 w-4 text-primary">
                            <div class="flex-1">
                                <p class="font-medium">Criar como débito (fiado)</p>
                                <p class="text-xs text-muted-foreground">Pedido será registrado como débito no cadastro do cliente</p>
                            </div>
                        </label>
                    </div>

                    <div id="payment-method-section" class="mt-4 space-y-2">
                        <label class="block text-sm font-medium">Método de Pagamento</label>
                        <select id="payment-method-select" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
                            <option value="pix">PIX (código copia e cola)</option>
                            <option value="link">Link de Pagamento (cartão)</option>
                        </select>
                        <p class="text-xs text-muted-foreground">
                            <strong>PIX:</strong> Envia código copia e cola + link com QR code<br>
                            <strong>Link:</strong> Envia link para pagamento via cartão
                        </p>
                    </div>
                    
                    <!-- Agendamento de Entrega -->
                    <div class="mt-4 pt-4 border-t space-y-2">
                        <div class="flex items-center gap-2 mb-2">
                            <i data-lucide="calendar" class="h-4 w-4 text-gray-500"></i>
                            <label class="text-sm font-medium">Agendamento de Entrega</label>
                        </div>
                        <input type="datetime-local" 
                               id="scheduled-delivery-at" 
                               class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                               placeholder="Selecione data e hora">
                        <p class="text-xs text-muted-foreground">Deixe em branco para entrega sem agendamento</p>
                    </div>
                </div>
            </div>
            
            <div class="flex gap-3 mt-6">
                <button type="button" id="btn-cancel-finalize" class="flex-1 inline-flex items-center justify-center rounded-md text-sm font-medium border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4">
                    Cancelar
                </button>
                <button type="button" id="btn-confirm-finalize" class="flex-1 inline-flex items-center justify-center rounded-md text-sm font-medium bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4">
                    Confirmar
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// PDV Error Handling System
const PDVErrorHandler = {
    errors: [],
    
    addError: function(message, error = null) {
        const errorObj = {
            timestamp: new Date().toISOString(),
            message: message,
            error: error
        };
        this.errors.push(errorObj);
        this.displayErrors();
        console.error('PDV Error:', errorObj);
    },
    
    displayErrors: function() {
        const errorContainer = document.getElementById('pdv-errors');
        const errorList = document.getElementById('pdv-error-list');
        
        if (this.errors.length > 0 && errorContainer && errorList) {
            errorContainer.classList.remove('hidden');
            errorList.innerHTML = '';
            
            this.errors.slice(-3).forEach(error => {
                const li = document.createElement('li');
                li.textContent = `${error.message}`;
                errorList.appendChild(li);
            });
        }
    },
    
    clearErrors: function() {
        this.errors = [];
        const errorContainer = document.getElementById('pdv-errors');
        if (errorContainer) {
            errorContainer.classList.add('hidden');
        }
    }
};

// Enhanced fetch wrapper with error handling
function safeFetch(url, options = {}) {
    return fetch(url, options)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response;
        })
        .catch(error => {
            PDVErrorHandler.addError(`Falha na requisição: ${error.message}`, error);
            throw error;
        });
}

// Check if required elements exist
function checkRequiredElements() {
    const requiredElements = [
        'pdv-main-container',
        'product-search',
        'customer-search',
        'btn-finalize-order',
        'pdv-items-list',
        'summary-total'
    ];
    
    const missingElements = [];
    
    requiredElements.forEach(id => {
        if (!document.getElementById(id)) {
            missingElements.push(id);
        }
    });
    
    if (missingElements.length > 0) {
        PDVErrorHandler.addError(`Elementos HTML faltando: ${missingElements.join(', ')}`);
    }
}

// Validate CSRF token
function validateCSRFToken() {
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    if (!csrfMeta || !csrfMeta.content) {
        PDVErrorHandler.addError('Token CSRF não encontrado');
        return false;
    }
    return true;
}

// Initialize PDV with error checking
function initializePDV() {
    try {
        // Clear previous errors
        PDVErrorHandler.clearErrors();
        
        // Check required elements
        checkRequiredElements();
        
        // Validate CSRF token
        if (!validateCSRFToken()) {
            return false;
        }
        
        // Initialize Lucide icons if available
        if (window.lucide) {
            try {
                window.lucide.createIcons();
            } catch (iconError) {
                PDVErrorHandler.addError('Erro ao inicializar ícones', iconError);
            }
        }
        
        console.log('PDV inicializado com sucesso');
        return true;
    } catch (initError) {
        PDVErrorHandler.addError('Erro durante inicialização do PDV', initError);
        return false;
    }
}

// Estado do PDV
const pdvState = {
    customer: null,
    items: [],
    coupon: null,
    deliveryType: 'delivery',
    deliveryFee: 0,
    notes: '',
};

// === CART TOGGLE PARA MOBILE ===
const cartPanel = document.getElementById('cart-panel');
const cartToggle = document.getElementById('cart-toggle');
const cartToggleText = document.getElementById('cart-toggle-text');
const cartToggleTotal = document.getElementById('cart-toggle-total');

if (cartToggle) {
    cartToggle.addEventListener('click', function() {
        cartPanel.classList.toggle('collapsed');
        const chevron = cartToggle.querySelector('.cart-chevron');
        if (chevron) {
            chevron.style.transform = cartPanel.classList.contains('collapsed') ? 'rotate(0deg)' : 'rotate(180deg)';
        }
    });
}

// Função para atualizar o texto do toggle do carrinho
function updateCartToggle() {
    if (!cartToggleText || !cartToggleTotal) return;
    
    const totalItems = pdvState.items.reduce((sum, item) => sum + item.quantity, 0);
    const subtotal = pdvState.items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const deliveryFee = parseFloat(document.getElementById('delivery-fee-input')?.value || 0);
    const discount = pdvState.coupon?.discount || 0;
    const total = Math.max(0, subtotal + deliveryFee - discount);
    
    if (totalItems === 0) {
        cartToggleText.textContent = 'Carrinho vazio';
    } else {
        cartToggleText.textContent = `${totalItems} ${totalItems === 1 ? 'item' : 'itens'}`;
    }
    
    cartToggleTotal.textContent = `R$ ${total.toFixed(2).replace('.', ',')}`;
}

// Abrir carrinho automaticamente quando adicionar item (mobile)
function expandCartOnMobile() {
    if (window.innerWidth < 1024 && cartPanel && cartPanel.classList.contains('collapsed')) {
        // Abrir brevemente para mostrar item adicionado
        cartPanel.classList.remove('collapsed');
        const chevron = cartToggle?.querySelector('.cart-chevron');
        if (chevron) chevron.style.transform = 'rotate(180deg)';
    }
}

// === CATEGORY FILTERING ===
let allProductsList = Array.from(document.querySelectorAll('.product-quick-add')).map(btn => ({
    element: btn,
    category: btn.dataset.category || 'outros',
    name: btn.dataset.productName || '',
}));

// Category filter buttons
document.querySelectorAll('.category-filter').forEach(btn => {
    btn.addEventListener('click', function() {
        const category = this.dataset.category;
        
        // Update active state
        document.querySelectorAll('.category-filter').forEach(b => {
            b.classList.remove('active', 'bg-orange-500', 'text-white');
            b.classList.add('bg-gray-100', 'text-gray-600');
        });
        this.classList.add('active', 'bg-orange-500', 'text-white');
        this.classList.remove('bg-gray-100', 'text-gray-600');
        
        // Filter products
        allProductsList.forEach(product => {
            if (category === 'all' || product.category === category) {
                product.element.parentElement?.classList.remove('hidden');
            } else {
                product.element.parentElement?.classList.add('hidden');
            }
        });
    });
});

// === INFINITE SCROLL FOR PRODUCTS ===
const productsContainer = document.querySelector('.products-scroll-container');
if (productsContainer) {
    productsContainer.addEventListener('scroll', function() {
        // Auto-hide header when scrolling down
        const header = this.querySelector('.sticky');
        if (header) {
            if (this.scrollTop > 50) {
                header.style.opacity = '0.8';
            } else {
                header.style.opacity = '1';
            }
        }
    });
}

// Limpar todos os itens do pedido
document.getElementById('btn-clear-order')?.addEventListener('click', function() {
    pdvState.items = [];
    renderItems();
    updateSummary();
    updateFinalizeButtons();
});

// Focar na busca ao clicar em "Adicionar Mais Itens"
document.getElementById('btn-add-more-items')?.addEventListener('click', function() {
    const searchInput = document.getElementById('product-search');
    if (searchInput) {
        searchInput.focus();
        searchInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
});

// Funções de busca de cliente
let pdvCustomerSearchTimeout;

// Função auxiliar para buscar clientes
function searchCustomers(query, resultsElementId) {
    clearTimeout(pdvCustomerSearchTimeout);
    
    if (query.length < 2) {
        const resultsEl = document.getElementById(resultsElementId);
        if (resultsEl) {
            resultsEl.classList.add('hidden');
        }
        return;
    }
    
    pdvCustomerSearchTimeout = setTimeout(() => {
        fetch(`{{ route('dashboard.pdv.customers.search') }}?q=${encodeURIComponent(query)}`)
            .then(res => res.json())
            .then(data => {
                const resultsEl = document.getElementById(resultsElementId);
                if (!resultsEl) return;
                
                if (data.customers && data.customers.length > 0) {
                    resultsEl.innerHTML = data.customers.map(c => `
                        <button type="button" class="customer-option w-full text-left p-2 hover:bg-accent cursor-pointer" 
                                data-customer-id="${c.id}" 
                                data-customer-name="${c.name || 'Sem nome'}"
                                data-customer-phone="${c.phone || ''}"
                                data-customer-email="${c.email || ''}"
                                data-customer-zip="${c.zip_code || ''}"
                                data-customer-address="${c.address || ''}"
                                data-customer-neighborhood="${c.neighborhood || ''}"
                                data-customer-city="${c.city || ''}"
                                data-customer-state="${c.state || ''}"
                                data-customer-fee="${c.custom_delivery_fee ?? ''}"
                                data-customer-wholesale="${c.is_wholesale ? '1' : '0'}">
                            <p class="font-medium">${c.name || 'Sem nome'}</p>
                            ${c.phone ? `<p class="text-xs text-muted-foreground">${c.phone}</p>` : ''}
                            ${c.email ? `<p class="text-xs text-muted-foreground">${c.email}</p>` : ''}
                        </button>
                    `).join('');
                    resultsEl.classList.remove('hidden');
                } else {
                    resultsEl.innerHTML = '<p class="p-2 text-sm text-muted-foreground">Nenhum cliente encontrado</p>';
                    resultsEl.classList.remove('hidden');
                }
            })
            .catch(err => console.error('Erro ao buscar clientes:', err));
    }, 300);
}

// Busca de cliente mobile (dentro do cart-body)
document.getElementById('customer-search')?.addEventListener('input', function(e) {
    searchCustomers(e.target.value.trim(), 'customer-results');
});

// Busca de cliente desktop (fora do cart-body)
document.getElementById('customer-search-desktop')?.addEventListener('input', function(e) {
    searchCustomers(e.target.value.trim(), 'customer-results-desktop');
});

// Selecionar cliente
document.addEventListener('click', function(e) {
    if (e.target.closest('.customer-option')) {
        const btn = e.target.closest('.customer-option');
        const customerId = btn.dataset.customerId;
        const customerName = btn.dataset.customerName;
        const customerPhone = btn.dataset.customerPhone;
        const customerEmail = btn.dataset.customerEmail;
        
        const customerZip = btn.dataset.customerZip || '';
        const customerAddress = btn.dataset.customerAddress || '';
        const customerNeighborhood = btn.dataset.customerNeighborhood || '';
        const customerCity = btn.dataset.customerCity || '';
        const customerState = btn.dataset.customerState || '';
        const customerFee = btn.dataset.customerFee;
        
        const isWholesale = btn.dataset.customerWholesale === '1' || btn.dataset.customerWholesale === 'true';
        
        pdvState.customer = {
            id: customerId,
            name: customerName,
            phone: customerPhone,
            email: customerEmail,
            zip_code: customerZip,
            address: customerAddress,
            neighborhood: customerNeighborhood,
            city: customerCity,
            state: customerState,
            custom_delivery_fee: customerFee !== '' ? parseFloat(customerFee) : null,
            is_wholesale: isWholesale,
        };
        
        document.getElementById('customer-id').value = customerId;
        
        // Atualizar elementos mobile (dentro do cart-body)
        const selectedCustomerName = document.getElementById('selected-customer-name');
        const selectedCustomerInfo = document.getElementById('selected-customer-info');
        const selectedCustomer = document.getElementById('selected-customer');
        if (selectedCustomerName) selectedCustomerName.textContent = customerName;
        let info = [customerPhone, customerEmail].filter(Boolean).join(' • ') || 'Sem informações de contato';
        if (isWholesale) {
            info += ' • 🔷 Revenda';
        }
        if (selectedCustomerInfo) selectedCustomerInfo.textContent = info;
        if (selectedCustomer) selectedCustomer.classList.remove('hidden');
        const customerResults = document.getElementById('customer-results');
        if (customerResults) customerResults.classList.add('hidden');
        const customerSearch = document.getElementById('customer-search');
        if (customerSearch) customerSearch.value = '';
        
        // Atualizar elementos desktop (fora do cart-body)
        const desktopCustomerName = document.getElementById('desktop-customer-name');
        const desktopCustomerInfo = document.getElementById('desktop-customer-info');
        const desktopSelectedCustomer = document.getElementById('desktop-selected-customer');
        if (desktopCustomerName) desktopCustomerName.textContent = customerName;
        if (desktopCustomerInfo) desktopCustomerInfo.textContent = info;
        if (desktopSelectedCustomer) desktopSelectedCustomer.classList.remove('hidden');
        const customerResultsDesktop = document.getElementById('customer-results-desktop');
        if (customerResultsDesktop) customerResultsDesktop.classList.add('hidden');
        const customerSearchDesktop = document.getElementById('customer-search-desktop');
        if (customerSearchDesktop) customerSearchDesktop.value = '';
        
        // Mostrar seção de frete após selecionar cliente
        // Seção de frete agora está integrada no resumo
        
        // Mostrar seção de produtos após selecionar cliente
        const productsSection = document.getElementById('products-section');
        if (productsSection) {
            productsSection.classList.remove('hidden');
            // Garantir que os produtos sejam visíveis
            productsSection.style.display = 'block';
        }
        
        // Atualizar resumo inicial
        updateSummary();
        
        // Inicializar ícones Lucide após mostrar as seções
        if (window.lucide) {
            window.lucide.createIcons();
        }
        
        // Atualizar preços dos produtos frequentes se for wholesale
        if (isWholesale) {
            updateProductPricesForWholesale(customerId);
            
            // Recarregar produtos da busca se houver
            const productSearch = document.getElementById('product-search');
            if (productSearch && productSearch.value.trim().length >= 2) {
                // Re-disparar busca de produtos para atualizar preços
                productSearch.dispatchEvent(new Event('input'));
            }
        } else {
            // Se não for wholesale, resetar para preços normais
            resetProductPricesToNormal();
        }
        
        // Preencher CEP automaticamente se cliente tiver
        if (customerZip) {
            const cepField = document.getElementById('destination-cep');
            if (cepField) {
                const cep = String(customerZip).replace(/\D/g, '');
                if (cep.length === 8) {
                    cepField.value = cep.substring(0, 5) + '-' + cep.substring(5);
                } else {
                    cepField.value = customerZip;
                }
            }
        }
        
        // Aplicar taxa customizada ou calcular automaticamente pelo CEP do cliente
        applyCustomerDeliveryFee();
        
        // Buscar endereço completo do cliente (da tabela addresses) se disponível
        if (customerId) {
            fetch(`{{ route('dashboard.pdv.customers.search') }}?q=${encodeURIComponent(customerName)}`)
                .then(res => res.json())
                .then(data => {
                    if (data.customers && data.customers.length > 0) {
                        const customer = data.customers.find(c => c.id == customerId);
                        if (customer && customer.address_id) {
                            // Cliente tem endereço na tabela addresses
                            pdvState.customer.address_id = customer.address_id;
                            // Se não tinha endereço completo antes, usar o da tabela
                            if (!pdvState.customer.address && customer.address) {
                                pdvState.customer.address = customer.address;
                            }
                            if (!pdvState.customer.neighborhood && customer.neighborhood) {
                                pdvState.customer.neighborhood = customer.neighborhood;
                            }
                            if (!pdvState.customer.city && customer.city) {
                                pdvState.customer.city = customer.city;
                            }
                            if (!pdvState.customer.state && customer.state) {
                                pdvState.customer.state = customer.state;
                            }
                            if (!pdvState.customer.zip_code && customer.zip_code) {
                                pdvState.customer.zip_code = customer.zip_code;
                            }
                        }
                    }
                })
                .catch(err => console.error('Erro ao buscar endereço do cliente:', err));
        }
        
        updateFinalizeButtons();
    }
});

function applyCustomerDeliveryFee() {
    if (!pdvState.customer) return;

    // Taxa fixa personalizada do cliente
    if (pdvState.customer.custom_delivery_fee !== undefined && pdvState.customer.custom_delivery_fee !== null) {
        const fee = parseFloat(pdvState.customer.custom_delivery_fee);
        if (!isNaN(fee)) {
            document.getElementById('delivery-fee-input').value = fee.toFixed(2);
            const infoEl = document.getElementById('delivery-fee-info');
            infoEl.innerHTML = `Taxa personalizada do cliente aplicada`;
            infoEl.classList.remove('hidden');
            updateSummary();
            updateFinalizeButtons();
            return;
        }
    }

    // Caso tenha CEP salvo no cliente, calcular automaticamente
    if (pdvState.customer.zip_code) {
        const subtotal = pdvState.items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        const btnCalc = document.getElementById('btn-calculate-fee');
        if (btnCalc) btnCalc.disabled = true;
        fetch('{{ route("dashboard.pdv.calculateDeliveryFee") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ 
                cep: String(pdvState.customer.zip_code).replace(/\D/g,''), 
                subtotal: Math.max(0, subtotal),
                customer_id: pdvState.customer.id 
            }),
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const fee = parseFloat(data.delivery_fee);
                document.getElementById('delivery-fee-input').value = fee.toFixed(2);
                const infoEl = document.getElementById('delivery-fee-info');
                if (data.custom) {
                    infoEl.innerHTML = `Taxa personalizada do cliente`;
                } else if (data.free_delivery) {
                    infoEl.innerHTML = `✓ Entrega grátis`;
                } else {
                    infoEl.innerHTML = `Distância: ${data.distance_km ?? '-'} km`;
                }
                infoEl.classList.remove('hidden');
                updateSummary();
            }
        })
        .catch(err => {
            console.error('Erro ao calcular frete:', err);
        })
        .finally(()=>{ if (btnCalc) btnCalc.disabled = false; });
    }

    updateFinalizeButtons();
}

// Função para limpar cliente (compartilhada)
function clearCustomer() {
    pdvState.customer = null;
    const customerIdEl = document.getElementById('customer-id');
    if (customerIdEl) customerIdEl.value = '';
    
    // Limpar elementos mobile
    const selectedCustomer = document.getElementById('selected-customer');
    if (selectedCustomer) selectedCustomer.classList.add('hidden');
    const customerSearch = document.getElementById('customer-search');
    if (customerSearch) customerSearch.value = '';
    const customerResults = document.getElementById('customer-results');
    if (customerResults) customerResults.classList.add('hidden');
    
    // Limpar elementos desktop
    const desktopSelectedCustomer = document.getElementById('desktop-selected-customer');
    if (desktopSelectedCustomer) desktopSelectedCustomer.classList.add('hidden');
    const customerSearchDesktop = document.getElementById('customer-search-desktop');
    if (customerSearchDesktop) customerSearchDesktop.value = '';
    const customerResultsDesktop = document.getElementById('customer-results-desktop');
    if (customerResultsDesktop) customerResultsDesktop.classList.add('hidden');
    
    // Resetar preços quando cliente é removido
    resetProductPricesToNormal();
    updateFinalizeButton();
}

// Limpar cliente mobile
document.getElementById('btn-clear-customer')?.addEventListener('click', clearCustomer);

// Limpar cliente desktop
document.getElementById('btn-clear-customer-desktop')?.addEventListener('click', clearCustomer);

// Função para atualizar preços dos produtos frequentes para clientes de revenda
function updateProductPricesForWholesale(customerId) {
    const productButtons = document.querySelectorAll('.product-quick-add');
    
    productButtons.forEach(btn => {
        const productId = btn.dataset.productId;
        const variantsJson = btn.dataset.variants || '[]';
        let variants = [];
        
        try {
            variants = JSON.parse(variantsJson);
        } catch(e) {
            console.error('Erro ao parsear variantes:', e);
        }
        
        // Buscar preços atualizados via API
        fetch(`{{ route('dashboard.pdv.products.search') }}?q=${encodeURIComponent(productId)}&customer_id=${customerId}&product_id=${productId}`)
            .then(res => res.json())
            .then(data => {
                if (data.products && data.products.length > 0) {
                    const product = data.products.find(p => p.id == productId) || data.products[0];
                    
                    // Atualizar preço do produto
                    const newPrice = product.price;
                    btn.dataset.productPrice = newPrice;
                    
                    // Atualizar variantes se houver
                    if (product.variants && product.variants.length > 0) {
                        btn.dataset.variants = JSON.stringify(product.variants);
                        
                        // Atualizar preço de exibição (menor preço entre variantes ou produto)
                        const minVariantPrice = Math.min(...product.variants.map(v => v.price));
                        const displayPrice = Math.min(newPrice, minVariantPrice);
                        btn.dataset.productPrice = displayPrice;
                        
                        const priceDisplay = btn.querySelector('.product-price-display');
                        if (priceDisplay) {
                            priceDisplay.textContent = `A partir de R$ ${displayPrice.toFixed(2).replace('.', ',')}`;
                        }
                    } else {
                        // Sem variantes, atualizar preço direto
                        const priceDisplay = btn.querySelector('.product-price-display');
                        if (priceDisplay) {
                            priceDisplay.textContent = `R$ ${newPrice.toFixed(2).replace('.', ',')}`;
                        }
                    }
                }
            })
            .catch(err => {
                console.error('Erro ao atualizar preço do produto:', err);
            });
    });
}

// Função para resetar preços para valores normais (carregar do servidor)
function resetProductPricesToNormal() {
    // Recarregar a página ou fazer uma requisição para obter preços normais
    // Por enquanto, vamos apenas recarregar os dados originais dos atributos data
    // Isso pode ser melhorado com uma chamada AJAX, mas por simplicidade vamos recarregar a seção
    const productButtons = document.querySelectorAll('.product-quick-add');
    
    // Os preços originais estão nos atributos data originais do servidor
    // Como não temos acesso fácil aos valores originais, vamos fazer uma requisição sem customer_id
    productButtons.forEach(btn => {
        const productId = btn.dataset.productId;
        const productName = btn.dataset.productName;
        
        // Buscar sem customer_id para obter preços normais
        fetch(`{{ route('dashboard.pdv.products.search') }}?q=${encodeURIComponent(productName)}&product_id=${productId}`)
            .then(res => res.json())
            .then(data => {
                if (data.products && data.products.length > 0) {
                    const product = data.products.find(p => p.id == productId) || data.products[0];
                    const newPrice = product.price;
                    
                    // Atualizar atributos
                    btn.dataset.productPrice = newPrice;
                    
                    if (product.variants && product.variants.length > 0) {
                        btn.dataset.variants = JSON.stringify(product.variants);
                        const minVariantPrice = Math.min(...product.variants.map(v => v.price));
                        const displayPrice = Math.min(newPrice, minVariantPrice);
                        
                        const priceDisplay = btn.querySelector('.product-price-display');
                        if (priceDisplay) {
                            priceDisplay.textContent = `A partir de R$ ${displayPrice.toFixed(2).replace('.', ',')}`;
                        }
                    } else {
                        const priceDisplay = btn.querySelector('.product-price-display');
                        if (priceDisplay) {
                            priceDisplay.textContent = `R$ ${newPrice.toFixed(2).replace('.', ',')}`;
                        }
                    }
                }
            })
            .catch(err => {
                console.error('Erro ao resetar preço do produto:', err);
            });
    });
}

// Buscar produtos com melhor relância
let allProducts = [];
let currentPage = 1;
const itemsPerPage = 12;

// Função para ordenar produtos por relevância
function sortProductsByRelevance(products, query) {
    const queryLower = query.toLowerCase();
    
    return products.sort((a, b) => {
        const aName = a.name.toLowerCase();
        const bName = b.name.toLowerCase();
        
        // Começam com a query
        const aStartsWith = aName.startsWith(queryLower) ? 0 : 1;
        const bStartsWith = bName.startsWith(queryLower) ? 0 : 1;
        if (aStartsWith !== bStartsWith) return aStartsWith - bStartsWith;
        
        // Contêm exatamente a query
        const aExact = aName === queryLower ? 0 : 1;
        const bExact = bName === queryLower ? 0 : 1;
        if (aExact !== bExact) return aExact - bExact;
        
        // Contêm a query (palavras)
        const aWords = aName.split(' ');
        const bWords = bName.split(' ');
        const aHasWord = aWords.some(w => w.startsWith(queryLower)) ? 0 : 1;
        const bHasWord = bWords.some(w => w.startsWith(queryLower)) ? 0 : 1;
        if (aHasWord !== bHasWord) return aHasWord - bHasWord;
        
        // Índice de posição da query no nome
        const aIndex = aName.indexOf(queryLower);
        const bIndex = bName.indexOf(queryLower);
        if (aIndex !== bIndex) return aIndex - bIndex;
        
        // Ordem alfabética como desempate
        return aName.localeCompare(bName);
    });
}

// Função para renderizar produtos com paginação
function renderProducts(products, page = 1) {
    const grid = document.getElementById('products-grid');
    const pagination = document.getElementById('pagination');
    
    if (!grid) return;
    
    const start = (page - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    const paginatedProducts = products.slice(start, end);
    const totalPages = Math.ceil(products.length / itemsPerPage);
    
    // Renderizar produtos
    grid.innerHTML = paginatedProducts.map(p => {
        const hasVariants = p.has_variants && p.variants && p.variants.length > 0;
        const displayPrice = hasVariants ? (p.variants[0]?.price || p.price) : p.price;
        return `
            <button type="button" class="product-quick-add text-left border rounded-lg hover:bg-accent hover:border-primary transition-all shadow-sm hover:shadow-md p-3 h-32 flex flex-col" 
                    style="border-color: hsl(var(--border)); background-color: hsl(var(--card));" 
                    data-product-id="${p.id}" 
                    data-product-name="${p.name}" 
                    data-product-price="${displayPrice}" 
                    data-has-variants="${hasVariants ? 'true' : 'false'}" 
                    data-variants='${JSON.stringify(p.variants || [])}'>
                <p class="font-semibold text-xs line-clamp-1" style="color: hsl(var(--foreground));">${p.name}</p>
                <div class="mt-auto w-full">
                    ${hasVariants 
                        ? `<p class="text-xs font-bold product-price-display" style="color: hsl(var(--primary));">A partir de R$ ${parseFloat(displayPrice).toFixed(2).replace('.', ',')}</p>`
                        : `<p class="text-xs font-bold product-price-display" style="color: hsl(var(--primary));">R$ ${parseFloat(displayPrice).toFixed(2).replace('.', ',')}</p>`
                    }
                </div>
            </button>
        `;
    }).join('');
    
    // Renderizar paginação
    if (totalPages > 1) {
        let paginationHTML = '';
        if (page > 1) {
            paginationHTML += `<button class="page-btn px-3 py-1 rounded border hover:bg-accent" data-page="${page - 1}">&larr;</button>`;
        }
        for (let i = Math.max(1, page - 2); i <= Math.min(totalPages, page + 2); i++) {
            paginationHTML += `<button class="page-btn px-3 py-1 rounded border ${i === page ? 'bg-orange-500 text-white' : 'hover:bg-accent'}" data-page="${i}">${i}</button>`;
        }
        if (page < totalPages) {
            paginationHTML += `<button class="page-btn px-3 py-1 rounded border hover:bg-accent" data-page="${page + 1}">&rarr;</button>`;
        }
        pagination.innerHTML = paginationHTML;
        
        // Event listeners para paginação
        pagination.querySelectorAll('.page-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const newPage = parseInt(btn.dataset.page);
                renderProducts(products, newPage);
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        });
    } else {
        pagination.innerHTML = '';
    }
}

let pdvProductSearchTimeout;
document.getElementById('product-search')?.addEventListener('input', function(e) {
    clearTimeout(pdvProductSearchTimeout);
    const query = e.target.value.trim();
    const resultsEl = document.getElementById('product-results');
    
    if (query.length < 1) {
        // Se busca vazia, mostrar todos os produtos da página
        allProducts = [];
        renderProducts(Array.from(document.querySelectorAll('.product-quick-add')).map(btn => ({
            id: btn.dataset.productId,
            name: btn.dataset.productName,
            price: parseFloat(btn.dataset.productPrice),
            has_variants: btn.dataset.hasVariants === 'true',
            variants: JSON.parse(btn.dataset.variants || '[]')
        })));
        resultsEl.classList.add('hidden');
        return;
    }
    
    pdvProductSearchTimeout = setTimeout(() => {
        const customerId = pdvState.customer?.id || '';
        const url = `{{ route('dashboard.pdv.products.search') }}?q=${encodeURIComponent(query)}${customerId ? '&customer_id=' + customerId : ''}`;
        fetch(url)
            .then(res => res.json())
            .then(data => {
                if (data.products && data.products.length > 0) {
                    // Ordenar por relevância
                    const sorted = sortProductsByRelevance(data.products, query);
                    resultsEl.innerHTML = sorted.map(p => {
                        const hasVariants = p.has_variants && p.variants && p.variants.length > 0;
                        const displayPrice = hasVariants ? (p.variants[0]?.price || p.price) : p.price;
                        return `
                            <button type="button" class="product-option w-full text-left p-2 hover:bg-accent cursor-pointer border-b last:border-b-0" 
                                    data-product-id="${p.id}" 
                                    data-product-name="${p.name}" 
                                    data-product-price="${displayPrice}" 
                                    data-has-variants="${hasVariants ? 'true' : 'false'}" 
                                    data-variants='${JSON.stringify(p.variants || [])}'>
                                <p class="font-medium text-sm">${p.name}</p>
                                ${hasVariants 
                                    ? `<p class="text-xs text-muted-foreground">A partir de R$ ${parseFloat(displayPrice).toFixed(2).replace('.', ',')}</p>`
                                    : `<p class="text-xs text-muted-foreground">R$ ${parseFloat(displayPrice).toFixed(2).replace('.', ',')}</p>`
                                }
                            </button>
                        `;
                    }).join('');
                    resultsEl.classList.remove('hidden');
                } else {
                    resultsEl.innerHTML = '<p class="p-2 text-sm text-muted-foreground">Nenhum produto encontrado</p>';
                    resultsEl.classList.remove('hidden');
                }
            })
            .catch(err => console.error('Erro ao buscar produtos:', err));
    }, 300);
});

// Modal de seleção de quantidade
function showQuantityModal(productName, productPrice, callback, existingObservation = '') {
    // Esta função agora é apenas um wrapper para showProductModal sem variantes
    showProductModal(null, productName, productPrice, [], callback, existingObservation);
}

// Modal de seleção de variante - agora redireciona para o modal unificado
function showVariantModal(productId, productName, variants) {
    showProductModal(productId, productName, null, variants, null, '');
}

// Modal UNIFICADO - Variantes + Quantidade + Observações
function showProductModal(productId, productName, basePrice, variants = [], directCallback = null, existingObservation = '') {
    const hasVariants = variants && variants.length > 0;
    let selectedVariant = hasVariants ? variants[0] : null;
    let currentPrice = hasVariants ? parseFloat(selectedVariant.price) : parseFloat(basePrice);
    let quantity = 1;
    
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 z-50 flex items-center justify-center';
    modal.style.backgroundColor = 'rgba(0, 0, 0, 0.75)';
    modal.id = 'product-modal';
    
    const updateTotal = () => {
        const totalEl = modal.querySelector('#modal-total');
        const priceEl = modal.querySelector('#modal-unit-price');
        if (totalEl) {
            totalEl.textContent = `R$ ${(quantity * currentPrice).toFixed(2).replace('.', ',')}`;
        }
        if (priceEl) {
            priceEl.textContent = `R$ ${currentPrice.toFixed(2).replace('.', ',')}`;
        }
    };
    
    modal.innerHTML = `
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 relative max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <h3 class="text-xl font-semibold mb-4">${productName}</h3>
                
                ${hasVariants ? `
                <!-- Seção de Variantes -->
                <div class="mb-4">
                    <p class="text-sm text-gray-500 mb-2">Escolha uma opção:</p>
                    <div class="space-y-2 max-h-40 overflow-y-auto">
                        ${variants.map((v, index) => `
                            <button type="button" 
                                    class="variant-option w-full text-left p-3 border-2 rounded-lg transition-colors ${index === 0 ? 'selected border-orange-500 bg-orange-50' : 'border-gray-200 hover:border-orange-300'}"
                                    data-variant-id="${v.id}"
                                    data-variant-name="${v.name}"
                                    data-variant-price="${v.price}">
                                <div class="flex justify-between items-center">
                                    <span class="font-medium">${v.name}</span>
                                    <span class="text-orange-600 font-bold">R$ ${parseFloat(v.price).toFixed(2).replace('.', ',')}</span>
                                </div>
                            </button>
                        `).join('')}
                    </div>
                </div>
                ` : ''}
                
                <!-- Preço unitário -->
                <div class="flex items-center justify-between text-sm mb-4 py-2 border-t border-b border-gray-100">
                    <span class="text-gray-500">Preço unitário:</span>
                    <span id="modal-unit-price" class="font-bold text-orange-600">R$ ${currentPrice.toFixed(2).replace('.', ',')}</span>
                </div>
                
                <!-- Quantidade -->
                <div class="flex items-center justify-center gap-6 mb-4">
                    <button type="button" id="btn-decrease-qty" class="w-12 h-12 rounded-full border-2 border-gray-300 flex items-center justify-center hover:bg-gray-100 hover:border-orange-500 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 12h14"></path>
                        </svg>
                    </button>
                    <span id="quantity-display" class="text-3xl font-bold w-16 text-center">${quantity}</span>
                    <button type="button" id="btn-increase-qty" class="w-12 h-12 rounded-full border-2 border-gray-300 flex items-center justify-center hover:bg-gray-100 hover:border-orange-500 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 12h14"></path>
                            <path d="M12 5v14"></path>
                        </svg>
                    </button>
                </div>
                
                <!-- Observação -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Observação (opcional)</label>
                    <textarea id="item-observation" rows="2" maxlength="500" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm resize-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500" placeholder="Ex: Sem glúten, sem açúcar, personalização...">${existingObservation}</textarea>
                </div>
                
                <!-- Total -->
                <div class="flex items-center justify-between text-lg font-bold mb-6 p-3 bg-orange-50 rounded-lg">
                    <span>Total:</span>
                    <span id="modal-total" class="text-orange-600">R$ ${(quantity * currentPrice).toFixed(2).replace('.', ',')}</span>
                </div>
                
                <!-- Botões -->
                <div class="flex gap-3">
                    <button type="button" id="btn-cancel-modal" class="flex-1 px-4 py-3 rounded-lg border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 font-medium">
                        Cancelar
                    </button>
                    <button type="button" id="btn-add-to-cart" class="flex-1 px-4 py-3 rounded-lg bg-orange-500 hover:bg-orange-600 text-white font-bold">
                        Adicionar
                    </button>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    const quantityDisplay = modal.querySelector('#quantity-display');
    const btnDecrease = modal.querySelector('#btn-decrease-qty');
    const btnIncrease = modal.querySelector('#btn-increase-qty');
    const btnCancel = modal.querySelector('#btn-cancel-modal');
    const btnAdd = modal.querySelector('#btn-add-to-cart');
    
    // Seletor de variantes
    if (hasVariants) {
        modal.querySelectorAll('.variant-option').forEach(btn => {
            btn.addEventListener('click', function() {
                // Remover seleção anterior
                modal.querySelectorAll('.variant-option').forEach(b => {
                    b.classList.remove('selected', 'border-orange-500', 'bg-orange-50');
                    b.classList.add('border-gray-200');
                });
                // Selecionar atual
                this.classList.add('selected', 'border-orange-500', 'bg-orange-50');
                this.classList.remove('border-gray-200');
                
                selectedVariant = {
                    id: this.dataset.variantId,
                    name: this.dataset.variantName,
                    price: parseFloat(this.dataset.variantPrice)
                };
                currentPrice = selectedVariant.price;
                updateTotal();
            });
        });
    }
    
    btnDecrease.addEventListener('click', () => {
        if (quantity > 1) {
            quantity--;
            quantityDisplay.textContent = quantity;
            updateTotal();
        }
    });
    
    btnIncrease.addEventListener('click', () => {
        quantity++;
        quantityDisplay.textContent = quantity;
        updateTotal();
    });
    
    btnCancel.addEventListener('click', () => {
        document.body.removeChild(modal);
    });
    
    btnAdd.addEventListener('click', () => {
        const observation = modal.querySelector('#item-observation')?.value?.trim() || '';
        
        if (directCallback) {
            // Chamado de showQuantityModal sem variantes
            directCallback(quantity, observation);
        } else {
            // Adicionar diretamente ao carrinho
            const itemName = hasVariants && selectedVariant 
                ? `${productName} - ${selectedVariant.name}` 
                : productName;
            
            addItem({
                product_id: productId,
                variant_id: hasVariants && selectedVariant ? selectedVariant.id : null,
                name: itemName,
                price: currentPrice,
                quantity: quantity,
                special_instructions: observation,
            });
        }
        
        document.body.removeChild(modal);
        document.getElementById('product-results')?.classList.add('hidden');
        document.getElementById('product-search').value = '';
    });
    
    // Fechar ao clicar fora
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            document.body.removeChild(modal);
        }
    });
}

// Adicionar produto (busca ou botão rápido)
document.addEventListener('click', function(e) {
    if (e.target.closest('.product-quick-add') || e.target.closest('.product-option')) {
        const btn = e.target.closest('.product-quick-add') || e.target.closest('.product-option');
        const productId = btn.dataset.productId;
        const productName = btn.dataset.productName;
        const productPrice = parseFloat(btn.dataset.productPrice);
        const hasVariants = btn.dataset.hasVariants === 'true';
        const variantsJson = btn.dataset.variants || '[]';
        
        // Se tem variantes, mostrar modal de seleção
        if (hasVariants) {
            try {
                const variants = JSON.parse(variantsJson);
                if (variants && variants.length > 0) {
                    showVariantModal(productId, productName, variants);
                    return;
                }
            } catch(err) {
                console.error('Erro ao parsear variantes:', err);
            }
        }
        
        // Sem variantes, mostrar modal de quantidade
        showQuantityModal(productName, productPrice, (qty, observation) => {
            addItem({
                product_id: productId,
                variant_id: null,
                name: productName,
                price: productPrice,
                quantity: qty,
                special_instructions: observation,
            });
        });
        
        document.getElementById('product-results')?.classList.add('hidden');
        document.getElementById('product-search').value = '';
    }
});

// Adicionar item ao pedido
function addItem(item) {
    try {
        // Validar dados do item
        if (!item) {
            console.error('Item inválido: item é null ou undefined');
            alert('Erro: Item inválido. Por favor, tente novamente.');
            return;
        }
        
        if (!item.name || item.name.trim() === '') {
            console.error('Item inválido: nome é obrigatório');
            alert('Erro: Nome do produto é obrigatório.');
            return;
        }
        
        // Garantir que product_id seja número ou null
        const productId = item.product_id ? parseInt(item.product_id) : null;
        const variantId = item.variant_id ? parseInt(item.variant_id) : null;
        const price = parseFloat(item.price);
        const quantity = parseInt(item.quantity || 1);
        
        // Validar preço
        if (isNaN(price) || price <= 0) {
            console.error('Item inválido: preço inválido', item);
            alert('Erro: Preço inválido. Por favor, tente novamente.');
            return;
        }
        
        // Validar quantidade
        if (isNaN(quantity) || quantity <= 0) {
            console.error('Item inválido: quantidade inválida', item);
            alert('Erro: Quantidade inválida. Por favor, tente novamente.');
            return;
        }
        
        // Identificar item único por produto + variante + preço
        const existingItem = pdvState.items.find(i => 
            i.product_id === productId && 
            i.variant_id === variantId &&
            Math.abs(i.price - price) < 0.01 // Comparação de float com tolerância
        );
        
        if (existingItem) {
            existingItem.quantity += quantity;
            // Se já existia sem observação e agora tem, adicionar
            if (item.special_instructions && !existingItem.special_instructions) {
                existingItem.special_instructions = item.special_instructions;
            }
        } else {
            pdvState.items.push({
                product_id: productId,
                variant_id: variantId,
                name: String(item.name).trim(),
                price: price,
                quantity: quantity,
                special_instructions: item.special_instructions || '',
            });
        }
        
        renderItems();
        updateSummary();
        updateFinalizeButtons();
        expandCartOnMobile(); // Expandir carrinho no mobile ao adicionar item
    } catch (error) {
        console.error('Erro ao adicionar item:', error, item);
        alert('Erro ao adicionar item ao pedido. Por favor, tente novamente.');
    }
}

// Remover item
function removeItem(index) {
    pdvState.items.splice(index, 1);
    renderItems();
    updateSummary();
    updateFinalizeButton();
}

// Modal para editar observação do item
function showObservationModal(index, currentObservation) {
    const item = pdvState.items[index];
    if (!item) return;
    
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 z-50 flex items-center justify-center';
    modal.style.backgroundColor = 'rgba(0, 0, 0, 0.75)';
    modal.id = 'observation-modal';
    
    modal.innerHTML = `
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 relative">
            <div class="p-6">
                <h3 class="text-lg font-semibold mb-2">Observação do Produto</h3>
                <p class="text-sm text-gray-600 mb-4">${item.name}</p>
                
                <div class="mb-4">
                    <textarea id="obs-text" rows="3" maxlength="500" class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm resize-none" placeholder="Ex: Sem glúten, sem açúcar, personalização...">${currentObservation}</textarea>
                    <p class="text-xs text-gray-400 mt-1">Máximo 500 caracteres</p>
                </div>
                
                <div class="flex gap-3">
                    <button type="button" id="btn-cancel-obs" class="flex-1 px-4 py-2 rounded-md border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 font-medium">
                        Cancelar
                    </button>
                    <button type="button" id="btn-clear-obs" class="px-4 py-2 rounded-md border border-red-300 bg-white hover:bg-red-50 text-red-600 font-medium">
                        Limpar
                    </button>
                    <button type="button" id="btn-save-obs" class="flex-1 px-4 py-2 rounded-md bg-orange-500 hover:bg-orange-600 text-white font-medium">
                        Salvar
                    </button>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Focar no textarea
    const textarea = modal.querySelector('#obs-text');
    textarea.focus();
    textarea.setSelectionRange(textarea.value.length, textarea.value.length);
    
    // Handlers
    modal.querySelector('#btn-cancel-obs').addEventListener('click', () => {
        document.body.removeChild(modal);
    });
    
    modal.querySelector('#btn-clear-obs').addEventListener('click', () => {
        pdvState.items[index].special_instructions = '';
        renderItems();
        document.body.removeChild(modal);
    });
    
    modal.querySelector('#btn-save-obs').addEventListener('click', () => {
        const newObs = modal.querySelector('#obs-text').value.trim();
        pdvState.items[index].special_instructions = newObs;
        renderItems();
        document.body.removeChild(modal);
    });
    
    // Fechar ao clicar fora
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            document.body.removeChild(modal);
        }
    });
}

// Atualizar quantidade
function updateQuantity(index, delta) {
    pdvState.items[index].quantity = Math.max(1, pdvState.items[index].quantity + delta);
    renderItems();
    updateSummary();
    updateFinalizeButton();
}

// Renderizar itens - Design Lovable
function renderItems() {
    const itemsEl = document.getElementById('pdv-items-list');
    const badgeEl = document.getElementById('order-items-badge');
    
    const totalItems = pdvState.items.reduce((sum, item) => sum + item.quantity, 0);
    
    // Atualizar badge
    if (badgeEl) {
        if (totalItems > 0) {
            badgeEl.textContent = totalItems;
            badgeEl.classList.remove('hidden');
            badgeEl.classList.add('flex');
        } else {
            badgeEl.classList.add('hidden');
            badgeEl.classList.remove('flex');
        }
    }
    
    if (pdvState.items.length === 0) {
        itemsEl.innerHTML = `
            <div class="flex flex-col items-center justify-center py-6 text-center text-gray-400">
                <i data-lucide="shopping-cart" class="h-6 w-6 mb-1 opacity-50"></i>
                <p class="text-xs">Carrinho vazio</p>
            </div>
        `;
        if (window.lucide) {
            window.lucide.createIcons();
        }
        return;
    }
    
    itemsEl.innerHTML = pdvState.items.map((item, index) => `
        <div class="flex items-start justify-between py-1.5 border-b border-gray-100 last:border-0">
            <div class="flex-1 min-w-0 pr-2">
                <p class="font-medium text-xs text-gray-800 truncate leading-tight">${item.name}</p>
                <p class="text-[10px] text-gray-500 whitespace-nowrap">R$ ${item.price.toFixed(2).replace('.', ',')} un</p>
            </div>
            <div class="flex items-center gap-0.5">
                <button type="button" class="btn-dec-qty w-6 h-6 flex items-center justify-center border border-gray-300 rounded text-gray-600 hover:bg-gray-100" data-index="${index}">
                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg>
                </button>
                <span class="w-5 text-center text-[11px] font-medium">${item.quantity}</span>
                <button type="button" class="btn-inc-qty w-6 h-6 flex items-center justify-center border border-gray-300 rounded text-gray-600 hover:bg-gray-100" data-index="${index}">
                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                </button>
                <span class="w-16 text-right text-[11px] font-bold text-orange-600 whitespace-nowrap">R$ ${(item.price * item.quantity).toFixed(2).replace('.', ',')}</span>
                <button type="button" class="btn-remove-item w-6 h-6 flex items-center justify-center text-gray-400 hover:text-red-500 hover:bg-red-50 rounded" data-index="${index}">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                </button>
            </div>
            ${item.special_instructions ? `
            <div class="mt-1 flex items-center gap-1">
                <span class="text-[10px] text-orange-600 bg-orange-50 px-1.5 py-0.5 rounded italic truncate flex-1" title="${(item.special_instructions || '').replace(/"/g, '&quot;')}">Obs: ${item.special_instructions}</span>
                <button type="button" class="btn-edit-obs text-[10px] text-blue-500 hover:text-blue-700 px-1" data-index="${index}" title="Editar">✏️</button>
            </div>
            ` : `
            <div class="mt-1"><button type="button" class="btn-add-obs text-[10px] text-gray-400 hover:text-gray-600" data-index="${index}">+ Obs</button></div>
            `}
        </div>
    `).join('');
    
    // Event listeners para botões
    itemsEl.querySelectorAll('.btn-dec-qty').forEach(btn => {
        btn.addEventListener('click', () => updateQuantity(parseInt(btn.dataset.index), -1));
    });
    
    itemsEl.querySelectorAll('.btn-inc-qty').forEach(btn => {
        btn.addEventListener('click', () => updateQuantity(parseInt(btn.dataset.index), 1));
    });
    
    itemsEl.querySelectorAll('.btn-remove-item').forEach(btn => {
        btn.addEventListener('click', () => removeItem(parseInt(btn.dataset.index)));
    });
    
    // Event listeners para observações
    itemsEl.querySelectorAll('.btn-add-obs, .btn-edit-obs').forEach(btn => {
        btn.addEventListener('click', () => {
            const index = parseInt(btn.dataset.index);
            const item = pdvState.items[index];
            showObservationModal(index, item.special_instructions || '');
        });
    });
}

// Atualizar resumo
function updateSummary() {
    const itemsCount = pdvState.items.reduce((sum, item) => sum + item.quantity, 0);
    const subtotal = pdvState.items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const deliveryFee = parseFloat(document.getElementById('delivery-fee-input')?.value) || 0;
    
    // Desconto do cupom
    const couponDiscount = pdvState.coupon ? (pdvState.coupon.discount || 0) : 0;
    
    // Desconto manual (fixo e porcentagem) - se os campos existirem
    const manualDiscountFixed = parseFloat(document.getElementById('manual-discount-fixed')?.value) || 0;
    const manualDiscountPercent = parseFloat(document.getElementById('manual-discount-percent')?.value) || 0;
    const manualDiscountFromPercent = subtotal * (manualDiscountPercent / 100);
    
    // Total de desconto (cupom + manual fixo + manual porcentagem)
    const totalDiscount = couponDiscount + manualDiscountFixed + manualDiscountFromPercent;
    
    // Calcular total final
    const total = Math.max(0, subtotal + deliveryFee - totalDiscount);
    
    // Atualizar contadores
    const orderItemsCountEl = document.getElementById('order-items-count');
    if (orderItemsCountEl) {
        orderItemsCountEl.textContent = itemsCount;
    }
    
    // Atualizar badge
    const badgeEl = document.getElementById('order-items-badge');
    if (badgeEl) {
        if (itemsCount > 0) {
            badgeEl.textContent = itemsCount;
            badgeEl.classList.remove('hidden');
            badgeEl.classList.add('flex');
        } else {
            badgeEl.classList.add('hidden');
            badgeEl.classList.remove('flex');
        }
    }
    
    // Atualizar subtotal
    const summarySubtotalEl = document.getElementById('summary-subtotal');
    if (summarySubtotalEl) {
        summarySubtotalEl.textContent = 'R$ ' + subtotal.toFixed(2).replace('.', ',');
    }
    
    // Atualizar taxa de entrega display
    const summaryDeliveryEl = document.getElementById('summary-delivery-fee');
    if (summaryDeliveryEl) {
        summaryDeliveryEl.textContent = 'R$ ' + deliveryFee.toFixed(2).replace('.', ',');
    }
    
    const deliveryFeeDisplayEl = document.getElementById('delivery-fee-display');
    if (deliveryFeeDisplayEl) {
        deliveryFeeDisplayEl.textContent = 'R$ ' + deliveryFee.toFixed(2).replace('.', ',');
    }
    
    // Atualizar total
    const summaryTotalEl = document.getElementById('summary-total');
    if (summaryTotalEl) {
        summaryTotalEl.textContent = 'R$ ' + total.toFixed(2).replace('.', ',');
    }
    
    // Mostrar/esconder linha de desconto
    const discountRowEl = document.getElementById('discount-row');
    const summaryDiscountEl = document.getElementById('summary-discount');
    if (discountRowEl && summaryDiscountEl) {
        if (totalDiscount > 0) {
            discountRowEl.classList.remove('hidden');
            summaryDiscountEl.textContent = '- R$ ' + totalDiscount.toFixed(2).replace('.', ',');
        } else {
            discountRowEl.classList.add('hidden');
        }
    }
    
    // Atualizar toggle do carrinho (mobile)
    updateCartToggle();
}

// Atualizar taxa de entrega
document.getElementById('delivery-fee-input')?.addEventListener('input', function() {
    // Atualizar display da taxa
    const fee = parseFloat(this.value) || 0;
    const displayEl = document.getElementById('delivery-fee-display');
    if (displayEl) {
        displayEl.textContent = 'R$ ' + fee.toFixed(2).replace('.', ',');
    }
    updateSummary();
});

// Botão Definir taxa manual
document.getElementById('btn-set-fee')?.addEventListener('click', function() {
    const feeInput = document.getElementById('delivery-fee-input');
    const fee = parseFloat(feeInput.value) || 0;
    pdvState.deliveryFee = fee;
    
    const displayEl = document.getElementById('delivery-fee-display');
    if (displayEl) {
        displayEl.textContent = 'R$ ' + fee.toFixed(2).replace('.', ',');
    }
    
    const infoEl = document.getElementById('delivery-fee-info');
    if (infoEl) {
        infoEl.textContent = 'Taxa manual definida';
        infoEl.classList.remove('hidden');
        setTimeout(() => infoEl.classList.add('hidden'), 3000);
    }
    
    updateSummary();
});

// Toggle Retirada/Entrega
document.getElementById('btn-pickup')?.addEventListener('click', function() {
    pdvState.deliveryType = 'pickup';
    
    // Atualizar estilos dos botões
    this.classList.add('bg-orange-500', 'text-white');
    this.classList.remove('bg-white', 'text-gray-600');
    
    const btnDelivery = document.getElementById('btn-delivery');
    btnDelivery.classList.remove('bg-orange-500', 'text-white');
    btnDelivery.classList.add('bg-white', 'text-gray-600');
    
    // Esconder seção de endereço
    const addressSection = document.getElementById('delivery-address-section');
    if (addressSection) {
        addressSection.classList.add('hidden');
    }
    
    // Zerar taxa
    document.getElementById('delivery-fee-input').value = '0';
    const displayEl = document.getElementById('delivery-fee-display');
    if (displayEl) {
        displayEl.textContent = 'R$ 0,00';
    }
    
    updateSummary();
});

document.getElementById('btn-delivery')?.addEventListener('click', function() {
    pdvState.deliveryType = 'delivery';
    
    // Atualizar estilos dos botões
    this.classList.add('bg-orange-500', 'text-white');
    this.classList.remove('bg-white', 'text-gray-600');
    
    const btnPickup = document.getElementById('btn-pickup');
    btnPickup.classList.remove('bg-orange-500', 'text-white');
    btnPickup.classList.add('bg-white', 'text-gray-600');
    
    // Mostrar seção de endereço
    const addressSection = document.getElementById('delivery-address-section');
    if (addressSection) {
        addressSection.classList.remove('hidden');
    }
    
    updateSummary();
});

// Atualizar resumo quando desconto manual for alterado
document.getElementById('manual-discount-fixed')?.addEventListener('input', updateSummary);
document.getElementById('manual-discount-percent')?.addEventListener('input', updateSummary);

// Calcular taxa de entrega por CEP
document.getElementById('btn-calculate-fee')?.addEventListener('click', function() {
    const cep = document.getElementById('destination-cep').value.trim();
    const cepClean = cep.replace(/\D/g, '');
    
    if (cepClean.length !== 8) {
        alert('CEP inválido. Digite um CEP válido com 8 dígitos.');
        return;
    }
    
    const subtotal = pdvState.items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    
    if (subtotal <= 0) {
        alert('Adicione itens ao pedido antes de calcular a taxa de entrega.');
        return;
    }
    
    const btn = this;
    btn.disabled = true;
    btn.textContent = 'Calculando...';
    
    fetch('{{ route("dashboard.pdv.calculateDeliveryFee") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
        },
        body: JSON.stringify({ cep: cepClean, subtotal: subtotal }),
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const fee = parseFloat(data.delivery_fee);
            pdvState.deliveryFee = fee;
            document.getElementById('delivery-fee-input').value = fee.toFixed(2);
            
            const infoEl = document.getElementById('delivery-fee-info');
            if (data.free_delivery) {
                infoEl.innerHTML = `✓ Entrega grátis!`;
                infoEl.classList.remove('hidden');
            } else {
                infoEl.innerHTML = `Distância: ${data.distance_km} km`;
                infoEl.classList.remove('hidden');
            }
            
            updateSummary();
        } else {
            alert(data.message || 'Erro ao calcular taxa de entrega');
            const infoEl = document.getElementById('delivery-fee-info');
            infoEl.classList.add('hidden');
        }
    })
    .catch(err => {
        console.error('Erro ao calcular taxa de entrega:', err);
        alert('Erro ao calcular taxa de entrega');
    })
    .finally(() => {
        btn.disabled = false;
        btn.textContent = 'Calcular';
    });
});

// Formatar CEP ao digitar e buscar endereço automaticamente
(function() {
    const destinationCepInput = document.getElementById('destination-cep');
    if (!destinationCepInput) return;
    
    let cepTimeout = null;
    
    // Função para buscar endereço via ViaCEP
    async function buscarEnderecoPorCep(cep) {
        const cepDigits = cep.replace(/\D/g, '');
        
        if (cepDigits.length !== 8) {
            return;
        }
        
        // Mostrar feedback visual
        destinationCepInput.disabled = true;
        destinationCepInput.style.opacity = '0.6';
        
        const infoEl = document.getElementById('delivery-fee-info');
        if (infoEl) {
            infoEl.innerHTML = 'Buscando endereço...';
            infoEl.classList.remove('hidden');
        }
        
        try {
            const response = await safeFetch(`https://viacep.com.br/ws/${cepDigits}/json/`);
            const data = await response.json();
            
            if (data.erro) {
                if (infoEl) {
                    infoEl.innerHTML = 'CEP não encontrado';
                    infoEl.classList.remove('hidden');
                }
            } else {
                // Atualizar dados do cliente no pdvState se disponível
                if (pdvState.customer) {
                    if (data.logradouro && !pdvState.customer.address) {
                        pdvState.customer.address = data.logradouro;
                    }
                    if (data.bairro && !pdvState.customer.neighborhood) {
                        pdvState.customer.neighborhood = data.bairro;
                    }
                    if (data.localidade && !pdvState.customer.city) {
                        pdvState.customer.city = data.localidade;
                    }
                    if (data.uf && !pdvState.customer.state) {
                        pdvState.customer.state = data.uf.toUpperCase();
                    }
                }
                
                if (infoEl) {
                    infoEl.innerHTML = `✓ Endereço encontrado: ${data.logradouro || ''}, ${data.bairro || ''}, ${data.localidade || ''}-${data.uf || ''}`;
                    infoEl.classList.remove('hidden');
                }
                
                // Feedback visual de sucesso
                destinationCepInput.style.borderColor = '#10b981';
                setTimeout(() => {
                    destinationCepInput.style.borderColor = '';
                }, 2000);
            }
        } catch (error) {
            console.error('Erro ao buscar CEP:', error);
            if (infoEl) {
                infoEl.innerHTML = 'Erro ao buscar CEP';
                infoEl.classList.remove('hidden');
            }
        } finally {
            destinationCepInput.disabled = false;
            destinationCepInput.style.opacity = '1';
        }
    }
    
    // Aplicar máscara e buscar automaticamente quando CEP for completo
    destinationCepInput.addEventListener('input', function(e) {
        // Aplicar máscara
        let value = e.target.value.replace(/\D/g, '');
        if (value.length > 5) {
            value = value.substring(0, 5) + '-' + value.substring(5, 8);
        }
        e.target.value = value;
        
        // Limpar timeout anterior
        if (cepTimeout) {
            clearTimeout(cepTimeout);
        }
        
        // Buscar endereço após 800ms de inatividade (quando usuário parar de digitar)
        const cepDigits = value.replace(/\D/g, '');
        if (cepDigits.length === 8) {
            cepTimeout = setTimeout(() => {
                buscarEnderecoPorCep(value);
            }, 800);
        }
    });
    
    // Também buscar quando o campo perder o foco (blur)
    destinationCepInput.addEventListener('blur', function() {
        const cep = this.value.replace(/\D/g, '');
        if (cep.length === 8) {
            buscarEnderecoPorCep(this.value);
        }
    });
})();

// Aplicar cupom
document.getElementById('btn-apply-coupon')?.addEventListener('click', function() {
    const code = document.getElementById('coupon-code').value.trim();
    if (!code) return;
    
    const subtotal = pdvState.items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    
    safeFetch('{{ route("dashboard.pdv.coupons.validate") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
        },
        body: JSON.stringify({ code, subtotal }),
    })
    .then(res => res.json())
    .then(data => {
        if (data.valid) {
            pdvState.coupon = data.coupon;
            pdvState.coupon.discount = data.discount;
            document.getElementById('coupon-info').innerHTML = `
                <p class="font-medium">${data.coupon.code} - ${data.coupon.name}</p>
                <p class="text-xs text-muted-foreground">Desconto: R$ ${data.discount.toFixed(2).replace('.', ',')}</p>
            `;
            document.getElementById('coupon-info').classList.remove('hidden');
            updateSummary();
        } else {
            alert(data.message || 'Cupom inválido');
        }
    })
    .catch(err => {
        console.error('Erro ao validar cupom:', err);
        alert('Erro ao validar cupom');
    });
});

// Tipo de entrega
document.getElementById('delivery-type')?.addEventListener('change', function(e) {
    pdvState.deliveryType = e.target.value;
});

// Observações
document.getElementById('order-notes')?.addEventListener('input', function(e) {
    pdvState.notes = e.target.value;
});

// Atualizar botões de finalizar/enviar
function updateFinalizeButton() {
    updateFinalizeButtons(); // Usar a nova função que atualiza ambos
}

// Chamar updateFinalizeButtons ao carregar a página
document.addEventListener('DOMContentLoaded', function() {
    // Initialize PDV with error checking
    if (!initializePDV()) {
        console.error('Falha na inicialização do PDV');
        return;
    }
    
    updateFinalizeButtons();
    
    // Atualizar resumo inicial
    updateSummary();
});

// Atualizar estado dos botões
function updateFinalizeButtons() {
    const hasCustomer = pdvState.customer !== null;
    const hasItems = pdvState.items.length > 0;
    const enabled = hasCustomer && hasItems;
    
    const btnFinalizeOrder = document.getElementById('btn-finalize-order');
    const btnSendOrder = document.getElementById('btn-send-order');
    const btnCreatePaidOrder = document.getElementById('btn-create-paid-order');
    
    if (btnFinalizeOrder) {
        btnFinalizeOrder.disabled = !enabled;
    }
    if (btnSendOrder) {
        btnSendOrder.disabled = !enabled;
    }
    if (btnCreatePaidOrder) {
        btnCreatePaidOrder.disabled = !enabled;
    }
}

// Enviar pedido (cria e envia link ao cliente)
document.getElementById('btn-send-order')?.addEventListener('click', function() {
    if (!pdvState.customer || pdvState.items.length === 0) {
        alert('Preencha cliente e adicione itens ao pedido');
        return;
    }
    
    if (!pdvState.customer.phone) {
        alert('O cliente precisa ter um telefone cadastrado para receber o pedido');
        return;
    }
    
    if (!confirm('Deseja enviar este pedido ao cliente por WhatsApp? Ele receberá o resumo e um link para escolher data/hora de entrega e forma de pagamento.')) {
        return;
    }
    
    const subtotal = pdvState.items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const deliveryFee = parseFloat(document.getElementById('delivery-fee-input').value) || 0;
    const discount = pdvState.coupon ? (pdvState.coupon.discount || 0) : 0;
    
    // Buscar CEP do campo de destino
    const destinationCep = document.getElementById('destination-cep')?.value?.trim() || '';
    const cepClean = destinationCep.replace(/\D/g, '');
    
    // Preparar dados de endereço se CEP foi fornecido
    let addressData = null;
    if (cepClean.length === 8) {
        // Se houver dados do endereço preenchidos (via busca de CEP), incluir
        addressData = {
            zip_code: cepClean,
            street: pdvState.customer.address || '', // Se tiver endereço do cliente
            number: pdvState.customer.number || '',
            neighborhood: pdvState.customer.neighborhood || '',
            city: pdvState.customer.city || '',
            state: pdvState.customer.state || '',
        };
    }
    
    const orderData = {
        customer_id: pdvState.customer.id,
        items: pdvState.items.map(item => ({
            product_id: item.product_id,
            name: item.name,
            price: item.price,
            quantity: item.quantity,
        })),
        delivery_type: pdvState.deliveryType,
        delivery_fee: deliveryFee,
        coupon_code: pdvState.coupon ? pdvState.coupon.code : null,
        discount_amount: discount,
        notes: pdvState.notes,
        send_to_customer: true, // Flag para enviar ao cliente
        zip_code: cepClean.length === 8 ? cepClean : null,
        address: addressData,
        scheduled_delivery_at: document.getElementById('scheduled-delivery-at')?.value || null,
    };
    
    this.disabled = true;
    this.textContent = 'Enviando...';
    
    fetch('{{ route("dashboard.pdv.send") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
        },
        body: JSON.stringify(orderData),
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            if (data.whatsapp_error) {
                // Pedido criado mas WhatsApp falhou
                const msg = data.message || 'Pedido criado, mas houve problema ao enviar via WhatsApp.';
                if (confirm(msg + '\n\nDeseja ver os detalhes do pedido?')) {
                    window.location.href = '{{ route("dashboard.orders.index") }}?search=' + data.order.order_number;
                } else {
                    window.location.reload();
                }
            } else {
                alert(data.message || 'Pedido enviado ao cliente com sucesso!');
                // Limpar estado e recarregar página
                window.location.reload();
            }
        } else {
            alert('Erro: ' + (data.message || 'Erro ao enviar pedido'));
            this.disabled = false;
            this.textContent = 'Enviar Pedido';
        }
    })
    .catch(err => {
        console.error('Erro ao enviar pedido:', err);
        alert('Erro ao enviar pedido');
        this.disabled = false;
        this.textContent = 'Enviar Pedido';
    });
});

// Finalizar pedido
document.getElementById('btn-finalize-order')?.addEventListener('click', function() {
    document.getElementById('finalize-modal').classList.remove('hidden');
});

// Opções de pagamento no modal
document.querySelectorAll('input[name="payment_option"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const paymentSection = document.getElementById('payment-method-section');
        if (this.value === 'send_link') {
            paymentSection.classList.remove('hidden');
        } else {
            paymentSection.classList.add('hidden');
        }
    });
});

// Confirmar finalização
document.getElementById('btn-confirm-finalize')?.addEventListener('click', function() {
    const paymentOption = document.querySelector('input[name="payment_option"]:checked').value;
    const paymentMethod = paymentOption === 'send_link' 
        ? document.getElementById('payment-method-select').value 
        : null;
    
    const subtotal = pdvState.items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const deliveryFee = parseFloat(document.getElementById('delivery-fee-input').value) || 0;
    const couponDiscount = pdvState.coupon ? (pdvState.coupon.discount || 0) : 0;
    const manualDiscountFixed = parseFloat(document.getElementById('manual-discount-fixed')?.value) || 0;
    const manualDiscountPercent = parseFloat(document.getElementById('manual-discount-percent')?.value) || 0;
    const manualDiscountFromPercent = subtotal * (manualDiscountPercent / 100);
    const totalDiscount = couponDiscount + manualDiscountFixed + manualDiscountFromPercent;
    
    const orderData = {
        customer_id: pdvState.customer.id,
        items: pdvState.items.map(item => ({
            product_id: item.product_id,
            name: item.name,
            price: item.price,
            quantity: item.quantity,
            special_instructions: item.special_instructions || null,
        })),
        delivery_type: pdvState.deliveryType,
        delivery_fee: deliveryFee,
        coupon_code: pdvState.coupon ? pdvState.coupon.code : null,
        discount_amount: totalDiscount,
        manual_discount_fixed: manualDiscountFixed,
        manual_discount_percent: manualDiscountPercent,
        notes: pdvState.notes,
        send_payment_link: paymentOption === 'send_link',
        payment_method: paymentMethod,
        address_id: pdvState.customer.address_id || null,
        scheduled_delivery_at: document.getElementById('scheduled-delivery-at')?.value || null,
    };
    
    fetch('{{ route("dashboard.pdv.store") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
        },
        body: JSON.stringify(orderData),
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            // Limpar estado e recarregar página
            window.location.reload();
        } else {
            alert('Erro: ' + (data.message || 'Erro ao criar pedido'));
        }
    })
    .catch(err => {
        console.error('Erro ao criar pedido:', err);
        alert('Erro ao criar pedido');
    });
});

// Fechar modais
document.getElementById('btn-close-finalize-modal')?.addEventListener('click', () => {
    document.getElementById('finalize-modal').classList.add('hidden');
});
document.getElementById('btn-cancel-finalize')?.addEventListener('click', () => {
    document.getElementById('finalize-modal').classList.add('hidden');
});

// Criar pedido já como pago (sem notificar - para migração)
document.getElementById('btn-create-paid-order')?.addEventListener('click', function() {
    if (!pdvState.customer || pdvState.items.length === 0) {
        alert('Preencha cliente e adicione itens ao pedido');
        return;
    }
    
    if (!confirm('Deseja criar este pedido já como PAGO, sem enviar notificação ao cliente?\n\nEsta ação é para migração de pedidos do sistema antigo.')) {
        return;
    }
    
    const subtotal = pdvState.items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const deliveryFee = parseFloat(document.getElementById('delivery-fee-input').value) || 0;
    const couponDiscount = pdvState.coupon ? (pdvState.coupon.discount || 0) : 0;
    const manualDiscountFixed = parseFloat(document.getElementById('manual-discount-fixed')?.value) || 0;
    const manualDiscountPercent = parseFloat(document.getElementById('manual-discount-percent')?.value) || 0;
    const manualDiscountFromPercent = subtotal * (manualDiscountPercent / 100);
    const totalDiscount = couponDiscount + manualDiscountFixed + manualDiscountFromPercent;
    
    // Buscar CEP do campo de destino ou do cliente
    const destinationCep = document.getElementById('destination-cep')?.value?.trim() || pdvState.customer.zip_code || '';
    const cepClean = destinationCep.replace(/\D/g, '');
    
    // Preparar dados de endereço se CEP foi fornecido
    let addressData = null;
    if (cepClean.length === 8) {
        addressData = {
            zip_code: cepClean,
            street: pdvState.customer.address || '',
            number: pdvState.customer.number || '',
            neighborhood: pdvState.customer.neighborhood || '',
            city: pdvState.customer.city || '',
            state: pdvState.customer.state || '',
        };
    }
    
    const orderData = {
        customer_id: pdvState.customer.id,
        items: pdvState.items.map(item => ({
            product_id: item.product_id,
            name: item.name,
            price: item.price,
            quantity: item.quantity,
            special_instructions: item.special_instructions || null,
        })),
        delivery_type: pdvState.deliveryType,
        delivery_fee: deliveryFee,
        coupon_code: pdvState.coupon ? pdvState.coupon.code : null,
        discount_amount: totalDiscount,
        manual_discount_fixed: manualDiscountFixed,
        manual_discount_percent: manualDiscountPercent,
        notes: pdvState.notes,
        create_as_paid: true, // Flag para criar já como pago
        skip_notification: true, // Não notificar cliente
        zip_code: cepClean.length === 8 ? cepClean : null,
        address: addressData,
        address_id: pdvState.customer.address_id || null,
        scheduled_delivery_at: document.getElementById('scheduled-delivery-at')?.value || null,
    };
    
    this.disabled = true;
    this.textContent = 'Criando...';
    
    fetch('{{ route("dashboard.pdv.store") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
        },
        body: JSON.stringify(orderData),
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert(data.message || 'Pedido criado como pago com sucesso!');
            // Limpar estado e recarregar página
            window.location.reload();
        } else {
            alert('Erro: ' + (data.message || 'Erro ao criar pedido'));
            this.disabled = false;
            this.textContent = 'Criar Pedido Pago (Migração)';
        }
    })
    .catch(err => {
        console.error('Erro ao criar pedido pago:', err);
        alert('Erro ao criar pedido pago');
        this.disabled = false;
        this.textContent = 'Criar Pedido Pago (Migração)';
    });
});

// Modal de novo cliente (mobile e desktop)
document.getElementById('btn-new-customer')?.addEventListener('click', () => {
    document.getElementById('new-customer-modal').classList.remove('hidden');
});

document.getElementById('btn-new-customer-desktop')?.addEventListener('click', () => {
    document.getElementById('new-customer-modal').classList.remove('hidden');
});

document.getElementById('new-customer-form')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const name = document.getElementById('new-customer-name').value;
    const phone = document.getElementById('new-customer-phone').value;
    const email = document.getElementById('new-customer-email').value;
    const isWholesale = document.getElementById('new-customer-is-wholesale').checked;
    
    // Coletar dados de endereço
    const zipCode = document.getElementById('new-customer-zip-code').value.trim();
    const street = document.getElementById('new-customer-street').value.trim();
    const number = document.getElementById('new-customer-number').value.trim();
    const complement = document.getElementById('new-customer-complement').value.trim();
    const neighborhood = document.getElementById('new-customer-neighborhood').value.trim();
    const city = document.getElementById('new-customer-city').value.trim();
    const state = document.getElementById('new-customer-state').value.trim().toUpperCase();
    
    // Preparar dados do endereço (só enviar se houver pelo menos CEP ou rua)
    const addressData = {};
    if (zipCode || street) {
        if (zipCode) addressData.zip_code = zipCode.replace(/\D/g, '');
        if (street) addressData.street = street;
        if (number) addressData.number = number;
        if (complement) addressData.complement = complement;
        if (neighborhood) addressData.neighborhood = neighborhood;
        if (city) addressData.city = city;
        if (state) addressData.state = state;
    }
    
    const requestData = { 
        name, 
        phone, 
        email, 
        is_wholesale: isWholesale 
    };
    
    // Adicionar endereço se houver dados
    if (Object.keys(addressData).length > 0) {
        requestData.address = addressData;
    }
    
    fetch('{{ route("dashboard.pdv.customers.store") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
        },
        body: JSON.stringify(requestData),
    })
    .then(res => res.json())
    .then(data => {
        if (data.customer) {
            // Selecionar o novo cliente
            const customer = data.customer;
            pdvState.customer = {
                id: customer.id,
                name: customer.name,
                phone: customer.phone,
                email: customer.email,
                zip_code: customer.zip_code || '',
                address: customer.address || '',
                neighborhood: customer.neighborhood || '',
                city: customer.city || '',
                state: customer.state || '',
                custom_delivery_fee: customer.custom_delivery_fee || null,
                is_wholesale: customer.is_wholesale || false,
            };
            document.getElementById('customer-id').value = customer.id;
            document.getElementById('selected-customer-name').textContent = customer.name;
            let info = [customer.phone, customer.email].filter(Boolean).join(' • ') || 'Sem informações de contato';
            if (customer.is_wholesale) {
                info += ' • 🔷 Revenda';
            }
            document.getElementById('selected-customer-info').textContent = info;
            document.getElementById('selected-customer').classList.remove('hidden');
            
            // Atualizar preços dos produtos frequentes se for wholesale
            if (customer.is_wholesale) {
                updateProductPricesForWholesale(customer.id);
                
                // Recarregar produtos da busca se houver
                const productSearch = document.getElementById('product-search');
                if (productSearch && productSearch.value.trim().length >= 2) {
                    // Re-disparar busca de produtos para atualizar preços
                    productSearch.dispatchEvent(new Event('input'));
                }
            } else {
                // Se não for wholesale, resetar para preços normais
                resetProductPricesToNormal();
            }
            
            // Fechar modal
            document.getElementById('new-customer-modal').classList.add('hidden');
            document.getElementById('new-customer-form').reset();
            
            updateFinalizeButton();
        }
    })
    .catch(err => {
        console.error('Erro ao criar cliente:', err);
        alert('Erro ao criar cliente');
    });
});

document.getElementById('btn-close-new-customer-modal')?.addEventListener('click', () => {
    document.getElementById('new-customer-modal').classList.add('hidden');
});
document.getElementById('btn-cancel-new-customer')?.addEventListener('click', () => {
    document.getElementById('new-customer-modal').classList.add('hidden');
});

// Botão para adicionar mais itens - rola para o topo da lista de produtos
document.getElementById('btn-add-more-items')?.addEventListener('click', function() {
    const productSearch = document.getElementById('product-search');
    if (productSearch) {
        productSearch.scrollIntoView({ behavior: 'smooth', block: 'start' });
        // Focar no campo de busca após um pequeno delay
        setTimeout(() => {
            productSearch.focus();
        }, 300);
    }
});

// Buscar pedido para confirmação de pagamento
let currentOrderId = null;

document.getElementById('btn-search-order')?.addEventListener('click', function() {
    const orderNumber = document.getElementById('order-number-search').value.trim();
    
    if (!orderNumber) {
        alert('Digite o número do pedido');
        return;
    }
    
    this.disabled = true;
    this.textContent = 'Buscando...';
    
    fetch('{{ route("dashboard.pdv.searchOrder") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
        },
        body: JSON.stringify({ order_number: orderNumber }),
    })
    .then(res => res.json())
    .then(data => {
        this.disabled = false;
        this.textContent = 'Buscar';
        
        if (data.success) {
            currentOrderId = data.order.id;
            const order = data.order;
            
            document.getElementById('order-info').innerHTML = `
                <div class="space-y-2">
                    <p><strong>Pedido:</strong> #${order.order_number}</p>
                    <p><strong>Cliente:</strong> ${order.customer_name}</p>
                    <p><strong>Telefone:</strong> ${order.customer_phone}</p>
                    <p><strong>Valor:</strong> R$ ${parseFloat(order.final_amount).toFixed(2).replace('.', ',')}</p>
                    <p><strong>Status:</strong> ${order.status}</p>
                    <p><strong>Pagamento:</strong> ${order.payment_status}</p>
                    <p><strong>Criado em:</strong> ${order.created_at}</p>
                </div>
            `;
            
            document.getElementById('order-search-result').classList.remove('hidden');
            
            // Habilitar botão de confirmar se ainda não estiver pago
            const btnConfirm = document.getElementById('btn-confirm-payment-silent');
            if (order.payment_status === 'paid') {
                btnConfirm.disabled = true;
                btnConfirm.textContent = 'Pagamento já confirmado';
                btnConfirm.classList.add('opacity-50', 'cursor-not-allowed');
            } else {
                btnConfirm.disabled = false;
                btnConfirm.textContent = 'Confirmar Pagamento (Sem Notificar)';
                btnConfirm.classList.remove('opacity-50', 'cursor-not-allowed');
            }
        } else {
            alert(data.message || 'Pedido não encontrado');
            document.getElementById('order-search-result').classList.add('hidden');
        }
    })
    .catch(err => {
        console.error('Erro ao buscar pedido:', err);
        alert('Erro ao buscar pedido');
        this.disabled = false;
        this.textContent = 'Buscar';
    });
});

// Confirmar pagamento sem notificar
document.getElementById('btn-confirm-payment-silent')?.addEventListener('click', function() {
    if (!currentOrderId) {
        alert('Busque um pedido primeiro');
        return;
    }
    
    if (!confirm('Deseja confirmar o pagamento deste pedido SEM enviar notificação ao cliente?\n\nEsta ação é para pedidos migrados entre plataformas.')) {
        return;
    }
    
    this.disabled = true;
    const originalText = this.textContent;
    this.textContent = 'Confirmando...';
    
    fetch(`/dashboard/pdv/orders/${currentOrderId}/confirm-payment-silent`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
        },
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert(data.message || 'Pagamento confirmado com sucesso!');
            // Recarregar informações do pedido
            document.getElementById('btn-search-order').click();
        } else {
            alert('Erro: ' + (data.message || 'Erro ao confirmar pagamento'));
            this.disabled = false;
            this.textContent = originalText;
        }
    })
    .catch(err => {
        console.error('Erro ao confirmar pagamento:', err);
        alert('Erro ao confirmar pagamento');
        this.disabled = false;
        this.textContent = originalText;
    });
});

// Permitir buscar ao pressionar Enter
document.getElementById('order-number-search')?.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        document.getElementById('btn-search-order').click();
    }
});

// Máscara e busca automática de CEP no formulário de novo cliente
const zipCodeInput = document.getElementById('new-customer-zip-code');
if (zipCodeInput) {
    let cepTimeout = null;
    
    // Função para buscar endereço via ViaCEP
    async function buscarEnderecoPorCep(cep) {
        const cepDigits = cep.replace(/\D/g, '');
        
        if (cepDigits.length !== 8) {
            return;
        }
        
        // Verificar se os campos de endereço já estão preenchidos
        const streetInput = document.getElementById('new-customer-street');
        const hasAddress = streetInput && streetInput.value.trim().length > 0;
        
        // Se já tem endereço, não buscar novamente
        if (hasAddress) {
            return;
        }
        
        // Mostrar feedback visual
        zipCodeInput.disabled = true;
        zipCodeInput.style.opacity = '0.6';
        
        try {
            const response = await fetch(`https://viacep.com.br/ws/${cepDigits}/json/`);
            const data = await response.json();
            
            if (data.erro) {
                // CEP não encontrado - não mostrar alerta, apenas deixar o usuário preencher manualmente
                console.log('CEP não encontrado:', cepDigits);
            } else {
                // Preencher campos automaticamente
                if (streetInput && data.logradouro) {
                    streetInput.value = data.logradouro;
                }
                const neighborhoodInput = document.getElementById('new-customer-neighborhood');
                if (neighborhoodInput && data.bairro) {
                    neighborhoodInput.value = data.bairro;
                }
                const cityInput = document.getElementById('new-customer-city');
                if (cityInput && data.localidade) {
                    cityInput.value = data.localidade;
                }
                const stateInput = document.getElementById('new-customer-state');
                if (stateInput && data.uf) {
                    stateInput.value = data.uf.toUpperCase();
                }
                
                // Feedback visual de sucesso
                zipCodeInput.style.borderColor = '#10b981';
                setTimeout(() => {
                    zipCodeInput.style.borderColor = '';
                }, 2000);
            }
        } catch (error) {
            console.error('Erro ao buscar CEP:', error);
        } finally {
            zipCodeInput.disabled = false;
            zipCodeInput.style.opacity = '1';
        }
    }
    
    // Aplicar máscara e buscar automaticamente quando CEP for completo
    zipCodeInput.addEventListener('input', function(e) {
        // Aplicar máscara
        let value = e.target.value.replace(/\D/g, '');
        if (value.length > 5) {
            value = value.substring(0, 5) + '-' + value.substring(5, 8);
        }
        e.target.value = value;
        
        // Limpar timeout anterior
        if (cepTimeout) {
            clearTimeout(cepTimeout);
        }
        
        // Buscar endereço após 800ms de inatividade (quando usuário parar de digitar)
        const cepDigits = value.replace(/\D/g, '');
        if (cepDigits.length === 8) {
            cepTimeout = setTimeout(() => {
                buscarEnderecoPorCep(value);
            }, 800);
        }
    });
    
    // Também buscar quando o campo perder o foco (blur)
    zipCodeInput.addEventListener('blur', function() {
        const cep = this.value.replace(/\D/g, '');
        if (cep.length === 8) {
            buscarEnderecoPorCep(this.value);
        }
    });
}

// Filtros de categoria
const categoryMap = {
    'bolos': ['bolo', 'cake'],
    'paes': ['pão', 'bread', 'integral', 'pães'],
    'doces': ['bolo', 'brownie', 'doce', 'cheesecake', 'biscoito', 'cookie', 'bombom'],
    'salgados': ['salgado', 'pão', 'foccacia', 'croissant', 'cheese'],
    'bebidas': ['bebida', 'café', 'coffee', 'chá', 'suco']
};

// Armazenar todos os produtos ao carregar a página
let allProductsData = [];
window.addEventListener('DOMContentLoaded', function() {
    // Capturar todos os produtos antes de qualquer filtro
    const allButtons = document.querySelectorAll('.product-quick-add');
    allProductsData = Array.from(allButtons).map(btn => ({
        id: btn.dataset.productId,
        name: btn.dataset.productName,
        price: parseFloat(btn.dataset.productPrice),
        has_variants: btn.dataset.hasVariants === 'true',
        variants: JSON.parse(btn.dataset.variants || '[]'),
        category: btn.dataset.category || ''
    }));
});

document.querySelectorAll('.category-filter').forEach(btn => {
    btn.addEventListener('click', function() {
        const category = this.dataset.category;
        
        // Atualizar estado do botão
        document.querySelectorAll('.category-filter').forEach(b => {
            b.classList.remove('bg-orange-500', 'text-white');
            b.classList.add('bg-muted', 'text-muted-foreground');
            b.classList.remove('active');
        });
        this.classList.remove('bg-muted', 'text-muted-foreground');
        this.classList.add('bg-orange-500', 'text-white', 'active');
        
        // Filtrar produtos
        let filteredProducts = [];
        
        if (category === 'all') {
            // Mostrar todos os produtos
            filteredProducts = [...allProductsData];
        } else {
            // Filtrar por categoria
            const keywords = categoryMap[category] || [];
            filteredProducts = allProductsData.filter(p => {
                const name = p.name.toLowerCase();
                return keywords.some(keyword => name.includes(keyword));
            });
        }
        
        // Mostrar produtos filtrados com paginação
        renderProducts(filteredProducts, 1);
        
        // Limpar busca
        document.getElementById('product-search').value = '';
        document.getElementById('product-results').classList.add('hidden');
    });
});

// Mostrar produtos automaticamente ao carregar a página
window.addEventListener('DOMContentLoaded', function() {
    const productsSection = document.getElementById('products-section');
    if (productsSection) {
        productsSection.classList.remove('hidden');
        productsSection.style.display = 'block';
    }
    // Inicializar ícones Lucide
    if (window.lucide) {
        window.lucide.createIcons();
    }
});
</script>
<style>
/* Responsividade Mobile */
@media (max-width: 1024px) {
    .dashboard-two-panel {
        flex-direction: column !important;
    }
    
    .dashboard-two-panel > div:last-child {
        width: 100% !important;
        max-height: none !important;
        position: relative !important;
        top: 0 !important;
        sticky: unset !important;
    }
    
    #products-grid {
        grid-template-columns: repeat(2, 1fr) !important;
    }
}

@media (max-width: 640px) {
    .dashboard-two-panel {
        gap: 2rem !important;
    }
    
    #products-grid {
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 0.5rem !important;
    }
    
    .category-filter {
        padding: 0.375rem 0.75rem !important;
        font-size: 0.75rem !important;
    }
    
    #summary-total {
        font-size: 1.125rem !important;
    }
    
    .product-quick-add {
        height: 7rem !important;
        padding: 0.625rem !important;
    }
}

</style>
@endpush
@endsection
