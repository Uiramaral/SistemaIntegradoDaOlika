<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo Fiscal - Pedido #{{ $order->order_number }}</title>
    <style>
        @media print {
            @page {
                size: 80mm auto;
                margin: 0;
            }
            body {
                margin: 0;
                padding: 5mm;
            }
            .no-print {
                display: none !important;
            }
        }
        
        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.4;
            width: 80mm;
            margin: 0 auto;
            padding: 5mm;
            background: white;
            color: #000;
        }
        
        .receipt {
            width: 100%;
        }
        
        .header {
            text-align: center;
            border-bottom: 1px dashed #000;
            padding-bottom: 5px;
            margin-bottom: 5px;
        }
        
        .header h1 {
            font-size: 16px;
            font-weight: bold;
            margin: 5px 0;
            text-transform: uppercase;
        }
        
        .header .subtitle {
            font-size: 11px;
            margin-bottom: 5px;
        }
        
        .divider {
            border-top: 1px dashed #000;
            margin: 5px 0;
        }
        
        .section {
            margin: 5px 0;
        }
        
        .section-title {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 11px;
            margin-bottom: 3px;
        }
        
        .info-line {
            font-size: 11px;
            margin: 2px 0;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin: 5px 0;
        }
        
        .items-table th {
            text-align: left;
            border-bottom: 1px dashed #000;
            padding: 2px 0;
            font-weight: bold;
        }
        
        .items-table td {
            padding: 2px 0;
            vertical-align: top;
        }
        
        .items-table .item-name {
            width: 50%;
        }
        
        .items-table .item-qty {
            width: 15%;
            text-align: center;
        }
        
        .items-table .item-price {
            width: 35%;
            text-align: right;
        }
        
        .item-obs {
            font-size: 10px;
            font-style: italic;
            color: #666;
            margin-left: 10px;
        }
        
        .totals {
            margin-top: 5px;
            border-top: 1px dashed #000;
            padding-top: 5px;
        }
        
        .total-line {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            margin: 2px 0;
        }
        
        .total-final {
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
            padding: 3px 0;
            margin: 5px 0;
            font-weight: bold;
            font-size: 14px;
        }
        
        .footer {
            text-align: center;
            margin-top: 10px;
            padding-top: 5px;
            border-top: 1px dashed #000;
            font-size: 10px;
        }
        
        .print-actions {
            text-align: center;
            margin: 20px 0;
            padding: 10px;
            background: #f5f5f5;
        }
        
        .print-actions button {
            padding: 10px 20px;
            margin: 5px;
            font-size: 14px;
            cursor: pointer;
            border: none;
            border-radius: 4px;
            background: #7A5230;
            color: white;
        }
        
        .print-actions button:hover {
            background: #5E3E23;
        }
    </style>
</head>
<body>
    <div class="no-print print-actions">
        <button onclick="window.print()">🖨️ Imprimir</button>
        <button onclick="window.close()">✖️ Fechar</button>
    </div>
    
    <div class="receipt">
        <div class="header">
            <h1>OLIKA</h1>
            <div class="subtitle">PÃES ARTESANAIS</div>
            <div class="divider"></div>
        </div>
        
        <div class="section">
            <div class="info-line"><strong>DATA:</strong> {{ $order->created_at->format('d/m/Y H:i:s') }}</div>
            <div class="info-line"><strong>PEDIDO:</strong> #{{ $order->order_number }}</div>
            <div class="divider"></div>
        </div>
        
        @if($order->customer)
        <div class="section">
            <div class="section-title">CLIENTE</div>
            <div class="info-line">{{ $order->customer->name }}</div>
            @if($order->customer->phone)
            <div class="info-line">TEL: {{ $order->customer->phone }}</div>
            @endif
        </div>
        @endif
        
        @if($order->address)
        <div class="section">
            <div class="section-title">ENTREGA</div>
            <div class="info-line">{{ $order->address->address }}, {{ $order->address->number }}</div>
            @if($order->address->complement)
            <div class="info-line">{{ $order->address->complement }}</div>
            @endif
            <div class="info-line">{{ $order->address->neighborhood }}</div>
            <div class="info-line">{{ $order->address->city }} - {{ $order->address->state }}</div>
            <div class="info-line">CEP: {{ $order->address->zip_code }}</div>
        </div>
        @endif
        
        <div class="divider"></div>
        
        <div class="section">
            <div class="section-title">ITENS DO PEDIDO</div>
            <table class="items-table">
                <thead>
                    <tr>
                        <th class="item-name">ITEM</th>
                        <th class="item-qty">QTD</th>
                        <th class="item-price">VALOR</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                    <tr>
                        <td class="item-name">
                            {{ $item->custom_name ?? ($item->product ? $item->product->name : 'Produto') }}
                            @if($item->special_instructions)
                            <div class="item-obs">Obs: {{ $item->special_instructions }}</div>
                            @endif
                        </td>
                        <td class="item-qty">{{ $item->quantity }}</td>
                        <td class="item-price">R$ {{ number_format($item->unit_price, 2, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="totals">
            <div class="total-line">
                <span>SUBTOTAL:</span>
                <span>R$ {{ number_format($order->total_amount ?? 0, 2, ',', '.') }}</span>
            </div>
            @if($order->delivery_fee > 0)
            <div class="total-line">
                <span>ENTREGA:</span>
                <span>R$ {{ number_format($order->delivery_fee, 2, ',', '.') }}</span>
            </div>
            @endif
            @if($order->discount_amount > 0)
            <div class="total-line">
                <span>
                    @if($order->coupon_code)
                        CUPOM {{ $order->coupon_code }}:
                    @else
                        DESCONTO:
                    @endif
                </span>
                <span>-R$ {{ number_format($order->discount_amount, 2, ',', '.') }}</span>
            </div>
            @endif
            <div class="total-line total-final">
                <span>TOTAL:</span>
                <span>R$ {{ number_format($order->final_amount ?? $order->total_amount ?? 0, 2, ',', '.') }}</span>
            </div>
        </div>
        
        <div class="divider"></div>
        
        <div class="section">
            <div class="section-title">PAGAMENTO</div>
            <div class="info-line">
                <strong>{{ strtoupper(str_replace('_', ' ', $order->payment_method ?? 'PIX')) }}</strong>
            </div>
            <div class="info-line">
                STATUS: 
                @if($order->payment_status === 'paid' || $order->payment_status === 'approved')
                    PAGO
                @else
                    PENDENTE
                @endif
            </div>
        </div>
        
        @if($order->notes)
        <div class="section">
            <div class="section-title">OBSERVAÇÕES</div>
            <div class="info-line">{{ $order->notes }}</div>
        </div>
        @endif
        
        <div class="divider"></div>
        
        <div class="footer">
            <div>OBRIGADO PELA PREFERÊNCIA!</div>
            <div style="margin-top: 5px;">www.olika.com.br</div>
        </div>
    </div>
    
    <script>
        // Auto-imprimir se for impressão automática
        @if(request()->get('auto_print'))
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 250);
        };
        @endif
    </script>
</body>
</html>
