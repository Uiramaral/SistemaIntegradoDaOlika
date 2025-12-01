@extends('dashboard.layouts.app')

@section('page_title', 'Integração WhatsApp')
@section('page_subtitle', 'Configure mensagens automáticas via WhatsApp')

@section('content')
<div class="space-y-6">

    @if(session('success'))
        <div class="rounded-lg border bg-green-50 text-green-900 px-4 py-3">{{ session('success') }}</div>
    @endif

    @if(session('ok'))
        <div class="rounded-lg border bg-green-50 text-green-900 px-4 py-3">{{ session('ok') }}</div>
    @endif

    @if($errors->any())
        <div class="rounded-lg border bg-red-50 text-red-900 px-4 py-3">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-4 gap-3">
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="p-4">
                <div class="flex items-center justify-between">
                    <div class="flex-1 min-w-0">
                        <p class="text-xs text-muted-foreground mb-1">Templates Ativos</p>
                        <p class="text-xl font-bold">{{ $stats['active_templates'] ?? 0 }}</p>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-text h-5 w-5 text-primary flex-shrink-0 ml-2">
                        <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" x2="8" y1="13" y2="13"></line>
                        <line x1="16" x2="8" y1="17" y2="17"></line>
                        <polyline points="10 9 9 9 8 9"></polyline>
                    </svg>
                </div>
            </div>
        </div>
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="p-4">
                <div class="flex items-center justify-between">
                    <div class="flex-1 min-w-0">
                        <p class="text-xs text-muted-foreground mb-1">Total de Templates</p>
                        <p class="text-xl font-bold">{{ $stats['total_templates'] ?? 0 }}</p>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-folder h-5 w-5 text-primary flex-shrink-0 ml-2">
                        <path d="M4 20h16a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.93a2 2 0 0 1-1.66-.9l-.82-1.2A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13c0 1.1.9 2 2 2Z"></path>
                    </svg>
                </div>
            </div>
        </div>
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="p-4">
                <div class="flex items-center justify-between">
                    <div class="flex-1 min-w-0">
                        <p class="text-xs text-muted-foreground mb-1">Status Configurados</p>
                        <p class="text-xl font-bold">{{ $stats['total_statuses'] ?? 0 }}</p>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-list-checks h-5 w-5 text-primary flex-shrink-0 ml-2">
                        <path d="m3 17 2 2 4-4"></path>
                        <path d="M3 7l2 2 4-4"></path>
                        <path d="M13 6h8"></path>
                        <path d="M13 12h8"></path>
                        <path d="M13 18h8"></path>
                    </svg>
                </div>
            </div>
        </div>
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="p-4">
                <div class="flex items-center justify-between">
                    <div class="flex-1 min-w-0">
                        <p class="text-xs text-muted-foreground mb-1">Notificações Ativas</p>
                        <p class="text-xl font-bold">{{ $stats['statuses_with_notifications'] ?? 0 }}</p>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-bell h-5 w-5 text-primary flex-shrink-0 ml-2">
                        <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"></path>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <div dir="ltr" data-orientation="horizontal" class="space-y-4">
        <div role="tablist" aria-orientation="horizontal" class="h-10 items-center justify-center rounded-md bg-muted p-1 text-muted-foreground grid w-full grid-cols-4">
            <button type="button" role="tab" data-tab="settings" class="tab-button inline-flex items-center justify-center whitespace-nowrap rounded-sm px-3 py-1.5 text-sm font-medium ring-offset-background transition-all data-[state=active]:bg-background data-[state=active]:text-foreground data-[state=active]:shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 active">Configurações</button>
            <button type="button" role="tab" data-tab="campaigns" class="tab-button inline-flex items-center justify-center whitespace-nowrap rounded-sm px-3 py-1.5 text-sm font-medium ring-offset-background transition-all data-[state=active]:bg-background data-[state=active]:text-foreground data-[state=active]:shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50">Campanhas</button>
            <button type="button" role="tab" data-tab="templates" class="tab-button inline-flex items-center justify-center whitespace-nowrap rounded-sm px-3 py-1.5 text-sm font-medium ring-offset-background transition-all data-[state=active]:bg-background data-[state=active]:text-foreground data-[state=active]:shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50">Templates</button>
            <button type="button" role="tab" data-tab="notifications" class="tab-button inline-flex items-center justify-center whitespace-nowrap rounded-sm px-3 py-1.5 text-sm font-medium ring-offset-background transition-all data-[state=active]:bg-background data-[state=active]:text-foreground data-[state=active]:shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50">Notificações</button>
        </div>

        <div data-tab-content="settings" class="tab-content mt-2 ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
            <!-- Seção de Instâncias WhatsApp -->
            <div class="rounded-lg border bg-card text-card-foreground shadow-sm mb-6">
                <div class="flex flex-col space-y-1.5 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-2xl font-semibold leading-none tracking-tight">Instâncias WhatsApp</h3>
                            <p class="text-sm text-muted-foreground">Gerencie múltiplas conexões WhatsApp</p>
                        </div>
                        <button onclick="showAddInstanceModal()" class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-plus">
                                <path d="M5 12h14"></path>
                                <path d="M12 5v14"></path>
                            </svg>
                            Nova Instância
                        </button>
                    </div>
                </div>
                <div class="p-6 pt-0 space-y-4">
                    @forelse($instances as $instance)
                        <div class="instance-card border rounded-lg p-4 hover:bg-muted/50 transition-colors" data-instance-id="{{ $instance->id }}">
                            <div class="flex items-center justify-between gap-4">
                                <!-- Informações da Instância -->
                                <div class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <p class="text-xs text-muted-foreground mb-1">Nome</p>
                                        <p class="font-semibold">{{ $instance->name }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-muted-foreground mb-1">Telefone</p>
                                        <p class="font-mono text-sm">{{ $instance->phone_number ?? 'Não configurado' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-muted-foreground mb-1">URL da API</p>
                                        <p class="text-sm truncate" title="{{ $instance->api_url }}">{{ $instance->api_url }}</p>
                                    </div>
                                </div>
                                
                                <!-- Status e Ações -->
                                <div class="flex items-center gap-3">
                                    <!-- Status Badge -->
                                    <div class="status-badge">
                                        @if($instance->status === 'CONNECTED')
                                            <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold bg-green-100 text-green-800 border-green-300">
                                                <span class="w-2 h-2 bg-green-600 rounded-full mr-2"></span>
                                                Conectado
                                            </span>
                                        @elseif($instance->last_error_message)
                                            <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold bg-red-100 text-red-800 border-red-300" title="{{ $instance->last_error_message }}">
                                                <span class="w-2 h-2 bg-red-600 rounded-full mr-2"></span>
                                                Erro Fatal
                                            </span>
                                        @elseif($instance->status === 'CONNECTING')
                                            <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold bg-amber-100 text-amber-800 border-amber-300">
                                                <span class="w-2 h-2 bg-amber-600 rounded-full mr-2 animate-pulse"></span>
                                                Conectando
                                            </span>
                                        @else
                                            <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold bg-gray-100 text-gray-600 border-gray-300">
                                                <span class="w-2 h-2 bg-gray-400 rounded-full mr-2"></span>
                                                Desconectado
                                            </span>
                                        @endif
                                    </div>
                                    
                                    <!-- Botões de Ação -->
                                    <div class="flex items-center gap-2">
                                        @if($instance->last_error_message)
                                            <button onclick="alert('Erro: {{ addslashes($instance->last_error_message) }}')" class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors border border-red-300 bg-red-50 text-red-700 hover:bg-red-100 h-9 w-9" title="Ver erro">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-alert-circle">
                                                    <circle cx="12" cy="12" r="10"></circle>
                                                    <line x1="12" x2="12" y1="8" y2="12"></line>
                                                    <line x1="12" x2="12.01" y1="16" y2="16"></line>
                                                </svg>
                                            </button>
                                        @endif

                                        <button onclick="openEditInstanceModal({{ $instance->id }})" class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 w-9">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-pencil">
                                                <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"></path>
                                                <path d="m15 5 4 4"></path>
                                            </svg>
                                        </button>

                                        @if($instance->status === 'CONNECTED')
                                            <button onclick="disconnectInstance({{ $instance->id }})" class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-red-300 bg-red-50 text-red-700 hover:bg-red-100 h-9 px-3">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-log-out">
                                                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                                    <polyline points="16 17 21 12 16 7"></polyline>
                                                    <line x1="21" x2="9" y1="12" y2="12"></line>
                                                </svg>
                                                Desconectar
                                            </button>
                                        @else
                                            <button onclick="connectInstance({{ $instance->id }})" class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-9 px-3">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-plug">
                                                    <path d="M12 22v-5"></path>
                                                    <path d="M9 8V2"></path>
                                                    <path d="M15 8V2"></path>
                                                    <path d="M18 8v5a4 4 0 0 1-4 4h-4a4 4 0 0 1-4-4V8Z"></path>
                                                </svg>
                                                Conectar
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-lg border border-dashed p-8 text-center text-muted-foreground">
                            <p>Nenhuma instância cadastrada ainda.</p>
                            <button onclick="showAddInstanceModal()" class="text-primary hover:underline mt-2 inline-block">Criar primeira instância</button>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Aba Campanhas -->
        <div data-tab-content="campaigns" class="tab-content mt-2 ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 hidden">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Formulário -->
                <div class="lg:col-span-1">
                    <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                        <div class="flex flex-col space-y-1.5 p-6">
                            <h3 class="text-xl font-semibold leading-none tracking-tight">Nova Campanha</h3>
                            <p class="text-sm text-muted-foreground">Envie mensagens em massa com rotação de números</p>
                        </div>
                        <div class="p-6 pt-0">
                            <form id="create-campaign-form" onsubmit="createCampaign(event)" class="space-y-4">
                                <div>
                                    <label class="text-sm font-medium mb-1 block">Nome da Campanha</label>
                                    <input type="text" name="name" required class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm" placeholder="Promoção Pizza Sexta">
                                </div>
                                <div>
                                    <label class="text-sm font-medium mb-1 block">Mensagem</label>
                                    <textarea name="message" rows="5" required class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm" placeholder="Olá {nome}, hoje tem promoção!"></textarea>
                                    <p class="text-xs text-muted-foreground mt-1">Variáveis: {nome}, {telefone}</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium mb-1 block">Público Alvo</label>
                                    <select name="target_audience" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
                                        <option value="all">Todos os Clientes</option>
                                        <option value="has_orders">Clientes que já compraram</option>
                                        <option value="no_orders">Leads (nunca compraram)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="text-sm font-medium mb-1 block">Intervalo (segundos)</label>
                                    <input type="number" name="interval_seconds" value="15" min="5" required class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
                                    <p class="text-xs text-muted-foreground mt-1">Tempo entre cada envio para evitar bloqueio.</p>
                                </div>
                                <button type="submit" class="w-full inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2">
                                    Iniciar Campanha
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Lista de Campanhas -->
                <div class="lg:col-span-2">
                    <div class="rounded-lg border bg-card text-card-foreground shadow-sm h-full">
                        <div class="flex flex-col space-y-1.5 p-6">
                            <h3 class="text-xl font-semibold leading-none tracking-tight">Histórico de Campanhas</h3>
                        </div>
                        <div class="p-6 pt-0">
                            <div class="relative w-full overflow-auto">
                                <table class="w-full caption-bottom text-sm">
                                    <thead class="[&_tr]:border-b">
                                        <tr class="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted">
                                            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Nome</th>
                                            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Status</th>
                                            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Progresso</th>
                                            <th class="h-12 px-4 text-right align-middle font-medium text-muted-foreground">Data</th>
                                        </tr>
                                    </thead>
                                    <tbody id="campaigns-list-body" class="[&_tr:last-child]:border-0">
                                        <tr>
                                            <td colspan="4" class="p-4 text-center text-muted-foreground">Carregando campanhas...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div data-tab-content="templates" class="tab-content mt-2 ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 hidden">
            <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                <div class="flex flex-col space-y-1.5 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-2xl font-semibold leading-none tracking-tight">Templates de Mensagem</h3>
                            <p class="text-sm text-muted-foreground">Gerencie templates utilizados nos status dos pedidos</p>
                        </div>
                        <a href="{{ route('dashboard.settings.status-templates') }}" class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4">Gerenciar Templates</a>
                    </div>
                </div>
                <div class="p-6 pt-0">
                    <div class="space-y-3">
                        @forelse($templates as $template)
                            <div class="rounded-lg border p-4 hover:bg-muted/50">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="font-semibold">{{ $template->slug }}</span>
                                            @if($template->active)
                                                <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-xs font-semibold bg-success text-success-foreground">Ativo</span>
                                            @else
                                                <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-xs font-semibold bg-muted text-muted-foreground">Inativo</span>
                                            @endif
                                        </div>
                                        <p class="text-sm text-muted-foreground whitespace-pre-wrap">{{ Str::limit($template->content, 150) }}</p>
                                        <p class="text-xs text-muted-foreground mt-2">Usado por: 
                                            @php
                                                $usedBy = $statuses->where('whatsapp_template_id', $template->id)->pluck('name')->join(', ') ?: 'Nenhum status';
                                            @endphp
                                            {{ $usedBy }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-lg border p-8 text-center text-muted-foreground">
                                <p>Nenhum template cadastrado ainda.</p>
                                <a href="{{ route('dashboard.settings.status-templates') }}" class="text-primary hover:underline mt-2 inline-block">Criar primeiro template</a>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div data-tab-content="notifications" class="tab-content mt-2 ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 hidden">
            <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                <div class="flex flex-col space-y-1.5 p-6">
                    <h3 class="text-2xl font-semibold leading-none tracking-tight">Notificações Automáticas</h3>
                    <p class="text-sm text-muted-foreground">Configure quais status devem enviar notificações automáticas</p>
                </div>
                <form action="{{ route('dashboard.settings.whatsapp.notifications.save') }}" method="POST" class="p-6 pt-0 space-y-4">
                    @csrf
                    @forelse($statuses as $status)
                        <div class="rounded-lg border p-4 space-y-3">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-semibold">{{ $status->name }}</p>
                                    <p class="text-sm text-muted-foreground">Código: {{ $status->code }}</p>
                                    @if($status->template_slug)
                                        <p class="text-xs text-muted-foreground mt-1">Template: <span class="font-medium">{{ $status->template_slug }}</span></p>
                                    @else
                                        <p class="text-xs text-amber-600 mt-1">⚠️ Nenhum template associado</p>
                                    @endif
                                </div>
                            </div>
                            <div class="grid md:grid-cols-2 gap-4">
                                <label class="flex items-center justify-between cursor-pointer">
                                    <div>
                                        <span class="font-medium text-sm">Notificar Cliente</span>
                                        <p class="text-xs text-muted-foreground">Enviar mensagem para o cliente quando o pedido mudar para este status</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="notifications[{{ $status->id }}][customer]" value="1" {{ $status->notify_customer ? 'checked' : '' }} class="sr-only peer">
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                    </label>
                                </label>
                                <label class="flex items-center justify-between cursor-pointer">
                                    <div>
                                        <span class="font-medium text-sm">Notificar Admin</span>
                                        <p class="text-xs text-muted-foreground">Enviar mensagem para o administrador</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="notifications[{{ $status->id }}][admin]" value="1" {{ $status->notify_admin ? 'checked' : '' }} class="sr-only peer">
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                    </label>
                                </label>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-lg border p-8 text-center text-muted-foreground">
                            <p>Nenhum status cadastrado ainda.</p>
                            <a href="{{ route('dashboard.settings.status-templates') }}" class="text-primary hover:underline mt-2 inline-block">Gerenciar status</a>
                        </div>
                    @endforelse
                    
                    @if($statuses->count() > 0)
                        <button type="submit" class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2 w-full">Salvar Configurações de Notificações</button>
                    @endif
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Código de Pareamento -->
<div id="pairing-code-modal" class="fixed inset-0 z-50 hidden items-center justify-center" style="background-color: rgba(0, 0, 0, 0.75);">
    <div class="bg-white rounded-lg shadow-2xl max-w-md w-full mx-4 p-6 relative">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-semibold" id="pairing-modal-title">Código de Pareamento</h3>
            <button onclick="closePairingCodeModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x">
                    <path d="M18 6L6 18"></path>
                    <path d="M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div id="pairing-code-content" class="space-y-4">
            <div class="flex items-center justify-center p-8">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
                <span class="ml-3 text-muted-foreground">Carregando código...</span>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Editar Instância -->
<div id="edit-instance-modal" class="fixed inset-0 z-50 hidden items-center justify-center" style="background-color: rgba(0, 0, 0, 0.75);">
    <div class="bg-white rounded-lg shadow-2xl w-full mx-4 p-5 max-h-[90vh] overflow-y-auto" style="max-width: 28rem !important; width: calc(100% - 2rem) !important;">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-semibold">Editar Instância WhatsApp</h3>
            <button onclick="closeEditInstanceModal()" class="text-gray-400 hover:text-gray-600">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x">
                    <path d="M18 6L6 18"></path>
                    <path d="M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <form id="edit-instance-form" onsubmit="updateInstance(event)" class="space-y-4">
            @csrf
            @method('PUT')
            <input type="hidden" name="id" id="edit-instance-id">
            <div>
                <label class="text-sm font-medium mb-1 block">Nome da Instância</label>
                <input type="text" name="name" id="edit-instance-name" required class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm" placeholder="Ex: Principal (Vendas)">
            </div>
            <div>
                <label class="text-sm font-medium mb-1 block">URL da API</label>
                <input type="url" name="api_url" id="edit-instance-api-url" required class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm" placeholder="https://whatsapp-01.up.railway.app">
            </div>
            <div>
                <label class="text-sm font-medium mb-1 block">Token da API (opcional)</label>
                <input type="text" name="api_token" id="edit-instance-api-token" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm" placeholder="Token de segurança">
            </div>
            <div>
                <label class="text-sm font-medium mb-1 block">Número do WhatsApp</label>
                <input type="text" name="phone_number" id="edit-instance-phone" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm" placeholder="5571999999999">
                <p class="text-xs text-muted-foreground mt-1">Será preenchido automaticamente após pareamento</p>
            </div>
            <div class="flex gap-3 pt-4">
                <button type="button" onclick="closeEditInstanceModal()" class="flex-1 inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors border border-input bg-background hover:bg-accent h-10 px-4">
                    Cancelar
                </button>
                <button type="submit" class="flex-1 inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4">
                    Atualizar
                </button>
            </div>
        </form>
    </div>
</div>

@push('styles')
<style>
.tab-button.active {
    background-color: hsl(var(--background));
    color: hsl(var(--foreground));
    box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
}
/* Modais devem estar ocultos por padrão */
#pairing-code-modal, #add-instance-modal, #edit-instance-modal {
    display: none;
    backdrop-filter: blur(4px);
}
/* Modais visíveis quando não têm classe hidden */
#pairing-code-modal:not(.hidden), #add-instance-modal:not(.hidden), #edit-instance-modal:not(.hidden) {
    display: flex;
    background-color: rgba(0, 0, 0, 0.75) !important;
    backdrop-filter: blur(4px);
}
/* Garantir que o conteúdo do modal tenha z-index maior */
#pairing-code-modal > div, #add-instance-modal > div, #edit-instance-modal > div {
    position: relative;
    z-index: 51;
    background-color: white;
    border-radius: 0.5rem;
}
</style>
@endpush

@push('scripts')
<script>
// Variáveis globais
let instanceStatusIntervals = {};

document.addEventListener('DOMContentLoaded', async function() {
    const tabs = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const targetTab = this.getAttribute('data-tab');
            
            // Remove active state from all tabs
            tabs.forEach(t => {
                t.classList.remove('active');
            });
            
            // Hide all tab contents
            tabContents.forEach(content => {
                content.classList.add('hidden');
            });
            
            // Show selected tab content
            const selectedContent = document.querySelector(`[data-tab-content="${targetTab}"]`);
            if (selectedContent) {
                selectedContent.classList.remove('hidden');
            }
            
            // Add active state to clicked tab
            this.classList.add('active');
        });
    });
    
    // Gerenciamento de conexão WhatsApp
    const connectionStatusDiv = document.getElementById('whatsapp-connection-status');
    let statusCheckInterval = null;
    let lastStatusUpdate = null; // Armazenar timestamp da última atualização para o expirômetro
    
    async function fetchWhatsAppStatus() {
        try {
            // Tenta primeiro a rota /whatsapp/status, depois fallback para /settings/whatsapp/status
            let url = '/dashboard/whatsapp/status';
            let response = await fetch(url);
            if (!response.ok) {
                url = '{{ route("dashboard.settings.whatsapp.status") }}';
                response = await fetch(url);
            }
            const status = await response.json();
            return status;
        } catch (error) {
            console.error('Erro ao buscar status:', error);
            return { connected: false, error: 'Erro ao conectar com o servidor' };
        }
    }
    
    // Removido: não vamos mais buscar QR Code, apenas status
    
    function renderConnectionStatus(status) {
        if (!status) {
            console.error('❌ Status é null ou undefined');
            status = {
                connected: false,
                pairingCode: null,
                user: null,
                last_updated: new Date().toISOString()
            };
        }
        
        const isConnected = status.connected || false;
        const pairingCode = status.pairingCode || null;
        const user = status.user;
        const lastUpdated = status.last_updated ? new Date(status.last_updated) : new Date();
        const hasError = status.error || false;
        
        console.log('🎨 Renderizando status:', { isConnected, hasPairingCode: !!pairingCode, hasError, user });
        
        // Armazenar timestamp globalmente para o expirômetro
        if (pairingCode) {
            lastStatusUpdate = lastUpdated;
        } else {
            lastStatusUpdate = null;
        }
        
        console.log('🎨 Renderizando status:', { isConnected, hasPairingCode: !!pairingCode, user });
        
        let html = '';
        
        // 1️⃣ Se conectado
        if (isConnected) {
            const userName = user?.name || 'WhatsApp Business';
            const userId = user?.id ? user.id.replace('@s.whatsapp.net', '') : 'N/A';
            
            html = `
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-6 border-2 rounded-lg bg-green-50 border-green-300 shadow-sm">
                        <div class="flex items-center gap-4">
                            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check-circle-2 text-green-600">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <polyline points="9 12 11 14 15 10"></polyline>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xl font-bold text-green-900 mb-1">✅ Conectado ao WhatsApp</p>
                                <p class="text-sm text-green-700 font-medium">${userName}</p>
                                <p class="text-xs text-green-600 mt-1">${userId}</p>
                            </div>
                        </div>
                        <div class="inline-flex items-center rounded-full border-2 border-green-300 px-4 py-2 text-sm font-bold bg-green-100 text-green-800">
                            Online
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button onclick="disconnectWhatsApp()" class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border-2 border-red-400 bg-red-50 text-red-700 hover:bg-red-100 h-11 px-6 py-2 font-semibold" id="disconnect-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-log-out">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                <polyline points="16 17 21 12 16 7"></polyline>
                                <line x1="21" x2="9" y1="12" y2="12"></line>
                            </svg>
                            Desconectar WhatsApp
                        </button>
                    </div>
                </div>
            `;
        } 
        // 2️⃣ Se não conectado e tem código de pareamento
        else if (pairingCode) {
            // Calcular tempo desde a geração do código
            const now = new Date();
            const ageSeconds = Math.floor((now - lastUpdated) / 1000);
            const isExpired = ageSeconds > 90; // Código expira em ~90 segundos
            
            html = `
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-6 border-2 rounded-lg bg-amber-50 border-amber-300 shadow-sm">
                        <div class="flex items-center gap-4">
                            <div class="w-16 h-16 bg-amber-100 rounded-full flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-alert-circle text-amber-600">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="12" x2="12" y1="8" y2="12"></line>
                                    <line x1="12" x2="12.01" y1="16" y2="16"></line>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xl font-bold text-amber-900 mb-1">🔄 Não Conectado</p>
                                <p class="text-sm text-amber-700 font-medium">Aguardando pareamento via código</p>
                            </div>
                        </div>
                        <div class="inline-flex items-center rounded-full border-2 border-amber-300 px-4 py-2 text-sm font-bold bg-amber-100 text-amber-800">
                            Aguardando
                        </div>
                    </div>
                    
                    <div class="flex flex-col items-center p-8 border-2 border-dashed border-amber-300 rounded-lg bg-white shadow-sm">
                        <p class="text-sm font-semibold text-gray-700 mb-4 uppercase tracking-wide">Código de Pareamento</p>
                        <div class="mb-6 p-8 bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl border-2 border-gray-300 shadow-inner">
                            <code class="text-6xl font-mono font-black text-gray-900 tracking-[0.2em] block text-center" id="pairing-code-display">${pairingCode}</code>
                        </div>
                        
                        <div id="pairing-code-age" class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg w-full">
                            <p class="text-xs text-blue-700 text-center">
                                <span class="font-semibold">Gerado há ${ageSeconds} segundos</span>
                                ${ageSeconds < 90 ? ` • Expira em ${90 - ageSeconds}s` : ' • Expirando em breve'}
                            </p>
                        </div>
                        
                        <div class="w-full bg-gray-50 rounded-lg p-5 border border-gray-200">
                            <p class="text-sm font-semibold text-gray-900 mb-3 text-center">📱 Como parear:</p>
                            <ol class="text-xs text-gray-700 space-y-2 list-decimal list-inside">
                                <li>Abra o <strong>WhatsApp Business</strong> no seu celular</li>
                                <li>Toque em <strong>Menu (⋮)</strong> → <strong>Aparelhos conectados</strong></li>
                                <li>Toque em <strong>Conectar um dispositivo</strong></li>
                                <li>Selecione <strong>Conectar via código</strong></li>
                                <li>Digite o código: <strong class="text-base font-mono bg-gray-200 px-2 py-1 rounded">${pairingCode}</strong></li>
                            </ol>
                        </div>
                        
                        <button onclick="refreshPairingCode()" class="mt-6 inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border-2 border-gray-300 bg-white hover:bg-gray-50 text-gray-700 h-11 px-6 py-2 font-semibold">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-refresh-cw">
                                <path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"></path>
                                <path d="M21 3v5h-5"></path>
                                <path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"></path>
                                <path d="M8 16H3v5"></path>
                            </svg>
                            Atualizar Status
                        </button>
                    </div>
                </div>
            `;
        } 
        // 3️⃣ Se não conectado e sem código - mostrar botão para conectar
        else {
            html = `
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-6 border-2 rounded-lg ${hasError ? 'bg-red-50 border-red-300' : 'bg-gray-50 border-gray-300'} shadow-sm">
                        <div class="flex items-center gap-4">
                            <div class="w-16 h-16 ${hasError ? 'bg-red-100' : 'bg-gray-100'} rounded-full flex items-center justify-center">
                                ${hasError ? `
                                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-alert-circle text-red-400">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <line x1="12" x2="12" y1="8" y2="12"></line>
                                        <line x1="12" x2="12.01" y1="16" y2="16"></line>
                                    </svg>
                                ` : `
                                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-plug text-gray-400">
                                        <path d="M12 22v-5"></path>
                                        <path d="M9 8V2"></path>
                                        <path d="M15 8V2"></path>
                                        <path d="M18 8v5a4 4 0 0 1-4 4h-4a4 4 0 0 1-4-4V8Z"></path>
                                    </svg>
                                `}
                            </div>
                            <div>
                                <p class="text-xl font-bold ${hasError ? 'text-red-900' : 'text-gray-900'} mb-1">
                                    ${hasError ? '❌ Erro ao Conectar' : '🔌 WhatsApp Desconectado'}
                                </p>
                                <p class="text-sm ${hasError ? 'text-red-700' : 'text-gray-600'} font-medium">
                                    ${hasError ? status.error || 'Erro ao buscar status do WhatsApp' : 'Clique no botão abaixo para iniciar a conexão'}
                                </p>
                            </div>
                        </div>
                        <div class="inline-flex items-center rounded-full border-2 ${hasError ? 'border-red-300 bg-red-100 text-red-800' : 'border-gray-300 bg-gray-100 text-gray-600'} px-4 py-2 text-sm font-bold">
                            ${hasError ? 'Erro' : 'Desconectado'}
                        </div>
                    </div>
                    <div class="flex justify-center">
                        <button onclick="connectWhatsApp()" id="connect-btn" class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-11 px-6 py-2 font-semibold">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-plug">
                                <path d="M12 22v-5"></path>
                                <path d="M9 8V2"></path>
                                <path d="M15 8V2"></path>
                                <path d="M18 8v5a4 4 0 0 1-4 4h-4a4 4 0 0 1-4-4V8Z"></path>
                            </svg>
                            Conectar WhatsApp
                        </button>
                    </div>
                </div>
            `;
        }
        
        connectionStatusDiv.innerHTML = html;
        
        // Atualizar expirômetro a cada segundo se houver código
        if (pairingCode && !isConnected) {
            // Limpar timer anterior se existir
            if (window.pairingCodeTimer) {
                clearInterval(window.pairingCodeTimer);
            }
            
            // Iniciar timer para atualizar expirômetro
            window.pairingCodeTimer = setInterval(() => {
                if (!lastStatusUpdate) {
                    clearInterval(window.pairingCodeTimer);
                    return;
                }
                
                const now = new Date();
                const ageSeconds = Math.floor((now - lastStatusUpdate) / 1000);
                const ageDisplay = document.querySelector('#pairing-code-age');
                
                if (ageDisplay) {
                    if (ageSeconds > 90) {
                        ageDisplay.className = 'mb-4 p-4 bg-red-50 border-2 border-red-300 rounded-lg w-full';
                        ageDisplay.innerHTML = `
                            <p class="text-sm font-semibold text-red-800 text-center">⚠️ Código expirado (gerado há ${ageSeconds}s)</p>
                            <p class="text-xs text-red-600 text-center mt-1">Um novo código será gerado automaticamente</p>
                        `;
                    } else {
                        ageDisplay.className = 'mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg w-full';
                        ageDisplay.innerHTML = `
                            <p class="text-xs text-blue-700 text-center">
                                <span class="font-semibold">Gerado há ${ageSeconds} segundos</span>
                                ${ageSeconds < 90 ? ` • Expira em ${90 - ageSeconds}s` : ' • Expirando em breve'}
                            </p>
                        `;
                    }
                }
            }, 1000);
        } else {
            // Limpar timer se não houver código
            if (window.pairingCodeTimer) {
                clearInterval(window.pairingCodeTimer);
                window.pairingCodeTimer = null;
            }
        }
    }
    
    async function updateConnectionStatus() {
        try {
            const status = await fetchWhatsAppStatus();
            
            // Sempre renderizar o status, mesmo se houver erro
            renderConnectionStatus(status);
            
            // Parar atualização automática se estiver conectado
            if (status.connected) {
                if (statusCheckInterval) {
                    clearInterval(statusCheckInterval);
                    statusCheckInterval = null;
                    console.log('✅ Parando atualização automática - WhatsApp conectado');
                }
        } else {
            // Se não estiver conectado e não houver interval, iniciar com intervalo de 60 segundos
            // IMPORTANTE: Limpar qualquer intervalo anterior antes de criar um novo
            if (statusCheckInterval) {
                clearInterval(statusCheckInterval);
                statusCheckInterval = null;
            }
            statusCheckInterval = setInterval(updateConnectionStatus, 60000); // 60 segundos (ideal para códigos que expiram em ~90s)
            console.log('🔄 Iniciando atualização automática - WhatsApp desconectado (intervalo: 60s)');
        }
        } catch (error) {
            console.error('❌ Erro ao atualizar status:', error);
            // Renderizar estado de erro
            renderConnectionStatus({
                connected: false,
                pairingCode: null,
                user: null,
                last_updated: new Date().toISOString(),
                error: 'Erro ao buscar status'
            });
        }
    }
    
    window.refreshPairingCode = async function() {
        console.log('🔄 Atualizando código de pareamento...');
        const status = await fetchWhatsAppStatus();
        renderConnectionStatus(status);
    };
    
    window.disconnectWhatsApp = async function() {
        if (!confirm('Tem certeza que deseja desconectar o WhatsApp? Será necessário fazer um novo pareamento.')) {
            return;
        }
        
        const btn = document.getElementById('disconnect-btn');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="animate-spin">⏳</span> Desconectando...';
        }
        
        try {
            // Tenta primeiro a rota /whatsapp/disconnect, depois fallback
            let url = '/dashboard/whatsapp/disconnect';
            // Obter token CSRF do formulário ou meta tag
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                             document.querySelector('input[name="_token"]')?.value || 
                             '{{ csrf_token() }}';
            
            let response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            });
            
            if (!response.ok) {
                url = '{{ route("dashboard.settings.whatsapp.disconnect") }}';
                response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });
            }
            
            const result = await response.json();
            
            if (result.success) {
                alert('✅ ' + result.message);
                // Atualizar status imediatamente
                setTimeout(() => {
                    updateConnectionStatus();
                }, 1000);
            } else {
                alert('❌ ' + (result.message || result.error || 'Erro ao desconectar'));
            }
        } catch (error) {
            console.error('Erro ao desconectar:', error);
            alert('❌ Erro ao desconectar WhatsApp. Tente novamente.');
        } finally {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-log-out">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <polyline points="16 17 21 12 16 7"></polyline>
                        <line x1="21" x2="9" y1="12" y2="12"></line>
                    </svg>
                    Desconectar WhatsApp
                `;
            }
        }
    };
    
    // NÃO atualizar status inicial automaticamente - aguardar botão de conexão
    // Apenas verificar se já está conectado (sem iniciar conexão)
    checkInitialStatus();
    
    // Função para verificar status inicial (sem iniciar conexão)
    async function checkInitialStatus() {
        try {
            const status = await fetchWhatsAppStatus();
            renderConnectionStatus(status);
            
            // Se já estiver conectado, parar aqui
            if (status.connected) {
                console.log('✅ WhatsApp já está conectado');
                return;
            }
            
            // Se não estiver conectado, mostrar botão de conexão
            // (não iniciar atualização automática ainda)
        } catch (error) {
            console.error('Erro ao verificar status inicial:', error);
            renderConnectionStatus({
                connected: false,
                pairingCode: null,
                user: null,
                last_updated: new Date().toISOString(),
                error: 'Erro ao buscar status'
            });
        }
    }
    
    // Função para conectar WhatsApp (chamada pelo botão)
    window.connectWhatsApp = async function() {
        const btn = document.getElementById('connect-btn');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="animate-spin">⏳</span> Conectando...';
        }
        
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                             document.querySelector('input[name="_token"]')?.value || 
                             '{{ csrf_token() }}';
            
            // Tenta primeiro a rota /whatsapp/connect, depois fallback
            let url = '/dashboard/whatsapp/connect';
            let response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            });
            
            if (!response.ok) {
                url = '{{ route("dashboard.settings.whatsapp.connect") }}';
                response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });
            }
            
            const result = await response.json();
            
            if (result.success) {
                alert('✅ ' + result.message);
                // Iniciar verificação de status após 2 segundos
                setTimeout(() => {
                    updateConnectionStatus();
                }, 2000);
            } else {
                alert('❌ ' + (result.message || result.error || 'Erro ao conectar'));
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = `
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-plug">
                            <path d="M12 22v-5"></path>
                            <path d="M9 8V2"></path>
                            <path d="M15 8V2"></path>
                            <path d="M18 8v5a4 4 0 0 1-4 4h-4a4 4 0 0 1-4-4V8Z"></path>
                        </svg>
                        Conectar WhatsApp
                    `;
                }
            }
        } catch (error) {
            console.error('Erro ao conectar:', error);
            alert('❌ Erro ao conectar WhatsApp. Tente novamente.');
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-plug">
                        <path d="M12 22v-5"></path>
                        <path d="M9 8V2"></path>
                        <path d="M15 8V2"></path>
                        <path d="M18 8v5a4 4 0 0 1-4 4h-4a4 4 0 0 1-4-4V8Z"></path>
                    </svg>
                    Conectar WhatsApp
                `;
            }
        }
    };
    
    // Limpar intervalos quando sair da página
    window.addEventListener('beforeunload', () => {
        if (statusCheckInterval) {
            clearInterval(statusCheckInterval);
        }
        if (window.pairingCodeTimer) {
            clearInterval(window.pairingCodeTimer);
        }
        // Limpar todos os intervalos de instâncias
        Object.values(instanceStatusIntervals || {}).forEach(interval => clearInterval(interval));
        // Limpar polling de códigos de pareamento
        Object.values(pairingCodePollingIntervals || {}).forEach(interval => clearInterval(interval));
        // Limpar polling global
        if (globalStatusPollingInterval) {
            clearInterval(globalStatusPollingInterval);
        }
    });

    // Iniciar polling global de status
    startGlobalStatusPolling();
});

