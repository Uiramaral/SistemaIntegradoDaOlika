<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    if (Schema::hasTable('sessions')) {
        echo "TABLE_FOUND";
    } else {
        echo "TABLE_NOT_FOUND";
    }
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
