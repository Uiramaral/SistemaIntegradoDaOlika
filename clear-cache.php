<?php
/**
 * Script temporário para limpar cache no servidor de produção
 * Acesse via: https://seudominio.com/clear-cache.php
 * IMPORTANTE: Delete este arquivo após uso por segurança!
 */

header('Content-Type: application/json; charset=utf-8');

$results = [];

// 1. Limpar OPCache do PHP (CRÍTICO!)
if (function_exists('opcache_reset')) {
    opcache_reset();
    $results[] = '✅ OPCache do PHP limpo';
} else {
    $results[] = '⚠️ OPCache não disponível';
}

// 2. Limpar cache de realpath do PHP
if (function_exists('clearstatcache')) {
    clearstatcache(true);
    $results[] = '✅ Cache de estatísticas de arquivos limpo';
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

// 5. Limpar views compiladas
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
    $results[] = "✅ {$count} views compiladas limpas";
} else {
    $results[] = '❌ Pasta de views não encontrada';
}

// 6. Limpar cache de configuração
$cachePath = __DIR__ . '/bootstrap/cache/config.php';
if (file_exists($cachePath)) {
    unlink($cachePath);
    $results[] = '✅ Cache de configuração limpo';
} else {
    $results[] = '⚠️ Arquivo de cache de configuração não encontrado';
}

// 7. Limpar cache de rotas (v7)
$routesPath = __DIR__ . '/bootstrap/cache/routes-v7.php';
if (file_exists($routesPath)) {
    unlink($routesPath);
    $results[] = '✅ Cache de rotas (v7) limpo';
} else {
    $results[] = '⚠️ Arquivo de cache de rotas (v7) não encontrado';
}

// 8. Tentar limpar cache de rotas antigo também
$oldRoutesPath = __DIR__ . '/bootstrap/cache/routes.php';
if (file_exists($oldRoutesPath)) {
    unlink($oldRoutesPath);
    $results[] = '✅ Cache de rotas (antigo) limpo';
}

// 9. Limpar cache de serviços
$servicesPath = __DIR__ . '/bootstrap/cache/services.php';
if (file_exists($servicesPath)) {
    unlink($servicesPath);
    $results[] = '✅ Cache de serviços limpo';
}

// 10. Limpar cache de pacotes
$packagesPath = __DIR__ . '/bootstrap/cache/packages.php';
if (file_exists($packagesPath)) {
    unlink($packagesPath);
    $results[] = '✅ Cache de pacotes limpo';
}

// 11. Verificar se OPCache está habilitado
$opcacheStatus = 'N/A';
if (function_exists('opcache_get_status')) {
    $status = opcache_get_status(false);
    if ($status !== false) {
        $opcacheStatus = 'Habilitado - Memória usada: ' . round($status['memory_usage']['used_memory'] / 1024 / 1024, 2) . ' MB';
    }
}

echo json_encode([
    'success' => true,
    'message' => '🔥 CACHE LIMPO COM SUCESSO!',
    'results' => $results,
    'opcache_status' => $opcacheStatus,
    'opcache_enabled' => function_exists('opcache_reset'),
    'php_version' => PHP_VERSION,
    'timestamp' => date('Y-m-d H:i:s'),
    'warning' => '⚠️ DELETE clear-cache.php IMEDIATAMENTE POR SEGURANÇA!'
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>
