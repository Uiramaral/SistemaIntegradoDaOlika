@extends('layouts.admin')

@section('title', isset($campaign) ? 'Editar Campanha' : 'Nova Campanha')
@section('page_title', isset($campaign) ? 'Editar Campanha' : 'Nova Campanha de Marketing')
@section('page_subtitle', 'Configure mensagens personalizadas com IA para seus clientes')

@section('content')
<div class="max-w-5xl mx-auto">
    <form method="POST" action="{{ isset($campaign) ? route('dashboard.marketing.update', $campaign) : route('dashboard.marketing.store') }}" class="space-y-6" id="campaign-form">
        @csrf
        @if(isset($campaign))
            @method('PUT')
        @endif

        <!-- Carregar Campanha Salva -->
        <div class="bg-card rounded-xl border border-border shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-foreground flex items-center gap-2">
                    <i data-lucide="folder-open" class="h-5 w-5 text-primary"></i>
                    Campanha
                </h3>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-foreground mb-2">Carregar campanha existente</label>
                    <select id="load-campaign" class="w-full px-3 py-2 rounded-md border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary">
                        <option value="">Nova campanha...</option>
                        @foreach($savedCampaigns ?? [] as $saved)
                            <option value="{{ $saved->id }}" data-campaign='@json($saved)'>{{ $saved->name }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-muted-foreground mt-1">Selecione uma campanha salva para editar</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-foreground mb-2">Nome da Campanha *</label>
                    <input type="text" name="name" id="campaign-name" value="{{ old('name', $campaign->name ?? '') }}" required
                           class="w-full px-3 py-2 rounded-md border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                           placeholder="Ex: Promoção de Fim de Ano">
                    @error('name')
                        <p class="text-sm text-destructive mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Card Mensagem -->
        <div class="bg-card rounded-xl border border-border shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-foreground flex items-center gap-2">
                    <i data-lucide="message-square" class="h-5 w-5 text-primary"></i>
                    Mensagem Base
                </h3>
                <span class="text-xs px-2 py-1 rounded-full bg-primary/10 text-primary font-medium">IA Ativa</span>
            </div>

            <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                <p class="text-sm text-blue-700">💡 A IA Gemini personalizará automaticamente esta mensagem para cada cliente</p>
            </div>

            <div class="space-y-4">
                <!-- Template da Mensagem -->
                <div>
                    <label class="block text-sm font-medium text-foreground mb-2">
                        Template da Mensagem <span class="text-destructive">*</span>
                    </label>
                    <textarea name="message_template_a" 
                              id="message-template"
                              rows="5"
                              required
                              placeholder="Clique nas variáveis abaixo para adicionar ao template..."
                              class="w-full px-4 py-2 border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary transition font-mono text-sm resize-none">{{ old('message_template_a', $campaign->message_template_a ?? '') }}</textarea>
                    <div class="flex items-center justify-between mt-1">
                        <p class="text-xs text-muted-foreground">Caracteres: <span id="char-count">0</span></p>
                        @error('message_template_a')
                            <p class="text-sm text-destructive">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Variáveis Clicáveis -->
                <div class="bg-gradient-to-r from-primary/5 to-purple-50 rounded-lg p-4 border border-primary/20">
                    <div class="flex items-center gap-2 mb-3">
                        <i data-lucide="sparkles" class="h-4 w-4 text-primary"></i>
                        <p class="text-sm font-semibold text-foreground">Variáveis disponíveis - Clique para adicionar</p>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                        @foreach($variables as $var => $desc)
                            <button type="button" 
                                    onclick="insertVariable('{{ $var }}')"
                                    class="flex items-center gap-2 px-3 py-2 bg-white hover:bg-primary hover:text-white border border-border hover:border-primary rounded-lg transition-all group cursor-pointer text-left">
                                <code class="text-xs font-bold group-hover:text-white">{{ $var }}</code>
                                <span class="text-xs text-muted-foreground group-hover:text-white/80 truncate">{{ $desc }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Card Filtros de Audiência -->
        <div class="bg-card rounded-xl border border-border shadow-sm p-6">
            <h3 class="text-lg font-semibold text-foreground mb-4 flex items-center gap-2">
                <i data-lucide="users" class="h-5 w-5 text-primary"></i>
                Filtros de Audiência
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Mínimo de pedidos -->
                <div>
                    <label class="block text-sm font-medium text-foreground mb-2">Mínimo de pedidos</label>
                    <input type="number" name="filter_min_orders" value="{{ old('filter_min_orders') }}" min="0" placeholder="Ex: 1"
                           class="w-full px-3 py-2 border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary transition">
                    <p class="text-xs text-muted-foreground mt-1">Clientes com pelo menos X pedidos</p>
                </div>

                <!-- Máximo de pedidos -->
                <div>
                    <label class="block text-sm font-medium text-foreground mb-2">Máximo de pedidos</label>
                    <input type="number" name="filter_max_orders" value="{{ old('filter_max_orders') }}" min="0" placeholder="Ex: 3"
                           class="w-full px-3 py-2 border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary transition">
                    <p class="text-xs text-muted-foreground mt-1">Clientes com no máximo X pedidos</p>
                </div>

                <!-- Tem cashback -->
                <div>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="filter_has_cashback" value="1" {{ old('filter_has_cashback') ? 'checked' : '' }}
                               class="rounded border-border text-primary focus:ring-primary">
                        <span class="text-sm font-medium text-foreground">Somente com cashback</span>
                    </label>
                    <p class="text-xs text-muted-foreground mt-1 ml-6">Apenas clientes com saldo</p>
                </div>

                <!-- Cashback mínimo -->
                <div>
                    <label class="block text-sm font-medium text-foreground mb-2">Cashback mínimo (R$)</label>
                    <input type="number" name="filter_min_cashback" value="{{ old('filter_min_cashback') }}" min="0" step="0.01" placeholder="Ex: 10.00"
                           class="w-full px-3 py-2 border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary transition">
                    <p class="text-xs text-muted-foreground mt-1">Cashback mínimo disponível</p>
                </div>

                <!-- Sem pedidos nos últimos X dias -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-foreground mb-2">Sem pedidos nos últimos (dias)</label>
                    <input type="number" name="filter_no_orders_days" value="{{ old('filter_no_orders_days') }}" min="0" placeholder="Ex: 30"
                           class="w-full px-3 py-2 border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary transition">
                    <p class="text-xs text-muted-foreground mt-1">Reativar clientes inativos</p>
                </div>
            </div>

            @if(isset($stats))
                <div class="mt-4 p-4 bg-gradient-to-r from-primary/10 to-purple-50 rounded-lg border border-primary/20">
                    <p class="text-sm font-medium text-foreground mb-3 flex items-center gap-2">
                        <i data-lucide="bar-chart-3" class="h-4 w-4"></i>
                        Estatísticas rápidas
                    </p>
                    <div class="grid grid-cols-3 gap-4 text-center">
                        <div>
                            <div class="text-3xl font-bold text-primary">{{ $stats['total_customers'] }}</div>
                            <div class="text-xs text-muted-foreground mt-1">Total de clientes</div>
                        </div>
                        <div>
                            <div class="text-3xl font-bold text-green-600">{{ $stats['with_cashback'] }}</div>
                            <div class="text-xs text-muted-foreground mt-1">Com cashback</div>
                        </div>
                        <div>
                            <div class="text-3xl font-bold text-blue-600">{{ $stats['with_orders'] }}</div>
                            <div class="text-xs text-muted-foreground mt-1">Com pedidos</div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Card Agendamento e Envio -->
        <div class="bg-card rounded-xl border border-border shadow-sm p-6">
            <h3 class="text-lg font-semibold text-foreground mb-4 flex items-center gap-2">
                <i data-lucide="calendar-clock" class="h-5 w-5 text-primary"></i>
                Agendamento e Envio
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Tipo de Envio -->
                <div class="space-y-3">
                    <label class="block text-sm font-medium text-foreground mb-3">Quando enviar?</label>
                    
                    <label class="flex items-center gap-3 p-3 border-2 border-border rounded-lg cursor-pointer hover:border-primary hover:bg-primary/5 transition has-[:checked]:border-primary has-[:checked]:bg-primary/10">
                        <input type="radio" name="send_type" value="immediate" checked class="text-primary focus:ring-primary">
                        <div>
                            <span class="font-medium text-foreground">Enviar imediatamente</span>
                            <p class="text-xs text-muted-foreground">Iniciar envio após salvar</p>
                        </div>
                    </label>

                    <label class="flex items-center gap-3 p-3 border-2 border-border rounded-lg cursor-pointer hover:border-primary hover:bg-primary/5 transition has-[:checked]:border-primary has-[:checked]:bg-primary/10">
                        <input type="radio" name="send_type" value="test" class="text-primary focus:ring-primary">
                        <div>
                            <span class="font-medium text-foreground">Enviar teste</span>
                            <p class="text-xs text-muted-foreground">Enviar para número de notificação admin</p>
                        </div>
                    </label>
                    
                    <label class="flex items-center gap-3 p-3 border-2 border-border rounded-lg cursor-pointer hover:border-primary hover:bg-primary/5 transition has-[:checked]:border-primary has-[:checked]:bg-primary/10">
                        <input type="radio" name="send_type" value="specific_customer" class="text-primary focus:ring-primary">
                        <div>
                            <span class="font-medium text-foreground">Enviar para cliente específico</span>
                            <p class="text-xs text-muted-foreground">Escolha um cliente para testar</p>
                        </div>
                    </label>

                    <label class="flex items-center gap-3 p-3 border-2 border-border rounded-lg cursor-pointer hover:border-primary hover:bg-primary/5 transition has-[:checked]:border-primary has-[:checked]:bg-primary/10">
                        <input type="radio" name="send_type" value="scheduled" class="text-primary focus:ring-primary">
                        <div>
                            <span class="font-medium text-foreground">Agendar envio</span>
                            <p class="text-xs text-muted-foreground">Programar data e hora</p>
                        </div>
                    </label>
                </div>

                <!-- Configurações de Agendamento -->
                <div>
                    <!-- Seleção de Cliente Específico -->
                    <div id="specific-customer-config" class="space-y-3 hidden">
                        <label class="block text-sm font-medium text-foreground mb-2">Selecione o cliente</label>
                        <select name="specific_customer_id" id="specific_customer_select" class="w-full px-3 py-2 border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary transition">
                            <option value="">Escolha um cliente...</option>
                        </select>
                        <p class="text-xs text-muted-foreground">Digite para buscar por nome ou telefone</p>
                    </div>
                
                    <div id="scheduled-config" class="space-y-4 hidden">
                        <div>
                            <label class="block text-sm font-medium text-foreground mb-2">Data e Hora</label>
                            <input type="datetime-local" name="scheduled_at" class="w-full px-3 py-2 border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary transition">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-foreground mb-2">Repetir campanha</label>
                            <select name="recurrence" class="w-full px-3 py-2 border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary transition">
                                <option value="once">Uma vez apenas</option>
                                <option value="daily">Diariamente</option>
                                <option value="weekly">Semanalmente</option>
                                <option value="monthly">Mensalmente</option>
                            </select>
                        </div>

                        <div id="weekly-config" class="hidden">
                            <label class="block text-sm font-medium text-foreground mb-2">Dias da semana</label>
                            <div class="grid grid-cols-4 gap-2">
                                @foreach(['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'] as $index => $day)
                                    <label class="flex items-center gap-2 p-2 border border-border rounded cursor-pointer hover:bg-muted transition has-[:checked]:bg-primary has-[:checked]:text-white has-[:checked]:border-primary">
                                        <input type="checkbox" name="weekdays[]" value="{{ $index }}" class="hidden">
                                        <span class="text-xs font-medium">{{ $day }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Intervalo -->
                    <div class="{{ isset($campaign) ? '' : 'mt-4' }}">
                        <label class="block text-sm font-medium text-foreground mb-2">Intervalo entre envios (segundos) *</label>
                        <input type="number" name="interval_seconds" value="{{ old('interval_seconds', $campaign->interval_seconds ?? 5) }}" required min="3" max="60"
                               class="w-full px-3 py-2 border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary transition">
                        <p class="text-xs text-muted-foreground mt-1">⚠️ Recomendado: 5-10 segundos</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botões -->
        <div class="flex items-center gap-3">
            <a href="{{ route('dashboard.marketing.index') }}" class="px-6 py-3 border-2 border-border hover:bg-muted rounded-lg font-medium transition">
                Cancelar
            </a>
            <button type="button" onclick="saveDraft()" class="px-6 py-3 bg-secondary text-secondary-foreground hover:bg-secondary/80 rounded-lg font-medium transition flex items-center gap-2">
                <i data-lucide="save" class="h-4 w-4"></i>
                Salvar Rascunho
            </button>
            <button type="submit" class="flex-1 bg-gradient-to-r from-primary to-purple-600 hover:from-primary/90 hover:to-purple-700 text-white px-6 py-3 rounded-lg font-semibold transition-all duration-200 hover:shadow-lg flex items-center justify-center gap-2">
                <i data-lucide="send" class="h-5 w-5"></i>
                <span id="submit-text">{{ isset($campaign) ? 'Atualizar Campanha' : 'Criar e Enviar' }}</span>
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
// Inserir variável no template
function insertVariable(variable) {
    const textarea = document.getElementById('message-template');
    const cursorPos = textarea.selectionStart;
    const textBefore = textarea.value.substring(0, cursorPos);
    const textAfter = textarea.value.substring(cursorPos);
    
    textarea.value = textBefore + variable + textAfter;
    textarea.focus();
    textarea.selectionStart = textarea.selectionEnd = cursorPos + variable.length;
    
    updateCharCount();
}

// Contador de caracteres
function updateCharCount() {
    const textarea = document.getElementById('message-template');
    const count = document.getElementById('char-count');
    count.textContent = textarea.value.length;
}

document.getElementById('message-template').addEventListener('input', updateCharCount);
updateCharCount();

// Toggle configurações de agendamento
document.querySelectorAll('input[name="send_type"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const scheduledConfig = document.getElementById('scheduled-config');
        const specificCustomerConfig = document.getElementById('specific-customer-config');
        const submitText = document.getElementById('submit-text');
        
        // Esconder todos primeiro
        scheduledConfig.classList.add('hidden');
        specificCustomerConfig.classList.add('hidden');
        
        if (this.value === 'scheduled') {
            scheduledConfig.classList.remove('hidden');
            submitText.textContent = 'Agendar Campanha';
        } else if (this.value === 'test') {
            submitText.textContent = 'Enviar Teste';
        } else if (this.value === 'specific_customer') {
            specificCustomerConfig.classList.remove('hidden');
            submitText.textContent = 'Enviar para Cliente';
            loadCustomers(); // Carregar clientes quando selecionado
        } else {
            submitText.textContent = '{{ isset($campaign) ? "Atualizar e Enviar" : "Criar e Enviar" }}';
        }
    });
});

