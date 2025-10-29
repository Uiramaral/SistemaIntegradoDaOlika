@extends('layouts.admin')

@section('title', 'PDV - Ponto de Venda')
@section('page_title', 'PDV - Ponto de Venda')

@section('content')
<div class="container-page">
  <div class="mb-6">
    <h1 class="text-2xl font-bold">PDV - Ponto de Venda</h1>
    <p class="text-gray-600 mt-2">Sistema integrado para vendas presenciais</p>
  </div>

  @if(session('status'))
    <x-alert type="success">{{ session('status') }}</x-alert>
  @endif

  @if(session('error'))
    <x-alert type="error">{{ session('error') }}</x-alert>
  @endif

  <!-- Cliente e Carrinho -->
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <!-- Cliente -->
    <x-card>
      <div class="flex items-center mb-4">
        <i class="fas fa-user text-blue-600 mr-3"></i>
        <h2 class="text-lg font-semibold">Cliente</h2>
      </div>
      
      <form id="form-cliente" class="space-y-3">
        <x-form-group label="Buscar Cliente">
          <x-input type="text" placeholder="Digite nome ou telefone" id="cliente-busca" />
          <div id="sugestoes-cliente" class="hidden mt-2 bg-white border rounded-lg shadow-lg max-h-40 overflow-y-auto">
            <!-- Sugestões aparecerão aqui -->
          </div>
        </x-form-group>

        <div id="cliente-dados" class="space-y-3 hidden">
          <x-form-group label="Nome">
            <x-input type="text" placeholder="Nome completo" id="cliente-nome" />
          </x-form-group>
          
          <x-form-group label="Telefone">
            <x-input type="text" placeholder="(11) 99999-9999" id="cliente-telefone" />
          </x-form-group>
          
          <div class="grid grid-cols-2 gap-3">
            <x-form-group label="CEP">
              <x-input type="text" placeholder="00000-000" id="cliente-cep" />
            </x-form-group>
            <x-form-group label="Número">
              <x-input type="text" placeholder="123" id="cliente-numero" />
            </x-form-group>
          </div>
          
          <x-form-group label="Rua">
            <x-input type="text" placeholder="Nome da rua" id="cliente-rua" disabled />
          </x-form-group>
          
          <x-form-group label="Bairro">
            <x-input type="text" placeholder="Nome do bairro" id="cliente-bairro" disabled />
          </x-form-group>
          
          <x-form-group label="Cidade">
            <x-input type="text" placeholder="Nome da cidade" id="cliente-cidade" disabled />
          </x-form-group>
          
          <div class="flex gap-2">
            <x-button variant="success" size="sm" type="button" id="btn-salvar-cliente">
              <i class="fas fa-save mr-1"></i> Salvar
            </x-button>
            <x-button variant="secondary" size="sm" type="button" id="btn-limpar-cliente">
              <i class="fas fa-times mr-1"></i> Limpar
            </x-button>
          </div>
        </div>
      </form>
    </x-card>

    <!-- Carrinho -->
    <x-card class="lg:col-span-2">
      <div class="flex items-center justify-between mb-4">
        <div class="flex items-center">
          <i class="fas fa-shopping-cart text-orange-600 mr-3"></i>
          <h2 class="text-lg font-semibold">Carrinho</h2>
        </div>
        <x-badge type="info" id="contador-itens">0 itens</x-badge>
      </div>
      
      <div id="lista-itens" class="space-y-3 min-h-32">
        <div class="text-center py-8 text-gray-500">
          <i class="fas fa-shopping-cart text-3xl mb-2"></i>
          <p>Nenhum item no carrinho</p>
          <p class="text-sm">Adicione produtos para começar a venda</p>
        </div>
      </div>

      <!-- Cupons e Descontos -->
      <div class="mt-6 pt-4 border-t">
        <h3 class="text-md font-semibold mb-3">Cupons e Descontos</h3>
        
        <div class="flex gap-2 mb-3">
          <x-input type="text" placeholder="Código do cupom" id="cupom" class="flex-1" />
          <x-button variant="secondary" id="btn-aplicar-cupom">
            <i class="fas fa-ticket-alt mr-1"></i> Aplicar
          </x-button>
        </div>
        
        <div class="grid grid-cols-2 gap-3">
          <x-form-group label="Desconto R$">
            <x-input type="number" step="0.01" placeholder="0.00" id="desconto-reais" />
          </x-form-group>
          <x-form-group label="Desconto %">
            <x-input type="number" step="0.1" placeholder="0.0" id="desconto-pct" />
          </x-form-group>
        </div>
      </div>

      <!-- Total -->
      <div class="mt-6 pt-4 border-t">
        <div class="flex justify-between items-center text-lg">
          <span class="font-semibold">Total:</span>
          <span class="text-2xl font-bold text-orange-600" id="total-geral">R$ 0,00</span>
        </div>
        <div class="text-sm text-gray-500 mt-1">
          <span id="desconto-aplicado">Sem desconto</span>
        </div>
      </div>
    </x-card>
  </div>

  <!-- Produtos e Finalização -->
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Produtos -->
    <x-card class="lg:col-span-2">
      <div class="flex items-center justify-between mb-4">
        <div class="flex items-center">
          <i class="fas fa-box text-green-600 mr-3"></i>
          <h2 class="text-lg font-semibold">Produtos</h2>
        </div>
        <x-badge type="success" id="contador-produtos">0 produtos</x-badge>
      </div>
      
      <x-form-group label="Buscar Produtos">
        <x-input type="text" placeholder="Digite o nome do produto" id="busca-produto" />
      </x-form-group>
      
      <div id="lista-produtos" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mt-4">
        <!-- Cards de produtos carregados por JS -->
        <div class="col-span-full text-center py-8 text-gray-500">
          <i class="fas fa-spinner fa-spin text-3xl mb-2"></i>
          <p>Carregando produtos...</p>
        </div>
      </div>
    </x-card>

    <!-- Finalizar -->
    <x-card>
      <div class="flex items-center mb-4">
        <i class="fas fa-credit-card text-purple-600 mr-3"></i>
        <h2 class="text-lg font-semibold">Finalizar Venda</h2>
      </div>
      
      <div class="space-y-3">
        <x-button variant="success" size="lg" class="w-full" id="btn-pagar">
          <i class="fas fa-credit-card mr-2"></i> Pagamento com Mercado Pago
        </x-button>
        
        <x-button variant="info" size="lg" class="w-full" id="btn-whatsapp">
          <i class="fab fa-whatsapp mr-2"></i> Enviar pedido por WhatsApp
        </x-button>
        
        <x-button variant="warning" size="lg" class="w-full" id="btn-fiado">
          <i class="fas fa-hand-holding-usd mr-2"></i> Venda no Fiado
        </x-button>
        
        <x-button variant="danger" size="lg" class="w-full" id="btn-cancelar">
          <i class="fas fa-times mr-2"></i> Cancelar Venda
        </x-button>
      </div>
      
      <!-- Resumo da Venda -->
      <div class="mt-6 pt-4 border-t">
        <h3 class="text-md font-semibold mb-3">Resumo</h3>
        <div class="space-y-2 text-sm">
          <div class="flex justify-between">
            <span>Subtotal:</span>
            <span id="subtotal">R$ 0,00</span>
          </div>
          <div class="flex justify-between">
            <span>Desconto:</span>
            <span id="desconto-total" class="text-green-600">R$ 0,00</span>
          </div>
          <div class="flex justify-between font-semibold border-t pt-2">
            <span>Total:</span>
            <span id="total-final">R$ 0,00</span>
          </div>
        </div>
      </div>
    </x-card>
  </div>
