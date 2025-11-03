<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\PaymentController;

// Rota raiz genérica (detecta subdomínio)
Route::get('/', function () {
    $host = request()->getHost();
    
    // Subdomínio do dashboard
    if (str_contains($host, 'dashboard.menuolika.com.br')) {
        return redirect()->route('dashboard.index');
    }
    
    // Subdomínio do pedido - usar MenuController diretamente (sem redirecionar)
    if (str_contains($host, 'pedido.menuolika.com.br')) {
        return app(MenuController::class)->index();
    }
    
    // Para desenvolvimento/local sem subdomínio
    // Se não tiver subdomínio configurado, redireciona para /pedido (loja pública)
    return redirect()->route('pedido.index');
})->name('home');
use App\Http\Controllers\LoyaltyController;
use App\Http\Controllers\ReferralController;
use App\Http\Controllers\DeliveryFeeController;
use App\Http\Controllers\CouponController;
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

// ============================================
// ROTAS PÚBLICAS DO PEDIDO (SEM AUTENTICAÇÃO)
// DEVEM VIR ANTES DAS ROTAS DO DASHBOARD
// ============================================

// Subdomínio: Pedido (Loja Front-end) - PÚBLICO
Route::domain('pedido.menuolika.com.br')->name('pedido.')->group(function () {
    // Página inicial
    Route::get('/', [MenuController::class, 'index'])->name('index');
    
    // Menu
    Route::prefix('menu')->name('menu.')->group(function () {
        Route::get('/', [MenuController::class, 'index'])->name('index');
        Route::get('/categoria/{category}', [MenuController::class, 'category'])->name('category');
        Route::get('/produto/{product}', [MenuController::class, 'product'])->name('product');
        Route::get('/produto/{product}/json', [MenuController::class, 'productJson'])->name('product.json');
        Route::get('/buscar', [MenuController::class, 'search'])->name('search');
    });
    
            // Carrinho
            Route::prefix('cart')->name('cart.')->group(function () {
                Route::get('/', [CartController::class, 'show'])->name('index');
                Route::get('/count', [CartController::class, 'count'])->name('count');
                Route::get('/items', [CartController::class, 'items'])->name('items');
                Route::get('/ai-suggestions', [CartController::class, 'aiSuggestions'])->name('ai');
                Route::post('/add', [CartController::class, 'add'])->name('add');
                Route::post('/update', [CartController::class, 'update'])->name('update');
                Route::post('/remove', [CartController::class, 'remove'])->name('remove');
                Route::post('/clear', [CartController::class, 'clear'])->name('clear');
                Route::post('/calculate-delivery-fee', [CartController::class, 'calculateDeliveryFee'])->name('calculateDeliveryFee');
            });
    
    // Checkout
    Route::prefix('checkout')->name('checkout.')->group(function () {
        Route::get('/', [OrderController::class, 'checkout'])->name('index');
        Route::post('/', [OrderController::class, 'store'])->name('store');
        Route::post('/calculate-discounts', [OrderController::class, 'calculateDiscounts'])->name('calculate-discounts');
    });
    
    // Finalizar pedido do PDV
    Route::get('/pdv/complete/{order}', [OrderController::class, 'completePdvOrder'])->name('pdv.complete');
    
    // Pagamento
    Route::prefix('payment')->name('payment.')->group(function () {
        Route::get('/pix/{order}', [PaymentController::class, 'pixPayment'])->name('pix');
        Route::get('/checkout/{order}', [PaymentController::class, 'checkout'])->name('checkout');
        Route::get('/status/{order}', [PaymentController::class, 'status'])->name('status');
        Route::get('/success/{order}', [PaymentController::class, 'success'])->name('success');
        Route::get('/failure/{order}', [PaymentController::class, 'failure'])->name('failure');
    });
});

