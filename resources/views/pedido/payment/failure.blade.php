@extends('pedido.layout')

@section('title', 'Pagamento Não Realizado - Olika')

@section('content')
<div class="max-w-2xl mx-auto text-center">
    <div class="mb-8">
        <div class="w-20 h-20 mx-auto mb-4 bg-red-100 rounded-full flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-red-500">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
        </div>
        <h1 class="text-3xl font-bold mb-2 text-red-600">Pagamento Não Realizado</h1>
        <p class="text-gray-600">Não foi possível processar seu pagamento</p>
    </div>

    @if(isset($order))
    <div class="border rounded-lg p-8 bg-white mb-6">
        <div class="space-y-4">
            <div>
                <p class="text-sm text-gray-600">Número do Pedido</p>
                <p class="text-2xl font-bold text-gray-700">#{{ $order->order_number ?? $order->id }}</p>
            </div>
            
            <div class="border-t pt-4">
                <p class="text-sm text-gray-600 mb-2">Valor</p>
                <p class="text-xl font-semibold">R$ {{ number_format($order->final_amount ?? $order->total ?? 0, 2, ',', '.') }}</p>
            </div>
        </div>
    </div>

    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
        <p class="text-sm text-red-800">
            <strong>O que pode ter acontecido?</strong><br>
            • Dados do cartão inválidos<br>
            • Saldo insuficiente<br>
            • Problemas de conexão<br>
            • Cartão bloqueado ou expirado
        </p>
    </div>
    @endif

    <div class="space-y-3">
        @if(isset($order))
        <a href="{{ route('pedido.payment.pix', $order->id) }}" class="inline-block bg-primary text-primary-foreground px-6 py-3 rounded-lg font-semibold hover:bg-primary/90 transition-colors">
            Tentar Pagamento Novamente
        </a>
        @endif
        <div>
            <a href="{{ route('pedido.cart.index') }}" class="text-gray-600 hover:text-primary">Voltar ao Carrinho</a>
        </div>
        <div>
            <a href="{{ route('pedido.index') }}" class="text-gray-600 hover:text-primary text-sm">Continuar Comprando</a>
        </div>
    </div>

    <div class="mt-8 text-sm text-gray-600">
        <p>Precisa de ajuda? Entre em contato:</p>
        <p class="font-semibold text-primary mt-1">(11) 98765-4321</p>
    </div>
</div>
@endsection
