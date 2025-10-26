<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

// Seus controllers (ajuste namespaces se necessário)
use App\Http\Controllers\MenuController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\LoyaltyController;
use App\Http\Controllers\ReferralController;
use App\Http\Controllers\DeliveryFeeController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\PaymentController;

/*
|--------------------------------------------------------------------------
| 1) DASHBOARD — subdomínio: dashboard.menuolika.com.br
|    Rotas de administração ficam restritas a esse host.
|--------------------------------------------------------------------------
*/
Route::domain('dashboard.menuolika.com.br')->group(function () {

    // Página inicial do subdomínio -> redireciona para o dashboard
    Route::get('/', function () {
        return redirect()->route('admin.dashboard');
    })->name('dashboard.home');

    // Rotas de administração
    Route::prefix('admin')->name('admin.')->group(function () {
        // Dashboard
        Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/stats', [\App\Http\Controllers\Admin\DashboardController::class, 'getStats'])->name('dashboard.stats');
        Route::get('/dashboard/sales-chart', [\App\Http\Controllers\Admin\DashboardController::class, 'getSalesChart'])->name('dashboard.sales-chart');

        // Payment Settings
        Route::get('/payment-settings', [\App\Http\Controllers\Admin\PaymentSettingsController::class, 'index'])->name('payment-settings');
        Route::post('/payment-settings', [\App\Http\Controllers\Admin\PaymentSettingsController::class, 'update'])->name('payment-settings.update');
        Route::get('/payment-settings/get', [\App\Http\Controllers\Admin\PaymentSettingsController::class, 'getSettings'])->name('payment-settings.get');
        Route::post('/payment-settings/test-connection', [\App\Http\Controllers\Admin\PaymentSettingsController::class, 'testConnection'])->name('payment-settings.test-connection');
        Route::post('/payment-settings/toggle-test-mode', [\App\Http\Controllers\Admin\PaymentSettingsController::class, 'toggleTestMode'])->name('payment-settings.toggle-test-mode');

        // Health Check
        Route::get('/health', [\App\Http\Controllers\Admin\HealthCheckController::class, 'index'])->name('health');
    });
});

