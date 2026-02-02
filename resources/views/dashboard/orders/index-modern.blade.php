@extends('dashboard.layouts.app')

@section('page_title', 'Pedidos')
@section('page_subtitle', 'Veja e gerencie as encomendas dos clientes')

@push('styles')
<style>
    /* Calendário horizontal */
    .calendar-day {
        @apply flex flex-col items-center justify-center min-w-[60px] h-20 rounded-2xl cursor-pointer transition-all;
        @apply bg-white text-gray-600 hover:bg-gray-50;
    }
    .calendar-day.today {
        @apply bg-pink-500 text-white hover:bg-pink-600;
    }
    .calendar-day-month {
        @apply text-xs uppercase font-medium mb-1;
    }
    .calendar-day-number {
        @apply text-2xl font-bold;
    }
    .calendar-day-weekday {
        @apply text-xs mt-0.5;
    }
    
    /* Cards de pedidos */
    .order-card {
        @apply bg-white rounded-2xl p-4 mb-3 border border-gray-100 cursor-pointer transition-all;
        @apply hover:shadow-md;
    }
    .order-card.expanded {
        @apply shadow-lg;
    }
    .order-card-header {
        @apply flex items-center justify-between;
    }
    .order-card-details {
        @apply mt-4 pt-4 border-t border-gray-100 space-y-2;
    }
    
    /* Badges de status */
    .status-badge {
        @apply inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold;
    }
    .status-badge.agendado { @apply bg-blue-100 text-blue-700; }
    .status-badge.confirmado { @apply bg-green-100 text-green-700; }
    .status-badge.em-preparo { @apply bg-orange-100 text-orange-700; }
    .status-badge.pronto { @apply bg-purple-100 text-purple-700; }
    .status-badge.entregue { @apply bg-green-100 text-green-700; }
    
    /* Header com ícone */
    .page-header-icon {
        @apply w-12 h-12 rounded-full flex items-center justify-center;
        @apply bg-gradient-to-br from-pink-500 to-pink-600 text-white;
    }
    
    /* Filtro */
    .filter-button {
        @apply flex items-center justify-between w-full px-4 py-3 rounded-xl border border-gray-200;
        @apply bg-white text-gray-700 hover:bg-gray-50 transition-colors;
    }
    
    /* Seções */
    .section-title {
        @apply text-sm font-medium text-gray-500 uppercase tracking-wider mb-3 mt-6;
    }
    
    /* FAB Button */
    .fab-button {
        @apply fixed bottom-6 right-6 w-14 h-14 rounded-full;
        @apply bg-gradient-to-br from-pink-500 to-pink-600 text-white;
        @apply flex items-center justify-center shadow-lg hover:shadow-xl;
        @apply transition-all hover:scale-110 z-50;
    }
</style>
@endpush

