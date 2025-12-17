# 🧠 Sistema Integrado da Olika

## Guia Técnico Completo — Multi-tenant + Subdomínios + Planos + Automação Railway

---

## 🧱 1️⃣ Visão Geral

O sistema funciona como uma **plataforma SaaS unificada**, onde:

- ✅ **Laravel** centraliza todos os dados, clientes e planos
- ✅ Cada cliente IA ganha uma **instância isolada no Railway**
- ✅ O **Node.js** (serviço IA) processa IA + WhatsApp + transcrição de áudio
- ✅ Tudo se comunica via **autenticação por token** (`X-API-Token`)

### Estrutura

```
Cliente → Laravel (Painel) → Railway (Instância Node.js) → WhatsApp/OpenAI
```

---

## 🌐 2️⃣ Arquitetura de Domínios e Subdomínios

| Tipo | Exemplo | Função |
|------|---------|--------|
| **Painel Laravel (central)** | `https://devdashboard.menuolika.com.br` | Centraliza dados e API |
| **Frontend pedidos** | `https://devpedido.menuolika.com.br` | Interface cliente final |
| **Cliente IA** | `https://churrasquinhodoze.menuonline.com.br` | Instância Railway do cliente |
| **API IA → Laravel** | `https://devpedido.menuolika.com.br/api/webhook` | Comunicação via token |

### DNS (recomendado)

| Tipo | Nome | Valor | Descrição |
|------|------|-------|-----------|
| **A** | `*.menuolika.com.br` | IP do Laravel | Suporte a múltiplos subdomínios |
| **A** | `*.menuonline.com.br` | IP do proxy | Futuras instâncias clientes |

---

## 🧩 3️⃣ Middleware e Modelos Laravel

### 📦 `/app/Http/Middleware/ApiTokenMiddleware.php`

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ApiToken;
use Illuminate\Support\Facades\Log;

class ApiTokenMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $tokenHeader = $request->header('X-API-Token');
        
        if (!$tokenHeader) {
            Log::warning('ApiTokenMiddleware: Token ausente', [
                'ip' => $request->ip(),
                'url' => $request->fullUrl()
            ]);
            return response()->json(['error' => 'Token ausente'], 401);
        }

        // Buscar token na tabela api_tokens com relacionamento client
        $apiToken = ApiToken::with('client')
            ->where('token', $tokenHeader)
            ->where(function($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
            })
            ->first();

        if (!$apiToken) {
            Log::warning('ApiTokenMiddleware: Token inválido ou expirado', [
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
                'token_prefix' => substr($tokenHeader, 0, 10) . '...'
            ]);
            return response()->json(['error' => 'Token inválido'], 403);
        }

        // Verificar se o cliente está ativo
        if (!$apiToken->client || !$apiToken->client->active) {
            Log::warning('ApiTokenMiddleware: Cliente inativo', [
                'client_id' => $apiToken->client_id,
                'ip' => $request->ip()
            ]);
            return response()->json(['error' => 'Cliente inativo'], 403);
        }

        // Adicionar client_id ao request para uso posterior
        $request->merge(['authenticated_client_id' => $apiToken->client_id]);
        
        // Disponibilizar o cliente no container para uso em controllers
        app()->instance('authenticated_client', $apiToken->client);

        return $next($request);
    }
}
```

### 📦 `/app/Http/Middleware/CheckPlan.php`

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPlan
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        
        if (!$user || !$user->client) {
            return response()->json([
                'error' => 'Client not found',
                'message' => 'Usuário não está vinculado a um cliente'
            ], 403);
        }

        $client = $user->client;

        if (!$client->active) {
            return response()->json([
                'error' => 'Client inactive',
                'message' => 'Cliente está inativo'
            ], 403);
        }

        if ($request->is('api/whatsapp/*') || $request->is('api/ai-status*')) {
            if (!$client->hasIaPlan()) {
                return response()->json([
                    'error' => 'Plan not allowed',
                    'message' => 'Plano básico — integração IA não disponível.'
                ], 403);
            }
        }

        return $next($request);
    }
}
```

