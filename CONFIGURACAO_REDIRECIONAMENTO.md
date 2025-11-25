# 🔄 Configuração de Redirecionamento da API

## 📋 Estrutura de Diretórios

```
/home/usuario/
├── public_html/
│   ├── index.php (cPanel padrão)
│   ├── .htaccess (este arquivo - redireciona API para Laravel)
│   └── ... (outros arquivos do domínio principal)
└── sistema/ (ou menuolika/)
    └── public/
        ├── index.php (Laravel)
        ├── .htaccess (Laravel)
        └── ... (arquivos públicos do Laravel)
```

## ✅ Solução Implementada

Foi criado um arquivo `.htaccess` em `public_html/` que:
- Mantém o domínio principal servindo o `index.php` padrão do cPanel
- Redireciona apenas as requisições para `/api/botconversa/*` para o Laravel
- As rotas da API passam pelo Laravel em `sistema/public/`

## 📁 Arquivo Criado

### `public_html/.htaccess`

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    
    # Redirecionar apenas as chamadas da API BotConversa para o Laravel
    # Isso mantém o domínio principal servindo o index padrão,
    # mas as rotas da API passam pelo Laravel em sistema/public/
    RewriteCond %{REQUEST_URI} ^/api/botconversa
    RewriteRule ^(.*)$ sistema/public/$1 [L]
</IfModule>
```

## 📤 Como Fazer Upload

1. **Copie o arquivo `public_html/.htaccess` para o servidor**

2. **Coloque-o em:**
   ```
   /home/usuario/public_html/.htaccess
   ```

3. **Verifique as permissões:**
   ```bash
   chmod 644 /home/usuario/public_html/.htaccess
   ```

## 🧪 Testes

Após fazer upload do arquivo, teste:

### 1. Rota de Teste Simples (GET)
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

### 2. Rota de Teste Completa (GET)
```
https://menuolika.com.br/api/botconversa/test
```

### 3. Rota de Sincronização (POST)
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

## ⚠️ Importante

### Caminho do Laravel

O arquivo `.htaccess` assume que o Laravel está em:
```
/home/usuario/public_html/sistema/public/
```

Se o Laravel estiver em outro caminho, ajuste a linha:
```apache
RewriteRule ^(.*)$ CAMINHO_AQUI/public/$1 [L]
```

**Exemplos:**
- Se estiver em `/home/usuario/menuolika/`: `menuolika/public/$1`
- Se estiver em `/home/usuario/app/`: `app/public/$1`
- Se estiver na raiz do usuário: `../sistema/public/$1` (usando caminho relativo)

### Verificação do Caminho

No servidor, verifique onde está o Laravel:
```bash
# Verificar estrutura
ls -la /home/usuario/

# Verificar se o Laravel está acessível
ls -la /home/usuario/sistema/public/index.php
# ou
ls -la /home/usuario/menuolika/public/index.php
```

## 🔍 Troubleshooting

### Se não funcionar, verifique:

1. **Mod_rewrite está habilitado?**
   ```bash
   apache2ctl -M | grep rewrite
   # ou
   httpd -M | grep rewrite
   ```

2. **Arquivo .htaccess está no lugar certo?**
   ```bash
   ls -la /home/usuario/public_html/.htaccess
   ```

3. **Permissões corretas?**
   ```bash
   chmod 644 /home/usuario/public_html/.htaccess
   ```

4. **Caminho do Laravel está correto?**
   Verifique se o caminho `sistema/public/` existe e é relativo ao `public_html/`

5. **Verificar logs do Apache:**
   ```bash
   tail -f /var/log/apache2/error.log
   # ou
   tail -f /var/log/httpd/error_log
   ```

## 📋 Checklist

- [ ] Arquivo `public_html/.htaccess` criado
- [ ] Arquivo enviado para o servidor em `/home/usuario/public_html/.htaccess`
- [ ] Permissões corretas (644)
- [ ] Caminho do Laravel verificado e ajustado se necessário
- [ ] Mod_rewrite habilitado no Apache
- [ ] Teste da rota `/api/botconversa/ping` funcionando
- [ ] Teste da rota POST `/api/botconversa/sync-customer` funcionando

## ✅ Vantagens desta Solução

1. ✅ Não precisa mudar o Document Root
2. ✅ Mantém o domínio principal funcionando normalmente
3. ✅ Apenas as rotas da API passam pelo Laravel
4. ✅ Facilita migração gradual
5. ✅ Não interfere com outros arquivos do `public_html/`

