@extends('dash.layouts.app')

@section('title', 'Cliente - OLIKA Painel')

@section('content')
<div class="space-y-6 animate-in fade-in duration-500">
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
        <a href="{{ route('dashboard.customers.edit', $customer->id) }}" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2 gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-edit">
                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
            </svg>
            Editar Cliente
    </a>
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
@endsection
