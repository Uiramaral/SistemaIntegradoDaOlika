@extends('layouts.app')

@section('title', 'Pagamento Cartão - Pedido #' . $order->order_number)

@section('content')
<div class="py-8">
    <div class="container">
        <div class="max-w-2xl mx-auto">
            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">
                    💳 Pagamento com Cartão
                </h1>
                <p class="text-gray-600">
                    Pedido #{{ $order->order_number }} - {{ $order->customer->name }}
                </p>
                <p class="text-2xl font-bold text-orange-600 mt-4">
                    R$ {{ number_format($order->final_amount, 2, ',', '.') }}
                </p>
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

            <!-- Botão de Pagamento -->
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 text-center">
                    🛡️ Pagamento Seguro
                </h3>
                
                <div class="text-center mb-6">
                    <p class="text-gray-600 mb-4">
                        Clique no botão abaixo para ser redirecionado para o MercadoPago
                    </p>
                    
                    @if($order->payment_link)
                    <a href="{{ $order->payment_link }}" 
                       target="_blank"
                       class="inline-flex items-center bg-blue-600 text-white px-8 py-4 rounded-lg font-semibold text-lg hover:bg-blue-700 transition">
                        <i class="fas fa-credit-card mr-3"></i>
                        Pagar com Cartão
                    </a>
                    @else
                    <button onclick="createPaymentPreference()" 
                            class="inline-flex items-center bg-blue-600 text-white px-8 py-4 rounded-lg font-semibold text-lg hover:bg-blue-700 transition">
                        <i class="fas fa-credit-card mr-3"></i>
                        Gerar Pagamento
                    </button>
                    @endif
                </div>

                <!-- Informações de Segurança -->
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex items-center mb-2">
                        <i class="fas fa-shield-alt text-green-600 mr-2"></i>
                        <span class="text-sm font-medium text-green-800">Pagamento 100% Seguro</span>
                    </div>
                    <ul class="text-xs text-green-700 space-y-1">
                        <li>• Dados protegidos com criptografia SSL</li>
                        <li>• Processado pelo MercadoPago</li>
                        <li>• Suporte a cartões de crédito e débito</li>
                        <li>• Parcelamento em até 12x</li>
                    </ul>
                </div>
            </div>

            <!-- Instruções -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <h3 class="text-sm font-medium text-blue-800 mb-2">
                    📝 Como Pagar
                </h3>
                <ul class="text-sm text-blue-700 space-y-1">
                    <li>1. Clique em "Pagar com Cartão"</li>
                    <li>2. Você será redirecionado para o MercadoPago</li>
                    <li>3. Preencha os dados do seu cartão</li>
                    <li>4. Confirme o pagamento</li>
                    <li>5. Você será redirecionado de volta</li>
                </ul>
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
    function createPaymentPreference() {
        const button = event.target;
        const originalText = button.innerHTML;
        
        button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Gerando...';
        button.disabled = true;

        fetch(`{{ route('api.payment.preference', $order->id) }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = data.payment_link;
            } else {
                showNotification('Erro ao gerar pagamento: ' + data.error, 'error');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            showNotification('Erro ao gerar pagamento', 'error');
        })
        .finally(() => {
            button.innerHTML = originalText;
            button.disabled = false;
        });
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
