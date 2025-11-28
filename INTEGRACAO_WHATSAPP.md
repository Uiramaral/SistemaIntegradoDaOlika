# 🔗 Integração Laravel → WhatsApp Bot (Railway)

## 📋 Visão Geral

Esta integração permite que o sistema Laravel envie automaticamente notificações de status de pedidos para os clientes via WhatsApp através do bot hospedado no Railway.

## 🏗️ Arquitetura

```
Laravel → Evento (OrderStatusUpdated) → Listener (SendOrderWhatsAppNotification) 
  → HTTP POST → Bot WhatsApp (Railway) → WhatsApp API → Cliente
```

## ⚙️ Configuração

### 1. Variáveis de Ambiente no Laravel (.env)

Adicione as seguintes variáveis ao seu arquivo `.env`:

```env
# URL do bot WhatsApp no Railway
WHATSAPP_WEBHOOK_URL=https://olika-bot.up.railway.app/api/notify

# Token de autenticação (deve ser o mesmo do bot)
WHATSAPP_WEBHOOK_TOKEN=olika_secret_token

# Código do país padrão (55 = Brasil)
WHATSAPP_DEFAULT_COUNTRY_CODE=55

# Timeout para requisições HTTP (em segundos)
WHATSAPP_WEBHOOK_TIMEOUT=10
```

### 2. Variáveis de Ambiente no Bot WhatsApp (Railway)

No painel do Railway, configure as seguintes variáveis:

```env
# Token de autenticação (deve ser o mesmo do Laravel)
API_SECRET=olika_secret_token
WEBHOOK_TOKEN=olika_secret_token

# Porta do servidor
PORT=3000
```

**⚠️ IMPORTANTE:** O valor de `WHATSAPP_WEBHOOK_TOKEN` no Laravel deve ser **exatamente igual** ao `API_SECRET` ou `WEBHOOK_TOKEN` no Railway.

## 📦 Componentes Implementados

### 1. Evento: `OrderStatusUpdated`

**Arquivo:** `app/Events/OrderStatusUpdated.php`

Disparado automaticamente quando o status de um pedido é atualizado.

**Parâmetros:**
- `$order` - Instância do pedido
- `$event` - Tipo de evento (`order_created`, `order_preparing`, `order_ready`, `order_completed`)
- `$note` - Observação opcional
- `$meta` - Metadados adicionais

### 2. Listener: `SendOrderWhatsAppNotification`

**Arquivo:** `app/Listeners/SendOrderWhatsAppNotification.php`

- ✅ Executa em **fila assíncrona** (não bloqueia a resposta)
- ✅ **3 tentativas** com backoff de 15 segundos
- ✅ Logs detalhados de sucesso/erro
- ✅ Normalização automática de telefone (adiciona código do país)

### 3. Bot WhatsApp Endpoints

**Arquivo:** `olika-whatsapp-integration/src/app.js`

#### `POST /api/notify` (Recomendado)

Endpoint profissional que processa payload completo do Laravel.

**Headers:**
- `X-Olika-Token` ou `X-Webhook-Token` ou `X-Api-Token`: Token de autenticação

**Payload:**
```json
{
  "event": "order_created",
  "order": {
    "id": 123,
    "number": "OLK-0123",
    "status": "confirmed",
    "total": 99.90
  },
  "customer": {
    "name": "João Silva",
    "phone": "5511999999999"
  },
  "phone": "5511999999999",
  "message": "Mensagem formatada (opcional)"
}
```

**Resposta:**
```json
{
  "success": true,
  "messageId": "3EB0C767F26BXXXX",
  "sent_at": "2025-01-27T18:30:00.000Z"
}
```

#### `POST /send-message` (Compatibilidade)

Endpoint simples para envio direto.

**Payload:**
```json
{
  "number": "5511999999999",
  "message": "Mensagem de texto"
}
```

## 🔄 Fluxo de Execução

