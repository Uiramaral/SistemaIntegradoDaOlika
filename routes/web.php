<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\PaymentController;

$primaryDomain = parse_url(config('app.url', 'https://menuolika.com.br'), PHP_URL_HOST) ?: 'menuolika.com.br';

// Detectar ambiente baseado no host acessado
$currentHost = request()->getHost();
$isDevDomain = str_contains($currentHost, 'devpedido.') || str_contains($currentHost, 'devdashboard.');

if ($isDevDomain) {
    // Desenvolvimento: usar subdomínios de dev
    $pedidoDomain = env('PEDIDO_DOMAIN', 'devpedido.' . $primaryDomain);
    $dashboardDomain = env('DASHBOARD_DOMAIN', 'devdashboard.' . $primaryDomain);
} else {
    // Produção: usar subdomínios padrão
    $pedidoDomain = env('PEDIDO_DOMAIN', 'pedido.' . $primaryDomain);
    $dashboardDomain = env('DASHBOARD_DOMAIN', 'dashboard.' . $primaryDomain);
}

// Debug: Log dos domínios detectados (remover em produção se necessário)
// \Log::info('Rotas configuradas', [
//     'current_host' => $currentHost,
//     'is_dev_domain' => $isDevDomain,
//     'pedido_domain' => $pedidoDomain,
//     'dashboard_domain' => $dashboardDomain,
// ]);

use App\Http\Controllers\LoyaltyController;
use App\Http\Controllers\ReferralController;
use App\Http\Controllers\DeliveryFeeController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\PedidosBulkController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\BotConversaController;

// ============================================
// API BotConversa - Sincronização de clientes (sem CSRF)
// CRÍTICO: Deve vir PRIMEIRO, antes de TUDO, incluindo autenticação e subdomínios
// Isso garante que as rotas da API não sejam interceptadas por outras rotas
// IMPORTANTE: Essas rotas são globais e funcionam em QUALQUER domínio/subdomínio
// Essas rotas devem funcionar tanto em menuolika.com.br quanto em pedido.menuolika.com.br
// ============================================

// Rotas específicas (sem prefix group) para garantir máxima prioridade
// IMPORTANTE: Estas rotas são globais e não dependem de subdomínio
Route::get('/api/botconversa/ping', function() {
    $host = request()->getHost();
    return response()->json([
        'status' => 'ok',
        'message' => 'API BotConversa está respondendo (domínio principal)',
        'host' => $host,
        'timestamp' => date('Y-m-d H:i:s'),
    ]);
})->name('api.botconversa.ping.main');

Route::get('/api/botconversa/test', [BotConversaController::class, 'test'])->name('api.botconversa.test.get.main');
Route::get('/api/botconversa', [BotConversaController::class, 'test'])->name('api.botconversa.test.main');
Route::post('/api/botconversa/sync-customer', [BotConversaController::class, 'syncCustomer'])->name('api.botconversa.sync-customer.main');
Route::post('/api/botconversa/sync-customers', [BotConversaController::class, 'syncCustomersBatch'])->name('api.botconversa.sync-customers.main');

// API Status da IA (para controle condicional do Gateway Node.js)
// Método POST para segurança (token no header, dados no body)
// IMPORTANTE: Rota global, funciona em qualquer domínio/subdomínio
Route::post('/api/ai-status', [\App\Http\Controllers\AiStatusController::class, 'checkStatus'])
    ->name('api.ai.status');

// API Contexto do Cliente (para injeção de dados dinâmicos no prompt da IA)
// Método POST para segurança (token no header, dados no body)
// IMPORTANTE: Rota global, funciona em qualquer domínio/subdomínio
Route::post('/api/customer-context', [\App\Http\Controllers\Api\CustomerSearchController::class, 'getContext'])
    ->name('api.customer.context');

// Também manter o grupo para consistência (mas as rotas específicas acima têm prioridade)
Route::prefix('api/botconversa')->name('api.botconversa.')->group(function () {
    Route::get('/ping', function() {
        $host = request()->getHost();
        return response()->json([
            'status' => 'ok',
            'message' => 'API BotConversa está respondendo (via group)',
            'host' => $host,
            'timestamp' => date('Y-m-d H:i:s'),
        ]);
    })->name('ping.group');
    
    Route::get('/', [BotConversaController::class, 'test'])->name('test.group');
    Route::get('/test', [BotConversaController::class, 'test'])->name('test.get.group');
    Route::post('/sync-customer', [BotConversaController::class, 'syncCustomer'])->name('sync-customer.group');
    Route::post('/sync-customers', [BotConversaController::class, 'syncCustomersBatch'])->name('sync-customers.group');
});

// Autenticação
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('auth.login');
Route::post('/logout', [LoginController::class, 'logout'])->name('auth.logout');
Route::get('/logout', [LoginController::class, 'logout'])->name('auth.logout.get');

// Registro de novos administradores
Route::get('/register', [RegisterController::class, 'showForm'])->name('register.form');
Route::post('/register', [RegisterController::class, 'register'])->name('register');

