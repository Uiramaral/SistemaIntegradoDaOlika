@extends('dashboard.layouts.app')

@section('page_title', 'Dashboard')
@section('page_subtitle', 'Acompanhe uma visão detalhada das métricas e resultados')

@section('content')

<div class="space-y-6">
  <!-- Controles superiores -->
  <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
    <div></div>
    <button class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors text-sm font-medium shadow-sm">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
      </svg>
      Exportar
    </button>
  </div>

  <!-- Cards de Métricas - Layout 3 colunas horizontais -->
  <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <!-- Card Faturamento -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
      <div class="flex items-start justify-between">
        <div class="flex-1">
          <p class="text-sm font-medium text-gray-600 mb-1">Faturamento</p>
          <p class="text-3xl font-bold text-gray-900">R$ {{ number_format($faturamento ?? 0, 2, ',', '.') }}</p>
        </div>
        <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
          <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
        </div>
      </div>
    </div>

    <!-- Card Pedidos -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
      <div class="flex items-start justify-between">
        <div class="flex-1">
          <p class="text-sm font-medium text-gray-600 mb-1">Pedidos</p>
          <p class="text-3xl font-bold text-gray-900">{{ $totalPedidos ?? 0 }}</p>
        </div>
        <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
          <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
          </svg>
        </div>
      </div>
    </div>

    <!-- Card Clientes -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
      <div class="flex items-start justify-between">
        <div class="flex-1">
          <p class="text-sm font-medium text-gray-600 mb-1">Clientes</p>
          <p class="text-3xl font-bold text-gray-900">{{ $novosClientes ?? 0 }}</p>
        </div>
        <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
          <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
          </svg>
        </div>
      </div>
    </div>
  </div>

  <!-- Seção de Filtros e Tabela de Pedidos -->
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
  <div class="lg:col-span-2 order-1">
    <!-- Filtros de Período -->
    <div class="flex flex-wrap items-center gap-2 mb-4">
      <a href="{{ route('dashboard.index', ['period' => '7days']) }}" class="px-4 py-2 text-sm font-medium {{ ($period ?? '7days') === '7days' ? 'text-primary bg-primary/10 border border-primary' : 'text-gray-700 bg-white border border-gray-300' }} rounded-lg hover:bg-gray-50 transition-colors">Últimos 7 dias</a>
      <a href="{{ route('dashboard.index', ['period' => '30days']) }}" class="px-4 py-2 text-sm font-medium {{ ($period ?? '7days') === '30days' ? 'text-primary bg-primary/10 border border-primary' : 'text-gray-700 bg-white border border-gray-300' }} rounded-lg hover:bg-gray-50 transition-colors">Últimos 30 dias</a>
      <a href="{{ route('dashboard.index', ['period' => '3months']) }}" class="px-4 py-2 text-sm font-medium {{ ($period ?? '7days') === '3months' ? 'text-primary bg-primary/10 border border-primary' : 'text-gray-700 bg-white border border-gray-300' }} rounded-lg hover:bg-gray-50 transition-colors">Últimos 3 meses</a>

      @php
        $meses = [
          1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março',
          4 => 'Abril', 5 => 'Maio', 6 => 'Junho',
          7 => 'Julho', 8 => 'Agosto', 9 => 'Setembro',
          10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
        ];
        $selMonth = (int)($selectedMonth ?? now()->month);
        $selYear = (int)($selectedYear ?? now()->year);
        $yearOptions = range(now()->year - 1, now()->year + 1);
        $mesAtual = $meses[$selMonth] ?? 'Janeiro';
      @endphp
      <form method="GET" action="{{ route('dashboard.index') }}" class="inline-flex items-center gap-2" id="month-filter-form">
        <input type="hidden" name="period" value="month">
        <select name="month" onchange="this.form.submit()" class="appearance-none bg-white {{ ($period ?? '7days') === 'month' ? 'border-primary text-primary' : 'border-gray-300 text-gray-700' }} border rounded-lg px-3 py-2 text-sm font-medium hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent cursor-pointer">
          @foreach($meses as $num => $label)
            <option value="{{ $num }}" {{ $selMonth === $num ? 'selected' : '' }}>{{ $label }}</option>
          @endforeach
        </select>
        <select name="year" onchange="this.form.submit()" class="appearance-none bg-white {{ ($period ?? '7days') === 'month' ? 'border-primary text-primary' : 'border-gray-300 text-gray-700' }} border rounded-lg px-3 py-2 text-sm font-medium hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent cursor-pointer">
          @foreach($yearOptions as $year)
            <option value="{{ $year }}" {{ $selYear === $year ? 'selected' : '' }}>{{ $year }}</option>
          @endforeach
        </select>
      </form>
    </div>

    <!-- Tabela de Pedidos -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
      <div class="flex items-center justify-between p-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">Pedidos</h3>
        <div class="flex items-center gap-2">
          <a href="{{ route('dashboard.orders.index') }}" class="text-sm font-medium text-primary hover:underline">Ver todos</a>
          <button class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
            </svg>
          </button>
        </div>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50 hidden md:table-header-group">
            <tr>
              <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PEDIDO</th>
              <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CLIENTE</th>
              <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">VALOR</th>
              <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">STATUS</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            @forelse(($recentOrders ?? collect()) as $order)
            <tr class="hover:bg-gray-50">
              <td class="px-3 py-2">
                <div class="md:hidden space-y-1.5">
                  <div>
                    <span class="text-xs text-gray-500 uppercase">Pedido:</span>
                    <a href="{{ route('dashboard.orders.show', $order->id) }}" class="font-semibold text-gray-900 hover:text-primary hover:underline text-sm block">#{{ $order->order_number ?? $order->id }}</a>
                  </div>
                  <div>
                    <span class="text-xs text-gray-500 uppercase">Cliente:</span>
                    @if($order->customer_id && $order->customer)
                      @php
                        $customerName = $order->customer->name ?? 'Cliente';
                        $truncatedName = \App\Helpers\FormatHelper::truncateName($customerName);
                      @endphp
                      <a href="{{ route('dashboard.customers.show', $order->customer_id) }}" class="font-semibold text-gray-900 hover:text-primary hover:underline text-sm block max-w-[200px] truncate" title="{{ $customerName }}">{{ $truncatedName }}</a>
                    @else
                      <div class="font-semibold text-gray-900 text-sm">Cliente</div>
                    @endif
                  </div>
                  <div>
                    <span class="text-xs text-gray-500 uppercase">Valor:</span>
                    <span class="font-semibold text-gray-900 text-sm">R$ {{ number_format((float)($order->final_amount ?? $order->total_amount ?? 0), 2, ',', '.') }}</span>
                  </div>
                  <div>
                    <span class="text-xs text-gray-500 uppercase">Status:</span>
                    @php
                      $statusLabels = [
                        'pending' => 'Pendente',
                        'confirmed' => 'Confirmado',
                        'preparing' => 'Em Preparo',
                        'ready' => 'Pronto',
                        'delivered' => 'Entregue',
                        'cancelled' => 'Cancelado'
                      ];
                      $statusColors = [
                        'pending' => 'bg-yellow-100 text-yellow-800',
                        'confirmed' => 'bg-green-100 text-green-800',
                        'preparing' => 'bg-blue-100 text-blue-800',
                        'ready' => 'bg-purple-100 text-purple-800',
                        'delivered' => 'bg-gray-100 text-gray-800',
                        'cancelled' => 'bg-red-100 text-red-800'
                      ];
                      $status = $order->status ?? 'pending';
                    @endphp
                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $statusColors[$status] ?? 'bg-gray-100 text-gray-800' }}">
                      {{ $statusLabels[$status] ?? ucfirst($status) }}
                    </span>
                  </div>
                </div>
                <div class="hidden md:table-cell px-3 py-2 whitespace-nowrap">
                  <a href="{{ route('dashboard.orders.show', $order->id) }}" class="font-semibold text-gray-900 hover:text-primary hover:underline">#{{ $order->order_number ?? $order->id }}</a>
                </div>
              </td>
              <td class="hidden md:table-cell px-3 py-2 whitespace-nowrap">
                @if($order->customer_id && $order->customer)
                  @php
                    $customerName = $order->customer->name ?? 'Cliente';
                    $truncatedName = \App\Helpers\FormatHelper::truncateName($customerName);
                  @endphp
                  <a href="{{ route('dashboard.customers.show', $order->customer_id) }}" class="font-semibold text-gray-900 hover:text-primary hover:underline max-w-[200px] truncate block" title="{{ $customerName }}">{{ $truncatedName }}</a>
                @else
                  <div class="font-semibold text-gray-900">Cliente</div>
                @endif
              </td>
              <td class="hidden md:table-cell px-3 py-2 whitespace-nowrap">
                <span class="font-semibold text-gray-900">R$ {{ number_format((float)($order->final_amount ?? $order->total_amount ?? 0), 2, ',', '.') }}</span>
              </td>
              <td class="hidden md:table-cell px-3 py-2 whitespace-nowrap">
                @php
                  $statusLabels = [
                    'pending' => 'Pendente',
                    'confirmed' => 'Confirmado',
                    'preparing' => 'Em Preparo',
                    'ready' => 'Pronto',
                    'delivered' => 'Entregue',
                    'cancelled' => 'Cancelado'
                  ];
                  $statusColors = [
                    'pending' => 'bg-yellow-100 text-yellow-800',
                    'confirmed' => 'bg-green-100 text-green-800',
                    'preparing' => 'bg-blue-100 text-blue-800',
                    'ready' => 'bg-purple-100 text-purple-800',
                    'delivered' => 'bg-gray-100 text-gray-800',
                    'cancelled' => 'bg-red-100 text-red-800'
                  ];
                  $status = $order->status ?? 'pending';
                @endphp
                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $statusColors[$status] ?? 'bg-gray-100 text-gray-800' }}">
                  {{ $statusLabels[$status] ?? ucfirst($status) }}
                </span>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="4" class="px-3 py-6 text-center text-gray-500">Nenhum pedido encontrado</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Sidebar com Produtos e Categorias -->
  <div class="space-y-6 order-2">
    <!-- Produtos -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
      <div class="flex items-center justify-between p-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">Produtos</h3>
        <a href="{{ route('dashboard.products.index') }}" class="text-sm font-medium text-primary hover:underline">Ver todos</a>
      </div>
      <div class="p-4 overflow-hidden">
        @if(isset($topProducts) && $topProducts->count() > 0)
          <div class="space-y-3 max-h-[400px] overflow-y-auto">
            @foreach($topProducts as $item)
              <div class="flex items-center gap-3 min-w-0">
                <div class="flex-shrink-0 w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center">
                  <span class="text-sm font-semibold text-gray-600">
                    {{ strtoupper(substr($item['product']->name ?? 'P', 0, 1)) }}
                  </span>
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-medium text-gray-900 truncate">{{ $item['product']->name ?? 'Produto' }}</p>
                  <p class="text-xs text-gray-500 truncate">{{ $item['product']->category_name ?? 'Sem categoria' }}</p>
                </div>
                <div class="flex-shrink-0 text-right ml-auto">
                  <p class="text-xs text-gray-500 whitespace-nowrap">{{ (int)$item['quantity'] }} vendidos</p>
                </div>
              </div>
            @endforeach
          </div>
        @else
          <p class="text-sm text-gray-500">Nenhum produto vendido recentemente</p>
        @endif
      </div>
    </div>

    <!-- Compradores -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
      <div class="flex items-center justify-between p-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">Compradores</h3>
        <a href="{{ route('dashboard.customers.index') }}" class="text-sm font-medium text-primary hover:underline">Ver todos</a>
      </div>
      <div class="p-4 overflow-hidden">
        @if(isset($topBuyers) && $topBuyers->count() > 0)
          <div class="space-y-1.5 max-h-[300px] overflow-y-auto">
            @foreach($topBuyers as $buyer)
              <div class="flex items-center gap-2 min-w-0">
                <div class="flex-shrink-0 w-2 h-2 rounded-full bg-blue-500"></div>
                <div class="flex-1 min-w-0">
                  @php
                    $buyerName = $buyer->name ?? 'Cliente';
                    $truncatedBuyerName = \App\Helpers\FormatHelper::truncateName($buyerName);
                  @endphp
                  <p class="text-sm font-medium text-gray-900 truncate" title="{{ $buyerName }}">{{ $truncatedBuyerName }}</p>
                  <p class="text-xs text-gray-500 truncate">{{ $buyer->total_compras ?? 0 }} compras • R$ {{ number_format((float)($buyer->total_valor ?? 0), 2, ',', '.') }}</p>
                </div>
              </div>
            @endforeach
          </div>
        @else
          <p class="text-sm text-gray-500">Nenhum comprador encontrado</p>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection
