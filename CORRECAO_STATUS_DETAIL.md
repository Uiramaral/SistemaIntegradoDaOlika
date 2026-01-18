# 🔧 Correção: Coluna `status_detail` não encontrada na tabela `payments`

## ❌ Erro Identificado

```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'status_detail' in 'field list'
SQL: select `id`, `order_id`, `status`, `status_detail` from `payments` where `payments`.`order_id` in (112, 113, 114, 115)
```

**Localização:** `app/Http/Controllers/Dashboard/OrdersController.php` linha 29

---

## 🔍 Análise

A tabela `payments` **não possui** a coluna `status_detail`. 

**Estrutura real da tabela `payments`:**
- `id`
- `order_id`
- `provider`
- `provider_id`
- `status`
- `payload` (JSON)
- `pix_qr_base64`
- `pix_copia_cola`
- `timestamps`

---

## ✅ Correção Aplicada

**Arquivo:** `app/Http/Controllers/Dashboard/OrdersController.php`

**Antes:**
```php
'payment:id,order_id,status,status_detail'
```

**Depois:**
```php
'payment:id,order_id,status,provider,provider_id'
```

---

## 📝 Nota Importante

Os outros usos de `status_detail` no código estão **corretos**:
- Linha 213: `$mpInfo['status_detail']` - Vem da API do Mercado Pago
- Linha 449: `$mpInfo['status_detail']` - Vem da API do Mercado Pago
- Linha 1934: `$data['status_detail']` - Vem de dados externos

Esses não precisam ser alterados, pois não estão tentando buscar da tabela `payments`.

---

## ✅ Status

**Correção:** ✅ Aplicada  
**Teste:** ⚠️ Pendente (após deploy)

---

**Última atualização:** 2025-01-27












