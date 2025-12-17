# 🧪 Rota de Teste - Gerar Cliente com Dados Aleatórios

## 📋 Descrição

Rota de teste que gera automaticamente um cliente com dados aleatórios para facilitar o desenvolvimento e testes do sistema multi-instância.

---

## 🔗 Endpoint

```
GET /api/test/generate-client
```

**⚠️ ATENÇÃO:** Esta rota é apenas para testes. Desabilite em produção ou proteja com senha/autenticação.

---

## 📤 Resposta

### Sucesso (201)

```json
{
  "success": true,
  "message": "Cliente de teste criado com sucesso!",
  "client": {
    "id": 2,
    "name": "Pizzaria Bella Vista 456",
    "slug": "pizzaria-bella-vista-456-7891",
    "plan": "ia",
    "whatsapp_phone": "5571987654321",
    "active": true,
    "instance_url": null
  },
  "token": {
    "id": 2,
    "token": "abc123def456...",
    "created_at": "2025-01-31 10:30:00"
  },
  "info": {
    "has_ia": true,
    "has_basic": false,
    "can_deploy": true
  },
  "next_steps": {
    "test_client": "GET /api/client/2 (Header: X-API-Token: abc123...)",
    "test_plan": "GET /api/client/2/plan (Header: X-API-Token: abc123...)",
    "deploy_instance": "POST /api/clients/2/deploy (auth required)"
  }
}
```

---

## 🎲 Dados Gerados Aleatoriamente

### Nomes:
- Churrascaria do Zé
- Pizzaria Bella Vista
- Hamburgueria Artesanal
- Restaurante Sabor Caseiro
- Lanchonete do Bairro
- Delivery Express
- Cantina Italiana
- Sushi Bar Premium
- Café & Cia
- Pastelaria Real

### Planos:
- `basic` - Plano básico (sem IA)
- `ia` - Plano IA (com recursos de IA)

### WhatsApp (apenas para plano IA):
- Formato: `5571XXXXXXXXX`
- Gerado aleatoriamente entre 900000000 e 999999999

---

## ✨ Funcionalidades

- ✅ Gera nome aleatório de estabelecimento
- ✅ Gera slug único automaticamente
- ✅ Escolhe plano aleatório (basic ou ia)
- ✅ Gera número WhatsApp para plano IA
- ✅ **Token gerado automaticamente** (via Model Client)
- ✅ Retorna token para uso imediato
- ✅ Sugere próximos passos de teste

---

## 🧪 Exemplo de Uso

```bash
# Gerar cliente de teste
curl https://devpedido.menuolika.com.br/api/test/generate-client

# Resposta incluirá o token gerado automaticamente
# Use esse token para testar outras APIs
```

---

## ⚠️ Segurança

**Recomendações:**
1. Desabilitar em produção ou proteger com middleware
2. Adicionar autenticação se necessário
3. Limitar por IP ou ambiente

**Exemplo de proteção:**
```php
Route::get('/api/test/generate-client', function () {
    // ...
})->middleware(['auth', 'role:admin'])->name('api.test.generate-client');
```

Ou apenas em desenvolvimento:
```php
if (app()->environment('local', 'development')) {
    Route::get('/api/test/generate-client', function () {
        // ...
    });
}
```

---

## 🎯 Próximos Passos Após Gerar

1. **Testar autenticação:**
   ```bash
   curl -H "X-API-Token: {token}" \
        https://devpedido.menuolika.com.br/api/client/{id}
   ```

2. **Testar deploy (se plano IA):**
   ```bash
   curl -X POST -H "Authorization: Bearer {token}" \
        https://devdashboard.menuolika.com.br/api/clients/{id}/deploy
   ```

---

**Rota de teste criada com sucesso! 🎉**

