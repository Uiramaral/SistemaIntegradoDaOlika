<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
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
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProductController;

// Autenticação
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('auth.login');
Route::post('/logout', [LoginController::class, 'logout'])->name('auth.logout');
Route::get('/logout', [LoginController::class, 'logout'])->name('auth.logout.get');

// Registro de novos administradores
Route::get('/register', [RegisterController::class, 'showForm'])->name('register.form');
Route::post('/register', [RegisterController::class, 'register'])->name('register');

// Subdomínio: Dashboard (produção)
Route::domain('dashboard.menuolika.com.br')->middleware('auth')->group(function () {

    Route::get('/', [\App\Http\Controllers\Dashboard\DashboardController::class, 'home'])->name('dashboard.index');
    Route::get('/compact', [\App\Http\Controllers\Dashboard\DashboardController::class, 'compact'])->name('dashboard.compact');
    
    // Views estáticas removidas - agora usando controllers com dados reais
    
    // Recursos via controllers (CRUDs)
    Route::resource('products',  \App\Http\Controllers\Dashboard\ProductsController::class)->names([
        'index' => 'dashboard.products.index',
        'create' => 'dashboard.products.create',
        'store' => 'dashboard.products.store',
        'show' => 'dashboard.products.show',
        'edit' => 'dashboard.products.edit',
        'update' => 'dashboard.products.update',
        'destroy' => 'dashboard.products.destroy',
    ]);
    Route::resource('customers', \App\Http\Controllers\Dashboard\CustomersController::class)->names([
        'index' => 'dashboard.customers.index',
        'create' => 'dashboard.customers.create',
        'store' => 'dashboard.customers.store',
        'show' => 'dashboard.customers.show',
        'edit' => 'dashboard.customers.edit',
        'update' => 'dashboard.customers.update',
        'destroy' => 'dashboard.customers.destroy',
    ]);
    Route::resource('categories',\App\Http\Controllers\Dashboard\CategoriesController::class)->names([
        'index' => 'dashboard.categories.index',
        'create' => 'dashboard.categories.create',
        'store' => 'dashboard.categories.store',
        'show' => 'dashboard.categories.show',
        'edit' => 'dashboard.categories.edit',
        'update' => 'dashboard.categories.update',
        'destroy' => 'dashboard.categories.destroy',
    ]);
    Route::resource('coupons',   \App\Http\Controllers\Dashboard\CouponsController::class)->names([
        'index' => 'dashboard.coupons.index',
        'create' => 'dashboard.coupons.create',
        'store' => 'dashboard.coupons.store',
        'show' => 'dashboard.coupons.show',
        'edit' => 'dashboard.coupons.edit',
        'update' => 'dashboard.coupons.update',
        'destroy' => 'dashboard.coupons.destroy',
    ]);
    Route::resource('cashback',  \App\Http\Controllers\Dashboard\CashbackController::class)->names([
        'index' => 'dashboard.cashback.index',
        'create' => 'dashboard.cashback.create',
        'store' => 'dashboard.cashback.store',
        'show' => 'dashboard.cashback.show',
        'edit' => 'dashboard.cashback.edit',
        'update' => 'dashboard.cashback.update',
        'destroy' => 'dashboard.cashback.destroy',
    ]);
    
    // Rotas adicionais para módulos
    Route::prefix('orders')->name('dashboard.orders.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Dashboard\OrdersController::class, 'index'])->name('index');
        Route::get('/{order}', [\App\Http\Controllers\Dashboard\OrdersController::class, 'show'])->name('show');
        Route::post('/{order}/status', [\App\Http\Controllers\Dashboard\OrdersController::class, 'updateStatus'])->name('updateStatus');
        Route::put('/{order}', [\App\Http\Controllers\Dashboard\OrdersController::class, 'update'])->name('update');
        Route::post('/{order}/coupon', [\App\Http\Controllers\Dashboard\OrdersController::class, 'applyCoupon'])->name('applyCoupon');
        Route::delete('/{order}/coupon', [\App\Http\Controllers\Dashboard\OrdersController::class, 'removeCoupon'])->name('removeCoupon');
        Route::post('/{order}/delivery-fee', [\App\Http\Controllers\Dashboard\OrdersController::class, 'adjustDeliveryFee'])->name('adjustDeliveryFee');
        Route::post('/{order}/discount', [\App\Http\Controllers\Dashboard\OrdersController::class, 'applyDiscount'])->name('applyDiscount');
        Route::delete('/{order}/discount', [\App\Http\Controllers\Dashboard\OrdersController::class, 'removeDiscount'])->name('removeDiscount');
        
        // Rotas para edição de itens
        Route::post('/{order}/items', [\App\Http\Controllers\Dashboard\OrdersController::class, 'addItem'])->name('addItem');
        Route::post('/{order}/items/{item}/add', [\App\Http\Controllers\Dashboard\OrdersController::class, 'addItemQuantity'])->name('addItemQuantity');
        Route::post('/{order}/items/{item}/reduce', [\App\Http\Controllers\Dashboard\OrdersController::class, 'reduceItemQuantity'])->name('reduceItemQuantity');
        Route::post('/{order}/items/{item}/quantity', [\App\Http\Controllers\Dashboard\OrdersController::class, 'updateItemQuantity'])->name('updateItemQuantity');
        Route::delete('/{order}/items/{item}', [\App\Http\Controllers\Dashboard\OrdersController::class, 'removeItem'])->name('removeItem');
        Route::get('/{order}/receipt', [\App\Http\Controllers\Dashboard\OrdersController::class, 'receipt'])->name('receipt');
    });
    
    // Alias para manter compatibilidade
    Route::get('/loyalty', [\App\Http\Controllers\Dashboard\LoyaltyController::class, 'index'])->name('dashboard.loyalty');
    Route::get('/reports', [\App\Http\Controllers\Dashboard\ReportsController::class, 'index'])->name('dashboard.reports');
    Route::get('/settings', [\App\Http\Controllers\Dashboard\SettingsController::class, 'index'])->name('dashboard.settings');
    
    Route::get('/reports/export', [ReportsController::class, 'export'])->name('dashboard.reports.export');

    // Configurações
    Route::get('/settings/whatsapp',      [\App\Http\Controllers\Dashboard\SettingsController::class, 'whatsapp'])->name('dashboard.settings.whatsapp');
    Route::post('/settings/whatsapp',     [\App\Http\Controllers\Dashboard\SettingsController::class, 'whatsappSave']);
    Route::get('/settings/mercadopago',   [\App\Http\Controllers\Dashboard\SettingsController::class, 'mp'])->name('dashboard.settings.mp');
    Route::post('/settings/mercadopago',  [\App\Http\Controllers\Dashboard\SettingsController::class, 'mpSave']);
    
    // PDV
    Route::prefix('pdv')->name('dashboard.pdv.')->group(function () {
        Route::get('/',          [\App\Http\Controllers\Dashboard\PDVController::class, 'index'])->name('index');
        Route::post('/calc',     [\App\Http\Controllers\Dashboard\PDVController::class, 'calculate'])->name('calculate');
        Route::post('/order',    [\App\Http\Controllers\Dashboard\PDVController::class, 'store'])->name('store');
    });

});

