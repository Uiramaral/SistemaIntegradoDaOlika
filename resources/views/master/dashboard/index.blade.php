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
</div>
@endsection
