# 📱 Integração Laravel ↔ WhatsApp (Railway + Baileys)

## 📋 Sumário Executivo

Esta documentação descreve a integração completa entre o sistema Laravel e o bot WhatsApp hospedado no Railway, utilizando a biblioteca Baileys para comunicação com a API do WhatsApp. A integração permite o envio automático de notificações de status de pedidos para clientes via WhatsApp.

**Versão:** 1.1.0  
**Data:** Janeiro 2025  
**Status:** ✅ Implementado e Funcional  
**Última Atualização:** 2025-01-27

---

## 🏗️ Arquitetura da Integração

### Fluxo Completo

```
┌─────────────────┐
│   Laravel App   │
│  (Pedido Criado │
│  ou Atualizado) │
└────────┬────────┘
         │
         │ 1. OrderStatusService::changeStatus()
         ▼
┌─────────────────────────┐
│  OrderStatusService     │
│  dispatchOrderEvent()    │
└────────┬────────────────┘
         │
         │ 2. event(new OrderStatusUpdated())
         ▼
┌─────────────────────────┐
│  OrderStatusUpdated     │
│  (Evento Laravel)       │
└────────┬────────────────┘
         │
         │ 3. Listener é acionado
         ▼
┌──────────────────────────────┐
│  SendOrderWhatsAppNotification│
│  (Listener)                  │
└────────┬─────────────────────┘
         │
         │ 4. HTTP POST /api/notify
         │    Headers: X-Olika-Token
         │    Body: Payload JSON completo
         ▼
┌──────────────────────────────┐
│  Bot WhatsApp (Railway)      │
│  Express.js + Baileys        │
│  Endpoint: /api/notify       │
└────────┬─────────────────────┘
         │
         │ 5. sendMessage() via Baileys
         ▼
┌──────────────────────────────┐
│  WhatsApp Business API       │
│  (via Baileys)               │
└────────┬─────────────────────┘
         │
         │ 6. Mensagem entregue
         ▼
┌──────────────────────────────┐
│  Cliente (WhatsApp)          │
└──────────────────────────────┘
```

---

## 📦 Componentes Implementados

### 1. Laravel (Backend)

#### 1.1. Evento: `OrderStatusUpdated`

**Arquivo:** `app/Events/OrderStatusUpdated.php`

```php
<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Order $order,
        public string $event,
        public ?string $note = null,
        public array $meta = []
    ) {
    }
}
```

**Propriedades:**
- `$order` - Instância completa do pedido
- `$event` - Tipo de evento (`order_created`, `order_preparing`, `order_ready`, `order_completed`)
- `$note` - Observação opcional
- `$meta` - Metadados adicionais

---

#### 1.2. Listener: `SendOrderWhatsAppNotification`

**Arquivo:** `app/Listeners/SendOrderWhatsAppNotification.php`

**Características:**
- ✅ Execução **SÍNCRONA** (sem fila) - adequado para ambiente compartilhado
- ✅ Retry automático: 3 tentativas com intervalo de 15 segundos
- ✅ Logs detalhados em cada etapa
- ✅ Normalização automática de telefone (adiciona código do país)
- ✅ Validação de configuração antes de enviar
- ✅ Tratamento específico para erro 502 (bot offline)

**Métodos Principais:**

```php
public function handle(OrderStatusUpdated $event): void
{
    // 1. Verifica se webhook URL está configurado
    // 2. Carrega dados do pedido (customer, items, address)
    // 3. Normaliza telefone do cliente
    // 4. Monta payload completo
    // 5. Envia HTTP POST com retry automático
    // 6. Loga sucesso/erro
}

private function normalizePhone(string $phone): string
{
    // Adiciona código do país (55) se não tiver
    // Remove caracteres não numéricos
    // Retorna formato: 5511999999999
}
```

**Payload Enviado:**

```json
{
  "event": "order_created",
  "status": "confirmed",
  "note": "Observação opcional",
  "meta": {},
  "order": {
    "id": 123,
    "number": "OLK-0123",
    "status": "confirmed",
    "payment_method": "pix",
    "delivery_type": "delivery",
    "total": 99.90,
    "delivery_fee": 5.00,
    "discount": 0.00,
    "scheduled_for": "2025-01-27T18:00:00Z",
    "notes": "Sem cebola",
    "items": [
      {
        "id": 1,
        "name": "Pizza Margherita",
        "quantity": 2,
        "unit_price": 45.00,
        "total": 90.00
      }
    ]
  },
  "customer": {
    "id": 456,
    "name": "João Silva",
    "phone": "5511999999999",
    "raw_phone": "(11) 99999-9999",
    "email": "joao@example.com"
  },
  "address": {
    "street": "Rua das Flores",
    "number": "123",
    "neighborhood": "Centro",
    "city": "São Paulo",
    "state": "SP",
    "zipcode": "01234-567",
    "complement": "Apto 45",
    "reference": "Próximo ao mercado"
  }
}
```

**Headers Enviados:**

```
X-Source-System: olika
Content-Type: application/json
X-Olika-Token: seu_token_aqui
X-Webhook-Token: seu_token_aqui (fallback)
```

---

#### 1.3. Serviço: `OrderStatusService`

**Arquivo:** `app/Services/OrderStatusService.php`

**Método:** `dispatchOrderEvent()`