// Toggle dias da semana para recorrência semanal
document.querySelector('select[name="recurrence"]')?.addEventListener('change', function() {
    const weeklyConfig = document.getElementById('weekly-config');
    if (this.value === 'weekly') {
        weeklyConfig.classList.remove('hidden');
    } else {
        weeklyConfig.classList.add('hidden');
    }
});

// Carregar campanha salva
document.getElementById('load-campaign')?.addEventListener('change', function() {
    if (!this.value) return;
    
    const option = this.options[this.selectedIndex];
    const campaign = JSON.parse(option.dataset.campaign);
    
    // Preencher formulário
    document.getElementById('campaign-name').value = campaign.name;
    document.getElementById('message-template').value = campaign.message_template_a || '';
    
    // Preencher filtros se existirem
    if (campaign.target_filter) {
        const filters = campaign.target_filter;
        if (filters.min_orders) document.querySelector('[name="filter_min_orders"]').value = filters.min_orders;
        if (filters.max_orders) document.querySelector('[name="filter_max_orders"]').value = filters.max_orders;
        if (filters.min_cashback) document.querySelector('[name="filter_min_cashback"]').value = filters.min_cashback;
        if (filters.no_orders_days) document.querySelector('[name="filter_no_orders_days"]').value = filters.no_orders_days;
        if (filters.has_cashback) document.querySelector('[name="filter_has_cashback"]').checked = true;
    }
    
    updateCharCount();
});

