@extends('pedido.layout')

@section('title', 'Checkout - Olika')

@section('content')
<div class="max-w-6xl mx-auto w-full">
    <h1 class="text-xl sm:text-2xl font-bold mb-4 sm:mb-6">Finalizar Pedido</h1>

    @if(session('error'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        {{ session('error') }}
    </div>
    @endif

    <form id="checkoutForm" method="POST" action="{{ route('pedido.checkout.store') }}">
        @csrf
        
        <!-- Campos hidden para dados de desconto de frete -->
        <input type="hidden" name="delivery_fee" id="hidden_delivery_fee" value="0">
        <input type="hidden" name="base_delivery_fee" id="hidden_base_delivery_fee" value="0">
        <input type="hidden" name="delivery_discount_percent" id="hidden_delivery_discount_percent" value="0">
        <input type="hidden" name="delivery_discount_amount" id="hidden_delivery_discount_amount" value="0">
        
        <!-- Campos hidden para pedido do PDV -->
        @if(isset($order) && $order)
        <input type="hidden" name="order_id" value="{{ $order->id }}">
        <input type="hidden" name="order_number" value="{{ $order->order_number }}">
        @endif
        
        <div class="grid lg:grid-cols-[1fr_400px] gap-4 sm:gap-6 lg:gap-8 w-full">
            <!-- Coluna Esquerda: Formulário -->
            <div class="space-y-4 sm:space-y-6 w-full">
                <!-- Dados do Cliente -->
                <div class="bg-white rounded-lg border p-4 sm:p-6">
                    <h2 class="text-lg sm:text-xl font-bold mb-3 sm:mb-4">Dados do Cliente</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">Nome *</label>
                            <input type="text" name="customer_name" value="{{ old('customer_name', $prefill['customer_name'] ?? '') }}" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">Telefone *</label>
                            <input type="tel" name="customer_phone" id="customer_phone" value="{{ old('customer_phone', $prefill['customer_phone'] ?? '') }}" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">Email</label>
                            <input type="email" name="customer_email" id="customer_email" value="{{ old('customer_email', $prefill['customer_email'] ?? '') }}" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary">
                        </div>
                    </div>
                </div>

                <!-- Endereço de Entrega -->
                <div class="bg-white rounded-lg border p-4 sm:p-6">
                    <h2 class="text-lg sm:text-xl font-bold mb-3 sm:mb-4">Endereço de Entrega</h2>
                    <div class="space-y-4">
                        <!-- CEP e Número lado a lado -->
                        <div class="grid grid-cols-3 gap-2 sm:gap-4">
                            <div class="col-span-2">
                                <label class="block text-sm font-medium mb-2">CEP *</label>
                                <input type="text" name="zip_code" id="zip_code" maxlength="9" value="{{ old('zip_code', $prefill['zip_code'] ?? '') }}" required class="w-full border border-gray-300 rounded-lg px-3 sm:px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary" placeholder="00000-000">
                                <p id="cepFeedback" class="text-xs text-gray-500 mt-1"></p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2">Número *</label>
                                <input type="text" name="number" id="number" value="{{ old('number', $prefill['number'] ?? '') }}" required class="w-full border border-gray-300 rounded-lg px-3 sm:px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary" inputmode="numeric" pattern="[0-9]*">
                            </div>
                        </div>
                        
                        <!-- Endereço (obrigatório após CEP) -->
                        <div>
                            <label class="block text-sm font-medium mb-2">Endereço *</label>
                            <input type="text" name="street" id="address" value="{{ old('street', $prefill['address'] ?? '') }}" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary" placeholder="Digite o endereço (rua/logradouro)" readonly>
                            <p class="text-xs text-gray-500 mt-1">Será preenchido automaticamente após buscar o CEP, mas pode ser editado</p>
                        </div>
                        
                        <!-- Complemento -->
                        <div>
                            <label class="block text-sm font-medium mb-2">Complemento</label>
                            <input type="text" name="complement" id="complement" value="{{ old('complement', $prefill['complement'] ?? '') }}" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary" placeholder="Apto, bloco, etc. (opcional)">
                        </div>
                        
                        <!-- Bairro (obrigatório após CEP) -->
                        <div>
                            <label class="block text-sm font-medium mb-2">Bairro *</label>
                            <input type="text" name="neighborhood" id="neighborhood" value="{{ old('neighborhood', $prefill['neighborhood'] ?? '') }}" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary" placeholder="Digite o bairro" readonly>
                            <p class="text-xs text-gray-500 mt-1">Será preenchido automaticamente após buscar o CEP, mas pode ser editado</p>
                        </div>
                        
                        <!-- Cidade e Estado (obrigatórios após CEP) -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-2">Cidade *</label>
                                <input type="text" name="city" id="city" value="{{ old('city', $prefill['city'] ?? '') }}" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary" placeholder="Digite a cidade" readonly>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2">Estado *</label>
                                <input type="text" name="state" id="state" value="{{ old('state', $prefill['state'] ?? '') }}" required maxlength="2" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary uppercase" placeholder="UF" readonly>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Observações Gerais do Pedido -->
                <div class="bg-white rounded-lg border p-4 sm:p-6">
                    <h2 class="text-lg sm:text-xl font-bold mb-3 sm:mb-4">Observações do Pedido</h2>
                    <div>
                        <label class="block text-sm font-medium mb-2">Observações gerais (opcional)</label>
                        <textarea name="notes" id="orderNotes" rows="4" maxlength="1000" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary resize-none" placeholder="Ex: Deixar na portaria, entregar para fulano, etc.">{{ old('notes', '') }}</textarea>
                        <p class="text-xs text-gray-500 mt-1">Máximo 1000 caracteres</p>
                    </div>
                </div>

                <!-- Cupons -->
                <div class="bg-white rounded-lg border p-4 sm:p-6">
                    <h2 class="text-lg sm:text-xl font-bold mb-3 sm:mb-4">Cupom de Desconto</h2>
                    @php
                        // Verificar se há cupons elegíveis de forma explícita
                        $hasEligibleCoupons = false;
                        if (isset($eligibleCoupons)) {
                            // Tentar múltiplas formas de verificar
                            if (method_exists($eligibleCoupons, 'count')) {
                                $hasEligibleCoupons = $eligibleCoupons->count() > 0;
                            } elseif (method_exists($eligibleCoupons, 'isEmpty')) {
                                $hasEligibleCoupons = !$eligibleCoupons->isEmpty();
                            } elseif (is_countable($eligibleCoupons)) {
                                $hasEligibleCoupons = count($eligibleCoupons) > 0;
                            } elseif (is_array($eligibleCoupons)) {
                                $hasEligibleCoupons = count($eligibleCoupons) > 0;
                            }
                        }
                    @endphp
                    @if($hasEligibleCoupons)
                    <div class="mb-4" id="couponsAvailableSection">
                        <label class="block text-sm font-medium mb-2">Cupons Disponíveis</label>
                        <select name="coupon_code" id="coupon_code_public" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary {{ isset($appliedCouponCode) && $appliedCouponCode ? 'bg-gray-100 cursor-not-allowed' : '' }}" {{ isset($appliedCouponCode) && $appliedCouponCode ? 'disabled' : '' }}>
                            <option value="">Selecione um cupom</option>
                            @foreach($eligibleCoupons as $coupon)
                            <option value="{{ $coupon->code }}" data-discount="{{ $coupon->formatted_value }}" {{ isset($appliedCouponCode) && $appliedCouponCode === $coupon->code ? 'selected' : '' }}>
                                {{ $coupon->name }} - {{ $coupon->formatted_value }}
                                @if($coupon->minimum_amount)
                                (Mín: R$ {{ number_format($coupon->minimum_amount, 2, ',', '.') }})
                                @endif
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div id="couponsSeparator" class="text-center text-sm text-gray-600 mb-3">ou</div>
                    @else
                    <!-- Esconder se não houver cupons elegíveis -->
                    <style>
                        #couponsAvailableSection { display: none !important; }
                        #couponsSeparator { display: none !important; }
                    </style>
                    @endif
                    <div class="flex gap-3">
                        <input type="text" name="coupon_code" id="coupon_code_private" placeholder="Digite o código do cupom privado" class="flex-1 border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary {{ isset($appliedCouponCode) && $appliedCouponCode ? 'bg-gray-100 cursor-not-allowed' : '' }}" value="{{ old('coupon_code', isset($appliedCouponCode) ? $appliedCouponCode : '') }}" {{ isset($appliedCouponCode) && $appliedCouponCode ? 'readonly' : '' }}>
                        <button type="button" id="applyCouponBtn" class="px-4 py-2 rounded-lg border border-gray-300 hover:bg-gray-50 {{ isset($appliedCouponCode) && $appliedCouponCode ? 'opacity-50 cursor-not-allowed' : '' }}" {{ isset($appliedCouponCode) && $appliedCouponCode ? 'disabled' : '' }}>
                            {{ isset($appliedCouponCode) && $appliedCouponCode ? 'Aplicado' : 'Aplicar' }}
                        </button>
                    </div>
                    @if(isset($appliedCouponCode) && $appliedCouponCode)
                    <p id="couponFeedback" class="text-sm mt-2 text-green-600 font-medium">✓ Cupom {{ $appliedCouponCode }} aplicado</p>
                    @else
                    <p id="couponFeedback" class="text-sm mt-2 text-gray-600"></p>
                    @endif
                    <input type="hidden" name="applied_coupon_code" id="applied_coupon_code" value="{{ isset($appliedCouponCode) ? $appliedCouponCode : '' }}">
                </div>

                <!-- Agendamento -->
                <div class="bg-white rounded-lg border p-4 sm:p-6">
                    <h2 class="text-lg sm:text-xl font-bold mb-3 sm:mb-4">Agendamento de Entrega</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">Data *</label>
                            <select name="scheduled_delivery_date" id="scheduled_delivery_date" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary">
                                <option value="">Selecione uma data</option>
                                @foreach($availableDates ?? [] as $date)
                                <option value="{{ $date['date'] }}" data-day="{{ $date['day_name'] }}">{{ $date['day_name'] }}, {{ $date['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">Horário *</label>
                            <select name="scheduled_delivery_slot" id="scheduled_delivery_slot" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary disabled:bg-gray-100 disabled:cursor-not-allowed" disabled>
                                <option value="">Selecione primeiro uma data</option>
                            </select>
                            <p id="slotError" class="text-xs text-red-500 mt-1 hidden">Por favor, selecione um horário de entrega</p>
                        </div>
                    </div>
                </div>
                
                <!-- Método de Pagamento -->
                <div class="bg-white rounded-lg border p-4 sm:p-6">
                    <h2 class="text-lg sm:text-xl font-bold mb-3 sm:mb-4">Forma de Pagamento</h2>
                    <div class="space-y-3">
                        <label class="flex items-center gap-3 p-3 border rounded-md cursor-pointer hover:bg-gray-50 {{ !isset($order) || ($order->payment_method ?? 'pix') === 'pix' ? 'border-primary bg-primary/5' : '' }}">
                            <input type="radio" name="payment_method" value="pix" id="payment_pix" class="h-4 w-4 text-primary" {{ !isset($order) || ($order->payment_method ?? 'pix') === 'pix' ? 'checked' : '' }} required>
                            <div class="flex-1">
                                <p class="font-medium">PIX</p>
                                <p class="text-xs text-gray-600">Pagamento instantâneo via QR Code ou código PIX</p>
                            </div>
                        </label>
                        
                        <label class="flex items-center gap-3 p-3 border rounded-md cursor-pointer hover:bg-gray-50 {{ isset($order) && ($order->payment_method ?? '') === 'mercadopago' ? 'border-primary bg-primary/5' : '' }}">
                            <input type="radio" name="payment_method" value="mercadopago" id="payment_mercadopago" class="h-4 w-4 text-primary" {{ isset($order) && ($order->payment_method ?? '') === 'mercadopago' ? 'checked' : '' }}>
                            <div class="flex-1">
                                <p class="font-medium">Cartão (Crédito ou Débito)</p>
                                <p class="text-xs text-gray-600">Será redirecionado para o Mercado Pago onde poderá escolher PIX ou cartão</p>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Coluna Direita: Resumo -->
            <div class="w-full order-1 lg:order-2">
                <div class="bg-white rounded-lg border p-4 sm:p-6 lg:sticky lg:top-20 lg:max-h-[calc(100vh-5rem)] lg:overflow-y-auto overflow-x-hidden">
                    <h2 class="text-lg sm:text-xl font-bold mb-3 sm:mb-4">Resumo do Pedido</h2>
                    
                    <!-- Itens do Carrinho -->
                    <div class="space-y-3 mb-4">
                        @foreach($cartData['items'] ?? [] as $item)
                        <div class="flex items-center gap-3">
                            <img src="{{ $item['image_url'] ?? asset('images/produto-placeholder.jpg') }}" alt="{{ $item['name'] }}" class="w-16 h-16 object-cover rounded">
                            <div class="flex-1">
                                <p class="font-medium text-sm">{{ $item['name'] }}</p>
                                @if($item['variant'])
                                <p class="text-xs text-gray-600">{{ $item['variant'] }}</p>
                                @endif
                                @if(!empty($item['special_instructions']))
                                <div class="text-xs text-yellow-700 mt-1 bg-yellow-50 border-l-2 border-yellow-400 px-2 py-1 rounded">
                                    <strong>Obs:</strong> {{ $item['special_instructions'] }}
                                </div>
                                @endif
                                <p class="text-xs text-gray-600">{{ $item['qty'] }}x R$ {{ number_format($item['price'], 2, ',', '.') }}</p>
                            </div>
                            <p class="font-bold">R$ {{ number_format($item['subtotal'], 2, ',', '.') }}</p>
                        </div>
                        @endforeach
                    </div>

                    <div id="orderSummaryTotals" class="border-t pt-4 space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Subtotal</span>
                            <span id="summarySubtotal" class="text-gray-900 font-medium">R$ {{ number_format($cartData['subtotal'] ?? 0, 2, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Taxa de Entrega</span>
                            <div id="summaryDeliveryFeeContainer" class="flex flex-col items-end">
                                <span id="summaryDeliveryFeeOriginal" class="text-gray-400 text-xs line-through hidden"></span>
                                <span id="summaryDeliveryFee" class="text-gray-500 font-medium text-sm">Informe o CEP</span>
                            </div>
                        </div>
                        <div id="summaryDeliveryDiscountRow" class="flex justify-between text-sm text-green-700 hidden">
                            <span id="summaryDeliveryDiscountLabel">Desconto no frete</span>
                            <span id="summaryDeliveryDiscount" class="font-medium">- R$ 0,00</span>
                        </div>
                        <div id="summaryCouponRow" class="flex justify-between text-sm text-green-700 hidden">
                            <span id="summaryCouponLabel">Cupom de desconto</span>
                            <span id="summaryCoupon" class="font-medium">- R$ 0,00</span>
                        </div>
                        <div id="summaryCashbackRow" class="flex justify-between text-sm text-blue-700 hidden">
                            <span>Cashback usado</span>
                            <span id="summaryCashback" class="font-medium">- R$ 0,00</span>
                        </div>
                        @if(isset($cashbackBalance) && $cashbackBalance > 0)
                        <div class="flex justify-between text-xs text-blue-600 pt-1">
                            <span>Cashback disponível: R$ {{ number_format($cashbackBalance, 2, ',', '.') }}</span>
                        </div>
                        @endif
                        <div class="flex justify-between pt-2 border-t">
                            <span class="font-semibold text-gray-900">Total</span>
                            <span id="summaryTotal" class="text-xl font-bold text-primary">R$ {{ number_format($cartData['subtotal'] ?? 0, 2, ',', '.') }}</span>
                        </div>
                        <div id="summaryCashbackEarned" class="flex justify-between text-xs text-gray-500 mt-2 hidden">
                            <span>Você ganhará</span>
                            <span id="summaryCashbackEarnedValue" class="font-medium">R$ 0,00 de cashback</span>
                        </div>
                    </div>

                    <div class="w-full mt-6 relative z-10">
                        <button type="submit" id="btn-finalize-order" class="w-full bg-primary text-primary-foreground py-3 rounded-lg font-semibold hover:bg-primary/90 transition-colors disabled:opacity-50 disabled:cursor-not-allowed relative z-10" style="touch-action: manipulation; -webkit-tap-highlight-color: transparent;" disabled>
                            Finalizar Pedido
                        </button>
                        <p id="frete-pending-message" class="mt-2 text-xs text-yellow-600 hidden text-center">
                            ⚠️ Aguardando cálculo do frete de entrega...
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
// Inicializar dados globais
window.checkoutData = {
    subtotal: {{ $cartData['subtotal'] ?? 0 }},
    deliveryFee: 0,
    freteCalculado: false // Flag para controlar se o frete foi calculado
};

// Função para atualizar estado do botão de finalizar
function updateFinalizeButtonState() {
    const btnFinalize = document.getElementById('btn-finalize-order');
    const messagePending = document.getElementById('frete-pending-message');
    const zipCode = document.getElementById('zip_code')?.value.replace(/\D/g, '');
    
    if (!btnFinalize) return;
    
    // Se não tem CEP ou CEP incompleto, manter desabilitado
    if (!zipCode || zipCode.length !== 8) {
        btnFinalize.disabled = true;
        if (messagePending) messagePending.classList.add('hidden');
        return;
    }
    
        // Verificar se o frete está no DOM (backup check)
        const deliveryFeeElement = document.getElementById('summaryDeliveryFee');
        const deliveryFeeText = deliveryFeeElement?.textContent?.trim() || '';
        
        // Ignorar textos iniciais que indicam que o frete ainda não foi calculado
        const textoInicial = deliveryFeeText.toLowerCase();
        const isTextoInicial = textoInicial.includes('informe o cep') || 
                              textoInicial.includes('aguardando') ||
                              (!textoInicial.includes('r$') && !textoInicial.includes('grátis') && !textoInicial.includes('gratis'));
        
        // Só tentar parsear se não for texto inicial
        const deliveryFeeInDOM = !isTextoInicial ? parseFloat(
            deliveryFeeText
                .replace(/[^\d,]/g, '')
                .replace(',', '.') || '0'
        ) || 0 : null;
        
        // Verificar se o texto é "Grátis" (indica que frete foi calculado e é zero)
        const isGratis = !isTextoInicial && (textoInicial.includes('grátis') || textoInicial.includes('gratis'));
        
        // Verificar se endereço está completo (CEP, número, rua, bairro, cidade, estado)
        const hasNumber = document.getElementById('number')?.value.trim().length > 0;
        const hasStreet = document.getElementById('address')?.value.trim().length > 0;
        const hasNeighborhood = document.getElementById('neighborhood')?.value.trim().length > 0;
        const hasCity = document.getElementById('city')?.value.trim().length > 0;
        const hasState = document.getElementById('state')?.value.trim().length > 0;
        const addressComplete = hasNumber && hasStreet && hasNeighborhood && hasCity && hasState;
        
        // Se tem CEP válido e endereço completo, mas frete ainda não foi calculado
        // Verificar tanto a flag quanto o valor no DOM
        if (!addressComplete) {
            // Endereço incompleto - manter desabilitado
            btnFinalize.disabled = true;
            if (messagePending) messagePending.classList.add('hidden');
            return;
        }
        
        // Se o frete foi calculado (flag = true) OU se há um valor válido de frete no DOM
        // IMPORTANTE: Ignorar textos iniciais - só considerar calculado se flag OU valor monetário/Grátis válido
        const freteFoiCalculado = window.checkoutData.freteCalculado === true || 
                                   (isGratis && !isTextoInicial) ||
                                   (!isTextoInicial && deliveryFeeInDOM !== null && deliveryFeeText.includes('R$'));
        
        if (!freteFoiCalculado) {
            btnFinalize.disabled = true;
            if (messagePending) {
                messagePending.classList.remove('hidden');
                messagePending.textContent = '⚠️ Aguardando cálculo do frete de entrega...';
            }
        } else {
            // Frete calculado (flag OU valor no DOM), habilitar botão
            // Se o frete está no DOM mas a flag não foi marcada, marcar agora
            if (!window.checkoutData.freteCalculado && !isTextoInicial && (isGratis || (deliveryFeeInDOM !== null && deliveryFeeText.includes('R$')))) {
                window.checkoutData.freteCalculado = true;
                window.checkoutData.deliveryFee = isGratis ? 0 : deliveryFeeInDOM;
                console.log('updateFinalizeButtonState: Frete detectado no DOM, marcando como calculado:', isGratis ? 0 : deliveryFeeInDOM);
            }
            btnFinalize.disabled = false;
            if (messagePending) messagePending.classList.add('hidden');
        }
}

// Gerenciar slots de agendamento
document.getElementById('scheduled_delivery_date')?.addEventListener('change', function() {
    const dateSelect = this;
    const slotSelect = document.getElementById('scheduled_delivery_slot');
    const selectedDate = dateSelect.value;
    
    if (!selectedDate) {
        slotSelect.innerHTML = '<option value="">Selecione primeiro uma data</option>';
        slotSelect.disabled = true;
        return;
    }
    
    const selectedOption = dateSelect.options[dateSelect.selectedIndex];
    const slots = @json($availableDates ?? []);
    const dateData = slots.find(d => d.date === selectedDate);
    
    if (dateData && dateData.slots) {
        slotSelect.innerHTML = '<option value="">Selecione um horário</option>';
        dateData.slots.forEach(slot => {
            // Apenas mostrar slots disponíveis, sem informação de quantidade
            if (slot.available > 0) {
                const option = document.createElement('option');
                option.value = slot.value;
                option.textContent = slot.label;
                slotSelect.appendChild(option);
            }
        });
        slotSelect.disabled = false;
    } else {
        slotSelect.innerHTML = '<option value="">Nenhum horário disponível</option>';
        slotSelect.disabled = true;
    }
});

// Gerenciar cupons
document.getElementById('coupon_code_public')?.addEventListener('change', async function() {
    const value = this.value;
    document.getElementById('coupon_code_private').value = '';
    document.getElementById('applied_coupon_code').value = value;
    // updateOrderSummary já atualiza o feedback do cupom
    await updateOrderSummary();
});

document.getElementById('coupon_code_private')?.addEventListener('input', function() {
    document.getElementById('coupon_code_public').value = '';
});

document.getElementById('applyCouponBtn')?.addEventListener('click', async function() {
    const couponCode = document.getElementById('coupon_code_private').value.trim().toUpperCase();
    if (!couponCode) {
        document.getElementById('couponFeedback').textContent = 'Digite um código de cupom';
        document.getElementById('couponFeedback').className = 'text-sm mt-2 text-red-600';
        return;
    }
    document.getElementById('applied_coupon_code').value = couponCode;
    // updateOrderSummary já atualiza o feedback do cupom
    await updateOrderSummary();
});

// Função para atualizar resumo do pedido
// Flag para prevenir chamadas simultâneas
let updateOrderSummaryRunning = false;

async function updateOrderSummary(subtotal = null, deliveryFee = null) {
    // Prevenir chamadas simultâneas
    if (updateOrderSummaryRunning) {
        console.log('updateOrderSummary: Já executando, ignorando chamada');
        return;
    }
    updateOrderSummaryRunning = true;

    try {
        // Priorizar parâmetros passados, depois window.checkoutData, depois elementos do DOM
    const currentSubtotal = parseFloat(subtotal) || parseFloat(window.checkoutData?.subtotal) || parseFloat(document.getElementById('summarySubtotal')?.textContent?.replace(/[^\d,]/g, '')?.replace(',', '.')) || 0;
    
    // Priorizar o parâmetro deliveryFee se foi passado (não-null), senão usar outras fontes
    let currentDeliveryFee;
    if (deliveryFee !== null && deliveryFee !== undefined && !isNaN(parseFloat(deliveryFee))) {
        currentDeliveryFee = parseFloat(deliveryFee);
        console.log('updateOrderSummary: Usando frete do parâmetro:', currentDeliveryFee);
    } else {
        // Tentar obter do window.checkoutData primeiro
        currentDeliveryFee = parseFloat(window.checkoutData?.deliveryFee);
        
        // Se não estiver em checkoutData, tentar do DOM, mas ignorar textos iniciais
        if (isNaN(currentDeliveryFee) || currentDeliveryFee === 0) {
            const deliveryFeeText = document.getElementById('summaryDeliveryFee')?.textContent?.trim() || '';
            const textoInicial = deliveryFeeText.toLowerCase();
            const isTextoInicial = textoInicial.includes('informe o cep') || 
                                  textoInicial.includes('aguardando') ||
                                  (!textoInicial.includes('r$') && !textoInicial.includes('grátis') && !textoInicial.includes('gratis'));
            
            // Só parsear se não for texto inicial
            if (!isTextoInicial && deliveryFeeText.includes('R$')) {
                currentDeliveryFee = parseFloat(deliveryFeeText.replace(/[^\d,]/g, '').replace(',', '.')) || 0;
            } else if (!isTextoInicial && (textoInicial.includes('grátis') || textoInicial.includes('gratis'))) {
                currentDeliveryFee = 0;
            } else {
                // Texto inicial ou não identificado - usar 0 apenas se já tiver sido calculado antes
                currentDeliveryFee = window.checkoutData?.freteCalculado ? (parseFloat(window.checkoutData?.deliveryFee) || 0) : null;
            }
        }
        
        // Se ainda não tiver valor válido, usar 0 apenas se realmente calculado
        if ((currentDeliveryFee === null || isNaN(currentDeliveryFee)) && !window.checkoutData?.freteCalculado) {
            currentDeliveryFee = null; // Não usar 0 se não foi calculado
        } else if (isNaN(currentDeliveryFee) || currentDeliveryFee === null) {
            currentDeliveryFee = 0;
        }
        
        console.log('updateOrderSummary: Lendo frete de outras fontes:', currentDeliveryFee);
    }
    
    // Buscar dados do formulário
    const customerEmail = document.querySelector('input[name="customer_email"]')?.value || '';
    const customerPhone = document.querySelector('input[name="customer_phone"]')?.value || '';
    const couponCode = document.getElementById('applied_coupon_code')?.value || '';
    
    try {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const response = await fetch('{{ route("pedido.checkout.calculate-discounts") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                customer_email: customerEmail,
                customer_phone: customerPhone,
                coupon_code: couponCode,
                delivery_fee: currentDeliveryFee,
                use_cashback: true,  // Sempre usar cashback se disponível
                @if(isset($order) && $order)
                order_number: '{{ $order->order_number }}',
                order_id: {{ $order->id }},
                @endif
            })
        });
        const data = await response.json();
        
        console.log('updateOrderSummary: Resposta completa do servidor', {
            coupon_message: data.coupon_message,
            coupon_discount: data.coupon_discount,
            coupon_message_type: typeof data.coupon_message,
            coupon_message_value: data.coupon_message
        });
        
        const couponDiscount = parseFloat(data.coupon_discount || 0);
        const couponMessage = data.coupon_message || null;
        const cashbackUsed = parseFloat(data.cashback_used || 0);
        const cashbackEarned = parseFloat(data.cashback_earned || 0);
        const eligibleCoupons = data.eligible_coupons || [];
        // Priorizar valores de window.checkoutData se existirem (frete já calculado)
        // Caso contrário, usar valores do backend
        let deliveryDiscountPercent = window.checkoutData?.deliveryDiscountPercent ?? 
            (data?.delivery_discount_percent !== undefined ? parseFloat(data.delivery_discount_percent || 0) : 0);
        let deliveryDiscountAmount = window.checkoutData?.deliveryDiscountAmount ?? 
            (data?.delivery_discount_amount !== undefined ? parseFloat(data.delivery_discount_amount || 0) : 0);
        let baseDeliveryFee = window.checkoutData?.baseDeliveryFee ?? 
            (data?.base_delivery_fee !== undefined ? parseFloat(data.base_delivery_fee || 0) : 
             (data?.delivery_fee !== undefined ? parseFloat(data.delivery_fee || 0) : 0));
        
        // Log detalhado para debug
        console.log('updateOrderSummary: Valores recebidos do backend', {
            subtotal: currentSubtotal,
            deliveryFee: currentDeliveryFee,
            couponDiscount: couponDiscount,
            cashbackUsed: cashbackUsed,
            cashbackEarned: cashbackEarned,
            totalFromBackend: data.total,
            calculatedTotal: currentSubtotal + currentDeliveryFee - couponDiscount - cashbackUsed
        });
        
        // Calcular total apenas se o frete foi realmente calculado
        const total = currentDeliveryFee !== null && currentDeliveryFee !== undefined && !isNaN(parseFloat(currentDeliveryFee))
            ? parseFloat(data.total || (currentSubtotal + currentDeliveryFee - couponDiscount - cashbackUsed))
            : currentSubtotal; // Se frete não foi calculado, mostrar apenas subtotal
        
        // Se houver mensagem sobre o cupom, exibir feedback
        const couponFeedback = document.getElementById('couponFeedback');
        
        // Prioridade: Mensagem específica > Cupom aplicado > Cupom sem desconto > Sem mensagem
        console.log('updateOrderSummary: Verificando mensagem do cupom', {
            couponMessage: couponMessage,
            couponMessageType: typeof couponMessage,
            couponMessageLength: couponMessage ? couponMessage.length : 0,
            couponCode: couponCode,
            couponDiscount: couponDiscount
        });
        
        if (couponMessage && typeof couponMessage === 'string' && couponMessage.trim() !== '') {
            // Sempre exibir mensagem específica do backend (tem prioridade)
            console.log('updateOrderSummary: Exibindo mensagem específica do cupom:', couponMessage);
            couponFeedback.textContent = couponMessage.trim();
            couponFeedback.className = 'text-sm mt-2 text-red-600';
            // Limpar cupom aplicado se houver erro
            document.getElementById('applied_coupon_code').value = '';
            document.getElementById('coupon_code_public').value = '';
            document.getElementById('coupon_code_private').value = '';
        } else if (couponCode && couponDiscount > 0) {
            // Cupom aplicado com sucesso
            console.log('updateOrderSummary: Cupom aplicado com sucesso, desconto:', couponDiscount);
            couponFeedback.textContent = 'Cupom aplicado!';
            couponFeedback.className = 'text-sm mt-2 text-green-600';
        } else if (couponCode && couponDiscount === 0) {
            // Cupom informado mas sem desconto - mostrar mensagem genérica apenas se não houver mensagem específica
            console.log('updateOrderSummary: Cupom sem desconto, sem mensagem específica do backend');
            couponFeedback.textContent = 'Cupom não pôde ser aplicado.';
            couponFeedback.className = 'text-sm mt-2 text-yellow-600';
        } else {
            // Sem cupom ou sem mensagem
            couponFeedback.textContent = '';
            couponFeedback.className = 'text-sm mt-2 text-gray-600';
        }
        
        // Atualizar display
        document.getElementById('summarySubtotal').textContent = `R$ ${Number(currentSubtotal).toFixed(2).replace('.', ',')}`;
        
        // Garantir que os valores sejam números válidos (já foram priorizados acima)
        if (isNaN(deliveryDiscountAmount)) deliveryDiscountAmount = 0;
        if (isNaN(deliveryDiscountPercent)) deliveryDiscountPercent = 0;
        if (isNaN(baseDeliveryFee)) baseDeliveryFee = currentDeliveryFee ?? 0;

        // Log para debug
        console.log('updateOrderSummary: Valores de entrega para exibição', {
            deliveryDiscountAmount,
            deliveryDiscountPercent,
            baseDeliveryFee,
            currentDeliveryFee,
            fromData: data?.base_delivery_fee,
            fromCheckoutData: window.checkoutData?.baseDeliveryFee,
            hasDiscount: deliveryDiscountAmount > 0 && baseDeliveryFee > 0,
            shouldShowOriginal: deliveryDiscountAmount > 0 && baseDeliveryFee > 0 && baseDeliveryFee > currentDeliveryFee
        });

        // Exibir desconto de entrega e valores
        const deliveryFeeOriginalEl = document.getElementById('summaryDeliveryFeeOriginal');
        const deliveryFeeEl = document.getElementById('summaryDeliveryFee');
        
        // Mostrar valor original se houver desconto E baseDeliveryFee for maior que o valor final
        const hasDiscount = deliveryDiscountAmount > 0 && baseDeliveryFee > 0;
        const shouldShowOriginal = hasDiscount && (baseDeliveryFee > currentDeliveryFee || (currentDeliveryFee === 0 && baseDeliveryFee > 0));
        
        if (shouldShowOriginal) {
            // Mostrar valor original riscado e desconto
            deliveryFeeOriginalEl.textContent = `R$ ${Number(baseDeliveryFee).toFixed(2).replace('.', ',')}`;
            deliveryFeeOriginalEl.classList.remove('hidden');
            
            const discountRow = document.getElementById('summaryDeliveryDiscountRow');
            const discountLabel = document.getElementById('summaryDeliveryDiscountLabel');
            discountLabel.textContent = `Desconto no frete (${deliveryDiscountPercent}%)`;
            document.getElementById('summaryDeliveryDiscount').textContent = `- R$ ${Number(deliveryDiscountAmount).toFixed(2).replace('.', ',')}`;
            discountRow.classList.remove('hidden');
        } else {
            // Sem desconto - esconder valor original
            deliveryFeeOriginalEl.classList.add('hidden');
            document.getElementById('summaryDeliveryDiscountRow').classList.add('hidden');
        }
        
        // Mostrar frete final (já com desconto aplicado)
        // IMPORTANTE: Só atualizar se realmente foi calculado (currentDeliveryFee não é null)
        if (currentDeliveryFee !== null && currentDeliveryFee !== undefined && !isNaN(parseFloat(currentDeliveryFee))) {
            // Só mostrar "Grátis" se realmente houver desconto de 100% OU se o frete foi configurado como grátis
            if (currentDeliveryFee <= 0 && baseDeliveryFee > 0 && deliveryDiscountAmount > 0) {
                // Frete grátis por desconto de 100%
                deliveryFeeEl.textContent = 'Grátis';
                deliveryFeeEl.classList.remove('text-gray-500', 'text-sm');
                deliveryFeeEl.classList.add('text-green-700', 'font-medium');
            } else if (currentDeliveryFee > 0) {
                // Há frete a pagar
                deliveryFeeEl.textContent = `R$ ${Number(currentDeliveryFee).toFixed(2).replace('.', ',')}`;
                deliveryFeeEl.classList.remove('text-gray-500', 'text-sm', 'text-green-700');
                deliveryFeeEl.classList.add('text-gray-900', 'font-medium');
            } else {
                // Frete zero sem desconto (pode ser configurado como grátis ou retirada)
                deliveryFeeEl.textContent = 'Grátis';
                deliveryFeeEl.classList.remove('text-gray-500', 'text-sm');
                deliveryFeeEl.classList.add('text-green-700', 'font-medium');
            }
        } else {
            // Frete ainda não foi calculado - manter texto inicial
            deliveryFeeEl.textContent = 'Informe o CEP';
            deliveryFeeEl.classList.remove('text-gray-900', 'text-green-700', 'font-medium');
            deliveryFeeEl.classList.add('text-gray-500', 'text-sm', 'font-medium');
        }
        
        // Marcar como calculado SEMPRE que recebemos um valor (mesmo 0 = grátis)
        // IMPORTANTE: Frete calculado = true quando temos um valor calculado, mesmo que seja 0 (grátis)
        if (currentDeliveryFee !== null && currentDeliveryFee !== undefined && !isNaN(parseFloat(currentDeliveryFee))) {
            window.checkoutData.freteCalculado = true;
            window.checkoutData.deliveryFee = currentDeliveryFee;
            console.log('updateOrderSummary: Frete calculado, marcando como true (valor:', currentDeliveryFee, ')');
            // Atualizar estado do botão após atualizar frete
            updateFinalizeButtonState();
        } else {
            // Se não recebeu um valor válido, verificar se já estava calculado
            const freteJaEstavaCalculado = window.checkoutData.freteCalculado === true;
            if (freteJaEstavaCalculado) {
                // Preservar estado anterior
                console.log('updateOrderSummary: Preservando frete já calculado (valor anterior:', window.checkoutData.deliveryFee, ')');
                updateFinalizeButtonState();
            } else {
                // Apenas marcar como não calculado se realmente nunca foi calculado
                if (!window.checkoutData.freteCalculado) {
                    console.log('updateOrderSummary: Frete ainda não calculado');
                }
            }
        }
        
        // Cupom - SEMPRE mostrar se houver desconto (mesmo que não tenha código, pode ser aplicado automaticamente)
        console.log('updateOrderSummary: Cupom - discount:', couponDiscount, 'code:', couponCode);
        if (couponDiscount > 0) {
            const couponRow = document.getElementById('summaryCouponRow');
            const couponLabel = document.getElementById('summaryCouponLabel');
            couponLabel.textContent = `Cupom${couponCode ? ' (' + couponCode + ')' : ''}`;
            document.getElementById('summaryCoupon').textContent = `- R$ ${couponDiscount.toFixed(2).replace('.', ',')}`;
            couponRow.classList.remove('hidden');
            console.log('updateOrderSummary: Exibindo linha de cupom com desconto:', couponDiscount);
        } else {
            document.getElementById('summaryCouponRow').classList.add('hidden');
            console.log('updateOrderSummary: Ocultando linha de cupom (sem desconto)');
        }
        
        // Cashback usado
        if (cashbackUsed > 0) {
            document.getElementById('summaryCashback').textContent = `- R$ ${cashbackUsed.toFixed(2).replace('.', ',')}`;
            document.getElementById('summaryCashbackRow').classList.remove('hidden');
        } else {
            document.getElementById('summaryCashbackRow').classList.add('hidden');
        }
        
        // Cashback ganho - sempre mostrar se cashbackEarned foi calculado
        if (cashbackEarned && cashbackEarned > 0) {
            document.getElementById('summaryCashbackEarnedValue').textContent = `R$ ${Number(cashbackEarned).toFixed(2).replace('.', ',')} de cashback`;
            document.getElementById('summaryCashbackEarned').classList.remove('hidden');
        } else {
            document.getElementById('summaryCashbackEarned').classList.add('hidden');
        }
        
        document.getElementById('summaryTotal').textContent = `R$ ${Number(total).toFixed(2).replace('.', ',')}`;
        
        // Log final do total
        console.log('updateOrderSummary: Total final calculado e exibido:', total);
        
        // Atualizar combobox de cupons baseado nos cupons elegíveis
        updateCouponsCombobox(eligibleCoupons);
        
        // Atualizar dados globais (preservar dados de desconto de frete se existirem)
        window.checkoutData = { 
            subtotal: Number(currentSubtotal), 
            deliveryFee: Number(currentDeliveryFee), 
            baseDeliveryFee: baseDeliveryFee,
            deliveryDiscountPercent: deliveryDiscountPercent,
            deliveryDiscountAmount: deliveryDiscountAmount,
            couponDiscount: Number(couponDiscount),
            cashbackUsed: Number(cashbackUsed),
            cashbackEarned: Number(cashbackEarned),
            total: Number(total)
        };
        
        // Atualizar campos hidden do formulário com dados de desconto de frete
        document.getElementById('hidden_delivery_fee').value = Number(currentDeliveryFee).toFixed(2);
        document.getElementById('hidden_base_delivery_fee').value = Number(baseDeliveryFee || currentDeliveryFee).toFixed(2);
        document.getElementById('hidden_delivery_discount_percent').value = Number(deliveryDiscountPercent || 0).toFixed(0);
        document.getElementById('hidden_delivery_discount_amount').value = Number(deliveryDiscountAmount || 0).toFixed(2);
        
        // Verificação de consistência: alertar se houver discrepância
        const expectedTotal = currentSubtotal + currentDeliveryFee - couponDiscount - cashbackUsed;
        if (Math.abs(total - expectedTotal) > 0.01) {
            console.warn('updateOrderSummary: ATENÇÃO - Discrepância no total!', {
                totalCalculado: expectedTotal,
                totalRecebido: total,
                diferenca: Math.abs(total - expectedTotal),
                subtotal: currentSubtotal,
                deliveryFee: currentDeliveryFee,
                couponDiscount: couponDiscount,
                cashbackUsed: cashbackUsed
            });
        }
    } catch (error) {
        console.error('Erro ao processar resposta do servidor:', error);
        // Fallback: manter valores atuais sem atualizar
    }
    } catch (error) {
        console.error('Erro ao calcular descontos:', error);
        // Fallback simples
        const simpleTotal = Number(currentSubtotal) + Number(currentDeliveryFee);
        if (document.getElementById('summaryTotal')) {
            document.getElementById('summaryTotal').textContent = `R$ ${simpleTotal.toFixed(2).replace('.', ',')}`;
        }
    } finally {
        // Sempre resetar a flag
        updateOrderSummaryRunning = false;
    }
}

// Atualizar ao mudar email/telefone
// Função para preencher endereço quando cliente é identificado
async function loadCustomerAddress(phone, email) {
    console.log('loadCustomerAddress: Chamada recebida', { phone, email });

    if (!phone && !email) {
        console.log('loadCustomerAddress: Nenhum telefone ou email fornecido');
        return;
    }

    console.log('loadCustomerAddress: Buscando cliente', { phone, email });
    
    try {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const response = await fetch('{{ route("pedido.checkout.calculate-discounts") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                customer_phone: phone,
                customer_email: email,
                delivery_fee: 0 // Para não precisar calcular frete aqui
            }),
        });
        
        if (!response.ok) {
            console.error('loadCustomerAddress: Erro na resposta', response.status);
            return;
        }
        
        const data = await response.json();
        console.log('loadCustomerAddress: Dados recebidos', data);
        
        // Se encontrou cliente com endereço salvo, preencher campos vazios
        if (data.customer) {
            const customer = data.customer;
            console.log('loadCustomerAddress: Cliente encontrado com endereço', customer);
            
            let filledAny = false;
            
            // Preencher apenas campos que estão vazios
            if (customer.zip_code) {
                const zipCodeField = document.getElementById('zip_code');
                if (zipCodeField && !zipCodeField.value.trim()) {
                    // Formatar CEP (00000-000)
                    const cep = String(customer.zip_code).replace(/\D/g, '');
                    if (cep.length === 8) {
                        zipCodeField.value = cep.substring(0, 5) + '-' + cep.substring(5);
                        filledAny = true;
                        console.log('loadCustomerAddress: CEP preenchido', zipCodeField.value);
                    } else {
                        zipCodeField.value = customer.zip_code;
                        filledAny = true;
                    }
                }
            }
            
            if (customer.address) {
                const addressField = document.getElementById('address');
                if (addressField && !addressField.value.trim()) {
                    addressField.value = customer.address;
                    addressField.removeAttribute('readonly');
                    filledAny = true;
                    console.log('loadCustomerAddress: Endereço preenchido', customer.address);
                }
            }
            
            if (customer.number) {
                const numberField = document.getElementById('number');
                if (numberField && !numberField.value.trim()) {
                    // Filtrar apenas números do número do endereço
                    const numberOnly = String(customer.number).replace(/\D/g, '');
                    numberField.value = numberOnly;
                    filledAny = true;
                }
            }
            
            if (customer.neighborhood) {
                const neighborhoodField = document.getElementById('neighborhood');
                if (neighborhoodField && !neighborhoodField.value.trim()) {
                    neighborhoodField.value = customer.neighborhood;
                    neighborhoodField.removeAttribute('readonly');
                    filledAny = true;
                }
            }
            
            if (customer.city) {
                const cityField = document.getElementById('city');
                if (cityField && !cityField.value.trim()) {
                    cityField.value = customer.city;
                    cityField.removeAttribute('readonly');
                    filledAny = true;
                }
            }
            
            if (customer.state) {
                const stateField = document.getElementById('state');
                if (stateField && !stateField.value.trim()) {
                    stateField.value = customer.state;
                    stateField.removeAttribute('readonly');
                    filledAny = true;
                }
            }
            
            // Se preencheu o CEP e não tinha valores completos, buscar CEP para completar
            if (customer.zip_code && !document.getElementById('address').value.trim()) {
                console.log('loadCustomerAddress: Chamando buscarCep para completar endereço');
                setTimeout(() => {
                    if (typeof buscarCep === 'function') {
                        buscarCep();
                    }
                }, 300);
            } else if (customer.zip_code && filledAny) {
                // Se já tem CEP preenchido, SEMPRE recalcular frete automaticamente
                // Isso garante que o frete seja atualizado mesmo em pedidos subsequentes
                console.log('loadCustomerAddress: CEP já preenchido, SEMPRE recalcular frete automaticamente');
                setTimeout(async () => {
                    const zipCodeField = document.getElementById('zip_code');
                    if (zipCodeField && zipCodeField.value.trim()) {
                        const cep = zipCodeField.value.replace(/\D/g, '');
                        if (cep.length === 8) {
                            // Sempre buscar CEP e recalcular frete, mesmo que já esteja preenchido
                            // Resetar flag para forçar recálculo
                            window.checkoutData.freteCalculado = false;
                            if (typeof buscarCep === 'function') {
                                await buscarCep(); // buscarCep já calcula o frete automaticamente e marca a flag
                            }
                        }
                    }
                }, 500);
            } else if (customer.zip_code && !filledAny) {
                // Se o CEP já estava preenchido mas não preencheu nada novo, mesmo assim recalcular frete
                // Isso garante que pedidos subsequentes sempre tenham o frete recalculado
                console.log('loadCustomerAddress: CEP já estava preenchido, forçando recálculo do frete');
                setTimeout(async () => {
                    const zipCodeField = document.getElementById('zip_code');
                    if (zipCodeField && zipCodeField.value.trim()) {
                        const cep = zipCodeField.value.replace(/\D/g, '');
                        if (cep.length === 8) {
                            // Resetar flag e recalcular frete
                            window.checkoutData.freteCalculado = false;
                            if (typeof buscarCep === 'function') {
                                await buscarCep();
                            }
                        }
                    }
                }, 700);
            }
            
            if (filledAny) {
                console.log('loadCustomerAddress: Campos preenchidos com sucesso');
            }
        } else {
            console.log('loadCustomerAddress: Cliente não encontrado ou sem endereço');
        }
    } catch (error) {
        console.error('Erro ao carregar endereço do cliente:', error);
    }
}

document.getElementById('customer_email')?.addEventListener('blur', async function() {
    const phone = document.getElementById('customer_phone')?.value || '';
    const email = this.value || '';
    await loadCustomerAddress(phone, email);
    updateOrderSummary();
});

document.getElementById('customer_phone')?.addEventListener('blur', async function() {
    const email = document.getElementById('customer_email')?.value || '';
    const phone = this.value || '';
    await loadCustomerAddress(phone, email);
    updateOrderSummary();
});

// Filtrar campo número para aceitar apenas números
const numberField = document.getElementById('number');
if (numberField) {
    // Filtro em tempo real: remover qualquer caractere não numérico
    numberField.addEventListener('input', function(e) {
        // Remover todos os caracteres não numéricos
        const value = e.target.value.replace(/\D/g, '');
        if (e.target.value !== value) {
            e.target.value = value;
        }
    });
    
    // Filtro ao colar (paste)
    numberField.addEventListener('paste', function(e) {
        e.preventDefault();
        const pastedText = (e.clipboardData || window.clipboardData).getData('text');
        const numbersOnly = pastedText.replace(/\D/g, '');
        const currentValue = e.target.value;
        const start = e.target.selectionStart;
        const end = e.target.selectionEnd;
        e.target.value = currentValue.substring(0, start) + numbersOnly + currentValue.substring(end);
        e.target.setSelectionRange(start + numbersOnly.length, start + numbersOnly.length);
    });
    
    // Garantir que o valor inicial também seja filtrado
    if (numberField.value) {
        const filteredValue = numberField.value.replace(/\D/g, '');
        if (numberField.value !== filteredValue) {
            numberField.value = filteredValue;
        }
    }
}

// Recalcular cashback quando número do endereço mudar (pode afetar frete)
document.getElementById('number')?.addEventListener('blur', async function() {
    // Garantir que o valor final seja apenas números
    const value = this.value.replace(/\D/g, '');
    if (this.value !== value) {
        this.value = value;
    }
    
    // Se o frete já foi calculado, recalcular resumo para garantir cashback atualizado
    if (window.checkoutData.freteCalculado) {
        await updateOrderSummary();
    }
});

// Busca automática de CEP via ViaCEP
(function() {
    const zipCodeInput = document.getElementById('zip_code');
    const cepFeedback = document.getElementById('cepFeedback');

    console.log('CEP: Inicializando busca automática de CEP', {
        zipCodeInput: !!zipCodeInput,
        cepFeedback: !!cepFeedback
    });

    if (!zipCodeInput) {
        console.warn('CEP: Campo zip_code não encontrado');
        return;
    }
    
    let timeoutId = null;
    
    // Formatação automática do CEP
    zipCodeInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length > 5) {
            value = value.substring(0, 5) + '-' + value.substring(5, 8);
        }
        e.target.value = value;
        
        // Limpar timeout anterior
        if (timeoutId) {
            clearTimeout(timeoutId);
        }
        
        // Buscar automaticamente quando CEP tiver 8 dígitos (após 500ms de inatividade)
        const cepDigits = value.replace(/\D/g, '');
        if (cepDigits.length === 8) {
            // Marcar que o frete ainda não foi calculado
            window.checkoutData.freteCalculado = false;
            updateFinalizeButtonState();
            timeoutId = setTimeout(() => {
                buscarCep();
            }, 500);
        } else {
            cepFeedback.textContent = '';
            window.checkoutData.freteCalculado = false;
            updateFinalizeButtonState();
        }
    });
    
    async function buscarCep() {
        const cep = zipCodeInput.value.replace(/\D/g, '');
        console.log('buscarCep: Iniciando busca para CEP:', cep);

        if (cep.length !== 8) {
            console.warn('buscarCep: CEP inválido, deve ter 8 dígitos');
            cepFeedback.textContent = 'CEP deve ter 8 dígitos';
            cepFeedback.className = 'text-xs text-red-500 mt-1';
            window.checkoutData.freteCalculado = false;
            updateFinalizeButtonState();
            return;
        }
        
        // Marcar que está buscando CEP e frete ainda não calculado
        window.checkoutData.freteCalculado = false;
        updateFinalizeButtonState();
        
        cepFeedback.textContent = 'Buscando...';
        cepFeedback.className = 'text-xs text-blue-500 mt-1';
        
        try {
            const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
            const data = await response.json();
            
            if (data.erro) {
                cepFeedback.textContent = 'CEP não encontrado';
                cepFeedback.className = 'text-xs text-red-500 mt-1';
                window.checkoutData.freteCalculado = false;
                updateFinalizeButtonState();
                return;
            }
            
            // Preencher campos automaticamente
            document.getElementById('address').value = data.logradouro || '';
            document.getElementById('neighborhood').value = data.bairro || '';
            document.getElementById('city').value = data.localidade || '';
            document.getElementById('state').value = data.uf || '';
            
            // Remover readonly para permitir edição (campos são obrigatórios)
            document.getElementById('address').removeAttribute('readonly');
            document.getElementById('neighborhood').removeAttribute('readonly');
            document.getElementById('city').removeAttribute('readonly');
            document.getElementById('state').removeAttribute('readonly');
            
            // Garantir que campos são obrigatórios após CEP ser encontrado
            document.getElementById('address').required = true;
            document.getElementById('neighborhood').required = true;
            document.getElementById('city').required = true;
            document.getElementById('state').required = true;
            
            // Se algum campo estiver vazio após busca, destacar e exigir preenchimento
            const requiredFields = ['address', 'neighborhood', 'city', 'state'];
            let hasEmptyFields = false;
            
            requiredFields.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (!field.value.trim()) {
                    field.classList.add('border-red-500');
                    field.setAttribute('placeholder', 'Este campo é obrigatório - Preencha manualmente');
                    hasEmptyFields = true;
                } else {
                    field.classList.remove('border-red-500');
                }
            });
            
            // Se houver campos vazios, mostrar alerta
            if (hasEmptyFields) {
                setTimeout(() => {
                    alert('Atenção: Alguns campos do endereço não foram preenchidos automaticamente. Por favor, preencha manualmente os campos destacados em vermelho.');
                }, 500);
            }
            
            // Calcular frete após CEP ser encontrado
            try {
                const zipcodeDigits = zipCodeInput.value.replace(/\D/g, '');
                const customerPhone = document.querySelector('input[name="customer_phone"]')?.value || '';
                const customerEmail = document.querySelector('input[name="customer_email"]')?.value || '';
                
                console.log('Calculando frete para CEP:', zipcodeDigits);
                
                const feeResponse = await fetch('{{ route("pedido.cart.calculateDeliveryFee") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ 
                        zipcode: zipcodeDigits, 
                        customer_phone: customerPhone, 
                        customer_email: customerEmail 
                    })
                });
                
                const feeData = await feeResponse.json();
                console.log('Resposta do cálculo de frete:', feeData);
                
                if (feeData.success && feeData.delivery_fee !== undefined) {
                    const deliveryFee = parseFloat(feeData.delivery_fee) || 0;
                    const baseDeliveryFee = parseFloat(feeData.base_delivery_fee || feeData.delivery_fee) || 0;
                    const discountPercent = parseFloat(feeData.discount_percent || 0) || 0;
                    const discountAmount = parseFloat(feeData.discount_amount || 0) || 0;
                    
                    console.log('Frete calculado:', {
                        deliveryFee,
                        baseDeliveryFee,
                        discountPercent,
                        discountAmount,
                        distance: feeData.distance_km
                    });
                    
                    // Marcar que o frete foi calculado e armazenar dados de desconto
                    window.checkoutData.freteCalculado = true;
                    window.checkoutData.deliveryFee = deliveryFee;
                    window.checkoutData.baseDeliveryFee = baseDeliveryFee;
                    window.checkoutData.deliveryDiscountPercent = discountPercent;
                    window.checkoutData.deliveryDiscountAmount = discountAmount;
                    
                    // Atualizar campos hidden do formulário
                    document.getElementById('hidden_delivery_fee').value = deliveryFee.toFixed(2);
                    document.getElementById('hidden_base_delivery_fee').value = baseDeliveryFee.toFixed(2);
                    document.getElementById('hidden_delivery_discount_percent').value = discountPercent.toFixed(0);
                    document.getElementById('hidden_delivery_discount_amount').value = discountAmount.toFixed(2);
                    
                    // Atualizar o resumo com o novo frete e garantir que os valores de desconto sejam passados
                    // Primeiro atualizar o resumo para que os valores sejam salvos em window.checkoutData
                    await updateOrderSummary(null, deliveryFee);
                    
                    // Forçar atualização novamente para garantir que os valores sejam exibidos
                    // Isso garante que baseDeliveryFee e deliveryDiscountAmount sejam usados
                    setTimeout(async () => {
                        await updateOrderSummary(null, deliveryFee);
                    }, 100);
                    
                    // Limpar feedback do CEP (não mostrar mensagem)
                    cepFeedback.textContent = '';
                    cepFeedback.className = '';
                    
                    // Filtrar cupons de frete grátis
                    filtrarCuponsFreteGratis(deliveryFee);
                    
                    // Garantir que o botão seja habilitado após calcular frete
                    updateFinalizeButtonState();
                } else {
                    console.error('Erro ao calcular frete:', feeData.message || 'Resposta inválida');
                    window.checkoutData.freteCalculado = false;
                    cepFeedback.textContent = feeData.message || 'Erro ao calcular frete';
                    cepFeedback.className = 'text-xs text-yellow-600 mt-1';
                    updateFinalizeButtonState();
                }
            } catch (error) {
                console.error('Erro ao calcular frete:', error);
                window.checkoutData.freteCalculado = false;
                cepFeedback.textContent = 'Erro ao calcular frete. Tente novamente.';
                cepFeedback.className = 'text-xs text-red-500 mt-1';
                updateFinalizeButtonState();
            }
            
            // Focar no campo número
            document.getElementById('number').focus();
            
        } catch (error) {
            console.error('Erro ao buscar CEP:', error);
            cepFeedback.textContent = 'Erro ao buscar CEP. Tente novamente.';
            cepFeedback.className = 'text-xs text-red-500 mt-1';
        }
    }
    
    // Filtrar cupons de frete grátis quando não há frete
    function filtrarCuponsFreteGratis(deliveryFee) {
        const couponSelect = document.getElementById('coupon_code_public');
        if (!couponSelect) return;
        
        const options = couponSelect.querySelectorAll('option');
        let selectedValue = couponSelect.value; // Salvar valor selecionado
        
        options.forEach(option => {
            if (!option.value) return; // Pular option vazio
            
            // Verificar se o texto do option indica frete grátis
            const optionText = option.textContent.toLowerCase();
            const isFreteGratis = optionText.includes('frete') && (
                optionText.includes('grátis') || 
                optionText.includes('gratis') || 
                optionText.includes('frete grátis') ||
                optionText.includes('frete gratis')
            );
            
            if (isFreteGratis) {
                if (deliveryFee <= 0) {
                    // Esconder e desabilitar cupom de frete grátis quando não há frete
                    option.style.display = 'none';
                    option.disabled = true;
                    // Se estava selecionado, limpar seleção
                    if (option.value === selectedValue) {
                        couponSelect.value = '';
                    }
                } else {
                    // Mostrar se há frete
                    option.style.display = '';
                    option.disabled = false;
                }
            } else {
                // Outros cupons sempre visíveis
                option.style.display = '';
                option.disabled = false;
            }
        });
    }
    
    // Filtrar cupons ao carregar a página e atualizar resumo
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOMContentLoaded: Checkout inicializando...');

        // Verificar se elementos essenciais existem
        const btnFinalize = document.getElementById('btn-finalize-order');
        const zipCodeInput = document.getElementById('zip_code');
        console.log('DOMContentLoaded: Elementos encontrados:', {
            btnFinalize: !!btnFinalize,
            zipCodeInput: !!zipCodeInput
        });

        // Preencher cupom aplicado se existir (pedido do PDV)
        @if(isset($appliedCouponCode) && !empty($appliedCouponCode))
        const appliedCoupon = '{{ $appliedCouponCode }}';
        if (appliedCoupon) {
            console.log('DOMContentLoaded: Aplicando cupom automaticamente:', appliedCoupon);
            const couponPrivateInput = document.getElementById('coupon_code_private');
            const appliedCouponHidden = document.getElementById('applied_coupon_code');
            if (couponPrivateInput) {
                couponPrivateInput.value = appliedCoupon;
                // Disparar evento de input para garantir que o valor seja reconhecido
                couponPrivateInput.dispatchEvent(new Event('input', { bubbles: true }));
            }
            if (appliedCouponHidden) {
                appliedCouponHidden.value = appliedCoupon;
            }
            // Atualizar resumo para aplicar o cupom automaticamente (com delay maior para garantir que campos estejam carregados)
            setTimeout(async () => {
                console.log('DOMContentLoaded: Chamando updateOrderSummary para aplicar cupom');
                await updateOrderSummary();
            }, 1500);
        }
        @endif
        
        // PRIMEIRO: Verificar se o frete já está calculado no DOM (mas SEMPRE recalcular para garantir valor atualizado)
        const deliveryFeeElement = document.getElementById('summaryDeliveryFee');
        let freteJaCalculado = false;
        if (deliveryFeeElement) {
            const deliveryFeeText = deliveryFeeElement.textContent.trim();
            const textoInicial = deliveryFeeText.toLowerCase();
            const isTextoInicial = textoInicial.includes('informe o cep') || 
                                  textoInicial.includes('aguardando') ||
                                  (!textoInicial.includes('r$') && !textoInicial.includes('grátis') && !textoInicial.includes('gratis'));
            
            // Só considerar calculado se não for texto inicial
            if (!isTextoInicial) {
                const deliveryFee = textoInicial.includes('grátis') || textoInicial.includes('gratis') 
                    ? 0 
                    : parseFloat(deliveryFeeText.replace(/[^\d,]/g, '').replace(',', '.')) || 0;
                
                // Se já existe um frete calculado, marcar temporariamente como calculado
                // MAS: vamos sempre recalcular para garantir que o valor está atualizado em pedidos subsequentes
                if (deliveryFeeText.length > 0 && (deliveryFeeText.includes('R$') || textoInicial.includes('grátis') || textoInicial.includes('gratis'))) {
                    // Temporariamente marcar como calculado apenas para não bloquear o botão inicialmente
                    // Mas será recalculado para garantir valor atualizado
                    freteJaCalculado = true;
                    console.log('DOMContentLoaded: Frete detectado no DOM (será recalculado):', deliveryFee);
                    // Atualizar estado do botão IMEDIATAMENTE se frete já está calculado
                    // Mas será recalculado para garantir valor atualizado
                    updateFinalizeButtonState();
                }
                
                if (deliveryFee <= 0) {
                    filtrarCuponsFreteGratis(0);
                }
            }
        }
        
        // IMPORTANTE: Sempre resetar a flag para forçar recálculo em pedidos subsequentes
        // Isso garante que o frete seja sempre atualizado, mesmo quando já está preenchido
        window.checkoutData.freteCalculado = false;
        
        // Se cliente já foi identificado (telefone/email preenchidos), carregar endereço automaticamente
        const customerPhone = document.getElementById('customer_phone')?.value || '';
        const customerEmail = document.getElementById('customer_email')?.value || '';
        if (customerPhone || customerEmail) {
            console.log('DOMContentLoaded: Cliente já identificado, carregando endereço', { customerPhone, customerEmail });
            // loadCustomerAddress irá calcular o frete automaticamente
            // NÃO preservar frete antigo - sempre recalcular para garantir valor atualizado
            loadCustomerAddress(customerPhone, customerEmail).then(() => {
                // Após carregar endereço, dar um tempo para buscarCep executar se foi chamado
                setTimeout(() => {
                    updateFinalizeButtonState();
                }, 1000);
            });
        }
        
        // SEMPRE verificar se há CEP preenchido e buscar endereço se campos estiverem vazios
        const zipCodeField = document.getElementById('zip_code');
        const addressField = document.getElementById('address');
        if (zipCodeField && zipCodeField.value.replace(/\D/g, '').length === 8) {
            // Verificar se campos de endereço estão vazios
            const needsAddress = !addressField || !addressField.value.trim();
            if (needsAddress && typeof buscarCep === 'function') {
                console.log('DOMContentLoaded: CEP preenchido mas endereço vazio, buscando endereço...');
                setTimeout(() => {
                    buscarCep().then(() => {
                        // Preservar frete se já estava calculado
                        if (freteJaCalculado && window.checkoutData.deliveryFee > 0) {
                            window.checkoutData.freteCalculado = true;
                        }
                        updateFinalizeButtonState();
                    });
                }, 300);
            } else {
                // Endereço já preenchido - SEMPRE recalcular frete para garantir valor atualizado
                // Não importa se já foi calculado antes - sempre recalcular em pedidos subsequentes
                console.log('DOMContentLoaded: Endereço já preenchido, mas SEMPRE recalcular frete para garantir valor atualizado');
                // Resetar flag para forçar recálculo
                window.checkoutData.freteCalculado = false;
                
                // Sempre recalcular o frete, mesmo que já tenha sido calculado antes
                // Isso garante que pedidos subsequentes sempre tenham o frete atualizado
                const zipCodeDigits = zipCodeField.value.replace(/\D/g, '');
                if (zipCodeDigits.length === 8 && typeof buscarCep === 'function') {
                    console.log('DOMContentLoaded: CEP preenchido, recalculando frete mesmo que endereço já esteja preenchido...');
                    // Chamar buscarCep que irá recalcular o frete mesmo que o endereço já esteja preenchido
                    buscarCep().then(() => {
                        updateFinalizeButtonState();
                    });
                } else {
                    // Se não tem CEP válido, apenas atualizar estado do botão
                    updateFinalizeButtonState();
                }
            }
        } else {
            updateFinalizeButtonState();
        }
        
        // Verificar estado inicial do botão (mas pode ser atualizado depois)
        updateFinalizeButtonState();
        
        // Atualizar resumo ao carregar para mostrar cashback ganho
        // MAS: NÃO preservar frete antigo - sempre recalcular para garantir valor atualizado
        updateOrderSummary().then(() => {
            // Não preservar frete antigo - ele será recalculado pelas funções acima
            // Apenas atualizar estado do botão
            updateFinalizeButtonState();
        });
        
        // Verificação periódica no mobile para garantir que o botão seja habilitado
        // (pode ser necessário devido a problemas de sincronização no mobile)
        if (window.innerWidth <= 768) {
            setInterval(() => {
                const btn = document.getElementById('btn-finalize-order');
                if (btn && btn.disabled) {
                    // Verificar se deveria estar habilitado
                    updateFinalizeButtonState();
                }
            }, 2000); // Verificar a cada 2 segundos
        }
    });
    
    // Buscar ao sair do campo (blur) também
    zipCodeInput.addEventListener('blur', function() {
        const cep = this.value.replace(/\D/g, '');
        console.log('CEP blur: Verificando CEP', cep);
        if (cep.length === 8) {
            console.log('CEP blur: Chamando buscarCep');
            buscarCep();
        } else {
            console.log('CEP blur: CEP incompleto, ignorando');
        }
    });
    
    // Buscar quando o campo mudar (change) - disparado quando navegador preenche automaticamente
    zipCodeInput.addEventListener('change', function() {
        const cep = this.value.replace(/\D/g, '');
        console.log('CEP change: Verificando CEP', cep);
        if (cep.length === 8 && !window.checkoutData.freteCalculado) {
            console.log('CEP change: Chamando buscarCep (CEP completo e frete não calculado)');
            buscarCep();
        }
    });
    
    // Verificar quando o campo recebe foco e já tem valor completo
    zipCodeInput.addEventListener('focus', function() {
        const cep = this.value.replace(/\D/g, '');
        console.log('CEP focus: Verificando CEP', cep);
        if (cep.length === 8 && !window.checkoutData.freteCalculado) {
            console.log('CEP focus: CEP completo mas frete não calculado, chamando buscarCep');
            buscarCep();
        }
    });
    
    // Verificar CEP no carregamento da página (caso já esteja preenchido)
    function verificarCepAoCarregar() {
        const cep = zipCodeInput.value.replace(/\D/g, '');
        console.log('Verificando CEP ao carregar:', cep);
        if (cep.length === 8) {
            // Verificar se o endereço já está preenchido (indicando que CEP já foi buscado)
            const hasAddress = document.getElementById('address')?.value.trim().length > 0;
            const hasNeighborhood = document.getElementById('neighborhood')?.value.trim().length > 0;
            
            // SEMPRE recalcular o frete quando o CEP está preenchido, mesmo que o endereço já esteja preenchido
            // Isso garante que o frete seja atualizado para pedidos subsequentes
            if (!hasAddress || !window.checkoutData.freteCalculado) {
                console.log('CEP ao carregar: CEP completo mas endereço/frete não calculado, buscando CEP e frete');
                // Resetar flag para forçar recálculo
                window.checkoutData.freteCalculado = false;
                // Aguardar um pouco para garantir que outros scripts já inicializaram
                setTimeout(() => {
                    buscarCep();
                }, 500);
            } else {
                console.log('CEP ao carregar: CEP completo e endereço já preenchido, mas SEMPRE recalcular frete para garantir valor atualizado');
                // SEMPRE recalcular o frete, mesmo que o endereço já esteja preenchido
                // Isso é necessário porque o frete pode mudar entre pedidos ou o valor pode estar desatualizado
                // Resetar flag para forçar recálculo
                window.checkoutData.freteCalculado = false;
                setTimeout(async () => {
                    const zipCodeDigits = zipCodeInput.value.replace(/\D/g, '');
                    if (zipCodeDigits.length === 8) {
                        const customerPhone = document.querySelector('input[name="customer_phone"]')?.value || '';
                        const customerEmail = document.querySelector('input[name="customer_email"]')?.value || '';
                        
                        console.log('CEP ao carregar: Recalculando frete para CEP:', zipCodeDigits);
                        
                        try {
                            const feeResponse = await fetch('{{ route("pedido.cart.calculateDeliveryFee") }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({ 
                                    zipcode: zipCodeDigits, 
                                    customer_phone: customerPhone, 
                                    customer_email: customerEmail 
                                })
                            });
                            
                            const feeData = await feeResponse.json();
                            console.log('CEP ao carregar: Resposta do cálculo de frete:', feeData);
                            
                            if (feeData.success && feeData.delivery_fee !== undefined) {
                                const deliveryFee = parseFloat(feeData.delivery_fee) || 0;
                                const baseDeliveryFee = parseFloat(feeData.base_delivery_fee || feeData.delivery_fee) || 0;
                                const discountPercent = parseFloat(feeData.discount_percent || 0) || 0;
                                const discountAmount = parseFloat(feeData.discount_amount || 0) || 0;
                                
                                console.log('CEP ao carregar: Frete recalculado:', {
                                    deliveryFee,
                                    baseDeliveryFee,
                                    discountPercent,
                                    discountAmount
                                });
                                
                                window.checkoutData.freteCalculado = true;
                                window.checkoutData.deliveryFee = deliveryFee;
                                window.checkoutData.baseDeliveryFee = baseDeliveryFee;
                                window.checkoutData.deliveryDiscountPercent = discountPercent;
                                window.checkoutData.deliveryDiscountAmount = discountAmount;
                                
                                document.getElementById('hidden_delivery_fee').value = deliveryFee.toFixed(2);
                                document.getElementById('hidden_base_delivery_fee').value = baseDeliveryFee.toFixed(2);
                                document.getElementById('hidden_delivery_discount_percent').value = discountPercent.toFixed(0);
                                document.getElementById('hidden_delivery_discount_amount').value = discountAmount.toFixed(2);
                                
                                await updateOrderSummary(null, deliveryFee);
                                updateFinalizeButtonState();
                            } else {
                                console.warn('CEP ao carregar: Falha ao calcular frete:', feeData.message || 'Resposta inválida');
                            }
                        } catch (error) {
                            console.error('Erro ao calcular frete ao carregar:', error);
                        }
                    }
                }, 1000);
            }
        }
    }
    
    // Executar verificação quando DOM estiver pronto
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', verificarCepAoCarregar);
    } else {
        // DOM já está pronto, executar após um pequeno delay para garantir que outros scripts inicializaram
        setTimeout(verificarCepAoCarregar, 1000);
    }

    // Tornar buscarCep disponível globalmente para debug
    window.buscarCep = buscarCep;
})();

