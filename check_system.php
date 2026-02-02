<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "Checking Database Tables:\n";
$tables = ['clients', 'users', 'sessions', 'webhook_logs', 'orders'];
foreach ($tables as $table) {
    if (Schema::hasTable($table)) {
        echo "[OK] Table '$table' exists.\n";
    } else {
        echo "[FAIL] Table '$table' DOES NOT exist.\n";
    }
}

echo "\nSession Driver: " . config('session.driver') . "\n";
echo "Session Domain: " . config('session.domain') . "\n";
echo "App URL: " . config('app.url') . "\n";
echo "Current Request Host: " . request()->getHost() . "\n";
