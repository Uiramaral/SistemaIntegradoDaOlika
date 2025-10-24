# 🎟️ Sistema de Cupons - Olika

## 🎯 **Tipos de Cupons Implementados**

### **1. Cupons Públicos**
- ✅ **Visibilidade:** `public`
- ✅ **Uso:** Qualquer cliente pode usar
- ✅ **Exibição:** Aparecem na página de cupons
- ✅ **Validação:** Apenas verifica se está ativo e válido

### **2. Cupons Privados**
- ✅ **Visibilidade:** `private`
- ✅ **Uso:** Apenas quem possui o código pode usar
- ✅ **Exibição:** Não aparecem na página de cupons
- ✅ **Validação:** Verifica se o código existe e está válido

### **3. Cupons Direcionados**
- ✅ **Visibilidade:** `targeted`
- ✅ **Uso:** Apenas um cliente específico pode usar
- ✅ **Exibição:** Aparecem apenas para o cliente alvo
- ✅ **Validação:** Verifica se o cliente é o alvo do cupom

## 🔧 **Funcionalidades Implementadas**

### **Criação de Cupons**
```php
// Cupom Público
Coupon::create([
    'code' => 'BEMVINDO',
    'name' => 'Bem-vindo',
    'description' => 'Desconto de boas-vindas',
    'type' => 'percentage',
    'value' => 10.00,
    'visibility' => 'public',
    'is_active' => true,
]);

// Cupom Privado
Coupon::create([
    'code' => 'PRIVADO123',
    'name' => 'Cupom Secreto',
    'description' => 'Desconto especial',
    'type' => 'fixed',
    'value' => 15.00,
    'visibility' => 'private',
    'is_active' => true,
]);

// Cupom Direcionado
Coupon::create([
    'code' => 'CLIENTE123',
    'name' => 'Cupom Exclusivo',
    'description' => 'Desconto exclusivo para cliente VIP',
    'type' => 'percentage',
    'value' => 20.00,
    'visibility' => 'targeted',
    'target_customer_id' => 123,
    'is_active' => true,
]);
```

### **Validação de Cupons**
```php
// Verificar se cupom pode ser usado por um cliente
$coupon = Coupon::where('code', 'BEMVINDO')->first();
$canUse = $coupon->canBeUsedBy($customerId);

// Verificar se cupom é válido
$isValid = $coupon->isValid($customerId);
```

### **Scopes Disponíveis**
```php
// Cupons públicos
$publicCoupons = Coupon::public()->active()->get();

// Cupons privados
$privateCoupons = Coupon::private()->active()->get();

// Cupons direcionados
$targetedCoupons = Coupon::targeted()->active()->get();

// Cupons visíveis para um cliente
$visibleCoupons = Coupon::visibleFor($customerId)->active()->get();
```

## 📊 **Estrutura do Banco**

### **Campos Adicionados à Tabela `coupons`**
```sql
- visibility (enum) - 'public', 'private', 'targeted'
- target_customer_id (foreign key) - Cliente alvo (cupons direcionados)
- private_description (text) - Descrição para cupons privados
```

### **Relacionamentos**
```php
// Cupom direcionado para cliente específico
$coupon->targetCustomer() // BelongsTo Customer

// Cliente com cupons direcionados
$customer->targetedCoupons() // HasMany Coupon
```

## 🚀 **APIs Disponíveis**

### **Validar Cupom**
```bash
POST /api/coupons/validate
{
    "code": "BEMVINDO",
    "customer_id": 123
}
```

### **Listar Cupons Visíveis**
```bash
GET /api/coupons?customer_id=123
```

### **Criar Cupom**
```bash
POST /api/coupons
{
    "code": "NOVO123",
    "name": "Novo Cupom",
    "description": "Desconto especial",
    "type": "percentage",
    "value": 15.00,
    "visibility": "public",
    "minimum_amount": 50.00,
    "usage_limit": 100,
    "usage_limit_per_customer": 1,
    "starts_at": "2024-01-01",
    "expires_at": "2024-12-31",
    "is_active": true
}
```

### **Atualizar Cupom**
```bash
PUT /api/coupons/{id}
{
    "name": "Cupom Atualizado",
    "description": "Nova descrição",
    "is_active": false
}
```

### **Deletar Cupom**
```bash
DELETE /api/coupons/{id}
```

### **Estatísticas**
```bash
GET /api/coupons/stats
```

