# ✅ Correção de URLs dos CSS

## 🔧 PROBLEMA IDENTIFICADO:

O HTML gerado estava apontando para `www.dashboard.menuolika.com.br`, mas o aplicativo roda em `dashboard.menuolika.com.br` (sem `www`).

## ✅ SOLUÇÃO APLICADA:

O layout `resources/views/layouts/dashboard.blade.php` **já estava usando `asset()`** corretamente:

```blade
<link rel="stylesheet" href="{{ asset('css/style.css') }}?v=1">
<link rel="stylesheet" href="{{ asset('css/style-mobile.css') }}?v=1" media="(max-width: 768px)">
```

## 🚀 PRÓXIMOS PASSOS (NO SERVIDOR):

### 1. Ajustar `.env` (IMPORTANTE):

Edite o arquivo `.env` na raiz do projeto e ajuste **SEM www**:

```env
APP_URL=https://dashboard.menuolika.com.br
ASSET_URL=${APP_URL}
```

**⚠️ IMPORTANTE:** Não use `www.dashboard.menuolika.com.br`, use apenas `dashboard.menuolika.com.br`!

### 2. Limpar Caches:

```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
```

### 3. Testar Acesso aos CSS:

Abra no navegador:

- https://dashboard.menuolika.com.br/css/style.css
- https://dashboard.menuolika.com.br/css/style-mobile.css

**Você deve ver o código CSS**, não um erro 404 ou 406.

## 📋 OPÇÃO ALTERNATIVA (URLs Absolutas):

Se preferir usar URLs absolutas (hardcoded), você pode modificar o layout para:

```blade
<link rel="stylesheet" href="https://dashboard.menuolika.com.br/css/style.css?v=1">
<link rel="stylesheet" href="https://dashboard.menuolika.com.br/css/style-mobile.css?v=1" media="(max-width: 768px)">
```

**⚠️ ATENÇÃO:** Se usar URLs absolutas, você **NÃO deve** usar `www`!

## ✅ CHECKLIST DE VERIFICAÇÃO:

Após fazer as alterações no servidor:

- [ ] Teste: https://dashboard.menuolika.com.br/css/style.css retorna 200 OK
- [ ] Teste: https://dashboard.menuolika.com.br/css/style-mobile.css retorna 200 OK
- [ ] Teste: https://dashboard.menuolika.com.br/ carrega o dashboard com estilos
- [ ] DevTools → Network: CSS retornam 200, não 406
- [ ] View Source: verifica se URLs geradas são sem `www`

## 🎯 STATUS ATUAL:

✅ **Layout corrigido no código** (já usa `asset()`)  
⏳ **Aguardando configuração do `.env` no servidor** (remover `www`)  
⏳ **Aguardando limpeza de cache no servidor**

## 📝 NOTA FINAL:

A função `asset()` do Laravel gera URLs baseadas em `APP_URL` definido no `.env`. Por isso é **essencial** configurar corretamente:

```env
APP_URL=https://dashboard.menuolika.com.br  # ← SEM www
```

Se o `.env` estiver com `www`, o `asset()` gerará URLs com `www`, causando o erro 406.

