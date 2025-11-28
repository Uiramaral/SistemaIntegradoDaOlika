# 🔍 Diagnóstico: Mensagens WhatsApp Não Estão Sendo Enviadas

## ❌ Problema Identificado

### Análise do `laravel.log`:

1. **WhatsAppService desabilitado:**
   ```
   [2025-11-28 15:26:11] local.DEBUG: WhatsAppService: configuração não encontrada (whatsapp_settings). Serviço desativado.
   ```

2. **BotConversa funcionando:**
   ```
   [2025-11-28 15:26:11] local.INFO: BotConversa: Enviando webhook {"status":200,"success":true}
   ```

3. **Nenhum log do SendOrderWhatsAppNotification:**
   - O listener não está sendo executado ou está retornando early

### Causa Raiz:

**`WHATSAPP_WEBHOOK_URL` está vazio no `.env`**

No arquivo `.env prod` (linha 96):
```env
WHATSAPP_WEBHOOK_URL=
```

O listener verifica se a URL está configurada e retorna early se estiver vazia:

```php
if (empty($webhookUrl)) {
    Log::debug('WhatsApp webhook URL não configurado, ignorando disparo.');
    return; // ← Para aqui e não envia nada
}
```

---

## ✅ Solução

### 1. Configurar Variáveis no `.env`

Adicione as seguintes variáveis ao seu arquivo `.env` (ou `.env prod`):

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
- Substitua `olika_secret_token` pelo token real configurado no Railway
- A URL deve terminar com `/api/notify`

### 2. Limpar Cache de Configuração

Após atualizar o `.env`, execute:

```bash
php artisan config:clear
php artisan cache:clear
```

### 3. Verificar Configuração

Teste se a configuração está sendo lida corretamente:

```bash
php artisan tinker
```

```php
config('notifications.wa_webhook_url');
config('notifications.wa_token');
```

Deve retornar os valores configurados.

---

## 🧪 Teste de Envio

### Opção 1: Via Rota de Teste

1. Acesse (logado no dashboard):
   ```
   https://dashboard.menuolika.com.br/test-whatsapp-notification
   ```

2. Verifique os logs:
   ```bash
   tail -f storage/logs/laravel.log | grep -i "whatsapp"
   ```

### Opção 2: Via Tinker

```bash
php artisan tinker
```

```php
$pedido = \App\Models\Order::with('customer')->whereHas('customer', function($q) {
    $q->whereNotNull('phone')->where('phone', '!=', '');
})->first();

event(new \App\Events\OrderStatusUpdated($pedido, 'order_created', 'Teste'));
```

### Opção 3: Alterar Status de Pedido Real

1. Acesse um pedido no dashboard
2. Altere o status (ex: para "confirmed" ou "preparing")
3. Verifique os logs

---

## 📊 Logs Esperados Após Configuração

### Logs de Sucesso:

```
[2025-01-27 XX:XX:XX] local.INFO: WhatsApp webhook enviado com sucesso. {"order_id":123,"event":"order_created","attempt":1}
```

### Logs de Erro (se houver problema):

```
[2025-01-27 XX:XX:XX] local.WARNING: WhatsApp webhook retorno de erro. {"attempt":1,"status":403,"order_id":123}
```

ou

```
[2025-01-27 XX:XX:XX] local.ERROR: Falha ao enviar payload WhatsApp webhook após 3 tentativas. {"order_id":123,"event":"order_created"}
```

---

## 🔍 Verificações Adicionais

### 1. Verificar se o Evento Está Sendo Disparado

Adicione log temporário no `OrderStatusService`:

```php
// Em app/Services/OrderStatusService.php, linha ~404
Log::info('Evento OrderStatusUpdated disparado', [
    'order_id' => $order->id,
    'status' => $status,
    'event' => $map[$status] ?? null
]);
event(new OrderStatusUpdated($order, $map[$status], $note));
```

### 2. Verificar se o Listener Está Sendo Chamado

O listener já tem logs, mas você pode adicionar um log no início:

```php
// Em app/Listeners/SendOrderWhatsAppNotification.php, início do método handle()
Log::info('SendOrderWhatsAppNotification executado', [
    'order_id' => $event->order->id,
    'event' => $event->event,
]);
```

### 3. Verificar Configuração do Railway

No painel do Railway, verifique:
- ✅ Bot está rodando
- ✅ Variável `API_SECRET` ou `WEBHOOK_TOKEN` configurada
- ✅ Endpoint `/api/notify` está acessível

Teste o endpoint:

```bash
curl -X GET https://olika-bot.up.railway.app/
```

Deve retornar:
```json
{"status":"running","connected":true}
```

---

## 🐛 Troubleshooting

### Problema: "WhatsApp webhook URL não configurado"

**Causa:** `WHATSAPP_WEBHOOK_URL` está vazio ou não está sendo lido

**Solução:**
1. Verifique o `.env` (não `.env.example`)
2. Execute `php artisan config:clear`
3. Verifique se não há espaços extras na URL
4. Reinicie o servidor (se necessário)

### Problema: "Access denied" (403)

**Causa:** Token não coincide entre Laravel e Railway

**Solução:**
1. Verifique se `WHATSAPP_WEBHOOK_TOKEN` no Laravel é igual a `API_SECRET` no Railway
2. Verifique se o header está sendo enviado corretamente

### Problema: "Connection timeout" ou "Failed to connect"

**Causa:** Bot não está acessível ou URL incorreta

**Solução:**
1. Verifique se a URL está correta
2. Verifique se o bot está rodando no Railway
3. Teste a URL manualmente com `curl`

### Problema: Listener não executa

**Causa:** Listener não está registrado ou evento não está sendo disparado

**Solução:**
1. Verifique `app/Providers/EventServiceProvider.php`
2. Execute `php artisan event:list` para ver eventos registrados
3. Adicione logs no `OrderStatusService` para verificar se o evento está sendo disparado

---

## ✅ Checklist de Verificação

- [ ] `WHATSAPP_WEBHOOK_URL` configurado no `.env`
- [ ] `WHATSAPP_WEBHOOK_TOKEN` configurado no `.env`
- [ ] Token coincide com Railway
- [ ] Cache limpo (`php artisan config:clear`)
- [ ] Bot Railway está rodando e acessível
- [ ] Teste executado via rota ou tinker
- [ ] Logs verificados após teste
- [ ] Mensagem recebida no WhatsApp (se tudo estiver OK)

---

## 📝 Próximos Passos

1. **Configure as variáveis** no `.env`
2. **Limpe o cache**: `php artisan config:clear`
3. **Teste** via rota `/test-whatsapp-notification`
4. **Verifique os logs** para confirmar o envio
5. **Teste com pedido real** alterando status

---

**Última atualização:** 2025-01-27
**Status:** ⚠️ Aguardando configuração de `WHATSAPP_WEBHOOK_URL` no `.env`

