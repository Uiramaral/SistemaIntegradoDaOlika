# 🚀 Deploy Automatizado Multi-Cliente - Guia Completo

## 📋 Visão Geral

Sistema de deploy automatizado que permite criar e fazer deploy de instâncias de clientes (plano IA) no Railway, utilizando Laravel + GitHub Actions, **sem depender da API GraphQL do Railway**.

## 🏗️ Arquitetura

```
┌─────────────┐         ┌──────────────┐         ┌─────────────┐
│   Laravel   │────────▶│ GitHub Actions│────────▶│   Railway   │
│  (Painel)   │         │   (CI/CD)    │         │  (Hospeda)  │
└─────────────┘         └──────────────┘         └─────────────┘
      │                         │                         │
      │                         │                         │
      └─────────────────────────┼─────────────────────────┘
                                │
                                ▼
                         ┌─────────────┐
                         │   MySQL     │
                         │ (Registros) │
                         └─────────────┘
```

## 📦 Componentes Implementados

### 1. Banco de Dados

#### ✅ Arquivos SQL Criados:

- **`database/sql/add_deploy_fields_to_clients.sql`**
  - Adiciona campo `deploy_status` na tabela `clients`
  - Adiciona campo `instance_url` (se não existir)

- **`database/sql/create_deployment_logs_table.sql`**
  - Cria tabela `deployment_logs` para armazenar histórico de deploys

#### 📝 Executar os SQLs:

```sql
-- 1. Adicionar campos na tabela clients
SOURCE database/sql/add_deploy_fields_to_clients.sql;

-- 2. Criar tabela de logs
SOURCE database/sql/create_deployment_logs_table.sql;
```

### 2. GitHub Actions Workflow

#### ✅ Arquivo Criado:

- **`.github/workflows/deploy-client.yml`**

**Funcionalidades:**
- ✅ Recebe inputs via `workflow_dispatch` (client_id, client_name, client_slug)
- ✅ Faz checkout do repositório
- ✅ Configura Node.js (versão 20)
- ✅ Instala dependências
- ✅ Faz deploy no Railway usando `railwayapp/action@v2`
- ✅ Envia callback para Laravel após deploy

### 3. Laravel - Controller e Rotas

#### ✅ Arquivos Criados:

- **`app/Http/Controllers/DeployClientController.php`**
  - Método `deploy()`: Dispara workflow no GitHub Actions
  - Método `webhook()`: Recebe callback do GitHub após deploy

#### ✅ Rotas Adicionadas em `routes/web.php`:

```php
// Deploy via GitHub Actions (autenticado)
Route::post('/api/deploy-client', [DeployClientController::class, 'deploy']);

// Webhook GitHub → Laravel (público, mas pode ser protegido)
Route::post('/api/github/webhook', [DeployClientController::class, 'webhook']);
```

### 4. Comando Artisan

#### ✅ Arquivo Criado:

- **`app/Console/Commands/DeployClientCommand.php`**

**Uso:**
```bash
php artisan olika:deploy {client_id}
```

**Exemplo:**
```bash
php artisan olika:deploy 5
```

### 5. Model Client

#### ✅ Atualizado:

- Adicionado `deploy_status` ao `$fillable`

## ⚙️ Configuração

### 1. Railway

1. **Criar projeto modelo** no Railway (ex: `olika-template`)
2. **Gerar Project Token**:
   - Settings → Integrations → Generate Token
   - Copiar o token (ex: `railway_production_xxxxx`)

### 2. GitHub

#### Secrets Necessários:

Vá em **Settings → Secrets → Actions** e adicione:

| Nome | Valor | Descrição |
|------|-------|-----------|
| `RAILWAY_TOKEN` | Token do Railway | Para deploy no Railway |
| `LARAVEL_WEBHOOK_URL` | `https://seu-dominio.com/api/github/webhook` | Callback após deploy |

### 3. Laravel (.env)

Adicione as seguintes variáveis:

```env
# GitHub
GITHUB_TOKEN=ghp_xxxxxxxxxxxxxxxxxxxxxxxx
GITHUB_REPO=seu-usuario/seu-repositorio

# Domínio (opcional, para URLs customizadas)
APP_DOMAIN=menuolika.com.br
```

## 🔄 Fluxo Completo

### 1️⃣ Cliente Criado
```php
$client = Client::create([
    'name' => 'Pizzaria Bella Vista',
    'plan' => 'ia',
    // ...
]);
```

### 2️⃣ Disparar Deploy

**Opção A - Via API:**
```bash
POST /api/deploy-client
Content-Type: application/json
Authorization: Bearer {token}

{
    "client_id": 5
}
```

**Opção B - Via Comando Artisan:**
```bash
php artisan olika:deploy 5
```

**Opção C - Direto no código:**
```php
$response = Http::post(route('api.deploy.client'), [
    'client_id' => $client->id
]);
```

### 3️⃣ GitHub Actions Executa

1. Recebe o workflow dispatch
2. Faz checkout do código
3. Instala dependências
4. Faz deploy no Railway
5. Envia callback para Laravel

### 4️⃣ Laravel Recebe Callback

- Atualiza `clients.deploy_status` → `completed`
- Atualiza `clients.instance_url` → URL da instância
- Registra log em `deployment_logs`

### 5️⃣ Resultado

Cliente agora tem:
- ✅ Instância Railway rodando
- ✅ URL: `https://pizzaria-bella-vista-5.railway.app`
- ✅ Status: `completed`

## 📊 Monitoramento

### Ver Logs de Deploy

```sql
SELECT 
    dl.*,
    c.name as client_name,
    c.slug
FROM deployment_logs dl
JOIN clients c ON dl.client_id = c.id
ORDER BY dl.created_at DESC
LIMIT 10;
```

### Ver Status dos Clientes

```sql
SELECT 
    id,
    name,
    slug,
    plan,
    deploy_status,
    instance_url,
    created_at
FROM clients
WHERE plan = 'ia'
ORDER BY created_at DESC;
```

## 🔒 Segurança

### Webhook GitHub

Por padrão, a rota `/api/github/webhook` é pública. Para proteger:

1. **Adicionar middleware de autenticação** (ex: token secreto)
2. **Verificar assinatura do GitHub** (X-Hub-Signature-256)

**Exemplo de proteção:**

```php
Route::post('/api/github/webhook', [DeployClientController::class, 'webhook'])
    ->middleware('verify.github.secret');
```

## 🐛 Troubleshooting

### Deploy não inicia

1. ✅ Verificar `GITHUB_TOKEN` no `.env`
2. ✅ Verificar `GITHUB_REPO` no formato correto: `usuario/repositorio`
3. ✅ Verificar se o cliente tem `plan = 'ia'`
4. ✅ Verificar logs do Laravel: `storage/logs/laravel.log`

### Webhook não recebe callback

1. ✅ Verificar `LARAVEL_WEBHOOK_URL` no GitHub Secrets
2. ✅ Verificar se a URL está acessível publicamente
3. ✅ Verificar logs do Laravel

### Railway deploy falha

1. ✅ Verificar `RAILWAY_TOKEN` no GitHub Secrets
2. ✅ Verificar se o serviço existe no Railway
3. ✅ Verificar logs no GitHub Actions

## 📈 Próximos Passos (Opcional)

- [ ] Adicionar fila (Queue) para processar deploys em background
- [ ] Integrar Cloudflare API para criar subdomínios automaticamente
- [ ] Monitorar tempo de deploy
- [ ] Criar dashboard para visualizar deploys
- [ ] Implementar rollback automático em caso de falha

## ✅ Checklist de Deploy

- [ ] Executar SQLs de atualização do banco
- [ ] Configurar secrets no GitHub
- [ ] Adicionar variáveis no `.env` do Laravel
- [ ] Testar deploy manual: `php artisan olika:deploy {id}`
- [ ] Verificar callback do webhook
- [ ] Verificar instância no Railway

---

**🎉 Sistema pronto para deploy automatizado!**