// ========== FUNÇÕES DE POLLING GLOBAL ==========
let globalStatusPollingInterval = null;

function startGlobalStatusPolling() {
    // Verifica a cada 10 segundos
    globalStatusPollingInterval = setInterval(() => {
        const instances = document.querySelectorAll('.instance-card');
        instances.forEach(card => {
            const instanceId = card.getAttribute('data-instance-id');
            if (instanceId) {
                updateInstanceStatus(instanceId);
            }
        });
    }, 10000); 
}

// ========== FUNÇÕES PARA GERENCIAR INSTÂNCIAS ==========

// Conectar instância
async function connectInstance(instanceId) {
    const btn = event.target.closest('button');
    const originalHTML = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = '<span class="animate-spin">⏳</span> Conectando...';
    
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                         document.querySelector('input[name="_token"]')?.value;
        
        const response = await fetch(`{{ route("dashboard.whatsapp.instances.connect", ":id") }}`.replace(':id', instanceId), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        });
        
        const result = await response.json();
        
        if (response.ok) {
            // Se tiver código de pareamento, exibir automaticamente
            if (result.pairingCode) {
                // Buscar nome da instância
                const instanceResponse = await fetch(`{{ route("dashboard.whatsapp.instances.show", ":id") }}`.replace(':id', instanceId));
                const instanceData = await instanceResponse.json();
                showPairingCodeModal(instanceId, instanceData.name || 'Instância');
            } else {
                // Iniciar polling para buscar o código automaticamente
                startPairingCodePolling(instanceId);
            }
            // Atualizar status após 2 segundos
            setTimeout(() => updateInstanceStatus(instanceId), 2000);
        } else {
            alert('❌ ' + (result.error || 'Erro ao conectar'));
        }
    } catch (error) {
        console.error('Erro ao conectar:', error);
        alert('❌ Erro ao conectar instância. Tente novamente.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalHTML;
    }
}

