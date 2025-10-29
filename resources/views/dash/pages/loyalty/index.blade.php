@extends('layouts.admin')

@section('title', 'Fidelidade')
@section('page_title', 'Programa de Fidelidade')

@section('content')
<div class="container-page">
  <div class="mb-6">
    <h1 class="text-2xl font-bold">Programa de Fidelidade</h1>
    <p class="text-gray-600 mt-2">Configure os benefícios do programa de fidelidade para seus clientes</p>
  </div>

  @if(session('status'))
    <x-alert type="success">{{ session('status') }}</x-alert>
  @endif

  @if(session('error'))
    <x-alert type="error">{{ session('error') }}</x-alert>
  @endif

  @if ($errors->any())
    <x-alert type="danger">
      <ul class="list-disc pl-5 text-sm">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </x-alert>
  @endif

  <!-- Cashback -->
  <x-card class="mb-6">
    <div class="flex items-center mb-4">
      <i class="fas fa-coins text-orange-600 mr-3"></i>
      <h2 class="text-lg font-semibold">Cashback</h2>
    </div>
    <p class="text-sm text-gray-600 mb-4">Configure o percentual de cashback que os clientes recebem em cada compra.</p>
    
    <form method="POST" action="{{ route('dashboard.loyalty.update.cashback') }}" class="space-y-4">
      @csrf
      <x-form-group label="% de Cashback por compra">
        <x-input type="number" name="percentual" step="0.1" value="{{ old('percentual', $cashbackPercent ?? 0) }}" placeholder="0.0" />
        <p class="text-xs text-gray-500 mt-1">Não inclui taxa de entrega nem descontos aplicados.</p>
      </x-form-group>
      
      <div class="flex gap-2">
        <x-button variant="primary" type="submit">
          <i class="fas fa-save mr-2"></i> Atualizar Cashback
        </x-button>
        <x-button variant="secondary" type="button" onclick="document.querySelector('input[name=\"percentual\"]').value = '0'">
          <i class="fas fa-times mr-2"></i> Limpar
        </x-button>
      </div>
    </form>
  </x-card>

  <!-- Indicação -->
  <x-card class="mb-6">
    <div class="flex items-center mb-4">
      <i class="fas fa-users text-blue-600 mr-3"></i>
      <h2 class="text-lg font-semibold">Indicação de Clientes</h2>
    </div>
    <p class="text-sm text-gray-600 mb-4">Configure o bônus para clientes que indicam novos compradores.</p>
    
    <form method="POST" action="{{ route('dashboard.loyalty.update.indicacao') }}" class="space-y-4">
      @csrf
      <x-form-group label="% de bônus por compra do indicado">
        <x-input type="number" name="percentual_indicacao" step="0.1" value="{{ old('percentual_indicacao', $indicacaoPercent ?? 0) }}" placeholder="0.0" />
        <p class="text-xs text-gray-500 mt-1">Valor baseado no total da compra do indicado, sem entrega nem cupons.</p>
      </x-form-group>
      
      <div class="flex gap-2">
        <x-button variant="primary" type="submit">
          <i class="fas fa-save mr-2"></i> Atualizar Bônus de Indicação
        </x-button>
        <x-button variant="secondary" type="button" onclick="document.querySelector('input[name=\"percentual_indicacao\"]').value = '0'">
          <i class="fas fa-times mr-2"></i> Limpar
        </x-button>
      </div>
    </form>
  </x-card>

  <!-- Bônus por Pedidos -->
  <x-card>
    <div class="flex items-center mb-4">
      <i class="fas fa-gift text-green-600 mr-3"></i>
      <h2 class="text-lg font-semibold">Bônus por Quantidade de Pedidos</h2>
    </div>
    <p class="text-sm text-gray-600 mb-4">Configure bônus especiais baseados no número de pedidos realizados pelo cliente.</p>
    
    <form method="POST" action="{{ route('dashboard.loyalty.update.bonus') }}" class="space-y-4">
      @csrf
      
      <div class="space-y-3">
        <div class="grid grid-cols-3 gap-4 text-sm font-medium text-gray-600 mb-2">
          <div>Quantidade de Pedidos</div>
          <div>Valor do Bônus (R$)</div>
          <div class="text-right">Ações</div>
        </div>
        
        @foreach($bonusPedidos ?? [] as $i => $bonus)
          <div class="grid grid-cols-3 gap-4 items-center p-3 bg-gray-50 rounded-lg">
            <x-input type="number" name="bonus[{{ $i }}][qtd]" value="{{ $bonus['qtd'] }}" placeholder="Ex: 5" />
            <x-input type="number" name="bonus[{{ $i }}][valor]" step="0.01" value="{{ $bonus['valor'] }}" placeholder="Ex: 10.00" />
            <div class="text-right">
              <x-button variant="danger" size="sm" type="button" onclick="this.closest('.grid').remove()">
                <i class="fas fa-trash"></i>
              </x-button>
            </div>
          </div>
        @endforeach
        
        <!-- Novo bônus -->
        <div class="grid grid-cols-3 gap-4 items-center p-3 border-2 border-dashed border-gray-300 rounded-lg">
          <x-input type="number" name="bonus[new][qtd]" placeholder="Nova quantidade" />
          <x-input type="number" name="bonus[new][valor]" step="0.01" placeholder="Novo bônus (R$)" />
          <div class="text-right">
            <x-badge type="info">Novo</x-badge>
          </div>
        </div>
      </div>
      
      <div class="flex gap-2">
        <x-button variant="primary" type="submit">
          <i class="fas fa-save mr-2"></i> Atualizar Bônus por Pedidos
        </x-button>
        <x-button variant="secondary" type="button" onclick="addNewBonus()">
          <i class="fas fa-plus mr-2"></i> Adicionar Mais
        </x-button>
      </div>
    </form>
  </x-card>

  <!-- Resumo -->
  <x-card class="mt-6">
    <div class="flex items-center mb-4">
      <i class="fas fa-chart-pie text-purple-600 mr-3"></i>
      <h2 class="text-lg font-semibold">Resumo do Programa</h2>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div class="text-center p-4 bg-orange-50 rounded-lg">
        <div class="text-2xl font-bold text-orange-600">{{ $cashbackPercent ?? 0 }}%</div>
        <div class="text-sm text-gray-600">Cashback por compra</div>
      </div>
      
      <div class="text-center p-4 bg-blue-50 rounded-lg">
        <div class="text-2xl font-bold text-blue-600">{{ $indicacaoPercent ?? 0 }}%</div>
        <div class="text-sm text-gray-600">Bônus por indicação</div>
      </div>
      
      <div class="text-center p-4 bg-green-50 rounded-lg">
        <div class="text-2xl font-bold text-green-600">{{ count($bonusPedidos ?? []) }}</div>
        <div class="text-sm text-gray-600">Marcas de bônus</div>
      </div>
    </div>
  </x-card>
</div>

<script>
function addNewBonus() {
  const container = document.querySelector('.space-y-3');
  const newBonus = document.createElement('div');
  newBonus.className = 'grid grid-cols-3 gap-4 items-center p-3 border-2 border-dashed border-gray-300 rounded-lg';
  newBonus.innerHTML = `
    <input type="number" name="bonus[new][qtd]" placeholder="Nova quantidade" class="input w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500">
    <input type="number" name="bonus[new][valor]" step="0.01" placeholder="Novo bônus (R$)" class="input w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500">
    <div class="text-right">
      <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">Novo</span>
    </div>
  `;
  container.appendChild(newBonus);
}
</script>
@endsection