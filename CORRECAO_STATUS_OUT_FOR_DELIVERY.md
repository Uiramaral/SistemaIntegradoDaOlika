# 🔧 Correção: Status "out_for_delivery" não estava mapeado

## ❌ Problema Identificado

O status `out_for_delivery` não estava no mapeamento do evento `OrderStatusUpdated`, então quando um pedido mudava para esse status, o evento não era disparado.

### Logs Mostravam:
```
[2025-11-28 15:33:36] local.WARNING: OrderStatusService: WhatsAppService desabilitado
{"order_id":115,"status_code":"out_for_delivery"}
```

Mas **não havia logs** de:
- `📨 Disparando evento OrderStatusUpdated`
- `📤 SendOrderWhatsAppNotification executado`

Isso significa que o evento não estava sendo disparado.

---

## ✅ Correção Aplicada

Adicionado `out_for_delivery` ao mapeamento de eventos:

```php
$map = [
    'pending' => 'order_created',
    'confirmed' => 'order_created',
    'preparing' => 'order_preparing',
    'ready' => 'order_ready',
    'out_for_delivery' => 'order_ready', // ✅ ADICIONADO
    'delivered' => 'order_completed',
];
```

Agora quando um pedido mudar para `out_for_delivery`, o evento será disparado como `order_ready` (pedido a caminho).

---

## 🧪 Como Testar

1. **Altere o status de um pedido** para "Saiu para entrega" (out_for_delivery)
2. **Verifique os logs** - você deve ver:

```
[2025-01-27 XX:XX:XX] local.INFO: 📨 Disparando evento OrderStatusUpdated {"order_id":115,"status":"out_for_delivery","event":"order_ready"}
[2025-01-27 XX:XX:XX] local.INFO: 📤 SendOrderWhatsAppNotification executado {"order_id":115,"event":"order_ready","webhook_url":"https://..."}
[2025-01-27 XX:XX:XX] local.INFO: WhatsApp webhook enviado com sucesso. {"order_id":115,"attempt":1}
```

---

## 📋 Status Mapeados

| Status Interno | Evento WhatsApp | Descrição |
|----------------|-----------------|-----------|
| `pending` | `order_created` | Pedido criado |
| `confirmed` | `order_created` | Pedido confirmado |
| `preparing` | `order_preparing` | Pedido em preparo |
| `ready` | `order_ready` | Pedido pronto |
| `out_for_delivery` | `order_ready` | Pedido a caminho ✅ |
| `delivered` | `order_completed` | Pedido entregue |

---

## ⚠️ Importante

Se você ainda não configurou o `.env`, o listener retornará early com:

```
⚠️ WhatsApp webhook URL não configurado! Configure WHATSAPP_WEBHOOK_URL no .env
```

Configure o `.env` conforme o arquivo `COMO_CONFIGURAR_WHATSAPP.md`.

---

**Última atualização:** 2025-01-27
**Status:** ✅ Correção aplicada - Teste alterando status para "out_for_delivery"

