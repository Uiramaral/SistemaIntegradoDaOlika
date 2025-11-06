@extends('dashboard.layouts.app')

@section('title', 'Pedidos - OLIKA Dashboard')

@section('content')
<div class="space-y-6 animate-in fade-in duration-500">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold tracking-tight">Pedidos</h1>
            <p class="text-muted-foreground">Gerencie todos os pedidos do restaurante</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('dashboard.orders.printerMonitor') }}" class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-printer">
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                    <path d="M6 9V3a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v6"></path>
                    <rect x="6" y="14" width="12" height="8"></rect>
                </svg>
                Monitor de Impressão
            </a>
            <button class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2 gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-plus h-4 w-4">
                    <path d="M5 12h14"></path>
                    <path d="M12 5v14"></path>
                </svg>
                Novo Pedido
            </button>
        </div>
    </div>
    <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
        <div class="flex flex-col space-y-1.5 p-6">
            <form method="GET" action="{{ route('dashboard.orders.index') }}" class="flex flex-col sm:flex-row gap-4">
                <div class="relative flex-1">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-search absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.3-4.3"></path>
                    </svg>
                    <input type="search" name="q" value="{{ request('q') }}" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm pl-10" placeholder="Buscar por cliente, número do pedido...">
                </div>
                @if(request('q'))
                    <a href="{{ route('dashboard.orders.index') }}" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2">Limpar</a>
                @endif
            </form>
        </div>
        <div class="p-6 pt-0">
            <div class="overflow-x-auto">
                <div class="relative w-full overflow-auto">
                    <table class="w-full caption-bottom text-sm">
                        <thead class="[&_tr]:border-b">
                            <tr class="border-b transition-colors data-[state=selected]:bg-muted hover:bg-muted/50">
                                <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground [&:has([role=checkbox])]:pr-0">#</th>
                                <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground [&:has([role=checkbox])]:pr-0">Cliente</th>
                                <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground [&:has([role=checkbox])]:pr-0">Total</th>
                                <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground [&:has([role=checkbox])]:pr-0">Status</th>
                                <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground [&:has([role=checkbox])]:pr-0">Pagamento</th>
                                <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground [&:has([role=checkbox])]:pr-0">Quando</th>
                                <th class="h-12 px-4 align-middle font-medium text-muted-foreground [&:has([role=checkbox])]:pr-0 text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="[&_tr:last-child]:border-0">
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
                                <tr class="border-b transition-colors data-[state=selected]:bg-muted hover:bg-muted/50">
                                    <td class="p-4 align-middle [&:has([role=checkbox])]:pr-0 font-medium">{{ $order->order_number }}</td>
                                    <td class="p-4 align-middle [&:has([role=checkbox])]:pr-0">
                                        <div>
                                            <div class="font-medium">{{ $order->customer->name ?? 'Cliente não informado' }}</div>
                                            @if($order->customer && $order->customer->phone)
                                                <div class="text-xs text-muted-foreground">{{ $order->customer->phone }}</div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="p-4 align-middle [&:has([role=checkbox])]:pr-0 font-semibold">R$ {{ number_format($order->final_amount ?? $order->total_amount ?? 0, 2, ',', '.') }}</td>
                                    <td class="p-4 align-middle [&:has([role=checkbox])]:pr-0">
                                        <div class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 border-transparent {{ $statusColor }}">{{ $statusText }}</div>
                                    </td>
                                    <td class="p-4 align-middle [&:has([role=checkbox])]:pr-0">
                                        <div class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors {{ $paymentColor }}">{{ $paymentText }}</div>
                                    </td>
                                    <td class="p-4 align-middle [&:has([role=checkbox])]:pr-0 text-muted-foreground">
                                        @php
                                            try {
                                                $diff = $order->created_at->diffForHumans();
                                            } catch (\Exception $e) {
                                                $diff = $order->created_at->format('d/m/Y H:i');
                                            }
                                        @endphp
                                        {{ $diff }}
                                        <div class="text-xs">{{ $order->created_at->format('d/m/Y H:i') }}</div>
                                    </td>
                                    <td class="p-4 align-middle [&:has([role=checkbox])]:pr-0 text-right">
                                        <div class="flex gap-2 justify-end">
                                            <button type="button" class="btn-print-receipt-direct inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-9 px-3" title="Imprimir Recibo Fiscal" data-order-id="{{ $order->id }}">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-printer">
                                                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                                                    <path d="M6 9V3a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v6"></path>
                                                    <rect x="6" y="14" width="12" height="8"></rect>
                                                </svg>
                                            </button>
                                            <a href="{{ route('dashboard.orders.show', $order->id) }}" class="inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 hover:bg-accent hover:text-accent-foreground h-9 rounded-md px-3">Ver detalhes</a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="p-8 text-center text-muted-foreground">
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
                {{ $orders->links() }}
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
    let lastOrderId = {{ $orders->isNotEmpty() ? $orders->first()->id : 0 }};
    let lastOrderCreatedAt = '{{ $orders->isNotEmpty() ? $orders->first()->created_at->toIso8601String() : '' }}';
    let pollingInterval = null;
    let isPolling = false;
    let knownOrderIds = new Set();
    let orderDataMap = new Map(); // Armazenar dados dos pedidos para comparar mudanças
    
    // Inicializar IDs conhecidos e dados
    @foreach($orders as $order)
        knownOrderIds.add({{ $order->id }});
        orderDataMap.set({{ $order->id }}, {
            status: '{{ $order->status }}',
            payment_status: '{{ $order->payment_status }}',
            updated_at: '{{ $order->updated_at->toIso8601String() }}'
        });
    @endforeach
    
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
                const emptyRow = tbody.querySelector('td[colspan="7"]');
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
        
        // Observar mudanças na tabela para reconectar botões após atualizações dinâmicas
        const tableBody = document.querySelector('#orders-tbody');
        if (tableBody) {
            const observer = new MutationObserver(function(mutations) {
                setupPrintButtons();
            });
            
            observer.observe(tableBody, {
                childList: true,
                subtree: true
            });
        }
    });
</script>
@endsection
