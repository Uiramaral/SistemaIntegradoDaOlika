@extends('layouts.admin')

@section('title', 'Clientes')
@section('page_title', 'Clientes')

@section('content')
<div class="mb-6">
    <div class="flex flex-col sm:flex-row gap-4">
        <div class="flex-1">
            <input type="text" placeholder="Buscar clientes..." class="input" id="search-customers">
        </div>
        <div class="flex gap-2">
            <x-button href="/customers/create" variant="primary">
                <i class="fas fa-plus"></i> Novo Cliente
            </x-button>
        </div>
    </div>
</div>

<x-card title="Lista de Clientes">
    <x-table :headers="['Nome', 'E-mail', 'Telefone', 'Pedidos', 'Último Pedido', 'Ações']" :actions="false">
        @forelse($customers ?? [] as $customer)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 border-b">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-orange-100 rounded-full mr-3 flex items-center justify-center">
                            <i class="fas fa-user text-orange-600"></i>
                        </div>
                        <div>
                            <div class="font-medium">{{ $customer->nome ?? 'Cliente' }}</div>
                            <div class="text-sm text-gray-500">ID: {{ $customer->id }}</div>
                        </div>
                    </div>
                </td>
                <td class="px-4 py-3 border-b">{{ $customer->email ?? '—' }}</td>
                <td class="px-4 py-3 border-b">{{ $customer->telefone ?? '—' }}</td>
                <td class="px-4 py-3 border-b">
                    <span class="badge badge-info">{{ $customer->orders_count ?? 0 }}</span>
                </td>
                <td class="px-4 py-3 border-b">
                    @if($customer->last_order_date ?? false)
                        {{ \Carbon\Carbon::parse($customer->last_order_date)->format('d/m/Y') }}
                    @else
                        <span class="text-gray-400">Nunca</span>
                    @endif
                </td>
                <td class="px-4 py-3 border-b text-right">
                    <div class="flex gap-2 justify-end">
                        <x-button href="/customers/{{ $customer->id }}" variant="secondary" size="sm">
                            <i class="fas fa-eye"></i>
                        </x-button>
                        <x-button href="/customers/{{ $customer->id }}/edit" variant="primary" size="sm">
                            <i class="fas fa-edit"></i>
                        </x-button>
                        <x-button href="/customers/{{ $customer->id }}/orders" variant="success" size="sm">
                            <i class="fas fa-shopping-cart"></i>
                        </x-button>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                    Nenhum cliente encontrado
                </td>
            </tr>
        @endforelse
    </x-table>
</x-card>

@push('scripts')
<script>
    // Filtros dinâmicos
    document.getElementById('search-customers').addEventListener('input', function() {
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