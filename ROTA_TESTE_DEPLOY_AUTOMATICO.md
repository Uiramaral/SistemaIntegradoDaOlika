# 🚀 Rota de Teste - Deploy Automático no Railway

## 📋 Problema Identificado

O cliente foi criado com plano **"basic"** (aleatório), então **não foi feito deploy** no Railway. Apenas clientes com plano **"ia"** podem ter instância Railway.

---

## ✅ Solução

Use a rota de teste que **cria cliente com plano IA e faz deploy automático**:

```
GET /api/test/generate-client-with-deploy
```

Esta rota:
- ✅ Cria cliente com plano **"ia"** (forçado)
- ✅ Gera token automaticamente
- ✅ **Faz deploy automático no Railway**
- ✅ Retorna informações completas

---

## 🧪 Como Usar

```bash
curl https://devpedido.menuolika.com.br/api/test/generate-client-with-deploy
```

---

## 📤 Resposta Esperada

```json
{
  "success": true,
  "message": "Cliente de teste criado com sucesso!",
  "client": {
    "id": 4,
    "name": "Pizzaria Bella Vista 456",
    "slug": "pizzaria-bella-vista-456-7891",
    "plan": "ia",
    "whatsapp_phone": "5571987654321",
    "active": true,
    "instance_url": "https://pizzaria-bella-vista-456-7891-ia.railway.app"
  },
  "token": {
    "id": 3,
    "token": "abc123...",
    "created_at": "2025-12-05 02:20:00"
  },
  "info": {
    "has_ia": true,
    "has_basic": false,
    "can_deploy": true
  },
  "railway_deploy": {
    "success": true,
    "instance_id": 1,
    "instance_url": "https://pizzaria-bella-vista-456-7891-ia.railway.app",
    "instance_status": "assigned"
  }
}
```

---

## 🔧 Diferença entre as Rotas

| Rota | Plano | Deploy Railway |
|------|-------|----------------|
| `/api/test/generate-client` | Aleatório (basic ou ia) | ❌ Não |
| `/api/test/generate-client-with-deploy` | **Sempre "ia"** | ✅ **Sim, automático** |

---

## ⚠️ Requisitos

Para o deploy funcionar, você precisa ter configurado no `.env`:

```bash
RAILWAY_API_KEY=rwsk_xxxxxxxxxxxxxxxxxxxxx
RAILWAY_SERVICE_ID=xxxxx-xxxxx-xxxxx
RAILWAY_ENVIRONMENT_ID=xxxxx-xxxxx-xxxxx
```

---

**Use a rota `/api/test/generate-client-with-deploy` para criar cliente e fazer deploy automático! 🚀**