// ============================================
// ROTA PARA SERVIR ARQUIVOS DO STORAGE
// DEVE VIR ANTES DE TUDO PARA FUNCIONAR EM TODOS OS SUBDOMÍNIOS
// ============================================
// Rota para servir arquivos da pasta storage/app/public
// Funciona como fallback se o symlink não estiver funcionando corretamente
Route::get('/storage/{path}', function (string $path) {
    try {
        // Decodificar path (pode conter barras e caracteres especiais)
        $path = urldecode($path);
        
        // Remover barras iniciais
        $path = ltrim($path, '/');
        
        $disk = Storage::disk('public');
        
        if (!$disk->exists($path)) {
            \Log::warning('Arquivo não encontrado no storage', [
                'path' => $path,
                'full_path' => $disk->path($path),
                'storage_root' => storage_path('app/public'),
            ]);
            abort(404, 'Arquivo não encontrado: ' . $path);
        }
        
        $filePath = $disk->path($path);
        
        // Verificar se o arquivo realmente existe
        if (!file_exists($filePath)) {
            \Log::error('Arquivo não existe fisicamente', [
                'path' => $path,
                'file_path' => $filePath,
            ]);
            abort(404, 'Arquivo não encontrado fisicamente');
        }
        
        $mimeType = $disk->mimeType($path) ?: 'application/octet-stream';
        
        return response()->file($filePath, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'public, max-age=31536000', // Cache de 1 ano
        ]);
    } catch (\Exception $e) {
        \Log::error('Erro ao servir arquivo do storage', [
            'path' => $path ?? 'unknown',
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        abort(404, 'Erro ao servir arquivo: ' . $e->getMessage());
    }
})->where('path', '.*')->name('storage.serve');

// ============================================
// ROTAS PÚBLICAS DO PEDIDO (SEM AUTENTICAÇÃO)
// DEVEM VIR ANTES DAS ROTAS DO DASHBOARD
// ============================================

// Rotas do Cliente (visualização de pedidos) - GLOBAIS (funcionam em qualquer subdomínio)
// IMPORTANTE: Estas rotas devem estar ANTES das rotas com domínio específico
Route::prefix('customer')->group(function () {
    Route::post('/orders/request-token', [\App\Http\Controllers\Customer\OrdersController::class, 'requestAccessToken'])
        ->middleware('throttle:3,1') // 3 tentativas por minuto
        ->name('customer.orders.request-token');
    Route::get('/orders', [\App\Http\Controllers\Customer\OrdersController::class, 'index'])->name('customer.orders.index');
    Route::get('/orders/{order}', [\App\Http\Controllers\Customer\OrdersController::class, 'show'])->name('customer.orders.show');
    Route::post('/orders/{order}/rate', [\App\Http\Controllers\Customer\OrdersController::class, 'rate'])->name('customer.orders.rate');
});

// ============================================
// ROTAS DO DASHBOARD (REQUEREM AUTENTICAÇÃO)
// IMPORTANTE: Devem vir ANTES das rotas do pedido para garantir prioridade
// ============================================

// Subdomínio: Dashboard (produção e desenvolvimento)
// Rotas públicas do dashboard (login, teste, etc.)
Route::domain($dashboardDomain)->group(function () {
    // Rota de teste SEM autenticação para diagnosticar
    Route::get('/test-dashboard-access', function() {
        return response()->json([
            'status' => 'success',
            'message' => 'Dashboard domain está funcionando!',
            'host' => request()->getHost(),
            'authenticated' => auth()->check(),
            'user' => auth()->user() ? auth()->user()->id : null,
        ]);
    })->name('dashboard.test-access');
    
    // Rotas de autenticação também devem funcionar no subdomínio
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('dashboard.login');
    Route::post('/login', [LoginController::class, 'login'])->name('dashboard.auth.login');
});

// Rotas do dashboard COM autenticação
Route::domain($dashboardDomain)->middleware('auth')->group(function () {
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
            // Rota para gerar SEO via IA
            Route::post('products/generate-seo', [\App\Http\Controllers\Dashboard\ProductsController::class, 'generateSEO'])->name('dashboard.products.generateSEO');
            
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
    Route::post('/customers/{customer}/send-pending-orders', [\App\Http\Controllers\Dashboard\DebtsController::class, 'sendPendingOrdersSummary'])->name('dashboard.customers.sendPendingOrders');
    Route::post('/customers/debts/{debt}/settle', [\App\Http\Controllers\Dashboard\DebtsController::class, 'settleDebt'])->name('dashboard.customers.debts.settle');
    Route::post('/customers/update-stats', [\App\Http\Controllers\Dashboard\CustomersController::class, 'updateStats'])->name('dashboard.customers.updateStats');
    Route::put('/customers/{customer}/cashback', [\App\Http\Controllers\Dashboard\CustomersController::class, 'updateCashback'])->name('dashboard.customers.updateCashback');
    Route::post('/customers/{customer}/adjust-debt-balance', [\App\Http\Controllers\Dashboard\CustomersController::class, 'adjustDebtBalance'])->name('dashboard.customers.adjustDebtBalance');
    Route::resource('wholesale-prices', \App\Http\Controllers\Dashboard\WholesalePricesController::class)->names([
        'index' => 'dashboard.wholesale-prices.index',
        'create' => 'dashboard.wholesale-prices.create',
        'store' => 'dashboard.wholesale-prices.store',
        'edit' => 'dashboard.wholesale-prices.edit',
        'update' => 'dashboard.wholesale-prices.update',
        'destroy' => 'dashboard.wholesale-prices.destroy',
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
        Route::get('/new-orders', [\App\Http\Controllers\Dashboard\OrdersController::class, 'getNewOrders'])->name('newOrders');
        Route::get('/printer-monitor', [\App\Http\Controllers\Dashboard\OrdersController::class, 'printerMonitor'])->name('printerMonitor');
        Route::get('/orders-for-print', [\App\Http\Controllers\Dashboard\OrdersController::class, 'getOrdersForPrint'])->name('forPrint'); // ANTES de /{order}
        Route::get('/{order}', [\App\Http\Controllers\Dashboard\OrdersController::class, 'show'])->name('show');
        Route::post('/{order}/status', [\App\Http\Controllers\Dashboard\OrdersController::class, 'updateStatus'])->name('updateStatus');
        Route::put('/{order}', [\App\Http\Controllers\Dashboard\OrdersController::class, 'update'])->name('update');
        Route::post('/{order}/coupon', [\App\Http\Controllers\Dashboard\OrdersController::class, 'applyCoupon'])->name('applyCoupon');
        Route::delete('/{order}/coupon', [\App\Http\Controllers\Dashboard\OrdersController::class, 'removeCoupon'])->name('removeCoupon');
        Route::post('/{order}/delivery-fee', [\App\Http\Controllers\Dashboard\OrdersController::class, 'adjustDeliveryFee'])->name('adjustDeliveryFee');
        Route::post('/{order}/discount', [\App\Http\Controllers\Dashboard\OrdersController::class, 'applyDiscount'])->name('applyDiscount');
        Route::delete('/{order}/discount', [\App\Http\Controllers\Dashboard\OrdersController::class, 'removeDiscount'])->name('removeDiscount');
        Route::post('/{order}/refund', [\App\Http\Controllers\Dashboard\OrdersController::class, 'refund'])->name('refund');
        Route::post('/{order}/send-receipt', [\App\Http\Controllers\Dashboard\OrdersController::class, 'sendReceipt'])->name('sendReceipt');
        
        // Rotas para edição de itens
        Route::post('/{order}/items', [\App\Http\Controllers\Dashboard\OrdersController::class, 'addItem'])->name('addItem');
        Route::post('/{order}/items/{item}/add', [\App\Http\Controllers\Dashboard\OrdersController::class, 'addItemQuantity'])->name('addItemQuantity');
        Route::post('/{order}/items/{item}/reduce', [\App\Http\Controllers\Dashboard\OrdersController::class, 'reduceItemQuantity'])->name('reduceItemQuantity');
        Route::post('/{order}/items/{item}/quantity', [\App\Http\Controllers\Dashboard\OrdersController::class, 'updateItemQuantity'])->name('updateItemQuantity');
        Route::delete('/{order}/items/{item}', [\App\Http\Controllers\Dashboard\OrdersController::class, 'removeItem'])->name('removeItem');
        Route::get('/{order}/receipt', [\App\Http\Controllers\Dashboard\OrdersController::class, 'receipt'])->name('receipt');
        Route::get('/{order}/fiscal-receipt', [\App\Http\Controllers\Dashboard\OrdersController::class, 'fiscalReceipt'])->name('fiscalReceipt');
        Route::get('/{order}/fiscal-receipt/escpos', [\App\Http\Controllers\Dashboard\OrdersController::class, 'fiscalReceiptEscPos'])->name('fiscalReceiptEscPos');
        Route::post('/{order}/request-print', [\App\Http\Controllers\Dashboard\OrdersController::class, 'requestPrint'])->name('requestPrint');
        Route::post('/{order}/mark-printed', [\App\Http\Controllers\Dashboard\OrdersController::class, 'markAsPrinted'])->name('markPrinted');
        Route::post('/{order}/confirm-mercadopago', [\App\Http\Controllers\Dashboard\OrdersController::class, 'confirmMercadoPagoStatus'])->name('confirmMercadoPagoStatus');
        Route::post('/{order}/register-debit', [\App\Http\Controllers\Dashboard\OrdersController::class, 'registerAsDebit'])->name('registerAsDebit');
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

    // Entregas (painel do entregador)
    Route::get('/deliveries', [\App\Http\Controllers\Dashboard\DeliveryController::class, 'index'])->name('dashboard.deliveries.index');
    Route::post('/deliveries/{order}/status', [\App\Http\Controllers\Dashboard\DeliveryController::class, 'updateStatus'])->name('dashboard.deliveries.status');

    // Configurações
    Route::get('/settings/whatsapp',      [\App\Http\Controllers\Dashboard\SettingsController::class, 'whatsapp'])->name('dashboard.settings.whatsapp');
    Route::post('/settings/whatsapp',     [\App\Http\Controllers\Dashboard\SettingsController::class, 'whatsappSave'])->name('dashboard.settings.whatsapp.save');
    Route::post('/settings/whatsapp/notifications', [\App\Http\Controllers\Dashboard\SettingsController::class, 'whatsappNotificationsSave'])->name('dashboard.settings.whatsapp.notifications.save');
    
    // Rotas para mensagens falhadas do WhatsApp
    Route::get('/whatsapp/failed-messages', [\App\Http\Controllers\Dashboard\WhatsAppFailedMessagesController::class, 'index'])->name('dashboard.whatsapp.failed-messages');
    Route::post('/whatsapp/failed-messages/{id}/retry', [\App\Http\Controllers\Dashboard\WhatsAppFailedMessagesController::class, 'retry'])->name('dashboard.whatsapp.failed-messages.retry');
    Route::get('/whatsapp/failed-messages/pending-count', [\App\Http\Controllers\Dashboard\WhatsAppFailedMessagesController::class, 'getPendingCount'])->name('dashboard.whatsapp.failed-messages.pending-count');
    Route::get('/settings/whatsapp/qr',   [\App\Http\Controllers\Dashboard\SettingsController::class, 'whatsappQR'])->name('dashboard.settings.whatsapp.qr');
    Route::get('/settings/whatsapp/status', [\App\Http\Controllers\Dashboard\SettingsController::class, 'whatsappStatus'])->name('dashboard.settings.whatsapp.status');
    Route::post('/settings/whatsapp/connect', [\App\Http\Controllers\Dashboard\SettingsController::class, 'whatsappConnect'])->name('dashboard.settings.whatsapp.connect');
    Route::post('/settings/whatsapp/clear-auth', [\App\Http\Controllers\Dashboard\SettingsController::class, 'whatsappClearAuth'])->name('dashboard.settings.whatsapp.clear-auth');
    Route::post('/settings/whatsapp/disconnect', [\App\Http\Controllers\Dashboard\SettingsController::class, 'whatsappDisconnect'])->name('dashboard.settings.whatsapp.disconnect');
    
    // Gerenciamento de Instâncias WhatsApp (Multi-instâncias)
    Route::prefix('whatsapp/instances')->name('dashboard.whatsapp.instances.')->group(function () {
        Route::get('/', [\App\Http\Controllers\WhatsappInstanceController::class, 'index'])->name('index');
        Route::get('/{id}', [\App\Http\Controllers\WhatsappInstanceController::class, 'show'])->name('show');
        Route::post('/', [\App\Http\Controllers\WhatsappInstanceController::class, 'store'])->name('store');
        Route::put('/{id}', [\App\Http\Controllers\WhatsappInstanceController::class, 'update'])->name('update');
        Route::post('/{id}/connect', [\App\Http\Controllers\WhatsappInstanceController::class, 'connect'])->name('connect');
    });

    // Campanhas em Massa WhatsApp
    Route::prefix('whatsapp/campaigns')->name('dashboard.whatsapp.campaigns.')->group(function () {
        Route::get('/', [\App\Http\Controllers\WhatsappCampaignController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\WhatsappCampaignController::class, 'store'])->name('store');
    });
    
    // WhatsApp (rotas alternativas para /dashboard/whatsapp)
    Route::get('/whatsapp/qr',   [\App\Http\Controllers\Dashboard\SettingsController::class, 'whatsappQR'])->name('dashboard.whatsapp.qr');
    Route::get('/whatsapp/status', [\App\Http\Controllers\Dashboard\SettingsController::class, 'whatsappStatus'])->name('dashboard.whatsapp.status');
    Route::get('/whatsapp/settings', [\App\Http\Controllers\Dashboard\SettingsController::class, 'whatsappSettingsApi'])->name('dashboard.whatsapp.settings');
    Route::post('/whatsapp/connect', [\App\Http\Controllers\Dashboard\SettingsController::class, 'whatsappConnect'])->name('dashboard.whatsapp.connect');
    Route::post('/whatsapp/clear-auth', [\App\Http\Controllers\Dashboard\SettingsController::class, 'whatsappClearAuth'])->name('dashboard.whatsapp.clear-auth');
    Route::post('/whatsapp/disconnect', [\App\Http\Controllers\Dashboard\SettingsController::class, 'whatsappDisconnect'])->name('dashboard.whatsapp.disconnect');
    Route::post('/whatsapp',     [\App\Http\Controllers\Dashboard\SettingsController::class, 'whatsappSave'])->name('dashboard.whatsapp.save');
    Route::post('/whatsapp/notifications', [\App\Http\Controllers\Dashboard\SettingsController::class, 'whatsappNotificationsSave'])->name('dashboard.whatsapp.notifications.save');
    Route::get('/settings/mercadopago',   [\App\Http\Controllers\Dashboard\SettingsController::class, 'mp'])->name('dashboard.settings.mp');
    Route::post('/settings/mercadopago',  [\App\Http\Controllers\Dashboard\SettingsController::class, 'mpSave'])->name('dashboard.settings.mp.save');
    Route::post('/settings/mercadopago/methods',  [\App\Http\Controllers\Dashboard\SettingsController::class, 'mpMethodsSave'])->name('dashboard.settings.mp.methods.save');
    Route::post('/settings/apis',         [\App\Http\Controllers\Dashboard\SettingsController::class, 'apisSave'])->name('dashboard.settings.apis.save');
    Route::get('/settings/status-templates', [\App\Http\Controllers\Dashboard\OrderStatusController::class, 'index'])->name('dashboard.settings.status-templates');
    Route::post('/settings/status-templates/status/{id}', [\App\Http\Controllers\Dashboard\OrderStatusController::class, 'updateStatus'])->name('dashboard.settings.status-templates.status.update');
    Route::post('/settings/status-templates/template', [\App\Http\Controllers\Dashboard\OrderStatusController::class, 'saveTemplate'])->name('dashboard.settings.status-templates.template.save');
    Route::delete('/settings/status-templates/template/{id}', [\App\Http\Controllers\Dashboard\OrderStatusController::class, 'deleteTemplate'])->name('dashboard.settings.status-templates.template.delete');
    Route::get('/settings/status-templates/template/{id}', [\App\Http\Controllers\Dashboard\OrderStatusController::class, 'getTemplate'])->name('dashboard.settings.status-templates.template.get');
    
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
        Route::post('/search-order', [\App\Http\Controllers\Dashboard\PDVController::class, 'searchOrder'])->name('searchOrder');
        Route::post('/orders/{order}/confirm-payment-silent', [\App\Http\Controllers\Dashboard\PDVController::class, 'confirmPaymentSilent'])->name('confirmPaymentSilent');
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

// Subdomínio: Pedido (Loja Front-end) - PÚBLICO
Route::domain($pedidoDomain)->name('pedido.')->group(function () {
    // API BotConversa também funciona no subdomínio (redundância para garantir)
    Route::prefix('api/botconversa')->name('api.botconversa.')->group(function () {
        Route::get('/ping', function() {
            return response()->json([
                'status' => 'ok',
                'message' => 'API BotConversa está respondendo (subdomínio pedido)',
                'host' => request()->getHost(),
                'timestamp' => date('Y-m-d H:i:s'),
            ]);
        })->name('ping.pedido');
        
        Route::get('/', [BotConversaController::class, 'test'])->name('test.pedido');
        Route::get('/test', [BotConversaController::class, 'test'])->name('test.get.pedido');
        Route::post('/sync-customer', [BotConversaController::class, 'syncCustomer'])->name('sync-customer.pedido');
        Route::post('/sync-customers', [BotConversaController::class, 'syncCustomersBatch'])->name('sync-customers.pedido');
    });
    
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
        Route::post('/lookup-customer', [OrderController::class, 'lookupCustomer'])->name('lookup-customer');
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
        Route::post('/locate-address', [OrderController::class, 'locateAddress'])->name('locate-address');
    });
    
    // Finalizar pedido do PDV - /pedido/pdv/complete/{order}
    Route::get('/pdv/complete/{order}', [OrderController::class, 'completePdvOrder'])->name('pdv.complete');
    
    // Adicionar também calculate-discounts no grupo sem subdomínio
    Route::post('/checkout/calculate-discounts', [OrderController::class, 'calculateDiscounts'])->name('checkout.calculate-discounts');
    Route::post('/checkout/locate-address', [OrderController::class, 'locateAddress'])->name('checkout.locate-address');
    
    // Pagamento - /pedido/payment/pix/{order}, etc.
    Route::prefix('payment')->name('payment.')->group(function () {
        Route::get('/pix/{order}', [PaymentController::class, 'pixPayment'])->name('pix');
        Route::get('/checkout/{order}', [PaymentController::class, 'checkout'])->name('checkout');
        Route::get('/status/{order}', [PaymentController::class, 'status'])->name('status');
        Route::get('/success/{order}', [PaymentController::class, 'success'])->name('success');
        Route::get('/failure/{order}', [PaymentController::class, 'failure'])->name('failure');
    });
});

// Fallback: Rotas do dashboard SEM subdomínio (útil quando DNS ainda não aponta)
Route::prefix('dashboard')->middleware('auth')->group(function () {
    Route::get('/', [\App\Http\Controllers\Dashboard\DashboardController::class, 'home'])->name('dashboard.index');
    Route::get('/pdv', [\App\Http\Controllers\Dashboard\PDVController::class, 'index'])->name('dashboard.pdv.index');
    Route::get('/pedidos', [\App\Http\Controllers\Dashboard\OrdersController::class, 'index'])->name('dashboard.orders.index');
    Route::get('/clientes', [\App\Http\Controllers\Dashboard\CustomersController::class, 'index'])->name('dashboard.customers.index');
    Route::post('/clientes/update-stats', [\App\Http\Controllers\Dashboard\CustomersController::class, 'updateStats'])->name('dashboard.customers.updateStats');
    Route::get('/produtos', [\App\Http\Controllers\Dashboard\ProductsController::class, 'index'])->name('dashboard.products.index');
    Route::get('/categorias', [\App\Http\Controllers\Dashboard\CategoriesController::class, 'index'])->name('dashboard.categories.index');
    Route::get('/cupons', [\App\Http\Controllers\Dashboard\CouponsController::class, 'index'])->name('dashboard.coupons.index');
    Route::resource('wholesale-prices', \App\Http\Controllers\Dashboard\WholesalePricesController::class)->names([
        'index' => 'dashboard.wholesale-prices.index',
        'create' => 'dashboard.wholesale-prices.create',
        'store' => 'dashboard.wholesale-prices.store',
        'edit' => 'dashboard.wholesale-prices.edit',
        'update' => 'dashboard.wholesale-prices.update',
        'destroy' => 'dashboard.wholesale-prices.destroy',
    ]);
    Route::get('/precos-revenda', function () {
        return redirect()->route('dashboard.wholesale-prices.index');
    })->name('dashboard.wholesale-prices.alias');
    Route::get('/cashback', [\App\Http\Controllers\Dashboard\CashbackController::class, 'index'])->name('dashboard.cashback.index');
    Route::get('/fidelidade', [\App\Http\Controllers\Dashboard\LoyaltyController::class, 'index'])->name('dashboard.loyalty');
    Route::get('/relatorios', [\App\Http\Controllers\Dashboard\ReportsController::class, 'index'])->name('dashboard.reports');
    Route::get('/configuracoes', [\App\Http\Controllers\Dashboard\SettingsController::class, 'index'])->name('dashboard.settings');
    Route::get('/entregas', [\App\Http\Controllers\Dashboard\DeliveryController::class, 'index'])->name('dashboard.deliveries.index');
    Route::post('/entregas/{order}/status', [\App\Http\Controllers\Dashboard\DeliveryController::class, 'updateStatus'])->name('dashboard.deliveries.status');
    Route::get('/whatsapp', [\App\Http\Controllers\Dashboard\SettingsController::class, 'whatsapp'])->name('dashboard.settings.whatsapp');
    
    // Gerenciamento de Instâncias WhatsApp (Multi-instâncias) - Fallback
    Route::prefix('whatsapp/instances')->name('dashboard.whatsapp.instances.')->group(function () {
        Route::get('/', [\App\Http\Controllers\WhatsappInstanceController::class, 'index'])->name('index');
        Route::get('/{id}', [\App\Http\Controllers\WhatsappInstanceController::class, 'show'])->name('show');
        Route::post('/', [\App\Http\Controllers\WhatsappInstanceController::class, 'store'])->name('store');
        Route::put('/{id}', [\App\Http\Controllers\WhatsappInstanceController::class, 'update'])->name('update');
        Route::post('/{id}/connect', [\App\Http\Controllers\WhatsappInstanceController::class, 'connect'])->name('connect');
    });
    
    Route::get('/mercado-pago', [\App\Http\Controllers\Dashboard\SettingsController::class, 'mp'])->name('dashboard.settings.mp');
    Route::get('/status-templates', [\App\Http\Controllers\Dashboard\OrderStatusController::class, 'index'])->name('dashboard.settings.status-templates');
    Route::post('/status-templates/status/{id}', [\App\Http\Controllers\Dashboard\OrderStatusController::class, 'updateStatus'])->name('dashboard.settings.status-templates.status.update');
    Route::post('/status-templates/template', [\App\Http\Controllers\Dashboard\OrderStatusController::class, 'saveTemplate'])->name('dashboard.settings.status-templates.template.save');
    Route::delete('/status-templates/template/{id}', [\App\Http\Controllers\Dashboard\OrderStatusController::class, 'deleteTemplate'])->name('dashboard.settings.status-templates.template.delete');
    Route::get('/status-templates/template/{id}', [\App\Http\Controllers\Dashboard\OrderStatusController::class, 'getTemplate'])->name('dashboard.settings.status-templates.template.get');
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
    
    // Rotas de orders no fallback (incluindo orders-for-print)
    Route::prefix('orders')->name('dashboard.orders.')->group(function () {
        Route::get('/orders-for-print', [\App\Http\Controllers\Dashboard\OrdersController::class, 'getOrdersForPrint'])->name('forPrint');
        Route::get('/printer-monitor', [\App\Http\Controllers\Dashboard\OrdersController::class, 'printerMonitor'])->name('printerMonitor');
        Route::get('/{order}/fiscal-receipt/escpos', [\App\Http\Controllers\Dashboard\OrdersController::class, 'fiscalReceiptEscPos'])->name('fiscalReceiptEscPos');
        Route::post('/{order}/request-print', [\App\Http\Controllers\Dashboard\OrdersController::class, 'requestPrint'])->name('requestPrint');
        Route::post('/{order}/mark-printed', [\App\Http\Controllers\Dashboard\OrdersController::class, 'markAsPrinted'])->name('markPrinted');
        Route::post('/{order}/register-debit', [\App\Http\Controllers\Dashboard\OrdersController::class, 'registerAsDebit'])->name('registerAsDebit');
    });
    
    // Configurações: Dias e horários de entrega (fallback)
    Route::prefix('entrega')->name('dashboard.settings.delivery.')->group(function () {
        Route::get('/agendamentos', [\App\Http\Controllers\Dashboard\DeliverySchedulesController::class, 'index'])->name('schedules.index');
        Route::post('/agendamentos', [\App\Http\Controllers\Dashboard\DeliverySchedulesController::class, 'store'])->name('schedules.store');
        Route::put('/agendamentos/{schedule}', [\App\Http\Controllers\Dashboard\DeliverySchedulesController::class, 'update'])->name('schedules.update');
        Route::delete('/agendamentos/{schedule}', [\App\Http\Controllers\Dashboard\DeliverySchedulesController::class, 'destroy'])->name('schedules.destroy');
    });
    
    // Taxas de entrega por distância (fallback)
    Route::prefix('taxas-entrega')->name('dashboard.delivery-pricing.')->group(function(){
        Route::get('/', [\App\Http\Controllers\Dashboard\DeliveryPricingController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\Dashboard\DeliveryPricingController::class, 'store'])->name('store');
        Route::post('/simulate', [\App\Http\Controllers\Dashboard\DeliveryPricingController::class, 'simulate'])->name('simulate');
        Route::put('/{pricing}', [\App\Http\Controllers\Dashboard\DeliveryPricingController::class, 'update'])->name('update');
        Route::delete('/{pricing}', [\App\Http\Controllers\Dashboard\DeliveryPricingController::class, 'destroy'])->name('destroy');
    });
});

// Rota para criar symlink do storage (acessar via navegador)
Route::get('/create-storage-link', function () {
    try {
        $link = public_path('storage');
        $target = storage_path('app/public');
        
        // Verificar se o target existe
        if (!is_dir($target)) {
            // Criar diretório target se não existir
            if (!mkdir($target, 0755, true)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Não foi possível criar o diretório target: ' . $target,
                ], 500);
            }
        }
        
        // Se já existe algo em $link
        if (file_exists($link)) {
            // Se é um symlink, remover
            if (is_link($link)) {
                unlink($link);
            } 
            // Se é um diretório, remover recursivamente
            elseif (is_dir($link)) {
                // Tentar remover o diretório
                if (!File::deleteDirectory($link)) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Não foi possível remover o diretório existente. Remova manualmente: ' . $link,
                        'link' => $link,
                        'target' => $target,
                        'exists' => true,
                        'is_link' => false,
                        'is_dir' => is_dir($link),
                    ], 500);
                }
            }
            // Se é um arquivo, remover
            else {
                unlink($link);
            }
        }
        
        // Criar diretório public se não existir
        if (!is_dir(public_path())) {
            mkdir(public_path(), 0755, true);
        }
        
        // Criar symlink
        if (symlink($target, $link)) {
            return response()->json([
                'status' => 'success',
                'message' => 'Symlink criado com sucesso!',
                'link' => $link,
                'target' => $target,
                'verified' => is_link($link) && file_exists($link),
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao criar symlink. Verifique permissões.',
                'link' => $link,
                'target' => $target,
                'target_exists' => is_dir($target),
                'public_exists' => is_dir(public_path()),
            ], 500);
        }
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Erro: ' . $e->getMessage(),
            'trace' => config('app.debug') ? $e->getTraceAsString() : null,
        ], 500);
    }
})->name('tools.create-storage-link');


