@extends('layouts.app')

@section('title', 'Cupons de Desconto')

@section('content')
<div class="py-8">
    <div class="container">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">
                    🎟️ Cupons de Desconto
                </h1>
                <p class="text-gray-600">
                    Aproveite nossos cupons especiais e economize em seus pedidos!
                </p>
            </div>

            <!-- Lista de Cupons -->
            @if($coupons->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($coupons as $coupon)
                <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-orange-500">
                    <!-- Cabeçalho do Cupom -->
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <div class="bg-orange-100 rounded-full p-2 mr-3">
                                <i class="fas fa-ticket-alt text-orange-600"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900">{{ $coupon->name }}</h3>
                                <p class="text-sm text-gray-600">{{ $coupon->code }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-2xl font-bold text-orange-600">
                                {{ $coupon->formatted_value }}
                            </span>
                        </div>
                    </div>

                    <!-- Descrição -->
                    <p class="text-gray-700 mb-4">{{ $coupon->description }}</p>

                    <!-- Condições -->
                    <div class="bg-gray-50 rounded-lg p-3 mb-4">
                        <div class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-info-circle mr-2"></i>
                            <span>
                                @if($coupon->minimum_amount)
                                    Válido para pedidos acima de R$ {{ number_format($coupon->minimum_amount, 2, ',', '.') }}
                                @else
                                    Válido para qualquer pedido
                                @endif
                            </span>
                        </div>
                        
                        @if($coupon->expires_at)
                        <div class="flex items-center text-sm text-gray-600 mt-1">
                            <i class="fas fa-clock mr-2"></i>
                            <span>
                                Válido até {{ $coupon->expires_at->format('d/m/Y') }}
                            </span>
                        </div>
                        @endif
                    </div>

                    <!-- Botão de Usar -->
                    <button onclick="useCoupon('{{ $coupon->code }}')" 
                            class="w-full bg-orange-600 text-white py-2 px-4 rounded-lg font-semibold hover:bg-orange-700 transition">
                        <i class="fas fa-shopping-cart mr-2"></i>
                        Usar Cupom
                    </button>
                </div>
                @endforeach
            </div>
            @else
            <!-- Sem Cupons -->
            <div class="text-center py-12">
                <div class="bg-gray-100 rounded-full w-24 h-24 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-ticket-alt text-gray-400 text-3xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">
                    Nenhum cupom disponível
                </h3>
                <p class="text-gray-600 mb-6">
                    Não há cupons de desconto disponíveis no momento.
                </p>
                <a href="{{ route('menu.index') }}" 
                   class="inline-flex items-center bg-orange-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-orange-700 transition">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Voltar ao Cardápio
                </a>
            </div>
            @endif

            <!-- Informações Adicionais -->
            <div class="mt-12 bg-blue-50 border border-blue-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-blue-900 mb-3">
                    💡 Como usar os cupons?
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm text-blue-800">
                    <div class="flex items-start">
                        <div class="bg-blue-200 rounded-full w-6 h-6 flex items-center justify-center mr-3 mt-0.5">
                            <span class="text-blue-900 font-bold text-xs">1</span>
                        </div>
                        <div>
                            <strong>Escolha um cupom</strong>
                            <p>Selecione o cupom que deseja usar</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <div class="bg-blue-200 rounded-full w-6 h-6 flex items-center justify-center mr-3 mt-0.5">
                            <span class="text-blue-900 font-bold text-xs">2</span>
                        </div>
                        <div>
                            <strong>Adicione ao carrinho</strong>
                            <p>Coloque itens no seu carrinho</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <div class="bg-blue-200 rounded-full w-6 h-6 flex items-center justify-center mr-3 mt-0.5">
                            <span class="text-blue-900 font-bold text-xs">3</span>
                        </div>
                        <div>
                            <strong>Aplique o cupom</strong>
                            <p>Digite o código na finalização</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function useCoupon(code) {
        // Salvar cupom no localStorage para usar no checkout
        localStorage.setItem('selectedCoupon', code);
        
        // Mostrar notificação
        showNotification('Cupom selecionado! Vá para o carrinho para aplicá-lo.', 'success');
        
        // Redirecionar para o cardápio
        setTimeout(() => {
            window.location.href = '{{ route("menu.index") }}';
        }, 2000);
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
</script>
@endpush
@endsection