**Mapeamento de Status:**

| Status Interno | Evento WhatsApp | Descrição |
|----------------|-----------------|-----------|
| `pending` | `order_created` | Pedido criado/aguardando pagamento |
| `confirmed` | `order_created` | Pedido confirmado |
| `preparing` | `order_preparing` | Pedido em preparo |
| `ready` | `order_ready` | Pedido pronto para entrega |
| `out_for_delivery` | `order_ready` | Pedido a caminho |
| `delivered` | `order_completed` | Pedido entregue |

**Código:**

```php
private function dispatchOrderEvent(Order $order, string $status, ?string $note = null): void
{
    $map = [
        'pending' => 'order_created',
        'confirmed' => 'order_created',
        'preparing' => 'order_preparing',
        'ready' => 'order_ready',
        'out_for_delivery' => 'order_ready',
        'delivered' => 'order_completed',
    ];

    if (!isset($map[$status])) {
        Log::debug('Status não mapeado para evento WhatsApp', [
            'status' => $status,
            'order_id' => $order->id,
        ]);
        return;
    }

    $eventType = $map[$status];
    
    Log::info('📨 Disparando evento OrderStatusUpdated', [
        'order_id' => $order->id,
        'order_number' => $order->order_number,
        'status' => $status,
        'event' => $eventType,
    ]);
    
    event(new OrderStatusUpdated($order, $eventType, $note));
}
```

**Quando é Disparado:**

O evento é disparado automaticamente quando:
1. Um pedido muda de status via `OrderStatusService::changeStatus()`
2. O parâmetro `$skipNotifications` é `false`
3. O status está no mapeamento acima

---

#### 1.4. Registro do Listener

**Arquivo:** `app/Providers/EventServiceProvider.php`

```php
protected $listen = [
    OrderStatusUpdated::class => [
        SendOrderWhatsAppNotification::class,
    ],
];
```

---

#### 1.5. Configuração Centralizada

**Arquivo:** `config/notifications.php`

```php
return [
    'email_enabled'      => env('NOTIFY_EMAIL_ENABLED', true),
    'wa_enabled'         => env('NOTIFY_WA_ENABLED', env('WHATSAPP_WEBHOOK_URL') ? true : false),
    'wa_webhook_url'     => env('WHATSAPP_WEBHOOK_URL', env('NOTIFY_WA_WEBHOOK_URL')),
    'wa_token'           => env('WHATSAPP_WEBHOOK_TOKEN', env('NOTIFY_WA_TOKEN')),
    'wa_sender'          => env('NOTIFY_WA_SENDER'),
    'wa_default_country' => env('WHATSAPP_DEFAULT_COUNTRY_CODE', '55'),
    'wa_timeout'         => env('WHATSAPP_WEBHOOK_TIMEOUT', 10),
];
```

#### 1.6. API Endpoint para Configurações do WhatsApp

**Arquivo:** `app/Http/Controllers/Dashboard/SettingsController.php`

**Método:** `whatsappSettingsApi()`

**Rota:** `GET /api/whatsapp/settings`

**Autenticação:** Header `X-API-Token` (deve ser igual a `API_SECRET` ou `WEBHOOK_TOKEN`)

**Resposta:**
```json
{
  "whatsapp_phone": "5571987019420"
}
```

**Características:**
- ✅ Prioriza número do banco de dados (`whatsapp_settings.whatsapp_phone`)
- ✅ Fallback para variável de ambiente `WHATSAPP_PHONE`
- ✅ Fallback padrão: `5571987019420`
- ✅ Autenticação obrigatória via token
- ✅ Logs detalhados para debug

---

### 2. Bot WhatsApp (Railway)

#### 2.1. Estrutura do Projeto

```
olika-whatsapp-integration/
├── src/
│   ├── app.js              # Servidor Express
│   ├── services/
│   │   └── socket.js       # Conexão Baileys
│   └── config/
│       └── logger.js       # Configuração de logs
├── package.json
└── Dockerfile
```

---

#### 2.2. Socket WhatsApp (`src/services/socket.js`)

**Tecnologias:**
- `@whiskeysockets/baileys` - Biblioteca para WhatsApp
- `pino` - Logger
- `@hapi/boom` - Tratamento de erros

**Características:**
- ✅ Conexão persistente com WhatsApp
- ✅ Heartbeat ativo (evita timeout em Railway)
- ✅ Reconexão automática com backoff exponencial
- ✅ Salvamento seguro de credenciais
- ✅ Tratamento global de exceções
- ✅ Código de pareamento (substitui QR Code)
- ✅ Busca número do WhatsApp do banco de dados (prioridade sobre .env)
- ✅ Graceful shutdown para encerramento limpo

**Funções Exportadas:**

```javascript
module.exports = {
  sendMessage,    // Envia mensagem via WhatsApp
  isConnected,    // Verifica se está conectado
  getSocket,      // Obtém instância do socket
};
```

**Função `sendMessage`:**

