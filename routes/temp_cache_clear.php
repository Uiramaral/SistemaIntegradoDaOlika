<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('/clear-cache-temp', function () {
    try {
        Artisan::call('view:clear');
        Artisan::call('cache:clear');
        return 'View and App Cache Cleared Successfully!';
    } catch (\Exception $e) {
        return 'Error clearing cache: ' . $e->getMessage();
    }
});
