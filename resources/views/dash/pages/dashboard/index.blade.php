@extends('layouts.dashboard')

@section('page_title', 'Visão Geral')
@section('page_subtitle', 'Acompanhe o desempenho do seu negócio em tempo real')

@section('page_actions')
    <a href="{{ route('dashboard.orders.create') }}" class="btn-primary">
        <i data-lucide="plus" class="h-4 w-4"></i>
        Novo Pedido
    </a>
    <a href="{{ route('dashboard.reports') }}" class="btn">
        <i data-lucide="chart-column" class="h-4 w-4"></i>
        Ver Relatórios
    </a>
@endsection

@section('stat_cards')
    @php
        $metrics = $metrics ?? [];
        $statCards = [
            [
                'label' => 'Vendas Hoje',
                'value' => data_get($metrics, 'sales.today_formatted', 'R$ 0,00'),
                'diff'  => data_get($metrics, 'sales.diff', '+0% vs. ontem'),
                'icon'  => 'trending-up',
            ],
            [
                'label' => 'Pedidos',
                'value' => data_get($metrics, 'orders.count_today', 0),
                'diff'  => data_get($metrics, 'orders.diff', '+0 novos'),
                'icon'  => 'receipt',
            ],
            [
                'label' => 'Clientes',
                'value' => data_get($metrics, 'customers.active', 0),
                'diff'  => data_get($metrics, 'customers.diff', '+0 esta semana'),
                'icon'  => 'users',
            ],
            [
                'label' => 'Ticket Médio',
                'value' => data_get($metrics, 'sales.avg_ticket', 'R$ 0,00'),
                'diff'  => data_get($metrics, 'sales.avg_ticket_diff', '+0% vs. mês anterior'),
                'icon'  => 'wallet',
            ],
        ];
    @endphp

    @foreach ($statCards as $card)
        <div class="rounded-2xl border border-border bg-card p-6 shadow-sm transition hover:shadow-lg">
            <div class="flex items-center justify-between pb-4">
                <div class="space-y-1">
                    <p class="text-xs font-semibold uppercase tracking-widest text-muted-foreground">
                        {{ $card['label'] }}
                    </p>
                    <p class="text-2xl font-bold text-foreground">
                        {{ $card['value'] }}
                    </p>
                </div>
                <span class="flex h-11 w-11 items-center justify-center rounded-full bg-primary/10 text-primary">
                    <i data-lucide="{{ $card['icon'] }}" class="h-5 w-5"></i>
                </span>
            </div>
            <p class="flex items-center gap-2 text-xs font-medium text-muted-foreground">
                <i data-lucide="arrow-up-right" class="h-3.5 w-3.5"></i>
                {{ $card['diff'] }}
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

    <div class="grid gap-6 lg:grid-cols-[2fr,1.4fr]">
        <div class="space-y-6">
            <div class="rounded-2xl border border-border bg-card shadow-sm">
                <div class="flex items-center justify-between border-b border-border/70 px-6 py-5">
                    <div>
                        <h3 class="text-lg font-semibold text-foreground">Pedidos Recentes</h3>
                        <p class="text-sm text-muted-foreground">Pedidos criados nas últimas 24h</p>
                    </div>
                    <a href="{{ route('dashboard.orders.index') }}" class="btn">
                        <i data-lucide="arrow-right" class="h-4 w-4"></i>
                        Ver todos
                    </a>
                </div>

                <div class="px-6 py-5">
                    @if (count($recentOrders))
                        <div class="overflow-x-auto">
                            <table class="table-compact">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Cliente</th>
                                        <th>Status</th>
                                        <th>Entrega</th>
                                        <th class="text-right">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($recentOrders as $order)
                                        <tr>
                                            <td>
                                                <a href="{{ route('dashboard.orders.show', $order) }}" class="font-semibold text-primary hover:underline">
                                                    #{{ $order->id }}
                                                </a>
                                            </td>
                                            <td>{{ $order->cliente->nome ?? $order->cliente_nome ?? 'Cliente' }}</td>
                                            <td>
                                                <span class="badge badge-{{ $order->status === 'pago' ? 'success' : 'default' }}">
                                                    {{ ucfirst($order->status ?? 'pendente') }}
                                                </span>
                                            </td>
                                            <td>{{ optional($order->data_entrega)->format('d/m H:i') ?? '—' }}</td>
                                            <td class="text-right font-semibold">
                                                {{ data_get($order, 'total_formatado', 'R$ '.number_format($order->total ?? 0, 2, ',', '.')) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center gap-3 py-10 text-center text-muted-foreground">
                            <span class="flex h-14 w-14 items-center justify-center rounded-full bg-muted">
                                <i data-lucide="shopping-bag" class="h-6 w-6"></i>
                            </span>
                            <div>
                                <p class="font-semibold text-foreground">Nenhum pedido recente</p>
                                <p class="text-sm">Quando novos pedidos chegarem, eles aparecerão aqui.</p>
                            </div>
                            <a href="{{ route('dashboard.orders.create') }}" class="btn-primary">
                                Criar primeiro pedido
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <div class="rounded-2xl border border-border bg-card shadow-sm">
                <div class="flex items-center justify-between border-b border-border/70 px-6 py-5">
                    <div>
                        <h3 class="text-lg font-semibold text-foreground">Atalhos rápidos</h3>
                        <p class="text-sm text-muted-foreground">Acesse os fluxos principais em segundos</p>
                    </div>
                </div>

                <div class="grid gap-4 px-6 py-5 md:grid-cols-3">
                    @foreach ($shortcuts as $quick)
                        <a href="{{ $quick['url'] }}" class="group rounded-xl border border-border bg-background/60 px-4 py-5 shadow-sm transition hover:-translate-y-1 hover:border-primary/60 hover:shadow-lg">
                            <span class="flex h-11 w-11 items-center justify-center rounded-full bg-primary/10 text-primary transition group-hover:bg-primary group-hover:text-primary-foreground">
                                <i data-lucide="{{ $quick['icon'] }}" class="h-5 w-5"></i>
                            </span>
                            <h4 class="mt-4 text-sm font-semibold text-foreground">{{ $quick['label'] }}</h4>
                            <p class="text-sm text-muted-foreground">{{ $quick['description'] }}</p>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-2xl border border-border bg-card shadow-sm">
                <div class="border-b border-border/70 px-6 py-5">
                    <h3 class="text-lg font-semibold text-foreground">Top produtos</h3>
                    <p class="text-sm text-muted-foreground">Itens mais vendidos nos últimos 7 dias</p>
                </div>
                <div class="px-6 py-5">
                    @if (count($topProducts))
                        <div class="space-y-4">
                            @foreach ($topProducts as $product)
                                <div class="flex items-center justify-between rounded-xl border border-border/60 bg-background/70 px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-primary/10 text-primary font-semibold">
                                            {{ strtoupper(\Illuminate\Support\Str::substr($product->nome ?? $product['name'], 0, 1)) }}
                                        </span>
                                        <div>
                                            <p class="text-sm font-semibold text-foreground">
                                                {{ $product->nome ?? $product['name'] ?? 'Produto' }}
                                            </p>
                                            <p class="text-xs text-muted-foreground">
                                                {{ data_get($product, 'categoria', 'Categoria não definida') }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-semibold text-foreground">
                                            {{ data_get($product, 'total_vendas', data_get($product, 'revenue', 'R$ 0,00')) }}
                                        </p>
                                        <p class="text-xs text-muted-foreground">
                                            {{ data_get($product, 'quantidade', data_get($product, 'sales', 0)) }} vendas
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center gap-3 py-10 text-center text-muted-foreground">
                            <span class="flex h-14 w-14 items-center justify-center rounded-full bg-muted">
                                <i data-lucide="pie-chart" class="h-6 w-6"></i>
                            </span>
                            <div>
                                <p class="font-semibold text-foreground">Ainda sem vendas</p>
                                <p class="text-sm">Os produtos mais vendidos aparecerão conforme os pedidos forem concluídos.</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="rounded-2xl border border-border bg-card shadow-sm">
                <div class="border-b border-border/70 px-6 py-5">
                    <h3 class="text-lg font-semibold text-foreground">Próximos passos</h3>
                    <p class="text-sm text-muted-foreground">Sugestões para manter tudo organizado</p>
                </div>
                <div class="space-y-4 px-6 py-5">
                    <div class="flex items-start gap-3 rounded-xl border border-border/70 bg-background/80 p-4">
                        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-success/10 text-success">
                            <i data-lucide="check-circle-2" class="h-5 w-5"></i>
                        </span>
                        <div>
                            <p class="text-sm font-semibold text-foreground">Configure notificações automáticas</p>
                            <p class="text-sm text-muted-foreground">
                                Defina templates no WhatsApp e garanta mensagens para status como preparo, entrega e conclusão.
                            </p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 rounded-xl border border-border/70 bg-background/80 p-4">
                        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-accent/20 text-accent-foreground">
                            <i data-lucide="sparkles" class="h-5 w-5"></i>
                        </span>
                        <div>
                            <p class="text-sm font-semibold text-foreground">Ative campanhas de fidelidade</p>
                            <p class="text-sm text-muted-foreground">
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