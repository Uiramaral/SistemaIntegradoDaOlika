@extends('layouts.admin')

@section('title', isset($campaign) ? 'Editar Campanha' : 'Nova Campanha')
@section('page_title', isset($campaign) ? 'Editar Campanha' : 'Nova Campanha')
@section('page_subtitle', 'Configure sua campanha de marketing WhatsApp com IA')

@section('content')
<form method="POST" action="{{ isset($campaign) ? route('dashboard.marketing.update', $campaign) : route('dashboard.marketing.store') }}" class="space-y-6">
    @csrf
    @if(isset($campaign))
        @method('PUT')
    @endif

    <!-- Card Principal -->
    <div class="bg-card rounded-xl border border-border shadow-sweetspot p-6">
        <h3 class="text-lg font-semibold text-foreground mb-4 flex items-center gap-2">
            <i data-lucide="info" class="h-5 w-5 text-primary-600"></i>
            Informações da Campanha
        </h3>

        <div class="space-y-4">
            <!-- Nome -->
            <div>
                <label class="block text-sm font-medium text-foreground mb-2">
                    Nome da Campanha <span class="text-destructive">*</span>
                </label>
                <input type="text" 
                       name="name" 
                       value="{{ old('name', $campaign->name ?? '') }}"
                       required
                       placeholder="Ex: Promoção de Fim de Ano"
                       class="w-full px-4 py-2 border border-border rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition">
                @error('name')
                    <p class="text-destructive text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Descrição -->
            <div>
                <label class="block text-sm font-medium text-foreground mb-2">
                    Descrição (opcional)
                </label>
                <textarea name="description" 
                          rows="2"
                          placeholder="Descreva o objetivo desta campanha..."
                          class="w-full px-4 py-2 border border-border rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition">{{ old('description', $campaign->description ?? '') }}</textarea>
            </div>
        </div>
    </div>

    <!-- Card Template de Mensagem -->
    <div class="bg-card rounded-xl border border-border shadow-sweetspot p-6">
        <div class="flex items-start justify-between mb-4">
            <div>
                <h3 class="text-lg font-semibold text-foreground flex items-center gap-2">
                    <i data-lucide="message-square" class="h-5 w-5 text-primary-600"></i>
                    Mensagem Base
                </h3>
                <p class="text-sm text-muted-foreground mt-1">
                    A IA Gemini vai personalizar automaticamente esta mensagem para cada cliente
                </p>
            </div>
            <div class="bg-primary-50 px-3 py-1 rounded-full text-xs font-medium text-primary-600 flex items-center gap-1">
                <i data-lucide="sparkles" class="h-3 w-3"></i>
                IA Ativa
            </div>
        </div>

        <div class="space-y-4">
            <!-- Template A -->
            <div>
                <label class="block text-sm font-medium text-foreground mb-2">
                    Template da Mensagem <span class="text-destructive">*</span>
                </label>
                <textarea name="message_template_a" 
                          rows="4"
                          required
                          placeholder="Olá {{primeiro_nome}}! Você tem {{cashback}} de cashback disponível. Use agora em {{estabelecimento}}!"
                          class="w-full px-4 py-2 border border-border rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition font-mono text-sm">{{ old('message_template_a', $campaign->message_template_a ?? '') }}</textarea>
                @error('message_template_a')
                    <p class="text-destructive text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Variáveis Disponíveis -->
            <div class="bg-muted/50 rounded-lg p-4">
                <p class="text-sm font-medium text-foreground mb-2">📝 Variáveis disponíveis:</p>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    @foreach($variables as $var => $desc)
                        <div class="flex items-center gap-2">
                            <code class="bg-primary-100 text-primary-700 px-2 py-1 rounded">{{ $var }}</code>
                            <span class="text-muted-foreground">{{ $desc }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Card Filtros de Audiência -->
    <div class="bg-card rounded-xl border border-border shadow-sweetspot p-6">
        <h3 class="text-lg font-semibold text-foreground mb-4 flex items-center gap-2">
            <i data-lucide="users" class="h-5 w-5 text-primary-600"></i>
            Filtros de Audiência
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Mínimo de pedidos -->
            <div>
                <label class="block text-sm font-medium text-foreground mb-2">
                    Mínimo de pedidos
                </label>
                <input type="number" 
                       name="filter_min_orders" 
                       value="{{ old('filter_min_orders') }}"
                       min="0"
                       placeholder="Ex: 1"
                       class="w-full px-4 py-2 border border-border rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition">
                <p class="text-xs text-muted-foreground mt-1">Clientes com pelo menos X pedidos</p>
            </div>

            <!-- Máximo de pedidos -->
            <div>
                <label class="block text-sm font-medium text-foreground mb-2">
                    Máximo de pedidos
                </label>
                <input type="number" 
                       name="filter_max_orders" 
                       value="{{ old('filter_max_orders') }}"
                       min="0"
                       placeholder="Ex: 3"
                       class="w-full px-4 py-2 border border-border rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition">
                <p class="text-xs text-muted-foreground mt-1">Clientes com no máximo X pedidos</p>
            </div>

            <!-- Tem cashback -->
            <div>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" 
                           name="filter_has_cashback" 
                           value="1"
                           {{ old('filter_has_cashback') ? 'checked' : '' }}
                           class="rounded border-border text-primary-600 focus:ring-primary-500">
                    <span class="text-sm font-medium text-foreground">Somente com cashback</span>
                </label>
                <p class="text-xs text-muted-foreground mt-1 ml-6">Apenas clientes com saldo de cashback</p>
            </div>

            <!-- Cashback mínimo -->
            <div>
                <label class="block text-sm font-medium text-foreground mb-2">
                    Cashback mínimo (R$)
                </label>
                <input type="number" 
                       name="filter_min_cashback" 
                       value="{{ old('filter_min_cashback') }}"
                       min="0"
                       step="0.01"
                       placeholder="Ex: 10.00"
                       class="w-full px-4 py-2 border border-border rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition">
                <p class="text-xs text-muted-foreground mt-1">Cashback mínimo disponível</p>
            </div>

            <!-- Sem pedidos nos últimos X dias -->
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-foreground mb-2">
                    Sem pedidos nos últimos (dias)
                </label>
                <input type="number" 
                       name="filter_no_orders_days" 
                       value="{{ old('filter_no_orders_days') }}"
                       min="0"
                       placeholder="Ex: 30"
                       class="w-full px-4 py-2 border border-border rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition">
                <p class="text-xs text-muted-foreground mt-1">Reativar clientes inativos (deixe vazio para ignorar)</p>
            </div>
        </div>

        @if(isset($stats))
            <div class="mt-4 p-4 bg-primary-50 rounded-lg">
                <p class="text-sm font-medium text-foreground mb-2">📊 Estatísticas rápidas:</p>
                <div class="grid grid-cols-3 gap-4 text-center">
                    <div>
                        <div class="text-2xl font-bold text-primary-600">{{ $stats['total_customers'] }}</div>
                        <div class="text-xs text-muted-foreground">Total de clientes</div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-success">{{ $stats['with_cashback'] }}</div>
                        <div class="text-xs text-muted-foreground">Com cashback</div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-blue-600">{{ $stats['with_orders'] }}</div>
                        <div class="text-xs text-muted-foreground">Com pedidos</div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Card Configurações de Envio -->
    <div class="bg-card rounded-xl border border-border shadow-sweetspot p-6">
        <h3 class="text-lg font-semibold text-foreground mb-4 flex items-center gap-2">
            <i data-lucide="send" class="h-5 w-5 text-primary-600"></i>
            Configurações de Envio
        </h3>

        <div class="space-y-4">
            <!-- Intervalo entre envios -->
            <div>
                <label class="block text-sm font-medium text-foreground mb-2">
                    Intervalo entre envios (segundos) <span class="text-destructive">*</span>
                </label>
                <input type="number" 
                       name="interval_seconds" 
                       value="{{ old('interval_seconds', $campaign->interval_seconds ?? 5) }}"
                       required
                       min="3"
                       max="300"
                       class="w-full px-4 py-2 border border-border rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition">
                <p class="text-xs text-muted-foreground mt-1">⚠️ Recomendado: 5-10 segundos para evitar bloqueios</p>
            </div>

            <!-- Agendar ou enviar agora -->
            <div class="space-y-3">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" 
                           name="send_type" 
                           value="immediate"
                           checked
                           class="border-border text-primary-600 focus:ring-primary-500">
                    <span class="text-sm font-medium text-foreground">Enviar imediatamente</span>
                </label>

                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" 
                           name="send_type" 
                           value="scheduled"
                           class="border-border text-primary-600 focus:ring-primary-500">
                    <span class="text-sm font-medium text-foreground">Agendar envio</span>
                </label>

                <div id="schedule-input" class="ml-6 hidden">
                    <input type="datetime-local" 
                           name="scheduled_at" 
                           class="w-full px-4 py-2 border border-border rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition">
                </div>
            </div>
        </div>
    </div>

    <!-- Botões -->
    <div class="flex items-center gap-3">
        <a href="{{ route('dashboard.marketing.index') }}" 
           class="px-6 py-3 border border-border hover:bg-muted rounded-lg font-medium transition">
            Cancelar
        </a>
        <button type="submit" 
                class="flex-1 bg-primary-600 hover:bg-primary-700 text-white px-6 py-3 rounded-lg font-medium transition-all duration-200 hover:shadow-lg flex items-center justify-center gap-2">
            <i data-lucide="save" class="h-5 w-5"></i>
            {{ isset($campaign) ? 'Atualizar Campanha' : 'Criar e Enviar' }}
        </button>
    </div>
</form>

@push('scripts')
<script>
// Toggle agendamento
document.querySelectorAll('input[name="send_type"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const scheduleInput = document.getElementById('schedule-input');
        if (this.value === 'scheduled') {
            scheduleInput.classList.remove('hidden');
            scheduleInput.querySelector('input').required = true;
        } else {
            scheduleInput.classList.add('hidden');
            scheduleInput.querySelector('input').required = false;
        }
    });
});
</script>
@endpush
@endsection