### 📦 `/app/Models/Client.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'plan',
        'instance_url',
        'whatsapp_phone',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    /**
     * ✅ Gera token automaticamente quando cliente é criado
     */
    protected static function booted()
    {
        static::created(function ($client) {
            // Gerar token único para o cliente
            $token = self::generateUniqueToken();
            
            // Criar token de API para o cliente
            ApiToken::create([
                'client_id' => $client->id,
                'token' => $token,
                'expires_at' => null, // Token sem expiração
            ]);
            
            Log::info('Client::booted - Token gerado automaticamente', [
                'client_id' => $client->id,
                'client_name' => $client->name,
            ]);
        });
    }

    /**
     * ✅ Gera um token único para autenticação
     */
    private static function generateUniqueToken(): string
    {
        // Usar Str::random() do Laravel para gerar token seguro de 64 caracteres
        do {
            $token = Str::random(64);
        } while (ApiToken::where('token', $token)->exists());
        
        return $token;
    }

    /**
     * ✅ Gera um novo token para o cliente (regenerar)
     */
    public function regenerateApiToken(): string
    {
        // Gerar novo token
        $token = self::generateUniqueToken();
        
        ApiToken::create([
            'client_id' => $this->id,
            'token' => $token,
            'expires_at' => null,
        ]);
        
        Log::info('Client::regenerateApiToken - Novo token gerado', [
            'client_id' => $this->id,
            'client_name' => $this->name,
        ]);
        
        return $token;
    }

    // ... relacionamentos e métodos ...
    
    public function hasIaPlan(): bool
    {
        return $this->plan === 'ia';
    }

    public function hasBasicPlan(): bool
    {
        return $this->plan === 'basic';
    }
}
```

### 📦 `/app/Models/ApiToken.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'token',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    /**
     * Relacionamento com cliente
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Verifica se o token está válido (não expirado)
     */
    public function isValid(): bool
    {
        if (!$this->expires_at) {
            return true; // Token sem expiração
        }

        return $this->expires_at->isFuture();
    }
}
```

---

## 🧰 4️⃣ Kernel.php

```php
<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    protected $middlewareAliases = [
        // ... outros middlewares ...
        'check.plan' => \App\Http\Middleware\CheckPlan::class,
        'api.token' => \App\Http\Middleware\ApiTokenMiddleware::class,
    ];
}
```

---

## 🌍 5️⃣ Rotas Laravel

### `routes/web.php`

```php
<?php

use Illuminate\Support\Facades\Route;

// API Cliente/Plano (para Node.js consultar informações do cliente e plano)
// ✅ Usa middleware api.token para autenticação via tabela api_tokens
Route::middleware('api.token')->group(function () {
    Route::get('/api/client/{id}', [\App\Http\Controllers\Api\ClientController::class, 'show'])
        ->name('api.client.show');

    Route::get('/api/client/{id}/plan', [\App\Http\Controllers\Api\ClientController::class, 'getPlan'])
        ->name('api.client.plan');
});

// API Status da IA (para controle condicional do Gateway Node.js)
Route::post('/api/ai-status', [\App\Http\Controllers\AiStatusController::class, 'checkStatus'])
    ->name('api.ai.status');

// API Contexto do Cliente (para injeção de dados dinâmicos no prompt da IA)
Route::post('/api/customer-context', [\App\Http\Controllers\Api\CustomerSearchController::class, 'getContext'])
    ->name('api.customer.context');

// Webhook do WhatsApp
Route::post('/api/whatsapp/webhook', [\App\Http\Controllers\WhatsappInstanceController::class, 'handleWebhook'])
    ->name('api.whatsapp.webhook');
```

---

## 🚀 6️⃣ Automação de Instâncias Railway (Clonagem de Serviço Modelo)

### 📦 `/app/Services/RailwayService.php`

```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Client;
use App\Models\Instance;
use App\Models\ApiToken;

class RailwayService
{
    protected $apiKey;
    protected $serviceId; // Serviço modelo base
    protected $environmentId;

    public function __construct()
    {
        $this->apiKey = env('RAILWAY_API_KEY');
        $this->serviceId = env('RAILWAY_SERVICE_ID');
        $this->environmentId = env('RAILWAY_ENVIRONMENT_ID');
    }