/*
|--------------------------------------------------------------------------
| 2) LOJA — subdomínio: pedido.menuolika.com.br
|    Rotas da loja pública sob esse host.
|--------------------------------------------------------------------------
*/
Route::domain('pedido.menuolika.com.br')->group(function () {

    // Home mostra o cardápio direto (sem /menu)
    Route::get('/', [MenuController::class, 'index'])->name('menu.index');

    // Alias /menu (opcional), com nome diferente pra não duplicar
    Route::prefix('menu')->name('menu.')->group(function () {
        Route::get('/', [MenuController::class, 'index'])->name('page');
        Route::get('/categoria/{category}', [MenuController::class, 'category'])->name('category');
        Route::get('/produto/{product}', [MenuController::class, 'product'])->name('product');
        Route::get('/buscar', [MenuController::class, 'search'])->name('search');
        Route::get('/download', [MenuController::class, 'download'])->name('download');
    });

    // API do carrinho (JSON) - URLs corretas para subdomínio pedido.menuolika.com.br
    Route::prefix('cart')->group(function () {
        Route::get('/', [CartController::class, 'show'])->name('cart.index');
        Route::get('/count',  [CartController::class, 'count'])->name('cart.count');
        Route::get('/items',  [CartController::class, 'items'])->name('cart.items');
        Route::post('/add',    [CartController::class, 'add'])->name('cart.add');
        Route::post('/update', [CartController::class, 'update'])->name('cart.update');
        Route::post('/remove', [CartController::class, 'remove'])->name('cart.remove');
        Route::post('/clear',  [CartController::class, 'clear'])->name('cart.clear');
    });
    
    // IMPORTANTE: No subdomínio pedido.menuolika.com.br, as rotas do carrinho são:
    // POST /cart/add (não /pedido/cart/add)
    // Use sempre {{ route('cart.add') }} no Blade/JS para evitar URLs hardcoded

    // Debug routes (temporário) - melhorado
    Route::get('/debug/routes', function () {
        return collect(\Route::getRoutes())->map(fn($r) => [
            'host'    => $r->domain(),
            'uri'     => $r->uri(),
            'methods' => $r->methods(),
            'name'    => $r->getName()
        ])->values();
    });

    // Debug menu (temporário)
    Route::get('/debug/menu', function () {
        $controller = new \App\Http\Controllers\MenuController();
        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('index');
        $method->setAccessible(true);
        $result = $method->invoke($controller);
        
        $featuredProducts = $result['featuredProducts'];
        $categories = $result['categories'];
        
        return [
            'featured_products' => $featuredProducts->pluck('id', 'name'),
            'categories' => $categories->map(function($cat) {
                return [
                    'name' => $cat->name,
                    'products' => $cat->products->pluck('id', 'name')
                ];
            })
        ];
    });

    // Rota para limpar cache do sistema (somente neste subdomínio)
    Route::match(['get', 'post'], '/cache/limpar', function() {
        $commands = [];
        $results = [];
        try {
            Artisan::call('cache:clear');      $commands[] = 'cache:clear';      $results['cache:clear'] = 'OK';
            Artisan::call('config:clear');     $commands[] = 'config:clear';     $results['config:clear'] = 'OK';
            Artisan::call('route:clear');      $commands[] = 'route:clear';      $results['route:clear'] = 'OK';
            Artisan::call('view:clear');       $commands[] = 'view:clear';       $results['view:clear'] = 'OK';
            Artisan::call('optimize:clear');   $commands[] = 'optimize:clear';   $results['optimize:clear'] = 'OK';
        } catch (\Exception $e) {
            $results['error'] = $e->getMessage();
        }
        return response()->json([
            'success'   => true,
            'message'   => 'Cache do sistema limpo com sucesso!',
            'commands'  => $commands,
            'results'   => $results,
            'timestamp' => now()->format('Y-m-d H:i:s')
        ]);
    })->name('cache.limpar');

    // Rotas do pedido / checkout
    Route::prefix('checkout')->name('checkout.')->middleware('cart.not.empty')->group(function () {
        Route::get('/', [OrderController::class, 'checkout'])->name('index');
        Route::post('/', [OrderController::class, 'store'])->name('store');

        // APIs para checkout por etapas
        Route::post('/save-customer-data', [OrderController::class, 'saveCustomerData'])->name('save-customer-data');
        Route::post('/save-delivery-address', [OrderController::class, 'saveDeliveryAddress'])->name('save-delivery-address');
        Route::post('/validate-coupon', [OrderController::class, 'validateCoupon'])->name('validate-coupon');
    });

    Route::prefix('order')->name('order.')->group(function () {
        Route::get('/success/{order}', [OrderController::class, 'success'])->name('success');
    });

    // Rotas de fidelidade
    Route::prefix('loyalty')->name('loyalty.')->group(function () {
        Route::get('/', [LoyaltyController::class, 'index'])->name('index');
        Route::post('/redeem', [LoyaltyController::class, 'redeemPoints'])->name('redeem');
    });

    // Rotas de indicação
    Route::prefix('referral')->name('referral.')->group(function () {
        Route::get('/', [ReferralController::class, 'index'])->name('index');
        Route::post('/create', [ReferralController::class, 'create'])->name('create');
    });

    // Rotas de taxa de entrega
    Route::prefix('delivery-fee')->name('delivery-fee.')->group(function () {
        Route::post('/adjust/{order}', [DeliveryFeeController::class, 'adjustFee'])->name('adjust');
        Route::post('/discount/{order}', [DeliveryFeeController::class, 'applyDiscount'])->name('discount');
        Route::post('/free/{order}', [DeliveryFeeController::class, 'setFreeDelivery'])->name('free');
        Route::post('/revert/{order}', [DeliveryFeeController::class, 'revertToCalculated'])->name('revert');
        Route::get('/history/{order}', [DeliveryFeeController::class, 'getAdjustmentHistory'])->name('history');
        Route::get('/stats', [DeliveryFeeController::class, 'getStats'])->name('stats');
        Route::get('/calculate/{order}', [DeliveryFeeController::class, 'calculateFee'])->name('calculate');
        Route::get('/adjustments', [DeliveryFeeController::class, 'getOrdersWithAdjustments'])->name('adjustments');
    });

    // Rotas de cupons
    Route::prefix('coupons')->name('coupons.')->group(function () {
        Route::get('/', [CouponController::class, 'index'])->name('index');
    });

    // Rotas de pagamento
    Route::prefix('payment')->name('payment.')->group(function () {
        Route::get('/pix/{order}', [PaymentController::class, 'pixPayment'])->name('pix');
        Route::get('/card/{order}', [PaymentController::class, 'cardPayment'])->name('card');
        Route::get('/success/{order}', [PaymentController::class, 'success'])->name('success');
        Route::get('/failure/{order}', [PaymentController::class, 'failure'])->name('failure');
        Route::get('/pending/{order}', [PaymentController::class, 'pending'])->name('pending');
    });

    // Rota __flush removida - usar apenas a versão global securitizada
});

