# 🔧 Correção do Erro 403 - Autenticação via Token

## ⚠️ Problema Identificado

O Node.js está recebendo erro **403 (Forbidden)** ao tentar carregar o cliente:

```
❌ Erro ao carregar cliente: Request failed with status code 403
```

## 🔍 Causa Raiz

O `ClientController` estava validando o token contra `env('API_SECRET')`, mas deveria validar contra a **tabela `api_tokens`** do banco de dados.

## ✅ Solução Implementada

### 1. Criado Middleware `ApiTokenMiddleware`

**Arquivo:** `app/Http/Middleware/ApiTokenMiddleware.php`

Este middleware:
- ✅ Valida o token do header `X-API-Token` contra a tabela `api_tokens`
- ✅ Verifica se o token não expirou
- ✅ Verifica se o cliente está ativo
- ✅ Adiciona `authenticated_client_id` ao request
- ✅ Disponibiliza o cliente autenticado no container Laravel

### 2. Registrado Middleware no Kernel

**Arquivo:** `app/Http/Kernel.php`

```php
'api.token' => \App\Http\Middleware\ApiTokenMiddleware::class,
```

### 3. Aplicado Middleware nas Rotas

**Arquivo:** `routes/web.php`

```php
Route::middleware('api.token')->group(function () {
    Route::get('/api/client/{id}', [\App\Http\Controllers\Api\ClientController::class, 'show'])
        ->name('api.client.show');

    Route::get('/api/client/{id}/plan', [\App\Http\Controllers\Api\ClientController::class, 'getPlan'])
        ->name('api.client.plan');
});
```

### 4. Atualizado ClientController

O controller agora:
- ✅ Usa o `authenticated_client_id` do middleware
- ✅ Verifica se o token pertence ao cliente solicitado
- ✅ Não valida mais contra `env('API_SECRET')`

## 🔑 Como Obter o Token

### 1. Verificar Token no Banco de Dados

```sql
SELECT * FROM api_tokens WHERE client_id = 1;
```

### 2. Copiar o Token para Railway

No Railway, configure:

```bash
CLIENT_ID=1
API_TOKEN=<token_da_tabela_api_tokens>
LARAVEL_API_URL=https://devpedido.menuolika.com.br
```

### 3. Se o Token não Existir

Se não houver token na tabela, ele será gerado automaticamente quando você criar um novo cliente via Laravel (devido ao `booted()` no Model Client).

Ou você pode gerar manualmente:

```sql
INSERT INTO api_tokens (client_id, token, created_at)
VALUES (1, UUID(), NOW());
```

## 🧪 Teste Manual

Teste a rota manualmente:

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

## ✅ Checklist Final

- [x] Middleware `ApiTokenMiddleware` criado
- [x] Middleware registrado no Kernel
- [x] Rotas protegidas com middleware
- [x] `ClientController` atualizado para usar autenticação do middleware
- [ ] Token obtido da tabela `api_tokens`
- [ ] Token configurado no Railway como `API_TOKEN`
- [ ] Teste manual realizado com sucesso

## 🚀 Próximos Passos

1. Execute o SQL para garantir que há um token na tabela
2. Configure `API_TOKEN` no Railway
3. Reinicie o serviço Node.js no Railway
4. Verifique os logs para confirmar que o cliente foi carregado

---

**Sistema pronto para autenticação via token da tabela `api_tokens`! 🎉**

