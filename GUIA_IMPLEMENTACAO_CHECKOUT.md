# 📦 Guia de Implementação - Checkout por Etapas

## ✅ Arquivos Já Criados

### Models
- ✅ `app/Models/Address.php` - Endereços de entrega
- ✅ `app/Models/Payment.php` - Registro de pagamentos
- ✅ `app/Models/Customer.php` - Atualizado com relacionamento addresses()
- ✅ `app/Models/Order.php` - Atualizado com relacionamentos address() e payment()

### Migrations
- ✅ `2024_01_01_000016_create_addresses_table.php`
- ✅ `2024_01_01_000017_create_payments_table.php`
- ✅ `2024_01_01_000018_add_address_id_to_orders_table.php`

### Documentação
- ✅ `CHECKOUT_ETAPAS_README.md` - Visão geral do sistema
- ✅ `GUIA_IMPLEMENTACAO_CHECKOUT.md` - Este arquivo

## 📋 Arquivos que Precisam ser Criados

### 1. Controllers (Prioridade ALTA)

Crie os seguintes controllers em `app/Http/Controllers/`:

#### CheckoutController (O MAIS IMPORTANTE)
```bash
app/Http/Controllers/CheckoutController.php
```

Este controller deve conter:
- `stepCustomer()` - Etapa 1
- `storeCustomer()` - Salvar dados do cliente
- `stepAddress()` - Etapa 2
- `storeAddress()` - Salvar endereço
- `stepReview()` - Etapa 3 (resumo + cupons)
- `stepPayment()` - Etapa 4 (pagamento)
- `success()` - Página de sucesso

#### CouponController
```bash
app/Http/Controllers/CouponController.php
```

Métodos:
- `apply()` - Aplicar cupom no checkout
- `remove()` - Remover cupom

#### PaymentController
```bash
app/Http/Controllers/PaymentController.php
```

Métodos:
- `createPix()` - Criar pagamento PIX
- `createMpPreference()` - Criar preferência Mercado Pago

### 2. Views (Blade)

Crie as seguintes views:

```
resources/views/checkout/
  ├── customer.blade.php     (Etapa 1: Dados do Cliente)
  ├── address.blade.php      (Etapa 2: Endereço)
  ├── review.blade.php       (Etapa 3: Revisão + Cupons)
  ├── payment.blade.php      (Etapa 4: Pagamento)
  └── success.blade.php      (Sucesso do pedido)
```

### 3. Atualizar Rotas

Edite `routes/web.php` e adicione as novas rotas conforme o pacote fornecido.

## 🚀 Próximos Passos

### Passo 1: Rodar Migrations

```bash
php artisan migrate
```

### Passo 2: Implementar Controllers

Use o código fornecido no "pacote base" como referência para implementar:
1. CheckoutController (basear-se no código fornecido)
2. CouponController (aplicar/remover cupons)
3. PaymentController (PIX e Mercado Pago)

### Passo 3: Criar Views

Para cada etapa do checkout, crie uma view Blade que:
1. Exibe formulário apropriado
2. Usa Alpine.js para interatividade
3. Implementa validação client-side
4. Integra ViaCEP para CEP
5. Salva dados em localStorage

### Passo 4: Integrar Cupons

A view `checkout/review.blade.php` deve:
- Mostrar cupons disponíveis do banco
- Permitir aplicar cupom por código
- Calcular desconto
- Mostrar total final

### Passo 5: Integrar Pagamentos

A view `checkout/payment.blade.php` deve:
- Mostrar opções PIX e Mercado Pago
- Gerar QR Code PIX
- Redirecionar para Mercado Pago
- Mostrar status do pagamento

### Passo 6: Webhook

Implementar `WebhookController::mercadoPago()` para:
- Receber notificações do Mercado Pago
- Atualizar status do pedido
- Enviar notificação WhatsApp

### Passo 7: Dashboard

Criar views do dashboard em `resources/views/dashboard/`:
- `index.blade.php` - Estatísticas
- `orders.blade.php` - Lista de pedidos
- `order_show.blade.php` - Detalhes do pedido
- `coupons.blade.php` - Gerenciar cupons

## 📝 Código de Referência

Todo o código necessário está no "pacote base" fornecido. Use-o como referência para implementar:

1. Controllers com lógica de checkout
2. Models com validações de cupons
3. Views com formulários e interatividade
4. Integração ViaCEP para CEP
5. Integração Mercado Pago
6. Webhook para confirmação de pagamento

## ⚠️ Importante

- As rotas estão parcialmente configuradas no `web.php`
- Os models estão criados e configurados
- As migrations estão prontas
- Falta implementar Controllers e Views

## 🔧 Configuração de Ambiente

Adicione ao seu `.env`:

```env
# Mercado Pago
MERCADOPAGO_ACCESS_TOKEN=seu_token
MERCADOPAGO_PUBLIC_KEY=sua_public_key

# WhatsApp (opcional)
WHATSAPP_API_URL=https://api.greenapi.com
WHATSAPP_INSTANCE_ID=instance_id
WHATSAPP_API_TOKEN=token
```

## 📞 Próxima Ação

Execute as migrations:

```bash
php artisan migrate
```

Depois implemente os Controllers e Views conforme o código de referência fornecido.

