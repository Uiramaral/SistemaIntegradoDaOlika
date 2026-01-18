# 🔧 Correção: Coluna `updated_at` na tabela `api_tokens`

## ❌ Problema

Ao tentar criar um cliente via rota de teste, ocorreu o erro:

```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'updated_at' in 'field list'
```

A tabela `api_tokens` foi criada **sem** a coluna `updated_at`, mas o Laravel Eloquent tenta inserir essa coluna porque os modelos usam timestamps por padrão.

---

## ✅ Solução

Execute o SQL para adicionar a coluna `updated_at`:

**Arquivo:** `database/sql/add_updated_at_to_api_tokens.sql`

```sql
ALTER TABLE `api_tokens` 
ADD COLUMN `updated_at` TIMESTAMP NULL DEFAULT NULL 
ON UPDATE CURRENT_TIMESTAMP 
AFTER `created_at`;
```

---

## 🚀 Como Aplicar

### Opção 1: Via MySQL direto

```bash
mysql -u seu_usuario -p nome_do_banco < database/sql/add_updated_at_to_api_tokens.sql
```

### Opção 2: Via cliente MySQL

```sql
SOURCE database/sql/add_updated_at_to_api_tokens.sql;
```

### Opção 3: Copiar e colar no cliente MySQL

Abra o arquivo `database/sql/add_updated_at_to_api_tokens.sql` e execute no seu cliente MySQL (phpMyAdmin, MySQL Workbench, etc).

---

## 📋 Estrutura Esperada

Após a correção, a tabela `api_tokens` deve ter:

- ✅ `id` (BIGINT UNSIGNED)
- ✅ `client_id` (BIGINT UNSIGNED)
- ✅ `token` (VARCHAR(80))
- ✅ `expires_at` (TIMESTAMP NULL)
- ✅ `created_at` (TIMESTAMP)
- ✅ **`updated_at` (TIMESTAMP)** ← **NOVA COLUNA**

---

## ✅ Verificação

Após executar o SQL, verifique:

```sql
DESCRIBE api_tokens;
```

Você deve ver a coluna `updated_at` listada.

---

## 🧪 Teste

Após aplicar a correção, teste novamente:

```bash
curl https://devpedido.menuolika.com.br/api/test/generate-client
```

Agora deve funcionar corretamente! ✅

