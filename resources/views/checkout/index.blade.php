@extends('layouts.app')

@section('title', 'Checkout - Olika')

@section('content')
<div class="py-8">
    <div class="container">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">
                    🛒 Finalizar Pedido
                </h1>
                <p class="text-gray-600">
                    Preencha os dados abaixo para finalizar seu pedido
                </p>
            </div>

            <form action="{{ route('checkout.store') }}" method="POST" class="grid lg:grid-cols-3 gap-8">
                @csrf
                
                <!-- Customer Information -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Customer Data -->
                    <div class="card">
                        <h2 class="text-xl font-bold text-gray-900 mb-6">
                            <i class="fas fa-user mr-2"></i>
                            Dados Pessoais
                        </h2>
                        
                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Nome Completo *
                                </label>
                                <input type="text" 
                                       name="customer_name" 
                                       value="{{ old('customer_name') }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                                       required>
                                @error('customer_name')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Telefone *
                                </label>
                                <input type="tel" 
                                       name="customer_phone" 
                                       value="{{ old('customer_phone') }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                                       placeholder="(71) 99999-9999"
                                       required>
                                @error('customer_phone')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    E-mail
                                </label>
                                <input type="email" 
                                       name="customer_email" 
                                       value="{{ old('customer_email') }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                                @error('customer_email')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Delivery Options -->
                    <div class="card">
                        <h2 class="text-xl font-bold text-gray-900 mb-6">
                            <i class="fas fa-truck mr-2"></i>
                            Tipo de Entrega
                        </h2>
                        
                        <div class="space-y-4">
                            <label class="flex items-center p-4 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                                <input type="radio" 
                                       name="delivery_type" 
                                       value="pickup" 
                                       {{ old('delivery_type', 'pickup') === 'pickup' ? 'checked' : '' }}
                                       class="mr-3">
                                <div>
                                    <div class="font-medium">Retirada no Local</div>
                                    <div class="text-sm text-gray-600">Retire seu pedido no estabelecimento</div>
                                </div>
                            </label>
                            
                            <label class="flex items-center p-4 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                                <input type="radio" 
                                       name="delivery_type" 
                                       value="delivery" 
                                       {{ old('delivery_type') === 'delivery' ? 'checked' : '' }}
                                       class="mr-3">
                                <div>
                                    <div class="font-medium">Entrega</div>
                                    <div class="text-sm text-gray-600">Entregamos no seu endereço</div>
                                </div>
                            </label>
                        </div>
                        
                        @error('delivery_type')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Delivery Address (conditional) -->
                    <div id="delivery-address" class="card" style="display: none;">
                        <h2 class="text-xl font-bold text-gray-900 mb-6">
                            <i class="fas fa-map-marker-alt mr-2"></i>
                            Endereço de Entrega
                        </h2>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Endereço Completo *
                                </label>
                                <textarea name="delivery_address" 
                                          rows="3"
                                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                                          placeholder="Rua, número, bairro..."></textarea>
                                @error('delivery_address')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div class="grid md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Bairro
                                    </label>
                                    <input type="text" 
                                           name="delivery_neighborhood" 
                                           value="{{ old('delivery_neighborhood') }}"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Complemento
                                    </label>
                                    <input type="text" 
                                           name="delivery_complement" 
                                           value="{{ old('delivery_complement') }}"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Instruções de Entrega
                                </label>
                                <textarea name="delivery_instructions" 
                                          rows="2"
                                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                                          placeholder="Ex: Portão azul, tocar interfone..."></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div class="card">
                        <h2 class="text-xl font-bold text-gray-900 mb-6">
                            <i class="fas fa-credit-card mr-2"></i>
                            Forma de Pagamento
                        </h2>
                        
                        <div class="space-y-4">
                            <label class="flex items-center p-4 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                                <input type="radio" 
                                       name="payment_method" 
                                       value="pix" 
                                       {{ old('payment_method', 'pix') === 'pix' ? 'checked' : '' }}
                                       class="mr-3">
                                <div class="flex items-center">
                                    <i class="fas fa-qrcode text-2xl mr-3 text-green-600"></i>
                                    <div>
                                        <div class="font-medium">PIX</div>
                                        <div class="text-sm text-gray-600">Pagamento instantâneo</div>
                                    </div>
                                </div>
                            </label>
                            
                            <label class="flex items-center p-4 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                                <input type="radio" 
                                       name="payment_method" 
                                       value="credit_card" 
                                       {{ old('payment_method') === 'credit_card' ? 'checked' : '' }}
                                       class="mr-3">
                                <div class="flex items-center">
                                    <i class="fas fa-credit-card text-2xl mr-3 text-blue-600"></i>
                                    <div>
                                        <div class="font-medium">Cartão de Crédito</div>
                                        <div class="text-sm text-gray-600">Visa, Mastercard, Elo</div>
                                    </div>
                                </div>
                            </label>
                        </div>
                        
                        @error('payment_method')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Additional Information -->
                    <div class="card">
                        <h2 class="text-xl font-bold text-gray-900 mb-6">
                            <i class="fas fa-comment mr-2"></i>
                            Informações Adicionais
                        </h2>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Observações do Pedido
                            </label>
                            <textarea name="observations" 
                                      rows="3"
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                                      placeholder="Alguma observação especial para seu pedido..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="lg:col-span-1">
                    <div class="card sticky top-24">
                        <h3 class="text-xl font-bold text-gray-900 mb-4">
                            Resumo do Pedido
                        </h3>
                        
                        <div class="space-y-3 mb-6">
                            @foreach($cart as $item)
                            <div class="flex justify-between items-center">
                                <div>
                                    <div class="font-medium">{{ $item['product']->name }}</div>
                                    <div class="text-sm text-gray-600">x{{ $item['quantity'] }}</div>
                                </div>
                                <div class="font-medium">
                                    R$ {{ number_format($item['quantity'] * $item['unit_price'], 2, ',', '.') }}
                                </div>
                            </div>
                            @endforeach
                            
                            <hr class="border-gray-200">
                            
                            <div class="flex justify-between">
                                <span class="text-gray-600">Subtotal:</span>
                                <span class="font-medium">R$ {{ number_format($total, 2, ',', '.') }}</span>
                            </div>
                            
                            <div class="flex justify-between">
                                <span class="text-gray-600">Taxa de entrega:</span>
                                <span class="font-medium">R$ 5,00</span>
                            </div>
                            
                            <hr class="border-gray-200">
                            
                            <div class="flex justify-between text-lg font-bold">
                                <span>Total:</span>
                                <span class="text-orange-600">R$ {{ number_format($total + 5, 2, ',', '.') }}</span>
                            </div>
                        </div>
                        
                        <button type="submit" 
                                class="w-full btn-primary text-center">
                            <i class="fas fa-credit-card mr-2"></i>
                            Finalizar Pedido
                        </button>
                        
                        <p class="text-xs text-gray-500 text-center mt-3">
                            * Você será redirecionado para o pagamento
                        </p>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Mostrar/ocultar endereço de entrega
    document.addEventListener('DOMContentLoaded', function() {
        const deliveryTypeInputs = document.querySelectorAll('input[name="delivery_type"]');
        const deliveryAddressDiv = document.getElementById('delivery-address');
        
        function toggleDeliveryAddress() {
            const selectedType = document.querySelector('input[name="delivery_type"]:checked');
            if (selectedType && selectedType.value === 'delivery') {
                deliveryAddressDiv.style.display = 'block';
            } else {
                deliveryAddressDiv.style.display = 'none';
            }
        }
        
        deliveryTypeInputs.forEach(input => {
            input.addEventListener('change', toggleDeliveryAddress);
        });
        
        // Verifica estado inicial
        toggleDeliveryAddress();
    });
</script>
@endpush
