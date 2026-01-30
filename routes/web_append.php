<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Carregar rota de correção financeira (fix_revenue_tool.php)
// Isso sobrescreve a rota temporária antiga se existir, pois é carregado depois/injetado aqui
if (file_exists(__DIR__ . '/fix_revenue_tool.php')) {
    require __DIR__ . '/fix_revenue_tool.php';
}
