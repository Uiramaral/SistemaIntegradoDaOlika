@extends('dashboard.layouts.app')

@section('page_title', 'PDV - Ponto de Venda')
@section('page_subtitle', 'Criar novo pedido')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/pages/pdv.css') }}?v={{ time() }}">
@endpush

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

    <!-- Seção para Confirmar Pagamento de Pedidos Migrados -->
    <details class="rounded-lg border bg-card text-card-foreground shadow-sm mb-6 group">
        <summary class="cursor-pointer select-none p-4 hover:bg-muted/30 transition-colors">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-base font-semibold leading-none tracking-tight">Confirmar Pagamento (Migração)</h3>
                    <p class="text-xs text-muted-foreground mt-1">Confirme o pagamento de pedidos migrados sem enviar notificação</p>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-down transition-transform group-open:rotate-180">
                    <path d="m6 9 6 6 6-6"></path>
                </svg>
            </div>
        </summary>
        <div class="p-6 pt-0 border-t">
            <div class="flex gap-2">
                <input type="text" 
                       id="order-number-search" 
                       class="flex-1 rounded-md border border-input bg-background px-3 py-2 text-sm" 
                       placeholder="Digite o número do pedido (ex: OLK20241106123456)..."
                       autocomplete="off">
                <button type="button" 
                        id="btn-search-order" 
                        class="inline-flex items-center justify-center rounded-md text-sm font-medium bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4">
                    Buscar
                </button>
            </div>
            <div id="order-search-result" class="mt-4 hidden">
                <div class="p-4 border rounded-md bg-muted/50">
                    <div id="order-info" class="space-y-2"></div>
                    <div class="mt-4">
                        <button type="button" 
                                id="btn-confirm-payment-silent" 
                                class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check-circle">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                <polyline points="22 4 12 14.01 9 11.01"></polyline>
                            </svg>
                            Confirmar Pagamento (Sem Notificar)
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </details>

    <div class="dashboard-two-panel gap-4 lg:items-start">
        <!-- Coluna Resumo -->
        <div class="dashboard-aside flex flex-col gap-4 lg:flex-shrink-0">
            <!-- Itens do Pedido -->
            <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                <div class="flex flex-col space-y-1.5 p-4 pb-3 border-b border-border/60">
                    <h3 class="text-lg font-semibold leading-none tracking-tight">Itens do Pedido</h3>
                </div>
                <div class="p-4">
                    <div id="pdv-items-list" class="space-y-2 max-h-72 overflow-y-auto pr-1">
                        <p class="text-sm text-muted-foreground text-center py-6">Nenhum item adicionado</p>
                    </div>
                </div>
            </div>

            <!-- Resumo -->
            <div class="rounded-lg border bg-card text-card-foreground shadow-sm lg:sticky lg:top-20">
                <div class="flex flex-col space-y-1.5 p-4 pb-3">
                    <h3 class="text-lg font-semibold leading-none tracking-tight">Resumo</h3>
                </div>
                <div class="p-4 pt-0 space-y-4">
                    <div class="space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-muted-foreground">Subtotal:</span>
                            <span id="summary-subtotal">R$ 0,00</span>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-xs font-medium text-muted-foreground mb-1">Taxa de Entrega / Desconto Manual</label>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                <div>
                                    <input type="number" id="delivery-fee-input" step="0.01" min="0" value="0" class="w-full rounded-md border border-input bg-background text-sm" placeholder="Taxa">
                                    <p class="text-xs text-muted-foreground mt-1">Taxa</p>
                                </div>
                                <div>
                                    <input type="number" id="manual-discount-fixed" step="0.01" min="0" value="0" placeholder="R$ 0,00" class="w-full rounded-md border border-input bg-background text-sm">
                                    <p class="text-xs text-muted-foreground mt-1">Valor fixo</p>
                                </div>
                                <div>
                                    <input type="number" id="manual-discount-percent" step="0.01" min="0" max="100" value="0" placeholder="0%" class="w-full rounded-md border border-input bg-background text-sm">
                                    <p class="text-xs text-muted-foreground mt-1">%</p>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-xs font-medium text-muted-foreground">Calcular por CEP</label>
                            <div class="flex flex-col gap-2 sm:flex-row">
                                <input type="text" id="destination-cep" class="flex-1 rounded-md border border-input bg-background text-sm" placeholder="00000-000" maxlength="10">
                                <button type="button" id="btn-calculate-fee" class="inline-flex items-center justify-center rounded-md text-sm font-medium border border-input bg-background hover:bg-accent hover:text-accent-foreground">
                                    Calcular
                                </button>
                            </div>
                            <div id="delivery-fee-info" class="mt-1 text-xs text-muted-foreground hidden"></div>
                        </div>

                        <div class="flex justify-between text-sm text-green-600 hidden" id="discount-row">
                            <span>Desconto:</span>
                            <span id="summary-discount">- R$ 0,00</span>
                        </div>

                        <div class="border-t pt-2 flex justify-between font-semibold">
                            <span>Total:</span>
                            <span id="summary-total" class="text-orange-600">R$ 0,00</span>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium mb-2">Cupom (opcional)</label>
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                                <input type="text" id="coupon-code" class="flex-1 rounded-md border border-input bg-background text-sm" placeholder="Código do cupom">
                                <button type="button" id="btn-apply-coupon" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium border border-input bg-background hover:bg-accent hover:text-accent-foreground">
                                    Aplicar
                                </button>
                            </div>
                            <div id="coupon-info" class="mt-2 hidden p-2 bg-muted rounded-md text-sm"></div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">Tipo de Entrega</label>
                            <select id="delivery-type" class="w-full rounded-md border border-input bg-background text-sm">
                                <option value="delivery">Entrega</option>
                                <option value="pickup">Retirada</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">Observações</label>
                            <textarea id="order-notes" rows="3" class="w-full rounded-md border border-input bg-background text-sm" placeholder="Observações do pedido..."></textarea>
                        </div>
                    </div>

                    <div class="flex flex-col gap-3">
                        <div class="flex flex-col gap-3 sm:flex-row">
                            <button type="button" id="btn-send-order" class="flex-1 inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium bg-green-600 text-white hover:bg-green-700 h-10 px-4 py-2 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                                Enviar Pedido
                            </button>
                            <button type="button" id="btn-finalize-order" class="flex-1 inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                                Finalizar Pedido
                            </button>
                        </div>
                        <button type="button" id="btn-create-paid-order" class="w-full inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium bg-orange-600 text-white hover:bg-orange-700 h-10 px-4 py-2 disabled:opacity-50 disabled:cursor-not-allowed" disabled title="Criar pedido já como pago, sem enviar notificação ao cliente (para migração)">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check-circle-2">
                                <path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"></path>
                                <path d="m9 12 2 2 4-4"></path>
                            </svg>
                            Criar Pedido Pago (Migração)
                        </button>

                        <div class="mt-4 pt-4 border-t">
                            <button type="button" id="btn-add-more-items" class="w-full inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-plus">
                                    <path d="M5 12h14"></path>
                                    <path d="M12 5v14"></path>
                                </svg>
                                Adicionar Mais Itens
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Coluna Conteúdo (Cliente + Produtos) -->
        <div class="dashboard-main flex flex-col space-y-6">
            <!-- Seleção de Cliente -->
            <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                <div class="flex flex-col space-y-1.5 p-6">
                    <h3 class="text-lg font-semibold leading-none tracking-tight">Cliente</h3>
                </div>
                <div class="p-6 pt-0">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">Buscar Cliente *</label>
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                                <input type="text" id="customer-search" class="flex-1 rounded-md border border-input bg-background text-sm" placeholder="Digite nome, telefone ou email..." autocomplete="off">
                                <button type="button" id="btn-new-customer" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium border border-input bg-background hover:bg-accent hover:text-accent-foreground">
                                    Novo Cliente
                                </button>
                            </div>
                            <div id="customer-results" class="mt-2 hidden max-h-60 overflow-y-auto border rounded-md bg-background"></div>
                            <input type="hidden" id="customer-id" name="customer_id" required>
                            <div id="selected-customer" class="mt-3 hidden p-3 bg-muted rounded-md">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="font-semibold truncate" id="selected-customer-name"></p>
                                        <p class="text-sm text-muted-foreground truncate" id="selected-customer-info"></p>
                                    </div>
                                    <button type="button" id="btn-clear-customer" class="text-muted-foreground hover:text-foreground">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x">
                                            <path d="M18 6 6 18"></path>
                                            <path d="M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Seleção de Produtos -->
            <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                <div class="flex flex-col space-y-1.5 p-6">
                    <h3 class="text-lg font-semibold leading-none tracking-tight">Produtos</h3>
                </div>
                <div class="p-6 pt-0">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">Buscar Produto</label>
                            <input type="text" id="product-search" class="w-full rounded-md border border-input bg-background text-sm" placeholder="Digite o nome do produto para buscar..." autocomplete="off">
                            <div id="product-results" class="mt-2 hidden max-h-64 overflow-y-auto border rounded-md bg-background shadow-lg"></div>
                        </div>

                        <div class="mt-4">
                            <h4 class="text-sm font-medium mb-3">Produtos Frequentes</h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 max-h-[600px] overflow-y-auto pr-2">
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
                                    @endphp
                                    <button type="button" class="product-quick-add text-left border rounded-lg hover:bg-accent hover:border-primary transition-all shadow-sm hover:shadow-md" data-product-id="{{ $product->id }}" data-product-name="{{ $product->name }}" data-product-price="{{ $displayPrice }}" data-has-variants="{{ $hasVariants ? 'true' : 'false' }}" data-variants="{{ json_encode($variantsData) }}">
                                        <p class="font-semibold text-sm mb-2">{{ $product->name }}</p>
                                        <div class="mt-auto">
                                            @if($hasVariants)
                                                <p class="text-base font-bold text-primary product-price-display mb-2">A partir de R$ {{ number_format($displayPrice, 2, ',', '.') }}</p>
                                                <p class="text-xs text-blue-600 font-medium">Escolher opção →</p>
                                            @else
                                                <p class="text-base font-bold text-primary product-price-display">R$ {{ number_format($displayPrice, 2, ',', '.') }}</p>
                                            @endif
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        </div>
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
                            <option value="pix">PIX</option>
                            <option value="credit_card">Cartão de Crédito</option>
                            <option value="debit_card">Cartão de Débito</option>
                        </select>
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
// Estado do PDV
const pdvState = {
    customer: null,
    items: [],
    coupon: null,
    deliveryType: 'delivery',
    deliveryFee: 0,
    notes: '',
};

