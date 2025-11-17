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
                padding: 0;
            }
            body {
                margin: 0;
                padding: 5mm;
                background: white !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                color-adjust: exact;
            }
            .no-print {
                display: none !important;
            }
            * {
                color: #000000 !important;
                background: white !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                color-adjust: exact;
            }
            img {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
                max-width: 100% !important;
                height: auto !important;
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
            color: #000000 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            color-adjust: exact;
        }
        
        * {
            color: #000000 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
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
            color: #000000 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        
        .header .subtitle {
            font-size: 11px;
            margin-bottom: 5px;
            color: #000000 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
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
            color: #000000 !important;
        }
        
        .info-line {
            font-size: 11px;
            margin: 2px 0;
            color: #000000 !important;
        }
        
        .info-line strong {
            color: #000000 !important;
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
            color: #000000 !important;
        }
        
        .items-table td {
            padding: 2px 0;
            vertical-align: top;
            color: #000000 !important;
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
            color: #000000 !important;
            margin-left: 10px;
            font-weight: normal;
        }
        
        .totals {
            margin-top: 5px;
            border-top: 1px solid #000000;
            padding-top: 5px;
        }
        
        .total-line {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            margin: 2px 0;
            color: #000000 !important;
        }
        
        .total-final {
            border-top: 2px solid #000000;
            border-bottom: 2px solid #000000;
            padding: 3px 0;
            margin: 5px 0;
            font-weight: bold;
            font-size: 14px;
            color: #000000 !important;
        }
        
        .total-final span {
            color: #000000 !important;
        }
        
        .footer {
            text-align: center;
            margin-top: 10px;
            padding-top: 5px;
            border-top: 1px solid #000000;
            font-size: 10px;
            color: #000000 !important;
        }
        
        .footer div {
            color: #000000 !important;
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
        <button id="btn-print-escpos" onclick="printViaEscPos()">🖨️ Imprimir via ESC/POS (Melhor Qualidade)</button>
        <button onclick="window.print()">🖨️ Imprimir (Navegador)</button>
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
            <div class="info-line">{{ $order->address->street ?? $order->address->address ?? '' }}, {{ $order->address->number ?? '' }}</div>
            @if($order->address->complement)
            <div class="info-line">{{ $order->address->complement }}</div>
            @endif
            <div class="info-line">{{ $order->address->neighborhood ?? '' }}</div>
            <div class="info-line">{{ $order->address->city ?? '' }} - {{ $order->address->state ?? '' }}</div>
            @php
                $cep = $order->address->cep ?? $order->address->zip_code ?? $order->customer->zip_code ?? null;
            @endphp
            @if($cep)
            <div class="info-line">CEP: {{ $cep }}</div>
            @endif
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
                                
                                // Montar nome completo: Nome + Variante (se houver e não estiver já no custom_name) + Peso (se houver)
                                $displayName = $itemName;
                                // Verificar se a variante já está no custom_name (para pedidos novos)
                                $variantAlreadyInName = $variantName && strpos($itemName, '(' . $variantName . ')') !== false;
                                if ($variantName && !$variantAlreadyInName) {
                                    $displayName .= ' (' . $variantName . ')';
                                }
                                if ($weight) {
                                    $displayName .= ' - ' . number_format($weight / 1000, 1, ',', '.') . 'kg';
                                }
                            @endphp
                            {{ $displayName }}
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
                    @elseif($order->manual_discount_type)
                        DESCONTO {{ strtoupper($order->manual_discount_type === 'percentage' ? 'PERCENTUAL' : 'FIXO') }}:
                    @else
                        DESCONTO:
                    @endif
                </span>
                <span>-R$ {{ number_format($order->discount_amount, 2, ',', '.') }}</span>
            </div>
            @endif
            @if($order->cashback_used > 0)
            <div class="total-line">
                <span>CASHBACK UTILIZADO:</span>
                <span>-R$ {{ number_format($order->cashback_used, 2, ',', '.') }}</span>
            </div>
            @endif
            <div class="total-line total-final">
                <span>TOTAL:</span>
                <span>R$ {{ number_format($order->final_amount ?? $order->total_amount ?? 0, 2, ',', '.') }}</span>
            </div>
        </div>
        
        <div class="divider"></div>
        
        @if($order->scheduled_delivery_at)
        <div class="section">
            <div class="section-title">ENTREGA AGENDADA</div>
            <div class="info-line">
                <strong>{{ \Carbon\Carbon::parse($order->scheduled_delivery_at)->format('d/m/Y') }} às {{ \Carbon\Carbon::parse($order->scheduled_delivery_at)->format('H:i') }}</strong>
            </div>
        </div>
        @endif
        
        <div class="section">
            <div class="section-title">PAGAMENTO</div>
            <div class="info-line">
                <strong>{{ strtoupper(str_replace('_', ' ', $order->payment_method ?? 'PIX')) }}</strong>
            </div>
            <div class="info-line">
                STATUS: 
                @php
                    $paymentStatus = $order->payment_status ?? 'pending';
                    $orderStatus = $order->status ?? 'pending';
                    // Se o status do pedido for "confirmed" e payment_status ainda não estiver pago, considerar como pago
                    if ($orderStatus === 'confirmed' && ($paymentStatus === 'pending' || $paymentStatus === null)) {
                        $paymentStatus = 'paid';
                    }
                @endphp
                @if($paymentStatus === 'paid' || $paymentStatus === 'approved' || $orderStatus === 'confirmed')
                    PAGO
                @else
                    PENDENTE
                @endif
            </div>
            
            @if($order->payment_method === 'pix' && $order->pix_qr_base64 && ($paymentStatus === 'pending' || $paymentStatus === null))
            <div class="divider"></div>
            <div style="text-align: center; margin: 10px 0;">
                <div class="section-title" style="margin-bottom: 5px;">QR CODE PIX</div>
                <div style="display: inline-block; padding: 5px; border: 1px solid #000000;">
                    <img 
                        src="data:image/png;base64,{{ $order->pix_qr_base64 }}" 
                        alt="QR Code PIX" 
                        style="width: 120px; height: 120px; display: block; -webkit-print-color-adjust: exact; print-color-adjust: exact; color-adjust: exact;"
                    >
                </div>
                @if($order->pix_copy_paste)
                <div class="info-line" style="margin-top: 5px; font-size: 9px; word-break: break-all; text-align: left;">
                    <strong>PIX Copia e Cola:</strong><br>
                    {{ $order->pix_copy_paste }}
                </div>
                @endif
            </div>
            @endif
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
            <div style="margin-top: 5px;">pedido.menuolika.com.br</div>
            @if(isset($whatsappQrBase64) && $whatsappQrBase64)
            <div style="margin-top: 5px;">
                <img 
                    src="data:image/png;base64,{{ $whatsappQrBase64 }}" 
                    alt="WhatsApp QR Code" 
                    style="width: 60px; height: 60px; margin: 5px auto; display: block; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; color-adjust: exact !important;"
                >
            </div>
            @endif
            @php
                $settings = \App\Models\Setting::getSettings();
                $phone = $settings->business_phone ?? config('olika.business.phone', '(71) 98701-9420');
            @endphp
            <div style="margin-top: 5px;">WhatsApp: {{ $phone }}</div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/qz-tray@2.2/qz-tray.min.js"></script>
<script>
let qzConnected = false;

// Verificar se QZ Tray está conectado
function isQZTrayConnected() {
    try {
        return typeof qz !== 'undefined' && 
               qz !== null && 
               qz.websocket !== null && 
               qz.websocket.isActive();
    } catch (error) {
        return false;
    }
}

// Conectar ao QZ Tray
async function connectQZTray() {
    try {
        if (typeof qz === 'undefined' || qz === null) {
            throw new Error('QZ Tray não está carregado. Verifique se o QZ Tray está instalado e rodando.');
        }
        
        if (isQZTrayConnected()) {
            qzConnected = true;
            console.log('✅ QZ Tray já estava conectado');
            return true;
        }
        
        await qz.websocket.connect();
        
        if (isQZTrayConnected()) {
            qzConnected = true;
            console.log('✅ QZ Tray conectado com sucesso');
            return true;
        } else {
            throw new Error('Falha ao verificar conexão após tentativa de conexão');
        }
    } catch (error) {
        console.error('❌ Erro ao conectar QZ Tray:', error);
        qzConnected = false;
        return false;
    }
}


async function printViaEscPos() {
    const orderId = {{ $order->id }};
    const PRINTER_NAME = "EPSON TM-T20X";
    
    if (typeof qz === 'undefined') {
        alert('❌ QZ Tray não está carregado.');
        return;
    }
    
    if (!isQZTrayConnected()) {
        try {
            const connected = await connectQZTray();
            if (!connected) {
                alert('❌ Não foi possível conectar ao QZ Tray.');
                return;
            }
        } catch (error) {
            alert('❌ Erro ao conectar ao QZ Tray:\n\n' + error.message);
            return;
        }
    }
    
    try {
        const printers = await qz.printers.find();
        if (!printers || printers.length === 0) {
            alert('Nenhuma impressora encontrada.');
            return;
        }
        
        // Buscar impressora EPSON TM-20X
        const printer = printers.find(p => 
            p.toUpperCase().includes('EPSON') && 
            (p.toUpperCase().includes('TM-20') || p.toUpperCase().includes('TM-T20'))
        ) || printers[0];
        
        if (!printer) {
            alert(`❌ Impressora "${PRINTER_NAME}" não encontrada.\nVerifique se está conectada.`);
            return;
        }
        
        console.log('🖨️ Usando impressora:', printer);
        
        const response = await fetch(`/dashboard/orders/${orderId}/fiscal-receipt/escpos`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            }
        });
        
        if (!response.ok) {
            throw new Error(`Erro ao buscar dados: ${response.status}`);
        }
        
        const orderData = await response.json();
        if (!orderData.success || !orderData.data) {
            throw new Error('Dados inválidos do servidor.');
        }
        
        console.log('📦 Base64 recebido (ESC/POS), tamanho:', orderData.data.length);
        
        const printConfig = qz.configs.create(printer);
        
        // ✅ CORREÇÃO CRÍTICA: Usar format: 'base64' e enviar diretamente a string base64
        await qz.print(printConfig, [{
            type: 'raw',
            format: 'base64', // ✅ CORRETO: base64, não 'command'
            data: orderData.data // ✅ Enviar diretamente a string base64
        }]);
        
        console.log('✅ Recibo enviado para impressora:', printer);
        alert('✅ Recibo enviado para impressão com sucesso!');
        
    } catch (error) {
        console.error('❌ Erro ao imprimir:', error);
        alert('❌ Erro ao imprimir: ' + (error.message || 'Erro desconhecido'));
    }
}


@if(request()->get('auto_print'))
window.onload = async function () {
    setTimeout(async () => {
        await connectQZTray();
        await printViaEscPos();
    }, 500); // Pequeno atraso para garantir QZ Tray pronto
};
@endif
</script>
</body>
</html>
