<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    // Simular usuário autenticado para passar pelo ClientScope, se houver
    $user = \App\Models\User::first();
    if ($user) {
        auth()->login($user);
        echo "Usuario autenticado: " . $user->name . " (Client ID: " . $user->client_id . ")\n";
    } else {
        echo "Nenhum usuario encontrado para autenticar.\n";
    }

    $p = \App\Models\Product::withoutGlobalScopes()->with('variants')->find(73);

    if (!$p) {
        echo "Produto 73 nao encontrado mesmo sem escopos.\n";
    } else {
        echo "Produto: " . $p->name . " (Client ID: " . $p->client_id . ")\n";

        $vars = \App\Models\ProductVariant::where('product_id', 73)->get();
        echo "Variantes (Query direta): " . $vars->count() . "\n";
        foreach ($vars as $v) {
            echo " - [Direto] " . $v->name . "\n";
        }

        echo "Variantes (Relacao): " . $p->variants->count() . "\n";
        foreach ($p->variants as $v) {
            echo " - [Relacao] " . $v->name . "\n";
        }
    }

} catch (\Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
