# 🔍 Diagnóstico: Assets (JS/CSS/Imagens) não funcionando em devpedido

## Problema
Arquivos JavaScript, CSS e imagens não estão sendo carregados corretamente no ambiente de desenvolvimento.

## ✅ Soluções Implementadas

1. **AppServiceProvider atualizado**: Detecta domínio atual e ajusta URLs dinamicamente
2. **Rota de fallback para storage**: `/storage/{path}` serve arquivos mesmo sem symlink
3. **Rota de teste**: `/test-assets` para diagnosticar URLs geradas

## 🔍 Passos para Diagnosticar

### 1. Testar URLs de assets

Acesse no navegador:
```
https://devpedido.menuolika.com.br/test-assets
```

Isso mostrará todas as URLs que estão sendo geradas. Verifique se:
- `test_asset_js` aponta para `devpedido.menuolika.com.br`
- `test_asset_css` aponta para `devpedido.menuolika.com.br`
- `test_asset_image` aponta para `devpedido.menuolika.com.br`

### 2. Limpar cache

Acesse:
```
https://devpedido.menuolika.com.br/clear-cache-now
```

Isso limpa todo o cache de configuração.

### 3. Verificar console do navegador

Abra o console (F12) e verifique:
- Erros 404 em arquivos JS/CSS
- URLs dos arquivos que estão falhando
- Se as URLs estão apontando para o domínio correto

### 4. Testar arquivo JS diretamente

Tente acessar diretamente:
```
https://devpedido.menuolika.com.br/js/olika-cart.js
```

Se retornar 404, o arquivo não existe ou o caminho está errado.

## 🛠️ Soluções Possíveis

### Se as URLs estão incorretas:

1. Limpar cache de configuração
2. Verificar se o `AppServiceProvider` está sendo executado
3. Verificar se há algum middleware interferindo

### Se os arquivos não existem:

1. Verificar se os arquivos estão em `public/js/` e `public/css/`
2. Verificar permissões dos arquivos (devem ser 644)
3. Verificar se o DocumentRoot está correto

### Se o symlink está quebrado:

1. Acessar `/create-storage-link` para recriar
2. Verificar se o diretório `public/storage` existe e é um symlink válido

## 📝 Nota

O `AppServiceProvider` já está configurado para detectar o domínio atual e ajustar as URLs automaticamente. Se ainda não funcionar após limpar o cache, pode ser um problema de:
- Cache do navegador (Ctrl+F5 para forçar reload)
- Ordem de execução do Service Provider
- Algum middleware interferindo

