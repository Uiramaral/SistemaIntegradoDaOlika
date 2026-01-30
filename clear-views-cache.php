<?php
// Script para limpar cache de views Blade no servidor de produção

$viewsCachePath = __DIR__ . '/storage/framework/views';

if (!is_dir($viewsCachePath)) {
    die("Diretório de cache de views não encontrado: {$viewsCachePath}\n");
}

$files = glob($viewsCachePath . '/*');
$count = 0;

foreach ($files as $file) {
    if (is_file($file)) {
        unlink($file);
        $count++;
    }
}

echo "Cache de views limpo com sucesso! {$count} arquivo(s) removido(s).\n";
echo "Agora você pode acessar /dashboard/orders novamente.\n";
