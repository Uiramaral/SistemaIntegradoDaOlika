@extends('dashboard.layouts.app')

@section('page_title', 'Pedidos')
@section('page_subtitle', 'Gerenciamento de pedidos')

@push('styles')
    <style>
        /* Estilos específicos para a página de pedidos */
        .view-btn {
            @apply px-4 py-2 rounded-lg text-sm font-semibold transition-all;
        }

        .view-btn.active {
            @apply bg-white text-foreground shadow-sm border border-gray-200;
        }

        .view-btn.inactive {
            @apply bg-transparent text-gray-600 hover:text-foreground;
        }

        .status-tab {
            @apply px-4 py-2 rounded-lg text-sm font-semibold transition-all flex items-center gap-2;
        }

        .status-tab.active {
            @apply bg-white text-foreground shadow-sm border border-gray-200;
        }

        .status-tab.inactive {
            @apply bg-transparent text-gray-600 hover:text-foreground;
        }

        /* Prevenir overflow horizontal nos cards */
        .order-card {
            max-width: 100%;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        /* Responsividade para mobile */
        @media (max-width: 768px) {
            #orders-list-view table {
                font-size: 0.875rem;
            }

            /* Garantir que o card não extrapole */
            .order-card {
                min-width: 0;
            }

            /* Garantir que o menu dropdown fique acima de outros elementos */
            [id^="order-actions-menu-"] {
                position: absolute;
                z-index: 1000;
            }
        }

        /* Menu de ações dropdown */
        [id^="order-actions-menu-"] {
            position: absolute;
            right: 0;
            z-index: 1000;
            min-width: 200px;
        }
    </style>
@endpush