// Rotas públicas de visual (ambiente local ou sem subdomínio)
// Mapeiam exatamente os caminhos do site clonado para evitar 404
// Adicionar middleware auth para exigir autenticação
Route::prefix('/')->middleware('auth')->group(function () {
    Route::get('/', [\App\Http\Controllers\Dashboard\DashboardController::class, 'home'])->name('dashboard.index');
    Route::get('/pdv', [\App\Http\Controllers\Dashboard\PDVController::class, 'index'])->name('dashboard.pdv.index');
    Route::get('/pedidos', [\App\Http\Controllers\Dashboard\OrdersController::class, 'index'])->name('dashboard.orders.index');
    Route::get('/clientes', [\App\Http\Controllers\Dashboard\CustomersController::class, 'index'])->name('dashboard.customers.index');
    Route::get('/produtos', [\App\Http\Controllers\Dashboard\ProductsController::class, 'index'])->name('dashboard.products.index');
    Route::get('/categorias', [\App\Http\Controllers\Dashboard\CategoriesController::class, 'index'])->name('dashboard.categories.index');
    Route::get('/cupons', [\App\Http\Controllers\Dashboard\CouponsController::class, 'index'])->name('dashboard.coupons.index');
    Route::get('/cashback', [\App\Http\Controllers\Dashboard\CashbackController::class, 'index'])->name('dashboard.cashback.index');
    Route::get('/fidelidade', [\App\Http\Controllers\Dashboard\LoyaltyController::class, 'index'])->name('dashboard.loyalty');
    Route::get('/relatorios', [\App\Http\Controllers\Dashboard\ReportsController::class, 'index'])->name('dashboard.reports');
    Route::get('/whatsapp', [\App\Http\Controllers\Dashboard\SettingsController::class, 'whatsapp'])->name('dashboard.settings.whatsapp');
    Route::get('/mercado-pago', [\App\Http\Controllers\Dashboard\SettingsController::class, 'mp'])->name('dashboard.settings.mp');
    Route::get('/status-templates', function () { return view('dash.pages.settings.status-templates'); })->name('dashboard.settings.status-templates');
});

// Rotas do Cliente (visualização de pedidos)
Route::prefix('customer')->name('customer.')->group(function () {
    Route::get('/orders', [\App\Http\Controllers\Customer\OrdersController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [\App\Http\Controllers\Customer\OrdersController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/rate', [\App\Http\Controllers\Customer\OrdersController::class, 'rate'])->name('orders.rate');
});

// Subdomínio: Pedido (Loja)
Route::domain('pedido.menuolika.com.br')->group(function () {
    Route::get('/', [MenuController::class, 'index'])->name('menu.index');

    Route::prefix('menu')->group(function () {
        Route::get('/',                 [MenuController::class, 'index']);
        Route::get('/categoria/{category}', [MenuController::class, 'category']);
        Route::get('/produto/{product}',    [MenuController::class, 'product']);
    });

    Route::prefix('cart')->group(function () {
        Route::get('/',     [CartController::class, 'show'])->name('cart.index');
        Route::post('/add', [CartController::class, 'add'])->name('cart.add');
        Route::post('/update', [CartController::class, 'update'])->name('cart.update');
    });

    Route::prefix('checkout')->group(function () {
        Route::get('/',   [OrderController::class, 'checkout'])->name('checkout.index');
        Route::post('/',  [OrderController::class, 'store'])->name('checkout.store');
    });

    Route::prefix('payment')->group(function () {
        Route::get('/pix/{order}', [PaymentController::class, 'pixPayment'])->name('payment.pix');
        Route::get('/checkout/{order}', [PaymentController::class, 'checkout'])->name('payment.checkout');
        Route::get('/success/{order}', [PaymentController::class, 'success'])->name('payment.success');
        Route::get('/failure/{order}', [PaymentController::class, 'failure'])->name('payment.failure');
    });

});

// Rotas globais auxiliares
Route::get('/clear-cache-now', function () {
    Artisan::call('optimize:clear');
    return response()->json(['status' => 'success', 'cleared' => true]);
})->name('tools.clear');

Route::prefix('webhooks')->group(function () {
    Route::post('/mercadopago', [WebhookController::class, 'mercadoPago'])->name('webhooks.mercadopago');
});