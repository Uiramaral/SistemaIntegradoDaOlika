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
// Configurações globais do checkout (passadas do Blade)
window.checkoutConfig = {
    routes: {
        calculateDiscounts: '{{ route("pedido.checkout.calculate-discounts") }}',
        lookupCustomer: '{{ route("pedido.checkout.lookup-customer") }}',
        locateAddress: '{{ route("pedido.checkout.locate-address") }}',
        calculateDeliveryFee: '{{ route("pedido.cart.calculateDeliveryFee") }}'
    },
    initialData: {
        subtotal: {{ isset($cartData['subtotal']) ? round($cartData['subtotal'], 2) : 0 }},
        deliveryFee: {{ $initialFreteCalculado ? round($initialDeliveryFee, 2) : 'null' }},
        baseDeliveryFee: {{ $initialFreteCalculado && $initialBaseDeliveryFee !== null ? round($initialBaseDeliveryFee, 2) : 'null' }},
        deliveryDiscountPercent: {{ $initialFreteCalculado ? round($initialDeliveryDiscountPercent, 2) : 0 }},
        deliveryDiscountAmount: {{ $initialFreteCalculado ? round($initialDeliveryDiscountAmount, 2) : 0 }},
        deliveryFeeLocked: {{ $initialFreteCalculado ? 'true' : 'false' }},
        freteCalculado: {{ $initialFreteCalculado ? 'true' : 'false' }},
        total: {{ isset($cartData['subtotal']) ? round(($cartData['subtotal'] ?? 0) + ($initialDeliveryFee ?? 0) - ($initialDeliveryDiscountAmount ?? 0), 2) : 0 }}
    },
    availableDates: @json($availableDates ?? []),
    appliedCouponCode: '{{ isset($appliedCouponCode) ? $appliedCouponCode : '' }}',
    @if(isset($order) && $order)
    order: {
        id: {{ $order->id }},
        order_number: '{{ $order->order_number }}'
    }
    @endif
};
</script>
<script src="{{ asset('js/checkout.js') }}"></script>
@endpush