</div>

<!-- Modal de Confirmação -->
<div id="modal-confirmacao" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
  <div class="flex items-center justify-center min-h-screen p-4">
    <div class="bg-white rounded-lg p-6 max-w-md w-full">
      <h3 class="text-lg font-semibold mb-4" id="modal-titulo">Confirmar Ação</h3>
      <p class="text-gray-600 mb-6" id="modal-mensagem">Tem certeza que deseja continuar?</p>
      <div class="flex gap-2 justify-end">
        <x-button variant="secondary" id="btn-modal-cancelar">Cancelar</x-button>
        <x-button variant="primary" id="btn-modal-confirmar">Confirmar</x-button>
      </div>
    </div>
  </div>
</div>

@endsection

@push('styles')
<style>
  .produto-card {
    @apply bg-white border rounded-lg p-3 hover:shadow-md transition-shadow cursor-pointer;
  }
  .produto-card:hover {
    @apply border-orange-300;
  }
  .item-carrinho {
    @apply bg-gray-50 border rounded-lg p-3 flex items-center justify-between;
  }
  .sugestao-cliente {
    @apply p-2 hover:bg-gray-100 cursor-pointer border-b last:border-b-0;
  }
  .sugestao-cliente:hover {
    @apply bg-orange-50;
  }
</style>
@endpush

@push('scripts')
<script src="{{ asset('js/pdv.js') }}"></script>
@endpush