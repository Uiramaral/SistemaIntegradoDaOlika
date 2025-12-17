# 🔧 Correção: Railway GraphQL API - Migration de `serviceDuplicate` para `serviceClone`

## 📋 Problema Identificado

O erro `"Problem processing request"` (400 Bad Request) ocorreu porque o Railway **descontinuou** a mutation `serviceDuplicate` e agora requer `serviceClone` com parâmetros diferentes.

---

## ✅ Mudanças Necessárias

### 1. Mutation GraphQL

**❌ ANTIGO (não funciona mais):**
```graphql
mutation DuplicateService($input: ServiceDuplicateInput!) {
    serviceDuplicate(input: $input) {
        service {
            id
            name
            deployments { edges { node { url } } }
        }
    }
}
```

**✅ NOVO:**
```graphql
mutation CloneService($input: ServiceCloneInput!) {
    serviceClone(input: $input) {
        id
        name
        deployments { edges { node { url } } }
    }
}
```

### 2. Parâmetros da Mutation

**❌ ANTIGO:**
```php
'input' => [
    'serviceId' => $this->serviceId,  // ❌ Nome incorreto
    'name' => $serviceName,
    'environmentId' => $this->environmentId,
],
```

**✅ NOVO:**
```php
'input' => [
    'sourceServiceId' => $this->serviceId,  // ✅ Nome correto
    'name' => $serviceName,
    'environmentId' => $this->environmentId,  // ✅ Obrigatório
],
```

### 3. Estrutura da Resposta

**❌ ANTIGO:**
```php
$serviceData = $responseData['data']['serviceDuplicate']['service'] ?? null;
```

**✅ NOVO:**
```php
$serviceData = $responseData['data']['serviceClone'] ?? null;
// Note: Não há mais o nível 'service', a resposta é direta
```

---

## 🔄 Mudanças Implementadas

O arquivo `app/Services/RailwayService.php` foi atualizado com:

1. ✅ Mutation GraphQL alterada de `serviceDuplicate` para `serviceClone`
2. ✅ Parâmetro `serviceId` alterado para `sourceServiceId`
3. ✅ Estrutura de resposta ajustada (removido nível `service`)
4. ✅ Logging melhorado para debug
5. ✅ Validações mais robustas

---

## 🧪 Teste Após Correção

Execute a rota de teste novamente:

```
GET /api/test/generate-client-with-deploy
```

**Resultado esperado:**
- ✅ Cliente criado com plano "ia"
- ✅ Serviço clonado no Railway
- ✅ URL da instância capturada e salva em `instance_url`
- ✅ Variáveis de ambiente configuradas

---

## ⚠️ Importante

### Variáveis de Ambiente Necessárias

Certifique-se de que as seguintes variáveis estão configuradas no `.env`:

```env
RAILWAY_API_KEY=sua_chave_api_railway
RAILWAY_SERVICE_ID=id_do_servico_modelo
RAILWAY_ENVIRONMENT_ID=id_do_ambiente
```

### Como Obter os IDs

1. **RAILWAY_API_KEY**: 
   - Railway Dashboard → Settings → API Tokens → Create Token

2. **RAILWAY_SERVICE_ID**:
   - Railway Dashboard → Seu projeto → Serviço modelo → Settings → Service ID

3. **RAILWAY_ENVIRONMENT_ID**:
   - Railway Dashboard → Seu projeto → Settings → Environment ID

---

## 📝 Logs Esperados (Sucesso)

```
[INFO] RailwayService: Iniciando clonagem de serviço
[INFO] RailwayService: Instância criada com sucesso
```

---

## 📝 Logs Esperados (Erro)

Se ainda houver erro, os logs agora incluem mais detalhes:

```
[ERROR] RailwayService: Erro HTTP ao clonar serviço
  - status: 400/401/403
  - response: detalhes do erro
  - service_id: id usado
  - environment_id: id usado
```

---

**Correção aplicada! ✅**