// Função para validar cupom antes do submit
async function validateCouponBeforeSubmit() {
    const couponCode = document.getElementById('applied_coupon_code')?.value?.trim() || '';
    
    // Se não há cupom, não precisa validar
    if (!couponCode) {
        return { valid: true };
    }
    
    try {
        const customerEmail = document.querySelector('input[name="customer_email"]')?.value || '';
        const customerPhone = document.querySelector('input[name="customer_phone"]')?.value || '';
        const currentDeliveryFee = parseFloat(window.checkoutData?.deliveryFee) || parseFloat(document.getElementById('summaryDeliveryFee')?.textContent?.replace(/[^\d,]/g, '')?.replace(',', '.')) || 0;
        
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const response = await fetch('{{ route("pedido.checkout.calculate-discounts") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                customer_email: customerEmail,
                customer_phone: customerPhone,
                coupon_code: couponCode,
                delivery_fee: currentDeliveryFee,
                use_cashback: false, // Não usar cashback na validação
            })
        });
        
        const data = await response.json();
        const couponDiscount = parseFloat(data.coupon_discount || 0);
        const couponMessage = data.coupon_message || null;
        
        // Se não há desconto E há mensagem de erro, o cupom é inválido
        if (couponDiscount === 0 && couponMessage && typeof couponMessage === 'string' && couponMessage.trim() !== '') {
            // Cupom inválido - limpar campos
            document.getElementById('applied_coupon_code').value = '';
            document.getElementById('coupon_code_public').value = '';
            document.getElementById('coupon_code_private').value = '';
            
            // Mostrar mensagem ao usuário
            const couponFeedback = document.getElementById('couponFeedback');
            if (couponFeedback) {
                couponFeedback.textContent = couponMessage.trim();
                couponFeedback.className = 'text-sm mt-2 text-red-600';
            }
            
            // Atualizar resumo sem o cupom
            await updateOrderSummary();
            
            return { 
                valid: false, 
                message: couponMessage.trim() 
            };
        }
        
        // Se há desconto ou não há mensagem de erro, o cupom é válido
        return { valid: true };
    } catch (error) {
        console.error('Erro ao validar cupom antes do submit:', error);
        // Em caso de erro, permitir continuar (o backend também validará)
        return { valid: true };
    }
}

