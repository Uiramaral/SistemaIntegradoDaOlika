@extends('layouts.admin')

@section('title', 'Cupons')
@section('page_title', 'Cupons')

@section('content')
<div class="container-page">
  <div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">Cupons</h1>
    <a href="{{ route('dashboard.coupons.create') }}" class="btn btn-primary">
      <i class="fas fa-plus mr-2"></i> Novo Cupom
    </a>
  </div>

  @if(session('status'))
    <x-alert type="success">{{ session('status') }}</x-alert>
  @endif

  @if(session('error'))
    <x-alert type="error">{{ session('error') }}</x-alert>
  @endif

  @if(isset($coupons) && $coupons->isEmpty())
    <x-card>
      <div class="text-center py-8">
        <i class="fas fa-ticket-alt text-4xl text-gray-400 mb-4"></i>
        <p class="text-gray-500 text-lg">Nenhum cupom cadastrado.</p>
        <p class="text-gray-400 text-sm mt-2">Comece criando seu primeiro cupom de desconto.</p>
      </div>
    </x-card>
  @else
    <div class="overflow-x-auto">
      <table class="min-w-full bg-white shadow-md rounded-lg">
        <thead>
          <tr class="bg-gray-100 text-left text-sm font-medium text-gray-600">
            <th class="px-4 py-3">Código</th>
            <th class="px-4 py-3">Tipo</th>
            <th class="px-4 py-3">Valor</th>
            <th class="px-4 py-3">Público</th>
            <th class="px-4 py-3">Uso Único</th>
            <th class="px-4 py-3">Ativo</th>
            <th class="px-4 py-3">Usos</th>
            <th class="px-4 py-3 text-right">Ações</th>
          </tr>
        </thead>
        <tbody class="text-sm text-gray-700">
          @foreach($coupons ?? [] as $coupon)
            <tr class="border-t hover:bg-gray-50 transition-colors">
              <td class="px-4 py-3 font-mono font-semibold text-orange-600">{{ $coupon->codigo }}</td>
              <td class="px-4 py-3">
                <x-badge type="{{ $coupon->tipo == 'porcentagem' ? 'info' : 'warning' }}">
                  {{ $coupon->tipo == 'porcentagem' ? 'Percentual' : 'Valor Fixo' }}
                </x-badge>
              </td>
              <td class="px-4 py-3 font-medium">
                {{ $coupon->tipo == 'porcentagem' ? $coupon->valor . '%' : 'R$ ' . number_format($coupon->valor, 2, ',', '.') }}
              </td>
              <td class="px-4 py-3">
                <x-badge type="{{ $coupon->publico ? 'success' : 'gray' }}">
                  {{ $coupon->publico ? 'Sim' : 'Não' }}
                </x-badge>
              </td>
              <td class="px-4 py-3">
                <x-badge type="{{ $coupon->uso_unico ? 'warning' : 'info' }}">
                  {{ $coupon->uso_unico ? 'Sim' : 'Não' }}
                </x-badge>
              </td>
              <td class="px-4 py-3">
                <x-badge type="{{ $coupon->ativo ? 'success' : 'danger' }}">
                  {{ $coupon->ativo ? 'Ativo' : 'Inativo' }}
                </x-badge>
              </td>
              <td class="px-4 py-3">
                <span class="text-sm">{{ $coupon->usos_count ?? 0 }} / {{ $coupon->limite_usos ?? '∞' }}</span>
              </td>
              <td class="px-4 py-3 text-right">
                <div class="flex justify-end gap-2">
                  <a href="{{ route('dashboard.coupons.show', $coupon) }}" class="text-orange-600 hover:text-orange-800 hover:underline text-sm">
                    <i class="fas fa-eye mr-1"></i> Ver
                  </a>
                  <a href="{{ route('dashboard.coupons.edit', $coupon) }}" class="text-blue-600 hover:text-blue-800 hover:underline text-sm">
                    <i class="fas fa-edit mr-1"></i> Editar
                  </a>
                </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    @if(isset($coupons) && $coupons->hasPages())
      <div class="mt-6">
        {{ $coupons->links() }}
      </div>
    @endif
  @endif
</div>
@endsection