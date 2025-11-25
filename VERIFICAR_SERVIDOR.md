# 🔍 Verificação de Problema no Servidor

## Problema
Quando você acessa qualquer rota em `/api/botconversa/*`, aparece uma página de informações do PHP (`phpinfo()`), ao invés do Laravel processar a requisição.

## Causa Provável
Há um **arquivo PHP físico no servidor** no caminho `public/api/botconversa/` que está sendo executado diretamente antes do Laravel processar a requisição.

## ⚠️ AÇÃO NECESSÁRIA NO SERVIDOR

### 1. Verificar se existe diretório `public/api/` no servidor

```bash
# Conecte-se ao servidor via SSH e execute:
ls -la public/api/
```

### 2. Se existir o diretório `public/api/`, verifique se há arquivos PHP dentro:

```bash
# Verificar arquivos PHP no diretório api
find public/api/ -name "*.php" -type f

# Verificar especificamente no botconversa
find public/api/botconversa/ -name "*.php" -type f 2>/dev/null
```

### 3. **REMOVER QUALQUER ARQUIVO PHP** encontrado:

```bash
# CUIDADO: Remova apenas arquivos PHP que não sejam do Laravel
# Se encontrar arquivos como:
# - public/api/botconversa/sync-customer.php
# - public/api/botconversa/test.php
# - public/api/botconversa/index.php
# - public/api/botconversa/ping.php
# - Qualquer arquivo .php nesses diretórios

# Remover arquivos PHP encontrados:
rm -f public/api/botconversa/*.php
rm -f public/api/*.php
```

### 4. Se houver um diretório completo `public/api/botconversa/`, remova-o:

```bash
# CUIDADO: Verifique o conteúdo antes de remover
ls -la public/api/botconversa/

# Se não houver arquivos importantes, remova o diretório:
rm -rf public/api/botconversa/
rm -rf public/api/
```

### 5. Limpar cache do Laravel:

```bash
cd /caminho/do/projeto
php artisan route:clear
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### 6. Verificar permissões:

```bash
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 7. Testar novamente:

Acesse no navegador:
- `https://menuolika.com.br/api/botconversa/ping`

Deveria retornar JSON ao invés de `phpinfo()`.

## 📋 Checklist

- [ ] Verificado se existe `public/api/` no servidor
- [ ] Verificado se há arquivos PHP em `public/api/`
- [ ] Removidos arquivos PHP encontrados
- [ ] Removido diretório `public/api/botconversa/` se existir
- [ ] Limpado cache do Laravel
- [ ] Testado a rota `/api/botconversa/ping`
- [ ] Testado a rota `/api/botconversa/test`
- [ ] Verificado logs do Laravel: `tail -f storage/logs/laravel.log`

## 🚨 IMPORTANTE

**NÃO deve haver NENHUM arquivo PHP no diretório `public/api/`** no servidor, pois isso interfere com as rotas do Laravel.

Todas as rotas da API devem ser processadas pelo Laravel através do `index.php`, não por arquivos PHP individuais.

