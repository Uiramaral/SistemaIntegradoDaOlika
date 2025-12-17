# Correções Aplicadas nos SQLs

## Problemas Identificados

Após análise do dump do banco de dados (`hg6ddb59_larav25 (11).sql`), foram identificados os seguintes problemas nos SQLs gerados:

1. **Payment Status**: O banco usa apenas `'paid'` no ENUM, não `'approved'`
2. **Sintaxe MySQL**: Uso correto de `INTERVAL` e `DATE_SUB`
3. **Charset/Collation**: Usar `utf8mb4_unicode_ci` conforme padrão do banco
4. **Índices**: Verificar existência antes de criar para evitar erros
5. **Foreign Keys**: Verificar existência antes de criar

## Correções Aplicadas

### 1. `add_product_sales_tracking.sql`

**Alterações:**
- Removido `'approved'` das verificações de `payment_status` (usar apenas `'paid'`)
- Adicionada verificação de existência de índices antes de criar
- Corrigida sintaxe para compatibilidade com MySQL 8.0
- Removida função `get_product_sales_count` (não necessária, a view é suficiente)
- View usa apenas `payment_status = 'paid'`

**Estrutura corrigida:**
```sql
WHERE o.payment_status = 'paid'
  AND o.created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)
```

### 2. `create_customer_tags_system.sql`

**Alterações:**
- Adicionada verificação de existência de foreign keys antes de criar
- Usado `INSERT IGNORE` em vez de `ON DUPLICATE KEY UPDATE` para tags padrão
- Corrigido charset para `utf8mb4_unicode_ci`
- Adicionada verificação de existência de constraints

### 3. Código PHP (`PDVController.php`)

**Alterações:**
- Corrigido para usar apenas `'paid'` em vez de `['approved', 'paid']`
- Mantida compatibilidade com estrutura do banco

## Observações Importantes

⚠️ **Nota sobre `payment_status`**: 
- O banco de dados usa ENUM: `'pending','paid','failed','refunded'`
- Não existe `'approved'` no banco atual
- O código PHP ainda referencia `'approved'` em alguns lugares, mas isso pode ser legado
- Para os SQLs de rastreamento de vendas, usar apenas `'paid'` está correto

## Como Aplicar os SQLs

1. Execute primeiro `add_product_sales_tracking.sql`
2. Execute depois `create_customer_tags_system.sql`
3. Os SQLs verificam automaticamente se índices/constraints já existem antes de criar

## Estrutura do Banco Identificada

- **orders.payment_status**: ENUM('pending','paid','failed','refunded')
- **order_items**: Tem `product_id`, `quantity`, `order_id`
- **products**: Tem `is_active` (não `active`)
- **customers**: Estrutura completa com `client_id` para multi-instância
- **Charset padrão**: `utf8mb4` com collation `utf8mb4_unicode_ci`