```javascript
const sendMessage = async (phone, message) => {
  const sock = global.sock;
  
  // Verificar conexão antes de tentar enviar
  if (!sock) {
    throw new Error('Socket não está conectado. Aguarde a conexão ser estabelecida.');
  }
  
  if (!sock.user && (!sock.ws || sock.ws.readyState !== 1)) {
    throw new Error('WhatsApp não está conectado. Aguarde a conexão ser estabelecida.');
  }
  
  // Normalizar número de telefone
  let normalizedPhone = phone.replace(/\D/g, '');
  if (!phone.includes('@s.whatsapp.net')) {
    normalizedPhone = `${normalizedPhone}@s.whatsapp.net`;
  }
  
  // Timeout interno de 5 segundos
  const sendPromise = sock.sendMessage(normalizedPhone, { text: message });
  const timeoutPromise = new Promise((_, reject) => {
    setTimeout(() => reject(new Error('Timeout interno: sendMessage demorou mais de 5s')), 5000);
  });
  
  const result = await Promise.race([sendPromise, timeoutPromise]);
  
  return {
    success: true,
    messageId: result?.key?.id,
  };
};
```

**Função `isConnected`:**

```javascript
const isConnected = () => {
  // Usar variável global de estado (mais confiável)
  if (!global.isWhatsAppConnected) {
    return false;
  }
  
  // Verificar se o socket existe e o WebSocket está aberto
  const sock = global.sock;
  if (!sock) {
    return false;
  }
  
  // Verificar estado do WebSocket (readyState: 1 = OPEN)
  const wsState = sock?.ws?.readyState;
  return wsState === 1;
};
```

**Função `getWhatsAppPhone` (Busca número do banco de dados):**

```javascript
async function getWhatsAppPhone() {
  const laravelApiUrl = process.env.LARAVEL_API_URL || 'https://devdashboard.menuolika.com.br';
  const laravelApiKey = process.env.API_SECRET || API_TOKEN;
  
  try {
    // Fazer requisição para /api/whatsapp/settings no Laravel
    const response = await fetch(`${laravelApiUrl}/api/whatsapp/settings`, {
      headers: {
        'X-API-Token': laravelApiKey,
        'Accept': 'application/json'
      },
      timeout: 5000
    });
    
    if (response.status === 403) {
      logger.warn('Erro de autenticação ao buscar número do WhatsApp');
      return process.env.WHATSAPP_PHONE || "5571987019420";
    }
    
    const settings = await response.json();
    
    // PRIORIDADE: Banco de dados > .env > Padrão
    if (settings.whatsapp_phone && String(settings.whatsapp_phone).trim() !== '') {
      return String(settings.whatsapp_phone).trim();
    }
    
    return process.env.WHATSAPP_PHONE || "5571987019420";
  } catch (error) {
    logger.warn('Erro ao buscar número do WhatsApp, usando fallback:', error.message);
    return process.env.WHATSAPP_PHONE || "5571987019420";
  }
}
```

---

#### 2.3. Servidor Express (`src/app.js`)

**Endpoints:**

##### `GET /` - Health Check

```javascript
app.get('/', (req, res) => {
  res.json({
    status: 'running',
    connected: isConnected(),
    uptime: Math.floor(process.uptime()),
    timestamp: new Date().toISOString(),
    port: PORT
  });
});
```

**Resposta:**
```json
{
  "status": "running",
  "connected": true,
  "uptime": 3600,
  "timestamp": "2025-01-27T18:30:00.000Z",
  "port": 8080
}
```

##### `POST /send-message` - Envio Simples (Compatibilidade)

```javascript
app.post('/send-message', requireAuth, async (req, res) => {
  const { number, message } = req.body;
  
  if (!number || !message) {
    return res.status(400).json({ error: 'Campos obrigatórios: number, message' });
  }

  if (!isConnected()) {
    return res.status(503).json({ 
      error: 'WhatsApp não está conectado.' 
    });
  }

  const result = await sendMessage(number, message);
  res.json(result);
});
```

**Payload:**
```json
{
  "number": "5511999999999",
  "message": "Mensagem de texto"
}
```

##### `GET /api/whatsapp/status` - Status da Conexão WhatsApp

```javascript
app.get('/api/whatsapp/status', requireAuth, (req, res) => {
  const sock = global.sock;
  const user = sock?.user;
  const connected = isConnected();
  
  // Retornar código de pareamento apenas se não estiver conectado
  const pairingCode = connected ? null : (global.currentPairingCode || null);
  
  res.json({
    connected: connected,
    pairingCode: pairingCode,
    user: user ? {
      id: user.id,
      name: user.name || null
    } : null,
    last_updated: new Date().toISOString()
  });
});
```

**Resposta (Conectado):**
```json
{
  "connected": true,
  "pairingCode": null,
  "user": {
    "id": "5511999999999",
    "name": "Nome do WhatsApp"
  },
  "last_updated": "2025-01-27T18:30:00.000Z"
}
```

**Resposta (Não Conectado):**
```json
{
  "connected": false,
  "pairingCode": "12345678",
  "user": null,
  "last_updated": "2025-01-27T18:30:00.000Z"
}
```

##### `POST /api/whatsapp/disconnect` - Desconectar WhatsApp Manualmente

```javascript
app.post('/api/whatsapp/disconnect', requireAuth, async (req, res) => {
  const result = await disconnect();
  
  if (result.success) {
    res.json({
      success: true,
      message: result.message
    });
  } else {
    res.status(400).json({
      success: false,
      message: result.message
    });
  }
});
```

##### `POST /api/whatsapp/restart` - Reiniciar Conexão com Novo Número

