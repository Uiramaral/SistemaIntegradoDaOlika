@extends('dashboard.layouts.app')

@section('page_title', 'Configurações de Impressão')
@section('page_subtitle', 'Configure impressoras e formatos de recibos')

@section('content')
<div class="space-y-6">
    <!-- Tipo de Impressora -->
    <div class="bg-card rounded-xl border border-border shadow-sm">
        <div class="p-6 border-b border-border">
            <h2 class="text-lg font-semibold flex items-center gap-2">
                <i data-lucide="printer" class="w-5 h-5"></i>
                Tipo de Impressora
            </h2>
            <p class="text-sm text-muted-foreground mt-1">Selecione o tipo de impressora que você utiliza</p>
        </div>
        <div class="p-6">
            <form id="printer-type-form" method="POST" action="{{ route('dashboard.settings.printing.update') }}">
                @csrf
                <div class="grid gap-4 md:grid-cols-2">
                    <!-- Impressora Fiscal Térmica -->
                    <div class="printer-type-card {{ $settings->printer_type === 'thermal' ? 'selected' : '' }}" data-type="thermal">
                        <input type="radio" name="printer_type" value="thermal" id="thermal" 
                               {{ $settings->printer_type === 'thermal' ? 'checked' : '' }} class="hidden">
                        <label for="thermal" class="block cursor-pointer">
                            <div class="p-6 border-2 rounded-xl transition-all hover:border-primary">
                                <div class="text-4xl mb-4">🖨️</div>
                                <h3 class="font-semibold mb-2">Impressora Fiscal Térmica</h3>
                                <p class="text-sm text-muted-foreground mb-3">
                                    Impressoras como EPSON TM-T20X, Bematech MP-4200 TH, Elgin i9
                                </p>
                                <div class="flex flex-wrap gap-2">
                                    <span class="text-xs px-2 py-1 bg-green-100 text-green-800 rounded-full">✓ Recibos térmicos</span>
                                    <span class="text-xs px-2 py-1 bg-green-100 text-green-800 rounded-full">✓ 58mm/80mm</span>
                                    <span class="text-xs px-2 py-1 bg-green-100 text-green-800 rounded-full">✓ ESC/POS</span>
                                </div>
                            </div>
                        </label>
                    </div>

                    <!-- Impressora Comum A4 -->
                    <div class="printer-type-card {{ $settings->printer_type === 'regular' ? 'selected' : '' }}" data-type="regular">
                        <input type="radio" name="printer_type" value="regular" id="regular" 
                               {{ $settings->printer_type === 'regular' ? 'checked' : '' }} class="hidden">
                        <label for="regular" class="block cursor-pointer">
                            <div class="p-6 border-2 rounded-xl transition-all hover:border-primary">
                                <div class="text-4xl mb-4">🖨</div>
                                <h3 class="font-semibold mb-2">Impressora Comum (A4)</h3>
                                <p class="text-sm text-muted-foreground mb-3">
                                    Impressoras jato de tinta ou laser tradicionais
                                </p>
                                <div class="flex flex-wrap gap-2">
                                    <span class="text-xs px-2 py-1 bg-blue-100 text-blue-800 rounded-full">✓ Papel A4</span>
                                    <span class="text-xs px-2 py-1 bg-blue-100 text-blue-800 rounded-full">✓ Múltiplas cópias</span>
                                    <span class="text-xs px-2 py-1 bg-blue-100 text-blue-800 rounded-full">✓ Colorido</span>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Configurações Específicas para Impressora Comum -->
                <div id="regular-settings" class="mt-6 p-6 bg-muted/30 rounded-xl {{ $settings->printer_type !== 'regular' ? 'hidden' : '' }}">
                    <h3 class="font-semibold mb-4 flex items-center gap-2">
                        <i data-lucide="settings" class="w-4 h-4"></i>
                        Configurações de Impressora Comum
                    </h3>
                    
                    <div class="grid gap-4 md:grid-cols-3">
                        <div>
                            <label class="block text-sm font-medium mb-2">Tamanho do Recibo</label>
                            <select name="receipt_size" class="w-full px-3 py-2 border border-border rounded-lg bg-background">
                                <option value="half-page" {{ $settings->receipt_size === 'half-page' ? 'selected' : '' }}>Meia Página A4 (14,8 x 21cm)</option>
                                <option value="quarter-page" {{ $settings->receipt_size === 'quarter-page' ? 'selected' : '' }}>1/4 de Página A4 (10,5 x 14,8cm)</option>
                                <option value="80mm" {{ $settings->receipt_size === '80mm' ? 'selected' : '' }}>80mm de largura</option>
                                <option value="full-page" {{ $settings->receipt_size === 'full-page' ? 'selected' : '' }}>Página Inteira A4</option>
                            </select>
                            <p class="text-xs text-muted-foreground mt-1">Tamanho recomendado: Meia Página</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">Número de Cópias</label>
                            <input type="number" name="default_copies" min="1" max="5" 
                                   value="{{ $settings->default_copies ?? 1 }}"
                                   class="w-full px-3 py-2 border border-border rounded-lg bg-background">
                            <p class="text-xs text-muted-foreground mt-1">Padrão: 1 cópia</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">Orientação</label>
                            <select name="page_orientation" class="w-full px-3 py-2 border border-border rounded-lg bg-background">
                                <option value="portrait" {{ $settings->page_orientation === 'portrait' ? 'selected' : '' }}>Retrato (vertical)</option>
                                <option value="landscape" {{ $settings->page_orientation === 'landscape' ? 'selected' : '' }}>Paisagem (horizontal)</option>
                            </select>
                            <p class="text-xs text-muted-foreground mt-1">Recomendado: Retrato</p>
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2 mt-4">
                        <div>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="show_logo" value="1" 
                                       {{ $settings->show_logo ? 'checked' : '' }}
                                       class="rounded border-border">
                                <span class="text-sm">Incluir logotipo no recibo</span>
                            </label>
                        </div>
                        <div>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="show_qrcode" value="1" 
                                       {{ $settings->show_qrcode ? 'checked' : '' }}
                                       class="rounded border-border">
                                <span class="text-sm">Incluir QR Code do WhatsApp</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" id="btn-test-print" class="btn-outline">
                        <i data-lucide="eye" class="w-4 h-4"></i>
                        Visualizar Exemplo
                    </button>
                    <button type="submit" class="btn-primary">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        Salvar Configurações
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Recibo de Conferência -->
    <div class="bg-card rounded-xl border border-border shadow-sm">
        <div class="p-6 border-b border-border">
            <h2 class="text-lg font-semibold flex items-center gap-2">
                <i data-lucide="clipboard-check" class="w-5 h-5"></i>
                Recibo de Conferência
            </h2>
            <p class="text-sm text-muted-foreground mt-1">Recibo sem valores para conferência de produtos</p>
        </div>
        <div class="p-6">
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                <div class="flex gap-3">
                    <i data-lucide="info" class="w-5 h-5 text-yellow-600 flex-shrink-0 mt-0.5"></i>
                    <div>
                        <p class="font-medium text-yellow-900 mb-1">O que é o Recibo de Conferência?</p>
                        <p class="text-sm text-yellow-800">
                            É uma versão do recibo que mostra todos os itens do pedido, quantidades e informações do cliente,
                            <strong>mas sem mostrar valores</strong>. Ideal para conferência de produtos, separação de pedidos
                            ou quando você precisa passar as informações sem expor preços.
                        </p>
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <h3 class="font-semibold mb-3">Informações Incluídas no Recibo de Conferência:</h3>
                    <div class="grid md:grid-cols-2 gap-3">
                        <div class="flex items-center gap-2 text-sm">
                            <i data-lucide="check" class="w-4 h-4 text-green-600"></i>
                            <span>Número do pedido</span>
                        </div>
                        <div class="flex items-center gap-2 text-sm">
                            <i data-lucide="check" class="w-4 h-4 text-green-600"></i>
                            <span>Nome do cliente</span>
                        </div>
                        <div class="flex items-center gap-2 text-sm">
                            <i data-lucide="check" class="w-4 h-4 text-green-600"></i>
                            <span>Telefone do cliente</span>
                        </div>
                        <div class="flex items-center gap-2 text-sm">
                            <i data-lucide="check" class="w-4 h-4 text-green-600"></i>
                            <span>Lista de produtos</span>
                        </div>
                        <div class="flex items-center gap-2 text-sm">
                            <i data-lucide="check" class="w-4 h-4 text-green-600"></i>
                            <span>Quantidades</span>
                        </div>
                        <div class="flex items-center gap-2 text-sm">
                            <i data-lucide="check" class="w-4 h-4 text-green-600"></i>
                            <span>Observações</span>
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="font-semibold mb-3">Informações NÃO Incluídas (ocultas):</h3>
                    <div class="grid md:grid-cols-2 gap-3">
                        <div class="flex items-center gap-2 text-sm">
                            <i data-lucide="x" class="w-4 h-4 text-red-600"></i>
                            <span>Preços dos produtos</span>
                        </div>
                        <div class="flex items-center gap-2 text-sm">
                            <i data-lucide="x" class="w-4 h-4 text-red-600"></i>
                            <span>Valor total</span>
                        </div>
                        <div class="flex items-center gap-2 text-sm">
                            <i data-lucide="x" class="w-4 h-4 text-red-600"></i>
                            <span>Taxa de entrega</span>
                        </div>
                        <div class="flex items-center gap-2 text-sm">
                            <i data-lucide="x" class="w-4 h-4 text-red-600"></i>
                            <span>Endereço de entrega</span>
                        </div>
                        <div class="flex items-center gap-2 text-sm">
                            <i data-lucide="x" class="w-4 h-4 text-red-600"></i>
                            <span>Método de pagamento</span>
                        </div>
                        <div class="flex items-center gap-2 text-sm">
                            <i data-lucide="x" class="w-4 h-4 text-red-600"></i>
                            <span>Descontos aplicados</span>
                        </div>
                    </div>
                </div>

                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-4">
                    <h3 class="font-semibold text-blue-900 mb-2 flex items-center gap-2">
                        <i data-lucide="lightbulb" class="w-4 h-4"></i>
                        Casos de Uso
                    </h3>
                    <ul class="text-sm text-blue-800 space-y-1 ml-6 list-disc">
                        <li>Separação de produtos no estoque</li>
                        <li>Conferência de itens antes da entrega</li>
                        <li>Organização interna da produção</li>
                        <li>Controle de qualidade dos pedidos</li>
                        <li>Entrega em condomínios (portaria)</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Serviço de Impressão Centralizada -->
    <div class="bg-card rounded-xl border border-border shadow-sm">
        <div class="p-6 border-b border-border">
            <h2 class="text-lg font-semibold flex items-center gap-2">
                <i data-lucide="server" class="w-5 h-5"></i>
                Central de Impressão
            </h2>
            <p class="text-sm text-muted-foreground mt-1">Marque este dispositivo como central de impressão para processar automaticamente todos os recibos</p>
        </div>
        <div class="p-6">
            <!-- Status Atual -->
            <div id="print-monitor-status" class="mb-6 p-4 rounded-lg border">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h3 class="font-semibold flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full" id="status-indicator"></span>
                            <span id="status-text">Verificando...</span>
                        </h3>
                        <p class="text-sm text-muted-foreground mt-1">Status do serviço de impressão</p>
                    </div>
                    <button type="button" id="btn-toggle-monitor" class="btn-primary" disabled>
                        <i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i>
                        <span id="btn-toggle-text">Carregando...</span>
                    </button>
                </div>
                
                <div id="monitor-info" class="hidden mt-4 pt-4 border-t space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">QZ Tray:</span>
                        <span id="qz-status" class="font-medium">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">Impressora:</span>
                        <span id="printer-name" class="font-medium">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">Recibos Impressos:</span>
                        <span id="printed-count" class="font-medium">0</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">Última Verificação:</span>
                        <span id="last-check" class="font-medium">-</span>
                    </div>
                </div>
            </div>
            <div class="bg-gradient-to-br from-purple-50 to-blue-50 border border-purple-200 rounded-xl p-6 mb-6">
                <h3 class="font-semibold text-purple-900 mb-3 flex items-center gap-2">
                    <i data-lucide="info" class="w-5 h-5"></i>
                    Como Funciona
                </h3>
                <div class="space-y-3 text-sm text-purple-800">
                    <div class="flex gap-3">
                        <div class="w-8 h-8 rounded-full bg-purple-500 text-white flex items-center justify-center font-bold flex-shrink-0">1</div>
                        <div>
                            <p class="font-medium">Marque este dispositivo como Central de Impressão</p>
                            <p class="text-purple-700">Clique em "Ativar Monitor" nesta página</p>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <div class="w-8 h-8 rounded-full bg-purple-500 text-white flex items-center justify-center font-bold flex-shrink-0">2</div>
                        <div>
                            <p class="font-medium">De qualquer dispositivo, solicite impressões</p>
                            <p class="text-purple-700">Celular, tablet ou outro computador - clique em "Adicionar à Fila"</p>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <div class="w-8 h-8 rounded-full bg-purple-500 text-white flex items-center justify-center font-bold flex-shrink-0">3</div>
                        <div>
                            <p class="font-medium">Este computador imprime automaticamente</p>
                            <p class="text-purple-700">O monitor detecta novos pedidos e imprime em segundo plano</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <!-- Requisitos -->
                <div>
                    <h3 class="font-semibold mb-4 flex items-center gap-2">
                        <i data-lucide="check-square" class="w-4 h-4"></i>
                        Requisitos
                    </h3>
                    
                    <div class="space-y-3">
                        <div class="flex items-start gap-3 text-sm">
                            <i data-lucide="check" class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5"></i>
                            <div>
                                <p class="font-medium">Impressora conectada a este computador</p>
                                <p class="text-muted-foreground">Impressora térmica fiscal ou impressora comum A4</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start gap-3 text-sm">
                            <i data-lucide="check" class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5"></i>
                            <div>
                                <p class="font-medium">QZ Tray instalado (apenas para impressora térmica)</p>
                                <p class="text-muted-foreground">
                                    <a href="https://qz.io/download/" target="_blank" class="text-primary hover:underline">
                                        Baixar QZ Tray
                                    </a>
                                    - Necessário para impressoras fiscais térmicas
                                </p>
                            </div>
                        </div>
                        
                        <div class="flex items-start gap-3 text-sm">
                            <i data-lucide="check" class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5"></i>
                            <div>
                                <p class="font-medium">Manter navegador aberto</p>
                                <p class="text-muted-foreground">Pode minimizar, mas não fechar o navegador</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dicas -->
                <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                    <h3 class="font-semibold text-amber-900 mb-2 flex items-center gap-2">
                        <i data-lucide="lightbulb" class="w-4 h-4"></i>
                        Dicas Importantes
                    </h3>
                    <ul class="text-sm text-amber-800 space-y-1 ml-6 list-disc">
                        <li><strong>Mantenha o navegador aberto:</strong> Não feche o navegador, apenas minimize</li>
                        <li><strong>Atalho útil:</strong> Salve a URL nos favoritos para abrir rapidamente</li>
                        <li><strong>Múltiplas impressoras:</strong> Abra uma aba para cada impressora</li>
                        <li><strong>Impressora comum:</strong> Não precisa QZ Tray, apenas configure o tipo acima</li>
                        <li><strong>Auto-reconexão:</strong> Se perder conexão, o serviço tenta reconectar sozinho</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle configurações de impressora comum
    const printerTypeCards = document.querySelectorAll('.printer-type-card');
    const regularSettings = document.getElementById('regular-settings');
    
    printerTypeCards.forEach(card => {
        card.addEventListener('click', function() {
            const type = this.dataset.type;
            const radio = this.querySelector('input[type="radio"]');
            
            // Atualizar visual
            printerTypeCards.forEach(c => c.classList.remove('selected'));
            this.classList.add('selected');
            
            // Marcar radio
            radio.checked = true;
            
            // Mostrar/ocultar configurações
            if (type === 'regular') {
                regularSettings.classList.remove('hidden');
            } else {
                regularSettings.classList.add('hidden');
            }
        });
    });
    
    // Copiar URL do serviço
    window.copyServiceUrl = function() {
        const input = document.getElementById('service-url');
        input.select();
        document.execCommand('copy');
        
        // Feedback visual
        const btn = event.target.closest('button');
        const originalHTML = btn.innerHTML;
        btn.innerHTML = '<i data-lucide="check" class="w-4 h-4"></i> Copiado!';
        if (window.lucide) lucide.createIcons();
        
        setTimeout(() => {
            btn.innerHTML = originalHTML;
            if (window.lucide) lucide.createIcons();
        }, 2000);
    };
    
    // Visualizar exemplo
    document.getElementById('btn-test-print')?.addEventListener('click', function() {
        const printerType = document.querySelector('input[name="printer_type"]:checked')?.value;
        const url = printerType === 'thermal' 
            ? '{{ route("dashboard.orders.fiscalReceipt", 1) }}' // ID exemplo
            : '{{ route("dashboard.orders.fiscalReceipt", 1) }}?format=a4';
        
        window.open(url, '_blank');
    });
    
    // Inicializar ícones Lucide
    if (window.lucide) {
        lucide.createIcons();
    }
    
    // ========================================
    // MONITOR DE IMPRESSÃO
    // ========================================
    let monitorActive = false;
    let monitorInterval = null;
    let qzConnected = false;
    let printedCount = 0;
    
    const statusIndicator = document.getElementById('status-indicator');
    const statusText = document.getElementById('status-text');
    const btnToggleMonitor = document.getElementById('btn-toggle-monitor');
    const btnToggleText = document.getElementById('btn-toggle-text');
    const monitorInfo = document.getElementById('monitor-info');
    const qzStatus = document.getElementById('qz-status');
    const printerName = document.getElementById('printer-name');
    const printedCountEl = document.getElementById('printed-count');
    const lastCheckEl = document.getElementById('last-check');
    
    // Verificar se QZ Tray está disponível
    function isQZTrayAvailable() {
        return typeof qz !== 'undefined' && qz !== null;
    }
    
    function isQZTrayConnected() {
        try {
            return isQZTrayAvailable() && qz.websocket !== null && qz.websocket.isActive();
        } catch (e) {
            return false;
        }
    }
    
    // Conectar ao QZ Tray
    async function connectQZTray() {
        if (!isQZTrayAvailable()) {
            return false;
        }
        
        try {
            if (isQZTrayConnected()) {
                return true;
            }
            
            await qz.websocket.connect();
            return isQZTrayConnected();
        } catch (e) {
            console.error('Erro ao conectar QZ Tray:', e);
            return false;
        }
    }
    
    // Atualizar status visual
    function updateMonitorStatus(status, message) {
        statusText.textContent = message;
        
        if (status === 'active') {
            statusIndicator.className = 'w-3 h-3 rounded-full bg-green-500 animate-pulse';
            btnToggleMonitor.className = 'btn-outline';
            btnToggleText.textContent = 'Desativar Monitor';
            monitorInfo.classList.remove('hidden');
        } else if (status === 'inactive') {
            statusIndicator.className = 'w-3 h-3 rounded-full bg-red-500';
            btnToggleMonitor.className = 'btn-primary';
            btnToggleText.textContent = 'Ativar Monitor';
            monitorInfo.classList.add('hidden');
        } else if (status === 'error') {
            statusIndicator.className = 'w-3 h-3 rounded-full bg-yellow-500';
            btnToggleMonitor.className = 'btn-primary';
            btnToggleText.textContent = 'Tentar Novamente';
            monitorInfo.classList.add('hidden');
        }
        
        btnToggleMonitor.disabled = false;
        if (window.lucide) lucide.createIcons();
    }
    
    // Verificar e imprimir pedidos
    async function checkAndPrintOrders() {
        if (!monitorActive) return;
        
        try {
            lastCheckEl.textContent = new Date().toLocaleTimeString('pt-BR');
            
            // Verificar conexão QZ Tray
            if (!isQZTrayConnected()) {
                const connected = await connectQZTray();
                if (!connected) {
                    qzStatus.textContent = '❌ Desconectado';
                    return;
                }
                qzStatus.textContent = '✅ Conectado';
            }
            
            // Buscar pedidos para imprimir
            const response = await fetch('/orders/orders-for-print', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'include'
            });
            
            if (!response.ok) {
                throw new Error(`Erro HTTP: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (!data.success || !data.orders || data.orders.length === 0) {
                return;
            }
            
            // Processar pedidos
            for (const orderInfo of data.orders) {
                if (orderInfo.printed_at) continue;
                
                try {
                    // Buscar detalhes ESC/POS
                    const detailsResponse = await fetch(`/orders/${orderInfo.id}/fiscal-receipt/escpos`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        credentials: 'include'
                    });
                    
                    if (!detailsResponse.ok) continue;
                    
                    const orderData = await detailsResponse.json();
                    if (!orderData.success || !orderData.data) continue;
                    
                    // Imprimir
                    const printers = await qz.printers.find();
                    let printer = printers.find(p => 
                        p.toUpperCase().includes('EPSON') && 
                        (p.toUpperCase().includes('TM-20') || p.toUpperCase().includes('TM-T20'))
                    ) || printers[0];
                    
                    if (!printer) continue;
                    
                    const printConfig = qz.configs.create(printer);
                    await qz.print(printConfig, [{
                        type: 'raw',
                        format: 'base64',
                        data: orderData.data
                    }]);
                    
                    // Marcar como impresso - COM CSRF TOKEN e verificação de sucesso
                    const markResponse = await fetch(`/orders/${orderInfo.id}/mark-printed`, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                        },
                        credentials: 'include',
                        body: JSON.stringify({})
                    });
                    
                    // Verificar se a marcação foi bem-sucedida
                    if (markResponse.ok) {
                        const markData = await markResponse.json();
                        if (markData.success) {
                            printedCount++;
                            printedCountEl.textContent = printedCount;
                            console.log(`✅ Pedido #${orderInfo.order_number} impresso e marcado`);
                        } else {
                            console.error(`❌ Falha ao marcar pedido #${orderInfo.order_number}:`, markData.message);
                        }
                    } else {
                        console.error(`❌ Erro HTTP ao marcar pedido #${orderInfo.order_number}:`, markResponse.status);
                    }
                } catch (e) {
                    console.error(`Erro ao imprimir pedido ${orderInfo.id}:`, e);
                }
            }
        } catch (error) {
            console.error('Erro na verificação:', error);
        }
    }
    
    // Ativar monitor
    async function startMonitor() {
        btnToggleMonitor.disabled = true;
        btnToggleText.textContent = 'Conectando...';
        
        // Limpar solicitações antigas ANTES de começar
        try {
            const clearResponse = await fetch('/orders/clear-old-print-requests', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                credentials: 'include'
            });
            
            if (clearResponse.ok) {
                const clearData = await clearResponse.json();
                console.log('✅ Solicitações antigas limpas:', clearData.cleared_count);
            }
        } catch (e) {
            console.warn('⚠️ Não foi possível limpar solicitações antigas:', e);
            // Continuar mesmo se falhar a limpeza
        }
        
        // Verificar QZ Tray
        const connected = await connectQZTray();
        
        if (!connected) {
            updateMonitorStatus('error', 'QZ Tray não disponível');
            qzStatus.textContent = '❌ Não instalado ou não está rodando';
            alert('⚠️ QZ Tray não encontrado!\n\nPara impressoras térmicas, você precisa:\n1. Instalar o QZ Tray (link na página)\n2. Executar o QZ Tray\n3. Tentar novamente');
            return;
        }
        
        // Detectar impressora
        try {
            const printers = await qz.printers.find();
            const printer = printers.find(p => 
                p.toUpperCase().includes('EPSON') && 
                (p.toUpperCase().includes('TM-20') || p.toUpperCase().includes('TM-T20'))
            ) || printers[0];
            
            if (printer) {
                printerName.textContent = printer;
            } else {
                printerName.textContent = 'Nenhuma impressora encontrada';
            }
        } catch (e) {
            printerName.textContent = 'Erro ao detectar';
        }
        
        // Ativar
        monitorActive = true;
        localStorage.setItem('printMonitorActive', 'true'); // Salvar estado
        qzStatus.textContent = '✅ Conectado';
        updateMonitorStatus('active', 'Monitor Ativo');
        
        // Iniciar polling
        monitorInterval = setInterval(checkAndPrintOrders, 3000);
        checkAndPrintOrders(); // Verificar imediatamente
    }
    
    // Desativar monitor
    function stopMonitor() {
        monitorActive = false;
        localStorage.setItem('printMonitorActive', 'false'); // Salvar estado
        if (monitorInterval) {
            clearInterval(monitorInterval);
            monitorInterval = null;
        }
        updateMonitorStatus('inactive', 'Monitor Inativo');
    }
    
    // Toggle monitor
    btnToggleMonitor.addEventListener('click', function() {
        if (monitorActive) {
            stopMonitor();
        } else {
            startMonitor();
        }
    });
    
    // Inicializar status
    setTimeout(() => {
        // Verificar se monitor estava ativo antes (ao recarregar página)
        const wasActive = localStorage.getItem('printMonitorActive') === 'true';
        
        if (wasActive && isQZTrayAvailable()) {
            console.log('🔄 Restaurando monitor de impressão...');
            startMonitor(); // Reativar automaticamente
        } else if (isQZTrayAvailable()) {
            updateMonitorStatus('inactive', 'Pronto para ativar');
        } else {
            updateMonitorStatus('error', 'QZ Tray não detectado');
            qzStatus.textContent = '❌ Não instalado';
            btnToggleText.textContent = 'QZ Tray Necessário';
        }
    }, 500);
});
</script>

<!-- QZ Tray SDK -->
<script src="https://cdn.jsdelivr.net/npm/qz-tray@2.2/qz-tray.min.js"></script>

<style>
.printer-type-card label > div {
    border-color: hsl(var(--border));
}
.printer-type-card.selected label > div {
    border-color: hsl(var(--primary));
    background: hsl(var(--primary) / 0.05);
}
</style>
@endpush
@endsection
