# 📋 Explicação: Campo `instance_url` na Tabela `clients`

## 🎯 Propósito

O campo `instance_url` armazena a **URL gerada pelo Railway** quando um novo serviço é criado. Esta URL é onde será integrado o **Laravel com o WhatsApp** (instância Node.js Gateway).

---

## 🔗 O que é essa URL?

Quando o `RailwayService` clona um serviço modelo no Railway:

1. **Railway cria um novo serviço** com um nome único (ex: `cliente-slug-ia`)
2. **Railway gera uma URL única** para esse serviço (ex: `https://cliente-slug-ia-abc123.railway.app`)
3. **Essa URL é salva** no campo `instance_url` da tabela `clients`
4. **Essa URL é onde o Node.js Gateway** (integração WhatsApp/IA) estará rodando

---

## 📊 Estrutura da Tabela

```sql
CREATE TABLE clients (
    id BIGINT UNSIGNED PRIMARY KEY,
    name VARCHAR(255),
    slug VARCHAR(255),
    plan ENUM('basic', 'ia'),
    instance_url VARCHAR(255) NULL,  -- ✅ URL do Railway
    whatsapp_phone VARCHAR(20) NULL,
    active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

---

## 🔄 Fluxo de Criação

```
1. Cliente é criado (plano "ia")
   ↓
2. RailwayService::cloneServiceForClient() é chamado
   ↓
3. Railway GraphQL API clona o serviço modelo
   ↓
4. Railway retorna o novo service ID e URL
   ↓
5. URL é extraída da resposta: $deployments[0]['node']['url']
   ↓
6. URL é salva em duas tabelas:
   - clients.instance_url = "https://..."
   - instances.url = "https://..."
   ↓
7. Variáveis de ambiente são configuradas no Railway
   ↓
8. Node.js Gateway sobe e fica acessível nessa URL
```

---

## 💾 Onde a URL é Salva

### 1. Tabela `clients`
```php
$client->update(['instance_url' => $url]);
```

**Propósito:** Referência rápida da URL do cliente

### 2. Tabela `instances`
```php
$instance = Instance::updateOrCreate(
    ['assigned_to' => $client->id],
    ['url' => $url, 'status' => 'assigned']
);
```

**Propósito:** Controle de instâncias Railway (pode ter histórico, status, etc.)

---

## 📍 Exemplo Real

Na sua tabela `clients`:

| id | name | slug | plan | instance_url | whatsapp_phone |
|----|------|------|------|--------------|----------------|
| 1 | Olika Tecnologia | olika | ia | `https://olika.menuonline.com.br` | 5571999999999 |
| 2 | Café & Cia 476 | cafe-cia-476-8640 | basic | `NULL` | `NULL` |
| 3 | Pastelaria Real 442 | pastelaria-real-442-3625 | basic | `NULL` | `NULL` |

**Observação:** 
- Cliente ID 1 (Olika) tem plano "ia" e possui `instance_url`
- Clientes ID 2 e 3 têm plano "basic", então `instance_url` é `NULL`

---

## 🔧 Código que Captura a URL

**Arquivo:** `app/Services/RailwayService.php`

```php
// Extrai a URL dos deployments do Railway
$deployments = $serviceData['deployments']['edges'] ?? [];
$url = $deployments[0]['node']['url'] ?? null;

// Se não tiver URL ainda (deployment em andamento)
if (!$url) {
    Log::warning('URL não disponível imediatamente');
    // URL padrão (pode não ser a final)
    $url = "https://{$serviceName}.railway.app";
}

// Salva na tabela clients
$client->update(['instance_url' => $url]);

// Salva na tabela instances
$instance = Instance::updateOrCreate(
    ['assigned_to' => $client->id],
    ['url' => $url, 'status' => 'assigned']
);
```

---

## ⚠️ Importante

1. **Apenas clientes com plano "ia"** têm `instance_url` preenchido
2. **A URL pode levar alguns segundos** para ficar disponível após a criação do serviço
3. **O Node.js Gateway** precisa estar configurado para responder nessa URL
4. **A URL é única** por cliente e não muda (a menos que o serviço seja recriado)

---

## 🎯 Uso da URL

A `instance_url` é usada para:

1. **Identificar onde o Node.js Gateway está rodando**
2. **Configurar webhooks** que apontam para essa instância
3. **Monitorar status** da instância
4. **Gerenciar instâncias** (parar, reiniciar, etc.)

---

**Campo `instance_url` documentado! ✅**