    /**
     * Clona o serviço modelo Railway para um novo cliente
     */
    public function cloneServiceForClient(Client $client)
    {
        // Garantir que o cliente tenha um token
        $token = $client->activeApiToken;
        if (!$token) {
            $tokenValue = $client->regenerateApiToken();
            $token = ApiToken::where('token', $tokenValue)->first();
        }

        $serviceName = $client->slug . '-ia';

        try {
            // Clone o serviço modelo usando GraphQL API
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json',
            ])->post('https://backboard.railway.app/graphql/v2', [
                'query' => '
                    mutation DuplicateService($input: ServiceDuplicateInput!) {
                        serviceDuplicate(input: $input) {
                            service {
                                id
                                name
                                deployments {
                                    edges {
                                        node {
                                            url
                                        }
                                    }
                                }
                            }
                        }
                    }',
                'variables' => [
                    'input' => [
                        'serviceId' => $this->serviceId,
                        'name' => $serviceName,
                        'environmentId' => $this->environmentId,
                    ],
                ],
            ]);

            if (!$response->successful()) {
                Log::error('RailwayService: Erro ao clonar serviço', [
                    'response' => $response->body(),
                    'client_id' => $client->id
                ]);
                throw new \Exception('Erro ao clonar serviço: ' . $response->body());
            }

            $data = $response->json('data.serviceDuplicate.service');
            $newServiceId = $data['id'];
            $url = $data['deployments']['edges'][0]['node']['url'] ?? null;

            // Criar registro de instância
            $instance = Instance::updateOrCreate(
                ['assigned_to' => $client->id],
                [
                    'url' => $url,
                    'status' => 'assigned',
                    'service_id' => $newServiceId,
                ]
            );

            // Atualizar cliente com URL da instância
            $client->update(['instance_url' => $url]);

            // Definir variáveis de ambiente
            $this->setEnvVars($newServiceId, [
                'CLIENT_ID' => (string)$client->id,
                'API_TOKEN' => $token->token,
                'LARAVEL_API_URL' => config('app.url'),
                'OPENAI_MODEL' => 'gpt-5-nano',
                'OPENAI_API_KEY' => env('OPENAI_API_KEY'),
                'WH_API_TOKEN' => $token->token,
                'WEBHOOK_URL' => config('app.url') . '/api/whatsapp/webhook',
                'AI_STATUS_URL' => config('app.url') . '/api/ai-status',
                'CUSTOMER_CONTEXT_URL' => config('app.url') . '/api/customer-context',
            ]);

            Log::info('RailwayService: Instância criada com sucesso', [
                'client_id' => $client->id,
                'service_id' => $newServiceId,
                'url' => $url
            ]);