```javascript
app.post('/api/whatsapp/restart', requireAuth, async (req, res) => {
  // Buscar novo número do banco de dados
  const newPhone = await getWhatsAppPhone();
  global.currentWhatsAppPhone = newPhone;
  
  // Desconectar conexão atual
  if (global.sock) {
    await disconnect();
  }
  
  // Aguardar antes de reconectar
  await new Promise(resolve => setTimeout(resolve, 2000));
  
  // Reconectar com novo número
  startSock(newPhone).catch(err => {
    logger.error(`Erro ao reconectar: ${err.message}`);
  });
  
  res.json({
    success: true,
    message: `Conexão reiniciada com número: ${newPhone}`,
    new_phone: newPhone
  });
});
```

##### `POST /api/notify` - Endpoint Principal (Laravel)

**IMPORTANTE:** Este endpoint possui timeout de 8 segundos para evitar erro 502 do Railway.

```javascript
app.post('/api/notify', requireAuth, async (req, res) => {
  // Timeout de segurança: resposta em no máximo 8 segundos
  let responseTimeout = setTimeout(() => {
    if (!res.headersSent) {
      res.status(504).json({
        success: false,
        error: 'Timeout interno: aplicação não respondeu a tempo',
        retry: true,
        timeout: true
      });
    }
  }, 8000);

  try {
    const { event, order, customer, phone, message } = req.body;
    
    // Verificar conexão ANTES de qualquer processamento
    if (!isConnected()) {
      return res.status(503).json({ 
        success: false,
        error: 'WhatsApp não conectado. Tente novamente em alguns segundos.',
        retry: true,
        connected: false
      });
    }

    // Determinar telefone
    const targetPhone = phone || customer?.phone;
    
    if (!targetPhone) {
      return res.status(400).json({ 
        success: false,
        error: 'Telefone do cliente é obrigatório (phone ou customer.phone)' 
      });
    }

    // Formata mensagem
    let finalMessage = message;
    if (!finalMessage && order) {
      finalMessage = formatOrderMessage(event, order, customer);
    }
    
    // Enviar mensagem com timeout interno (6 segundos)
    const sendPromise = sendMessage(targetPhone, finalMessage);
    const timeoutPromise = new Promise((_, reject) => {
      setTimeout(() => reject(new Error('Timeout ao enviar mensagem (6s)')), 6000);
    });

    const result = await Promise.race([sendPromise, timeoutPromise]);
    clearTimeout(responseTimeout);
    
    res.json({
      success: true,
      messageId: result.messageId,
      sent_at: new Date().toISOString()
    });
  } catch (error) {
    clearTimeout(responseTimeout);
    
    if (error.message.includes('Timeout')) {
      return res.status(503).json({ 
        success: false,
        error: 'Timeout ao enviar mensagem. WhatsApp pode estar reconectando.',
        retry: true,
        timeout: true
      });
    }
    
    return res.status(500).json({ 
      success: false,
      error: error.message || 'Falha no envio WhatsApp'
    });
  }
});
```

**Formatação de Mensagens:**

O bot formata automaticamente mensagens baseadas no evento:

```javascript
function formatOrderMessage(event, order, customer) {
  const customerName = customer?.name || 'Cliente';
  const orderNumber = order?.number || order?.id || 'N/A';
  const total = order?.total ? `R$ ${parseFloat(order.total).toFixed(2).replace('.', ',')}` : 'R$ 0,00';
  
  const messages = {
    'order_created': `✅ *Pedido Confirmado!*\n\n` +
        `Olá, ${customerName}! Recebemos o pedido *#${orderNumber}* e já estamos separando tudo com carinho.\n\n` +
        `💰 Total: ${total}\n\n` +
        `Assim que a entrega estiver a caminho, avisaremos por aqui!`,
        
    'order_preparing': `👩‍🍳 *Pedido em Preparo*\n\n` +
        `Olá, ${customerName}! O pedido *#${orderNumber}* está sendo preparado com muito carinho.\n\n` +
        `Em breve estará pronto! 🍕`,
        
    'order_ready': `🚗 *Pedido Pronto para Entrega!*\n\n` +
        `Olá, ${customerName}! O pedido *#${orderNumber}* já está pronto e aguardando a coleta do entregador.\n\n` +
        `Obrigado por comprar com a Olika!`,
        
    'order_completed': `🎉 *Pedido Entregue!*\n\n` +
        `Olá, ${customerName}! Confirmamos que o pedido *#${orderNumber}* foi entregue com sucesso.\n\n` +
        `Agradecemos a preferência e esperamos que aproveite! 😋`,
  };
  
  return messages[event] || `📦 Atualização do pedido *#${orderNumber}*\n\nStatus: ${event}`;
}
```

**Autenticação:**

```javascript
const requireAuth = (req, res, next) => {
    const token = req.headers['x-api-token'] || 
                  req.headers['x-webhook-token'] || 
                  req.headers['x-olika-token'];
    
    const API_TOKEN = process.env.API_SECRET;
    const WEBHOOK_TOKEN = process.env.WEBHOOK_TOKEN || API_TOKEN;
    
    // Se não tiver token configurado, bloquear por segurança
    if (!API_TOKEN && !WEBHOOK_TOKEN) {
        logger.error('ERRO CRÍTICO: Nenhum token configurado no .env');
        return res.status(500).json({ error: 'Configuração de servidor inválida' });
    }

    const validToken = token === API_TOKEN || token === WEBHOOK_TOKEN;
    
    if (validToken) {
        next();
    } else {
        logger.warn(`Tentativa de acesso negado. Token recebido: ${token ? '***' : 'nenhum'}`);
        res.status(403).json({ error: 'Acesso negado' });
    }
};
```

**Graceful Shutdown:**

```javascript
const gracefulShutdown = async (signal) => {
    logger.info(`Sinal ${signal} recebido. Iniciando Graceful Shutdown...`);
    
    // 1. Tenta desconectar o WhatsApp de forma limpa
    if (global.sock) {
        logger.info('Encerrando conexão Baileys (logout)...');
        try {
            await global.sock.logout();
            logger.info('Baileys desconectado e credenciais salvas.');
        } catch (error) {
            logger.error('Falha no logout Baileys, tentando encerrar o socket:', error.message);
            try {
                await global.sock.end();
            } catch (e) {
                logger.error('Erro ao encerrar socket:', e.message);
            }
        }
    }
    
    // 2. Fecha o servidor HTTP para novas conexões
    if (server) {
        server.close(() => {
            logger.info('Servidor HTTP encerrado.');
            process.exit(0);
        });
        
        // 3. Timeout para forçar o encerramento se o Baileys travar
        setTimeout(() => {
            logger.error('Shutdown timeout. Forçando encerramento.');
            process.exit(1);
        }, 10000); // 10 segundos para o Railway
    } else {
        process.exit(0);
    }
};

