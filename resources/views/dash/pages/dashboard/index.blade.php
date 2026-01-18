@extends('layouts.dashboard')

@section('page_title', 'Visão Geral')
@section('page_subtitle', 'Acompanhe o desempenho do seu negócio em tempo real')

@section('page_actions')
    <a href="{{ route('dashboard.pdv.index') }}" class="btn-primary">
        <i data-lucide="plus" class="h-4 w-4"></i>
        Novo Pedido
    </a>
    <a href="{{ route('dashboard.orders.index') }}" class="btn">
        <i data-lucide="receipt" class="h-4 w-4"></i>
        Ver Pedidos
    </a>
@endsection

@section('stat_cards')
    @php
        $metrics = $metrics ?? [];
        $statCards = [
            [
                'label' => 'Receita Hoje',
                'value' => data_get($metrics, 'sales.today_formatted', 'R$ 0,00'),
                'diff'  => data_get($metrics, 'sales.diff', '+0% vs. ontem'),
                'icon'  => 'trending-up',
                'color' => 'primary',
            ],
            [
                'label' => 'Pedidos Hoje',
                'value' => data_get($metrics, 'orders.count_today', 0),
                'diff'  => data_get($metrics, 'orders.diff', '+0 novos'),
                'icon'  => 'receipt',
                'color' => 'success',
            ],
            [
                'label' => 'Pagos Hoje',
                'value' => data_get($metrics, 'orders.paid_today', 0),
                'diff'  => data_get($metrics, 'orders.pending', '0 pendentes'),
                'icon'  => 'check-circle-2',
                'color' => 'accent',
            ],
            [
                'label' => 'Ticket Médio',
                'value' => data_get($metrics, 'sales.avg_ticket', 'R$ 0,00'),
                'diff'  => data_get($metrics, 'sales.avg_ticket_diff', '+0% vs. mês anterior'),
                'icon'  => 'wallet',
                'color' => 'primary',
            ],
        ];
    @endphp

    @foreach ($statCards as $card)
        <div class="rounded-xl sm:rounded-2xl border border-border bg-gradient-to-br from-card to-card/80 p-3 sm:p-6 shadow-sm transition hover:shadow-md hover:border-{{ $card['color'] }}/30">
            <div class="flex items-start justify-between gap-2 pb-2 sm:pb-4">
                <div class="space-y-0.5 sm:space-y-1 flex-1 min-w-0">
                    <p class="text-[10px] sm:text-xs font-semibold uppercase tracking-wider text-muted-foreground leading-tight">
                        {{ $card['label'] }}
                    </p>
                    <p class="text-lg sm:text-2xl font-bold text-foreground leading-tight truncate">
                        {{ $card['value'] }}
                    </p>
                </div>
                <span class="flex h-9 w-9 sm:h-11 sm:w-11 flex-shrink-0 items-center justify-center rounded-full bg-{{ $card['color'] }}/10 text-{{ $card['color'] }}">
                    <i data-lucide="{{ $card['icon'] }}" class="h-4 w-4 sm:h-5 sm:w-5"></i>
                </span>
            </div>
            <p class="flex items-center gap-1 sm:gap-2 text-[10px] sm:text-xs font-medium text-muted-foreground">
                <i data-lucide="arrow-up-right" class="h-2.5 w-2.5 sm:h-3.5 sm:w-3.5 flex-shrink-0"></i>
                <span class="truncate">{{ $card['diff'] }}</span>
            </p>
        </div>
    @endforeach
@endsection

