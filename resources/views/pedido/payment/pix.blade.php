@extends('pedido.layout')

@section('title', 'Pagamento PIX - Olika')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="text-center mb-8">
        @php
            $logoSquare = null;
            if (\Storage::disk('public')->exists('uploads/branding/logo.png')) { 
                $logoSquare = 'uploads/branding/logo.png'; 
            } elseif (\Storage::disk('public')->exists('uploads/branding/logo.jpg')) { 
                $logoSquare = 'uploads/branding/logo.jpg'; 
            }
        @endphp
        <div class="w-20 h-20 mx-auto mb-4 rounded-full flex items-center justify-center overflow-hidden bg-white shadow-lg">
            @if($logoSquare)
                <img src="{{ asset('storage/'.$logoSquare) }}" alt="Olika" class="w-full h-full object-cover">
            @else
                <div class="w-full h-full bg-primary flex items-center justify-center">
                    <span class="text-4xl font-bold text-primary-foreground">O</span>
                </div>
            @endif
        </div>
        <h1 class="text-3xl font-bold mb-2">Pagamento via PIX</h1>
        <p class="text-gray-600">Escaneie o QR Code ou copie o código PIX</p>
    </div>

    <div class="border rounded-lg p-8 bg-white">
        @if(isset($order))
        <div class="text-center mb-6">
            <p class="text-sm text-gray-600 mb-2">Pedido #{{ $order->order_number ?? $order->id }}</p>
            <p class="text-2xl font-bold text-primary">R$ {{ number_format($order->final_amount ?? $order->total ?? 0, 2, ',', '.') }}</p>
        </div>

        <!-- Resumo do pedido -->
        <div class="mb-6">
            <h2 class="text-lg font-semibold mb-3">Resumo do pedido</h2>
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                <div class="space-y-2">
                    @php
                        $items = $order->items ?? collect();
                        $subtotal = (float) ($order->total_amount ?? $items->sum('total_price'));
                        $deliveryFee = (float) ($order->delivery_fee ?? 0);
                        $couponDiscount = (float) ($order->discount_amount ?? 0);
                        $cashbackUsed = (float) ($order->cashback_used ?? 0);
                        $totalDiscounts = $couponDiscount + $cashbackUsed;
                        $final = (float) ($order->final_amount ?? ($subtotal + $deliveryFee - $totalDiscounts));
                        
                        // Calcular desconto de frete progressivo - APENAS usar dados reais salvos
                        $deliveryDiscountAmount = 0;
                        $deliveryDiscountPercent = 0;
                        $baseDeliveryFee = $deliveryFee; // Valor base para exibição (sem desconto)
                        
                        // Tentar obter do relacionamento OrderDeliveryFee (única fonte confiável)
                        if ($order->orderDeliveryFee) {
                            $calculatedFee = (float) ($order->orderDeliveryFee->calculated_fee ?? 0);
                            $finalFee = (float) ($order->orderDeliveryFee->final_fee ?? $deliveryFee);
                            
                            // Se temos calculated_fee, usar ele como base (mesmo sem desconto)
                            if ($calculatedFee > 0) {
                                $baseDeliveryFee = $calculatedFee;
                            }
                            
                            // Só calcular desconto se houver diferença real entre calculado e final
                            // E se o valor calculado for maior que o final (indicando desconto aplicado)
                            if ($calculatedFee > $finalFee && $calculatedFee > 0 && $finalFee >= 0) {
                                $deliveryDiscountAmount = $calculatedFee - $finalFee;
                                if ($deliveryDiscountAmount > 0.01) { // Só se o desconto for significativo (> 1 centavo)
                                    $deliveryDiscountPercent = round(($deliveryDiscountAmount / $calculatedFee) * 100);
                                    // Quando houver desconto, usar o valor base (calculated_fee) para exibição
                                    $baseDeliveryFee = $calculatedFee;
                                    // O deliveryFee já está correto (final_fee com desconto aplicado)
                                    // e será usado no cálculo do total
                                } else {
                                    $deliveryDiscountAmount = 0;
                                    $deliveryDiscountPercent = 0;
                                }
                            }
                        }
                        
                        // Se baseDeliveryFee ainda é 0 mas deliveryFee não é 0, pode ser um pedido antigo
                        // Nesse caso, usar deliveryFee como base
                        if ($baseDeliveryFee == 0 && $deliveryFee > 0) {
                            $baseDeliveryFee = $deliveryFee;
                        }
                        
                        // NÃO fazer estimativas - apenas usar dados reais salvos
                        // Se não houver dados salvos de desconto, não mostrar desconto
                    @endphp

                    @if($items && count($items))
                        <ul class="divide-y">
                            @foreach($items as $it)
                                <li class="py-2 flex items-center justify-between text-sm">
                                    <div class="flex items-center gap-2">
                                        <span class="font-medium">{{ $it->quantity ?? $it->qty }}x</span>
                                        <span class="text-gray-700">{{ $it->custom_name ?? optional($it->product)->name ?? 'Item' }}</span>
                                    </div>
                                    <div class="text-gray-900 font-medium">R$ {{ number_format((float)($it->total_price ?? (($it->unit_price ?? $it->price ?? 0) * ($it->quantity ?? $it->qty ?? 1))), 2, ',', '.') }}</div>
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    <div class="pt-3 mt-3 border-t space-y-2 text-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Subtotal</span>
                            <span class="font-medium">R$ {{ number_format($subtotal, 2, ',', '.') }}</span>
                        </div>
                        @if($deliveryDiscountAmount > 0 && $baseDeliveryFee > $deliveryFee)
                        {{-- Quando há desconto, mostrar valor original riscado e valor com desconto --}}
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Taxa de Entrega</span>
                            <span class="font-medium line-through text-gray-400">R$ {{ number_format($baseDeliveryFee, 2, ',', '.') }}</span>
                        </div>
                        <div class="flex items-center justify-between text-green-700">
                            <span>Entrega com desconto no frete{{ $deliveryDiscountPercent > 0 ? ' (' . $deliveryDiscountPercent . '%)' : '' }}</span>
                            <span class="font-medium">R$ {{ number_format($deliveryFee, 2, ',', '.') }}</span>
                        </div>
                        @elseif($baseDeliveryFee > 0)
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Taxa de Entrega</span>
                            <span class="font-medium">R$ {{ number_format($baseDeliveryFee, 2, ',', '.') }}</span>
                        </div>
                        @elseif($baseDeliveryFee == 0 && $deliveryFee == 0 && $subtotal > 0)
                        {{-- Só mostrar "Grátis" se realmente confirmado que é grátis --}}
                        @if($order->orderDeliveryFee)
                            @if(($order->orderDeliveryFee->calculated_fee ?? 0) == 0 && ($order->orderDeliveryFee->final_fee ?? 0) == 0)
                            {{-- Confirmado que é grátis pelo OrderDeliveryFee (calculated_fee = 0 e final_fee = 0) --}}
                            <div class="flex items-center justify-between text-green-700">
                                <span class="text-gray-600">Taxa de Entrega</span>
                                <span class="font-medium">Grátis</span>
                            </div>
                            @elseif($order->orderDeliveryFee->is_free_delivery ?? false)
                            {{-- Confirmado que é grátis pela flag is_free_delivery --}}
                            <div class="flex items-center justify-between text-green-700">
                                <span class="text-gray-600">Taxa de Entrega</span>
                                <span class="font-medium">Grátis</span>
                            </div>
                            @endif
                        @else
                        {{-- Não há OrderDeliveryFee - não assumir que é grátis, pode ser pedido antigo sem dados salvos --}}
                        {{-- Não mostrar nada ou mostrar R$ 0,00 se deliveryFee realmente é 0 --}}
                        @if($deliveryFee == 0)
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Taxa de Entrega</span>
                            <span class="font-medium">R$ 0,00</span>
                        </div>
                        @endif
                        @endif
                        @elseif($baseDeliveryFee == 0 && $deliveryFee > 0)
                        {{-- Se baseDeliveryFee é 0 mas deliveryFee não é, mostrar o deliveryFee --}}
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Taxa de Entrega</span>
                            <span class="font-medium">R$ {{ number_format($deliveryFee, 2, ',', '.') }}</span>
                        </div>
                        @endif
                        @if($couponDiscount > 0)
                        <div class="flex items-center justify-between text-green-700">
                            <span>Cupom{{ $order->coupon_code ? ' (' . $order->coupon_code . ')' : '' }}</span>
                            <span class="font-medium">- R$ {{ number_format($couponDiscount, 2, ',', '.') }}</span>
                        </div>
                        @endif
                        @if($cashbackUsed > 0)
                        <div class="flex items-center justify-between text-green-700">
                            <span>Cashback Utilizado</span>
                            <span class="font-medium">- R$ {{ number_format($cashbackUsed, 2, ',', '.') }}</span>
                        </div>
                        @endif
                        <div class="flex items-center justify-between pt-2 mt-1 border-t">
                            <span class="font-semibold">Total</span>
                            <span class="font-bold text-primary">R$ {{ number_format($final, 2, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if(isset($order) && ($order->pix_copy_paste ?? null))
        <!-- QR Code -->
        <div class="mb-6 text-center">
            @if(isset($order->pix_qr_base64) && $order->pix_qr_base64)
            <div class="inline-block p-4 bg-white border-2 border-gray-200 rounded-lg mb-4">
                <img src="data:image/png;base64,{{ $order->pix_qr_base64 }}" alt="QR Code PIX" class="w-64 h-64 mx-auto">
            </div>
            @endif
            
            <!-- Código PIX Copia e Cola -->
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2 text-left">Código PIX (Copia e Cola):</label>
                <div class="flex gap-2">
                    <input type="text" id="pixCode" value="{{ $order->pix_copy_paste ?? '' }}" readonly class="flex-1 border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 text-sm">
                    <button onclick="copyPixCode()" class="bg-primary text-primary-foreground px-4 py-2 rounded-lg hover:bg-primary/90 transition-colors whitespace-nowrap">
                        Copiar
                    </button>
                </div>
            </div>
        </div>

        <div class="bg-primary/10 border border-primary/20 rounded-lg p-4 mb-6">
            <p class="text-sm text-primary/90">
                <strong>Instruções:</strong><br>
                1. Abra o app do seu banco<br>
                2. Escaneie o QR Code ou cole o código PIX<br>
                3. Confirme o pagamento<br>
                4. Aguarde a confirmação (pode levar alguns minutos)
            </p>
        </div>

        <div class="text-center">
            <p class="text-sm text-gray-600 mb-4">Após o pagamento, você será notificado automaticamente.</p>
            <button onclick="checkPaymentStatus()" class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 transition-colors">
                Verificar Status do Pagamento
            </button>
        </div>
        @else
        <div class="text-center py-8">
            <p class="text-gray-600 mb-4">Gerando código PIX...</p>
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
        </div>
        @endif
        @else
        <div class="text-center py-8">
            <p class="text-red-600 mb-4">Pedido não encontrado.</p>
            <a href="{{ route('pedido.index') }}" class="text-primary hover:text-primary/90">Voltar ao início</a>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
function copyPixCode() {
    const pixCode = document.getElementById('pixCode');
    pixCode.select();
    pixCode.setSelectionRange(0, 99999);
    
    try {
        document.execCommand('copy');
        alert('Código PIX copiado!');
    } catch (err) {
        console.error('Erro ao copiar:', err);
    }
}

function checkPaymentStatus() {
    window.location.reload();
}

// Verificar status automaticamente a cada 5 segundos (mais rápido para PIX)
@if(isset($order) && $order->payment_status !== 'paid' && $order->payment_status !== 'approved')
let pollCount = 0;
const maxPolls = 120; // Máximo 10 minutos (120 * 5 segundos)

const paymentPoll = setInterval(function() {
    pollCount++;
    
    // Parar polling se exceder o tempo máximo
    if (pollCount > maxPolls) {
        clearInterval(paymentPoll);
        console.log('Polling interrompido após tempo máximo');
        return;
    }
    
    fetch('{{ route("pedido.payment.status", $order->id) }}', {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro HTTP: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            console.log('Status do pagamento:', data);
            
            if (!data || !data.success) {
                console.warn('Resposta inválida do servidor:', data);
                return;
            }
            
            const paymentStatus = (data.payment_status || '').toLowerCase();
            console.log('Payment status verificado:', paymentStatus);
            
            if (paymentStatus === 'paid' || paymentStatus === 'approved') {
                clearInterval(paymentPoll);
                console.log('Pagamento confirmado! Redirecionando...');
                // Pequeno delay para garantir que tudo foi processado
                setTimeout(() => {
                    window.location.href = '{{ route("pedido.payment.success", $order->id) }}';
                }, 500);
            }
        })
        .catch(err => {
            console.error('Erro ao verificar status:', err);
            // Não interromper polling por erro ocasional, mas logar o erro
            if (err.message) {
                console.error('Detalhes do erro:', err.message);
            }
        });
}, 5000); // 5 segundos
@endif
</script>
@endpush
