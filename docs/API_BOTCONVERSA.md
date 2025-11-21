# API BotConversa - Sincronização de Clientes

Esta API permite sincronizar dados de clientes do BotConversa com o sistema Olika.

## URLs

### Base URL
```
https://menuolika.com.br/api/botconversa
```

### Endpoints

#### 1. Sincronizar Cliente Individual
```
POST /api/botconversa/sync-customer
```

#### 2. Sincronizar Múltiplos Clientes (Batch)
```
POST /api/botconversa/sync-customers
```

## Autenticação

Estes endpoints **NÃO** requerem autenticação e estão liberados do CSRF.

## Formato de Requisição

Todas as requisições devem ser enviadas como **JSON** com o header:
```
Content-Type: application/json
Accept: application/json
```

## Dados Aceitos

### Campos Obrigatórios

| Campo | Tipo | Descrição | Exemplo |
|-------|------|-----------|---------|
| `phone` | string | Telefone do cliente (será normalizado removendo caracteres especiais) | `"11999999999"` ou `"(11) 99999-9999"` |
| `name` | string | Nome completo do cliente | `"João Silva"` |

### Campos Opcionais

| Campo | Tipo | Descrição | Exemplo |
|-------|------|-----------|---------|
| `email` | string (email) | Email do cliente | `"joao@example.com"` |
| `newsletter` | boolean | Se o cliente recebe notificações/newsletter | `true` ou `false` (padrão: `false`) |
| `visitor_id` | string | ID do visitante no BotConversa | `"visitor_123456"` |
| `address` | string | Endereço completo | `"Rua das Flores, 123"` |
| `neighborhood` | string | Bairro | `"Centro"` |
| `city` | string | Cidade | `"São Paulo"` |
| `state` | string | Estado (2 caracteres) | `"SP"` |
| `zip_code` | string | CEP | `"01234-567"` |
| `birth_date` | string (date) | Data de nascimento (formato: YYYY-MM-DD) | `"1990-01-15"` |
| `cpf` | string | CPF do cliente | `"123.456.789-00"` |
| `preferences` | object/array | Preferências do cliente (JSON) | `{"favorite_category": "pizzas"}` |
| `created_at` | string (datetime) | Data de cadastro do cliente (formato: YYYY-MM-DD HH:MM:SS ou ISO 8601) | `"2024-01-15 10:30:00"` ou `"2024-01-15T10:30:00"` |
| `last_order_at` | string (datetime) | Data do último pedido do cliente (formato: YYYY-MM-DD HH:MM:SS ou ISO 8601) | `"2024-01-20 15:45:00"` ou `"2024-01-20T15:45:00"` |

## Exemplos de Requisição

### 1. Sincronizar Cliente Individual - Mínimo

```json
{
  "phone": "11999999999",
  "name": "João Silva"
}
```

### 2. Sincronizar Cliente Individual - Completo

```json
{
  "phone": "(11) 99999-9999",
  "name": "João Silva",
  "email": "joao@example.com",
  "newsletter": true,
  "visitor_id": "visitor_123456",
  "address": "Rua das Flores, 123",
  "neighborhood": "Centro",
  "city": "São Paulo",
  "state": "SP",
  "zip_code": "01234-567",
  "birth_date": "1990-01-15",
  "cpf": "123.456.789-00",
  "preferences": {
    "favorite_category": "pizzas",
    "allergies": ["lactose"]
  },
  "created_at": "2024-01-15 10:30:00",
  "last_order_at": "2024-01-20 15:45:00"
}
```

### 2.1. Sincronizar Cliente Individual - Com Datas

```json
{
  "phone": "11999999999",
  "name": "João Silva",
  "newsletter": true,
  "created_at": "2024-01-15T10:30:00",
  "last_order_at": "2024-01-20T15:45:00"
}
```

### 3. Sincronizar Múltiplos Clientes (Batch)

```json
{
  "customers": [
    {
      "phone": "11999999999",
      "name": "João Silva",
      "email": "joao@example.com",
      "newsletter": true
    },
    {
      "phone": "11888888888",
      "name": "Maria Santos",
      "email": "maria@example.com",
      "newsletter": false
    }
  ]
}
```

**Limite**: Máximo de 100 clientes por requisição batch.

## Comportamento

### Sincronização Individual (`/sync-customer`)

1. O sistema **normaliza o telefone** removendo caracteres especiais (parênteses, hífens, espaços, etc.)
2. Verifica se já existe um cliente com o telefone informado
3. Se **existe**: atualiza os dados do cliente (apenas campos fornecidos)
   - Se `created_at` for fornecido, atualiza a data de cadastro do cliente
   - Se `last_order_at` for fornecido, atualiza a data do último pedido
4. Se **não existe**: cria um novo cliente
   - Se `created_at` for fornecido, usa essa data como data de cadastro
   - Se `last_order_at` for fornecido, define a data do último pedido
5. O campo `newsletter` determina se o cliente recebe notificações
6. Retorna informações do cliente criado/atualizado

**Importante sobre datas:**
- `created_at`: Permite atualizar a data de cadastro do cliente mesmo se ele já existe
- `last_order_at`: Define ou atualiza a data do último pedido (útil quando o cliente já tem compras no BotConversa)

