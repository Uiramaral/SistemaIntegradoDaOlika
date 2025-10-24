@extends('layouts.app')

@section('title', 'Pagamento PIX - Pedido #' . $order->order_number)

@section('content')
<div class="py-8">
    <div class="container">
        <div class="max-w-2xl mx-auto">
            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">
                    💳 Pagamento PIX
                </h1>
                <p class="text-gray-600">
                    Pedido #{{ $order->order_number }} - {{ $order->customer->name }}
                </p>
                <p class="text-2xl font-bold text-orange-600 mt-4">
                    R$ {{ number_format($order->final_amount, 2, ',', '.') }}
                </p>
            </div>

            <!-- Status do Pagamento -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-clock text-blue-600 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">
                            Aguardando Pagamento
                        </h3>
                        <p class="text-sm text-blue-600">
                            Escaneie o QR Code ou copie o código PIX para pagar
                        </p>
                    </div>
                </div>
            </div>

            <!-- QR Code -->
            @if($order->pix_qr_code_base64)
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 text-center">
                    📱 Escaneie o QR Code
                </h3>
                <div class="flex justify-center">
                    <img src="data:image/png;base64,{{ $order->pix_qr_code_base64 }}" 
                         alt="QR Code PIX" 
                         class="w-64 h-64 border border-gray-200 rounded-lg">
                </div>
            </div>
            @endif

            <!-- Código PIX -->
            @if($order->pix_copy_paste)
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    📋 Código PIX para Copiar
                </h3>
                <div class="relative">
                    <textarea 
                        id="pix-code" 
                        readonly 
                        class="w-full p-3 border border-gray-300 rounded-lg font-mono text-sm bg-gray-50 resize-none"
                        rows="4">{{ $order->pix_copy_paste }}</textarea>
                    <button 
                        onclick="copyPixCode()" 
                        class="absolute top-2 right-2 bg-orange-600 text-white px-3 py-1 rounded text-sm hover:bg-orange-700 transition">
                        <i class="fas fa-copy mr-1"></i>
                        Copiar
                    </button>
                </div>
            </div>
            @endif

            <!-- Instruções -->
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                <h3 class="text-sm font-medium text-yellow-800 mb-2">
                    📝 Como Pagar
                </h3>
                <ul class="text-sm text-yellow-700 space-y-1">
                    <li>1. Abra o app do seu banco</li>
                    <li>2. Escolha "PIX" ou "Pagar"</li>
                    <li>3. Escaneie o QR Code ou cole o código</li>
                    <li>4. Confirme o pagamento</li>
                </ul>
            </div>

            <!-- Informações do Pedido -->
            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <h3 class="text-sm font-medium text-gray-900 mb-2">
                    📦 Resumo do Pedido
                </h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span>Subtotal:</span>
                        <span>R$ {{ number_format($order->total_amount, 2, ',', '.') }}</span>
                    </div>
                    @if($order->delivery_fee > 0)
                    <div class="flex justify-between">
                        <span>Taxa de Entrega:</span>
                        <span>R$ {{ number_format($order->delivery_fee, 2, ',', '.') }}</span>
                    </div>
                    @endif
                    @if($order->discount_amount > 0)
                    <div class="flex justify-between text-green-600">
                        <span>Desconto:</span>
                        <span>-R$ {{ number_format($order->discount_amount, 2, ',', '.') }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between font-bold text-lg border-t pt-2">
                        <span>Total:</span>
                        <span>R$ {{ number_format($order->final_amount, 2, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <!-- Botões de Ação -->
            <div class="flex flex-col sm:flex-row gap-4">
                <button 
                    onclick="checkPaymentStatus()" 
                    class="flex-1 bg-orange-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-orange-700 transition">
                    <i class="fas fa-sync-alt mr-2"></i>
                    Verificar Pagamento
                </button>
                
                <a href="{{ route('menu.index') }}" 
                   class="flex-1 bg-gray-300 text-gray-800 px-6 py-3 rounded-lg font-semibold hover:bg-gray-400 transition text-center">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Voltar ao Cardápio
                </a>
            </div>

            <!-- Modo de Teste -->
            @if(\App\Models\PaymentSetting::isTestModeEnabled())
            <div class="mt-6 bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex items-center">
                    <i class="fas fa-flask text-green-600 mr-2"></i>
                    <span class="text-sm font-medium text-green-800">
                        🧪 Modo de Teste Ativo - Valores entre 1-10 centavos
                    </span>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
    function copyPixCode() {
        const textarea = document.getElementById('pix-code');
        textarea.select();
        textarea.setSelectionRange(0, 99999);
        
        try {
            document.execCommand('copy');
            showNotification('Código PIX copiado!', 'success');
        } catch (err) {
            showNotification('Erro ao copiar código', 'error');
        }
    }

    function checkPaymentStatus() {
        const button = event.target;
        const originalText = button.innerHTML;
        
        button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Verificando...';
        button.disabled = true;

        fetch(`{{ route('api.payment.status', $order->id) }}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.payment.status === 'approved') {
                        showNotification('Pagamento aprovado! Redirecionando...', 'success');
                        setTimeout(() => {
                            window.location.href = '{{ route("order.success", $order->id) }}';
                        }, 2000);
                    } else if (data.payment.status === 'pending') {
                        showNotification('Pagamento ainda pendente', 'info');
                    } else {
                        showNotification('Status: ' + data.payment.status, 'info');
                    }
                } else {
                    showNotification('Erro ao verificar pagamento', 'error');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                showNotification('Erro ao verificar pagamento', 'error');
            })
            .finally(() => {
                button.innerHTML = originalText;
                button.disabled = false;
            });
    }

    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg text-white z-50 ${
            type === 'success' ? 'bg-green-500' : 
            type === 'error' ? 'bg-red-500' : 'bg-blue-500'
        }`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }

    // Verificar pagamento automaticamente a cada 10 segundos
    setInterval(() => {
        if (document.visibilityState === 'visible') {
            checkPaymentStatus();
        }
    }, 10000);
</script>
@endpush
@endsection
