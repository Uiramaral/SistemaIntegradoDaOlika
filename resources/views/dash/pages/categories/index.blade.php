@extends('layouts.admin')

@section('title', 'Categorias')
@section('page_title', 'Categorias')

@section('content')
<div class="mb-6">
    <div class="flex flex-col sm:flex-row gap-4">
        <div class="flex-1">
            <input type="text" placeholder="Buscar categorias..." class="input" id="search-categories">
        </div>
        <div class="flex gap-2">
            <x-button href="/categories/create" variant="primary">
                <i class="fas fa-plus"></i> Nova Categoria
            </x-button>
        </div>
    </div>
</div>

<x-card title="Lista de Categorias">
    <x-table :headers="['Nome', 'Descrição', 'Produtos', 'Status', 'Ações']" :actions="false">
        @forelse($categories ?? [] as $category)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 border-b">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg mr-3 flex items-center justify-center">
                            <i class="fas fa-tag text-blue-600"></i>
                        </div>
                        <div>
                            <div class="font-medium">{{ $category->name ?? 'Categoria' }}</div>
                            <div class="text-sm text-gray-500">ID: {{ $category->id }}</div>
                        </div>
                    </div>
                </td>
                <td class="px-4 py-3 border-b">{{ Str::limit($category->description ?? 'Sem descrição', 50) }}</td>
                <td class="px-4 py-3 border-b">
                    <span class="badge badge-info">{{ $category->products_count ?? 0 }}</span>
                </td>
                <td class="px-4 py-3 border-b">
                    <span class="badge {{ ($category->active ?? false) ? 'badge-success' : 'badge-danger' }}">
                        {{ ($category->active ?? false) ? 'Ativa' : 'Inativa' }}
                    </span>
                </td>
                <td class="px-4 py-3 border-b text-right">
                    <div class="flex gap-2 justify-end">
                        <x-button href="/categories/{{ $category->id }}" variant="secondary" size="sm">
                            <i class="fas fa-eye"></i>
                        </x-button>
                        <x-button href="/categories/{{ $category->id }}/edit" variant="primary" size="sm">
                            <i class="fas fa-edit"></i>
                        </x-button>
                        <x-button variant="danger" size="sm" onclick="confirmDelete({{ $category->id }})">
                            <i class="fas fa-trash"></i>
                        </x-button>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                    Nenhuma categoria encontrada
                </td>
            </tr>
        @endforelse
    </x-table>
</x-card>

@push('scripts')
<script>
    function confirmDelete(categoryId) {
        if (confirm('Tem certeza que deseja excluir esta categoria?')) {
            fetch(`/categories/${categoryId}`, {
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
    document.getElementById('search-categories').addEventListener('input', function() {
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