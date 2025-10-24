<?php

use Illuminate\Support\Facades\Route;

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

// ======================================================================
// 1) DASHBOARD — subdomínio: dashboard.menuolika.com.br
//    Rotas de administração ficam restritas a esse host.
// ======================================================================
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

// ======================================================================
// 2) LOJA — subdomínio: pedido.menuolika.com.br
//    (Opcional) Duplicamos as rotas da loja com prefixo de nome "pedido.*"
//    para você ter nomes distintos sem conflito com as globais.
// ======================================================================
Route::domain('pedido.menuolika.com.br')->name('pedido.')->group(function () {

    // Página inicial - redireciona para o cardápio
    Route::get('/', function () {
        return redirect()->route('pedido.menu.index');
    })->name('home');

    // Rotas do cardápio
    Route::prefix('menu')->name('menu.')->group(function () {
        Route::get('/', [MenuController::class, 'index'])->name('index');
        Route::get('/categoria/{category}', [MenuController::class, 'category'])->name('category');
        Route::get('/produto/{product}', [MenuController::class, 'product'])->name('product');
        Route::get('/buscar', [MenuController::class, 'search'])->name('search');
    });

    // Rotas do carrinho
    Route::prefix('cart')->name('cart.')->group(function () {
        Route::get('/', [CartController::class, 'index'])->name('index');
        Route::get('/count', [CartController::class, 'getCount'])->name('count');
        Route::post('/add', [CartController::class, 'add'])->name('add');
        Route::put('/update', [CartController::class, 'update'])->name('update');
        Route::delete('/remove', [CartController::class, 'remove'])->name('remove');
        Route::delete('/clear', [CartController::class, 'clear'])->name('clear');
    });

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
});

// ======================================================================
// 3) LOJA — rotas globais (domínio principal /sistema/public)
//     -> são exatamente as SUAS rotas originais, intactas
// ======================================================================

// Página inicial - redireciona para o cardápio
Route::get('/', function () {
    return redirect()->route('menu.index');
});

// Rotas do cardápio
Route::prefix('menu')->name('menu.')->group(function () {
    Route::get('/', [MenuController::class, 'index'])->name('index');
    Route::get('/categoria/{category}', [MenuController::class, 'category'])->name('category');
    Route::get('/produto/{product}', [MenuController::class, 'product'])->name('product');
    Route::get('/buscar', [MenuController::class, 'search'])->name('search');
});

// Rotas do carrinho
Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::get('/count', [CartController::class, 'getCount'])->name('count');
    Route::post('/add', [CartController::class, 'add'])->name('add');
    Route::put('/update', [CartController::class, 'update'])->name('update');
    Route::delete('/remove', [CartController::class, 'remove'])->name('remove');
    Route::delete('/clear', [CartController::class, 'clear'])->name('clear');
});

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

// ======================================================================
// 4) WEBHOOKS — globais (use a mesma URL configurada no provedor)
// ======================================================================
Route::prefix('webhooks')->name('webhooks.')->group(function () {
    Route::post('/mercadopago', [WebhookController::class, 'mercadoPago'])->name('mercadopago');
    Route::post('/whatsapp', [WebhookController::class, 'whatsApp'])->name('whatsapp');
});
