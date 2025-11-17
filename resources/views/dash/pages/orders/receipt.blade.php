<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo do Pedido #{{ $order->order_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', 'Helvetica', sans-serif;
            font-size: 14px;
            line-height: 1.6;
            color: #333;
            background: #f5f5f5;
            padding: 20px;
        }
        
        .receipt-container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .receipt-header {
            background: linear-gradient(135deg, #ea580c 0%, #c2410c 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        
        .receipt-header h1 {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .receipt-header .subtitle {
            font-size: 14px;
            opacity: 0.9;
        }
        
        .receipt-body {
            padding: 30px 20px;
        }
        
        .section {
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e5e5e5;
        }
        
        .section:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #ea580c;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            padding: 5px 0;
        }
        
        .info-label {
            font-weight: 600;
            color: #666;
        }
        
        .info-value {
            color: #333;
            text-align: right;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        .items-table th {
            background: #f9f9f9;
            padding: 10px;
            text-align: left;
            font-weight: 600;
            font-size: 12px;
            color: #666;
            border-bottom: 2px solid #e5e5e5;
        }
        
        .items-table td {
            padding: 12px 10px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .items-table tr:last-child td {
            border-bottom: none;
        }
        
        .item-name {
            font-weight: 500;
        }
        
        .item-details {
            font-size: 12px;
            color: #999;
            margin-top: 3px;
        }
        
        .item-qty {
            text-align: center;
        }
        
        .item-price {
            text-align: right;
        }
        
        .totals {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 2px solid #e5e5e5;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 15px;
        }
        
        .total-row.subtotal {
            color: #666;
        }
        
        .total-row.discount {
            color: #16a34a;
        }
        
        .total-row.final {
            font-size: 20px;
            font-weight: bold;
            color: #ea580c;
            padding-top: 10px;
            border-top: 2px solid #e5e5e5;
            margin-top: 10px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-confirmed { background: #dbeafe; color: #1e40af; }
        .status-preparing { background: #fef08a; color: #854d0e; }
        .status-ready { background: #bfdbfe; color: #1e3a8a; }
        .status-delivered { background: #bbf7d0; color: #14532d; }
        .status-cancelled { background: #fecaca; color: #991b1b; }
        
        .payment-status {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .payment-pending { background: #fef3c7; color: #92400e; }
        .payment-paid { background: #bbf7d0; color: #14532d; }
        .payment-failed { background: #fecaca; color: #991b1b; }
        
        .timeline {
            margin-top: 10px;
        }
        
        .timeline-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 12px;
            padding-left: 20px;
            position: relative;
        }
        
        .timeline-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 6px;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #ea580c;
        }
        
        .timeline-item::after {
            content: '';
            position: absolute;
            left: 3px;
            top: 14px;
            width: 2px;
            height: calc(100% + 4px);
            background: #e5e5e5;
        }
        
        .timeline-item:last-child::after {
            display: none;
        }
        
        .timeline-label {
            font-weight: 600;
            color: #666;
            font-size: 12px;
            margin-bottom: 2px;
        }
        
        .timeline-value {
            color: #333;
            font-size: 13px;
        }
        
        .footer {
            background: #f9f9f9;
            padding: 20px;
            text-align: center;
            color: #666;
            font-size: 12px;
        }
        
        @media print {
            body {
                background: white;
                padding: 0;
            }
            
            .receipt-container {
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <!-- Cabeçalho -->
        <div class="receipt-header">
            @php
                $settings = $settings ?? \App\Models\Setting::getSettings();
            @endphp
            @if($settings->logo_url)
                <img src="{{ $settings->logo_url }}" alt="{{ $settings->business_name ?? 'OLIKA' }}" style="max-height: 80px; margin-bottom: 10px;">
            @endif
            <h1>{{ $settings->business_name ?? 'OLIKA' }}</h1>
            <div class="subtitle">Recibo do Pedido</div>
            @if($settings->business_description)
                <p style="font-size: 12px; opacity: 0.8; margin-top: 5px;">{{ $settings->business_description }}</p>
            @endif
        </div>
        
        <div class="receipt-body">
            <!-- Informações do Pedido -->
            <div class="section">
                <div class="section-title">Informações do Pedido</div>
                <div class="info-row">
                    <span class="info-label">Número do Pedido:</span>
                    <span class="info-value"><strong>#{{ $order->order_number }}</strong></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Data do Pedido:</span>
                    <span class="info-value">{{ $order->created_at->format('d/m/Y H:i') }}</span>
                </div>
                @if($order->updated_at && $order->updated_at != $order->created_at)
                <div class="info-row">
                    <span class="info-label">Última Atualização:</span>
                    <span class="info-value">{{ $order->updated_at->format('d/m/Y H:i') }}</span>
                </div>
                @endif
                <div class="info-row">
                    <span class="info-label">Status:</span>
                    <span class="info-value">
                        @php
                            $statusClasses = [
                                'pending' => 'status-pending',
                                'confirmed' => 'status-confirmed',
                                'preparing' => 'status-preparing',
                                'ready' => 'status-ready',
                                'delivered' => 'status-delivered',
                                'cancelled' => 'status-cancelled',
                            ];
                            $statusLabels = [
                                'pending' => 'Pendente',
                                'confirmed' => 'Confirmado',
                                'preparing' => 'Em Preparo',
                                'ready' => 'Pronto',
                                'delivered' => 'Entregue',
                                'cancelled' => 'Cancelado',
                            ];
                            $statusClass = $statusClasses[$order->status] ?? 'status-pending';
                            $statusLabel = $statusLabels[$order->status] ?? ucfirst($order->status);
                        @endphp
                        <span class="status-badge {{ $statusClass }}">{{ $statusLabel }}</span>
                    </span>
                </div>
            </div>
            
            <!-- Cliente -->
            <div class="section">
                <div class="section-title">Cliente</div>
                <div class="info-row">
                    <span class="info-label">Nome:</span>
                    <span class="info-value">{{ $order->customer->name ?? 'Não informado' }}</span>
                </div>
                @if($order->customer && $order->customer->phone)
                <div class="info-row">
                    <span class="info-label">Telefone:</span>
                    <span class="info-value">{{ $order->customer->phone }}</span>
                </div>
                @endif
                @if($order->customer && $order->customer->email)
                <div class="info-row">
                    <span class="info-label">E-mail:</span>
                    <span class="info-value">{{ $order->customer->email }}</span>
                </div>
                @endif
            </div>
            
            <!-- Entrega -->
            @if($order->delivery_type)
            <div class="section">
                <div class="section-title">Entrega</div>
                <div class="info-row">
                    <span class="info-label">Tipo:</span>
                    <span class="info-value">{{ $order->delivery_type === 'delivery' ? 'Entrega' : 'Retirada' }}</span>
                </div>
                @if($order->delivery_address)
                <div class="info-row">
                    <span class="info-label">Endereço:</span>
                    <span class="info-value">{{ $order->delivery_address }}</span>
                </div>
                @endif
                @if($order->estimated_time)
                <div class="info-row">
                    <span class="info-label">Tempo Estimado:</span>
                    <span class="info-value">{{ $order->estimated_time }} minutos</span>
                </div>
                @endif
                @if($order->scheduled_delivery_at)
                <div class="info-row">
                    <span class="info-label">Data/Hora Agendada:</span>
                    <span class="info-value">{{ \Carbon\Carbon::parse($order->scheduled_delivery_at)->format('d/m/Y H:i') }}</span>
                </div>
                @endif
            </div>
            @endif
            
            <!-- Itens do Pedido -->
            <div class="section">
                <div class="section-title">Itens do Pedido</div>
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th style="text-align: center; width: 60px;">Qtd</th>
                            <th style="text-align: right;">Preço Unit.</th>
                            <th style="text-align: right;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                        <tr>
                            <td>
                                <div class="item-name">
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
                                </div>
                                @if($item->special_instructions)
                                <div class="item-details">Obs: {{ $item->special_instructions }}</div>
                                @endif
                            </td>
                            <td class="item-qty">{{ $item->quantity }}</td>
                            <td class="item-price">R$ {{ number_format($item->unit_price, 2, ',', '.') }}</td>
                            <td class="item-price"><strong>R$ {{ number_format($item->total_price, 2, ',', '.') }}</strong></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Totais -->
            <div class="section totals">
                <div class="total-row subtotal">
                    <span>Subtotal dos Itens:</span>
                    <span>R$ {{ number_format($order->total_amount ?? 0, 2, ',', '.') }}</span>
                </div>
                @if($order->delivery_fee > 0)
                <div class="total-row subtotal">
                    <span>Taxa de Entrega:</span>
                    <span>R$ {{ number_format($order->delivery_fee, 2, ',', '.') }}</span>
                </div>
                @endif
                @if($order->discount_amount > 0)
                <div class="total-row discount">
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
                <div class="total-row final">
                    <span>TOTAL:</span>
                    <span>R$ {{ number_format($order->final_amount ?? $order->total_amount ?? 0, 2, ',', '.') }}</span>
                </div>
            </div>
            
            <!-- Pagamento -->
            <div class="section">
                <div class="section-title">Pagamento</div>
                <div class="info-row">
                    <span class="info-label">Método:</span>
                    <span class="info-value">{{ ucfirst(str_replace('_', ' ', $order->payment_method ?? 'Não informado')) }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Status:</span>
                    <span class="info-value">
                        @php
                            $paymentClasses = [
                                'pending' => 'payment-pending',
                                'paid' => 'payment-paid',
                                'failed' => 'payment-failed',
                            ];
                            $paymentLabels = [
                                'pending' => 'Pendente',
                                'paid' => 'Pago',
                                'failed' => 'Falhou',
                            ];
                            $paymentClass = $paymentClasses[$order->payment_status] ?? 'payment-pending';
                            $paymentLabel = $paymentLabels[$order->payment_status] ?? ucfirst($order->payment_status ?? 'Pendente');
                        @endphp
                        <span class="payment-status {{ $paymentClass }}">{{ $paymentLabel }}</span>
                    </span>
                </div>
            </div>
            
            <!-- Observações -->
            @if($order->notes || $order->delivery_instructions)
            <div class="section">
                <div class="section-title">Observações</div>
                @if($order->notes)
                <div class="info-row" style="flex-direction: column; align-items: flex-start;">
                    <span class="info-label" style="margin-bottom: 5px;">Notas do Pedido:</span>
                    <span class="info-value">{{ $order->notes }}</span>
                </div>
                @endif
                @if($order->delivery_instructions)
                <div class="info-row" style="flex-direction: column; align-items: flex-start; margin-top: 10px;">
                    <span class="info-label" style="margin-bottom: 5px;">Instruções de Entrega:</span>
                    <span class="info-value">{{ $order->delivery_instructions }}</span>
                </div>
                @endif
            </div>
            @endif
            
            <!-- Histórico de Alterações -->
            @if(isset($statusHistory) && $statusHistory->count() > 0)
            <div class="section">
                <div class="section-title">Histórico de Alterações</div>
                <div class="timeline">
                    @foreach($statusHistory as $history)
                    <div class="timeline-item">
                        <div>
                            <div class="timeline-label">
                                {{ \Carbon\Carbon::parse($history->created_at)->format('d/m/Y H:i') }}
                            </div>
                            <div class="timeline-value">
                                @if($history->old_status)
                                    Status alterado de <strong>{{ ucfirst($history->old_status) }}</strong> para <strong>{{ ucfirst($history->new_status) }}</strong>
                                @else
                                    Status definido como <strong>{{ ucfirst($history->new_status) }}</strong>
                                @endif
                                @if($history->note)
                                    - {{ $history->note }}
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
        
        <div class="footer">
            @php
                $settings = $settings ?? \App\Models\Setting::getSettings();
            @endphp
            <p><strong>{{ $settings->business_name ?? 'OLIKA' }}</strong>@if($settings->business_description) - {{ $settings->business_description }}@endif</p>
            @if($settings->business_phone)
                <p>Telefone: {{ $settings->business_phone }}</p>
            @endif
            @if($settings->business_email)
                <p>E-mail: {{ $settings->business_email }}</p>
            @endif
            @if($settings->business_address)
                <p>{{ $settings->business_address }}</p>
            @endif
            <p style="margin-top: 10px;">Este é um recibo gerado automaticamente.</p>
            <p>Em caso de dúvidas, entre em contato conosco.</p>
        </div>
    </div>
</body>
</html>