1. **Pedido é atualizado** no Laravel (ex: status muda para "confirmed")
2. **OrderStatusService** detecta a mudança e dispara o evento `OrderStatusUpdated`
3. **SendOrderWhatsAppNotification** (listener) é executado em fila
4. Listener faz **HTTP POST** para `/api/notify` do bot
5. Bot formata a mensagem e envia via WhatsApp
6. Cliente recebe a notificação no WhatsApp

## 📝 Eventos Mapeados

| Status do Pedido | Evento | Mensagem |
|------------------|--------|----------|
| `pending` / `confirmed` | `order_created` | ✅ Pedido Confirmado! |
| `preparing` | `order_preparing` | 👩‍🍳 Pedido em Preparo |
| `ready` | `order_ready` | 🚗 Pedido Pronto para Entrega |
| `delivered` | `order_completed` | 🎉 Pedido Entregue |

## 🧪 Testando a Integração

### Teste Manual via Tinker

```bash
php artisan tinker
```

```php
$pedido = \App\Models\Order::first();
event(new \App\Events\OrderStatusUpdated($pedido, 'order_created'));
```

### Teste via API do Bot

```bash
curl -X POST https://olika-bot.up.railway.app/api/notify \
  -H "Content-Type: application/json" \
  -H "X-Olika-Token: olika_secret_token" \
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

## 📊 Logs

### Laravel

Os logs são salvos em `storage/logs/laravel.log`:

```
[2025-01-27 18:30:00] local.INFO: WhatsApp webhook enviado com sucesso. {"order_id":123,"event":"order_created"}
```

### Bot WhatsApp (Railway)

Os logs aparecem no console do Railway:

```
📩 Notificação enviada com sucesso { event: 'order_created', order_id: 123, phone: '5511999999999' }
```

## 🔒 Segurança

- ✅ Autenticação via token no header
- ✅ Validação de payload no bot
- ✅ Timeout configurável para evitar travamentos
- ✅ Retry automático em caso de falha
- ✅ Logs de tentativas de acesso não autorizado

## 🐛 Troubleshooting

### Bot não recebe notificações

1. Verifique se o token está correto em ambos os lados
2. Verifique se a URL está acessível: `curl https://olika-bot.up.railway.app/`
3. Verifique os logs do Railway para erros
4. Verifique se o WhatsApp está conectado: `curl https://olika-bot.up.railway.app/` deve retornar `{"connected": true}`

### Mensagens não chegam ao cliente

1. Verifique se o número está no formato correto (com código do país)
2. Verifique se o cliente tem o número salvo no WhatsApp
3. Verifique os logs do bot para erros de envio

### Listener não executa

1. Verifique se a fila está rodando: `php artisan queue:work`
2. Verifique se o evento está sendo disparado (logs)
3. Verifique se o listener está registrado em `EventServiceProvider`

## 📚 Arquivos Relacionados

- `app/Events/OrderStatusUpdated.php` - Evento
- `app/Listeners/SendOrderWhatsAppNotification.php` - Listener
- `app/Providers/EventServiceProvider.php` - Registro do listener
- `app/Services/OrderStatusService.php` - Serviço que dispara eventos
- `config/notifications.php` - Configurações
- `olika-whatsapp-integration/src/app.js` - Bot WhatsApp
- `olika-whatsapp-integration/src/services/socket.js` - Socket WhatsApp

## ✅ Checklist de Implementação

- [x] Evento `OrderStatusUpdated` criado
- [x] Listener `SendOrderWhatsAppNotification` criado
- [x] Listener registrado no `EventServiceProvider`
- [x] Endpoint `/api/notify` no bot criado
- [x] Função `sendMessage` exportada no socket.js
- [x] Configuração centralizada em `config/notifications.php`
- [x] Evento disparado automaticamente no `OrderStatusService`
- [x] Logs implementados
- [x] Fila assíncrona configurada
- [x] Retry automático configurado

## 🚀 Próximos Passos

1. Configure as variáveis de ambiente no `.env` do Laravel
2. Configure as variáveis de ambiente no Railway
3. Execute `php artisan queue:work` para processar a fila
4. Teste alterando o status de um pedido no dashboard
5. Verifique os logs para confirmar o envio

---

**Status:** ✅ Implementação completa e pronta para uso!

