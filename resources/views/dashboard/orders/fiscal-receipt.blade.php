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
            <div style="margin-top: 5px;">www.menuolika.com.br</div>
            <div style="margin-top: 5px;">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=80x80&data=https://wa.me/5571987019420" alt="WhatsApp QR Code" style="width: 60px; height: 60px; margin: 5px auto; display: block;">
            </div>
            <div style="margin-top: 5px;">WhatsApp: (71) 98701-9420</div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/qz-tray@2.2/qz-tray.min.js"></script>
    <script>
        let qzConnected = false;
        
        // Verificar se QZ Tray está realmente conectado
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
        
        // Conectar ao QZ Tray (mesma lógica do monitor que funciona)
        async function connectQZTray() {
            try {
                // Verificar se o objeto qz existe
                if (typeof qz === 'undefined' || qz === null) {
                    throw new Error('QZ Tray não está carregado. Verifique se o QZ Tray está instalado e rodando.');
                }

                // Verificar se já está conectado
                if (isQZTrayConnected()) {
                    qzConnected = true;
                    console.log('✅ QZ Tray já estava conectado');
                    return true;
                }

                // Tentar conectar
                await qz.websocket.connect();
                
                // Verificar novamente se realmente conectou
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
                throw error; // Re-throw para tratamento no chamador
            }
        }
        
        // Imprimir via ESC/POS (melhor qualidade)
        async function printViaEscPos() {
            const orderId = {{ $order->id }};
            
            // Verificar se QZ Tray está disponível
            if (typeof qz === 'undefined') {
                alert('❌ QZ Tray não está carregado. Certifique-se de que:\n\n1. O QZ Tray está instalado\n2. O QZ Tray está rodando\n3. Você permitiu o acesso no navegador');
                return;
            }
            
            // Verificar/conectar ao QZ Tray
            if (!isQZTrayConnected()) {
                try {
                    const connected = await connectQZTray();
                    if (!connected) {
                        alert('❌ Não foi possível conectar ao QZ Tray. Certifique-se de que o QZ Tray está instalado e rodando.');
                        return;
                    }
                } catch (error) {
                    alert('❌ Erro ao conectar ao QZ Tray:\n\n' + error.message + '\n\nCertifique-se de que:\n1. O QZ Tray está instalado\n2. O QZ Tray está rodando\n3. Você permitiu o acesso no navegador');
                    return;
                }
            }
            
            try {
                // Obter impressoras
                const printers = await qz.printers.find();
                if (!printers || printers.length === 0) {
                    alert('Nenhuma impressora encontrada. Configure o QZ Tray.');
                    return;
                }
                
                // Se houver múltiplas impressoras, permitir seleção
                let printer;
                if (printers.length > 1) {
                    const printerNames = printers.map((p, i) => `${i + 1}. ${p}`);
                    const selected = prompt(`Selecione a impressora:\n\n${printerNames.join('\n')}\n\nDigite o número:`, '1');
                    const index = parseInt(selected) - 1;
                    if (isNaN(index) || index < 0 || index >= printers.length) {
                        alert('Seleção inválida. Usando primeira impressora.');
                        printer = printers[0];
                    } else {
                        printer = printers[index];
                    }
                } else {
                    printer = printers[0];
                }
                
                console.log('🖨️ Usando impressora:', printer);
                
                // Buscar dados ESC/POS do backend
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
                    throw new Error('Dados inválidos recebidos do servidor');
                }
                
                // Função auxiliar para converter base64 → Uint8Array
                // (qz.util.decodeBase64ToUint8Array não existe na biblioteca QZ Tray)
                function base64ToUint8Array(base64) {
                    const binaryString = atob(base64);
                    const len = binaryString.length;
                    const bytes = new Uint8Array(len);
                    for (let i = 0; i < len; i++) {
                        bytes[i] = binaryString.charCodeAt(i);
                    }
                    return bytes;
                }
                
                // Converter base64 para Uint8Array
                const rawData = base64ToUint8Array(orderData.data);
                
                // Verificar se os primeiros bytes são ESC @
                if (rawData.length < 2 || rawData[0] !== 0x1B || rawData[1] !== 0x40) {
                    console.error('❌ ERRO CRÍTICO: Dados não começam com ESC @ (0x1B 0x40)');
                    console.error('❌ Primeiros bytes:', 
                        Array.from(rawData.slice(0, 10)).map(b => '0x' + b.toString(16).padStart(2, '0')).join(' '));
                    alert('❌ Erro: Dados ESC/POS inválidos. Verifique o console para mais detalhes.');
                    return;
                }
                
                console.log('📦 Dados ESC/POS validados:', {
                    printer: printer,
                    bytesLength: rawData.length,
                    firstBytes: Array.from(rawData.slice(0, 10)),
                    isValidEscPos: rawData[0] === 0x1B && rawData[1] === 0x40
                });
                
                console.log('🚀 Preparando envio RAW para impressora...');
                
                console.log('📦 Dados decodificados para Uint8Array:', {
                    length: rawData.length,
                    firstBytes: Array.from(rawData.slice(0, 10)),
                    isValidEscPos: rawData[0] === 0x1B && rawData[1] === 0x40
                });
                
                // Configurar impressão
                const printConfig = qz.configs.create(printer);
                
                // Enviar como objeto RAW com formato command
                // Formato correto para dados ESC/POS binários
                await qz.print(printConfig, [{
                    type: 'raw',
                    format: 'command',
                    data: rawData
                }]);
                
                console.log('✅ Dados RAW enviados com sucesso');
                
                // Verificar se a impressora é virtual (PDF) - isso pode ser o problema
                const printerLower = printer.toLowerCase();
                if (printerLower.includes('pdf') || printerLower.includes('virtual') || printerLower.includes('document')) {
                    alert('⚠️ ATENÇÃO: Você selecionou uma impressora virtual (PDF/Documentos).\n\nSelecione a impressora térmica física para imprimir o recibo.');
                } else {
                    alert('✅ Recibo enviado para impressão com sucesso!\n\nSe não imprimiu, verifique:\n1. A impressora está ligada e com papel\n2. A impressora não está em modo "Pausa"\n3. Verifique a fila de impressão do Windows');
                }
            } catch (error) {
                console.error('Erro ao imprimir:', error);
                console.error('Stack:', error.stack);
                alert('❌ Erro ao imprimir: ' + (error.message || 'Erro desconhecido') + '\n\nVerifique o console do navegador (F12) para mais detalhes.');
            }
        }
        
        // Auto-imprimir se for impressão automática
        @if(request()->get('auto_print'))
        window.onload = function() {
            // Aguardar um pouco mais para garantir que o CSS foi aplicado
            setTimeout(function() {
                // Forçar atualização de estilos antes de imprimir
                document.body.style.display = 'none';
                document.body.offsetHeight; // Trigger reflow
                document.body.style.display = '';
                setTimeout(function() {
                    window.print();
                }, 100);
            }, 500);
        };
        @endif
    </script>
</body>
</html>
