@extends('layouts.admin')

@section('title', 'Relatórios')
@section('page_title', 'Relatórios')

@section('content')
<div class="mb-6">
    <form method="GET" class="flex flex-col sm:flex-row gap-4">
        <div class="flex-1">
            <input type="date" name="start_date" value="{{ request('start_date') }}" class="input" placeholder="Data inicial">
        </div>
        <div class="flex-1">
            <input type="date" name="end_date" value="{{ request('end_date') }}" class="input" placeholder="Data final">
        </div>
        <div class="flex gap-2">
            <select name="report_type" class="input">
                <option value="sales">Vendas</option>
                <option value="products">Produtos</option>
                <option value="customers">Clientes</option>
                <option value="cashback">Cashback</option>
            </select>
            <x-button type="submit" variant="primary">
                <i class="fas fa-filter"></i> Filtrar
            </x-button>
            <x-button variant="success" onclick="exportReport()">
                <i class="fas fa-download"></i> Exportar
            </x-button>
        </div>
    </form>
</div>

<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <x-card class="text-center">
        <div class="text-3xl font-bold text-blue-600 mb-2">{{ $totalOrders ?? 0 }}</div>
        <div class="text-sm text-gray-600">Total de Pedidos</div>
    </x-card>
    
    <x-card class="text-center">
        <div class="text-3xl font-bold text-green-600 mb-2">R$ {{ number_format($totalAmount ?? 0, 2, ',', '.') }}</div>
        <div class="text-sm text-gray-600">Faturamento</div>
    </x-card>
    
    <x-card class="text-center">
        <div class="text-3xl font-bold text-purple-600 mb-2">R$ {{ number_format($averageTicket ?? 0, 2, ',', '.') }}</div>
        <div class="text-sm text-gray-600">Ticket Médio</div>
    </x-card>
    
    <x-card class="text-center">
        <div class="text-3xl font-bold text-orange-600 mb-2">{{ $newCustomers ?? 0 }}</div>
        <div class="text-sm text-gray-600">Novos Clientes</div>
    </x-card>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <x-card title="Vendas por Dia">
        <div class="h-64">
            <canvas id="salesChart"></canvas>
        </div>
    </x-card>
    
    <x-card title="Pedidos por Status">
        <div class="h-64">
            <canvas id="statusChart"></canvas>
        </div>
    </x-card>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <x-card title="Top 5 Produtos">
        <x-table :headers="['Produto', 'Vendas', 'Faturamento']" :actions="false">
            @forelse($topProducts ?? [] as $product)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 border-b font-medium">{{ $product->name ?? 'Produto' }}</td>
                    <td class="px-4 py-3 border-b">
                        <span class="badge badge-info">{{ $product->sales_count ?? 0 }}</span>
                    </td>
                    <td class="px-4 py-3 border-b font-medium text-green-600">
                        R$ {{ number_format($product->total_revenue ?? 0, 2, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="px-4 py-8 text-center text-gray-500">
                        Nenhum dado disponível
                    </td>
                </tr>
            @endforelse
        </x-table>
    </x-card>
    
    <x-card title="Top 5 Clientes">
        <x-table :headers="['Cliente', 'Pedidos', 'Faturamento']" :actions="false">
            @forelse($topCustomers ?? [] as $customer)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 border-b font-medium">{{ $customer->nome ?? 'Cliente' }}</td>
                    <td class="px-4 py-3 border-b">
                        <span class="badge badge-info">{{ $customer->orders_count ?? 0 }}</span>
                    </td>
                    <td class="px-4 py-3 border-b font-medium text-green-600">
                        R$ {{ number_format($customer->total_spent ?? 0, 2, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="px-4 py-8 text-center text-gray-500">
                        Nenhum dado disponível
                    </td>
                </tr>
            @endforelse
        </x-table>
    </x-card>
</div>

<x-card title="Resumo Detalhado" class="mt-6">
    <x-table :headers="['Métrica', 'Valor', 'Variação']" :actions="false">
        <tr class="hover:bg-gray-50">
            <td class="px-4 py-3 border-b font-medium">Pedidos Confirmados</td>
            <td class="px-4 py-3 border-b">{{ $confirmedOrders ?? 0 }}</td>
            <td class="px-4 py-3 border-b">
                <span class="badge badge-success">+{{ $ordersGrowth ?? 0 }}%</span>
            </td>
        </tr>
        <tr class="hover:bg-gray-50">
            <td class="px-4 py-3 border-b font-medium">Taxa de Conversão</td>
            <td class="px-4 py-3 border-b">{{ $conversionRate ?? 0 }}%</td>
            <td class="px-4 py-3 border-b">
                <span class="badge badge-info">{{ $conversionGrowth ?? 0 }}%</span>
            </td>
        </tr>
        <tr class="hover:bg-gray-50">
            <td class="px-4 py-3 border-b font-medium">Cashback Total</td>
            <td class="px-4 py-3 border-b">R$ {{ number_format($totalCashback ?? 0, 2, ',', '.') }}</td>
            <td class="px-4 py-3 border-b">
                <span class="badge badge-warning">{{ $cashbackGrowth ?? 0 }}%</span>
            </td>
        </tr>
    </x-table>
</x-card>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    function exportReport() {
        const params = new URLSearchParams(window.location.search);
        params.set('export', 'true');
        window.open(`/reports/export?${params.toString()}`, '_blank');
    }
    
    // Gráfico de vendas
    const salesCtx = document.getElementById('salesChart').getContext('2d');
    new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($chartLabels ?? []) !!},
            datasets: [{
                label: 'Vendas (R$)',
                data: {!! json_encode($chartData ?? []) !!},
                borderColor: '#ea580c',
                backgroundColor: 'rgba(234, 88, 12, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
    
    // Gráfico de status
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode(array_keys($statusSummary ?? [])) !!},
            datasets: [{
                data: {!! json_encode(array_values($statusSummary ?? [])) !!},
                backgroundColor: ['#f59e0b', '#3b82f6', '#10b981', '#ef4444']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
</script>
@endpush
@endsection