// Capturar os sinais de encerramento do sistema (Railway envia SIGTERM)
process.on('SIGTERM', () => gracefulShutdown('SIGTERM'));
process.on('SIGINT', () => gracefulShutdown('SIGINT'));
```

---

## 🔐 Autenticação e Conexão WhatsApp

### Código de Pareamento

O sistema utiliza **código de pareamento numérico** em vez de QR Code para conectar o WhatsApp Business.

**Como funciona:**

1. Quando o bot inicia e não está conectado, gera automaticamente um código de 8 dígitos
2. O código é válido por aproximadamente 90 segundos
3. Se expirar, um novo código é gerado automaticamente
4. O código pode ser obtido via endpoint `/api/whatsapp/status`

**Como parear:**

1. Abra o **WhatsApp Business** no seu celular
2. Toque em **Menu (⋮)** → **Aparelhos conectados**
3. Toque em **Conectar um dispositivo**
4. Selecione **Conectar via código**
5. Digite o código de 8 dígitos exibido no dashboard

**Endpoint para obter código:**

```bash
GET /api/whatsapp/status
Headers: X-Olika-Token: seu_token
```

**Resposta (não conectado):**
```json
{
  "connected": false,
  "pairingCode": "12345678",
  "user": null,
  "last_updated": "2025-01-27T18:30:00.000Z"
}
```

**Resposta (conectado):**
```json
{
  "connected": true,
  "pairingCode": null,
  "user": {
    "id": "5511999999999",
    "name": "Nome do WhatsApp"
  },
  "last_updated": "2025-01-27T18:30:00.000Z"
}
```

### Gerenciamento de Número do WhatsApp

O número do WhatsApp é gerenciado através do banco de dados Laravel, com prioridade:

1. **Banco de dados** (`whatsapp_settings.whatsapp_phone`) - **PRIORIDADE MÁXIMA**
2. Variável de ambiente (`WHATSAPP_PHONE`) - Fallback
3. Número padrão (`5571987019420`) - Último recurso

**Quando o número muda no dashboard:**
- O Laravel notifica automaticamente o bot via `/api/whatsapp/restart`
- O bot desconecta a conexão atual
- O bot busca o novo número do banco de dados
- O bot reconecta com o novo número

---

## ⚙️ Configuração

### 1. Variáveis de Ambiente - Laravel

**Arquivo:** `.env`

```env
# URL do bot WhatsApp no Railway
WHATSAPP_WEBHOOK_URL=https://olika-bot.up.railway.app/api/notify

# Token de autenticação (deve ser o mesmo do Railway)
WHATSAPP_WEBHOOK_TOKEN=olika_secret_token

# Código do país padrão (55 = Brasil)
WHATSAPP_DEFAULT_COUNTRY_CODE=55

# Timeout para requisições HTTP (em segundos)
WHATSAPP_WEBHOOK_TIMEOUT=10
```

**⚠️ IMPORTANTE:** 
- O `WHATSAPP_WEBHOOK_TOKEN` deve ser **exatamente igual** ao `API_SECRET` ou `WEBHOOK_TOKEN` no Railway
- Após configurar, execute: `php artisan config:clear`

---

### 2. Variáveis de Ambiente - Railway

**No painel do Railway → Variables:**

```env
# Token de autenticação (deve ser o mesmo do Laravel)
API_SECRET=olika_secret_token
WEBHOOK_TOKEN=olika_secret_token

# Porta do servidor (Railway usa 8080 por padrão)
PORT=8080

# URL da API do Laravel (para buscar número do WhatsApp do banco)
LARAVEL_API_URL=https://devdashboard.menuolika.com.br

