@extends('dashboard.layouts.app')

@section('page_title', 'Visão Geral')
@section('page_subtitle', 'Acompanhe o desempenho do seu negócio em tempo real')

@section('page_actions')
    <a href="{{ route('dashboard.orders.index') }}" class="btn">
        <i data-lucide="list-checks" class="h-4 w-4"></i>
        Pedidos
    </a>
    <a href="{{ route('dashboard.reports') }}" class="btn-primary">
        <i data-lucide="chart-column" class="h-4 w-4"></i>
        Relatórios
    </a>
@endsection

@section('stat_cards')
    @php
        $statCards = [
            [
                'label' => 'Receita Hoje',
                'value' => 'R$ ' . number_format($receitaHoje ?? 0, 2, ',', '.'),
                'hint'  => 'Pedidos pagos no dia',
                'icon'  => 'wallet',
            ],
            [
                'label' => 'Pedidos Hoje',
                'value' => $pedidosHoje ?? 0,
                'hint'  => 'Totais criados nas últimas 24h',
                'icon'  => 'shopping-bag',
            ],
            [
                'label' => 'Pagos Hoje',
                'value' => $pagosHoje ?? 0,
                'hint'  => ($pedidosHoje ?? 0) > 0
                    ? number_format((($pagosHoje ?? 0) / max($pedidosHoje, 1)) * 100, 1, ',', '.') . '% aprovados'
                    : 'Sem pedidos hoje',
                'icon'  => 'circle-check-big',
            ],
            [
                'label' => 'Pendentes de Pagamento',
                'value' => $pendentesPagamento ?? 0,
                'hint'  => 'Acompanhe estes pedidos de perto',
                'icon'  => 'clock',
            ],
        ];
    @endphp

    @foreach ($statCards as $card)
        <div class="rounded-lg border border-border bg-card p-3 shadow-sm transition hover:-translate-y-[2px] hover:shadow-lg">
            <div class="flex items-center justify-between pb-2">
                <div class="space-y-0.5">
                    <p class="text-xs font-semibold uppercase tracking-widest text-muted-foreground">
                        {{ $card['label'] }}
                    </p>
                    <p class="text-xl font-bold text-foreground">
                        {{ $card['value'] }}
                    </p>
                </div>
                <span class="flex h-9 w-9 items-center justify-center rounded-full bg-primary/10 text-primary">
                    <i data-lucide="{{ $card['icon'] }}" class="h-4 w-4"></i>
                </span>
            </div>
            <p class="text-xs font-medium text-muted-foreground">
                {{ $card['hint'] }}
            </p>
        </div>
    @endforeach
@endsection