// Desconectar instância
async function disconnectInstance(instanceId) {
    if (!confirm('Tem certeza que deseja desconectar esta instância?')) {
        return;
    }
    
    const btn = event.target.closest('button');
    const originalHTML = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = '<span class="animate-spin">⏳</span> Desconectando...';
    
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                         document.querySelector('input[name="_token"]')?.value;
        
        // Atualizar status no banco
        const response = await fetch(`{{ route("dashboard.whatsapp.instances.update", ":id") }}`.replace(':id', instanceId), {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ status: 'DISCONNECTED' })
        });
        
        if (response.ok) {
            alert('✅ Instância desconectada');
            location.reload(); // Recarregar para atualizar status
        } else {
            alert('❌ Erro ao desconectar');
        }
    } catch (error) {
        console.error('Erro ao desconectar:', error);
        alert('❌ Erro ao desconectar instância. Tente novamente.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalHTML;
    }
}

// Buscar status de uma instância específica
async function fetchInstanceStatus(instanceId) {
    try {
        const response = await fetch(`{{ route("dashboard.whatsapp.instances.show", ":id") }}`.replace(':id', instanceId));
        if (!response.ok) return null;
        
        const instance = await response.json();
        
        // Buscar status do Node.js
        if (instance.api_url) {
            const statusResponse = await fetch(`${instance.api_url}/api/whatsapp/status`, {
                headers: {
                    'X-Olika-Token': instance.api_token || '{{ $whatsappApiKey }}'
                }
            });
            
            if (statusResponse.ok) {
                const status = await statusResponse.json();
                return {
                    ...instance,
                    connected: status.connected || false,
                    pairingCode: status.pairingCode || null,
                    user: status.user || null,
                    last_updated: status.last_updated || new Date().toISOString()
                };
            }
        }
        
        return instance;
    } catch (error) {
        console.error('Erro ao buscar status da instância:', error);
        return null;
    }
}

