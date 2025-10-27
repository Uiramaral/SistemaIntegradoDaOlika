<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MenuApiController;
use App\Http\Controllers\Api\OrderApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Rotas da API do cardápio
Route::prefix('menu')->name('api.menu.')->group(function () {
    Route::get('/categories', [MenuApiController::class, 'categories'])->name('categories');
    Route::get('/products', [MenuApiController::class, 'products'])->name('products');
    Route::get('/products/featured', [MenuApiController::class, 'featured'])->name('featured');
    Route::get('/products/search', [MenuApiController::class, 'search'])->name('search');
    Route::get('/category/{category}/products', [MenuApiController::class, 'categoryProducts'])->name('category.products');
    Route::get('/product/{product}', [MenuApiController::class, 'product'])->name('product');
});

// Rotas da API de pedidos
Route::prefix('orders')->name('api.orders.')->group(function () {
    Route::get('/', [OrderApiController::class, 'index'])->name('index');
    Route::get('/customer', [OrderApiController::class, 'customerOrders'])->name('customer');
    Route::get('/{order}', [OrderApiController::class, 'show'])->name('show');
    Route::put('/{order}/status', [OrderApiController::class, 'updateStatus'])->name('update.status');
});

// Rotas da API de fidelidade
Route::prefix('loyalty')->name('api.loyalty.')->group(function () {
    Route::get('/points', [\App\Http\Controllers\LoyaltyController::class, 'getCustomerPoints'])->name('points');
    Route::post('/redeem', [\App\Http\Controllers\LoyaltyController::class, 'redeemPoints'])->name('redeem');
});

// Rotas da API de indicação
Route::prefix('referral')->name('api.referral.')->group(function () {
    Route::get('/code', [\App\Http\Controllers\ReferralController::class, 'getReferralCode'])->name('code');
    Route::post('/validate', [\App\Http\Controllers\ReferralController::class, 'validateReferralCode'])->name('validate');
    Route::post('/create', [\App\Http\Controllers\ReferralController::class, 'create'])->name('create');
});

// Rotas da API de taxa de entrega
Route::prefix('delivery-fee')->name('api.delivery-fee.')->group(function () {
    Route::post('/adjust/{order}', [\App\Http\Controllers\DeliveryFeeController::class, 'adjustFee'])->name('adjust');
    Route::post('/discount/{order}', [\App\Http\Controllers\DeliveryFeeController::class, 'applyDiscount'])->name('discount');
    Route::post('/free/{order}', [\App\Http\Controllers\DeliveryFeeController::class, 'setFreeDelivery'])->name('free');
    Route::post('/revert/{order}', [\App\Http\Controllers\DeliveryFeeController::class, 'revertToCalculated'])->name('revert');
    Route::get('/history/{order}', [\App\Http\Controllers\DeliveryFeeController::class, 'getAdjustmentHistory'])->name('history');
    Route::get('/stats', [\App\Http\Controllers\DeliveryFeeController::class, 'getStats'])->name('stats');
    Route::get('/calculate/{order}', [\App\Http\Controllers\DeliveryFeeController::class, 'calculateFee'])->name('calculate');
    Route::get('/adjustments', [\App\Http\Controllers\DeliveryFeeController::class, 'getOrdersWithAdjustments'])->name('adjustments');
});

// Rotas da API de cupons
Route::prefix('coupons')->name('api.coupons.')->group(function () {
    Route::get('/', [\App\Http\Controllers\CouponController::class, 'getVisibleCoupons'])->name('index');
    Route::get('/validate', [\App\Http\Controllers\CouponController::class, 'validateCoupon'])->name('validate');
    Route::post('/', [\App\Http\Controllers\CouponController::class, 'create'])->name('create');
    Route::put('/{coupon}', [\App\Http\Controllers\CouponController::class, 'update'])->name('update');
    Route::delete('/{coupon}', [\App\Http\Controllers\CouponController::class, 'delete'])->name('delete');
    Route::get('/stats', [\App\Http\Controllers\CouponController::class, 'getStats'])->name('stats');
    Route::get('/admin', [\App\Http\Controllers\CouponController::class, 'adminIndex'])->name('admin');
});

// Rotas da API de pagamento
Route::prefix('payment')->name('api.payment.')->group(function () {
    Route::post('/pix/{order}', [\App\Http\Controllers\PaymentController::class, 'createPix'])->name('pix');
    Route::post('/preference/{order}', [\App\Http\Controllers\PaymentController::class, 'createPreference'])->name('preference');
    Route::get('/status/{order}', [\App\Http\Controllers\PaymentController::class, 'getPaymentStatus'])->name('status');
    Route::get('/config', [\App\Http\Controllers\PaymentController::class, 'getPublicConfig'])->name('config');
});

// Webhooks da API
Route::prefix('webhooks')->name('api.webhooks.')->group(function () {
    Route::post('/mercadopago', [\App\Http\Controllers\WebhookController::class, 'mercadoPago'])->name('mercadopago');
    Route::post('/whatsapp', [\App\Http\Controllers\WebhookController::class, 'whatsApp'])->name('whatsapp');
});
