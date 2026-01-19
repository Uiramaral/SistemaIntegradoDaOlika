@extends('layouts.admin')

@section('title', 'Integrações de APIs')
@section('page_title', 'Integrações de APIs')
@section('page_subtitle', 'Configure e gerencie suas integrações com APIs externas')

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

    <!-- Grid de Integrações -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($providers as $providerKey => $provider)
            @php
                $integration = $integrations[$providerKey] ?? null;
                $isActive = $integration && $integration->is_enabled;
                $lastTest = $integration?->last_test_status;
                $colorClass = "text-{$provider['color']}-600";
                $bgClass = "bg-{$provider['color']}-50";
                $borderClass = "border-{$provider['color']}-200";
            @endphp

            <div class="bg-card rounded-xl border border-border shadow-sweetspot hover:shadow-lg transition-all duration-200 overflow-hidden">
                <!-- Header do Card -->
                <div class="p-6 {{ $bgClass }} border-b {{ $borderClass }}">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="p-3 bg-white rounded-lg shadow-sm">
                                <i data-lucide="{{ $provider['icon'] }}" class="h-6 w-6 {{ $colorClass }}"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-foreground text-lg">{{ $provider['name'] }}</h3>
                                @if($lastTest)
                                    <span class="text-xs px-2 py-1 rounded-full {{ $lastTest === 'success' ? 'bg-success/20 text-success' : 'bg-destructive/20 text-destructive' }}">
                                        {{ $lastTest === 'success' ? '✓ Testado' : '✗ Erro' }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Toggle Ativo/Inativo -->
                        <form method="POST" action="{{ route('dashboard.integrations.toggle', $providerKey) }}" class="inline">
                            @csrf
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" 
                                       {{ $isActive ? 'checked' : '' }} 
                                       onchange="this.form.submit()"
                                       class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
                            </label>
                        </form>
                    </div>
                </div>

                <!-- Form de Configuração -->
                <form method="POST" action="{{ route('dashboard.integrations.update', $providerKey) }}" class="p-6 space-y-4">
                    @csrf

                    <!-- Credenciais -->
                    @foreach($provider['fields'] as $fieldKey => $field)
                        <div>
                            <label class="block text-sm font-medium text-foreground mb-2">
                                {{ is_array($field['label']) ? json_encode($field['label']) : $field['label'] }}
                                @if($field['required'] ?? false)
                                    <span class="text-destructive">*</span>
                                @endif
                            </label>
                            
                            @if($field['type'] === 'password')
                                <input type="password" 
                                       name="credentials[{{ $fieldKey }}]" 
                                       value="{{ $integration?->getCredential($fieldKey) ?? '' }}"
                                       placeholder="{{ $field['placeholder'] ?? '••••••••' }}"
                                       class="w-full px-3 py-2 border border-border rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition"
                                       {{ ($field['required'] ?? false) ? 'required' : '' }}>
                            @elseif($field['type'] === 'email')
                                <input type="email" 
                                       name="credentials[{{ $fieldKey }}]" 
                                       value="{{ $integration?->getCredential($fieldKey) ?? '' }}"
                                       placeholder="{{ $field['placeholder'] ?? 'email@exemplo.com' }}"
                                       class="w-full px-3 py-2 border border-border rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition"
                                       {{ ($field['required'] ?? false) ? 'required' : '' }}>
                            @elseif($field['type'] === 'url')
                                <input type="url" 
                                       name="credentials[{{ $fieldKey }}]" 
                                       value="{{ $integration?->getCredential($fieldKey) ?? '' }}"
                                       placeholder="{{ $field['placeholder'] ?? 'https://api.exemplo.com' }}"
                                       class="w-full px-3 py-2 border border-border rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition"
                                       {{ ($field['required'] ?? false) ? 'required' : '' }}>
                            @else
                                <input type="text" 
                                       name="credentials[{{ $fieldKey }}]" 
                                       value="{{ $integration?->getCredential($fieldKey) ?? '' }}"
                                       placeholder="{{ $field['placeholder'] ?? '' }}"
                                       class="w-full px-3 py-2 border border-border rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition"
                                       {{ ($field['required'] ?? false) ? 'required' : '' }}>
                            @endif
                        </div>
                    @endforeach

                    <!-- Settings (Configurações Avançadas) -->
                    @if(isset($provider['settings_fields']) && count($provider['settings_fields']) > 0)
                        <details class="border-t pt-4">
                            <summary class="cursor-pointer text-sm font-medium text-muted-foreground hover:text-foreground flex items-center gap-2">
                                <i data-lucide="settings-2" class="h-4 w-4"></i>
                                Configurações Avançadas
                            </summary>
                            <div class="mt-4 space-y-4">
                                @foreach($provider['settings_fields'] as $settingKey => $setting)
                                    <div>
                                        <label class="block text-sm font-medium text-foreground mb-2">
                                            {{ is_array($setting['label']) ? json_encode($setting['label']) : $setting['label'] }}
                                        </label>

                                        @if($setting['type'] === 'select')
                                            <select name="settings[{{ $settingKey }}]" 
                                                    class="w-full px-3 py-2 border border-border rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition">
                                                @foreach($setting['options'] as $optionValue => $optionLabel)
                                                    @php
                                                        // Suporta tanto array simples quanto associativo
                                                        $value = is_numeric($optionValue) ? $optionLabel : $optionValue;
                                                        $label = is_numeric($optionValue) ? $optionLabel : $optionLabel;
                                                        $currentValue = $integration?->getSetting($settingKey, $setting['default'] ?? null) ?? '';
                                                    @endphp
                                                    <option value="{{ $value }}" 
                                                            {{ $currentValue === $value ? 'selected' : '' }}>
                                                        {{ $label }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        @elseif($setting['type'] === 'number')
                                            <input type="number" 
                                                   name="settings[{{ $settingKey }}]" 
                                                   value="{{ $integration?->getSetting($settingKey, $setting['default'] ?? '') ?? '' }}"
                                                   min="{{ $setting['min'] ?? '' }}"
                                                   max="{{ $setting['max'] ?? '' }}"
                                                   step="{{ $setting['step'] ?? 'any' }}"
                                                   class="w-full px-3 py-2 border border-border rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition">
                                        @elseif($setting['type'] === 'checkbox')
                                            <label class="flex items-center gap-2">
                                                <input type="checkbox" 
                                                       name="settings[{{ $settingKey }}]" 
                                                       value="1"
                                                       {{ ($integration?->getSetting($settingKey, $setting['default'] ?? false) ?? false) ? 'checked' : '' }}
                                                       class="rounded border-border text-primary-600 focus:ring-primary-500">
                                                <span class="text-sm text-muted-foreground">Habilitar</span>
                                            </label>
                                        @else
                                            <input type="text" 
                                                   name="settings[{{ $settingKey }}]" 
                                                   value="{{ $integration?->getSetting($settingKey, $setting['default'] ?? '') ?? '' }}"
                                                   class="w-full px-3 py-2 border border-border rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition">
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </details>
                    @endif

                    <!-- Botões de Ação -->
                    <div class="flex items-center gap-2 pt-4 border-t">
                        <button type="submit" 
                                class="flex-1 bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-lg font-medium transition-all duration-200 hover:shadow-lg flex items-center justify-center gap-2">
                            <i data-lucide="save" class="h-4 w-4"></i>
                            Salvar
                        </button>
                        <button type="button" 
                                onclick="testIntegration('{{ $providerKey }}')"
                                class="px-4 py-2 border border-border hover:bg-muted rounded-lg font-medium transition-all duration-200 flex items-center gap-2">
                            <i data-lucide="test-tube" class="h-4 w-4"></i>
                            Testar
                        </button>
                    </div>
                </form>

                <!-- Último Erro -->
                @if($integration && $integration->last_error)
                    <div class="px-6 pb-6">
                        <div class="bg-destructive/10 border border-destructive/30 rounded-lg p-3">
                            <p class="text-xs font-medium text-destructive mb-1">Último erro:</p>
                            <p class="text-xs text-muted-foreground">{{ Str::limit($integration->last_error, 100) }}</p>
                        </div>
                    </div>
                @endif
            </div>
        @endforeach
    </div>

</div>

@push('scripts')
<script>
function testIntegration(provider) {
    const button = event.target.closest('button');
    const originalHTML = button.innerHTML;
    
    button.disabled = true;
    button.innerHTML = '<i data-lucide="loader-2" class="h-4 w-4 animate-spin"></i> Testando...';
    lucide.createIcons();
    
    fetch(`/integrations/${provider}/test`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        button.disabled = false;
        button.innerHTML = originalHTML;
        lucide.createIcons();
        
        if (data.success) {
            alert('✓ Teste realizado com sucesso!\n\n' + data.message);
            window.location.reload();
        } else {
            alert('✗ Falha no teste:\n\n' + data.message);
        }
    })
    .catch(error => {
        button.disabled = false;
        button.innerHTML = originalHTML;
        lucide.createIcons();
        alert('Erro ao testar integração: ' + error.message);
    });
}
</script>
@endpush
@endsection
