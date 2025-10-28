@extends('layouts.admin')

@section('title', 'Configurações de Pagamento')

@push('styles')
<style>
    .form-input {
        @apply w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent;
    }
    
    .btn-primary {
        @apply bg-orange-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-orange-700 transition;
    }
    
    .btn-secondary {
        @apply bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 transition;
    }
    
    .notification {
        @apply fixed top-4 right-4 px-6 py-3 rounded-lg text-white z-50;
    }
    
    .notification.success {
        @apply bg-green-500;
    }
    
    .notification.error {
        @apply bg-red-500;
    }
    
    .notification.info {
        @apply bg-blue-500;
    }
</style>
@endpush

@section('content')
<div class="py-8">
    <div class="max-w-4xl mx-auto px-4">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">
                    ⚙️ Configurações de Pagamento
                </h1>
                <p class="text-gray-600">
                    Configure as integrações de pagamento e modo de teste
                </p>
            </div>

            <!-- Formulário de Configurações -->
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                <form id="payment-settings-form">
                    @csrf
                    
                    <!-- MercadoPago -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            🔗 MercadoPago
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Token de Acesso
                                </label>
                                <input type="text" 
                                       name="mercadopago_access_token" 
                                       value="{{ $settings['mercadopago_access_token']->value ?? '' }}"
                                       class="form-input"
                                       placeholder="APP_USR-...">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Chave Pública
                                </label>
                                <input type="text" 
                                       name="mercadopago_public_key" 
                                       value="{{ $settings['mercadopago_public_key']->value ?? '' }}"
                                       class="form-input"
                                       placeholder="APP_USR-...">
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Ambiente
                            </label>
                            <select name="mercadopago_environment" 
                                    class="form-input">
                                <option value="sandbox" {{ ($settings['mercadopago_environment']->value ?? 'sandbox') === 'sandbox' ? 'selected' : '' }}>
                                    Sandbox (Teste)
                                </option>
                                <option value="production" {{ ($settings['mercadopago_environment']->value ?? '') === 'production' ? 'selected' : '' }}>
                                    Produção
                                </option>
                            </select>
                        </div>
                    </div>

                    <!-- PIX -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            💳 PIX
                        </h3>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Tempo de Expiração (minutos)
                            </label>
                            <input type="number" 
                                   name="pix_expiration_minutes" 
                                   value="{{ $settings['pix_expiration_minutes']->value ?? 30 }}"
                                   min="1" 
                                   max="1440"
                                   class="form-input">
                        </div>
                    </div>

                    <!-- Modo de Teste -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            🧪 Modo de Teste
                        </h3>
                        
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <div class="flex items-center mb-3">
                                <input type="checkbox" 
                                       id="test_mode_enabled" 
                                       name="test_mode_enabled" 
                                       {{ ($settings['test_mode_enabled']->value ?? 'false') === 'true' ? 'checked' : '' }}
                                       class="h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 rounded">
                                <label for="test_mode_enabled" class="ml-2 text-sm font-medium text-gray-700">
                                    Ativar Modo de Teste
                                </label>
                            </div>
                            <p class="text-sm text-yellow-700">
                                Quando ativado, todos os valores de pagamento serão convertidos para valores entre 1-10 centavos, 
                                facilitando os testes mesmo em produção.
                            </p>
                        </div>
                    </div>

                    <!-- Botões -->
                    <div class="flex flex-col sm:flex-row gap-4">
                        <button type="submit" 
                                class="flex-1 btn-primary">
                            <i class="fas fa-save mr-2"></i>
                            Salvar Configurações
                        </button>
                        
                        <button type="button" 
                                onclick="testConnection()" 
                                class="flex-1 btn-secondary">
                            <i class="fas fa-wifi mr-2"></i>
                            Testar Conexão
                        </button>
                    </div>
                </form>
            </div>

            <!-- Status Atual -->
            <div class="bg-gray-50 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    📊 Status Atual
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-white rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-globe text-blue-600 text-xl"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900">Ambiente</p>
                                <p class="text-sm text-gray-600" id="current-environment">
                                    {{ $settings['mercadopago_environment']->value ?? 'sandbox' }}
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-flask text-green-600 text-xl"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900">Modo de Teste</p>
                                <p class="text-sm text-gray-600" id="test-mode-status">
                                    {{ ($settings['test_mode_enabled']->value ?? 'false') === 'true' ? 'Ativado' : 'Desativado' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('payment-settings-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const button = this.querySelector('button[type="submit"]');
        const originalText = button.innerHTML;
        
        button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Salvando...';
        button.disabled = true;

        fetch('{{ route("admin.payment-settings.update") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                updateStatusDisplay();
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            showNotification('Erro ao salvar configurações', 'error');
        })
        .finally(() => {
            button.innerHTML = originalText;
            button.disabled = false;
        });
    });

    function testConnection() {
        const button = event.target;
        const originalText = button.innerHTML;
        
        button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Testando...';
        button.disabled = true;

        fetch('{{ route("admin.payment-settings.test-connection") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            showNotification('Erro ao testar conexão', 'error');
        })
        .finally(() => {
            button.innerHTML = originalText;
            button.disabled = false;
        });
    }

    function updateStatusDisplay() {
        // Atualizar status na tela
        fetch('{{ route("admin.payment-settings.get") }}')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('current-environment').textContent = data.settings.mercadopago_environment;
                    document.getElementById('test-mode-status').textContent = 
                        data.settings.test_mode_enabled === 'true' ? 'Ativado' : 'Desativado';
                }
            });
    }

    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }
</script>
@endpush
@endsection
