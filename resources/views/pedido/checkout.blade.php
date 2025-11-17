@extends('pedido.layout')

@section('title', 'Checkout - Olika')

@section('content')
@php
    $initialDeliveryFee = isset($initialDeliveryFee) ? $initialDeliveryFee : ($cartData['delivery_fee'] ?? null);
    $initialDeliveryFee = $initialDeliveryFee !== null ? (float)$initialDeliveryFee : null;

    $initialBaseDeliveryFee = isset($initialBaseDeliveryFee) ? $initialBaseDeliveryFee : $initialDeliveryFee;
    $initialBaseDeliveryFee = $initialBaseDeliveryFee !== null ? (float)$initialBaseDeliveryFee : null;

    $initialDeliveryDiscountAmount = isset($initialDeliveryDiscountAmount) ? (float)$initialDeliveryDiscountAmount : 0.0;
    $initialDeliveryDiscountPercent = isset($initialDeliveryDiscountPercent) ? (float)$initialDeliveryDiscountPercent : 0.0;

    $initialFreteCalculado = $initialDeliveryFee !== null;
@endphp
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
        <input type="hidden" name="delivery_fee" id="hidden_delivery_fee" value="{{ $initialDeliveryFee !== null ? number_format($initialDeliveryFee, 2, '.', '') : 0 }}">
        <input type="hidden" name="base_delivery_fee" id="hidden_base_delivery_fee" value="{{ $initialBaseDeliveryFee !== null ? number_format($initialBaseDeliveryFee, 2, '.', '') : 0 }}">
        <input type="hidden" name="delivery_discount_percent" id="hidden_delivery_discount_percent" value="{{ number_format($initialDeliveryDiscountPercent, 2, '.', '') }}">
        <input type="hidden" name="delivery_discount_amount" id="hidden_delivery_discount_amount" value="{{ number_format($initialDeliveryDiscountAmount, 2, '.', '') }}">
        <input type="hidden" name="delivery_fee_locked" id="hidden_delivery_fee_locked" value="{{ $initialFreteCalculado ? 1 : 0 }}">
        
        <!-- Campos hidden para pedido do PDV -->
        @if(isset($order) && $order)
        <input type="hidden" name="order_id" value="{{ $order->id }}">
        <input type="hidden" name="order_number" value="{{ $order->order_number }}">
        @endif
        
        <div class="grid lg:grid-cols-[1fr_400px] gap-4 sm:gap-6 lg:gap-8 w-full">
            <!-- Coluna Esquerda: Formulário -->
            <div class="space-y-4 sm:space-y-6 w-full">
                <!-- Dados do Cliente -->
                <div id="addressCard" class="bg-white rounded-lg border p-4 sm:p-6">
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
                                <div class="flex flex-col gap-2">
                                    <div class="relative">
                                        <input type="text" name="zip_code" id="zip_code" maxlength="9" value="{{ old('zip_code', $prefill['zip_code'] ?? '') }}" required class="w-full border border-gray-300 rounded-lg px-3 sm:px-4 py-2 pr-10 focus:outline-none focus:ring-2 focus:ring-primary transition-colors" placeholder="00000-000">
                                        <div id="cepLoadingSpinner" class="hidden absolute right-3 top-1/2 transform -translate-y-1/2">
                                            <svg class="animate-spin h-5 w-5 text-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <button type="button" id="zip_code_manual_button" class="hidden w-full border border-dashed border-red-400 text-red-600 rounded-lg px-3 sm:px-4 py-2 text-sm font-medium transition hover:bg-red-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-400">
                                        Localizar meu endereço
                                    </button>
                                    <button type="button" id="btn-manual-address" class="hidden w-full border border-gray-300 text-gray-700 rounded-lg px-3 sm:px-4 py-2 text-sm font-medium transition hover:bg-gray-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-gray-400">
                                        Não sei o CEP / Digitar manualmente
                                    </button>
                                </div>
                                <p id="cepFeedback" class="text-xs text-gray-500 mt-1 min-h-[1.25rem]"></p>
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
                                <span id="summaryDeliveryFeeOriginal" class="text-gray-400 text-xs line-through {{ ($initialDeliveryDiscountAmount > 0 && $initialBaseDeliveryFee !== null && $initialBaseDeliveryFee > ($initialDeliveryFee ?? 0)) ? '' : 'hidden' }}">
                                    @if($initialDeliveryDiscountAmount > 0 && $initialBaseDeliveryFee !== null && $initialBaseDeliveryFee > ($initialDeliveryFee ?? 0))
                                        R$ {{ number_format($initialBaseDeliveryFee, 2, ',', '.') }}
                                    @endif
                                </span>
                                <span id="summaryDeliveryFee"
                                      class="{{ $initialFreteCalculado ? ($initialDeliveryFee > 0 ? 'text-gray-900 font-medium' : 'text-green-700 font-medium') : 'text-gray-500 font-medium text-sm' }}">
                                    @if($initialFreteCalculado)
                                        @if($initialDeliveryFee > 0)
                                            R$ {{ number_format($initialDeliveryFee, 2, ',', '.') }}
                                        @else
                                            Grátis
                                        @endif
                                    @else
                                        Informe o CEP
                                    @endif
                                </span>
                            </div>
                        </div>
                        <div id="summaryDeliveryDiscountRow" class="flex justify-between text-sm text-green-700 {{ ($initialDeliveryDiscountAmount > 0 && $initialBaseDeliveryFee !== null && $initialBaseDeliveryFee > ($initialDeliveryFee ?? 0)) ? '' : 'hidden' }}">
                            <span id="summaryDeliveryDiscountLabel">
                                Desconto no frete
                                @if($initialDeliveryDiscountPercent > 0)
                                    ({{ number_format($initialDeliveryDiscountPercent, 2, ',', '.') }}%)
                                @endif
                            </span>
                            <span id="summaryDeliveryDiscount" class="font-medium">
                                - R$ {{ number_format($initialDeliveryDiscountAmount, 2, ',', '.') }}
                            </span>
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
    subtotal: {{ isset($cartData['subtotal']) ? round($cartData['subtotal'], 2) : 0 }},
    deliveryFee: {{ $initialFreteCalculado ? round($initialDeliveryFee, 2) : 'null' }},
    baseDeliveryFee: {{ $initialFreteCalculado && $initialBaseDeliveryFee !== null ? round($initialBaseDeliveryFee, 2) : 'null' }},
    deliveryDiscountPercent: {{ $initialFreteCalculado ? round($initialDeliveryDiscountPercent, 2) : 0 }},
    deliveryDiscountAmount: {{ $initialFreteCalculado ? round($initialDeliveryDiscountAmount, 2) : 0 }},
    deliveryFeeLocked: {{ $initialFreteCalculado ? 'true' : 'false' }},
    freteCalculado: {{ $initialFreteCalculado ? 'true' : 'false' }},
    couponDiscount: 0,
    cashbackUsed: 0,
    cashbackEarned: 0,
    total: {{ isset($cartData['subtotal']) ? round(($cartData['subtotal'] ?? 0) + ($initialDeliveryFee ?? 0) - ($initialDeliveryDiscountAmount ?? 0), 2) : 0 }},
    allowFinalizeWithoutFrete: false,
    manualFreteReason: null,
    lastLookupPhone: null,
    lastLookupEmail: null,
    currentCustomerId: null,
    skipAutoCepLookup: false,
    autoLookupEnabled: true,
    skipLookupUntil: 0,
    manualAddressPending: false,
    manualLocateInFlight: false,
    manualOriginalZip: null,
    manualGeneralizedZip: null
};

const ADDRESS_FIELD_IDS = ['zip_code', 'number', 'address', 'neighborhood', 'city', 'state'];
const addressCardElement = document.getElementById('addressCard');
const zipCodeInput = document.getElementById('zip_code');
const zipCodeManualButton = document.getElementById('zip_code_manual_button');

let manualLookupDebounceId = null;