@section('content')
<div class="max-w-4xl mx-auto px-4 py-6 space-y-6">
    <!-- Header -->
    <div class="flex items-center gap-3">
        <div class="page-header-icon">
            <i data-lucide="calendar-check" class="w-6 h-6"></i>
        </div>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Pedidos</h1>
            <p class="text-sm text-gray-600">Veja e gerencie as encomendas dos clientes</p>
        </div>
    </div>

    <!-- Calendário Horizontal -->
    <div class="bg-white rounded-3xl p-4 shadow-sm border border-gray-100">
        <div class="flex items-center justify-between mb-4">
            <button type="button" class="p-2 hover:bg-gray-100 rounded-lg transition-colors" id="prev-week">
                <i data-lucide="chevron-left" class="w-5 h-5 text-gray-600"></i>
            </button>
            <h3 class="text-base font-semibold text-gray-900" id="current-month-year">Janeiro 2026</h3>
            <button type="button" class="p-2 hover:bg-gray-100 rounded-lg transition-colors" id="next-week">
                <i data-lucide="chevron-right" class="w-5 h-5 text-gray-600"></i>
            </button>
        </div>
        
        <div class="flex gap-2 overflow-x-auto pb-2 scrollbar-hide" id="calendar-days">
            <!-- Dias serão injetados via JavaScript -->
        </div>
    </div>

    <!-- Filtro de Status -->
    <button type="button" class="filter-button" onclick="toggleFilterDropdown()">
        <div class="flex items-center gap-2">
            <i data-lucide="filter" class="w-4 h-4 text-pink-500"></i>
            <span class="font-medium">Filtro de Status</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-sm text-gray-500" id="filter-count">5 status selecionados</span>
            <i data-lucide="chevron-down" class="w-4 h-4 text-gray-400"></i>
        </div>
    </button>

    <!-- Dropdown de Filtros (oculto por padrão) -->
    <div id="filter-dropdown" class="hidden bg-white rounded-2xl p-4 shadow-lg border border-gray-100">
        <div class="space-y-2">
            @foreach([
                'pending' => ['label' => 'Aguardando Pagamento', 'color' => 'yellow'],
                'confirmed' => ['label' => 'Confirmado', 'color' => 'green'],
                'preparing' => ['label' => 'Em Preparo', 'color' => 'orange'],
                'ready' => ['label' => 'Pronto', 'color' => 'purple'],
                'delivered' => ['label' => 'Entregue', 'color' => 'green'],
            ] as $status => $data)
            <label class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50 cursor-pointer">
                <input type="checkbox" class="status-filter-checkbox rounded border-gray-300 text-pink-500 focus:ring-pink-500" 
                       value="{{ $status }}" checked>
                <span class="status-badge status-badge-{{ $data['color'] }}">{{ $data['label'] }}</span>
            </label>
            @endforeach
        </div>
    </div>

    <!-- Pedidos Pendentes -->
    <div>
        <h2 class="section-title">PEDIDOS PENDENTES</h2>
        <div class="space-y-3" id="pending-orders">
            @foreach($orders->where('status', '!=', 'delivered')->where('status', '!=', 'cancelled') as $order)
                @php
                    $orderNumberDisplay = $order->order_number ?? '#' . $order->id;
                    if (preg_match('/OLK-(\d+)-/', $orderNumberDisplay, $matches)) {
                        $orderNumberDisplay = $matches[1];
                    }
                    
                    $customerName = $order->customer->name ?? 'Cliente';
                    $nameParts = explode(' ', trim($customerName));
                    if (count($nameParts) > 1) {
                        $customerName = $nameParts[0] . ' ' . end($nameParts);
                    }
                    
                    $orderDate = $order->scheduled_delivery_at ?? $order->created_at;
                    $totalAmount = $order->final_amount ?? $order->total_amount ?? 0;
                    
                    $statusMap = [
                        'pending' => ['label' => 'Agendado', 'class' => 'agendado', 'color' => 'blue'],
                        'confirmed' => ['label' => 'Confirmado', 'class' => 'confirmado', 'color' => 'green'],
                        'preparing' => ['label' => 'Em Preparo', 'class' => 'em-preparo', 'color' => 'orange'],
                        'ready' => ['label' => 'Pronto', 'class' => 'pronto', 'color' => 'purple'],
                    ];
                    $statusData = $statusMap[$order->status] ?? ['label' => 'Pendente', 'class' => 'agendado', 'color' => 'blue'];
                @endphp
                
                <div class="order-card" onclick="toggleOrderCard({{ $order->id }})">
                    <div class="order-card-header">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <h3 class="font-bold text-gray-900">{{ $customerName }}</h3>
                                <span class="text-sm text-gray-500">#{{ $orderNumberDisplay }}</span>
                            </div>
                            <span class="status-badge status-badge-{{ $statusData['class'] }}">
                                {{ $statusData['label'] }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                                <i data-lucide="more-vertical" class="w-5 h-5 text-gray-400"></i>
                            </button>
                            <i data-lucide="chevron-down" class="w-5 h-5 text-gray-400 transition-transform order-chevron-{{ $order->id }}"></i>
                        </div>
                    </div>
                    
                    <!-- Detalhes Expandíveis -->
                    <div class="hidden order-card-details" id="order-details-{{ $order->id }}">
                        <div class="flex items-center gap-2 text-sm text-gray-600">
                            <i data-lucide="clock" class="w-4 h-4"></i>
                            <span>Pedido em: {{ $orderDate->format('d \d\e F \à\s H:i') }}</span>
                        </div>
                        
                        @if($order->scheduled_delivery_at)
                        <div class="flex items-center gap-2 text-sm text-gray-600">
                            <i data-lucide="map-pin" class="w-4 h-4"></i>
                            <span>Retirada: {{ $order->scheduled_delivery_at->format('d \d\e F \à\s H:i') }}</span>
                        </div>
                        @endif
                        
                        @if($order->items && $order->items->count() > 0)
                        <div class="mt-3">
                            @foreach($order->items as $item)
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-gray-700">{{ $item->quantity }}x {{ $item->product_name }}</span>
                                <span class="text-gray-900 font-medium">R$ {{ number_format($item->subtotal, 2, ',', '.') }}</span>
                            </div>
                            @endforeach
                        </div>
                        @endif
                        
                        <div class="flex justify-between items-center pt-3 border-t border-gray-200">
                            <span class="text-xs text-gray-500 uppercase font-medium">Subtotal</span>
                            <span class="text-base text-gray-900">R$ {{ number_format($totalAmount, 2, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center pt-1">
                            <span class="text-sm text-gray-900 font-semibold">Total</span>
                            <span class="text-xl text-pink-600 font-bold">R$ {{ number_format($totalAmount, 2, ',', '.') }}</span>
                        </div>
                        
                        @if($order->payment_method)
                        <div class="flex items-center gap-2 mt-2 p-2 bg-blue-50 rounded-lg">
                            <i data-lucide="credit-card" class="w-4 h-4 text-blue-600"></i>
                            <span class="text-sm text-blue-700">{{ ucfirst($order->payment_method) }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Pedidos Concluídos -->
    @if($orders->whereIn('status', ['delivered', 'cancelled'])->count() > 0)
    <div>
        <h2 class="section-title">PEDIDOS CONCLUÍDOS</h2>
        <div class="space-y-3" id="completed-orders">
            @foreach($orders->whereIn('status', ['delivered', 'cancelled']) as $order)
                @php
                    $orderNumberDisplay = $order->order_number ?? '#' . $order->id;
                    if (preg_match('/OLK-(\d+)-/', $orderNumberDisplay, $matches)) {
                        $orderNumberDisplay = $matches[1];
                    }
                    
                    $customerName = $order->customer->name ?? 'Cliente';
                    $nameParts = explode(' ', trim($customerName));
                    if (count($nameParts) > 1) {
                        $customerName = $nameParts[0] . ' ' . end($nameParts);
                    }
                    
                    $totalAmount = $order->final_amount ?? $order->total_amount ?? 0;
                @endphp
                
                <div class="order-card opacity-75">
                    <div class="order-card-header">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <h3 class="font-bold text-gray-900">{{ $customerName }}</h3>
                                <span class="text-sm text-gray-500">#{{ $orderNumberDisplay }}</span>
                            </div>
                            <span class="status-badge bg-green-100 text-green-700">
                                {{ $order->status === 'delivered' ? 'Entregue' : 'Cancelado' }}
                            </span>
                        </div>
                        <button type="button" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                            <i data-lucide="more-vertical" class="w-5 h-5 text-gray-400"></i>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- FAB Button -->
    <button type="button" class="fab-button" id="btn-nova-encomenda">
        <i data-lucide="plus" class="w-6 h-6"></i>
    </button>
</div>

{{-- Modal Nova Encomenda --}}
@include('dashboard.orders.partials.nova-encomenda-modal')
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar calendário
    initCalendar();
    
    // Inicializar ícones
    if (window.lucide) lucide.createIcons();
    
    // Botão Nova Encomenda
    document.getElementById('btn-nova-encomenda')?.addEventListener('click', function() {
        window.dispatchEvent(new CustomEvent('open-nova-encomenda', { 
            detail: { userInitiated: true } 
        }));
    });
});

// Função para gerar calendário
function initCalendar() {
    const container = document.getElementById('calendar-days');
    const today = new Date();
    
    // Gerar 7 dias (3 antes, hoje, 3 depois)
    for (let i = -3; i <= 3; i++) {
        const date = new Date(today);
        date.setDate(today.getDate() + i);
        
        const month = date.toLocaleString('pt-BR', { month: 'short' }).toUpperCase().replace('.', '');
        const day = date.getDate();
        const weekday = date.toLocaleString('pt-BR', { weekday: 'short' }).replace('.', '');
        
        const isToday = i === 0;
        
        const dayEl = document.createElement('div');
        dayEl.className = 'calendar-day' + (isToday ? ' today' : '');
        dayEl.innerHTML = `
            <div class="calendar-day-month">${month}</div>
            <div class="calendar-day-number">${day}</div>
            <div class="calendar-day-weekday">${weekday}</div>
        `;
        dayEl.onclick = () => selectDate(date);
        
        container.appendChild(dayEl);
    }
}

// Selecionar data
function selectDate(date) {
    console.log('Data selecionada:', date);
    // Implementar filtro por data
}

// Toggle filtro dropdown
function toggleFilterDropdown() {
    const dropdown = document.getElementById('filter-dropdown');
    dropdown.classList.toggle('hidden');
}

// Toggle card de pedido
function toggleOrderCard(orderId) {
    const details = document.getElementById('order-details-' + orderId);
    const chevron = document.querySelector('.order-chevron-' + orderId);
    
    if (details && chevron) {
        details.classList.toggle('hidden');
        chevron.style.transform = details.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(180deg)';
        
        // Re-inicializar ícones
        if (window.lucide) lucide.createIcons();
    }
}

// Navegação do calendário
document.getElementById('prev-week')?.addEventListener('click', () => {
    // Implementar navegação
    console.log('Semana anterior');
});

document.getElementById('next-week')?.addEventListener('click', () => {
    // Implementar navegação
    console.log('Próxima semana');
});
</script>
@endpush