// Validação antes do submit
document.getElementById('checkoutForm')?.addEventListener('submit', async function(e) {
    // IMPORTANTE: Prevenir submit padrão até validar cupom
    e.preventDefault();
    
    // Primeiro: validar cupom se houver
    const couponValidation = await validateCouponBeforeSubmit();
    if (!couponValidation.valid) {
        // Cupom inválido já foi removido e mensagem mostrada
        console.log('Submit: Cupom inválido removido, continuando sem cupom');
        // Aguardar um pouco para o usuário ver a mensagem
        await new Promise(resolve => setTimeout(resolve, 500));
    }
    
    // Garantir que o cupom foi removido dos campos antes de continuar
    const appliedCoupon = document.getElementById('applied_coupon_code')?.value?.trim() || '';
    const publicCoupon = document.getElementById('coupon_code_public')?.value?.trim() || '';
    const privateCoupon = document.getElementById('coupon_code_private')?.value?.trim() || '';
    
    // Se ainda há cupom após validação, remover manualmente
    if (appliedCoupon && !couponValidation.valid) {
        document.getElementById('applied_coupon_code').value = '';
        document.getElementById('coupon_code_public').value = '';
        document.getElementById('coupon_code_private').value = '';
        console.log('Submit: Removendo cupom inválido dos campos antes de submeter');
    }
    
    // Agora prosseguir com a validação normal e submit
    // Verificar se o frete foi calculado
    // Verificar também no DOM para ter certeza (backup)
    const deliveryFeeText = document.getElementById('summaryDeliveryFee')?.textContent?.trim() || '';
    const deliveryFeeInDOM = parseFloat(deliveryFeeText.replace(/[^\d,]/g, '').replace(',', '.')) || 0;
    const isGratis = deliveryFeeText.toLowerCase().includes('grátis') || deliveryFeeText.toLowerCase().includes('gratis');
    
    // Verificar se é texto inicial (não calcular)
    const textoInicial = deliveryFeeText.toLowerCase();
    const isTextoInicial = textoInicial.includes('informe o cep') || 
                          textoInicial.includes('aguardando') ||
                          (!textoInicial.includes('r$') && !textoInicial.includes('grátis') && !textoInicial.includes('gratis'));
    
    // Frete calculado = flag true OU existe valor válido no DOM (ignorar textos iniciais)
    const freteCalculado = window.checkoutData.freteCalculado === true || 
                           (!isTextoInicial && isGratis) ||
                           (!isTextoInicial && deliveryFeeText.length > 0 && (!isNaN(deliveryFeeInDOM) || deliveryFeeText.includes('R$')));
    
    console.log('Submit: Verificando frete', {
        freteCalculado: window.checkoutData.freteCalculado,
        deliveryFeeInDOM: deliveryFeeInDOM,
        deliveryFeeInData: window.checkoutData.deliveryFee,
        deliveryFeeText: deliveryFeeText,
        finalCheck: freteCalculado
    });
    
    if (!freteCalculado) {
        e.preventDefault();
        const zipCode = document.getElementById('zip_code')?.value.replace(/\D/g, '');
        if (!zipCode || zipCode.length !== 8) {
            alert('Por favor, informe um CEP válido para calcular o frete de entrega.');
            document.getElementById('zip_code')?.focus();
        } else {
            alert('Aguarde o cálculo do frete de entrega antes de finalizar o pedido.');
        }
        return false;
    }
    
    const zipCode = document.getElementById('zip_code')?.value.trim();
    const number = document.getElementById('number')?.value.trim();
    const street = document.getElementById('address')?.value.trim();
    const neighborhood = document.getElementById('neighborhood')?.value.trim();
    const city = document.getElementById('city')?.value.trim();
    const state = document.getElementById('state')?.value.trim();
    const dateSelect = document.getElementById('scheduled_delivery_date');
    const slotSelect = document.getElementById('scheduled_delivery_slot');
    const slotError = document.getElementById('slotError');
    
    // Validar CEP e número primeiro
    if (!zipCode || zipCode.replace(/\D/g, '').length !== 8) {
        e.preventDefault();
        alert('Por favor, informe um CEP válido.');
        document.getElementById('zip_code')?.focus();
        return false;
    }
    
    if (!number) {
        e.preventDefault();
        alert('Por favor, informe o número do endereço.');
        document.getElementById('number')?.focus();
        return false;
    }
    
    // Após CEP e número, validar campos restantes do endereço
    if (!street) {
        e.preventDefault();
        alert('Por favor, informe o endereço (rua/logradouro).');
        document.getElementById('address')?.classList.add('border-red-500');
        document.getElementById('address')?.focus();
        return false;
    }
    
    if (!neighborhood) {
        e.preventDefault();
        alert('Por favor, informe o bairro.');
        document.getElementById('neighborhood')?.classList.add('border-red-500');
        document.getElementById('neighborhood')?.focus();
        return false;
    }
    
    if (!city) {
        e.preventDefault();
        alert('Por favor, informe a cidade.');
        document.getElementById('city')?.classList.add('border-red-500');
        document.getElementById('city')?.focus();
        return false;
    }
    
    if (!state || state.length !== 2) {
        e.preventDefault();
        alert('Por favor, informe o estado (UF com 2 letras).');
        document.getElementById('state')?.classList.add('border-red-500');
        document.getElementById('state')?.focus();
        return false;
    }
    
    // Validar data
    if (!dateSelect || !dateSelect.value) {
        e.preventDefault();
        alert('Por favor, selecione uma data de entrega.');
        dateSelect?.focus();
        return false;
    }
    
    // Validar horário
    if (!slotSelect || !slotSelect.value || slotSelect.disabled) {
        e.preventDefault();
        alert('Por favor, selecione um horário de entrega.');
        if (slotError) {
            slotError.classList.remove('hidden');
        }
        slotSelect?.focus();
        return false;
    }
    
    // Limpar erro se tudo estiver ok
    if (slotError) {
        slotError.classList.add('hidden');
    }
    
    // Se chegou até aqui, todas as validações passaram
    // Submeter o formulário manualmente
    console.log('Submit: Todas as validações passaram, submetendo formulário');
    const form = document.getElementById('checkoutForm');
    if (form) {
        // Criar um novo evento de submit para garantir que o formulário seja processado
        const submitEvent = new Event('submit', { bubbles: true, cancelable: true });
        
        // Não prevenir o novo evento - deixar o formulário ser submetido normalmente
        // Mas primeiro precisamos remover o preventDefault do evento original
        // Então vamos submeter diretamente
        form.submit();
    }
    
    return false; // Já submetemos manualmente, não precisa do submit padrão
});

