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
                                <input type="text" name="number" id="number" value="{{ old('number', $prefill['number'] ?? '') }}" required class="w-full border border-gray-300 rounded-lg px-3 sm:px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary">
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
                        <select name="coupon_code" id="coupon_code_public" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary">
                            <option value="">Selecione um cupom</option>
                            @foreach($eligibleCoupons as $coupon)
                            <option value="{{ $coupon->code }}" data-discount="{{ $coupon->formatted_value }}">
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
                        <input type="text" name="coupon_code" id="coupon_code_private" placeholder="Digite o código do cupom privado" class="flex-1 border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary" value="{{ old('coupon_code', isset($appliedCouponCode) ? $appliedCouponCode : '') }}">
                        <button type="button" id="applyCouponBtn" class="px-4 py-2 rounded-lg border border-gray-300 hover:bg-gray-50">Aplicar</button>
                    </div>
                    <p id="couponFeedback" class="text-sm mt-2 text-gray-600"></p>
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
                
                <!-- Método de Pagamento (hidden, padrão PIX) -->
                <input type="hidden" name="payment_method" value="pix" id="payment_method">
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
                            <span id="summaryDeliveryFee" class="text-gray-900 font-medium">R$ 0,00</span>
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

                    <div class="w-full mt-6">
                        <button type="submit" id="btn-finalize-order" class="w-full bg-primary text-primary-foreground py-3 rounded-lg font-semibold hover:bg-primary/90 transition-colors disabled:opacity-50 disabled:cursor-not-allowed" disabled>
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
        const deliveryFeeInDOM = parseFloat(
            document.getElementById('summaryDeliveryFee')?.textContent
                .replace(/[^\d,]/g, '')
                .replace(',', '.') || '0'
        ) || 0;
        
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
        
        // Se o frete foi calculado (flag = true) OU se há um valor de frete no DOM (mesmo que 0 = grátis)
        // IMPORTANTE: Se deliveryFeeInDOM existe no DOM, significa que o frete foi calculado
        // Mesmo que seja 0 (grátis), isso significa que foi calculado
        const freteFoiCalculado = window.checkoutData.freteCalculado === true || 
                                   (deliveryFeeInDOM !== null && !isNaN(deliveryFeeInDOM) && document.getElementById('summaryDeliveryFee')?.textContent.trim().length > 0);
        
        if (!freteFoiCalculado) {
            btnFinalize.disabled = true;
            if (messagePending) {
                messagePending.classList.remove('hidden');
                messagePending.textContent = '⚠️ Aguardando cálculo do frete de entrega...';
            }
        } else {
            // Frete calculado (flag OU valor no DOM), habilitar botão
            // Se o frete está no DOM mas a flag não foi marcada, marcar agora
            if (!window.checkoutData.freteCalculado && document.getElementById('summaryDeliveryFee')?.textContent.trim().length > 0) {
                window.checkoutData.freteCalculado = true;
                window.checkoutData.deliveryFee = deliveryFeeInDOM;
                console.log('updateFinalizeButtonState: Frete detectado no DOM, marcando como calculado:', deliveryFeeInDOM);
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
async function updateOrderSummary(subtotal = null, deliveryFee = null) {
    // Priorizar parâmetros passados, depois window.checkoutData, depois elementos do DOM
    const currentSubtotal = parseFloat(subtotal) || parseFloat(window.checkoutData?.subtotal) || parseFloat(document.getElementById('summarySubtotal')?.textContent?.replace(/[^\d,]/g, '')?.replace(',', '.')) || 0;
    
    // Priorizar o parâmetro deliveryFee se foi passado (não-null), senão usar outras fontes
    let currentDeliveryFee;
    if (deliveryFee !== null && deliveryFee !== undefined && !isNaN(parseFloat(deliveryFee))) {
        currentDeliveryFee = parseFloat(deliveryFee);
        console.log('updateOrderSummary: Usando frete do parâmetro:', currentDeliveryFee);
    } else {
        currentDeliveryFee = parseFloat(window.checkoutData?.deliveryFee) || parseFloat(document.getElementById('summaryDeliveryFee')?.textContent?.replace(/[^\d,]/g, '')?.replace(',', '.')) || 0;
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
        
        const total = parseFloat(data.total || (currentSubtotal + currentDeliveryFee - couponDiscount - cashbackUsed));
        
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
        
        // Exibir desconto de frete se houver (vem de window.checkoutData após buscarCep)
        const deliveryDiscountAmount = window.checkoutData?.deliveryDiscountAmount || 0;
        const deliveryDiscountPercent = window.checkoutData?.deliveryDiscountPercent || 0;
        const baseDeliveryFee = window.checkoutData?.baseDeliveryFee || currentDeliveryFee;
        
        if (deliveryDiscountAmount > 0) {
            const discountRow = document.getElementById('summaryDeliveryDiscountRow');
            const discountLabel = document.getElementById('summaryDeliveryDiscountLabel');
            discountLabel.textContent = `Desconto no frete (${deliveryDiscountPercent}%)`;
            document.getElementById('summaryDeliveryDiscount').textContent = `- R$ ${Number(deliveryDiscountAmount).toFixed(2).replace('.', ',')}`;
            discountRow.classList.remove('hidden');
        } else {
            document.getElementById('summaryDeliveryDiscountRow').classList.add('hidden');
        }
        
        // Mostrar frete final (já com desconto aplicado)
        if (currentDeliveryFee <= 0 && baseDeliveryFee > 0) {
            document.getElementById('summaryDeliveryFee').textContent = 'Grátis';
            document.getElementById('summaryDeliveryFee').classList.add('text-green-700');
        } else {
            document.getElementById('summaryDeliveryFee').textContent = `R$ ${Number(currentDeliveryFee).toFixed(2).replace('.', ',')}`;
            document.getElementById('summaryDeliveryFee').classList.remove('text-green-700');
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
        
        // Atualizar dados globais (preservar dados de desconto de frete se existirem)
        window.checkoutData = { 
            subtotal: Number(currentSubtotal), 
            deliveryFee: Number(currentDeliveryFee), 
            baseDeliveryFee: window.checkoutData?.baseDeliveryFee || Number(currentDeliveryFee),
            deliveryDiscountPercent: window.checkoutData?.deliveryDiscountPercent || 0,
            deliveryDiscountAmount: window.checkoutData?.deliveryDiscountAmount || 0,
            couponDiscount: Number(couponDiscount),
            cashbackUsed: Number(cashbackUsed),
            cashbackEarned: Number(cashbackEarned),
            total: Number(total)
        };
        
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
        console.error('Erro ao calcular descontos:', error);
        // Fallback simples
        const simpleTotal = Number(currentSubtotal) + Number(currentDeliveryFee);
        if (document.getElementById('summaryTotal')) {
            document.getElementById('summaryTotal').textContent = `R$ ${simpleTotal.toFixed(2).replace('.', ',')}`;
        }
    }
}

// Atualizar ao mudar email/telefone
// Função para preencher endereço quando cliente é identificado
async function loadCustomerAddress(phone, email) {
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
                    numberField.value = customer.number;
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
                // Se já tem CEP preenchido, calcular frete automaticamente
                console.log('loadCustomerAddress: CEP já preenchido, calculando frete automaticamente');
                setTimeout(async () => {
                    const zipCodeField = document.getElementById('zip_code');
                    if (zipCodeField && zipCodeField.value.trim()) {
                        const cep = zipCodeField.value.replace(/\D/g, '');
                        if (cep.length === 8) {
                            // Buscar CEP e calcular frete - buscarCep já marca freteCalculado quando bem-sucedido
                            if (typeof buscarCep === 'function') {
                                await buscarCep(); // buscarCep já calcula o frete automaticamente e marca a flag
                            }
                        }
                    }
                }, 500);
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

// Recalcular cashback quando número do endereço mudar (pode afetar frete)
document.getElementById('number')?.addEventListener('blur', async function() {
    // Se o frete já foi calculado, recalcular resumo para garantir cashback atualizado
    if (window.checkoutData.freteCalculado) {
        await updateOrderSummary();
    }
});

// Busca automática de CEP via ViaCEP
(function() {
    const zipCodeInput = document.getElementById('zip_code');
    const cepFeedback = document.getElementById('cepFeedback');
    
    if (!zipCodeInput) return;
    
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
        if (cep.length !== 8) {
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
                    
                    // Atualizar o resumo com o novo frete
                    await updateOrderSummary(null, deliveryFee);
                    
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
        
        // PRIMEIRO: Verificar se o frete já está calculado no DOM
        const deliveryFeeElement = document.getElementById('summaryDeliveryFee');
        let freteJaCalculado = false;
        if (deliveryFeeElement) {
            const deliveryFeeText = deliveryFeeElement.textContent;
            const deliveryFee = parseFloat(deliveryFeeText.replace(/[^\d,]/g, '').replace(',', '.')) || 0;
            
            // Se já existe um frete calculado e maior que 0, marcar como calculado
            if (deliveryFee > 0) {
                window.checkoutData.freteCalculado = true;
                window.checkoutData.deliveryFee = deliveryFee;
                freteJaCalculado = true;
                console.log('DOMContentLoaded: Frete já calculado detectado:', deliveryFee);
                // Atualizar estado do botão IMEDIATAMENTE se frete já está calculado
                updateFinalizeButtonState();
            }
            
            if (deliveryFee <= 0) {
                filtrarCuponsFreteGratis(0);
            }
        }
        
        // Se cliente já foi identificado (telefone/email preenchidos), carregar endereço automaticamente
        const customerPhone = document.getElementById('customer_phone')?.value || '';
        const customerEmail = document.getElementById('customer_email')?.value || '';
        if (customerPhone || customerEmail) {
            console.log('DOMContentLoaded: Cliente já identificado, carregando endereço', { customerPhone, customerEmail });
            // loadCustomerAddress pode calcular o frete, então aguardar antes de verificar estado do botão
            // MAS: Se o frete já está calculado, não deixar loadCustomerAddress sobrescrever
            loadCustomerAddress(customerPhone, customerEmail).then(() => {
                // Preservar estado do frete se já estava calculado
                if (freteJaCalculado && window.checkoutData.deliveryFee > 0) {
                    window.checkoutData.freteCalculado = true;
                    console.log('DOMContentLoaded: Preservando frete já calculado após loadCustomerAddress');
                }
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
                // Endereço já preenchido
                if (!freteJaCalculado) {
                    // Apenas calcular frete se ainda não foi calculado
                    const currentFee = parseFloat(window.checkoutData.deliveryFee) || 0;
                    if (currentFee <= 0 && typeof buscarCep === 'function') {
                        console.log('DOMContentLoaded: CEP preenchido sem frete, calculando...');
                        // Chamar apenas a parte de cálculo de frete, sem buscar endereço novamente
                        buscarCep().then(() => {
                            updateFinalizeButtonState();
                        });
                    } else {
                        updateFinalizeButtonState();
                    }
                } else {
                    // Frete já calculado, apenas atualizar estado do botão
                    updateFinalizeButtonState();
                }
            }
        } else {
            updateFinalizeButtonState();
        }
        
        // Verificar estado inicial do botão (mas pode ser atualizado depois)
        updateFinalizeButtonState();
        
        // Atualizar resumo ao carregar para mostrar cashback ganho
        // MAS: Se frete já estava calculado, não deixar updateOrderSummary sobrescrever
        updateOrderSummary().then(() => {
            // Preservar frete calculado após updateOrderSummary
            if (freteJaCalculado && window.checkoutData.deliveryFee > 0) {
                window.checkoutData.freteCalculado = true;
                console.log('DOMContentLoaded: Preservando frete já calculado após updateOrderSummary');
                updateFinalizeButtonState();
            }
        });
    });
    
    // Buscar ao sair do campo (blur) também
    zipCodeInput.addEventListener('blur', function() {
        const cep = this.value.replace(/\D/g, '');
        if (cep.length === 8) {
            buscarCep();
        }
    });
})();

// Validação antes do submit
document.getElementById('checkoutForm')?.addEventListener('submit', function(e) {
    // Verificar se o frete foi calculado
    // Verificar também no DOM para ter certeza (backup)
    const deliveryFeeText = document.getElementById('summaryDeliveryFee')?.textContent || '';
    const deliveryFeeInDOM = parseFloat(deliveryFeeText.replace(/[^\d,]/g, '').replace(',', '.')) || 0;
    // Frete calculado = flag true OU existe valor no DOM (mesmo que seja 0 = grátis)
    const freteCalculado = window.checkoutData.freteCalculado === true || 
                           (deliveryFeeText.trim().length > 0 && !isNaN(deliveryFeeInDOM));
    
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
    
    return true;
});
</script>
@endpush
