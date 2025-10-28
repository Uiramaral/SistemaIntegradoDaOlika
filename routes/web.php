<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\{
    MenuController,
    CartController,
    OrderController,
    WebhookController,
    LoyaltyController,
    ReferralController,
    DeliveryFeeController,
    CouponController,
    PaymentController,
    PedidosBulkController,
    ReportsController,
    CustomerController,
    ProductController
};
use App\Http\Controllers\Auth\LoginController;

// =================== ROTAS DE AUTENTICAÇÃO =================== //
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('auth.login');
Route::post('/logout', [LoginController::class, 'logout'])->name('auth.logout');

/*
|--------------------------------------------------------------------------
| 1) DASHBOARD — subdomínio: dashboard.menuolika.com.br
|    Rotas de administração ficam restritas a esse host.
|--------------------------------------------------------------------------
*/
// =================== SUBDOMÍNIO: DASHBOARD =================== //
Route::domain('dashboard.menuolika.com.br')->middleware('auth')->group(function () {
    
    // Home principal (Dashboard)
    Route::get('/', function () {
        return view('dash.pages.dashboard');
    })->name('dashboard.index');

    // Rotas principais do sistema (views básicas)
    Route::get('/orders', fn() => view('dash.pages.orders'));
    Route::get('/products', fn() => view('dash.pages.products'));
    Route::get('/categories', fn() => view('dash.pages.categories'));
    Route::get('/coupons', fn() => view('dash.pages.coupons'));
    Route::get('/customers', fn() => view('dash.pages.customers'));
    Route::get('/cashback', fn() => view('dash.pages.cashback'));
    Route::get('/loyalty', fn() => view('dash.pages.loyalty'));
    Route::get('/reports', fn() => view('dash.pages.reports'));
    Route::get('/settings', fn() => view('dash.pages.settings'));

    // Recursos com controllers funcionais
    Route::resources([
        'orders'     => \App\Http\Controllers\Dashboard\DashboardController::class,
        'products'   => \App\Http\Controllers\Dashboard\ProductsController::class,
        'customers'  => \App\Http\Controllers\Dashboard\CustomersController::class,
        'categories' => \App\Http\Controllers\Dashboard\CategoriesController::class,
        'coupons'    => \App\Http\Controllers\Dashboard\CouponsController::class,
        'cashback'   => \App\Http\Controllers\Dashboard\CashbackController::class,
    ]);

    // Relatórios
    Route::get('/reports', [ReportsController::class, 'index'])->name('dashboard.reports');
    Route::get('/reports/export', [ReportsController::class, 'export'])->name('dashboard.reports.export');

    // Fidelidade
    Route::get('/loyalty', [\App\Http\Controllers\Dashboard\DashboardController::class, 'loyalty'])->name('dashboard.loyalty');

    // Configurações e integrações
    Route::get('/settings/whatsapp', [\App\Http\Controllers\Dashboard\SettingsController::class, 'whatsapp'])->name('dashboard.settings.whatsapp');
    Route::post('/settings/whatsapp', [\App\Http\Controllers\Dashboard\SettingsController::class, 'whatsappSave']);
    Route::get('/settings/mercadopago', [\App\Http\Controllers\Dashboard\SettingsController::class, 'mp'])->name('dashboard.settings.mp');
    Route::post('/settings/mercadopago', [\App\Http\Controllers\Dashboard\SettingsController::class, 'mpSave']);

    // PDV
    Route::prefix('pdv')->name('dashboard.pdv.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Dashboard\PDVController::class, 'index'])->name('index');
        Route::post('/calc', [\App\Http\Controllers\Dashboard\PDVController::class, 'calculate'])->name('calculate');
        Route::post('/order', [\App\Http\Controllers\Dashboard\PDVController::class, 'store'])->name('store');
    });

    // Rotas de administração
    Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
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
        Route::get('/', [\App\Http\Controllers\Dashboard\CouponsController::class, 'index'])->name('index');
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
|     IMPORTANTE: Esta rota só deve ser usada como fallback final
|--------------------------------------------------------------------------
*/

// Home no domínio raiz (COMENTADA - só usar se não houver subdomínio configurado)
// Route::get('/', [MenuController::class, 'index'])->name('menu.index');

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

Route::post('/dashboard/settings/production/toggle',[SettingsController::class,'toggleProduction'])->name('settings.production.toggle');
Route::post('/dashboard/pdv/order',[PDVController::class,'store'])->name('dashboard.pdv.store');

// Stubs de retorno (ajuste se já existirem)
Route::post('/mp/webhook', fn()=>response()->json(['ok'=>true]))->name('mp.webhook');
Route::get('/pagamento/sucesso', fn()=> 'Pagamento aprovado')->name('checkout.success');
Route::get('/pagamento/erro', fn()=> 'Pagamento com erro')->name('checkout.failure');
Route::get('/pagamento/pendente', fn()=> 'Pagamento pendente')->name('checkout.pending');

// Produtos
Route::prefix('products')->name('dashboard.products.')->middleware('auth')->group(function () {
    Route::get('/',         [ProductController::class, 'index'])->name('index');
    Route::get('/create',   [ProductController::class, 'create'])->name('create');
    Route::post('/',        [ProductController::class, 'store'])->name('store');
    Route::get('/{product}/edit', [ProductController::class, 'edit'])->name('edit');
    Route::put('/{product}',       [ProductController::class, 'update'])->name('update');
    Route::patch('/{product}/toggle', [ProductController::class,'toggle'])->name('toggle');
    Route::get('/search', [ProductController::class, 'search'])->name('search');

    // Galeria (AJAX)
    Route::post('/{product}/images',                           [\App\Http\Controllers\ProductGalleryController::class,'store'])->name('images.store');
    Route::patch('/{product}/images/{image}/primary',          [\App\Http\Controllers\ProductGalleryController::class,'primary'])->name('images.primary');
    Route::delete('/{product}/images/{image}',                 [\App\Http\Controllers\ProductGalleryController::class,'destroy'])->name('images.destroy');
    Route::patch('/{product}/images/{image}/move/{direction}', [\App\Http\Controllers\ProductGalleryController::class,'move'])->name('images.move'); // up/down
});

// --- PDV — AJAX --- //
Route::middleware(['auth'])->group(function () {
    Route::get('/ajax/test', [\App\Http\Controllers\Ajax\TestController::class, 'test'])->name('dashboard.ajax.test');
    Route::get('/ajax/cep', [\App\Http\Controllers\Ajax\PdvAjaxController::class, 'cep'])->name('dashboard.ajax.cep');
    Route::get('/ajax/customers', [\App\Http\Controllers\Ajax\PdvAjaxController::class, 'customers'])->name('dashboard.ajax.customers');
    Route::get('/ajax/products', [\App\Http\Controllers\Ajax\PdvAjaxController::class, 'products'])->name('dashboard.ajax.products');
    Route::get('/ajax/coupons/eligible', [\App\Http\Controllers\Ajax\PdvAjaxController::class, 'eligibleCoupons'])->name('dashboard.ajax.coupons.eligible');
    Route::post('/ajax/delivery/options', [\App\Http\Controllers\Ajax\PdvAjaxController::class, 'deliveryOptions'])->name('dashboard.ajax.delivery.options');
});

Route::prefix('dashboard/pedidos')->name('dashboard.orders.')->group(function () {
    Route::get('{order}', [OrderController::class, 'show'])->name('show');

    // Edições rápidas (data/hora/status/observação)
    Route::post('{order}/meta', [OrderController::class, 'updateMeta'])->name('meta');

    // Itens (adicionar/remover/alterar qtd)
    Route::post('{order}/items', [OrderController::class, 'addItem'])->name('items.add');
    Route::patch('{order}/items/{item}', [OrderController::class, 'updateItem'])->name('items.update');
    Route::delete('{order}/items/{item}', [OrderController::class, 'removeItem'])->name('items.remove');
});

// Clientes - ver/editar
Route::get('/dashboard/clientes/{customer}', [CustomerController::class, 'show'])->name('dashboard.customers.show');
Route::post('/dashboard/clientes/{customer}/update', [CustomerController::class, 'update'])->name('dashboard.customers.update');

// Produtos - index (cards padrão), editar, salvar
Route::get('/dashboard/produtos/{product}/edit', [ProductController::class, 'edit'])->name('dashboard.products.edit');
Route::post('/dashboard/produtos/{product}', [ProductController::class, 'update'])->name('dashboard.products.update');
Route::post('/dashboard/produtos/{product}/images', [ProductController::class, 'storeImage'])->name('dashboard.products.images.store');
Route::post('/dashboard/produtos/{product}/images/{image}/primary', [ProductController::class, 'makePrimary'])->name('dashboard.products.images.primary');
Route::delete('/dashboard/produtos/{product}/images/{image}', [ProductController::class, 'destroyImage'])->name('dashboard.products.images.destroy');

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
    Route::get('/', [\App\Http\Controllers\Dashboard\CouponsController::class, 'index'])->name('index');
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

// ===== Espelho de debug (SEM binding de domínio): testa controller/view mesmo que o host não case) =====
Route::get('/__orders_mirror', [\App\Http\Controllers\Dashboard\DashboardController::class, 'orders'])
    ->name('debug.orders.mirror');

// ===== Dashboard em subdomínio: suporta com e sem www =====
$dashboardHosts = ['dashboard.menuolika.com.br', 'www.dashboard.menuolika.com.br'];

foreach ($dashboardHosts as $host) {
    Route::domain($host)->group(function () {
    // Home completo e compacto
    Route::get('/', [\App\Http\Controllers\Dashboard\DashboardController::class, 'home'])->name('dashboard.index');
    Route::get('/compact', [\App\Http\Controllers\Dashboard\DashboardController::class, 'compact'])->name('dashboard.compact');
    
    // Pedidos
    Route::get('/pedidos', [OrderController::class, 'index'])->name('dashboard.orders.index');
    Route::get('/orders', [\App\Http\Controllers\Dashboard\DashboardController::class, 'orders'])->name('dashboard.orders');
    // Rota duplicada: aceita tanto code quanto id (compatibilidade)
    Route::get('/orders/{order}', [\App\Http\Controllers\Dashboard\DashboardController::class, 'orderShow'])
        ->name('dashboard.orders.show');
    Route::post('/orders/{order}/status', [\App\Http\Controllers\Dashboard\DashboardController::class, 'orderChangeStatus'])
        ->name('dashboard.orders.status');
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
    }); // fim do foreach $host
} // fim do foreach $dashboardHosts

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

// =================== ROTAS GLOBAIS =================== //
Route::get('/clear-cache-now', function () {
    Artisan::call('optimize:clear');
    return response()->json(['status' => 'success', 'cleared' => true]);
});

Route::prefix('webhooks')->group(function () {
    Route::post('/mercadopago', [WebhookController::class, 'mercadoPago']);
});

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
