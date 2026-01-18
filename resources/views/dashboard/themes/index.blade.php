@extends('dashboard.layouts.app')

@section('page_title', 'Configuração de Temas')
@section('page_subtitle', 'Personalize a aparência do seu PDV')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    @if(session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 p-4 text-green-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Preview Panel -->
        <div class="lg:col-span-1">
            <div class="rounded-lg border border-border bg-card shadow-sm sticky top-6">
                <div class="p-6 border-b border-border">
                    <h3 class="text-lg font-semibold text-foreground">Pré-visualização</h3>
                    <p class="text-sm text-muted-foreground mt-1">Veja como ficará o PDV</p>
                </div>
                
                <div class="p-6">
                    <div class="bg-theme-background rounded-lg border border-theme-border p-4 shadow-theme-shadow" 
                         id="theme-preview">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="font-semibold text-theme-text" style="font-family: var(--theme-font-family)">
                                Pedido #1234
                            </h4>
                            <span class="px-2 py-1 rounded-full text-xs bg-theme-primary text-white">
                                Em andamento
                            </span>
                        </div>
                        
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-theme-text" style="font-family: var(--theme-font-family)">Bolo de Chocolate</span>
                                <span class="font-medium text-theme-text">R$ 45,00</span>
                            </div>
                            
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-theme-text" style="font-family: var(--theme-font-family)">Pão Francês (6 unidades)</span>
                                <span class="font-medium text-theme-text">R$ 12,00</span>
                            </div>
                            
                            <div class="border-t border-theme-border pt-3 mt-3">
                                <div class="flex justify-between items-center">
                                    <span class="font-semibold text-theme-text">Total</span>
                                    <span class="font-bold text-theme-primary text-lg">R$ 57,00</span>
                                </div>
                            </div>
                            
                            <button class="w-full mt-4 py-2 rounded-lg bg-theme-primary text-white font-medium hover:bg-theme-primary/90 transition-colors"
                                    style="border-radius: var(--theme-border-radius)">
                                Finalizar Pedido
                            </button>
                        </div>
                    </div>
                    
                    <div class="mt-4 text-center">
                        <button type="button" 
                                onclick="resetPreview()" 
                                class="text-sm text-muted-foreground hover:text-theme-primary transition-colors">
                            Resetar Visualização
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Configuration Panel -->
        <div class="lg:col-span-2">
            <form action="{{ route('dashboard.themes.update') }}" method="POST" id="theme-form">
                @csrf
                
                <div class="space-y-6">
                    <!-- Branding Section -->
                    <div class="rounded-lg border border-border bg-card shadow-sm">
                        <div class="p-6 border-b border-border">
                            <h3 class="text-lg font-semibold text-foreground">Branding</h3>
                            <p class="text-sm text-muted-foreground mt-1">Personalize sua marca</p>
                        </div>
                        
                        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-foreground mb-2">
                                    Nome da Marca
                                </label>
                                <input type="text" 
                                       name="theme_brand_name" 
                                       value="{{ $themeSettings['theme_brand_name'] }}"
                                       class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm focus:border-ring focus:ring-1 focus:ring-ring">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-foreground mb-2">
                                    Logo URL
                                </label>
                                <input type="url" 
                                       name="theme_logo_url" 
                                       value="{{ $themeSettings['theme_logo_url'] }}"
                                       class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm focus:border-ring focus:ring-1 focus:ring-ring"
                                       placeholder="https://exemplo.com/logo.png">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-foreground mb-2">
                                    Favicon URL
                                </label>
                                <input type="url" 
                                       name="theme_favicon_url" 
                                       value="{{ $themeSettings['theme_favicon_url'] }}"
                                       class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm focus:border-ring focus:ring-1 focus:ring-ring"
                                       placeholder="https://exemplo.com/favicon.ico">
                            </div>
                        </div>
                    </div>

                    <!-- Colors Section -->
                    <div class="rounded-lg border border-border bg-card shadow-sm">
                        <div class="p-6 border-b border-border">
                            <h3 class="text-lg font-semibold text-foreground">Cores</h3>
                            <p class="text-sm text-muted-foreground mt-1">Escolha a paleta de cores</p>
                        </div>
                        
                        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Primary Color -->
                            <div>
                                <label class="block text-sm font-medium text-foreground mb-2">
                                    Cor Primária
                                    <span class="text-muted-foreground text-xs">(Botões principais)</span>
                                </label>
                                <div class="flex items-center gap-3">
                                    <input type="color" 
                                           name="theme_primary_color" 
                                           value="{{ $themeSettings['theme_primary_color'] }}"
                                           class="w-12 h-12 rounded-lg border border-input cursor-pointer"
                                           onchange="updatePreview()">
                                    <input type="text" 
                                           id="primary-color-input"
                                           value="{{ $themeSettings['theme_primary_color'] }}"
                                           class="flex-1 rounded-lg border border-input bg-background px-3 py-2 text-sm font-mono focus:border-ring focus:ring-1 focus:ring-ring"
                                           oninput="updateColor('primary', this.value)">
                                </div>
                                
                                <!-- Quick presets -->
                                <div class="mt-2 flex flex-wrap gap-2">
                                    <button type="button" class="w-6 h-6 rounded-full bg-orange-500 border-2 border-white shadow-sm" 
                                            onclick="setColor('primary', '#f97316')" title="Laranja"></button>
                                    <button type="button" class="w-6 h-6 rounded-full bg-purple-500 border-2 border-white shadow-sm" 
                                            onclick="setColor('primary', '#8b5cf6')" title="Roxo"></button>
                                    <button type="button" class="w-6 h-6 rounded-full bg-blue-500 border-2 border-white shadow-sm" 
                                            onclick="setColor('primary', '#3b82f6')" title="Azul"></button>
                                    <button type="button" class="w-6 h-6 rounded-full bg-green-500 border-2 border-white shadow-sm" 
                                            onclick="setColor('primary', '#10b981')" title="Verde"></button>
                                    <button type="button" class="w-6 h-6 rounded-full bg-red-500 border-2 border-white shadow-sm" 
                                            onclick="setColor('primary', '#ef4444')" title="Vermelho"></button>
                                </div>
                            </div>

                            <!-- Secondary Color -->
                            <div>
                                <label class="block text-sm font-medium text-foreground mb-2">
                                    Cor Secundária
                                    <span class="text-muted-foreground text-xs">(Elementos secundários)</span>
                                </label>
                                <div class="flex items-center gap-3">
                                    <input type="color" 
                                           name="theme_secondary_color" 
                                           value="{{ $themeSettings['theme_secondary_color'] }}"
                                           class="w-12 h-12 rounded-lg border border-input cursor-pointer"
                                           onchange="updatePreview()">
                                    <input type="text" 
                                           id="secondary-color-input"
                                           value="{{ $themeSettings['theme_secondary_color'] }}"
                                           class="flex-1 rounded-lg border border-input bg-background px-3 py-2 text-sm font-mono focus:border-ring focus:ring-1 focus:ring-ring"
                                           oninput="updateColor('secondary', this.value)">
                                </div>
                            </div>

                            <!-- Accent Color -->
                            <div>
                                <label class="block text-sm font-medium text-foreground mb-2">
                                    Cor de Destaque
                                    <span class="text-muted-foreground text-xs">(Sucessos, links)</span>
                                </label>
                                <div class="flex items-center gap-3">
                                    <input type="color" 
                                           name="theme_accent_color" 
                                           value="{{ $themeSettings['theme_accent_color'] }}"
                                           class="w-12 h-12 rounded-lg border border-input cursor-pointer"
                                           onchange="updatePreview()">
                                    <input type="text" 
                                           id="accent-color-input"
                                           value="{{ $themeSettings['theme_accent_color'] }}"
                                           class="flex-1 rounded-lg border border-input bg-background px-3 py-2 text-sm font-mono focus:border-ring focus:ring-1 focus:ring-ring"
                                           oninput="updateColor('accent', this.value)">
                                </div>
                            </div>

                            <!-- Background Color -->
                            <div>
                                <label class="block text-sm font-medium text-foreground mb-2">
                                    Cor de Fundo
                                </label>
                                <div class="flex items-center gap-3">
                                    <input type="color" 
                                           name="theme_background_color" 
                                           value="{{ $themeSettings['theme_background_color'] }}"
                                           class="w-12 h-12 rounded-lg border border-input cursor-pointer"
                                           onchange="updatePreview()">
                                    <input type="text" 
                                           id="background-color-input"
                                           value="{{ $themeSettings['theme_background_color'] }}"
                                           class="flex-1 rounded-lg border border-input bg-background px-3 py-2 text-sm font-mono focus:border-ring focus:ring-1 focus:ring-ring"
                                           oninput="updateColor('background', this.value)">
                                </div>
                            </div>

                            <!-- Text Color -->
                            <div>
                                <label class="block text-sm font-medium text-foreground mb-2">
                                    Cor do Texto
                                </label>
                                <div class="flex items-center gap-3">
                                    <input type="color" 
                                           name="theme_text_color" 
                                           value="{{ $themeSettings['theme_text_color'] }}"
                                           class="w-12 h-12 rounded-lg border border-input cursor-pointer"
                                           onchange="updatePreview()">
                                    <input type="text" 
                                           id="text-color-input"
                                           value="{{ $themeSettings['theme_text_color'] }}"
                                           class="flex-1 rounded-lg border border-input bg-background px-3 py-2 text-sm font-mono focus:border-ring focus:ring-1 focus:ring-ring"
                                           oninput="updateColor('text', this.value)">
                                </div>
                            </div>

                            <!-- Border Color -->
                            <div>
                                <label class="block text-sm font-medium text-foreground mb-2">
                                    Cor das Bordas
                                </label>
                                <div class="flex items-center gap-3">
                                    <input type="color" 
                                           name="theme_border_color" 
                                           value="{{ $themeSettings['theme_border_color'] }}"
                                           class="w-12 h-12 rounded-lg border border-input cursor-pointer"
                                           onchange="updatePreview()">
                                    <input type="text" 
                                           id="border-color-input"
                                           value="{{ $themeSettings['theme_border_color'] }}"
                                           class="flex-1 rounded-lg border border-input bg-background px-3 py-2 text-sm font-mono focus:border-ring focus:ring-1 focus:ring-ring"
                                           oninput="updateColor('border', this.value)">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Typography & Spacing Section -->
                    <div class="rounded-lg border border-border bg-card shadow-sm">
                        <div class="p-6 border-b border-border">
                            <h3 class="text-lg font-semibold text-foreground">Tipografia & Espaçamento</h3>
                            <p class="text-sm text-muted-foreground mt-1">Fontes e bordas</p>
                        </div>
                        
                        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-foreground mb-2">
                                    Família de Fontes
                                </label>
                                <select name="theme_font_family" 
                                        class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm focus:border-ring focus:ring-1 focus:ring-ring"
                                        onchange="updatePreview()">
                                    <option value="'Inter', -apple-system, BlinkMacSystemFont, sans-serif" 
                                            {{ $themeSettings['theme_font_family'] === "'Inter', -apple-system, BlinkMacSystemFont, sans-serif" ? 'selected' : '' }}>
                                        Inter (Padrão)
                                    </option>
                                    <option value="system-ui, -apple-system, sans-serif" 
                                            {{ $themeSettings['theme_font_family'] === "system-ui, -apple-system, sans-serif" ? 'selected' : '' }}>
                                        System UI
                                    </option>
                                    <option value="'Helvetica Neue', Arial, sans-serif" 
                                            {{ $themeSettings['theme_font_family'] === "'Helvetica Neue', Arial, sans-serif" ? 'selected' : '' }}>
                                        Helvetica/Arial
                                    </option>
                                    <option value="'Roboto', sans-serif" 
                                            {{ $themeSettings['theme_font_family'] === "'Roboto', sans-serif" ? 'selected' : '' }}>
                                        Roboto
                                    </option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-foreground mb-2">
                                    Raio das Bordas
                                </label>
                                <select name="theme_border_radius" 
                                        class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm focus:border-ring focus:ring-1 focus:ring-ring"
                                        onchange="updatePreview()">
                                    <option value="0px" {{ $themeSettings['theme_border_radius'] === '0px' ? 'selected' : '' }}>Quadrado</option>
                                    <option value="4px" {{ $themeSettings['theme_border_radius'] === '4px' ? 'selected' : '' }}>Pequeno</option>
                                    <option value="8px" {{ $themeSettings['theme_border_radius'] === '8px' ? 'selected' : '' }}>Médio</option>
                                    <option value="12px" {{ $themeSettings['theme_border_radius'] === '12px' ? 'selected' : '' }}>Grande (Padrão)</option>
                                    <option value="16px" {{ $themeSettings['theme_border_radius'] === '16px' ? 'selected' : '' }}>Extra Grande</option>
                                    <option value="9999px" {{ $themeSettings['theme_border_radius'] === '9999px' ? 'selected' : '' }}>Circular</option>
                                </select>
                            </div>
                            
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-foreground mb-2">
                                    Sombra dos Elementos
                                </label>
                                <select name="theme_shadow_style" 
                                        class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm focus:border-ring focus:ring-1 focus:ring-ring"
                                        onchange="updatePreview()">
                                    <option value="none" {{ $themeSettings['theme_shadow_style'] === 'none' ? 'selected' : '' }}>Nenhuma</option>
                                    <option value="0 1px 3px rgba(0,0,0,0.1)" {{ $themeSettings['theme_shadow_style'] === '0 1px 3px rgba(0,0,0,0.1)' ? 'selected' : '' }}>Leve</option>
                                    <option value="0 4px 6px rgba(0,0,0,0.1)" {{ $themeSettings['theme_shadow_style'] === '0 4px 6px rgba(0,0,0,0.1)' ? 'selected' : '' }}>Média</option>
                                    <option value="0 4px 12px rgba(0,0,0,0.08)" {{ $themeSettings['theme_shadow_style'] === '0 4px 12px rgba(0,0,0,0.08)' ? 'selected' : '' }}>Forte (Padrão)</option>
                                    <option value="0 10px 25px rgba(0,0,0,0.15)" {{ $themeSettings['theme_shadow_style'] === '0 10px 25px rgba(0,0,0,0.15)' ? 'selected' : '' }}>Muito Forte</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-wrap gap-3 justify-end">
                        <button type="button" 
                                onclick="resetToDefaults()" 
                                class="px-4 py-2 rounded-lg border border-input bg-background text-foreground hover:bg-muted transition-colors">
                            Restaurar Padrão
                        </button>
                        <button type="submit" 
                                class="px-6 py-2 rounded-lg bg-primary text-primary-foreground hover:bg-primary/90 font-medium transition-colors">
                            Salvar Alterações
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Custom CSS will be injected via JavaScript -->

<script>
// Atualizar preview em tempo real
function updatePreview() {
    const formData = new FormData(document.getElementById('theme-form'));
    const settings = {};
    
    for (let [key, value] of formData.entries()) {
        if (value) {
            settings[key] = value;
        }
    }
    
    updatePreviewStyles(settings);
}

// Atualizar cor específica
function updateColor(type, hex) {
    if (!/^#[0-9A-F]{6}$/i.test(hex)) return;
    
    document.querySelector(`input[name="theme_${type}_color"]`).value = hex;
    document.getElementById(`${type}-color-input`).value = hex;
    updatePreview();
}

// Definir cor rápida
function setColor(type, hex) {
    document.querySelector(`input[name="theme_${type}_color"]`).value = hex;
    document.getElementById(`${type}-color-input`).value = hex;
    updatePreview();
}

// Resetar preview
function resetPreview() {
    // Resetar todos os campos para valores padrão
    document.querySelector('input[name="theme_primary_color"]').value = '#f59e0b';
    document.querySelector('input[name="theme_secondary_color"]').value = '#8b5cf6';
    document.querySelector('input[name="theme_accent_color"]').value = '#10b981';
    document.querySelector('input[name="theme_background_color"]').value = '#ffffff';
    document.querySelector('input[name="theme_text_color"]').value = '#1f2937';
    document.querySelector('input[name="theme_border_color"]').value = '#e5e7eb';
    
    // Resetar inputs textuais
    document.getElementById('primary-color-input').value = '#f59e0b';
    document.getElementById('secondary-color-input').value = '#8b5cf6';
    document.getElementById('accent-color-input').value = '#10b981';
    document.getElementById('background-color-input').value = '#ffffff';
    document.getElementById('text-color-input').value = '#1f2937';
    document.getElementById('border-color-input').value = '#e5e7eb';
    
    updatePreview();
}

// Resetar para padrão
function resetToDefaults() {
    if (confirm('Tem certeza que deseja restaurar todas as configurações para o padrão?')) {
        fetch('{{ route("dashboard.themes.reset") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }
}

// Atualizar estilos do preview
function updatePreviewStyles(settings) {
    const preview = document.getElementById('theme-preview');
    if (!preview) return;
    
    // Aplicar cores
    if (settings.theme_primary_color) {
        preview.style.setProperty('--theme-primary', settings.theme_primary_color);
    }
    if (settings.theme_secondary_color) {
        preview.style.setProperty('--theme-secondary', settings.theme_secondary_color);
    }
    if (settings.theme_accent_color) {
        preview.style.setProperty('--theme-accent', settings.theme_accent_color);
    }
    if (settings.theme_background_color) {
        preview.style.setProperty('--theme-background', settings.theme_background_color);
    }
    if (settings.theme_text_color) {
        preview.style.setProperty('--theme-text', settings.theme_text_color);
    }
    if (settings.theme_border_color) {
        preview.style.setProperty('--theme-border', settings.theme_border_color);
    }
    
    // Aplicar tipografia
    if (settings.theme_font_family) {
        preview.style.setProperty('--theme-font-family', settings.theme_font_family);
    }
    
    // Aplicar bordas
    if (settings.theme_border_radius) {
        preview.style.setProperty('--theme-border-radius', settings.theme_border_radius);
    }
    
    // Aplicar sombras
    if (settings.theme_shadow_style) {
        preview.style.setProperty('--theme-shadow', settings.theme_shadow_style);
    }
}

// Event listeners para inputs coloridos
document.querySelectorAll('input[type="color"]').forEach(input => {
    input.addEventListener('input', updatePreview);
});

// Event listeners para selects
document.querySelectorAll('select').forEach(select => {
    select.addEventListener('change', updatePreview);
});
</script>
@endsection