<?php
/**
 * FORÇA RELOAD COMPLETO DO SISTEMA
 * Acesse via: https://seudominio.com/force-reload.php
 * DELETE APÓS USO!
 */

header('Content-Type: application/json; charset=utf-8');

$results = [];

// 1. Limpar OPCache
if (function_exists('opcache_reset')) {
    opcache_reset();
    $results[] = '✅ OPCache resetado';
} else {
    $results[] = '⚠️ OPCache não disponível';
}

// 2. Limpar cache de stat
if (function_exists('clearstatcache')) {
    clearstatcache(true);
    $results[] = '✅ Stat cache limpo';
}

// 3. Tocar no OrdersController para forçar recompilação
$controllerPath = __DIR__ . '/app/Http/Controllers/Dashboard/OrdersController.php';
if (file_exists($controllerPath)) {
    touch($controllerPath);
    $results[] = '✅ OrdersController tocado (forçará recompilação)';
} else {
    $results[] = '❌ OrdersController não encontrado';
}

// 4. Tocar no web.php
$webPath = __DIR__ . '/routes/web.php';
if (file_exists($webPath)) {
    touch($webPath);
    $results[] = '✅ web.php tocado';
} else {
    $results[] = '❌ web.php não encontrado';
}

// 5. Deletar TODOS os caches
$cachePaths = [
    '/bootstrap/cache/routes-v7.php',
    '/bootstrap/cache/routes.php',
    '/bootstrap/cache/config.php',
    '/bootstrap/cache/services.php',
    '/bootstrap/cache/packages.php',
];

foreach ($cachePaths as $path) {
    $fullPath = __DIR__ . $path;
    if (file_exists($fullPath)) {
        unlink($fullPath);
        $results[] = "✅ Deletado: $path";
    }
}

// 6. Limpar views
$viewsPath = __DIR__ . '/storage/framework/views';
if (is_dir($viewsPath)) {
    $files = glob($viewsPath . '/*.php');
    $count = 0;
    foreach ($files as $file) {
        if (is_file($file)) {
            unlink($file);
            $count++;
        }
    }
    $results[] = "✅ {$count} views compiladas deletadas";
}

// 7. Verificar se OPCache está habilitado
$opcacheStatus = 'N/A';
if (function_exists('opcache_get_status')) {
    $status = opcache_get_status(false);
    if ($status !== false) {
        $opcacheStatus = 'Habilitado - Memória usada: ' . round($status['memory_usage']['used_memory'] / 1024 / 1024, 2) . ' MB';
    }
}

echo json_encode([
    'success' => true,
    'message' => '🔥 RELOAD FORÇADO COMPLETO!',
    'results' => $results,
    'opcache_status' => $opcacheStatus,
    'php_version' => PHP_VERSION,
    'timestamp' => date('Y-m-d H:i:s'),
    'warning' => '⚠️ DELETE force-reload.php E clear-cache.php IMEDIATAMENTE!'
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>