function setCepFeedback(message, className = '') {
    const cepFeedbackEl = document.getElementById('cepFeedback');
    if (!cepFeedbackEl) {
        return;
    }

    if (!message) {
        cepFeedbackEl.textContent = '';
        cepFeedbackEl.className = '';
        return;
    }

    cepFeedbackEl.textContent = message;
    cepFeedbackEl.className = className;
}

function setSummaryDeliveryFeePending() {
    const deliveryFeeEl = document.getElementById('summaryDeliveryFee');
    if (deliveryFeeEl) {
        deliveryFeeEl.textContent = 'Aguardando endereço completo';
        deliveryFeeEl.className = 'text-sm text-yellow-600 font-medium';
    }

    const hiddenFee = document.getElementById('hidden_delivery_fee');
    if (hiddenFee) hiddenFee.value = '0.00';
    const hiddenBase = document.getElementById('hidden_base_delivery_fee');
    if (hiddenBase) hiddenBase.value = '0.00';
    const hiddenDiscountPercent = document.getElementById('hidden_delivery_discount_percent');
    if (hiddenDiscountPercent) hiddenDiscountPercent.value = '0';
    const hiddenDiscountAmount = document.getElementById('hidden_delivery_discount_amount');
    if (hiddenDiscountAmount) hiddenDiscountAmount.value = '0.00';
    const hiddenLocked = document.getElementById('hidden_delivery_fee_locked');
    if (hiddenLocked) hiddenLocked.value = 0;
}

function normalizeZipToDistrict(value) {
    if (!value) {
        return null;
    }

    const digits = String(value).replace(/\D/g, '');
    if (digits.length >= 8) {
        return `${digits.substring(0, 5)}000`;
    }

    if (digits.length >= 5) {
        return `${digits.substring(0, 5)}000`;
    }

    return null;
}

function applyNormalizedZip(normalizedDigits, options = {}) {
    if (!zipCodeInput || !normalizedDigits || normalizedDigits.length !== 8) {
        return null;
    }

    const formatted = `${normalizedDigits.substring(0, 5)}-${normalizedDigits.substring(5)}`;
    window.checkoutData.skipAutoCepLookup = true;
    zipCodeInput.value = formatted;
    if (options.restoreAuto !== false) {
        setTimeout(() => {
            window.checkoutData.skipAutoCepLookup = false;
        }, 0);
    }
    window.checkoutData.manualGeneralizedZip = normalizedDigits;
    return normalizedDigits;
}

function ensureManualFieldsEditable() {
    ['address', 'neighborhood', 'city', 'state'].forEach((fieldId) => {
        const field = document.getElementById(fieldId);
        if (field) {
            field.removeAttribute('readonly');
            field.required = true;
        }
    });
}

function generalizeZipCode(options = {}) {
    if (!zipCodeInput) {
        return null;
    }

    const normalized = options.value
        ? normalizeZipToDistrict(options.value)
        : normalizeZipToDistrict(zipCodeInput.value);

    if (!normalized) {
        return null;
    }

    applyNormalizedZip(normalized, { restoreAuto: false });

    window.checkoutData.autoLookupEnabled = false;
    window.checkoutData.skipAutoCepLookup = true;
    window.checkoutData.skipLookupUntil = Date.now() + 60 * 1000;
    window.checkoutData.freteCalculado = false;
    window.checkoutData.deliveryFee = null;
    window.checkoutData.baseDeliveryFee = null;

    const shouldClearAddress = options.forceClear !== false;
    if (shouldClearAddress) {
        ['address', 'neighborhood', 'number'].forEach((fieldId) => {
            const field = document.getElementById(fieldId);
            if (field) {
                field.value = '';
                field.dispatchEvent(new Event('input', { bubbles: true }));
            }
        });
        setAddressErrorState(true, {
            fields: ['address', 'neighborhood', 'number'],
            focusFieldId: 'address',
            placeholderMessage: 'Preencha rua, bairro e número'
        });
    }

    setSummaryDeliveryFeePending();

    if (options.ensureEditable) {
        ensureManualFieldsEditable();
    }

    if (options.feedbackMessage) {
        setCepFeedback(options.feedbackMessage, options.feedbackClass || 'text-xs text-yellow-600 mt-1');
    }

    updateFinalizeButtonState();
    if (typeof updateOrderSummary === 'function') {
        updateOrderSummary();
    }

    return normalized;
}

function manualFieldsAreComplete() {
    const requiredFields = ['address', 'neighborhood', 'city', 'state', 'number'];
    return requiredFields.every((fieldId) => {
        const field = document.getElementById(fieldId);
        return field && field.value.trim().length > 0;
    });
}

function scheduleManualLookup() {
    if (!window.checkoutData.manualAddressPending) {
        return;
    }

    if (!manualFieldsAreComplete()) {
        return;
    }

    if (window.checkoutData.manualLocateInFlight) {
        return;
    }

    if (manualLookupDebounceId) {
        clearTimeout(manualLookupDebounceId);
    }

    manualLookupDebounceId = setTimeout(() => {
        if (zipCodeManualButton) {
            zipCodeManualButton.click();
        }
    }, 500);
}

['address', 'neighborhood', 'city', 'state', 'number'].forEach((fieldId) => {
    const field = document.getElementById(fieldId);
    if (field) {
        field.addEventListener('input', () => {
            clearAddressErrorState();
            scheduleManualLookup();
        });
        field.addEventListener('blur', scheduleManualLookup);
        field.addEventListener('change', scheduleManualLookup);
    }
});

function setAddressErrorState(isError, options = {}) {
    const { fields, focusFieldId, placeholderMessage } = options || {};
    const targetFields = Array.isArray(fields) && fields.length ? fields : ADDRESS_FIELD_IDS;

    if (addressCardElement) {
        addressCardElement.classList.toggle('border-red-500', isError);
        addressCardElement.classList.toggle('ring-1', isError);
        addressCardElement.classList.toggle('ring-red-200', isError);
    }

    targetFields.forEach((fieldId) => {
        const field = document.getElementById(fieldId);
        if (!field) {
            return;
        }

        if (!field.dataset.originalPlaceholder) {
            field.dataset.originalPlaceholder = field.getAttribute('placeholder') || '';
        }

        if (isError) {
            field.classList.add('border-red-500', 'focus:border-red-500', 'focus:ring-red-500');
            if (placeholderMessage) {
                field.setAttribute('placeholder', placeholderMessage);
            }
        } else {
            field.classList.remove('border-red-500', 'focus:border-red-500', 'focus:ring-red-500');
            field.setAttribute('placeholder', field.dataset.originalPlaceholder);
        }
    });

    if (isError && focusFieldId) {
        const focusField = document.getElementById(focusFieldId);
        if (focusField) {
            setTimeout(() => focusField.focus(), 0);
        }
    }
}

function clearAddressErrorState() {
    setAddressErrorState(false);
}

function toggleZipCodeManualMode(enable, options = {}) {
    const { reason, focusZip = false } = options || {};
    if (!zipCodeInput || !zipCodeManualButton) {
        return;
    }

    if (enable) {
        window.checkoutData.skipAutoCepLookup = true;
        window.checkoutData.skipLookupUntil = Date.now() + 3000;

        zipCodeInput.dataset.requiredOriginal = zipCodeInput.dataset.requiredOriginal ?? (zipCodeInput.hasAttribute('required') ? 'true' : 'false');
        zipCodeInput.removeAttribute('required');
        ['address', 'neighborhood', 'city', 'state'].forEach((fieldId) => {
            const field = document.getElementById(fieldId);
            if (field) {
                field.removeAttribute('readonly');
            }
        });
        zipCodeManualButton.classList.remove('hidden');
        zipCodeManualButton.disabled = false;
        zipCodeManualButton.dataset.reason = reason || '';
        zipCodeManualButton.textContent = reason
            ? 'Confirmar endereço manualmente'
            : 'Localizar meu endereço';
    } else {
        if (zipCodeInput.dataset.requiredOriginal === 'true') {
            zipCodeInput.setAttribute('required', 'required');
        }
        if (window.checkoutData) {
            window.checkoutData.skipAutoCepLookup = false;
            window.checkoutData.skipLookupUntil = Date.now();
        }
        zipCodeManualButton.classList.add('hidden');
        zipCodeManualButton.disabled = true;
        zipCodeManualButton.dataset.reason = '';
        zipCodeManualButton.textContent = 'Localizar meu endereço';
        if (focusZip) {
            zipCodeInput.focus();
            zipCodeInput.selectionStart = zipCodeInput.selectionEnd = zipCodeInput.value.length;
        }
    }
}

