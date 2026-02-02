@extends('dashboard.layouts.app')

@section('page_title', 'Entregas')
@section('page_subtitle', 'Gerenciamento de entregas')

@push('styles')
    <style>
        /* Estilos específicos para a página de entregas */
        .view-btn {
            @apply px-4 py-2 rounded-lg text-sm font-semibold transition-all;
        }

        .view-btn.active {
            @apply bg-white text-foreground shadow-sm border border-gray-200;
        }

        .view-btn.inactive {
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

            /* Garantir que o card não extrapole */
            .order-card {
                min-width: 0;
            }
        }
    </style>
@endpush

@section('content')
    <div class="bg-card rounded-xl border border-border animate-fade-in max-w-full" id="deliveries-page"
        x-data="deliveriesLiveSearch('{{ request('q') ?? '' }}', '{{ request('status') ?? 'all' }}')">
        <!-- Card Header: Busca, Filtros e Botão -->
        <div class="p-3 sm:p-4 md:p-6 border-b border-border overflow-visible">
            <form method="GET" action="{{ route('dashboard.deliveries.index') }}" class="flex flex-col gap-3">
                <!-- Desktop: Busca, Filtro e Botões na mesma linha -->
                <div class="hidden lg:flex items-center gap-3">
                    <!-- Barra de Busca -->
                    <div class="relative flex-1">
                        <i data-lucide="search"
                            class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground pointer-events-none"></i>
                        <input type="text" name="q" x-model="search"
                            @input.debounce.500ms="$event.target.form && $event.target.form.submit()"
                            placeholder="Buscar por cliente, número do pedido..."
                            class="form-input pl-10 h-10 bg-muted/30 border-transparent focus:bg-white transition-all text-sm rounded-lg w-full"
                            autocomplete="off">
                    </div>

                    <!-- Filtro Status -->
                    <select name="status" x-model="statusFilter" @change="$event.target.form && $event.target.form.submit()"
                        class="h-10 rounded-lg border border-input bg-muted/30 text-sm px-3 focus:ring-2 focus:ring-primary/20 focus:border-primary w-[160px] shrink-0">
                        <option value="all">Todos Status</option>
                        <option value="pending">Pendente</option>
                        <option value="out_for_delivery">Em Trânsito</option>
                        <option value="delivered">Entregue</option>
                    </select>

                    <!-- Botões -->
                    <a href="{{ route('dashboard.deliveries.index') }}"
                        class="h-10 px-4 rounded-lg text-sm font-medium gap-2 bg-muted/30 border border-input hover:bg-muted inline-flex items-center justify-center shrink-0"
                        title="Limpar filtros">
                        <i data-lucide="eraser" class="w-4 h-4 shrink-0"></i>
                        <span>Limpar</span>
                    </a>

                    <a href="{{ route('dashboard.orders.index') }}"
                        class="btn-primary gap-2 h-10 px-4 rounded-lg shadow-sm inline-flex items-center justify-center shrink-0">
                        <i data-lucide="plus" class="h-4 w-4 text-white"></i>
                        <span class="font-bold text-white text-sm">Nova Entrega</span>
                    </a>
                </div>

                <!-- Mobile: Layout empilhado -->
                <div class="flex lg:hidden flex-col gap-3">
                    <!-- Barra de Busca -->
                    <div class="relative w-full">
                        <i data-lucide="search"
                            class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground pointer-events-none"></i>
                        <input type="text" name="q" x-model="search"
                            @input.debounce.500ms="$event.target.form && $event.target.form.submit()"
                            placeholder="Buscar por cliente, número do pedido..."
                            class="form-input pl-10 h-10 bg-muted/30 border-transparent focus:bg-white transition-all text-sm rounded-lg w-full"
                            autocomplete="off">
                    </div>

                    <!-- Filtro Status -->
                    <select name="status" x-model="statusFilter" @change="$event.target.form && $event.target.form.submit()"
                        class="h-10 rounded-lg border border-input bg-muted/30 text-sm px-3 focus:ring-2 focus:ring-primary/20 focus:border-primary w-full">
                        <option value="all">Todos Status</option>
                        <option value="pending">Pendente</option>
                        <option value="out_for_delivery">Em Trânsito</option>
                        <option value="delivered">Entregue</option>
                    </select>

                    <!-- Botões Limpar e Nova Entrega lado a lado, 50/50 -->
                    <div class="flex items-center gap-2 w-full">
                        <a href="{{ route('dashboard.deliveries.index') }}"
                            class="h-10 px-3 rounded-lg text-sm font-medium gap-1.5 bg-muted/30 border border-input hover:bg-muted inline-flex items-center justify-center flex-1"
                            title="Limpar filtros">
                            <i data-lucide="eraser" class="w-4 h-4 shrink-0"></i>
                            <span>Limpar</span>
                        </a>

                        <a href="{{ route('dashboard.orders.index') }}"
                            class="btn-primary gap-2 h-10 px-4 rounded-lg shadow-sm inline-flex items-center justify-center flex-1">
                            <i data-lucide="plus" class="h-4 w-4 text-white"></i>
                            <span class="font-bold text-white text-sm">Nova Entrega</span>
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Deliveries Grid -->
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

                        // Data formatada - data de entrega agendada
                        $deliveryDate = $order->scheduled_delivery_at ?? $order->created_at;
                        $formattedDate = $deliveryDate->format('d/m/Y');

                        // Valor total
                        $totalAmount = $order->final_amount ?? $order->total_amount ?? 0;

                        // Status
                        $statusMap = [
                            'confirmed' => ['label' => 'Confirmado', 'class' => 'bg-yellow-100 text-yellow-800', 'icon' => 'check-circle'],
                            'preparing' => ['label' => 'Preparando', 'class' => 'bg-blue-100 text-blue-800', 'icon' => 'chef-hat'],
                            'ready' => ['label' => 'Pronto', 'class' => 'bg-indigo-100 text-indigo-800', 'icon' => 'package-check'],
                            'out_for_delivery' => ['label' => 'Em Trânsito', 'class' => 'bg-blue-100 text-blue-800', 'icon' => 'truck'],
                            'delivered' => ['label' => 'Entregue', 'class' => 'bg-green-100 text-green-800', 'icon' => 'check'],
                        ];
                        $statusData = $statusMap[$order->status] ?? ['label' => ucfirst($order->status), 'class' => 'bg-gray-100 text-gray-800', 'icon' => 'circle'];

                        $searchCustomer = mb_strtolower($customerName, 'UTF-8');
                        $searchOrder = mb_strtolower($orderNumberDisplay, 'UTF-8');
                        $searchStatus = mb_strtolower($statusData['label'], 'UTF-8');
                    @endphp

                    <div class="order-card bg-white border border-border rounded-xl p-3 sm:p-4 hover:shadow-md transition-all"
                        data-search-customer="{{ $searchCustomer }}" data-search-order="{{ $searchOrder }}"
                        data-search-status="{{ $searchStatus }}" data-order-status="{{ $order->status }}"
                        x-show="matchesCard($el)">
                        <!-- Header: Nome, Status, Ações -->
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
                                                                                }" @click.outside="open = false"
                                    class="relative">
                                    <button type="button" x-ref="trigger" @click.stop="toggle()"
                                        class="inline-flex items-center justify-center h-8 w-8 sm:h-9 sm:w-9 rounded-md hover:bg-muted transition-colors text-muted-foreground hover:text-foreground"
                                        :class="{ 'bg-muted': open }" title="Ações">
                                        <i data-lucide="more-vertical" class="h-4 w-4"></i>
                                    </button>

                                    {{-- Menu Dropdown com position fixed via style inline dinâmico --}}
                                    <div x-show="open" x-cloak x-transition x-ref="dropdown"
                                        class="fixed w-56 bg-white rounded-lg shadow-xl border border-border z-[9999] py-1 overflow-y-auto"
                                        style="display: none;">
                                        <a href="{{ route('dashboard.orders.show', $order->id) }}"
                                            class="flex items-center gap-2 px-4 py-2 text-sm text-foreground hover:bg-muted w-full text-left">
                                            <i data-lucide="eye" class="w-4 h-4 shrink-0"></i>
                                            <span>Ver detalhes</span>
                                        </a>

                                        @if($order->delivery_type === 'delivery' && $order->delivery_address)
                                            @if(!$order->tracking_enabled)
                                                {{-- Botão: Iniciar Rastreamento --}}
                                                <button type="button" onclick="startTracking({{ $order->id }})"
                                                    class="flex items-center gap-2 px-4 py-2 text-sm text-green-600 hover:bg-green-50 w-full text-left">
                                                    <i data-lucide="radio" class="w-4 h-4 shrink-0"></i>
                                                    <span>Iniciar Rastreamento</span>
                                                </button>
                                            @else
                                                {{-- Botão: Parar Rastreamento --}}
                                                <button type="button" onclick="stopTracking({{ $order->id }})"
                                                    class="flex items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50 w-full text-left">
                                                    <i data-lucide="radio-tower" class="w-4 h-4 shrink-0"></i>
                                                    <span>Parar Rastreamento</span>
                                                </button>

                                                {{-- Botão: Ver no Mapa --}}
                                                <button type="button" onclick="openTrackingMap('{{ $order->tracking_token }}')"
                                                    class="flex items-center gap-2 px-4 py-2 text-sm text-blue-600 hover:bg-blue-50 w-full text-left">
                                                    <i data-lucide="map" class="w-4 h-4 shrink-0"></i>
                                                    <span>Ver no Mapa</span>
                                                </button>
                                            @endif
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

                                        @if(in_array($order->status, ['confirmed', 'preparing', 'ready']))
                                            <div class="border-t border-border my-1"></div>
                                            <button type="button" @click="$dispatch('open-start-delivery', { 
                                                                                                    id: {{ $order->id }}, 
                                                                                                    number: '{{ $orderNumberDisplay }}', 
                                                                                                    customer: '{{ addslashes($customerName) }}', 
                                                                                                    address: '{{ addslashes($order->delivery_address ?? 'Sem endereço') }}' 
                                                                                                })"
                                                class="flex items-center gap-2 px-4 py-2 text-sm text-blue-600 hover:bg-blue-50 w-full text-left">
                                                <i data-lucide="truck" class="w-4 h-4 shrink-0"></i>
                                                <span>Iniciar Entrega</span>
                                            </button>
                                        @elseif($order->status === 'out_for_delivery')
                                            <div class="border-t border-border my-1"></div>
                                            <button type="button" @click="$dispatch('open-finish-delivery', { 
                                                                                                    id: {{ $order->id }}, 
                                                                                                    number: '{{ $orderNumberDisplay }}' 
                                                                                                })"
                                                class="flex items-center gap-2 px-4 py-2 text-sm text-green-600 hover:bg-green-50 w-full text-left">
                                                <i data-lucide="check-circle" class="w-4 h-4 shrink-0"></i>
                                                <span>Confirmar Entrega</span>
                                            </button>
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
                                    <p class="text-[10px] text-muted-foreground uppercase tracking-wide">Entrega</p>
                                    <p class="text-xs font-medium text-foreground mt-0.5">{{ $formattedDate }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-center text-muted-foreground py-12">
                        <div class="flex flex-col items-center gap-2">
                            <i data-lucide="inbox" class="w-12 h-12 opacity-20"></i>
                            <p class="text-sm">Nenhuma entrega encontrada</p>
                        </div>
                    </div>
                @endforelse
                @if($orders->count() > 0)
                    <div class="delivery-filter-no-results col-span-full text-center text-muted-foreground py-8"
                        x-show="search && showNoResults" x-cloak x-transition>
                        <div class="flex flex-col items-center gap-2">
                            <i data-lucide="search-x" class="w-10 h-10 opacity-40"></i>
                            <p class="text-sm">Nenhuma entrega encontrada para "<span x-text="search"></span>"</p>
                        </div>
                    </div>
                @endif

                <!-- Pagination -->
                @if($orders->hasPages())
                    <div class="px-4 sm:px-6 py-3 sm:py-4 border-t border-border bg-muted/20">
                        {{ $orders->withQueryString()->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
    {{-- Modals de Início/Fim de Entrega --}}
    <div x-data="{
                        startModalOpen: false,
                        finishModalOpen: false,
                        deliveryData: { id: null, number: '', customer: '', address: '' },
                        message: '',
                        waze: false,
                        finishNote: '',

                        init() {
                            window.addEventListener('open-start-delivery', (e) => {
                                this.deliveryData = e.detail;
                                this.message = ''; 
                                this.waze = false;
                                this.startModalOpen = true;
                            });

                            window.addEventListener('open-finish-delivery', (e) => {
                                this.deliveryData = e.detail;
                                this.finishNote = '';
                                this.finishModalOpen = true;
                            });
                        },

                        async startDelivery() {
                            if(!this.deliveryData.id) return;

                            // 1. Solicitar permissão GPS IMEDIATAMENTE antes de qualquer fetch
                            if (navigator.geolocation) {
                                try {
                                    await new Promise((resolve, reject) => {
                                        navigator.geolocation.getCurrentPosition(resolve, reject, { timeout: 5000 });
                                    });
                                } catch (e) {
                                    alert('⚠️ Precisamos do seu GPS para rastrear a entrega.\n\nPor favor, permita o acesso à localização e tente novamente.');
                                    return; // Abortar se negar
                                }
                            } else {
                                alert('❌ GPS não suportado neste navegador.');
                                return;
                            }

                            try {
                                // Iniciar Rastreamento no backend
                                const trackResp = await fetch(`/deliveries/${this.deliveryData.id}/tracking/start`, {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').content,
                                        'Accept': 'application/json',
                                        'Content-Type': 'application/json'
                                    }
                                });

                                if(!trackResp.ok) throw new Error('Erro ao iniciar rastreamento');

                                // Iniciar loop de GPS (função global)
                                if(typeof startGPSTracking === 'function') {
                                    startGPSTracking(this.deliveryData.id);
                                }

                                // Abrir Waze se solicitado
                                if(this.waze) {
                                     const encodedAddr = encodeURIComponent(this.deliveryData.address);
                                     window.open(`https://waze.com/ul?q=${encodedAddr}&navigate=yes`, '_blank');
                                }

                                // Aguardar um pouco para garantir que o GPS iniciou antes de submeter
                                await new Promise(r => setTimeout(r, 500));

                                // Salvar flag de rastreamento para retomar após reload
                                localStorage.setItem('active_tracking_order', this.deliveryData.id);

                                // Submit Form
                                const form = document.createElement('form');
                                form.method = 'POST';
                                // Usar rota padrão de orders para garantir compatibilidade
                                form.action = `/orders/${this.deliveryData.id}/status`;

                                const csrf = document.createElement('input');
                                csrf.type = 'hidden';
                                csrf.name = '_token';
                                csrf.value = document.querySelector('meta[name=\'csrf-token\']').content;
                                form.appendChild(csrf);

                                const status = document.createElement('input');
                                status.type = 'hidden';
                                status.name = 'status';
                                status.value = 'out_for_delivery';
                                form.appendChild(status);

                                document.body.appendChild(form);
                                form.submit();

                            } catch(e) {
                                console.error(e);
                                // Mostrar erro detalhado se for resposta de rede
                                alert('Erro ao iniciar entrega: ' + (e.message || 'Erro desconhecido'));
                            }
                        },

                        async finishDelivery() {
                            if(!this.deliveryData.id) return;

                            try {
                                // Parar Rastreamento
                                await fetch(`/deliveries/${this.deliveryData.id}/tracking/stop`, {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').content,
                                        'Accept': 'application/json'
                                    }
                                });

                                if(typeof stopGPSTracking === 'function') {
                                    stopGPSTracking();
                                }

                                // Salvar Nota se houver
                                if(this.finishNote.trim()) {
                                     await fetch(`/orders/${this.deliveryData.id}`, {
                                        method: 'PUT',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').content,
                                            'Accept': 'application/json'
                                        },
                                        body: JSON.stringify({ delivery_instructions: this.finishNote })
                                    });
                                }

                                // Submit Form
                                const form = document.createElement('form');
                                form.method = 'POST';
                                form.action = `/deliveries/${this.deliveryData.id}/status`;

                                const csrf = document.createElement('input');
                                csrf.type = 'hidden';
                                csrf.name = '_token';
                                csrf.value = document.querySelector('meta[name=\'csrf-token\']').content;
                                form.appendChild(csrf);

                                const status = document.createElement('input');
                                status.type = 'hidden';
                                status.name = 'status';
                                status.value = 'delivered';
                                form.appendChild(status);

                                document.body.appendChild(form);
                                form.submit();

                            } catch(e) {
                                console.error(e);
                                alert('Erro ao finalizar entrega: ' + e.message);
                            }
                        }
                    }" x-cloak>

        {{-- Modal Start --}}
        <div x-show="startModalOpen" class="fixed inset-0 z-[200] flex items-center justify-center bg-black/50 p-4"
            x-transition.opacity style="display: none;">
            <div @click.outside="startModalOpen = false"
                class="bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden animate-in fade-in zoom-in duration-200">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xl font-semibold flex items-center gap-2">
                            <i data-lucide="bike" class="w-6 h-6 text-primary"></i>
                            Iniciar Entrega
                        </h3>
                        <button type="button" @click="startModalOpen = false" class="text-gray-400 hover:text-gray-600">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

                    <div class="bg-blue-50 p-4 rounded-lg mb-4 border border-blue-100">
                        <p class="text-sm text-blue-800 font-medium">Pedido #<span x-text="deliveryData.number"></span></p>
                        <p class="text-sm text-blue-600 mt-1" x-text="deliveryData.customer"></p>
                        <p class="text-xs text-blue-500 mt-1 truncate" x-text="deliveryData.address"></p>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">Mensagem (Opcional)</label>
                            <textarea x-model="message" rows="2"
                                class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm"
                                placeholder="Ex: Saindo agora!"></textarea>
                        </div>
                        <div class="flex items-center gap-2 border p-3 rounded-lg hover:bg-gray-50 cursor-pointer"
                            @click="waze = !waze">
                            <input type="checkbox" x-model="waze" class="h-4 w-4 text-primary rounded border-input">
                            <label class="text-sm font-medium cursor-pointer flex-1 user-select-none">Abrir no
                                Waze/Maps</label>
                            <i data-lucide="map-pin" class="w-4 h-4 text-muted-foreground"></i>
                        </div>
                        <div class="flex gap-2 pt-2">
                            <button type="button" @click="startModalOpen = false"
                                class="flex-1 px-4 py-2 rounded-lg border border-input hover:bg-muted text-sm font-medium">Cancelar</button>
                            <button type="button" @click="startDelivery()"
                                class="flex-1 px-4 py-2 rounded-lg bg-primary text-primary-foreground hover:bg-primary/90 text-sm font-medium shadow-sm">Iniciar
                                🚀</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal Finish --}}
        <div x-show="finishModalOpen" class="fixed inset-0 z-[200] flex items-center justify-center bg-black/50 p-4"
            x-transition.opacity style="display: none;">
            <div @click.outside="finishModalOpen = false"
                class="bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden animate-in fade-in zoom-in duration-200">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xl font-semibold text-green-700 flex items-center gap-2">
                            <i data-lucide="check-circle" class="w-6 h-6"></i>
                            Finalizar Entrega
                        </h3>
                        <button type="button" @click="finishModalOpen = false" class="text-gray-400 hover:text-gray-600">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>
                    <p class="text-sm text-gray-600 mb-4">Confirmar entrega do pedido #<span x-text="deliveryData.number"
                            class="font-bold"></span>?</p>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">Observação (Opcional)</label>
                            <textarea x-model="finishNote" rows="3"
                                class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm"
                                placeholder="Entregue para..."></textarea>
                        </div>
                        <div class="flex gap-2">
                            <button type="button" @click="finishModalOpen = false"
                                class="flex-1 px-4 py-2 rounded-lg border border-input hover:bg-muted text-sm font-medium">Cancelar</button>
                            <button type="button" @click="finishDelivery()"
                                class="flex-1 px-4 py-2 rounded-lg bg-green-600 text-white hover:bg-green-700 text-sm font-medium shadow-sm">Confirmar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        [x-cloak] {
            display: none !important
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('alpine:init', function () {
            Alpine.data('deliveriesLiveSearch', function (initialQ, initialStatus) {
                return {
                    search: (typeof initialQ === 'string' ? initialQ : '') || '',
                    statusFilter: (typeof initialStatus === 'string' ? initialStatus : 'all') || 'all',
                    showNoResults: false,

                    init: function () {
                        var self = this;
                        function updateNoResults() {
                            self.$nextTick(function () {
                                var root = document.getElementById('deliveries-page');
                                var cards = root ? root.querySelectorAll('.order-card') : [];
                                var visible = 0;
                                cards.forEach(function (el) {
                                    if (self.matchesCard(el)) visible++;
                                });
                                self.showNoResults = self.search.trim() !== '' && visible === 0;
                            });
                        }
                        this.$watch('search', updateNoResults);
                        this.$watch('statusFilter', updateNoResults);
                        updateNoResults();
                    },

                    matchesCard: function (el) {
                        var q = this.search.trim().toLowerCase();
                        var statusFilter = this.statusFilter;

                        // Filtro de status
                        var orderStatus = el.getAttribute('data-order-status') || '';
                        if (statusFilter === 'pending') {
                            if (orderStatus !== 'confirmed' && orderStatus !== 'preparing' && orderStatus !== 'ready') return false;
                        } else if (statusFilter !== 'all') {
                            if (orderStatus !== statusFilter) return false;
                        }

                        // Se não há busca de texto, mostrar
                        if (!q) return true;

                        // Busca de texto
                        var customer = (el.getAttribute('data-search-customer') || '').toLowerCase();
                        var order = (el.getAttribute('data-search-order') || '').toLowerCase();
                        var status = (el.getAttribute('data-search-status') || '').toLowerCase();

                        var customerNorm = customer.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                        var orderNorm = order.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                        var statusNorm = status.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                        var qNorm = q.normalize('NFD').replace(/[\u0300-\u036f]/g, '');

                        if (customer.includes(q) || customerNorm.includes(qNorm)) return true;
                        if (order.includes(q) || orderNorm.includes(qNorm)) return true;
                        if (status.includes(q) || statusNorm.includes(qNorm)) return true;

                        return false;
                    }
                };
            });
        });

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

        document.addEventListener('DOMContentLoaded', function () {
            // Inicializar ícones Lucide
            if (window.lucide) {
                lucide.createIcons();
            }
        });

        // Incluir as mesmas funções da página de pedidos
        window.openMaps = function (address, neighborhood, city) {
            const fullAddress = `${address}, ${neighborhood}, ${city}`.trim();
            const encodedAddress = encodeURIComponent(fullAddress);
            const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
            if (isIOS) {
                window.location.href = `waze://?q=${encodedAddress}`;
                setTimeout(() => { window.location.href = `comgooglemaps://?q=${encodedAddress}`; }, 500);
                setTimeout(() => { window.location.href = `maps://maps.apple.com/?q=${encodedAddress}`; }, 1000);
            } else {
                window.open(`https://www.google.com/maps/search/?api=1&query=${encodedAddress}`, '_blank');
            }
        };

        // RASTREAMENTO DE ENTREGAS
        window.startTracking = async function (orderId) {
            if (!confirm('📍 Iniciar rastreamento desta entrega?\n\nO GPS do seu dispositivo será compartilhado em tempo real.')) return;

            try {
                const response = await fetch(`/dashboard/deliveries/${orderId}/tracking/start`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    const trackingUrl = data.tracking_url;

                    // Copiar link
                    try {
                        await navigator.clipboard.writeText(trackingUrl);
                        alert('✅ Rastreamento iniciado!\n\n🔗 Link copiado para área de transferência:\n' + trackingUrl);
                    } catch (e) {
                        alert('✅ Rastreamento iniciado!\n\n🔗 Link de acompanhamento:\n' + trackingUrl);
                    }

                    // Iniciar envio automático de GPS
                    startGPSTracking(orderId);

                    location.reload();
                }
            } catch (error) {
                alert('❌ Erro ao iniciar rastreamento');
                console.error(error);
            }
        };

        window.stopTracking = async function (orderId) {
            if (!confirm('Parar rastreamento desta entrega?')) return;

            try {
                const response = await fetch(`/dashboard/deliveries/${orderId}/tracking/stop`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                if (response.ok) {
                    alert('✅ Rastreamento parado');
                    stopGPSTracking();
                    location.reload();
                }
            } catch (error) {
                alert('❌ Erro ao parar rastreamento');
                console.error(error);
            }
        };

        window.openTrackingMap = function (token) {
            window.open('/tracking/' + token, '_blank');
        };

        let gpsTrackingInterval = null;

        function startGPSTracking(orderId) {
            if (!navigator.geolocation) {
                console.error('GPS não disponível');
                return;
            }

            // Enviar localização a cada 5 segundos
            gpsTrackingInterval = setInterval(function () {
                navigator.geolocation.getCurrentPosition(
                    async function (position) {
                        try {
                            await fetch(`/dashboard/deliveries/${orderId}/tracking/update`, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({
                                    latitude: position.coords.latitude,
                                    longitude: position.coords.longitude,
                                    accuracy: position.coords.accuracy,
                                    speed: position.coords.speed,
                                    heading: position.coords.heading
                                })
                            });
                            console.log('📍 GPS atualizado');
                        } catch (error) {
                            console.error('❌ Erro ao enviar GPS:', error);
                        }
                    },
                    function (error) {
                        console.error('❌ Erro ao obter GPS:', error);
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: 5000,
                        maximumAge: 0
                    }
                );
            }, 5000);
        }

        function stopGPSTracking() {
            if (gpsTrackingInterval) {
                clearInterval(gpsTrackingInterval);
                gpsTrackingInterval = null;
                console.log('⏹️ GPS tracking parado');
            }
        }

        // FUNÇÃO: Iniciar entrega COM rastreamento automático
        window.startDeliveryWithTracking = async function (orderId) {
            if (!confirm('🚚 Iniciar entrega?\n\n✅ O rastreamento GPS será ativado automaticamente\n📍 O link será enviado ao cliente via WhatsApp')) {
                return;
            }

            try {
                console.log('🔵 Iniciando rastreamento para pedido:', orderId);

                // 1. Iniciar rastreamento primeiro
                const trackingResponse = await fetch(`/deliveries/${orderId}/tracking/start`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                });

                console.log('🔵 Response status:', trackingResponse.status);

                if (!trackingResponse.ok) {
                    const errorText = await trackingResponse.text();
                    console.error('❌ Erro HTTP:', trackingResponse.status, errorText);
                    throw new Error(`HTTP ${trackingResponse.status}: ${errorText.substring(0, 100)}`);
                }

                const trackingData = await trackingResponse.json();
                console.log('✅ Tracking data:', trackingData);

                if (!trackingData.success) {
                    throw new Error(trackingData.error || 'Erro ao iniciar rastreamento');
                }

                console.log('✅ Rastreamento iniciado:', trackingData.tracking_url);

                // 2. Mudar status para out_for_delivery
                const statusForm = document.createElement('form');
                statusForm.method = 'POST';
                statusForm.action = `/deliveries/${orderId}/status`;

                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = document.querySelector('meta[name="csrf-token"]').content;

                const statusInput = document.createElement('input');
                statusInput.type = 'hidden';
                statusInput.name = 'status';
                statusInput.value = 'out_for_delivery';

                statusForm.appendChild(csrfInput);
                statusForm.appendChild(statusInput);
                document.body.appendChild(statusForm);

                // 3. Iniciar envio de GPS
                startGPSTracking(orderId);

                // 4. Submeter formulário (recarregará a página)
                statusForm.submit();

            } catch (error) {
                console.error('❌ Erro completo:', error);
                alert('❌ Erro ao iniciar entrega:\n\n' + error.message + '\n\nVerifique o console para mais detalhes.');
            }
        };

        // FUNÇÃO: Iniciar rastreamento manualmente
        window.startTracking = async function (orderId) {
            try {
                const response = await fetch(`/deliveries/${orderId}/tracking/start`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    alert('✅ Rastreamento iniciado!');
                    startGPSTracking(orderId);
                    location.reload();
                } else {
                    alert('❌ Erro: ' + (data.error || 'Erro desconhecido'));
                }
            } catch (error) {
                console.error('❌ Erro:', error);
                alert('❌ Erro ao iniciar rastreamento');
            }
        };

        // FUNÇÃO: Parar rastreamento
        window.stopTracking = async function (orderId) {
            if (!confirm('⏹️ Parar rastreamento GPS?')) return;

            try {
                const response = await fetch(`/deliveries/${orderId}/tracking/stop`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    stopGPSTracking();
                    alert('✅ Rastreamento parado');
                    location.reload();
                } else {
                    alert('❌ Erro ao parar rastreamento');
                }
            } catch (error) {
                console.error('❌ Erro:', error);
                alert('❌ Erro ao parar rastreamento');
            }
        };

        // FUNÇÃO: Abrir mapa de rastreamento
        window.openTrackingMap = function (token) {
            if (!token) {
                alert('❌ Token de rastreamento não encontrado');
                return;
            }
            window.open(`/tracking/${token}`, '_blank');
        };

        // VARIÁVEL GLOBAL: Intervalo de GPS
        let gpsTrackingInterval = null;
        window.gpsErrorAlerted = false;

        // FUNÇÃO: Iniciar captura de GPS
        function startGPSTracking(orderId) {
            if (gpsTrackingInterval) return; // Já está rodando

            if (!navigator.geolocation) {
                alert('❌ Erro: Seu navegador não suporta geolocalização.');
                return;
            }

            console.log('📍 Iniciando captura de GPS a cada 5s...');

            // Tentar uma primeira leitura pra garantir permissão
            navigator.geolocation.getCurrentPosition(() => { }, (err) => {
                alert('⚠️ Atenção: Não foi possível obter sua localização. Verifique se o GPS está ativado e a permissão concedida.\n\nErro: ' + err.message);
            });

            gpsTrackingInterval = setInterval(function () {
                navigator.geolocation.getCurrentPosition(
                    async function (position) {
                        window.gpsErrorAlerted = false; // Resetar flag de erro se sucesso
                        try {
                            const response = await fetch(`/deliveries/${orderId}/tracking/update`, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({
                                    latitude: position.coords.latitude,
                                    longitude: position.coords.longitude,
                                    accuracy: position.coords.accuracy,
                                    speed: position.coords.speed,
                                    heading: position.coords.heading
                                })
                            });

                            if (response.ok) {
                                console.log('✅ GPS atualizado:', position.coords.latitude, position.coords.longitude);
                            } else {
                                console.error('❌ Erro ao enviar GPS:', response.status);
                            }
                        } catch (error) {
                            console.error('❌ Erro na requisição GPS:', error);
                        }
                    },
                    function (error) {
                        console.error('❌ Erro ao obter GPS:', error.message);
                        if (!window.gpsErrorAlerted) {
                            // Alertar apenas uma vez para não travar a tela
                            // alert('❌ Falha no GPS: ' + error.message);
                            window.gpsErrorAlerted = true;
                        }
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: 5000,
                        maximumAge: 0
                    }
                );
            }, 5000); // A cada 5 segundos
        }

        // FUNÇÃO: Parar captura de GPS
        function stopGPSTracking() {
            if (gpsTrackingInterval) {
                clearInterval(gpsTrackingInterval);
                gpsTrackingInterval = null;
                console.log('⏹️ Captura de GPS parada');
            }
        }

        window.openScheduleModal = async function (orderId, orderNumber) {
            const modal = document.getElementById('schedule-delivery-modal');
            if (!modal) {
                console.error('❌ Modal não encontrado');
                return;
            }
            document.getElementById('schedule-order-id').value = orderId;
            document.getElementById('schedule-order-number').textContent = orderNumber;
            try {
                console.log('🔍 Buscando slots de entrega...');
                const response = await fetch('/orders/delivery-slots', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
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
            if (modal) modal.classList.add('hidden');
        };

        window.submitScheduleDelivery = async function () {
            const orderId = document.getElementById('schedule-order-id').value;
            const offHours = document.getElementById('schedule-off-hours').checked;
            const slot = document.getElementById('schedule-delivery-slot').value;
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
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ scheduled_delivery_at: scheduledDeliveryAt })
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
            if (modal) modal.classList.add('hidden');
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
                    body: JSON.stringify({ delivery_instructions: note })
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

        // Event listeners
        document.addEventListener('DOMContentLoaded', function () {
            // Verificar localStorage para retomar rastreamento
            const activeTrackingOrder = localStorage.getItem('active_tracking_order');
            if (activeTrackingOrder && typeof startGPSTracking === 'function') {
                console.log('🔄 Retomando rastreamento para pedido:', activeTrackingOrder);
                // Pequeno delay para garantir que tudo carregou
                setTimeout(() => {
                    startGPSTracking(activeTrackingOrder);
                }, 1000);
            }

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