# 🔧 Correção: Instalação do Railway CLI no Container

## 📋 Problema Identificado

O erro `railway: command not found` ou `/bin/sh: railway: not found` ocorre porque o **Railway CLI não está instalado** dentro do container que executa a aplicação.

---

## ✅ Correções Implementadas

### 1. Dockerfile Atualizado

**Arquivo**: `olika-whatsapp-integration/Dockerfile`

Adicionada a instalação do Railway CLI:

```dockerfile
# Instala Railway CLI globalmente para uso de scripts
RUN npm install -g @railway/cli
```

**Resultado**: O Railway CLI será instalado automaticamente ao buildar a imagem Docker.

---

### 2. Script Melhorado

**Arquivo**: `olika-whatsapp-integration/scripts/getRailwayToken.js`

O script agora:

1. ✅ **Verifica** se o Railway CLI está instalado
2. ✅ **Instala automaticamente** se não estiver
3. ✅ **Trata erros** de forma mais robusta
4. ✅ **Fornece mensagens claras** sobre o que fazer

---

### 3. Package.json Atualizado

**Arquivo**: `olika-whatsapp-integration/package.json`

Adicionados dois scripts:

```json
{
  "scripts": {
    "get-token": "node scripts/getRailwayToken.js",
    "get-token:install": "npm install -g @railway/cli && node scripts/getRailwayToken.js"
  }
}
```

- `npm run get-token`: Tenta usar o CLI (instala automaticamente se necessário)
- `npm run get-token:install`: Força instalação antes de executar

---

## 🚀 Como Usar

### Opção 1: Com Dockerfile (Recomendado)

1. **Build e deploy**:
   ```bash
   # O Dockerfile já inclui a instalação do Railway CLI
   # Basta fazer deploy normalmente
   ```

2. **Dentro do container, execute**:
   ```bash
   npm run get-token
   ```

### Opção 2: Sem Dockerfile (Via Buildpacks)

Se o Railway está usando buildpacks automáticos:

1. **Execute com instalação forçada**:
   ```bash
   npm run get-token:install
   ```

2. **Ou no package.json, adicione prestart**:
   ```json
   {
     "scripts": {
       "prestart": "npm install -g @railway/cli || true",
       "get-token": "node scripts/getRailwayToken.js"
     }
   }
   ```

---

## 📝 Fluxo do Script Atualizado

```
1. Verifica se Railway CLI está instalado
   ↓
2. Se NÃO → Instala automaticamente (npm install -g @railway/cli)
   ↓
3. Executa: railway whoami --json
   ↓
4. Extrai token rwsk_
   ↓
5. Salva em .railway_token
```

---

## ⚠️ Importante

### Autenticação Necessária

Antes de usar o script, você precisa estar autenticado:

```bash
railway login
```

Isso pode ser feito:
- **Localmente** (antes do deploy)
- **Dentro do container** (via SSH ou logs interativos)

### Token CLI vs Token API

- **Token CLI (`rwsk_`)**: Obtido por este script, usado pelo Railway CLI
- **Token API (UUID)**: Gerado no Railway Dashboard, usado no `RailwayService.php`

**⚠️ Atenção**: O token CLI **NÃO** funciona como `RAILWAY_API_KEY` no Laravel.

---

## 🔍 Troubleshooting

### Erro: "railway: command not found"

**Solução**: 
1. Verifique se o Dockerfile foi atualizado
2. Faça rebuild da imagem: `docker build -t sua-imagem .`
3. Ou execute: `npm run get-token:install`

### Erro: "not authenticated"

**Solução**:
```bash
railway login
```

### Erro: "Cannot find module @railway/cli"

**Solução**:
```bash
npm install -g @railway/cli
```

---

## 📁 Arquivos Modificados

1. ✅ `olika-whatsapp-integration/Dockerfile` - Instalação do Railway CLI
2. ✅ `olika-whatsapp-integration/scripts/getRailwayToken.js` - Instalação automática
3. ✅ `olika-whatsapp-integration/package.json` - Scripts atualizados

---

## 🎯 Resultado Esperado

Após as correções, ao executar `npm run get-token`:

```
🔍 Railway CLI não está instalado. Tentando instalar...
📦 Railway CLI não encontrado. Instalando...
✅ Railway CLI instalado com sucesso!
🔍 Executando: railway whoami --json
✅ Railway Token (rwsk_) encontrado: rwsk_abc123...
✅ Token salvo em: /app/.railway_token
```

---

**Correções aplicadas! ✅**

