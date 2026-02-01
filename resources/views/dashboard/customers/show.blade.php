@extends('dashboard.layouts.app')

@section('page_title', $customer->name ?? 'Cliente')
@section('page_subtitle', 'Detalhes do cliente')

@section('page_actions')
    @php
        $pendingDebtsCount = $openDebts->count();
        $totalOpenAmount = $openDebts->sum('amount');
    @endphp
    @if($pendingDebtsCount > 0)
        <form action="{{ route('dashboard.customers.sendPendingOrders', $customer->id) }}" method="POST" class="inline">
            @csrf
            <button id="send-debts-button" type="submit"
                onclick="return confirm('Deseja enviar os resumos de {{ $pendingDebtsCount }} pedido(s) pendente(s) para {{ $customer->name }}?')"
                class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-green-600 text-white hover:bg-green-700 h-10 px-4 py-2 gap-2"
                data-count="{{ $pendingDebtsCount }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="lucide lucide-send">
                    <path d="m22 2-7 20-4-9-9-4Z"></path>
                    <path d="M22 2 11 13"></path>
                </svg>
                Enviar Pedidos Pendentes ({{ $pendingDebtsCount }})
            </button>
        </form>
    @endif
    <a href="{{ route('dashboard.customers.edit', $customer->id) }}"
        class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2 gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-edit">
            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
        </svg>
        Editar Cliente
    </a>
@endsection

