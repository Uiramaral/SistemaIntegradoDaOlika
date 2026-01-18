# 🔑 Script para Obter Token Railway (rwsk_)

## 📋 Descrição

Este script obtém automaticamente o token Railway (`rwsk_`) usando o CLI do Railway e salva em um arquivo para uso posterior.

---

## ⚠️ Importante: Diferença entre Tokens

### Token CLI (rwsk_)
- **O que é**: Token de autenticação do Railway CLI
- **Como obter**: Via `railway whoami --json` ou este script
- **Uso**: Para autenticação via CLI do Railway
- **Formato**: `rwsk_xxxxxxxxxxxxx`

### Token API (RAILWAY_API_KEY)
- **O que é**: Token de API para usar com a GraphQL API do Railway
- **Como obter**: Railway Dashboard → Settings → API Tokens → Create Token
- **Uso**: Para usar no `RailwayService.php` (Laravel)
- **Formato**: `xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx` (UUID)

**Nota**: O script obtém o token CLI (`rwsk_`), que é diferente do token de API usado no `RailwayService.php`.

---

## 🚀 Como Usar

### 1. Pré-requisitos

Certifique-se de ter o Railway CLI instalado:

```bash
# Instalar Railway CLI globalmente
npm install -g @railway/cli

# Ou via script de instalação
curl -fsSL https://railway.app/install.sh | sh
```

### 2. Autenticar no Railway CLI

```bash
railway login
```

Isso abrirá o navegador para autenticação. Após autenticar, o token será salvo localmente.

### 3. Executar o Script

```bash
cd olika-whatsapp-integration
npm run get-token
```

### 4. Resultado

O script irá:
- ✅ Obter o token `rwsk_` do Railway CLI
- ✅ Salvar em `.railway_token` na raiz do projeto
- ✅ Exibir o token no console

---

## 📁 Arquivos Criados

```
olika-whatsapp-integration/
├── scripts/
│   └── getRailwayToken.js    # Script para obter token
├── .railway_token            # Token salvo (gerado automaticamente)
└── package.json              # Script adicionado: "get-token"
```

---

## 🔧 Estrutura do Script

```javascript
// scripts/getRailwayToken.js
const { execSync } = require("child_process");
const fs = require("fs");
const path = require("path");

// 1. Executa: railway whoami --json
// 2. Extrai o token do JSON
// 3. Salva em .railway_token
// 4. Exibe no console
```

---

## 💡 Uso do Token

### Opção 1: Usar diretamente no código
```javascript
const token = fs.readFileSync('.railway_token', 'utf8').trim();
```

### Opção 2: Exportar como variável de ambiente
```bash
export RAILWAY_CLI_TOKEN=$(cat .railway_token)
```

### Opção 3: Usar no Laravel (após converter para API token)
Se você precisar usar no `RailwayService.php`, você ainda precisará gerar um **token de API** no dashboard do Railway, pois o token CLI (`rwsk_`) não funciona com a GraphQL API.

---

## ⚠️ Segurança

1. **Não commitar o token**: Adicione `.railway_token` ao `.gitignore`
2. **Permissões do arquivo**: O script salva com permissões `600` (apenas leitura/escrita para o proprietário)
3. **Token expira**: Tokens CLI podem expirar; execute o script novamente se necessário

---

## 🔍 Troubleshooting

### Erro: "railway: command not found"
```bash
# Instale o Railway CLI
npm install -g @railway/cli
```

### Erro: "not authorized"
```bash
# Faça login novamente
railway login
```

### Erro: "Token não encontrado"
- Verifique se você está autenticado: `railway whoami`
- Tente fazer logout e login novamente: `railway logout && railway login`

---

## 📝 Exemplo de Saída

```
✅ Railway Token (rwsk_) encontrado: rwsk_abc123def456ghi789
✅ Token salvo em: /path/to/olika-whatsapp-integration/.railway_token

💡 Você pode usar este token como RAILWAY_API_KEY no Laravel
   Ou copie o valor: rwsk_abc123def456ghi789
```

---

## 🔗 Relacionado

- **RailwayService.php**: Usa `RAILWAY_API_KEY` (token de API diferente)
- **Documentação Railway CLI**: https://docs.railway.app/develop/cli
- **Railway Dashboard**: https://railway.app/dashboard

---

**Script criado! ✅**

