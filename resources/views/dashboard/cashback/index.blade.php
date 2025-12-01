@extends('dashboard.layouts.app')

@section('page_title', 'Programa de Cashback')
@section('page_subtitle', 'Recompense seus clientes fiéis com cashback em compras')

@section('page_actions')
    <a href="{{ route('dashboard.cashback.create') }}" class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-plus"><path d="M5 12h14"></path><path d="M12 5v14"></path></svg>
        Nova Transação
    </a>
@endsection

@section('content')
<div class="space-y-6">

  @if(session('success'))
    <div class="rounded-lg border bg-green-50 border-green-200 p-4 text-green-700 mb-4">
      {{ session('success') }}
    </div>
  @endif

  <div class="grid grid-cols-4 gap-3">
    <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
      <div class="p-4">
        <div class="flex items-center justify-between">
          <div class="flex-1 min-w-0">
            <p class="text-xs text-muted-foreground mb-1">Total Gerado</p>
            <p class="text-xl font-bold">R$ {{ number_format($totalCredits ?? 0, 2, ',', '.') }}</p>
          </div>
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-dollar-sign h-5 w-5 text-primary flex-shrink-0 ml-2">
            <line x1="12" x2="12" y1="2" y2="22"></line>
            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
          </svg>
        </div>
      </div>
    </div>
    
    <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
      <div class="p-4">
        <div class="flex items-center justify-between">
          <div class="flex-1 min-w-0">
            <p class="text-xs text-muted-foreground mb-1">Total Utilizado</p>
            <p class="text-xl font-bold">R$ {{ number_format($totalDebits ?? 0, 2, ',', '.') }}</p>
          </div>
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trending-down h-5 w-5 text-red-600 flex-shrink-0 ml-2">
            <polyline points="22 17 13.5 8.5 8.5 13.5 2 7"></polyline>
            <polyline points="16 17 22 17 22 11"></polyline>
          </svg>
        </div>
      </div>
    </div>
    
    <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
      <div class="p-4">
        <div class="flex items-center justify-between">
          <div class="flex-1 min-w-0">
            <p class="text-xs text-muted-foreground mb-1">Saldo Disponível</p>
            <p class="text-xl font-bold">R$ {{ number_format($totalAvailable ?? 0, 2, ',', '.') }}</p>
          </div>
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-award h-5 w-5 text-primary flex-shrink-0 ml-2">
            <path d="m15.477 12.89 1.515 8.526a.5.5 0 0 1-.81.47l-3.58-2.687a1 1 0 0 0-1.197 0l-3.586 2.686a.5.5 0 0 1-.81-.469l1.514-8.526"></path>
            <circle cx="12" cy="8" r="6"></circle>
          </svg>
        </div>
      </div>
    </div>
    
    <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
      <div class="p-4">
        <div class="flex items-center justify-between">
          <div class="flex-1 min-w-0">
            <p class="text-xs text-muted-foreground mb-1">Clientes com Saldo</p>
            <p class="text-xl font-bold">{{ $activeCustomers ?? 0 }}</p>
          </div>
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-users h-5 w-5 text-primary flex-shrink-0 ml-2">
            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
            <circle cx="9" cy="7" r="4"></circle>
            <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
          </svg>
        </div>
      </div>
    </div>
  </div>
  
  <div class="grid gap-6 lg:grid-cols-2">
    <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
      <div class="flex flex-col space-y-1.5 p-6">
        <h3 class="text-2xl font-semibold leading-none tracking-tight">Configurações do Programa</h3>
        <p class="text-sm text-muted-foreground">Configure as regras do programa de cashback</p>
      </div>
      <div class="p-6 pt-0 space-y-6">
        <form action="{{ route('dashboard.cashback.settings.save') }}" method="POST" id="cashbackSettingsForm">
          @csrf
          
          <div class="flex items-center justify-between">
            <div class="space-y-0.5">
              <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70" for="cashback_enabled">Programa Ativo</label>
              <p class="text-sm text-muted-foreground">Ativar ou desativar o programa de cashback</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
              <input type="checkbox" name="cashback_enabled" id="cashback_enabled" value="1" {{ ($cashbackSettings['enabled'] ?? true) ? 'checked' : '' }} onchange="this.parentElement.querySelector('.toggle-slider').classList.toggle('translate-x-5', this.checked); this.parentElement.querySelector('.toggle-bg').classList.toggle('bg-primary', this.checked); this.parentElement.querySelector('.toggle-bg').classList.toggle('bg-gray-300', !this.checked);" class="sr-only">
              <div class="toggle-bg w-11 h-6 {{ ($cashbackSettings['enabled'] ?? true) ? 'bg-primary' : 'bg-gray-300' }} rounded-full transition-colors duration-200 ease-in-out"></div>
              <div class="toggle-slider absolute left-[2px] top-[2px] bg-white border border-gray-300 rounded-full h-5 w-5 transition-transform duration-200 ease-in-out {{ ($cashbackSettings['enabled'] ?? true) ? 'translate-x-5' : '' }}"></div>
            </label>
          </div>
          
          <div class="space-y-2">
            <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70" for="cashback_percentage">Percentual de Cashback (%)</label>
            <input type="number" name="cashback_percentage" id="cashback_percentage" step="0.1" min="0" max="100" value="{{ old('cashback_percentage', $cashbackSettings['percentage'] ?? 5.0) }}" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm" required />
            <p class="text-sm text-muted-foreground">Porcentagem do valor da compra devolvida como cashback</p>
            @error('cashback_percentage')
              <span class="text-sm text-red-600">{{ $message }}</span>
            @enderror
          </div>
          
          <div class="space-y-2">
            <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70" for="cashback_min_purchase">Compra Mínima (R$)</label>
            <input type="number" name="cashback_min_purchase" id="cashback_min_purchase" step="0.01" min="0" value="{{ old('cashback_min_purchase', $cashbackSettings['min_purchase'] ?? 30.0) }}" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm" placeholder="0.00" required />
            <p class="text-sm text-muted-foreground">Valor mínimo da compra para receber cashback</p>
            @error('cashback_min_purchase')
              <span class="text-sm text-red-600">{{ $message }}</span>
            @enderror
          </div>
          
          <div class="space-y-2">
            <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70" for="cashback_max_amount">Cashback Máximo por Compra (R$)</label>
            <input type="number" name="cashback_max_amount" id="cashback_max_amount" step="0.01" min="0" value="{{ old('cashback_max_amount', $cashbackSettings['max_amount'] ?? 50.0) }}" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm" placeholder="0.00" />
            <p class="text-sm text-muted-foreground">Valor máximo de cashback por compra (deixe 0 para ilimitado)</p>
            @error('cashback_max_amount')
              <span class="text-sm text-red-600">{{ $message }}</span>
            @enderror
          </div>
          
          <div class="space-y-2">
            <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70" for="cashback_expiry_days">Validade do Cashback (dias)</label>
            <input type="number" name="cashback_expiry_days" id="cashback_expiry_days" min="1" value="{{ old('cashback_expiry_days', $cashbackSettings['expiry_days'] ?? 90) }}" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm" placeholder="90" required />
            <p class="text-sm text-muted-foreground">Tempo até o cashback expirar se não for utilizado</p>
            @error('cashback_expiry_days')
              <span class="text-sm text-red-600">{{ $message }}</span>
            @enderror
          </div>
          
          <button type="submit" class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2 w-full">
            Salvar Configurações
          </button>
        </form>
      </div>
    </div>
    
    <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
      <div class="flex flex-col space-y-1.5 p-6">
        <h3 class="text-2xl font-semibold leading-none tracking-tight">Últimas Transações</h3>
        <p class="text-sm text-muted-foreground">Transações de cashback recentes</p>
      </div>
      <div class="p-6 pt-0">
        @if(isset($recentTransactions) && $recentTransactions->count() > 0)
        <div class="relative w-full overflow-auto">
          <table class="w-full caption-bottom text-sm" data-mobile-card="true">
            <thead class="[&_tr]:border-b">
              <tr class="border-b transition-colors data-[state=selected]:bg-muted hover:bg-muted/50">
                <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Cliente</th>
                <th class="h-12 px-4 align-middle font-medium text-muted-foreground text-right">Tipo</th>
                <th class="h-12 px-4 align-middle font-medium text-muted-foreground text-right">Valor</th>
                <th class="h-12 px-4 align-middle font-medium text-muted-foreground">Descrição</th>
                <th class="h-12 px-4 align-middle font-medium text-muted-foreground text-right">Data</th>
                <th class="h-12 px-4 align-middle font-medium text-muted-foreground text-center">Ações</th>
              </tr>
            </thead>
            <tbody class="[&_tr:last-child]:border-0">
              @foreach($recentTransactions as $transaction)
              <tr class="border-b transition-colors data-[state=selected]:bg-muted hover:bg-muted/50">
                <td class="p-4 align-middle font-medium">{{ $transaction->customer->name ?? 'Cliente não encontrado' }}</td>
                <td class="p-4 align-middle text-right">
                  <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $transaction->type === 'credit' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                    {{ $transaction->type === 'credit' ? 'Crédito' : 'Débito' }}
                  </span>
                </td>
                <td class="p-4 align-middle text-right font-semibold {{ $transaction->type === 'credit' ? 'text-green-600' : 'text-red-600' }}">
                  {{ $transaction->type === 'credit' ? '+' : '-' }}R$ {{ number_format((float)$transaction->amount, 2, ',', '.') }}
                </td>
                <td class="p-4 align-middle text-sm text-muted-foreground">{{ $transaction->description ?? '-' }}</td>
                <td class="p-4 align-middle text-right text-muted-foreground">{{ $transaction->created_at->format('d/m/Y H:i') }}</td>
                <td class="p-4 align-middle text-center actions-cell">
                  <a href="{{ route('dashboard.cashback.edit', $transaction) }}" class="inline-flex items-center text-sm text-primary hover:underline">
                    Editar
                  </a>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        @else
        <div class="text-center py-8 text-muted-foreground">
          <p>Nenhuma transação de cashback registrada ainda.</p>
        </div>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection
