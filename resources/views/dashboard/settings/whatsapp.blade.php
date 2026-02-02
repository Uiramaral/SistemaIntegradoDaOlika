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

        <x-stat-grid :items="[
            ['label' => 'Templates Ativos', 'value' => ($stats['active_templates'] ?? 0), 'icon' => 'file-text'],
            ['label' => 'Total de Templates', 'value' => ($stats['total_templates'] ?? 0), 'icon' => 'folder'],
            ['label' => 'Status Configurados', 'value' => ($stats['total_statuses'] ?? 0), 'icon' => 'list-checks'],
            ['label' => 'Notificações Ativas', 'value' => ($stats['statuses_with_notifications'] ?? 0), 'icon' => 'bell'],
        ]" />

        <div dir="ltr" data-orientation="horizontal" class="space-y-4">
            <x-tab-bar type="buttons" :tabs="[
            ['id' => 'settings', 'label' => 'Configurações', 'data-tab' => 'settings'],
            ['id' => 'campaigns', 'label' => 'Campanhas', 'data-tab' => 'campaigns'],
            ['id' => 'templates', 'label' => 'Templates', 'data-tab' => 'templates'],
            ['id' => 'notifications', 'label' => 'Notificações', 'data-tab' => 'notifications'],
        ]" active="settings" />

            <div data-tab-content="settings"
                class="tab-content mt-2 ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                <!-- Seção de Instâncias WhatsApp -->
                <div class="rounded-lg border bg-card text-card-foreground shadow-sm mb-6">
                    <div class="flex flex-col space-y-1.5 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold leading-none tracking-tight">Instâncias WhatsApp</h3>
                                <p class="text-sm text-muted-foreground">Gerencie múltiplas conexões WhatsApp</p>
                            </div>
                            @if($instances->count() < $maxInstances)
                                <button onclick="showAddInstanceModal()"
                                    class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" class="lucide lucide-plus">
                                        <path d="M5 12h14"></path>
                                        <path d="M12 5v14"></path>
                                    </svg>
                                    + Nova Instância
                                </button>
                            @endif
                        </div>
                    </div>
                    <div class="p-6 pt-0 space-y-4">
                        @forelse($instances as $instance)
                            <div class="instance-card border rounded-lg p-4 hover:bg-muted/50 transition-colors"
                                data-instance-id="{{ $instance->id }}">
                                <div class="flex items-center justify-between gap-4">
                                    <!-- Informações da Instância -->
                                    <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <p class="text-xs text-muted-foreground mb-1">Atendimento</p>
                                            <p class="font-semibold">{{ $instance->name }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-muted-foreground mb-1">Telefone</p>
                                            <p class="font-mono text-sm">{{ $instance->phone_number ?? 'Não configurado' }}</p>
                                        </div>
                                    </div>

                                    <!-- Status e Ações -->
                                    <div class="flex items-center gap-3">
                                        <!-- Status Badge -->
                                        <div class="status-badge">
                                            @if($instance->status === 'CONNECTED')
                                                <span
                                                    class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold bg-green-100 text-green-800 border-green-300">
                                                    <span class="w-2 h-2 bg-green-600 rounded-full mr-2"></span>
                                                    Conectado
                                                </span>
                                            @elseif($instance->last_error_message)
                                                <span
                                                    class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold bg-red-100 text-red-800 border-red-300"
                                                    title="{{ $instance->last_error_message }}">
                                                    <span class="w-2 h-2 bg-red-600 rounded-full mr-2"></span>
                                                    @if(str_contains(strtolower($instance->last_error_message), 'persistent_failure') || str_contains(strtolower($instance->last_error_message), 'desconectado') || str_contains(strtolower($instance->last_error_message), 'instável'))
                                                        Desconectado
                                                    @else
                                                        Falha de Conexão
                                                    @endif
                                                </span>
                                            @elseif($instance->status === 'CONNECTING')
                                                <span
                                                    class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold bg-amber-100 text-amber-800 border-amber-300">
                                                    <span class="w-2 h-2 bg-amber-600 rounded-full mr-2 animate-pulse"></span>
                                                    Conectando
                                                </span>
                                            @else
                                                <span
                                                    class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold bg-gray-100 text-gray-600 border-gray-300">
                                                    <span class="w-2 h-2 bg-gray-400 rounded-full mr-2"></span>
                                                    Desconectado
                                                </span>
                                            @endif
                                        </div>

                                        <!-- Botões de Ação -->
                                        <div class="flex items-center gap-2">
                                            @if($instance->last_error_message)
                                                <button onclick="alert('Erro: {{ addslashes($instance->last_error_message) }}')"
                                                    class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors border border-red-300 bg-red-50 text-red-700 hover:bg-red-100 h-9 w-9"
                                                    title="Ver erro">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                        stroke-linecap="round" stroke-linejoin="round"
                                                        class="lucide lucide-alert-circle">
                                                        <circle cx="12" cy="12" r="10"></circle>
                                                        <line x1="12" x2="12" y1="8" y2="12"></line>
                                                        <line x1="12" x2="12.01" y1="16" y2="16"></line>
                                                    </svg>
                                                </button>
                                            @endif

                                            <button onclick="openEditInstanceModal({{ $instance->id }})"
                                                class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 w-9">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                    stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-pencil">
                                                    <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"></path>
                                                    <path d="m15 5 4 4"></path>
                                                </svg>
                                            </button>

                                            @if($instance->status === 'CONNECTED')
                                                <button onclick="disconnectInstance({{ $instance->id }})"
                                                    class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-red-300 bg-red-50 text-red-700 hover:bg-red-100 h-9 px-3">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                        stroke-linecap="round" stroke-linejoin="round"
                                                        class="lucide lucide-log-out">
                                                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                                        <polyline points="16 17 21 12 16 7"></polyline>
                                                        <line x1="21" x2="9" y1="12" y2="12"></line>
                                                    </svg>
                                                    → Desconectar
                                                </button>
                                            @else
                                                <button onclick="connectInstance({{ $instance->id }})"
                                                    class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-9 px-3">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                        stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-plug">
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
                                <button onclick="showAddInstanceModal()"
                                    class="text-primary hover:underline mt-2 inline-block">Criar primeira instância</button>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Seção de Configurações Gerais -->
                <div class="rounded-lg border bg-card text-card-foreground shadow-sm mt-6">
                    <div class="flex flex-col space-y-1.5 p-6">
                        <div>
                            <h3 class="text-lg font-semibold leading-none tracking-tight">Configurações de Notificações</h3>
                            <p class="text-sm text-muted-foreground">Configure o número que receberá notificações de admin
                            </p>
                        </div>
                    </div>
                    <div class="p-6 pt-0">
                        <form action="{{ route('dashboard.settings.whatsapp.save') }}" method="POST" class="space-y-4">
                            @csrf
                            <div>
                                <label class="text-sm font-medium mb-2 block">Número para Notificações de Admin</label>
                                <input type="text" name="admin_notification_phone" id="admin_notification_phone"
                                    value="{{ ($row && $row->admin_notification_phone) ? preg_replace('/^55/', '', $row->admin_notification_phone) : ($connectedPhone ? preg_replace('/^55/', '', $connectedPhone) : '') }}"
                                    placeholder="71999999999" pattern="[0-9]+" maxlength="15"
                                    class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm phone-input">
                                <p class="text-xs text-muted-foreground mt-1">
                                    Digite apenas o DDD e o número (ex: 71999999999). O sistema adiciona automaticamente o
                                    código do país (55).
                                </p>
                                <p class="text-xs text-amber-600 mt-1">
                                    ⚠️ Este número receberá notificações quando "Notificar Admin" estiver ativado nos status
                                    dos pedidos.
                                </p>
                            </div>

                            <div>
                                <label class="text-sm font-medium mb-2 block">Número Padrão para Confirmações de
                                    Pagamento</label>
                                <input type="text" name="default_payment_confirmation_phone"
                                    id="default_payment_confirmation_phone" value="{{ $connectedPhone ?? '' }}"
                                    placeholder="5571999999999" readonly disabled
                                    class="w-full rounded-md border border-input bg-muted px-3 py-2 text-sm cursor-not-allowed">
                                <p class="text-xs text-muted-foreground mt-1">
                                    Este número é automaticamente definido como o número da sua instância WhatsApp conectada
                                    e não pode ser alterado.
                                </p>
                                <p class="text-xs text-blue-600 mt-1">
                                    ℹ️ Este número será sempre usado para confirmações de pagamento.
                                </p>
                            </div>
                            <button type="submit"
                                class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2">
                                Salvar Configuração
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Aba Campanhas -->
            <div data-tab-content="campaigns"
                class="tab-content mt-2 ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 hidden">
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
                                        <input type="text" name="name" required
                                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                                            placeholder="Promoção Pizza Sexta">
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium mb-1 block">Mensagem</label>
                                        <textarea name="message" rows="5" required
                                            class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                                            placeholder="Olá {nome}, hoje tem promoção!"></textarea>
                                        <p class="text-xs text-muted-foreground mt-1">Variáveis: {nome}, {telefone}</p>
                                    </div>

                                    <!-- Filtros Combinados -->
                                    <div class="space-y-3 p-3 border rounded-md bg-muted/30">
                                        <label class="text-sm font-semibold mb-2 block">Filtros de Público</label>

                                        <div>
                                            <label class="text-sm font-medium mb-1 block">Público Alvo Base</label>
                                            <select name="target_audience"
                                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
                                                <option value="all">Todos os Clientes</option>
                                                <option value="has_orders">Clientes que já compraram</option>
                                                <option value="no_orders">Leads (nunca compraram)</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="flex items-center gap-2 cursor-pointer">
                                                <input type="checkbox" name="filter_newsletter" value="1"
                                                    class="h-4 w-4 text-primary">
                                                <span class="text-sm font-medium">Apenas Newsletter (clientes que optaram
                                                    por receber)</span>
                                            </label>
                                        </div>

                                        <div>
                                            <label class="text-sm font-medium mb-1 block">Tipo de Cliente (combinar com
                                                Newsletter)</label>
                                            <select name="filter_customer_type"
                                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
                                                <option value="all">Todos</option>
                                                <option value="new_customers">Apenas Leads (nunca compraram)</option>
                                                <option value="existing_customers">Apenas Clientes (já compraram)</option>
                                            </select>
                                            <p class="text-xs text-muted-foreground mt-1">Pode combinar com Newsletter para
                                                filtrar melhor</p>
                                        </div>
                                    </div>

                                    <!-- Cliente Único para Testes -->
                                    <div class="p-3 border rounded-md bg-blue-50/50">
                                        <label class="text-sm font-semibold mb-2 block">🧪 Teste com Cliente Único</label>
                                        <select name="test_customer_id" id="test_customer_id"
                                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
                                            <option value="">Nenhum (enviar para todos do filtro)</option>
                                        </select>
                                        <p class="text-xs text-muted-foreground mt-1">Selecione um cliente para enviar
                                            apenas para ele (útil para testes)</p>
                                        <input type="text" id="test_customer_search"
                                            placeholder="Buscar cliente por nome, telefone ou email..."
                                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm mt-2">
                                        <div id="test_customer_results"
                                            class="mt-2 hidden max-h-40 overflow-y-auto border rounded-md bg-background">
                                        </div>
                                    </div>

                                    <!-- Agendamento -->
                                    <div class="p-3 border rounded-md bg-amber-50/50">
                                        <label class="text-sm font-semibold mb-2 block">📅 Agendar Campanha</label>
                                        <div class="grid grid-cols-2 gap-2">
                                            <div>
                                                <label class="text-xs font-medium mb-1 block">Data de Início</label>
                                                <input type="date" name="scheduled_date" id="scheduled_date"
                                                    min="{{ date('Y-m-d') }}"
                                                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
                                            </div>
                                            <div>
                                                <label class="text-xs font-medium mb-1 block">Horário</label>
                                                <input type="time" name="scheduled_time" id="scheduled_time"
                                                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
                                            </div>
                                        </div>
                                        <p class="text-xs text-muted-foreground mt-1">Deixe em branco para iniciar
                                            imediatamente</p>
                                    </div>

                                    <div>
                                        <label class="text-sm font-medium mb-1 block">Intervalo (segundos)</label>
                                        <input type="number" name="interval_seconds" value="15" min="5" required
                                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
                                        <p class="text-xs text-muted-foreground mt-1">Tempo entre cada envio para evitar
                                            bloqueio.</p>
                                    </div>
                                    <button type="submit"
                                        class="w-full inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2">
                                        <span id="campaign-submit-text">Iniciar Campanha</span>
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
                                            <tr
                                                class="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted">
                                                <th
                                                    class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">
                                                    Nome</th>
                                                <th
                                                    class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">
                                                    Status</th>
                                                <th
                                                    class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">
                                                    Progresso</th>
                                                <th
                                                    class="h-12 px-4 text-right align-middle font-medium text-muted-foreground">
                                                    Data</th>
                                            </tr>
                                        </thead>
                                        <tbody id="campaigns-list-body" class="[&_tr:last-child]:border-0">
                                            <tr>
                                                <td colspan="4" class="p-4 text-center text-muted-foreground">Carregando
                                                    campanhas...</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div data-tab-content="templates"
                class="tab-content mt-2 ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 hidden">
                <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                    <div class="flex flex-col space-y-1.5 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-2xl font-semibold leading-none tracking-tight">Templates de Mensagem</h3>
                                <p class="text-sm text-muted-foreground">Gerencie templates utilizados nos status dos
                                    pedidos</p>
                            </div>
                            <a href="{{ route('dashboard.settings.status-templates') }}"
                                class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4">Gerenciar
                                Templates</a>
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
                                                    <span
                                                        class="inline-flex items-center rounded-full border px-2 py-0.5 text-xs font-semibold bg-success text-success-foreground">Ativo</span>
                                                @else
                                                    <span
                                                        class="inline-flex items-center rounded-full border px-2 py-0.5 text-xs font-semibold bg-muted text-muted-foreground">Inativo</span>
                                                @endif
                                            </div>
                                            <p class="text-sm text-muted-foreground whitespace-pre-wrap">
                                                {{ Str::limit($template->content, 150) }}</p>
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
                                    <a href="{{ route('dashboard.settings.status-templates') }}"
                                        class="text-primary hover:underline mt-2 inline-block">Criar primeiro template</a>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <div data-tab-content="notifications"
                class="tab-content mt-2 ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 hidden">
                <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                    <div class="flex flex-col space-y-1.5 p-6">
                        <h3 class="text-2xl font-semibold leading-none tracking-tight">Notificações Automáticas</h3>
                        <p class="text-sm text-muted-foreground">Configure quais status devem enviar notificações
                            automáticas</p>
                    </div>
                    <form action="{{ route('dashboard.settings.whatsapp.notifications.save') }}" method="POST"
                        class="p-6 pt-0 space-y-4">
                        @csrf
                        @forelse($statuses as $status)
                            <div class="rounded-lg border p-4 space-y-3">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-semibold">{{ $status->name }}</p>
                                        <p class="text-sm text-muted-foreground">Código: {{ $status->code }}</p>
                                        @if($status->template_slug)
                                            <p class="text-xs text-muted-foreground mt-1">Template: <span
                                                    class="font-medium">{{ $status->template_slug }}</span></p>
                                        @else
                                            <p class="text-xs text-amber-600 mt-1">⚠️ Nenhum template associado</p>
                                        @endif
                                    </div>
                                </div>
                                <div class="grid md:grid-cols-2 gap-4">
                                    <label class="flex items-center justify-between cursor-pointer">
                                        <div>
                                            <span class="font-medium text-sm">Notificar Cliente</span>
                                            <p class="text-xs text-muted-foreground">Enviar mensagem para o cliente quando o
                                                pedido mudar para este status</p>
                                        </div>
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="notifications[{{ $status->id }}][customer]" value="1"
                                                {{ $status->notify_customer ? 'checked' : '' }} class="sr-only peer">
                                            <div
                                                class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary">
                                            </div>
                                        </label>
                                    </label>
                                    <label class="flex items-center justify-between cursor-pointer">
                                        <div>
                                            <span class="font-medium text-sm">Notificar Admin</span>
                                            <p class="text-xs text-muted-foreground">Enviar mensagem para o administrador</p>
                                        </div>
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="notifications[{{ $status->id }}][admin]" value="1" {{ $status->notify_admin ? 'checked' : '' }} class="sr-only peer">
                                            <div
                                                class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary">
                                            </div>
                                        </label>
                                    </label>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-lg border p-8 text-center text-muted-foreground">
                                <p>Nenhum status cadastrado ainda.</p>
                                <a href="{{ route('dashboard.settings.status-templates') }}"
                                    class="text-primary hover:underline mt-2 inline-block">Gerenciar status</a>
                            </div>
                        @endforelse

                        @if($statuses->count() > 0)
                            <button type="submit"
                                class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2 w-full">Salvar
                                Configurações de Notificações</button>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Código de Pareamento -->
    <div id="pairing-code-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/75">
        <div class="bg-white rounded-lg shadow-2xl max-w-md w-full mx-4 p-6 relative">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-semibold" id="pairing-modal-title">Código de Pareamento</h3>
                <button onclick="closePairingCodeModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="lucide lucide-x">
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

    <!-- Modal para Adicionar Instância -->
    <div id="add-instance-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/75">
        <div class="bg-white rounded-lg shadow-2xl w-full mx-4 p-5 max-h-[90vh] overflow-y-auto whatsapp-modal-content">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-semibold">Nova Instância WhatsApp</h3>
                <button onclick="closeAddInstanceModal()" class="text-gray-400 hover:text-gray-600">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="lucide lucide-x">
                        <path d="M18 6L6 18"></path>
                        <path d="M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <form id="add-instance-form" onsubmit="saveInstance(event)" class="space-y-4">
                @csrf
                <div>
                    <label class="text-sm font-medium mb-1 block">Nome da Instância</label>
                    <input type="text" name="name" required
                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                        placeholder="Ex: Principal (Vendas)">
                </div>
                <div>
                    <label class="text-sm font-medium mb-1 block">Número do WhatsApp *</label>
                    <input type="text" name="phone_number" required
                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                        placeholder="5571999999999"
                        value="{{ auth()->user()->client->whatsapp_phone ?? auth()->user()->client->phone ?? '' }}">
                    <p class="text-xs text-muted-foreground mt-1">Número do WhatsApp cadastrado no seu estabelecimento (pode
                        ser alterado depois)</p>
                </div>
                <input type="hidden" name="api_url" value="">
                <input type="hidden" name="api_key" value="">
                <div class="flex gap-3 pt-4">
                    <button type="button" onclick="closeAddInstanceModal()"
                        class="flex-1 inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors border border-input bg-background hover:bg-accent h-10 px-4">
                        Cancelar
                    </button>
                    <button type="submit"
                        class="flex-1 inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4">
                        Criar Instância
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal para Editar Instância -->
    <div id="edit-instance-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/75">
        <div class="bg-white rounded-lg shadow-2xl w-full mx-4 p-5 max-h-[90vh] overflow-y-auto whatsapp-modal-content">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-semibold">Editar Instância WhatsApp</h3>
                <button onclick="closeEditInstanceModal()" class="text-gray-400 hover:text-gray-600">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="lucide lucide-x">
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
                    <input type="text" name="name" id="edit-instance-name" required
                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                        placeholder="Ex: Principal (Vendas)">
                </div>
                <input type="hidden" name="api_url" id="edit-instance-api-url">
                <input type="hidden" name="api_token" id="edit-instance-api-token">
                <div>
                    <label class="text-sm font-medium mb-1 block">Número do WhatsApp</label>
                    <input type="text" name="phone_number" id="edit-instance-phone"
                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm phone-input"
                        placeholder="71999999999">
                    <p class="text-xs text-muted-foreground mt-1">Digite apenas o DDD e o número (ex: 71999999999). O
                        sistema adiciona automaticamente o código do país (55).</p>
                </div>
                <div class="flex gap-3 pt-4">
                    <button type="button" onclick="closeEditInstanceModal()"
                        class="flex-1 inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors border border-input bg-background hover:bg-accent h-10 px-4">
                        Cancelar
                    </button>
                    <button type="submit"
                        class="flex-1 inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4">
                        Atualizar
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/pages/whatsapp.css') }}?v={{ time() }}">
        <style>
            .tab-button.active {
                background-color: hsl(var(--background));
                color: hsl(var(--foreground));
                box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            }

            /* Modais devem estar ocultos por padrão */
            #pairing-code-modal,
            #add-instance-modal,
            #edit-instance-modal {
                display: none;
                backdrop-filter: blur(4px);
            }

            /* Modais visíveis quando não têm classe hidden */
            #pairing-code-modal:not(.hidden),
            #add-instance-modal:not(.hidden),
            #edit-instance-modal:not(.hidden) {
                display: flex;
                background-color: rgba(0, 0, 0, 0.75) !important;
                backdrop-filter: blur(4px);
            }

            /* Garantir que o conteúdo do modal tenha z-index maior */
            #pairing-code-modal>div,
            #add-instance-modal>div,
            #edit-instance-modal>div {
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

            document.addEventListener('DOMContentLoaded', async function () {
                const tabs = document.querySelectorAll('.tab-button');
                const tabContents = document.querySelectorAll('.tab-content');

                // Gerenciamento de abas
                tabs.forEach(tab => {
                    tab.addEventListener('click', function () {
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

                // Limpar intervalos quando sair da página
                window.addEventListener('beforeunload', () => {
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
                    // Verifica a cada 60 segundos (reduzido para evitar 429)
                    globalStatusPollingInterval = setInterval(() => {
                        const instances = document.querySelectorAll('.instance-card');
                        instances.forEach(card => {
                            const instanceId = card.getAttribute('data-instance-id');
                            if (instanceId) {
                                updateInstanceStatus(instanceId);
                            }
                        });
                    }, 60000);
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

                            const response = await fetch(`{{ route("dashboard.settings.whatsapp.instances.connect", ":id") }}`.replace(':id', instanceId), {
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
                                    const instanceResponse = await fetch(`{{ route("dashboard.settings.whatsapp.instances.show", ":id") }}`.replace(':id', instanceId));
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

                            // 1. Buscar dados da instância
                            const instanceResponse = await fetch(`{{ route("dashboard.settings.whatsapp.instances.show", ":id") }}`.replace(':id', instanceId));
                            const instance = await instanceResponse.json();

                            // 2. Enviar comando de desconexão para o Node.js (Railway)
                            if (instance.api_url) {
                                try {
                                    console.log('🔴 Enviando comando de desconexão para:', instance.api_url);
                                    const disconnectResponse = await fetch(`${instance.api_url}/api/whatsapp/disconnect`, {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-Olika-Token': instance.api_token || '{{ $whatsappApiKey }}'
                                        }
                                    });

                                    if (disconnectResponse.ok) {
                                        console.log('✅ Desconectado do Node.js com sucesso');
                                    } else {
                                        console.warn('⚠️ Falha ao desconectar do Node.js, status:', disconnectResponse.status);
                                    }
                                } catch (nodeError) {
                                    console.error('❌ Erro ao desconectar do Node.js:', nodeError);
                                    // Continua para atualizar o banco mesmo se falhar no Node.js
                                }
                            }

                            // 3. Atualizar status no banco
                            const response = await fetch(`{{ route("dashboard.settings.whatsapp.instances.update", ":id") }}`.replace(':id', instanceId), {
                                method: 'PUT',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken,
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({ status: 'DISCONNECTED' })
                            });

                            if (response.ok) {
                                alert('✅ Instância desconectada com sucesso!');
                                location.reload(); // Recarregar para atualizar status
                            } else {
                                alert('❌ Erro ao atualizar status no banco');
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
                            const response = await fetch(`{{ route("dashboard.settings.whatsapp.instances.show", ":id") }}`.replace(':id', instanceId));
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
                                    fetch(`{{ route("dashboard.settings.whatsapp.instances.show", ":id") }}`.replace(':id', instanceId))
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
                    fetch(`{{ route("dashboard.settings.whatsapp.instances.show", ":id") }}`.replace(':id', instanceId))
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

                        const response = await fetch('{{ route("dashboard.settings.whatsapp.instances.store") }}', {
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
                        const response = await fetch(`{{ route("dashboard.settings.whatsapp.instances.show", ":id") }}`.replace(':id', instanceId));
                        if (!response.ok) throw new Error('Erro ao buscar dados da instância');

                        const instance = await response.json();

                        document.getElementById('edit-instance-id').value = instance.id;
                        document.getElementById('edit-instance-name').value = instance.name;
                        document.getElementById('edit-instance-api-url').value = instance.api_url;
                        document.getElementById('edit-instance-api-url-display').textContent = instance.api_url || 'Gerenciada automaticamente pelo sistema';
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

                        const response = await fetch(`{{ route("dashboard.settings.whatsapp.instances.update", ":id") }}`.replace(':id', instanceId), {
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

                    // Processar checkbox de newsletter
                    data.filter_newsletter = formData.has('filter_newsletter') ? 1 : 0;

                    // Verificar se há agendamento
                    const scheduledDate = data.scheduled_date;
                    const scheduledTime = data.scheduled_time;
                    const isScheduled = scheduledDate && scheduledTime;

                    const btn = form.querySelector('button[type="submit"]');
                    const submitText = document.getElementById('campaign-submit-text');
                    const originalText = submitText ? submitText.textContent : btn.innerHTML;

                    if (submitText) {
                        submitText.textContent = isScheduled ? 'Agendando...' : 'Iniciando...';
                    } else {
                        btn.innerHTML = isScheduled ? 'Agendando...' : 'Iniciando...';
                    }
                    btn.disabled = true;

                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                            document.querySelector('input[name="_token"]')?.value;

                        const response = await fetch('{{ route("dashboard.marketing.store") }}', {
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
                            // Limpar seleção de cliente de teste
                            document.getElementById('test_customer_id').innerHTML = '<option value="">Nenhum (enviar para todos do filtro)</option>';
                            document.getElementById('test_customer_search').value = '';
                            document.getElementById('test_customer_results').classList.add('hidden');
                            // Atualizar lista de campanhas (recarregar página por enquanto)
                            location.reload();
                        } else {
                            alert('❌ Erro: ' + (result.message || 'Erro ao criar campanha'));
                        }
                    } catch (error) {
                        console.error('Erro ao criar campanha:', error);
                        alert('❌ Erro ao criar campanha. Tente novamente.');
                    } finally {
                        if (submitText) {
                            submitText.textContent = originalText;
                        } else {
                            btn.innerHTML = originalText;
                        }
                        btn.disabled = false;
                    }
                }

                // Buscar clientes para teste
                let customerSearchTimeout = null;

                function selectTestCustomer(customerId, customerName, customerPhone) {
                    const select = document.getElementById('test_customer_id');
                    const search = document.getElementById('test_customer_search');
                    const results = document.getElementById('test_customer_results');

                    // Limpar opções existentes e adicionar o selecionado
                    select.innerHTML = `<option value="${customerId}" selected>${customerName} - ${customerPhone}</option>`;
                    search.value = `${customerName} - ${customerPhone}`;
                    results.classList.add('hidden');
                }

                // Inicializar event listeners quando a aba de campanhas for aberta
                function initCampaignFormListeners() {
                    const scheduledDate = document.getElementById('scheduled_date');
                    const scheduledTime = document.getElementById('scheduled_time');
                    const testCustomerSearch = document.getElementById('test_customer_search');

                    if (scheduledDate && !scheduledDate.dataset.listenerAdded) {
                        scheduledDate.addEventListener('change', updateSubmitButton);
                        scheduledDate.dataset.listenerAdded = 'true';
                    }

                    if (scheduledTime && !scheduledTime.dataset.listenerAdded) {
                        scheduledTime.addEventListener('change', updateSubmitButton);
                        scheduledTime.dataset.listenerAdded = 'true';
                    }

                    if (testCustomerSearch && !testCustomerSearch.dataset.listenerAdded) {
                        testCustomerSearch.addEventListener('input', function (e) {
                            const query = e.target.value.trim();
                            const resultsDiv = document.getElementById('test_customer_results');
                            const select = document.getElementById('test_customer_id');

                            if (query.length < 2) {
                                resultsDiv?.classList.add('hidden');
                                return;
                            }

                            clearTimeout(customerSearchTimeout);
                            customerSearchTimeout = setTimeout(async () => {
                                try {
                                    const response = await fetch(`/dashboard/pdv/search-customers?q=${encodeURIComponent(query)}`, {
                                        headers: {
                                            'Accept': 'application/json',
                                            'X-Requested-With': 'XMLHttpRequest'
                                        }
                                    });

                                    if (!response.ok) return;

                                    const data = await response.json();
                                    const customers = data.customers || [];

                                    if (customers.length === 0) {
                                        resultsDiv.innerHTML = '<div class="p-3 text-sm text-muted-foreground text-center">Nenhum cliente encontrado</div>';
                                        resultsDiv.classList.remove('hidden');
                                        return;
                                    }

                                    resultsDiv.innerHTML = customers.map(customer => `
                                <div class="p-2 hover:bg-muted cursor-pointer border-b last:border-b-0" onclick="selectTestCustomer(${customer.id}, '${(customer.name || '').replace(/'/g, "\\'")}', '${(customer.phone || '').replace(/'/g, "\\'")}')">
                                    <div class="font-medium text-sm">${customer.name || 'Sem nome'}</div>
                                    <div class="text-xs text-muted-foreground">${customer.phone || ''} ${customer.email ? '• ' + customer.email : ''}</div>
                                </div>
                            `).join('');
                                    resultsDiv.classList.remove('hidden');
                                } catch (error) {
                                    console.error('Erro ao buscar clientes:', error);
                                }
                            }, 300);
                        });
                        testCustomerSearch.dataset.listenerAdded = 'true';
                    }
                }

                // Inicializar quando a aba de campanhas for clicada
                document.addEventListener('DOMContentLoaded', function () {
                    const campaignsTab = document.querySelector('[data-tab="campaigns"]');
                    if (campaignsTab) {
                        campaignsTab.addEventListener('click', function () {
                            setTimeout(initCampaignFormListeners, 100);
                        });
                    }
                    // Também inicializar se a aba já estiver ativa
                    if (campaignsTab?.classList.contains('active')) {
                        setTimeout(initCampaignFormListeners, 100);
                    }
                });

                function updateSubmitButton() {
                    const date = document.getElementById('scheduled_date')?.value;
                    const time = document.getElementById('scheduled_time')?.value;
                    const submitText = document.getElementById('campaign-submit-text');

                    if (submitText) {
                        if (date && time) {
                            const [year, month, day] = date.split('-');
                            const [hours, minutes] = time.split(':');
                            const scheduledDate = new Date(year, month - 1, day, hours, minutes);
                            const now = new Date();

                            if (scheduledDate > now) {
                                submitText.textContent = `Agendar para ${day}/${month}/${year} às ${hours}:${minutes}`;
                            } else {
                                submitText.textContent = 'Iniciar Campanha';
                            }
                        } else {
                            submitText.textContent = 'Iniciar Campanha';
                        }
                    }
                }

                // Fechar resultados ao clicar fora
                document.addEventListener('click', function (e) {
                    const results = document.getElementById('test_customer_results');
                    const search = document.getElementById('test_customer_search');

                    if (results && !results.contains(e.target) && e.target !== search) {
                        results.classList.add('hidden');
                    }
                });

                // Buscar Campanhas (executado ao carregar ou trocar de aba)
                async function fetchCampaigns() {
                    try {
                        const response = await fetch('{{ route("dashboard.marketing.index") }}');
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

                        tbody.innerHTML = campaigns.map(c => {
                            const scheduledInfo = c.scheduled_at ?
                                `<div class="text-xs text-purple-600 mt-1">📅 ${new Date(c.scheduled_at).toLocaleString('pt-BR')}</div>` : '';
                            const testInfo = c.test_customer_id ?
                                `<div class="text-xs text-blue-600 mt-1">🧪 Teste: Cliente ID ${c.test_customer_id}</div>` : '';
                            const filtersInfo = [];
                            if (c.filter_newsletter) filtersInfo.push('📧 Newsletter');
                            if (c.filter_customer_type === 'new_customers') filtersInfo.push('🆕 Leads');
                            if (c.filter_customer_type === 'existing_customers') filtersInfo.push('👥 Clientes');
                            const filtersText = filtersInfo.length > 0 ? `<div class="text-xs text-muted-foreground mt-1">${filtersInfo.join(' • ')}</div>` : '';

                            return `
                    <tr class="border-b transition-colors hover:bg-muted/50">
                        <td class="p-4 font-medium">
                            <div>${c.name}</div>
                            ${scheduledInfo}
                            ${testInfo}
                            ${filtersText}
                        </td>
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
                `;
                        }).join('');

                    } catch (error) {
                        console.error('Erro ao buscar campanhas:', error);
                    }
                }

                function getStatusBadgeClass(status) {
                    switch (status) {
                        case 'completed': return 'border-transparent bg-green-100 text-green-800 hover:bg-green-200';
                        case 'processing': return 'border-transparent bg-blue-100 text-blue-800 hover:bg-blue-200 animate-pulse';
                        case 'pending': return 'border-transparent bg-yellow-100 text-yellow-800 hover:bg-yellow-200';
                        case 'scheduled': return 'border-transparent bg-purple-100 text-purple-800 hover:bg-purple-200';
                        default: return 'border-transparent bg-gray-100 text-gray-800 hover:bg-gray-200';
                    }
                }

                function formatStatus(status) {
                    const map = {
                        'pending': 'Pendente',
                        'processing': 'Enviando',
                        'completed': 'Concluída',
                        'scheduled': 'Agendada',
                        'paused': 'Pausada',
                        'cancelled': 'Cancelada'
                    };
                    return map[status] || status;
                }

                // Fechar modais ao clicar fora e garantir que estão ocultos ao carregar
                document.addEventListener('DOMContentLoaded', function () {
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
                        pairingModal.addEventListener('click', function (e) {
                            if (e.target === this) closePairingCodeModal();
                        });
                    }

                    if (addModal) {
                        addModal.classList.add('hidden');
                        addModal.addEventListener('click', function (e) {
                            if (e.target === this) closeAddInstanceModal();
                        });
                    }

                    if (editModal) {
                        editModal.classList.add('hidden');
                        editModal.addEventListener('click', function (e) {
                            if (e.target === this) closeEditInstanceModal();
                        });
                    }

                    // Normalização automática de números de telefone
                    function normalizePhoneInput(input) {
                        if (!input) return;

                        let value = input.value.replace(/\D/g, ''); // Remove caracteres não numéricos

                        // Se começa com 55, remove (usuário não precisa digitar)
                        if (value.startsWith('55')) {
                            value = value.substring(2);
                        }

                        input.value = value;

                        // Validação visual
                        if (value.length >= 10 && value.length <= 11) {
                            input.classList.remove('border-red-500');
                            input.classList.add('border-green-300');
                        } else if (value.length > 0) {
                            input.classList.remove('border-green-300');
                            input.classList.add('border-red-300');
                        } else {
                            input.classList.remove('border-red-300', 'border-green-300');
                        }
                    }

                    // Aplicar normalização em todos os inputs de telefone
                    document.querySelectorAll('.phone-input').forEach(input => {
                        input.addEventListener('input', function () {
                            normalizePhoneInput(this);
                        });

                        input.addEventListener('blur', function () {
                            normalizePhoneInput(this);
                        });

                        // Normalizar valor inicial
                        normalizePhoneInput(input);
                    });

                    // Normalizar antes de enviar formulário de notificações
                    const adminPhoneForm = document.querySelector('form[action*="whatsapp"]');
                    if (adminPhoneForm) {
                        adminPhoneForm.addEventListener('submit', function (e) {
                            const phoneInput = document.getElementById('admin_notification_phone');
                            if (phoneInput && phoneInput.value) {
                                let value = phoneInput.value.replace(/\D/g, '');
                                if (!value.startsWith('55') && value.length >= 10) {
                                    value = '55' + value;
                                }
                                // Criar input hidden com valor normalizado
                                const hiddenInput = document.createElement('input');
                                hiddenInput.type = 'hidden';
                                hiddenInput.name = 'admin_notification_phone';
                                hiddenInput.value = value;
                                this.appendChild(hiddenInput);
                                phoneInput.name = ''; // Remover name do input original para não enviar valor não normalizado
                            }
                        });
                    }

                    // Normalizar números em modais de instância
                    const addInstanceForm = document.getElementById('add-instance-form');
                    if (addInstanceForm) {
                        addInstanceForm.addEventListener('submit', function (e) {
                            const phoneInput = document.getElementById('add-instance-phone');
                            if (phoneInput && phoneInput.value) {
                                let value = phoneInput.value.replace(/\D/g, '');
                                if (!value.startsWith('55') && value.length >= 10) {
                                    value = '55' + value;
                                }
                                phoneInput.value = value;
                            }
                        });
                    }

                    const editInstanceForm = document.getElementById('edit-instance-form');
                    if (editInstanceForm) {
                        editInstanceForm.addEventListener('submit', function (e) {
                            const phoneInput = document.getElementById('edit-instance-phone');
                            if (phoneInput && phoneInput.value) {
                                let value = phoneInput.value.replace(/\D/g, '');
                                if (!value.startsWith('55') && value.length >= 10) {
                                    value = '55' + value;
                                }
                                phoneInput.value = value;
                            }
                        });
                    }
                });
        </script>
    @endpush
@endsection