// Atualizar status de uma instância
async function updateInstanceStatus(instanceId) {
    const status = await fetchInstanceStatus(instanceId);
    if (!status) return;
    
    // Atualizar badge de status
    const card = document.querySelector(`[data-instance-id="${instanceId}"]`);
    if (!card) return;
    
    const statusBadge = card.querySelector('.status-badge');
    if (statusBadge) {
        if (status.connected) {
            statusBadge.innerHTML = `
                <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold bg-green-100 text-green-800 border-green-300">
                    <span class="w-2 h-2 bg-green-600 rounded-full mr-2"></span>
                    Conectado
                </span>
            `;
            // Parar polling se estiver conectado
            if (pairingCodePollingIntervals[instanceId]) {
                clearInterval(pairingCodePollingIntervals[instanceId]);
                delete pairingCodePollingIntervals[instanceId];
            }
        } else if (status.last_error_message) {
            statusBadge.innerHTML = `
                <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold bg-red-100 text-red-800 border-red-300" title="${status.last_error_message}">
                    <span class="w-2 h-2 bg-red-600 rounded-full mr-2"></span>
                    Erro Fatal
                </span>
            `;
        } else if (status.pairingCode) {
            statusBadge.innerHTML = `
                <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold bg-amber-100 text-amber-800 border-amber-300">
                    <span class="w-2 h-2 bg-amber-600 rounded-full mr-2 animate-pulse"></span>
                    Conectando
                </span>
            `;
            // Se tiver código e modal não estiver aberto, abrir automaticamente
            const modal = document.getElementById('pairing-code-modal');
            if (modal && modal.classList.contains('hidden')) {
                // Buscar nome da instância
                fetch(`{{ route("dashboard.whatsapp.instances.show", ":id") }}`.replace(':id', instanceId))
                    .then(res => res.json())
                    .then(instanceData => {
                        showPairingCodeModal(instanceId, instanceData.name || 'Instância');
                    });
            }
        } else {
            statusBadge.innerHTML = `
                <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold bg-gray-100 text-gray-600 border-gray-300">
                    <span class="w-2 h-2 bg-gray-400 rounded-full mr-2"></span>
                    Desconectado
                </span>
            `;
        }
    }
    
    // Atualizar botões
    const actionsDiv = card.querySelector('.flex.items-center.gap-2');
    if (actionsDiv) {
        if (status.connected) {
            actionsDiv.innerHTML = `
                <button onclick="disconnectInstance(${instanceId})" class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-red-300 bg-red-50 text-red-700 hover:bg-red-100 h-9 px-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-log-out">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <polyline points="16 17 21 12 16 7"></polyline>
                        <line x1="21" x2="9" y1="12" y2="12"></line>
                    </svg>
                    Desconectar
                </button>
            `;
        } else {
            actionsDiv.innerHTML = `
                <button onclick="connectInstance(${instanceId})" class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-9 px-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-plug">
                        <path d="M12 22v-5"></path>
                        <path d="M9 8V2"></path>
                        <path d="M15 8V2"></path>
                        <path d="M18 8v5a4 4 0 0 1-4 4h-4a4 4 0 0 1-4-4V8Z"></path>
                    </svg>
                    Conectar
                </button>
            `;
        }
    }
}

