@extends('layouts.admin')

@section('title', 'Planos e Assinatura')
@section('page_title', 'Planos e Assinatura')
@section('page_subtitle', 'Gerencie sua assinatura e escolha o melhor plano para seu negócio')

@section('content')
<div class="space-y-6">
    {{-- Notificações de Vencimento --}}
    @if($notifications && count($notifications) > 0)
        <div class="rounded-lg border border-warning/30 bg-warning/10 p-4">
            <div class="flex items-start gap-3">
                <i data-lucide="alert-triangle" class="h-5 w-5 text-warning mt-0.5"></i>
                <div class="flex-1">
                    <h4 class="font-medium text-warning">Atenção</h4>
                    @foreach($notifications as $notification)
                        <p class="text-sm text-warning/80 mt-1">{{ $notification->message }}</p>
                    @endforeach
                </div>
                <form action="{{ route('dashboard.subscription.notifications.read-all') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-xs text-warning hover:underline">Marcar como lida</button>
                </form>
            </div>
        </div>
    @endif

    {{-- Card da Assinatura Atual --}}
    <div class="rounded-lg border border-border bg-card p-6 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-foreground">Sua Assinatura</h2>
                @if($subscription)
                    <div class="flex items-center gap-3 mt-2">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-primary/10 text-primary">
                            {{ $subscription->plan->name ?? 'Plano Básico' }}
                        </span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $subscription->status_color }}/10 text-{{ $subscription->status_color }}">
                            {{ $subscription->status_label }}
                        </span>
                    </div>
                @else
                    <p class="text-muted-foreground mt-2">Você ainda não possui uma assinatura ativa.</p>
                @endif
            </div>

            @if($subscription)
                <div class="flex flex-col items-end gap-2">
                    <div class="text-3xl font-bold text-foreground">
                        {{ $subscription->formatted_price }}<span class="text-lg font-normal text-muted-foreground">/mês</span>
                    </div>
                    @if($subscription->addons->count() > 0)
                        <div class="text-sm text-muted-foreground">
                            + {{ $subscription->addons->count() }} adicionais = <span class="font-medium text-foreground">{{ $subscription->formatted_monthly_total }}</span>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        @if($subscription)
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6 pt-6 border-t border-border">
                <div>
                    <p class="text-sm text-muted-foreground">Data de Início</p>
                    <p class="font-medium text-foreground">
                        {{ $subscription->started_at?->format('d/m/Y') ?? '-' }}
                    </p>
                </div>
                <div>
                    <p class="text-sm text-muted-foreground">Período Atual</p>
                    <p class="font-medium text-foreground">
                        {{ $subscription->current_period_start?->format('d/m/Y') ?? '-' }} 
                        até 
                        {{ $subscription->current_period_end?->format('d/m/Y') ?? '-' }}
                    </p>
                </div>
                <div>
                    <p class="text-sm text-muted-foreground">Próxima Renovação</p>
                    <p class="font-medium text-foreground">
                        @if($subscription->current_period_end)
                            {{ $subscription->current_period_end->format('d/m/Y') }}
                            @if($subscription->daysUntilExpiry() !== null)
                                <span class="text-sm {{ $subscription->isExpiringSoon() ? 'text-warning' : 'text-muted-foreground' }}">
                                    ({{ $subscription->daysUntilExpiry() }} dias)
                                </span>
                            @endif
                        @else
                            -
                        @endif
                    </p>
                </div>
            </div>

            {{-- Botão Renovar --}}
            <div class="mt-6 pt-6 border-t border-border flex justify-end">
                <form action="{{ route('dashboard.subscription.renew') }}" method="POST">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-primary-foreground rounded-md hover:bg-primary/90 transition">
                        <i data-lucide="refresh-cw" class="h-4 w-4"></i>
                        Renovar Agora
                    </button>
                </form>
            </div>
        @endif
    </div>

    {{-- Adicionais da Assinatura --}}
    @if($subscription && $subscription->addons->count() > 0)
        <div class="rounded-lg border border-border bg-card p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-foreground mb-4">Adicionais Contratados</h3>
            <div class="space-y-3">
                @foreach($subscription->addons as $addon)
                    <div class="flex items-center justify-between p-3 rounded-md bg-muted/30">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center">
                                <i data-lucide="message-circle" class="h-5 w-5 text-primary"></i>
                            </div>
                            <div>
                                <p class="font-medium text-foreground">{{ $addon->type_label }}</p>
                                <p class="text-sm text-muted-foreground">{{ $addon->description }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-medium text-foreground">{{ $addon->formatted_unit_price }}/mês</p>
                            @if($addon->prorated_price)
                                <p class="text-xs text-muted-foreground">Proporcional pago: {{ $addon->formatted_prorated_price }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Planos Disponíveis --}}
    <div class="rounded-lg border border-border bg-card p-6 shadow-sm">
        <h3 class="text-lg font-semibold text-foreground mb-6">Planos Disponíveis</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($plans as $plan)
                @php
                    $isCurrentPlan = $subscription && $subscription->plan_id === $plan->id;
                @endphp
                <div class="relative rounded-lg border {{ $plan->is_featured ? 'border-primary shadow-lg' : 'border-border' }} bg-card p-6 {{ $isCurrentPlan ? 'ring-2 ring-primary' : '' }}">
                    @if($plan->is_featured)
                        <div class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-1 bg-primary text-primary-foreground text-xs font-medium rounded-full">
                            Mais Popular
                        </div>
                    @endif
                    
                    @if($isCurrentPlan)
                        <div class="absolute -top-3 right-4 px-3 py-1 bg-success text-success-foreground text-xs font-medium rounded-full">
                            Plano Atual
                        </div>
                    @endif

                    <div class="text-center mb-6">
                        <h4 class="text-xl font-bold text-foreground">{{ $plan->name }}</h4>
                        <p class="text-sm text-muted-foreground mt-1">{{ $plan->description }}</p>
                        <div class="mt-4">
                            <span class="text-4xl font-bold text-foreground">{{ $plan->formatted_price }}</span>
                            <span class="text-muted-foreground">/mês</span>
                        </div>
                    </div>

                    <ul class="space-y-3 mb-6">
                        @foreach($plan->features ?? [] as $feature)
                            <li class="flex items-start gap-2 text-sm">
                                <i data-lucide="check" class="h-4 w-4 text-success mt-0.5 flex-shrink-0"></i>
                                <span class="text-foreground">{{ $feature }}</span>
                            </li>
                        @endforeach
                    </ul>

                    <div class="flex flex-wrap gap-2 text-xs text-muted-foreground mb-4">
                        @if($plan->has_whatsapp)
                            <span class="inline-flex items-center gap-1 px-2 py-1 bg-success/10 text-success rounded">
                                <i data-lucide="message-circle" class="h-3 w-3"></i> WhatsApp
                            </span>
                        @endif
                        @if($plan->has_ai)
                            <span class="inline-flex items-center gap-1 px-2 py-1 bg-primary/10 text-primary rounded">
                                <i data-lucide="bot" class="h-3 w-3"></i> I.A.
                            </span>
                        @endif
                    </div>

                    @if(!$isCurrentPlan)
                        <form action="{{ route('dashboard.subscription.upgrade') }}" method="POST">
                            @csrf
                            <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                            <button type="submit" class="w-full px-4 py-2 {{ $plan->is_featured ? 'bg-primary text-primary-foreground hover:bg-primary/90' : 'bg-secondary text-secondary-foreground hover:bg-secondary/80' }} rounded-md transition font-medium">
                                @if($subscription && $plan->price > $subscription->price)
                                    Fazer Upgrade
                                @elseif($subscription)
                                    Alterar Plano
                                @else
                                    Assinar
                                @endif
                            </button>
                        </form>
                    @else
                        <button disabled class="w-full px-4 py-2 bg-muted text-muted-foreground rounded-md cursor-not-allowed font-medium">
                            Plano Atual
                        </button>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    {{-- Histórico de Faturas --}}
    @if($subscription && $subscription->invoices && $subscription->invoices->count() > 0)
        <div class="rounded-lg border border-border bg-card p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-foreground">Últimas Faturas</h3>
                <a href="{{ route('dashboard.subscription.invoices') }}" class="text-sm text-primary hover:underline">
                    Ver todas
                </a>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-border">
                            <th class="text-left py-2 text-sm font-medium text-muted-foreground">Fatura</th>
                            <th class="text-left py-2 text-sm font-medium text-muted-foreground">Valor</th>
                            <th class="text-left py-2 text-sm font-medium text-muted-foreground">Vencimento</th>
                            <th class="text-left py-2 text-sm font-medium text-muted-foreground">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($subscription->invoices as $invoice)
                            <tr class="border-b border-border/50">
                                <td class="py-3 text-sm font-medium text-foreground">#{{ $invoice->invoice_number }}</td>
                                <td class="py-3 text-sm text-foreground">{{ $invoice->formatted_amount }}</td>
                                <td class="py-3 text-sm text-muted-foreground">{{ $invoice->due_date->format('d/m/Y') }}</td>
                                <td class="py-3">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-{{ $invoice->status_color }}/10 text-{{ $invoice->status_color }}">
                                        {{ $invoice->status_label }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection
