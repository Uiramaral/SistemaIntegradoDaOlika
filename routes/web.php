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
use App\Http\Controllers\PedidosBulkController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\ConsignacoesController;

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

// ===== Checkout por Etapas (Novo Sistema) =====

Route::prefix('cart')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('cart.index');
    Route::post('add', [CartController::class, 'add'])->name('cart.add');
    Route::post('update', [CartController::class, 'update'])->name('cart.update');
    Route::post('remove', [CartController::class, 'remove'])->name('cart.remove');
});

Route::prefix('checkout')->group(function () {
    // Etapa 1: Cliente
    Route::get('/', [\App\Http\Controllers\CheckoutController::class, 'stepCustomer'])->name('checkout.customer');
    Route::post('customer', [\App\Http\Controllers\CheckoutController::class, 'storeCustomer'])->name('checkout.customer.store');
    
    // Etapa 2: Endereço
    Route::get('address', [\App\Http\Controllers\CheckoutController::class, 'stepAddress'])->name('checkout.address');
    Route::post('address', [\App\Http\Controllers\CheckoutController::class, 'storeAddress'])->name('checkout.address.store');
    
    // Etapa 3: Revisão + Cupons
    Route::get('review', [\App\Http\Controllers\CheckoutController::class, 'stepReview'])->name('checkout.review');
    Route::post('apply-coupon', [CouponController::class, 'apply'])->name('checkout.coupon.apply');
    Route::post('remove-coupon', [CouponController::class, 'remove'])->name('checkout.coupon.remove');
    
    // Etapa 4: Pagamento
    Route::get('payment', [\App\Http\Controllers\CheckoutController::class, 'stepPayment'])->name('checkout.payment');
    Route::post('payment/pix', [\App\Http\Controllers\PaymentController::class, 'createPix'])->name('payment.pix');
    Route::post('payment/mp', [\App\Http\Controllers\PaymentController::class, 'createMpPreference'])->name('payment.mercadopago');
    
    // Sucesso
    Route::get('success/{order}', [\App\Http\Controllers\CheckoutController::class, 'success'])->name('checkout.success');
});

// Webhook Mercado Pago
Route::post('/payments/mercadopago/webhook', [\App\Http\Controllers\WebhookController::class, 'mercadoPago'])->name('webhook.mercadopago');