function applyCustomerPrefill(prefill, options = {}) {
    if (!prefill) {
        return;
    }

    if (window.checkoutData?.manualAddressPending) {
        return;
    }

    const force = options.force ?? true;
    const triggerFrete = options.triggerFrete ?? true;
    const runSummaryUpdate = options.runSummaryUpdate ?? true;

    const nameInput = document.getElementById('customer_name');
    if (nameInput && prefill.customer_name) {
        if (force || !nameInput.value.trim()) {
            nameInput.value = prefill.customer_name;
        }
    }

    const phoneInput = document.getElementById('customer_phone');
    if (phoneInput && prefill.customer_phone) {
        if (force || !phoneInput.value.trim()) {
            phoneInput.value = prefill.customer_phone;
        }
    }

    const emailInput = document.getElementById('customer_email');
    if (emailInput && prefill.customer_email) {
        if (force || !emailInput.value.trim()) {
            emailInput.value = prefill.customer_email;
        }
    }

    const addressField = document.getElementById('address');
    if (addressField && prefill.address) {
        if (force || !addressField.value.trim()) {
            addressField.value = prefill.address;
            addressField.removeAttribute('readonly');
        }
    }

    const numberField = document.getElementById('number');
    if (numberField && prefill.number) {
        const numberOnly = String(prefill.number).replace(/\D/g, '');
        if (force || !numberField.value.trim()) {
            numberField.value = numberOnly;
        }
    }

    const neighborhoodField = document.getElementById('neighborhood');
    if (neighborhoodField && prefill.neighborhood) {
        if (force || !neighborhoodField.value.trim()) {
            neighborhoodField.value = prefill.neighborhood;
            neighborhoodField.removeAttribute('readonly');
        }
    }

    const cityField = document.getElementById('city');
    if (cityField && prefill.city) {
        if (force || !cityField.value.trim()) {
            cityField.value = prefill.city;
            cityField.removeAttribute('readonly');
        }
    }

    const stateField = document.getElementById('state');
    if (stateField && prefill.state) {
        if (force || !stateField.value.trim()) {
            stateField.value = prefill.state;
            stateField.removeAttribute('readonly');
        }
    }

    let cepFoiAlterado = false;
    const zipCodeField = document.getElementById('zip_code');
    if (zipCodeField && prefill.zip_code) {
        const cepDigits = String(prefill.zip_code).replace(/\D/g, '');
        if (cepDigits.length === 8) {
            const formattedCep = `${cepDigits.substring(0, 5)}-${cepDigits.substring(5)}`;
            if (force || zipCodeField.value.replace(/\D/g, '') !== cepDigits) {
                zipCodeField.value = formattedCep;
                cepFoiAlterado = true;
            }
        } else if (force || !zipCodeField.value.trim()) {
            zipCodeField.value = prefill.zip_code;
            cepFoiAlterado = true;
        }
    }

    if (prefill.complement !== undefined) {
        const complementField = document.getElementById('complement');
        if (complementField) {
            if (force || !complementField.value.trim()) {
                complementField.value = prefill.complement ?? '';
            }
        }
    }

    clearAddressErrorState();
    window.checkoutData.allowFinalizeWithoutFrete = false;
    window.checkoutData.manualFreteReason = null;

    if (cepFoiAlterado && triggerFrete && typeof buscarCep === 'function') {
        window.checkoutData.freteCalculado = false;
        window.checkoutData.deliveryFeeLocked = false;
        buscarCep();
    } else if (runSummaryUpdate) {
        updateFinalizeButtonState();
    }
}

