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
<!-- Header com botão voltar -->
<header class="sticky top-0 z-50 bg-background/95 backdrop-blur-sm border-b border-border">
    <div class="container mx-auto px-4 py-4">
        <a href="{{ route('pedido.cart.index') }}" class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4">
                <path d="m12 19-7-7 7-7"></path>
                <path d="M19 12H5"></path>
            </svg>
            Voltar ao carrinho
        </a>
    </div>
</header>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-5xl mx-auto">
        <h1 class="text-3xl font-serif font-bold text-foreground mb-8">Finalizar Pedido</h1>

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
        
                <div class="grid lg:grid-cols-3 gap-8">
                    <!-- Coluna Esquerda: Formulário -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Informações de Contato -->
                        <div id="addressCard" class="rounded-lg border bg-card text-card-foreground shadow-sm shadow-warm">
                            <div class="flex flex-col space-y-1.5 p-6">
                                <h3 class="text-2xl font-semibold leading-none tracking-tight flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5 text-primary">
                                        <path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"></path>
                                        <circle cx="12" cy="10" r="3"></circle>
                                    </svg>
                                    Informações de Contato
                                </h3>
                            </div>
                            <div class="p-6 pt-0 space-y-4">
                                <div>
                                    <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70" for="customer_name">Nome completo *</label>
                                    <input type="text" name="customer_name" id="customer_name" value="{{ old('customer_name', $prefill['customer_name'] ?? '') }}" required class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm" placeholder="Seu nome">
                                </div>
                                <div>
                                    <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70" for="customer_phone">Telefone *</label>
                                    <input type="tel" name="customer_phone" id="customer_phone" value="{{ old('customer_phone', $prefill['customer_phone'] ?? '') }}" required class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm" placeholder="(00) 00000-0000">
                                </div>
                            </div>
                        </div>

                        <!-- Endereço de Entrega -->
                        <div id="addressCard" class="rounded-lg border bg-card text-card-foreground shadow-sm shadow-warm">
                            <div class="flex flex-col space-y-1.5 p-6">
                                <h3 class="text-2xl font-semibold leading-none tracking-tight flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5 text-primary">
                                        <path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"></path>
                                        <circle cx="12" cy="10" r="3"></circle>
                                    </svg>
                                    Endereço de Entrega
                                </h3>
                            </div>
                            <div class="p-6 pt-0 space-y-4">
                                <!-- CEP -->
                                <div>
                                    <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70" for="zip_code">CEP *</label>
                                    <div class="flex gap-2">
                                        <input type="text" name="zip_code" id="zip_code" value="{{ old('zip_code', $prefill['zip_code'] ?? '') }}" required maxlength="9" pattern="[0-9]{5}-?[0-9]{3}" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm" placeholder="00000-000">
                                        <button type="button" id="zip_code_manual_button" class="hidden px-4 py-2 rounded-md border border-input bg-background text-sm font-medium hover:bg-accent hover:text-accent-foreground transition-colors">
                                            Localizar meu endereço
                                        </button>
                                    </div>
                                    <div id="cepLoadingSpinner" class="hidden mt-2">
                                        <div class="flex items-center gap-2 text-sm text-blue-600">
                                            <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            Buscando endereço...
                                        </div>
                                    </div>
                                    <p id="cepFeedback" class="text-xs mt-1"></p>
                                </div>

                                <!-- Rua/Logradouro -->
                                <div>
                                    <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70" for="address">Rua/Logradouro *</label>
                                    <input type="text" name="street" id="address" value="{{ old('street', $prefill['address'] ?? $prefill['street'] ?? '') }}" required class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm" placeholder="Nome da rua, avenida, etc.">
                                </div>

                                <!-- Número e Complemento -->
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70" for="number">Número *</label>
                                        <input type="text" name="number" id="number" value="{{ old('number', $prefill['number'] ?? '') }}" required class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm" placeholder="123">
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70" for="complement">Complemento</label>
                                        <input type="text" name="complement" id="complement" value="{{ old('complement', $prefill['complement'] ?? '') }}" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm" placeholder="Apto, bloco, etc.">
                                    </div>
                                </div>

                                <!-- Bairro -->
                                <div>
                                    <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70" for="neighborhood">Bairro *</label>
                                    <input type="text" name="neighborhood" id="neighborhood" value="{{ old('neighborhood', $prefill['neighborhood'] ?? '') }}" required class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm" placeholder="Nome do bairro">
                                </div>

                                <!-- Cidade e Estado -->
                                <div class="grid grid-cols-3 gap-4">
                                    <div class="col-span-2">
                                        <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70" for="city">Cidade *</label>
                                        <input type="text" name="city" id="city" value="{{ old('city', $prefill['city'] ?? '') }}" required class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm" placeholder="Nome da cidade">
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70" for="state">Estado *</label>
                                        <input type="text" name="state" id="state" value="{{ old('state', $prefill['state'] ?? '') }}" required maxlength="2" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm uppercase" placeholder="SP">
                                    </div>
                                </div>
                            </div>
                        </div>

                <!-- Observações Gerais do Pedido -->
                <div class="rounded-lg border bg-card text-card-foreground shadow-sm shadow-warm">
                    <div class="flex flex-col space-y-1.5 p-6">
                        <h3 class="text-2xl font-semibold leading-none tracking-tight flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5 text-primary">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                                <line x1="16" x2="8" y1="13" y2="13"></line>
                                <line x1="16" x2="8" y1="17" y2="17"></line>
                                <polyline points="10 9 9 9 8 9"></polyline>
                            </svg>
                            Observações do Pedido
                        </h3>
                    </div>
                    <div class="p-6 pt-0">
                        <div>
                            <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70 mb-2 block" for="orderNotes">Observações gerais (opcional)</label>
                            <textarea name="notes" id="orderNotes" rows="4" maxlength="1000" class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 resize-none" placeholder="Ex: Deixar na portaria, entregar para fulano, etc.">{{ old('notes', '') }}</textarea>
                            <p class="text-xs text-muted-foreground mt-1">Máximo 1000 caracteres</p>
                        </div>
                    </div>
                </div>

                <!-- Cupons -->
                <div class="rounded-lg border bg-card text-card-foreground shadow-sm shadow-warm">
                    <div class="flex flex-col space-y-1.5 p-6">
                        <h3 class="text-2xl font-semibold leading-none tracking-tight flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5 text-primary">
                                <path d="M21.5 2v6h-6M2.5 22v-6h6M2 11.5a10 10 0 0 1 18.8-4.3M22 12.5a10 10 0 0 1-18.8 4.2"></path>
                            </svg>
                            Cupom de Desconto
                        </h3>
                    </div>
                    <div class="p-6 pt-0 space-y-4">
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
                        <div id="couponsAvailableSection">
                            <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70 mb-2 block">Cupons Disponíveis</label>
                            <select name="coupon_code" id="coupon_code_public" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm {{ isset($appliedCouponCode) && $appliedCouponCode ? 'bg-muted cursor-not-allowed' : '' }}" {{ isset($appliedCouponCode) && $appliedCouponCode ? 'disabled' : '' }}>
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
                        <div id="couponsSeparator" class="text-center text-sm text-muted-foreground my-3">ou</div>
                        @else
                        <!-- Esconder se não houver cupons elegíveis -->
                        <style>
                            #couponsAvailableSection { display: none !important; }
                            #couponsSeparator { display: none !important; }
                        </style>
                        @endif
                        <div class="flex gap-3">
                            <input type="text" name="coupon_code" id="coupon_code_private" placeholder="Digite o código do cupom" class="flex-1 flex h-10 rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm {{ isset($appliedCouponCode) && $appliedCouponCode ? 'bg-muted cursor-not-allowed' : '' }}" value="{{ old('coupon_code', isset($appliedCouponCode) ? $appliedCouponCode : '') }}" {{ isset($appliedCouponCode) && $appliedCouponCode ? 'readonly' : '' }}>
                            <button type="button" id="applyCouponBtn" class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2 {{ isset($appliedCouponCode) && $appliedCouponCode ? 'opacity-50 cursor-not-allowed' : '' }}" {{ isset($appliedCouponCode) && $appliedCouponCode ? 'disabled' : '' }}>
                                {{ isset($appliedCouponCode) && $appliedCouponCode ? 'Aplicado' : 'Aplicar' }}
                            </button>
                        </div>
                        @if(isset($appliedCouponCode) && $appliedCouponCode)
                        <p id="couponFeedback" class="text-sm text-green-600 font-medium">✓ Cupom {{ $appliedCouponCode }} aplicado</p>
                        @else
                        <p id="couponFeedback" class="text-sm text-muted-foreground"></p>
                        @endif
                        <input type="hidden" name="applied_coupon_code" id="applied_coupon_code" value="{{ isset($appliedCouponCode) ? $appliedCouponCode : '' }}">
                    </div>
                </div>

                <!-- Agendamento -->
                <div class="rounded-lg border bg-card text-card-foreground shadow-sm shadow-warm">
                    <div class="flex flex-col space-y-1.5 p-6">
                        <h3 class="text-2xl font-semibold leading-none tracking-tight flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5 text-primary">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                            Agendamento de Entrega
                        </h3>
                    </div>
                    <div class="p-6 pt-0 space-y-4">
                        <div>
                            <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70 mb-2 block">Data *</label>
                            <select name="scheduled_delivery_date" id="scheduled_delivery_date" required class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm">
                                <option value="">Selecione uma data</option>
                                @foreach($availableDates ?? [] as $date)
                                <option value="{{ $date['date'] }}" data-day="{{ $date['day_name'] }}">{{ $date['day_name'] }}, {{ $date['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70 mb-2 block">Horário *</label>
                            <select name="scheduled_delivery_slot" id="scheduled_delivery_slot" required class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm disabled:bg-muted" disabled>
                                <option value="">Selecione primeiro uma data</option>
                            </select>
                            <p id="slotError" class="text-xs text-destructive mt-1 hidden">Por favor, selecione um horário de entrega</p>
                        </div>
                    </div>
                </div>
                
                        <!-- Forma de Pagamento -->
                        <div class="rounded-lg border bg-card text-card-foreground shadow-sm shadow-warm">
                            <div class="flex flex-col space-y-1.5 p-6">
                                <h3 class="text-2xl font-semibold leading-none tracking-tight flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5 text-primary">
                                        <rect width="20" height="14" x="2" y="5" rx="2"></rect>
                                        <line x1="2" x2="22" y1="10" y2="10"></line>
                                    </svg>
                                    Forma de Pagamento
                                </h3>
                            </div>
                            <div class="p-6 pt-0">
                                <div role="radiogroup" class="grid gap-2">
                                    <div class="flex items-center space-x-3 p-4 border border-border rounded-lg hover:bg-secondary/50 transition-smooth {{ !isset($order) || ($order->payment_method ?? 'pix') === 'pix' ? 'border-primary bg-primary/5' : '' }}">
                                        <input type="radio" name="payment_method" value="pix" id="payment_pix" class="aspect-square h-4 w-4 rounded-full border border-primary text-primary ring-offset-background focus:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50" {{ !isset($order) || ($order->payment_method ?? 'pix') === 'pix' ? 'checked' : '' }} required>
                                        <label for="payment_pix" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70 flex-1 cursor-pointer">
                                            <div class="font-semibold">PIX</div>
                                            <div class="text-sm text-muted-foreground">Pagamento instantâneo</div>
                                        </label>
                                    </div>
                                    <div class="flex items-center space-x-3 p-4 border border-border rounded-lg hover:bg-secondary/50 transition-smooth {{ isset($order) && ($order->payment_method ?? '') === 'mercadopago' ? 'border-primary bg-primary/5' : '' }}">
                                        <input type="radio" name="payment_method" value="mercadopago" id="payment_mercadopago" class="aspect-square h-4 w-4 rounded-full border border-primary text-primary ring-offset-background focus:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50" {{ isset($order) && ($order->payment_method ?? '') === 'mercadopago' ? 'checked' : '' }}>
                                        <label for="payment_mercadopago" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70 flex-1 cursor-pointer">
                                            <div class="font-semibold">Cartão de Crédito</div>
                                            <div class="text-sm text-muted-foreground">Visa, Mastercard, Elo</div>
                                        </label>
                                    </div>
                                    <div class="flex items-center space-x-3 p-4 border border-border rounded-lg hover:bg-secondary/50 transition-smooth">
                                        <input type="radio" name="payment_method" value="money" id="payment_money" class="aspect-square h-4 w-4 rounded-full border border-primary text-primary ring-offset-background focus:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                                        <label for="payment_money" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70 flex-1 cursor-pointer">
                                            <div class="font-semibold">Dinheiro</div>
                                            <div class="text-sm text-muted-foreground">Pagamento na entrega</div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Coluna Direita: Resumo -->
                    <div class="lg:col-span-1">
                        <div class="rounded-lg border bg-card text-card-foreground shadow-sm sticky top-24 shadow-warm-lg">
                            <div class="flex flex-col space-y-1.5 p-6">
                                <h3 class="text-2xl font-semibold leading-none tracking-tight">Resumo do Pedido</h3>
                            </div>
                            <div class="p-6 pt-0 space-y-4">
                                <!-- Itens do Carrinho -->
                                <div class="space-y-3 max-h-60 overflow-y-auto">
                                    @foreach($cartData['items'] ?? [] as $item)
                                    <div class="flex justify-between text-sm">
                                        <span class="text-muted-foreground">{{ $item['qty'] }}x {{ $item['name'] }}</span>
                                        <span class="font-semibold">R$ {{ number_format($item['subtotal'], 2, ',', '.') }}</span>
                                    </div>
                                    @endforeach
                                </div>

                                <div data-orientation="horizontal" role="none" class="shrink-0 bg-border h-[1px] w-full"></div>

                                <div class="space-y-2">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-muted-foreground">Subtotal</span>
                                        <span id="summarySubtotal" class="font-semibold">R$ {{ number_format($cartData['subtotal'] ?? 0, 2, ',', '.') }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-muted-foreground">Entrega</span>
                                        <span id="summaryDeliveryFee" class="font-semibold text-primary">
                                            @if($initialFreteCalculado && $initialDeliveryFee > 0)
                                                R$ {{ number_format($initialDeliveryFee, 2, ',', '.') }}
                                            @elseif($initialFreteCalculado && $initialDeliveryFee == 0)
                                                Grátis
                                            @else
                                                Aguardando CEP
                                            @endif
                                        </span>
                                    </div>
                                </div>

                                <div data-orientation="horizontal" role="none" class="shrink-0 bg-border h-[1px] w-full"></div>

                                <div class="flex justify-between items-center">
                                    <span class="font-semibold">Total</span>
                                    <span id="summaryTotal" class="text-2xl font-bold text-primary">R$ {{ number_format($cartData['subtotal'] ?? 0, 2, ',', '.') }}</span>
                                </div>

                                <button type="submit" class="inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-11 rounded-md px-8 w-full shadow-warm">
                                    Confirmar Pedido
                                </button>
                                
                                <p class="text-xs text-center text-muted-foreground">Ao confirmar, você concorda com nossos termos de serviço</p>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
    </div>
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

// Formatação automática do CEP
(function() {
    const zipCodeInput = document.getElementById('zip_code');
    if (zipCodeInput) {
        zipCodeInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 5) {
                value = value.substring(0, 5) + '-' + value.substring(5, 8);
            }
            e.target.value = value;
        });
    }

    // Formatação automática do Estado (uppercase)
    const stateInput = document.getElementById('state');
    if (stateInput) {
        stateInput.addEventListener('input', function(e) {
            e.target.value = e.target.value.toUpperCase();
        });
    }
})();
</script>
<script src="{{ asset('js/checkout.js') }}"></script>
@endpush