// Rota de teste para verificar URLs de assets
Route::get('/test-assets', function () {
    $request = request();
    
    // Verificar se os arquivos existem fisicamente
    $publicPath = public_path();
    $jsPath = $publicPath . '/js/olika-cart.js';
    $cssPath = $publicPath . '/css/olika.css';
    $imagePath = $publicPath . '/images/logo-olika.png';
    
    return response()->json([
        'current_host' => $request->getHost(),
        'current_url' => $request->url(),
        'app_url' => config('app.url'),
        'asset_url' => config('app.asset_url'),
        'test_asset_js' => asset('js/olika-cart.js'),
        'test_asset_css' => asset('css/olika.css'),
        'test_asset_image' => asset('images/logo-olika.png'),
        'test_storage' => asset('storage/uploads/products/test.jpg'),
        'url_root' => url('/'),
        'storage_disk_url' => config('filesystems.disks.public.url'),
        'files_exist' => [
            'js_olika_cart' => file_exists($jsPath),
            'css_olika' => file_exists($cssPath),
            'image_logo' => file_exists($imagePath),
            'public_path' => $publicPath,
            'public_exists' => is_dir($publicPath),
        ],
        'public_path' => $publicPath,
        'js_files' => is_dir($publicPath . '/js') ? array_slice(scandir($publicPath . '/js'), 2) : 'directory_not_found',
        'css_files' => is_dir($publicPath . '/css') ? array_slice(scandir($publicPath . '/css'), 2) : 'directory_not_found',
    ]);
})->name('tools.test-assets');