// Rotas públicas equivalentes (sem depender do subdomínio) - fallback PÚBLICO
// IMPORTANTE: Estas rotas funcionam quando o DNS não está configurado com subdomínio
Route::prefix('pedido')->name('pedido.')->group(function () {
    // Página inicial - /pedido/
    Route::get('/', [MenuController::class, 'index'])->name('index');
    
    // Menu - /pedido/menu, /pedido/menu/categoria/{id}, etc.
    Route::prefix('menu')->name('menu.')->group(function () {
        Route::get('/', [MenuController::class, 'index'])->name('index');
        Route::get('/categoria/{category}', [MenuController::class, 'category'])->name('category');
        Route::get('/produto/{product}', [MenuController::class, 'product'])->name('product');
        Route::get('/produto/{product}/json', [MenuController::class, 'productJson'])->name('product.json');
        Route::get('/buscar', [MenuController::class, 'search'])->name('search');
    });
    
            // Carrinho - /pedido/cart, /pedido/cart/add, etc.
            Route::prefix('cart')->name('cart.')->group(function () {
                Route::get('/', [CartController::class, 'show'])->name('index');
                Route::get('/count', [CartController::class, 'count'])->name('count');
                Route::get('/items', [CartController::class, 'items'])->name('items');
                Route::get('/ai-suggestions', [CartController::class, 'aiSuggestions'])->name('ai');
                Route::post('/add', [CartController::class, 'add'])->name('add');
                Route::post('/update', [CartController::class, 'update'])->name('update');
                Route::post('/remove', [CartController::class, 'remove'])->name('remove');
                Route::post('/clear', [CartController::class, 'clear'])->name('clear');
                Route::post('/calculate-delivery-fee', [CartController::class, 'calculateDeliveryFee'])->name('calculateDeliveryFee');
            });
    
    // Checkout - /pedido/checkout
    Route::prefix('checkout')->name('checkout.')->group(function () {
        Route::get('/', [OrderController::class, 'checkout'])->name('index');
        Route::post('/', [OrderController::class, 'store'])->name('store');
        Route::post('/calculate-discounts', [OrderController::class, 'calculateDiscounts'])->name('calculate-discounts');
    });
    
    // Finalizar pedido do PDV - /pedido/pdv/complete/{order}
    Route::get('/pdv/complete/{order}', [OrderController::class, 'completePdvOrder'])->name('pdv.complete');
    
    // Adicionar também calculate-discounts no grupo sem subdomínio
    Route::post('/checkout/calculate-discounts', [OrderController::class, 'calculateDiscounts'])->name('checkout.calculate-discounts');
    
    // Pagamento - /pedido/payment/pix/{order}, etc.
    Route::prefix('payment')->name('payment.')->group(function () {
        Route::get('/pix/{order}', [PaymentController::class, 'pixPayment'])->name('pix');
        Route::get('/checkout/{order}', [PaymentController::class, 'checkout'])->name('checkout');
        Route::get('/success/{order}', [PaymentController::class, 'success'])->name('success');
        Route::get('/failure/{order}', [PaymentController::class, 'failure'])->name('failure');
    });
});

