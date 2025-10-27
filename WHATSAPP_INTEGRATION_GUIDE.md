# 📱 Guia de Integração WhatsApp

## ✅ Arquivos Criados

### Migration
- ✅ `2024_01_01_000020_create_whatsapp_settings_table.php`

### Service
- ✅ `app/Services/WhatsAppService.php` - Atualizado com API não oficial

### Controller
- ✅ `app/Http/Controllers/WebhookController.php` - Integrado envio automático de WhatsApp

## 🚀 Configuração Inicial

### 1. Execute a Migration

```bash
php artisan migrate
```

### 2. Configure sua Instância WhatsApp (Evolution API / Green API / Baileys)

Insira suas credenciais na tabela `whatsapp_settings`:

```sql
INSERT INTO whatsapp_settings (instance_name, api_url, api_key, sender_name, active)
VALUES (
    'olika_main',
    'https://seuservidor.whatsappapi.com',
    'CHAVE_API_AQUI',
    'Olika Atendimento',
    1
);
```

### 3. Configure Telefone do Admin (Opcional)

```sql
INSERT INTO settings (key, value)
VALUES ('whatsapp_admin_phone', '55719987654321');
```

## 📱 Como Funciona

### Fluxo Automático

Quando um pedido tem pagamento aprovado (`status = 'approved'`):

1. **Webhook recebe notificação do Mercado Pago**
2. **Status muda para `paid`**
3. **Cupom é registrado (se aplicado)**
4. **WhatsApp é enviado automaticamente:**
   - ✅ Para o cliente (confirmação de pagamento)
   - 💼 Para o admin (notificação de novo pedido)

### Mensagens Enviadas

#### Para o Cliente:
```
✅ Pagamento confirmado!

Olá, João Silva!
Seu pedido #20240115123456 foi confirmado com sucesso.

📦 Valor: R$ 45,90
🕒 Em breve entraremos em contato para entrega.

Atenciosamente,
Equipe Olika 🥖
```

#### Para o Admin:
```
💰 Novo Pedido Pago

Pedido: #20240115123456
Cliente: João Silva
Total: R$ 45,90
Forma: PIX
```

## 🧪 Teste Manual

### Via Artisan Tinker

```bash
php artisan tinker
```

```php
use App\Services\WhatsAppService;

$wa = new WhatsAppService();
$wa->sendText('55719987654321', 'Teste Olika - integração ativa! 🧡');
```

### Via Controller

Crie uma rota de teste:

```php
Route::get('/test-whatsapp', function() {
    $wa = new \App\Services\WhatsAppService();
    
    $result = $wa->sendText('55719987654321', 'Teste Olika Bot!');
    
    return response()->json(['ok' => $result]);
});
```

## 🔧 Endpoints Suportados

O serviço é compatível com:

- ✅ **Evolution API** - `/message/text`
- ✅ **Green API** - `/message/text`
- ✅ **Baileys (Node)** - `/message/text`
- ✅ **Chat-API** - `/message/text`

### Formato da Requisição

```json
{
  "number": "55719987654321",
  "message": "Texto da mensagem"
}
```

### Headers

```
Content-Type: application/json
Authorization: CHAVE_API_AQUI
```

## 📝 Personalização

### Mudar Mensagem de Confirmação

Edite o método `sendPaymentConfirmed()` em `WhatsAppService.php`:

```php
public function sendPaymentConfirmed(Order $order)
{
    $message = "Sua mensagem personalizada aqui\n\n"
              . "Pedido: #{$order->order_number}";
              
    return $this->sendText($order->customer->phone, $message);
}
```

### Templates com Variáveis

```php
$wa->sendTemplate($phone, 'Olá {nome}! Seu pedido {pedido} está pronto.', [
    'nome' => 'João',
    'pedido' => '12345'
]);
```

## ⚠️ Troubleshooting

### WhatsApp não envia

1. Verifique se a instância está online no servidor
2. Verifique os logs: `storage/logs/laravel.log`
3. Teste a API manualmente via cURL

### cURL error

```bash
# Teste direto na API
curl -X POST https://seuservidor.whatsappapi.com/message/text \
  -H "Content-Type: application/json" \
  -H "Authorization: CHAVE_API_AQUI" \
  -d '{"number":"55719987654321","message":"Teste"}'
```

### Número não formatado

O serviço formata automaticamente:
- `71998765432` → `55719987654321`
- `(71) 99876-5432` → `55719987654321`

## 📊 Logs

Todas as tentativas de envio são logadas em:
```
storage/logs/laravel.log
```

Procure por:
- `Enviando WhatsApp`
- `WhatsApp cURL error`
- `Erro ao enviar WhatsApp`

## 🎯 Boas Práticas

1. **Mensagens curtas** - Evite textos longos
2. **Emojis moderados** - Use com parcimônia
3. **Horário comercial** - Evite enviar fora do horário
4. **Teste antes** - Sempre teste em ambiente de homologação
5. **Fallback** - O serviço continua funcionando mesmo se WhatsApp falhar

## ✅ Status

- ✅ Migration criada
- ✅ Service atualizado
- ✅ Integrado no Webhook
- ✅ Notificação cliente OK
- ✅ Notificação admin OK
- ✅ Logs configurados
- ✅ Fallback para settings antigas

## 📞 Suporte

Se tiver problemas, verifique:
1. Logs do Laravel
2. Status da instância WhatsApp
3. Credenciais corretas no banco
4. Formato do número (deve incluir DDI)