/*
|--------------------------------------------------------------------------
| 3) LOJA — rotas globais (domínio principal /sistema/public)
|     Mantém compatibilidade acessando pelo domínio raiz.
|--------------------------------------------------------------------------
*/

// Home no domínio raiz
Route::get('/', [MenuController::class, 'index'])->name('menu.index');

// Alias /menu (opcional), com nome diferente pra não duplicar
Route::prefix('menu')->name('menu.')->group(function () {
    Route::get('/', [MenuController::class, 'index'])->name('page');
    Route::get('/categoria/{category}', [MenuController::class, 'category'])->name('category');
    Route::get('/produto/{product}', [MenuController::class, 'product'])->name('product');
    Route::get('/buscar', [MenuController::class, 'search'])->name('search');
    Route::get('/download', [MenuController::class, 'download'])->name('download');
});

// Página HTML do carrinho (usa o nome esperado pelo Blade)
Route::get('/cart', [CartController::class, 'show'])->name('cart.index');

// Rota para limpar cache do sistema (global)
Route::match(['get', 'post'], '/cache/limpar', function() {
    $commands = [];
    $results  = [];
    try {
        Artisan::call('cache:clear');      $commands[] = 'cache:clear';      $results['cache:clear'] = 'OK';
        Artisan::call('config:clear');     $commands[] = 'config:clear';     $results['config:clear'] = 'OK';
        Artisan::call('route:clear');      $commands[] = 'route:clear';      $results['route:clear'] = 'OK';
        Artisan::call('view:clear');       $commands[] = 'view:clear';       $results['view:clear'] = 'OK';
        Artisan::call('optimize:clear');   $commands[] = 'optimize:clear';   $results['optimize:clear'] = 'OK';
    } catch (\Exception $e) {
        $results['error'] = $e->getMessage();
    }
    return response()->json([
        'success'   => true,
        'message'   => 'Cache do sistema limpo com sucesso!',
        'commands'  => $commands,
        'results'   => $results,
        'timestamp' => now()->format('Y-m-d H:i:s')
    ]);
})->name('cache.limpar');

// Rotas do pedido
Route::prefix('checkout')->name('checkout.')->middleware('cart.not.empty')->group(function () {
    Route::get('/', [OrderController::class, 'checkout'])->name('index');
    Route::post('/', [OrderController::class, 'store'])->name('store');

    // APIs para checkout por etapas
    Route::post('/save-customer-data', [OrderController::class, 'saveCustomerData'])->name('save-customer-data');
    Route::post('/save-delivery-address', [OrderController::class, 'saveDeliveryAddress'])->name('save-delivery-address');
    Route::post('/validate-coupon', [OrderController::class, 'validateCoupon'])->name('validate-coupon');
});

Route::prefix('order')->name('order.')->group(function () {
    Route::get('/success/{order}', [OrderController::class, 'success'])->name('success');
});

// Rotas de fidelidade
Route::prefix('loyalty')->name('loyalty.')->group(function () {
    Route::get('/', [LoyaltyController::class, 'index'])->name('index');
    Route::post('/redeem', [LoyaltyController::class, 'redeemPoints'])->name('redeem');
});

// Rotas de indicação
Route::prefix('referral')->name('referral.')->group(function () {
    Route::get('/', [ReferralController::class, 'index'])->name('index');
    Route::post('/create', [ReferralController::class, 'create'])->name('create');
});

