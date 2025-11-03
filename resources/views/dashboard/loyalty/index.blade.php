@extends('dashboard.layouts.app')

@section('title', 'Fidelidade - OLIKA Dashboard')

@section('content')
<div class="space-y-6 animate-in fade-in duration-500">
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-3xl font-bold tracking-tight">Programa de Fidelidade</h1>
      <p class="text-muted-foreground">Recompense clientes fiéis e aumente o engajamento</p>
    </div>
    <a href="{{ route('dashboard.loyalty.create') }}" class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2">
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-plus"><path d="M5 12h14"></path><path d="M12 5v14"></path></svg>
      Nova Transação
    </a>
  </div>

  @if(session('success'))
    <div class="rounded-lg border bg-green-50 border-green-200 p-4 text-green-700 mb-4">
      {{ session('success') }}
    </div>
  @endif

  <div class="grid gap-4 md:grid-cols-4">
    <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
      <div class="p-6 pt-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-muted-foreground">Pontos Emitidos</p>
            <p class="text-2xl font-bold">{{ number_format($totalPointsEarned ?? 0, 0, ',', '.') }}</p>
          </div>
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-star h-8 w-8 text-primary">
            <path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"></path>
          </svg>
        </div>
      </div>
    </div>

    <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
      <div class="p-6 pt-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-muted-foreground">Pontos Resgatados</p>
            <p class="text-2xl font-bold">{{ number_format($totalPointsRedeemed ?? 0, 0, ',', '.') }}</p>
          </div>
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-gift h-8 w-8 text-green-600">
            <rect x="3" y="8" width="18" height="4" rx="1"></rect>
            <path d="M12 8v13"></path>
            <path d="M19 12v7a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-7"></path>
            <path d="M7.5 8a2.5 2.5 0 0 1 0-5A4.8 8 0 0 1 12 8a4.8 8 0 0 1 4.5-5 2.5 2.5 0 0 1 0 5"></path>
          </svg>
        </div>
      </div>
    </div>

    <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
      <div class="p-6 pt-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-muted-foreground">Pontos Disponíveis</p>
            <p class="text-2xl font-bold">{{ number_format($totalPointsAvailable ?? 0, 0, ',', '.') }}</p>
          </div>
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-award h-8 w-8 text-primary">
            <path d="m15.477 12.89 1.515 8.526a.5.5 0 0 1-.81.47l-3.58-2.687a1 1 0 0 0-1.197 0l-3.586 2.686a.5.5 0 0 1-.81-.469l1.514-8.526"></path>
            <circle cx="12" cy="8" r="6"></circle>
          </svg>
        </div>
      </div>
    </div>

    <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
      <div class="p-6 pt-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-muted-foreground">Clientes Ativos</p>
            <p class="text-2xl font-bold">{{ $activeCustomers ?? 0 }}</p>
          </div>
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-users h-8 w-8 text-primary">
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
        <p class="text-sm text-muted-foreground">Configure as regras do programa de fidelidade</p>
      </div>
      <div class="p-6 pt-0 space-y-6">
        <form action="{{ route('dashboard.loyalty.settings.save') }}" method="POST">
          @csrf
          
          <div class="flex items-center justify-between">
            <div class="space-y-0.5">
              <label class="text-sm font-medium leading-none" for="loyalty_enabled">Programa Ativo</label>
              <p class="text-sm text-muted-foreground">Ativar ou desativar o programa de fidelidade</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
              <input type="checkbox" name="loyalty_enabled" id="loyalty_enabled" value="1" {{ ($settings['enabled'] ?? true) ? 'checked' : '' }} onchange="this.parentElement.querySelector('.toggle-slider').classList.toggle('translate-x-5', this.checked); this.parentElement.querySelector('.toggle-bg').classList.toggle('bg-primary', this.checked); this.parentElement.querySelector('.toggle-bg').classList.toggle('bg-gray-300', !this.checked);" class="sr-only">
              <div class="toggle-bg w-11 h-6 {{ ($settings['enabled'] ?? true) ? 'bg-primary' : 'bg-gray-300' }} rounded-full transition-colors duration-200 ease-in-out"></div>
              <div class="toggle-slider absolute left-[2px] top-[2px] bg-white border border-gray-300 rounded-full h-5 w-5 transition-transform duration-200 ease-in-out {{ ($settings['enabled'] ?? true) ? 'translate-x-5' : '' }}"></div>
            </label>
          </div>

          <div class="grid gap-4 md:grid-cols-2">
            <div class="space-y-2">
              <label class="text-sm font-medium leading-none" for="points_per_real">Pontos por Real (R$)</label>
              <input type="number" name="points_per_real" id="points_per_real" step="0.01" min="0.01" value="{{ old('points_per_real', $settings['points_per_real'] ?? 10.0) }}" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 md:text-sm" required />
              <p class="text-sm text-muted-foreground">Quantos pontos o cliente ganha por R$ 1,00 gasto</p>
              @error('points_per_real')
                <span class="text-sm text-red-600">{{ $message }}</span>
              @enderror
            </div>
            <div class="space-y-2">
              <label class="text-sm font-medium leading-none" for="real_per_point">Valor por Ponto (R$)</label>
              <input type="number" name="real_per_point" id="real_per_point" step="0.001" min="0.001" value="{{ old('real_per_point', $settings['real_per_point'] ?? 0.10) }}" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 md:text-sm" required />
              <p class="text-sm text-muted-foreground">Valor em reais equivalente a cada ponto</p>
              @error('real_per_point')
                <span class="text-sm text-red-600">{{ $message }}</span>
              @enderror
            </div>
          </div>

          <div class="grid gap-4 md:grid-cols-2">
            <div class="space-y-2">
              <label class="text-sm font-medium leading-none" for="minimum_points">Pontos Mínimos para Resgate</label>
              <input type="number" name="minimum_points" id="minimum_points" min="1" value="{{ old('minimum_points', $settings['minimum_points'] ?? 100) }}" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 md:text-sm" required />
              @error('minimum_points')
                <span class="text-sm text-red-600">{{ $message }}</span>
              @enderror
            </div>
            <div class="space-y-2">
              <label class="text-sm font-medium leading-none" for="min_order_value">Pedido Mínimo (R$)</label>
              <input type="number" name="min_order_value" id="min_order_value" step="0.01" min="0" value="{{ old('min_order_value', $settings['min_order_value'] ?? 0) }}" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 md:text-sm" />
              <p class="text-sm text-muted-foreground">Valor mínimo do pedido para ganhar pontos</p>
              @error('min_order_value')
                <span class="text-sm text-red-600">{{ $message }}</span>
              @enderror
            </div>
          </div>

          <div class="space-y-2">
            <label class="text-sm font-medium leading-none" for="expiry_days">Validade dos Pontos (dias)</label>
            <input type="number" name="expiry_days" id="expiry_days" min="1" value="{{ old('expiry_days', $settings['expiry_days'] ?? 365) }}" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 md:text-sm" />
            <p class="text-sm text-muted-foreground">Deixe em branco para pontos sem expiração</p>
            @error('expiry_days')
              <span class="text-sm text-red-600">{{ $message }}</span>
            @enderror
          </div>

          <button type="submit" class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2 w-full">
            Salvar Configurações
          </button>
        </form>
      </div>
    </div>

    <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
      <div class="flex flex-col space-y-1.5 p-6">
        <h3 class="text-2xl font-semibold leading-none tracking-tight">Últimas Transações</h3>
        <p class="text-sm text-muted-foreground">Transações de pontos recentes</p>
      </div>
      <div class="p-6 pt-0">
        @if(isset($recentTransactions) && $recentTransactions->count() > 0)
        <div class="relative w-full overflow-auto">
          <table class="w-full caption-bottom text-sm">
            <thead class="[&_tr]:border-b">
              <tr class="border-b transition-colors">
                <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Cliente</th>
                <th class="h-12 px-4 align-middle font-medium text-muted-foreground text-right">Tipo</th>
                <th class="h-12 px-4 align-middle font-medium text-muted-foreground text-right">Pontos</th>
                <th class="h-12 px-4 align-middle font-medium text-muted-foreground">Descrição</th>
                <th class="h-12 px-4 align-middle font-medium text-muted-foreground text-right">Data</th>
                <th class="h-12 px-4 align-middle font-medium text-muted-foreground text-center">Ações</th>
              </tr>
            </thead>
            <tbody class="[&_tr:last-child]:border-0">
              @foreach($recentTransactions as $transaction)
              <tr class="border-b transition-colors hover:bg-muted/50">
                <td class="p-4 align-middle font-medium">{{ $transaction->customer->name ?? 'Cliente não encontrado' }}</td>
                <td class="p-4 align-middle text-right">
                  <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $transaction->type === 'earned' || $transaction->type === 'bonus' ? 'bg-green-100 text-green-800' : ($transaction->type === 'redeemed' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800') }}">
                    {{ $transaction->type_label }}
                  </span>
                </td>
                <td class="p-4 align-middle text-right font-semibold {{ $transaction->type === 'earned' || $transaction->type === 'bonus' ? 'text-green-600' : ($transaction->type === 'redeemed' ? 'text-red-600' : 'text-gray-600') }}">
                  {{ $transaction->type === 'redeemed' ? '-' : '+' }}{{ number_format($transaction->points, 0, ',', '.') }}
                </td>
                <td class="p-4 align-middle text-sm text-muted-foreground">{{ $transaction->description ?? '-' }}</td>
                <td class="p-4 align-middle text-right text-muted-foreground">{{ $transaction->created_at->format('d/m/Y H:i') }}</td>
                <td class="p-4 align-middle text-center">
                  <a href="{{ route('dashboard.loyalty.edit', $transaction) }}" class="inline-flex items-center text-sm text-primary hover:underline">
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
          <p>Nenhuma transação de pontos registrada ainda.</p>
        </div>
        @endif
      </div>
    </div>
  </div>

  @if(isset($topCustomers) && $topCustomers->count() > 0)
  <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
    <div class="flex flex-col space-y-1.5 p-6">
      <h3 class="text-2xl font-semibold leading-none tracking-tight">Top Clientes</h3>
      <p class="text-sm text-muted-foreground">Clientes com mais pontos disponíveis</p>
    </div>
    <div class="p-6 pt-0">
      <div class="relative w-full overflow-auto">
        <table class="w-full caption-bottom text-sm">
          <thead class="[&_tr]:border-b">
            <tr class="border-b transition-colors">
              <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Cliente</th>
              <th class="h-12 px-4 align-middle font-medium text-muted-foreground text-right">Pontos Ganhos</th>
              <th class="h-12 px-4 align-middle font-medium text-muted-foreground text-right">Pontos Resgatados</th>
              <th class="h-12 px-4 align-middle font-medium text-muted-foreground text-right">Pontos Disponíveis</th>
            </tr>
          </thead>
          <tbody class="[&_tr:last-child]:border-0">
            @foreach($topCustomers as $customer)
            <tr class="border-b transition-colors hover:bg-muted/50">
              <td class="p-4 align-middle font-medium">{{ $customer->name }}</td>
              <td class="p-4 align-middle text-right text-green-600">{{ number_format($customer->total_earned ?? 0, 0, ',', '.') }}</td>
              <td class="p-4 align-middle text-right text-red-600">{{ number_format($customer->total_redeemed ?? 0, 0, ',', '.') }}</td>
              <td class="p-4 align-middle text-right font-semibold text-primary">{{ number_format($customer->available_points ?? 0, 0, ',', '.') }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
  @endif
</div>
@endsection