// Listener direto no botão para mobile (garantir que funciona mesmo se submit event não disparar)
document.getElementById('btn-finalize-order')?.addEventListener('click', function(e) {
    console.log('Botão clicado diretamente:', {
        disabled: this.disabled,
        type: this.type,
        form: this.form
    });
    
    // Se o botão está disabled, prevenir clique
    if (this.disabled) {
        e.preventDefault();
        e.stopPropagation();
        console.log('Botão está disabled, bloqueando clique');
        
        // Verificar por que está disabled e mostrar mensagem
        const deliveryFeeText = document.getElementById('summaryDeliveryFee')?.textContent?.trim() || '';
        const textoInicial = deliveryFeeText.toLowerCase();
        const isTextoInicial = textoInicial.includes('informe o cep') || 
                              textoInicial.includes('aguardando') ||
                              (!textoInicial.includes('r$') && !textoInicial.includes('grátis') && !textoInicial.includes('gratis'));
        const isGratis = !isTextoInicial && (textoInicial.includes('grátis') || textoInicial.includes('gratis'));
        const freteCalculado = window.checkoutData.freteCalculado === true || (!isTextoInicial && isGratis);
        
        if (!freteCalculado) {
            alert('Por favor, aguarde o cálculo do frete de entrega antes de finalizar o pedido.');
        } else {
            // Forçar atualização do estado do botão
            updateFinalizeButtonState();
            // Se ainda estiver disabled após atualização, tentar submeter o form manualmente
            setTimeout(() => {
                if (!this.disabled && this.form) {
                    this.form.requestSubmit();
                }
            }, 100);
        }
        return false;
    }
    
    // Se não está disabled, permitir que o submit event do form seja disparado
    return true;
});