// Rotas de taxa de entrega
Route::prefix('delivery-fee')->name('delivery-fee.')->group(function () {
    Route::post('/adjust/{order}', [DeliveryFeeController::class, 'adjustFee'])->name('adjust');
    Route::post('/discount/{order}', [DeliveryFeeController::class, 'applyDiscount'])->name('discount');
    Route::post('/free/{order}', [DeliveryFeeController::class, 'setFreeDelivery'])->name('free');
    Route::post('/revert/{order}', [DeliveryFeeController::class, 'revertToCalculated'])->name('revert');
    Route::get('/history/{order}', [DeliveryFeeController::class, 'getAdjustmentHistory'])->name('history');
    Route::get('/stats', [DeliveryFeeController::class, 'getStats'])->name('stats');
    Route::get('/calculate/{order}', [DeliveryFeeController::class, 'calculateFee'])->name('calculate');
    Route::get('/adjustments', [DeliveryFeeController::class, 'getOrdersWithAdjustments'])->name('adjustments');
});

// Rotas de cupons
Route::prefix('coupons')->name('coupons.')->group(function () {
    Route::get('/', [CouponController::class, 'index'])->name('index');
});

// Rotas de pagamento
Route::prefix('payment')->name('payment.')->group(function () {
    Route::get('/pix/{order}', [PaymentController::class, 'pixPayment'])->name('pix');
    Route::get('/card/{order}', [PaymentController::class, 'cardPayment'])->name('card');
    Route::get('/success/{order}', [PaymentController::class, 'success'])->name('success');
    Route::get('/failure/{order}', [PaymentController::class, 'failure'])->name('failure');
    Route::get('/pending/{order}', [PaymentController::class, 'pending'])->name('pending');
});

/*
|--------------------------------------------------------------------------
| 4) WEBHOOKS — globais (use a mesma URL configurada no provedor)
|--------------------------------------------------------------------------
*/
Route::prefix('webhooks')->name('webhooks.')->group(function () {
    Route::post('/mercadopago', [WebhookController::class, 'mercadoPago'])->name('mercadopago');
    Route::post('/whatsapp', [WebhookController::class, 'whatsApp'])->name('whatsapp');
});

/*
|--------------------------------------------------------------------------
| 5) Utilitários globais (health + clear)
|--------------------------------------------------------------------------
*/
Route::get('/health-sistema', function() {
    // Limpar cache automaticamente quando acessar health-sistema
    try {
        Artisan::call('route:clear');
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('view:clear');
        Artisan::call('optimize:clear');
        
        return response()->json([
            'status' => 'ok-from-sistema',
            'cache_cleared' => true,
            'message' => 'Cache limpo automaticamente',
            'time' => now(),
            'layout_fixed' => true,
            'routes_count' => count(\Route::getRoutes())
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Erro ao limpar cache: ' . $e->getMessage(),
            'time' => now(),
            'error_type' => get_class($e)
        ]);
    }
});

// Rota de diagnóstico para RouteNotFoundException
Route::get('/debug-route-error', function() {
    try {
        $routes = collect(\Route::getRoutes())->map(fn($r) => [
            'name' => $r->getName(),
            'uri' => $r->uri(),
            'methods' => $r->methods(),
            'domain' => $r->domain()
        ])->values();
        
        return response()->json([
            'status' => 'success',
            'total_routes' => $routes->count(),
            'routes' => $routes,
            'time' => now()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'error_type' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'time' => now()
        ]);
    }
});

// Rotas de teste SEM middleware CSRF
Route::get('/test-no-csrf', function() {
    return response()->json([
        'status' => 'success',
        'message' => 'Rota sem CSRF funcionando',
        'time' => now(),
        'csrf_status' => 'disabled'
    ]);
})->withoutMiddleware(['web']);

Route::post('/test-post-no-csrf', function() {
    return response()->json([
        'status' => 'success',
        'message' => 'POST sem CSRF funcionando',
        'time' => now(),
        'method' => request()->method()
    ]);
})->withoutMiddleware(['web']);