@section('content')
    <div class="bg-card rounded-xl border border-border animate-fade-in max-w-full" id="orders-page"
        x-data="ordersLiveSearch()">
        <!-- Card Header: Busca, Filtros e Botão -->
        <div class="p-3 sm:p-4 md:p-6 border-b border-border overflow-visible">
            <form method="GET" action="{{ route('dashboard.orders.index') }}" class="flex flex-col gap-3">
                <!-- Desktop: Busca, Filtro e Botões na mesma linha -->
                <div class="hidden lg:flex items-center gap-3">
                    <!-- Barra de Busca -->
                    <div class="relative flex-1">
                        <i data-lucide="search"
                            class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground pointer-events-none"></i>
                        <input type="text" name="q" x-model="search" placeholder="Buscar por cliente, número do pedido..."
                            class="form-input pl-10 h-10 bg-muted/30 border-transparent focus:bg-white transition-all text-sm rounded-lg w-full"
                            autocomplete="off">
                    </div>

                    <!-- Filtro Status -->
                    <select name="status" x-model="statusFilter" @change="$event.target.form && $event.target.form.submit()"
                        class="h-10 rounded-lg border border-input bg-muted/30 text-sm px-3 focus:ring-2 focus:ring-primary/20 focus:border-primary w-[160px] shrink-0">
                        <option value="all">Todos Status</option>
                        <option value="active">Ativos</option>
                        <option value="pending">Pendente</option>
                        <option value="confirmed">Confirmado</option>
                        <option value="preparing">Em Preparo</option>
                        <option value="ready">Pronto</option>
                        <option value="delivered">Entregue</option>
                        <option value="cancelled">Cancelado</option>
                    </select>

                    <!-- Botões -->
                    <a href="{{ route('dashboard.orders.index') }}"
                        class="h-10 px-4 rounded-lg text-sm font-medium gap-2 bg-muted/30 border border-input hover:bg-muted inline-flex items-center justify-center shrink-0"
                        title="Limpar filtros">
                        <i data-lucide="eraser" class="w-4 h-4 shrink-0"></i>
                        <span>Limpar</span>
                    </a>

                    <button type="button"
                        class="btn-primary gap-2 h-10 px-4 rounded-lg shadow-sm inline-flex items-center justify-center shrink-0"
                        id="btn-nova-encomenda">
                        <i data-lucide="plus" class="h-4 w-4 text-white"></i>
                        <span class="font-bold text-white text-sm">Novo pedido</span>
                    </button>
                </div>

                <!-- Mobile: Layout empilhado -->
                <div class="flex lg:hidden flex-col gap-3">
                    <!-- Barra de Busca -->
                    <div class="relative w-full">
                        <i data-lucide="search"
                            class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground pointer-events-none"></i>
                        <input type="text" name="q" x-model="search" placeholder="Buscar por cliente, número do pedido..."
                            class="form-input pl-10 h-10 bg-muted/30 border-transparent focus:bg-white transition-all text-sm rounded-lg w-full"
                            autocomplete="off">
                    </div>

                    <!-- Filtro Status -->
                    <select name="status" x-model="statusFilter" @change="$event.target.form && $event.target.form.submit()"
                        class="h-10 rounded-lg border border-input bg-muted/30 text-sm px-3 focus:ring-2 focus:ring-primary/20 focus:border-primary w-full">
                        <option value="all">Todos Status</option>
                        <option value="active">Ativos</option>
                        <option value="pending">Pendente</option>
                        <option value="confirmed">Confirmado</option>
                        <option value="preparing">Em Preparo</option>
                        <option value="ready">Pronto</option>
                        <option value="delivered">Entregue</option>
                        <option value="cancelled">Cancelado</option>
                    </select>

                    <!-- Botões Limpar e Novo Pedido lado a lado, 50/50 -->
                    <div class="flex items-center gap-2 w-full">
                        <a href="{{ route('dashboard.orders.index') }}"
                            class="h-10 px-3 rounded-lg text-sm font-medium gap-1.5 bg-muted/30 border border-input hover:bg-muted inline-flex items-center justify-center flex-1"
                            title="Limpar filtros">
                            <i data-lucide="eraser" class="w-4 h-4 shrink-0"></i>
                            <span>Limpar</span>
                        </a>

                        <button type="button"
                            class="btn-primary gap-2 h-10 px-4 rounded-lg shadow-sm inline-flex items-center justify-center flex-1"
                            id="btn-nova-encomenda-mobile">
                            <i data-lucide="plus" class="h-4 w-4 text-white"></i>
                            <span class="font-bold text-white text-sm">Novo pedido</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Orders Grid -->
        <div class="p-3 sm:p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-4">
                @forelse($orders as $order)
                    @php
                        // Extrair apenas o número do pedido
                        $orderNumberDisplay = $order->order_number ?? '#' . $order->id;
                        if (preg_match('/OLK-(\d+)-/', $orderNumberDisplay, $matches)) {
                            $orderNumberDisplay = $matches[1];
                        }

                        // Nome COMPLETO do cliente
                        $customerName = $order->customer->name ?? 'Cliente';

                        // Data formatada - data do pedido (quando foi realizado)
                        $orderDate = $order->created_at;
                        $formattedDate = $orderDate->format('d/m/Y');

                        // Valor total
                        $totalAmount = $order->final_amount ?? $order->total_amount ?? 0;

                        // Status
                        $statusMap = [
                            'pending' => ['label' => 'Pendente', 'class' => 'bg-yellow-100 text-yellow-800', 'icon' => 'clock'],
                            'confirmed' => ['label' => 'Confirmado', 'class' => 'bg-green-100 text-green-800', 'icon' => 'check-circle'],
                            'preparing' => ['label' => 'Preparando', 'class' => 'bg-blue-100 text-blue-800', 'icon' => 'chef-hat'],
                            'ready' => ['label' => 'Pronto', 'class' => 'bg-indigo-100 text-indigo-800', 'icon' => 'package-check'],
                            'delivered' => ['label' => 'Entregue', 'class' => 'bg-green-100 text-green-800', 'icon' => 'check'],
                            'cancelled' => ['label' => 'Cancelado', 'class' => 'bg-red-100 text-red-800', 'icon' => 'x-circle'],
                        ];
                        $statusData = $statusMap[$order->status] ?? ['label' => ucfirst($order->status), 'class' => 'bg-gray-100 text-gray-800', 'icon' => 'circle'];



                        $searchCustomer = mb_strtolower($customerName, 'UTF-8');
                        $searchOrder = mb_strtolower($orderNumberDisplay, 'UTF-8');
                        $searchStatus = mb_strtolower($statusData['label'], 'UTF-8');
                    @endphp

                    <div class="order-card bg-white border border-border rounded-xl p-3 sm:p-4 hover:shadow-md transition-all"
                        data-search-id="{{ $searchOrder }}" data-search-customer="{{ $searchCustomer }}"
                        data-status="{{ $order->status }}" x-show="matchesCard($el)">
                        <!-- Header: Avatar, Nome, Status, Ações -->
                        <div class="flex items-start justify-between gap-2 mb-3">
                            <div class="flex items-center gap-2 flex-1 overflow-hidden">
                                <div class="flex-1 overflow-hidden">
                                    <a href="{{ route('dashboard.orders.show', $order->id) }}" class="block group">
                                        <h3 class="font-semibold text-foreground text-sm group-hover:text-primary transition-colors truncate"
                                            title="{{ $customerName }}">{{ $customerName }}</h3>
                                        <p class="text-xs text-muted-foreground mt-0.5 truncate">Pedido
                                            #{{ $orderNumberDisplay }}</p>
                                    </a>
                                </div>
                            </div>
                            <div class="flex items-center gap-1.5 shrink-0">
                                <span
                                    class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full text-[10px] sm:text-xs font-semibold {{ $statusData['class'] }} shrink-0 whitespace-nowrap">
                                    <i data-lucide="{{ $statusData['icon'] }}" class="w-3 h-3 sm:w-3.5 sm:h-3.5 shrink-0"></i>
                                    <span class="hidden md:inline xl:hidden 2xl:inline">{{ $statusData['label'] }}</span>
                                </span>
                                <div x-data="{
                                                                                                                                                    open: false,
                                                                                                                                                    toggle() {
                                                                                                                                                        if (this.open) {
                                                                                                                                                            this.open = false;
                                                                                                                                                        } else {
                                                                                                                                                            // Fechar outros menus antes de abrir este
                                                                                                                                                            window.dispatchEvent(new CustomEvent('close-all-menus', { detail: { except: this.$el } }));
                                                                                                                                                            this.open = true;
                                                                                                                                                            this.updatePosition();
                                                                                                                                                        }
                                                                                                                                                    },
                                                                                                                                                    updatePosition() {
                                                                                                                                                        this.$nextTick(() => {
                                                                                                                                                            const trigger = this.$refs.trigger;
                                                                                                                                                            const dropdown = this.$refs.dropdown;
                                                                                                                                                            if (!trigger || !dropdown) return;

                                                                                                                                                            const rect = trigger.getBoundingClientRect();
                                                                                                                                                            const dropdownHeight = 320; // Altura estimada máxima
                                                                                                                                                            const viewportHeight = window.innerHeight;
                                                                                                                                                            const spaceBelow = viewportHeight - rect.bottom;

                                                                                                                                                            // Resetar estilos base
                                                                                                                                                            dropdown.style.position = 'fixed';
                                                                                                                                                            dropdown.style.right = 'auto';
                                                                                                                                                            dropdown.style.left = (rect.right - 224) + 'px'; // 224px = w-56

                                                                                                                                                            // Decidir se abre para cima ou para baixo
                                                                                                                                                            if (spaceBelow < dropdownHeight && rect.top > spaceBelow) {
                                                                                                                                                                // Abrir para cima
                                                                                                                                                                dropdown.style.top = 'auto';
                                                                                                                                                                dropdown.style.bottom = (viewportHeight - rect.top + 4) + 'px';
                                                                                                                                                                dropdown.style.maxHeight = (rect.top - 20) + 'px';
                                                                                                                                                            } else {
                                                                                                                                                                // Abrir para baixo
                                                                                                                                                                dropdown.style.bottom = 'auto';
                                                                                                                                                                dropdown.style.top = (rect.bottom + 4) + 'px';
                                                                                                                                                                dropdown.style.maxHeight = (spaceBelow - 20) + 'px';
                                                                                                                                                            }
                                                                                                                                                        });
                                                                                                                                                    },
                                                                                                                                                    init() {
                                                                                                                                                        // Escutar evento global para fechar
                                                                                                                                                        window.addEventListener('close-all-menus', (e) => {
                                                                                                                                                            if (e.detail && e.detail.except === this.$el) return;
                                                                                                                                                            this.open = false;
                                                                                                                                                        });

                                                                                                                                                        // Fechar ao rolar a página para evitar desconexão visual
                                                                                                                                                        window.addEventListener('scroll', () => { if(this.open) this.open = false; }, true);
                                                                                                                                                    }
                                                                                                                                                }"
                                    @click.outside="open = false" class="relative">
                                    <button type="button" x-ref="trigger" @click.stop="toggle()"
                                        class="inline-flex items-center justify-center h-8 w-8 sm:h-9 sm:w-9 rounded-md hover:bg-muted transition-colors text-muted-foreground hover:text-foreground"
                                        :class="{ 'bg-muted': open }" title="Ações">
                                        <i data-lucide="more-vertical" class="h-4 w-4"></i>
                                    </button>

                                    {{-- Menu Dropdown com position fixed via style inline dinâmico --}}
                                    <div x-show="open" x-cloak x-transition x-ref="dropdown"
                                        class="fixed w-56 bg-white rounded-lg shadow-xl border border-border z-[9999] py-1 overflow-y-auto"
                                        style="display: none;">
                                        <a href="{{ route('dashboard.orders.show', ['dashboard_domain' => request()->getHost(), 'order' => $order->id]) }}"
                                            class="flex items-center gap-2 px-4 py-2 text-sm text-foreground hover:bg-muted w-full text-left">
                                            <i data-lucide="eye" class="w-4 h-4 shrink-0"></i>
                                            <span>Ver detalhes</span>
                                        </a>

                                        {{-- AÇÕES GENÉRICAS PARA TODOS OS STATUS --}}
                                        <button type="button"
                                            onclick="requestPrint({{ $order->id }}, '{{ $order->order_number }}', event)"
                                            class="flex items-center gap-2 px-4 py-2 text-sm text-foreground hover:bg-muted w-full text-left">
                                            <i data-lucide="printer" class="w-4 h-4 shrink-0"></i>
                                            <span>Imprimir Recibo</span>
                                        </button>

                                        <button type="button"
                                            onclick="requestPrint({{ $order->id }}, '{{ $order->order_number }}', event, true)"
                                            class="flex items-center gap-2 px-4 py-2 text-sm text-foreground hover:bg-muted w-full text-left">
                                            <i data-lucide="clipboard-check" class="w-4 h-4 shrink-0"></i>
                                            <span>Imprimir Recibo Conferência</span>
                                        </button>

                                        @if(optional($order->customer)->phone)
                                            <form method="POST" action="{{ route('dashboard.orders.sendReceipt', $order->id) }}"
                                                class="w-full">
                                                @csrf
                                                <button type="submit"
                                                    class="flex items-center gap-2 px-4 py-2 text-sm text-blue-600 hover:bg-blue-50 w-full text-left">
                                                    <i data-lucide="send" class="w-4 h-4 shrink-0"></i>
                                                    <span>Enviar pelo WhatsApp</span>
                                                </button>
                                            </form>
                                        @endif

                                        @if($order->delivery_type === 'delivery' && $order->delivery_address)
                                            <button type="button"
                                                onclick="openMaps('{{ addslashes($order->delivery_address) }}', '{{ addslashes(optional($order->address)->neighborhood ?? $order->delivery_neighborhood ?? '') }}', '{{ addslashes(optional($order->address)->city ?? '') }}')"
                                                class="flex items-center gap-2 px-4 py-2 text-sm text-foreground hover:bg-muted w-full text-left">
                                                <i data-lucide="map-pin" class="w-4 h-4 shrink-0"></i>
                                                <span>Abrir no Maps</span>
                                            </button>
                                        @endif

                                        @if(!$order->scheduled_delivery_at)
                                            <button type="button"
                                                onclick="openScheduleModal({{ $order->id }}, '{{ $order->order_number }}')"
                                                class="flex items-center gap-2 px-4 py-2 text-sm text-purple-600 hover:bg-purple-50 w-full text-left">
                                                <i data-lucide="calendar-clock" class="w-4 h-4 shrink-0"></i>
                                                <span>Programar Entrega</span>
                                            </button>
                                        @endif

                                        @if(in_array($order->status, ['out_for_delivery', 'delivering']))
                                            <button type="button"
                                                onclick="openDeliveryNoteModal({{ $order->id }}, '{{ $order->order_number }}')"
                                                class="flex items-center gap-2 px-4 py-2 text-sm text-foreground hover:bg-muted w-full text-left">
                                                <i data-lucide="message-square" class="w-4 h-4 shrink-0"></i>
                                                <span>Observação da Entrega</span>
                                            </button>
                                        @endif

                                        @if($order->status === 'cancelled')
                                            {{-- PEDIDO CANCELADO: Duplicar --}}
                                            <div class="border-t border-border my-1"></div>
                                            <form method="POST" action="{{ route('dashboard.orders.duplicate', $order->id) }}"
                                                class="w-full">
                                                @csrf
                                                <button type="submit"
                                                    onclick="return confirm('Deseja duplicar este pedido? Um novo pedido será criado com os mesmos itens.')"
                                                    class="flex items-center gap-2 px-4 py-2 text-sm text-indigo-600 hover:bg-indigo-50 w-full text-left">
                                                    <i data-lucide="copy" class="w-4 h-4 shrink-0"></i>
                                                    <span>Duplicar Pedido</span>
                                                </button>
                                            </form>
                                        @elseif(in_array($order->status, ['pending', 'awaiting_payment', 'awaiting_review']))
                                            {{-- PEDIDO PENDENTE, AGUARDANDO PAGAMENTO OU REVISÃO --}}
                                            <div class="border-t border-border my-1"></div>

                                            @if(isset($order->payment_status) && $order->payment_status === 'pending')
                                                <form method="POST" action="{{ route('dashboard.orders.updateStatus', $order->id) }}"
                                                    class="w-full">
                                                    @csrf
                                                    <input type="hidden" name="status" value="paid">
                                                    <button type="submit"
                                                        class="flex items-center gap-2 px-4 py-2 text-sm text-green-600 hover:bg-green-50 w-full text-left">
                                                        <i data-lucide="check-circle" class="w-4 h-4 shrink-0"></i>
                                                        <span>Confirmar Pagamento</span>
                                                    </button>
                                                </form>
                                            @endif

                                            <form method="POST" action="{{ route('dashboard.orders.updateStatus', $order->id) }}"
                                                class="w-full">
                                                @csrf
                                                <input type="hidden" name="status" value="confirmed">
                                                <button type="submit"
                                                    class="flex items-center gap-2 px-4 py-2 text-sm text-green-600 hover:bg-green-50 w-full text-left">
                                                    <i data-lucide="check" class="w-4 h-4 shrink-0"></i>
                                                    <span>Confirmar Pedido (fiado)</span>
                                                </button>
                                            </form>

                                            <form method="POST" action="{{ route('dashboard.orders.updateStatus', $order->id) }}"
                                                class="w-full">
                                                @csrf
                                                <input type="hidden" name="status" value="cancelled">
                                                <button type="submit"
                                                    onclick="return confirm('Tem certeza que deseja cancelar este pedido?')"
                                                    class="flex items-center gap-2 px-4 py-2 text-sm text-destructive hover:bg-destructive/10 w-full text-left">
                                                    <i data-lucide="x-circle" class="w-4 h-4 shrink-0"></i>
                                                    <span>Cancelar Pedido</span>
                                                </button>
                                            </form>

                                            @if(isset($order->payment_status) && $order->payment_status === 'pending' && optional($order->customer)->phone)
                                                <form method="POST"
                                                    action="{{ route('dashboard.orders.sendPaymentCharge', $order->id) }}"
                                                    class="w-full">
                                                    @csrf
                                                    <button type="submit"
                                                        class="flex items-center gap-2 px-4 py-2 text-sm text-orange-600 hover:bg-orange-50 w-full text-left">
                                                        <i data-lucide="credit-card" class="w-4 h-4 shrink-0"></i>
                                                        <span>Enviar Cobrança WhatsApp</span>
                                                    </button>
                                                </form>
                                            @endif
                                        @elseif(in_array($order->status, ['confirmed', 'preparing', 'ready', 'out_for_delivery', 'delivering', 'delivered', 'paid']))
                                            {{-- PEDIDOS PAGOS, ENTREGUES, EM PREPARAÇÃO, CONFIRMADO, SAIU PARA ENTREGA, PRONTO,
                                            ENTREGANDO --}}
                                            <div class="border-t border-border my-1"></div>
                                            <form method="POST" action="{{ route('dashboard.orders.updateStatus', $order->id) }}"
                                                class="w-full">
                                                @csrf
                                                <input type="hidden" name="status" value="cancelled">
                                                <button type="submit"
                                                    onclick="return confirm('Tem certeza que deseja cancelar este pedido?')"
                                                    class="flex items-center gap-2 px-4 py-2 text-sm text-destructive hover:bg-destructive/10 w-full text-left">
                                                    <i data-lucide="x-circle" class="w-4 h-4 shrink-0"></i>
                                                    <span>Cancelar Pedido</span>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Footer: Valor e Data -->
                        <div class="pt-2 border-t border-border">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-[10px] text-muted-foreground uppercase tracking-wide">Valor</p>
                                    <p class="text-sm font-bold text-primary mt-0.5">R$
                                        {{ number_format($totalAmount, 2, ',', '.') }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-[10px] text-muted-foreground uppercase tracking-wide">Data</p>
                                    <p class="text-xs font-medium text-foreground mt-0.5">{{ $formattedDate }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-center text-muted-foreground py-12">
                        <div class="flex flex-col items-center gap-2">
                            <i data-lucide="inbox" class="w-12 h-12 opacity-20"></i>
                            <p class="text-sm">Nenhum pedido encontrado</p>
                        </div>
                    </div>
                @endforelse
                @if($orders->count() > 0)
                @endif

            </div>
        </div>

        <!-- Pagination -->
        @if($orders->hasPages())
            <div class="px-4 sm:px-6 py-3 sm:py-4 border-t border-border bg-muted/20 rounded-b-xl">
                {{ $orders->withQueryString()->links() }}
            </div>
        @endif
    </div>
@endsection

{{-- Modal Nova Encomenda --}}
@include('dashboard.orders.partials.nova-encomenda-modal')

@push('styles')
    <style>
        [x-cloak] {
            display: none !important
        }
    </style>
@endpush

@push('scripts')
    <script>
        window.ordersLiveSearch = function (initialQ, initialStatus) {
            return {
                search: (typeof initialQ === 'string' ? initialQ : '') || '',
                statusFilter: (typeof initialStatus === 'string' ? initialStatus : 'all') || 'all',

                matchesCard(el) {
                    if (!el) return false;

                    const searchLower = this.search.toLowerCase();

                    // Filter by status first
                    const orderStatus = el.dataset.orderStatus || 'all';
                    if (this.statusFilter !== 'all' && orderStatus !== this.statusFilter) {
                        return false;
                    }

                    // Then by search term
                    if (searchLower === '') return true;

                    const searchTerms = [
                        el.dataset.searchId || '',
                        el.dataset.searchCustomer || '',
                        el.dataset.searchTotal || ''
                    ];

                    return searchTerms.some(term => term.toLowerCase().includes(searchLower));
                }
            };
        };

        // Função global para fechar todos os menus
        window.closeAllMenus = function () {
            document.querySelectorAll('[x-data]').forEach(function (el) {
                try {
                    const alpineData = Alpine.$data(el);
                    if (alpineData && typeof alpineData.open !== 'undefined') {
                        alpineData.open = false;
                    }
                } catch (e) {
                    // Ignorar elementos sem Alpine
                }
            });
        };

        // Botão Nova Encomenda (desktop e mobile)
        document.addEventListener('DOMContentLoaded', function () {
            const btnDesktop = document.getElementById('btn-nova-encomenda');
            const btnMobile = document.getElementById('btn-nova-encomenda-mobile');

            const openNovaEncomenda = function () {
                window.dispatchEvent(new CustomEvent('open-nova-encomenda', {
                    detail: { userInitiated: true }
                }));
            };

            if (btnDesktop) {
                btnDesktop.addEventListener('click', openNovaEncomenda);
            }

            if (btnMobile) {
                btnMobile.addEventListener('click', openNovaEncomenda);
            }

            // Inicializar ícones Lucide
            if (window.lucide) {
                lucide.createIcons();
            }
        });

        // Função global para solicitar impressão
        window.requestPrint = async function (orderId, orderNumber, event, isCheckReceipt = false) {
            if (event) {
                event.preventDefault();
                event.stopPropagation();
            }

            console.log('🟢 requestPrint chamado:', { orderId, orderNumber, isCheckReceipt });

            // Mostrar feedback visual
            const btnElement = event?.target?.closest('button');
            let originalHTML = null;
            if (btnElement) {
                originalHTML = btnElement.innerHTML;
                btnElement.disabled = true;
                btnElement.innerHTML = '<i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i><span>Carregando...</span>';
                if (window.lucide) lucide.createIcons();
            }

            try {
                // Rotas de API para solicitar impressão na fila (servidor de impressão)
                const url = isCheckReceipt
                    ? `/orders/${orderId}/request-print-check`
                    : `/orders/${orderId}/request-print`;

                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                if (!response.ok) {
                    throw new Error('Erro ao solicitar impressão');
                }

                const data = await response.json();

                if (data.success) {
                    alert('✅ Pedido enviado para a fila de impressão!');
                } else {
                    alert(data.message || 'Erro ao enviar para impressão');
                }
            } catch (error) {
                console.error('Erro:', error);
                alert('Erro ao solicitar impressão. Tente novamente.');
            } finally {
                if (btnElement && originalHTML) {
                    btnElement.disabled = false;
                    btnElement.innerHTML = originalHTML;
                    if (window.lucide) lucide.createIcons();
                }
            }
        };

        // Função para abrir Maps/Waze
        window.openMaps = function (address, neighborhood, city) {
            const fullAddress = `${address}, ${neighborhood}, ${city}`.trim();
            const encodedAddress = encodeURIComponent(fullAddress);

            // Detectar se é iOS
            const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;

            if (isIOS) {
                // iOS - Tentar abrir Waze, depois Google Maps, depois Apple Maps
                window.location.href = `waze://?q=${encodedAddress}`;
                setTimeout(() => {
                    window.location.href = `comgooglemaps://?q=${encodedAddress}`;
                }, 500);
                setTimeout(() => {
                    window.location.href = `maps://maps.apple.com/?q=${encodedAddress}`;
                }, 1000);
            } else {
                // Android/Desktop - Abrir Google Maps no navegador
                window.open(`https://www.google.com/maps/search/?api=1&query=${encodedAddress}`, '_blank');
            }
        };

        // Função para abrir modal de programação de entrega
        window.openScheduleModal = async function (orderId, orderNumber) {
            const modal = document.getElementById('schedule-delivery-modal');
            if (!modal) {
                console.error('❌ Modal não encontrado');
                return;
            }

            document.getElementById('schedule-order-id').value = orderId;
            document.getElementById('schedule-order-number').textContent = orderNumber;

            // Buscar slots disponíveis
            try {
                console.log('🔍 Buscando slots de entrega...');
                const response = await fetch('/orders/delivery-slots', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                console.log('👉 Response status:', response.status);

                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('❌ Erro na resposta:', errorText);
                    throw new Error(`HTTP ${response.status}: ${errorText}`);
                }

                const data = await response.json();
                console.log('✅ Dados recebidos:', data);

                const dateSelect = document.getElementById('schedule-delivery-date');
                const slotSelect = document.getElementById('schedule-delivery-slot');

                dateSelect.innerHTML = '<option value="">Selecione uma data</option>';
                slotSelect.innerHTML = '<option value="">Selecione primeiro uma data</option>';
                slotSelect.disabled = true;

                if (data.slots && data.slots.length > 0) {
                    console.log(`📅 ${data.slots.length} datas disponíveis`);
                    data.slots.forEach(dateData => {
                        const option = document.createElement('option');
                        option.value = dateData.date;
                        option.textContent = `${dateData.label} (${dateData.day_name})`;
                        option.dataset.slots = JSON.stringify(dateData.slots);
                        dateSelect.appendChild(option);
                    });
                } else {
                    console.warn('⚠️ Nenhum slot disponível');
                    alert('Nenhum horário disponível no momento. Configure os horários de entrega nas configurações.');
                }

                modal.classList.remove('hidden');
            } catch (error) {
                console.error('❌ Erro detalhado:', error);
                alert('Erro ao carregar horários disponíveis: ' + error.message);
            }
        };

        window.closeScheduleModal = function () {
            const modal = document.getElementById('schedule-delivery-modal');
            if (modal) {
                modal.classList.add('hidden');
            }
        };

        window.submitScheduleDelivery = async function () {
            const orderId = document.getElementById('schedule-order-id').value;
            const date = document.getElementById('schedule-delivery-date').value;
            const slot = document.getElementById('schedule-delivery-slot').value;
            const offHours = document.getElementById('schedule-off-hours').checked;
            const genericDate = document.getElementById('schedule-generic-date').value;
            const genericTime = document.getElementById('schedule-generic-time').value;

            let scheduledDeliveryAt = null;

            if (offHours && genericDate && genericTime) {
                scheduledDeliveryAt = `${genericDate} ${genericTime}:00`;
            } else if (!offHours && slot) {
                scheduledDeliveryAt = slot + ':00';
            } else {
                alert('Por favor, selecione uma data e horário');
                return;
            }

            try {
                const response = await fetch(`/orders/${orderId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        _method: 'PUT',
                        scheduled_delivery_at: scheduledDeliveryAt
                    })
                });

                if (response.ok) {
                    closeScheduleModal();
                    location.reload();
                } else {
                    alert('Erro ao agendar entrega');
                }
            } catch (error) {
                console.error('Erro:', error);
                alert('Erro ao agendar entrega');
            }
        };

        // Função para abrir modal de observação de entrega
        window.openDeliveryNoteModal = function (orderId, orderNumber) {
            const modal = document.getElementById('delivery-note-modal');
            if (!modal) return;

            document.getElementById('note-order-id').value = orderId;
            document.getElementById('note-order-number').textContent = orderNumber;
            document.getElementById('delivery-note-text').value = '';

            modal.classList.remove('hidden');
        };

        window.closeDeliveryNoteModal = function () {
            const modal = document.getElementById('delivery-note-modal');
            if (modal) {
                modal.classList.add('hidden');
            }
        };

        window.submitDeliveryNote = async function () {
            const orderId = document.getElementById('note-order-id').value;
            const note = document.getElementById('delivery-note-text').value.trim();

            if (!note) {
                alert('Por favor, digite uma observação');
                return;
            }

            try {
                const response = await fetch(`/orders/${orderId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        delivery_instructions: note
                    })
                });

                if (response.ok) {
                    closeDeliveryNoteModal();
                    alert('Observação salva com sucesso!');
                } else {
                    alert('Erro ao salvar observação');
                }
            } catch (error) {
                console.error('Erro:', error);
                alert('Erro ao salvar observação');
            }
        };

        // Event listener para mudança de data no modal de agendamento
        document.addEventListener('DOMContentLoaded', function () {
            const dateSelect = document.getElementById('schedule-delivery-date');
            const slotSelect = document.getElementById('schedule-delivery-slot');
            const offHoursCheckbox = document.getElementById('schedule-off-hours');
            const slotsContainer = document.getElementById('slots-container');
            const genericContainer = document.getElementById('generic-container');

            if (dateSelect) {
                dateSelect.addEventListener('change', function () {
                    const selectedOption = this.options[this.selectedIndex];
                    if (!selectedOption || !selectedOption.dataset.slots) {
                        slotSelect.innerHTML = '<option value="">Selecione primeiro uma data</option>';
                        slotSelect.disabled = true;
                        return;
                    }

                    const slots = JSON.parse(selectedOption.dataset.slots);
                    slotSelect.innerHTML = '<option value="">Selecione um horário</option>';

                    slots.forEach(slot => {
                        if (slot.available > 0) {
                            const option = document.createElement('option');
                            option.value = slot.value;
                            option.textContent = slot.label;
                            slotSelect.appendChild(option);
                        }
                    });

                    slotSelect.disabled = false;
                });
            }

            if (offHoursCheckbox && slotsContainer && genericContainer) {
                offHoursCheckbox.addEventListener('change', function () {
                    if (this.checked) {
                        slotsContainer.classList.add('hidden');
                        genericContainer.classList.remove('hidden');
                    } else {
                        slotsContainer.classList.remove('hidden');
                        genericContainer.classList.add('hidden');
                    }
                });
            }
        });
    </script>
