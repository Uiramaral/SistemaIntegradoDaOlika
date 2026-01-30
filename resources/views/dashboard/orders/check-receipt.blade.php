<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo de Conferência - Pedido #{{ $order->order_number }}</title>
    <style>
        @media print {
            @page {
                size: 80mm auto;
                margin: 0;
                padding: 0;
            }
            body {
                margin: 0;
                padding: 5mm;
                background: white !important;
            }
            .no-print {
                display: none !important;
            }
        }
        
        body {
            font-family: 'Courier New', 'Courier', monospace;
            font-size: 12px;
            line-height: 1.4;
            width: 80mm;
            max-width: 80mm;
            margin: 0 auto;
            padding: 5mm;
            background: white;
            color: #000000;
        }
        
        .receipt {
            width: 100%;
        }
        
        .header {
            text-align: center;
            border-bottom: 2px solid #000000;
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
        
        .header .badge {
            background: #fbbf24;
            color: #000;
            font-weight: bold;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            margin-top: 5px;
            display: inline-block;
        }
        
        .divider {
            border-top: 1px solid #000000;
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
        
        .info-line strong {
            font-weight: bold;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin: 5px 0;
        }
        
        .items-table th {
            text-align: left;
            border-bottom: 1px solid #000000;
            padding: 2px 0;
            font-weight: bold;
        }
        
        .items-table td {
            padding: 2px 0;
            vertical-align: top;
        }
        
        .items-table .item-name {
            width: 70%;
        }
        
        .items-table .item-qty {
            width: 30%;
            text-align: center;
        }
        
        .item-obs {
            font-size: 10px;
            font-style: italic;
            margin-left: 10px;
            font-weight: normal;
        }
        
        .footer {
            text-align: center;
            margin-top: 10px;
            padding-top: 5px;
            border-top: 1px solid #000000;
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
        
        .info-box {
            background: #fef3c7;
            border: 1px solid #fbbf24;
            padding: 8px;
            margin: 10px 0;
            border-radius: 4px;
            font-size: 11px;
            text-align: center;
        }
        
        .info-box strong {
            font-weight: bold;
            font-size: 12px;
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
            <h1>RECIBO DE CONFERÊNCIA</h1>
            <div class="badge">SEM VALORES</div>
            <div class="subtitle">Somente para conferência de produtos</div>
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
        
        <div class="divider"></div>
        
        <div class="section">
            <div class="section-title">ITENS DO PEDIDO</div>
            <table class="items-table">
                <thead>
                    <tr>
                        <th class="item-name">PRODUTO</th>
                        <th class="item-qty">QTD</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                    <tr>
                        <td class="item-name">
                            @php
                                $itemName = $item->custom_name ?? ($item->product ? $item->product->name : 'Produto');
                                $variantName = null;
                                $weight = null;
                                
                                if ($item->variant_id && $item->variant) {
                                    $variantName = $item->variant->name;
                                    $weight = $item->variant->weight_grams;
                                } elseif ($item->product) {
                                    $weight = $item->product->weight_grams;
                                }
                                
                                // Exibir nome do item
                                echo htmlspecialchars($itemName);
                                
                                // Exibir variante, se houver
                                if ($variantName) {
                                    echo ' (' . htmlspecialchars($variantName) . ')';
                                }
                                
                                // Exibir peso, se houver
                                if ($weight) {
                                    if ($weight >= 1000) {
                                        echo ' ' . number_format($weight / 1000, 2, ',', '.') . 'kg';
                                    } else {
                                        echo ' ' . $weight . 'g';
                                    }
                                }
                            @endphp
                            
                            @if($item->notes)
                                <div class="item-obs">OBS: {{ $item->notes }}</div>
                            @endif
                        </td>
                        <td class="item-qty">{{ $item->quantity }}x</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        @if($order->notes)
        <div class="section">
            <div class="section-title">OBSERVAÇÕES DO PEDIDO</div>
            <div class="info-line">{{ $order->notes }}</div>
        </div>
        @endif
        
        <div class="divider"></div>
        
        <div class="info-box">
            <strong>⚠️ ATENÇÃO</strong><br>
            Este é um recibo de conferência.<br>
            Não contém valores, endereço ou forma de pagamento.
        </div>
        
        <div class="footer">
            <div>Olika - Pães Artesanais</div>
            <div>{{ now()->format('d/m/Y H:i:s') }}</div>
        </div>
    </div>

    <script>
        // Auto-imprimir quando carregar (opcional)
        // window.onload = function() {
        //     window.print();
        // };
    </script>
</body>
</html>
