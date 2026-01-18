@extends('layouts.admin')

@section('title', 'Gerenciar Clientes')
@section('page_title', 'Gerenciar Clientes')
@section('page_subtitle', 'Gerencie os estabelecimentos/clientes do sistema')

@section('page_actions')
    <a href="{{ route('master.clients.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-primary-foreground rounded-md hover:bg-primary/90 transition">
        <i data-lucide="plus" class="h-4 w-4"></i>
        Novo Cliente
    </a>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Filtros --}}
    <div class="rounded-lg border border-border bg-card p-4 shadow-sm">
        <form action="{{ route('master.clients.index') }}" method="GET" class="flex flex-wrap items-center gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Buscar por nome, slug ou email..."
                       class="w-full px-3 py-2 rounded-md border border-border bg-background text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary">
            </div>
            
            <select name="status" class="px-3 py-2 rounded-md border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary">
                <option value="">Todos os status</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Ativos</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inativos</option>
            </select>

            <select name="plan" class="px-3 py-2 rounded-md border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary">
                <option value="">Todos os planos</option>
                @foreach($plans as $plan)
                    <option value="{{ $plan->id }}" {{ request('plan') == $plan->id ? 'selected' : '' }}>
                        {{ $plan->name }}
                    </option>
                @endforeach
            </select>

            <button type="submit" class="px-4 py-2 bg-secondary text-secondary-foreground rounded-md hover:bg-secondary/80 transition">
                <i data-lucide="search" class="h-4 w-4"></i>
            </button>
        </form>
    </div>

    {{-- Tabela de Clientes --}}
    <div class="rounded-lg border border-border bg-card shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-muted/50">
                    <tr>
                        <th class="text-left px-4 py-3 text-sm font-medium text-muted-foreground">Cliente</th>
                        <th class="text-left px-4 py-3 text-sm font-medium text-muted-foreground">Slug</th>
                        <th class="text-left px-4 py-3 text-sm font-medium text-muted-foreground">Plano</th>
                        <th class="text-left px-4 py-3 text-sm font-medium text-muted-foreground">Status</th>
                        <th class="text-left px-4 py-3 text-sm font-medium text-muted-foreground">Vencimento</th>
                        <th class="text-right px-4 py-3 text-sm font-medium text-muted-foreground">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse($clients as $client)
                        <tr class="hover:bg-muted/30 transition">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center">
                                        <span class="text-sm font-bold text-primary">{{ substr($client->name, 0, 2) }}</span>
                                    </div>
                                    <div>
                                        <p class="font-medium text-foreground">{{ $client->name }}</p>
                                        <p class="text-xs text-muted-foreground">{{ $client->email ?? 'Sem email' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <code class="text-sm bg-muted px-2 py-1 rounded">{{ $client->slug }}</code>
                            </td>
                            <td class="px-4 py-3">
                                @if($client->subscription?->plan)
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-primary/10 text-primary">
                                        {{ $client->subscription->plan->name }}
                                    </span>
                                @else
                                    <span class="text-muted-foreground text-sm">Sem plano</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $client->active ? 'bg-success/10 text-success' : 'bg-destructive/10 text-destructive' }}">
                                    {{ $client->active ? 'Ativo' : 'Inativo' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                @if($client->subscription?->current_period_end)
                                    <p class="text-sm text-foreground">{{ $client->subscription->current_period_end->format('d/m/Y') }}</p>
                                    @if($client->subscription->days_until_expiration !== null && $client->subscription->days_until_expiration <= 7)
                                        <p class="text-xs text-warning">{{ $client->subscription->days_until_expiration }} dias</p>
                                    @endif
                                @else
                                    <span class="text-muted-foreground text-sm">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('master.clients.show', $client) }}" 
                                       class="p-2 rounded-md hover:bg-muted transition" title="Ver detalhes">
                                        <i data-lucide="eye" class="h-4 w-4 text-muted-foreground"></i>
                                    </a>
                                    <a href="{{ route('master.clients.edit', $client) }}" 
                                       class="p-2 rounded-md hover:bg-muted transition" title="Editar">
                                        <i data-lucide="edit" class="h-4 w-4 text-muted-foreground"></i>
                                    </a>
                                    <form action="{{ route('master.clients.toggle-status', $client) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="p-2 rounded-md hover:bg-muted transition" 
                                                title="{{ $client->active ? 'Desativar' : 'Ativar' }}">
                                            <i data-lucide="{{ $client->active ? 'pause' : 'play' }}" 
                                               class="h-4 w-4 {{ $client->active ? 'text-warning' : 'text-success' }}"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-muted-foreground">
                                Nenhum cliente encontrado
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginação --}}
        @if($clients->hasPages())
            <div class="px-4 py-3 border-t border-border">
                {{ $clients->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
