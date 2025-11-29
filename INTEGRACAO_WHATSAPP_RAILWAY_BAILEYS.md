# 📱 Integração Laravel ↔ WhatsApp (Railway + Baileys)

## 📋 Sumário Executivo

Esta documentação descreve a integração completa entre o sistema Laravel e o bot WhatsApp hospedado no Railway, utilizando a biblioteca Baileys para comunicação com a API do WhatsApp. A integração permite o envio automático de notificações de status de pedidos para clientes via WhatsApp.

**Versão:** 1.0.0  
**Data:** Janeiro 2025  
**Status:** ✅ Implementado e Funcional

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
  if (!globalSock) {
    throw new Error('Socket não está conectado.');
  }
  
  // Normaliza telefone: 5511999999999 -> 5511999999999@s.whatsapp.net
  let normalizedPhone = phone.replace(/\D/g, '');
  if (!phone.includes('@s.whatsapp.net')) {
    normalizedPhone = `${normalizedPhone}@s.whatsapp.net`;
  }
  
  const result = await globalSock.sendMessage(normalizedPhone, { text: message });
  
  return {
    success: true,
    messageId: result?.key?.id,
  };
};
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
    timestamp: new Date().toISOString()
  });
});
```

**Resposta:**
```json
{
  "status": "running",
  "connected": true,
  "timestamp": "2025-01-27T18:30:00.000Z"
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

##### `POST /api/notify` - Endpoint Principal (Laravel)

```javascript
app.post('/api/notify', requireAuth, async (req, res) => {
  const { event, order, customer, phone, message } = req.body;
  
  // Validação
  if (!phone && !customer?.phone) {
    return res.status(400).json({ 
      error: 'Telefone do cliente é obrigatório' 
    });
  }

  if (!isConnected()) {
    return res.status(503).json({ 
      error: 'WhatsApp não está conectado.',
      retry: true 
    });
  }

  // Determina telefone
  const targetPhone = phone || customer?.phone;
  
  // Formata mensagem
  let finalMessage = message;
  if (!finalMessage && order) {
    finalMessage = formatOrderMessage(event, order, customer);
  }
  
  // Envia
  const result = await sendMessage(targetPhone, finalMessage);
  
  res.json({
    success: true,
    messageId: result.messageId,
    sent_at: new Date().toISOString()
  });
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
    
    if (!API_TOKEN && !WEBHOOK_TOKEN) {
        return res.status(500).json({ error: 'Configuração de servidor inválida' });
    }

    const validToken = token === API_TOKEN || token === WEBHOOK_TOKEN;
    
    if (validToken) {
        next();
    } else {
        res.status(403).json({ error: 'Acesso negado' });
    }
};
```

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

# Porta do servidor
PORT=3000
```

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
1. Verifique se o bot está conectado ao WhatsApp
2. Verifique os logs do Railway para problemas de conexão
3. Pode ser necessário reautenticar (gerar novo QR Code)

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
└── Services/
    └── OrderStatusService.php           # Dispara eventos

config/
└── notifications.php                    # Configurações

routes/
└── web.php                              # Rota de teste
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
└── package.json                         # Dependências
```

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
- [x] Função `sendMessage` exportada
- [x] Autenticação implementada
- [x] Formatação de mensagens implementada
- [x] Health check endpoint criado
- [x] Tratamento de erros implementado
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

## ✅ Status Final

**Implementação:** ✅ Completa  
**Testes:** ⚠️ Pendente (após configuração)  
**Documentação:** ✅ Completa  
**Produção:** ⚠️ Aguardando configuração e testes

---

**Última atualização:** 2025-01-27  
**Versão:** 1.0.0  
**Autor:** Sistema Unificado da Olika

