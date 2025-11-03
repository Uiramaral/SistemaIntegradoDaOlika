{{-- Componente de Resumo do Pedido --}}
@php
    // Calcular valores
    $subtotal = $order->total_amount ?? ($order->items->sum('total_price') ?? 0);
    $deliveryFee = $order->delivery_fee ?? 0;
    $discount = $order->discount_amount ?? 0;
    $total = $order->final_amount ?? ($subtotal + $deliveryFee - $discount);
@endphp

<div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
    <h2 class="text-xl font-semibold mb-4 text-gray-900">Resumo</h2>
    
    {{-- Itens do pedido --}}
    <div class="space-y-3 mb-4">
        @if(isset($order) && $order->items)
            @foreach($order->items as $item)
            <div class="flex items-center justify-between text-sm">
                <span class="text-gray-700">{{ $item->quantity ?? $item->qty ?? 1 }}x {{ $item->custom_name ?? optional($item->product)->name ?? 'Item' }}</span>
                <span class="text-gray-900 font-medium">R$ {{ number_format((float)($item->total_price ?? (($item->unit_price ?? $item->price ?? 0) * ($item->quantity ?? $item->qty ?? 1))), 2, ',', '.') }}</span>
            </div>
            @endforeach
        @elseif(isset($cartItems))
            @foreach($cartItems as $item)
            <div class="flex items-center justify-between text-sm">
                <span class="text-gray-700">{{ $item['qty'] ?? 1 }}x {{ $item['name'] ?? 'Item' }}</span>
                <span class="text-gray-900 font-medium">R$ {{ number_format((float)($item['subtotal'] ?? ($item['price'] ?? 0) * ($item['qty'] ?? 1)), 2, ',', '.') }}</span>
            </div>
            @endforeach
        @endif
    </div>
    
    <div class="border-t pt-4 space-y-2">
        <div class="flex justify-between text-sm">
            <span class="text-gray-600">Subtotal</span>
            <span class="text-gray-900 font-medium">R$ {{ number_format((float)$subtotal, 2, ',', '.') }}</span>
        </div>
        @if($deliveryFee > 0)
        <div class="flex justify-between text-sm">
            <span class="text-gray-600">Taxa de Entrega</span>
            <span class="text-gray-900 font-medium">R$ {{ number_format((float)$deliveryFee, 2, ',', '.') }}</span>
        </div>
        @endif
        @if($discount > 0)
        <div class="flex justify-between text-sm text-green-700">
            <span>Desconto{{ isset($order->coupon_code) ? ' (' . $order->coupon_code . ')' : '' }}</span>
            <span class="font-medium">- R$ {{ number_format((float)$discount, 2, ',', '.') }}</span>
        </div>
        @endif
        <div class="flex justify-between pt-2 border-t">
            <span class="font-semibold text-gray-900">Total</span>
            <span class="text-xl font-bold text-orange-500">R$ {{ number_format((float)$total, 2, ',', '.') }}</span>
        </div>
    </div>
</div>

