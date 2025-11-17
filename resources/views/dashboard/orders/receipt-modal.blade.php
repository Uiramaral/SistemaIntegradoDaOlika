@php
    $settings = $settings ?? \App\Models\Setting::getSettings();
    $statusLabels = [
        'pending' => 'Pendente',
        'confirmed' => 'Confirmado',
        'preparing' => 'Em Preparo',
        'ready' => 'Pronto',
        'delivered' => 'Entregue',
        'cancelled' => 'Cancelado',
    ];
    $statusColors = [
        'pending' => 'bg-muted text-muted-foreground',
        'confirmed' => 'bg-primary text-primary-foreground',
        'preparing' => 'bg-warning text-warning-foreground',
        'ready' => 'bg-primary/80 text-primary-foreground',
        'delivered' => 'bg-success text-success-foreground',
        'cancelled' => 'bg-destructive text-destructive-foreground',
    ];
    $paymentLabels = [
        'pending' => 'Pendente',
        'paid' => 'Pago',
        'failed' => 'Falhou',
    ];
    $paymentColors = [
        'pending' => 'bg-muted text-muted-foreground',
        'paid' => 'bg-success text-success-foreground',
        'failed' => 'bg-destructive text-destructive-foreground',
    ];
    $statusLabel = $statusLabels[$order->status] ?? ucfirst($order->status ?? 'Pendente');
    $statusColor = $statusColors[$order->status] ?? 'bg-muted text-muted-foreground';
    $paymentLabel = $paymentLabels[$order->payment_status] ?? ucfirst($order->payment_status ?? 'Pendente');
    $paymentColor = $paymentColors[$order->payment_status] ?? 'bg-muted text-muted-foreground';
@endphp

