@extends('dashboard.layouts.app')

@section('page_title', 'PDV')
@section('page_subtitle', 'Ponto de Venda - Registre suas vendas')

{{-- CSS antigo removido - usando apenas Photo-Zen design system --}}

@section('content')
<div class="space-y-6">

    @if(session('success'))
    <div class="rounded-lg border bg-green-50 border-green-200 p-4 text-green-700">
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="rounded-lg border bg-red-50 border-red-200 p-4 text-red-700">
        {{ session('error') }}
    </div>
    @endif

    <!-- Cliente e Buscar Produto - Mesma linha -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
        <!-- Cliente -->
        <div>
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-semibold">Cliente</h3>
                <button type="button" id="btn-new-customer" class="inline-flex items-center gap-1 text-xs font-medium text-primary hover:underline">
                    <i data-lucide="user-plus" class="h-3.5 w-3.5"></i>
                    Novo
                </button>
            </div>
            <div class="relative">
                <i data-lucide="user" class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground"></i>
                <input type="text" id="customer-search" class="w-full pl-10 rounded-md border border-input bg-background text-sm h-10" placeholder="Buscar cliente por nome ou telefone..." autocomplete="off">
            </div>
            <div id="customer-results" class="hidden max-h-60 overflow-y-auto border rounded-md bg-background mt-2"></div>
            <input type="hidden" id="customer-id" name="customer_id" required>
            <div id="selected-customer" class="hidden p-3 rounded-md bg-muted mt-2">
                <div class="flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <p class="font-semibold truncate" id="selected-customer-name"></p>
                        <p class="text-sm truncate text-muted-foreground" id="selected-customer-info"></p>
                    </div>
                    <button type="button" id="btn-clear-customer" class="text-muted-foreground hover:text-foreground">
                        <i data-lucide="x" class="h-5 w-5"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Buscar Produto -->
        <div>
            <h3 class="text-sm font-semibold mb-2">Buscar Produto</h3>
            <div class="relative">
                <i data-lucide="search" class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground"></i>
                <input type="text" id="product-search" class="w-full pl-10 rounded-md border border-input bg-background text-sm h-10" placeholder="Digite o nome do produto..." autocomplete="off">
            </div>
            <div id="product-results" class="mt-2 hidden max-h-64 overflow-y-auto border border-gray-200 rounded-lg bg-white shadow-lg"></div>
        </div>
    </div>

    <!-- Filtros de Categorias -->
    <div class="flex flex-wrap gap-2 mb-4">
        <button type="button" data-category="all" class="category-filter inline-flex items-center justify-center gap-2 rounded-md border border-primary bg-primary text-primary-foreground text-sm font-medium px-4 h-10 whitespace-nowrap">
            <i data-lucide="tags" class="h-4 w-4"></i>
            Todos
        </button>
        @php
            $categoryFilters = $products->map(function($p){
                return $p->category->name ?? 'Sem categoria';
            })->unique()->values();
        @endphp
        @foreach($categoryFilters as $cat)
            <button type="button" data-category="{{ $cat }}" class="category-filter inline-flex items-center justify-center gap-2 rounded-md border border-border bg-white text-foreground text-sm font-medium px-4 h-10 whitespace-nowrap hover:bg-accent">
                <i data-lucide="tag" class="h-4 w-4"></i>
                {{ $cat }}
            </button>
        @endforeach
    </div>

    <!-- Seção Completa do PDV - Sempre visível -->
    <div id="pdv-full-interface">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 w-full min-w-0 max-w-full">
            <!-- Coluna Resumo - 1/3 da largura -->
            <div class="dashboard-aside flex flex-col gap-4 lg:col-span-1 min-w-0">
                <!-- Carrinho -->
                <div class="rounded-xl border bg-white shadow-sm">
                    <div class="flex items-center justify-between p-4 border-b">
                        <div class="flex items-center gap-2">
                            <i data-lucide="shopping-cart" class="h-4 w-4 text-muted-foreground"></i>
                            <h3 class="text-base font-semibold">Carrinho</h3>
                        </div>
                        <span class="inline-flex items-center justify-center rounded-full bg-primary/10 text-primary text-xs font-semibold px-2 py-0.5">
                            <span id="cart-items-count">0</span> itens
                        </span>
                    </div>
                    <div class="p-4">
                        <div id="pdv-items-list" class="space-y-2 max-h-72 overflow-y-auto pr-1">
                            <div class="flex flex-col items-center justify-center gap-2 py-8 text-center text-muted-foreground">
                                <i data-lucide="shopping-cart" class="h-12 w-12 text-muted-foreground/30"></i>
                                <div class="text-sm font-medium">Carrinho vazio</div>
                                <div class="text-xs">Clique nos produtos para adicionar</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botão Adicionar Entrega -->
                <button type="button" id="btn-toggle-delivery" class="w-full inline-flex items-center justify-center gap-2 rounded-lg border border-input bg-white hover:bg-accent hover:text-accent-foreground text-sm font-medium h-10">
                    <i data-lucide="truck" class="h-4 w-4"></i>
                    Adicionar Entrega
                </button>
                
                <!-- Seção de Entrega (oculta inicialmente) -->
                <div id="delivery-section" class="hidden rounded-xl border bg-white shadow-sm p-4 space-y-3">
                    <select id="delivery-type" class="hidden">
                        <option value="delivery" selected>Entrega</option>
                        <option value="pickup">Retirada</option>
                    </select>
                    <div>
                        <input type="text" id="delivery-address" class="w-full rounded-md border border-input bg-background text-sm h-10 px-3" placeholder="Endereço">
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <input type="text" id="destination-cep" class="w-full rounded-md border border-input bg-background text-sm h-10 px-3" placeholder="CEP" maxlength="10">
                        <input type="number" id="delivery-fee-input" step="0.01" min="0" value="0" class="w-full rounded-md border border-input bg-background text-sm h-10 px-3" placeholder="Taxa">
                    </div>
                    <button type="button" id="btn-calculate-fee" class="w-full inline-flex items-center justify-center rounded-md text-sm font-medium border border-input bg-white hover:bg-accent hover:text-accent-foreground h-10">
                        Calcular
                    </button>
                    <div id="delivery-fee-info" class="text-xs text-muted-foreground hidden"></div>
                </div>

                <!-- Total e Pagamento -->
                <div class="rounded-xl border bg-white shadow-sm lg:sticky lg:top-20">
                    <div class="p-4 space-y-4">
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between text-muted-foreground">
                                <span>Subtotal</span>
                                <span id="summary-subtotal">R$ 0,00</span>
                            </div>
                            <div class="flex justify-between text-sm text-green-600 hidden" id="discount-row">
                                <span>Desconto</span>
                                <span id="summary-discount">-R$ 0,00</span>
                            </div>
                            <div class="flex justify-between text-base font-semibold pt-2 border-t border-border">
                                <span>Total</span>
                                <span id="summary-total" class="text-primary">R$ 0,00</span>
                            </div>
                            <span id="summary-items-count" class="hidden">0</span>
                            <span id="summary-delivery-fee" class="hidden">R$ 0,00</span>
                        </div>

                        <div class="grid grid-cols-3 gap-2">
                            <button type="button" id="btn-payment-pix" class="inline-flex items-center justify-center gap-2 rounded-lg border border-blue-600 bg-blue-50 text-blue-700 text-sm font-medium h-10">
                                <i data-lucide="qr-code" class="h-4 w-4"></i>
                                PIX
                            </button>
                            <button type="button" id="btn-payment-card" class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm font-medium h-10">
                                <i data-lucide="credit-card" class="h-4 w-4"></i>
                                Cartão
                            </button>
                            <button type="button" id="btn-payment-cash" class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm font-medium h-10">
                                <i data-lucide="banknote" class="h-4 w-4"></i>
                                Dinheiro
                            </button>
                        </div>

                        <button type="button" id="btn-finalize-order" class="w-full inline-flex items-center justify-center gap-2 rounded-lg bg-primary text-primary-foreground text-sm font-medium h-10 disabled:opacity-70 disabled:cursor-not-allowed" disabled>
                            Finalizar Venda
                        </button>
                    </div>
                </div>

                <!-- Detalhes do Pedido -->
                <details class="rounded-xl border bg-white shadow-sm group">
                    <summary class="cursor-pointer select-none p-4 hover:bg-muted/30 transition-colors">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-base font-semibold leading-none tracking-tight">Detalhes do Pedido</h3>
                                <p class="text-xs text-muted-foreground mt-1">Pagamento e observações</p>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-down transition-transform group-open:rotate-180">
                                <path d="m6 9 6 6 6-6"></path>
                            </svg>
                        </div>
                    </summary>
                    <div class="p-4 pt-0 border-t space-y-4">
                        <input type="hidden" id="payment-method-select" value="pix">
                        <div class="rounded-lg border border-gray-200 bg-white p-3 space-y-3">
                            <h4 class="text-sm font-semibold">Custos e Descontos</h4>
                            <div class="flex flex-col gap-2 sm:flex-row">
                                <input type="text" id="coupon-code" class="flex-1 rounded-md border border-input bg-background text-sm h-10 px-3" placeholder="Código do cupom">
                                <button type="button" id="btn-apply-coupon" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium border border-input bg-white hover:bg-accent hover:text-accent-foreground px-3 h-10 sm:w-auto w-full">
                                    Aplicar
                                </button>
                            </div>
                            <div id="coupon-info" class="hidden p-2 bg-muted rounded-md text-sm"></div>
                            <div class="grid grid-cols-2 gap-2">
                                <input type="number" id="manual-discount-fixed" step="0.01" min="0" value="0" placeholder="Desconto (R$)" class="w-full rounded-md border border-input bg-background text-sm h-10 px-3">
                                <input type="number" id="manual-discount-percent" step="0.01" min="0" max="100" value="0" placeholder="Desconto (%)" class="w-full rounded-md border border-input bg-background text-sm h-10 px-3">
                            </div>
                        </div>
                        <div class="rounded-lg border border-gray-200 bg-white p-3 space-y-2">
                            <h4 class="text-sm font-semibold">Observações</h4>
                            <textarea id="order-notes" rows="3" class="w-full rounded-md border border-input bg-background text-sm" placeholder="Observações do pedido..."></textarea>
                        </div>

                        <div class="flex flex-col gap-2">
                            <button type="button" id="btn-send-order" class="w-full inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium bg-green-600 text-white hover:bg-green-700 h-10 px-4 py-2 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                                Enviar Pedido
                            </button>
                            <button type="button" id="btn-add-more-items" class="w-full inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2">
                                Adicionar Mais Itens
                            </button>
                        </div>
                    </div>
                </details>
        </div>

            <!-- Coluna Produtos - 2/3 da largura -->
            <div id="products-section" class="lg:col-span-2 min-w-0">
                <!-- Grid de Produtos - Estilo do Site -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3" id="products-grid">
                    @foreach($products as $product)
                        @php
                            $categoryName = $product->category->name ?? 'Sem categoria';
                            $activeVariants = $product->variants
                                ? $product->variants->where('is_active', true)->values()
                                : collect();
                            $variantsPayload = $activeVariants->map(function ($variant) {
                                return [
                                    'id' => $variant->id,
                                    'name' => $variant->name,
                                    'price' => (float)$variant->price,
                                ];
                            });
                        @endphp
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-3 hover:shadow-md transition-shadow cursor-pointer product-card-pdv product-quick-add relative"
                             data-product-id="{{ $product->id }}"
                             data-product-name="{{ $product->name }}"
                             data-product-price="{{ (float)($product->price ?? 0) }}"
                             data-category="{{ $categoryName }}"
                             data-has-variants="{{ $activeVariants->count() > 0 ? 'true' : 'false' }}"
                             data-variants='@json($variantsPayload)'>
                            @if($activeVariants->count() > 0)
                                <span class="absolute right-2 top-2 inline-flex items-center justify-center w-6 h-6 rounded-full bg-primary text-white text-xs font-semibold">
                                    +{{ $activeVariants->count() }}
                                </span>
                            @endif
                        <div class="flex flex-col items-center text-center gap-2">
                                <!-- Ícone Placeholder -->
                                <div class="w-16 h-16 rounded-xl bg-gray-100 flex items-center justify-center">
                                    <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                </div>
                                <!-- Tag de Categoria -->
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-gray-100 text-gray-600">
                                    {{ $categoryName }}
                                </span>
                                <!-- Nome do Produto -->
                                <h3 class="text-sm font-semibold text-gray-900">{{ $product->name }}</h3>
                            <p class="text-sm text-blue-600 font-semibold product-price-display">R$ {{ number_format((float)($product->price ?? 0), 2, ',', '.') }}</p>
                            @if($activeVariants->count() > 0)
                                <span class="text-xs text-orange-600">Escolher opção →</span>
                            @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Novo Cliente -->