// Listener adicional para touch events no mobile
document.getElementById('btn-finalize-order')?.addEventListener('touchend', function(e) {
    console.log('Touch event no botão:', {
        disabled: this.disabled,
        type: this.type
    });
    
    // Se não está disabled, disparar submit do form
    if (!this.disabled && this.form) {
        // Pequeno delay para garantir que o click event também seja processado
        setTimeout(() => {
            if (!this.disabled) {
                this.form.requestSubmit();
            }
        }, 50);
    }
}, { passive: true });

// Função para atualizar dinamicamente o combobox de cupons
function updateCouponsCombobox(eligibleCoupons) {
    const couponsSection = document.getElementById('couponsAvailableSection');
    const couponsSeparator = document.getElementById('couponsSeparator');
    const couponSelect = document.getElementById('coupon_code_public');

    if (!couponsSection || !couponsSeparator || !couponSelect) {
        console.warn('updateCouponsCombobox: Elementos do combobox não encontrados');
        return;
    }

    // Se não há cupons elegíveis, esconder o combobox
    if (!eligibleCoupons || eligibleCoupons.length === 0) {
        couponsSection.style.display = 'none';
        couponsSeparator.style.display = 'none';
        console.log('updateCouponsCombobox: Nenhum cupom elegível, combobox oculto');
        return;
    }

    // Se há cupons elegíveis, mostrar o combobox e popular com os cupons
    console.log('updateCouponsCombobox: Cupons elegíveis encontrados:', eligibleCoupons.length);

    // Limpar opções existentes (manter apenas a primeira)
    couponSelect.innerHTML = '<option value="">Selecione um cupom</option>';

    // Adicionar cupons elegíveis
    eligibleCoupons.forEach(coupon => {
        const option = document.createElement('option');
        option.value = coupon.code;
        option.setAttribute('data-discount', coupon.formatted_value);
        option.textContent = `${coupon.name} - ${coupon.formatted_value}`;
        if (coupon.minimum_amount) {
            option.textContent += ` (Mín: R$ ${Number(coupon.minimum_amount).toFixed(2).replace('.', ',')})`;
        }
        couponSelect.appendChild(option);
    });

    // Mostrar o combobox
    couponsSection.style.display = 'block';
    couponsSeparator.style.display = 'block';

    console.log('updateCouponsCombobox: Combobox atualizado com', eligibleCoupons.length, 'cupons');
}
</script>
@endpush