@section('content')
    <div class="grid gap-4 lg:grid-cols-2">
        <!-- Coluna Esquerda -->
        <div class="space-y-4">
            <div class="rounded-lg border border-border bg-card shadow-sm">
                <div class="flex items-center justify-between border-b border-border/70 px-4 py-3">
                    <div class="min-w-0 flex-1">
                        <h3 class="text-base font-semibold text-foreground">Pedidos Recentes</h3>
                        <p class="text-xs text-muted-foreground">Últimos pedidos criados na plataforma</p>
                    </div>
                    <a href="{{ route('dashboard.orders.index') }}" class="btn flex-shrink-0 ml-2">
                        <i data-lucide="arrow-right" class="h-4 w-4"></i>
                        Ver todos
                    </a>
                </div>
                <div class="px-4 py-3">
                    @if(isset($recentOrders) && $recentOrders->count())
                        <div class="space-y-3 overflow-y-auto max-h-[380px] pr-1">
                            @foreach($recentOrders as $order)
                                <a href="{{ route('dashboard.orders.show', $order) }}"
                                   class="flex items-center justify-between rounded-xl border border-border/80 bg-background/60 px-4 py-3 text-sm transition hover:border-primary/40 hover:bg-primary/5">
                                    <div class="min-w-0 flex-1">
                                        <p class="font-semibold text-foreground truncate">
                                            #{{ $order->order_number ?? $order->id }} — {{ $order->customer->name ?? 'Cliente' }}
                                        </p>
                                        <p class="text-xs text-muted-foreground">
                                            {{ optional($order->created_at)->format('d/m H:i') }} •
                                            {{ ucfirst($order->status ?? 'sem status') }}
                                        </p>
                                    </div>
                                    <div class="text-right pl-3 flex-shrink-0">
                                        <p class="font-semibold text-foreground">
                                            R$ {{ number_format((float)($order->final_amount ?? 0), 2, ',', '.') }}
                                        </p>
                                        <p class="text-xs text-muted-foreground">
                                            {{ strtoupper($order->payment_status ?? 'pendente') }}
                                        </p>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center gap-3 py-8 text-center text-muted-foreground">
                            <span class="flex h-12 w-12 items-center justify-center rounded-full bg-muted overflow-hidden">
                                <i data-lucide="shopping-basket" class="h-5 w-5"></i>
                            </span>
                            <div>
                                <p class="font-semibold text-foreground text-sm">Nenhum pedido por aqui</p>
                                <p class="text-xs mt-1">Quando os pedidos chegarem, eles aparecerão nesta lista.</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="rounded-lg border border-border bg-card shadow-sm">
                <div class="flex items-center justify-between border-b border-border/70 px-4 py-3">
                    <div class="min-w-0 flex-1">
                        <h3 class="text-base font-semibold text-foreground">Pedidos Agendados</h3>
                        <p class="text-xs text-muted-foreground">Próximas entregas com horário definido</p>
                    </div>
                    <span class="rounded-full bg-primary/10 px-3 py-1 text-xs font-medium text-primary flex-shrink-0 ml-2">
                        {{ $scheduledTodayCount ?? 0 }} hoje
                    </span>
                </div>
                <div class="px-4 py-3">
                    @if(isset($nextScheduled) && $nextScheduled->count())
                        <div class="space-y-3">
                            @foreach($nextScheduled as $order)
                                <div class="flex items-center justify-between rounded-xl border border-border/70 bg-background/70 px-4 py-3 text-sm">
                                    <div class="min-w-0 flex-1">
                                        <p class="font-semibold text-foreground truncate">
                                            #{{ $order->order_number ?? $order->id }}
                                            — {{ $order->customer->name ?? 'Cliente' }}
                                        </p>
                                        <p class="text-xs text-muted-foreground">
                                            Entrega {{ optional($order->scheduled_delivery_at)->format('d/m H:i') ?? '—' }}
                                        </p>
                                    </div>
                                    <div class="text-right flex-shrink-0 ml-3">
                                        <p class="font-semibold text-foreground">
                                            R$ {{ number_format((float)($order->final_amount ?? 0), 2, ',', '.') }}
                                        </p>
                                        <p class="text-xs text-muted-foreground">
                                            {{ ucfirst($order->status ?? 'pendente') }}
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center gap-3 py-8 text-center text-muted-foreground">
                            <span class="flex h-12 w-12 items-center justify-center rounded-full bg-muted overflow-hidden">
                                <i data-lucide="calendar-clock" class="h-5 w-5"></i>
                            </span>
                            <div>
                                <p class="font-semibold text-foreground text-sm">Nenhum agendamento</p>
                                <p class="text-xs mt-1">Assim que um pedido tiver horário marcado, ele aparecerá aqui.</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Coluna Direita -->
        <div class="space-y-4">
            <div class="rounded-lg border border-border bg-card shadow-sm">
                <div class="border-b border-border/70 px-4 py-3">
                    <div class="min-w-0">
                        <h3 class="text-base font-semibold text-foreground">Top produtos</h3>
                        <p class="text-xs text-muted-foreground">Desempenho nos últimos 7 dias</p>
                    </div>
                </div>
                <div class="px-4 py-3">
                    @if(isset($topProducts) && $topProducts->count())
                        <div class="space-y-3">
                            @foreach($topProducts as $item)
                                <div class="flex items-center justify-between rounded-xl border border-border/70 bg-background/70 px-4 py-3">
                                    <div class="flex items-center gap-3 min-w-0 flex-1">
                                        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-primary/10 text-primary font-semibold flex-shrink-0">
                                            {{ strtoupper(\Illuminate\Support\Str::substr(optional($item['product'])->name ?? 'P', 0, 1)) }}
                                        </span>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-semibold text-foreground truncate">
                                                {{ optional($item['product'])->name ?? 'Produto removido' }}
                                            </p>
                                            <p class="text-xs text-muted-foreground">
                                                {{ $item['quantity'] }} unidades • R$ {{ number_format((float)$item['revenue'], 2, ',', '.') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center gap-3 py-8 text-center text-muted-foreground">
                            <span class="flex h-12 w-12 items-center justify-center rounded-full bg-muted overflow-hidden">
                                <i data-lucide="package" class="h-5 w-5"></i>
                            </span>
                            <div>
                                <p class="font-semibold text-foreground text-sm">Ainda sem histórico</p>
                                <p class="text-xs mt-1">Conclua vendas para acompanhar os destaques por aqui.</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="rounded-lg border border-border bg-card shadow-sm">
                <div class="border-b border-border/70 px-4 py-3">
                    <div class="min-w-0">
                        <h3 class="text-base font-semibold text-foreground">Status dos pedidos</h3>
                        <p class="text-xs text-muted-foreground">Situação atual da operação</p>
                    </div>
                </div>
                <div class="grid gap-2 px-4 py-3">
                    @php
                        $statusMap = [
                            'pending'   => ['label' => 'Pendentes', 'icon' => 'hourglass', 'color' => 'text-warning'],
                            'confirmed' => ['label' => 'Confirmados', 'icon' => 'check-circle', 'color' => 'text-success'],
                            'preparing' => ['label' => 'Em preparo', 'icon' => 'chef-hat', 'color' => 'text-primary'],
                            'delivered' => ['label' => 'Entregues', 'icon' => 'truck', 'color' => 'text-muted-foreground'],
                        ];
                    @endphp
                    @foreach ($statusMap as $key => $meta)
                        <div class="flex items-center justify-between rounded-lg border border-border/70 bg-background/70 px-3 py-2.5 text-sm">
                            <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-muted/60 {{ $meta['color'] }} flex-shrink-0 overflow-hidden">
                                    <i data-lucide="{{ $meta['icon'] }}" class="h-4 w-4"></i>
                                </span>
                                <span class="font-medium text-foreground truncate">{{ $meta['label'] }}</span>
                            </div>
                            <span class="text-right font-semibold text-foreground flex-shrink-0 ml-2">
                                {{ $statusCount[$key] ?? 0 }}
                            </span>
                        </div>
                    @endforeach
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
