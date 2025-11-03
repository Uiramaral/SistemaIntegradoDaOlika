@extends('pedido.layout')

@section('title', 'Pagamento Confirmado - Olika')

@section('content')
<div class="max-w-2xl mx-auto text-center">
    <div class="mb-8">
        <div class="w-20 h-20 mx-auto mb-4 bg-green-100 rounded-full flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-green-500">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
        </div>
        <h1 class="text-3xl font-bold mb-2 text-green-600">Pagamento Confirmado!</h1>
        <p class="text-gray-600">Seu pedido foi recebido com sucesso</p>
    </div>

    @if(isset($order))
    <div class="border rounded-lg p-8 bg-white mb-6">
        <div class="space-y-4">
            <div>
                <p class="text-sm text-gray-600">Número do Pedido</p>
                <p class="text-2xl font-bold text-primary">#{{ $order->order_number ?? $order->id }}</p>
            </div>
            
            <div class="border-t pt-4">
                <p class="text-sm text-gray-600 mb-2">Valor Pago</p>
                <p class="text-xl font-semibold">R$ {{ number_format($order->final_amount ?? $order->total ?? 0, 2, ',', '.') }}</p>
            </div>

            @if(isset($order->delivery_date) && $order->delivery_date)
            <div class="border-t pt-4">
                <p class="text-sm text-gray-600 mb-2">Previsão de Entrega</p>
                <p class="text-lg font-medium">{{ \Carbon\Carbon::parse($order->delivery_date)->format('d/m/Y') }}</p>
            </div>
            @endif
        </div>
    </div>

    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
        <p class="text-sm text-blue-800">
            <strong>O que acontece agora?</strong><br>
            Você receberá um e-mail de confirmação em breve. Acompanhe o status do seu pedido e receba notificações sobre a entrega.
        </p>
    </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
// Mostrar confetti ou animação de sucesso
if (typeof confetti !== 'undefined') {
    confetti({
        particleCount: 100,
        spread: 70,
        origin: { y: 0.6 }
    });
}
</script>
@endpush
