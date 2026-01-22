@extends('dashboard.layouts.app')

@section('page_title', 'Pedidos')
@section('page_subtitle', 'Acompanhe uma visão detalhada das métricas e resultados')

@section('page_actions')
    <div class="flex items-center gap-2">
        <button class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
            </svg>
        </button>
        <button class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"></path>
            </svg>
        </button>
    </div>
    <a href="{{ route('dashboard.pdv.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors text-sm font-medium">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M5 12h14"></path>
            <path d="M12 5v14"></path>
        </svg>
        + Agendar pedido
    </a>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Busca e Filtros -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <form method="GET" action="{{ route('dashboard.orders.index') }}" class="flex flex-col sm:flex-row gap-4">
            <!-- Campo de Busca -->
            <div class="flex-1">
                <input type="text" 
                       name="q" 
                       value="{{ request('q') }}" 
                       placeholder="Buscar por cliente ou número do pedido..." 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
            </div>
            <!-- Manter filtro de status na URL -->
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif
            <!-- Botões -->
            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors text-sm font-medium">
                    Buscar
                </button>
                @if(request('q') || request('status'))
                    <a href="{{ route('dashboard.orders.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors text-sm font-medium">
                        Limpar
                    </a>
                @endif
            </div>
        </form>
    </div>
    
    <!-- Filtros de Status -->
    <div class="flex flex-wrap items-center gap-2 mb-4">
        <a href="{{ route('dashboard.orders.index', array_merge(request()->except('status'), ['status' => 'all'])) }}" 
           class="px-4 py-2 text-sm font-medium rounded-lg transition-colors {{ (request('status', 'all') === 'all') ? 'bg-primary text-white' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50' }}">
            Todos
        </a>
        <a href="{{ route('dashboard.orders.index', array_merge(request()->except('status'), ['status' => 'active'])) }}" 
           class="px-4 py-2 text-sm font-medium rounded-lg transition-colors {{ request('status') === 'active' ? 'bg-primary text-white' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50' }}">
            Ativos
        </a>
        <a href="{{ route('dashboard.orders.index', array_merge(request()->except('status'), ['status' => 'pending'])) }}" 
           class="px-4 py-2 text-sm font-medium rounded-lg transition-colors {{ request('status') === 'pending' ? 'bg-primary text-white' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50' }}">
            Pendentes
        </a>
        <a href="{{ route('dashboard.orders.index', array_merge(request()->except('status'), ['status' => 'confirmed'])) }}" 
           class="px-4 py-2 text-sm font-medium rounded-lg transition-colors {{ request('status') === 'confirmed' ? 'bg-primary text-white' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50' }}">
            Confirmados
        </a>
        <a href="{{ route('dashboard.orders.index', array_merge(request()->except('status'), ['status' => 'preparing'])) }}" 
           class="px-4 py-2 text-sm font-medium rounded-lg transition-colors {{ request('status') === 'preparing' ? 'bg-primary text-white' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50' }}">
            Em Preparo
        </a>
        <a href="{{ route('dashboard.orders.index', array_merge(request()->except('status'), ['status' => 'ready'])) }}" 
           class="px-4 py-2 text-sm font-medium rounded-lg transition-colors {{ request('status') === 'ready' ? 'bg-primary text-white' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50' }}">
            Prontos
        </a>
        <a href="{{ route('dashboard.orders.index', array_merge(request()->except('status'), ['status' => 'delivered'])) }}" 
           class="px-4 py-2 text-sm font-medium rounded-lg transition-colors {{ request('status') === 'delivered' ? 'bg-primary text-white' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50' }}">
            Entregues
        </a>
        <a href="{{ route('dashboard.orders.index', array_merge(request()->except('status'), ['status' => 'cancelled'])) }}" 
           class="px-4 py-2 text-sm font-medium rounded-lg transition-colors {{ request('status') === 'cancelled' ? 'bg-primary text-white' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50' }}">
            Cancelados
        </a>
    </div>
    
    <div class="rounded-lg border bg-white shadow-sm border-gray-200">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                            <tr class="border-b">
                                <th class="h-12 px-4 text-left align-middle text-xs font-medium text-gray-500 uppercase tracking-wider">CLIENTE</th>
                                <th class="h-12 px-4 text-left align-middle text-xs font-medium text-gray-500 uppercase tracking-wider">DATA</th>
                                <th class="h-12 px-4 text-left align-middle text-xs font-medium text-gray-500 uppercase tracking-wider">CATEGORIA</th>
                                <th class="h-12 px-4 text-left align-middle text-xs font-medium text-gray-500 uppercase tracking-wider">VALOR</th>
                                <th class="h-12 px-4 text-left align-middle text-xs font-medium text-gray-500 uppercase tracking-wider">STATUS</th>
                                <th class="h-12 px-4 text-left align-middle text-xs font-medium text-gray-500 uppercase tracking-wider">AÇÕES</th>
                            </tr>
                        </thead>
                        <tbody class="[&_tr:last-child]:border-0" id="orders-tbody">
                            @php
                                $orders = $orders ?? collect();
                            @endphp
                            @forelse($orders as $order)
                                @php
                                    $statusColors = [
                                        'pending' => 'bg-muted text-muted-foreground',
                                        'confirmed' => 'bg-primary text-primary-foreground',
                                        'preparing' => 'bg-warning text-warning-foreground',
                                        'ready' => 'bg-primary/80 text-primary-foreground',
                                        'delivered' => 'bg-success text-success-foreground',
                                        'cancelled' => 'bg-destructive text-destructive-foreground',
                                    ];
                                    $statusLabel = [
                                        'pending' => 'Pendente',
                                        'confirmed' => 'Confirmado',
                                        'preparing' => 'Em Preparo',
                                        'ready' => 'Pronto',
                                        'delivered' => 'Entregue',
                                        'cancelled' => 'Cancelado',
                                    ];
                                    $paymentStatusColors = [
                                        'pending' => 'bg-muted text-muted-foreground',
                                        'paid' => 'bg-success text-success-foreground',
                                        'failed' => 'bg-destructive text-destructive-foreground',
                                        'refunded' => 'bg-warning text-warning-foreground',
                                    ];
                                    $paymentStatusLabel = [
                                        'pending' => 'Pendente',
                                        'paid' => 'Pago',
                                        'failed' => 'Falhou',
                                        'refunded' => 'Reembolsado',
                                    ];
                                    $statusColor = $statusColors[$order->status] ?? 'bg-muted text-muted-foreground';
                                    $statusText = $statusLabel[$order->status] ?? ucfirst($order->status);
                                    $paymentColor = $paymentStatusColors[$order->payment_status] ?? 'bg-muted text-muted-foreground';
                                    $paymentText = $paymentStatusLabel[$order->payment_status] ?? ucfirst($order->payment_status);
                                @endphp
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="font-semibold text-gray-900">{{ $order->customer->name ?? 'Cliente não informado' }}</div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                        {{ $order->created_at->format('d/m/y, H:i') }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">Padaria</td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span class="font-semibold text-gray-900">R$ {{ number_format($order->final_amount ?? $order->total_amount ?? 0, 2, ',', '.') }}</span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                                            {{ $statusText }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <a href="{{ route('dashboard.orders.show', $order->id) }}" class="text-gray-400 hover:text-gray-600" title="Ver detalhes">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="p-8 text-center text-gray-500">
                                        Nenhum pedido encontrado.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @if(isset($orders) && method_exists($orders, 'links'))
            <div class="mt-4 flex justify-center">
                {{ $orders->onEachSide(1)->links('vendor.pagination.compact') }}
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
(function() {
    'use strict';
    
    // Configurações
    const POLL_INTERVAL = 5000; // 5 segundos
    const ANIMATION_DURATION = 500;
    
    // Estado da atualização
    let lastOrderId = {{ ($orders && $orders->isNotEmpty()) ? $orders->first()->id : 0 }};
    let lastOrderCreatedAt = '{{ ($orders && $orders->isNotEmpty()) ? $orders->first()->created_at->toIso8601String() : '' }}';
    let pollingInterval = null;
    let isPolling = false;
    let knownOrderIds = new Set();
    let orderDataMap = new Map(); // Armazenar dados dos pedidos para comparar mudanças
    
    // Inicializar IDs conhecidos e dados
    @if($orders && $orders->isNotEmpty())
        @foreach($orders as $order)
            knownOrderIds.add({{ $order->id }});
            orderDataMap.set({{ $order->id }}, {
                status: '{{ $order->status }}',
                payment_status: '{{ $order->payment_status }}',
                updated_at: '{{ $order->updated_at->toIso8601String() }}'
            });
        @endforeach
    @endif
    
    // Função para criar uma linha de pedido
    function createOrderRow(order) {
        const row = document.createElement('tr');
        row.className = 'border-b transition-colors data-[state=selected]:bg-muted hover:bg-muted/50 new-order-highlight';
        row.dataset.orderId = order.id;
        
        row.innerHTML = `
            <td class="p-4 align-middle [&:has([role=checkbox])]:pr-0 font-medium">${order.order_number}</td>
            <td class="p-4 align-middle [&:has([role=checkbox])]:pr-0">
                <div>
                    <div class="font-medium">${escapeHtml(order.customer_name)}</div>
                    ${order.customer_phone ? `<div class="text-xs text-muted-foreground">${escapeHtml(order.customer_phone)}</div>` : ''}
                </div>
            </td>
            <td class="p-4 align-middle [&:has([role=checkbox])]:pr-0 font-semibold">R$ ${formatMoney(order.total_amount)}</td>
            <td class="p-4 align-middle [&:has([role=checkbox])]:pr-0">
                <div class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 border-transparent ${order.status_color}">${escapeHtml(order.status_label)}</div>
            </td>
            <td class="p-4 align-middle [&:has([role=checkbox])]:pr-0">
                <div class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors ${order.payment_color}">${escapeHtml(order.payment_label)}</div>
            </td>
            <td class="p-4 align-middle [&:has([role=checkbox])]:pr-0 text-muted-foreground">
                ${order.created_at_human}
                <div class="text-xs">${order.created_at_formatted}</div>
            </td>
            <td class="p-4 align-middle [&:has([role=checkbox])]:pr-0 text-right">
                <div class="flex gap-2 justify-end">
                    <button type="button" class="btn-print-receipt-direct inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-9 px-3" title="Imprimir Recibo Fiscal" data-order-id="${order.id}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-printer">
                            <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                            <path d="M6 9V3a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v6"></path>
                            <rect x="6" y="14" width="12" height="8"></rect>
                        </svg>
                    </button>
                    <a href="${order.show_url}" class="inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 hover:bg-accent hover:text-accent-foreground h-9 rounded-md px-3">Ver detalhes</a>
                </div>
            </td>
        `;
        
        return row;
    }
    
    // Função para escapar HTML
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Função para formatar dinheiro
    function formatMoney(value) {
        return parseFloat(value).toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }
    
    // Função para buscar novos pedidos
    async function fetchNewOrders() {
        if (isPolling) return;
        
        isPolling = true;
        
        try {
            // Coletar IDs dos pedidos exibidos na página
            const displayedOrderIds = Array.from(knownOrderIds);
            
            const params = new URLSearchParams({
                last_order_id: lastOrderId,
                last_order_created_at: lastOrderCreatedAt,
            });
            
            // Adicionar IDs conhecidos para verificar atualizações
            displayedOrderIds.forEach(id => {
                params.append('known_order_ids[]', id);
            });
            
            // Adicionar filtros da página se houver
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('q')) {
                params.append('q', urlParams.get('q'));
            }
            if (urlParams.has('status')) {
                params.append('status', urlParams.get('status'));
            }
            
            const response = await fetch('{{ route("dashboard.orders.newOrders") }}?' + params.toString(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                credentials: 'same-origin'
            });
            
            if (!response.ok) {
                throw new Error('Erro ao buscar novos pedidos');
            }
            
            const data = await response.json();
            
            if (data.success) {
                const tbody = document.querySelector('tbody');
                if (!tbody) {
                    isPolling = false;
                    return;
                }
                
                // Log para debug (remover em produção se necessário)
                if (data.updated_orders && data.updated_orders.length > 0) {
                    console.log('Pedidos atualizados detectados:', data.updated_orders.length);
                }
                
                let newOrdersCount = 0;
                let updatedOrdersCount = 0;
                
                // Processar novos pedidos
                if (data.orders && data.orders.length > 0) {
                    const trulyNewOrders = data.orders.filter(order => !knownOrderIds.has(order.id));
                    
                    if (trulyNewOrders.length > 0) {
                        // Adicionar novos pedidos no topo da tabela
                        trulyNewOrders.reverse().forEach(order => {
                            const row = createOrderRow(order);
                            tbody.insertBefore(row, tbody.firstChild);
                            knownOrderIds.add(order.id);
                            orderDataMap.set(order.id, {
                                status: order.status,
                                payment_status: order.payment_status,
                                updated_at: order.updated_at
                            });
                            newOrdersCount++;
                        });
                        
                        // Atualizar referências
                        if (trulyNewOrders.length > 0) {
                            const newestOrder = trulyNewOrders[trulyNewOrders.length - 1];
                            lastOrderId = Math.max(lastOrderId, newestOrder.id);
                            lastOrderCreatedAt = newestOrder.created_at;
                        }
                        
                        // Mostrar notificação
                        if (newOrdersCount > 0) {
                            showNotification(`${newOrdersCount} novo${newOrdersCount > 1 ? 's' : ''} pedido${newOrdersCount > 1 ? 's' : ''}!`);
                        }
                    }
                }
                
                // Processar pedidos atualizados
                if (data.updated_orders && data.updated_orders.length > 0) {
                    data.updated_orders.forEach(order => {
                        const existingRow = tbody.querySelector(`tr[data-order-id="${order.id}"]`);
                        const oldData = orderDataMap.get(order.id);
                        
                        // Verificar se houve mudança - sempre atualizar se houver diferença
                        const hasChanged = !oldData || (
                            oldData.status !== order.status ||
                            oldData.payment_status !== order.payment_status ||
                            oldData.updated_at !== order.updated_at
                        );
                        
                        if (hasChanged) {
                            if (existingRow) {
                                // Atualizar linha existente
                                const newRow = createOrderRow(order);
                                newRow.classList.add('updated-order-highlight');
                                existingRow.replaceWith(newRow);
                                updatedOrdersCount++;
                                
                                // Animação de atualização
                                setTimeout(() => {
                                    newRow.style.animation = 'highlightUpdatedOrder 1s ease-out';
                                    setTimeout(() => {
                                        newRow.classList.remove('updated-order-highlight');
                                        newRow.style.animation = '';
                                    }, 1000);
                                }, 100);
                                
                                // Reconfigurar botões após atualizar
                                setupPrintButtons();
                            }
                            
                            // Atualizar dados no mapa (sempre atualizar, mesmo se não existir)
                            orderDataMap.set(order.id, {
                                status: order.status,
                                payment_status: order.payment_status,
                                updated_at: order.updated_at
                            });
                        }
                    });
                }
                
                // Remover linha vazia se existir
                const emptyRow = tbody.querySelector('td[colspan="6"]');
                if (emptyRow && emptyRow.closest('tr')) {
                    emptyRow.closest('tr').remove();
                }
                
                // Animação de destaque para novos pedidos
                const newRows = tbody.querySelectorAll('.new-order-highlight');
                newRows.forEach((row, index) => {
                    setTimeout(() => {
                        row.style.animation = 'highlightNewOrder 2s ease-out';
                        setTimeout(() => {
                            row.classList.remove('new-order-highlight');
                            row.style.animation = '';
                        }, 2000);
                    }, index * 100);
                });

                document.dispatchEvent(new Event('dashboard:table-refresh'));
            }
        } catch (error) {
            console.error('Erro ao buscar novos pedidos:', error);
        } finally {
            isPolling = false;
        }
    }
    
    // Função para mostrar notificação
    function showNotification(message) {
        // Criar elemento de notificação
        const notification = document.createElement('div');
        notification.className = 'fixed top-4 right-4 bg-primary text-primary-foreground px-4 py-3 rounded-lg shadow-lg z-50 animate-in slide-in-from-right';
        notification.textContent = message;
        notification.style.animation = 'slideInRight 0.3s ease-out';
        
        document.body.appendChild(notification);
        
        // Remover após 3 segundos
        setTimeout(() => {
            notification.style.animation = 'slideOutRight 0.3s ease-out';
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 3000);
    }
    
    // Iniciar polling quando a página carregar
    function startPolling() {
        if (pollingInterval) {
            clearInterval(pollingInterval);
        }
        
        // Primeira verificação após 2 segundos
        setTimeout(fetchNewOrders, 2000);
        
        // Depois verificar a cada X segundos
        pollingInterval = setInterval(fetchNewOrders, POLL_INTERVAL);
    }
    
    // Parar polling quando a página perder foco (economizar recursos)
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            if (pollingInterval) {
                clearInterval(pollingInterval);
                pollingInterval = null;
            }
        } else {
            if (!pollingInterval) {
                startPolling();
            }
        }
    });
    
    // Iniciar quando DOM estiver pronto
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', startPolling);
    } else {
        startPolling();
    }
    
    // Adicionar estilos CSS para animação
    const style = document.createElement('style');
    style.textContent = `
        @keyframes highlightNewOrder {
            0% { background-color: rgba(34, 197, 94, 0.3); }
            100% { background-color: transparent; }
        }
        @keyframes highlightUpdatedOrder {
            0% { background-color: rgba(59, 130, 246, 0.3); }
            50% { background-color: rgba(59, 130, 246, 0.5); }
            100% { background-color: transparent; }
        }
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
        .new-order-highlight {
            animation: highlightNewOrder 2s ease-out;
        }
    `;
    document.head.appendChild(style);
})();
</script>
@endpush

