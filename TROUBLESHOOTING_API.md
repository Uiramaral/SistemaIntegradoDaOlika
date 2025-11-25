# 🔧 Troubleshooting - API BotConversa Não Funciona

## 📋 Checklist de Verificação

### 1️⃣ Verificar Estrutura de Diretórios no Servidor

**Execute no servidor via SSH:**

```bash
# Verificar onde está o Laravel
find /home/usuario -name "artisan" -type f 2>/dev/null

# Verificar estrutura de diretórios
ls -la /home/usuario/
ls -la /home/usuario/public_html/

# Verificar se existe sistema/public/ ou menuolika/public/
ls -la /home/usuario/sistema/public/index.php
ls -la /home/usuario/menuolika/public/index.php
```

### 2️⃣ Verificar Caminho Relativo

**Do public_html para o Laravel:**

```bash
# Estar no diretório public_html
cd /home/usuario/public_html

# Verificar caminho relativo
ls -la ../sistema/public/index.php
# ou
ls -la sistema/public/index.php
```

### 3️⃣ Verificar .htaccess

**Verificar se o arquivo existe:**

```bash
cat /home/usuario/public_html/.htaccess
```

**Verificar permissões:**

```bash
chmod 644 /home/usuario/public_html/.htaccess
chown usuario:usuario /home/usuario/public_html/.htaccess
```

### 4️⃣ Verificar Mod_rewrite

```bash
# Verificar se mod_rewrite está habilitado
apache2ctl -M | grep rewrite
# ou
httpd -M | grep rewrite

# Se não estiver habilitado, habilitar:
# sudo a2enmod rewrite
# sudo systemctl restart apache2
```

---

## 🔄 Configurações Alternativas do .htaccess

### Configuração 1: Laravel em `public_html/sistema/public/`

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_URI} ^/api/botconversa
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^api/botconversa(.*)$ sistema/public/index.php [L,QSA]
</IfModule>
```

### Configuração 2: Laravel em `/home/usuario/sistema/public/` (fora do public_html)

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_URI} ^/api/botconversa
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^api/botconversa(.*)$ ../sistema/public/index.php [L,QSA]
</IfModule>
```

### Configuração 3: Laravel em `public_html/menuolika/public/`

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_URI} ^/api/botconversa
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^api/botconversa(.*)$ menuolika/public/index.php [L,QSA]
</IfModule>
```

### Configuração 4: Caminho Absoluto (mais confiável)

**IMPORTANTE:** Substitua `/home/usuario/sistema/` pelo caminho real do seu Laravel.

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /
    
    RewriteCond %{REQUEST_URI} ^/api/botconversa
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    
    # Usar caminho absoluto a partir da raiz do usuário
    RewriteRule ^api/botconversa(.*)$ /home/usuario/sistema/public/index.php [L,QSA]
</IfModule>
```

**NOTA:** Caminhos absolutos podem não funcionar em alguns hosts compartilhados. Prefira caminhos relativos.

---

## 🧪 Testes de Diagnóstico

### Teste 1: Verificar se o .htaccess está sendo processado

**Crie um arquivo de teste em `public_html/test-rewrite.php`:**

```php
<?php
echo "Rewriting works!";
```

**Acesse:** `https://menuolika.com.br/test-rewrite.php`

**Se funcionar, o Apache está processando PHP. Se não, verifique as configurações.**

### Teste 2: Verificar se consegue acessar o Laravel diretamente

**Tente acessar diretamente o index.php do Laravel:**

```
https://menuolika.com.br/sistema/public/index.php
```

**Ou, se estiver fora do public_html:**

Crie um arquivo de teste que tenta incluir o Laravel:

```php
<?php
// public_html/test-laravel.php
$laravelPath = __DIR__ . '/../sistema/public/index.php';
if (file_exists($laravelPath)) {
    echo "Laravel encontrado em: " . $laravelPath;
    require $laravelPath;
} else {
    echo "Laravel NÃO encontrado. Procurando...\n";
    echo "Tentando: " . $laravelPath . "\n";
    
    // Tentar outros caminhos
    $alternatives = [
        __DIR__ . '/sistema/public/index.php',
        __DIR__ . '/menuolika/public/index.php',
        '/home/usuario/sistema/public/index.php',
    ];
    
    foreach ($alternatives as $path) {
        if (file_exists($path)) {
            echo "Encontrado em: " . $path . "\n";
            break;
        }
    }
}
```

### Teste 3: Verificar Logs do Apache

```bash
# Ver logs de erro do Apache
tail -f /var/log/apache2/error.log

# Ou em alguns hosts:
tail -f /var/log/httpd/error_log
tail -f ~/logs/error_log
```

**Acesse a URL e veja os erros nos logs.**

---

## 🎯 Solução Definitiva: Descobrir o Caminho Correto

### Passo 1: Criar Script de Diagnóstico

**Crie `public_html/find-laravel.php`:**

```php
<?php
echo "<h1>Diagnóstico - Localizar Laravel</h1>";

$basePath = dirname(__DIR__);
echo "<p>Base Path: $basePath</p>";

echo "<h2>Procurando Laravel...</h2>";

$possiblePaths = [
    $basePath . '/sistema/public/index.php',
    $basePath . '/menuolika/public/index.php',
    $basePath . '/app/public/index.php',
    __DIR__ . '/sistema/public/index.php',
    __DIR__ . '/menuolika/public/index.php',
];

echo "<ul>";
foreach ($possiblePaths as $path) {
    $exists = file_exists($path);
    $status = $exists ? "✅ EXISTE" : "❌ Não existe";
    $relative = str_replace(dirname(__DIR__), '..', $path);
    
    echo "<li>$status: <code>$path</code>";
    echo "<br>Relativo: <code>$relative</code></li>";
    
    if ($exists) {
        echo "<br><strong>Use este caminho no .htaccess!</strong>";
    }
}
echo "</ul>";

// Procurar recursivamente
echo "<h2>Procurando recursivamente...</h2>";
function findArtisan($dir, $maxDepth = 3, $currentDepth = 0) {
    if ($currentDepth >= $maxDepth) return [];
    
    $found = [];
    if (!is_dir($dir)) return $found;
    
    $files = @scandir($dir);
    if (!$files) return $found;
    
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        
        $path = $dir . '/' . $file;
        
        if ($file === 'artisan' && is_file($path)) {
            $publicIndex = dirname($path) . '/public/index.php';
            if (file_exists($publicIndex)) {
                $found[] = $publicIndex;
            }
        } elseif (is_dir($path)) {
            $found = array_merge($found, findArtisan($path, $maxDepth, $currentDepth + 1));
        }
    }
    
    return $found;
}

$laravelInstalls = findArtisan($basePath);
if (!empty($laravelInstalls)) {
    echo "<h3>Instalações Laravel encontradas:</h3><ul>";
    foreach ($laravelInstalls as $install) {
        $relative = str_replace(dirname(__DIR__) . '/', '../', $install);
        echo "<li><code>$install</code><br>Relativo: <code>$relative</code></li>";
    }
    echo "</ul>";
} else {
    echo "<p>Nenhuma instalação Laravel encontrada.</p>";
}
?>
```

### Passo 2: Acessar o Script

```
https://menuolika.com.br/find-laravel.php
```

**Isso mostrará onde está o Laravel e o caminho relativo correto.**

### Passo 3: Ajustar o .htaccess

**Use o caminho relativo mostrado pelo script.**

---

## ✅ Próximos Passos

1. Execute o script `find-laravel.php`
2. Copie o caminho relativo mostrado
3. Use esse caminho no `.htaccess`
4. Teste a rota `/api/botconversa/ping`
5. Se ainda não funcionar, verifique os logs do Apache

