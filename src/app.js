require('dotenv').config();
const express = require('express');
const axios = require('axios');
const { sendMessage } = require('./services/socket');
const logger = require('./config/logger');

const app = express();
app.use(express.json());

const PORT = process.env.PORT || 3000;
const API_TOKEN = process.env.API_SECRET;
const LARAVEL_URL = process.env.LARAVEL_URL || 'http://localhost:8000';
const CLIENT_ID = process.env.CLIENT_ID || 1;

// 💾 Carregar dados do cliente do Laravel na inicialização
const carregarCliente = async () => {
    try {
        const response = await axios.get(`${LARAVEL_URL}/api/client/${CLIENT_ID}`);
        global.client = response.data;
        logger.info(`✅ Dados do cliente carregados: ${global.client?.name || 'Desconhecido'}`);
        logger.info(`📱 Número de notificação: ${global.client?.notificacao_whatsapp || 'Não configurado'}`);
    } catch (error) {
        logger.warn(`⚠️  Não foi possível carregar dados do cliente: ${error.message}`);
        // Inicializar com um objeto vazio para evitar erros
        global.client = {};
    }
};

// Carregar cliente ao iniciar
carregarCliente();

// Middleware de Segurança (aceita x-api-token OU apikey para compatibilidade Evolution)
app.use((req, res, next) => {
    // Aceita token de múltiplas fontes para compatibilidade
    const token = req.headers['x-api-token'] || req.headers['apikey'] || req.headers['authorization']?.replace('Bearer ', '');
    
    // Se não tiver API_SECRET definido no env, bloqueia tudo por segurança
    if (!API_TOKEN) {
        logger.error('ERRO CRÍTICO: API_SECRET não configurado no .env');
        return res.status(500).json({ error: 'Configuração de servidor inválida' });
    }

    if (token === API_TOKEN) {
        next();
    } else {
        logger.warn(`Tentativa de acesso negado. Token recebido: ${token ? token.substring(0,10) + '...' : 'vazio'}`);
        res.status(403).json({ error: 'Acesso negado' });
    }
});

app.get('/', (req, res) => {
    res.send('Olika WhatsApp Gateway is Running ');
});

// Rota principal para envio de mensagens
app.post('/send-message', async (req, res) => {
    try {
        const { number, message } = req.body;
        
        if (!number || !message) {
            return res.status(400).json({ error: 'Campos obrigatórios: number, message' });
        }

        const result = await sendMessage(number, message);
        logger.info(`Mensagem enviada para ${number}`);
        res.json(result);

    } catch (error) {
        logger.error(`Erro no envio: ${error.message}`);
        res.status(500).json({ error: error.message });
    }
});

// ============================================
// ROTAS DE COMPATIBILIDADE COM EVOLUTION API
// ============================================

// Rota para envio de texto (formato Evolution API)
// POST /message/sendText/:instance
app.post('/message/sendText/:instance', async (req, res) => {
    try {
        const { number, text } = req.body;
        const message = text; // Evolution usa 'text', nosso sistema usa 'message'
        
        if (!number || !message) {
            return res.status(400).json({ 
                success: false,
                error: 'Campos obrigatórios: number, text' 
            });
        }

        logger.info(`[Evolution API] Enviando mensagem para ${number} via instância ${req.params.instance}`);
        const result = await sendMessage(number, message);
        
        res.json({
            success: true,
            messageId: result.messageId,
            status: 'SENT'
        });

    } catch (error) {
        logger.error(`[Evolution API] Erro no envio: ${error.message}`);
        res.status(500).json({ 
            success: false,
            error: error.message 
        });
    }
});

// Rota para envio de mídia (formato Evolution API) - placeholder
app.post('/message/sendMedia/:instance', async (req, res) => {
    // Por enquanto retorna erro - implementar se necessário
    logger.warn('[Evolution API] sendMedia não implementado ainda');
    res.status(501).json({ 
        success: false,
        error: 'sendMedia não implementado nesta versão' 
    });
});

// Health check da instância (formato Evolution API)
app.get('/instance/health/:instance', (req, res) => {
    const { isConnected, getStatus } = require('./services/socket');
    
    const status = {
        instance: req.params.instance,
        connected: isConnected(),
        state: isConnected() ? 'CONNECTED' : 'DISCONNECTED',
        timestamp: new Date().toISOString()
    };
    
    logger.info(`[Evolution API] Health check: ${status.state}`);
    res.json(status);
});

// Connect instance (formato Evolution API) - redireciona para /connect existente
app.get('/instance/connect/:instance', async (req, res) => {
    const { startSock, isConnected } = require('./services/socket');
    
    if (isConnected()) {
        return res.json({ 
            success: true, 
            status: 'ALREADY_CONNECTED',
            message: 'Instância já conectada'
        });
    }
    
    try {
        await startSock();
        res.json({ 
            success: true, 
            status: 'CONNECTING',
            message: 'Conexão iniciada'
        });
    } catch (error) {
        logger.error(`[Evolution API] Erro ao conectar: ${error.message}`);
        res.status(500).json({ 
            success: false, 
            error: error.message 
        });
    }
});

app.listen(PORT, () => {
    logger.info(` Servidor rodando na porta ${PORT}`);
});