// Rotas globais auxiliares
Route::get('/clear-cache-now', function () {
    Artisan::call('optimize:clear');
    return response()->json(['status' => 'success', 'cleared' => true]);
})->name('tools.clear');

// Rota de teste para integração WhatsApp (protegida por autenticação)
Route::middleware('auth')->get('/test-whatsapp-notification', function () {
    try {
        $pedido = \App\Models\Order::with(['customer', 'items.product', 'address'])
            ->whereHas('customer', function($q) {
                $q->whereNotNull('phone')->where('phone', '!=', '');
            })
            ->latest()
            ->first();
        
        if (!$pedido) {
            return response()->json([
                'error' => 'Nenhum pedido com cliente e telefone encontrado para teste.',
                'suggestion' => 'Crie um pedido de teste com um cliente que tenha telefone cadastrado.'
            ], 404);
        }
        
        // Disparar evento de teste
        event(new \App\Events\OrderStatusUpdated($pedido, 'order_created', 'Teste de integração WhatsApp'));
        
        return response()->json([
            'success' => true,
            'message' => 'Evento OrderStatusUpdated disparado com sucesso!',
            'order' => [
                'id' => $pedido->id,
                'number' => $pedido->order_number,
                'customer' => $pedido->customer->name ?? 'N/A',
                'phone' => $pedido->customer->phone ?? 'N/A',
            ],
            'webhook_url' => config('notifications.wa_webhook_url'),
            'webhook_configured' => !empty(config('notifications.wa_webhook_url')),
            'note' => 'Verifique os logs do Laravel (storage/logs/laravel.log) e do Railway para confirmar o envio.'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Erro ao disparar evento de teste',
            'message' => $e->getMessage(),
            'trace' => config('app.debug') ? $e->getTraceAsString() : null
        ], 500);
    }
})->name('tools.test-whatsapp');

