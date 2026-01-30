@extends('dashboard.layouts.app')

@section('title', 'Monitor de Impressão - OLIKA Painel')

@section('content')
    <div class="space-y-6 animate-in fade-in duration-500">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-3xl font-bold tracking-tight">Monitor de Impressão</h1>
                <p class="text-muted-foreground">Impressão automática de recibos fiscais</p>
            </div>
            <div class="flex gap-2">
                <button id="btn-test-print"
                    class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="lucide lucide-printer">
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                        <path d="M6 9V3a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v6"></path>
                        <rect x="6" y="14" width="12" height="8"></rect>
                    </svg>
                    Testar Impressora
                </button>
                <button id="btn-toggle-monitor"
                    class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-success text-success-foreground hover:bg-success/90 h-10 px-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="lucide lucide-power">
                        <path d="M12 2v10"></path>
                        <path d="M18.4 6.6a9 9 0 1 1-12.77.04"></path>
                    </svg>
                    <span id="monitor-status-text">Iniciar Monitor</span>
                </button>
            </div>
        </div>

        <!-- Status Card -->
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 rounded-full flex items-center justify-center" id="status-indicator"
                            style="background: #e5e7eb;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="text-gray-400">
                                <circle cx="12" cy="12" r="10"></circle>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold" id="status-title">Monitor Desligado</h3>
                            <p class="text-sm text-muted-foreground" id="status-description">Clique em "Iniciar Monitor"
                                para começar a imprimir automaticamente</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-2xl font-bold" id="printed-count">0</div>
                        <div class="text-sm text-muted-foreground">Recibos Impressos</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Configurações -->
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="flex flex-col space-y-1.5 p-6">
                <h3 class="text-lg font-semibold">Configurações</h3>
            </div>
            <div class="p-6 pt-0 space-y-4">
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="space-y-2">
                        <label class="text-sm font-medium" for="printer-name">Nome da Impressora</label>
                        <input type="text" id="printer-name"
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                            placeholder="Ex: EPSON TM-T20X Receipt">
                        <p class="text-xs text-muted-foreground">Deixe vazio para detectar automaticamente a EPSON TM-20X
                            Receipt</p>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium" for="poll-interval">Intervalo de Verificação (segundos)</label>
                        <input type="number" id="poll-interval" min="1" max="60" value="3"
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
                        <p class="text-xs text-muted-foreground">Valor recomendado: 3 segundos para resposta rápida</p>
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium flex items-center gap-2">
                        <input type="checkbox" id="auto-print-new" checked>
                        <span>Imprimir automaticamente pedidos novos</span>
                    </label>
                    <label class="text-sm font-medium flex items-center gap-2">
                        <input type="checkbox" id="auto-print-paid" checked>
                        <span>Imprimir automaticamente quando pagamento for confirmado</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Últimos Pedidos Processados -->
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="flex flex-col space-y-1.5 p-6">
                <h3 class="text-lg font-semibold">Últimos Pedidos Processados</h3>
            </div>
            <div class="p-6 pt-0">
                <div id="orders-log" class="space-y-2 max-h-96 overflow-y-auto">
                    <p class="text-sm text-muted-foreground text-center py-4">Nenhum pedido processado ainda</p>
                </div>
            </div>
        </div>
    </div>

    <!-- QZ Tray Script -->
    <script src="https://cdn.jsdelivr.net/npm/qz-tray@2.2/qz-tray.min.js"></script>

    <script>
        let monitorActive = false;
        let pollInterval = null;
        let printedCount = 0;
        let processedOrders = new Set();
        let qzConnected = false;

        // Configurações
        let config = {
            printer: localStorage.getItem('printer_name') || '',
            pollInterval: parseInt(localStorage.getItem('poll_interval') || '3') * 1000, // Padrão: 3 segundos
            autoPrintNew: localStorage.getItem('auto_print_new') !== 'false',
            autoPrintPaid: localStorage.getItem('auto_print_paid') !== 'false',
        };

        // Atualizar campos de configuração
        document.getElementById('printer-name').value = config.printer;
        document.getElementById('poll-interval').value = config.pollInterval / 1000;
        document.getElementById('auto-print-new').checked = config.autoPrintNew;
        document.getElementById('auto-print-paid').checked = config.autoPrintPaid;

        // Salvar configurações
        document.getElementById('printer-name').addEventListener('change', function () {
            config.printer = this.value;
            localStorage.setItem('printer_name', this.value);
        });

        document.getElementById('poll-interval').addEventListener('change', function () {
            config.pollInterval = parseInt(this.value) * 1000;
            localStorage.setItem('poll_interval', this.value);
        });

        document.getElementById('auto-print-new').addEventListener('change', function () {
            config.autoPrintNew = this.checked;
            localStorage.setItem('auto_print_new', this.checked);
        });

        document.getElementById('auto-print-paid').addEventListener('change', function () {
            config.autoPrintPaid = this.checked;
            localStorage.setItem('auto_print_paid', this.checked);
        });

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

        // Conectar ao QZ Tray
        async function connectQZTray() {
            try {
                // Verificar se o objeto qz existe
                if (typeof qz === 'undefined' || qz === null) {
                    throw new Error('QZ Tray não está carregado. Verifique se o QZ Tray está instalado e rodando.');
                }

                // Verificar se já está conectado
                if (isQZTrayConnected()) {
                    qzConnected = true;
                    updateStatus('connected', 'QZ Tray Conectado', 'Pronto para imprimir recibos fiscais');
                    console.log('✅ QZ Tray já estava conectado');
                    return true;
                }

                // Tentar conectar
                await qz.websocket.connect();

                // Verificar novamente se realmente conectou
                if (isQZTrayConnected()) {
                    qzConnected = true;
                    updateStatus('connected', 'QZ Tray Conectado', 'Pronto para imprimir recibos fiscais');
                    console.log('✅ QZ Tray conectado com sucesso');
                    return true;
                } else {
                    throw new Error('Falha ao verificar conexão após tentativa de conexão');
                }
            } catch (error) {
                console.error('❌ Erro ao conectar QZ Tray:', error);
                qzConnected = false;
                updateStatus('error', 'Erro de Conexão', 'QZ Tray não está rodando ou não pode ser acessado. Certifique-se de que o QZ Tray está instalado e rodando.');
                return false;
            }
        }

        // Atualizar status visual
        function updateStatus(status, title, description) {
            const indicator = document.getElementById('status-indicator');
            const titleEl = document.getElementById('status-title');
            const descEl = document.getElementById('status-description');

            titleEl.textContent = title;
            descEl.textContent = description;

            switch (status) {
                case 'connected':
                    indicator.style.background = '#10b981';
                    break;
                case 'monitoring':
                    indicator.style.background = '#3b82f6';
                    break;
                case 'error':
                    indicator.style.background = '#ef4444';
                    break;
                default:
                    indicator.style.background = '#e5e7eb';
            }
        }

        // Buscar novos pedidos via API
        async function checkNewOrders() {
            if (!monitorActive) return;

            try {
                // Buscar últimos pedidos que ainda não foram impressos via API JSON
                // Usar URL absoluta para garantir que funcione em qualquer contexto
                const apiUrl = '/orders/orders-for-print?payment_status=paid';
                console.log('Buscando pedidos para impressão:', apiUrl);

                const response = await fetch(apiUrl, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    }
                });

                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('Resposta do servidor:', errorText);
                    throw new Error(`Erro ao buscar pedidos: ${response.status} ${response.statusText}`);
                }

                const data = await response.json();

                console.log('📦 Resposta da API:', data);

                if (!data.success || !data.orders) {
                    console.warn('⚠️ Resposta inválida da API:', data);
                    return;
                }

                console.log(`📋 ${data.orders.length} pedido(s) encontrado(s) para verificação`);

                if (data.orders.length === 0) {
                    console.log('ℹ️ Nenhum pedido novo para imprimir');
                    return;
                }

                // Processar apenas pedidos que ainda não foram impressos
                // Processar em paralelo para não bloquear outros pedidos
                const printPromises = [];

                for (const orderInfo of data.orders) {
                    const orderId = orderInfo.id;

                    console.log(`🔍 Verificando pedido #${orderInfo.order_number} (ID: ${orderId})`, {
                        status: orderInfo.status,
                        payment_status: orderInfo.payment_status,
                        print_requested: orderInfo.print_requested_at || false,
                        already_printed: orderInfo.printed_at || false
                    });

                    // Pular se já foi impresso (marcado no servidor)
                    if (orderInfo.printed_at) {
                        console.log(`⏭️ Pedido #${orderInfo.order_number} já foi impresso anteriormente, pulando`);
                        processedOrders.add(orderId); // Marcar como processado para não verificar novamente
                        continue;
                    }

                    // Se já foi processado com SUCESSO nesta sessão, pular
                    // Mas verificar novamente se falhou antes
                    if (processedOrders.has(orderId)) {
                        console.log(`⏭️ Pedido #${orderInfo.order_number} já foi processado com sucesso nesta sessão, pulando`);
                        continue;
                    }

                    // Processar este pedido (não bloquear outros)
                    printPromises.push((async () => {
                        try {
                            // Buscar detalhes completos do pedido (incluindo dados ESC/POS)
                            const printType = orderInfo.print_type || 'normal';
                            const order = await fetchOrderDetails(orderId, printType);

                            if (!order) {
                                console.error(`❌ Falha ao buscar detalhes do pedido #${orderInfo.order_number}`);
                                return;
                            }

                            // Verificar se os dados estão válidos
                            if (!order.success || !order.data) {
                                console.error(`❌ Dados inválidos para pedido #${orderInfo.order_number}`, order);
                                return;
                            }

                            if (order && shouldPrintOrder(order)) {
                                // Passar print_type para a função printOrder
                                order.print_type = orderInfo.print_type || 'normal';
                                const printed = await printOrder(order);
                                if (printed) {
                                    processedOrders.add(orderId);
                                    printedCount++;
                                    document.getElementById('printed-count').textContent = printedCount;
                                    addOrderToLog(order);
                                } else {
                                    console.error(`❌ Falha ao imprimir pedido #${orderInfo.order_number}`);
                                }
                            } else {
                                console.log(`⏭️ Pedido #${orderInfo.order_number} não deve ser impresso (shouldPrintOrder retornou false)`);
                            }
                        } catch (error) {
                            console.error(`❌ Erro ao processar pedido #${orderInfo.order_number}:`, error);
                            // Não adicionar a processedOrders para tentar novamente
                        }
                    })());
                }

                // Aguardar todos os processamentos (mas não bloquear o próximo ciclo)
                if (printPromises.length > 0) {
                    console.log(`⏳ Processando ${printPromises.length} pedido(s) em paralelo...`);
                    const results = await Promise.allSettled(printPromises);

                    const successful = results.filter(r => r.status === 'fulfilled').length;
                    const failed = results.filter(r => r.status === 'rejected').length;

                    console.log(`📊 Resultado: ${successful} sucesso, ${failed} falhas`);
                } else {
                    console.log('ℹ️ Nenhum pedido novo para processar');
                }
            } catch (error) {
                console.error('Erro ao verificar pedidos:', error);
                updateStatus('error', 'Erro ao Buscar Pedidos', error.message || 'Erro desconhecido');
            }
        }

        // Buscar detalhes de um pedido
        async function fetchOrderDetails(orderId, printType = 'normal') {
            try {
                const endpoint = printType === 'check' ? 'check-receipt' : 'fiscal-receipt';
                const url = `/orders/${orderId}/${endpoint}/escpos`;

                const response = await fetch(url, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    }
                });

                if (!response.ok) {
                    throw new Error(`Erro ao buscar pedido: ${response.status} ${response.statusText}`);
                }

                const data = await response.json();
                return data;
            } catch (error) {
                console.error(`Erro ao buscar pedido ${orderId}:`, error);
                return null;
            }
        }

        // Verificar se deve imprimir o pedido
        function shouldPrintOrder(order) {
            // Lógica: imprimir se for novo ou se pagamento foi confirmado
            // Aqui você precisa ajustar baseado na estrutura do seu retorno
            return true; // Simplificado - ajustar conforme necessário
        }

        // Imprimir pedido via QZ Tray
        async function printOrder(orderData) {
            const PRINTER_NAME = "EPSON TM-T20X";

            console.log('📄 Iniciando impressão do pedido:', orderData);

            // Verificar conexão antes de tentar imprimir
            if (!isQZTrayConnected()) {
                console.log('🔄 Reconectando ao QZ Tray...');
                const connected = await connectQZTray();
                if (!connected) {
                    console.error('❌ Não foi possível conectar ao QZ Tray para imprimir');
                    updateStatus('error', 'Erro de Conexão', 'QZ Tray não conectado');
                    return false;
                }
            }

            try {
                // Obter lista de impressoras
                console.log('🔍 Buscando impressoras disponíveis...');
                const printers = await qz.printers.find();
                console.log('📋 Impressoras encontradas:', printers);

                if (!printers || printers.length === 0) {
                    throw new Error('Nenhuma impressora encontrada. Configure o QZ Tray.');
                }

                // Buscar impressora EPSON TM-20X
                let printer = config.printer;
                if (!printer) {
                    const epsonPrinter = printers.find(p =>
                        p.toUpperCase().includes('EPSON') &&
                        (p.toUpperCase().includes('TM-20') || p.toUpperCase().includes('TM-T20'))
                    );
                    printer = epsonPrinter || printers[0];
                }

                if (!printer) {
                    console.error(`❌ Impressora "${PRINTER_NAME}" não encontrada`);
                    updateStatus('error', 'Impressora Não Encontrada', `Impressora ${PRINTER_NAME} não encontrada`);
                    return false;
                }

                console.log('🖨️ Usando impressora:', printer);

                // Buscar dados ESC/POS do servidor (se não vierem no orderData)
                let base64Data = orderData.data;
                if (!base64Data && orderData.order_id) {
                    const printType = orderData.print_type || 'normal';
                    const endpoint = printType === 'check' ? 'check-receipt' : 'fiscal-receipt';
                    const url = `/orders/${orderData.order_id || orderData.id}/${endpoint}/escpos`;

                    const response = await fetch(url, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        }
                    });

                    if (!response.ok) {
                        throw new Error(`Erro ao buscar dados ESC/POS (${response.status})`);
                    }

                    const json = await response.json();
                    if (!json.success || !json.data) {
                        throw new Error('Dados inválidos ou ausentes');
                    }

                    base64Data = json.data;
                }

                if (!base64Data) {
                    throw new Error('Dados ESC/POS não encontrados');
                }

                console.log('📦 Base64 recebido (ESC/POS), tamanho:', base64Data.length);

                // Configurar impressão
                const printConfig = qz.configs.create(printer);

                // ✅ CORREÇÃO CRÍTICA: Usar format: 'base64' e enviar diretamente a string base64
                await qz.print(printConfig, [{
                    type: 'raw',
                    format: 'base64', // ✅ CORRETO: base64, não 'command'
                    data: base64Data  // ✅ Enviar diretamente a string base64
                }]);

                console.log('✅ Pedido impresso com sucesso:', orderData.order_number || orderData.order_id);

                // Marcar pedido como impresso no servidor
                const orderId = orderData.order_id || orderData.id;
                if (orderId) {
                    try {
                        const response = await fetch(`/orders/${orderId}/mark-printed`, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                            }
                        });

                        const result = await response.json();
                        if (result.success) {
                            console.log('✅ Pedido marcado como impresso no servidor');
                        } else {
                            console.warn('⚠️ Falha ao marcar pedido como impresso:', result.message);
                        }
                    } catch (err) {
                        console.error('❌ Erro ao marcar pedido como impresso:', err);
                    }
                }

                // Aguardar um pouco para garantir que a impressão foi processada
                await new Promise(resolve => setTimeout(resolve, 500));

                updateStatus('monitoring', 'Monitor Ativo', 'Última impressão: ' + new Date().toLocaleTimeString('pt-BR'));

                console.log('✅ Impressão concluída para pedido:', orderData.order_number || orderData.order_id);
                return true;

            } catch (error) {
                console.error('❌ Erro ao imprimir:', error);
                console.error('❌ Stack trace:', error.stack);
                updateStatus('error', 'Erro de Impressão', error.message || 'Erro desconhecido');

                // Se foi erro de conexão, marcar como desconectado
                if (error.message && (error.message.includes('WebSocket') || error.message.includes('connection'))) {
                    qzConnected = false;
                }

                return false;
            }
        }

        // Testar impressora
        document.getElementById('btn-test-print').addEventListener('click', async function () {
            // Verificar conexão antes de tentar imprimir
            if (!isQZTrayConnected()) {
                const connected = await connectQZTray();
                if (!connected) {
                    alert('❌ Não foi possível conectar ao QZ Tray. Certifique-se de que o QZ Tray está instalado e rodando.');
                    return;
                }
            }

            try {
                const printers = await qz.printers.find();
                if (!printers || printers.length === 0) {
                    alert('Nenhuma impressora encontrada. Configure o QZ Tray.');
                    return;
                }

                // Usar impressora configurada, ou procurar EPSON TM-20X Receipt, ou usar primeira disponível
                let printer = config.printer;
                if (!printer) {
                    // Procurar especificamente por EPSON TM-20X Receipt
                    const epsonPrinter = printers.find(p =>
                        p.toLowerCase().includes('epson') &&
                        (p.toLowerCase().includes('tm-20') || p.toLowerCase().includes('tm-t20'))
                    );
                    printer = epsonPrinter || printers[0];
                }
                console.log('Imprimindo teste na impressora:', printer);

                // Criar dados de teste em ESC/POS
                const testData = '\x1B\x40' + // Reset
                    '\x1B\x61\x01' + // Centralizar
                    'TESTE DE IMPRESSAO\n' +
                    '\x1B\x61\x00' + // Alinhar à esquerda
                    'OLIKA - PAES ARTESANAIS\n' +
                    'Data: ' + new Date().toLocaleString('pt-BR') + '\n' +
                    '\n\n\n' +
                    '\x1D\x56\x41\x03'; // Cortar

                // Converter string para array de bytes (array JavaScript simples)
                const bytes = [];
                for (let i = 0; i < testData.length; i++) {
                    bytes.push(testData.charCodeAt(i));
                }

                // Configurar impressão (sem opções especiais para dados raw)
                const testConfig = qz.configs.create(printer);

                console.log('Enviando teste de impressão...', { printer, bytesLength: bytes.length });

                // Enviar dados raw para impressora (QZ Tray espera array JavaScript, não Uint8Array)
                await qz.print(testConfig, bytes);

                alert('✅ Teste de impressão enviado com sucesso!');
                updateStatus('connected', 'QZ Tray Conectado', 'Teste enviado: ' + new Date().toLocaleTimeString('pt-BR'));
            } catch (error) {
                console.error('Erro ao testar:', error);
                alert('❌ Erro ao testar impressão: ' + (error.message || 'Erro desconhecido'));
                updateStatus('error', 'Erro no Teste', error.message || 'Erro desconhecido');

                // Se foi erro de conexão, marcar como desconectado
                if (error.message && (error.message.includes('WebSocket') || error.message.includes('connection'))) {
                    qzConnected = false;
                }
            }
        });

        // Toggle monitor
        document.getElementById('btn-toggle-monitor').addEventListener('click', async function () {
            if (!monitorActive) {
                const connected = await connectQZTray();
                if (!connected) return;

                monitorActive = true;
                this.classList.remove('bg-success');
                this.classList.add('bg-destructive', 'text-destructive-foreground');
                document.getElementById('monitor-status-text').textContent = 'Parar Monitor';

                updateStatus('monitoring', 'Monitor Ativo', 'Imprimindo recibos automaticamente...');

                // Iniciar polling
                pollInterval = setInterval(checkNewOrders, config.pollInterval);
                checkNewOrders(); // Verificar imediatamente
            } else {
                monitorActive = false;
                if (pollInterval) {
                    clearInterval(pollInterval);
                    pollInterval = null;
                }

                this.classList.remove('bg-destructive', 'text-destructive-foreground');
                this.classList.add('bg-success');
                document.getElementById('monitor-status-text').textContent = 'Iniciar Monitor';

                updateStatus('connected', 'Monitor Parado', 'Clique em "Iniciar Monitor" para continuar');
            }
        });

        // Adicionar pedido ao log
        function addOrderToLog(order) {
            const log = document.getElementById('orders-log');
            if (log.querySelector('p.text-center')) {
                log.innerHTML = '';
            }

            const logEntry = document.createElement('div');
            logEntry.className = 'flex items-center justify-between p-3 border rounded-lg';
            logEntry.innerHTML = `
            <div>
                <div class="font-medium">Pedido #${order.order_number || 'N/A'}</div>
                <div class="text-xs text-muted-foreground">${new Date().toLocaleString('pt-BR')}</div>
            </div>
            <div class="text-green-600 font-medium">✓ Impresso</div>
        `;

            log.insertBefore(logEntry, log.firstChild);

            // Manter apenas últimos 20
            while (log.children.length > 20) {
                log.removeChild(log.lastChild);
            }
        }

        // Verificar conexão periodicamente
        setInterval(async function () {
            if (monitorActive && !isQZTrayConnected()) {
                console.warn('⚠️ Conexão perdida, tentando reconectar...');
                qzConnected = false;
                await connectQZTray();
            }
        }, 5000); // Verificar a cada 5 segundos

        // Função para iniciar o monitor automaticamente
        async function startMonitorAuto() {
            console.log('🔄 Tentando iniciar monitor automaticamente...');

            // Tentar conectar ao QZ Tray
            let connected = false;
            let attempts = 0;
            const maxAttempts = 5;

            while (!connected && attempts < maxAttempts) {
                attempts++;
                console.log(`Tentativa ${attempts}/${maxAttempts} de conexão com QZ Tray...`);
                connected = await connectQZTray();

                if (!connected) {
                    console.log(`Aguardando 2 segundos antes da próxima tentativa...`);
                    await new Promise(resolve => setTimeout(resolve, 2000));
                }
            }

            if (connected) {
                // Iniciar monitor automaticamente
                monitorActive = true;
                const toggleBtn = document.getElementById('btn-toggle-monitor');
                if (toggleBtn) {
                    toggleBtn.classList.remove('bg-success');
                    toggleBtn.classList.add('bg-destructive', 'text-destructive-foreground');
                    const statusText = document.getElementById('monitor-status-text');
                    if (statusText) {
                        statusText.textContent = 'Parar Monitor';
                    }
                }

                updateStatus('monitoring', 'Monitor Ativo', 'Imprimindo recibos automaticamente...');

                // Iniciar polling
                if (pollInterval) {
                    clearInterval(pollInterval);
                }
                pollInterval = setInterval(checkNewOrders, config.pollInterval);
                checkNewOrders(); // Verificar imediatamente

                // Verificar novamente após 1 segundo para garantir resposta rápida
                setTimeout(checkNewOrders, 1000);

                console.log('✅ Monitor iniciado automaticamente');
            } else {
                console.warn('⚠️ Não foi possível conectar ao QZ Tray. Monitor não iniciado automaticamente.');
                updateStatus('error', 'QZ Tray não conectado', 'Clique em "Iniciar Monitor" após verificar o QZ Tray');
            }
        }

        // Iniciar automaticamente quando a página carregar
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function () {
                setTimeout(startMonitorAuto, 1000);
            });
        } else {
            // DOM já carregado
            setTimeout(startMonitorAuto, 1000);
        }

        // Também tentar iniciar quando a página ganhar foco (se ainda não estiver ativo)
        document.addEventListener('visibilitychange', function () {
            if (!document.hidden && !monitorActive) {
                console.log('Página visível novamente, tentando iniciar monitor...');
                setTimeout(startMonitorAuto, 500);
            }
        });

        // Manter monitor ativo mesmo quando a página não está em foco
        // Não parar o polling quando a página perde foco
    </script>
@endsection