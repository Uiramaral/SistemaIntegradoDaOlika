@extends('layouts.admin')

@section('title', 'Pedidos')
@section('page_title', 'Pedidos')

@section('content')
@if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
    </div>
@endif

<div class="mb-6">
    <div class="flex flex-col sm:flex-row gap-4">
        <div class="flex-1">
            <input type="text" placeholder="Buscar pedidos..." class="input" id="search-orders">
        </div>
        <div class="flex gap-2">
            <select class="input" id="status-filter">
                <option value="">Todos os status</option>
                <option value="pending">Pendente</option>
                <option value="confirmed">Confirmado</option>
                <option value="preparing">Preparando</option>
                <option value="delivered">Entregue</option>
            </select>
            <button class="btn btn-secondary">
                <i class="fas fa-filter"></i> Filtrar
            </button>
        </div>
    </div>
</div>

<div class="bg-white rounded-xl shadow p-6">
    <h2 class="text-xl font-bold mb-4">Lista de Pedidos</h2>
    
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="border-b">
                    <th class="py-3 px-4 font-medium">#Pedido</th>
                    <th class="py-3 px-4 font-medium">Cliente</th>
                    <th class="py-3 px-4 font-medium">Total</th>
                    <th class="py-3 px-4 font-medium">Status</th>
                    <th class="py-3 px-4 font-medium">Data</th>
                    <th class="py-3 px-4 font-medium text-right">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders ?? [] as $order)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 border-b font-medium">#OLK{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</td>
                        <td class="px-4 py-3 border-b">{{ $order->customer->nome ?? 'Cliente não identificado' }}</td>
                        <td class="px-4 py-3 border-b font-medium text-green-600">R$ {{ number_format($order->total ?? 0, 2, ',', '.') }}</td>
                        <td class="px-4 py-3 border-b">
                            @php
                                $statusClass = match($order->status ?? 'pending') {
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'confirmed' => 'bg-blue-100 text-blue-800',
                                    'preparing' => 'bg-orange-100 text-orange-800',
                                    'delivered' => 'bg-green-100 text-green-800',
                                    default => 'bg-gray-100 text-gray-800'
                                };
                            @endphp
                            <span class="px-2 py-1 rounded-full text-xs font-medium {{ $statusClass }}">
                                {{ ucfirst($order->status ?? 'pending') }}
                            </span>
                        </td>
                        <td class="px-4 py-3 border-b">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3 border-b text-right">
                            <div class="flex gap-2 justify-end">
                                <a href="{{ route('dashboard.orders.show', $order->id) }}" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <button onclick="updateStatus({{ $order->id }})" class="btn btn-primary btn-sm">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                            <i class="fas fa-shopping-cart text-4xl mb-4"></i>
                            <p>Nenhum pedido encontrado</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if(isset($orders) && $orders->hasPages())
        <div class="mt-6">
            {{ $orders->links() }}
        </div>
    @endif
</div>

@push('scripts')
<script>
    // Filtros dinâmicos
    document.getElementById('search-orders').addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });
    
    // Atualizar status do pedido
    function updateStatus(orderId) {
        const status = prompt('Digite o novo status (pending, confirmed, preparing, delivered):');
        if (status) {
            fetch(`/orders/${orderId}/status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ status: status })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Erro ao atualizar status');
                }
            });
        }
    }
</script>
@endpush
@endsection