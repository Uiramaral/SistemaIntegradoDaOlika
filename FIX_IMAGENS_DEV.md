# 🔧 Fix: Imagens não exibidas em devpedido.menuolika.com.br

## Problema
As fotos dos produtos não estão sendo exibidas no subdomínio de desenvolvimento.

## ✅ Soluções Aplicadas

1. **AppServiceProvider atualizado**: Agora detecta o domínio atual dinamicamente e ajusta as URLs
2. **Configuração de Storage**: URL do storage público configurada dinamicamente baseada no host atual

## 🔍 Verificações Necessárias

### 1. Limpar cache de configuração
Execute no servidor:
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

### 2. Verificar symlink do storage
O symlink do storage deve estar criado na pasta `public`:
```bash
php artisan storage:link
```

Verifique se existe o link simbólico:
```
public/storage -> storage/app/public
```

### 3. Verificar permissões
As pastas de storage devem ter permissões corretas:
```bash
chmod -R 755 storage
chmod -R 755 public/storage
```

### 4. Verificar se as imagens existem
Acesse via FTP/cPanel File Manager e verifique se as imagens estão em:
```
storage/app/public/uploads/products/
```

### 5. Testar URL diretamente
Tente acessar uma imagem diretamente:
```
https://devpedido.menuolika.com.br/storage/uploads/products/[nome-da-imagem].jpg
```

Se retornar 404, o problema é o symlink ou permissões.
Se retornar a imagem, o problema era apenas a geração de URLs (já corrigido).

## 📝 Nota Importante

As alterações feitas garantem que:
- O helper `asset()` usa o domínio atual (devpedido ou pedido)
- As URLs do storage são geradas com o domínio correto
- Funciona tanto em desenvolvimento quanto em produção

## ⚠️ Se ainda não funcionar

1. Verifique os logs: `storage/logs/laravel.log`
2. Verifique o console do navegador (F12) para ver erros 404 nas imagens
3. Verifique se o symlink está criado corretamente
4. Verifique se as imagens existem no servidor

