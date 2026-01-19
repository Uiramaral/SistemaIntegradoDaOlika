@extends('layouts.admin')

@section('title', 'Dashboard Master')
@section('page_title', 'Dashboard Master')
@section('page_subtitle', 'Visão geral do sistema - Olika Tecnologia')

@section('content')
<div class="space-y-6">
    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="rounded-lg border border-border bg-card p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-muted-foreground">Total de Clientes</p>
                    <p class="text-2xl font-bold text-foreground">{{ number_format($stats['total_clients']) }}</p>
                </div>
                <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center">
                    <i data-lucide="building-2" class="h-6 w-6 text-primary"></i>
                </div>
            </div>
            <p class="text-xs text-muted-foreground mt-2">{{ $stats['active_clients'] }} ativos</p>
        </div>

        <div class="rounded-lg border border-border bg-card p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-muted-foreground">Assinaturas Ativas</p>
                    <p class="text-2xl font-bold text-foreground">{{ number_format($stats['active_subscriptions']) }}</p>
                </div>
                <div class="w-12 h-12 rounded-full bg-success/10 flex items-center justify-center">
                    <i data-lucide="check-circle" class="h-6 w-6 text-success"></i>
                </div>
            </div>
            <p class="text-xs text-warning mt-2">{{ $stats['expiring_subscriptions'] }} expirando em 7 dias</p>
        </div>

        <div class="rounded-lg border border-border bg-card p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-muted-foreground">Receita Mensal</p>
                    <p class="text-2xl font-bold text-foreground">R$ {{ number_format($stats['total_revenue'], 2, ',', '.') }}</p>
                </div>
                <div class="w-12 h-12 rounded-full bg-success/10 flex items-center justify-center">
                    <i data-lucide="dollar-sign" class="h-6 w-6 text-success"></i>
                </div>
            </div>
            <p class="text-xs text-muted-foreground mt-2">Assinaturas ativas</p>
        </div>

        <div class="rounded-lg border border-border bg-card p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-muted-foreground">Pedidos Hoje</p>
                    <p class="text-2xl font-bold text-foreground">{{ number_format($stats['orders_today']) }}</p>
                </div>
                <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center">
                    <i data-lucide="shopping-cart" class="h-6 w-6 text-primary"></i>
                </div>
            </div>
            <p class="text-xs text-muted-foreground mt-2">{{ number_format($stats['orders_this_month']) }} este mês</p>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <a href="{{ route('master.clients.create') }}" class="rounded-lg border border-border bg-card p-4 shadow-sm hover:border-primary/50 transition group">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center group-hover:bg-primary/20 transition">
                    <i data-lucide="plus" class="h-5 w-5 text-primary"></i>
                </div>
                <div>
                    <p class="font-medium text-foreground">Novo Cliente</p>
                    <p class="text-xs text-muted-foreground">Adicionar estabelecimento</p>
                </div>
            </div>
        </a>

        <a href="{{ route('master.plans.index') }}" class="rounded-lg border border-border bg-card p-4 shadow-sm hover:border-primary/50 transition group">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center group-hover:bg-primary/20 transition">
                    <i data-lucide="crown" class="h-5 w-5 text-primary"></i>
                </div>
                <div>
                    <p class="font-medium text-foreground">Planos</p>
                    <p class="text-xs text-muted-foreground">Gerenciar planos</p>
                </div>
            </div>
        </a>

        <a href="{{ route('master.whatsapp-urls.index') }}" class="rounded-lg border border-border bg-card p-4 shadow-sm hover:border-primary/50 transition group">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-success/10 flex items-center justify-center group-hover:bg-success/20 transition">
                    <i data-lucide="message-circle" class="h-5 w-5 text-success"></i>
                </div>
                <div>
                    <p class="font-medium text-foreground">WhatsApp URLs</p>
                    <p class="text-xs text-muted-foreground">Instâncias Railway</p>
                </div>
            </div>
        </a>

        <a href="{{ route('master.settings.index') }}" class="rounded-lg border border-border bg-card p-4 shadow-sm hover:border-primary/50 transition group">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-muted flex items-center justify-center group-hover:bg-muted/70 transition">
                    <i data-lucide="settings" class="h-5 w-5 text-muted-foreground"></i>
                </div>
                <div>
                    <p class="font-medium text-foreground">Configurações</p>
                    <p class="text-xs text-muted-foreground">Configurações globais</p>
                </div>
            </div>
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Últimos Clientes --}}
        <div class="rounded-lg border border-border bg-card p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-foreground">Últimos Clientes</h3>
                <a href="{{ route('master.clients.index') }}" class="text-sm text-primary hover:underline">Ver todos</a>
            </div>
            
            <div class="space-y-3">
                @forelse($recentClients as $client)
                    <div class="flex items-center justify-between p-3 rounded-md bg-muted/30">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center">
                                <span class="text-sm font-bold text-primary">{{ substr($client->name, 0, 2) }}</span>
                            </div>
                            <div>
                                <p class="font-medium text-foreground">{{ $client->name }}</p>
                                <p class="text-xs text-muted-foreground">{{ $client->slug }}.menuolika.com.br</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $client->active ? 'bg-success/10 text-success' : 'bg-destructive/10 text-destructive' }}">
                                {{ $client->active ? 'Ativo' : 'Inativo' }}
                            </span>
                            @if($client->subscription?->plan)
                                <p class="text-xs text-muted-foreground mt-1">{{ $client->subscription->plan->name }}</p>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-center text-muted-foreground py-4">Nenhum cliente cadastrado</p>
                @endforelse
            </div>
        </div>

        {{-- Assinaturas Expirando --}}
        <div class="rounded-lg border border-border bg-card p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-foreground">Assinaturas Expirando</h3>
                <span class="text-xs text-muted-foreground">Próximos 7 dias</span>
            </div>
            
            <div class="space-y-3">
                @forelse($expiringSubscriptions as $subscription)
                    <div class="flex items-center justify-between p-3 rounded-md bg-warning/10 border border-warning/20">
                        <div>
                            <p class="font-medium text-foreground">{{ $subscription->client->name ?? 'Cliente' }}</p>
                            <p class="text-xs text-muted-foreground">{{ $subscription->plan->name ?? 'Plano' }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium text-warning">
                                {{ $subscription->current_period_end?->format('d/m/Y') }}
                            </p>
                            <p class="text-xs text-warning">
                                {{ $subscription->daysUntilExpiry() }} dias
                            </p>
                        </div>
                    </div>
                @empty
                    <p class="text-center text-muted-foreground py-4">Nenhuma assinatura expirando</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Distribuição por Plano --}}
    <div class="rounded-lg border border-border bg-card p-6 shadow-sm">
        <h3 class="text-lg font-semibold text-foreground mb-4">Distribuição por Plano</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach($planDistribution as $plan)
                <div class="p-4 rounded-lg border border-border bg-muted/20">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium text-foreground">{{ $plan->name }}</p>
                            <p class="text-sm text-muted-foreground">{{ $plan->formatted_price }}/mês</p>
                        </div>
                        <div class="text-right">
                            <p class="text-2xl font-bold text-primary">{{ $plan->subscriptions_count }}</p>
                            <p class="text-xs text-muted-foreground">clientes</p>
                        </div>
                    </div>
                    <div class="mt-3 flex gap-2">
                        @if($plan->has_whatsapp)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-success/10 text-success text-xs rounded">
                                <i data-lucide="message-circle" class="h-3 w-3"></i> WhatsApp
                            </span>
                        @endif
                        @if($plan->has_ai)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-primary/10 text-primary text-xs rounded">
                                <i data-lucide="bot" class="h-3 w-3"></i> I.A.
                            </span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- =================================================== --}}
    {{-- RELATÓRIO DE LUCRO DE IA (Gemini) --}}
    {{-- =================================================== --}}
    
    {{-- Cards de Estatísticas de IA --}}
    <div class="mt-6">
        <div class="flex items-center gap-2 mb-4">
            <i data-lucide="bot" class="h-6 w-6 text-primary"></i>
            <h2 class="text-xl font-bold text-foreground">Relatório de IA (Mês Atual)</h2>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            {{-- Lucro Total --}}
            <div class="rounded-lg border border-success/30 bg-success/5 p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">Lucro IA (Mês)</p>
                        <p class="text-2xl font-bold text-success">R$ {{ number_format($aiStats['total_profit_brl'] ?? 0, 2, ',', '.') }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-success/10 flex items-center justify-center">
                        <i data-lucide="trending-up" class="h-6 w-6 text-success"></i>
                    </div>
                </div>
                <div class="mt-2 flex items-center gap-2">
                    <span class="text-xs px-2 py-0.5 bg-success/20 text-success rounded">+{{ number_format($aiStats['profit_margin'] ?? 0, 0) }}% margem</span>
                </div>
            </div>

            {{-- Cobrado dos Clientes --}}
            <div class="rounded-lg border border-border bg-card p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">Cobrado dos Clientes</p>
                        <p class="text-2xl font-bold text-foreground">R$ {{ number_format($aiStats['total_charged_brl'] ?? 0, 2, ',', '.') }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center">
                        <i data-lucide="receipt" class="h-6 w-6 text-primary"></i>
                    </div>
                </div>
                <p class="text-xs text-muted-foreground mt-2">Custo Google: R$ {{ number_format($aiStats['total_cost_brl'] ?? 0, 2, ',', '.') }}</p>
            </div>

            {{-- Requisições --}}
            <div class="rounded-lg border border-border bg-card p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">Requisições IA</p>
                        <p class="text-2xl font-bold text-foreground">{{ number_format($aiStats['total_requests'] ?? 0) }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center">
                        <i data-lucide="message-square" class="h-6 w-6 text-primary"></i>
                    </div>
                </div>
                <p class="text-xs text-muted-foreground mt-2">{{ number_format($aiStats['clients_with_ai'] ?? 0) }} clientes usando IA</p>
            </div>

            {{-- Tokens --}}
            <div class="rounded-lg border border-border bg-card p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">Tokens Consumidos</p>
                        <p class="text-2xl font-bold text-foreground">{{ number_format($aiStats['total_tokens'] ?? 0) }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-warning/10 flex items-center justify-center">
                        <i data-lucide="coins" class="h-6 w-6 text-warning"></i>
                    </div>
                </div>
                @if(($aiStats['total_errors'] ?? 0) > 0)
                    <p class="text-xs text-destructive mt-2">{{ $aiStats['total_errors'] }} erros este mês</p>
                @else
                    <p class="text-xs text-success mt-2">Sem erros este mês</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Tabelas de Lucro por Cliente e Uso por Modelo --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Top 10 Clientes mais Lucrativos --}}
        <div class="rounded-lg border border-border bg-card p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-foreground">Top 10 Clientes (Lucro IA)</h3>
                <span class="text-xs text-muted-foreground">Mês atual</span>
            </div>
            
            @if(isset($aiProfitByClient) && $aiProfitByClient->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-border">
                                <th class="text-left py-2 font-medium text-muted-foreground">Cliente</th>
                                <th class="text-right py-2 font-medium text-muted-foreground">Reqs</th>
                                <th class="text-right py-2 font-medium text-muted-foreground">Lucro</th>
                                <th class="text-right py-2 font-medium text-muted-foreground">Saldo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($aiProfitByClient as $client)
                                <tr class="border-b border-border/50 hover:bg-muted/30">
                                    <td class="py-2">
                                        <div class="font-medium text-foreground">{{ $client->name }}</div>
                                        <div class="text-xs text-muted-foreground">{{ $client->slug }}</div>
                                    </td>
                                    <td class="py-2 text-right text-muted-foreground">{{ number_format($client->requests_count) }}</td>
                                    <td class="py-2 text-right">
                                        <span class="font-medium text-success">R$ {{ number_format($client->total_profit_brl, 2, ',', '.') }}</span>
                                    </td>
                                    <td class="py-2 text-right">
                                        <span class="{{ ($client->ai_balance ?? 0) < 1 ? 'text-destructive' : 'text-foreground' }}">R$ {{ number_format($client->ai_balance ?? 0, 2, ',', '.') }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8">
                    <i data-lucide="bar-chart-2" class="h-12 w-12 text-muted-foreground/50 mx-auto mb-2"></i>
                    <p class="text-muted-foreground">Nenhum uso de IA registrado este mês</p>
                    <p class="text-xs text-muted-foreground mt-1">Execute o SQL de migração para habilitar o tracking</p>
                </div>
            @endif
        </div>

        {{-- Uso por Modelo --}}
        <div class="rounded-lg border border-border bg-card p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-foreground">Uso por Modelo Gemini</h3>
                <span class="text-xs text-muted-foreground">Mês atual</span>
            </div>
            
            @if(isset($aiUsageByModel) && $aiUsageByModel->count() > 0)
                <div class="space-y-4">
                    @foreach($aiUsageByModel as $model)
                        @php
                            $modelLabels = [
                                'gemini-2.5-flash' => ['label' => 'Flash 2.5', 'color' => 'primary', 'desc' => 'Chat rápido'],
                                'gemini-2.5-flash-lite' => ['label' => 'Flash Lite', 'color' => 'success', 'desc' => 'Econômico'],
                                'gemini-3-pro' => ['label' => 'Pro 3', 'color' => 'warning', 'desc' => 'Avançado'],
                            ];
                            $info = $modelLabels[$model->model] ?? ['label' => $model->model, 'color' => 'muted', 'desc' => ''];
                        @endphp
                        <div class="p-4 rounded-lg border border-border bg-muted/20">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-1 bg-{{ $info['color'] }}/10 text-{{ $info['color'] }} text-xs font-medium rounded">
                                        {{ $info['label'] }}
                                    </span>
                                    <span class="text-xs text-muted-foreground">{{ $info['desc'] }}</span>
                                </div>
                                <span class="text-sm font-bold text-success">R$ {{ number_format($model->total_profit_brl, 2, ',', '.') }}</span>
                            </div>
                            <div class="grid grid-cols-3 gap-4 text-center">
                                <div>
                                    <p class="text-lg font-bold text-foreground">{{ number_format($model->requests_count) }}</p>
                                    <p class="text-xs text-muted-foreground">requisições</p>
                                </div>
                                <div>
                                    <p class="text-lg font-bold text-foreground">{{ number_format($model->total_tokens) }}</p>
                                    <p class="text-xs text-muted-foreground">tokens</p>
                                </div>
                                <div>
                                    <p class="text-lg font-bold text-foreground">R$ {{ number_format($model->total_cost_brl, 2, ',', '.') }}</p>
                                    <p class="text-xs text-muted-foreground">custo Google</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <i data-lucide="cpu" class="h-12 w-12 text-muted-foreground/50 mx-auto mb-2"></i>
                    <p class="text-muted-foreground">Nenhum modelo utilizado este mês</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
