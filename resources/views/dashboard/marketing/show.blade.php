@extends('layouts.admin')

@section('title', $campaign->name)
@section('page_title', $campaign->name)
@section('page_subtitle', $campaign->description ?: 'Detalhes da campanha')

@section('page_actions')
    <div class="flex items-center gap-2">
        @if($campaign->canStart())
            <form method="POST" action="{{ route('dashboard.marketing.start', $campaign) }}" class="inline">
                @csrf
                <button type="submit" 
                        class="inline-flex items-center gap-2 bg-success hover:bg-success/90 text-white px-4 py-2 rounded-lg font-medium transition">
                    <i data-lucide="play" class="h-5 w-5"></i>
                    Iniciar
                </button>
            </form>
        @endif

        @if($campaign->canPause())
            <form method="POST" action="{{ route('dashboard.marketing.pause', $campaign) }}" class="inline">
                @csrf
                <button type="submit" 
                        class="inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg font-medium transition">
                    <i data-lucide="pause" class="h-5 w-5"></i>
                    Pausar
                </button>
            </form>
        @endif

        @if(in_array($campaign->status, ['draft', 'scheduled']))
            <a href="{{ route('dashboard.marketing.edit', $campaign) }}" 
               class="inline-flex items-center gap-2 border border-border hover:bg-muted px-4 py-2 rounded-lg font-medium transition">
                <i data-lucide="edit" class="h-5 w-5"></i>
                Editar
            </a>
        @endif
    </div>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Cards de Estatísticas -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <!-- Status -->
        <div class="bg-card rounded-xl border border-border shadow-sweetspot p-6">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm text-muted-foreground">Status</span>
                <i data-lucide="info" class="h-4 w-4 text-muted-foreground"></i>
            </div>
            <div class="text-2xl font-bold text-foreground mb-1">{{ $campaign->status_label }}</div>
            <span class="inline-block px-2 py-1 rounded-full text-xs font-medium bg-{{ $campaign->status_color }}-100 text-{{ $campaign->status_color }}-700">
                {{ ucfirst($campaign->status) }}
            </span>
        </div>

        <!-- Enviados -->
        <div class="bg-card rounded-xl border border-border shadow-sweetspot p-6">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm text-muted-foreground">Enviados</span>
                <i data-lucide="send" class="h-4 w-4 text-blue-600"></i>
            </div>
            <div class="text-3xl font-bold text-foreground">{{ $campaign->sent_count }}</div>
            <div class="text-xs text-muted-foreground mt-1">
                de {{ $campaign->target_count }} total
            </div>
        </div>

        <!-- Entregues -->
        <div class="bg-card rounded-xl border border-border shadow-sweetspot p-6">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm text-muted-foreground">Entregues</span>
                <i data-lucide="check-circle" class="h-4 w-4 text-success"></i>
            </div>
            <div class="text-3xl font-bold text-success">{{ $campaign->delivered_count }}</div>
            <div class="text-xs text-muted-foreground mt-1">
                {{ $stats['success_rate'] }}% de sucesso
            </div>
        </div>

        <!-- Falhas -->
        <div class="bg-card rounded-xl border border-border shadow-sweetspot p-6">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm text-muted-foreground">Falhas</span>
                <i data-lucide="alert-circle" class="h-4 w-4 text-destructive"></i>
            </div>
            <div class="text-3xl font-bold text-destructive">{{ $campaign->failed_count }}</div>
            <div class="text-xs text-muted-foreground mt-1">
                {{ $campaign->target_count > 0 ? round(($campaign->failed_count / $campaign->target_count) * 100, 1) : 0 }}% do total
            </div>
        </div>
    </div>

    <!-- Progresso -->
    @if($campaign->status === 'running' || $campaign->status === 'completed')
        <div class="bg-card rounded-xl border border-border shadow-sweetspot p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-foreground">Progresso da Campanha</h3>
                <span class="text-2xl font-bold text-primary-600">{{ $stats['progress'] }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-4 overflow-hidden">
                <div class="bg-gradient-to-r from-primary-500 to-primary-600 h-4 transition-all duration-500" 
                     style="width: {{ $stats['progress'] }}%"></div>
            </div>
            <div class="grid grid-cols-4 gap-4 mt-4 text-center text-sm">
                <div>
                    <div class="text-muted-foreground">Pendentes</div>
                    <div class="font-semibold text-foreground">{{ $stats['pending'] }}</div>
                </div>
                <div>
                    <div class="text-muted-foreground">Enviados</div>
                    <div class="font-semibold text-blue-600">{{ $stats['sent'] }}</div>
                </div>
                <div>
                    <div class="text-muted-foreground">Entregues</div>
                    <div class="font-semibold text-success">{{ $stats['delivered'] }}</div>
                </div>
                <div>
                    <div class="text-muted-foreground">Falhas</div>
                    <div class="font-semibold text-destructive">{{ $stats['failed'] }}</div>
                </div>
            </div>
        </div>
    @endif

    <!-- Informações da Campanha -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Template -->
        <div class="bg-card rounded-xl border border-border shadow-sweetspot p-6">
            <h3 class="text-lg font-semibold text-foreground mb-4 flex items-center gap-2">
                <i data-lucide="message-square" class="h-5 w-5 text-primary-600"></i>
                Template da Mensagem
            </h3>
            <div class="bg-muted/50 rounded-lg p-4 font-mono text-sm text-foreground whitespace-pre-wrap">{{ $campaign->message_template_a }}</div>
            
            @if($campaign->use_ab_testing)
                <div class="mt-3 flex items-center gap-2 text-sm text-primary-600">
                    <i data-lucide="sparkles" class="h-4 w-4"></i>
                    <span>Personalização com IA Gemini ativada</span>
                </div>
            @endif
        </div>

        <!-- Detalhes -->
        <div class="bg-card rounded-xl border border-border shadow-sweetspot p-6">
            <h3 class="text-lg font-semibold text-foreground mb-4 flex items-center gap-2">
                <i data-lucide="info" class="h-5 w-5 text-primary-600"></i>
                Detalhes
            </h3>
            <div class="space-y-3 text-sm">
                <div class="flex items-center justify-between py-2 border-b border-border">
                    <span class="text-muted-foreground">Criado em:</span>
                    <span class="font-medium text-foreground">{{ $campaign->created_at->format('d/m/Y H:i') }}</span>
                </div>
                
                @if($campaign->scheduled_at)
                    <div class="flex items-center justify-between py-2 border-b border-border">
                        <span class="text-muted-foreground">Agendado para:</span>
                        <span class="font-medium text-foreground">{{ $campaign->scheduled_at->format('d/m/Y H:i') }}</span>
                    </div>
                @endif

                @if($campaign->started_at)
                    <div class="flex items-center justify-between py-2 border-b border-border">
                        <span class="text-muted-foreground">Iniciado em:</span>
                        <span class="font-medium text-foreground">{{ $campaign->started_at->format('d/m/Y H:i') }}</span>
                    </div>
                @endif

                @if($campaign->completed_at)
                    <div class="flex items-center justify-between py-2 border-b border-border">
                        <span class="text-muted-foreground">Concluído em:</span>
                        <span class="font-medium text-foreground">{{ $campaign->completed_at->format('d/m/Y H:i') }}</span>
                    </div>
                @endif

                <div class="flex items-center justify-between py-2 border-b border-border">
                    <span class="text-muted-foreground">Intervalo entre envios:</span>
                    <span class="font-medium text-foreground">{{ $campaign->interval_seconds }}s</span>
                </div>

                <div class="flex items-center justify-between py-2">
                    <span class="text-muted-foreground">Total de destinatários:</span>
                    <span class="font-medium text-foreground">{{ $campaign->target_count }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Logs Recentes -->
    @if($campaign->logs->isNotEmpty())
        <div class="bg-card rounded-xl border border-border shadow-sweetspot p-6">
            <h3 class="text-lg font-semibold text-foreground mb-4 flex items-center gap-2">
                <i data-lucide="list" class="h-5 w-5 text-primary-600"></i>
                Últimos Envios ({{ $campaign->logs->count() }})
            </h3>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-border">
                            <th class="text-left py-3 px-4 text-sm font-medium text-muted-foreground">Cliente</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-muted-foreground">Telefone</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-muted-foreground">Status</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-muted-foreground">Template</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-muted-foreground">Enviado em</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($campaign->logs as $log)
                            <tr class="border-b border-border hover:bg-muted/50 transition">
                                <td class="py-3 px-4 text-sm text-foreground">{{ $log->customer_name ?: 'N/A' }}</td>
                                <td class="py-3 px-4 text-sm text-muted-foreground">{{ $log->phone }}</td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium 
                                        {{ $log->status === 'sent' ? 'bg-blue-100 text-blue-700' : '' }}
                                        {{ $log->status === 'delivered' ? 'bg-success/20 text-success' : '' }}
                                        {{ $log->status === 'failed' ? 'bg-destructive/20 text-destructive' : '' }}
                                        {{ $log->status === 'pending' ? 'bg-gray-100 text-gray-700' : '' }}">
                                        {{ $log->status_label }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-sm text-muted-foreground">{{ $log->template_version }}</td>
                                <td class="py-3 px-4 text-sm text-muted-foreground">
                                    {{ $log->sent_at ? $log->sent_at->format('d/m H:i') : '-' }}
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