<!-- QZ Tray Script para impressão direta -->
<script src="https://cdn.jsdelivr.net/npm/qz-tray@2.2/qz-tray.min.js"></script>
<script>
    // Função para verificar se QZ Tray está conectado
    function isQZTrayConnected() {
        try {
            return typeof qz !== 'undefined' && 
                   qz !== null && 
                   qz.websocket !== null && 
                   qz.websocket.isActive();
        } catch (error) {
            return false;
        }
    }

    // Conectar ao QZ Tray
    async function connectQZTray() {
        try {
            if (typeof qz === 'undefined' || qz === null) {
                throw new Error('QZ Tray não está carregado. Verifique se o QZ Tray está instalado e rodando.');
            }
            
            if (isQZTrayConnected()) {
                console.log('✅ QZ Tray já estava conectado');
                return true;
            }
            
            await qz.websocket.connect();
            
            if (isQZTrayConnected()) {
                console.log('✅ QZ Tray conectado com sucesso');
                return true;
            } else {
                throw new Error('Falha ao verificar conexão após tentativa de conexão');
            }
        } catch (error) {
            console.error('❌ Erro ao conectar QZ Tray:', error);
            return false;
        }
    }

    // Detectar se é dispositivo móvel
    function isMobileDevice() {
        return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ||
               (window.innerWidth <= 768);
    }

    // Imprimir recibo diretamente
    async function printReceiptDirect(orderId) {
        // Se for mobile, adicionar à fila de impressão
        if (isMobileDevice()) {
            const clickedBtn = document.querySelector(`.btn-print-receipt-direct[data-order-id="${orderId}"]`);
            if (clickedBtn) {
                const originalHTML = clickedBtn.innerHTML;
                clickedBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-loader-2 animate-spin"><path d="M21 12a9 9 0 1 1-6.219-8.56"></path></svg>';
                clickedBtn.disabled = true;
            }
            
            try {
                const response = await fetch(`/dashboard/orders/${orderId}/request-print`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    if (clickedBtn) {
                        clickedBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check"><path d="M20 6 9 17l-5-5"></path></svg>';
                        clickedBtn.classList.add('bg-success');
                        setTimeout(() => {
                            clickedBtn.innerHTML = originalHTML;
                            clickedBtn.disabled = false;
                            clickedBtn.classList.remove('bg-success');
                        }, 2000);
                    }
                    alert('✅ Pedido adicionado à fila de impressão!\n\nO recibo será impresso automaticamente no desktop.');
                } else {
                    throw new Error(data.message || 'Erro ao adicionar à fila');
                }
            } catch (error) {
                console.error('❌ Erro ao solicitar impressão:', error);
                alert('❌ Erro ao solicitar impressão: ' + (error.message || 'Erro desconhecido'));
                if (clickedBtn) {
                    clickedBtn.innerHTML = originalHTML;
                    clickedBtn.disabled = false;
                }
            }
            return;
        }
        
        // Desktop: imprimir diretamente via QZ Tray
        const PRINTER_NAME = "EPSON TM-T20X";
        
        if (typeof qz === 'undefined') {
            alert('❌ QZ Tray não está carregado.\n\nPor favor, instale e inicie o QZ Tray antes de imprimir.');
            return;
        }
        
        if (!isQZTrayConnected()) {
            try {
                const connected = await connectQZTray();
                if (!connected) {
                    alert('❌ Não foi possível conectar ao QZ Tray.\n\nCertifique-se de que o QZ Tray está instalado e rodando.');
                    return;
                }
            } catch (error) {
                alert('❌ Erro ao conectar ao QZ Tray:\n\n' + error.message);
                return;
            }
        }
        
        try {
            const printers = await qz.printers.find();
            if (!printers || printers.length === 0) {
                alert('Nenhuma impressora encontrada.');
                return;
            }
            
            // Buscar impressora EPSON TM-20X
            const printer = printers.find(p => 
                p.toUpperCase().includes('EPSON') && 
                (p.toUpperCase().includes('TM-20') || p.toUpperCase().includes('TM-T20'))
            ) || printers[0];
            
            if (!printer) {
                alert(`❌ Impressora "${PRINTER_NAME}" não encontrada.\nVerifique se está conectada.`);
                return;
            }
            
            console.log('🖨️ Usando impressora:', printer);
            
            const response = await fetch(`/dashboard/orders/${orderId}/fiscal-receipt/escpos`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });
            
            if (!response.ok) {
                throw new Error(`Erro ao buscar dados: ${response.status}`);
            }
            
            const orderData = await response.json();
            if (!orderData.success || !orderData.data) {
                throw new Error('Dados inválidos do servidor.');
            }
            
            console.log('📦 Base64 recebido (ESC/POS), tamanho:', orderData.data.length);
            
            const printConfig = qz.configs.create(printer);
            
            // Enviar para impressão
            await qz.print(printConfig, [{
                type: 'raw',
                format: 'base64',
                data: orderData.data
            }]);
            
            console.log('✅ Recibo enviado para impressora:', printer);
            
            // Mostrar feedback visual
            const clickedBtn = document.querySelector(`.btn-print-receipt-direct[data-order-id="${orderId}"]`);
            if (clickedBtn) {
                const originalHTML = clickedBtn.innerHTML;
                clickedBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check"><path d="M20 6 9 17l-5-5"></path></svg>';
                clickedBtn.disabled = true;
                clickedBtn.classList.add('bg-success');
                setTimeout(() => {
                    clickedBtn.innerHTML = originalHTML;
                    clickedBtn.disabled = false;
                    clickedBtn.classList.remove('bg-success');
                }, 2000);
            }
        } catch (error) {
            console.error('❌ Erro ao imprimir:', error);
            alert('❌ Erro ao imprimir: ' + (error.message || 'Erro desconhecido'));
        }
    }

    // Função para configurar botões de impressão (usado em inicialização e após atualizações)
    function setupPrintButtons() {
        const printButtons = document.querySelectorAll('.btn-print-receipt-direct');
        printButtons.forEach(btn => {
            // Remover listeners antigos para evitar duplicação
            const newBtn = btn.cloneNode(true);
            btn.parentNode.replaceChild(newBtn, btn);
            
            newBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const orderId = this.getAttribute('data-order-id');
                if (orderId) {
                    printReceiptDirect(orderId);
                }
            });
        });
    }

    // Botões de impressão direta (inicialização e após atualizações dinâmicas)
    document.addEventListener('DOMContentLoaded', function() {
        setupPrintButtons();
        if (window.applyTableMobileLabels) {
            window.applyTableMobileLabels();
        }
        
        // Observar mudanças na tabela para reconectar botões após atualizações dinâmicas
        const tableBody = document.querySelector('#orders-tbody');
        if (tableBody) {
            const observer = new MutationObserver(function(mutations) {
                setupPrintButtons();
                const table = tableBody.closest('table');
                if (table && window.applyTableMobileLabels) {
                    window.applyTableMobileLabels(table);
                }
            });
            
            observer.observe(tableBody, {
                childList: true,
                subtree: true
            });
        }
    });
</script>
@endsection