// Rota de teste para diagnosticar problemas de subdomínio
Route::get('/test-dashboard-route', function() use ($dashboardDomain, $pedidoDomain) {
    $currentHost = request()->getHost();
    $isDevDomain = str_contains($currentHost, 'devpedido.') || str_contains($currentHost, 'devdashboard.');
    
    return response()->json([
        'current_host' => $currentHost,
        'is_dev_domain' => $isDevDomain,
        'dashboard_domain' => $dashboardDomain,
        'pedido_domain' => $pedidoDomain,
        'matches_dashboard' => $currentHost === $dashboardDomain,
        'matches_pedido' => $currentHost === $pedidoDomain,
        'trust_hosts_allowed' => true, // Verificar manualmente no TrustHosts
        'routes_cached' => file_exists(base_path('bootstrap/cache/routes-v7.php')),
    ]);
})->name('tools.test-dashboard-route');

Route::prefix('webhooks')->group(function () {
    Route::post('/mercadopago', [WebhookController::class, 'mercadoPago'])->name('webhooks.mercadopago');
});

// ============================================
// ROTA RAIZ GENÉRICA (FALLBACK)
// IMPORTANTE: Esta rota deve vir POR ÚLTIMO para não interferir com Route::domain()
// ============================================
// Rota raiz genérica (fallback APENAS para domínio principal sem subdomínio)
// Ela só é executada quando o host NÃO corresponde a nenhum subdomínio configurado
Route::get('/', function () use ($dashboardDomain, $pedidoDomain, $primaryDomain) {
    $host = request()->getHost();

    // Se o host corresponde exatamente a um dos domínios configurados,
    // as rotas Route::domain() devem ter tratado - se chegou aqui, algo está errado
    if ($host === $dashboardDomain || $host === $pedidoDomain) {
        // Se chegou aqui, significa que nenhuma rota Route::domain() foi encontrada
        // Isso não deveria acontecer, mas vamos redirecionar para login se for dashboard
        if ($host === $dashboardDomain) {
            return redirect()->route('dashboard.login');
        }
        // Se for pedido, redirecionar para a página inicial
        if ($host === $pedidoDomain) {
            return redirect()->route('pedido.index');
        }
    }
    
    // Apenas para domínio principal ou localhost
    if ($host === $primaryDomain || $host === 'localhost' || $host === '127.0.0.1') {
        return redirect()->route('pedido.index');
    }

    // Se chegou aqui e não é nenhum dos domínios esperados, 
    // provavelmente é um subdomínio não configurado
    abort(404);
})->name('home');