// Dashboard (subdomínio)
Route::domain('dashboard.menuolika.com.br')->group(function () {
    // Home completo e compacto
    Route::get('/', [\App\Http\Controllers\Dashboard\DashboardController::class, 'home'])->name('dashboard.index');
    Route::get('/compact', [\App\Http\Controllers\Dashboard\DashboardController::class, 'compact'])->name('dashboard.compact');
    
    // Pedidos
    Route::get('/orders', [\App\Http\Controllers\Dashboard\DashboardController::class, 'orders'])->name('dashboard.orders');
    Route::get('/orders/{order}', [\App\Http\Controllers\Dashboard\DashboardController::class, 'orderShow'])->name('dashboard.orders.show');
    Route::post('/orders/{order}/status', [\App\Http\Controllers\Dashboard\DashboardController::class, 'orderChangeStatus'])->name('dashboard.orders.status');
    Route::post('/pedidos/bulk', [PedidosBulkController::class, 'update'])->name('pedidos.bulk');
    
    // Clientes (CRUD completo)
    Route::resource('/customers', \App\Http\Controllers\Dashboard\CustomersController::class)->names([
        'index' => 'dashboard.customers',
        'create' => 'dashboard.customers.create',
        'store' => 'dashboard.customers.store',
        'show' => 'dashboard.customers.show',
        'edit' => 'dashboard.customers.edit',
        'update' => 'dashboard.customers.update',
        'destroy' => 'dashboard.customers.destroy',
    ]);
    
    // Produtos (CRUD completo)
    Route::resource('/products', \App\Http\Controllers\Dashboard\ProductsController::class)->names([
        'index' => 'dashboard.products',
        'create' => 'dashboard.products.create',
        'store' => 'dashboard.products.store',
        'edit' => 'dashboard.products.edit',
        'update' => 'dashboard.products.update',
        'destroy' => 'dashboard.products.destroy',
    ]);
    Route::post('/products/{id}/toggle', [\App\Http\Controllers\Dashboard\ProductsController::class, 'toggleStatus'])->name('dashboard.products.toggle');
    
    // Categorias (CRUD completo)
    Route::resource('/categories', \App\Http\Controllers\Dashboard\CategoriesController::class)->names([
        'index' => 'dashboard.categories',
        'create' => 'dashboard.categories.create',
        'store' => 'dashboard.categories.store',
        'edit' => 'dashboard.categories.edit',
        'update' => 'dashboard.categories.update',
        'destroy' => 'dashboard.categories.destroy',
    ]);
    Route::post('/categories/{id}/toggle', [\App\Http\Controllers\Dashboard\CategoriesController::class, 'toggleStatus'])->name('dashboard.categories.toggle');
    
    // Cupons (CRUD completo)
    Route::resource('/coupons', \App\Http\Controllers\Dashboard\CouponsController::class)->names([
        'index' => 'dashboard.coupons',
        'create' => 'dashboard.coupons.create',
        'store' => 'dashboard.coupons.store',
        'edit' => 'dashboard.coupons.edit',
        'update' => 'dashboard.coupons.update',
        'destroy' => 'dashboard.coupons.destroy',
    ]);
    Route::post('/coupons/{id}/toggle', [\App\Http\Controllers\Dashboard\CouponsController::class, 'toggleStatus'])->name('dashboard.coupons.toggle');
    
    // Cashback (CRUD completo)
    Route::resource('/cashback', \App\Http\Controllers\Dashboard\CashbackController::class)->names([
        'index' => 'dashboard.cashback',
        'create' => 'dashboard.cashback.create',
        'store' => 'dashboard.cashback.store',
        'edit' => 'dashboard.cashback.edit',
        'update' => 'dashboard.cashback.update',
        'destroy' => 'dashboard.cashback.destroy',
    ]);
    
    // Fidelidade (somente visualização - mantém DashboardController)
    Route::get('/loyalty', [\App\Http\Controllers\Dashboard\DashboardController::class, 'loyalty'])->name('dashboard.loyalty');
    
    // Relatórios
    Route::get('/reports', [\App\Http\Controllers\Dashboard\DashboardController::class, 'reports'])->name('dashboard.reports');
    Route::get('/relatorios', [ReportsController::class,'index'])->name('relatorios.index');
    Route::get('/relatorios/export', [ReportsController::class,'export'])->name('relatorios.export');
    
    // Consignações
    Route::resource('consignacoes', ConsignacoesController::class);
    
        // Settings - WhatsApp e Mercado Pago
        Route::get('/whatsapp', [\App\Http\Controllers\Dashboard\SettingsController::class, 'whatsapp'])->name('dashboard.whatsapp');
        Route::post('/whatsapp', [\App\Http\Controllers\Dashboard\SettingsController::class, 'whatsappSave'])->name('dashboard.whatsapp.save');
        Route::post('/whatsapp/connect', [\App\Http\Controllers\Dashboard\SettingsController::class, 'waConnect'])->name('dashboard.whatsapp.connect');
        Route::post('/whatsapp/health', [\App\Http\Controllers\Dashboard\SettingsController::class, 'waHealth'])->name('dashboard.whatsapp.health');
        Route::get('/mercadopago', [\App\Http\Controllers\Dashboard\SettingsController::class, 'mp'])->name('dashboard.mp');
        Route::post('/mercadopago', [\App\Http\Controllers\Dashboard\SettingsController::class, 'mpSave'])->name('dashboard.mp.save');
    
    // Status de pedidos
    Route::get('/statuses', [\App\Http\Controllers\Dashboard\OrderStatusController::class, 'index'])->name('dashboard.statuses');
    Route::post('/statuses', [\App\Http\Controllers\Dashboard\OrderStatusController::class, 'store'])->name('dashboard.statuses.store');
    Route::patch('/statuses/{id}', [\App\Http\Controllers\Dashboard\OrderStatusController::class, 'updateFlags'])->name('dashboard.statuses.update');
    Route::delete('/statuses/{id}', [\App\Http\Controllers\Dashboard\OrderStatusController::class, 'destroy'])->name('dashboard.statuses.destroy');
    
    // PDV
    Route::get('/pdv', [\App\Http\Controllers\Dashboard\PDVController::class, 'index'])->name('dashboard.pdv');
    Route::post('/pdv/calc', [\App\Http\Controllers\Dashboard\PDVController::class, 'calculate'])->name('dashboard.pdv.calculate');
    Route::post('/pdv/order', [\App\Http\Controllers\Dashboard\PDVController::class, 'store'])->name('dashboard.pdv.store');
    Route::get('/pdv/search/customers', [\App\Http\Controllers\Dashboard\PDVController::class, 'searchCustomers'])->name('dashboard.pdv.search.customers');
    Route::get('/pdv/search/products', [\App\Http\Controllers\Dashboard\PDVController::class, 'searchProducts'])->name('dashboard.pdv.search.products');
    Route::post('/pdv/validate-coupon', [\App\Http\Controllers\Dashboard\PDVController::class, 'validateCoupon'])->name('dashboard.pdv.validate.coupon');
    Route::post('/pdv/address', [\App\Http\Controllers\Dashboard\PDVController::class, 'saveAddress'])->name('dashboard.pdv.address');
    
    // Detalhes do pedido (com QR PIX)
    Route::get('/orders/{id}', [\App\Http\Controllers\OrderViewController::class, 'show'])->name('orders.show');
    
    // Fiados do cliente
    Route::get('/customers/{id}/fiados', [\App\Http\Controllers\DebtsController::class, 'index'])->name('debts.index');
    
    // API auxiliar para endereços
    Route::get('/api/addresses', function (\Illuminate\Http\Request $r) {
        if (!$r->has('customer_id')) return response()->json([]);
        return response()->json(
            \Illuminate\Support\Facades\DB::table('addresses')
                ->where('customer_id', $r->customer_id)
                ->get()
        );
    });
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

/*
|--------------------------------------------------------------------------
| ROTAS DO PDV (Ponto de Venda)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    // Busca de clientes e produtos (GET)
    Route::get('/api/customers/search', [\App\Http\Controllers\Api\CustomerSearchController::class, 'index'])->name('api.customers.search');
    Route::get('/api/products/search', [\App\Http\Controllers\Api\ProductSearchController::class, 'index'])->name('api.products.search');
    
    // Fiado - saldo do cliente
    Route::get('/api/customers/fiado/balance', [\App\Http\Controllers\Api\FiadoController::class, 'balance'])->name('api.customers.fiado.balance');
    
    // Cupons
    Route::get('/api/coupons/eligible', [\App\Http\Controllers\Api\CouponController::class, 'eligible'])->name('api.coupons.eligible');
    Route::post('/api/coupons/validate', [\App\Http\Controllers\Api\CouponController::class, 'validateCode'])->name('api.coupons.validate');
    
    // Salvar pedido do PDV
    Route::post('/api/pdv/store', [\App\Http\Controllers\PDVController::class, 'store'])->name('api.pdv.store');
    
    // (opcionais do topo da página)
    Route::get('/dashboard/layout/download', fn() => abort(404))->name('dashboard.layout.download');
    Route::get('/dashboard/status/create', fn() => abort(404))->name('dashboard.status.create');
});
