# ✅ Solução Completa - Erro 403 Corrigido

## 🎯 Problema Resolvido

O erro **403 Forbidden** ao carregar cliente foi corrigido. Agora o sistema usa autenticação via **tabela `api_tokens`** em vez de validar contra `env('API_SECRET')`.

---

## 📋 O que foi Implementado

### 1. ✅ Middleware `ApiTokenMiddleware`

**Arquivo:** `app/Http/Middleware/ApiTokenMiddleware.php`

- Valida token do header `X-API-Token`
- Busca token na tabela `api_tokens`
- Verifica se token não expirou
- Verifica se cliente está ativo
- Adiciona `authenticated_client_id` ao request

### 2. ✅ Registrado no Kernel

**Arquivo:** `app/Http/Kernel.php`

```php
'api.token' => \App\Http\Middleware\ApiTokenMiddleware::class,
```

### 3. ✅ Rotas Protegidas

**Arquivo:** `routes/web.php`

```php
Route::middleware('api.token')->group(function () {
    Route::get('/api/client/{id}', [...]);
    Route::get('/api/client/{id}/plan', [...]);
});
```

### 4. ✅ Controller Atualizado

**Arquivo:** `app/Http/Controllers/Api/ClientController.php`

- Removida validação contra `env('API_SECRET')`
- Usa `authenticated_client_id` do middleware
- Valida se token pertence ao cliente solicitado

---

## 🔑 Como Configurar

### 1. Obter Token do Banco

```sql
SELECT token FROM api_tokens WHERE client_id = 1 ORDER BY created_at DESC LIMIT 1;
```

### 2. Configurar no Railway

```bash
CLIENT_ID=1
API_TOKEN=<token_obtido_do_banco>
LARAVEL_API_URL=https://devpedido.menuolika.com.br
```

### 3. Se não houver Token

O token é gerado automaticamente quando você cria um cliente. Ou você pode criar manualmente:

```sql
INSERT INTO api_tokens (client_id, token, created_at)
VALUES (1, UUID(), NOW());
```

**OU** usar o método do Model Client:

```php
$client = Client::find(1);
$token = $client->regenerateApiToken();
```

---

## 🧪 Teste

```bash
curl -H "X-API-Token: <SEU_TOKEN>" \
     https://devpedido.menuolika.com.br/api/client/1
```

**Resposta esperada:**

```json
{
  "id": 1,
  "name": "Olika Cozinha Artesanal",
  "slug": "olika",
  "plan": "ia",
  "instance_url": "https://pedido.menuonline.com.br",
  "whatsapp_phone": "5571987019420",
  "active": true,
  "has_ia": true
}
```

---

## ✅ Arquivos Modificados

1. ✅ `app/Http/Middleware/ApiTokenMiddleware.php` - **NOVO**
2. ✅ `app/Http/Kernel.php` - Registrado middleware
3. ✅ `routes/web.php` - Rotas protegidas
4. ✅ `app/Http/Controllers/Api/ClientController.php` - Atualizado

---

## 🚀 Próximos Passos

1. ✅ Execute SQL para garantir token existe
2. ✅ Configure `API_TOKEN` no Railway
3. ✅ Reinicie serviço Node.js
4. ✅ Verifique logs

**Sistema pronto! 🎉**

