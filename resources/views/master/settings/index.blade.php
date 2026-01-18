@extends('layouts.admin')

@section('title', 'Configurações Master')
@section('page_title', 'Configurações Master')
@section('page_subtitle', 'Configurações globais do sistema Olika')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    @if(session('success'))
        <div class="rounded-lg border border-success/30 bg-success/10 p-4 text-success">
            {{ session('success') }}
        </div>
    @endif

    {{-- Configurações de Preços --}}
    <div class="rounded-lg border border-border bg-card shadow-sm">
        <div class="p-6 border-b border-border">
            <h3 class="text-lg font-semibold text-foreground">Configurações de Preços</h3>
            <p class="text-sm text-muted-foreground">Defina valores padrão para cobrança</p>
        </div>
        <form action="{{ route('master.settings.update') }}" method="POST" class="p-6 space-y-6">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="whatsapp_instance_price" class="block text-sm font-medium text-foreground mb-1">
                        Preço por Instância WhatsApp (R$)
                    </label>
                    <input type="number" name="whatsapp_instance_price" id="whatsapp_instance_price" 
                           value="{{ old('whatsapp_instance_price', $settings['whatsapp_instance_price'] ?? 15.00) }}"
                           step="0.01" min="0"
                           class="w-full px-3 py-2 rounded-md border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                           placeholder="15.00">
                    <p class="text-xs text-muted-foreground mt-1">Valor adicional por instância WhatsApp criada</p>
                </div>

                <div>
                    <label for="ai_message_price" class="block text-sm font-medium text-foreground mb-1">
                        Preço por 1000 Mensagens I.A. (R$)
                    </label>
                    <input type="number" name="ai_message_price" id="ai_message_price" 
                           value="{{ old('ai_message_price', $settings['ai_message_price'] ?? 5.00) }}"
                           step="0.01" min="0"
                           class="w-full px-3 py-2 rounded-md border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                           placeholder="5.00">
                    <p class="text-xs text-muted-foreground mt-1">Custo excedente de mensagens com I.A.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="default_trial_days" class="block text-sm font-medium text-foreground mb-1">
                        Dias de Trial Padrão
                    </label>
                    <input type="number" name="default_trial_days" id="default_trial_days" 
                           value="{{ old('default_trial_days', $settings['default_trial_days'] ?? 7) }}"
                           min="0"
                           class="w-full px-3 py-2 rounded-md border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                           placeholder="7">
                </div>

                <div>
                    <label for="billing_cycle_days" class="block text-sm font-medium text-foreground mb-1">
                        Ciclo de Cobrança (dias)
                    </label>
                    <input type="number" name="billing_cycle_days" id="billing_cycle_days" 
                           value="{{ old('billing_cycle_days', $settings['billing_cycle_days'] ?? 30) }}"
                           min="1"
                           class="w-full px-3 py-2 rounded-md border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                           placeholder="30">
                </div>
            </div>

            <button type="submit" class="w-full px-4 py-2 bg-primary text-primary-foreground rounded-md hover:bg-primary/90 transition font-medium">
                Salvar Configurações de Preços
            </button>
        </form>
    </div>

    {{-- Configurações de Notificações --}}
    <div class="rounded-lg border border-border bg-card shadow-sm">
        <div class="p-6 border-b border-border">
            <h3 class="text-lg font-semibold text-foreground">Notificações de Vencimento</h3>
            <p class="text-sm text-muted-foreground">Configure quando avisar sobre assinaturas expirando</p>
        </div>
        <form action="{{ route('master.settings.update') }}" method="POST" class="p-6 space-y-6">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="expiry_warning_days_1" class="block text-sm font-medium text-foreground mb-1">
                        1ª Notificação (dias antes)
                    </label>
                    <input type="number" name="expiry_warning_days_1" id="expiry_warning_days_1" 
                           value="{{ old('expiry_warning_days_1', $settings['expiry_warning_days_1'] ?? 7) }}"
                           min="1"
                           class="w-full px-3 py-2 rounded-md border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                           placeholder="7">
                </div>

                <div>
                    <label for="expiry_warning_days_2" class="block text-sm font-medium text-foreground mb-1">
                        2ª Notificação (dias antes)
                    </label>
                    <input type="number" name="expiry_warning_days_2" id="expiry_warning_days_2" 
                           value="{{ old('expiry_warning_days_2', $settings['expiry_warning_days_2'] ?? 3) }}"
                           min="1"
                           class="w-full px-3 py-2 rounded-md border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                           placeholder="3">
                </div>

                <div>
                    <label for="expiry_warning_days_3" class="block text-sm font-medium text-foreground mb-1">
                        3ª Notificação (dias antes)
                    </label>
                    <input type="number" name="expiry_warning_days_3" id="expiry_warning_days_3" 
                           value="{{ old('expiry_warning_days_3', $settings['expiry_warning_days_3'] ?? 1) }}"
                           min="1"
                           class="w-full px-3 py-2 rounded-md border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                           placeholder="1">
                </div>
            </div>

            <div>
                <label for="grace_period_days" class="block text-sm font-medium text-foreground mb-1">
                    Período de Carência (dias após vencimento)
                </label>
                <input type="number" name="grace_period_days" id="grace_period_days" 
                       value="{{ old('grace_period_days', $settings['grace_period_days'] ?? 3) }}"
                       min="0"
                       class="w-full max-w-xs px-3 py-2 rounded-md border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                       placeholder="3">
                <p class="text-xs text-muted-foreground mt-1">Dias que o cliente ainda pode usar após vencimento</p>
            </div>

            <button type="submit" class="w-full px-4 py-2 bg-primary text-primary-foreground rounded-md hover:bg-primary/90 transition font-medium">
                Salvar Configurações de Notificações
            </button>
        </form>
    </div>

    {{-- Configurações de Email --}}
    <div class="rounded-lg border border-border bg-card shadow-sm">
        <div class="p-6 border-b border-border">
            <h3 class="text-lg font-semibold text-foreground">Configurações de Email</h3>
            <p class="text-sm text-muted-foreground">Email de contato e suporte</p>
        </div>
        <form action="{{ route('master.settings.update') }}" method="POST" class="p-6 space-y-6">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="support_email" class="block text-sm font-medium text-foreground mb-1">
                        Email de Suporte
                    </label>
                    <input type="email" name="support_email" id="support_email" 
                           value="{{ old('support_email', $settings['support_email'] ?? '') }}"
                           class="w-full px-3 py-2 rounded-md border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                           placeholder="suporte@olika.com.br">
                </div>

                <div>
                    <label for="billing_email" class="block text-sm font-medium text-foreground mb-1">
                        Email de Faturamento
                    </label>
                    <input type="email" name="billing_email" id="billing_email" 
                           value="{{ old('billing_email', $settings['billing_email'] ?? '') }}"
                           class="w-full px-3 py-2 rounded-md border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                           placeholder="faturamento@olika.com.br">
                </div>
            </div>

            <div>
                <label for="support_whatsapp" class="block text-sm font-medium text-foreground mb-1">
                    WhatsApp de Suporte
                </label>
                <input type="text" name="support_whatsapp" id="support_whatsapp" 
                       value="{{ old('support_whatsapp', $settings['support_whatsapp'] ?? '') }}"
                       class="w-full max-w-xs px-3 py-2 rounded-md border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                       placeholder="5571999999999">
                <p class="text-xs text-muted-foreground mt-1">Formato internacional sem espaços</p>
            </div>

            <button type="submit" class="w-full px-4 py-2 bg-primary text-primary-foreground rounded-md hover:bg-primary/90 transition font-medium">
                Salvar Configurações de Email
            </button>
        </form>
    </div>

    {{-- Informações do Sistema --}}
    <div class="rounded-lg border border-border bg-card shadow-sm">
        <div class="p-6 border-b border-border">
            <h3 class="text-lg font-semibold text-foreground">Informações do Sistema</h3>
        </div>
        <div class="p-6 space-y-4">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <p class="text-sm text-muted-foreground">Versão</p>
                    <p class="font-medium text-foreground">{{ config('app.version', '1.0.0') }}</p>
                </div>
                <div>
                    <p class="text-sm text-muted-foreground">Laravel</p>
                    <p class="font-medium text-foreground">{{ app()->version() }}</p>
                </div>
                <div>
                    <p class="text-sm text-muted-foreground">PHP</p>
                    <p class="font-medium text-foreground">{{ phpversion() }}</p>
                </div>
                <div>
                    <p class="text-sm text-muted-foreground">Ambiente</p>
                    <p class="font-medium text-foreground">{{ app()->environment() }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