// Salvar como rascunho
function saveDraft() {
    const form = document.getElementById('campaign-form');
    const statusInput = document.createElement('input');
    statusInput.type = 'hidden';
    statusInput.name = 'save_as_draft';
    statusInput.value = '1';
    form.appendChild(statusInput);
    form.submit();
}

// Carregar clientes para seleção
let customersLoaded = false;
function loadCustomers() {
    if (customersLoaded) return; // Só carregar uma vez
    
    const select = document.getElementById('specific_customer_select');
    select.innerHTML = '<option value="">Carregando...</option>';
    
    fetch('/api/customers')
        .then(response => response.json())
        .then(data => {
            select.innerHTML = '<option value="">Escolha um cliente...</option>';
            
            if (data.customers && data.customers.length > 0) {
                data.customers.forEach(customer => {
                    const option = document.createElement('option');
                    option.value = customer.id;
                    option.textContent = `${customer.name} - ${customer.phone || 'Sem telefone'}`;
                    select.appendChild(option);
                });
                customersLoaded = true;
            } else {
                select.innerHTML = '<option value="">Nenhum cliente encontrado</option>';
            }
        })
        .catch(error => {
            console.error('Erro ao carregar clientes:', error);
            select.innerHTML = '<option value="">Erro ao carregar clientes</option>';
        });
}
</script>
@endpush
@endsection
