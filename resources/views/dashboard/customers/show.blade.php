@extends('dashboard.layouts.app')

@section('title', 'Cliente - OLIKA Dashboard')

@section('content')
<div class="space-y-6 animate-in fade-in duration-500">
    @if(session('success'))
        <div class="rounded-lg border bg-green-50 text-green-900 px-4 py-3">
            <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check-circle">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
                {{ session('success') }}
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="rounded-lg border bg-red-50 text-red-900 px-4 py-3">
            <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-alert-circle">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                {{ session('error') }}
            </div>
        </div>
    @endif

    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('dashboard.customers.index') }}" class="inline-flex items-center justify-center rounded-md p-2 hover:bg-accent hover:text-accent-foreground">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-left h-5 w-5">
                    <path d="m12 19-7-7 7-7"></path>
                    <path d="M19 12H5"></path>
                </svg>
            </a>
    <div>
                <h1 class="text-3xl font-bold tracking-tight">{{ $customer->name ?? 'Cliente' }}</h1>
                <p class="text-muted-foreground">Detalhes do cliente</p>
            </div>
    </div>
        <div class="flex gap-2">
            @php
                $pendingDebtsCount = \App\Models\CustomerDebt::where('customer_id', $customer->id)
                    ->where('type', 'debit')
                    ->where('status', 'open')
                    ->count();
            @endphp
            @if($pendingDebtsCount > 0)
            <form action="{{ route('dashboard.customers.sendPendingOrders', $customer->id) }}" method="POST" class="inline">
                @csrf
                <button type="submit" onclick="return confirm('Deseja enviar os resumos de {{ $pendingDebtsCount }} pedido(s) pendente(s) para {{ $customer->name }}?')" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-green-600 text-white hover:bg-green-700 h-10 px-4 py-2 gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-send">
                        <path d="m22 2-7 20-4-9-9-4Z"></path>
                        <path d="M22 2 11 13"></path>
                    </svg>
                    Enviar Pedidos Pendentes ({{ $pendingDebtsCount }})
                </button>
            </form>
            @endif
            <a href="{{ route('dashboard.customers.edit', $customer->id) }}" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2 gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-edit">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                </svg>
                Editar Cliente
            </a>
        </div>
  </div>

    <!-- Dados do Cliente -->
    <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
        <div class="flex flex-col space-y-1.5 p-6">
            <h3 class="text-lg font-semibold leading-none tracking-tight">Informações do Cliente</h3>
        </div>
        <div class="p-6 pt-0">
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <p class="text-sm font-medium text-muted-foreground mb-1">Nome</p>
                    <p class="text-base">{{ $customer->name ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-muted-foreground mb-1">Telefone</p>
                    <p class="text-base">{{ $customer->phone ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-muted-foreground mb-1">E-mail</p>
                    <p class="text-base">{{ $customer->email ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-muted-foreground mb-1">CPF</p>
                    <p class="text-base">{{ $customer->cpf ?? '—' }}</p>
                </div>
                @if($customer->birth_date)
                <div>
                    <p class="text-sm font-medium text-muted-foreground mb-1">Data de Nascimento</p>
                    <p class="text-base">{{ \Carbon\Carbon::parse($customer->birth_date)->format('d/m/Y') }}</p>
                </div>
                @endif
                @php
                    $debtsBalance = \App\Models\CustomerDebt::getBalance($customer->id);
                    $cashbackBalance = \App\Models\CustomerCashback::getBalance($customer->id);
                @endphp
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <p class="text-sm font-medium text-muted-foreground">Saldo de Cashback</p>
                        <button 
                            type="button"
                            onclick="openCashbackModal()"
                            class="text-xs text-primary hover:text-primary/80 font-medium inline-flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-edit">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                            </svg>
                            Editar
                        </button>
                    </div>
                    <p class="text-base font-semibold text-green-600">R$ {{ number_format($cashbackBalance, 2, ',', '.') }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-muted-foreground mb-1">Saldo de Débitos</p>
                    @if($debtsBalance > 0)
                        <p class="text-base font-semibold text-red-600">R$ {{ number_format($debtsBalance, 2, ',', '.') }}</p>
                    @else
                        <p class="text-base text-muted-foreground">R$ 0,00</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Histórico de Pedidos -->
    <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
        <div class="flex flex-col space-y-1.5 p-6">
            <h3 class="text-lg font-semibold leading-none tracking-tight">Histórico de Pedidos</h3>
      </div>
        <div class="p-6 pt-0">
            @if($orders && $orders->count() > 0)
      <div class="overflow-x-auto">
                    <table class="w-full caption-bottom text-sm">
                        <thead class="[&_tr]:border-b">
                            <tr class="border-b transition-colors hover:bg-muted/50">
                                <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Pedido</th>
                                <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Data</th>
                                <th class="h-12 px-4 text-right align-middle font-medium text-muted-foreground">Total</th>
                                <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Status</th>
                                <th class="h-12 px-4 text-center align-middle font-medium text-muted-foreground">Ações</th>
            </tr>
          </thead>
                        <tbody class="[&_tr:last-child]:border-0">
                            @foreach($orders as $order)
                                @php
                                    $statusColors = [
                                        'pending' => 'bg-muted text-muted-foreground',
                                        'confirmed' => 'bg-primary text-primary-foreground',
                                        'preparing' => 'bg-warning text-warning-foreground',
                                        'ready' => 'bg-primary/80 text-primary-foreground',
                                        'delivered' => 'bg-success text-success-foreground',
                                        'cancelled' => 'bg-destructive text-destructive-foreground',
                                    ];
                                    $statusLabel = [
                                        'pending' => 'Pendente',
                                        'confirmed' => 'Confirmado',
                                        'preparing' => 'Em Preparo',
                                        'ready' => 'Pronto',
                                        'delivered' => 'Entregue',
                                        'cancelled' => 'Cancelado',
                                    ];
                                    $statusColor = $statusColors[$order->status ?? 'pending'] ?? 'bg-muted text-muted-foreground';
                                    $statusText = $statusLabel[$order->status ?? 'pending'] ?? ucfirst($order->status ?? 'Pendente');
                  @endphp
                                <tr class="border-b transition-colors hover:bg-muted/50">
                                    <td class="p-4 align-middle font-medium">#{{ $order->order_number ?? $order->id }}</td>
                                    <td class="p-4 align-middle text-muted-foreground">
                                        {{ \Carbon\Carbon::parse($order->created_at)->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="p-4 align-middle text-right font-semibold">
                                        R$ {{ number_format($order->final_amount ?? $order->total_amount ?? 0, 2, ',', '.') }}
                                    </td>
                                    <td class="p-4 align-middle">
                                        <div class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors {{ $statusColor }}">
                                            {{ $statusText }}
                                        </div>
                                    </td>
                                    <td class="p-4 align-middle text-center">
                                        <a href="{{ route('dashboard.orders.show', $order->id) }}" class="inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 hover:bg-accent hover:text-accent-foreground h-9 rounded-md px-3">
                                            Ver Detalhes
                                        </a>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
                @if(method_exists($orders, 'links'))
                    <div class="mt-4 flex justify-center">
                        {{ $orders->links() }}
                    </div>
                @endif
            @else
                <div class="text-center py-12">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-shopping-cart mx-auto mb-4 text-muted-foreground">
                        <circle cx="8" cy="21" r="1"></circle>
                        <circle cx="19" cy="21" r="1"></circle>
                        <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"></path>
                    </svg>
                    <p class="text-muted-foreground text-lg">Nenhum pedido encontrado</p>
                    <p class="text-muted-foreground text-sm mt-2">Este cliente ainda não fez nenhum pedido.</p>
                </div>
    @endif
        </div>
    </div>
</div>

<!-- Modal para editar cashback -->
<div id="cashbackModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-lg p-6 max-w-md w-full mx-4">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">Editar Saldo de Cashback</h3>
            <button onclick="closeCashbackModal()" class="text-gray-500 hover:text-gray-700">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x">
                    <path d="M18 6 6 18"></path>
                    <path d="M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <form action="{{ route('dashboard.customers.updateCashback', $customer->id) }}" method="POST" id="cashbackForm">
            @csrf
            @method('PUT')
            <div class="space-y-4">
                <div>
                    <p class="text-sm text-muted-foreground mb-2">Saldo atual: <strong class="text-foreground">R$ {{ number_format($cashbackBalance, 2, ',', '.') }}</strong></p>
                </div>
                <div>
                    <label for="new_cashback_balance" class="block text-sm font-medium mb-2">Novo saldo de cashback (R$)</label>
                    <input 
                        type="number" 
                        id="new_cashback_balance" 
                        name="cashback_balance" 
                        step="0.01"
                        min="0"
                        value="{{ number_format($cashbackBalance, 2, '.', '') }}" 
                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                        required
                    >
                    <p class="text-xs text-muted-foreground mt-1">O sistema ajustará automaticamente criando uma transação de ajuste.</p>
                </div>
                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" onclick="closeCashbackModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">
                        Cancelar
                    </button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-primary rounded-md hover:bg-primary/90">
                        Salvar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function openCashbackModal() {
    document.getElementById('cashbackModal').classList.remove('hidden');
}

function closeCashbackModal() {
    document.getElementById('cashbackModal').classList.add('hidden');
}

// Fechar modal ao clicar fora
document.getElementById('cashbackModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeCashbackModal();
    }
});
</script>
@endsection

