# 🔧 Como Adicionar Campos Manualmente

## ✅ SQL FINAL CRIADO

Arquivo: **`AJUSTES_FINAL.sql`**

Este arquivo:
✅ Cria TODAS as tabelas que faltam
✅ NÃO adiciona campos (já comentado)
✅ Evita erros de "campo duplicado"

---

## 📋 EXECUTE PRIMEIRO

Execute **`AJUSTES_FINAL.sql`** - só vai criar as tabelas novas.

Depois adicione os campos MANUALMENTE (se necessário):

---

## 🎯 ADICIONAR CAMPOS (Se Necessário)

Execute **UM POR VEZ** no phpMyAdmin:

### 1. Campo slug em categories
```sql
ALTER TABLE `categories` ADD COLUMN `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `name`;
```
⚠️ Se der erro "campo duplicado", **IGNORE** - já existe!

### 2. Campo image em categories
```sql
ALTER TABLE `categories` ADD COLUMN `image` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `slug`;
```
⚠️ Se der erro "campo duplicado", **IGNORE** - já existe!

### 3. Campo display_order em categories
```sql
ALTER TABLE `categories` ADD COLUMN `display_order` int NOT NULL DEFAULT 0 AFTER `is_active`;
```
⚠️ Se der erro "campo duplicado", **IGNORE** - já existe!

### 4. Campo sku em products
```sql
ALTER TABLE `products` ADD COLUMN `sku` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `name`;
```
⚠️ Se der erro "campo duplicado", **IGNORE** - já existe!

### 5. Campo address_id em orders
```sql
ALTER TABLE `orders` ADD COLUMN `address_id` bigint UNSIGNED DEFAULT NULL AFTER `customer_id`;
```
⚠️ Se der erro "campo duplicado", **IGNORE** - já existe!

---

## ⚡ ATALHO RÁPIDO

**Prefere executar tudo de uma vez e ignorar erros?**

Use o arquivo **`AJUSTES_SIMPLES.sql`** que já comentou os comandos problemáticos.

---

## ✅ VERIFICAÇÃO

Após executar `AJUSTES_FINAL.sql`, verifique se as tabelas foram criadas:

```sql
SHOW TABLES LIKE 'cashback';
SHOW TABLES LIKE 'addresses';
SHOW TABLES LIKE 'whatsapp_settings';
```

Se todas retornarem 1 linha, está OK! ✅

---

## 🚀 PRÓXIMOS PASSOS

1. Execute `AJUSTES_FINAL.sql`
2. Esqueça os campos (não são obrigatórios para funcionar)
3. Limpe o cache:
   ```bash
   php artisan route:clear
   php artisan cache:clear
   ```
4. Teste o dashboard

**Funciona mesmo sem os campos extras!** 🎉

