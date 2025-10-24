@extends('layouts.app')

@section('title', 'Finalizar Pedido - Olika')

@section('content')
<div class="py-8">
    <div class="container">
        <div class="max-w-6xl mx-auto">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">
                    🛒 Finalizar Pedido
                </h1>
                <p class="text-gray-600">
                    Complete seu pedido em poucos passos
                </p>
            </div>

            <!-- Progress Steps -->
            <div class="mb-8">
                <div class="flex items-center justify-center space-x-8">
                    <div class="flex items-center step-item" data-step="1">
                        <div class="w-10 h-10 rounded-full bg-orange-600 text-white flex items-center justify-center font-bold step-number">1</div>
                        <span class="ml-3 font-medium text-gray-900">Dados Pessoais</span>
                    </div>
                    <div class="w-16 h-1 bg-gray-300 step-line"></div>
                    <div class="flex items-center step-item" data-step="2">
                        <div class="w-10 h-10 rounded-full bg-gray-300 text-gray-600 flex items-center justify-center font-bold step-number">2</div>
                        <span class="ml-3 font-medium text-gray-600">Endereço</span>
                    </div>
                    <div class="w-16 h-1 bg-gray-300 step-line"></div>
                    <div class="flex items-center step-item" data-step="3">
                        <div class="w-10 h-10 rounded-full bg-gray-300 text-gray-600 flex items-center justify-center font-bold step-number">3</div>
                        <span class="ml-3 font-medium text-gray-600">Cupons</span>
                    </div>
                    <div class="w-16 h-1 bg-gray-300 step-line"></div>
                    <div class="flex items-center step-item" data-step="4">
                        <div class="w-10 h-10 rounded-full bg-gray-300 text-gray-600 flex items-center justify-center font-bold step-number">4</div>
                        <span class="ml-3 font-medium text-gray-600">Pagamento</span>
                    </div>
                </div>
            </div>

            <div class="grid lg:grid-cols-3 gap-8">
                <!-- Steps Content -->
                <div class="lg:col-span-2">
                    <!-- Step 1: Customer Data -->
                    <div id="step-1" class="step-content">
                        <div class="card">
                            <h2 class="text-xl font-bold text-gray-900 mb-6">
                                <i class="fas fa-user mr-2"></i>
                                Dados Pessoais
                            </h2>
                            
                            <form id="customer-form">
                                @csrf
                                <div class="grid md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Nome Completo *
                                        </label>
                                        <input type="text" 
                                               name="name" 
                                               id="customer_name"
                                               value="{{ $customerData['name'] }}"
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                                               required>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Telefone *
                                        </label>
                                        <input type="tel" 
                                               name="phone" 
                                               id="customer_phone"
                                               value="{{ $customerData['phone'] }}"
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                                               placeholder="(71) 99999-9999"
                                               required>
                                    </div>
                                    
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            E-mail
                                        </label>
                                        <input type="email" 
                                               name="email" 
                                               id="customer_email"
                                               value="{{ $customerData['email'] }}"
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                                    </div>
                                </div>
                                
                                <div class="mt-6 flex justify-end">
                                    <button type="button" 
                                            onclick="nextStep(2)" 
                                            class="btn-primary">
                                        Continuar <i class="fas fa-arrow-right ml-2"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Step 2: Delivery Address -->
                    <div id="step-2" class="step-content hidden">
                        <div class="card">
                            <h2 class="text-xl font-bold text-gray-900 mb-6">
                                <i class="fas fa-map-marker-alt mr-2"></i>
                                Endereço de Entrega
                            </h2>
                            
                            <form id="address-form">
                                @csrf
                                <div class="space-y-4">
                                    <!-- CEP e Número -->
                                    <div class="grid md:grid-cols-3 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                                CEP *
                                            </label>
                                            <div class="relative">
                                                <input type="text" 
                                                       name="zipcode" 
                                                       id="delivery_zipcode"
                                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                                                       placeholder="00000-000"
                                                       maxlength="9"
                                                       required>
                                                <button type="button" 
                                                        id="search_zipcode_btn"
                                                        class="absolute right-2 top-1/2 transform -translate-y-1/2 px-3 py-1 bg-orange-600 text-white text-sm rounded hover:bg-orange-700"
                                                        onclick="searchZipcode()">
                                                    <i class="fas fa-search"></i>
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                                Número *
                                            </label>
                                            <input type="text" 
                                                   name="number" 
                                                   id="delivery_number"
                                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                                                   placeholder="123"
                                                   required>
                                        </div>
                                        
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                                Complemento
                                            </label>
                                            <input type="text" 
                                                   name="complement" 
                                                   id="delivery_complement"
                                                   value="{{ $customerData['complement'] }}"
                                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                                                   placeholder="Apto, casa, etc.">
                                        </div>
                                    </div>
                                    
                                    <!-- Endereço (preenchido automaticamente) -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Rua/Logradouro *
                                        </label>
                                        <input type="text" 
                                               name="street" 
                                               id="delivery_street"
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                                               placeholder="Nome da rua"
                                               required>
                                    </div>
                                    
                                    <div class="grid md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                                Bairro *
                                            </label>
                                            <input type="text" 
                                                   name="neighborhood" 
                                                   id="delivery_neighborhood"
                                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                                                   placeholder="Nome do bairro"
                                                   required>
                                        </div>
                                        
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                                Cidade/UF *
                                            </label>
                                            <input type="text" 
                                                   name="city_state" 
                                                   id="delivery_city_state"
                                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                                                   placeholder="Cidade - UF"
                                                   required>
                                        </div>
                                    </div>
                                    
                                    <!-- Endereço completo (para referência) -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Endereço Completo (gerado automaticamente)
                                        </label>
                                        <textarea name="address" 
                                                  id="delivery_address"
                                                  rows="2"
                                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-50"
                                                  readonly>{{ $customerData['address'] }}</textarea>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Instruções de Entrega
                                        </label>
                                        <textarea name="instructions" 
                                                  id="delivery_instructions"
                                                  rows="2"
                                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                                                  placeholder="Ex: Portão azul, tocar interfone..."></textarea>
                                    </div>
                                </div>
                                
                                <div class="mt-6 flex justify-between">
                                    <button type="button" 
                                            onclick="prevStep(1)" 
                                            class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                                        <i class="fas fa-arrow-left mr-2"></i>
                                        Voltar
                                    </button>
                                    <button type="button" 
                                            onclick="nextStep(3)" 
                                            class="btn-primary">
                                        Continuar <i class="fas fa-arrow-right ml-2"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Step 3: Coupons -->
                    <div id="step-3" class="step-content hidden">
                        <div class="card">
                            <h2 class="text-xl font-bold text-gray-900 mb-6">
                                <i class="fas fa-ticket-alt mr-2"></i>
                                Cupons de Desconto
                            </h2>
                            
                            @if($availableCoupons->count() > 0)
                            <div class="mb-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Cupons Disponíveis</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    @foreach($availableCoupons as $coupon)
                                    <div class="border border-gray-200 rounded-lg p-4 hover:border-orange-300 transition cursor-pointer coupon-item"
                                         data-coupon-code="{{ $coupon->code }}"
                                         onclick="selectCoupon('{{ $coupon->code }}')">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <h4 class="font-semibold text-gray-900">{{ $coupon->name }}</h4>
                                                <p class="text-sm text-gray-600">{{ $coupon->description }}</p>
                                                <p class="text-xs text-gray-500 mt-1">Código: {{ $coupon->code }}</p>
                                            </div>
                                            <div class="text-right">
                                                <span class="text-lg font-bold text-orange-600">
                                                    {{ $coupon->formatted_value }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                            
                            <div class="mb-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Ou digite um código</h3>
                                <div class="flex space-x-4">
                                    <input type="text" 
                                           id="coupon_code_input"
                                           placeholder="Digite o código do cupom"
                                           class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                                    <button type="button" 
                                            onclick="validateCoupon()" 
                                            class="px-6 py-3 bg-orange-600 text-white rounded-lg hover:bg-orange-700">
                                        Validar
                                    </button>
                                </div>
                                <div id="coupon_result" class="mt-3"></div>
                            </div>
                            
                            <div class="mt-6 flex justify-between">
                                <button type="button" 
                                        onclick="prevStep(2)" 
                                        class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                                    <i class="fas fa-arrow-left mr-2"></i>
                                    Voltar
                                </button>
                                <button type="button" 
                                        onclick="nextStep(4)" 
                                        class="btn-primary">
                                    Continuar <i class="fas fa-arrow-right ml-2"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Step 4: Payment -->
                    <div id="step-4" class="step-content hidden">
                        <div class="card">
                            <h2 class="text-xl font-bold text-gray-900 mb-6">
                                <i class="fas fa-credit-card mr-2"></i>
                                Forma de Pagamento
                            </h2>
                            
                            <form id="payment-form" action="{{ route('checkout.store') }}" method="POST">
                                @csrf
                                <input type="hidden" name="customer_name" id="final_customer_name">
                                <input type="hidden" name="customer_phone" id="final_customer_phone">
                                <input type="hidden" name="customer_email" id="final_customer_email">
                                <input type="hidden" name="delivery_address" id="final_delivery_address">
                                <input type="hidden" name="delivery_neighborhood" id="final_delivery_neighborhood">
                                <input type="hidden" name="delivery_complement" id="final_delivery_complement">
                                <input type="hidden" name="delivery_instructions" id="final_delivery_instructions">
                                <input type="hidden" name="coupon_code" id="final_coupon_code">
                                <input type="hidden" name="delivery_type" value="delivery">
                                
                                <div class="space-y-4">
                                    <label class="flex items-center p-4 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                                        <input type="radio" 
                                               name="payment_method" 
                                               value="pix" 
                                               checked
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
                                
                                <div class="mt-6">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Observações do Pedido
                                    </label>
                                    <textarea name="observations" 
                                              rows="3"
                                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                                              placeholder="Alguma observação especial para seu pedido..."></textarea>
                                </div>
                                
                                <div class="mt-6 flex justify-between">
                                    <button type="button" 
                                            onclick="prevStep(3)" 
                                            class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                                        <i class="fas fa-arrow-left mr-2"></i>
                                        Voltar
                                    </button>
                                    <button type="submit" 
                                            class="btn-primary">
                                        <i class="fas fa-credit-card mr-2"></i>
                                        Finalizar Pedido
                                    </button>
                                </div>
                            </form>
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
                            @foreach($cartItems as $item)
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
                            
                            <div id="coupon_discount" class="flex justify-between text-green-600 hidden">
                                <span>Desconto:</span>
                                <span class="font-medium" id="discount_value">-R$ 0,00</span>
                            </div>
                            
                            <hr class="border-gray-200">
                            
                            <div class="flex justify-between text-lg font-bold">
                                <span>Total:</span>
                                <span class="text-orange-600" id="final_total">R$ {{ number_format($total + 5, 2, ',', '.') }}</span>
                            </div>
                        </div>
                        
                        <div class="text-xs text-gray-500 text-center">
                            * Taxa de entrega pode variar conforme a distância
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let currentStep = 1;
    let selectedCoupon = null;
    let couponDiscount = 0;

    // Navigation functions
    function nextStep(step) {
        if (validateCurrentStep()) {
            saveCurrentStepData();
            showStep(step);
            updateProgress(step);
        }
    }

    function prevStep(step) {
        showStep(step);
        updateProgress(step);
    }

    function showStep(step) {
        // Hide all steps
        document.querySelectorAll('.step-content').forEach(el => el.classList.add('hidden'));
        
        // Show current step
        document.getElementById(`step-${step}`).classList.remove('hidden');
        currentStep = step;
    }

    function updateProgress(step) {
        document.querySelectorAll('.step-item').forEach((item, index) => {
            const stepNumber = index + 1;
            const numberEl = item.querySelector('.step-number');
            const textEl = item.querySelector('span');
            const lineEl = item.querySelector('.step-line') || item.nextElementSibling?.querySelector('.step-line');
            
            if (stepNumber <= step) {
                numberEl.classList.remove('bg-gray-300', 'text-gray-600');
                numberEl.classList.add('bg-orange-600', 'text-white');
                textEl.classList.remove('text-gray-600');
                textEl.classList.add('text-gray-900');
                if (lineEl) {
                    lineEl.classList.remove('bg-gray-300');
                    lineEl.classList.add('bg-orange-600');
                }
            } else {
                numberEl.classList.remove('bg-orange-600', 'text-white');
                numberEl.classList.add('bg-gray-300', 'text-gray-600');
                textEl.classList.remove('text-gray-900');
                textEl.classList.add('text-gray-600');
                if (lineEl) {
                    lineEl.classList.remove('bg-orange-600');
                    lineEl.classList.add('bg-gray-300');
                }
            }
        });
    }

    function validateCurrentStep() {
        switch (currentStep) {
            case 1:
                const name = document.getElementById('customer_name').value.trim();
                const phone = document.getElementById('customer_phone').value.trim();
                if (!name || !phone) {
                    showNotification('Preencha todos os campos obrigatórios', 'error');
                    return false;
                }
                return true;
            case 2:
                const address = document.getElementById('delivery_address').value.trim();
                if (!address) {
                    showNotification('Preencha o endereço de entrega', 'error');
                    return false;
                }
                return true;
            case 3:
                return true; // Cupons são opcionais
            case 4:
                return true; // Pagamento sempre válido
        }
        return true;
    }

    function saveCurrentStepData() {
        switch (currentStep) {
            case 1:
                saveCustomerData();
                break;
            case 2:
                saveDeliveryAddress();
                break;
            case 3:
                // Cupom já foi selecionado
                break;
            case 4:
                prepareFinalData();
                break;
        }
    }

    function saveCustomerData() {
        const formData = {
            name: document.getElementById('customer_name').value,
            phone: document.getElementById('customer_phone').value,
            email: document.getElementById('customer_email').value,
        };

        fetch('{{ route("checkout.save-customer-data") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.csrfToken
            },
            body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Dados salvos com sucesso', 'success');
            }
        });
    }

    function saveDeliveryAddress() {
        const formData = {
            address: document.getElementById('delivery_address').value,
            neighborhood: document.getElementById('delivery_neighborhood').value,
            complement: document.getElementById('delivery_complement').value,
            instructions: document.getElementById('delivery_instructions').value,
        };

        fetch('{{ route("checkout.save-delivery-address") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.csrfToken
            },
            body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Endereço salvo com sucesso', 'success');
            }
        });
    }

    function selectCoupon(code) {
        // Remove previous selection
        document.querySelectorAll('.coupon-item').forEach(item => {
            item.classList.remove('border-orange-500', 'bg-orange-50');
            item.classList.add('border-gray-200');
        });

        // Select current coupon
        const selectedItem = document.querySelector(`[data-coupon-code="${code}"]`);
        selectedItem.classList.remove('border-gray-200');
        selectedItem.classList.add('border-orange-500', 'bg-orange-50');

        selectedCoupon = code;
        validateCouponCode(code);
    }

    function validateCoupon() {
        const code = document.getElementById('coupon_code_input').value.trim();
        if (!code) {
            showNotification('Digite um código de cupom', 'error');
            return;
        }
        validateCouponCode(code);
    }

    function validateCouponCode(code) {
        const phone = document.getElementById('customer_phone').value;
        
        fetch('{{ route("checkout.validate-coupon") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.csrfToken
            },
            body: JSON.stringify({
                coupon_code: code,
                customer_phone: phone
            })
        })
        .then(response => response.json())
        .then(data => {
            const resultDiv = document.getElementById('coupon_result');
            
            if (data.success) {
                selectedCoupon = code;
                couponDiscount = data.discount;
                
                resultDiv.innerHTML = `
                    <div class="p-3 bg-green-50 border border-green-200 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-green-600 mr-2"></i>
                            <span class="text-green-800 font-medium">Cupom válido!</span>
                        </div>
                        <p class="text-green-700 text-sm mt-1">
                            Desconto: ${data.formatted_discount}
                        </p>
                    </div>
                `;
                
                updateTotal();
            } else {
                resultDiv.innerHTML = `
                    <div class="p-3 bg-red-50 border border-red-200 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-times-circle text-red-600 mr-2"></i>
                            <span class="text-red-800 font-medium">${data.message}</span>
                        </div>
                    </div>
                `;
            }
        });
    }

    function updateTotal() {
        const subtotal = {{ $total }};
        const deliveryFee = 5.00;
        const total = subtotal + deliveryFee - couponDiscount;
        
        document.getElementById('final_total').textContent = 
            'R$ ' + total.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        
        if (couponDiscount > 0) {
            document.getElementById('coupon_discount').classList.remove('hidden');
            document.getElementById('discount_value').textContent = 
                '-R$ ' + couponDiscount.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }
    }

    function prepareFinalData() {
        document.getElementById('final_customer_name').value = document.getElementById('customer_name').value;
        document.getElementById('final_customer_phone').value = document.getElementById('customer_phone').value;
        document.getElementById('final_customer_email').value = document.getElementById('customer_email').value;
        document.getElementById('final_delivery_address').value = document.getElementById('delivery_address').value;
        document.getElementById('final_delivery_neighborhood').value = document.getElementById('delivery_neighborhood').value;
        document.getElementById('final_delivery_complement').value = document.getElementById('delivery_complement').value;
        document.getElementById('final_delivery_instructions').value = document.getElementById('delivery_instructions').value;
        document.getElementById('final_coupon_code').value = selectedCoupon || '';
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

    // CEP Functions
    function formatZipcode(value) {
        // Remove tudo que não é dígito
        const numbers = value.replace(/\D/g, '');
        // Aplica a máscara 00000-000
        return numbers.replace(/(\d{5})(\d{3})/, '$1-$2');
    }

    function searchZipcode() {
        const zipcode = document.getElementById('delivery_zipcode').value.replace(/\D/g, '');
        
        if (zipcode.length !== 8) {
            showNotification('CEP deve ter 8 dígitos', 'error');
            return;
        }

        const btn = document.getElementById('search_zipcode_btn');
        const originalContent = btn.innerHTML;
        
        // Mostrar loading
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        btn.disabled = true;

        // Usar API ViaCEP
        fetch(`https://viacep.com.br/ws/${zipcode}/json/`)
            .then(response => response.json())
            .then(data => {
                if (data.erro) {
                    showNotification('CEP não encontrado', 'error');
                    return;
                }

                // Preencher campos automaticamente
                document.getElementById('delivery_street').value = data.logradouro || '';
                document.getElementById('delivery_neighborhood').value = data.bairro || '';
                document.getElementById('delivery_city_state').value = `${data.localidade || ''} - ${data.uf || ''}`;
                
                // Gerar endereço completo
                updateFullAddress();
                
                showNotification('Endereço encontrado!', 'success');
            })
            .catch(error => {
                console.error('Erro ao buscar CEP:', error);
                showNotification('Erro ao buscar CEP. Tente novamente.', 'error');
            })
            .finally(() => {
                // Restaurar botão
                btn.innerHTML = originalContent;
                btn.disabled = false;
            });
    }

    function updateFullAddress() {
        const zipcode = document.getElementById('delivery_zipcode').value;
        const number = document.getElementById('delivery_number').value;
        const complement = document.getElementById('delivery_complement').value;
        const street = document.getElementById('delivery_street').value;
        const neighborhood = document.getElementById('delivery_neighborhood').value;
        const cityState = document.getElementById('delivery_city_state').value;

        let fullAddress = '';
        
        if (street) {
            fullAddress += street;
            if (number) fullAddress += `, ${number}`;
            if (complement) fullAddress += `, ${complement}`;
            if (neighborhood) fullAddress += `, ${neighborhood}`;
            if (cityState) fullAddress += `, ${cityState}`;
            if (zipcode) fullAddress += `, ${zipcode}`;
        }

        document.getElementById('delivery_address').value = fullAddress;
    }

    // Event listeners para CEP
    document.addEventListener('DOMContentLoaded', function() {
        const zipcodeInput = document.getElementById('delivery_zipcode');
        const numberInput = document.getElementById('delivery_number');
        const complementInput = document.getElementById('delivery_complement');

        // Formatar CEP enquanto digita
        zipcodeInput.addEventListener('input', function(e) {
            e.target.value = formatZipcode(e.target.value);
        });

        // Buscar CEP quando completar 8 dígitos
        zipcodeInput.addEventListener('input', function(e) {
            const value = e.target.value.replace(/\D/g, '');
            if (value.length === 8) {
                setTimeout(() => searchZipcode(), 500); // Delay para evitar múltiplas chamadas
            }
        });

        // Atualizar endereço completo quando outros campos mudarem
        [numberInput, complementInput].forEach(input => {
            input.addEventListener('input', updateFullAddress);
        });
    });

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        updateProgress(1);
    });
</script>
@endpush