// Mostrar modal de código de pareamento
async function showPairingCodeModal(instanceId, instanceName) {
    const modal = document.getElementById('pairing-code-modal');
    const title = document.getElementById('pairing-modal-title');
    const content = document.getElementById('pairing-code-content');
    
    title.textContent = `Código de Pareamento - ${instanceName}`;
    content.innerHTML = `
        <div class="flex items-center justify-center p-8">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
            <span class="ml-3 text-muted-foreground">Carregando código...</span>
        </div>
    `;
    
    modal.classList.remove('hidden');
    
    // Buscar status da instância
    const status = await fetchInstanceStatus(instanceId);
    
    if (status && status.pairingCode) {
        const code = status.pairingCode.match(/.{1,4}/g)?.join('-') || status.pairingCode;
        content.innerHTML = `
            <div class="space-y-6">
                <div class="bg-muted/50 p-6 rounded-xl border border-border text-center space-y-3">
                    <p class="text-sm font-medium text-muted-foreground uppercase tracking-wider">Código de Pareamento</p>
                    <div class="relative group">
                        <div class="absolute -inset-1 bg-gradient-to-r from-primary to-primary/50 rounded-lg blur opacity-25 group-hover:opacity-50 transition duration-1000 group-hover:duration-200"></div>
                        <div class="relative bg-background border rounded-lg p-4 shadow-sm">
                            <code class="text-4xl font-mono font-bold tracking-widest text-primary select-all">${code}</code>
                        </div>
                    </div>
                    <p class="text-xs text-muted-foreground">Este código expira em breve</p>
                </div>

                <div class="space-y-3">
                    <h4 class="text-sm font-semibold flex items-center gap-2">
                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-primary/10 text-primary text-xs font-bold">?</span>
                        Como conectar seu WhatsApp
                    </h4>
                    <div class="grid gap-3 text-sm text-muted-foreground">
                        <div class="flex gap-3 items-start">
                            <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full border text-[10px] font-medium mt-0.5">1</span>
                            <span>Abra o <strong>WhatsApp</strong> ou <strong>WhatsApp Business</strong> no seu celular.</span>
                        </div>
                        <div class="flex gap-3 items-start">
                            <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full border text-[10px] font-medium mt-0.5">2</span>
                            <span>Toque em <strong>Menu (⋮)</strong> (Android) ou <strong>Configurações</strong> (iOS) e selecione <strong>Aparelhos conectados</strong>.</span>
                        </div>
                        <div class="flex gap-3 items-start">
                            <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full border text-[10px] font-medium mt-0.5">3</span>
                            <span>Toque em <strong>Conectar um aparelho</strong> e depois em <strong>Conectar com número de telefone</strong>.</span>
                        </div>
                        <div class="flex gap-3 items-start">
                            <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full border text-[10px] font-medium mt-0.5">4</span>
                            <span>Digite o código acima no seu celular para confirmar a conexão.</span>
                        </div>
                    </div>
                </div>
            </div>
        `;
    } else if (status && status.connected) {
        content.innerHTML = `
            <div class="text-center p-8">
                <p class="text-lg font-semibold text-green-600 mb-2">✅ Instância já está conectada!</p>
                <p class="text-sm text-muted-foreground">Não é necessário código de pareamento.</p>
            </div>
        `;
    } else {
        content.innerHTML = `
            <div class="text-center p-8">
                <p class="text-lg font-semibold text-gray-600 mb-2">Nenhum código disponível</p>
                <p class="text-sm text-muted-foreground mb-4">Clique em "Conectar" para gerar um novo código.</p>
                <button onclick="connectInstance(${instanceId}); closePairingCodeModal();" class="inline-flex items-center justify-center gap-2 rounded-md text-sm font-medium bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4">
                    Conectar Agora
                </button>
            </div>
        `;
    }
}

