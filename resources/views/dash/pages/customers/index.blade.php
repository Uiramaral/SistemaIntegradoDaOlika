@extends('layouts.admin')

@section('title', 'Clientes')
@section('page_title', 'Clientes')

@section('content')
<div class="container-page">
  <div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">Clientes</h1>
    <a href="{{ route('dashboard.customers.create') }}" class="btn btn-primary">
      <i class="fas fa-plus mr-2"></i> Novo Cliente
    </a>
  </div>

  @if(session('status'))
    <x-alert type="success">{{ session('status') }}</x-alert>
  @endif

  @if(session('error'))
    <x-alert type="error">{{ session('error') }}</x-alert>
  @endif

  @if(isset($customers) && $customers->isEmpty())
    <x-card>
      <div class="text-center py-8">
        <i class="fas fa-users text-4xl text-gray-400 mb-4"></i>
        <p class="text-gray-500 text-lg">Nenhum cliente encontrado.</p>
        <p class="text-gray-400 text-sm mt-2">Os clientes aparecerão aqui quando forem cadastrados.</p>
      </div>
    </x-card>
  @else
    <div class="overflow-x-auto">
      <table class="min-w-full bg-white shadow-md rounded-lg">
        <thead>
          <tr class="bg-gray-100 text-left text-sm font-medium text-gray-600">
            <th class="px-4 py-3">Nome</th>
            <th class="px-4 py-3">Telefone</th>
            <th class="px-4 py-3">Fiado</th>
            <th class="px-4 py-3">Última Compra</th>
            <th class="px-4 py-3 text-right">Ações</th>
          </tr>
        </thead>
        <tbody class="text-sm text-gray-700">
          @foreach($customers ?? [] as $customer)
            <tr class="border-t hover:bg-gray-50 transition-colors">
              <td class="px-4 py-3 font-medium">{{ $customer->nome }}</td>
              <td class="px-4 py-3">{{ $customer->telefone ?? '—' }}</td>
              <td class="px-4 py-3">
                @if(($customer->fiado ?? 0) > 0)
                  <x-badge type="danger">R$ {{ number_format($customer->fiado, 2, ',', '.') }}</x-badge>
                @else
                  <x-badge type="success">R$ 0,00</x-badge>
                @endif
              </td>
              <td class="px-4 py-3">{{ optional($customer->last_order)->created_at->format('d/m/Y') ?? '—' }}</td>
              <td class="px-4 py-3 text-right">
                <div class="flex justify-end gap-2">
                  <a href="{{ route('dashboard.customers.show', $customer) }}" class="text-orange-600 hover:text-orange-800 hover:underline text-sm">
                    <i class="fas fa-eye mr-1"></i> Ver
                  </a>
                  <a href="{{ route('dashboard.customers.edit', $customer) }}" class="text-blue-600 hover:text-blue-800 hover:underline text-sm">
                    <i class="fas fa-edit mr-1"></i> Editar
                  </a>
                </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    @if(isset($customers) && $customers->hasPages())
      <div class="mt-6">
        {{ $customers->links() }}
      </div>
    @endif
  @endif
</div>
@endsection