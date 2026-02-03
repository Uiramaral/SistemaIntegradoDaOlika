@extends('dashboard.layouts.app')

@section('page_title', 'Configurações')
@section('page_subtitle', 'Gerencie as configurações do sistema')

@section('content')
<div x-data="{ 
    activeTab: 'geral', 
    init() {
        if(window.location.hash) {
            const hash = window.location.hash.substring(1);
            if(['geral', 'loja', 'entrega', 'personalizacao', 'pwa', 'impressao'].includes(hash)) {
                this.activeTab = hash;
            }
        }
    },
    setTab(tab) {
        this.activeTab = tab;
        window.location.hash = tab;
    }
}" class="space-y-6">

    @if(session('success'))
        <div class="rounded-xl border border-green-200 bg-green-50 text-green-900 px-4 py-3 shadow-sm animate-fade-in flex items-center gap-2">
            <i data-lucide="check-circle" class="w-5 h-5 text-green-600"></i>
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 text-red-900 px-4 py-3 shadow-sm animate-fade-in">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Tabs Navigation -->
    <div class="bg-muted/30 p-1.5 rounded-xl flex flex-wrap gap-1 sm:gap-2">
        <button @click="setTab('geral')" 
                :class="activeTab === 'geral' ? 'bg-white text-primary shadow-sm ring-1 ring-black/5' : 'text-muted-foreground hover:bg-white/50 hover:text-foreground'"
                class="flex-1 sm:flex-none px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 flex items-center justify-center gap-2">
            <i data-lucide="settings" class="w-4 h-4"></i>
            Geral
        </button>
        <button @click="setTab('loja')" 
                :class="activeTab === 'loja' ? 'bg-white text-primary shadow-sm ring-1 ring-black/5' : 'text-muted-foreground hover:bg-white/50 hover:text-foreground'"
                class="flex-1 sm:flex-none px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 flex items-center justify-center gap-2">
            <i data-lucide="shopping-bag" class="w-4 h-4"></i>
            Loja
        </button>
        <button @click="setTab('entrega')" 
                :class="activeTab === 'entrega' ? 'bg-white text-primary shadow-sm ring-1 ring-black/5' : 'text-muted-foreground hover:bg-white/50 hover:text-foreground'"
                class="flex-1 sm:flex-none px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 flex items-center justify-center gap-2">
            <i data-lucide="truck" class="w-4 h-4"></i>
            Entrega
        </button>
        <button @click="setTab('personalizacao')" 
                :class="activeTab === 'personalizacao' ? 'bg-white text-primary shadow-sm ring-1 ring-black/5' : 'text-muted-foreground hover:bg-white/50 hover:text-foreground'"
                class="flex-1 sm:flex-none px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 flex items-center justify-center gap-2">
            <i data-lucide="palette" class="w-4 h-4"></i>
            Personalização
        </button>
        <button @click="setTab('pwa')" 
                :class="activeTab === 'pwa' ? 'bg-white text-primary shadow-sm ring-1 ring-black/5' : 'text-muted-foreground hover:bg-white/50 hover:text-foreground'"
                class="flex-1 sm:flex-none px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 flex items-center justify-center gap-2">
            <i data-lucide="smartphone" class="w-4 h-4"></i>
            App & Notificações
        </button>
        <button @click="setTab('impressao')" 
                :class="activeTab === 'impressao' ? 'bg-white text-primary shadow-sm ring-1 ring-black/5' : 'text-muted-foreground hover:bg-white/50 hover:text-foreground'"
                class="flex-1 sm:flex-none px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 flex items-center justify-center gap-2">
            <i data-lucide="printer" class="w-4 h-4"></i>
            Impressão
        </button>
    </div>

    <!-- Conteúdo: Geral -->
    <div x-show="activeTab === 'geral'" class="space-y-6" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
        <div class="grid gap-6 md:grid-cols-2">
            <!-- Informações da Empresa -->
            <div class="bg-card rounded-xl border border-border shadow-sm h-full">
                <div class="p-6 border-b border-border bg-muted/10">
                    <h3 class="text-lg font-semibold flex items-center gap-2 text-foreground">
                        <div class="h-8 w-8 rounded-lg bg-primary/10 flex items-center justify-center text-primary">
                            <i data-lucide="building-2" class="w-4 h-4"></i>
                        </div>
                        Informações da Empresa
                    </h3>
                    <p class="text-sm text-muted-foreground mt-1 ml-10">Dados básicos da sua empresa/confeitaria</p>
                </div>
                <form action="{{ route('dashboard.settings.general.save') }}" method="POST" class="p-6 space-y-4">
                    @csrf
                    <div class="space-y-4">
                        <div class="space-y-2">
                            <label class="text-sm font-medium" for="company_name">Nome da Empresa / Confeitaria <span class="text-destructive">*</span></label>
                            <input name="company_name" id="company_name" 
                                   value="{{ $generalSettings['company_name'] ?? auth()->user()->name ?? '' }}" 
                                   class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm" 
                                   placeholder="Ex.: Confeitaria Pro" required>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium" for="company_phone">Telefone / WhatsApp <span class="text-destructive">*</span></label>
                            <input name="company_phone" id="company_phone" 
                                   value="{{ $generalSettings['company_phone'] ?? auth()->user()->phone ?? '' }}" 
                                   class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm" 
                                   placeholder="(11) 99999-9999" required>
                            <p class="text-[11px] text-muted-foreground">Usado para notificações via WhatsApp</p>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium" for="company_email">E-mail <span class="text-destructive">*</span></label>
                            <input type="email" name="company_email" id="company_email" 
                                   value="{{ $generalSettings['company_email'] ?? auth()->user()->email ?? '' }}" 
                                   class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm" 
                                   placeholder="contato@confeitaria.com" required>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium" for="business_subtitle">Subtítulo do Sidebar</label>
                            <input name="business_subtitle" id="business_subtitle" 
                                   value="{{ $generalSettings['business_subtitle'] ?? 'Gestão profissional' }}" 
                                   class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm" 
                                   placeholder="Ex.: Gestão profissional">
                            <p class="text-[11px] text-muted-foreground">Texto que aparece abaixo do nome da marca no menu lateral</p>
                        </div>
                    </div>
                    <div class="pt-4 flex justify-end">
                        <button type="submit" class="btn-primary w-full sm:w-auto gap-2">
                            <i data-lucide="save" class="w-4 h-4"></i>
                            Salvar Informações
                        </button>
                    </div>
                </form>
            </div>

            <!-- Configurações Regionais -->
            <div class="bg-card rounded-xl border border-border shadow-sm h-full">
                <div class="p-6 border-b border-border bg-muted/10">
                    <h3 class="text-lg font-semibold flex items-center gap-2 text-foreground">
                        <div class="h-8 w-8 rounded-lg bg-blue-500/10 flex items-center justify-center text-blue-600">
                            <i data-lucide="globe" class="w-4 h-4"></i>
                        </div>
                        Configurações Regionais
                    </h3>
                    <p class="text-sm text-muted-foreground mt-1 ml-10">Idioma, moeda e fuso horário</p>
                </div>
                <form action="{{ route('dashboard.settings.general.save') }}" method="POST" class="p-6 space-y-4">
                    @csrf
                    <div class="space-y-4">
                        <div class="space-y-2">
                            <label class="text-sm font-medium" for="language">Idioma</label>
                            <select name="language" id="language" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
                                <option value="pt-BR" {{ ($generalSettings['language'] ?? 'pt-BR') === 'pt-BR' ? 'selected' : '' }}>Português (Brasil)</option>
                                <option value="en" {{ ($generalSettings['language'] ?? '') === 'en' ? 'selected' : '' }}>English</option>
                                <option value="es" {{ ($generalSettings['language'] ?? '') === 'es' ? 'selected' : '' }}>Español</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium" for="currency">Moeda</label>
                            <select name="currency" id="currency" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
                                <option value="BRL" {{ ($generalSettings['currency'] ?? 'BRL') === 'BRL' ? 'selected' : '' }}>Real (R$)</option>
                                <option value="USD" {{ ($generalSettings['currency'] ?? '') === 'USD' ? 'selected' : '' }}>Dólar (US$)</option>
                                <option value="EUR" {{ ($generalSettings['currency'] ?? '') === 'EUR' ? 'selected' : '' }}>Euro (€)</option>
                            </select>
                        </div>
                    </div>
                    <div class="pt-4 flex justify-end">
                        <button type="submit" class="btn-primary w-full sm:w-auto gap-2">
                            <i data-lucide="save" class="w-4 h-4"></i>
                            Salvar Regionais
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Conteúdo: Loja -->
    <div x-show="activeTab === 'loja'" class="space-y-6" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;">
        <div class="grid gap-6 md:grid-cols-2">
            <!-- Configurações de Pedidos -->
            <div class="bg-card rounded-xl border border-border shadow-sm h-full">
                <div class="p-6 border-b border-border bg-muted/10">
                    <h3 class="text-lg font-semibold flex items-center gap-2 text-foreground">
                        <div class="h-8 w-8 rounded-lg bg-primary/10 flex items-center justify-center text-primary">
                            <i data-lucide="shopping-basket" class="w-4 h-4"></i>
                        </div>
                        Pedidos & Vendas
                    </h3>
                    <p class="text-sm text-muted-foreground mt-1 ml-10">Numeração e frete grátis</p>
                </div>
                <form action="{{ route('dashboard.settings.apis.save') }}" method="POST" class="p-6 space-y-4">
                    @csrf
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <label class="text-sm font-medium" for="order_number_prefix">Prefixo dos Pedidos</label>
                            <input name="order_number_prefix" id="order_number_prefix" 
                                   value="{{ $apiSettings['order_number_prefix'] ?? 'OLK-' }}" 
                                   class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm" 
                                   placeholder="Ex.: OLK-">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium" for="next_order_number">Próximo Número</label>
                            <input type="number" name="next_order_number" id="next_order_number" 
                                   value="{{ (int)($apiSettings['next_order_number'] ?? 1) }}" 
                                   class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm" 
                                   min="1">
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium" for="free_shipping_min_total">Frete Grátis a partir de (R$)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2.5 text-muted-foreground text-sm">R$</span>
                            <input type="number" step="0.01" name="free_shipping_min_total" id="free_shipping_min_total" 
                                   value="{{ isset($apiSettings['free_shipping_min_total']) ? (float)$apiSettings['free_shipping_min_total'] : 200 }}" 
                                   class="flex h-10 w-full rounded-md border border-input bg-background pl-9 pr-3 py-2 text-sm" 
                                   placeholder="0.00">
                        </div>
                        <p class="text-[11px] text-muted-foreground">Valor zero desativa o frete grátis automático</p>
                    </div>
                    <div class="pt-4 flex justify-end">
                        <button type="submit" class="btn-primary w-full sm:w-auto gap-2">
                            <i data-lucide="save" class="w-4 h-4"></i>
                            Salvar Loja
                        </button>
                    </div>
                </form>
            </div>

            <!-- Mercado Pago -->
            <div class="bg-card rounded-xl border border-border shadow-sm h-full">
                <div class="p-6 border-b border-border bg-muted/10">
                    <h3 class="text-lg font-semibold flex items-center gap-2 text-foreground">
                        <div class="h-8 w-8 rounded-lg bg-blue-500/10 flex items-center justify-center text-blue-600">
                            <i data-lucide="credit-card" class="w-4 h-4"></i>
                        </div>
                        Pagamentos
                    </h3>
                    <p class="text-sm text-muted-foreground mt-1 ml-10">Integração com Mercado Pago</p>
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex items-center gap-4 p-4 rounded-xl border border-blue-100 bg-blue-50/50">
                        <img src="https://http2.mlstatic.com/frontend-assets/ml-web-navigation/ui-navigation/5.21.22/mercadopago/logo__large.png" alt="Mercado Pago" class="h-6 object-contain">
                        <div class="flex-1">
                            <p class="text-sm font-medium">Configurações do Mercado Pago</p>
                            <p class="text-xs text-muted-foreground">Configure suas chaves de API e métodos de pagamento aceitos.</p>
                        </div>
                    </div>
                    <div class="pt-2">
                        <a href="{{ route('dashboard.settings.mp') }}" class="flex w-full items-center justify-center gap-2 rounded-md bg-white border border-input px-4 py-2.5 text-sm font-medium shadow-sm hover:bg-accent transition-colors">
                            <i data-lucide="external-link" class="w-4 h-4"></i>
                            Gerenciar Mercado Pago
                        </a>
                    </div>
                </div>
            </div>

            <!-- Assistente IA -->
            <div class="bg-card rounded-xl border border-border shadow-sm h-full">
                <div class="p-6 border-b border-border bg-muted/10">
                    <h3 class="text-lg font-semibold flex items-center gap-2 text-foreground">
                        <div class="h-8 w-8 rounded-lg bg-indigo-500/10 flex items-center justify-center text-indigo-600">
                            <i data-lucide="bot" class="w-4 h-4"></i>
                        </div>
                        Assistente IA
                    </h3>
                    <p class="text-sm text-muted-foreground mt-1 ml-10">Personalize seu assistente virtual</p>
                </div>
                <form action="{{ route('dashboard.settings.apis.save') }}" method="POST" class="p-6 space-y-4">
                    @csrf
                    <div class="space-y-2">
                        <label class="text-sm font-medium" for="assistente_ia_nome">Nome do Assistente</label>
                        <input name="assistente_ia_nome" id="assistente_ia_nome" 
                               value="{{ old('assistente_ia_nome', $assistenteIaNome ?? 'ChefIA') }}" 
                               class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm" 
                               placeholder="Ex.: ChefIA">
                        <p class="text-[11px] text-muted-foreground">Nome exibido durante o atendimento automático</p>
                    </div>
                    <div class="pt-4 flex justify-end">
                        <button type="submit" class="btn-primary w-full sm:w-auto gap-2">
                            <i data-lucide="save" class="w-4 h-4"></i>
                            Salvar Assistente
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Conteúdo: Entrega -->
    <div x-show="activeTab === 'entrega'" class="space-y-6" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;">
        <div class="grid gap-6 md:grid-cols-2">
            <!-- Agendamento e Prazos -->
            <div class="bg-card rounded-xl border border-border shadow-sm h-full">
                <div class="p-6 border-b border-border bg-muted/10">
                    <h3 class="text-lg font-semibold flex items-center gap-2 text-foreground">
                        <div class="h-8 w-8 rounded-lg bg-primary/10 flex items-center justify-center text-primary">
                            <i data-lucide="truck" class="w-4 h-4"></i>
                        </div>
                        Regras de Entrega
                    </h3>
                    <p class="text-sm text-muted-foreground mt-1 ml-10">Prazos, capacidade e horários limite</p>
                </div>
                <form action="{{ route('dashboard.settings.apis.save') }}" method="POST" class="p-6 space-y-4">
                    @csrf
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <label class="text-sm font-medium" for="delivery_slot_capacity">Capacidade por Slot (30min)</label>
                            <input type="number" name="delivery_slot_capacity" id="delivery_slot_capacity" 
                                   value="{{ $apiSettings['delivery_slot_capacity'] ?? 2 }}" 
                                   class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm" 
                                   min="1" max="50">
                            <p class="text-[10px] text-muted-foreground">Máximo de pedidos por janela de tempo</p>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium" for="advance_order_days">Antecedência Mínima (Dias)</label>
                            <input type="number" name="advance_order_days" id="advance_order_days" 
                                   value="{{ $apiSettings['advance_order_days'] ?? 2 }}" 
                                   class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm" 
                                   min="0" max="30">
                            <p class="text-[10px] text-muted-foreground">Diferença mínima entre pedido e entrega</p>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium" for="default_cutoff_time">Horário Limite diário (Cutoff)</label>
                        <input type="time" name="default_cutoff_time" id="default_cutoff_time" 
                               value="{{ $apiSettings['default_cutoff_time'] ?? '' }}" 
                               class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
                        <p class="text-[10px] text-muted-foreground">Pedidos após este horário contam para o próximo dia útil</p>
                    </div>
                    <div class="pt-4 flex justify-end">
                        <button type="submit" class="btn-primary w-full sm:w-auto gap-2">
                            <i data-lucide="save" class="w-4 h-4"></i>
                            Salvar Regras
                        </button>
                    </div>
                </form>
            </div>

            <!-- Atalhos de Logística -->
            <div class="bg-card rounded-xl border border-border shadow-sm h-full">
                <div class="p-6 border-b border-border bg-muted/10">
                    <h3 class="text-lg font-semibold flex items-center gap-2 text-foreground">
                        <div class="h-8 w-8 rounded-lg bg-orange-500/10 flex items-center justify-center text-orange-600">
                            <i data-lucide="map-pin" class="w-4 h-4"></i>
                        </div>
                        Configurações Detalhadas
                    </h3>
                    <p class="text-sm text-muted-foreground mt-1 ml-10">Dias de entrega e taxas por distância</p>
                </div>
                <div class="p-6 space-y-4">
                    <a href="{{ route('dashboard.settings.delivery.schedules.index') }}" class="flex items-center justify-between p-4 rounded-xl border border-border hover:border-primary/50 hover:bg-primary/5 transition-all group">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center text-primary group-hover:scale-110 transition-transform">
                                <i data-lucide="calendar-clock" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <p class="font-semibold text-sm">Dias e Horários de Entrega</p>
                                <p class="text-xs text-muted-foreground">Defina janelas de entrega por dia da semana</p>
                            </div>
                        </div>
                        <i data-lucide="chevron-right" class="w-5 h-5 text-muted-foreground group-hover:text-primary transition-colors"></i>
                    </a>

                    <a href="{{ route('dashboard.delivery-pricing.index') }}" class="flex items-center justify-between p-4 rounded-xl border border-border hover:border-orange-500/50 hover:bg-orange-50/50 transition-all group">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-orange-500/10 flex items-center justify-center text-orange-600 group-hover:scale-110 transition-transform">
                                <i data-lucide="truck" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <p class="font-semibold text-sm">Taxas por Distância / Região</p>
                                <p class="text-xs text-muted-foreground">Configure valores de frete baseado na localização</p>
                            </div>
                        </div>
                        <i data-lucide="chevron-right" class="w-5 h-5 text-muted-foreground group-hover:text-orange-600 transition-colors"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Conteúdo: Personalização -->
    <div x-show="activeTab === 'personalizacao'" class="space-y-6" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;">
        <div class="grid gap-6 md:grid-cols-2">
            <!-- Cor do Tema -->
            <div class="bg-card rounded-xl border border-border shadow-sm">
                <div class="p-6 border-b border-border bg-muted/10">
                    <h3 class="text-lg font-semibold flex items-center gap-2">
                        <div class="h-8 w-8 rounded-lg bg-pink-500/10 flex items-center justify-center text-pink-600">
                            <i data-lucide="palette" class="w-4 h-4"></i>
                        </div>
                        Aparência
                    </h3>
                    <p class="text-sm text-muted-foreground mt-1 ml-10">Personalize as cores do seu sistema</p>
                </div>
                <form action="{{ route('dashboard.settings.personalization.save') }}" method="POST" class="p-6 space-y-6">
                    @csrf
                    @php
                        $selectedColor = $personalizationSettings['theme_color'] ?? '#f59e0b';
                    @endphp
                    
                    <div class="space-y-4">
                        <label class="text-sm font-medium block">Cor Principal</label>
                        <div class="flex items-center gap-4">
                            <div class="relative group">
                                <input type="color" 
                                       id="custom_color_picker" 
                                       value="{{ $selectedColor }}"
                                       class="w-16 h-16 rounded-xl border-2 border-border cursor-pointer p-0.5 overflow-hidden transition-transform group-hover:scale-105"
                                       onchange="document.getElementById('theme_color_input').value = this.value; window.updatePresets(this.value);">
                                <div class="absolute inset-0 pointer-events-none rounded-xl ring-1 ring-inset ring-black/10"></div>
                            </div>
                            <div class="flex-1 space-y-2">
                                <input type="text" 
                                       id="theme_color_input" 
                                       name="theme_color" 
                                       value="{{ $selectedColor }}"
                                       pattern="^#[0-9A-Fa-f]{6}$"
                                       class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm font-mono uppercase"
                                       placeholder="#F59E0B"
                                       onchange="document.getElementById('custom_color_picker').value = this.value; window.updatePresets(this.value);">
                                <p class="text-xs text-muted-foreground">Hexadecimal da cor</p>
                            </div>
                        </div>

                        <div class="space-y-3 pt-2">
                            <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Cores Sugeridas</label>
                            <div class="grid grid-cols-6 gap-2">
                                @foreach([
                                    '#f472b6', '#3b82f6', '#10b981', '#8b5cf6', 
                                    '#f97316', '#ef4444', '#eab308', '#14b8a6', 
                                    '#6366f1', '#ec4899', '#84cc16', '#06b6d4'
                                ] as $color)
                                    <button type="button" 
                                            class="preset-color-btn w-full aspect-square rounded-lg border border-border shadow-sm hover:scale-110 transition-all focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
                                            style="background-color: {{ $color }}"
                                            onclick="document.getElementById('theme_color_input').value = '{{ $color }}'; document.getElementById('custom_color_picker').value = '{{ $color }}'; window.updatePresets('{{ $color }}');">
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 flex justify-end">
                        <button type="submit" class="btn-primary w-full sm:w-auto gap-2">
                            <i data-lucide="save" class="w-4 h-4"></i>
                            Salvar Aparência
                        </button>
                    </div>
                </form>
            </div>

            <!-- Logotipo e Favicon -->
            <div class="bg-card rounded-xl border border-border shadow-sm">
                <div class="p-6 border-b border-border bg-muted/10">
                    <h3 class="text-lg font-semibold flex items-center gap-2">
                        <div class="h-8 w-8 rounded-lg bg-orange-500/10 flex items-center justify-center text-orange-600">
                            <i data-lucide="image" class="w-4 h-4"></i>
                        </div>
                        Identidade Visual
                    </h3>
                    <p class="text-sm text-muted-foreground mt-1 ml-10">Logotipo e ícone do navegador</p>
                </div>
                <form action="{{ route('dashboard.settings.personalization.save') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
                    @csrf
                    <div class="space-y-6">
                        <div class="space-y-3">
                            <label class="text-sm font-medium flex justify-between">
                                Logotipo
                                <span class="text-xs text-muted-foreground font-normal">Recomendado: 200px de altura</span>
                            </label>
                            <div class="border-2 border-dashed border-border rounded-xl p-6 flex flex-col items-center justify-center gap-4 bg-muted/5 hover:bg-muted/10 transition-colors">
                                @if(isset($personalizationSettings['logo_url']))
                                    <img src="{{ $personalizationSettings['logo_url'] }}" alt="Logo" class="h-16 object-contain mb-2">
                                @else
                                    <div class="h-16 w-full flex items-center justify-center text-muted-foreground/30">
                                        <i data-lucide="image-off" class="w-10 h-10"></i>
                                    </div>
                                @endif
                                <label class="cursor-pointer w-full text-center py-2 px-4 rounded border border-input hover:bg-accent hover:text-accent-foreground">
                                    <span>Escolher arquivo...</span>
                                    <input type="file" name="logo" accept="image/*" class="hidden">
                                </label>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <label class="text-sm font-medium flex justify-between">
                                Favicon
                                <span class="text-xs text-muted-foreground font-normal">Ícone da aba (32x32px)</span>
                            </label>
                            <div class="flex items-center gap-4">
                                <div class="w-16 h-16 rounded-xl border border-border bg-white flex items-center justify-center shrink-0 shadow-sm">
                                    @if(isset($personalizationSettings['favicon_url']))
                                        <img src="{{ $personalizationSettings['favicon_url'] }}" alt="Favicon" class="w-8 h-8 object-contain">
                                    @else
                                        <i data-lucide="globe" class="w-8 h-8 text-muted-foreground/30"></i>
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <input type="file" name="favicon" accept="image/*" 
                                           class="file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 text-sm text-muted-foreground w-full">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="pt-4 flex justify-end">
                        <button type="submit" class="btn-primary w-full sm:w-auto gap-2">
                            <i data-lucide="save" class="w-4 h-4"></i>
                            Salvar Imagens
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Conteúdo: PWA & Notificações -->
    <div x-show="activeTab === 'pwa'" class="space-y-6" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;">
        <div class="grid gap-6 md:grid-cols-2">
            <!-- Instalar Aplicativo (PWA) -->
            <div class="bg-card rounded-xl border border-border shadow-sm">
                <div class="p-6 border-b border-border bg-muted/10">
                    <h3 class="text-lg font-semibold flex items-center gap-2">
                        <div class="h-8 w-8 rounded-lg bg-green-500/10 flex items-center justify-center text-green-600">
                            <i data-lucide="download" class="w-4 h-4"></i>
                        </div>
                        Instalar Aplicativo
                    </h3>
                    <p class="text-sm text-muted-foreground mt-1 ml-10">App offline para acesso rápido</p>
                </div>
                <div class="p-6 space-y-6">
                    <div id="pwa-install-container" class="space-y-4">
                        <!-- Install Button -->
                        <div id="pwa-install-prompt" class="hidden text-center py-4">
                            <button id="btn-install-pwa" class="flex w-full items-center justify-center rounded-md bg-primary px-8 py-3 text-sm font-medium text-primary-foreground shadow transition-colors hover:bg-primary/90">
                                <i data-lucide="smartphone" class="w-5 h-5 mr-2"></i>
                                Instalar Agora
                            </button>
                            <p class="text-xs text-muted-foreground mt-3">
                                Instale na sua área de trabalho para uma experiência melhor
                            </p>
                        </div>

                        <!-- Already Installed -->
                        <div id="pwa-already-installed" class="hidden">
                            <div class="flex items-center gap-4 p-4 bg-green-500/10 border border-green-500/20 rounded-xl">
                                <i data-lucide="check-circle" class="w-6 h-6 text-green-600 shrink-0"></i>
                                <div>
                                    <p class="font-bold text-green-900">Aplicativo Instalado</p>
                                    <p class="text-xs text-green-700 mt-1">O app já está pronto para uso offline.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Not Supported -->
                        <div id="pwa-not-supported">
                            <div class="flex items-center gap-4 p-4 bg-muted border border-border rounded-xl">
                                <i data-lucide="info" class="w-5 h-5 text-muted-foreground shrink-0"></i>
                                <div>
                                    <p class="font-medium text-foreground">Aguardando verificação...</p>
                                    <p class="text-xs text-muted-foreground mt-1">Checando compatibilidade do navegador...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notificações Push -->
            <div class="bg-card rounded-xl border border-border shadow-sm">
                <div class="p-6 border-b border-border bg-muted/10">
                    <h3 class="text-lg font-semibold flex items-center gap-2">
                        <div class="h-8 w-8 rounded-lg bg-purple-500/10 flex items-center justify-center text-purple-600">
                            <i data-lucide="bell" class="w-4 h-4"></i>
                        </div>
                        Notificações Push
                    </h3>
                    <p class="text-sm text-muted-foreground mt-1 ml-10">Alertas de pedidos em tempo real</p>
                </div>
                <div class="p-6 space-y-6">
                    <!-- Status -->
                    <div id="notification-status-container">
                        <div id="notification-enabled" class="hidden flex flex-col gap-3 p-4 bg-green-500/10 border border-green-500/20 rounded-xl text-center">
                            <div class="mx-auto w-10 h-10 rounded-full bg-green-100 flex items-center justify-center text-green-600">
                                <i data-lucide="bell-ring" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <p class="font-bold text-green-900">Notificações Ativas</p>
                                <p class="text-xs text-green-700 mt-1">Você receberá todos os alertas.</p>
                            </div>
                        </div>

                        <div id="notification-blocked" class="hidden flex flex-col gap-3 p-4 bg-red-500/10 border border-red-500/20 rounded-xl text-center">
                             <div class="mx-auto w-10 h-10 rounded-full bg-red-100 flex items-center justify-center text-red-600">
                                <i data-lucide="x-circle" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <p class="font-bold text-red-900">Notificações Bloqueadas</p>
                                <p class="text-xs text-red-700 mt-1">Você bloqueou as notificações. Ative nas configurações do navegador.</p>
                            </div>
                        </div>

                        <div id="notification-disabled" class="hidden flex flex-col gap-3 p-4 bg-amber-500/10 border border-amber-500/20 rounded-xl text-center">
                            <div class="mx-auto w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center text-amber-600">
                                <i data-lucide="bell-off" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <p class="font-bold text-amber-900">Notificações Desativadas</p>
                                <p class="text-xs text-amber-700 mt-1">Ative para não perder nenhum pedido.</p>
                            </div>
                            <button id="btn-enable-notifications" class="flex w-full items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow transition-colors hover:bg-primary/90 mt-2">
                                Ativar Notificações
                            </button>
                        </div>
                        
                        <div id="notification-loading" class="text-center py-4">
                            <i data-lucide="loader-2" class="w-6 h-6 animate-spin mx-auto text-muted-foreground"></i>
                        </div>
                    </div>
                    
                    <button id="btn-test-notification" class="flex w-full items-center justify-center rounded-md border border-input bg-background px-4 py-2 text-sm font-medium shadow-sm transition-colors hover:bg-accent hover:text-accent-foreground gap-2" disabled>
                        <i data-lucide="send" class="w-4 h-4"></i>
                        Enviar Teste
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Conteúdo: Impressão -->
    <div x-show="activeTab === 'impressao'" class="space-y-6" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;">
        @include('dashboard.settings.printing-content')
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/qz-tray@2.2/qz-tray.min.js"></script>
<script>
    // Scripts originais mantidos e adaptados
    window.updatePresets = function(color) {
        document.querySelectorAll('.preset-color-btn').forEach(btn => {
            btn.classList.add('opacity-50');
            if(btn.style.backgroundColor === color) {
                btn.classList.remove('opacity-50');
                btn.classList.add('ring-2', 'ring-offset-2', 'ring-gray-400');
            } else {
                btn.classList.remove('ring-2', 'ring-offset-2', 'ring-gray-400');
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        // ===========================================
        // PWA: INSTALAR APLICATIVO
        // ===========================================
        let deferredPrompt;
        const btnInstallPWA = document.getElementById('btn-install-pwa');
        const pwaInstallPrompt = document.getElementById('pwa-install-prompt');
        const pwaAlreadyInstalled = document.getElementById('pwa-already-installed');
        const pwaNotSupported = document.getElementById('pwa-not-supported');
        
        // Verificar se já está instalado
        if (window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true) {
            pwaNotSupported.classList.add('hidden');
            pwaAlreadyInstalled.classList.remove('hidden');
        }
        
        // Capturar evento beforeinstallprompt
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            pwaNotSupported.classList.add('hidden');
            pwaInstallPrompt.classList.remove('hidden');
        });
        
        // Botão de instalação
        if (btnInstallPWA) {
            btnInstallPWA.addEventListener('click', async () => {
                if (!deferredPrompt) return;
                
                deferredPrompt.prompt();
                const { outcome } = await deferredPrompt.userChoice;
                
                if (outcome === 'accepted') {
                    pwaInstallPrompt.classList.add('hidden');
                    pwaAlreadyInstalled.classList.remove('hidden');
                }
                
                deferredPrompt = null;
            });
        }
        
        // Timeout para verificação
        setTimeout(() => {
            if (pwaInstallPrompt.classList.contains('hidden') && pwaAlreadyInstalled.classList.contains('hidden')) {
                const title = pwaNotSupported.querySelector('p.font-medium');
                const desc = pwaNotSupported.querySelector('p.text-xs') || pwaNotSupported.querySelector('p.text-sm');
                if(title) title.textContent = 'Instalação não disponível';
                if(desc) desc.textContent = 'Seu navegador não suporta instalação de aplicativo ou você já está usando o app instalado';
            }
        }, 2000);
        
        // ===========================================
        // NOTIFICAÇÕES PUSH
        // ===========================================
        const notificationEnabled = document.getElementById('notification-enabled');
        const notificationDisabled = document.getElementById('notification-disabled');
        const notificationBlocked = document.getElementById('notification-blocked');
        const notificationLoading = document.getElementById('notification-loading');
        const btnEnableNotifications = document.getElementById('btn-enable-notifications');
        const btnDisableNotifications = document.getElementById('btn-disable-notifications');
        const btnTestNotification = document.getElementById('btn-test-notification');
        
        // Verificar status das notificações
        async function checkNotificationStatus() {
            if (!('Notification' in window)) {
                notificationLoading.classList.add('hidden');
                if(notificationDisabled) {
                    notificationDisabled.classList.remove('hidden');
                    const title = notificationDisabled.querySelector('p.font-medium');
                    const desc = notificationDisabled.querySelector('p.text-xs');
                    if(title) title.textContent = 'Notificações não suportadas';
                    if(desc) desc.textContent = 'Seu navegador não suporta notificações push';
                }
                if(btnEnableNotifications) btnEnableNotifications.classList.add('hidden');
                return;
            }
            
            const permission = Notification.permission;
            notificationLoading.classList.add('hidden');
            
            if (permission === 'granted') {
                if(notificationEnabled) notificationEnabled.classList.remove('hidden');
                if(btnTestNotification) btnTestNotification.disabled = false;
            } else if (permission === 'denied') {
                if(notificationBlocked) notificationBlocked.classList.remove('hidden');
            } else {
                if(notificationDisabled) notificationDisabled.classList.remove('hidden');
            }
        }
        
        // Ativar notificações
        if (btnEnableNotifications) {
            btnEnableNotifications.addEventListener('click', async () => {
                try {
                    const permission = await Notification.requestPermission();
                    
                    if (permission === 'granted') {
                        if(notificationDisabled) notificationDisabled.classList.add('hidden');
                        if(notificationEnabled) notificationEnabled.classList.remove('hidden');
                        if(btnTestNotification) btnTestNotification.disabled = false;
                        
                        // Enviar notificação de boas-vindas
                        new Notification('Notificações Ativadas!', {
                            body: 'Você receberá alertas sobre novos pedidos e atualizações importantes.',
                            icon: '/favicon/android-chrome-192x192.png'
                        });
                    } else if (permission === 'denied') {
                        if(notificationDisabled) notificationDisabled.classList.add('hidden');
                        if(notificationBlocked) notificationBlocked.classList.remove('hidden');
                    }
                } catch (error) {
                    console.error('Erro ao solicitar permissão:', error);
                    alert('Erro ao ativar notificações. Tente novamente.');
                }
            });
        }
        
        // Enviar notificação de teste
        if (btnTestNotification) {
            btnTestNotification.addEventListener('click', async () => {
                if (Notification.permission !== 'granted') {
                    alert('⚠️ Permissão de notificações não concedida!');
                    return;
                }
                
                try {
                    if ('serviceWorker' in navigator && navigator.serviceWorker.controller) {
                        const registration = await navigator.serviceWorker.ready;
                        await registration.showNotification('Teste de Notificação', {
                            body: 'Se você está vendo isso, as notificações estão funcionando perfeitamente!',
                            icon: '/favicon/android-chrome-192x192.png',
                            badge: '/favicon/android-chrome-192x192.png',
                            tag: 'test-notification',
                            requireInteraction: false
                        });
                    } else {
                        new Notification('Teste de Notificação', {
                            body: 'Se você está vendo isso, as notificações estão funcionando perfeitamente!',
                            icon: '/favicon/android-chrome-192x192.png',
                            badge: '/favicon/android-chrome-192x192.png'
                        });
                    }
                } catch (error) {
                    console.error('❌ Erro ao enviar notificação:', error);
                    alert('❌ Erro ao enviar notificação: ' + error.message);
                }
            });
        }
        
        checkNotificationStatus();
        
        // ===========================================
        // IMPRESSÃO: MONITOR DE IMPRESSÃO
        // ===========================================
        let monitorActive = false;
        let monitorInterval = null;
        let qzConnected = false;
        let printedCount = 0;
        
        const statusIndicator = document.getElementById('status-indicator');
        const statusText = document.getElementById('status-text');
        const btnToggleMonitor = document.getElementById('btn-toggle-monitor');
        const btnToggleText = document.getElementById('btn-toggle-text');
        const monitorInfo = document.getElementById('monitor-info');
        const qzStatus = document.getElementById('qz-status');
        const printerNameEl = document.getElementById('printer-name');
        const printedCountEl = document.getElementById('printed-count');
        const lastCheckEl = document.getElementById('last-check');
        
        // Verificar se QZ Tray está disponível
        function isQZTrayAvailable() {
            return typeof qz !== 'undefined' && qz !== null;
        }
        
        function isQZTrayConnected() {
            try {
                return isQZTrayAvailable() && qz.websocket !== null && qz.websocket.isActive();
            } catch (e) {
                return false;
            }
        }
        
        // Conectar ao QZ Tray
        async function connectQZTray() {
            if (!isQZTrayAvailable()) {
                return false;
            }
            
            try {
                if (isQZTrayConnected()) {
                    return true;
                }
                
                await qz.websocket.connect();
                return isQZTrayConnected();
            } catch (e) {
                console.error('Erro ao conectar QZ Tray:', e);
                return false;
            }
        }
        
        // Atualizar status visual
        function updateMonitorStatus(status, message) {
            if (!statusText) return;
            statusText.textContent = message;
            
            if (status === 'active') {
                if(statusIndicator) statusIndicator.className = 'w-3 h-3 rounded-full bg-green-500 animate-pulse';
                if(btnToggleMonitor) btnToggleMonitor.className = 'btn-outline';
                if(btnToggleText) btnToggleText.textContent = 'Desativar Monitor';
                if(monitorInfo) monitorInfo.classList.remove('hidden');
            } else if (status === 'inactive') {
                if(statusIndicator) statusIndicator.className = 'w-3 h-3 rounded-full bg-red-500';
                if(btnToggleMonitor) btnToggleMonitor.className = 'btn-primary';
                if(btnToggleText) btnToggleText.textContent = 'Ativar Monitor';
                if(monitorInfo) monitorInfo.classList.add('hidden');
            } else if (status === 'error') {
                if(statusIndicator) statusIndicator.className = 'w-3 h-3 rounded-full bg-yellow-500';
                if(btnToggleMonitor) btnToggleMonitor.className = 'btn-primary';
                if(btnToggleText) btnToggleText.textContent = 'Tentar Novamente';
                if(monitorInfo) monitorInfo.classList.add('hidden');
            }
            
            if(btnToggleMonitor) btnToggleMonitor.disabled = false;
        }
        
        // Verificar e imprimir pedidos
        async function checkAndPrintOrders() {
            if (!monitorActive) return;
            
            console.log('🔄 checkAndPrintOrders: Verificando pedidos...');
            
            try {
                if (lastCheckEl) lastCheckEl.textContent = new Date().toLocaleTimeString('pt-BR');
                
                // Verificar conexão QZ Tray
                if (!isQZTrayConnected()) {
                    console.log('⚠️ QZ Tray desconectado, tentando reconectar...');
                    const connected = await connectQZTray();
                    if (!connected) {
                        console.error('❌ Falha ao conectar QZ Tray');
                        if (qzStatus) qzStatus.textContent = '❌ Desconectado';
                        return;
                    }
                    console.log('✅ QZ Tray reconectado');
                    if (qzStatus) qzStatus.textContent = '✅ Conectado';
                }
                
                // Buscar pedidos para imprimir
                console.log('🔍 Buscando pedidos para imprimir... (v3.0 - FIX)');
                console.log('DEBUG: URL deveria ser /orders/orders-for-print');
                const response = await fetch('/orders/orders-for-print', {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'include'
                });
                
                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('❌ Erro HTTP ao buscar pedidos:', response.status);
                    console.error('📜 Detalhes do erro:', errorText);
                    throw new Error(`Erro HTTP: ${response.status} - ${errorText.substring(0, 100)}`);
                }
                
                const data = await response.json();
                console.log('📦 Pedidos recebidos:', data);
                
                if (!data.success || !data.orders || data.orders.length === 0) {
                    console.log('ℹ️ Nenhum pedido pendente para imprimir');
                    return;
                }
                
                console.log(`📝 ${data.orders.length} pedido(s) para processar`);
                
                // Processar pedidos
                for (const orderInfo of data.orders) {
                    if (orderInfo.printed_at) {
                        console.log(`⏭️ Pedido #${orderInfo.order_number} já foi impresso`);
                        continue;
                    }
                    
                    console.log(`🖨️ Processando pedido #${orderInfo.order_number}...`);
                    
                    try {
                        const printType = orderInfo.print_type || 'normal';
                        const escposEndpoint = printType === 'check' 
                            ? `/orders/${orderInfo.id}/check-receipt/escpos`
                            : `/orders/${orderInfo.id}/fiscal-receipt/escpos`;
                        
                        const detailsResponse = await fetch(escposEndpoint, {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            credentials: 'include'
                        });
                        
                        if (!detailsResponse.ok) {
                            console.error(`❌ Erro ao buscar detalhes:`, detailsResponse.status);
                            continue;
                        }
                        
                        const orderData = await detailsResponse.json();
                        if (!orderData.success || !orderData.data) {
                            console.error(`❌ Dados inválidos para pedido`);
                            continue;
                        }
                        
                        // Imprimir
                        const printers = await qz.printers.find();
                        let printer = printers.find(p => 
                            p.toUpperCase().includes('EPSON') && 
                            (p.toUpperCase().includes('TM-20') || p.toUpperCase().includes('TM-T20'))
                        ) || printers[0];
                        
                        if (!printer) {
                            console.error('❌ Nenhuma impressora disponível');
                            continue;
                        }
                        
                        const printConfig = qz.configs.create(printer);
                        await qz.print(printConfig, [{
                            type: 'raw',
                            format: 'base64',
                            data: orderData.data
                        }]);
                        
                        // Marcar como impresso
                        const markResponse = await fetch(`/orders/${orderInfo.id}/mark-printed`, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                            },
                            credentials: 'include',
                            body: JSON.stringify({})
                        });
                        
                        if (markResponse.ok) {
                            printedCount++;
                            if (printedCountEl) printedCountEl.textContent = printedCount;
                        }
                    } catch (e) {
                        console.error(`❌ Erro ao imprimir:`, e);
                    }
                }
            } catch (error) {
                console.error('❌ Erro na verificação:', error);
            }
        }
        
        // Ativar monitor
        async function startMonitor() {
            if (!btnToggleMonitor) return;
            btnToggleMonitor.disabled = true;
            if (btnToggleText) btnToggleText.textContent = 'Conectando...';
            
            try {
                const clearResponse = await fetch('/orders/clear-old-print-requests', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    credentials: 'include'
                });
            } catch (e) {
                console.warn('⚠️ Não foi possível limpar solicitações antigas:', e);
            }
            
            const connected = await connectQZTray();
            
            if (!connected) {
                updateMonitorStatus('error', 'QZ Tray não disponível');
                if (qzStatus) qzStatus.textContent = '❌ Não instalado ou não está rodando';
                alert('⚠️ QZ Tray não encontrado!\n\nCertifique-se que ele está rodando.');
                return;
            }
            
            try {
                const printers = await qz.printers.find();
                const printer = printers.find(p => 
                    p.toUpperCase().includes('EPSON') && 
                    (p.toUpperCase().includes('TM-20') || p.toUpperCase().includes('TM-T20'))
                ) || printers[0];
                
                if (printer && printerNameEl) {
                    printerNameEl.textContent = printer;
                }
            } catch (e) {}
            
            monitorActive = true;
            localStorage.setItem('printMonitorActive', 'true');
            if (qzStatus) qzStatus.textContent = '✅ Conectado';
            updateMonitorStatus('active', 'Monitor Ativo');
            
            monitorInterval = setInterval(checkAndPrintOrders, 3000);
            checkAndPrintOrders();
        }
        
        // Desativar monitor
        function stopMonitor() {
            monitorActive = false;
            localStorage.setItem('printMonitorActive', 'false');
            if (monitorInterval) {
                clearInterval(monitorInterval);
                monitorInterval = null;
            }
            updateMonitorStatus('inactive', 'Monitor Inativo');
        }
        
        // Toggle monitor
        if (btnToggleMonitor) {
            btnToggleMonitor.addEventListener('click', function() {
                if (monitorActive) {
                    stopMonitor();
                } else {
                    startMonitor();
                }
            });
        }
        
        // Inicializar status do monitor
        setTimeout(() => {
            if (btnToggleMonitor) {
                const wasActive = localStorage.getItem('printMonitorActive') === 'true';
                
                if (wasActive && isQZTrayAvailable()) {
                    startMonitor();
                } else if (isQZTrayAvailable()) {
                    updateMonitorStatus('inactive', 'Pronto para ativar');
                } else {
                    updateMonitorStatus('error', 'QZ Tray não detectado');
                    if (qzStatus) qzStatus.textContent = '❌ Não instalado';
                }
            }
        }, 500);
        
        // ===========================================
        // IMPRESSÃO: CONFIGURAÇÃO DO TIPO
        // ===========================================
        const printerTypeCards = document.querySelectorAll('.printer-type-card');
        const regularSettings = document.getElementById('regular-settings');
        
        if (printerTypeCards.length > 0) {
            printerTypeCards.forEach(card => {
                card.addEventListener('click', function() {
                    const type = this.dataset.type;
                    const radio = this.querySelector('input[type="radio"]');
                    
                    printerTypeCards.forEach(c => c.classList.remove('selected'));
                    this.classList.add('selected');
                    
                    if (radio) radio.checked = true;
                    
                    if (regularSettings) {
                        if (type === 'regular') {
                            regularSettings.classList.remove('hidden');
                        } else {
                            regularSettings.classList.add('hidden');
                        }
                    }
                });
            });
        }
        
        const btnTestPrint = document.getElementById('btn-test-print');
        if (btnTestPrint) {
            btnTestPrint.addEventListener('click', function() {
                const printerType = document.querySelector('input[name="printer_type"]:checked')?.value;
                const url = printerType === 'thermal' 
                    ? '/orders/1/fiscal-receipt' 
                    : '/orders/1/fiscal-receipt?format=a4';
                
                window.open(url, '_blank');
            });
        }
        
        // Inicializar ícones
        if (window.lucide) {
            lucide.createIcons();
        }
    });
</script>

<style>
.printer-type-card label > div {
    border-color: hsl(var(--border));
}
.printer-type-card.selected label > div {
    border-color: hsl(var(--primary));
    background: hsl(var(--primary) / 0.05);
}
</style>
@endpush
@endsection
