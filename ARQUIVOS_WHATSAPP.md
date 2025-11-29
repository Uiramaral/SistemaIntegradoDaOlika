# 📋 Arquivos Relacionados ao Envio de Mensagens WhatsApp

## 🎯 Arquivos Principais da Integração

### Eventos e Listeners
- `app/Events/OrderStatusUpdated.php` - Evento disparado quando status muda
- `app/Listeners/SendOrderWhatsAppNotification.php` - Listener que envia via webhook
- `app/Providers/EventServiceProvider.php` - Registro do listener

### Serviços
- `app/Services/OrderStatusService.php` - Dispara eventos e mapeia status
- `app/Services/WhatsAppService.php` - Serviço antigo (manter para compatibilidade)
- `app/Services/BotConversaService.php` - Integração antiga BotConversa

### Controllers
- `app/Http/Controllers/Dashboard/OrdersController.php` - Atualização de status
- `app/Http/Controllers/Dashboard/DashboardController.php` - Otimizações

### Configuração
- `config/notifications.php` - Configurações centralizadas

### Bot WhatsApp (src/)
- `src/services/socket.js` - Socket WhatsApp (restaurado)
- `src/app.js` - Servidor Express do bot

### Rotas
- `routes/web.php` - Rota de teste `/test-whatsapp-notification`

### Documentação
- `INTEGRACAO_WHATSAPP.md` - Guia completo
- `DIAGNOSTICO_WHATSAPP.md` - Troubleshooting
- `VERIFICACAO_INTEGRACAO_WHATSAPP.md` - Checklist
- `COMO_CONFIGURAR_WHATSAPP.md` - Configuração passo a passo
- `CORRECAO_STATUS_OUT_FOR_DELIVERY.md` - Correção de mapeamento

### Performance
- `database/indexes_performance.sql` - Índices para otimização
- `OTIMIZACAO_DASHBOARD.md` - Documentação de otimizações

---

## 📦 Arquivos para Commit

### Core da Integração
1. `app/Events/OrderStatusUpdated.php`
2. `app/Listeners/SendOrderWhatsAppNotification.php`
3. `app/Providers/EventServiceProvider.php`
4. `app/Services/OrderStatusService.php`
5. `config/notifications.php`

### Bot WhatsApp
6. `src/services/socket.js`
7. `src/app.js`

### Otimizações
8. `app/Http/Controllers/Dashboard/DashboardController.php`
9. `app/Http/Controllers/Dashboard/OrdersController.php`

### Rotas
10. `routes/web.php`

### Documentação
11. `*.md` (todos os arquivos de documentação)

### Database
12. `database/indexes_performance.sql`

---

## ❌ Excluídos do Commit

- `.env` e `.env prod` - Arquivos de configuração local
- `olika-whatsapp-integration/` - Pasta do bot (projeto separado)
- `storage/logs/` - Logs locais
- `node_modules/` - Dependências

