# 🔧 Troubleshooting: Erro 404 em devpedido.menuolika.com.br

## Problema
O subdomínio `devpedido.menuolika.com.br` está retornando erro 404 do servidor (HostGator), não do Laravel.

## ✅ Verificações Necessárias no HostGator

### 1. **Configuração do Subdomínio no cPanel**

1. Acesse o **cPanel** do HostGator
2. Vá em **Subdomínios** (Subdomains)
3. Verifique se `devpedido` está criado e apontando para:
   ```
   /public_html/desenvolvimento/public
   ```
   ou
   ```
   /home/usuario/public_html/desenvolvimento/public
   ```

### 2. **Verificar DocumentRoot**

O DocumentRoot do subdomínio **DEVE** apontar para a pasta `public` do Laravel:

```
DocumentRoot: /public_html/desenvolvimento/public
```

**NÃO** deve apontar para:
- ❌ `/public_html/desenvolvimento`
- ❌ `/public_html/desenvolvimento/app`
- ❌ `/public_html/desenvolvimento/resources`

### 3. **Verificar Arquivo .htaccess**

Certifique-se de que existe um arquivo `.htaccess` na pasta `public` com o conteúdo:

```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%1]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=302]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

### 4. **Verificar Permissões**

As permissões devem ser:
- Pastas: `755`
- Arquivos: `644`
- `public/index.php`: `644`

### 5. **Verificar Arquivo .env**

No arquivo `.env` do ambiente de desenvolvimento, certifique-se de:

```env
APP_ENV=local
APP_DEBUG=true
APP_URL=https://devpedido.menuolika.com.br

PEDIDO_DOMAIN=devpedido.menuolika.com.br
DASHBOARD_DOMAIN=devdashboard.menuolika.com.br
```

### 6. **Verificar DNS**

No cPanel, vá em **Zona DNS** e verifique se existe um registro A ou CNAME:

```
devpedido    A     IP_DO_SERVIDOR
```

ou

```
devpedido    CNAME    menuolika.com.br
```

## 🔍 Como Diagnosticar

### Teste 1: Verificar se o subdomínio está resolvendo
```bash
ping devpedido.menuolika.com.br
```

### Teste 2: Verificar se o arquivo index.php existe
Acesse via FTP/cPanel File Manager:
```
/public_html/desenvolvimento/public/index.php
```

### Teste 3: Verificar logs do Apache
No cPanel, vá em **Logs** → **Erros** e verifique mensagens relacionadas.

### Teste 4: Testar diretamente o index.php
Tente acessar:
```
http://devpedido.menuolika.com.br/index.php
```

Se funcionar, o problema é o `.htaccess`. Se não funcionar, o problema é o DocumentRoot.

## 🛠️ Solução Rápida (cPanel)

1. **Criar/Editar Subdomínio:**
   - cPanel → Subdomínios
   - Subdomínio: `devpedido`
   - Domínio: `menuolika.com.br`
   - Document Root: `/public_html/desenvolvimento/public`
   - Clique em **Criar**

2. **Aguardar propagação DNS** (pode levar alguns minutos)

3. **Testar novamente**

## 📝 Nota Importante

O código Laravel já está configurado corretamente para aceitar `devpedido.menuolika.com.br`. O problema é **100% de configuração do servidor**, não do código.

## ⚠️ Se o problema persistir

1. Verifique se o módulo `mod_rewrite` está habilitado no Apache
2. Entre em contato com o suporte do HostGator
3. Solicite que verifiquem a configuração do VirtualHost para o subdomínio