@section('content')
    @php
        $recentOrders = $recentOrders ?? [];
        $topProducts = $topProducts ?? [];
        $shortcuts = [
            [
                'label' => 'Novo Produto',
                'url' => route('dashboard.products.create'),
                'description' => 'Cadastre um item com imagens e variantes',
                'icon' => 'package-plus',
            ],
            [
                'label' => 'Agendar Entregas',
                'url' => route('dashboard.orders.index', ['status' => 'agendado']),
                'description' => 'Defina horários e acompanhe os pedidos',
                'icon' => 'calendar-clock',
            ],
            [
                'label' => 'Templates WhatsApp',
                'url' => route('dashboard.settings.status-templates'),
                'description' => 'Personalize mensagens automáticas',
                'icon' => 'messages-square',
            ],
        ];
    @endphp

    <div class="grid gap-4 sm:gap-6 lg:grid-cols-[2fr,1.4fr]">
        <div class="space-y-4 sm:space-y-6">
            <div class="rounded-xl sm:rounded-2xl border border-border bg-card shadow-sm">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-border/70 px-4 sm:px-6 py-4 sm:py-5 gap-2 sm:gap-0">
                    <div>
                        <h3 class="text-base sm:text-lg font-semibold text-foreground">Pedidos Recentes</h3>
                        <p class="text-xs sm:text-sm text-muted-foreground">Pedidos criados nas últimas 24h</p>
                    </div>
                    <a href="{{ route('dashboard.orders.index') }}" class="btn btn-sm text-xs sm:text-sm">
                        <i data-lucide="arrow-right" class="h-3 w-3 sm:h-4 sm:w-4"></i>
                        Ver todos
                    </a>
                </div>

                <div class="px-2 sm:px-6 py-4 sm:py-5">
                    @if (count($recentOrders))
                        <!-- Mobile: Cards -->
                        <div class="space-y-3 sm:hidden">
                            @foreach ($recentOrders as $order)
                                <a href="{{ route('dashboard.orders.show', $order) }}" 
                                   class="block rounded-lg border border-border bg-background/50 p-3 hover:border-primary/60 hover:bg-background transition">
                                    <div class="flex items-start justify-between mb-2">
                                        <div class="flex-1">
                                            <p class="font-semibold text-primary text-sm">#{{ $order->order_number }}</p>
                                            <p class="text-xs text-muted-foreground">{{ $order->customer->name ?? 'Cliente' }}</p>
                                        </div>
                                        <p class="text-sm font-bold text-foreground">R$ {{ number_format($order->final_amount, 2, ',', '.') }}</p>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="badge badge-sm @if($order->status === 'cancelled') badge-destructive @elseif($order->status === 'delivered') badge-success @elseif($order->payment_status === 'paid') badge-success @else badge-default @endif">
                                            @if($order->status === 'cancelled')
                                                Cancelado
                                            @elseif($order->status === 'delivered')
                                                Entregue
                                            @elseif($order->payment_status === 'paid')
                                                Pago
                                            @else
                                                Pendente
                                            @endif
                                        </span>
                                        <p class="text-xs text-muted-foreground">
                                            <i data-lucide="{{ $order->delivery_type === 'delivery' ? 'bike' : 'store' }}" class="h-3 w-3 inline"></i>
                                            {{ $order->delivery_type === 'delivery' ? 'Entrega' : 'Retirada' }}
                                        </p>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                        
                        <!-- Desktop: Table -->
                        <div class="hidden sm:block overflow-x-auto">
                            <table class="table-compact w-full">
                                <thead>
                                    <tr>
                                        <th class="text-left">#</th>
                                        <th class="text-left">Cliente</th>
                                        <th class="text-left">Status</th>
                                        <th class="text-left">Tipo</th>
                                        <th class="text-right">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($recentOrders as $order)
                                        <tr class="hover:bg-muted/30">
                                            <td>
                                                <a href="{{ route('dashboard.orders.show', $order) }}" class="font-semibold text-primary hover:underline">
                                                    {{ $order->order_number }}
                                                </a>
                                            </td>
                                            <td class="text-sm">{{ $order->customer->name ?? 'Cliente' }}</td>
                                            <td>
                                                <span class="badge badge-sm @if($order->status === 'cancelled') badge-destructive @elseif($order->status === 'delivered') badge-success @elseif($order->payment_status === 'paid') badge-success @else badge-default @endif">
                                                    @if($order->status === 'cancelled')
                                                        Cancelado
                                                    @elseif($order->status === 'delivered')
                                                        Entregue
                                                    @elseif($order->payment_status === 'paid')
                                                        Pago
                                                    @else
                                                        Pendente
                                                    @endif
                                                </span>
                                            </td>
                                            <td class="text-sm">
                                                <i data-lucide="{{ $order->delivery_type === 'delivery' ? 'bike' : 'store' }}" class="h-3.5 w-3.5 inline"></i>
                                                {{ $order->delivery_type === 'delivery' ? 'Entrega' : 'Retirada' }}
                                            </td>
                                            <td class="text-right font-semibold text-sm">
                                                R$ {{ number_format($order->final_amount, 2, ',', '.') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center gap-3 py-10 text-center text-muted-foreground">
                            <span class="flex h-12 w-12 sm:h-14 sm:w-14 items-center justify-center rounded-full bg-muted">
                                <i data-lucide="shopping-bag" class="h-5 w-5 sm:h-6 sm:w-6"></i>
                            </span>
                            <div>
                                <p class="font-semibold text-foreground text-sm sm:text-base">Nenhum pedido recente</p>
                                <p class="text-xs sm:text-sm">Quando novos pedidos chegarem, eles aparecerão aqui.</p>
                            </div>
                            <a href="{{ route('dashboard.pdv.index') }}" class="btn-primary btn-sm">
                                Criar primeiro pedido
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <div class="rounded-xl sm:rounded-2xl border border-border bg-card shadow-sm">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-border/70 px-4 sm:px-6 py-4 sm:py-5 gap-2 sm:gap-0">
                    <div>
                        <h3 class="text-base sm:text-lg font-semibold text-foreground">Atalhos rápidos</h3>
                        <p class="text-xs sm:text-sm text-muted-foreground">Acesse os fluxos principais em segundos</p>
                    </div>
                </div>

                <div class="grid gap-3 sm:gap-4 px-3 sm:px-6 py-4 sm:py-5 grid-cols-1 sm:grid-cols-2 md:grid-cols-3">
                    @foreach ($shortcuts as $quick)
                        <a href="{{ $quick['url'] }}" class="group rounded-lg sm:rounded-xl border border-border bg-gradient-to-br from-background/80 to-background/40 px-3 sm:px-4 py-4 sm:py-5 shadow-sm transition hover:-translate-y-1 hover:border-primary/60 hover:shadow-md">
                            <span class="flex h-10 w-10 sm:h-11 sm:w-11 items-center justify-center rounded-full bg-primary/10 text-primary transition group-hover:bg-primary group-hover:text-primary-foreground">
                                <i data-lucide="{{ $quick['icon'] }}" class="h-4 w-4 sm:h-5 sm:w-5"></i>
                            </span>
                            <h4 class="mt-3 sm:mt-4 text-sm font-semibold text-foreground">{{ $quick['label'] }}</h4>
                            <p class="text-xs sm:text-sm text-muted-foreground leading-relaxed">{{ $quick['description'] }}</p>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="space-y-4 sm:space-y-6">
            <div class="rounded-xl sm:rounded-2xl border border-border bg-card shadow-sm">
                <div class="border-b border-border/70 px-4 sm:px-6 py-4 sm:py-5">
                    <h3 class="text-base sm:text-lg font-semibold text-foreground">Top produtos</h3>
                    <p class="text-xs sm:text-sm text-muted-foreground">Itens mais vendidos nos últimos 7 dias</p>
                </div>
                <div class="px-3 sm:px-6 py-4 sm:py-5">
                    @if (count($topProducts))
                        <div class="space-y-3 sm:space-y-4">
                            @foreach ($topProducts as $product)
                                <div class="flex items-center justify-between rounded-lg sm:rounded-xl border border-border/60 bg-gradient-to-r from-background/90 to-background/60 px-3 sm:px-4 py-3">
                                    <div class="flex items-center gap-2 sm:gap-3 flex-1 min-w-0">
                                        <span class="flex h-9 w-9 sm:h-10 sm:w-10 flex-shrink-0 items-center justify-center rounded-full bg-primary/10 text-primary font-semibold text-sm">
                                            {{ strtoupper(\Illuminate\Support\Str::substr($product->nome ?? $product['name'], 0, 1)) }}
                                        </span>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-xs sm:text-sm font-semibold text-foreground truncate">
                                                {{ $product->nome ?? $product['name'] ?? 'Produto' }}
                                            </p>
                                            <p class="text-xs text-muted-foreground truncate">
                                                {{ data_get($product, 'categoria', 'Sem categoria') }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-right flex-shrink-0 ml-2">
                                        <p class="text-xs sm:text-sm font-semibold text-foreground">
                                            {{ data_get($product, 'total_vendas', data_get($product, 'revenue', 'R$ 0,00')) }}
                                        </p>
                                        <p class="text-xs text-muted-foreground">
                                            {{ data_get($product, 'quantidade', data_get($product, 'sales', 0)) }} un.
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center gap-3 py-8 sm:py-10 text-center text-muted-foreground">
                            <span class="flex h-12 w-12 sm:h-14 sm:w-14 items-center justify-center rounded-full bg-muted">
                                <i data-lucide="pie-chart" class="h-5 w-5 sm:h-6 sm:w-6"></i>
                            </span>
                            <div>
                                <p class="font-semibold text-foreground text-sm">Ainda sem vendas</p>
                                <p class="text-xs">Os produtos mais vendidos aparecerão conforme os pedidos forem concluídos.</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="rounded-xl sm:rounded-2xl border border-border bg-card shadow-sm">
                <div class="border-b border-border/70 px-4 sm:px-6 py-4 sm:py-5">
                    <h3 class="text-base sm:text-lg font-semibold text-foreground">Próximos passos</h3>
                    <p class="text-xs sm:text-sm text-muted-foreground">Sugestões para manter tudo organizado</p>
                </div>
                <div class="space-y-3 sm:space-y-4 px-3 sm:px-6 py-4 sm:py-5">
                    <div class="flex items-start gap-2 sm:gap-3 rounded-lg sm:rounded-xl border border-border/70 bg-gradient-to-br from-success/5 to-background p-3 sm:p-4">
                        <span class="flex h-9 w-9 sm:h-10 sm:w-10 flex-shrink-0 items-center justify-center rounded-full bg-success/10 text-success">
                            <i data-lucide="check-circle-2" class="h-4 w-4 sm:h-5 sm:w-5"></i>
                        </span>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs sm:text-sm font-semibold text-foreground">Configure notificações automáticas</p>
                            <p class="text-xs text-muted-foreground leading-relaxed">
                                Defina templates no WhatsApp e garanta mensagens para status como preparo, entrega e conclusão.
                            </p>
                        </div>
                    </div>
                    <div class="flex items-start gap-2 sm:gap-3 rounded-lg sm:rounded-xl border border-border/70 bg-gradient-to-br from-accent/5 to-background p-3 sm:p-4">
                        <span class="flex h-9 w-9 sm:h-10 sm:w-10 flex-shrink-0 items-center justify-center rounded-full bg-accent/20 text-accent-foreground">
                            <i data-lucide="sparkles" class="h-4 w-4 sm:h-5 sm:w-5"></i>
                        </span>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs sm:text-sm font-semibold text-foreground">Ative campanhas de fidelidade</p>
                            <p class="text-xs text-muted-foreground leading-relaxed">
                                Configure cashback ou pontos para incentivar o retorno dos clientes e acompanhe no módulo Fidelidade.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (window.lucide) {
            window.lucide.createIcons();
        }
    });
</script>
@endpush