async function handleDeliveryFeeSuccess(feeData, options = {}) {
    if (!feeData) {
        return;
    }

    const wasManualFlow = window.checkoutData.manualAddressPending === true || window.checkoutData.manualOriginalZip !== null;
    const manualOriginalZipDigits = window.checkoutData.manualOriginalZip;
    window.checkoutData.manualAddressPending = false;
    window.checkoutData.manualOriginalZip = null;
    window.checkoutData.manualLocateInFlight = false;

    if (zipCodeManualButton) {
        zipCodeManualButton.classList.add('hidden');
        zipCodeManualButton.disabled = true;
        zipCodeManualButton.dataset.reason = '';
        zipCodeManualButton.textContent = 'Localizar meu endereço';
    }

    const deliveryFee = parseFloat(feeData.delivery_fee ?? 0) || 0;
    const baseDeliveryFee = parseFloat(feeData.base_delivery_fee ?? feeData.delivery_fee ?? 0) || 0;
    const discountPercent = parseFloat(feeData.discount_percent ?? 0) || 0;
    const discountAmount = parseFloat(feeData.discount_amount ?? 0) || 0;
    const distanceKm = feeData.distance_km ?? null;
    const resolvedZip = feeData.resolved_zip_code ?? feeData.zip_code ?? null;

    if (resolvedZip && zipCodeInput) {
        const resolvedDigitsRaw = String(resolvedZip).replace(/\D/g, '');
        let digitsToApply = resolvedDigitsRaw;

        if (wasManualFlow) {
            const normalized = normalizeZipToDistrict(resolvedDigitsRaw || window.checkoutData.manualGeneralizedZip || manualOriginalZipDigits);
            if (normalized) {
                digitsToApply = normalized;
            }
        }

        if (digitsToApply && digitsToApply.length === 8) {
            applyNormalizedZip(digitsToApply);
        }
    }

    toggleZipCodeManualMode(false, { focusZip: false });

    window.checkoutData.freteCalculado = true;
    window.checkoutData.allowFinalizeWithoutFrete = false;
    window.checkoutData.manualFreteReason = null;
    window.checkoutData.deliveryFee = deliveryFee;
    window.checkoutData.baseDeliveryFee = baseDeliveryFee;
    window.checkoutData.deliveryDiscountPercent = discountPercent;
    window.checkoutData.deliveryDiscountAmount = discountAmount;
    window.checkoutData.deliveryFeeLocked = false;
    window.checkoutData.distanceKm = distanceKm;

    const hiddenFee = document.getElementById('hidden_delivery_fee');
    const hiddenBase = document.getElementById('hidden_base_delivery_fee');
    const hiddenDiscountPercent = document.getElementById('hidden_delivery_discount_percent');
    const hiddenDiscountAmount = document.getElementById('hidden_delivery_discount_amount');
    const hiddenLocked = document.getElementById('hidden_delivery_fee_locked');

    if (hiddenFee) hiddenFee.value = deliveryFee.toFixed(2);
    if (hiddenBase) hiddenBase.value = baseDeliveryFee.toFixed(2);
    if (hiddenDiscountPercent) hiddenDiscountPercent.value = discountPercent.toFixed(2);
    if (hiddenDiscountAmount) hiddenDiscountAmount.value = discountAmount.toFixed(2);
    if (hiddenLocked) hiddenLocked.value = 0;

    await updateOrderSummary(null, deliveryFee);
    setTimeout(async () => {
        await updateOrderSummary(null, deliveryFee);
    }, 100);

    filtrarCuponsFreteGratis(deliveryFee);

    const successMessage = options.successMessage || 'Frete calculado com sucesso!';
    setCepFeedback(successMessage, 'text-xs text-green-600 mt-1');

    window.checkoutData.autoLookupEnabled = true;
    window.checkoutData.skipAutoCepLookup = false;
    window.checkoutData.skipLookupUntil = Date.now() + 500;

    clearAddressErrorState();
    updateFinalizeButtonState();
}

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
            if (window.checkoutData.allowFinalizeWithoutFrete === true) {
                btnFinalize.disabled = false;
                if (messagePending) {
                    messagePending.classList.remove('hidden');
                    messagePending.textContent = window.checkoutData.manualFreteReason || 'Frete não calculado automaticamente. Confirme o endereço e combine a taxa de entrega com a loja.';
                }
            } else {
                btnFinalize.disabled = true;
                if (messagePending) {
                    messagePending.classList.remove('hidden');
                    messagePending.textContent = '⚠️ Aguardando cálculo do frete de entrega...';
                }
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
                delivery_fee_locked: window.checkoutData?.deliveryFeeLocked ?? false,
                base_delivery_fee: window.checkoutData?.baseDeliveryFee ?? currentDeliveryFee,
                delivery_discount_percent: window.checkoutData?.deliveryDiscountPercent ?? 0,
                delivery_discount_amount: window.checkoutData?.deliveryDiscountAmount ?? 0,
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
            if (typeof window.checkoutData.deliveryFeeLocked === 'undefined') {
                window.checkoutData.deliveryFeeLocked = false;
            }
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
        const previousFreteCalculado = window.checkoutData?.freteCalculado === true;
        const previousDeliveryFeeLocked = window.checkoutData?.deliveryFeeLocked === true;
        const previousData = window.checkoutData || {};
        window.checkoutData = {
            ...previousData,
            subtotal: Number(currentSubtotal),
            deliveryFee: Number(currentDeliveryFee),
            baseDeliveryFee: baseDeliveryFee,
            deliveryDiscountPercent: deliveryDiscountPercent,
            deliveryDiscountAmount: deliveryDiscountAmount,
            couponDiscount: Number(couponDiscount),
            cashbackUsed: Number(cashbackUsed),
            cashbackEarned: Number(cashbackEarned),
            total: Number(total),
            freteCalculado: previousFreteCalculado,
            deliveryFeeLocked: previousDeliveryFeeLocked
        };
        
        // Atualizar campos hidden do formulário com dados de desconto de frete
        document.getElementById('hidden_delivery_fee').value = Number(currentDeliveryFee).toFixed(2);
        document.getElementById('hidden_base_delivery_fee').value = Number(baseDeliveryFee || currentDeliveryFee).toFixed(2);
        document.getElementById('hidden_delivery_discount_percent').value = Number(deliveryDiscountPercent || 0).toFixed(0);
        document.getElementById('hidden_delivery_discount_amount').value = Number(deliveryDiscountAmount || 0).toFixed(2);
        document.getElementById('hidden_delivery_fee_locked').value = window.checkoutData.deliveryFeeLocked ? 1 : 0;
        
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

    if (window.checkoutData?.manualAddressPending) {
        console.log('loadCustomerAddress: Ignorando porque manualAddressPending está ativo');
        return;
    }

    const normalizedPhone = phone ? String(phone).replace(/\D/g, '') : '';
    const normalizedEmail = email ? String(email).trim() : '';

    console.log('loadCustomerAddress: Buscando cliente', { 
        phone: normalizedPhone, 
        email: normalizedEmail,
        lastLookupPhone: window.checkoutData.lastLookupPhone,
        lastLookupEmail: window.checkoutData.lastLookupEmail
    });

    // Verificar se já foi buscado recentemente (evitar múltiplas chamadas)
    if (window.checkoutData.lastLookupPhone === normalizedPhone && 
        window.checkoutData.lastLookupEmail === normalizedEmail &&
        normalizedPhone.length >= 10) {
        console.log('loadCustomerAddress: Busca já realizada para estes dados, ignorando nova chamada');
        return;
    }

    // Atualizar flags antes da busca
    window.checkoutData.lastLookupPhone = normalizedPhone;
    window.checkoutData.lastLookupEmail = normalizedEmail;

    try {
        const lookupPayload = {};
        if (normalizedPhone.length >= 10) {
            lookupPayload.phone = normalizedPhone;
        }
        if (normalizedEmail !== '') {
            lookupPayload.email = normalizedEmail;
        }

        if (Object.keys(lookupPayload).length === 0) {
            console.log('loadCustomerAddress: Nenhum dado válido para busca (telefone precisa ter pelo menos 10 dígitos)');
            return;
        }

        console.log('loadCustomerAddress: Enviando requisição para lookup-customer', lookupPayload);
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const lookupResponse = await fetch('{{ route("pedido.checkout.lookup-customer") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            },
            body: JSON.stringify(lookupPayload),
        });

        console.log('loadCustomerAddress: Resposta recebida', {
            status: lookupResponse.status,
            ok: lookupResponse.ok
        });

        if (lookupResponse.ok) {
            const lookupData = await lookupResponse.json();
            console.log('loadCustomerAddress: Dados recebidos', lookupData);
            if (lookupData?.success && lookupData?.data?.prefill) {
                console.log('loadCustomerAddress: Prefill recebido do lookup-customer', lookupData.data.prefill);
                applyCustomerPrefill(lookupData.data.prefill, {
                    force: true,
                    triggerFrete: true,
                    runSummaryUpdate: false,
                });
                window.checkoutData.currentCustomerId = lookupData.data.customer?.id ?? null;
            } else {
                console.log('loadCustomerAddress: Resposta OK mas sem prefill', lookupData);
            }
        } else if (lookupResponse.status === 404) {
            console.log('loadCustomerAddress: Cliente não encontrado (404)');
        } else {
            console.warn('loadCustomerAddress: lookup-customer retornou status', lookupResponse.status);
            const errorData = await lookupResponse.json().catch(() => ({}));
            console.warn('loadCustomerAddress: Erro detalhado', errorData);
        }
    } catch (lookupError) {
        console.error('loadCustomerAddress: Erro ao consultar lookup-customer', lookupError);
    }
    
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
            let updatedIdentity = false;

            if (customer.name) {
                const nameField = document.getElementById('customer_name');
                if (nameField && (!nameField.value.trim() || nameField.dataset.autofilled !== 'true')) {
                    nameField.value = customer.name;
                    nameField.dataset.autofilled = 'true';
                    updatedIdentity = true;
                }
            }

            if (customer.email) {
                const emailField = document.getElementById('customer_email');
                if (emailField && (!emailField.value.trim() || emailField.dataset.autofilled !== 'true')) {
                    emailField.value = customer.email;
                    emailField.dataset.autofilled = 'true';
                    updatedIdentity = true;
                }
            }

            if (customer.phone) {
                const phoneField = document.getElementById('customer_phone');
                if (phoneField && (!phoneField.value.trim() || phoneField.dataset.autofilled !== 'true')) {
                    phoneField.value = customer.phone;
                    phoneField.dataset.autofilled = 'true';
                    updatedIdentity = true;
                }
            }
            
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
                        window.checkoutData.deliveryFeeLocked = false;
                            window.checkoutData.deliveryFeeLocked = false;
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
                            window.checkoutData.deliveryFeeLocked = false;
                            if (typeof buscarCep === 'function') {
                                await buscarCep();
                            }
                        }
                    }
                }, 700);
            }
            
            if (filledAny || updatedIdentity) {
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

document.getElementById('customer_phone')?.addEventListener('input', function() {
    window.checkoutData.lastLookupPhone = null;
    window.checkoutData.currentCustomerId = null;
});

zipCodeManualButton?.addEventListener('click', async function() {
    if (window.checkoutData.manualLocateInFlight) {
        return;
    }

    if (manualLookupDebounceId) {
        clearTimeout(manualLookupDebounceId);
        manualLookupDebounceId = null;
    }

    const addressField = document.getElementById('address');
    const numberField = document.getElementById('number');
    const neighborhoodField = document.getElementById('neighborhood');
    const cityField = document.getElementById('city');
    const stateField = document.getElementById('state');
    const cepFeedbackEl = document.getElementById('cepFeedback');

    const street = addressField?.value.trim() || '';
    const number = numberField?.value.trim() || '';
    const neighborhood = neighborhoodField?.value.trim() || '';
    const city = cityField?.value.trim() || '';
    const state = stateField?.value.trim().toUpperCase() || '';

    if (!street || !number || !city || !state) {
        if (cepFeedbackEl) {
            cepFeedbackEl.textContent = 'Preencha rua, número, cidade e estado para localizar o endereço.';
            cepFeedbackEl.className = 'text-xs text-red-500 mt-1';
        }
        setAddressErrorState(true, {
            fields: ['address', 'number', 'city', 'state'],
            focusFieldId: !street ? 'address' : (!number ? 'number' : (!city ? 'city' : 'state')),
            placeholderMessage: 'Preencha este campo para localizar o endereço'
        });
        window.checkoutData.manualLocateInFlight = false;
        return;
    }

    window.checkoutData.manualLocateInFlight = true;

    const customerPhone = document.getElementById('customer_phone')?.value || '';
    const customerEmail = document.getElementById('customer_email')?.value || '';

    try {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const response = await fetch('{{ route("pedido.checkout.locate-address") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                street,
                number,
                complement: document.getElementById('complement')?.value || '',
                neighborhood,
                city,
                state,
                zip_code: zipCodeInput?.value || '',
                customer_phone: customerPhone,
                customer_email: customerEmail
            }),
        });

        const data = await response.json();
        console.log('Localizar endereço (manual):', data);

        if (response.ok && data.success && data.delivery_fee !== undefined) {
            await handleDeliveryFeeSuccess(data, {
                successMessage: 'Endereço localizado com sucesso!'
            });
            window.checkoutData.manualAddressPending = false;
            window.checkoutData.manualOriginalZip = null;
            if (data.resolved_zip_code) {
                const resolvedDigits = String(data.resolved_zip_code).replace(/\D/g, '');
                if (resolvedDigits.length === 8) {
                    window.checkoutData.manualGeneralizedZip = resolvedDigits;
                }
            }
            clearAddressErrorState();
        } else {
            const message = data.message || 'Não foi possível localizar o endereço informado. Verifique os dados.';
            if (window.checkoutData.freteCalculado) {
                clearAddressErrorState();
                toggleZipCodeManualMode(false, { focusZip: false });
                if (cepFeedbackEl) {
                    cepFeedbackEl.textContent = '';
                    cepFeedbackEl.className = '';
                }
                window.checkoutData.manualAddressPending = false;
            } else {
                if (cepFeedbackEl) {
                    cepFeedbackEl.textContent = message;
                    cepFeedbackEl.className = 'text-xs text-red-500 mt-1';
                }
                window.checkoutData.freteCalculado = false;
                window.checkoutData.deliveryFeeLocked = false;
                window.checkoutData.allowFinalizeWithoutFrete = true;
                window.checkoutData.manualFreteReason = message;
                const wasPendingManual = window.checkoutData.manualAddressPending === true;
                window.checkoutData.manualAddressPending = true;
                toggleZipCodeManualMode(true, { reason: message });
                generalizeZipCode({
                    forceClear: !wasPendingManual,
                    ensureEditable: true,
                    feedbackMessage: message,
                    feedbackClass: 'text-xs text-red-500 mt-1'
                });
                setAddressErrorState(true, {
                    fields: ['address', 'number', 'city', 'state'],
                    focusFieldId: 'address'
                });
                updateFinalizeButtonState();
            }
        }
    } catch (error) {
        console.error('Erro ao localizar endereço manualmente:', error);
        if (window.checkoutData.freteCalculado) {
            clearAddressErrorState();
            toggleZipCodeManualMode(false, { focusZip: false });
            if (cepFeedbackEl) {
                cepFeedbackEl.textContent = '';
                cepFeedbackEl.className = '';
            }
            window.checkoutData.manualAddressPending = false;
        } else {
            if (cepFeedbackEl) {
                cepFeedbackEl.textContent = 'Erro ao localizar o endereço. Tente novamente.';
                cepFeedbackEl.className = 'text-xs text-red-500 mt-1';
            }
            window.checkoutData.freteCalculado = false;
            window.checkoutData.deliveryFeeLocked = false;
            window.checkoutData.allowFinalizeWithoutFrete = true;
            window.checkoutData.manualFreteReason = 'Erro ao localizar o endereço. Confirme os dados e tente novamente.';
            window.checkoutData.manualAddressPending = true;
            toggleZipCodeManualMode(true, { reason: 'Erro ao localizar o endereço. Informe os dados completos.' });
            generalizeZipCode({
                ensureEditable: true,
                feedbackMessage: 'Erro ao localizar o endereço. Informe os dados completos.',
                feedbackClass: 'text-xs text-red-500 mt-1'
            });
            updateFinalizeButtonState();
        }
    }
    window.checkoutData.manualLocateInFlight = false;
});

