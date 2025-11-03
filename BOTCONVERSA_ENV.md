# Variáveis de Ambiente - BotConversa

Adicione as seguintes variáveis ao seu arquivo `.env`:

```env
# BotConversa Webhook Configuration
BOTCONVERSA_WEBHOOK_URL=https://new-backend.botconversa.com.br/api/v1/webhooks-automation/catch/YOUR_WEBHOOK_ID/YOUR_WEBHOOK_TOKEN
BOTCONVERSA_PAID_WEBHOOK_URL=https://new-backend.botconversa.com.br/api/v1/webhooks-automation/catch/YOUR_WEBHOOK_ID/YOUR_WEBHOOK_TOKEN
BOTCONVERSA_TOKEN=
```

## Descrição das Variáveis

- **BOTCONVERSA_WEBHOOK_URL**: URL padrão do webhook para envio de notificações
- **BOTCONVERSA_PAID_WEBHOOK_URL**: URL específica para pedidos pagos (opcional, se vazia usa a URL padrão)
- **BOTCONVERSA_TOKEN**: Token de autenticação Bearer (opcional)

## Prioridade de Configuração

O sistema usa as configurações na seguinte ordem de prioridade:

1. **Configurações do Dashboard** (tabela `settings` no banco de dados)
2. **Variáveis de ambiente** (arquivo `.env`)
3. **Config do Laravel** (`config/services.php`)

## Exemplo

```env
BOTCONVERSA_WEBHOOK_URL=https://new-backend.botconversa.com.br/api/v1/webhooks-automation/catch/20351/QXtIfFUMyCfV/
BOTCONVERSA_PAID_WEBHOOK_URL=https://new-backend.botconversa.com.br/api/v1/webhooks-automation/catch/20351/QXtIfFUMyCfV/
BOTCONVERSA_TOKEN=seu_token_aqui_se_necessario
```

