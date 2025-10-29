@extends('layouts.admin')

@section('title', isset($coupon) ? 'Editar Cupom' : 'Novo Cupom')
@section('page_title', isset($coupon) ? 'Editar Cupom' : 'Novo Cupom')

@section('content')
<div class="container-page">
  <div class="mb-6">
    <h1 class="text-2xl font-bold">
      {{ isset($coupon) ? 'Editar Cupom' : 'Novo Cupom' }}
    </h1>
    <p class="text-gray-600 mt-2">
      {{ isset($coupon) ? 'Atualize as informações do cupom' : 'Crie um novo cupom de desconto' }}
    </p>
  </div>

  @if ($errors->any())
    <x-alert type="danger">
      <ul class="list-disc pl-5 text-sm">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </x-alert>
  @endif

  <x-card>
    <form method="POST" action="{{ isset($coupon) ? route('dashboard.coupons.update', $coupon) : route('dashboard.coupons.store') }}" class="space-y-6">
      @csrf
      @if(isset($coupon))
        @method('PUT')
      @endif

      <x-form-group label="Código do Cupom" required>
        <x-input name="codigo" value="{{ old('codigo', $coupon->codigo ?? '') }}" placeholder="Ex: DESCONTO10" />
        <p class="text-xs text-gray-500 mt-1">Código que o cliente digitará no checkout</p>
      </x-form-group>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <x-form-group label="Tipo de Desconto" required>
          <select name="tipo" class="input w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500">
            <option value="fixo" {{ old('tipo', $coupon->tipo ?? '') == 'fixo' ? 'selected' : '' }}>Valor Fixo (R$)</option>
            <option value="porcentagem" {{ old('tipo', $coupon->tipo ?? '') == 'porcentagem' ? 'selected' : '' }}>Porcentagem (%)</option>
          </select>
        </x-form-group>

        <x-form-group label="Valor do Desconto" required>
          <x-input type="number" name="valor" step="0.01" value="{{ old('valor', $coupon->valor ?? '') }}" placeholder="0.00" />
          <p class="text-xs text-gray-500 mt-1">Valor em R$ ou percentual conforme o tipo</p>
        </x-form-group>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <x-form-group label="Limite de Usos">
          <x-input type="number" name="limite_usos" value="{{ old('limite_usos', $coupon->limite_usos ?? '') }}" placeholder="Deixe vazio para ilimitado" />
          <p class="text-xs text-gray-500 mt-1">Quantas vezes este cupom pode ser usado</p>
        </x-form-group>

        <x-form-group label="Valor Mínimo do Pedido">
          <x-input type="number" name="valor_minimo" step="0.01" value="{{ old('valor_minimo', $coupon->valor_minimo ?? '') }}" placeholder="0.00" />
          <p class="text-xs text-gray-500 mt-1">Valor mínimo para usar o cupom</p>
        </x-form-group>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <x-form-group label="Data de Início">
          <x-input type="date" name="data_inicio" value="{{ old('data_inicio', $coupon->data_inicio ?? '') }}" />
        </x-form-group>

        <x-form-group label="Data de Expiração">
          <x-input type="date" name="data_expiracao" value="{{ old('data_expiracao', $coupon->data_expiracao ?? '') }}" />
        </x-form-group>
      </div>

      <div class="space-y-4">
        <h3 class="text-lg font-semibold text-gray-700">Configurações</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <label class="inline-flex items-center p-3 border rounded-lg hover:bg-gray-50 cursor-pointer">
            <input type="checkbox" name="publico" value="1" {{ old('publico', $coupon->publico ?? false) ? 'checked' : '' }} class="rounded border-gray-300 text-orange-600 shadow-sm focus:border-orange-300 focus:ring focus:ring-orange-200 focus:ring-opacity-50">
            <div class="ml-3">
              <div class="text-sm font-medium text-gray-700">Público</div>
              <div class="text-xs text-gray-500">Visível para todos os clientes</div>
            </div>
          </label>

          <label class="inline-flex items-center p-3 border rounded-lg hover:bg-gray-50 cursor-pointer">
            <input type="checkbox" name="uso_unico" value="1" {{ old('uso_unico', $coupon->uso_unico ?? false) ? 'checked' : '' }} class="rounded border-gray-300 text-orange-600 shadow-sm focus:border-orange-300 focus:ring focus:ring-orange-200 focus:ring-opacity-50">
            <div class="ml-3">
              <div class="text-sm font-medium text-gray-700">Uso Único</div>
              <div class="text-xs text-gray-500">Apenas uma vez por cliente</div>
            </div>
          </label>

          <label class="inline-flex items-center p-3 border rounded-lg hover:bg-gray-50 cursor-pointer">
            <input type="checkbox" name="ativo" value="1" {{ old('ativo', $coupon->ativo ?? true) ? 'checked' : '' }} class="rounded border-gray-300 text-orange-600 shadow-sm focus:border-orange-300 focus:ring focus:ring-orange-200 focus:ring-opacity-50">
            <div class="ml-3">
              <div class="text-sm font-medium text-gray-700">Ativo</div>
              <div class="text-xs text-gray-500">Cupom disponível para uso</div>
            </div>
          </label>
        </div>
      </div>

      <x-form-group label="Descrição">
        <textarea name="descricao" rows="3" class="input w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500" placeholder="Descrição opcional do cupom">{{ old('descricao', $coupon->descricao ?? '') }}</textarea>
      </x-form-group>

      <div class="pt-4 flex gap-2">
        <x-button variant="primary" type="submit">
          <i class="fas fa-save mr-2"></i>{{ isset($coupon) ? 'Atualizar Cupom' : 'Criar Cupom' }}
        </x-button>
        <a href="{{ route('dashboard.coupons.index') }}" class="btn btn-secondary">
          <i class="fas fa-arrow-left mr-2"></i> Voltar
        </a>
      </div>
    </form>
  </x-card>

  @if(isset($coupon))
    <x-card class="mt-6">
      <h3 class="text-lg font-semibold mb-4">Estatísticas do Cupom</h3>
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="text-center p-4 bg-blue-50 rounded-lg">
          <div class="text-2xl font-bold text-blue-600">{{ $coupon->usos_count ?? 0 }}</div>
          <div class="text-sm text-gray-600">Usos</div>
        </div>
        <div class="text-center p-4 bg-green-50 rounded-lg">
          <div class="text-2xl font-bold text-green-600">{{ $coupon->limite_usos ?? '∞' }}</div>
          <div class="text-sm text-gray-600">Limite</div>
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
  @endif
</div>
@endsection