// Polling para buscar código de pareamento automaticamente
let pairingCodePollingIntervals = {};

function startPairingCodePolling(instanceId) {
    // Parar polling anterior se existir
    if (pairingCodePollingIntervals[instanceId]) {
        clearInterval(pairingCodePollingIntervals[instanceId]);
    }
    
    // Buscar nome da instância primeiro
    fetch(`{{ route("dashboard.whatsapp.instances.show", ":id") }}`.replace(':id', instanceId))
        .then(res => res.json())
        .then(instanceData => {
            const instanceName = instanceData.name || 'Instância';
            
            // Tentar buscar código imediatamente
            checkAndShowPairingCode(instanceId, instanceName);
            
            // Iniciar polling a cada 3 segundos
            pairingCodePollingIntervals[instanceId] = setInterval(() => {
                checkAndShowPairingCode(instanceId, instanceName);
            }, 3000);
            
            // Parar após 2 minutos (código expira em ~90s)
            setTimeout(() => {
                if (pairingCodePollingIntervals[instanceId]) {
                    clearInterval(pairingCodePollingIntervals[instanceId]);
                    delete pairingCodePollingIntervals[instanceId];
                }
            }, 120000);
        });
}

async function checkAndShowPairingCode(instanceId, instanceName) {
    const status = await fetchInstanceStatus(instanceId);
    
    if (status && status.pairingCode) {
        // Parar polling
        if (pairingCodePollingIntervals[instanceId]) {
            clearInterval(pairingCodePollingIntervals[instanceId]);
            delete pairingCodePollingIntervals[instanceId];
        }
        
        // Exibir modal com código
        showPairingCodeModal(instanceId, instanceName);
    }
}