@section('content')
    <div class="space-y-6">
        @if(session('success'))
            <div class="rounded-lg border bg-green-50 text-green-900 px-4 py-3">
                <div class="flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="lucide lucide-check-circle">
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
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="lucide lucide-alert-circle">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    {{ session('error') }}
                </div>
            </div>
        @endif

        <!-- Botão Voltar -->
        <div class="mb-4">
            <a href="{{ route('dashboard.customers.index') }}"
                class="inline-flex items-center justify-center gap-2 text-sm text-muted-foreground hover:text-foreground transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="lucide lucide-arrow-left">
                    <path d="m12 19-7-7 7-7"></path>
                    <path d="M19 12H5"></path>
                </svg>
                Voltar para lista de clientes
            </a>
        </div>

        <!-- Estatísticas de Pedidos -->
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                <div class="flex flex-col space-y-1 p-3">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-medium text-muted-foreground">Total de Pedidos</h3>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="lucide lucide-shopping-cart text-muted-foreground">
                            <circle cx="8" cy="21" r="1"></circle>
                            <circle cx="19" cy="21" r="1"></circle>
                            <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12">
                            </path>
                        </svg>
                    </div>
                    <p class="text-xl font-bold">{{ $totalOrders ?? 0 }}</p>
                    <p class="text-xs text-muted-foreground">Pedidos realizados</p>
                </div>
            </div>
            <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                <div class="flex flex-col space-y-1.5 p-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-medium text-muted-foreground">Valor Total</h3>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="lucide lucide-dollar-sign text-muted-foreground">
                            <line x1="12" x2="12" y1="2" y2="22"></line>
                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                        </svg>
                    </div>
                    <p class="text-xl font-bold text-green-600">R$ {{ number_format($totalOrdersValue ?? 0, 2, ',', '.') }}
                    </p>
                    <p class="text-xs text-muted-foreground">Soma de todos os pedidos</p>
                </div>
            </div>
        </div>
        <div class="grid gap-3 md:grid-cols-2">
            <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                <div class="flex flex-col space-y-1 p-3">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-medium text-muted-foreground">Média por Pedido</h3>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="lucide lucide-trending-up text-muted-foreground">
                            <polyline points="22 7 13.5 15.5 8.5 10.5 2 17"></polyline>
                            <polyline points="16 7 22 7 22 13"></polyline>
                        </svg>
                    </div>
                    <p class="text-xl font-bold text-blue-600">R$ {{ number_format($averageOrderValue ?? 0, 2, ',', '.') }}
                    </p>
                    <p class="text-xs text-muted-foreground">Valor médio dos pedidos</p>
                </div>
            </div>
            <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                <div class="flex flex-col space-y-1 p-3">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-medium text-muted-foreground">Saldo em Aberto</h3>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="lucide lucide-alert-circle text-muted-foreground">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                    </div>
                    <p
                        class="text-xl font-bold {{ ($totalOpenAmount ?? 0) > 0 ? 'text-red-600' : 'text-muted-foreground' }}">
                        R$ {{ number_format($totalOpenAmount ?? 0, 2, ',', '.') }}</p>
                    <p class="text-xs text-muted-foreground">{{ $pendingDebtsCount ?? 0 }} pedido(s) pendente(s)</p>
                </div>
            </div>
        </div>

        <!-- Dados do Cliente -->
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="flex flex-col space-y-1.5 p-3 pb-2 border-b border-border/60">
                <h3 class="text-base font-semibold leading-none tracking-tight">Informações do Cliente</h3>
            </div>
            <div class="p-3">
                <div class="grid gap-3 md:grid-cols-2">
                    <div>
                        <p class="text-xs font-medium text-muted-foreground mb-0.5">Nome</p>
                        <p class="text-sm">{{ $customer->name ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-muted-foreground mb-0.5">Telefone</p>
                        <p class="text-sm">{{ $customer->phone ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-muted-foreground mb-0.5">E-mail</p>
                        <p class="text-sm">{{ $customer->email ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-muted-foreground mb-0.5">CPF</p>
                        <p class="text-sm">{{ $customer->cpf ?? '—' }}</p>
                    </div>
                    @if($customer->birth_date)
                        <div>
                            <p class="text-xs font-medium text-muted-foreground mb-0.5">Data de Nascimento</p>
                            <p class="text-sm">{{ \Carbon\Carbon::parse($customer->birth_date)->format('d/m/Y') }}</p>
                        </div>
                    @endif
                    @php
                        $debtsBalance = \App\Models\CustomerDebt::getBalance($customer->id);
                        $cashbackBalance = \App\Models\CustomerCashback::getBalance($customer->id);
                    @endphp
                    <div>
                        <div class="flex items-center justify-between mb-0.5">
                            <p class="text-xs font-medium text-muted-foreground">Saldo de Cashback</p>
                            <button type="button" onclick="openCashbackModal()"
                                class="text-xs text-primary hover:text-primary/80 font-medium inline-flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="lucide lucide-edit">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                </svg>
                                Editar
                            </button>
                        </div>
                        <p class="text-sm font-semibold text-green-600">R$
                            {{ number_format($cashbackBalance, 2, ',', '.') }}</p>
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-0.5">
                            <p class="text-xs font-medium text-muted-foreground">Saldo de Débitos</p>
                            <button type="button" onclick="openDebtBalanceModal()"
                                class="text-xs text-primary hover:text-primary/80 font-medium inline-flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="lucide lucide-edit">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                </svg>
                                Editar
                            </button>
                        </div>
                        @if($debtsBalance > 0)
                            <p class="text-sm font-semibold text-red-600">R$ {{ number_format($debtsBalance, 2, ',', '.') }}</p>
                        @else
                            <p class="text-sm text-muted-foreground">R$ 0,00</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Débitos / Fiado -->
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div
                class="flex flex-col gap-2 p-3 pb-2 border-b border-border/60 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h3 class="text-base font-semibold leading-none tracking-tight">Fiado / Débitos</h3>
                    <p class="text-xs text-muted-foreground mt-0.5">Gerencie os lançamentos de fiado deste cliente, registre
                        pagamentos e acompanhe o histórico.</p>
                </div>
                <div class="flex flex-col items-start gap-2 sm:items-end">
                    <div class="text-sm">
                        <span class="text-muted-foreground">Saldo em aberto:</span>
                        <span id="open-debts-total" data-value="{{ number_format($totalOpenAmount, 2, '.', '') }}"
                            class="font-semibold {{ $totalOpenAmount > 0 ? 'text-red-600' : 'text-muted-foreground' }}">
                            R$ {{ number_format($totalOpenAmount, 2, ',', '.') }}
                        </span>
                    </div>
                    <div class="text-sm">
                        <span class="text-muted-foreground">Pedidos com fiado:</span>
                        <span id="open-debts-count" class="font-semibold">{{ $pendingDebtsCount }}</span>
                    </div>
                </div>
            </div>

            <div class="p-3 space-y-3">
                <div id="open-debts-table-wrapper" class="{{ $openDebts->isEmpty() ? 'hidden' : '' }}">
                    <div class="overflow-x-auto rounded-lg border border-border/60">
                        <table class="w-full text-sm">
                            <thead class="bg-muted/40">
                                <tr class="text-left text-muted-foreground">
                                    <th class="px-4 py-3 font-medium">Pedido</th>
                                    <th class="px-4 py-3 font-medium">Criado em</th>
                                    <th class="px-4 py-3 font-medium">Valor</th>
                                    <th class="px-4 py-3 font-medium">Descrição</th>
                                    <th class="px-4 py-3 font-medium text-right">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($openDebts as $debt)
                                    @php
                                        $order = $debt->order;
                                        $orderLabel = $order ? '#' . $order->order_number : ($debt->order_id ? '#' . $debt->order_id : '—');
                                    @endphp
                                    <tr data-debt-row="{{ $debt->id }}" class="border-b last:border-b-0">
                                        <td class="px-4 py-3 font-medium">
                                            {{ $orderLabel }}
                                        </td>
                                        <td class="px-4 py-3 text-muted-foreground">
                                            {{ \Carbon\Carbon::parse($debt->created_at)->format('d/m/Y H:i') }}
                                        </td>
                                        <td class="px-4 py-3 font-semibold text-red-600"
                                            data-debt-amount="{{ number_format($debt->amount, 2, '.', '') }}">
                                            R$ {{ number_format($debt->amount, 2, ',', '.') }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-muted-foreground">
                                            {{ $debt->description ?? 'Débito registrado automaticamente' }}
                                        </td>
                                        <td class="px-4 py-3 text-right space-y-2">
                                            <div class="flex justify-end gap-2">
                                                @if($order)
                                                    <a href="{{ route('dashboard.orders.show', $order->id) }}"
                                                        class="inline-flex items-center gap-1 rounded-md border border-input px-3 py-2 text-xs font-medium hover:bg-accent hover:text-accent-foreground">
                                                        <i data-lucide="file-text" class="h-4 w-4"></i>
                                                        Ver pedido
                                                    </a>
                                                @endif
                                                <button type="button"
                                                    class="inline-flex items-center gap-1 rounded-md bg-green-600 px-3 py-2 text-xs font-medium text-white hover:bg-green-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 data-[loading=true]:opacity-70"
                                                    data-action="settle-debt"
                                                    data-url="{{ route('dashboard.customers.debts.settle', $debt->id) }}"
                                                    data-amount="{{ number_format($debt->amount, 2, '.', '') }}">
                                                    <i data-lucide="check" class="h-4 w-4"></i>
                                                    Dar baixa
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div id="open-debts-empty"
                    class="rounded-md border border-dashed border-border/70 bg-muted/10 p-3 text-center {{ $openDebts->isEmpty() ? '' : 'hidden' }}">
                    <p class="text-sm text-muted-foreground">Nenhum débito em aberto para este cliente.</p>
                </div>

                @if($debtHistory->count() > 0)
                    <details class="rounded-md border border-border/60 bg-muted/10">
                        <summary
                            class="cursor-pointer select-none px-4 py-3 text-sm font-semibold text-muted-foreground flex items-center justify-between">
                            <span>Histórico de fiado (últimos {{ $debtHistory->count() }} lançamentos)</span>
                            <i data-lucide="chevron-down" class="h-4 w-4 shrink-0 transition duration-200"></i>
                        </summary>
                        <div class="px-4 pb-4 pt-0">
                            <div class="overflow-x-auto max-h-64 overflow-y-auto mt-3">
                                <table class="w-full text-xs">
                                    <thead class="bg-muted/30 text-muted-foreground">
                                        <tr>
                                            <th class="px-3 py-2 text-left font-medium">Data</th>
                                            <th class="px-3 py-2 text-left font-medium">Tipo</th>
                                            <th class="px-3 py-2 text-left font-medium">Status</th>
                                            <th class="px-3 py-2 text-left font-medium">Valor</th>
                                            <th class="px-3 py-2 text-left font-medium">Descrição</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($debtHistory as $entry)
                                            <tr class="border-b last:border-b-0">
                                                <td class="px-3 py-2">
                                                    {{ \Carbon\Carbon::parse($entry->created_at)->format('d/m/Y H:i') }}</td>
                                                <td class="px-3 py-2">
                                                    <span
                                                        class="inline-flex items-center rounded-full px-2 py-0.5 font-medium {{ $entry->type === 'debit' ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700' }}">
                                                        {{ $entry->type === 'debit' ? 'Débito' : 'Crédito' }}
                                                    </span>
                                                </td>
                                                <td class="px-3 py-2">
                                                    <span
                                                        class="inline-flex items-center rounded-full px-2 py-0.5 font-medium {{ $entry->status === 'open' ? 'bg-blue-100 text-blue-700' : 'bg-gray-200 text-gray-800' }}">
                                                        {{ ucfirst($entry->status) }}
                                                    </span>
                                                </td>
                                                <td
                                                    class="px-3 py-2 font-semibold {{ $entry->type === 'debit' ? 'text-red-600' : 'text-green-600' }}">
                                                    {{ $entry->type === 'debit' ? '-' : '+' }}R$
                                                    {{ number_format($entry->amount, 2, ',', '.') }}
                                                </td>
                                                <td class="px-3 py-2 text-muted-foreground">
                                                    {{ $entry->description ?? '—' }}
                                                    @if($entry->order)
                                                        <a href="{{ route('dashboard.orders.show', $entry->order->id) }}"
                                                            class="ml-1 inline-flex items-center gap-1 text-primary hover:underline">
                                                            <i data-lucide="external-link" class="h-3 w-3"></i>
                                                            Pedido #{{ $entry->order->order_number }}
                                                        </a>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </details>
                @endif
            </div>
        </div>

        <!-- Histórico de Pedidos -->
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="flex flex-col space-y-1.5 p-3 pb-2 border-b border-border/60">
                <h3 class="text-base font-semibold leading-none tracking-tight">Histórico de Pedidos</h3>
            </div>
            <div class="p-3">
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
                                        <td class="p-2 align-middle font-medium text-sm">#{{ $order->order_number ?? $order->id }}
                                        </td>
                                        <td class="p-2 align-middle text-muted-foreground text-sm">
                                            {{ \Carbon\Carbon::parse($order->created_at)->format('d/m/Y H:i') }}
                                        </td>
                                        <td class="p-2 align-middle text-right font-semibold text-sm">
                                            R$ {{ number_format($order->final_amount ?? $order->total_amount ?? 0, 2, ',', '.') }}
                                        </td>
                                        <td class="p-2 align-middle">
                                            <div
                                                class="inline-flex items-center rounded-full border px-2 py-0.5 text-xs font-semibold transition-colors {{ $statusColor }}">
                                                {{ $statusText }}
                                            </div>
                                        </td>
                                        <td class="p-2 align-middle text-center">
                                            <a href="{{ route('dashboard.orders.show', $order->id) }}"
                                                class="inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 hover:bg-accent hover:text-accent-foreground h-9 rounded-md px-3">
                                                Ver Detalhes
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if(isset($orders) && method_exists($orders, 'links') && $orders->hasPages())
                        <div class="px-4 sm:px-6 py-3 sm:py-4 border-t border-border bg-muted/20 rounded-b-lg">
                            {{ $orders->onEachSide(1)->links() }}
                        </div>
                    @endif
                @else
                    <div class="text-center py-12">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="lucide lucide-shopping-cart mx-auto mb-4 text-muted-foreground">
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
    <div id="cashbackModal"
        class="fixed top-0 bottom-0 left-0 right-0 md:left-64 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-lg p-6 max-w-md w-full mx-4">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Editar Saldo de Cashback</h3>
                <button onclick="closeCashbackModal()" class="text-gray-500 hover:text-gray-700">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="lucide lucide-x">
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
                        <p class="text-sm text-muted-foreground mb-2">Saldo atual: <strong class="text-foreground">R$
                                {{ number_format($cashbackBalance, 2, ',', '.') }}</strong></p>
                    </div>
                    <div>
                        <label for="new_cashback_balance" class="block text-sm font-medium mb-2">Novo saldo de cashback
                            (R$)</label>
                        <input type="number" id="new_cashback_balance" name="cashback_balance" step="0.01" min="0"
                            value="{{ number_format($cashbackBalance, 2, '.', '') }}"
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                            required>
                        <p class="text-xs text-muted-foreground mt-1">O sistema ajustará automaticamente criando uma
                            transação de ajuste.</p>
                    </div>
                    <div class="flex justify-end gap-3 pt-4">
                        <button type="button" onclick="closeCashbackModal()"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">
                            Cancelar
                        </button>
                        <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-primary rounded-md hover:bg-primary/90">
                            Salvar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal para editar saldo devedor -->
    <div id="debtBalanceModal"
        class="fixed top-0 bottom-0 left-0 right-0 md:left-64 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-lg p-6 max-w-md w-full mx-4">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Editar Saldo de Débitos</h3>
                <button onclick="closeDebtBalanceModal()" class="text-gray-500 hover:text-gray-700">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="lucide lucide-x">
                        <path d="M18 6 6 18"></path>
                        <path d="M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <form action="{{ route('dashboard.customers.adjustDebtBalance', $customer->id) }}" method="POST"
                id="debtBalanceForm">
                @csrf
                <div class="space-y-4">
                    <div>
                        <p class="text-sm text-muted-foreground mb-2">
                            Saldo atual: <strong class="text-foreground {{ $debtsBalance > 0 ? 'text-red-600' : '' }}">R$
                                {{ number_format($debtsBalance, 2, ',', '.') }}</strong>
                        </p>
                    </div>
                    <div>
                        <label for="new_debt_balance" class="block text-sm font-medium mb-2">Novo saldo de débitos
                            (R$)</label>
                        <input type="number" id="new_debt_balance" name="new_balance" step="0.01" min="0"
                            value="{{ number_format($debtsBalance, 2, '.', '') }}"
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                            required>
                        <p class="text-xs text-muted-foreground mt-1">O sistema ajustará automaticamente criando um
                            lançamento de ajuste no histórico.</p>
                    </div>
                    <div>
                        <label for="adjustment_reason" class="block text-sm font-medium mb-2">Motivo do ajuste
                            (opcional)</label>
                        <textarea id="adjustment_reason" name="reason" rows="3"
                            class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                            placeholder="Ex: Ajuste por acordo, correção de erro, etc."></textarea>
                    </div>
                    <div class="bg-yellow-50 border border-yellow-200 rounded-md p-3">
                        <p class="text-xs text-yellow-800">
                            <strong>Atenção:</strong> Este ajuste criará um registro no histórico de ajustes e um lançamento
                            de débito/crédito para igualar o saldo.
                        </p>
                    </div>
                    <div class="flex justify-end gap-3 pt-4">
                        <button type="button" onclick="closeDebtBalanceModal()"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">
                            Cancelar
                        </button>
                        <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-primary rounded-md hover:bg-primary/90">
                            Salvar Ajuste
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

        function openDebtBalanceModal() {
            document.getElementById('debtBalanceModal').classList.remove('hidden');
        }

        function closeDebtBalanceModal() {
            document.getElementById('debtBalanceModal').classList.add('hidden');
        }

        // Fechar modal ao clicar fora
        document.getElementById('cashbackModal')?.addEventListener('click', function (e) {
            if (e.target === this) {
                closeCashbackModal();
            }
        });

        document.getElementById('debtBalanceModal')?.addEventListener('click', function (e) {
            if (e.target === this) {
                closeDebtBalanceModal();
            }
        });

        document.addEventListener('DOMContentLoaded', function () {
            if (window.lucide) {
                window.lucide.createIcons();
            }

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const totalEl = document.getElementById('open-debts-total');
            const countEl = document.getElementById('open-debts-count');
            const tableWrapper = document.getElementById('open-debts-table-wrapper');
            const emptyState = document.getElementById('open-debts-empty');
            const sendButton = document.getElementById('send-debts-button');

            function formatCurrency(value) {
                return value.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

            function showToast(message, type = 'success') {
                const toast = document.createElement('div');
                toast.className = `fixed top-4 right-4 z-50 rounded-lg px-4 py-3 text-white shadow-lg ${type === 'success' ? 'bg-green-600' : 'bg-red-600'}`;
                toast.textContent = message;
                document.body.appendChild(toast);
                setTimeout(() => toast.remove(), 4000);
            }

            function updateSummary(amountRemoved) {
                if (!totalEl || !countEl) {
                    return;
                }

                const currentTotal = parseFloat((totalEl.dataset.value ?? totalEl.textContent ?? '0')
                    .replace(/[^\d,.-]/g, '')
                    .replace(/\./g, '')
                    .replace(',', '.')) || 0;

                const newTotal = currentTotal - amountRemoved;
                totalEl.dataset.value = String(newTotal);
                totalEl.textContent = `R$ ${formatCurrency(newTotal)}`;

                const currentCount = parseInt(countEl.textContent || '0', 10) || 0;
                const newCount = Math.max(0, currentCount - 1);
                countEl.textContent = newCount.toString();

                if (sendButton) {
                    if (newCount <= 0) {
                        sendButton.disabled = true;
                        sendButton.classList.add('opacity-60', 'cursor-not-allowed');
                        sendButton.textContent = 'Nenhum pedido pendente';
                    } else {
                        sendButton.dataset.count = String(newCount);
                        sendButton.innerHTML = `
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-send">
                            <path d="m22 2-7 20-4-9-9-4Z"></path>
                            <path d="M22 2 11 13"></path>
                        </svg>
                        Enviar Pedidos Pendentes (${newCount})`;
                        if (window.lucide) {
                            window.lucide.createIcons();
                        }
                    }
                }
            }

            document.querySelectorAll('[data-action="settle-debt"]').forEach(function (button) {
                button.addEventListener('click', async function () {
                    if (!confirm('Confirmar a baixa deste fiado?')) {
                        return;
                    }

                    const url = this.getAttribute('data-url');
                    const amount = parseFloat(this.getAttribute('data-amount') || '0');
                    const row = this.closest('[data-debt-row]');

                    if (!url || !row) {
                        return;
                    }

                    const originalHTML = this.innerHTML;
                    this.dataset.loading = 'true';
                    this.innerHTML = '<svg class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4l3-3-3-3v4a10 10 0 00-10 10h4z"></path></svg>';
                    this.disabled = true;

                    try {
                        const response = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': csrfToken,
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({ _token: csrfToken }),
                        });

                        const data = await response.json();

                        if (!response.ok || !data.ok) {
                            throw new Error(data.message || 'Falha ao registrar pagamento.');
                        }

                        row.remove();
                        updateSummary(amount);

                        if (!document.querySelector('[data-debt-row]')) {
                            if (tableWrapper) tableWrapper.classList.add('hidden');
                            if (emptyState) emptyState.classList.remove('hidden');
                        }

                        showToast('Pagamento registrado com sucesso!', 'success');
                    } catch (error) {
                        console.error(error);
                        showToast(error.message || 'Erro ao registrar pagamento.', 'error');
                        this.disabled = false;
                        this.innerHTML = originalHTML;
                        this.dataset.loading = 'false';
                        return;
                    }

                    this.dataset.loading = 'false';
                });
            });
        });
    </script>
@endsection