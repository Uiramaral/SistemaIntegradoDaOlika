@extends('layouts.admin')

@section('title', 'Cupom: ' . $coupon->codigo)
@section('page_title', 'Cupom: ' . $coupon->codigo)

@section('content')
<div class="container-page">
  <div class="flex justify-between items-center mb-6">
    <div>
      <h1 class="text-2xl font-bold font-mono text-orange-600">{{ $coupon->codigo }}</h1>
      <p class="text-sm text-gray-500">{{ $coupon->descricao ?? 'Sem descrição' }}</p>
    </div>
    <div class="flex gap-2">
      <a href="{{ route('dashboard.coupons.edit', $coupon) }}" class="btn btn-secondary">
        <i class="fas fa-edit mr-2"></i> Editar
      </a>
      <form method="POST" action="{{ route('dashboard.coupons.toggle', $coupon) }}" class="inline">
        @csrf
        <x-button variant="{{ $coupon->ativo ? 'warning' : 'success' }}" type="submit">
          <i class="fas fa-{{ $coupon->ativo ? 'pause' : 'play' }} mr-2"></i>
          {{ $coupon->ativo ? 'Desativar' : 'Ativar' }}
        </x-button>
      </form>
    </div>
  </div>

  @if(session('status'))
    <x-alert type="success">{{ session('status') }}</x-alert>
  @endif

  <!-- Informações do Cupom -->
  <x-card class="mb-6">
    <h2 class="text-lg font-semibold mb-4">Informações do Cupom</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div>
        <label class="block text-sm font-medium text-gray-700">Tipo de Desconto</label>
        <div class="mt-1">
          <x-badge type="{{ $coupon->tipo == 'porcentagem' ? 'info' : 'warning' }}">
            {{ $coupon->tipo == 'porcentagem' ? 'Percentual' : 'Valor Fixo' }}
          </x-badge>
        </div>
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-700">Valor</label>
        <div class="mt-1 text-lg font-semibold">
          {{ $coupon->tipo == 'porcentagem' ? $coupon->valor . '%' : 'R$ ' . number_format($coupon->valor, 2, ',', '.') }}
        </div>
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-700">Status</label>
        <div class="mt-1">
          <x-badge type="{{ $coupon->ativo ? 'success' : 'danger' }}">
            {{ $coupon->ativo ? 'Ativo' : 'Inativo' }}
          </x-badge>
        </div>
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-700">Público</label>
        <div class="mt-1">
          <x-badge type="{{ $coupon->publico ? 'success' : 'gray' }}">
            {{ $coupon->publico ? 'Sim' : 'Não' }}
          </x-badge>
        </div>
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-700">Uso Único</label>
        <div class="mt-1">
          <x-badge type="{{ $coupon->uso_unico ? 'warning' : 'info' }}">
            {{ $coupon->uso_unico ? 'Sim' : 'Não' }}
          </x-badge>
        </div>
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-700">Limite de Usos</label>
        <div class="mt-1 text-lg font-semibold">
          {{ $coupon->limite_usos ?? 'Ilimitado' }}
        </div>
      </div>
    </div>
    
    @if($coupon->valor_minimo)
      <div class="mt-4 pt-4 border-t">
        <label class="block text-sm font-medium text-gray-700">Valor Mínimo do Pedido</label>
        <div class="mt-1 text-lg font-semibold">R$ {{ number_format($coupon->valor_minimo, 2, ',', '.') }}</div>
      </div>
    @endif
    
    @if($coupon->data_inicio || $coupon->data_expiracao)
      <div class="mt-4 pt-4 border-t">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          @if($coupon->data_inicio)
            <div>
              <label class="block text-sm font-medium text-gray-700">Data de Início</label>
              <div class="mt-1">{{ \Carbon\Carbon::parse($coupon->data_inicio)->format('d/m/Y') }}</div>
            </div>
          @endif
          
          @if($coupon->data_expiracao)
            <div>
              <label class="block text-sm font-medium text-gray-700">Data de Expiração</label>
              <div class="mt-1">{{ \Carbon\Carbon::parse($coupon->data_expiracao)->format('d/m/Y') }}</div>
            </div>
          @endif
        </div>
      </div>
    @endif
  </x-card>

  <!-- Estatísticas -->
  <x-card class="mb-6">
    <h2 class="text-lg font-semibold mb-4">Estatísticas de Uso</h2>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <div class="text-center p-4 bg-blue-50 rounded-lg">
        <div class="text-2xl font-bold text-blue-600">{{ $coupon->usos_count ?? 0 }}</div>
        <div class="text-sm text-gray-600">Total de Usos</div>
      </div>
      
      <div class="text-center p-4 bg-green-50 rounded-lg">
        <div class="text-2xl font-bold text-green-600">{{ $coupon->limite_usos ? ($coupon->limite_usos - ($coupon->usos_count ?? 0)) : '∞' }}</div>
        <div class="text-sm text-gray-600">Usos Restantes</div>
      </div>
      
      <div class="text-center p-4 bg-orange-50 rounded-lg">
        <div class="text-2xl font-bold text-orange-600">R$ {{ number_format($coupon->valor_total_desconto ?? 0, 2, ',', '.') }}</div>
        <div class="text-sm text-gray-600">Total Desconto</div>
      </div>
      
      <div class="text-center p-4 bg-purple-50 rounded-lg">
        <div class="text-2xl font-bold text-purple-600">{{ $coupon->pedidos_count ?? 0 }}</div>
        <div class="text-sm text-gray-600">Pedidos</div>
      </div>
    </div>
  </x-card>

  <!-- Histórico de Uso -->
  <x-card>
    <h2 class="text-lg font-semibold mb-4">Histórico de Uso</h2>
    @if(isset($coupon->usos) && $coupon->usos->isEmpty())
      <div class="text-center py-8">
        <i class="fas fa-ticket-alt text-4xl text-gray-400 mb-4"></i>
        <p class="text-gray-500 text-lg">Cupom ainda não foi utilizado.</p>
        <p class="text-gray-400 text-sm mt-2">Os usos aparecerão aqui quando clientes utilizarem este cupom.</p>
      </div>
    @else
      <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
          <thead>
            <tr class="bg-gray-50 text-left text-sm font-medium text-gray-600">
              <th class="px-4 py-3">Cliente</th>
              <th class="px-4 py-3">Pedido</th>
              <th class="px-4 py-3">Valor Desconto</th>
              <th class="px-4 py-3">Data</th>
            </tr>
          </thead>
          <tbody class="text-sm text-gray-700">
            @foreach($coupon->usos ?? [] as $uso)
              <tr class="border-t hover:bg-gray-50">
                <td class="px-4 py-3">{{ $uso->cliente->nome ?? 'Cliente não encontrado' }}</td>
                <td class="px-4 py-3 font-medium">#{{ $uso->pedido_id }}</td>
                <td class="px-4 py-3 font-medium text-green-600">R$ {{ number_format($uso->valor_desconto, 2, ',', '.') }}</td>
                <td class="px-4 py-3">{{ $uso->created_at->format('d/m/Y H:i') }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </x-card>
</div>
@endsection