# Número do WhatsApp (fallback se não encontrar no banco)
WHATSAPP_PHONE=5571987019420
```

**⚠️ IMPORTANTE:**
- O número do WhatsApp é buscado do banco de dados via API `/api/whatsapp/settings`
- A prioridade é: **Banco de dados > Variável de ambiente (.env) > Padrão**
- Se o número mudar no dashboard Laravel, o bot será notificado automaticamente via `/api/whatsapp/restart`

---

### 3. Dependências - Bot WhatsApp

**Arquivo:** `olika-whatsapp-integration/package.json`

```json
{
  "dependencies": {
    "@hapi/boom": "^10.0.1",
    "@whiskeysockets/baileys": "^6.6.0",
    "dotenv": "^16.3.1",
    "express": "^4.18.2",
    "pino": "^8.16.1",
    "qrcode-terminal": "^0.12.0"
  }
}
```

**Instalação:**

```bash
cd olika-whatsapp-integration
npm install
```

---

## 🔄 Fluxo de Execução Detalhado

### Cenário: Pedido muda de status para "preparing"

1. **Usuário altera status no dashboard**
   - Controller: `OrdersController::updateStatus()`
   - Método: `OrderStatusService::changeStatus()`

2. **OrderStatusService processa mudança**
   ```php
   $orderStatusService->changeStatus($order, 'preparing', $note);
   ```

3. **Evento é disparado**
   ```php
   $this->dispatchOrderEvent($order, 'preparing', $note);
   // Mapeia: 'preparing' => 'order_preparing'
   event(new OrderStatusUpdated($order, 'order_preparing', $note));
   ```

4. **Listener é acionado**
   ```php
   SendOrderWhatsAppNotification::handle($event);
   ```

5. **Listener valida e prepara**
   - Verifica se `WHATSAPP_WEBHOOK_URL` está configurado
   - Carrega dados do pedido (customer, items, address)
   - Normaliza telefone: `(11) 99999-9999` → `5511999999999`
   - Monta payload completo

6. **HTTP POST é enviado**
   ```php
   Http::timeout(10)
       ->asJson()
       ->withHeaders(['X-Olika-Token' => $token])
       ->post($webhookUrl, $payload);
   ```

7. **Bot recebe requisição**
   - Valida token de autenticação
   - Verifica se WhatsApp está conectado
   - Formata mensagem baseada no evento
   - Envia via Baileys

8. **Mensagem é entregue**
   - Cliente recebe notificação no WhatsApp
   - Bot retorna `{ success: true, messageId: "..." }`

9. **Logs são registrados**
   - Laravel: `✅ WhatsApp webhook enviado com sucesso`
   - Railway: `📩 Notificação enviada com sucesso`

---

## 📊 Logs e Monitoramento

### Logs do Laravel

**Localização:** `storage/logs/laravel.log`

**Logs de Sucesso:**
```
[2025-01-27 18:30:00] local.INFO: 📨 Disparando evento OrderStatusUpdated {"order_id":123,"status":"preparing","event":"order_preparing"}
[2025-01-27 18:30:00] local.INFO: 📤 SendOrderWhatsAppNotification executado {"order_id":123,"event":"order_preparing","webhook_url":"https://..."}
[2025-01-27 18:30:00] local.INFO: 📤 Tentando enviar para WhatsApp webhook {"order_id":123,"phone":"5511999999999"}
[2025-01-27 18:30:01] local.INFO: ✅ WhatsApp webhook enviado com sucesso! {"order_id":123,"attempt":1,"response_status":200}
```

**Logs de Erro:**
```
[2025-01-27 18:30:00] local.WARNING: ⚠️ WhatsApp webhook URL não configurado! Configure WHATSAPP_WEBHOOK_URL no .env
[2025-01-27 18:30:00] local.ERROR: ❌ Falha ao enviar payload WhatsApp webhook após 3 tentativas. {"last_error":{"status":502}}
```

**Logs de Retry:**
```
[2025-01-27 18:30:00] local.WARNING: WhatsApp webhook retorno de erro. {"attempt":1,"status":503}
[2025-01-27 18:30:00] local.INFO: ⏳ Aguardando 15 segundos antes de tentar novamente... {"next_attempt":2}
```

---

### Logs do Railway

**Localização:** Console do Railway (Logs do projeto)

**Logs de Sucesso:**
```
📩 Notificação enviada com sucesso {
  event: 'order_preparing',
  order_id: 123,
  order_number: 'OLK-0123',
  phone: '5511999999999',
  message_length: 156
}
```

**Logs de Erro:**
```
❌ Erro ao processar notificação {
  error: 'Socket não está conectado',
  body: { event: 'order_preparing', ... }
}
```

---

## 🧪 Testes

### 1. Teste via Rota (Recomendado)

**URL:** `https://dashboard.menuolika.com.br/test-whatsapp-notification`

**Requisitos:**
- Usuário autenticado
- Pedido existente com cliente e telefone

**Resposta:**
```json
{
  "success": true,
  "message": "Evento OrderStatusUpdated disparado com sucesso!",
  "order": {
    "id": 123,
    "number": "OLK-0123",
    "customer": "João Silva",
    "phone": "5511999999999"
  },
  "webhook_url": "https://olika-bot.up.railway.app/api/notify",
  "webhook_configured": true
}
```

