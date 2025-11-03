@extends('dashboard.layouts.app')

@section('title', 'Visão Geral - OLIKA Dashboard')

@section('content')
<div class="space-y-6 animate-in fade-in duration-500">
    <div>
        <h1 class="text-3xl font-bold tracking-tight">Visão Geral</h1>
        <p class="text-muted-foreground">Acompanhe suas métricas e desempenho em tempo real</p>
  </div>

    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm hover:shadow-md transition-shadow">
            <div class="p-6 flex flex-row items-center justify-between space-y-0 pb-2">
                <h3 class="tracking-tight text-sm font-medium text-muted-foreground">Total Hoje</h3>
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-dollar-sign h-4 w-4 text-muted-foreground">
                    <line x1="12" x2="12" y1="2" y2="22"></line>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                </svg>
            </div>
            <div class="p-6 pt-0">
                <div class="text-2xl font-bold">R$ {{ number_format($receitaHoje ?? 0, 2, ',', '.') }}</div>
            </div>
        </div>
        
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm hover:shadow-md transition-shadow">
            <div class="p-6 flex flex-row items-center justify-between space-y-0 pb-2">
                <h3 class="tracking-tight text-sm font-medium text-muted-foreground">Pedidos Hoje</h3>
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-shopping-bag h-4 w-4 text-muted-foreground">
                    <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"></path>
                    <path d="M3 6h18"></path>
                    <path d="M16 10a4 4 0 0 1-8 0"></path>
                </svg>
            </div>
            <div class="p-6 pt-0">
                <div class="text-2xl font-bold">{{ $pedidosHoje ?? 0 }}</div>
            </div>
  </div>

        <div class="rounded-lg border bg-card text-card-foreground shadow-sm hover:shadow-md transition-shadow">
            <div class="p-6 flex flex-row items-center justify-between space-y-0 pb-2">
                <h3 class="tracking-tight text-sm font-medium text-muted-foreground">Pagos Hoje</h3>
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-check-big h-4 w-4 text-muted-foreground">
                    <path d="M21.801 10A10 10 0 1 1 17 3.335"></path>
                    <path d="m9 11 3 3L22 4"></path>
                </svg>
            </div>
            <div class="p-6 pt-0">
                <div class="text-2xl font-bold">{{ $pagosHoje ?? 0 }}</div>
          </div>
      </div>
        
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm hover:shadow-md transition-shadow">
            <div class="p-6 flex flex-row items-center justify-between space-y-0 pb-2">
                <h3 class="tracking-tight text-sm font-medium text-muted-foreground">Pendentes Pgto</h3>
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-clock h-4 w-4 text-muted-foreground">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
            </div>
            <div class="p-6 pt-0">
                <div class="text-2xl font-bold">{{ $pendentesPagamento ?? 0 }}</div>
            </div>
        </div>
        
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm hover:shadow-md transition-shadow">
            <div class="p-6 flex flex-row items-center justify-between space-y-0 pb-2">
                <h3 class="tracking-tight text-sm font-medium text-muted-foreground">Agendados (7 dias)</h3>
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-calendar h-4 w-4 text-muted-foreground">
                    <rect width="18" height="18" x="3" y="4" rx="2" ry="2"></rect>
                    <line x1="16" x2="16" y1="2" y2="6"></line>
                    <line x1="8" x2="8" y1="2" y2="6"></line>
                    <line x1="3" x2="21" y1="10" y2="10"></line>
                </svg>
            </div>
            <div class="p-6 pt-0">
                <div class="text-2xl font-bold">{{ $scheduledNext7Days ?? 0 }}</div>
                <div class="text-sm text-muted-foreground">Hoje: {{ $scheduledTodayCount ?? 0 }}</div>
            </div>
        </div>
  </div>

    <div class="grid gap-4 lg:grid-cols-2">
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="space-y-1.5 p-6 flex flex-row items-center justify-between">
                <div>
                    <h3 class="text-2xl font-semibold leading-none tracking-tight">Pedidos Recentes</h3>
                    <p class="text-sm text-muted-foreground">Últimos pedidos realizados</p>
                </div>
                <button class="inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 hover:bg-accent hover:text-accent-foreground h-9 rounded-md px-3">Ver todos</button>
            </div>
            <div class="p-6 pt-0">
                @if(isset($recentOrders) && $recentOrders->count() > 0)
                    <div class="space-y-3 max-h-96 overflow-y-auto">
                        @foreach($recentOrders as $order)
                            <div class="flex items-center justify-between p-3 border rounded-lg hover:bg-accent/50 transition-colors">
                                <div class="flex-1 min-w-0">
                                    <div class="font-medium text-sm">#{{ $order->order_number ?? 'N/A' }}</div>
                                    <div class="text-xs text-muted-foreground">
                                        {{ $order->customer->name ?? 'Cliente não identificado' }} • {{ $order->created_at->format('d/m H:i') }}
                                    </div>
                                </div>
                                <div class="text-right ml-4">
                                    <div class="font-medium text-sm">R$ {{ number_format((float)$order->final_amount, 2, ',', '.') }}</div>
                                    <div class="text-xs text-muted-foreground capitalize">{{ $order->status }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center py-8 text-muted-foreground">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-shopping-bag h-12 w-12 mb-4 opacity-20">
                            <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"></path>
                            <path d="M3 6h18"></path>
                            <path d="M16 10a4 4 0 0 1-8 0"></path>
                        </svg>
                        <p>Nenhum pedido registrado ainda</p>
                    </div>
                @endif
            </div>
        </div>
        
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="flex flex-col space-y-1.5 p-6">
                <h3 class="text-2xl font-semibold leading-none tracking-tight">Top Produtos</h3>
                <p class="text-sm text-muted-foreground">Últimos 7 dias</p>
            </div>
            <div class="p-6 pt-0">
                @if(isset($topProducts) && $topProducts->count() > 0)
                    <div class="space-y-3">
                        @foreach($topProducts as $item)
                            <div class="flex items-center justify-between p-3 border rounded-lg hover:bg-accent/50 transition-colors">
                                <div class="flex-1 min-w-0">
                                    <div class="font-medium text-sm">{{ $item['product']->name }}</div>
                                    <div class="text-xs text-muted-foreground">{{ $item['quantity'] }} unidades vendidas</div>
                                </div>
                                <div class="text-right ml-4">
                                    <div class="font-medium text-sm">R$ {{ number_format((float)$item['revenue'], 2, ',', '.') }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center py-8 text-muted-foreground">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-dollar-sign h-12 w-12 mb-4 opacity-20">
                            <line x1="12" x2="12" y1="2" y2="22"></line>
                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                        </svg>
                        <p>Nenhum produto vendido ainda</p>
                    </div>
                @endif
            </div>
        </div>

        <div class="rounded-lg border bg-card text-card-foreground shadow-sm lg:col-span-2">
            <div class="flex flex-col space-y-1.5 p-6">
                <h3 class="text-2xl font-semibold leading-none tracking-tight">Próximos pedidos agendados</h3>
                <p class="text-sm text-muted-foreground">Próximas janelas</p>
            </div>
            <div class="p-6 pt-0">
                @if(isset($nextScheduled) && $nextScheduled->count())
                    <div class="divide-y">
                        @foreach($nextScheduled as $ord)
                            <div class="py-3 flex items-center justify-between text-sm">
                                <div class="flex-1">
                                    <div class="font-medium">#{{ $ord->order_number }}</div>
                                    <div class="text-muted-foreground">{{ optional($ord->scheduled_delivery_at)->format('d/m H:i') }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="font-medium">R$ {{ number_format((float)$ord->final_amount, 2, ',', '.') }}</div>
                                    <div class="text-muted-foreground">{{ ucfirst($ord->status) }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center py-8 text-muted-foreground">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-calendar h-12 w-12 mb-4 opacity-20">
                            <rect width="18" height="18" x="3" y="4" rx="2" ry="2"></rect>
                            <line x1="16" x2="16" y1="2" y2="6"></line>
                            <line x1="8" x2="8" y1="2" y2="6"></line>
                            <line x1="3" x2="21" y1="10" y2="10"></line>
                        </svg>
                        <p>Nenhum pedido agendado</p>
                    </div>
                @endif
            </div>
        </div>
  </div>
</div>
@endsection
