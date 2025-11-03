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
                        @if($deliveryFee > 0)
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Taxa de Entrega</span>
                            <span class="font-medium">R$ {{ number_format($deliveryFee, 2, ',', '.') }}</span>
                        </div>
                        @elseif($deliveryFee == 0 && $subtotal > 0)
                        <div class="flex items-center justify-between text-green-700">
                            <span class="text-gray-600">Taxa de Entrega</span>
                            <span class="font-medium">Grátis</span>
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
            <p class="text-sm text-gray-600 mb-4">Após o pagamento, você será redirecionado automaticamente.</p>
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

// Verificar status automaticamente a cada 10 segundos
@if(isset($order))
setInterval(function() {
    fetch('{{ route("pedido.payment.status", $order->id) }}', {headers:{'Accept':'application/json'}})
        .then(response => response.json())
        .then(data => {
            if ((data.payment_status||'').toLowerCase() === 'paid' || (data.payment_status||'').toLowerCase() === 'approved') {
                window.location.href = '{{ route("pedido.payment.success", $order->id) }}';
            }
        })
        .catch(err => console.error('Erro ao verificar status:', err));
}, 10000);
@endif
</script>
@endpush