### **Lista Admin**
```bash
GET /api/coupons/admin?visibility=public&is_active=true&search=termo
```

## 🎨 **Views Implementadas**

### **Página de Cupons Públicos**
- ✅ `/coupons` - Lista cupons públicos
- ✅ Cards com informações do cupom
- ✅ Botão para usar cupom
- ✅ Instruções de uso

### **Funcionalidades da View**
- ✅ Seleção de cupom
- ✅ Salvamento no localStorage
- ✅ Redirecionamento para cardápio
- ✅ Notificações de sucesso

## 🔄 **Fluxo de Funcionamento**

### **1. Cupons Públicos**
1. Cliente acessa `/coupons`
2. Vê lista de cupons disponíveis
3. Clica em "Usar Cupom"
4. Cupom é salvo no localStorage
5. Cliente vai para o cardápio
6. No checkout, cupom é aplicado automaticamente

### **2. Cupons Privados**
1. Cliente recebe código por WhatsApp/email
2. Digita código no checkout
3. Sistema valida se cupom existe e está ativo
4. Se válido, desconto é aplicado

### **3. Cupons Direcionados**
1. Admin cria cupom para cliente específico
2. Cliente acessa `/coupons` (só vê seus cupons)
3. Sistema valida se cliente é o alvo
4. Se válido, desconto é aplicado

## 🎯 **Exemplos de Uso**

### **Cupom de Boas-vindas (Público)**
```php
Coupon::create([
    'code' => 'BEMVINDO',
    'name' => 'Bem-vindo',
    'description' => '10% de desconto para novos clientes',
    'type' => 'percentage',
    'value' => 10.00,
    'minimum_amount' => 50.00,
    'visibility' => 'public',
    'usage_limit' => 1000,
    'usage_limit_per_customer' => 1,
    'is_active' => true,
]);
```

### **Cupom de Indicação (Privado)**
```php
Coupon::create([
    'code' => 'INDICACAO123',
    'name' => 'Cupom de Indicação',
    'description' => 'R$ 15 de desconto por indicação',
    'type' => 'fixed',
    'value' => 15.00,
    'minimum_amount' => 80.00,
    'visibility' => 'private',
    'private_description' => 'Cupom enviado por indicação',
    'usage_limit' => 100,
    'is_active' => true,
]);
```

### **Cupom VIP (Direcionado)**
```php
Coupon::create([
    'code' => 'VIP123',
    'name' => 'Cupom VIP',
    'description' => '20% de desconto exclusivo',
    'type' => 'percentage',
    'value' => 20.00,
    'minimum_amount' => 100.00,
    'visibility' => 'targeted',
    'target_customer_id' => 123,
    'usage_limit' => 1,
    'is_active' => true,
]);
```

## 🔒 **Validações de Segurança**

### **Cupons Públicos**
- ✅ Verifica se está ativo
- ✅ Verifica se não expirou
- ✅ Verifica limite de uso geral
- ✅ Verifica limite por cliente

### **Cupons Privados**
- ✅ Verifica se código existe
- ✅ Verifica se está ativo
- ✅ Verifica se não expirou
- ✅ Verifica limites de uso

### **Cupons Direcionados**
- ✅ Verifica se cliente é o alvo
- ✅ Verifica se está ativo
- ✅ Verifica se não expirou
- ✅ Verifica limites de uso

## 📈 **Relatórios e Estatísticas**

### **Estatísticas Disponíveis**
- ✅ Total de cupons
- ✅ Cupons ativos
- ✅ Cupons por tipo (público/privado/direcionado)
- ✅ Cupons expirados
- ✅ Cupons utilizados

### **Filtros Admin**
- ✅ Por visibilidade
- ✅ Por status (ativo/inativo)
- ✅ Por busca (código/nome/descrição)
- ✅ Paginação

## 🚀 **Próximos Passos**

### **1. Executar Migration**
```bash
# Usar o script existente
https://seudominio.com/run_migrations.php
```

### **2. Testar Sistema**
1. Acesse `/coupons` para ver cupons públicos
2. Teste validação via API
3. Crie cupons de diferentes tipos
4. Teste fluxo completo

### **3. Integrar com Checkout**
1. Adicionar campo de cupom no checkout
2. Validar cupom antes de aplicar
3. Calcular desconto automaticamente
4. Salvar cupom usado no pedido

**O sistema de cupons está completo com os três tipos implementados! 🎟️✨**
