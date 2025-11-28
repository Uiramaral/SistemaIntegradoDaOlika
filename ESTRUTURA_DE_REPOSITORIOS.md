# Estrutura de Repositórios – Olika Ecosystem

Este documento descreve como os componentes do ecossistema Olika estão distribuídos em diferentes repositórios e como eles se relacionam.

## Visão Geral

| Repositório | Conteúdo | Observações |
|-------------|----------|-------------|
| `Uiramaral/SistemaIntegradoDaOlika` | Aplicação Laravel completa (Dashboard, PDV, APIs, integrações locais) | Mantém todo o backend principal, filas, eventos e templates Blade |
| `Uiramaral/olika-whatsapp-integration` | Bot WhatsApp (Node.js + Baileys) com CI/CD Railway | Repositório desacoplado para facilitar deploys e evitar dependências do Laravel |

## Fluxo de Trabalho

1. **Eventos no Laravel**
   - `App\Events\OrderStatusUpdated` é disparado sempre que um pedido muda de status.
   - `App\Listeners\SendOrderWhatsAppNotification` transforma o evento em payload HTTP e envia para o bot configurado.

2. **Bot WhatsApp**
   - Recebe requisições na rota `/api/notify`.
   - Formata mensagens, aplica templates e envia via sessão autenticada Baileys.
   - Executa no Railway com deploy automático (`.github/workflows/deploy.yml`).

3. **Deploy Automatizado**
   - `sync-to-github.ps1` no monorepo copia a pasta `olika-whatsapp-integration/` para um clone limpo e faz `git push`.
   - GitHub Actions (`railwayapp/railway-action@v3`) aciona o deploy no Railway.

## Referências Cruzadas

| Item | Local |
|------|-------|
| Webhook URL/token | `.env` do Laravel (`WHATSAPP_WEBHOOK_URL`, `WHATSAPP_WEBHOOK_TOKEN`) |
| Documentação bot | `olika-whatsapp-integration/README.md` |
| Boas práticas de segurança | `olika-whatsapp-integration/SECURITY_AND_PUSH_PROTECTION.md` |

## Próximos Passos

- Manter ambos os repositórios sincronizados via script (`sync-to-github.ps1`).
- Registrar links diretos nesta página para facilitar onboarding de novos colaboradores.

