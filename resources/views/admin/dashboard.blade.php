@extends('layouts.dashboard')

@section('title', 'Dashboard')

@section('content')
<div class="page p-6">
    <h1 class="text-2xl font-bold mb-4">Dashboard</h1>
    <p class="text-gray-600 mb-6">Visão geral do sistema Olika</p>

    <!-- Period Filter -->
    <div class="mb-6">
        <div class="flex space-x-2">
            <button onclick="changePeriod('today')" 
                    class="period-btn px-4 py-2 rounded-lg {{ $period === 'today' ? 'bg-orange-600 text-white' : 'bg-gray-200 text-gray-700' }}">
                Hoje
            </button>
            <button onclick="changePeriod('week')" 
                    class="period-btn px-4 py-2 rounded-lg {{ $period === 'week' ? 'bg-orange-600 text-white' : 'bg-gray-200 text-gray-700' }}">
                Esta Semana
            </button>
            <button onclick="changePeriod('month')" 
                    class="period-btn px-4 py-2 rounded-lg {{ $period === 'month' ? 'bg-orange-600 text-white' : 'bg-gray-200 text-gray-700' }}">
                Este Mês
            </button>
            <button onclick="changePeriod('year')" 
                    class="period-btn px-4 py-2 rounded-lg {{ $period === 'year' ? 'bg-orange-600 text-white' : 'bg-gray-200 text-gray-700' }}">
                Este Ano
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <i class="fas fa-shopping-cart text-blue-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total de Pedidos</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total_orders'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-green-100 rounded-lg">
                    <i class="fas fa-dollar-sign text-green-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Faturamento</p>
                    <p class="text-2xl font-bold text-gray-900">R$ {{ number_format($stats['total_revenue'], 2, ',', '.') }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-purple-100 rounded-lg">
                    <i class="fas fa-users text-purple-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Novos Clientes</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['new_customers'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-orange-100 rounded-lg">
                    <i class="fas fa-chart-line text-orange-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Ticket Médio</p>
                    <p class="text-2xl font-bold text-gray-900">R$ {{ number_format($stats['average_order_value'], 2, ',', '.') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Status Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
            <div class="flex items-center">
                <i class="fas fa-clock text-yellow-600 text-2xl mr-4"></i>
                <div>
                    <p class="text-sm font-medium text-yellow-800">Pendentes</p>
                    <p class="text-2xl font-bold text-yellow-900">{{ $stats['pending_orders'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
            <div class="flex items-center">
                <i class="fas fa-check-circle text-blue-600 text-2xl mr-4"></i>
                <div>
                    <p class="text-sm font-medium text-blue-800">Confirmados</p>
                    <p class="text-2xl font-bold text-blue-900">{{ $stats['confirmed_orders'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-purple-50 border border-purple-200 rounded-lg p-6">
            <div class="flex items-center">
                <i class="fas fa-utensils text-purple-600 text-2xl mr-4"></i>
                <div>
                    <p class="text-sm font-medium text-purple-800">Preparando</p>
                    <p class="text-2xl font-bold text-purple-900">{{ $stats['preparing_orders'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-green-50 border border-green-200 rounded-lg p-6">
            <div class="flex items-center">
                <i class="fas fa-check-double text-green-600 text-2xl mr-4"></i>
                <div>
                    <p class="text-sm font-medium text-green-800">Prontos</p>
                    <p class="text-2xl font-bold text-green-900">{{ $stats['ready_orders'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts and Tables -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Sales Chart -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Vendas dos Últimos 30 Dias</h3>
            <div id="sales-chart" class="h-64"></div>
        </div>

        <!-- Top Products -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Produtos Mais Vendidos</h3>
            <div class="space-y-3">
                @foreach($topProducts as $product)
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-medium text-gray-900">{{ $product->name }}</p>
                        <p class="text-sm text-gray-600">{{ $product->total_quantity }} vendidos</p>
                    </div>
                    <div class="text-right">
                        <p class="font-semibold text-gray-900">R$ {{ number_format($product->total_revenue, 2, ',', '.') }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Pedidos Recentes</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pedido</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($recentOrders as $order)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            #{{ $order->order_number }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $order->customer->name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $order->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                   ($order->status === 'confirmed' ? 'bg-blue-100 text-blue-800' : 
                                   ($order->status === 'preparing' ? 'bg-purple-100 text-purple-800' : 
                                   'bg-green-100 text-green-800')) }}">
                                {{ $order->status_label }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            R$ {{ number_format($order->final_amount, 2, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $order->created_at->format('d/m/Y H:i') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Change period
    function changePeriod(period) {
        window.location.href = `{{ route('admin.dashboard') }}?period=${period}`;
    }

    // Sales Chart
    document.addEventListener('DOMContentLoaded', function() {
        fetch('{{ route("admin.dashboard.stats") }}?period={{ $period }}')
            .then(response => response.json())
            .then(data => {
                // Implementar gráfico com Chart.js
                console.log('Sales data:', data);
            });
    });
</script>
@endpush
