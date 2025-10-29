@extends('layouts.admin')

@section('title', 'Categorias')
@section('page_title', 'Categorias')

@section('content')
<x-card title="Lista de Categorias" :actions="true">
    <x-slot name="actions">
        <x-button href="/categories/create" variant="primary">
            <i class="fas fa-plus"></i> Nova Categoria
        </x-button>
    </x-slot>

    <x-table :headers="['Nome', 'Status']" :actions="true">
        @forelse($categories ?? [] as $category)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 border-b font-medium">{{ $category->name ?? '—' }}</td>
                <td class="px-4 py-3 border-b">
                    <span class="badge {{ ($category->active ?? false) ? 'badge-success' : 'badge-danger' }}">
                        {{ ($category->active ?? false) ? 'Ativa' : 'Inativa' }}
                    </span>
                </td>
                <td class="px-4 py-3 border-b text-right">
                    <x-button href="/categories/{{ $category->id }}/edit" variant="secondary" size="sm">
                        <i class="fas fa-edit"></i> Editar
                    </x-button>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="3" class="px-4 py-8 text-center text-gray-500">
                    Nenhuma categoria encontrada
                </td>
            </tr>
        @endforelse
    </x-table>
</x-card>
@endsection