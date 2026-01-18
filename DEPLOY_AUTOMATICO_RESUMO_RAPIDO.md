# ⚡ Deploy Automatizado - Resumo Rápido

## 🚀 Setup Inicial (5 minutos)

### 1. Banco de Dados
```sql
-- Executar no MySQL
SOURCE database/sql/add_deploy_fields_to_clients.sql;
SOURCE database/sql/create_deployment_logs_table.sql;
```

### 2. GitHub Secrets
**Settings → Secrets → Actions:**
- `RAILWAY_TOKEN` = Token do Railway (Settings → Integrations → Generate Token)
- `LARAVEL_WEBHOOK_URL` = `https://seu-dominio.com/api/github/webhook`

### 3. Laravel .env
```env
GITHUB_TOKEN=ghp_xxxxxxxxxxxxxxxxxxxxxxxx
GITHUB_REPO=seu-usuario/seu-repositorio
APP_DOMAIN=menuolika.com.br  # Opcional
```

## 📝 Como Usar

### Opção 1: Via Comando Artisan
```bash
php artisan olika:deploy 5
```

### Opção 2: Via API (Laravel)
```bash
POST /api/deploy-client
Content-Type: application/json
Authorization: Bearer {token}

{
    "client_id": 5
}
```

### Opção 3: Direto no Código
```php
use Illuminate\Support\Facades\Http;

Http::post(route('api.deploy.client'), [
    'client_id' => $client->id
]);
```

## ✅ Verificar Status

```sql
SELECT id, name, deploy_status, instance_url 
FROM clients 
WHERE id = 5;
```

## 🔍 Arquivos Criados

1. ✅ `database/sql/add_deploy_fields_to_clients.sql`
2. ✅ `database/sql/create_deployment_logs_table.sql`
3. ✅ `.github/workflows/deploy-client.yml`
4. ✅ `app/Http/Controllers/DeployClientController.php`
5. ✅ `app/Console/Commands/DeployClientCommand.php`
6. ✅ Rotas adicionadas em `routes/web.php`
7. ✅ Model `Client` atualizado com `deploy_status`

## 🎯 Fluxo

1. Cliente criado com `plan = 'ia'`
2. Disparar deploy (comando ou API)
3. GitHub Actions executa
4. Railway faz deploy
5. GitHub envia callback para Laravel
6. Laravel atualiza status e URL

**Pronto! ✅**