// ============================================
// ROTAS DO DASHBOARD (REQUEREM AUTENTICAÇÃO)
// ============================================

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
            // Rotas auxiliares para gerenciar imagens dos produtos
            Route::prefix('products/{product}')->name('dashboard.products.')->group(function () {
                Route::post('/duplicate', [\App\Http\Controllers\Dashboard\ProductsController::class, 'duplicate'])->name('duplicate');
                Route::delete('/images/{image}', [\App\Http\Controllers\Dashboard\ProductsController::class, 'deleteImage'])->name('images.delete');
                Route::post('/images/{image}/set-primary', [\App\Http\Controllers\Dashboard\ProductsController::class, 'setPrimaryImage'])->name('images.set-primary');
                Route::post('/images/reorder', [\App\Http\Controllers\Dashboard\ProductsController::class, 'reorderImages'])->name('images.reorder');
                Route::delete('/variants/{variant}', [\App\Http\Controllers\Dashboard\ProductsController::class, 'destroyVariant'])->name('variants.destroy');
            });
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
    Route::post('categories/update-product', [\App\Http\Controllers\Dashboard\CategoriesController::class, 'updateProductCategory'])->name('dashboard.categories.update-product');
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
    Route::post('cashback/settings', [\App\Http\Controllers\Dashboard\CashbackController::class, 'saveSettings'])->name('dashboard.cashback.settings.save');
    
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
        Route::get('/{order}/fiscal-receipt', [\App\Http\Controllers\Dashboard\OrdersController::class, 'fiscalReceipt'])->name('fiscalReceipt');
        Route::get('/{order}/fiscal-receipt/escpos', [\App\Http\Controllers\Dashboard\OrdersController::class, 'fiscalReceiptEscPos'])->name('fiscalReceiptEscPos');
    });
    
    // Alias para manter compatibilidade
    Route::get('/loyalty', [\App\Http\Controllers\Dashboard\LoyaltyController::class, 'index'])->name('dashboard.loyalty');
    Route::resource('loyalty', \App\Http\Controllers\Dashboard\LoyaltyController::class)->names([
        'index' => 'dashboard.loyalty.index',
        'create' => 'dashboard.loyalty.create',
        'store' => 'dashboard.loyalty.store',
        'edit' => 'dashboard.loyalty.edit',
        'update' => 'dashboard.loyalty.update',
        'destroy' => 'dashboard.loyalty.destroy',
    ]);
    Route::post('loyalty/settings', [\App\Http\Controllers\Dashboard\LoyaltyController::class, 'saveSettings'])->name('dashboard.loyalty.settings.save');
    Route::get('/reports', [\App\Http\Controllers\Dashboard\ReportsController::class, 'index'])->name('dashboard.reports');
    Route::get('/settings', [\App\Http\Controllers\Dashboard\SettingsController::class, 'index'])->name('dashboard.settings');
    
    Route::get('/reports/export', [ReportsController::class, 'export'])->name('dashboard.reports.export');

    // Configurações
    Route::get('/settings/whatsapp',      [\App\Http\Controllers\Dashboard\SettingsController::class, 'whatsapp'])->name('dashboard.settings.whatsapp');
    Route::post('/settings/whatsapp',     [\App\Http\Controllers\Dashboard\SettingsController::class, 'whatsappSave'])->name('dashboard.settings.whatsapp.save');
    Route::post('/settings/whatsapp/notifications', [\App\Http\Controllers\Dashboard\SettingsController::class, 'whatsappNotificationsSave'])->name('dashboard.settings.whatsapp.notifications.save');
    Route::get('/settings/mercadopago',   [\App\Http\Controllers\Dashboard\SettingsController::class, 'mp'])->name('dashboard.settings.mp');
    Route::post('/settings/mercadopago',  [\App\Http\Controllers\Dashboard\SettingsController::class, 'mpSave'])->name('dashboard.settings.mp.save');
    Route::post('/settings/mercadopago/methods',  [\App\Http\Controllers\Dashboard\SettingsController::class, 'mpMethodsSave'])->name('dashboard.settings.mp.methods.save');
    Route::post('/settings/apis',         [\App\Http\Controllers\Dashboard\SettingsController::class, 'apisSave'])->name('dashboard.settings.apis.save');
    Route::get('/settings/status-templates', [\App\Http\Controllers\Dashboard\OrderStatusController::class, 'index'])->name('dashboard.status-templates.index');
    Route::post('/settings/status-templates/status/{id}', [\App\Http\Controllers\Dashboard\OrderStatusController::class, 'updateStatus'])->name('dashboard.status-templates.status.update');
    Route::post('/settings/status-templates/template', [\App\Http\Controllers\Dashboard\OrderStatusController::class, 'saveTemplate'])->name('dashboard.status-templates.template.save');
    Route::delete('/settings/status-templates/template/{id}', [\App\Http\Controllers\Dashboard\OrderStatusController::class, 'deleteTemplate'])->name('dashboard.status-templates.template.delete');
    Route::get('/settings/status-templates/template/{id}', [\App\Http\Controllers\Dashboard\OrderStatusController::class, 'getTemplate'])->name('dashboard.status-templates.template.get');
    
    // Configurações: Dias e horários de entrega
    Route::prefix('settings/entrega')->name('dashboard.settings.delivery.')->group(function () {
        Route::get('/agendamentos', [\App\Http\Controllers\Dashboard\DeliverySchedulesController::class, 'index'])->name('schedules.index');
        Route::post('/agendamentos', [\App\Http\Controllers\Dashboard\DeliverySchedulesController::class, 'store'])->name('schedules.store');
        Route::put('/agendamentos/{schedule}', [\App\Http\Controllers\Dashboard\DeliverySchedulesController::class, 'update'])->name('schedules.update');
        Route::delete('/agendamentos/{schedule}', [\App\Http\Controllers\Dashboard\DeliverySchedulesController::class, 'destroy'])->name('schedules.destroy');
    });
    
    // PDV
    Route::prefix('pdv')->name('dashboard.pdv.')->group(function () {
        Route::get('/',          [\App\Http\Controllers\Dashboard\PDVController::class, 'index'])->name('index');
        Route::post('/calc',     [\App\Http\Controllers\Dashboard\PDVController::class, 'calculate'])->name('calculate');
        Route::post('/order',    [\App\Http\Controllers\Dashboard\PDVController::class, 'store'])->name('store');
        Route::post('/send',     [\App\Http\Controllers\Dashboard\PDVController::class, 'send'])->name('send');
    });

    // Taxas de entrega por distância
    Route::prefix('delivery-pricing')->name('dashboard.delivery-pricing.')->group(function(){
        Route::get('/', [\App\Http\Controllers\Dashboard\DeliveryPricingController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\Dashboard\DeliveryPricingController::class, 'store'])->name('store');
        Route::post('/simulate', [\App\Http\Controllers\Dashboard\DeliveryPricingController::class, 'simulate'])->name('simulate');
        Route::put('/{pricing}', [\App\Http\Controllers\Dashboard\DeliveryPricingController::class, 'update'])->name('update');
        Route::delete('/{pricing}', [\App\Http\Controllers\Dashboard\DeliveryPricingController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('tools')->name('dashboard.tools.')->group(function(){
        Route::get('/import-ingredients', [\App\Http\Controllers\Dashboard\ToolsController::class, 'importIngredients'])->name('import-ingredients');
        Route::get('/flush', [\App\Http\Controllers\Dashboard\ToolsController::class, 'flushCaches'])->name('flush');
    });

});

// Rotas do dashboard vinculadas ao subdomínio correto
Route::domain('dashboard.menuolika.com.br')->middleware('auth')->group(function () {
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
    Route::get('/whatsapp', [\App\Http\Controllers\Dashboard\SettingsController::class, 'whatsapp'])->name('dashboard.settings.whatsapp.alias');
    Route::get('/mercado-pago', [\App\Http\Controllers\Dashboard\SettingsController::class, 'mp'])->name('dashboard.settings.mp.alias');
            Route::get('/status-templates', function () { return view('dashboard.settings.status-templates'); })->name('dashboard.settings.status-templates.alias');
    Route::post('/settings/apis',         [\App\Http\Controllers\Dashboard\SettingsController::class, 'apisSave'])->name('dashboard.settings.apis.save');
});

// Fallback: Rotas do dashboard SEM subdomínio (útil quando DNS ainda não aponta)
Route::prefix('dashboard')->middleware('auth')->group(function () {
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
    Route::get('/status-templates', function () { return view('dashboard.settings.status-templates'); })->name('dashboard.settings.status-templates');
    Route::post('/settings/apis', [\App\Http\Controllers\Dashboard\SettingsController::class, 'apisSave'])->name('dashboard.settings.apis.save');
    
    // Rotas de produtos (resource e auxiliares)
    Route::resource('products', \App\Http\Controllers\Dashboard\ProductsController::class)->names([
        'index' => 'dashboard.products.index',
        'create' => 'dashboard.products.create',
        'store' => 'dashboard.products.store',
        'show' => 'dashboard.products.show',
        'edit' => 'dashboard.products.edit',
        'update' => 'dashboard.products.update',
        'destroy' => 'dashboard.products.destroy',
    ]);
    
    // Rotas auxiliares para gerenciar imagens e variantes dos produtos
    Route::prefix('products/{product}')->name('dashboard.products.')->group(function () {
        Route::post('/duplicate', [\App\Http\Controllers\Dashboard\ProductsController::class, 'duplicate'])->name('duplicate');
        Route::delete('/images/{image}', [\App\Http\Controllers\Dashboard\ProductsController::class, 'deleteImage'])->name('images.delete');
        Route::post('/images/{image}/set-primary', [\App\Http\Controllers\Dashboard\ProductsController::class, 'setPrimaryImage'])->name('images.set-primary');
        Route::post('/images/reorder', [\App\Http\Controllers\Dashboard\ProductsController::class, 'reorderImages'])->name('images.reorder');
        Route::delete('/variants/{variant}', [\App\Http\Controllers\Dashboard\ProductsController::class, 'destroyVariant'])->name('variants.destroy');
    });
});

// Rotas do Cliente (visualização de pedidos) - PÚBLICAS
Route::prefix('customer')->name('customer.')->group(function () {
    Route::get('/orders', [\App\Http\Controllers\Customer\OrdersController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [\App\Http\Controllers\Customer\OrdersController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/rate', [\App\Http\Controllers\Customer\OrdersController::class, 'rate'])->name('orders.rate');
});

// Rota para servir arquivos da pasta storage/app/public sem necessidade de symlink
Route::get('/storage/{path}', function (string $path) {
    if (!Storage::disk('public')->exists($path)) {
        abort(404);
    }
    return Storage::disk('public')->response($path);
})->where('path', '.*');

// Rotas globais auxiliares
Route::get('/clear-cache-now', function () {
    Artisan::call('optimize:clear');
    return response()->json(['status' => 'success', 'cleared' => true]);
})->name('tools.clear');

Route::prefix('webhooks')->group(function () {
    Route::post('/mercadopago', [WebhookController::class, 'mercadoPago'])->name('webhooks.mercadopago');
});