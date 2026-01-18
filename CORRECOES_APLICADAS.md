# Correções Aplicadas - Sistema Olika

## Data: 18/12/2025

### 1. ✅ Erro no laravel.log - `isExpiringSoon()` não existe

**Problema**: O método `isExpiringSoon()` era chamado como método de instância, mas só existe como scope no model Subscription.

**Arquivo corrigido**: `resources/views/master/clients/index.blade.php`

**Alteração**:
```blade
// ANTES
@if($client->subscription->isExpiringSoon())
    <p class="text-xs text-warning">{{ $client->subscription->daysUntilExpiry() }} dias</p>
@endif

// DEPOIS
@if($client->subscription->days_until_expiration !== null && $client->subscription->days_until_expiration <= 7)
    <p class="text-xs text-warning">{{ $client->subscription->days_until_expiration }} dias</p>
@endif
```

---

### 2. ✅ Pedidos Agendados com valores zerados

**Problema**: Os pedidos agendados apareciam sem valores (R$ 0,00) porque a query não carregava corretamente os relacionamentos e campos necessários.

**Arquivo corrigido**: `app/Http/Controllers/Dashboard/DashboardController.php`

**Alteração**:
```php
// ANTES
$nextScheduled = Order::whereNotNull('scheduled_delivery_at')
    ->where('scheduled_delivery_at', '>=', now())
    ->orderBy('scheduled_delivery_at')
    ->limit(8)
    ->get(['id', 'order_number', 'scheduled_delivery_at', 'status', 'customer_id']);

// DEPOIS
$nextScheduled = Order::with(['customer:id,name'])
    ->whereNotNull('scheduled_delivery_at')
    ->where('scheduled_delivery_at', '>=', now())
    ->orderBy('scheduled_delivery_at')
    ->limit(8)
    ->get();
```

**SQL adicional**: Execute `SQL_FIXES.sql` para corrigir dados já existentes:
```sql
UPDATE orders o
SET o.final_amount = COALESCE(o.final_amount, o.total_amount, 0)
WHERE o.scheduled_delivery_at IS NOT NULL 
AND (o.final_amount IS NULL OR o.final_amount = 0)
AND o.total_amount > 0;
```

---

### 3. ✅ Cards no dashboard sem funcionalidade de click

**Problema**: Os cards indicavam ter funcionalidade, mas não eram clicáveis.

**Arquivo corrigido**: `resources/views/dashboard/dashboard/index.blade.php`

**Alteração**:
- Transformados os cards `<div>` em links `<a>` 
- Adicionadas rotas com filtros específicos para cada card
- Adicionado hover effect e cursor pointer

**Rotas configuradas**:
- Receita Hoje → `/dashboard/reports`
- Pedidos Hoje → `/dashboard/orders`
- Pagos Hoje → `/dashboard/orders?status=active`
- Pendentes de Pagamento → `/dashboard/orders?status=pending`

---

### 4. ✅ WhatsApp URL e API Key devem vir do banco e ser readonly

**Problema**: Os campos URL e API Key não eram salvos no banco de dados e podiam ser editados mesmo após atribuição a um cliente.

**Arquivos modificados**:
1. `app/Models/WhatsappInstanceUrl.php` - Adicionados campos `api_key` e `description` ao `$fillable`
2. `app/Http/Controllers/Master/WhatsappInstanceUrlsController.php` - Adicionada validação e salvamento dos campos
3. `resources/views/master/whatsapp-urls/form.blade.php` - Campos tornam-se readonly após atribuição

**SQL necessário**:
```sql
ALTER TABLE whatsapp_instance_urls ADD COLUMN IF NOT EXISTS api_key VARCHAR(255) NULL AFTER url;
ALTER TABLE whatsapp_instance_urls ADD COLUMN IF NOT EXISTS description TEXT NULL AFTER api_key;
```

**Comportamento**:
- Campos URL e API Key são **editáveis** ao criar nova instância
- Campos tornam-se **readonly** (não editáveis) após atribuição a um cliente
- Visual muda para fundo acinzentado (`bg-muted`) quando readonly

---

### 5. ⚠️ Página de cadastro não existe (PENDENTE)

**Problema**: Não existe rota `/cadastrar` para cadastro público de estabelecimentos.

**Status**: PENDENTE - Requer discussão sobre:
- Se o cadastro deve ser público ou apenas via painel Master
- Qual fluxo de onboarding desejado (trial, aprovação manual, etc.)
- Se deve integrar com sistema de pagamentos desde o início

**Recomendação**: 
- Se for público: criar rota, controller e view em `routes/web.php` sem middleware de auth
- Se for apenas Master: manter cadastro apenas via `/master/clients/create`
- Considerar fluxo: Cadastro → Trial → Ativação → Assinatura

---

## Arquivos Criados

1. **SQL_FIXES.sql** - Scripts SQL para correções no banco de dados
2. **CORRECOES_APLICADAS.md** - Este arquivo de documentação

---

## Próximos Passos

1. **CRÍTICO**: Executar os scripts SQL:
   ```bash
   # No servidor de desenvolvimento/produção
   mysql -u usuario -p database_name < SQL_FIXES.sql
   ```

2. **IMPORTANTE**: Limpar cache do Laravel:
   ```bash
   php artisan cache:clear
   php artisan view:clear
   php artisan config:clear
   ```

3. **TESTAR**:
   - [ ] Acessar página de clientes no painel Master - verificar se não há mais erro `isExpiringSoon()`
   - [ ] Acessar Dashboard principal - verificar se pedidos agendados mostram valores corretos
   - [ ] Clicar nos cards do dashboard - verificar se redirecionam corretamente
   - [ ] Criar/editar instância WhatsApp - verificar se URL e API Key salvam e ficam readonly após atribuição

4. **DECIDIR**: Sobre o cadastro público de estabelecimentos (item 5)

---

## Observações Técnicas

### Memórias do sistema:
- **Sempre responder em português**
- **Nunca gerar migrations, sempre SQL direto**
- **Servidores são externos (sem SSH local)**

### Padrões mantidos:
- Eager loading otimizado (`:id,name` para reduzir carga)
- Cache de 60s no dashboard para dados estáticos
- Validações no backend (controller)
- Classes Tailwind para estilização
- Readonly via atributo HTML + classe CSS
