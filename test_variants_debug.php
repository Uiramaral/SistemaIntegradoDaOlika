<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $p = \App\Models\Product::with('variants')->find(73);
    if (!$p) {
        echo "Produto 73 nao encontrado.\n";
        exit;
    }
    echo "Produto: " . $p->name . "\n";
    echo "Variantes Count: " . $p->variants->count() . "\n";
    foreach ($p->variants as $v) {
        echo " - " . $v->name . " (ID: " . $v->id . ")\n";
    }
} catch (\Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
