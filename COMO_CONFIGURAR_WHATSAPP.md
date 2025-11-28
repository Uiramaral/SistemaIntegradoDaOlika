# 📝 Como Configurar a URL do WhatsApp

## 📍 Localização do Arquivo

O arquivo de configuração está em:
```
.env prod
```

**⚠️ IMPORTANTE:** 
- Se você estiver usando um arquivo `.env` diferente no servidor, configure lá também
- O nome do arquivo pode variar (`.env`, `.env.production`, etc.)

---

## 🔧 Configuração Passo a Passo

### 1. Abra o arquivo `.env prod`

### 2. Localize a seção de WhatsApp (linhas 94-98)

Você verá algo assim:
```env
WHATSAPP_API_URL=
WHATSAPP_API_KEY=
WHATSAPP_WEBHOOK_URL=
WHATSAPP_SESSION_NAME=olika_session
WHATSAPP_DEFAULT_PHONE=5571999999999
```

### 3. Adicione as seguintes linhas:

```env
# URL do bot WhatsApp no Railway (NOVO - Integração com bot Railway)
WHATSAPP_WEBHOOK_URL=https://olika-bot.up.railway.app/api/notify

# Token de autenticação (deve ser o mesmo configurado no Railway)
WHATSAPP_WEBHOOK_TOKEN=olika_secret_token

# Código do país padrão (55 = Brasil)
WHATSAPP_DEFAULT_COUNTRY_CODE=55

# Timeout para requisições HTTP (em segundos)
WHATSAPP_WEBHOOK_TIMEOUT=10
```

### 4. Substitua o Token

**⚠️ IMPORTANTE:** Substitua `olika_secret_token` pelo token real que você configurou no Railway.

Para encontrar o token:
1. Acesse o painel do Railway
2. Vá em **Variables** do seu projeto do bot
3. Procure por `API_SECRET` ou `WEBHOOK_TOKEN`
4. Copie o valor e cole no `.env`

---

## ✅ Exemplo Completo

Sua seção de WhatsApp deve ficar assim:

```env
WHATSAPP_API_URL=
WHATSAPP_API_KEY=
# URL do bot WhatsApp no Railway (NOVO - Integração com bot Railway)
WHATSAPP_WEBHOOK_URL=https://olika-bot.up.railway.app/api/notify
# Token de autenticação (deve ser o mesmo configurado no Railway)
WHATSAPP_WEBHOOK_TOKEN=seu_token_aqui_123456
# Código do país padrão (55 = Brasil)
WHATSAPP_DEFAULT_COUNTRY_CODE=55
# Timeout para requisições HTTP (em segundos)
WHATSAPP_WEBHOOK_TIMEOUT=10
WHATSAPP_SESSION_NAME=olika_session
WHATSAPP_DEFAULT_PHONE=5571999999999
```

---

## 🔄 Após Configurar

### 1. Limpar Cache

Execute no servidor (ou via SSH):

```bash
php artisan config:clear
php artisan cache:clear
```

### 2. Verificar Configuração

Teste se está sendo lida corretamente:

```bash
php artisan tinker
```

```php
config('notifications.wa_webhook_url');
// Deve retornar: "https://olika-bot.up.railway.app/api/notify"

config('notifications.wa_token');
// Deve retornar: "seu_token_aqui_123456"
```

### 3. Testar Envio

Acesse (logado no dashboard):
```
https://dashboard.menuolika.com.br/test-whatsapp-notification
```

Ou altere o status de um pedido real no dashboard.

---

## 🐛 Troubleshooting

### Problema: "WhatsApp webhook URL não configurado"

**Causa:** A URL não está sendo lida do `.env`

**Solução:**
1. Verifique se você editou o arquivo `.env` correto (pode haver `.env`, `.env.production`, etc.)
2. Execute `php artisan config:clear`
3. Verifique se não há espaços extras na URL
4. Reinicie o servidor (se necessário)

### Problema: "Access denied" (403)

**Causa:** Token não coincide entre Laravel e Railway

**Solução:**
1. Verifique se `WHATSAPP_WEBHOOK_TOKEN` no Laravel é **exatamente igual** ao `API_SECRET` no Railway
2. Não deve ter espaços antes ou depois
3. Execute `php artisan config:clear` após alterar

### Problema: URL não funciona

**Causa:** URL incorreta ou bot não está rodando

**Solução:**
1. Teste a URL manualmente:
   ```bash
   curl https://olika-bot.up.railway.app/
   ```
   Deve retornar: `{"status":"running","connected":true}`

2. Verifique no Railway se o bot está rodando
3. Verifique se a URL termina com `/api/notify`

---

## 📋 Checklist

- [ ] Arquivo `.env` (ou `.env prod`) aberto
- [ ] `WHATSAPP_WEBHOOK_URL` configurado com URL do Railway
- [ ] `WHATSAPP_WEBHOOK_TOKEN` configurado com token do Railway
- [ ] Token coincide entre Laravel e Railway
- [ ] Cache limpo (`php artisan config:clear`)
- [ ] Configuração verificada via `php artisan tinker`
- [ ] Teste executado

---

## 🔗 Links Úteis

- **Railway Dashboard:** https://railway.app
- **Documentação da Integração:** `INTEGRACAO_WHATSAPP.md`
- **Diagnóstico Completo:** `DIAGNOSTICO_WHATSAPP.md`

---

**Última atualização:** 2025-01-27

