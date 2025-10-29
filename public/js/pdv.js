// PDV Script - Sistema Olika Dashboard
// Funcionalidades: CEP, Carrinho, Pagamento, WhatsApp

document.addEventListener('DOMContentLoaded', () => {
    console.log('PDV Script carregado');
    
    // Variáveis globais
    let carrinho = [];
    let produtos = [];
    let clienteSelecionado = null;
    let cupomAplicado = null;
    
    // Elementos DOM
    const totalGeral = document.getElementById('total-geral');
    const listaItens = document.getElementById('lista-itens');
    const listaProdutos = document.getElementById('lista-produtos');
    const contadorItens = document.getElementById('contador-itens');
    const descontoReais = document.getElementById('desconto-reais');
    const descontoPct = document.getElementById('desconto-pct');
    
    // Inicialização
    inicializarEventos();
    carregarProdutos();
    atualizarContadores();
    
    // ==================== FUNCIONALIDADES DE CLIENTE ====================
    
    // Cliente CEP autofill (via viacep)
    const cepInput = document.getElementById('cliente-cep');
    cepInput?.addEventListener('blur', async () => {
        const cep = cepInput.value.replace(/\D/g, '');
        
        if (cep.length === 8) {
            try {
                const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
                const data = await response.json();
                
                if (!data.erro) {
                    document.getElementById('cliente-rua').value = data.logradouro || '';
                    document.getElementById('cliente-bairro').value = data.bairro || '';
                    document.getElementById('cliente-cidade').value = data.localidade || '';
                    
                    mostrarNotificacao('Endereço preenchido automaticamente!', 'success');
                } else {
                    mostrarNotificacao('CEP não encontrado', 'error');
                }
            } catch (error) {
                console.error('Erro ao buscar CEP:', error);
                mostrarNotificacao('Erro ao buscar CEP', 'error');
            }
        }
    });
    
    // Busca de cliente
    const clienteBusca = document.getElementById('cliente-busca');
    clienteBusca?.addEventListener('input', async function() {
        const termo = this.value.toLowerCase();
        
        if (termo.length >= 2) {
            try {
                const response = await fetch(`/api/clientes?search=${termo}`);
                const clientes = await response.json();
                mostrarSugestoesClientes(clientes);
            } catch (error) {
                console.error('Erro ao buscar clientes:', error);
            }
        } else {
            document.getElementById('sugestoes-cliente').classList.add('hidden');
        }
    });
    
    // Salvar cliente
    document.getElementById('btn-salvar-cliente')?.addEventListener('click', async () => {
        const cliente = {
            nome: document.getElementById('cliente-nome').value,
            telefone: document.getElementById('cliente-telefone').value,
            cep: document.getElementById('cliente-cep').value,
            numero: document.getElementById('cliente-numero').value,
            rua: document.getElementById('cliente-rua').value,
            bairro: document.getElementById('cliente-bairro').value,
            cidade: document.getElementById('cliente-cidade').value
        };
        
        if (!cliente.nome) {
            mostrarNotificacao('Nome é obrigatório', 'error');
            return;
        }
        
        try {
            const response = await fetch('/api/clientes', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(cliente)
            });
            
            const resultado = await response.json();
            
            if (resultado.success) {
                clienteSelecionado = resultado.cliente;
                mostrarNotificacao('Cliente salvo com sucesso!', 'success');
            } else {
                mostrarNotificacao('Erro ao salvar cliente: ' + resultado.message, 'error');
            }
        } catch (error) {
            console.error('Erro:', error);
            mostrarNotificacao('Erro ao salvar cliente', 'error');
        }
    });
    
    // Limpar cliente
    document.getElementById('btn-limpar-cliente')?.addEventListener('click', () => {
        document.getElementById('form-cliente').reset();
        clienteSelecionado = null;
        document.getElementById('cliente-dados').classList.add('hidden');
        document.getElementById('sugestoes-cliente').classList.add('hidden');
    });
    
    // ==================== FUNCIONALIDADES DE PRODUTOS ====================
    
    // Carregar produtos
    async function carregarProdutos() {
        try {
            const response = await fetch('/api/produtos');
            produtos = await response.json();
            renderizarProdutos(produtos);
            atualizarContadorProdutos();
        } catch (error) {
            console.error('Erro ao carregar produtos:', error);
            listaProdutos.innerHTML = `
                <div class="col-span-full text-center py-8 text-red-500">
                    <i class="fas fa-exclamation-triangle text-3xl mb-2"></i>
                    <p>Erro ao carregar produtos</p>
                </div>
            `;
        }
    }
    
    // Busca de produtos
    document.getElementById('busca-produto')?.addEventListener('input', function() {
        const termo = this.value.toLowerCase();
        const produtosFiltrados = produtos.filter(produto => 
            produto.nome.toLowerCase().includes(termo)
        );
        renderizarProdutos(produtosFiltrados);
    });
    
    // Renderizar produtos
    function renderizarProdutos(produtosFiltrados) {
        if (produtosFiltrados.length === 0) {
            listaProdutos.innerHTML = `
                <div class="col-span-full text-center py-8 text-gray-500">
                    <i class="fas fa-search text-3xl mb-2"></i>
                    <p>Nenhum produto encontrado</p>
                </div>
            `;
            return;
        }
        
        listaProdutos.innerHTML = produtosFiltrados.map(produto => `
            <div class="produto-card" onclick="adicionarAoCarrinho(${produto.id})">
                <div class="text-center">
                    <img src="${produto.imagem || '/img/placeholder.png'}" alt="${produto.nome}" class="w-full h-20 object-cover rounded mb-2">
                    <h3 class="font-semibold text-sm mb-1">${produto.nome}</h3>
                    <p class="text-orange-600 font-bold">R$ ${produto.preco.toFixed(2).replace('.', ',')}</p>
                    ${produto.estoque <= 5 ? '<p class="text-red-500 text-xs">Estoque baixo</p>' : ''}
                </div>
            </div>
        `).join('');
    }
    
    // ==================== FUNCIONALIDADES DE CARRINHO ====================
    
    // Adicionar ao carrinho
    window.adicionarAoCarrinho = function(produtoId) {
        const produto = produtos.find(p => p.id === produtoId);
        if (!produto) return;
        
        const itemExistente = carrinho.find(item => item.id === produtoId);
        
        if (itemExistente) {
            itemExistente.quantidade++;
        } else {
            carrinho.push({
                id: produto.id,
                nome: produto.nome,
                preco: produto.preco,
                quantidade: 1,
                imagem: produto.imagem
            });
        }
        
        atualizarCarrinho();
        atualizarContadores();
        mostrarNotificacao(`${produto.nome} adicionado ao carrinho`, 'success');
    };
    
    // Remover do carrinho
    window.removerDoCarrinho = function(produtoId) {
        carrinho = carrinho.filter(item => item.id !== produtoId);
        atualizarCarrinho();
        atualizarContadores();
    };
    
    // Atualizar quantidade
    window.atualizarQuantidade = function(produtoId, novaQuantidade) {
        if (novaQuantidade <= 0) {
            removerDoCarrinho(produtoId);
            return;
        }
        
        const item = carrinho.find(item => item.id === produtoId);
        if (item) {
            item.quantidade = novaQuantidade;
            atualizarCarrinho();
            atualizarContadores();
        }
    };
    
    // Atualizar carrinho
    function atualizarCarrinho() {
        if (carrinho.length === 0) {
            listaItens.innerHTML = `
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-shopping-cart text-3xl mb-2"></i>
                    <p>Nenhum item no carrinho</p>
                    <p class="text-sm">Adicione produtos para começar a venda</p>
                </div>
            `;
            atualizarTotal();
            return;
        }
        
        listaItens.innerHTML = carrinho.map(item => `
            <div class="item-carrinho">
                <div class="flex items-center">
                    <img src="${item.imagem || '/img/placeholder.png'}" alt="${item.nome}" class="w-12 h-12 object-cover rounded mr-3">
                    <div>
                        <h4 class="font-semibold text-sm">${item.nome}</h4>
                        <p class="text-orange-600 font-bold">R$ ${item.preco.toFixed(2).replace('.', ',')}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button onclick="atualizarQuantidade(${item.id}, ${item.quantidade - 1})" class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center hover:bg-gray-300">
                        <i class="fas fa-minus text-xs"></i>
                    </button>
                    <span class="w-8 text-center font-semibold">${item.quantidade}</span>
                    <button onclick="atualizarQuantidade(${item.id}, ${item.quantidade + 1})" class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center hover:bg-gray-300">
                        <i class="fas fa-plus text-xs"></i>
                    </button>
                    <button onclick="removerDoCarrinho(${item.id})" class="w-8 h-8 bg-red-200 rounded-full flex items-center justify-center hover:bg-red-300 ml-2">
                        <i class="fas fa-trash text-xs text-red-600"></i>
                    </button>
                </div>
            </div>
        `).join('');
        
        atualizarTotal();
    }
    
    // ==================== FUNCIONALIDADES DE DESCONTO ====================
    
    // Aplicar cupom
    document.getElementById('btn-aplicar-cupom')?.addEventListener('click', async () => {
        const codigoCupom = document.getElementById('cupom').value.trim();
        
        if (!codigoCupom) {
            mostrarNotificacao('Digite um código de cupom', 'error');
            return;
        }
        
        try {
            const response = await fetch(`/api/cupons/${codigoCupom}`);
            const cupom = await response.json();
            
            if (cupom.success) {
                cupomAplicado = cupom.cupom;
                
                if (cupom.cupom.tipo === 'porcentagem') {
                    document.getElementById('desconto-pct').value = cupom.cupom.valor;
                } else {
                    document.getElementById('desconto-reais').value = cupom.cupom.valor;
                }
                
                atualizarTotal();
                mostrarNotificacao(`Cupom ${codigoCupom} aplicado!`, 'success');
            } else {
                mostrarNotificacao('Cupom inválido ou expirado', 'error');
            }
        } catch (error) {
            console.error('Erro ao aplicar cupom:', error);
            mostrarNotificacao('Erro ao aplicar cupom', 'error');
        }
    });
    
    // Atualizar total com descontos
    function atualizarTotal() {
        let total = carrinho.reduce((acc, item) => acc + (item.preco * item.quantidade), 0);
        
        const descR = parseFloat(descontoReais.value) || 0;
        const descP = parseFloat(descontoPct.value) || 0;
        
        if (descR > 0) total -= descR;
        if (descP > 0) total *= (1 - (descP / 100));
        
        total = Math.max(0, total);
        
        totalGeral.textContent = `R$ ${total.toFixed(2).replace('.', ',')}`;
        
        // Atualizar outros elementos de total
        const subtotal = carrinho.reduce((acc, item) => acc + (item.preco * item.quantidade), 0);
        const descontoTotal = descR + (subtotal * descP / 100);
        
        document.getElementById('subtotal').textContent = `R$ ${subtotal.toFixed(2).replace('.', ',')}`;
        document.getElementById('desconto-total').textContent = `R$ ${descontoTotal.toFixed(2).replace('.', ',')}`;
        document.getElementById('total-final').textContent = `R$ ${total.toFixed(2).replace('.', ',')}`;
        
        // Atualizar texto do desconto aplicado
        if (descontoTotal > 0) {
            document.getElementById('desconto-aplicado').textContent = `Desconto: R$ ${descontoTotal.toFixed(2).replace('.', ',')}`;
        } else {
            document.getElementById('desconto-aplicado').textContent = 'Sem desconto';
        }
    }
    
    // Eventos de desconto - atualização automática
    [descontoReais, descontoPct].forEach(input => input?.addEventListener('input', atualizarTotal));
    
    // ==================== FUNCIONALIDADES DE PAGAMENTO ====================
    
    // Botão Mercado Pago (mock)
    document.getElementById('btn-pagar')?.addEventListener('click', () => {
        if (carrinho.length === 0) {
            mostrarNotificacao('Adicione pelo menos um produto ao carrinho', 'error');
            return;
        }
        
        alert('Integração com Mercado Pago ativa — redirecionando...');
        
        // Simular requisição backend para gerar link de pagamento
        window.open('https://pagamento.exemplo.com', '_blank');
    });
    
    // Envio WhatsApp
    document.getElementById('btn-whatsapp')?.addEventListener('click', () => {
        if (carrinho.length === 0) {
            mostrarNotificacao('Adicione pelo menos um produto ao carrinho', 'error');
            return;
        }
        
        const nome = document.getElementById('cliente-nome').value || 'Cliente';
        const total = totalGeral.textContent;
        const tel = document.getElementById('cliente-telefone').value.replace(/\D/g, '');
        
        if (!tel) {
            alert('Informe um número de telefone válido.');
            return;
        }
        
        const itens = carrinho.map(item => 
            `• ${item.nome} x${item.quantidade} - R$ ${(item.preco * item.quantidade).toFixed(2).replace('.', ',')}`
        ).join('\n');
        
        const msg = `🍞 *Olika - Pedido Confirmado*

Olá, ${nome}! Seu pedido foi confirmado:

${itens}

*Total: R$ ${total}*

Obrigado pela preferência! 🥖`;

        const link = `https://wa.me/55${tel}?text=${encodeURIComponent(msg)}`;
        window.open(link, '_blank');
        
        mostrarNotificacao('Pedido enviado por WhatsApp!', 'success');
    });
    
    // Venda no fiado
    document.getElementById('btn-fiado')?.addEventListener('click', () => {
        if (carrinho.length === 0) {
            mostrarNotificacao('Adicione pelo menos um produto ao carrinho', 'error');
            return;
        }
        
        if (!clienteSelecionado && !document.getElementById('cliente-nome').value) {
            mostrarNotificacao('Selecione um cliente para venda no fiado', 'error');
            return;
        }
        
        const total = parseFloat(totalGeral.textContent.replace('R$ ', '').replace(',', '.'));
        
        mostrarModal(
            'Venda no Fiado',
            `Confirmar venda no fiado de R$ ${total.toFixed(2).replace('.', ',')}?`,
            'fiado'
        );
    });
    
    // Cancelar venda
    document.getElementById('btn-cancelar')?.addEventListener('click', () => {
        if (confirm('Deseja cancelar o pedido atual?')) {
            carrinho.length = 0;
            atualizarTotal();
            document.getElementById('lista-itens').innerHTML = '';
            descontoReais.value = '';
            descontoPct.value = '';
            document.getElementById('cupom').value = '';
            
            // Limpar dados do cliente
            document.getElementById('form-cliente').reset();
            clienteSelecionado = null;
            cupomAplicado = null;
            
            atualizarContadores();
            mostrarNotificacao('Pedido cancelado', 'success');
        }
    });
    
    // ==================== FUNCIONALIDADES AUXILIARES ====================
    
    // Atualizar contadores
    function atualizarContadores() {
        const totalItens = carrinho.reduce((total, item) => total + item.quantidade, 0);
        contadorItens.textContent = `${totalItens} itens`;
        atualizarContadorProdutos();
    }
    
    function atualizarContadorProdutos() {
        document.getElementById('contador-produtos').textContent = `${produtos.length} produtos`;
    }
    
    // Mostrar sugestões de clientes
    function mostrarSugestoesClientes(clientes) {
        const container = document.getElementById('sugestoes-cliente');
        
        if (clientes.length === 0) {
            container.innerHTML = '<div class="p-2 text-gray-500">Nenhum cliente encontrado</div>';
        } else {
            container.innerHTML = clientes.map(cliente => `
                <div class="sugestao-cliente" onclick="selecionarCliente(${cliente.id}, '${cliente.nome}', '${cliente.telefone || ''}')">
                    <div class="font-semibold">${cliente.nome}</div>
                    <div class="text-sm text-gray-500">${cliente.telefone || 'Sem telefone'}</div>
                </div>
            `).join('');
        }
        
        container.classList.remove('hidden');
    }
    
    // Selecionar cliente
    window.selecionarCliente = function(id, nome, telefone) {
        clienteSelecionado = { id, nome, telefone };
        document.getElementById('cliente-busca').value = nome;
        document.getElementById('sugestoes-cliente').classList.add('hidden');
        document.getElementById('cliente-dados').classList.remove('hidden');
        document.getElementById('cliente-nome').value = nome;
        document.getElementById('cliente-telefone').value = telefone;
    };
    
    // Mostrar modal
    function mostrarModal(titulo, mensagem, acao) {
        document.getElementById('modal-titulo').textContent = titulo;
        document.getElementById('modal-mensagem').textContent = mensagem;
        document.getElementById('modal-confirmacao').classList.remove('hidden');
        document.getElementById('btn-modal-confirmar').dataset.acao = acao;
    }
    
    // Fechar modal
    document.getElementById('btn-modal-cancelar')?.addEventListener('click', () => {
        document.getElementById('modal-confirmacao').classList.add('hidden');
    });
    
    // Confirmar ação
    document.getElementById('btn-modal-confirmar')?.addEventListener('click', () => {
        const acao = document.getElementById('btn-modal-confirmar').dataset.acao;
        
        switch(acao) {
            case 'mercadopago':
                processarPagamentoMercadoPago();
                break;
            case 'fiado':
                processarVendaFiado();
                break;
            case 'cancelar':
                limparVenda();
                break;
        }
        
        document.getElementById('modal-confirmacao').classList.add('hidden');
    });
    
    // Processar pagamento Mercado Pago
    async function processarPagamentoMercadoPago() {
        try {
            const pedido = criarPedido();
            const response = await fetch('/api/pedidos/mercadopago', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(pedido)
            });
            
            const resultado = await response.json();
            
            if (resultado.success) {
                window.open(resultado.payment_url, '_blank');
                mostrarNotificacao('Pagamento iniciado! Aguarde a confirmação.', 'success');
            } else {
                mostrarNotificacao('Erro ao processar pagamento: ' + resultado.message, 'error');
            }
        } catch (error) {
            console.error('Erro:', error);
            mostrarNotificacao('Erro ao processar pagamento', 'error');
        }
    }
    
    // Processar venda no fiado
    async function processarVendaFiado() {
        try {
            const pedido = criarPedido();
            pedido.metodo_pagamento = 'fiado';
            
            const response = await fetch('/api/pedidos/fiado', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(pedido)
            });
            
            const resultado = await response.json();
            
            if (resultado.success) {
                mostrarNotificacao('Venda no fiado registrada com sucesso!', 'success');
                limparVenda();
            } else {
                mostrarNotificacao('Erro ao processar venda: ' + resultado.message, 'error');
            }
        } catch (error) {
            console.error('Erro:', error);
            mostrarNotificacao('Erro ao processar venda', 'error');
        }
    }
    
    // Criar pedido
    function criarPedido() {
        const subtotal = carrinho.reduce((total, item) => total + (item.preco * item.quantidade), 0);
        const descontoReais = parseFloat(document.getElementById('desconto-reais').value) || 0;
        const descontoPct = parseFloat(document.getElementById('desconto-pct').value) || 0;
        
        let descontoTotal = descontoReais;
        if (descontoPct > 0) {
            descontoTotal += (subtotal * descontoPct / 100);
        }
        
        return {
            cliente_id: clienteSelecionado?.id || null,
            cliente_nome: clienteSelecionado?.nome || document.getElementById('cliente-nome').value,
            cliente_telefone: clienteSelecionado?.telefone || document.getElementById('cliente-telefone').value,
            itens: carrinho.map(item => ({
                produto_id: item.id,
                nome: item.nome,
                preco: item.preco,
                quantidade: item.quantidade
            })),
            subtotal: subtotal,
            desconto: descontoTotal,
            total: Math.max(0, subtotal - descontoTotal),
            cupom: cupomAplicado,
            observacoes: ''
        };
    }
    
    // Limpar venda
    function limparVenda() {
        carrinho = [];
        clienteSelecionado = null;
        cupomAplicado = null;
        
        document.getElementById('form-cliente').reset();
        document.getElementById('cliente-dados').classList.add('hidden');
        document.getElementById('desconto-reais').value = '';
        document.getElementById('desconto-pct').value = '';
        document.getElementById('cupom').value = '';
        
        atualizarCarrinho();
        atualizarContadores();
        mostrarNotificacao('Venda cancelada', 'success');
    }
    
    // Mostrar notificação
    function mostrarNotificacao(mensagem, tipo = 'info') {
        // Implementar sistema de notificações
        console.log(`${tipo.toUpperCase()}: ${mensagem}`);
        
        // Notificação visual simples
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 ${
            tipo === 'success' ? 'bg-green-500 text-white' :
            tipo === 'error' ? 'bg-red-500 text-white' :
            'bg-blue-500 text-white'
        }`;
        notification.textContent = mensagem;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }
    
    // Inicializar eventos
    function inicializarEventos() {
        console.log('Eventos do PDV inicializados');
    }
    
    console.log('PDV Script inicializado com sucesso');
});