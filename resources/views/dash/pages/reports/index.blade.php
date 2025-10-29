@extends('dash.layouts.base')

@section('title', 'Relatórios')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold mb-2">Relatórios de Vendas</h1>
    <form method="GET" class="flex gap-4">
        <input type="date" name="start_date" value="{{ request('start_date') }}" class="input">
        <input type="date" name="end_date" value="{{ request('end_date') }}" class="input">
        <button class="btn btn-primary">Filtrar</button>
    </form>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white p-4 rounded-xl shadow">
        <h2 class="text-sm text-gray-600">Total de Pedidos</h2>
        <div class="text-2xl font-bold">{{ $totalOrders }}</div>
    </div>
    <div class="bg-white p-4 rounded-xl shadow">
        <h2 class="text-sm text-gray-600">Total Vendido</h2>
        <div class="text-2xl font-bold">R$ {{ number_format($totalAmount, 2, ',', '.') }}</div>
    </div>
    <div class="bg-white p-4 rounded-xl shadow">
        <h2 class="text-sm text-gray-600">Ticket Médio</h2>
        <div class="text-2xl font-bold">R$ {{ number_format($averageTicket, 2, ',', '.') }}</div>
    </div>
</div>

<div class="bg-white p-4 rounded-xl shadow mb-6">
    <canvas id="ordersChart" height="100"></canvas>
</div>

<div class="bg-white p-4 rounded-xl shadow">
    <h2 class="text-lg font-bold mb-2">Pedidos por Status</h2>
    <table class="w-full text-left">
        <thead>
            <tr>
                <th class="py-2 border-b">Status</th>
                <th class="py-2 border-b">Quantidade</th>
            </tr>
        </thead>
        <tbody>
            @foreach($statusSummary as $status => $count)
                <tr>
                    <td class="py-2 border-b">{{ ucfirst($status) }}</td>
                    <td class="py-2 border-b">{{ $count }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('ordersChart').getContext('2d');
    const ordersChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($chartLabels) !!},
            datasets: [{
                label: 'Pedidos por Dia',
                data: {!! json_encode($chartData) !!},
                backgroundColor: '#ea580c'
            }]
        }
    });
</script>
@endpush
@endsection