@extends('layouts.admin')

@section('title', 'Produtos')
@section('page_title', 'Produtos')

@section('content')
<div class="mb-6">
    <div class="flex flex-col sm:flex-row gap-4">
        <div class="flex-1">
            <input type="text" placeholder="Buscar produtos..." class="input" id="search-products">
        </div>
        <div class="flex gap-2">
            <select class="input" id="category-filter">
                <option value="">Todas as categorias</option>
                @foreach($categories ?? [] as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
            <x-button href="/products/create" variant="primary">
                <i class="fas fa-plus"></i> Novo Produto
            </x-button>
        </div>
    </div>
</div>

<x-card title="Lista de Produtos">
    <x-table :headers="['Nome', 'Categoria', 'Preço', 'Status', 'Ações']" :actions="false">
        @forelse($produtos ?? [] as $produto)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 border-b">
                    <div class="flex items-center">
                        @if($produto->image ?? false)
                            <img src="{{ $produto->image }}" alt="{{ $produto->name }}" class="w-10 h-10 rounded-lg object-cover mr-3">
                        @else
                            <div class="w-10 h-10 bg-gray-200 rounded-lg mr-3 flex items-center justify-center">
                                <i class="fas fa-image text-gray-400"></i>
                            </div>
                        @endif
                        <div>
                            <div class="font-medium">{{ $produto->name ?? '—' }}</div>
                            <div class="text-sm text-gray-500">{{ Str::limit($produto->description ?? '', 50) }}</div>
                        </div>
                    </div>
                </td>
                <td class="px-4 py-3 border-b">
                    <span class="badge badge-info">{{ $produto->categoria->name ?? 'Sem categoria' }}</span>
                </td>
                <td class="px-4 py-3 border-b font-medium text-green-600">R$ {{ number_format($produto->price ?? 0, 2, ',', '.') }}</td>
                <td class="px-4 py-3 border-b">
                    <span class="badge {{ ($produto->active ?? false) ? 'badge-success' : 'badge-danger' }}">
                        {{ ($produto->active ?? false) ? 'Ativo' : 'Inativo' }}
                    </span>
                </td>
                <td class="px-4 py-3 border-b text-right">
                    <div class="flex gap-2 justify-end">
                        <x-button href="/products/{{ $produto->id }}" variant="secondary" size="sm">
                            <i class="fas fa-eye"></i>
                        </x-button>
                        <x-button href="/products/{{ $produto->id }}/edit" variant="primary" size="sm">
                            <i class="fas fa-edit"></i>
                        </x-button>
                        <x-button variant="danger" size="sm" onclick="confirmDelete({{ $produto->id }})">
                            <i class="fas fa-trash"></i>
                        </x-button>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                    Nenhum produto encontrado
                </td>
            </tr>
        @endforelse
    </x-table>
</x-card>

@push('scripts')
<script>
    function confirmDelete(productId) {
        if (confirm('Tem certeza que deseja excluir este produto?')) {
            fetch(`/products/${productId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            }).then(() => {
                location.reload();
            });
        }
    }
    
    // Filtros dinâmicos
    document.getElementById('search-products').addEventListener('input', function() {
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