# 🚀 GitHub Actions - Deploy Automático no Railway

## 📋 Descrição

Este documento explica como configurar o deploy automático do projeto **Olika WhatsApp Gateway** no Railway usando GitHub Actions.

---

## 📁 Arquivos Criados

1. **`.github/workflows/deploy.yml`** - Deploy automático em push para `main`/`master`
2. **`.github/workflows/deploy-manual.yml`** - Deploy manual via GitHub Actions UI

---

## ⚙️ Configuração Inicial

### 1. Obter Token Railway

Você precisa de um **token de serviço** do Railway (não o token CLI):

1. Acesse: https://railway.app/dashboard
2. Vá em **Settings** → **Tokens**
3. Clique em **New Token**
4. Dê um nome (ex: "GitHub Actions Deploy")
5. **Copie o token** (você não conseguirá vê-lo novamente!)

**⚠️ Importante**: Use um **Service Token**, não um Personal Token.

---

### 2. Configurar GitHub Secrets

No seu repositório GitHub:

1. Vá em **Settings** → **Secrets and variables** → **Actions**
2. Clique em **New repository secret**
3. Nome: `RAILWAY_TOKEN`
4. Valor: Cole o token do Railway que você copiou
5. Clique em **Add secret**

---

### 3. Configurar Railway Project

No Railway Dashboard:

1. Acesse seu projeto
2. Vá em **Settings** → **General**
3. **Anote o nome do serviço** (ex: `olika-gateway`)
4. Se necessário, crie um arquivo `railway.json` ou `.railway` para vincular o projeto

**Opção A: Via arquivo `.railway` (recomendado)**

No diretório `olika-whatsapp-integration/`, crie `.railway`:

```json
{
  "project": "seu-project-id",
  "service": "olika-gateway"
}
```

**Opção B: Via variável de ambiente no GitHub Actions**

Adicione no workflow:

```yaml
env:
  RAILWAY_PROJECT_ID: ${{ secrets.RAILWAY_PROJECT_ID }}
  RAILWAY_SERVICE_NAME: olika-gateway
```

---

## 🔄 Fluxo de Deploy

### Deploy Automático (deploy.yml)

```
Push para main/master
    ↓
GitHub Actions acionado
    ↓
Checkout do código
    ↓
Setup Node.js 20
    ↓
Instala dependências (npm ci)
    ↓
Instala Railway CLI
    ↓
Autentica com Railway (token)
    ↓
Executa: railway up
    ↓
Verifica status do deploy
    ↓
✅ Deploy concluído!
```

---

## 🎯 Como Funciona

### Workflow Automático (`deploy.yml`)

- **Trigger**: Push para `main` ou `master`
- **Filtro**: Só executa se houver mudanças em `olika-whatsapp-integration/**`
- **Ação**: Faz deploy automático no Railway

### Workflow Manual (`deploy-manual.yml`)

- **Trigger**: Execução manual via GitHub Actions UI
- **Opções**:
  - Escolher nome do serviço
  - Escolher ambiente (production/staging)

---

## 🚀 Como Usar

### Deploy Automático

1. Faça push para o branch `main`:
   ```bash
   git add .
   git commit -m "feat: nova funcionalidade"
   git push origin main
   ```

2. O GitHub Actions acionará automaticamente o deploy

3. Acompanhe o progresso em: **Actions** → **Railway Deploy - Olika Gateway**

### Deploy Manual

1. Acesse: **Actions** → **Railway Deploy - Manual Trigger**
2. Clique em **Run workflow**
3. Escolha:
   - Branch (geralmente `main`)
   - Nome do serviço (ou deixe vazio para padrão)
   - Ambiente (production/staging)
4. Clique em **Run workflow**

---

## 📝 Estrutura dos Workflows

### deploy.yml

```yaml
on:
  push:
    branches: [main, master]
    paths: ['olika-whatsapp-integration/**']
  workflow_dispatch: # Permite execução manual também

jobs:
  deployment:
    runs-on: ubuntu-latest
    defaults:
      run:
        working-directory: ./olika-whatsapp-integration
    steps:
      - Checkout
      - Setup Node.js 20
      - Install Dependencies
      - Install Railway CLI
      - Authenticate with Railway
      - Deploy to Railway
      - Check Status
```

### deploy-manual.yml

```yaml
on:
  workflow_dispatch:
    inputs:
      service_name: '...'
      environment: '...'

jobs:
  deployment:
    # Mesma estrutura, mas com inputs customizáveis
```

---

## ⚙️ Variáveis e Secrets

### Secrets Necessários

| Secret | Descrição | Onde Obter |
|--------|-----------|------------|
| `RAILWAY_TOKEN` | Token de serviço Railway | Railway Dashboard → Settings → Tokens |

### Variáveis Opcionais

Você pode adicionar mais secrets se necessário:

- `RAILWAY_PROJECT_ID`: ID do projeto Railway
- `RAILWAY_SERVICE_NAME`: Nome do serviço (se não usar padrão)

---

## 🔍 Troubleshooting

### Erro: "RAILWAY_TOKEN not found"

**Solução**: Verifique se o secret foi criado corretamente:
1. GitHub → Settings → Secrets and variables → Actions
2. Verifique se `RAILWAY_TOKEN` existe
3. Se não, crie novamente

### Erro: "Service not found"

**Solução**: 
1. Verifique o nome do serviço no Railway Dashboard
2. Atualize o workflow com o nome correto:
   ```yaml
   run: railway up --service nome-correto-do-servico
   ```

### Erro: "Not authenticated"

**Solução**:
1. Verifique se o token está correto
2. Regere o token no Railway Dashboard
3. Atualize o secret no GitHub

### Deploy não aciona automaticamente

**Verificações**:
1. O branch é `main` ou `master`?
2. Há mudanças em `olika-whatsapp-integration/**`?
3. O workflow está habilitado? (GitHub → Actions → verificar se não está desabilitado)

---

## 📊 Monitoramento

### Logs do Deploy

Acesse em: **GitHub → Actions → [Workflow Run]**

### Status no Railway

Acesse em: **Railway Dashboard → [Seu Projeto] → Deployments**

---

## 🔒 Segurança

1. **Nunca commite o token**: Use sempre GitHub Secrets
2. **Use Service Tokens**: Não use tokens pessoais
3. **Rotacione tokens**: Regere tokens periodicamente
4. **Limite permissões**: Crie tokens com permissões mínimas necessárias

---

## 📚 Referências

- [Railway CLI Documentation](https://docs.railway.app/develop/cli)
- [GitHub Actions Documentation](https://docs.github.com/en/actions)
- [Railway Service Tokens](https://docs.railway.app/develop/cli#service-tokens)

---

## ✅ Checklist de Configuração

- [ ] Token Railway criado (Service Token)
- [ ] Secret `RAILWAY_TOKEN` configurado no GitHub
- [ ] Nome do serviço Railway identificado
- [ ] Workflow `deploy.yml` criado
- [ ] Teste de deploy automático executado
- [ ] Teste de deploy manual executado
- [ ] Logs verificados após primeiro deploy

---

**Configuração completa! ✅**

