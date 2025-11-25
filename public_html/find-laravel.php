<?php
/**
 * Script de Diagnóstico - Localizar Laravel
 * 
 * Acesse: https://menuolika.com.br/find-laravel.php
 * 
 * Este script ajuda a descobrir onde está o Laravel no servidor
 * e qual caminho usar no .htaccess
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico - Localizar Laravel</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 3px solid #007bff; padding-bottom: 10px; }
        h2 { color: #555; margin-top: 30px; }
        .info { background: #e7f3ff; padding: 15px; border-left: 4px solid #007bff; margin: 15px 0; }
        .success { background: #d4edda; padding: 15px; border-left: 4px solid #28a745; margin: 15px 0; }
        .error { background: #f8d7da; padding: 15px; border-left: 4px solid #dc3545; margin: 15px 0; }
        .warning { background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 15px 0; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; font-family: 'Courier New', monospace; }
        ul { line-height: 1.8; }
        li { margin: 10px 0; }
        .path { font-family: 'Courier New', monospace; font-size: 14px; }
        pre { background: #f4f4f4; padding: 15px; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Diagnóstico - Localizar Laravel</h1>

        <?php
        $publicHtml = __DIR__;
        $basePath = dirname($publicHtml);
        
        echo "<div class='info'>";
        echo "<strong>Diretório atual (public_html):</strong><br>";
        echo "<code class='path'>$publicHtml</code><br><br>";
        echo "<strong>Diretório base:</strong><br>";
        echo "<code class='path'>$basePath</code>";
        echo "</div>";

        echo "<h2>1. Caminhos Possíveis Comuns</h2>";
        
        $possiblePaths = [
            'public_html/sistema/public/index.php' => $publicHtml . '/sistema/public/index.php',
            'public_html/menuolika/public/index.php' => $publicHtml . '/menuolika/public/index.php',
            '../sistema/public/index.php' => dirname($publicHtml) . '/sistema/public/index.php',
            '../menuolika/public/index.php' => dirname($publicHtml) . '/menuolika/public/index.php',
            '/home/' . get_current_user() . '/sistema/public/index.php' => '/home/' . get_current_user() . '/sistema/public/index.php',
            '/home/' . get_current_user() . '/menuolika/public/index.php' => '/home/' . get_current_user() . '/menuolika/public/index.php',
        ];

        $found = [];
        echo "<ul>";
        foreach ($possiblePaths as $label => $fullPath) {
            $exists = file_exists($fullPath);
            $relative = str_replace($publicHtml . '/', '', $fullPath);
            if (!str_starts_with($relative, '/')) {
                $relative = './' . $relative;
            }
            
            $status = $exists ? "✅ <strong style='color: green;'>EXISTE</strong>" : "❌ Não existe";
            
            echo "<li>$status: <code class='path'>$label</code><br>";
            echo "Caminho completo: <code class='path'>$fullPath</code>";
            
            if ($exists) {
                $found[] = [
                    'label' => $label,
                    'full' => $fullPath,
                    'relative' => $relative,
                ];
                echo "<br><span style='color: green;'><strong>✓ Use este caminho!</strong></span>";
            }
            echo "</li>";
        }
        echo "</ul>";

        if (!empty($found)) {
            echo "<div class='success'>";
            echo "<h2>✅ Laravel Encontrado!</h2>";
            echo "<p><strong>Use uma das configurações abaixo no arquivo <code>public_html/.htaccess</code>:</strong></p>";
            
            foreach ($found as $item) {
                $relativePath = str_replace($publicHtml . '/', '', $item['full']);
                if (str_starts_with($relativePath, '/')) {
                    $relativePath = str_replace($basePath . '/', '../', $item['full']);
                }
                
                // Ajustar para caminho relativo a partir do public_html
                if (str_starts_with($item['full'], $publicHtml)) {
                    $htaccessPath = str_replace($publicHtml . '/', '', $item['full']);
                    $htaccessPath = dirname($htaccessPath) . '/index.php';
                } else {
                    $htaccessPath = '../' . basename(dirname(dirname($item['full']))) . '/public/index.php';
                }
                
                echo "<h3>Opção para .htaccess:</h3>";
                echo "<pre>&lt;IfModule mod_rewrite.c&gt;
    RewriteEngine On
    RewriteCond %{REQUEST_URI} ^/api/botconversa
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^api/botconversa(.*)$ " . htmlspecialchars($htaccessPath) . " [L,QSA]
&lt;/IfModule&gt;</pre>";
            }
            echo "</div>";
        } else {
            echo "<div class='warning'>";
            echo "<h2>⚠️ Laravel não encontrado nos caminhos comuns</h2>";
            echo "<p>Vamos procurar recursivamente...</p>";
            echo "</div>";

            echo "<h2>2. Busca Recursiva</h2>";
            
            function findArtisan($dir, $maxDepth = 4, $currentDepth = 0, $publicHtml = '') {
                if ($currentDepth >= $maxDepth) return [];
                if (!is_dir($dir)) return [];
                
                $found = [];
                $files = @scandir($dir);
                if (!$files) return $found;
                
                foreach ($files as $file) {
                    if ($file === '.' || $file === '..') continue;
                    
                    $path = rtrim($dir, '/') . '/' . $file;
                    
                    if ($file === 'artisan' && is_file($path)) {
                        $publicIndex = dirname($path) . '/public/index.php';
                        if (file_exists($publicIndex)) {
                            // Calcular caminho relativo a partir do public_html
                            $relative = str_replace($publicHtml . '/', '', $publicIndex);
                            if (str_starts_with($relative, '/')) {
                                $relative = str_replace(dirname($publicHtml) . '/', '../', $publicIndex);
                            }
                            
                            $found[] = [
                                'full' => $publicIndex,
                                'relative' => $relative,
                                'depth' => $currentDepth,
                            ];
                        }
                    } elseif (is_dir($path) && $file !== 'vendor' && $file !== 'node_modules') {
                        $found = array_merge($found, findArtisan($path, $maxDepth, $currentDepth + 1, $publicHtml));
                    }
                }
                
                return $found;
            }

            $laravelInstalls = findArtisan($basePath, 4, 0, $publicHtml);
            
            if (!empty($laravelInstalls)) {
                echo "<div class='success'>";
                echo "<h3>✅ Instalações Laravel Encontradas:</h3>";
                echo "<ul>";
                
                foreach ($laravelInstalls as $install) {
                    // Calcular caminho para .htaccess
                    if (str_starts_with($install['full'], $publicHtml)) {
                        $htaccessPath = dirname(str_replace($publicHtml . '/', '', $install['full'])) . '/index.php';
                    } else {
                        $parts = explode('/', str_replace($basePath . '/', '', dirname($install['full'])));
                        $folder = $parts[0];
                        $htaccessPath = '../' . $folder . '/public/index.php';
                    }
                    
                    echo "<li>";
                    echo "<strong>Caminho completo:</strong><br><code class='path'>{$install['full']}</code><br>";
                    echo "<strong>Profundidade:</strong> {$install['depth']} níveis<br>";
                    echo "<strong>Configuração para .htaccess:</strong><br>";
                    echo "<pre>RewriteRule ^api/botconversa(.*)$ " . htmlspecialchars($htaccessPath) . " [L,QSA]</pre>";
                    echo "</li>";
                }
                echo "</ul>";
                echo "</div>";
            } else {
                echo "<div class='error'>";
                echo "<h3>❌ Nenhuma instalação Laravel encontrada</h3>";
                echo "<p>Verifique se:</p>";
                echo "<ul>";
                echo "<li>O Laravel está instalado no servidor</li>";
                echo "<li>O diretório tem permissões de leitura</li>";
                echo "<li>O arquivo <code>artisan</code> existe</li>";
                echo "</ul>";
                echo "</div>";
            }
        }

        echo "<h2>3. Informações do Servidor</h2>";
        echo "<ul>";
        echo "<li><strong>Usuário atual:</strong> " . get_current_user() . "</li>";
        echo "<li><strong>Diretório home:</strong> " . ($_SERVER['HOME'] ?? 'Não definido') . "</li>";
        echo "<li><strong>Document Root:</strong> " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Não definido') . "</li>";
        echo "<li><strong>Script Filename:</strong> " . ($_SERVER['SCRIPT_FILENAME'] ?? 'Não definido') . "</li>";
        echo "</ul>";

        echo "<h2>4. Próximos Passos</h2>";
        echo "<ol>";
        echo "<li>Copie o caminho relativo mostrado acima</li>";
        echo "<li>Edite o arquivo <code>public_html/.htaccess</code></li>";
        echo "<li>Use o caminho na linha <code>RewriteRule</code></li>";
        echo "<li>Teste a rota: <code>https://menuolika.com.br/api/botconversa/ping</code></li>";
        echo "</ol>";

        echo "<div class='warning'>";
        echo "<p><strong>⚠️ IMPORTANTE:</strong> Após usar este script, DELETE o arquivo <code>find-laravel.php</code> por segurança!</p>";
        echo "</div>";
        ?>
    </div>
</body>
</html>