// Funções de busca de cliente
let customerSearchTimeout;
document.getElementById('customer-search')?.addEventListener('input', function(e) {
    clearTimeout(customerSearchTimeout);
    const query = e.target.value.trim();
    
    if (query.length < 2) {
        document.getElementById('customer-results').classList.add('hidden');
        return;
    }
    
    customerSearchTimeout = setTimeout(() => {
        fetch(`{{ route('api.pdv.customers.search') }}?q=${encodeURIComponent(query)}`)
            .then(res => res.json())
            .then(data => {
                const resultsEl = document.getElementById('customer-results');
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
        document.getElementById('customer-results').classList.add('hidden');
        document.getElementById('customer-search').value = '';
        
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
        
        // Se cliente possui taxa fixa personalizada, aplicar
        if (pdvState.customer.custom_delivery_fee !== undefined && pdvState.customer.custom_delivery_fee !== null) {
            const fee = parseFloat(pdvState.customer.custom_delivery_fee);
            if (!isNaN(fee)) {
                document.getElementById('delivery-fee-input').value = fee.toFixed(2);
                const infoEl = document.getElementById('delivery-fee-info');
                infoEl.innerHTML = `Taxa personalizada do cliente aplicada`;
                infoEl.classList.remove('hidden');
                updateSummary();
                updateFinalizeButtons();
                return; // não calcular por CEP
            }
        }

        // Caso tenha CEP salvo no cliente, calcular automaticamente (mesmo sem itens)
        if (pdvState.customer.zip_code) {
            const subtotal = pdvState.items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            const btnCalc = document.getElementById('btn-calculate-fee');
            if (btnCalc) btnCalc.disabled = true;
            fetch('{{ route("api.pdv.calculateDeliveryFee") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ 
                    cep: String(pdvState.customer.zip_code).replace(/\D/g,''), 
                    subtotal: Math.max(0, subtotal), // Pelo menos 0
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
});

// Limpar cliente
document.getElementById('btn-clear-customer')?.addEventListener('click', function() {
    pdvState.customer = null;
    document.getElementById('customer-id').value = '';
    document.getElementById('selected-customer').classList.add('hidden');
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
                        return `
                            <button type="button" class="product-option w-full text-left p-2 hover:bg-accent cursor-pointer" 
                                    data-product-id="${p.id}" 
                                    data-product-name="${p.name}"
                                    data-product-price="${displayPrice}"
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

// Modal de seleção de variante
function showVariantModal(productId, productName, variants) {
    // Criar modal dinamicamente
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 z-50 flex items-center justify-center';
    modal.style.backgroundColor = 'rgba(0, 0, 0, 0.75)';
    modal.id = 'variant-modal';
    modal.innerHTML = `
        <div class="bg-white rounded-lg shadow-2xl w-full max-w-md mx-4 relative">
            <div class="p-6">
                <h3 class="text-lg font-semibold mb-4">${productName}</h3>
                <p class="text-sm text-muted-foreground mb-4">Escolha uma opção:</p>
                <div class="space-y-2 max-h-60 overflow-y-auto">
                    ${variants.map(v => `
                        <button type="button" 
                                class="variant-option w-full text-left p-3 border rounded-md hover:bg-accent transition-colors"
                                data-variant-id="${v.id}"
                                data-variant-name="${v.name}"
                                data-variant-price="${v.price}">
                            <p class="font-medium">${v.name}</p>
                            <p class="text-sm text-muted-foreground">R$ ${parseFloat(v.price).toFixed(2).replace('.', ',')}</p>
                        </button>
                    `).join('')}
                </div>
                <div class="mt-4 flex justify-end gap-2">
                    <button type="button" 
                            id="btn-cancel-variant" 
                            class="px-4 py-2 rounded-md border border-input bg-background hover:bg-accent">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
    
    // Handlers
    modal.querySelectorAll('.variant-option').forEach(btn => {
        btn.addEventListener('click', function() {
            const variantId = btn.dataset.variantId;
            const variantName = btn.dataset.variantName;
            const variantPrice = parseFloat(btn.dataset.variantPrice);
            
            addItem({
                product_id: productId,
                variant_id: variantId,
                name: `${productName} - ${variantName}`,
                price: variantPrice,
                quantity: 1,
            });
            
            document.body.removeChild(modal);
            document.getElementById('product-results')?.classList.add('hidden');
            document.getElementById('product-search').value = '';
        });
    });
    
    modal.querySelector('#btn-cancel-variant')?.addEventListener('click', function() {
        document.body.removeChild(modal);
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
        
        // Sem variantes, adicionar diretamente
        addItem({
            product_id: productId,
            variant_id: null,
            name: productName,
            price: productPrice,
            quantity: 1,
        });
        
        document.getElementById('product-results')?.classList.add('hidden');
        document.getElementById('product-search').value = '';
    }
});

// Adicionar item ao pedido
function addItem(item) {
    // Identificar item único por produto + variante + preço
    const existingItem = pdvState.items.find(i => 
        i.product_id === item.product_id && 
        i.variant_id === (item.variant_id || null) &&
        i.price === item.price
    );
    
    if (existingItem) {
        existingItem.quantity += item.quantity || 1;
    } else {
        pdvState.items.push({
            product_id: item.product_id,
            variant_id: item.variant_id || null,
            name: item.name,
            price: item.price,
            quantity: item.quantity || 1,
        });
    }
    
    renderItems();
    updateSummary();
    updateFinalizeButtons();
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
        itemsEl.innerHTML = '<p class="text-sm text-muted-foreground text-center py-8">Nenhum item adicionado</p>';
        return;
    }
    
    itemsEl.innerHTML = pdvState.items.map((item, index) => `
        <div class="flex items-center justify-between p-2 border rounded-md">
            <div class="flex-1">
                <p class="font-medium text-sm">${item.name}</p>
                <p class="text-xs text-muted-foreground">R$ ${item.price.toFixed(2).replace('.', ',')} x ${item.quantity}</p>
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
    
    document.getElementById('summary-subtotal').textContent = 'R$ ' + subtotal.toFixed(2).replace('.', ',');
    document.getElementById('summary-delivery').textContent = 'R$ ' + deliveryFee.toFixed(2).replace('.', ',');
    document.getElementById('summary-total').textContent = 'R$ ' + total.toFixed(2).replace('.', ',');
    
    if (totalDiscount > 0) {
        document.getElementById('discount-row').classList.remove('hidden');
        document.getElementById('summary-discount').textContent = '- R$ ' + totalDiscount.toFixed(2).replace('.', ',');
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

// Formatar CEP ao digitar
document.getElementById('destination-cep')?.addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length > 5) {
        value = value.substring(0, 5) + '-' + value.substring(5, 8);
    }
    e.target.value = value;
});

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
});

// Atualizar estado dos botões
function updateFinalizeButtons() {
    const hasCustomer = pdvState.customer !== null;
    const hasItems = pdvState.items.length > 0;
    const enabled = hasCustomer && hasItems;
    
    document.getElementById('btn-finalize-order').disabled = !enabled;
    document.getElementById('btn-send-order').disabled = !enabled;
    document.getElementById('btn-create-paid-order').disabled = !enabled;
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
            alert(data.message || 'Pedido enviado ao cliente com sucesso!');
            // Limpar estado e recarregar página
            window.location.reload();
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
    const manualDiscountFixed = parseFloat(document.getElementById('manual-discount-fixed').value) || 0;
    const manualDiscountPercent = parseFloat(document.getElementById('manual-discount-percent').value) || 0;
    const manualDiscountFromPercent = subtotal * (manualDiscountPercent / 100);
    const totalDiscount = couponDiscount + manualDiscountFixed + manualDiscountFromPercent;
    
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
        discount_amount: totalDiscount,
        manual_discount_fixed: manualDiscountFixed,
        manual_discount_percent: manualDiscountPercent,
        notes: pdvState.notes,
        send_payment_link: paymentOption === 'send_link',
        payment_method: paymentMethod,
        address_id: pdvState.customer.address_id || null,
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
    const manualDiscountFixed = parseFloat(document.getElementById('manual-discount-fixed').value) || 0;
    const manualDiscountPercent = parseFloat(document.getElementById('manual-discount-percent').value) || 0;
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
</script>
@endpush
@endsection
