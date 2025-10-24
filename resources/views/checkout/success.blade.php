@extends('layouts.app')

@section('title', 'Pedido Confirmado - Olika')

@section('content')
<div class="py-8">
    <div class="container">
        <div class="max-w-4xl mx-auto">
            <!-- Success Header -->
            <div class="text-center mb-8">
                <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-check text-green-600 text-3xl"></i>
                </div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">
                    🎉 Pedido Confirmado!
                </h1>
                <p class="text-gray-600">
                    Seu pedido foi recebido e está sendo processado
                </p>
            </div>

            <!-- Order Details -->
            <div class="grid lg:grid-cols-2 gap-8">
                <!-- Order Info -->
                <div class="card">
                    <h2 class="text-xl font-bold text-gray-900 mb-6">
                        <i class="fas fa-receipt mr-2"></i>
                        Detalhes do Pedido
                    </h2>
                    
                    <div class="space-y-4">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Número do Pedido:</span>
                            <span class="font-semibold text-gray-900">#{{ $order->order_number }}</span>
                        </div>
                        
                        <div class="flex justify-between">
                            <span class="text-gray-600">Status:</span>
                            <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm font-medium">
                                {{ $order->status_label }}
                            </span>
                        </div>
                        
                        <div class="flex justify-between">
                            <span class="text-gray-600">Cliente:</span>
                            <span class="font-semibold text-gray-900">{{ $order->customer->name }}</span>
                        </div>
                        
                        <div class="flex justify-between">
                            <span class="text-gray-600">Telefone:</span>
                            <span class="font-semibold text-gray-900">{{ $order->customer->phone }}</span>
                        </div>
                        
                        <div class="flex justify-between">
                            <span class="text-gray-600">Tipo de Entrega:</span>
                            <span class="font-semibold text-gray-900">{{ $order->delivery_type_label }}</span>
                        </div>
                        
                        @if($order->delivery_address)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Endereço:</span>
                            <span class="font-semibold text-gray-900 text-right max-w-xs">
                                {{ $order->delivery_address }}
                                @if($order->delivery_neighborhood)
                                <br><span class="text-sm text-gray-500">{{ $order->delivery_neighborhood }}</span>
                                @endif
                            </span>
                        </div>
                        @endif
                        
                        <div class="flex justify-between">
                            <span class="text-gray-600">Data do Pedido:</span>
                            <span class="font-semibold text-gray-900">
                                {{ $order->created_at->format('d/m/Y H:i') }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Order Items -->
                <div class="card">
                    <h2 class="text-xl font-bold text-gray-900 mb-6">
                        <i class="fas fa-shopping-cart mr-2"></i>
                        Itens do Pedido
                    </h2>
                    
                    <div class="space-y-4">
                        @foreach($order->items as $item)
                        <div class="flex items-center justify-between py-3 border-b border-gray-100 last:border-b-0">
                            <div class="flex items-center space-x-3">
                                <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-utensils text-gray-400"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900">{{ $item->product->name }}</h3>
                                    <p class="text-sm text-gray-600">x{{ $item->quantity }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-gray-900">
                                    R$ {{ number_format($item->total_price, 2, ',', '.') }}
                                </p>
                                <p class="text-sm text-gray-600">
                                    R$ {{ number_format($item->unit_price, 2, ',', '.') }} cada
                                </p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    
                    <!-- Order Total -->
                    <div class="mt-6 pt-4 border-t border-gray-200">
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Subtotal:</span>
                                <span class="font-medium">R$ {{ number_format($order->total_amount, 2, ',', '.') }}</span>
                            </div>
                            
                            <div class="flex justify-between">
                                <span class="text-gray-600">Taxa de entrega:</span>
                                <span class="font-medium">R$ {{ number_format($order->delivery_fee, 2, ',', '.') }}</span>
                            </div>
                            
                            @if($order->discount_amount > 0)
                            <div class="flex justify-between text-green-600">
                                <span>Desconto:</span>
                                <span class="font-medium">-R$ {{ number_format($order->discount_amount, 2, ',', '.') }}</span>
                            </div>
                            @endif
                            
                            <div class="flex justify-between text-lg font-bold pt-2 border-t border-gray-200">
                                <span>Total:</span>
                                <span class="text-orange-600">R$ {{ number_format($order->final_amount, 2, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Info -->
            @if($order->payment_method)
            <div class="card mt-8">
                <h2 class="text-xl font-bold text-gray-900 mb-6">
                    <i class="fas fa-credit-card mr-2"></i>
                    Informações de Pagamento
                </h2>
                
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <div class="flex items-center space-x-3 mb-4">
                            @if($order->payment_method === 'pix')
                            <i class="fas fa-qrcode text-2xl text-green-600"></i>
                            <div>
                                <h3 class="font-semibold text-gray-900">PIX</h3>
                                <p class="text-sm text-gray-600">Pagamento instantâneo</p>
                            </div>
                            @else
                            <i class="fas fa-credit-card text-2xl text-blue-600"></i>
                            <div>
                                <h3 class="font-semibold text-gray-900">Cartão de Crédito</h3>
                                <p class="text-sm text-gray-600">Visa, Mastercard, Elo</p>
                            </div>
                            @endif
                        </div>
                        
                        <div class="flex justify-between">
                            <span class="text-gray-600">Status do Pagamento:</span>
                            <span class="px-3 py-1 rounded-full text-sm font-medium
                                {{ $order->payment_status === 'paid' ? 'bg-green-100 text-green-800' : 
                                   ($order->payment_status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                {{ $order->payment_status_label }}
                            </span>
                        </div>
                    </div>
                    
                    @if($order->payment_link)
                    <div class="text-center">
                        <a href="{{ $order->payment_link }}" 
                           target="_blank"
                           class="inline-flex items-center bg-orange-600 text-white px-6 py-3 rounded-lg hover:bg-orange-700 transition">
                            <i class="fas fa-external-link-alt mr-2"></i>
                            Finalizar Pagamento
                        </a>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Next Steps -->
            <div class="card mt-8 bg-blue-50 border border-blue-200">
                <h2 class="text-xl font-bold text-blue-900 mb-4">
                    <i class="fas fa-info-circle mr-2"></i>
                    Próximos Passos
                </h2>
                
                <div class="grid md:grid-cols-3 gap-6 text-blue-800">
                    <div class="flex items-start">
                        <div class="bg-blue-200 rounded-full w-8 h-8 flex items-center justify-center mr-3 mt-0.5">
                            <span class="text-blue-900 font-bold text-sm">1</span>
                        </div>
                        <div>
                            <h3 class="font-semibold">Confirmação</h3>
                            <p class="text-sm">Você receberá uma confirmação por WhatsApp</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="bg-blue-200 rounded-full w-8 h-8 flex items-center justify-center mr-3 mt-0.5">
                            <span class="text-blue-900 font-bold text-sm">2</span>
                        </div>
                        <div>
                            <h3 class="font-semibold">Preparação</h3>
                            <p class="text-sm">Seu pedido será preparado com carinho</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="bg-blue-200 rounded-full w-8 h-8 flex items-center justify-center mr-3 mt-0.5">
                            <span class="text-blue-900 font-bold text-sm">3</span>
                        </div>
                        <div>
                            <h3 class="font-semibold">Entrega</h3>
                            <p class="text-sm">Entregaremos no endereço informado</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex flex-col sm:flex-row gap-4 mt-8">
                <a href="{{ route('menu.index') }}" 
                   class="flex-1 bg-orange-600 text-white px-6 py-3 rounded-lg hover:bg-orange-700 transition text-center font-medium">
                    <i class="fas fa-utensils mr-2"></i>
                    Fazer Novo Pedido
                </a>
                
                <a href="https://wa.me/5571987019420?text=Olá! Tenho uma dúvida sobre o pedido #{{ $order->order_number }}" 
                   target="_blank"
                   class="flex-1 bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition text-center font-medium">
                    <i class="fab fa-whatsapp mr-2"></i>
                    Falar no WhatsApp
                </a>
            </div>
        </div>
    </div>
</div>
@endsection