(async () => {
    const phoneInput = document.getElementById('customer_phone');
    const emailInput = document.getElementById('customer_email');

    if (phoneInput && phoneInput.value.trim()) {
        await loadCustomerAddress(phoneInput.value, emailInput?.value || '');
        updateOrderSummary();
    } else if (phoneInput) {
        setTimeout(() => {
            if (!phoneInput.value.trim()) {
                phoneInput.focus();
            }
        }, 150);
    }
})();

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
        cepFeedback: !!cepFeedback,
        autoLookupEnabled: window.checkoutData?.autoLookupEnabled,
        skipAutoCepLookup: window.checkoutData?.skipAutoCepLookup
    });

    if (!zipCodeInput) {
        console.warn('CEP: Campo zip_code não encontrado');
        return;
    }
    
    // Garantir que autoLookupEnabled está habilitado por padrão
    if (window.checkoutData) {
        window.checkoutData.autoLookupEnabled = window.checkoutData.autoLookupEnabled !== false;
        console.log('CEP: autoLookupEnabled inicializado como:', window.checkoutData.autoLookupEnabled);
    }
    
    let timeoutId = null;

    function shouldSkipCepLookup() {
        const now = Date.now();

        if (window.checkoutData.skipAutoCepLookup) {
            window.checkoutData.skipAutoCepLookup = false;
            return true;
        }

        if (window.checkoutData.autoLookupEnabled === false) {
            return true;
        }

        if (now < (window.checkoutData.skipLookupUntil || 0)) {
            return true;
        }

        return false;
    }
    
    // Formatação automática do CEP
    zipCodeInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length > 5) {
            value = value.substring(0, 5) + '-' + value.substring(5, 8);
        }
        e.target.value = value;
        clearAddressErrorState();
        window.checkoutData.allowFinalizeWithoutFrete = false;
        window.checkoutData.manualFreteReason = null;
        if (!zipCodeManualButton?.classList.contains('hidden')) {
            toggleZipCodeManualMode(false);
        }

        const skipLookup = shouldSkipCepLookup();
        if (skipLookup) {
            console.log('CEP input: Busca pulada por shouldSkipCepLookup');
            return;
        }

        // Garantir que autoLookupEnabled está habilitado
        if (window.checkoutData.autoLookupEnabled === false) {
            console.log('CEP input: Habilitando autoLookupEnabled');
            window.checkoutData.autoLookupEnabled = true;
        }
        
        // Limpar timeout anterior
        if (timeoutId) {
            clearTimeout(timeoutId);
        }
        
        // Validação em tempo real do formato
        const cepDigits = value.replace(/\D/g, '');
        
        if (cepDigits.length > 0 && cepDigits.length < 8) {
            setCepFeedback(`Digite mais ${8 - cepDigits.length} dígito(s)`, 'text-xs text-gray-500 mt-1');
            zipCodeInput.classList.remove('border-red-500', 'border-green-400', 'ring-2', 'ring-red-200', 'ring-green-200');
        } else if (cepDigits.length === 0) {
            setCepFeedback('', '');
            zipCodeInput.classList.remove('border-red-500', 'border-green-400', 'ring-2', 'ring-red-200', 'ring-green-200');
        }
        
        // Buscar automaticamente quando CEP tiver 8 dígitos (após 500ms de inatividade)
        if (cepDigits.length === 8) {
            // Verificar se deve pular a busca
            if (shouldSkipCepLookup()) {
                console.log('CEP: Busca automática pulada devido a shouldSkipCepLookup');
                return;
            }
            
            // Verificar se autoLookupEnabled está desabilitado
            if (window.checkoutData.autoLookupEnabled === false) {
                console.log('CEP: Busca automática desabilitada (autoLookupEnabled = false)');
                return;
            }
            
            // Marcar que o frete ainda não foi calculado
            window.checkoutData.freteCalculado = false;
            window.checkoutData.deliveryFeeLocked = false;
            updateFinalizeButtonState();
            
            console.log('CEP: Agendando busca automática em 500ms');
            timeoutId = setTimeout(() => {
                console.log('CEP: Executando busca automática agendada');
                buscarCep();
            }, 500);
        } else if (cepDigits.length !== 8) {
            setCepFeedback('', '');
            showCepLoading(false);
            window.checkoutData.freteCalculado = false;
            window.checkoutData.deliveryFeeLocked = false;
            window.checkoutData.allowFinalizeWithoutFrete = false;
            window.checkoutData.manualFreteReason = null;
            updateFinalizeButtonState();
            clearAddressErrorState();
        }
    });
    
    // Cache de CEPs consultados (localStorage)
    const CEP_CACHE_KEY = 'olika_cep_cache';
    const CEP_CACHE_TTL = 7 * 24 * 60 * 60 * 1000; // 7 dias
    
    function getCepCache() {
        try {
            const cached = localStorage.getItem(CEP_CACHE_KEY);
            if (!cached) return {};
            const data = JSON.parse(cached);
            const now = Date.now();
            // Limpar entradas expiradas
            Object.keys(data).forEach(cep => {
                if (now - data[cep].timestamp > CEP_CACHE_TTL) {
                    delete data[cep];
                }
            });
            return data;
        } catch (e) {
            return {};
        }
    }
    
    function setCepCache(cep, addressData) {
        try {
            const cache = getCepCache();
            cache[cep] = {
                data: addressData,
                timestamp: Date.now()
            };
            localStorage.setItem(CEP_CACHE_KEY, JSON.stringify(cache));
        } catch (e) {
            console.warn('Erro ao salvar cache de CEP:', e);
        }
    }
    
    function getCachedCep(cep) {
        const cache = getCepCache();
        return cache[cep]?.data || null;
    }
    
    function showCepLoading(show = true) {
        const spinner = document.getElementById('cepLoadingSpinner');
        if (spinner) {
            spinner.classList.toggle('hidden', !show);
        }
        if (zipCodeInput) {
            zipCodeInput.classList.toggle('border-blue-400', show);
            zipCodeInput.classList.toggle('ring-2', show);
            zipCodeInput.classList.toggle('ring-blue-200', show);
        }
    }
    
    function preencherEndereco(data) {
        if (data.logradouro) {
            const addressField = document.getElementById('address');
            if (addressField) {
                addressField.value = data.logradouro;
                addressField.removeAttribute('readonly');
                addressField.required = true;
            }
        }
        if (data.bairro) {
            const neighborhoodField = document.getElementById('neighborhood');
            if (neighborhoodField) {
                neighborhoodField.value = data.bairro;
                neighborhoodField.removeAttribute('readonly');
                neighborhoodField.required = true;
            }
        }
        if (data.localidade) {
            const cityField = document.getElementById('city');
            if (cityField) {
                cityField.value = data.localidade;
                cityField.removeAttribute('readonly');
                cityField.required = true;
            }
        }
        if (data.uf) {
            const stateField = document.getElementById('state');
            if (stateField) {
                stateField.value = data.uf.toUpperCase();
                stateField.removeAttribute('readonly');
                stateField.required = true;
            }
        }
    }
    
    async function buscarCepViaAPI(cep, api = 'viacep') {
        const apis = {
            viacep: `https://viacep.com.br/ws/${cep}/json/`,
            brasilapi: `https://brasilapi.com.br/api/cep/v1/${cep}`
        };
        
        const url = apis[api] || apis.viacep;
        
        try {
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            
            const data = await response.json();
            
            // Normalizar resposta da BrasilAPI para formato ViaCEP
            if (api === 'brasilapi' && data) {
                return {
                    logradouro: data.street || '',
                    bairro: data.neighborhood || data.district || '',
                    localidade: data.city || '',
                    uf: data.state || '',
                    erro: false
                };
            }
            
            return data;
        } catch (error) {
            console.warn(`Erro ao buscar CEP na API ${api}:`, error);
            throw error;
        }
    }
    
    async function buscarCep() {
        const cep = zipCodeInput.value.replace(/\D/g, '');
        console.log('buscarCep: Iniciando busca para CEP:', cep);
        clearAddressErrorState();
        showCepLoading(false);

        // Validação prévia do formato
        if (cep.length !== 8) {
            if (cep.length > 0) {
                setCepFeedback('Digite um CEP com 8 dígitos', 'text-xs text-red-500 mt-1');
                zipCodeInput.classList.add('border-red-500', 'ring-2', 'ring-red-200');
            }
            window.checkoutData.autoLookupEnabled = false;
            window.checkoutData.skipLookupUntil = Date.now() + 2000;
            window.checkoutData.freteCalculado = false;
            window.checkoutData.deliveryFeeLocked = false;
            updateFinalizeButtonState();
            return;
        }
        
        // Verificar cache primeiro
        const cached = getCachedCep(cep);
        if (cached && !cached.erro) {
            console.log('CEP encontrado no cache:', cached);
            preencherEndereco(cached);
            await calcularFreteAposCep(cep, false);
            return;
        }
        
        // Marcar que está buscando CEP e frete ainda não calculado
        window.checkoutData.freteCalculado = false;
        window.checkoutData.deliveryFeeLocked = false;
        updateFinalizeButtonState();
        
        setCepFeedback('Buscando endereço...', 'text-xs text-blue-600 mt-1');
        showCepLoading(true);
        
        let data = null;
        let viaCepFound = false;
        
        try {
            window.checkoutData.autoLookupEnabled = false;
            
            // Tentar ViaCEP primeiro
            try {
                data = await buscarCepViaAPI(cep, 'viacep');
                viaCepFound = data && !data.erro;
                
                // Salvar no cache se encontrado
                if (viaCepFound) {
                    setCepCache(cep, data);
                }
            } catch (viaCepError) {
                console.warn('ViaCEP falhou, tentando BrasilAPI:', viaCepError);
                
                // Fallback para BrasilAPI
                try {
                    data = await buscarCepViaAPI(cep, 'brasilapi');
                    viaCepFound = data && !data.erro;
                    
                    if (viaCepFound) {
                        setCepCache(cep, data);
                    }
                } catch (brasilApiError) {
                    console.error('BrasilAPI também falhou:', brasilApiError);
                    throw new Error('Não foi possível consultar o CEP. Verifique sua conexão.');
                }
            }
            
            window.checkoutData.autoLookupEnabled = true;
            window.checkoutData.skipLookupUntil = Date.now() + 1500;
            
            const requiredFields = ['address', 'neighborhood', 'city', 'state'];
            const originalCepDigits = zipCodeInput.value.replace(/\D/g, '');
            let manualEntryRequired = false;

            if (viaCepFound) {
                showCepLoading(false);
                setCepFeedback('Endereço encontrado!', 'text-xs text-green-600 mt-1');
                zipCodeInput.classList.remove('border-red-500', 'ring-2', 'ring-red-200');
                zipCodeInput.classList.add('border-green-400', 'ring-2', 'ring-green-200');
                setTimeout(() => {
                    zipCodeInput.classList.remove('border-green-400', 'ring-2', 'ring-green-200');
                }, 2000);
                window.checkoutData.manualAddressPending = false;
                window.checkoutData.manualOriginalZip = null;
                window.checkoutData.manualGeneralizedZip = null;

                preencherEndereco(data);
                
                const missingAutofill = requiredFields.filter(fieldId => {
                    const field = document.getElementById(fieldId);
                    return !field || !field.value.trim();
                });

                if (missingAutofill.length > 0) {
                    setAddressErrorState(true, {
                        fields: missingAutofill,
                        focusFieldId: missingAutofill[0],
                        placeholderMessage: 'Preencha este campo manualmente'
                    });
                    setCepFeedback('Alguns campos não foram preenchidos automaticamente. Complete-os manualmente.', 'text-xs text-yellow-600 mt-1');
                } else {
                    clearAddressErrorState();
                }

                toggleZipCodeManualMode(false, { focusZip: false });
                document.getElementById('btn-manual-address')?.classList.add('hidden');
                window.checkoutData.allowFinalizeWithoutFrete = false;
                window.checkoutData.manualFreteReason = null;
            } else {
                showCepLoading(false);
                manualEntryRequired = true;
                const wasAlreadyPending = window.checkoutData.manualAddressPending === true;
                window.checkoutData.manualAddressPending = true;
                window.checkoutData.manualOriginalZip = originalCepDigits;
                window.checkoutData.allowFinalizeWithoutFrete = false;
                window.checkoutData.manualFreteReason = 'CEP não encontrado. Informe o endereço completo.';
                
                setCepFeedback('CEP não encontrado. Por favor, preencha o endereço manualmente.', 'text-xs text-yellow-600 mt-1 font-medium');
                zipCodeInput.classList.remove('border-blue-400', 'ring-2', 'ring-blue-200');
                zipCodeInput.classList.add('border-yellow-400', 'ring-2', 'ring-yellow-200');
                
                toggleZipCodeManualMode(true, {
                    reason: 'CEP não encontrado. Informe o endereço completo.',
                    focusZip: false
                });
                
                // Mostrar botão de preenchimento manual
                const btnManual = document.getElementById('btn-manual-address');
                if (btnManual) {
                    btnManual.classList.remove('hidden');
                }

                generalizeZipCode({
                    forceClear: true,
                    ensureEditable: true,
                    feedbackMessage: 'Não encontramos esse CEP. Ajustamos para o CEP geral da região. Informe o endereço completo abaixo.'
                });

                setAddressErrorState(true, {
                    fields: requiredFields,
                    focusFieldId: requiredFields[0],
                    placeholderMessage: 'Digite o endereço completo'
                });

                if (manualFieldsAreComplete()) {
                    scheduleManualLookup();
                }
            }
            
            // Calcular frete após CEP ser encontrado
            await calcularFreteAposCep(cep, manualEntryRequired);
            
        } catch (error) {
            showCepLoading(false);
            console.error('Erro ao buscar CEP:', error);
            
            const errorMessage = error.message || 'Erro ao consultar CEP. Verifique sua conexão e tente novamente.';
            setCepFeedback(errorMessage, 'text-xs text-red-500 mt-1 font-medium');
            zipCodeInput.classList.add('border-red-500', 'ring-2', 'ring-red-200');
            
            window.checkoutData.freteCalculado = false;
            window.checkoutData.deliveryFeeLocked = false;
            window.checkoutData.allowFinalizeWithoutFrete = true;
            window.checkoutData.manualFreteReason = 'Erro ao consultar CEP. Informe o endereço completo manualmente.';
            window.checkoutData.autoLookupEnabled = false;
            window.checkoutData.skipLookupUntil = Date.now() + 2000;
            
            toggleZipCodeManualMode(true, { 
                reason: 'Erro ao consultar CEP. Informe o endereço completo.',
                focusZip: false 
            });
            
            const btnManual = document.getElementById('btn-manual-address');
            if (btnManual) {
                btnManual.classList.remove('hidden');
            }
            
            generalizeZipCode({
                ensureEditable: true,
                feedbackMessage: 'Erro ao consultar CEP. Informe o endereço completo abaixo.',
                feedbackClass: 'text-xs text-red-500 mt-1'
            });
            
            setAddressErrorState(true, {
                fields: ['address', 'neighborhood', 'city', 'state'],
                focusFieldId: 'address',
                placeholderMessage: 'Digite o endereço completo'
            });
            
            updateFinalizeButtonState();
        }
    }
    
    async function calcularFreteAposCep(cep, manualEntryRequired) {
        try {
            const zipcodeDigits = cep.replace(/\D/g, '');
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
                    const successMessage = manualEntryRequired
                        ? 'Frete calculado! Complete o endereço manualmente.'
                        : 'Frete calculado com sucesso!';

                    await handleDeliveryFeeSuccess(feeData, {
                        successMessage
                    });

                    if (manualEntryRequired) {
                        const manualMissingAfter = requiredFields.filter(fieldId => {
                            const field = document.getElementById(fieldId);
                            return !field || !field.value.trim();
                        });

                        if (manualMissingAfter.length > 0) {
                            setAddressErrorState(true, {
                                fields: manualMissingAfter,
                                focusFieldId: manualMissingAfter[0],
                                placeholderMessage: 'Preencha este campo manualmente'
                            });
                            setCepFeedback('Informações de endereço pendentes. Complete os campos para finalizar.', 'text-xs text-yellow-600 mt-1');
                            setSummaryDeliveryFeePending();
                        } else {
                            clearAddressErrorState();
                        }
                    } else {
                        clearAddressErrorState();
                    }
                } else {
                    console.error('Erro ao calcular frete:', feeData.message || 'Resposta inválida');
                    window.checkoutData.freteCalculado = false;
                    window.checkoutData.deliveryFeeLocked = false;
                    window.checkoutData.allowFinalizeWithoutFrete = true;
                    window.checkoutData.manualFreteReason = feeData.message || 'Não foi possível calcular o frete automaticamente.';
                    window.checkoutData.autoLookupEnabled = false;
                    window.checkoutData.skipLookupUntil = Date.now() + 2000;
                    const manualFields = ['address', 'neighborhood', 'city', 'state'];
                    const errorMessage = feeData.message || 'Não foi possível calcular o frete. Digite o endereço completo.';
                    setCepFeedback(errorMessage, 'text-xs text-red-500 mt-1 font-medium');
                    toggleZipCodeManualMode(true, { reason: errorMessage });
                    window.checkoutData.manualAddressPending = true;
                    window.checkoutData.manualOriginalZip = cep;
                    
                    const btnManual = document.getElementById('btn-manual-address');
                    if (btnManual) {
                        btnManual.classList.remove('hidden');
                    }
                    
                    generalizeZipCode({
                        ensureEditable: true,
                        feedbackMessage: errorMessage,
                        feedbackClass: 'text-xs text-red-500 mt-1'
                    });
                    setAddressErrorState(true, {
                        fields: feeData.error_code === 'missing_api_key' ? ADDRESS_FIELD_IDS : manualFields,
                        focusFieldId: feeData.error_code === 'missing_api_key' ? 'zip_code' : manualFields[0],
                        placeholderMessage: 'Digite o endereço completo'
                    });
                    const summaryDeliveryFeeEl = document.getElementById('summaryDeliveryFee');
                    if (summaryDeliveryFeeEl) {
                        summaryDeliveryFeeEl.textContent = 'Combinar com a loja';
                        summaryDeliveryFeeEl.className = 'text-sm text-red-600 font-medium';
                    }
                    document.getElementById('hidden_delivery_fee').value = '0.00';
                    document.getElementById('hidden_base_delivery_fee').value = '0.00';
                    document.getElementById('hidden_delivery_discount_percent').value = '0';
                    document.getElementById('hidden_delivery_discount_amount').value = '0.00';
                    document.getElementById('hidden_delivery_fee_locked').value = 0;
                    updateFinalizeButtonState();
                }
            } catch (error) {
                console.error('Erro ao calcular frete:', error);
                if (window.checkoutData.freteCalculado === true) {
                    console.warn('Frete já foi calculado com sucesso anteriormente. Mantendo estado atual.', error);
                    window.checkoutData.autoLookupEnabled = true;
                    window.checkoutData.skipLookupUntil = Date.now() + 1500;
                    window.checkoutData.skipAutoCepLookup = true;
                    setCepFeedback('', '');
                    toggleZipCodeManualMode(false);
                    updateFinalizeButtonState();
                    return;
                }

                window.checkoutData.freteCalculado = false;
                window.checkoutData.deliveryFeeLocked = false;
                window.checkoutData.allowFinalizeWithoutFrete = true;
                window.checkoutData.manualFreteReason = 'Erro ao calcular o frete automaticamente. Confirme o endereço e combine a taxa com a loja.';
                window.checkoutData.autoLookupEnabled = false;
                window.checkoutData.skipLookupUntil = Date.now() + 2000;
                setCepFeedback('Erro ao calcular frete. Tente novamente.', 'text-xs text-red-500 mt-1');
                toggleZipCodeManualMode(true, { reason: 'Erro ao calcular o frete automaticamente. Informe o endereço completo.' });
                window.checkoutData.manualAddressPending = true;
                window.checkoutData.manualOriginalZip = cep;
                
                const btnManual = document.getElementById('btn-manual-address');
                if (btnManual) {
                    btnManual.classList.remove('hidden');
                }
                
                generalizeZipCode({
                    ensureEditable: true,
                    feedbackMessage: 'Erro ao calcular o frete automaticamente. Informe o endereço completo.',
                    feedbackClass: 'text-xs text-red-500 mt-1'
                });
                setAddressErrorState(true, {
                    fields: ['zip_code', 'address', 'neighborhood', 'city', 'state'],
                    focusFieldId: 'address',
                    placeholderMessage: 'Digite o endereço completo'
                });
                const summaryDeliveryFeeEl = document.getElementById('summaryDeliveryFee');
                if (summaryDeliveryFeeEl) {
                    summaryDeliveryFeeEl.textContent = 'Combinar com a loja';
                    summaryDeliveryFeeEl.className = 'text-sm text-red-600 font-medium';
                }
                document.getElementById('hidden_delivery_fee').value = '0.00';
                document.getElementById('hidden_base_delivery_fee').value = '0.00';
                document.getElementById('hidden_delivery_discount_percent').value = '0';
                document.getElementById('hidden_delivery_discount_amount').value = '0.00';
                document.getElementById('hidden_delivery_fee_locked').value = 0;
                updateFinalizeButtonState();
            }
            
            // Focar no campo número
            document.getElementById('number').focus();
            
        } catch (error) {
            console.error('Erro ao buscar CEP:', error);
            cepFeedback.textContent = 'Erro ao buscar CEP. Tente novamente.';
            cepFeedback.className = 'text-xs text-red-500 mt-1';
            toggleZipCodeManualMode(true, { reason: 'Não foi possível consultar o CEP. Informe o endereço completo.' });
            setAddressErrorState(true, {
                fields: ADDRESS_FIELD_IDS,
                focusFieldId: 'zip_code'
            });
            window.checkoutData.autoLookupEnabled = false;
            window.checkoutData.skipLookupUntil = Date.now() + 2000;
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
        window.checkoutData.deliveryFeeLocked = false;
        
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
                window.checkoutData.deliveryFeeLocked = false;
                
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
        if (cep.length === 8 && !shouldSkipCepLookup()) {
            console.log('CEP blur: Chamando buscarCep');
            buscarCep();
        } else if (cep.length > 0 && cep.length < 8) {
            setCepFeedback('CEP incompleto. Digite 8 dígitos.', 'text-xs text-red-500 mt-1');
            zipCodeInput.classList.add('border-red-500', 'ring-2', 'ring-red-200');
        }
    });
    
    // Buscar quando o campo mudar (change) - disparado quando navegador preenche automaticamente
    zipCodeInput.addEventListener('change', function() {
        const cep = this.value.replace(/\D/g, '');
        console.log('CEP change: Verificando CEP', cep);
        if (cep.length === 8 && !window.checkoutData.freteCalculado && !shouldSkipCepLookup()) {
            console.log('CEP change: Chamando buscarCep (CEP completo e frete não calculado)');
            buscarCep();
        }
    });
    
    // Verificar quando o campo recebe foco e já tem valor completo
    zipCodeInput.addEventListener('focus', function() {
        const cep = this.value.replace(/\D/g, '');
        console.log('CEP focus: Verificando CEP', cep);
        if (cep.length === 8 && !window.checkoutData.freteCalculado && !shouldSkipCepLookup()) {
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
                window.checkoutData.deliveryFeeLocked = false;
                // Aguardar um pouco para garantir que outros scripts já inicializaram
                setTimeout(() => {
                    if (typeof buscarCep === 'function') {
                        buscarCep();
                    } else {
                        console.error('CEP: buscarCep não está definida ainda!');
                    }
                }, 500);
            } else {
                console.log('CEP ao carregar: CEP completo e endereço já preenchido, mas SEMPRE recalcular frete para garantir valor atualizado');
                // SEMPRE recalcular o frete, mesmo que o endereço já esteja preenchido
                // Isso é necessário porque o frete pode mudar entre pedidos ou o valor pode estar desatualizado
                // Resetar flag para forçar recálculo
                window.checkoutData.freteCalculado = false;
                window.checkoutData.deliveryFeeLocked = false;
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
                                window.checkoutData.deliveryFeeLocked = false;
                                
                                document.getElementById('hidden_delivery_fee').value = deliveryFee.toFixed(2);
                                document.getElementById('hidden_base_delivery_fee').value = baseDeliveryFee.toFixed(2);
                                document.getElementById('hidden_delivery_discount_percent').value = discountPercent.toFixed(0);
                                document.getElementById('hidden_delivery_discount_amount').value = discountAmount.toFixed(2);
                                document.getElementById('hidden_delivery_fee_locked').value = 0;
                                
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
    
    // Botão "Não sei o CEP / Digitar manualmente"
    document.getElementById('btn-manual-address')?.addEventListener('click', function() {
        toggleZipCodeManualMode(true, { 
            reason: 'Preencha o endereço completo abaixo',
            focusZip: false 
        });
        this.classList.add('hidden');
        
        // Limpar CEP se necessário
        if (zipCodeInput) {
            zipCodeInput.value = '';
            zipCodeInput.focus();
        }
        
        // Habilitar campos manualmente
        ensureManualFieldsEditable();
        setCepFeedback('Preencha todos os campos do endereço abaixo', 'text-xs text-blue-600 mt-1');
        
        // Focar no primeiro campo
        const addressField = document.getElementById('address');
        if (addressField) {
            setTimeout(() => addressField.focus(), 100);
        }
    });
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
    const manualFreteAllowed = window.checkoutData.allowFinalizeWithoutFrete === true;
    
    console.log('Submit: Verificando frete', {
        freteCalculado: window.checkoutData.freteCalculado,
        deliveryFeeInDOM: deliveryFeeInDOM,
        deliveryFeeInData: window.checkoutData.deliveryFee,
        deliveryFeeText: deliveryFeeText,
        finalCheck: freteCalculado
    });
    
    if (!freteCalculado && !manualFreteAllowed) {
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
    if (!freteCalculado && manualFreteAllowed) {
        if (!confirm(window.checkoutData.manualFreteReason ? window.checkoutData.manualFreteReason + '\n\nDeseja finalizar mesmo assim?' : 'O frete não foi calculado automaticamente. Confirme o endereço completo e combine a taxa diretamente com a loja. Deseja continuar?')) {
            return false;
        }
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