// Teste de flush SEM CSRF e SEM token
Route::match(['get','post'], '/flush-no-csrf', function () {
    $results = []; $errors = [];
    try {
        Artisan::call('cache:clear');        $results['cache:clear']       = 'OK';
        Artisan::call('config:clear');       $results['config:clear']      = 'OK';
        Artisan::call('route:clear');        $results['route:clear']       = 'OK';
        Artisan::call('view:clear');         $results['view:clear']        = 'OK';
        Artisan::call('optimize:clear');     $results['optimize:clear']    = 'OK';
        if (function_exists('opcache_reset')) { @opcache_reset(); $results['opcache:reset'] = 'OK'; }
    } catch (\Throwable $e) {
        $errors[] = $e->getMessage();
        $results['error'] = $e->getMessage();
    }

    return response()->json([
        'success'     => empty($errors),
        'message'     => 'Flush sem CSRF realizado',
        'timestamp'   => now()->toDateTimeString(),
        'results'     => $results,
        'errors'      => $errors,
        'csrf_status' => 'disabled'
    ]);
})->withoutMiddleware(['web']);

// Rota de limpeza de cache (sem token para teste)
Route::get('/clear-cache-now', function () {
    try {
        Artisan::call('route:clear');
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('view:clear');
        Artisan::call('optimize:clear');
        
        return response()->json([
            'status' => 'success',
            'message' => 'Cache limpo com sucesso',
            'time' => now(),
            'commands' => ['route:clear', 'cache:clear', 'config:clear', 'view:clear', 'optimize:clear']
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'time' => now()
        ]);
    }
});

// Rotas de teste para diagnóstico do 404
Route::get('/test-simple', fn() => 'TESTE SIMPLES FUNCIONANDO');
Route::get('/test-json', fn() => response()->json(['status' => 'ok', 'time' => now()]));
Route::get('/test-phpinfo', fn() => phpinfo());

// Debug routes global (para verificar todas as rotas)
Route::get('/debug/routes', function () {
    return collect(\Route::getRoutes())->map(fn($r) => [
        'host'    => $r->domain(),
        'uri'     => $r->uri(),
        'methods' => $r->methods(),
        'name'    => $r->getName()
    ])->values();
});

// --- Utilitários globais (respondem em qualquer host, protegidos por token) ---

Route::any('/_tools/clear', function () {
    abort_unless(request('t') === env('SYSTEM_CLEAR_TOKEN'), 403, 'Acesso não autorizado');

    if (function_exists('opcache_reset')) { @opcache_reset(); }
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('route:clear');
    Artisan::call('view:clear');
    Artisan::call('optimize:clear');

    return response()->json([
        'status'  => 'ok',
        'time'    => now()->toDateTimeString(),
        'actions' => ['cache:clear','config:clear','route:clear','view:clear','optimize:clear'],
    ]);
})->name('tools.clear');

Route::match(['get','post'], '/__flush', function () {
    abort_unless(request('t') === env('SYSTEM_FLUSH_TOKEN'), 403, 'Acesso não autorizado');

    $results = []; $errors = [];
    try {
        Artisan::call('cache:clear');        $results['cache:clear']       = 'OK';
        Artisan::call('config:clear');       $results['config:clear']      = 'OK';
        Artisan::call('route:clear');        $results['route:clear']       = 'OK';
        Artisan::call('view:clear');         $results['view:clear']        = 'OK';
        Artisan::call('optimize:clear');     $results['optimize:clear']    = 'OK';
        Artisan::call('auth:clear-resets');  $results['auth:clear-resets'] = 'OK';
        if (function_exists('opcache_reset')) { @opcache_reset(); $results['opcache:reset'] = 'OK'; }
    } catch (\Throwable $e) {
        $errors[] = $e->getMessage();
        $results['error'] = $e->getMessage();
    }

    return response()->json([
        'success'     => empty($errors),
        'message'     => 'Flush completo realizado',
        'timestamp'   => now()->toDateTimeString(),
        'results'     => $results,
        'errors'      => $errors,
        'server_info' => ['php' => PHP_VERSION, 'laravel' => app()->version(), 'env' => app()->environment()],
    ]);
})->name('system.flush');