---

### 2. Teste via Tinker

```bash
php artisan tinker
```

```php
$pedido = \App\Models\Order::with('customer')
    ->whereHas('customer', function($q) {
        $q->whereNotNull('phone')->where('phone', '!=', '');
    })
    ->first();

event(new \App\Events\OrderStatusUpdated($pedido, 'order_created', 'Teste de integração'));
```

---

### 3. Teste Manual do Bot

**Teste de Status:**
```bash
curl -X GET https://olika-bot.up.railway.app/api/whatsapp/status \
  -H "X-Olika-Token: seu_token_aqui"
```

**Teste de Notificação:**
```bash
curl -X POST https://olika-bot.up.railway.app/api/notify \
  -H "Content-Type: application/json" \
  -H "X-Olika-Token: seu_token_aqui" \
  -d '{
    "event": "order_created",
    "order": {
      "id": 1,
      "number": "OLK-0001",
      "total": 99.90
    },
    "customer": {
      "name": "Teste",
      "phone": "5511999999999"
    }
  }'
```

**Resposta Esperada:**
```json
{
  "success": true,
  "messageId": "3EB0C767F26BXXXX",
  "sent_at": "2025-01-27T18:30:00.000Z"
}
```

**Teste de Reinício (quando número muda):**
```bash
curl -X POST https://olika-bot.up.railway.app/api/whatsapp/restart \
  -H "X-Olika-Token: seu_token_aqui"
```

**Teste de Desconexão:**
```bash
curl -X POST https://olika-bot.up.railway.app/api/whatsapp/disconnect \
  -H "X-Olika-Token: seu_token_aqui"
```

---

## 🐛 Troubleshooting

### Problema: "WhatsApp webhook URL não configurado"

**Sintomas:**
```
⚠️ WhatsApp webhook URL não configurado! Configure WHATSAPP_WEBHOOK_URL no .env
```

**Solução:**
1. Verifique se `WHATSAPP_WEBHOOK_URL` está no `.env`
2. Execute `php artisan config:clear`
3. Verifique se não há espaços extras na URL

---

### Problema: "Access denied" (403)

**Sintomas:**
```
WhatsApp webhook retorno de erro. {"status":403}
```

**Solução:**
1. Verifique se `WHATSAPP_WEBHOOK_TOKEN` no Laravel é igual a `API_SECRET` no Railway
2. Verifique se o header `X-Olika-Token` está sendo enviado
3. Teste o token manualmente com `curl`

---

### Problema: "Bot não está respondendo" (502)

**Sintomas:**
```
❌ Bot WhatsApp não está respondendo (502 Bad Gateway)
```

**Solução:**
1. Verifique se o bot está rodando no Railway
2. Verifique os logs do Railway para erros
3. Teste o health check: `curl https://olika-bot.up.railway.app/`
4. Reinicie o serviço no Railway se necessário

---

### Problema: "WhatsApp não está conectado" (503)

**Sintomas:**
```
WhatsApp não está conectado. A mensagem será perdida.
```

**Solução:**
1. Verifique se o bot está conectado ao WhatsApp via `/api/whatsapp/status`
2. Verifique os logs do Railway para problemas de conexão
3. Se não estiver conectado, verifique se há código de pareamento disponível
4. Use o código de pareamento no WhatsApp Business para conectar
5. Se necessário, desconecte e reconecte via `/api/whatsapp/disconnect` e `/api/whatsapp/restart`

### Problema: "Timeout interno" (504)

**Sintomas:**
```
Timeout interno: aplicação não respondeu a tempo
```

**Solução:**
1. O endpoint `/api/notify` tem timeout de 8 segundos
2. Verifique se o WhatsApp está conectado e respondendo
3. Pode indicar que o WhatsApp está reconectando
4. O Laravel tentará novamente automaticamente (retry)

---

### Problema: Listener não executa

**Sintomas:**
- Não há logs do `SendOrderWhatsAppNotification`
- Evento é disparado mas nada acontece

**Solução:**
1. Verifique se o listener está registrado em `EventServiceProvider`
2. Execute `php artisan event:list` para ver eventos registrados
3. Verifique se `WHATSAPP_WEBHOOK_URL` está configurado (listener retorna early se não estiver)

---

### Problema: Status não dispara evento

**Sintomas:**
- Status muda mas evento não é disparado

**Solução:**
1. Verifique se o status está no mapeamento (`dispatchOrderEvent`)
2. Verifique se `$skipNotifications` é `false`
3. Adicione logs no `OrderStatusService` para debug

---

## 📁 Estrutura de Arquivos

### Laravel

```
app/
├── Events/
│   └── OrderStatusUpdated.php          # Evento
├── Listeners/
│   └── SendOrderWhatsAppNotification.php # Listener
├── Providers/
│   └── EventServiceProvider.php         # Registro
├── Services/
│   └── OrderStatusService.php           # Dispara eventos
└── Http/
    └── Controllers/
        └── Dashboard/
            └── SettingsController.php   # API /api/whatsapp/settings

config/
└── notifications.php                    # Configurações

routes/
└── web.php                              # Rota de teste
└── api.php                              # Rota /api/whatsapp/settings
```

### Bot WhatsApp