<div id="new-customer-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/75">
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

<!-- Modal: Agendamento e Pagamento -->
<div id="finalize-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/75">
    <div class="bg-white rounded-lg shadow-2xl w-full max-w-md mx-4 relative">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-semibold">Agendar Entrega</h3>
                <button type="button" id="btn-close-finalize-modal" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x">
                        <path d="M18 6 6 18"></path>
                        <path d="M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-2">Data *</label>
                    <select id="scheduled_delivery_date" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
                        <option value="">Selecione uma data</option>
                        @foreach($availableDates ?? [] as $date)
                            <option value="{{ $date['date'] }}">{{ $date['day_name'] }}, {{ $date['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Horário *</label>
                    <select id="scheduled_delivery_slot" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm" disabled>
                        <option value="">Selecione primeiro uma data</option>
                    </select>
                </div>

            </div>

            <div class="flex gap-3 mt-6">
                <button type="button" id="btn-cancel-finalize" class="flex-1 inline-flex items-center justify-center rounded-md text-sm font-medium border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4">
                    Cancelar
                </button>
                <button type="button" id="btn-confirm-finalize" class="flex-1 inline-flex items-center justify-center rounded-md text-sm font-medium bg-blue-600 text-white hover:bg-blue-700 h-10 px-4">
                    Confirmar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Pagamento via PIX -->
<div id="pix-payment-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/75">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg mx-4 relative">
        <div class="p-6 space-y-5">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i data-lucide="qr-code" class="h-5 w-5 text-blue-600"></i>
                    <h3 class="text-lg font-semibold">Pagamento via PIX</h3>
                </div>
                <button type="button" id="btn-close-pix-payment-modal" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i data-lucide="x" class="h-5 w-5"></i>
                </button>
            </div>

            <div class="space-y-2 text-sm">
                <p class="text-muted-foreground">Resumo do Pedido</p>
                <div id="pix-summary-items" class="space-y-1"></div>
                <div class="border-t pt-2 space-y-1">
                    <div class="flex justify-between text-muted-foreground">
                        <span>Subtotal</span>
                        <span id="pix-subtotal">R$ 0,00</span>
                    </div>
                    <div class="flex justify-between text-muted-foreground">
                        <span>Entrega</span>
                        <span id="pix-delivery">R$ 0,00</span>
                    </div>
                    <div class="flex justify-between text-base font-semibold">
                        <span>Total</span>
                        <span id="pix-total" class="text-blue-600">R$ 0,00</span>
                    </div>
                </div>
            </div>

            <div class="rounded-lg bg-gray-50 border p-3 text-sm">
                <p class="font-medium text-gray-700">Endereço de Entrega</p>
                <p id="pix-address" class="text-gray-600"></p>
                <p id="pix-cep" class="text-gray-500"></p>
            </div>

            <div class="space-y-2">
                <p class="text-sm font-semibold">Como deseja processar o PIX?</p>
                <label class="flex items-center justify-between gap-3 p-3 border rounded-lg cursor-pointer hover:bg-accent">
                    <div class="flex items-center gap-3">
                        <input type="radio" name="pix_option" value="display_qr" class="h-4 w-4 text-blue-600">
                        <div>
                            <p class="text-sm font-medium">Gerar QR Code em Tela</p>
                            <p class="text-xs text-muted-foreground">Cliente escaneia o QR Code na tela</p>
                        </div>
                    </div>
                </label>
                <label class="flex items-center justify-between gap-3 p-3 border rounded-lg cursor-pointer hover:bg-accent">
                    <div class="flex items-center gap-3">
                        <input type="radio" name="pix_option" value="send_whatsapp" class="h-4 w-4 text-blue-600">
                        <div>
                            <p class="text-sm font-medium">Enviar Cobrança PIX</p>
                            <p class="text-xs text-muted-foreground">Envia cobrança via WhatsApp</p>
                        </div>
                    </div>
                </label>
            </div>

            <div class="flex gap-3">
                <button type="button" id="btn-cancel-pix-payment" class="flex-1 inline-flex items-center justify-center rounded-md text-sm font-medium border border-input bg-white hover:bg-accent h-10 px-4">
                    Cancelar
                </button>
                <button type="button" id="btn-confirm-pix-payment" class="flex-1 inline-flex items-center justify-center rounded-md text-sm font-medium bg-blue-600 text-white hover:bg-blue-700 h-10 px-4">
                    Gerar QR Code
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Link de Pagamento -->
<div id="link-payment-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/75">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg mx-4 relative">
        <div class="p-6 space-y-5">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i data-lucide="link" class="h-5 w-5 text-blue-600"></i>
                    <h3 class="text-lg font-semibold">Link de Pagamento</h3>
                </div>
                <button type="button" id="btn-close-link-payment-modal" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i data-lucide="x" class="h-5 w-5"></i>
                </button>
            </div>

            <div class="space-y-2 text-sm">
                <p class="text-muted-foreground">Resumo do Pedido</p>
                <div id="link-summary-items" class="space-y-1"></div>
                <div class="border-t pt-2 space-y-1">
                    <div class="flex justify-between text-muted-foreground">
                        <span>Subtotal</span>
                        <span id="link-subtotal">R$ 0,00</span>
                    </div>
                    <div class="flex justify-between text-base font-semibold">
                        <span>Total</span>
                        <span id="link-total" class="text-blue-600">R$ 0,00</span>
                    </div>
                </div>
            </div>

            <div class="rounded-lg bg-blue-50 border border-blue-100 p-3 text-sm">
                <p class="font-medium text-blue-700">Link será enviado ao cliente</p>
                <p class="text-xs text-blue-600">O link de pagamento será enviado automaticamente via WhatsApp.</p>
            </div>

            <div class="flex gap-3">
                <button type="button" id="btn-cancel-link-payment" class="flex-1 inline-flex items-center justify-center rounded-md text-sm font-medium border border-input bg-white hover:bg-accent h-10 px-4">
                    Cancelar
                </button>
                <button type="button" id="btn-confirm-link-payment" class="flex-1 inline-flex items-center justify-center rounded-md text-sm font-medium bg-blue-600 text-white hover:bg-blue-700 h-10 px-4">
                    Enviar Cobrança
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Exibir QR Code PIX -->
<div id="pix-qr-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/75">
    <div class="bg-white rounded-lg shadow-2xl w-full max-w-md mx-4 relative">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-semibold">Pagamento PIX</h3>
                <button type="button" id="btn-close-pix-qr-modal" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x">
                        <path d="M18 6 6 18"></path>
                        <path d="M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <div id="pix-qr-content" class="space-y-4">
                <!-- QR Code será inserido aqui -->
                <div class="text-center">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary mb-4"></div>
                    <p class="text-sm text-muted-foreground">Gerando QR Code...</p>
                </div>
            </div>
            
            <div class="mt-6 flex gap-3">
                <button type="button" id="btn-close-pix-qr" class="flex-1 inline-flex items-center justify-center rounded-md text-sm font-medium border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4">
                    Fechar
                </button>
            </div>
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
    deliveryFee: 0,
    notes: '',
    scheduled_delivery_date: '',
    scheduled_delivery_slot: '',
};

// Carregar slots disponíveis ao iniciar
window.availableDates = [];
// Funções de busca de cliente
let customerSearchTimeout;
document.getElementById('customer-search')?.addEventListener('input', function(e) {
    clearTimeout(customerSearchTimeout);
    const query = e.target.value.trim();
    
    const resultsEl = document.getElementById('customer-results');
    if (query.length < 2) {
        resultsEl.classList.add('hidden');
        resultsEl.innerHTML = '';
        return;
    }
    
    customerSearchTimeout = setTimeout(() => {
        fetch(`{{ route('api.pdv.customers.search') }}?q=${encodeURIComponent(query)}`)
            .then(res => res.json())
            .then(data => {
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
                                data-customer-wholesale="${Number(c.is_wholesale) === 1 ? '1' : '0'}">
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
});

document.getElementById('customer-search')?.addEventListener('blur', function() {
    const resultsEl = document.getElementById('customer-results');
    setTimeout(() => {
        resultsEl.classList.add('hidden');
    }, 150);
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
        document.getElementById('selected-customer-name').textContent = customerName;
        let info = [customerPhone, customerEmail].filter(Boolean).join(' • ') || 'Sem informações de contato';
        if (isWholesale) {
            info += ' • 🔷 Revenda';
        }
        document.getElementById('selected-customer-info').textContent = info;
        document.getElementById('selected-customer').classList.remove('hidden');
        const resultsEl = document.getElementById('customer-results');
        resultsEl.classList.add('hidden');
        resultsEl.innerHTML = '';
        document.getElementById('customer-search').value = '';

        const deliveryAddress = document.getElementById('delivery-address');
        if (deliveryAddress) {
            deliveryAddress.value = customerAddress || '';
        }
        
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
            fetch(`{{ route('api.pdv.customers.search') }}?q=${encodeURIComponent(customerName)}`)
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
        fetch('{{ route("api.pdv.calculateDeliveryFee") }}', {
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

// Limpar cliente
document.getElementById('btn-clear-customer')?.addEventListener('click', function() {
    pdvState.customer = null;
    document.getElementById('customer-id').value = '';
    document.getElementById('selected-customer').classList.add('hidden');
    // Campos de frete permanecem visíveis no topo
    resetProductPricesToNormal(); // Resetar preços quando cliente é removido
    updateFinalizeButton();
});

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
        fetch(`{{ route('api.pdv.products.search') }}?q=${encodeURIComponent(productId)}&customer_id=${customerId}&product_id=${productId}`)
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
        fetch(`{{ route('api.pdv.products.search') }}?q=${encodeURIComponent(productName)}&product_id=${productId}`)
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

// Buscar produtos
let productSearchTimeout;
document.getElementById('product-search')?.addEventListener('input', function(e) {
    clearTimeout(productSearchTimeout);
    const query = e.target.value.trim();
    
    if (query.length < 2) {
        document.getElementById('product-results').classList.add('hidden');
        return;
    }
    
    productSearchTimeout = setTimeout(() => {
        const customerId = pdvState.customer?.id || '';
        const url = `{{ route('api.pdv.products.search') }}?q=${encodeURIComponent(query)}${customerId ? '&customer_id=' + customerId : ''}`;
        fetch(url)
            .then(res => res.json())
            .then(data => {
                const resultsEl = document.getElementById('product-results');
                if (data.products && data.products.length > 0) {
                    resultsEl.innerHTML = data.products.map(p => {
                        const hasVariants = p.has_variants && p.variants && p.variants.length > 0;
                        const displayPrice = hasVariants ? (p.variants[0]?.price || p.price) : p.price;
                        const category = p.category?.name || p.category_name || 'Produto';
                        return `
                            <button type="button" class="product-option w-full text-left p-2 hover:bg-accent cursor-pointer" 
                                    data-product-id="${p.id}" 
                                    data-product-name="${p.name}"
                                    data-product-price="${displayPrice}"
                                    data-category="${category}"
                                    data-has-variants="${hasVariants ? 'true' : 'false'}"
                                    data-variants='${JSON.stringify(p.variants || [])}'>
                                <p class="font-medium">${p.name}</p>
                                ${hasVariants 
                                    ? `<p class="text-xs text-muted-foreground">A partir de R$ ${parseFloat(displayPrice).toFixed(2).replace('.', ',')}</p>
                                       <p class="text-xs text-blue-600">Escolher opção</p>`
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

// Filtros de categoria
document.querySelectorAll('.category-filter').forEach(btn => {
    btn.addEventListener('click', function() {
        const selected = this.dataset.category;
        document.querySelectorAll('.category-filter').forEach(b => {
            b.classList.remove('border-primary', 'bg-primary/10', 'text-primary');
            b.classList.add('border-border', 'bg-white', 'text-foreground');
        });
        this.classList.add('border-primary', 'bg-primary/10', 'text-primary');
        this.classList.remove('border-border', 'bg-white', 'text-foreground');

        document.querySelectorAll('.product-quick-add').forEach(card => {
            const cardCategory = card.dataset.category || '';
            if (selected === 'all' || cardCategory === selected) {
                card.classList.remove('hidden');
            } else {
                card.classList.add('hidden');
            }
        });
    });
});

// Modal de seleção de quantidade
function showQuantityModal(productName, productPrice, productCategory, callback) {
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 z-50 flex items-center justify-center';
    modal.style.backgroundColor = 'rgba(0, 0, 0, 0.6)';
    modal.id = 'quantity-modal';
    
    let quantity = 1;
    
    const updateTotal = () => {
        const totalEl = modal.querySelector('#quantity-total');
        if (totalEl) {
            totalEl.textContent = `R$ ${(quantity * productPrice).toFixed(2).replace('.', ',')}`;
        }
    };
    
    modal.innerHTML = `
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg mx-4 relative">
            <div class="p-6 space-y-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-lg bg-blue-50 flex items-center justify-center">
                        <i data-lucide="shopping-cart" class="h-5 w-5 text-blue-600"></i>
                    </div>
                    <div>
                        <p class="text-xs text-muted-foreground">${productCategory}</p>
                        <h3 class="text-lg font-semibold">${productName}</h3>
                        <p class="text-blue-600 font-semibold">R$ ${productPrice.toFixed(2).replace('.', ',')}</p>
                    </div>
                </div>

                <div class="space-y-2">
                    <p class="text-sm font-semibold">Quantidade</p>
                    <div class="flex items-center gap-3">
                        <button type="button" id="btn-decrease-qty" class="w-10 h-10 rounded-full border border-gray-300 text-gray-600">-</button>
                        <span id="quantity-display" class="text-lg font-semibold w-6 text-center">${quantity}</span>
                        <button type="button" id="btn-increase-qty" class="w-10 h-10 rounded-full border border-gray-300 text-gray-600">+</button>
                    </div>
                </div>

                <div class="space-y-2">
                    <p class="text-sm font-semibold">Observação (opcional)</p>
                    <textarea id="item-notes" rows="3" class="w-full rounded-md border border-input bg-background text-sm" placeholder="Ex: Sem açúcar, bem assado, cortar em fatias..."></textarea>
                </div>

                <div class="flex items-center justify-between rounded-lg border bg-blue-50 px-4 py-3">
                    <span class="text-sm text-blue-700">Total do item</span>
                    <span id="quantity-total" class="text-blue-700 font-semibold">R$ ${(quantity * productPrice).toFixed(2).replace('.', ',')}</span>
                </div>

                <div class="flex gap-3">
                    <button type="button" id="btn-cancel-quantity" class="flex-1 px-4 py-2 rounded-md border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 font-medium">
                        Cancelar
                    </button>
                    <button type="button" id="btn-add-quantity" class="flex-1 px-4 py-2 rounded-md bg-blue-600 hover:bg-blue-700 text-white font-medium">
                        Adicionar
                    </button>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    if (window.lucide) {
        window.lucide.createIcons();
    }
    
    const quantityDisplay = modal.querySelector('#quantity-display');
    const btnDecrease = modal.querySelector('#btn-decrease-qty');
    const btnIncrease = modal.querySelector('#btn-increase-qty');
    const btnCancel = modal.querySelector('#btn-cancel-quantity');
    const btnAdd = modal.querySelector('#btn-add-quantity');
    
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
        const notes = modal.querySelector('#item-notes')?.value?.trim() || '';
        callback(quantity, notes);
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

// Modal de seleção de variante
function showVariantModal(productId, productName, productCategory, variants) {
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 z-50 flex items-center justify-center';
    modal.style.backgroundColor = 'rgba(0, 0, 0, 0.6)';
    modal.id = 'variant-modal';

    const first = variants[0];
    const initialPrice = parseFloat(first?.price || 0);

    modal.innerHTML = `
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg mx-4 relative">
            <div class="p-6 space-y-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-lg bg-blue-50 flex items-center justify-center">
                        <i data-lucide="shopping-cart" class="h-5 w-5 text-blue-600"></i>
                    </div>
                    <div>
                        <p class="text-xs text-muted-foreground">${productCategory}</p>
                        <h3 class="text-lg font-semibold">${productName}</h3>
                        <p class="text-blue-600 font-semibold">R$ <span id="variant-price">${initialPrice.toFixed(2).replace('.', ',')}</span></p>
                    </div>
                </div>

                <div class="space-y-2">
                    <p class="text-sm font-semibold">Escolha a variação</p>
                    <div class="space-y-2">
                        ${variants.map((v, index) => `
                            <label class="flex items-center justify-between gap-3 p-3 border rounded-lg cursor-pointer hover:bg-accent">
                                <div class="flex items-center gap-3">
                                    <input type="radio" name="variant_option" value="${v.id}" data-name="${v.name}" data-price="${v.price}" ${index === 0 ? 'checked' : ''}>
                                    <span class="text-sm font-medium">${v.name}</span>
                                </div>
                                <span class="text-sm font-semibold">R$ ${parseFloat(v.price).toFixed(2).replace('.', ',')}</span>
                            </label>
                        `).join('')}
                    </div>
                </div>

                <div class="space-y-2">
                    <p class="text-sm font-semibold">Quantidade</p>
                    <div class="flex items-center gap-3">
                        <button type="button" id="variant-dec" class="w-10 h-10 rounded-full border border-gray-300 text-gray-600">-</button>
                        <span id="variant-qty" class="text-lg font-semibold w-6 text-center">1</span>
                        <button type="button" id="variant-inc" class="w-10 h-10 rounded-full border border-gray-300 text-gray-600">+</button>
                    </div>
                </div>

                <div class="space-y-2">
                    <p class="text-sm font-semibold">Observação (opcional)</p>
                    <textarea id="variant-notes" rows="3" class="w-full rounded-md border border-input bg-background text-sm" placeholder="Ex: Sem açúcar, bem assado..."></textarea>
                </div>

                <div class="flex items-center justify-between rounded-lg border bg-blue-50 px-4 py-3">
                    <span class="text-sm text-blue-700">Total do item</span>
                    <span id="variant-total" class="text-blue-700 font-semibold">R$ ${initialPrice.toFixed(2).replace('.', ',')}</span>
                </div>

                <div class="flex gap-3">
                    <button type="button" id="variant-cancel" class="flex-1 px-4 py-2 rounded-md border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 font-medium">Cancelar</button>
                    <button type="button" id="variant-add" class="flex-1 px-4 py-2 rounded-md bg-blue-600 hover:bg-blue-700 text-white font-medium">Adicionar</button>
                </div>
            </div>
        </div>
    `;

    document.body.appendChild(modal);
    if (window.lucide) {
        window.lucide.createIcons();
    }

    let qty = 1;
    const updateTotals = () => {
        const selected = modal.querySelector('input[name="variant_option"]:checked');
        const price = parseFloat(selected?.dataset.price || 0);
        modal.querySelector('#variant-price').textContent = price.toFixed(2).replace('.', ',');
        modal.querySelector('#variant-total').textContent = `R$ ${(price * qty).toFixed(2).replace('.', ',')}`;
    };

    modal.querySelectorAll('input[name="variant_option"]').forEach(radio => {
        radio.addEventListener('change', updateTotals);
    });
    modal.querySelector('#variant-dec')?.addEventListener('click', () => {
        qty = Math.max(1, qty - 1);
        modal.querySelector('#variant-qty').textContent = qty;
        updateTotals();
    });
    modal.querySelector('#variant-inc')?.addEventListener('click', () => {
        qty += 1;
        modal.querySelector('#variant-qty').textContent = qty;
        updateTotals();
    });

    modal.querySelector('#variant-add')?.addEventListener('click', () => {
        const selected = modal.querySelector('input[name="variant_option"]:checked');
        const variantId = selected?.value;
        const variantName = selected?.dataset.name || '';
        const variantPrice = parseFloat(selected?.dataset.price || 0);
        const notes = modal.querySelector('#variant-notes')?.value?.trim() || '';

        addItem({
            product_id: productId,
            variant_id: variantId,
            name: `${productName} - ${variantName}`,
            price: variantPrice,
            quantity: qty,
            special_instructions: notes,
        });

        document.body.removeChild(modal);
        document.getElementById('product-results')?.classList.add('hidden');
        document.getElementById('product-search').value = '';
    });

    modal.querySelector('#variant-cancel')?.addEventListener('click', () => {
        document.body.removeChild(modal);
    });

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
        const productCategory = btn.dataset.category || btn.getAttribute('data-category') || 'Produto';
        const hasVariants = btn.dataset.hasVariants === 'true';
        const variantsJson = btn.dataset.variants || '[]';
        
        // Se tem variantes, mostrar modal de seleção
        if (hasVariants) {
            try {
                const variants = JSON.parse(variantsJson);
                if (variants && variants.length > 0) {
                    showVariantModal(productId, productName, productCategory, variants);
                    return;
                }
            } catch(err) {
                console.error('Erro ao parsear variantes:', err);
            }
        }
        
        // Sem variantes, mostrar modal de quantidade
        showQuantityModal(productName, productPrice, productCategory, (qty, notes) => {
            addItem({
                product_id: productId,
                variant_id: null,
                name: productName,
                price: productPrice,
                quantity: qty,
                special_instructions: notes,
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
        const notes = String(item.special_instructions || '').trim();
        
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
            i.special_instructions === notes &&
            Math.abs(i.price - price) < 0.01 // Comparação de float com tolerância
        );
        
        if (existingItem) {
            existingItem.quantity += quantity;
        } else {
            pdvState.items.push({
                product_id: productId,
                variant_id: variantId,
                name: String(item.name).trim(),
                price: price,
                quantity: quantity,
                special_instructions: notes,
            });
        }
        
        renderItems();
        updateSummary();
        updateFinalizeButtons();
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

// Atualizar quantidade
function updateQuantity(index, delta) {
    pdvState.items[index].quantity = Math.max(1, pdvState.items[index].quantity + delta);
    renderItems();
    updateSummary();
    updateFinalizeButton();
}

// Renderizar itens
function renderItems() {
    const itemsEl = document.getElementById('pdv-items-list');
    
    if (pdvState.items.length === 0) {
        itemsEl.innerHTML = `
            <div class="flex flex-col items-center justify-center gap-3 py-8 text-center text-muted-foreground">
                <span class="flex h-12 w-12 items-center justify-center rounded-full bg-muted overflow-hidden">
                    <i data-lucide="shopping-cart" class="h-5 w-5"></i>
                </span>
                <div>
                    <p class="font-semibold text-foreground text-sm">Nenhum produto adicionado</p>
                </div>
            </div>
        `;
        if (window.lucide) {
            window.lucide.createIcons();
        }
        return;
    }
    
    itemsEl.innerHTML = pdvState.items.map((item, index) => `
        <div class="flex items-center justify-between p-2 border rounded-md">
            <div class="flex-1">
                <p class="font-medium text-sm">${item.name}</p>
                <p class="text-xs text-muted-foreground">R$ ${item.price.toFixed(2).replace('.', ',')} x ${item.quantity}</p>
                ${item.special_instructions ? `<p class="text-xs text-gray-500 mt-1">Obs: ${item.special_instructions}</p>` : ''}
            </div>
            <div class="flex items-center gap-2">
                <button type="button" class="btn-dec-qty p-1 hover:bg-accent rounded" data-index="${index}">-</button>
                <span class="text-sm w-8 text-center">${item.quantity}</span>
                <button type="button" class="btn-inc-qty p-1 hover:bg-accent rounded" data-index="${index}">+</button>
                <button type="button" class="btn-remove-item text-red-600 p-1 hover:bg-red-50 rounded ml-2" data-index="${index}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trash-2">
                        <path d="M3 6h18"></path>
                        <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path>
                    </svg>
                </button>
            </div>
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
}

// Atualizar resumo
function updateSummary() {
    const itemsCount = pdvState.items.reduce((sum, item) => sum + item.quantity, 0);
    const subtotal = pdvState.items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const deliveryFee = parseFloat(document.getElementById('delivery-fee-input').value) || 0;
    
    // Desconto do cupom
    const couponDiscount = pdvState.coupon ? (pdvState.coupon.discount || 0) : 0;
    
    // Desconto manual (fixo e porcentagem)
    const manualDiscountFixed = parseFloat(document.getElementById('manual-discount-fixed').value) || 0;
    const manualDiscountPercent = parseFloat(document.getElementById('manual-discount-percent').value) || 0;
    const manualDiscountFromPercent = subtotal * (manualDiscountPercent / 100);
    
    // Total de desconto (cupom + manual fixo + manual porcentagem)
    const totalDiscount = couponDiscount + manualDiscountFixed + manualDiscountFromPercent;
    
    // Calcular total final
    const total = Math.max(0, subtotal + deliveryFee - totalDiscount);
    
    const itemsCountEl = document.getElementById('summary-items-count');
    if (itemsCountEl) {
        itemsCountEl.textContent = itemsCount;
    }
    const cartCountEl = document.getElementById('cart-items-count');
    if (cartCountEl) {
        cartCountEl.textContent = itemsCount;
    }
    
    // Atualizar botões de pagamento
    updatePaymentButtons();
    const summarySubtotalEl = document.getElementById('summary-subtotal');
    if (summarySubtotalEl) {
        summarySubtotalEl.textContent = 'R$ ' + subtotal.toFixed(2).replace('.', ',');
    }
    
    const summaryDeliveryEl = document.getElementById('summary-delivery-fee');
    if (summaryDeliveryEl) {
        summaryDeliveryEl.textContent = 'R$ ' + deliveryFee.toFixed(2).replace('.', ',');
    }
    
    const summaryTotalEl = document.getElementById('summary-total');
    if (summaryTotalEl) {
        summaryTotalEl.textContent = 'R$ ' + total.toFixed(2).replace('.', ',');
    }
    
    if (totalDiscount > 0) {
        document.getElementById('discount-row').classList.remove('hidden');
        document.getElementById('summary-discount').textContent = '-R$ ' + totalDiscount.toFixed(2).replace('.', ',');
    } else {
        document.getElementById('discount-row').classList.add('hidden');
    }
}

// Atualizar taxa de entrega
document.getElementById('delivery-fee-input')?.addEventListener('input', updateSummary);

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
    
    fetch('{{ route("api.pdv.calculateDeliveryFee") }}', {
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
            const response = await fetch(`https://viacep.com.br/ws/${cepDigits}/json/`);
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
    
    fetch('{{ route("api.pdv.coupons.validate") }}', {
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

// Removido - substituído pelo novo código abaixo

document.getElementById('delivery-address')?.addEventListener('input', function(e) {
    if (pdvState.customer) {
        pdvState.customer.address = e.target.value;
    }
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
    updateFinalizeButtons();
    
    // Atualizar resumo inicial
    updateSummary();
    
    // Carregar slots de entrega disponíveis
    loadDeliverySlots();
    
    // Inicializar ícones Lucide
    if (window.lucide) {
        window.lucide.createIcons();
    }
});

// Atualizar estado dos botões
function updateFinalizeButtons() {
    const hasCustomer = pdvState.customer !== null;
    const hasItems = pdvState.items.length > 0;
    const enabled = hasCustomer && hasItems;
    
    document.getElementById('btn-finalize-order').disabled = !enabled;
    document.getElementById('btn-send-order').disabled = !enabled;
    // Botões removidos de migração
}

// Enviar pedido (abre fluxo de agendamento + pagamento)
document.getElementById('btn-send-order')?.addEventListener('click', function() {
    if (!pdvState.customer || pdvState.items.length === 0) {
        alert('Preencha cliente e adicione itens ao pedido');
        return;
    }
    document.getElementById('btn-finalize-order')?.click();
});

// Carregar slots de entrega disponíveis
async function loadDeliverySlots() {
    try {
        console.log('🔍 Carregando slots de entrega para PDV...');
        const response = await fetch('/dashboard/orders/delivery-slots', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });
        
        if (!response.ok) {
            const errorText = await response.text();
            console.error('❌ Erro ao carregar slots:', errorText);
            throw new Error(`HTTP ${response.status}: ${errorText}`);
        }
        
        const data = await response.json();
        console.log('✅ Slots carregados:', data);
        
        if (data.success && data.slots) {
            window.availableDates = data.slots;
            populateDateOptions();
        } else {
            console.warn('⚠️ Nenhum slot disponível ou erro na resposta');
            window.availableDates = [];
        }
    } catch (error) {
        console.error('❌ Erro ao carregar slots de entrega:', error);
        window.availableDates = [];
        alert('Erro ao carregar horários de entrega disponíveis. Tente novamente.');
    }
}

// Preencher opções de data
function populateDateOptions() {
    const dateSelect = document.getElementById('scheduled_delivery_date');
    if (!dateSelect) return;
    
    dateSelect.innerHTML = '<option value="">Selecione uma data</option>';
    
    if (!window.availableDates || window.availableDates.length === 0) {
        return;
    }
    
    window.availableDates.forEach(dateObj => {
        const option = document.createElement('option');
        option.value = dateObj.date;
        option.textContent = `${dateObj.day_name}, ${dateObj.label}`;
        option.dataset.slots = JSON.stringify(dateObj.slots || []);
        dateSelect.appendChild(option);
    });
}

// Atualizar slots de agendamento
function updateScheduleSlots() {
    const dateSelect = document.getElementById('scheduled_delivery_date');
    const slotSelect = document.getElementById('scheduled_delivery_slot');
    if (!dateSelect || !slotSelect) return;

    const selectedDate = dateSelect.value;
    const selectedOption = dateSelect.options[dateSelect.selectedIndex];
    
    if (!selectedOption || !selectedOption.dataset.slots) {
        slotSelect.innerHTML = '<option value="">Selecione primeiro uma data</option>';
        slotSelect.disabled = true;
        return;
    }

    const slots = JSON.parse(selectedOption.dataset.slots);
    
    if (!slots || slots.length === 0) {
        slotSelect.innerHTML = '<option value="">Nenhum horário disponível</option>';
        slotSelect.disabled = true;
        return;
    }

    slotSelect.innerHTML = '<option value="">Selecione um horário</option>';
    slots.forEach(slot => {
        if (slot.available > 0) {
            const option = document.createElement('option');
            option.value = slot.value;
            option.textContent = `${slot.label} (${slot.available} vagas)`;
            slotSelect.appendChild(option);
        }
    });
    
    slotSelect.disabled = slotSelect.options.length <= 1; // Desabilita se só tem a opção "Selecione"
}

document.getElementById('scheduled_delivery_date')?.addEventListener('change', updateScheduleSlots);

// Finalizar pedido (abre agendamento)
document.getElementById('btn-finalize-order')?.addEventListener('click', async function() {
    // Garantir que os slots estejam carregados
    if (!window.availableDates || window.availableDates.length === 0) {
        await loadDeliverySlots();
    }
    
    const dateSelect = document.getElementById('scheduled_delivery_date');
    if (dateSelect && !dateSelect.value && window.availableDates && window.availableDates.length > 0) {
        dateSelect.value = window.availableDates[0].date;
        updateScheduleSlots();
    }
    document.getElementById('finalize-modal').classList.remove('hidden');
});

// Atualizar botões de pagamento
document.getElementById('payment-method-select')?.addEventListener('change', function() {
    updatePaymentButtons();
});

function updatePaymentButtons() {
    const paymentSelect = document.getElementById('payment-method-select');
    const btnPix = document.getElementById('btn-payment-pix');
    const btnLink = document.getElementById('btn-payment-link');
    if (!paymentSelect || !btnPix || !btnLink) {
        return;
    }

    const method = paymentSelect.value;
    const activeClasses = ['border-blue-600', 'bg-blue-50', 'text-blue-700'];
    const inactiveClasses = ['border-gray-300', 'bg-white', 'text-gray-700'];

    if (method === 'pix') {
        btnPix.classList.remove(...inactiveClasses);
        btnPix.classList.add(...activeClasses);
        btnLink.classList.remove(...activeClasses);
        btnLink.classList.add(...inactiveClasses);
    } else {
        btnLink.classList.remove(...inactiveClasses);
        btnLink.classList.add(...activeClasses);
        btnPix.classList.remove(...activeClasses);
        btnPix.classList.add(...inactiveClasses);
    }
    
    // Habilitar botões se houver itens no carrinho
    const hasItems = pdvState.items && pdvState.items.length > 0;
    if (btnPix) btnPix.disabled = !hasItems;
    if (btnLink) btnLink.disabled = !hasItems;
}

function setPaymentMethod(method) {
    const paymentSelect = document.getElementById('payment-method-select');
    if (paymentSelect) {
        paymentSelect.value = method;
    }
    updatePaymentButtons();
}

document.getElementById('btn-payment-pix')?.addEventListener('click', function () {
    setPaymentMethod('pix');
});

document.getElementById('btn-payment-link')?.addEventListener('click', function () {
    setPaymentMethod('credit_card');
});

// Inicializar seleção de pagamento no carregamento
updatePaymentButtons();

// Toggle seção de entrega
document.getElementById('btn-toggle-delivery')?.addEventListener('click', function() {
    const deliverySection = document.getElementById('delivery-section');
    if (deliverySection) {
        deliverySection.classList.toggle('hidden');
        if (deliverySection.classList.contains('hidden')) {
            this.innerHTML = '<i data-lucide="truck" class="h-4 w-4"></i> Adicionar Entrega';
        } else {
            this.innerHTML = '<i data-lucide="truck" class="h-4 w-4"></i> Ocultar Entrega';
        }
        if (window.lucide) {
            window.lucide.createIcons();
        }
    }
});

function calculateOrderTotals() {
    const subtotal = pdvState.items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const deliveryFee = parseFloat(document.getElementById('delivery-fee-input').value) || 0;
    const couponDiscount = pdvState.coupon ? (pdvState.coupon.discount || 0) : 0;
    const manualDiscountFixed = parseFloat(document.getElementById('manual-discount-fixed').value) || 0;
    const manualDiscountPercent = parseFloat(document.getElementById('manual-discount-percent').value) || 0;
    const manualDiscountFromPercent = subtotal * (manualDiscountPercent / 100);
    const totalDiscount = couponDiscount + manualDiscountFixed + manualDiscountFromPercent;
    const total = Math.max(0, subtotal + deliveryFee - totalDiscount);
    return { subtotal, deliveryFee, total, totalDiscount };
}

function fillPaymentSummary(prefix) {
    const { subtotal, deliveryFee, total } = calculateOrderTotals();
    const listEl = document.getElementById(`${prefix}-summary-items`);
    if (listEl) {
        listEl.innerHTML = pdvState.items.map(item => `
            <div class="flex justify-between text-sm">
                <span>${item.quantity}x ${item.name}</span>
                <span>R$ ${(item.price * item.quantity).toFixed(2).replace('.', ',')}</span>
            </div>
        `).join('');
    }
    const subtotalEl = document.getElementById(`${prefix}-subtotal`);
    if (subtotalEl) subtotalEl.textContent = `R$ ${subtotal.toFixed(2).replace('.', ',')}`;
    const totalEl = document.getElementById(`${prefix}-total`);
    if (totalEl) totalEl.textContent = `R$ ${total.toFixed(2).replace('.', ',')}`;
    const deliveryEl = document.getElementById(`${prefix}-delivery`);
    if (deliveryEl) deliveryEl.textContent = `R$ ${deliveryFee.toFixed(2).replace('.', ',')}`;

    if (prefix === 'pix') {
        const address = document.getElementById('delivery-address')?.value || pdvState.customer?.address || '';
        const cep = document.getElementById('destination-cep')?.value || pdvState.customer?.zip_code || '';
        const addressEl = document.getElementById('pix-address');
        const cepEl = document.getElementById('pix-cep');
        if (addressEl) addressEl.textContent = address || 'Endereço não informado';
        if (cepEl) cepEl.textContent = cep ? `CEP: ${cep}` : '';
    }
}

function openPixPaymentModal() {
    fillPaymentSummary('pix');
    const radio = document.querySelector('input[name="pix_option"][value="display_qr"]');
    if (radio) {
        radio.checked = true;
    }
    const btn = document.getElementById('btn-confirm-pix-payment');
    if (btn) {
        btn.textContent = 'Gerar QR Code';
    }
    document.getElementById('pix-payment-modal')?.classList.remove('hidden');
}

function openLinkPaymentModal() {
    fillPaymentSummary('link');
    document.getElementById('link-payment-modal')?.classList.remove('hidden');
}

// Confirmar agendamento
document.getElementById('btn-confirm-finalize')?.addEventListener('click', function() {
    const scheduledDate = document.getElementById('scheduled_delivery_date')?.value || '';
    const scheduledSlot = document.getElementById('scheduled_delivery_slot')?.value || '';
    if (!scheduledDate || !scheduledSlot) {
        alert('Selecione a data e o horário de entrega.');
        return;
    }
    pdvState.scheduled_delivery_date = scheduledDate;
    pdvState.scheduled_delivery_slot = scheduledSlot;
    document.getElementById('finalize-modal')?.classList.add('hidden');

    const paymentMethod = document.getElementById('payment-method-select')?.value || 'pix';
    if (paymentMethod === 'pix') {
        openPixPaymentModal();
    } else {
        openLinkPaymentModal();
    }
});

function submitOrder(paymentMethod, pixOption) {
    const { totalDiscount } = calculateOrderTotals();
    const deliveryFee = parseFloat(document.getElementById('delivery-fee-input').value) || 0;
    const manualDiscountFixed = parseFloat(document.getElementById('manual-discount-fixed').value) || 0;
    const manualDiscountPercent = parseFloat(document.getElementById('manual-discount-percent').value) || 0;

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
        send_payment_link: true,
        payment_method: paymentMethod,
        pix_option: pixOption || null,
        address_id: pdvState.customer.address_id || null,
        scheduled_delivery_date: pdvState.scheduled_delivery_date || null,
        scheduled_delivery_slot: pdvState.scheduled_delivery_slot || null,
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
            if (paymentMethod === 'pix' && pixOption === 'display_qr' && data.order_id) {
                fetch(`{{ url("/dashboard/pdv/pix-qr/:id") }}`.replace(':id', data.order_id))
                    .then(res => res.json())
                    .then(pixData => {
                        showPixQrModal(data.order_id, pixData);
                        document.getElementById('pix-payment-modal')?.classList.add('hidden');
                    });
            } else {
                alert(data.message);
                window.location.reload();
            }
        } else {
            alert('Erro: ' + (data.message || 'Erro ao criar pedido'));
        }
    })
    .catch(err => {
        console.error('Erro ao criar pedido:', err);
        alert('Erro ao criar pedido');
    });
}

// Fechar modais
document.getElementById('btn-close-finalize-modal')?.addEventListener('click', () => {
    document.getElementById('finalize-modal').classList.add('hidden');
});
document.getElementById('btn-cancel-finalize')?.addEventListener('click', () => {
    document.getElementById('finalize-modal').classList.add('hidden');
});

document.getElementById('btn-close-pix-payment-modal')?.addEventListener('click', () => {
    document.getElementById('pix-payment-modal').classList.add('hidden');
});
document.getElementById('btn-cancel-pix-payment')?.addEventListener('click', () => {
    document.getElementById('pix-payment-modal').classList.add('hidden');
});
document.getElementById('btn-confirm-pix-payment')?.addEventListener('click', () => {
    const pixOption = document.querySelector('input[name="pix_option"]:checked')?.value || 'display_qr';
    submitOrder('pix', pixOption);
});
document.querySelectorAll('input[name="pix_option"]').forEach(radio => {
    radio.addEventListener('change', () => {
        const btn = document.getElementById('btn-confirm-pix-payment');
        if (!btn) return;
        btn.textContent = radio.value === 'send_whatsapp' ? 'Enviar Cobrança' : 'Gerar QR Code';
    });
});

document.getElementById('btn-close-link-payment-modal')?.addEventListener('click', () => {
    document.getElementById('link-payment-modal').classList.add('hidden');
});
document.getElementById('btn-cancel-link-payment')?.addEventListener('click', () => {
    document.getElementById('link-payment-modal').classList.add('hidden');
});
document.getElementById('btn-confirm-link-payment')?.addEventListener('click', () => {
    submitOrder('credit_card', null);
});

// Modal de novo cliente
document.getElementById('btn-new-customer')?.addEventListener('click', () => {
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
    
    fetch('{{ route("api.pdv.customers.store") }}', {
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

// Função para exibir modal com QR Code PIX
function showPixQrModal(orderId, pixData) {
    const modal = document.getElementById('pix-qr-modal');
    const content = document.getElementById('pix-qr-content');
    
    if (!pixData || !pixData.qr_code_base64) {
        content.innerHTML = `
            <div class="text-center py-8">
                <p class="text-red-600 mb-4">Erro ao gerar QR Code</p>
                <button onclick="location.reload()" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
                    Fechar
                </button>
            </div>
        `;
        modal.classList.remove('hidden');
        return;
    }
    
    const qrCodeImg = pixData.qr_code_base64 ? `data:image/png;base64,${pixData.qr_code_base64}` : '';
    const copyPaste = pixData.copy_paste || pixData.qr_code || '';
    const amount = pixData.amount || 0;
    
    content.innerHTML = `
        <div class="text-center space-y-4">
            <div>
                <p class="text-lg font-semibold mb-2">Valor: R$ ${parseFloat(amount).toFixed(2).replace('.', ',')}</p>
                <p class="text-sm text-muted-foreground">Escaneie o QR Code ou copie o código PIX</p>
            </div>
            
            ${qrCodeImg ? `
                <div class="inline-block p-4 bg-white border-2 border-gray-200 rounded-lg">
                    <img src="${qrCodeImg}" alt="QR Code PIX" class="w-64 h-64 mx-auto">
                </div>
            ` : ''}
            
            ${copyPaste ? `
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-left">Código PIX (Copiar e Colar):</label>
                    <div class="flex gap-2">
                        <input type="text" id="pix-copy-paste-code" value="${copyPaste}" readonly 
                               class="flex-1 border border-gray-300 rounded-lg px-3 py-2 bg-gray-50 text-sm font-mono">
                        <button type="button" onclick="copyPixCode()" 
                                class="bg-primary text-primary-foreground px-4 py-2 rounded-lg hover:bg-primary/90 whitespace-nowrap">
                            Copiar
                        </button>
                    </div>
                    <p id="copy-feedback" class="text-xs text-green-600 hidden">Código copiado!</p>
                </div>
            ` : ''}
            
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <p class="text-sm text-blue-900">
                    <strong>Aguardando pagamento...</strong><br>
                    O pagamento será confirmado automaticamente quando processado.
                </p>
            </div>
        </div>
    `;
    
    modal.classList.remove('hidden');
    
    // Iniciar monitoramento de pagamento
    startPaymentMonitoring(orderId);
}

function copyPixCode() {
    const input = document.getElementById('pix-copy-paste-code');
    if (!input) return;
    
    input.select();
    input.setSelectionRange(0, 99999);
    
    try {
        document.execCommand('copy');
        const feedback = document.getElementById('copy-feedback');
        if (feedback) {
            feedback.classList.remove('hidden');
            setTimeout(() => feedback.classList.add('hidden'), 2000);
        }
    } catch (err) {
        console.error('Erro ao copiar:', err);
        alert('Erro ao copiar código. Por favor, selecione e copie manualmente.');
    }
}

// Monitoramento de pagamento
let paymentPollInterval = null;

function startPaymentMonitoring(orderId) {
    if (paymentPollInterval) {
        clearInterval(paymentPollInterval);
    }
    
    let pollCount = 0;
    const maxPolls = 240; // Máximo 20 minutos (240 * 5 segundos)
    
    paymentPollInterval = setInterval(() => {
        pollCount++;
        
        if (pollCount > maxPolls) {
            clearInterval(paymentPollInterval);
            const content = document.getElementById('pix-qr-content');
            if (content) {
                const warning = document.createElement('div');
                warning.className = 'bg-yellow-50 border border-yellow-200 rounded-lg p-4 mt-4';
                warning.innerHTML = '<p class="text-sm text-yellow-900">Tempo de espera excedido. Você pode fechar esta tela e verificar o pagamento mais tarde.</p>';
                content.appendChild(warning);
            }
            return;
        }
        
        fetch(`/dashboard/orders/${orderId}/payment-status`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success && (data.payment_status === 'paid' || data.payment_status === 'approved')) {
                clearInterval(paymentPollInterval);
                
                // Atualizar UI com confirmação
                const content = document.getElementById('pix-qr-content');
                if (content) {
                    content.innerHTML = `
                        <div class="text-center space-y-4">
                            <div class="inline-block p-4 bg-green-100 rounded-full">
                                <svg class="w-16 h-16 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xl font-semibold text-green-600 mb-2">Pagamento Confirmado!</p>
                                <p class="text-sm text-muted-foreground">O pedido foi pago com sucesso.</p>
                            </div>
                            <div class="flex gap-3 mt-6">
                                <button onclick="location.reload()" 
                                        class="flex-1 bg-primary text-primary-foreground px-4 py-2 rounded-lg hover:bg-primary/90">
                                    Novo Pedido
                                </button>
                                <button onclick="window.location.href='/dashboard/orders/${orderId}'" 
                                        class="flex-1 border border-input bg-background px-4 py-2 rounded-lg hover:bg-accent">
                                    Ver Pedido
                                </button>
                            </div>
                        </div>
                    `;
                }
            }
        })
        .catch(err => {
            console.error('Erro ao verificar pagamento:', err);
        });
    }, 5000); // Verificar a cada 5 segundos
}

// Fechar modal QR Code
document.getElementById('btn-close-pix-qr-modal')?.addEventListener('click', function() {
    if (paymentPollInterval) {
        clearInterval(paymentPollInterval);
    }
    document.getElementById('pix-qr-modal').classList.add('hidden');
});

document.getElementById('btn-close-pix-qr')?.addEventListener('click', function() {
    if (paymentPollInterval) {
        clearInterval(paymentPollInterval);
    }
    document.getElementById('pix-qr-modal').classList.add('hidden');
});
</script>
@endpush
@endsection
