# Sistema de Checkout por Etapas - Integração

## 📋 Visão Geral

Este documento descreve o sistema de checkout por etapas integrado ao Laravel Olika, incluindo:
- Carrinho de compras
- Checkout em 4 etapas (Cliente → Endereço → Revisão → Pagamento)
- Sistema de cupons dinâmico
- Integração Mercado Pago e PIX
- Dashboard de gerenciamento

## 🏗️ Estrutura de Arquivos

### Models Criados/Atualizados:
- `app/Models/Address.php` - Endereços de entrega
- `app/Models/Customer.php` - Atualizado com relacionamentos
- `app/Models/Order.php` - Atualizado com novos campos
- `app/Models/OrderItem.php` - Itens do pedido
- `app/Models/Coupon.php` - Sistema de cupons
- `app/Models/Payment.php` - Registro de pagamentos

### Controllers Criados:
- `app/Http/Controllers/CartController.php` - Carrinho
- `app/Http/Controllers/CheckoutController.php` - Checkout em etapas
- `app/Http/Controllers/CouponController.php` - Cupons
- `app/Http/Controllers/PaymentController.php` - Pagamentos
- `app/Http/Controllers/WebhookController.php` - Webhooks
- `app/Http/Controllers/Dashboard/DashboardController.php` - Dashboard

### Migrations Necessárias:
1. `create_addresses_table`
2. `add_payment_fields_to_orders_table`
3. `create_payments_table`
4. `update_customers_table_with_address_relation`

## 🔄 Rotas (web.php)

As rotas principais já estão configuradas no sistema. Principais rotas:

```php
// Carrinho
POST /cart/add
POST /cart/update  
POST /cart/remove
GET  /cart

// Checkout (4 etapas)
GET  /checkout (Etapa 1: Cliente)
POST /checkout/customer
GET  /checkout/address (Etapa 2: Endereço)
POST /checkout/address
GET  /checkout/review (Etapa 3: Revisão + Cupons)
POST /checkout/apply-coupon
POST /checkout/remove-coupon
GET  /checkout/payment (Etapa 4: Pagamento)
POST /payment/pix
POST /payment/mp
GET  /checkout/success/{order}

// Webhooks
POST /payments/mercadopago/webhook

// Dashboard (subdomínio)
GET  dashboard.menuolika.com.br
GET  dashboard.menuolika.com.br/orders
GET  dashboard.menuolika.com.br/coupons
```

## 📦 Funcionalidades

### 1. Carrinho de Compras
- Adicionar produtos
- Atualizar quantidade
- Remover itens
- Cálculo de subtotal
- Sessão persistente

### 2. Checkout em Etapas

**Etapa 1: Dados do Cliente**
- Nome completo
- Telefone (usado como identificador)
- Email
- Cookie de lembrança (30 dias)

**Etapa 2: Endereço de Entrega**
- Preenchimento automático via CEP (ViaCEP)
- Validação de campos obrigatórios
- Salvamento múltiplo de endereços

**Etapa 3: Revisão + Cupons**
- Resumo do pedido
- Aplicação de cupons
- Lista de cupons disponíveis do BD
- Cálculo de desconto
- Taxa de entrega

**Etapa 4: Pagamento**
- PIX (QR Code + Copia-e-Cola)
- Mercado Pago (Checkout externo)
- Status do pagamento
- Redirecionamento para sucesso

### 3. Sistema de Cupons

**Tipos:**
- `percent` - Desconto percentual
- `fixed` - Desconto fixo em R$

**Validações:**
- Data de início/término
- Limite de usos global
- Limite de usos por cliente
- Valor mínimo do pedido
- Primeira compra apenas

**Métodos do Model:**
```php
$coupon->isValidFor($customer)  // Valida todos os critérios
$coupon->applyTo($subtotal)    // Calcula desconto
```

### 4. Dashboard

**Estatísticas:**
- Pedidos hoje
- Receita hoje
- Pedidos pendentes

**Gestão:**
- Listar todos os pedidos
- Detalhes do pedido
- Criar cupons
- Remover cupons
- Visualizar status de pagamento

## 🔧 Configuração

### 1. Banco de Dados

Execute as migrations:
```bash
php artisan migrate
```

### 2. Variáveis de Ambiente (.env)

```env
# Mercado Pago
MERCADOPAGO_ACCESS_TOKEN=seu_token
MERCADOPAGO_PUBLIC_KEY=sua_public_key

# WhatsApp (opcional)
WHATSAPP_API_URL=https://api.greenapi.com
WHATSAPP_INSTANCE_ID=instance_id
WHATSAPP_API_TOKEN=token
```

### 3. Subdomínio

Configure o DNS:
- `dashboard.menuolika.com.br` → Mesmo servidor

## 📱 Views (Blade)

Estrutura de views:
```
resources/views/
  cart/
    index.blade.php
  checkout/
    customer.blade.php
    address.blade.php
    review.blade.php
    payment.blade.php
    success.blade.php
  dashboard/
    index.blade.php
    orders.blade.php
    order_show.blade.php
    coupons.blade.php
```

## 🎨 Recursos Frontend

### Alpine.js
Uso minimalista para interatividade:
- Toggle de etapas
- Validações client-side
- Formulários dinâmicos

### ViaCEP
Preenchimento automático de endereços:
```javascript
fetch('https://viacep.com.br/ws/{cep}/json/')
```

### LocalStorage
Lembrança de dados do cliente:
```javascript
localStorage.setItem('olika_customer_name', value)
```

## 🔐 Segurança

- CSRF token em todos os formulários
- Validação server-side
- Sanitização de inputs
- Sessões seguras
- Rate limiting (opcional)

## 🧪 Testando

1. Adicione produtos ao carrinho
2. Complete as 4 etapas do checkout
3. Teste PIX e Mercado Pago
4. Verifique webhook de confirmação
5. Acesse dashboard e gerencie

## 📝 Próximos Passos

1. Criar migrations
2. Implementar Controllers
3. Criar Views (Blade)
4. Adicionar validações
5. Testar fluxo completo
6. Integrar WhatsApp
7. Configurar cron jobs (se necessário)

## 📞 Suporte

Documentação baseada no pacote fornecido. Para implementação completa, siga os arquivos de exemplo abaixo.