// Fechar modal de código
function closePairingCodeModal() {
    document.getElementById('pairing-code-modal').classList.add('hidden');
}

// Mostrar modal de adicionar instância
function showAddInstanceModal() {
    document.getElementById('add-instance-modal').classList.remove('hidden');
}

// Fechar modal de adicionar instância
function closeAddInstanceModal() {
    document.getElementById('add-instance-modal').classList.add('hidden');
    document.getElementById('add-instance-form').reset();
}

// Salvar nova instância
async function saveInstance(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    const data = Object.fromEntries(formData);
    
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                         document.querySelector('input[name="_token"]')?.value;
        
        const response = await fetch('{{ route("dashboard.whatsapp.instances.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        });
        
        if (response.ok) {
            alert('✅ Instância criada com sucesso!');
            location.reload();
        } else {
            const result = await response.json();
            alert('❌ Erro: ' + (result.message || 'Erro ao criar instância'));
        }
    } catch (error) {
        console.error('Erro ao salvar instância:', error);
        alert('❌ Erro ao salvar instância. Tente novamente.');
    }
}

// Mostrar modal de edição
async function openEditInstanceModal(instanceId) {
    try {
        const response = await fetch(`{{ route("dashboard.whatsapp.instances.show", ":id") }}`.replace(':id', instanceId));
        if (!response.ok) throw new Error('Erro ao buscar dados da instância');
        
        const instance = await response.json();
        
        document.getElementById('edit-instance-id').value = instance.id;
        document.getElementById('edit-instance-name').value = instance.name;
        document.getElementById('edit-instance-api-url').value = instance.api_url;
        document.getElementById('edit-instance-api-token').value = instance.api_token || '';
        document.getElementById('edit-instance-phone').value = instance.phone_number || '';
        
        document.getElementById('edit-instance-modal').classList.remove('hidden');
    } catch (error) {
        console.error('Erro:', error);
        alert('Erro ao carregar dados da instância.');
    }
}

