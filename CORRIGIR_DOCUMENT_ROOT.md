# 🔧 Corrigir Document Root do Domínio

## ✅ Problema Identificado

O domínio `menuolika.com.br` está apontando para:
```
/home/usuario/public_html/index.php
```

Mas o Laravel deveria rodar em:
```
/home/usuario/menuolika/public/index.php
```

Isso explica por que o servidor mostra o `phpinfo()`: o `index.php` da raiz do hosting (padrão do cPanel) está sendo executado, e não o do Laravel.

---

## 🚀 Como Corrigir

### 1️⃣ Ajustar o Document Root no cPanel

1. **Acesse o cPanel**
2. **Vá em:** `Domínios` → `Gerenciar` → `Document Root`
3. **Altere o Document Root de:**
   ```
   /home/usuario/public_html
   ```
   
   **Para:**
   ```
   /home/usuario/menuolika/public
   ```
   (ou o caminho correto onde está seu projeto Laravel)

4. **Salve as alterações**

### 2️⃣ Verificar o .htaccess

O arquivo `public/.htaccess` já está configurado corretamente com as regras do Laravel.

Confirme se o arquivo existe em:
```
/home/usuario/menuolika/public/.htaccess
```

Se não existir, copie o conteúdo do arquivo `.htaccess` do repositório para o servidor.

### 3️⃣ Verificar Permissões

Certifique-se de que o diretório `public` tem as permissões corretas:

```bash
chmod 755 /home/usuario/menuolika/public
chmod 644 /home/usuario/menuolika/public/.htaccess
chmod 644 /home/usuario/menuolika/public/index.php
```

### 4️⃣ Verificar se é o index.php do Laravel

Execute via SSH:

```bash
cat /home/usuario/menuolika/public/index.php | head -n 10
```

Deveria mostrar algo como:
```php
<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__.'/../vendor/autoload.php';
```

Se mostrar `phpinfo()` ou outro conteúdo, o arquivo está errado.

### 5️⃣ Limpar Cache do Laravel

Após corrigir o Document Root, limpe o cache:

```bash
cd /home/usuario/menuolika
php artisan route:clear
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### 6️⃣ Testar a Rota

Após corrigir o Document Root, teste:

**Via navegador:**
```
https://menuolika.com.br/api/botconversa/ping
```

**Resposta esperada:**
```json
{
  "status": "ok",
  "message": "API BotConversa está respondendo",
  "timestamp": "2025-01-28 10:42:00"
}
```

**Via POST (como o BotConversa fará):**
```bash
curl -X POST https://menuolika.com.br/api/botconversa/sync-customer \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "phone": "11999999999",
    "name": "João Silva",
    "newsletter": true
  }'
```

---

## 📋 Checklist

- [ ] Document Root ajustado para `/home/usuario/menuolika/public`
- [ ] Arquivo `.htaccess` existe em `public/.htaccess`
- [ ] Arquivo `index.php` é o do Laravel (não mostra phpinfo)
- [ ] Permissões corretas nos arquivos
- [ ] Cache do Laravel limpo
- [ ] Rota `/api/botconversa/ping` retorna JSON
- [ ] Rota POST `/api/botconversa/sync-customer` funciona

---

## ⚠️ Importante

**NÃO deve haver:**
- Diretório `public/api/` no servidor
- Arquivos PHP em `public/api/botconversa/`
- `index.php` na raiz do hosting executando `phpinfo()`

**DEVE haver:**
- `public/index.php` do Laravel
- `public/.htaccess` com as regras do Laravel
- Document Root apontando para `public/`

---

## 🔍 Verificação Adicional

Se após corrigir o Document Root ainda não funcionar:

1. **Verifique se o Laravel está no caminho correto:**
   ```bash
   ls -la /home/usuario/menuolika/public/index.php
   ls -la /home/usuario/menuolika/public/.htaccess
   ```

2. **Verifique os logs do Laravel:**
   ```bash
   tail -f /home/usuario/menuolika/storage/logs/laravel.log
   ```

3. **Verifique os logs do servidor web (Apache/Nginx):**
   ```bash
   tail -f /var/log/apache2/error.log
   # ou
   tail -f /var/log/nginx/error.log
   ```

---

## ✅ Após a Correção

Uma vez que o Document Root esteja correto e apontando para `public/`, todas as rotas do Laravel devem funcionar, incluindo:

- `GET /api/botconversa/ping` - Teste simples
- `GET /api/botconversa/test` - Teste completo
- `POST /api/botconversa/sync-customer` - Sincronizar cliente individual
- `POST /api/botconversa/sync-customers` - Sincronizar múltiplos clientes

