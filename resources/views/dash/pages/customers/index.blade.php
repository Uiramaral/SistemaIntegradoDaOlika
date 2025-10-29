@extends('layouts.admin')

@section('title', 'Clientes')
@section('page_title', 'Clientes')

@section('content')
<x-card title="Lista de Clientes" :actions="true">
    <div class="mb-4">
        <form action="/customers" method="GET" class="flex gap-2">
            <input type="text" name="search" placeholder="Buscar cliente..." class="input flex-1" value="{{ request('search') }}">
            <x-button type="submit" variant="secondary">
                <i class="fas fa-search"></i> Buscar
            </x-button>
        </form>
    </div>

    <x-table :headers="['Nome', 'E-mail', 'Telefone']" :actions="true">
        @forelse($customers ?? [] as $customer)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 border-b font-medium">{{ $customer->nome ?? '—' }}</td>
                <td class="px-4 py-3 border-b">{{ $customer->email ?? '—' }}</td>
                <td class="px-4 py-3 border-b">{{ $customer->telefone ?? '—' }}</td>
                <td class="px-4 py-3 border-b text-right">
                    <x-button href="/customers/{{ $customer->id }}" variant="secondary" size="sm">
                        <i class="fas fa-eye"></i> Ver
                    </x-button>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="px-4 py-8 text-center text-gray-500">
                    Nenhum cliente encontrado
                </td>
            </tr>
        @endforelse
    </x-table>
</x-card>
@endsection