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
    Route::post('/validate', [\App\Http\Controllers\CouponController::class, 'validate'])->name('validate');
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

// Rotas do PDV
Route::prefix('pdv')->name('api.pdv.')->group(function () {
    Route::get('/customers/search', [\App\Http\Controllers\Dashboard\PDVController::class, 'searchCustomers'])->name('customers.search');
    Route::post('/customers', [\App\Http\Controllers\Dashboard\PDVController::class, 'storeCustomer'])->name('customers.store');
    Route::get('/products/search', [\App\Http\Controllers\Dashboard\PDVController::class, 'searchProducts'])->name('products.search');
    Route::post('/coupons/validate', [\App\Http\Controllers\Dashboard\PDVController::class, 'validateCoupon'])->name('coupons.validate');
    Route::post('/orders', [\App\Http\Controllers\Dashboard\PDVController::class, 'storeOrder'])->name('orders.store');
    
    // Rota de cálculo de frete por distância
    Route::get('/calc-frete', function(\Illuminate\Http\Request $r){
        $pdv = app(\App\Http\Controllers\Dashboard\PDVController::class);
        $addr = [
            'cep' => $r->get('cep'),
            'street' => $r->get('street'),
            'number' => $r->get('number'),
            'neighborhood' => $r->get('neighborhood'),
            'city' => $r->get('city'),
            'state' => $r->get('state'),
            'latitude' => $r->get('lat'),
            'longitude' => $r->get('lng'),
        ];
        $fee = $pdv->computeDeliveryFeeByDistance($addr, (float)$r->get('subtotal',0));
        return response()->json(['fee' => $fee]);
    })->name('calc-frete');
});

// Rotas de fiados
Route::prefix('fiados')->name('api.fiados.')->group(function () {
    Route::get('/saldo', [\App\Http\Controllers\DebtsController::class, 'balance'])->name('balance');
    Route::post('/{debt}/baixa', [\App\Http\Controllers\DebtsController::class, 'settle'])->name('settle');
});