```
olika-whatsapp-integration/
├── src/
│   ├── app.js                           # Servidor Express
│   ├── services/
│   │   └── socket.js                    # Socket Baileys
│   └── config/
│       └── logger.js                    # Logger
├── auth_info_baileys/                  # Credenciais (por número)
│   └── {numero}/                        # Sessão por número
└── package.json                         # Dependências
```

**Nota:** As credenciais são armazenadas em `auth_info_baileys/{numero}/` para permitir múltiplas sessões por número.

---

## 🔒 Segurança

### Autenticação

- ✅ Token obrigatório em todos os endpoints protegidos
- ✅ Validação de token no header `X-Olika-Token`
- ✅ Tokens devem coincidir entre Laravel e Railway
- ✅ Logs de tentativas de acesso não autorizado

### Dados Sensíveis

- ✅ Números de telefone são normalizados mas não logados em produção
- ✅ Tokens nunca são expostos em logs
- ✅ Payloads são enviados via HTTPS

### Recomendações

1. Use tokens fortes e únicos
2. Não versionar arquivos `.env`
3. Rotacione tokens periodicamente
4. Monitore logs para tentativas de acesso não autorizado

---

## 📈 Performance

### Otimizações Implementadas

1. **Listener Síncrono**
   - Não usa fila (adequado para ambiente compartilhado)
   - Retry manual com intervalo configurável

2. **Eager Loading**
   - Carrega relacionamentos necessários em uma query
   - Evita N+1 queries

3. **Timeout Configurável**
   - Padrão: 10 segundos
   - Evita requisições travadas

4. **Retry Inteligente**
   - 3 tentativas com intervalo de 15 segundos
   - Não bloqueia o fluxo principal em caso de falha

---

## 🚀 Deploy

### Laravel

1. Configure variáveis de ambiente no servidor
2. Execute `php artisan config:clear`
3. Teste via rota `/test-whatsapp-notification`

### Railway

1. Conecte repositório GitHub ao Railway
2. Configure variáveis de ambiente
3. Deploy automático via GitHub Actions (se configurado)
4. Verifique logs após deploy

---

## 📝 Checklist de Implementação

### Laravel

- [x] Evento `OrderStatusUpdated` criado
- [x] Listener `SendOrderWhatsAppNotification` criado
- [x] Listener registrado no `EventServiceProvider`
- [x] Mapeamento de status implementado
- [x] Configuração centralizada em `config/notifications.php`
- [x] Evento disparado automaticamente no `OrderStatusService`
- [x] Logs implementados
- [x] Retry automático configurado
- [x] Rota de teste criada

### Bot WhatsApp

- [x] Endpoint `/api/notify` criado
- [x] Endpoint `/api/whatsapp/status` criado (com código de pareamento)
- [x] Endpoint `/api/whatsapp/disconnect` criado
- [x] Endpoint `/api/whatsapp/restart` criado
- [x] Função `sendMessage` exportada
- [x] Função `isConnected` implementada
- [x] Função `getWhatsAppPhone` implementada (busca do banco)
- [x] Autenticação implementada
- [x] Formatação de mensagens implementada
- [x] Health check endpoint criado
- [x] Tratamento de erros implementado
- [x] Timeout no `/api/notify` (8 segundos)
- [x] Graceful shutdown implementado
- [x] Código de pareamento (substitui QR Code)
- [x] Logs detalhados

### Configuração

- [ ] Variáveis de ambiente configuradas no Laravel
- [ ] Variáveis de ambiente configuradas no Railway
- [ ] Tokens coincidem entre sistemas
- [ ] Testes executados
- [ ] Logs verificados

---

## 📚 Referências

- **Baileys:** https://github.com/WhiskeySockets/Baileys
- **Railway:** https://railway.app
- **Laravel Events:** https://laravel.com/docs/events

---

## 🔄 Mudanças na Versão 1.1.0

### Novidades

1. **Código de Pareamento**
   - Substituição do QR Code por código numérico de 8 dígitos
   - Código expira em ~90 segundos
   - Geração automática via `requestPairingCode()` do Baileys

2. **Busca de Número do Banco de Dados**
   - Prioridade: Banco de dados > Variável de ambiente > Padrão
   - Endpoint Laravel: `/api/whatsapp/settings`
   - Busca automática na inicialização e reconexão

3. **Novos Endpoints**
   - `GET /api/whatsapp/status` - Status da conexão e código de pareamento
   - `POST /api/whatsapp/disconnect` - Desconectar manualmente
   - `POST /api/whatsapp/restart` - Reiniciar com novo número

4. **Melhorias de Performance**
   - Timeout de 8 segundos no `/api/notify` para evitar 502
   - Timeout interno de 6 segundos no envio de mensagem
   - Graceful shutdown para encerramento limpo

5. **Melhorias de Confiabilidade**
   - Variável global `isWhatsAppConnected` para estado mais preciso
   - Verificação de estado do WebSocket antes de enviar
   - Tratamento específico para timeouts

---

## ✅ Status Final

**Implementação:** ✅ Completa  
**Testes:** ⚠️ Pendente (após configuração)  
**Documentação:** ✅ Completa  
**Produção:** ⚠️ Aguardando configuração e testes

---

**Última atualização:** 2025-01-27  
**Versão:** 1.1.0  
**Autor:** Sistema Unificado da Olika

