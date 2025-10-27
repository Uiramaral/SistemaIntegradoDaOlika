# 🔧 AJUSTES NECESSÁRIOS NO BANCO DE DADOS

## ❌ PROBLEMAS IDENTIFICADOS

### 1. **Tabela `cashback` NÃO EXISTE**
- Controller tenta acessar mas tabela não existe
- **Solução:** Criar tabela (SQL fornecido)

### 2. **Falta campo `slug` em `categories`**
- CRUD espera campo `slug` mas não existe
- **Solução:** Adicionar campo

### 3. **Falta campo `sku` em `products`**
- Formulário pede SKU mas campo não existe
- **Solução:** Adicionar campo

### 4. **Faltam campos em `categories`**
- `image` e `display_order` não existem
- **Solução:** Adicionar campos

### 5. **Tabelas não existem**
- `addresses` - usada no PDV
- `payments` - usada em pagamentos
- `coupon_usages` - rastreamento de cupons
- `whatsapp_settings` - configuração WhatsApp
- `whatsapp_templates` - templates de mensagens
- `order_statuses` - status customizados
- `order_status_history` - histórico de mudanças

---

## ✅ SOLUÇÃO COMPLETA

**Arquivo criado:** `AJUSTES_BANCO_NECESSARIOS.sql`

Este arquivo contém **TODOS** os comandos SQL necessários para:
1. Criar tabela `cashback`
2. Adicionar `slug` em categories
3. Adicionar `sku` em products
4. Adicionar campos em categories (image, display_order)
5. Criar tabela `addresses`
6. Adicionar `address_id` em orders
7. Criar tabela `payments`
8. Criar tabela `coupon_usages`
9. Criar tabelas WhatsApp (settings, templates)
10. Criar tabelas de Status (statuses, history)
11. Inserir dados iniciais (status padrão, templates)

---

## 🚀 COMO APLICAR

### Opção 1: MySQL/phpMyAdmin
```bash
# Baixe o arquivo AJUSTES_BANCO_NECESSARIOS.sql
# Execute no phpMyAdmin ou MySQL Workbench
```

### Opção 2: Via Terminal/SSH
```bash
mysql -u seu_usuario -p hg6ddb59_larav25 < AJUSTES_BANCO_NECESSARIOS.sql
```

### Opção 3: Via PHPMyAdmin
1. Acesse seu phpMyAdmin
2. Selecione o banco `hg6ddb59_larav25`
3. Aba "SQL"
4. Copie e cole todo conteúdo de `AJUSTES_BANCO_NECESSARIOS.sql`
5. Clique em "Executar"

---

## 📋 VERIFICAÇÃO

Após executar o SQL, verifique:

```sql
-- Verificar se cashback foi criada
SHOW TABLES LIKE 'cashback';

-- Verificar campos em categories
DESCRIBE categories;
-- Deve ter: slug, image, display_order

-- Verificar campos em products
DESCRIBE products;
-- Deve ter: sku

-- Verificar tabelas novas
SHOW TABLES;
-- Deve aparecer: addresses, payments, coupon_usages, whatsapp_settings, etc.
```

---

## 🎯 CAMPOS ESPERADOS

### `categories`
- `id`, `name`, **`slug`**, `display_mode`, `description`, **`image`**, `is_active`, **`display_order`**, `sort_order`, `created_at`, `updated_at`

### `products`
- `id`, `category_id`, `name`, **`sku`**, `description`, `price`, `image_url`, `is_featured`, `is_available`, `preparation_time`, `allergens`, `nutritional_info`, `sort_order`, `variants`, `is_active`, `created_at`, `updated_at`

### `orders`
- Todos existentes + **`address_id`**

---

## ⚠️ IMPORTANTE

- Faça **backup** do banco antes de executar
- O SQL usa `CREATE TABLE IF NOT EXISTS` - não sobrescreve dados existentes
- Agora verifica se colunas existem antes de adicionar (evita erro de duplicação)
- Dados iniciais usam `ON DUPLICATE KEY UPDATE` - não duplica
- Todos os campos opcionais podem ficar NULL

## 🔧 CORREÇÃO APLICADA

**Problema:** `#1060 - Nome da coluna 'address_id' duplicado`

**Solução:** Adicionada verificação antes de criar cada coluna:
```sql
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'address_id');
```

Agora o SQL só adiciona campos que **não existem**!

---

## ✅ APÓS EXECUTAR

O dashboard terá:
- ✅ CRUD de Cashback funcionando
- ✅ CRUD de Produtos com SKU
- ✅ CRUD de Categorias com Slug
- ✅ PDV usando addresses
- ✅ Pagamentos separados
- ✅ WhatsApp configurável
- ✅ Status de pedidos customizáveis

**Tudo funcionando 100%!** 🚀