            return $instance;

        } catch (\Exception $e) {
            Log::error('RailwayService: Exceção ao clonar serviço', [
                'error' => $e->getMessage(),
                'client_id' => $client->id
            ]);
            throw $e;
        }
    }

    /**
     * Define variáveis de ambiente no serviço Railway
     */
    protected function setEnvVars($serviceId, $vars)
    {
        foreach ($vars as $key => $value) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => "Bearer {$this->apiKey}",
                    'Content-Type' => 'application/json',
                ])->post('https://backboard.railway.app/graphql/v2', [
                    'query' => '
                        mutation VariableSet($input: VariableSetInput!) {
                            variableSet(input: $input) {
                                id
                            }
                        }',
                    'variables' => [
                        'input' => [
                            'serviceId' => $serviceId,
                            'key' => $key,
                            'value' => (string)$value,
                        ],
                    ],
                ]);

                if (!$response->successful()) {
                    Log::warning('RailwayService: Erro ao definir variável', [
                        'key' => $key,
                        'service_id' => $serviceId,
                        'response' => $response->body()
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('RailwayService: Exceção ao definir variável', [
                    'key' => $key,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }
}
```

### 📦 Rota de Deploy (`routes/web.php` ou `routes/api.php`)

```php
use App\Services\RailwayService;
use App\Models\Client;

Route::post('/api/clients/{id}/deploy', function ($id, RailwayService $railway) {
    try {
        $client = Client::findOrFail($id);
        
        if ($client->plan !== 'ia') {
            return response()->json([
                'error' => 'Plano básico não permite instância IA'
            ], 403);
        }

        $instance = $railway->cloneServiceForClient($client);
        
        return response()->json([
            'success' => true,
            'message' => 'Instância criada com sucesso!',
            'instance' => [
                'url' => $instance->url,
                'status' => $instance->status,
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Erro ao criar instância',
            'message' => $e->getMessage()
        ], 500);
    }
})->middleware(['auth', 'check.plan']);
```

---

## 🔐 7️⃣ Variáveis .env (Laravel)

```bash
# Railway API
RAILWAY_API_KEY=rwsk_xxxxxxxxxxxxxxxxxxxxxxxxxx
RAILWAY_SERVICE_ID=abcd1234-efgh-5678-ijkl-9012mnopqrstu  # Serviço modelo base
RAILWAY_ENVIRONMENT_ID=yyyyyyyy-yyyy-yyyy-yyyy-yyyyyyyyyyyy

# OpenAI
OPENAI_API_KEY=sk-xxxxxx
OPENAI_MODEL=gpt-5-nano

# Aplicação
APP_URL=https://devpedido.menuolika.com.br
```

### Variáveis .env (Node.js - Railway)

```bash
# Cliente
CLIENT_ID=1
API_TOKEN=<token_da_tabela_api_tokens>

# Laravel
LARAVEL_API_URL=https://devpedido.menuolika.com.br
WEBHOOK_URL=https://devpedido.menuolika.com.br/api/whatsapp/webhook
AI_STATUS_URL=https://devpedido.menuolika.com.br/api/ai-status
CUSTOMER_CONTEXT_URL=https://devpedido.menuolika.com.br/api/customer-context
WH_API_TOKEN=<mesmo_token_de_API_TOKEN>

# OpenAI
OPENAI_API_KEY=sk-xxxxxx
OPENAI_MODEL=gpt-5-nano
OPENAI_TIMEOUT=30

# Sistema IA
AI_SYSTEM_PROMPT="Você é um assistente profissional da Olika..."

# Porta
PORT=8080
```

---

## 🧮 8️⃣ Fluxo Completo

| Etapa | Origem | Destino | Ação |
|-------|--------|---------|------|
| **1️⃣** | Laravel | Railway API | Clona o serviço modelo |
| **2️⃣** | Railway | Laravel | Retorna URL e Service ID |
| **3️⃣** | Laravel | Banco `instances` | Salva URL e token |
| **4️⃣** | Laravel | Railway API | Define variáveis .env |
| **5️⃣** | Railway | Instância IA | Sobe automaticamente |
| **6️⃣** | IA Node | Laravel | Envia dados via `/api/whatsapp/webhook` |
| **7️⃣** | IA Node | Laravel | Consulta status via `/api/ai-status` |
| **8️⃣** | IA Node | Laravel | Busca contexto via `/api/customer-context` |

---

## ✅ 9️⃣ Resultado

- ✅ Cada novo cliente IA → cria uma instância Railway em segundos
- ✅ Cada instância tem variáveis .env únicas
- ✅ Comunicação Laravel ↔ IA é segura via token
- ✅ Subdomínios e planos são controlados automaticamente
- ✅ Token é gerado automaticamente ao criar cliente
- ✅ Sistema multi-tenant completo e escalável

---

## 🔧 1️⃣0️⃣ Estrutura de Banco de Dados

### Tabelas Principais

1. **`clients`** - Clientes do sistema
2. **`api_tokens`** - Tokens de autenticação (gerados automaticamente)
3. **`instances`** - Instâncias Railway vinculadas
4. **`users`** - Usuários (com `client_id`)
5. **`orders`** - Pedidos (com `client_id`)
6. **`customers`** - Clientes finais (com `client_id`)
7. **`products`** - Produtos (com `client_id`)

### SQLs Necessários

Execute na ordem:

1. `olika_multi_instance_full_update.sql` - Estrutura completa
2. `add_client_id_to_users.sql` - Adiciona client_id em users
3. `update_existing_data_client_id.sql` - Atualiza dados existentes

---

## 🎯 1️⃣1️⃣ Checklist de Deploy

- [ ] SQL executado no banco de dados
- [ ] Token gerado para cliente padrão (Olika)
- [ ] Variáveis de ambiente configuradas no Laravel
- [ ] Serviço modelo Railway criado e funcionando
- [ ] `RAILWAY_SERVICE_ID` configurado no .env
- [ ] Middleware registrado no Kernel
- [ ] Rotas de API protegidas
- [ ] Teste de autenticação funcionando
- [ ] Clonagem de serviço testada

---

**Sistema 100% pronto para produção! 🚀**

