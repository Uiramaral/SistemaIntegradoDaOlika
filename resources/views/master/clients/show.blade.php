@extends('layouts.admin')

@section('title', 'Cliente: ' . $client->name)
@section('page_title', $client->name)
@section('page_subtitle', 'Detalhes do estabelecimento')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    {{-- Header com Ações --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('master.clients.index') }}" class="p-2 rounded-md hover:bg-muted transition">
                <i data-lucide="arrow-left" class="h-5 w-5"></i>
            </a>
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-foreground">{{ $client->name }}</h1>
                    @if($client->active)
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Ativo</span>
                    @else
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">Inativo</span>
                    @endif
                </div>
                <p class="text-sm text-muted-foreground">{{ $client->slug }}.menuolika.com.br</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('master.clients.edit', $client) }}" class="px-4 py-2 bg-primary text-primary-foreground rounded-md hover:bg-primary/90 transition flex items-center gap-2">
                <i data-lucide="edit" class="h-4 w-4"></i>
                Editar
            </a>
            <form action="{{ route('master.clients.toggle-status', $client) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="px-4 py-2 {{ $client->active ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700' }} text-white rounded-md transition">
                    {{ $client->active ? 'Desativar' : 'Ativar' }}
                </button>
            </form>
        </div>
    </div>

    {{-- Cards de Estatísticas --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="rounded-lg border border-border bg-card p-4">
            <p class="text-sm text-muted-foreground">Pedidos Total</p>
            <p class="text-2xl font-bold text-foreground">{{ $orderStats['total'] ?? 0 }}</p>
        </div>
        <div class="rounded-lg border border-border bg-card p-4">
            <p class="text-sm text-muted-foreground">Pedidos Este Mês</p>
            <p class="text-2xl font-bold text-foreground">{{ $orderStats['this_month'] ?? 0 }}</p>
        </div>
        <div class="rounded-lg border border-border bg-card p-4">
            <p class="text-sm text-muted-foreground">Receita Este Mês</p>
            <p class="text-2xl font-bold text-green-600">R$ {{ number_format($orderStats['revenue_this_month'] ?? 0, 2, ',', '.') }}</p>
        </div>
        <div class="rounded-lg border border-border bg-card p-4">
            <p class="text-sm text-muted-foreground">Cadastrado em</p>
            <p class="text-lg font-semibold text-foreground">{{ $client->created_at->format('d/m/Y') }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Informações do Cliente --}}
        <div class="rounded-lg border border-border bg-card">
            <div class="p-4 border-b border-border">
                <h3 class="font-semibold text-foreground">Informações</h3>
            </div>
            <div class="p-4 space-y-3">
                <div class="flex justify-between">
                    <span class="text-muted-foreground">Email</span>
                    <span class="text-foreground">{{ $client->email ?? '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-muted-foreground">Telefone</span>
                    <span class="text-foreground">{{ $client->phone ?? '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-muted-foreground">WhatsApp</span>
                    <span class="text-foreground">{{ $client->whatsapp_phone ?? '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-muted-foreground">URL do Cardápio</span>
                    <a href="https://{{ $client->slug }}.menuolika.com.br" target="_blank" class="text-primary hover:underline flex items-center gap-1">
                        {{ $client->slug }}.menuolika.com.br
                        <i data-lucide="external-link" class="h-3 w-3"></i>
                    </a>
                </div>
            </div>
        </div>

        {{-- Assinatura --}}
        <div class="rounded-lg border border-border bg-card">
            <div class="p-4 border-b border-border flex justify-between items-center">
                <h3 class="font-semibold text-foreground">Assinatura</h3>
                @if($client->subscription)
                    <span class="px-2 py-1 text-xs rounded-full {{ $client->subscription->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                        {{ $client->subscription->status_label }}
                    </span>
                @endif
            </div>
            <div class="p-4 space-y-3">
                @if($client->subscription)
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">Plano</span>
                        <span class="text-foreground font-medium">{{ $client->subscription->plan?->name ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">Valor</span>
                        <span class="text-foreground">R$ {{ number_format($client->subscription->price, 2, ',', '.') }}/mês</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">Próximo Vencimento</span>
                        <span class="text-foreground">{{ $client->subscription->current_period_end?->format('d/m/Y') ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">Dias Restantes</span>
                        <span class="text-foreground {{ ($client->subscription->days_until_expiration ?? 0) <= 7 ? 'text-red-600 font-bold' : '' }}">
                            {{ $client->subscription->days_until_expiration ?? 0 }} dias
                        </span>
                    </div>
                    <div class="pt-3 border-t border-border flex gap-2">
                        <form action="{{ route('master.clients.renew', $client) }}" method="POST">
                            @csrf
                            <button type="submit" class="px-3 py-1 bg-green-600 text-white text-sm rounded hover:bg-green-700 transition">
                                Renovar +30 dias
                            </button>
                        </form>
                    </div>
                @else
                    <p class="text-muted-foreground text-center py-4">Nenhuma assinatura ativa</p>
                @endif
            </div>
        </div>

        {{-- Usuários --}}
        <div class="rounded-lg border border-border bg-card">
            <div class="p-4 border-b border-border">
                <h3 class="font-semibold text-foreground">Usuários ({{ count($users ?? []) }})</h3>
            </div>
            <div class="p-4">
                @forelse($users ?? [] as $user)
                    <div class="flex items-center justify-between py-2 {{ !$loop->last ? 'border-b border-border' : '' }}">
                        <div>
                            <p class="text-foreground font-medium">{{ $user->name }}</p>
                            <p class="text-sm text-muted-foreground">{{ $user->email }}</p>
                        </div>
                        <span class="px-2 py-1 text-xs rounded bg-muted text-muted-foreground">{{ $user->role ?? 'user' }}</span>
                    </div>
                @empty
                    <p class="text-muted-foreground text-center py-4">Nenhum usuário cadastrado</p>
                @endforelse
            </div>
        </div>

        {{-- Alterar Plano --}}
        <div class="rounded-lg border border-border bg-card">
            <div class="p-4 border-b border-border">
                <h3 class="font-semibold text-foreground">Alterar Plano</h3>
            </div>
            <div class="p-4">
                <form action="{{ route('master.clients.change-plan', $client) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="plan_id" class="block text-sm font-medium text-foreground mb-1">Novo Plano</label>
                        <select name="plan_id" id="plan_id" required class="w-full px-3 py-2 rounded-md border border-border bg-background text-foreground">
                            @php
                                $plans = \App\Models\Plan::active()->get();
                            @endphp
                            @foreach($plans as $plan)
                                <option value="{{ $plan->id }}" {{ ($client->subscription?->plan_id ?? '') == $plan->id ? 'selected' : '' }}>
                                    {{ $plan->name }} - R$ {{ number_format($plan->price, 2, ',', '.') }}/mês
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="w-full px-4 py-2 bg-primary text-primary-foreground rounded-md hover:bg-primary/90 transition">
                        Alterar Plano
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
