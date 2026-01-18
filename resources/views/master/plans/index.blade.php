@extends('layouts.admin')

@section('title', 'Gerenciar Planos')
@section('page_title', 'Gerenciar Planos')
@section('page_subtitle', 'Configure os planos e preços disponíveis para os clientes')

@section('page_actions')
    <a href="{{ route('master.plans.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-primary-foreground rounded-md hover:bg-primary/90 transition">
        <i data-lucide="plus" class="h-4 w-4"></i>
        Novo Plano
    </a>
@endsection

@section('content')
<div class="space-y-6">
    @if(session('success'))
        <div class="rounded-lg border border-success/30 bg-success/10 p-4 text-success">
            {{ session('success') }}
        </div>
    @endif

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="rounded-lg border border-border bg-card p-4 shadow-sm">
            <p class="text-sm text-muted-foreground">Total de Planos</p>
            <p class="text-2xl font-bold text-foreground">{{ $plans->count() }}</p>
        </div>
        <div class="rounded-lg border border-border bg-card p-4 shadow-sm">
            <p class="text-sm text-muted-foreground">Planos Ativos</p>
            <p class="text-2xl font-bold text-success">{{ $plans->where('active', true)->count() }}</p>
        </div>
        <div class="rounded-lg border border-border bg-card p-4 shadow-sm">
            <p class="text-sm text-muted-foreground">Com WhatsApp</p>
            <p class="text-2xl font-bold text-foreground">{{ $plans->where('has_whatsapp', true)->count() }}</p>
        </div>
        <div class="rounded-lg border border-border bg-card p-4 shadow-sm">
            <p class="text-sm text-muted-foreground">Com I.A.</p>
            <p class="text-2xl font-bold text-foreground">{{ $plans->where('has_ai', true)->count() }}</p>
        </div>
    </div>

    {{-- Lista de Planos --}}
    <div class="rounded-lg border border-border bg-card shadow-sm">
        <div class="p-6 border-b border-border">
            <h3 class="text-lg font-semibold text-foreground">Planos Cadastrados</h3>
            <p class="text-sm text-muted-foreground">Arraste para reordenar a exibição</p>
        </div>
        
        <div class="divide-y divide-border" id="plans-list">
            @forelse($plans as $plan)
                <div class="p-4 hover:bg-muted/30 transition flex items-center gap-4" data-plan-id="{{ $plan->id }}">
                    <div class="cursor-move text-muted-foreground hover:text-foreground">
                        <i data-lucide="grip-vertical" class="h-5 w-5"></i>
                    </div>
                    
                    <div class="flex-1">
                        <div class="flex items-center gap-3">
                            <h4 class="font-semibold text-foreground">{{ $plan->name }}</h4>
                            @if($plan->is_featured)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-primary/10 text-primary">
                                    <i data-lucide="star" class="h-3 w-3 mr-1"></i> Destaque
                                </span>
                            @endif
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $plan->active ? 'bg-success/10 text-success' : 'bg-muted text-muted-foreground' }}">
                                {{ $plan->active ? 'Ativo' : 'Inativo' }}
                            </span>
                        </div>
                        <p class="text-sm text-muted-foreground mt-1">{{ $plan->description }}</p>
                        <div class="flex items-center gap-4 mt-2">
                            @if($plan->has_whatsapp)
                                <span class="inline-flex items-center gap-1 text-xs text-success">
                                    <i data-lucide="message-circle" class="h-3 w-3"></i> WhatsApp
                                </span>
                            @endif
                            @if($plan->has_ai)
                                <span class="inline-flex items-center gap-1 text-xs text-primary">
                                    <i data-lucide="bot" class="h-3 w-3"></i> I.A.
                                </span>
                            @endif
                            @if($plan->max_products)
                                <span class="text-xs text-muted-foreground">Até {{ $plan->max_products }} produtos</span>
                            @endif
                            @if($plan->max_whatsapp_instances)
                                <span class="text-xs text-muted-foreground">{{ $plan->max_whatsapp_instances }} instância(s) WhatsApp</span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="text-right">
                        <p class="text-xl font-bold text-foreground">{{ $plan->formatted_price }}</p>
                        <p class="text-xs text-muted-foreground">/mês</p>
                        <p class="text-xs text-muted-foreground mt-1">{{ $plan->subscriptions_count ?? 0 }} assinantes</p>
                    </div>
                    
                    <div class="flex items-center gap-2">
                        <form action="{{ route('master.plans.toggle-featured', $plan) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="p-2 rounded-md hover:bg-muted transition" 
                                    title="{{ $plan->is_featured ? 'Remover destaque' : 'Destacar' }}">
                                <i data-lucide="star" class="h-4 w-4 {{ $plan->is_featured ? 'text-primary fill-primary' : 'text-muted-foreground' }}"></i>
                            </button>
                        </form>
                        
                        <form action="{{ route('master.plans.toggle-status', $plan) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="p-2 rounded-md hover:bg-muted transition" 
                                    title="{{ $plan->active ? 'Desativar' : 'Ativar' }}">
                                <i data-lucide="{{ $plan->active ? 'pause' : 'play' }}" 
                                   class="h-4 w-4 {{ $plan->active ? 'text-warning' : 'text-success' }}"></i>
                            </button>
                        </form>
                        
                        <a href="{{ route('master.plans.edit', $plan) }}" 
                           class="p-2 rounded-md hover:bg-muted transition" title="Editar">
                            <i data-lucide="edit" class="h-4 w-4 text-muted-foreground"></i>
                        </a>
                        
                        @if(($plan->subscriptions_count ?? 0) == 0)
                            <form action="{{ route('master.plans.destroy', $plan) }}" method="POST" class="inline" 
                                  onsubmit="return confirm('Tem certeza que deseja excluir este plano?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 rounded-md hover:bg-destructive/10 transition" title="Excluir">
                                    <i data-lucide="trash-2" class="h-4 w-4 text-destructive"></i>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-muted-foreground">
                    <i data-lucide="crown" class="h-12 w-12 mx-auto mb-3 opacity-50"></i>
                    <p>Nenhum plano cadastrado</p>
                    <a href="{{ route('master.plans.create') }}" class="text-primary hover:underline mt-2 inline-block">
                        Criar primeiro plano
                    </a>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
