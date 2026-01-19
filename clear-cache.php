<?php
/**
 * Script temporário para limpar cache no servidor de produção
 * Acesse via: https://seudominio.com/clear-cache.php
 * IMPORTANTE: Delete este arquivo após uso por segurança!
 */

// Limpar views compiladas
$viewsPath = __DIR__ . '/storage/framework/views';
if (is_dir($viewsPath)) {
    $files = glob($viewsPath . '/*.php');
    foreach ($files as $file) {
        if (is_file($file)) {
            unlink($file);
        }
    }
    echo "✅ Views compiladas limpas<br>";
} else {
    echo "❌ Pasta de views não encontrada<br>";
}

// Limpar cache de configuração
$cachePath = __DIR__ . '/bootstrap/cache/config.php';
if (file_exists($cachePath)) {
    unlink($cachePath);
    echo "✅ Cache de configuração limpo<br>";
}

// Limpar cache de rotas
$routesPath = __DIR__ . '/bootstrap/cache/routes-v7.php';
if (file_exists($routesPath)) {
    unlink($routesPath);
    echo "✅ Cache de rotas limpo<br>";
}

echo "<br><strong>Cache limpo com sucesso!</strong><br>";
echo "<br><span style='color:red;'>⚠️ IMPORTANTE: DELETE ESTE ARQUIVO AGORA POR SEGURANÇA!</span>";
?>