<div class="rounded-lg border bg-card text-card-foreground shadow-sm max-h-[90vh] overflow-y-auto">
        <!-- Cabeçalho do Recibo -->
        <div class="bg-primary text-primary-foreground p-6 text-center">
            @if($settings && $settings->logo_url)
                <img src="{{ $settings->logo_url }}" alt="{{ $settings->business_name ?? 'OLIKA' }}" class="mx-auto h-16 mb-4">
            @else
                <h1 class="text-3xl font-bold mb-2">{{ $settings->business_name ?? 'OLIKA' }}</h1>
            @endif
            <p class="text-sm opacity-90">Recibo do Pedido</p>
            @if($settings && $settings->business_description)
                <p class="text-xs opacity-75 mt-2">{{ $settings->business_description }}</p>
            @endif
        </div>
    
    <div class="p-6 space-y-6">
        <!-- Informações do Pedido -->
        <div>
            <h3 class="text-sm font-semibold text-muted-foreground mb-2">INFORMAÇÕES DO PEDIDO</h3>
            <div class="space-y-1 text-sm">
                <div class="flex justify-between">
                    <span class="text-muted-foreground">Número:</span>
                    <span class="font-semibold">#{{ $order->order_number }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-muted-foreground">Data:</span>
                    <span>{{ $order->created_at->format('d/m/Y H:i') }}</span>
                </div>
                @if($order->updated_at && $order->updated_at != $order->created_at)
                <div class="flex justify-between">
                    <span class="text-muted-foreground">Última Atualização:</span>
                    <span>{{ $order->updated_at->format('d/m/Y H:i') }}</span>
                </div>
                @endif
                <div class="flex justify-between items-center">
                    <span class="text-muted-foreground">Status:</span>
                    <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold {{ $statusColor }}">
                        {{ $statusLabel }}
                    </span>
                </div>
            </div>
        </div>
        
        <!-- Cliente -->
        <div>
            <h3 class="text-sm font-semibold text-muted-foreground mb-2">CLIENTE</h3>
            <div class="space-y-1 text-sm">
                <div class="flex justify-between">
                    <span class="text-muted-foreground">Nome:</span>
                    <span>{{ $order->customer->name ?? 'Não informado' }}</span>
                </div>
                @if($order->customer && $order->customer->phone)
                <div class="flex justify-between">
                    <span class="text-muted-foreground">Telefone:</span>
                    <span>{{ $order->customer->phone }}</span>
                </div>
                @endif
                @if($order->customer && $order->customer->email)
                <div class="flex justify-between">
                    <span class="text-muted-foreground">E-mail:</span>
                    <span>{{ $order->customer->email }}</span>
                </div>
                @endif
            </div>
        </div>
        
        <!-- Entrega -->
        @if($order->delivery_type)
        <div>
            <h3 class="text-sm font-semibold text-muted-foreground mb-2">ENTREGA</h3>
            <div class="space-y-1 text-sm">
                <div class="flex justify-between">
                    <span class="text-muted-foreground">Tipo:</span>
                    <span>{{ $order->delivery_type === 'delivery' ? 'Entrega' : 'Retirada' }}</span>
                </div>
                @if($order->delivery_address)
                <div class="flex justify-between">
                    <span class="text-muted-foreground">Endereço:</span>
                    <span class="text-right">{{ $order->delivery_address }}</span>
                </div>
                @endif
                @if($order->estimated_time)
                <div class="flex justify-between">
                    <span class="text-muted-foreground">Tempo Estimado:</span>
                    <span>{{ $order->estimated_time }} minutos</span>
                </div>
                @endif
            </div>
        </div>
        @endif
        
        <!-- Itens do Pedido -->
        <div>
            <h3 class="text-sm font-semibold text-muted-foreground mb-3">ITENS DO PEDIDO</h3>
            <div class="space-y-2">
                @foreach($order->items as $item)
                <div class="border-b pb-2">
                    <div class="flex justify-between items-start mb-1">
                        <span class="font-medium text-sm">
                            {{ $item->quantity }}x 
                            @php
                                $itemName = null;
                                if(!$item->product_id && $item->custom_name) {
                                    $itemName = 'Item Avulso - ' . $item->custom_name;
                                } elseif($item->custom_name) {
                                    $itemName = $item->custom_name;
                                } elseif($item->product) {
                                    $itemName = $item->product->name;
                                } else {
                                    $itemName = 'Produto';
                                }
                                
                                $variantName = null;
                                $weight = null;
                                
                                if ($item->variant_id && $item->variant) {
                                    $variantName = $item->variant->name;
                                    $weight = $item->variant->weight_grams;
                                } elseif ($item->product) {
                                    $weight = $item->product->weight_grams;
                                }
                                
                                // Montar nome completo: Nome + Variante (se houver) + Peso (se houver)
                                $displayName = $itemName;
                                if ($variantName) {
                                    $displayName .= ' (' . $variantName . ')';
                                }
                                if ($weight) {
                                    $displayName .= ' - ' . number_format($weight / 1000, 1, ',', '.') . 'kg';
                                }
                            @endphp
                            {{ $displayName }}
                        </span>
                        <span class="font-semibold text-sm">R$ {{ number_format($item->total_price, 2, ',', '.') }}</span>
                    </div>
                    @if($item->special_instructions)
                    <p class="text-xs text-muted-foreground">Obs: {{ $item->special_instructions }}</p>
                    @endif
                    <p class="text-xs text-muted-foreground">R$ {{ number_format($item->unit_price, 2, ',', '.') }} cada</p>
                </div>
                @endforeach
            </div>
        </div>
        
        <!-- Totais -->
        <div class="border-t pt-4 space-y-2">
            <div class="flex justify-between text-sm">
                <span class="text-muted-foreground">Subtotal dos Itens:</span>
                <span>R$ {{ number_format($order->total_amount ?? 0, 2, ',', '.') }}</span>
            </div>
            @if($order->delivery_fee > 0)
            <div class="flex justify-between text-sm">
                <span class="text-muted-foreground">Taxa de Entrega:</span>
                <span>R$ {{ number_format($order->delivery_fee, 2, ',', '.') }}</span>
            </div>
            @endif
            @if($order->discount_amount > 0)
            <div class="flex justify-between text-sm text-green-600">
                <span>
                    @if($order->coupon_code)
                        @php
                            $coupon = \App\Models\Coupon::where('code', $order->coupon_code)->first();
                            if ($coupon) {
                                $couponValue = $coupon->type === 'percentage' 
                                    ? number_format($coupon->value, 1) . '%' 
                                    : 'R$ ' . number_format($coupon->value, 2, ',', '.');
                                echo "Cupom {$order->coupon_code} {$couponValue}";
                            } else {
                                echo "Cupom {$order->coupon_code}";
                            }
                        @endphp
                    @elseif($order->manual_discount_type)
                        @php
                            $discountValue = $order->manual_discount_type === 'percentage' 
                                ? number_format($order->manual_discount_value, 1) . '%' 
                                : 'R$ ' . number_format($order->manual_discount_value, 2, ',', '.');
                            echo "Desconto {$discountValue}";
                        @endphp
                    @else
                        Desconto Aplicado
                    @endif
                </span>
                <span>- R$ {{ number_format($order->discount_amount, 2, ',', '.') }}</span>
            </div>
            @endif
            <div class="flex justify-between text-lg font-bold pt-2 border-t">
                <span>TOTAL:</span>
                <span class="text-primary">R$ {{ number_format($order->final_amount ?? $order->total_amount ?? 0, 2, ',', '.') }}</span>
            </div>
        </div>
        
        <!-- Pagamento -->
        <div>
            <h3 class="text-sm font-semibold text-muted-foreground mb-2">PAGAMENTO</h3>
            <div class="space-y-1 text-sm">
                <div class="flex justify-between">
                    <span class="text-muted-foreground">Método:</span>
                    <span>{{ ucfirst(str_replace('_', ' ', $order->payment_method ?? 'Não informado')) }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-muted-foreground">Status:</span>
                    <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold {{ $paymentColor }}">
                        {{ $paymentLabel }}
                    </span>
                </div>
            </div>
        </div>
        
        <!-- Observações -->
        @if($order->notes || $order->delivery_instructions)
        <div>
            <h3 class="text-sm font-semibold text-muted-foreground mb-2">OBSERVAÇÕES</h3>
            <div class="space-y-2 text-sm">
                @if($order->notes)
                <div>
                    <span class="text-muted-foreground">Notas:</span>
                    <p>{{ $order->notes }}</p>
                </div>
                @endif
                @if($order->delivery_instructions)
                <div>
                    <span class="text-muted-foreground">Instruções de Entrega:</span>
                    <p>{{ $order->delivery_instructions }}</p>
                </div>
                @endif
            </div>
        </div>
        @endif
        
        <!-- Rodapé -->
        <div class="pt-4 border-t text-center text-xs text-muted-foreground">
            <p class="font-semibold mb-1">{{ $settings->business_name ?? 'OLIKA' }}</p>
            @if($settings && $settings->business_phone)
            <p>Telefone: {{ $settings->business_phone }}</p>
            @endif
            @if($settings && $settings->business_email)
            <p>E-mail: {{ $settings->business_email }}</p>
            @endif
            @if($settings && $settings->business_address)
            <p>{{ $settings->business_address }}</p>
            @endif
            <p class="mt-2">Este é um recibo gerado automaticamente.</p>
        </div>
    </div>
</div>


