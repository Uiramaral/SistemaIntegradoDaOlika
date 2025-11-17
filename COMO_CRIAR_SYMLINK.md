# 🔗 Como Criar o Symlink do Storage (Sem SSH)

## ✅ Solução Implementada

Criei uma rota que permite criar o symlink do storage diretamente pelo navegador, sem precisar de acesso SSH.

## 📋 Passo a Passo

### 1. Acesse a URL de criação do symlink

Abra no navegador (em qualquer subdomínio):
```
https://devpedido.menuolika.com.br/create-storage-link
```

ou

```
https://devdashboard.menuolika.com.br/create-storage-link
```

### 2. Verifique a resposta

A rota retornará um JSON com o status:

**Se o symlink foi criado com sucesso:**
```json
{
  "status": "success",
  "message": "Symlink criado com sucesso!",
  "link": "/caminho/para/public/storage",
  "target": "/caminho/para/storage/app/public"
}
```

**Se o symlink já existe:**
```json
{
  "status": "info",
  "message": "Symlink já existe",
  "link": "/caminho/para/public/storage",
  "target": "/caminho/para/storage/app/public",
  "exists": true,
  "is_link": true
}
```

**Se houver erro:**
```json
{
  "status": "error",
  "message": "Erro: [descrição do erro]"
}
```

### 3. Teste as imagens

Após criar o symlink, acesse:
```
https://devpedido.menuolika.com.br/
```

As imagens devem aparecer corretamente.

## 🔍 Verificação Manual

Se quiser verificar manualmente via cPanel File Manager:

1. Acesse o **File Manager** no cPanel
2. Navegue até: `public_html/desenvolvimento/public/`
3. Verifique se existe um link chamado `storage`
4. Se não existir, a rota `/create-storage-link` deve criar

## ⚠️ Se a rota não funcionar

Se a rota retornar erro de permissões, você pode:

1. **Criar manualmente via cPanel File Manager:**
   - Acesse `public_html/desenvolvimento/public/`
   - Crie um novo link simbólico
   - Nome: `storage`
   - Destino: `../storage/app/public`

2. **Ou solicitar ao suporte do HostGator** para criar o symlink

## 🔄 Fallback Automático

Mesmo sem o symlink, as imagens devem funcionar porque criei uma rota fallback (`/storage/{path}`) que serve os arquivos diretamente do storage. Mas o symlink é a solução ideal para performance.

