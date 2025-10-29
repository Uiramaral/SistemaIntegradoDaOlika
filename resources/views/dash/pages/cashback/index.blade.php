@extends('layouts.admin')

@section('title', 'Cashback')
@section('page_title', 'Cashback')

@section('content')
<div class="mb-6">
    <div class="flex flex-col sm:flex-row gap-4">
        <div class="flex-1">
            <input type="text" placeholder="Buscar por cliente..." class="input" id="search-cashback">
        </div>
        <div class="flex gap-2">
            <select class="input" id="status-filter">
                <option value="">Todos os status</option>
                <option value="pending">Pendente</option>
                <option value="processed">Processado</option>
                <option value="cancelled">Cancelado</option>
            </select>
            <input type="date" class="input" id="date-filter" placeholder="Filtrar por data">
        </div>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <x-card class="text-center">
        <div class="text-3xl font-bold text-green-600 mb-2">R$ {{ number_format($totalCashback ?? 0, 2, ',', '.') }}</div>
        <div class="text-sm text-gray-600">Total Acumulado</div>
    </x-card>
    
    <x-card class="text-center">
        <div class="text-3xl font-bold text-blue-600 mb-2">R$ {{ number_format($pendingCashback ?? 0, 2, ',', '.') }}</div>
        <div class="text-sm text-gray-600">Pendente</div>
    </x-card>
    
    <x-card class="text-center">
        <div class="text-3xl font-bold text-purple-600 mb-2">{{ $totalTransactions ?? 0 }}</div>
        <div class="text-sm text-gray-600">Transações</div>
    </x-card>
</div>

<x-card title="Histórico de Cashback">
    <x-table :headers="['Cliente', 'Pedido', 'Valor', 'Tipo', 'Data', 'Status', 'Ações']" :actions="false">
        @forelse($cashbacks ?? [] as $cashback)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 border-b">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-green-100 rounded-full mr-3 flex items-center justify-center">
                            <i class="fas fa-user text-green-600"></i>
                        </div>
                        <div>
                            <div class="font-medium">{{ $cashback->customer->nome ?? 'Cliente' }}</div>
                            <div class="text-sm text-gray-500">{{ $cashback->customer->email ?? '—' }}</div>
                        </div>
                    </div>
                </td>
                <td class="px-4 py-3 border-b">
                    <span class="font-mono text-sm">#OLK{{ str_pad($cashback->order_id ?? 0, 5, '0', STR_PAD_LEFT) }}</span>
                </td>
                <td class="px-4 py-3 border-b font-medium text-green-600">R$ {{ number_format($cashback->valor ?? 0, 2, ',', '.') }}</td>
                <td class="px-4 py-3 border-b">
                    <span class="badge badge-info">{{ ucfirst($cashback->type ?? 'cashback') }}</span>
                </td>
                <td class="px-4 py-3 border-b">{{ $cashback->created_at->format('d/m/Y H:i') }}</td>
                <td class="px-4 py-3 border-b">
                    @php
                        $statusClass = match($cashback->status ?? 'pending') {
                            'pending' => 'badge-warning',
                            'processed' => 'badge-success',
                            'cancelled' => 'badge-danger',
                            default => 'badge-warning'
                        };
                    @endphp
                    <span class="badge {{ $statusClass }}">{{ ucfirst($cashback->status ?? 'pending') }}</span>
                </td>
                <td class="px-4 py-3 border-b text-right">
                    <div class="flex gap-2 justify-end">
                        <x-button href="/cashback/{{ $cashback->id }}" variant="secondary" size="sm">
                            <i class="fas fa-eye"></i>
                        </x-button>
                        @if(($cashback->status ?? 'pending') === 'pending')
                            <x-button variant="success" size="sm" onclick="processCashback({{ $cashback->id }})">
                                <i class="fas fa-check"></i>
                            </x-button>
                        @endif
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                    Nenhum registro de cashback encontrado
                </td>
            </tr>
        @endforelse
    </x-table>
</x-card>

@push('scripts')
<script>
    function processCashback(cashbackId) {
        if (confirm('Processar este cashback?')) {
            fetch(`/cashback/${cashbackId}/process`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            }).then(() => {
                location.reload();
            });
        }
    }
    
    // Filtros dinâmicos
    document.getElementById('search-cashback').addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });
</script>
@endpush
@endsection