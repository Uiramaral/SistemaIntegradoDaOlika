@extends('dashboard.layouts.app')

@section('page_title', 'Configurações')
@section('page_subtitle', 'Gerencie as configurações do sistema')

@push('styles')
<style>
    .view-btn {
        @apply px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2;
    }
    .view-btn.active {
        @apply bg-white text-foreground shadow-sm;
    }
    .view-btn.inactive {
        @apply bg-transparent text-gray-600 hover:text-foreground hover:bg-gray-50;
    }
    
    input[type="color"] {
        cursor: pointer;
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        border: none;
    }
    input[type="color"]::-webkit-color-swatch-wrapper {
        padding: 0;
    }
    input[type="color"]::-webkit-color-swatch {
        border: 2px solid #d1d5db;
        border-radius: 0.5rem;
    }
</style>
@endpush

@section('content')
<div class="space-y-6">
    @if(session('success'))
        <div class="rounded-lg border bg-green-50 text-green-900 px-4 py-3">{{ session('success') }}</div>
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

    <!-- Botões de Visualização (Abas) -->
    <div class="flex items-center gap-2 bg-gray-100 p-1 rounded-lg">
        <button class="view-btn active" id="btn-view-geral" onclick="switchTab('geral')">
            <i data-lucide="settings" class="w-4 h-4"></i>
            Geral
        </button>
        <button class="view-btn inactive" id="btn-view-personalizacao" onclick="switchTab('personalizacao')">
            <i data-lucide="palette" class="w-4 h-4"></i>
            Personalização
        </button>
        <button class="view-btn inactive" id="btn-view-pwa" onclick="switchTab('pwa')">
            <i data-lucide="smartphone" class="w-4 h-4"></i>
            App & Notificações
        </button>
        <button class="view-btn inactive" id="btn-view-impressao" onclick="switchTab('impressao')">
            <i data-lucide="printer" class="w-4 h-4"></i>
            Impressão
        </button>
    </div>

    <!-- Conteúdo: Geral -->
    <div id="content-geral" class="tab-content">
        <div class="space-y-6">
            <!-- Informações da Empresa -->
            <div class="bg-card rounded-xl border border-border shadow-sm">
                <div class="p-6 border-b border-border">
                    <h3 class="text-lg font-semibold flex items-center gap-2">
                        <i data-lucide="building-2" class="w-5 h-5"></i>
                        Informações da Empresa
                    </h3>
                    <p class="text-sm text-muted-foreground mt-1">Dados básicos da sua empresa/confeitaria</p>
                </div>
                <form action="{{ route('dashboard.settings.general.save') }}" method="POST" class="p-6 space-y-4">
                    @csrf
                    <div class="grid md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="text-sm font-medium" for="company_name">Nome da Empresa / Confeitaria *</label>
                            <input name="company_name" id="company_name" value="{{ $generalSettings['company_name'] ?? '' }}" 
                                   class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm" 
                                   placeholder="Ex.: Confeitaria Pro" required>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium" for="company_phone">Telefone / WhatsApp *</label>
                            <input name="company_phone" id="company_phone" value="{{ $generalSettings['company_phone'] ?? '' }}" 
                                   class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm" 
                                   placeholder="(11) 99999-9999" required>
                            <p class="text-xs text-muted-foreground">Usado para notificações via WhatsApp</p>
                        </div>
                        <div class="space-y-2 md:col-span-2">
                            <label class="text-sm font-medium" for="company_email">E-mail *</label>
                            <input type="email" name="company_email" id="company_email" value="{{ $generalSettings['company_email'] ?? '' }}" 
                                   class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm" 
                                   placeholder="contato@confeitaria.com" required>
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="btn-primary">
                            <i data-lucide="save" class="w-4 h-4"></i>
                            Salvar Informações
                        </button>
                    </div>
                </form>
            </div>

            <!-- Configurações Regionais -->
            <div class="bg-card rounded-xl border border-border shadow-sm">
                <div class="p-6 border-b border-border">
                    <h3 class="text-lg font-semibold flex items-center gap-2">
                        <i data-lucide="globe" class="w-5 h-5"></i>
                        Configurações Regionais
                    </h3>
                    <p class="text-sm text-muted-foreground mt-1">Idioma e moeda do sistema</p>
                </div>
                <form action="{{ route('dashboard.settings.general.save') }}" method="POST" class="p-6 space-y-4">
                    @csrf
                    <div class="grid md:grid-cols-2 gap-4">
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
                    <div class="flex justify-end">
                        <button type="submit" class="btn-primary">
                            <i data-lucide="save" class="w-4 h-4"></i>
                            Salvar Configurações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Conteúdo: Personalização -->
    <div id="content-personalizacao" class="tab-content hidden">
        <div class="space-y-6">
            <!-- Cor do Tema -->
            <div class="bg-card rounded-xl border border-border shadow-sm">
                <div class="p-6 border-b border-border">
                    <h3 class="text-lg font-semibold flex items-center gap-2">
                        <i data-lucide="palette" class="w-5 h-5"></i>
                        Cor do Tema
                    </h3>
                    <p class="text-sm text-muted-foreground mt-1">Personalize a cor principal do seu sistema</p>
                </div>
                <form action="{{ route('dashboard.settings.personalization.save') }}" method="POST" class="p-6 space-y-6">
                    @csrf
                    @php
                        $selectedColor = $personalizationSettings['theme_color'] ?? '#f59e0b';
                    @endphp
                    
                    <!-- Color Picker Visual -->
                    <div class="space-y-3">
                        <label class="text-sm font-medium block">Escolha uma cor personalizada</label>
                        <div class="flex items-center gap-4">
                            <input type="color" 
                                   id="custom_color_picker" 
                                   value="{{ $selectedColor }}"
                                   class="w-20 h-20 rounded-lg border-2 border-gray-300 cursor-pointer hover:border-gray-400 transition-all"
                                   onchange="updateColorFromPicker(this.value)">
                            <div class="flex-1">
                                <input type="text" 
                                       id="theme_color_input" 
                                       name="theme_color" 
                                       value="{{ $selectedColor }}"
                                       pattern="^#[0-9A-Fa-f]{6}$"
                                       class="flex h-10 w-full max-w-xs rounded-md border border-input bg-background px-3 py-2 text-sm font-mono"
                                       placeholder="#f59e0b"
                                       onchange="updateColorFromInput(this.value)">
                                <p class="text-xs text-muted-foreground mt-1">Digite o código hexadecimal da cor (ex: #f59e0b)</p>
                            </div>
                        </div>
                    </div>

                    <!-- Cores Pré-definidas -->
                    <div class="space-y-3">
                        <label class="text-sm font-medium block">Ou escolha uma cor pré-definida</label>
                        <div class="grid grid-cols-4 md:grid-cols-8 gap-3">
                            @php
                                $colors = [
                                    ['name' => 'Rosa', 'value' => '#f472b6'],
                                    ['name' => 'Azul', 'value' => '#3b82f6'],
                                    ['name' => 'Verde', 'value' => '#10b981'],
                                    ['name' => 'Roxo', 'value' => '#8b5cf6'],
                                    ['name' => 'Laranja', 'value' => '#f97316'],
                                    ['name' => 'Vermelho', 'value' => '#ef4444'],
                                    ['name' => 'Amarelo', 'value' => '#eab308'],
                                    ['name' => 'Turquesa', 'value' => '#14b8a6'],
                                ];
                            @endphp
                            @foreach($colors as $color)
                                <button type="button" 
                                        class="relative w-12 h-12 rounded-lg border-2 transition-all hover:scale-110 {{ $selectedColor === $color['value'] ? 'border-gray-800 ring-2 ring-offset-2' : 'border-gray-300 hover:border-gray-400' }}" 
                                        style="background-color: {{ $color['value'] }}"
                                        data-color="{{ $color['value'] }}" 
                                        onclick="selectPresetColor('{{ $color['value'] }}')"
                                        title="{{ $color['name'] }}">
                                    @if($selectedColor === $color['value'])
                                        <div class="absolute inset-0 flex items-center justify-center">
                                            <svg class="w-6 h-6 text-white drop-shadow-lg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        </div>
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="btn-primary">
                            <i data-lucide="save" class="w-4 h-4"></i>
                            Salvar Cor do Tema
                        </button>
                    </div>
                </form>
            </div>

            <!-- Logotipo e Favicon -->
            <div class="bg-card rounded-xl border border-border shadow-sm">
                <div class="p-6 border-b border-border">
                    <h3 class="text-lg font-semibold flex items-center gap-2">
                        <i data-lucide="image" class="w-5 h-5"></i>
                        Logotipo e Favicon
                    </h3>
                    <p class="text-sm text-muted-foreground mt-1">Personalize a identidade visual do sistema</p>
                </div>
                <form action="{{ route('dashboard.settings.personalization.save') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
                    @csrf
                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="space-y-3">
                            <label class="text-sm font-medium">Logotipo</label>
                            @if(isset($personalizationSettings['logo_url']))
                                <img src="{{ $personalizationSettings['logo_url'] }}" alt="Logo" class="h-16 object-contain">
                            @endif
                            <input type="file" name="logo" accept="image/*" 
                                   class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm file:border-0 file:bg-transparent file:text-sm file:font-medium">
                            <p class="text-xs text-muted-foreground">PNG ou JPG, máximo 2MB</p>
                        </div>
                        <div class="space-y-3">
                            <label class="text-sm font-medium">Favicon</label>
                            @if(isset($personalizationSettings['favicon_url']))
                                <img src="{{ $personalizationSettings['favicon_url'] }}" alt="Favicon" class="h-16 w-16 object-contain">
                            @endif
                            <input type="file" name="favicon" accept="image/*" 
                                   class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm file:border-0 file:bg-transparent file:text-sm file:font-medium">
                            <p class="text-xs text-muted-foreground">Ícone 32x32px, PNG recomendado</p>
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="btn-primary">
                            <i data-lucide="save" class="w-4 h-4"></i>
                            Salvar Imagens
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Conteúdo: PWA & Notificações -->
    <div id="content-pwa" class="tab-content hidden">
        <div class="space-y-6">
            <!-- Instalar Aplicativo (PWA) -->
            <div class="bg-card rounded-xl border border-border shadow-sm">
                <div class="p-6 border-b border-border">
                    <h3 class="text-lg font-semibold flex items-center gap-2">
                        <i data-lucide="download" class="w-5 h-5"></i>
                        Instalar Aplicativo
                    </h3>
                    <p class="text-sm text-muted-foreground mt-1">Instale a Confeitaria Pro no seu dispositivo para acesso rápido e offline</p>
                </div>
                <div class="p-6">
                    <div id="pwa-install-container" class="space-y-4">
                        <!-- Botão de Instalação (mostrado apenas se PWA for suportado) -->
                        <div id="pwa-install-prompt" class="hidden">
                            <button id="btn-install-pwa" class="btn-primary w-full md:w-auto">
                                <i data-lucide="smartphone" class="w-4 h-4"></i>
                                Instalar Aplicativo
                            </button>
                            <p class="text-xs text-muted-foreground mt-2">
                                Instale o app para acessar rapidamente e usar offline
                            </p>
                        </div>

                        <!-- Já Instalado -->
                        <div id="pwa-already-installed" class="hidden">
                            <div class="flex items-center gap-3 p-4 bg-green-50 border border-green-200 rounded-lg">
                                <i data-lucide="check-circle" class="w-5 h-5 text-green-600"></i>
                                <div>
                                    <p class="font-medium text-green-900">Aplicativo já instalado</p>
                                    <p class="text-sm text-green-700">Você pode acessar o app pela tela inicial do seu dispositivo</p>
                                </div>
                            </div>
                        </div>

                        <!-- Não Suportado -->
                        <div id="pwa-not-supported">
                            <div class="flex items-center gap-3 p-4 bg-gray-50 border border-gray-200 rounded-lg">
                                <i data-lucide="info" class="w-5 h-5 text-gray-600"></i>
                                <div>
                                    <p class="font-medium text-gray-900">Verificando suporte...</p>
                                    <p class="text-sm text-gray-700">Aguarde enquanto verificamos se seu navegador suporta instalação</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Instruções Manuais -->
                    <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <h4 class="font-semibold text-blue-900 mb-2 flex items-center gap-2">
                            <i data-lucide="help-circle" class="w-4 h-4"></i>
                            Como instalar manualmente
                        </h4>
                        <div class="text-sm text-blue-800 space-y-2">
                            <p><strong>No Chrome/Edge (Desktop):</strong> Clique no ícone de instalação (➕) na barra de endereços</p>
                            <p><strong>No Safari (iOS):</strong> Toque em "Compartilhar" → "Adicionar à Tela Inicial"</p>
                            <p><strong>No Chrome (Android):</strong> Menu (⋮) → "Adicionar à tela inicial"</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notificações Push -->
            <div class="bg-card rounded-xl border border-border shadow-sm">
                <div class="p-6 border-b border-border">
                    <h3 class="text-lg font-semibold flex items-center gap-2">
                        <i data-lucide="bell" class="w-5 h-5"></i>
                        Notificações Push
                    </h3>
                    <p class="text-sm text-muted-foreground mt-1">Receba alertas sobre novos pedidos e atualizações importantes</p>
                </div>
                <div class="p-6 space-y-4">
                    <!-- Status das Notificações -->
                    <div id="notification-status-container">
                        <div id="notification-enabled" class="hidden flex items-center gap-3 p-4 bg-green-50 border border-green-200 rounded-lg">
                            <i data-lucide="check-circle" class="w-5 h-5 text-green-600"></i>
                            <div class="flex-1">
                                <p class="font-medium text-green-900">Notificações ativadas ✓</p>
                                <p class="text-sm text-green-700">Você receberá alertas sobre novos pedidos e atualizações</p>
                            </div>
                            <button id="btn-disable-notifications" class="btn-outline text-sm">
                                Desativar
                            </button>
                        </div>

                        <div id="notification-disabled" class="hidden flex items-center gap-3 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <i data-lucide="bell-off" class="w-5 h-5 text-yellow-600"></i>
                            <div class="flex-1">
                                <p class="font-medium text-yellow-900">Notificações desativadas</p>
                                <p class="text-sm text-yellow-700">Ative para receber alertas em tempo real</p>
                            </div>
                            <button id="btn-enable-notifications" class="btn-primary text-sm">
                                <i data-lucide="bell" class="w-4 h-4"></i>
                                Ativar
                            </button>
                        </div>

                        <div id="notification-blocked" class="hidden flex items-center gap-3 p-4 bg-red-50 border border-red-200 rounded-lg">
                            <i data-lucide="x-circle" class="w-5 h-5 text-red-600"></i>
                            <div>
                                <p class="font-medium text-red-900">Notificações bloqueadas</p>
                                <p class="text-sm text-red-700">Você bloqueou as notificações. Para ativar, vá nas configurações do navegador.</p>
                            </div>
                        </div>

                        <div id="notification-loading">
                            <div class="flex items-center gap-3 p-4 bg-gray-50 border border-gray-200 rounded-lg">
                                <i data-lucide="loader-2" class="w-5 h-5 text-gray-600 animate-spin"></i>
                                <p class="text-gray-700">Verificando status das notificações...</p>
                            </div>
                        </div>
                    </div>

                    <!-- Enviar Notificação de Teste -->
                    <div class="pt-4 border-t">
                        <button id="btn-test-notification" class="btn-outline w-full md:w-auto" disabled>
                            <i data-lucide="send" class="w-4 h-4"></i>
                            Enviar Notificação de Teste
                        </button>
                        <p class="text-xs text-muted-foreground mt-2">
                            Teste se as notificações estão funcionando corretamente
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Conteúdo: Impressão (incluir o conteúdo da página printing.blade.php) -->
    <div id="content-impressao" class="tab-content hidden">
        @include('dashboard.settings.printing-content')
    </div>
</div>

@push('scripts')
<!-- QZ Tray SDK (para impressão térmica) -->
<script src="https://cdn.jsdelivr.net/npm/qz-tray@2.2/qz-tray.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // ===========================================
    // SISTEMA DE ABAS
    // ===========================================
    window.switchTab = function(tabName) {
        // Ocultar todos os conteúdos
        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.add('hidden');
        });
        
        // Remover 'active' de todos os botões
        document.querySelectorAll('.view-btn').forEach(btn => {
            btn.classList.remove('active');
            btn.classList.add('inactive');
        });
        
        // Mostrar conteúdo selecionado
        document.getElementById(`content-${tabName}`).classList.remove('hidden');
        
        // Ativar botão selecionado
        const activeBtn = document.getElementById(`btn-view-${tabName}`);
        activeBtn.classList.add('active');
        activeBtn.classList.remove('inactive');
        
        // Reinicializar ícones Lucide
        if (window.lucide) lucide.createIcons();
    };
    
    // ===========================================
    // PERSONALIZAÇÃO: COR DO TEMA
    // ===========================================
    window.updateColorFromPicker = function(color) {
        document.getElementById('theme_color_input').value = color;
        updateColorSelection(color);
    };
    
    window.updateColorFromInput = function(color) {
        if (/^#[0-9A-Fa-f]{6}$/.test(color)) {
            document.getElementById('custom_color_picker').value = color;
            updateColorSelection(color);
        }
    };
    
    window.selectPresetColor = function(color) {
        document.getElementById('theme_color_input').value = color;
        document.getElementById('custom_color_picker').value = color;
        updateColorSelection(color);
    };
    
    function updateColorSelection(selectedColor) {
        document.querySelectorAll('[data-color]').forEach(btn => {
            const color = btn.dataset.color;
            if (color === selectedColor) {
                btn.classList.add('border-gray-800', 'ring-2', 'ring-offset-2');
                btn.classList.remove('border-gray-300', 'hover:border-gray-400');
            } else {
                btn.classList.remove('border-gray-800', 'ring-2', 'ring-offset-2');
                btn.classList.add('border-gray-300', 'hover:border-gray-400');
            }
        });
    }
    
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
            pwaNotSupported.querySelector('p.font-medium').textContent = 'Instalação não disponível';
            pwaNotSupported.querySelector('p.text-sm').textContent = 'Seu navegador não suporta instalação de aplicativo ou você já está usando o app instalado';
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
            notificationDisabled.classList.remove('hidden');
            notificationDisabled.querySelector('p.font-medium').textContent = 'Notificações não suportadas';
            notificationDisabled.querySelector('p.text-sm').textContent = 'Seu navegador não suporta notificações push';
            btnEnableNotifications.classList.add('hidden');
            return;
        }
        
        const permission = Notification.permission;
        notificationLoading.classList.add('hidden');
        
        if (permission === 'granted') {
            notificationEnabled.classList.remove('hidden');
            btnTestNotification.disabled = false;
        } else if (permission === 'denied') {
            notificationBlocked.classList.remove('hidden');
        } else {
            notificationDisabled.classList.remove('hidden');
        }
    }
    
    // Ativar notificações
    if (btnEnableNotifications) {
        btnEnableNotifications.addEventListener('click', async () => {
            try {
                const permission = await Notification.requestPermission();
                
                if (permission === 'granted') {
                    notificationDisabled.classList.add('hidden');
                    notificationEnabled.classList.remove('hidden');
                    btnTestNotification.disabled = false;
                    
                    // Enviar notificação de boas-vindas
                    new Notification('Notificações Ativadas!', {
                        body: 'Você receberá alertas sobre novos pedidos e atualizações importantes.',
                        icon: '/favicon/android-chrome-192x192.png'
                    });
                } else if (permission === 'denied') {
                    notificationDisabled.classList.add('hidden');
                    notificationBlocked.classList.remove('hidden');
                }
            } catch (error) {
                console.error('Erro ao solicitar permissão:', error);
                alert('Erro ao ativar notificações. Tente novamente.');
            }
        });
    }
    
    // Desativar notificações (apenas visual - permissão não pode ser revogada via JS)
    if (btnDisableNotifications) {
        btnDisableNotifications.addEventListener('click', () => {
            alert('Para desativar completamente, vá em Configurações do navegador > Site > Notificações');
        });
    }
    
    // Enviar notificação de teste
    if (btnTestNotification) {
        btnTestNotification.addEventListener('click', () => {
            if (Notification.permission === 'granted') {
                new Notification('Teste de Notificação', {
                    body: 'Se você está vendo isso, as notificações estão funcionando perfeitamente!',
                    icon: '/favicon/android-chrome-192x192.png',
                    badge: '/favicon/android-chrome-192x192.png'
                });
            }
        });
    }
    
    // Verificar status ao carregar
    checkNotificationStatus();
    
    // Inicializar ícones Lucide
    if (window.lucide) {
        lucide.createIcons();
    }
});
</script>
@endpush
@endsection