// Fechar modal de edição
function closeEditInstanceModal() {
    document.getElementById('edit-instance-modal').classList.add('hidden');
    document.getElementById('edit-instance-form').reset();
}

// Atualizar instância
async function updateInstance(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    const data = Object.fromEntries(formData);
    const instanceId = data.id;
    
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                         document.querySelector('input[name="_token"]')?.value;
        
        const response = await fetch(`{{ route("dashboard.whatsapp.instances.update", ":id") }}`.replace(':id', instanceId), {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        });
        
        if (response.ok) {
            alert('✅ Instância atualizada com sucesso!');
            location.reload();
        } else {
            const result = await response.json();
            alert('❌ Erro: ' + (result.message || result.error || 'Erro ao atualizar instância'));
        }
    } catch (error) {
        console.error('Erro ao atualizar instância:', error);
        alert('❌ Erro ao atualizar instância. Tente novamente.');
    }
}

// Criar Campanha
async function createCampaign(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    const data = Object.fromEntries(formData);
    
    // Converter para inteiros
    data.interval_seconds = parseInt(data.interval_seconds);
    
    const btn = form.querySelector('button[type="submit"]');
    const originalText = btn.innerHTML;
    btn.innerHTML = 'Iniciando...';
    btn.disabled = true;
    
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                         document.querySelector('input[name="_token"]')?.value;
        
        const response = await fetch('{{ route("dashboard.whatsapp.campaigns.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (response.ok) {
            alert('✅ ' + result.message);
            form.reset();
            // Atualizar lista de campanhas (recarregar página por enquanto)
            location.reload();
        } else {
            alert('❌ Erro: ' + (result.message || 'Erro ao criar campanha'));
        }
    } catch (error) {
        console.error('Erro ao criar campanha:', error);
        alert('❌ Erro ao criar campanha. Tente novamente.');
    } finally {
        btn.innerHTML = originalText;
        btn.disabled = false;
    }
}

// Buscar Campanhas (executado ao carregar ou trocar de aba)
async function fetchCampaigns() {
    try {
        const response = await fetch('{{ route("dashboard.whatsapp.campaigns.index") }}');
        if (!response.ok) return;
        
        const campaigns = await response.json();
        const tbody = document.getElementById('campaigns-list-body');
        
        if (campaigns.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="4" class="p-4 text-center text-muted-foreground">Nenhuma campanha encontrada.</td>
                </tr>
            `;
            return;
        }
        
        tbody.innerHTML = campaigns.map(c => `
            <tr class="border-b transition-colors hover:bg-muted/50">
                <td class="p-4 font-medium">${c.name}</td>
                <td class="p-4">
                    <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 ${getStatusBadgeClass(c.status)}">
                        ${formatStatus(c.status)}
                    </span>
                </td>
                <td class="p-4">
                    <div class="flex items-center gap-2">
                        <div class="h-2 w-full bg-secondary rounded-full overflow-hidden">
                            <div class="h-full bg-primary transition-all" style="width: ${(c.processed_count / c.total_leads * 100) || 0}%"></div>
                        </div>
                        <span class="text-xs text-muted-foreground whitespace-nowrap">
                            ${c.processed_count} / ${c.total_leads}
                        </span>
                    </div>
                </td>
                <td class="p-4 text-right text-muted-foreground">
                    ${new Date(c.created_at).toLocaleDateString('pt-BR')}
                </td>
            </tr>
        `).join('');
        
    } catch (error) {
        console.error('Erro ao buscar campanhas:', error);
    }
}

function getStatusBadgeClass(status) {
    switch(status) {
        case 'completed': return 'border-transparent bg-green-100 text-green-800 hover:bg-green-200';
        case 'processing': return 'border-transparent bg-blue-100 text-blue-800 hover:bg-blue-200 animate-pulse';
        case 'pending': return 'border-transparent bg-yellow-100 text-yellow-800 hover:bg-yellow-200';
        default: return 'border-transparent bg-gray-100 text-gray-800 hover:bg-gray-200';
    }
}

function formatStatus(status) {
    const map = {
        'pending': 'Pendente',
        'processing': 'Enviando',
        'completed': 'Concluída',
        'paused': 'Pausada',
        'cancelled': 'Cancelada'
    };
    return map[status] || status;
}

// Fechar modais ao clicar fora e garantir que estão ocultos ao carregar
document.addEventListener('DOMContentLoaded', function() {
    // Carregar campanhas se a aba estiver ativa (ou ao clicar na aba)
    const campaignsTabBtn = document.querySelector('[data-tab="campaigns"]');
    if (campaignsTabBtn) {
        campaignsTabBtn.addEventListener('click', fetchCampaigns);
    }
    // Garantir que os modais estão ocultos ao carregar
    const pairingModal = document.getElementById('pairing-code-modal');
    const addModal = document.getElementById('add-instance-modal');
    const editModal = document.getElementById('edit-instance-modal');
    
    if (pairingModal) {
        pairingModal.classList.add('hidden');
        pairingModal.addEventListener('click', function(e) {
            if (e.target === this) closePairingCodeModal();
        });
    }
    
    if (addModal) {
        addModal.classList.add('hidden');
        addModal.addEventListener('click', function(e) {
            if (e.target === this) closeAddInstanceModal();
        });
    }

    if (editModal) {
        editModal.classList.add('hidden');
        editModal.addEventListener('click', function(e) {
            if (e.target === this) closeEditInstanceModal();
        });
    }
});
</script>
@endpush
@endsection