### Sincronização Batch (`/sync-customers`)

1. Processa múltiplos clientes em uma única requisição
2. Cada cliente é processado individualmente (mesmo comportamento da sincronização individual)
3. Se algum cliente falhar, os demais continuam sendo processados
4. Retorna estatísticas de criação, atualização e erros

## Respostas

### Sucesso - Cliente Criado (201)

```json
{
  "success": true,
  "message": "Cliente criado com sucesso",
  "action": "created",
  "customer": {
    "id": 123,
    "name": "João Silva",
    "phone": "11999999999",
    "email": "joao@example.com",
    "newsletter": true,
    "created_at": "2024-01-15 10:30:00",
    "last_order_at": "2024-01-20 15:45:00"
  }
}
```

### Sucesso - Cliente Atualizado (200)

```json
{
  "success": true,
  "message": "Cliente atualizado com sucesso",
  "action": "updated",
  "customer": {
    "id": 123,
    "name": "João Silva",
    "phone": "11999999999",
    "email": "joao@example.com",
    "newsletter": true,
    "created_at": "2024-01-15 10:30:00",
    "last_order_at": "2024-01-20 15:45:00"
  }
}
```

### Sucesso - Batch (200)

```json
{
  "success": true,
  "message": "Sincronização em lote concluída",
  "results": {
    "created": 5,
    "updated": 10,
    "errors": 0,
    "details": [
      {
        "index": 0,
        "phone": "11999999999",
        "action": "created",
        "customer_id": 123
      },
      {
        "index": 1,
        "phone": "11888888888",
        "action": "updated",
        "customer_id": 124
      }
    ]
  }
}
```

### Erro - Validação (422)

```json
{
  "success": false,
  "message": "Dados inválidos",
  "errors": {
    "phone": ["O campo phone é obrigatório."],
    "email": ["O campo email deve ser um endereço de e-mail válido."]
  }
}
```

### Erro - Servidor (500)

```json
{
  "success": false,
  "message": "Erro ao salvar cliente: [mensagem de erro]"
}
```

## Códigos HTTP

| Código | Significado |
|--------|-------------|
| 200 | Sucesso - Cliente atualizado ou batch processado |
| 201 | Sucesso - Cliente criado |
| 422 | Erro de validação |
| 500 | Erro interno do servidor |

## Notas Importantes

1. **Telefone como Chave Única**: O telefone é usado como identificador único. Se você enviar dados para um telefone que já existe, o cliente será atualizado.

2. **Atualização Parcial**: Ao atualizar um cliente existente, apenas os campos fornecidos são atualizados. Campos não enviados mantêm seus valores originais.

3. **Normalização de Telefone**: O sistema remove automaticamente caracteres especiais do telefone. `"(11) 99999-9999"` será normalizado para `"11999999999"`.

4. **Campo Newsletter**: O campo `newsletter` determina se o cliente recebe notificações. Padrão: `false` se não informado.

5. **Logs**: Todas as requisições são logadas no sistema para auditoria e debugging.

6. **Transações**: Operações são realizadas dentro de transações do banco de dados. Se ocorrer erro, todas as mudanças são revertidas.

## Exemplo de Integração (cURL)

### Cliente Individual
```bash
curl -X POST https://menuolika.com.br/api/botconversa/sync-customer \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "phone": "11999999999",
    "name": "João Silva",
    "email": "joao@example.com",
    "newsletter": true,
    "created_at": "2024-01-15 10:30:00",
    "last_order_at": "2024-01-20 15:45:00"
  }'
```

### Batch
```bash
curl -X POST https://menuolika.com.br/api/botconversa/sync-customers \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "customers": [
      {
        "phone": "11999999999",
        "name": "João Silva",
        "newsletter": true
      },
      {
        "phone": "11888888888",
        "name": "Maria Santos",
        "newsletter": false
      }
    ]
  }'
```

## Exemplo de Integração (JavaScript/Fetch)

```javascript
// Sincronizar cliente individual
async function syncCustomer(customerData) {
  const response = await fetch('https://menuolika.com.br/api/botconversa/sync-customer', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    },
    body: JSON.stringify(customerData)
  });
  
  const result = await response.json();
  return result;
}

// Uso
syncCustomer({
  phone: '11999999999',
  name: 'João Silva',
  email: 'joao@example.com',
  newsletter: true,
  created_at: '2024-01-15 10:30:00',
  last_order_at: '2024-01-20 15:45:00'
}).then(result => {
  console.log('Cliente sincronizado:', result);
});
```

## Exemplo de Integração (PHP)

```php
<?php
function syncCustomer($customerData) {
    $url = 'https://menuolika.com.br/api/botconversa/sync-customer';
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($customerData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response, true);
}

// Uso
$result = syncCustomer([
    'phone' => '11999999999',
    'name' => 'João Silva',
    'email' => 'joao@example.com',
    'newsletter' => true,
    'created_at' => '2024-01-15 10:30:00',
    'last_order_at' => '2024-01-20 15:45:00'
]);
?>
```

## Suporte

Em caso de dúvidas ou problemas, verifique os logs do sistema ou entre em contato com o suporte técnico.

