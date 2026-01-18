@extends('layouts.admin')

@section('title', 'Campanhas de Marketing')
@section('page_title', 'Campanhas de Marketing')
@section('page_subtitle', 'Gerencie suas campanhas de WhatsApp com IA')

@section('page_actions')
    <a href="{{ route('dashboard.marketing.create') }}" 
       class="inline-flex items-center gap-2 bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-lg font-medium transition-all duration-200 hover:shadow-lg">
        <i data-lucide="plus" class="h-5 w-5"></i>
        Nova Campanha
    </a>
@endsection

@section('content')
<div class="space-y-6">
    
    @if(session('success'))
        <div class="alert-success rounded-lg border border-success/30 bg-success/10 px-4 py-3 text-success shadow-sm flex items-center gap-2">
            <i data-lucide="check-circle" class="h-5 w-5"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="alert-error rounded-lg border border-destructive/30 bg-destructive/10 px-4 py-3 text-destructive shadow-sm flex items-center gap-2">
            <i data-lucide="alert-circle" class="h-5 w-5"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if($campaigns->isEmpty())
        <!-- Estado Vazio -->
        <div class="bg-card rounded-xl border border-border shadow-sweetspot p-12 text-center">
            <div class="mx-auto w-24 h-24 bg-primary-50 rounded-full flex items-center justify-center mb-6">
                <i data-lucide="megaphone" class="h-12 w-12 text-primary-600"></i>
            </div>
            <h3 class="text-xl font-semibold text-foreground mb-2">Nenhuma campanha criada ainda</h3>
            <p class="text-muted-foreground mb-6 max-w-md mx-auto">
                Crie sua primeira campanha de marketing para enviar mensagens personalizadas no WhatsApp com IA.
            </p>
            <a href="{{ route('dashboard.marketing.create') }}" 
               class="inline-flex items-center gap-2 bg-primary-600 hover:bg-primary-700 text-white px-6 py-3 rounded-lg font-medium transition-all duration-200 hover:shadow-lg">
                <i data-lucide="plus" class="h-5 w-5"></i>
                Criar Primeira Campanha
            </a>
        </div>
    @else
        <!-- Grid de Campanhas -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($campaigns as $campaign)
                <div class="bg-card rounded-xl border border-border shadow-sweetspot hover:shadow-lg transition-all duration-200 overflow-hidden">
                    <!-- Header -->
                    <div class="p-6 bg-gradient-to-br from-primary-50 to-primary-100 border-b border-primary-200">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex-1">
                                <h3 class="font-semibold text-foreground text-lg mb-1">{{ $campaign->name }}</h3>
                                @if($campaign->description)
                                    <p class="text-sm text-muted-foreground">{{ Str::limit($campaign->description, 60) }}</p>
                                @endif
                            </div>
                            <span class="px-3 py-1 rounded-full text-xs font-medium bg-{{ $campaign->status_color }}-100 text-{{ $campaign->status_color }}-700">
                                {{ $campaign->status_label }}
                            </span>
                        </div>

                        <!-- Estatísticas Rápidas -->
                        <div class="grid grid-cols-3 gap-2 text-center">
                            <div class="bg-white/50 rounded-lg p-2">
                                <div class="text-xs text-muted-foreground">Enviados</div>
                                <div class="text-lg font-bold text-foreground">{{ $campaign->sent_count }}</div>
                            </div>
                            <div class="bg-white/50 rounded-lg p-2">
                                <div class="text-xs text-muted-foreground">Entregues</div>
                                <div class="text-lg font-bold text-success">{{ $campaign->delivered_count }}</div>
                            </div>
                            <div class="bg-white/50 rounded-lg p-2">
                                <div class="text-xs text-muted-foreground">Falhas</div>
                                <div class="text-lg font-bold text-destructive">{{ $campaign->failed_count }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Corpo -->
                    <div class="p-6 space-y-4">
                        <!-- Informações -->
                        <div class="space-y-2 text-sm">
                            <div class="flex items-center gap-2 text-muted-foreground">
                                <i data-lucide="users" class="h-4 w-4"></i>
                                <span>{{ $campaign->target_count }} destinatários</span>
                            </div>
                            
                            @if($campaign->scheduled_at)
                                <div class="flex items-center gap-2 text-muted-foreground">
                                    <i data-lucide="calendar" class="h-4 w-4"></i>
                                    <span>Agendado: {{ $campaign->scheduled_at->format('d/m/Y H:i') }}</span>
                                </div>
                            @endif

                            @if($campaign->started_at)
                                <div class="flex items-center gap-2 text-muted-foreground">
                                    <i data-lucide="clock" class="h-4 w-4"></i>
                                    <span>Iniciado: {{ $campaign->started_at->format('d/m/Y H:i') }}</span>
                                </div>
                            @endif

                            @if($campaign->use_ab_testing)
                                <div class="flex items-center gap-2 text-primary-600">
                                    <i data-lucide="sparkles" class="h-4 w-4"></i>
                                    <span>IA Personalizada (Gemini)</span>
                                </div>
                            @endif
                        </div>

                        <!-- Progresso -->
                        @if($campaign->status === 'running' || $campaign->status === 'completed')
                            <div>
                                <div class="flex items-center justify-between text-xs mb-1">
                                    <span class="text-muted-foreground">Progresso</span>
                                    <span class="font-medium text-foreground">{{ $campaign->getProgressPercentage() }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                                    <div class="bg-gradient-to-r from-primary-500 to-primary-600 h-2 transition-all duration-300" 
                                         style="width: {{ $campaign->getProgressPercentage() }}%"></div>
                                </div>
                            </div>
                        @endif

                        <!-- Ações -->
                        <div class="flex items-center gap-2 pt-4 border-t">
                            <a href="{{ route('dashboard.marketing.show', $campaign) }}" 
                               class="flex-1 text-center px-3 py-2 border border-border hover:bg-muted rounded-lg text-sm font-medium transition">
                                Ver Detalhes
                            </a>

                            @if($campaign->canStart())
                                <form method="POST" action="{{ route('dashboard.marketing.start', $campaign) }}" class="flex-1">
                                    @csrf
                                    <button type="submit" 
                                            class="w-full px-3 py-2 bg-success hover:bg-success/90 text-white rounded-lg text-sm font-medium transition">
                                        <i data-lucide="play" class="h-4 w-4 inline"></i>
                                        Iniciar
                                    </button>
                                </form>
                            @endif

                            @if($campaign->canPause())
                                <form method="POST" action="{{ route('dashboard.marketing.pause', $campaign) }}" class="flex-1">
                                    @csrf
                                    <button type="submit" 
                                            class="w-full px-3 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-lg text-sm font-medium transition">
                                        <i data-lucide="pause" class="h-4 w-4 inline"></i>
                                        Pausar
                                    </button>
                                </form>
                            @endif

                            @if($campaign->canCancel())
                                <form method="POST" action="{{ route('dashboard.marketing.cancel', $campaign) }}" 
                                      onsubmit="return confirm('Tem certeza que deseja cancelar esta campanha?')"
                                      class="flex-1">
                                    @csrf
                                    <button type="submit" 
                                            class="w-full px-3 py-2 bg-destructive hover:bg-destructive/90 text-white rounded-lg text-sm font-medium transition">
                                        <i data-lucide="x" class="h-4 w-4 inline"></i>
                                        Cancelar
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Paginação -->
        @if($campaigns->hasPages())
            <div class="mt-6">
                {{ $campaigns->links() }}
            </div>
        @endif
    @endif

</div>
@endsection
