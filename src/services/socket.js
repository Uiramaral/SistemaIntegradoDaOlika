/**
 * Olika WhatsApp Integration — socket.js
 * Versão restaurada - funcionando com conexão estável
 */

const {
  default: makeWASocket,
  useMultiFileAuthState,
  DisconnectReason,
  fetchLatestBaileysVersion,
} = require("@whiskeysockets/baileys");
const P = require("pino");
const { Boom } = require("@hapi/boom");
const NodeCache = require("node-cache");

const SESSION_PATH = "./auth_info_baileys/5571987019420";

// ✅ NOVO: Cache para controlar a janela de 10 minutos por cliente
// stdTTL: 600 segundos (10 minutos).
// checkperiod: 60 (verifica expiração a cada minuto)
const notificationCache = new NodeCache({ stdTTL: 600, checkperiod: 60 });

let globalSock = null;

const startSock = async () => {
  const { version } = await fetchLatestBaileysVersion();
  const { state, saveCreds } = await useMultiFileAuthState(SESSION_PATH);
  const logger = P({ level: "info" });

  let sock;
  let reconnectAttempts = 0;
  let lastConnected = null;
  let heartbeatInterval;

  // 🩺 Heartbeat ativo — evita timeout em Railway
  const startHeartbeat = () => {
    if (heartbeatInterval) clearInterval(heartbeatInterval);
    heartbeatInterval = setInterval(() => {
      try {
        if (sock?.ws?.readyState === 1) {
          sock.ws.send("ping");
          logger.debug("💓 Heartbeat enviado para manter conexão viva");
        }
      } catch (err) {
        logger.warn("Erro ao enviar heartbeat:", err.message);
      }
    }, 20000);
  };

  // 🔁 Reconector com backoff
  const reconnect = async () => {
    reconnectAttempts++;
    const delay = Math.min(30000, 5000 * reconnectAttempts);
    logger.warn(
      `Conexão instável. Tentando reconectar em ${delay / 1000}s (tentativa ${reconnectAttempts})...`
    );
    await new Promise((r) => setTimeout(r, delay));
    startSock();
  };

  // 🚀 Inicializa socket
  sock = makeWASocket({
    version,
    logger,
    printQRInTerminal: true,
    auth: state,
    browser: ["Ubuntu", "Chrome", "20.0.04"],
    syncFullHistory: false,
    markOnlineOnConnect: true,
    connectTimeoutMs: 60000,
    defaultQueryTimeoutMs: 60000,
  });

  // 🧠 Eventos principais
  sock.ev.on("connection.update", async (update) => {
    const { connection, lastDisconnect, qr } = update;

    if (qr) {
      logger.info("📲 Novo código de pareamento gerado. Escaneie rapidamente!");
    }

    if (connection === "open") {
      reconnectAttempts = 0;
      lastConnected = Date.now();
      logger.info("✅ Conectado com sucesso ao WhatsApp!");
      startHeartbeat();
      globalSock = sock; // Atualizar referência global
    }

    if (connection === "close") {
      const reason = new Boom(lastDisconnect?.error)?.output?.statusCode;
      const uptime = lastConnected
        ? ((Date.now() - lastConnected) / 60000).toFixed(1)
        : "0";

      logger.warn(`🔴 Desconectado após ${uptime} minutos online. Motivo: ${reason}`);

      if (reason === DisconnectReason.loggedOut) {
        logger.error(
          "🚫 Sessão encerrada. É necessário novo pareamento (QR Code)."
        );
      } else {
        reconnect();
      }
    }
  });

  // 🔐 Salvamento seguro das credenciais
  sock.ev.on("creds.update", async () => {
    try {
      await saveCreds();
      logger.info("💾 Credenciais atualizadas com sucesso!");
    } catch (err) {
      logger.error("Erro ao salvar credenciais:", err.message);
    }
  });

  // ⚠️ Tratamento global de exceções
  process.on("uncaughtException", (err) => {
    logger.error("Erro não tratado:", err);
  });

  process.on("unhandledRejection", (reason) => {
    logger.error("Promise rejeitada sem tratamento:", reason);
  });

  globalSock = sock;
  return sock;
};

// 🟢 Inicialização segura
(async () => {
  try {
    const sock = await startSock();
    console.log("🚀 Olika WhatsApp socket iniciado com sucesso.");
  } catch (err) {
    console.error("❌ Falha ao iniciar o socket:", err);
  }
})();

/**
 * Envia mensagem via WhatsApp
 * @param {string} phone - Número do telefone
 * @param {string} message - Mensagem a ser enviada
 * @returns {Promise<{success: boolean, messageId?: string}>}
 */
const sendMessage = async (phone, message) => {
  if (!globalSock) {
    throw new Error('Socket não está conectado. Aguarde a conexão ser estabelecida.');
  }
  
  if (!phone || !message) {
    throw new Error('Phone e message são obrigatórios');
  }
  
  // Normalizar número de telefone
  let normalizedPhone = phone.replace(/\D/g, '');
  
  if (!phone.includes('@s.whatsapp.net')) {
    normalizedPhone = `${normalizedPhone}@s.whatsapp.net`;
  } else {
    normalizedPhone = phone;
  }
  
  try {
    const result = await globalSock.sendMessage(normalizedPhone, { text: message });
    
    return {
      success: true,
      messageId: result?.key?.id,
    };
  } catch (error) {
    console.error('Erro ao enviar mensagem:', error);
    throw new Error(`Falha ao enviar mensagem: ${error.message}`);
  }
};

/**
 * Verifica se o socket está conectado
 * @returns {boolean}
 */
const isConnected = () => {
  return globalSock !== null && globalSock.ws?.readyState === 1;
};

/**
 * Desconecta e logout da sessão WhatsApp
 * @returns {Promise<void>}
 */
const disconnectSock = async () => {
  const logger = P({ level: "info" });
  
  if (!globalSock) {
    logger.warn('⚠️  Socket já está desconectado');
    return;
  }
  
  try {
    logger.info('🔴 Iniciando desconexão do WhatsApp...');
    
    // 1. Fazer logout para invalidar a sessão
    await globalSock.logout();
    logger.info('✅ Logout realizado com sucesso');
    
    // 2. Fechar a conexão WebSocket
    if (globalSock.ws) {
      globalSock.ws.close();
      logger.info('✅ WebSocket fechado');
    }
    
    // 3. Limpar referência global
    globalSock = null;
    logger.info('✅ Instância desconectada completamente');
    
  } catch (error) {
    logger.error(`❌ Erro ao desconectar: ${error.message}`);
    // Forçar limpeza mesmo com erro
    globalSock = null;
    throw error;
  }
};

/**
 * Obtém a instância do socket
 * @returns {object|null}
 */
const getSocket = () => {
  return globalSock;
};

module.exports = {
  sendMessage,
  isConnected,
  getSocket,
  startSock,
  disconnectSock,
};