@endpush

{{-- Modal: Programar Entrega --}}
<div id="schedule-delivery-modal" class="hidden fixed inset-0 z-[200] flex items-center justify-center bg-black/50">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-semibold">Programar Entrega</h3>
                <button type="button" onclick="closeScheduleModal()" class="text-gray-400 hover:text-gray-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <input type="hidden" id="schedule-order-id">
            <p class="text-sm text-muted-foreground mb-4">Pedido #<span id="schedule-order-number"></span></p>

            <div class="space-y-4">
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="schedule-off-hours" class="h-4 w-4 text-primary rounded border-input">
                    <label for="schedule-off-hours" class="text-sm font-medium">Programar fora dos horários
                        disponíveis</label>
                </div>

                <div id="slots-container" class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium mb-2">Data *</label>
                        <select id="schedule-delivery-date"
                            class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm">
                            <option value="">Selecione uma data</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">Horário *</label>
                        <select id="schedule-delivery-slot"
                            class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm" disabled>
                            <option value="">Selecione primeiro uma data</option>
                        </select>
                    </div>
                </div>

                <div id="generic-container" class="hidden space-y-3">
                    <div>
                        <label class="block text-sm font-medium mb-2">Data *</label>
                        <input type="date" id="schedule-generic-date"
                            class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">Horário *</label>
                        <input type="time" id="schedule-generic-time"
                            class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm">
                    </div>
                </div>

                <div class="flex gap-2 pt-2">
                    <button type="button" onclick="closeScheduleModal()"
                        class="flex-1 px-4 py-2 rounded-lg border border-input hover:bg-muted text-sm font-medium">Cancelar</button>
                    <button type="button" onclick="submitScheduleDelivery()"
                        class="flex-1 px-4 py-2 rounded-lg bg-primary text-primary-foreground hover:bg-primary/90 text-sm font-medium">Agendar</button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal: Observação da Entrega --}}
<div id="delivery-note-modal" class="hidden fixed inset-0 z-[200] flex items-center justify-center bg-black/50">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-semibold">Observação da Entrega</h3>
                <button type="button" onclick="closeDeliveryNoteModal()" class="text-gray-400 hover:text-gray-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <input type="hidden" id="note-order-id">
            <p class="text-sm text-muted-foreground mb-4">Pedido #<span id="note-order-number"></span></p>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-2">Observação para notificação</label>
                    <textarea id="delivery-note-text" rows="4"
                        class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm"
                        placeholder="Ex.: Chegaremos em 10 minutos..."></textarea>
                    <p class="text-xs text-muted-foreground mt-1">Esta mensagem será enviada junto com a notificação de
                        status</p>
                </div>

                <div class="flex gap-2">
                    <button type="button" onclick="closeDeliveryNoteModal()"
                        class="flex-1 px-4 py-2 rounded-lg border border-input hover:bg-muted text-sm font-medium">Cancelar</button>
                    <button type="button" onclick="submitDeliveryNote()"
                        class="flex-1 px-4 py-2 rounded-lg bg-primary text-primary-foreground hover:bg-primary/90 text-sm font-medium">Salvar</button>
                </div>
            </div>
        </div>
    </div>
</div>