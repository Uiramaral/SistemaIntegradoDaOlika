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

// Novo domínio para multi-tenancy
$cozinhaDomain = 'cozinhapro.app.br';

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
use App\Http\Controllers\ProfileController;

// ============================================
// ROTAS MULTI-TENANT (cozinhapro.app.br)
// ============================================

// 1. LOJA DO CLIENTE (Ex: padaria-do-jose.cozinhapro.app.br)
Route::domain('{slug}.' . $cozinhaDomain)->middleware(['identify.tenant'])->group(function () {
    Route::get('/', [MenuController::class, 'index'])->name('store.index');
    Route::get('/menu', [MenuController::class, 'index'])->name('store.menu');
    Route::get('/produto/{product}', [MenuController::class, 'product'])->name('store.menu.product'); // Renamed to match prefix logic
    Route::get('/produto/{product}/modal', [MenuController::class, 'productModal'])->name('store.menu.product.modal');
    Route::get('/produto/{product}/json', [MenuController::class, 'productJson'])->name('store.menu.product.json');
    Route::get('/categoria/{category}', [MenuController::class, 'category'])->name('store.menu.category');
    Route::get('/buscar', [MenuController::class, 'search'])->name('store.menu.search');

    // Carrinho
    Route::prefix('cart')->name('store.cart.')->group(function () {
        Route::get('/', [CartController::class, 'show'])->name('index');
        Route::get('/count', [CartController::class, 'count'])->name('count');
        Route::get('/items', [CartController::class, 'items'])->name('items');
        Route::post('/add', [CartController::class, 'add'])->name('add');
        Route::post('/update', [CartController::class, 'update'])->name('update');
        Route::post('/remove', [CartController::class, 'remove'])->name('remove');
        Route::post('/clear', [CartController::class, 'clear'])->name('clear');
        Route::post('/calculate-delivery-fee', [CartController::class, 'calculateDeliveryFee'])->name('calculateDeliveryFee');
    });

    // Checkout
    Route::prefix('checkout')->name('store.checkout.')->group(function () {
        Route::get('/', [OrderController::class, 'checkout'])->name('index');
        Route::post('/', [OrderController::class, 'store'])->name('store');
        Route::post('/calculate-discounts', [OrderController::class, 'calculateDiscounts'])->name('calculate-discounts');
        Route::post('/lookup-customer', [OrderController::class, 'lookupCustomer'])->name('lookup-customer');
    });

    // Payment
    Route::prefix('payment')->name('store.payment.')->group(function () {
        Route::get('/pix/{order}', [PaymentController::class, 'pixPayment'])->name('pix');
        Route::get('/checkout/{order}', [PaymentController::class, 'checkout'])->name('checkout');
        Route::get('/status/{order}', [PaymentController::class, 'status'])->name('status');
        Route::get('/success/{order}', [PaymentController::class, 'success'])->name('success');
        Route::get('/failure/{order}', [PaymentController::class, 'failure'])->name('failure');
    });
});

// 2. DASHBOARD DO ASSINANTE (Ex: dashboard.cozinhapro.app.br)
Route::domain('dashboard.' . $cozinhaDomain)->group(function () {
    // Rotas públicas
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('cozinha.dashboard.login');
    Route::post('/login', [LoginController::class, 'login'])->name('cozinha.dashboard.auth.login');

    // Rotas autenticadas
    Route::middleware('auth')->group(function () {
        Route::get('/', [\App\Http\Controllers\Dashboard\DashboardController::class, 'home'])->name('cozinha.dashboard.index');

        // Gerenciamento de Perfil e Slug
        Route::get('/perfil', [ProfileController::class, 'index'])->name('cozinha.profile.index');
        Route::post('/perfil/update-slug', [ProfileController::class, 'updateSlug'])->name('cozinha.profile.slug.update');
        Route::post('/perfil/check-slug', [ProfileController::class, 'checkSlugAvailability'])->name('cozinha.profile.slug.check');
        Route::post('/perfil/update', [ProfileController::class, 'update'])->name('cozinha.profile.update');
    });
});

// 3. LANDING PAGE (cozinhapro.app.br)
Route::domain($cozinhaDomain)->group(function () {
    Route::get('/', function () {
        return view('site.cozinha-home');
    })->name('cozinha.home');
});

// ============================================
// API BotConversa - Sincronização de clientes (sem CSRF)
// CRÍTICO: Deve vir PRIMEIRO, antes de TUDO, incluindo autenticação e subdomínios
// Isso garante que as rotas da API não sejam interceptadas por outras rotas
// IMPORTANTE: Essas rotas são globais e funcionam em QUALQUER domínio/subdomínio
// Essas rotas devem funcionar tanto em menuolika.com.br quanto em pedido.menuolika.com.br
// ============================================

// Rotas específicas (sem prefix group) para garantir máxima prioridade
// IMPORTANTE: Estas rotas são globais e não dependem de subdomínio
Route::get('/api/botconversa/ping', function () {
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

// API para Node.js WhatsApp Gateway buscar dados do cliente
Route::get('/api/client/{id}', [\App\Http\Controllers\Dashboard\SettingsController::class, 'getClientData'])->name('api.client.get');

// ============================================
// ROTA PARA LIMPAR CACHE (uso temporário)
// Acesse: /clear-cache?key=olika2024
// ============================================
Route::get('/clear-cache', function () {
    $key = request()->get('key');
    if ($key !== 'olika2024') {
        return response()->json(['error' => 'Chave inválida'], 403);
    }

    // Limpar caches do Laravel
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
    Artisan::call('route:clear');
    Artisan::call('view:clear');

    $results = ['config', 'cache', 'route', 'view'];

    // Limpar OPCache do PHP (CRÍTICO para aplicar mudanças no código!)
    if (function_exists('opcache_reset')) {
        opcache_reset();
        $results[] = 'opcache';
    }


    // Limpar cache de estatísticas de arquivos
    if (function_exists('clearstatcache')) {
        clearstatcache(true);
        $results[] = 'statcache';
    }

    // Tocar nos arquivos para forçar recompilação
    $filesToTouch = [
        app_path('Http/Controllers/Dashboard/OrdersController.php'),
        base_path('routes/web.php'),
    ];

    $touched = [];
    foreach ($filesToTouch as $file) {
        if (file_exists($file)) {
            touch($file);
            $touched[] = basename($file);
        }
    }

    if (count($touched) > 0) {
        $results[] = 'touched: ' . implode(', ', $touched);
    }

    return response()->json([
        'success' => true,
        'message' => '🔥 Cache limpo com sucesso! Código atualizado e OPCache resetado.',
        'cleared' => $results,
        'opcache_enabled' => function_exists('opcache_reset'),
        'php_version' => PHP_VERSION,
        'timestamp' => now()->toDateTimeString(),
    ]);
})->name('clear-cache');

// ROTA TEMPORÁRIA PARA SINCRONIZAÇÃO FINANCEIRA
// ROTA DE DIAGNÓSTICO E CORREÇÃO FINANCEIRA
Route::get('/admin/fix-revenue', function () {
    $days = request()->get('days', 30); // Padrão 30 dias para não poluir
    $startDate = now()->subDays((int) $days);

    // Buscar TODOS os pedidos recentes, não só os pagos, para diagnosticar
    $orders = \App\Models\Order::where('created_at', '>=', $startDate)
        ->orderBy('created_at', 'desc')
        ->with('customer')
        ->get();

    $results = [];
    $processedCount = 0;

    foreach ($orders as $order) {
        // Verificar se já tem transação
        $exists = \App\Models\FinancialTransaction::withoutGlobalScopes()
            ->where('order_id', $order->id)
            ->where('type', 'revenue')
            ->exists();

        $status = strtolower($order->payment_status ?? 'vazio');
        $isPaid = in_array($status, ['paid', 'approved']);

        $action = 'Ignorado';
        $reason = '';

        // Tentar identificar o client_id correto para exibição
        $resolvedClientId = $order->client_id;
        if (!$resolvedClientId && $order->customer)
            $resolvedClientId = $order->customer->client_id;

        if ($exists) {
            // RETROATIVO: Se existe transação mas o pedido NÃO está pago, remover (ex: Fiado confirmado que não foi pago ainda)
            if (!$isPaid) {
                \App\Models\FinancialTransaction::withoutGlobalScopes()
                    ->where('order_id', $order->id)
                    ->where('type', 'revenue')
                    ->delete();
                $action = 'REMOVIDO';
                $reason = 'Pedido não está como Pago/Aprovado (Limpeza retroativa)';
            } else {
                $reason = 'Já existe no financeiro e está pago';
            }
        } elseif (!$isPaid) {
            $reason = 'Status de pagamento não é Pago/Aprovado';
        } else {
            // Tentar criar
            if (!$resolvedClientId) {
                // Tentar fallback admin
                $admin = DB::table('users')->where('role', 'admin')->whereNotNull('client_id')->first();
                if ($admin)
                    $resolvedClientId = $admin->client_id;
            }

            if ($resolvedClientId) {
                try {
                    // Lógica de valores
                    $amount = (float) ($order->final_amount ?? $order->total_amount ?? 0);
                    $netAmount = null;
                    if (!empty($order->payment_raw_response)) {
                        $raw = $order->payment_raw_response;
                        if (is_string($raw))
                            $raw = json_decode($raw, true);
                        if (is_array($raw)) {
                            if (isset($raw['transaction_details']['net_received_amount'])) {
                                $netAmount = (float) $raw['transaction_details']['net_received_amount'];
                            } elseif (isset($raw['fee_details']) && is_array($raw['fee_details'])) {
                                $fee = collect($raw['fee_details'])->sum('amount');
                                $netAmount = max(0, $amount - $fee);
                            }
                        }
                    }
                    $finalAmount = $netAmount !== null ? $netAmount : $amount;

                    // Data
                    $date = $order->created_at ?? now();
                    if ($order->payment_method === 'fiado' && $order->updated_at > $order->created_at) {
                        $date = $order->updated_at;
                    }

                    $t = new \App\Models\FinancialTransaction();
                    $t->client_id = $resolvedClientId;
                    $t->type = 'revenue';
                    $t->amount = $finalAmount;
                    $t->description = 'Pedido ' . ($order->order_number ?? '#' . $order->id);
                    $t->transaction_date = $date->format('Y-m-d');
                    $t->category = 'Pedidos';
                    $t->order_id = $order->id;
                    $t->save();

                    $action = 'CRIADO';
                    $reason = 'Recuperado com sucesso';
                    $processedCount++;
                } catch (\Exception $e) {
                    $action = 'ERRO';
                    $reason = $e->getMessage();
                }
            } else {
                $reason = 'Sem client_id definido';
            }
        }

        $results[] = [
            'id' => $order->id,
            'number' => $order->order_number ?? $order->id,
            'date' => $order->created_at ? $order->created_at->format('d/m H:i') : '-',
            'customer' => $order->customer ? $order->customer->name : 'N/A',
            'amount' => $order->final_amount ?? $order->total_amount,
            'payment_status' => $order->payment_status,
            'resolved_client_id' => $resolvedClientId,
            'action' => $action,
            'reason' => $reason
        ];
    }

    // Gerar tabela HTML
    $html = '<html><head><style>body{font-family:sans-serif;padding:20px;} table{border-collapse:collapse;width:100%;} th,td{border:1px solid #ddd;padding:8px;text-align:left;} th{background:#f4f4f4;} .CRIADO{background:#dff0d8;color:green;font-weight:bold;} .ERRO{background:#f2dede;color:red;}</style></head><body>';
    $html .= "<h1>Relatório de Diagnóstico Financeiro (Últimos {$days} dias)</h1>";
    $html .= "<p>Total Recuperado Agora: <strong>{$processedCount}</strong></p>";
    $html .= "<table><thead><tr><th>Pedido</th><th>Data</th><th>Cliente</th><th>Valor</th><th>Status Pagamento</th><th>Client ID</th><th>Ação</th><th>Motivo</th></tr></thead><tbody>";

    foreach ($results as $row) {
        $class = $row['action'] === 'CRIADO' ? 'CRIADO' : ($row['action'] === 'ERRO' ? 'ERRO' : '');
        $html .= "<tr class='{$class}'>";
        $html .= "<td>#{$row['number']}</td>";
        $html .= "<td>{$row['date']}</td>";
        $html .= "<td>{$row['customer']}</td>";
        $html .= "<td>R$ " . number_format((float) $row['amount'], 2, ',', '.') . "</td>";
        $html .= "<td>{$row['payment_status']}</td>";
        $html .= "<td>{$row['resolved_client_id']}</td>";
        $html .= "<td>{$row['action']}</td>";
        $html .= "<td>{$row['reason']}</td>";
        $html .= "</tr>";
    }
    $html .= "</tbody></table></body></html>";

    return response($html);
});


// Também manter o grupo para consistência (mas as rotas específicas acima têm prioridade)
Route::prefix('api/botconversa')->name('api.botconversa.')->group(function () {
    Route::get('/ping', function () {
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

// ============================================
// LANDING PAGE - PÁGINA INSTITUCIONAL DO SAAS
// ============================================
Route::get('/landing', function () {
    return view('landing');
})->name('landing');


// ============================================
// CADASTRO PÚBLICO DE ESTABELECIMENTOS
// URL OFICIAL: /cadastro
// ============================================
// Esta é a ÚNICA rota pública para novos estabelecimentos se cadastrarem
// Exemplos: Lanchonetes, Restaurantes, Pizzarias, etc.
Route::get('/cadastro', [\App\Http\Controllers\StoreSignupController::class, 'show'])->name('store-signup.show');
Route::post('/cadastro', [\App\Http\Controllers\StoreSignupController::class, 'store'])->name('store-signup.store');
Route::post('/cadastro/whatsapp/send-code', [\App\Http\Controllers\StoreSignupController::class, 'sendWhatsAppVerification'])->name('store-signup.whatsapp.send-code');
Route::post('/cadastro/whatsapp/verify-code', [\App\Http\Controllers\StoreSignupController::class, 'verifyWhatsAppCode'])->name('store-signup.whatsapp.verify-code');

// ============================================
// ROTA /register DESABILITADA
// ============================================
// IMPORTANTE: /register NÃO deve ser usada para cadastrar estabelecimentos!
// Ela foi criada por engano e está sendo mantida apenas para compatibilidade
// de links antigos. Redireciona para /cadastro (landing page oficial).
Route::get('/register', function () {
    return redirect()->route('store-signup.show')
        ->with('info', 'Use a página de cadastro oficial para criar seu estabelecimento.');
})->name('register.form');

// POST mantido apenas para compatibilidade com formulários antigos
// TODO: Remover completamente após migração de todos os links
Route::post('/register', function () {
    return redirect()->route('store-signup.show')
        ->with('error', 'Esta rota está desabilitada. Use /cadastro para criar seu estabelecimento.');
})->name('register');

// ============================================
// LANDING PAGE INSTITUCIONAL
// ============================================
// Página de apresentação do sistema para novos clientes
Route::get('/landing', function () {
    return view('landing');
})->name('landing');

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

// Rastreamento de entregas (página pública para o cliente)
Route::get('/tracking/{token}', [\App\Http\Controllers\DeliveryTrackingController::class, 'show'])->name('tracking.show');
Route::get('/tracking/{token}/location', [\App\Http\Controllers\DeliveryTrackingController::class, 'getLocation'])->name('tracking.location');

// ============================================
// ROTAS DO DASHBOARD (REQUEREM AUTENTICAÇÃO)
// IMPORTANTE: Devem vir ANTES das rotas do pedido para garantir prioridade
// ============================================

// Subdomínio: Dashboard (produção e desenvolvimento)
// Rotas públicas do dashboard (login, teste, etc.)
// Manifest dinâmico para PWA (deve estar fora do middleware auth para funcionar)
// Funciona em ambos os domínios
Route::get('/manifest.json', [\App\Http\Controllers\ManifestController::class, 'index'])->name('manifest.json');
Route::domain($dashboardDomain)->get('/manifest.json', [\App\Http\Controllers\ManifestController::class, 'index']);

Route::domain($dashboardDomain)->group(function () {
    // Rota de teste SEM autenticação para diagnosticar
    Route::get('/test-dashboard-access', function () {
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
    Route::resource('products', \App\Http\Controllers\Dashboard\ProductsController::class)->names([
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
    Route::post('wholesale-prices/{id}/toggle-status', [\App\Http\Controllers\Dashboard\WholesalePricesController::class, 'toggleStatus'])->name('dashboard.wholesale-prices.toggle-status');
    Route::resource('wholesale-prices', \App\Http\Controllers\Dashboard\WholesalePricesController::class)->names([
        'index' => 'dashboard.wholesale-prices.index',
        'create' => 'dashboard.wholesale-prices.create',
        'store' => 'dashboard.wholesale-prices.store',
        'edit' => 'dashboard.wholesale-prices.edit',
        'update' => 'dashboard.wholesale-prices.update',
        'destroy' => 'dashboard.wholesale-prices.destroy',
    ]);
    Route::resource('categories', \App\Http\Controllers\Dashboard\CategoriesController::class)->names([
        'index' => 'dashboard.categories.index',
        'create' => 'dashboard.categories.create',
        'store' => 'dashboard.categories.store',
        'show' => 'dashboard.categories.show',
        'edit' => 'dashboard.categories.edit',
        'update' => 'dashboard.categories.update',
        'destroy' => 'dashboard.categories.destroy',
    ]);
    Route::post('categories/update-product', [\App\Http\Controllers\Dashboard\CategoriesController::class, 'updateProductCategory'])->name('dashboard.categories.update-product');
    Route::resource('coupons', \App\Http\Controllers\Dashboard\CouponsController::class)->names([
        'index' => 'dashboard.coupons.index',
        'create' => 'dashboard.coupons.create',
        'store' => 'dashboard.coupons.store',
        'show' => 'dashboard.coupons.show',
        'edit' => 'dashboard.coupons.edit',
        'update' => 'dashboard.coupons.update',
        'destroy' => 'dashboard.coupons.destroy',
    ]);
    Route::resource('cashback', \App\Http\Controllers\Dashboard\CashbackController::class)->names([
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
        Route::get('/modal-products', [\App\Http\Controllers\Dashboard\OrdersController::class, 'modalProducts'])->name('modalProducts');
        Route::get('/delivery-slots', [\App\Http\Controllers\Dashboard\OrdersController::class, 'deliverySlots'])->name('deliverySlots');
        Route::get('/printer-monitor', [\App\Http\Controllers\Dashboard\OrdersController::class, 'printerMonitor'])->name('printerMonitor');
        Route::get('/orders-for-print', [\App\Http\Controllers\Dashboard\OrdersController::class, 'getOrdersForPrint'])->name('forPrint'); // ANTES de /{order}
        Route::post('/batch-status', [\App\Http\Controllers\Dashboard\OrdersController::class, 'batchStatus'])->name('batchStatus');
        Route::post('/clear-old-print-requests', [\App\Http\Controllers\Dashboard\OrdersController::class, 'clearOldPrintRequests'])->name('clearOldPrintRequests');
        Route::get('/{order}', [\App\Http\Controllers\Dashboard\OrdersController::class, 'show'])->name('show');
        Route::get('/{order}/payment-status', [\App\Http\Controllers\Dashboard\OrdersController::class, 'paymentStatus'])->name('paymentStatus');
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
        Route::get('/{order}/check-receipt', [\App\Http\Controllers\Dashboard\OrdersController::class, 'checkReceipt'])->name('checkReceipt');
        Route::get('/{order}/fiscal-receipt/escpos', [\App\Http\Controllers\Dashboard\OrdersController::class, 'fiscalReceiptEscPos'])->name('fiscalReceiptEscPos');
        Route::get('/{order}/check-receipt/escpos', [\App\Http\Controllers\Dashboard\OrdersController::class, 'checkReceiptEscPos'])->name('checkReceiptEscPos'); // Novo: ESC/POS para recibo de conferência
        Route::post('/{order}/request-print', [\App\Http\Controllers\Dashboard\OrdersController::class, 'requestPrint'])->name('requestPrint');
        Route::post('/{order}/request-print-check', [\App\Http\Controllers\Dashboard\OrdersController::class, 'requestPrintCheck'])->name('requestPrintCheck');
        Route::post('/{order}/mark-printed', [\App\Http\Controllers\Dashboard\OrdersController::class, 'markAsPrinted'])->name('markPrinted');
        Route::post('/{order}/confirm-mercadopago', [\App\Http\Controllers\Dashboard\OrdersController::class, 'confirmMercadoPagoStatus'])->name('confirmMercadoPagoStatus');
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

    // Redirect antigo /reports para /financas (unificação de módulos)
    Route::get('/reports', function () {
        return redirect()->route('dashboard.financas.index');
    })->name('dashboard.reports');

    // Finanças - Módulo completo
    Route::prefix('financas')->name('dashboard.financas.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Dashboard\FinancasController::class, 'index'])->name('index');
        Route::get('/relatorio-vendas', [\App\Http\Controllers\Dashboard\FinancasController::class, 'relatorioVendas'])->name('relatorio-vendas');
        Route::get('/api/resumo', [\App\Http\Controllers\Dashboard\FinancasController::class, 'apiResumo'])->name('api.resumo');
        Route::get('/api/chart', [\App\Http\Controllers\Dashboard\FinancasController::class, 'apiChartData'])->name('api.chart');
        Route::post('/', [\App\Http\Controllers\Dashboard\FinancasController::class, 'store'])->name('store');
        Route::put('/{id}', [\App\Http\Controllers\Dashboard\FinancasController::class, 'update'])->name('update');
        Route::delete('/{id}', [\App\Http\Controllers\Dashboard\FinancasController::class, 'destroy'])->name('destroy');
    });

    // Perfil do Usuário e Loja
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'index'])->name('profile.edit');
    Route::patch('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    // Rotas específicas de slug
    Route::patch('/profile/slug', [\App\Http\Controllers\ProfileController::class, 'updateSlug'])->name('cozinha.profile.slug.update');
    Route::get('/profile/check-slug', [\App\Http\Controllers\ProfileController::class, 'checkSlugAvailability'])->name('profile.check.slug');

    Route::get('/settings', [\App\Http\Controllers\Dashboard\SettingsController::class, 'index'])->name('dashboard.settings');
    Route::post('/settings/general', [\App\Http\Controllers\Dashboard\SettingsController::class, 'generalSave'])->name('dashboard.settings.general.save');
    Route::post('/settings/personalization', [\App\Http\Controllers\Dashboard\SettingsController::class, 'personalizationSave'])->name('dashboard.settings.personalization.save');

    Route::get('/reports/export', [ReportsController::class, 'export'])->name('dashboard.reports.export');

    // Entregas (painel do entregador)
    Route::get('/deliveries', [\App\Http\Controllers\Dashboard\DeliveryController::class, 'index'])->name('dashboard.deliveries.index');
    Route::post('/deliveries/{order}/status', [\App\Http\Controllers\Dashboard\DeliveryController::class, 'updateStatus'])->name('dashboard.deliveries.status');

    // Rastreamento de entregas
    Route::post('/deliveries/{order}/tracking/start', [\App\Http\Controllers\DeliveryTrackingController::class, 'start'])->name('dashboard.deliveries.tracking.start');
    Route::post('/deliveries/{order}/tracking/stop', [\App\Http\Controllers\DeliveryTrackingController::class, 'stop'])->name('dashboard.deliveries.tracking.stop');
    Route::post('/deliveries/{order}/tracking/update', [\App\Http\Controllers\DeliveryTrackingController::class, 'updateLocation'])->name('dashboard.deliveries.tracking.update');

    // Configurações
    Route::get('/settings/whatsapp', [\App\Http\Controllers\Dashboard\SettingsController::class, 'whatsapp'])->name('dashboard.settings.whatsapp');
    Route::post('/settings/whatsapp', [\App\Http\Controllers\Dashboard\SettingsController::class, 'whatsappSave'])->name('dashboard.settings.whatsapp.save');
    Route::get('/settings/whatsapp/status', [\App\Http\Controllers\Dashboard\SettingsController::class, 'whatsappStatus'])->name('dashboard.settings.whatsapp.status');
    Route::post('/settings/whatsapp/disconnect', [\App\Http\Controllers\Dashboard\SettingsController::class, 'whatsappDisconnect'])->name('dashboard.settings.whatsapp.disconnect');
    Route::post('/settings/whatsapp/connect', [\App\Http\Controllers\Dashboard\SettingsController::class, 'whatsappConnect'])->name('dashboard.settings.whatsapp.connect');
    Route::post('/settings/whatsapp/notifications', [\App\Http\Controllers\Dashboard\SettingsController::class, 'whatsappNotificationsSave'])->name('dashboard.settings.whatsapp.notifications.save');
    Route::post('/settings/whatsapp/admin-notification', [\App\Http\Controllers\Dashboard\SettingsController::class, 'whatsappAdminNotificationSave'])->name('dashboard.settings.whatsapp.admin-notification.save');

    // Notificações
    Route::get('/notifications', [\App\Http\Controllers\Dashboard\NotificationsController::class, 'index'])->name('dashboard.notifications.index');
    Route::post('/notifications', [\App\Http\Controllers\Dashboard\NotificationsController::class, 'save'])->name('dashboard.notifications.save');

    // Gestão de instâncias WhatsApp (múltiplas instâncias)
    Route::post('/settings/whatsapp/instances', [\App\Http\Controllers\Dashboard\SettingsController::class, 'whatsappInstanceStore'])->name('dashboard.settings.whatsapp.instances.store');
    Route::get('/settings/whatsapp/instances/{instance}', [\App\Http\Controllers\Dashboard\SettingsController::class, 'whatsappInstanceShow'])->name('dashboard.settings.whatsapp.instances.show');
    Route::put('/settings/whatsapp/instances/{instance}', [\App\Http\Controllers\Dashboard\SettingsController::class, 'whatsappInstanceUpdate'])->name('dashboard.settings.whatsapp.instances.update');
    Route::delete('/settings/whatsapp/instances/{instance}', [\App\Http\Controllers\Dashboard\SettingsController::class, 'whatsappInstanceDestroy'])->name('dashboard.settings.whatsapp.instances.destroy');
    Route::get('/settings/whatsapp/instances/{instance}/status', [\App\Http\Controllers\Dashboard\SettingsController::class, 'whatsappInstanceStatus'])->name('dashboard.settings.whatsapp.instances.status');
    Route::post('/settings/whatsapp/instances/{instance}/connect', [\App\Http\Controllers\Dashboard\SettingsController::class, 'whatsappInstanceConnect'])->name('dashboard.settings.whatsapp.instances.connect');
    Route::post('/settings/whatsapp/instances/{instance}/disconnect', [\App\Http\Controllers\Dashboard\SettingsController::class, 'whatsappInstanceDisconnect'])->name('dashboard.settings.whatsapp.instances.disconnect');

    Route::get('/settings/mercadopago', [\App\Http\Controllers\Dashboard\SettingsController::class, 'mp'])->name('dashboard.settings.mp');
    Route::post('/settings/mercadopago', [\App\Http\Controllers\Dashboard\SettingsController::class, 'mpSave'])->name('dashboard.settings.mp.save');
    Route::post('/settings/mercadopago/methods', [\App\Http\Controllers\Dashboard\SettingsController::class, 'mpMethodsSave'])->name('dashboard.settings.mp.methods.save');
    Route::post('/settings/apis', [\App\Http\Controllers\Dashboard\SettingsController::class, 'apisSave'])->name('dashboard.settings.apis.save');
    // Rota antiga redirecionada - mantida para compatibilidade
    Route::post('/settings/production', function () {
        return redirect()->route('dashboard.producao.configuracoes-custos.index')
            ->with('info', 'As configurações de custos de produção foram movidas para o módulo de Produção.');
    })->name('dashboard.settings.production.save');
    Route::get('/settings/status-templates', [\App\Http\Controllers\Dashboard\OrderStatusController::class, 'index'])->name('dashboard.settings.status-templates');
    Route::post('/settings/status-templates/status/{id}', [\App\Http\Controllers\Dashboard\OrderStatusController::class, 'updateStatus'])->name('dashboard.settings.status-templates.status.update');
    Route::post('/settings/status-templates/template', [\App\Http\Controllers\Dashboard\OrderStatusController::class, 'saveTemplate'])->name('dashboard.settings.status-templates.template.save');
    Route::delete('/settings/status-templates/template/{id}', [\App\Http\Controllers\Dashboard\OrderStatusController::class, 'deleteTemplate'])->name('dashboard.settings.status-templates.template.delete');
    Route::get('/settings/status-templates/template/{id}', [\App\Http\Controllers\Dashboard\OrderStatusController::class, 'getTemplate'])->name('dashboard.settings.status-templates.template.get');

    // Configurações de Impressão
    Route::get('/settings/printing', [\App\Http\Controllers\Dashboard\SettingsController::class, 'printing'])->name('dashboard.settings.printing');
    Route::post('/settings/printing', [\App\Http\Controllers\Dashboard\SettingsController::class, 'printingSave'])->name('dashboard.settings.printing.update');

    // Configurações: Dias e horários de entrega
    Route::prefix('settings/entrega')->name('dashboard.settings.delivery.')->group(function () {
        Route::get('/agendamentos', [\App\Http\Controllers\Dashboard\DeliverySchedulesController::class, 'index'])->name('schedules.index');
        Route::post('/agendamentos', [\App\Http\Controllers\Dashboard\DeliverySchedulesController::class, 'store'])->name('schedules.store');
        Route::put('/agendamentos/{schedule}', [\App\Http\Controllers\Dashboard\DeliverySchedulesController::class, 'update'])->name('schedules.update');
        Route::delete('/agendamentos/{schedule}', [\App\Http\Controllers\Dashboard\DeliverySchedulesController::class, 'destroy'])->name('schedules.destroy');
    });

    // PDV
    Route::prefix('pdv')->name('dashboard.pdv.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Dashboard\PDVController::class, 'index'])->name('index');
        Route::get('/sweetspot', [\App\Http\Controllers\Dashboard\PDVController::class, 'index'])->name('sweetspot');
        Route::post('/calc', [\App\Http\Controllers\Dashboard\PDVController::class, 'calculate'])->name('calculate');
        Route::post('/order', [\App\Http\Controllers\Dashboard\PDVController::class, 'store'])->name('store');
        Route::post('/send', [\App\Http\Controllers\Dashboard\PDVController::class, 'send'])->name('send');
        Route::post('/search-order', [\App\Http\Controllers\Dashboard\PDVController::class, 'searchOrder'])->name('searchOrder');
        Route::post('/orders/{order}/confirm-payment-silent', [\App\Http\Controllers\Dashboard\PDVController::class, 'confirmPaymentSilent'])->name('confirmPaymentSilent');

        // API routes for PDV search (session-based auth)
        Route::get('/customers/search', [\App\Http\Controllers\Dashboard\PDVController::class, 'searchCustomers'])->name('customers.search');
        Route::post('/customers', [\App\Http\Controllers\Dashboard\PDVController::class, 'storeCustomer'])->name('customers.store');
        Route::get('/products/search', [\App\Http\Controllers\Dashboard\PDVController::class, 'searchProducts'])->name('products.search');
        Route::post('/coupons/validate', [\App\Http\Controllers\Dashboard\PDVController::class, 'validateCoupon'])->name('coupons.validate');
        Route::post('/calculate-delivery-fee', [\App\Http\Controllers\Dashboard\PDVController::class, 'calculateDeliveryFee'])->name('calculateDeliveryFee');
        Route::get('/pix-qr/{order}', [\App\Http\Controllers\Dashboard\PDVController::class, 'getPixQr'])->name('pixQr');
        Route::post('/send-pix-whatsapp/{order}', [\App\Http\Controllers\Dashboard\PDVController::class, 'sendPixWhatsApp'])->name('sendPixWhatsApp');
    });

    Route::prefix('production')->name('dashboard.producao.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Dashboard\ProductionController::class, 'dashboard'])->name('index');

        // Receitas
        Route::prefix('recipes')->name('receitas.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Dashboard\RecipesController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Dashboard\RecipesController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Dashboard\RecipesController::class, 'store'])->name('store');
            Route::get('/{id}', [\App\Http\Controllers\Dashboard\RecipesController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [\App\Http\Controllers\Dashboard\RecipesController::class, 'edit'])->name('edit');
            Route::put('/{id}', [\App\Http\Controllers\Dashboard\RecipesController::class, 'update'])->name('update');
            Route::delete('/{id}', [\App\Http\Controllers\Dashboard\RecipesController::class, 'destroy'])->name('destroy');
            Route::post('/create-ingredient-from-product/{productId}', [\App\Http\Controllers\Dashboard\RecipesController::class, 'createIngredientFromProduct'])->name('create-ingredient-from-product');
            Route::post('/import-from-product-ingredient/{productId}', [\App\Http\Controllers\Dashboard\RecipesController::class, 'importFromProductIngredient'])->name('import-from-product-ingredient');
        });

        // Ingredientes
        Route::prefix('ingredients')->name('ingredientes.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Dashboard\IngredientsController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Dashboard\IngredientsController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Dashboard\IngredientsController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [\App\Http\Controllers\Dashboard\IngredientsController::class, 'edit'])->name('edit');
            Route::put('/{id}', [\App\Http\Controllers\Dashboard\IngredientsController::class, 'update'])->name('update');
            Route::post('/{id}/update-stock', [\App\Http\Controllers\Dashboard\IngredientsController::class, 'updateStock'])->name('update-stock');
            Route::delete('/{id}', [\App\Http\Controllers\Dashboard\IngredientsController::class, 'destroy'])->name('destroy');
        });

        // Embalagens
        Route::prefix('packagings')->name('embalagens.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Dashboard\PackagingsController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Dashboard\PackagingsController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Dashboard\PackagingsController::class, 'store'])->name('store');
            Route::get('/{embalagem}/edit', [\App\Http\Controllers\Dashboard\PackagingsController::class, 'edit'])->name('edit');
            Route::put('/{embalagem}', [\App\Http\Controllers\Dashboard\PackagingsController::class, 'update'])->name('update');
            Route::delete('/{embalagem}', [\App\Http\Controllers\Dashboard\PackagingsController::class, 'destroy'])->name('destroy');
        });

        // Lista de Produção
        Route::prefix('list')->name('lista-producao.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Dashboard\ProductionController::class, 'listaProducao'])->name('index');
            Route::post('/create', [\App\Http\Controllers\Dashboard\ProductionController::class, 'createList'])->name('create');
            Route::post('/{id}/items', [\App\Http\Controllers\Dashboard\ProductionController::class, 'addItemToList'])->name('add-item');
            Route::post('/items/{id}/produced', [\App\Http\Controllers\Dashboard\ProductionController::class, 'markItemProduced'])->name('mark-produced');
            Route::post('/items/{id}/mark-print', [\App\Http\Controllers\Dashboard\ProductionController::class, 'toggleMarkForPrint'])->name('mark-print');
            Route::delete('/items/{id}', [\App\Http\Controllers\Dashboard\ProductionController::class, 'removeItemFromList'])->name('remove-item');
            Route::post('/add-recipe', [\App\Http\Controllers\Dashboard\ProductionController::class, 'addRecipeToTodayList'])->name('add-recipe');
            Route::get('/{id}/print', [\App\Http\Controllers\Dashboard\ProductionController::class, 'printProductionList'])->name('print');
        });

        Route::get('/lista-compras', [\App\Http\Controllers\Dashboard\ProductionController::class, 'listaCompras'])->name('lista-compras.index');
        Route::get('/inventory-produced', function () {
            return view('dashboard.producao.estoque-produzidos');
        })->name('estoque-produzidos.index');
        Route::prefix('costs')->name('custos.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Dashboard\CustosController::class, 'index'])->name('index');
            Route::post('/calculate', [\App\Http\Controllers\Dashboard\CustosController::class, 'calculate'])->name('calculate');
        });
        Route::get('/reports', [\App\Http\Controllers\Dashboard\ProductionController::class, 'relatorios'])->name('relatorios-producao.index');

        // Configurações de Custos de Produção
        Route::get('/configuracoes-custos', [\App\Http\Controllers\Dashboard\ProductionController::class, 'configuracoesCustos'])->name('configuracoes-custos.index');
        Route::post('/configuracoes-custos', [\App\Http\Controllers\Dashboard\ProductionController::class, 'salvarConfiguracoesCustos'])->name('configuracoes-custos.save');

        // Fila de Impressão
        Route::get('/print-queue/from-list', [\App\Http\Controllers\Dashboard\ProductionController::class, 'getPrintQueueFromList'])->name('print-queue.from-list');
        Route::post('/print-queue/add', [\App\Http\Controllers\Dashboard\ProductionController::class, 'addToPrintQueue'])->name('print-queue.add');
        Route::get('/print-queue', [\App\Http\Controllers\Dashboard\ProductionController::class, 'getPrintQueue'])->name('print-queue.get');
        Route::delete('/print-queue/{index}', [\App\Http\Controllers\Dashboard\ProductionController::class, 'removeFromPrintQueue'])->name('print-queue.remove');
        Route::post('/print-queue/clear', [\App\Http\Controllers\Dashboard\ProductionController::class, 'clearPrintQueue'])->name('print-queue.clear');
        Route::put('/print-queue/{index}', [\App\Http\Controllers\Dashboard\ProductionController::class, 'updatePrintQueueItem'])->name('print-queue.update');
        Route::get('/print-queue/print', [\App\Http\Controllers\Dashboard\ProductionController::class, 'printQueue'])->name('print-queue.print');
    });

    Route::get('/assistente-ia', [\App\Http\Controllers\Dashboard\AssistenteIAController::class, 'index'])->name('dashboard.assistente-ia.index');
    Route::post('/assistente-ia/ask', [\App\Http\Controllers\Dashboard\AssistenteIAController::class, 'ask'])->name('dashboard.assistente-ia.ask');
    Route::get('/assistente-ia/test', [\App\Http\Controllers\Dashboard\AssistenteIAController::class, 'testBackend'])->name('dashboard.assistente-ia.test');

    Route::get('/guia-tecnico-completo', function () {
        return view('guia-tecnico-completo');
    })->name('guia-tecnico-completo');

    // Gerenciamento de Temas
    Route::prefix('themes')->name('dashboard.themes.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Dashboard\ThemeController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\Dashboard\ThemeController::class, 'update'])->name('update');
        Route::post('/reset', [\App\Http\Controllers\Dashboard\ThemeController::class, 'reset'])->name('reset');
        Route::get('/custom-css', [\App\Http\Controllers\Dashboard\ThemeController::class, 'customCss'])->name('custom-css');
        Route::post('/preview', [\App\Http\Controllers\Dashboard\ThemeController::class, 'preview'])->name('preview');
    });

    // Campanhas de Marketing (WhatsApp)
    Route::prefix('marketing')->name('dashboard.marketing.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Dashboard\MarketingController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Dashboard\MarketingController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Dashboard\MarketingController::class, 'store'])->name('store');
        Route::get('/{campaign}', [\App\Http\Controllers\Dashboard\MarketingController::class, 'show'])->name('show');
        Route::get('/{campaign}/edit', [\App\Http\Controllers\Dashboard\MarketingController::class, 'edit'])->name('edit');
        Route::put('/{campaign}', [\App\Http\Controllers\Dashboard\MarketingController::class, 'update'])->name('update');
        Route::delete('/{campaign}', [\App\Http\Controllers\Dashboard\MarketingController::class, 'destroy'])->name('destroy');
        Route::post('/{campaign}/start', [\App\Http\Controllers\Dashboard\MarketingController::class, 'start'])->name('start');
        Route::post('/{campaign}/pause', [\App\Http\Controllers\Dashboard\MarketingController::class, 'pause'])->name('pause');
        Route::post('/{campaign}/cancel', [\App\Http\Controllers\Dashboard\MarketingController::class, 'cancel'])->name('cancel');
        Route::post('/preview-audience', [\App\Http\Controllers\Dashboard\MarketingController::class, 'previewAudience'])->name('preview-audience');
    });

    // Integrações de APIs (Gemini, OpenAI, MercadoPago, etc)
    Route::prefix('integrations')->name('dashboard.integrations.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Dashboard\IntegrationsController::class, 'index'])->name('index');
        Route::post('/{provider}', [\App\Http\Controllers\Dashboard\IntegrationsController::class, 'update'])->name('update');
        Route::match(['get', 'post'], '/{provider}/test', [\App\Http\Controllers\Dashboard\IntegrationsController::class, 'test'])->name('test');
        Route::post('/{provider}/toggle', [\App\Http\Controllers\Dashboard\IntegrationsController::class, 'toggle'])->name('toggle');
    });

    // SaaS Clients Management (Master only)
    Route::prefix('saas-clients')->name('dashboard.saas-clients.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Dashboard\SaasClientsController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Dashboard\SaasClientsController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Dashboard\SaasClientsController::class, 'store'])->name('store');
        Route::get('/{saasClient}', [\App\Http\Controllers\Dashboard\SaasClientsController::class, 'show'])->name('show');
    });

    // Taxas de entrega por distância
    Route::prefix('delivery-pricing')->name('dashboard.delivery-pricing.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Dashboard\DeliveryPricingController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\Dashboard\DeliveryPricingController::class, 'store'])->name('store');
        Route::post('/simulate', [\App\Http\Controllers\Dashboard\DeliveryPricingController::class, 'simulate'])->name('simulate');
        Route::put('/{pricing}', [\App\Http\Controllers\Dashboard\DeliveryPricingController::class, 'update'])->name('update');
        Route::delete('/{pricing}', [\App\Http\Controllers\Dashboard\DeliveryPricingController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('tools')->name('dashboard.tools.')->group(function () {
        Route::get('/import-ingredients', [\App\Http\Controllers\Dashboard\ToolsController::class, 'importIngredients'])->name('import-ingredients');
        Route::get('/flush', [\App\Http\Controllers\Dashboard\ToolsController::class, 'flushCaches'])->name('flush');
    });

    // ============================================
    // PLANOS E ASSINATURAS (dashboard do cliente)
    // ============================================
    Route::prefix('subscription')->name('dashboard.subscription.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Dashboard\SubscriptionController::class, 'index'])->name('index');
        Route::post('/upgrade', [\App\Http\Controllers\Dashboard\SubscriptionController::class, 'requestUpgrade'])->name('upgrade');
        Route::post('/renew', [\App\Http\Controllers\Dashboard\SubscriptionController::class, 'renew'])->name('renew');
        Route::get('/invoices', [\App\Http\Controllers\Dashboard\SubscriptionController::class, 'invoices'])->name('invoices');
        Route::post('/notifications/{notification}/read', [\App\Http\Controllers\Dashboard\SubscriptionController::class, 'markNotificationRead'])->name('notifications.read');
        Route::post('/notifications/read-all', [\App\Http\Controllers\Dashboard\SubscriptionController::class, 'markAllNotificationsRead'])->name('notifications.read-all');
    });

});

// Subdomínio: Pedido (Loja Front-end) - PÚBLICO
Route::domain($pedidoDomain)->name('pedido.')->group(function () {
    // API BotConversa também funciona no subdomínio (redundância para garantir)
    Route::prefix('api/botconversa')->name('api.botconversa.')->group(function () {
        Route::get('/ping', function () {
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
        Route::get('/produto/{product}/modal', [MenuController::class, 'productModal'])->name('product.modal');
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
        Route::get('/produto/{product}/modal', [MenuController::class, 'productModal'])->name('product.modal');
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
    Route::get('/', [\App\Http\Controllers\Dashboard\DashboardController::class, 'home'])->name('fb.dashboard.index');
    Route::get('/pdv', [\App\Http\Controllers\Dashboard\PDVController::class, 'index'])->name('fb.dashboard.pdv.index');
    Route::get('/pedidos', [\App\Http\Controllers\Dashboard\OrdersController::class, 'index'])->name('fb.dashboard.orders.index');
    Route::get('/clientes', [\App\Http\Controllers\Dashboard\CustomersController::class, 'index'])->name('fb.dashboard.customers.index');
    Route::post('/clientes/update-stats', [\App\Http\Controllers\Dashboard\CustomersController::class, 'updateStats'])->name('fb.dashboard.customers.updateStats');
    Route::get('/produtos', [\App\Http\Controllers\Dashboard\ProductsController::class, 'index'])->name('fb.dashboard.products.index');
    Route::get('/categorias', [\App\Http\Controllers\Dashboard\CategoriesController::class, 'index'])->name('fb.dashboard.categories.index');
    Route::get('/cupons', [\App\Http\Controllers\Dashboard\CouponsController::class, 'index'])->name('fb.dashboard.coupons.index');
    Route::resource('wholesale-prices', \App\Http\Controllers\Dashboard\WholesalePricesController::class)->names([
        'index' => 'fb.dashboard.wholesale-prices.index',
        'create' => 'fb.dashboard.wholesale-prices.create',
        'store' => 'fb.dashboard.wholesale-prices.store',
        'edit' => 'fb.dashboard.wholesale-prices.edit',
        'update' => 'fb.dashboard.wholesale-prices.update',
        'destroy' => 'fb.dashboard.wholesale-prices.destroy',
    ]);
    Route::get('/precos-revenda', function () {
        return redirect()->route('dashboard.wholesale-prices.index');
    })->name('fb.dashboard.wholesale-prices.alias');
    Route::get('/cashback', [\App\Http\Controllers\Dashboard\CashbackController::class, 'index'])->name('fb.dashboard.cashback.index');
    Route::get('/fidelidade', [\App\Http\Controllers\Dashboard\LoyaltyController::class, 'index'])->name('fb.dashboard.loyalty');
    Route::get('/relatorios', [\App\Http\Controllers\Dashboard\ReportsController::class, 'index'])->name('fb.dashboard.reports');
    Route::get('/configuracoes', [\App\Http\Controllers\Dashboard\SettingsController::class, 'index'])->name('fb.dashboard.settings');
    Route::get('/entregas', [\App\Http\Controllers\Dashboard\DeliveryController::class, 'index'])->name('fb.dashboard.deliveries.index');
    Route::post('/entregas/{order}/status', [\App\Http\Controllers\Dashboard\DeliveryController::class, 'updateStatus'])->name('fb.dashboard.deliveries.status');

    // Rastreamento de entregas (fallback)
    Route::post('/deliveries/{order}/tracking/start', [\App\Http\Controllers\DeliveryTrackingController::class, 'start'])->name('fb.dashboard.deliveries.tracking.start');
    Route::post('/deliveries/{order}/tracking/stop', [\App\Http\Controllers\DeliveryTrackingController::class, 'stop'])->name('fb.dashboard.deliveries.tracking.stop');
    Route::post('/deliveries/{order}/tracking/update', [\App\Http\Controllers\DeliveryTrackingController::class, 'updateLocation'])->name('fb.dashboard.deliveries.tracking.update');
    Route::get('/whatsapp', [\App\Http\Controllers\Dashboard\SettingsController::class, 'whatsapp'])->name('fb.dashboard.settings.whatsapp');
    Route::get('/mercado-pago', [\App\Http\Controllers\Dashboard\SettingsController::class, 'mp'])->name('fb.dashboard.settings.mp');
    Route::get('/status-templates', [\App\Http\Controllers\Dashboard\OrderStatusController::class, 'index'])->name('fb.dashboard.settings.status-templates');
    Route::post('/status-templates/status/{id}', [\App\Http\Controllers\Dashboard\OrderStatusController::class, 'updateStatus'])->name('fb.dashboard.settings.status-templates.status.update');
    Route::post('/status-templates/template', [\App\Http\Controllers\Dashboard\OrderStatusController::class, 'saveTemplate'])->name('fb.dashboard.settings.status-templates.template.save');
    Route::delete('/status-templates/template/{id}', [\App\Http\Controllers\Dashboard\OrderStatusController::class, 'deleteTemplate'])->name('fb.dashboard.settings.status-templates.template.delete');
    Route::get('/status-templates/template/{id}', [\App\Http\Controllers\Dashboard\OrderStatusController::class, 'getTemplate'])->name('fb.dashboard.settings.status-templates.template.get');
    Route::post('/settings/apis', [\App\Http\Controllers\Dashboard\SettingsController::class, 'apisSave'])->name('fb.dashboard.settings.apis.save');

    // Rotas de produtos (resource e auxiliares)
    Route::resource('products', \App\Http\Controllers\Dashboard\ProductsController::class)->names([
        'index' => 'fb.dashboard.products.index',
        'create' => 'fb.dashboard.products.create',
        'store' => 'fb.dashboard.products.store',
        'show' => 'fb.dashboard.products.show',
        'edit' => 'fb.dashboard.products.edit',
        'update' => 'fb.dashboard.products.update',
        'destroy' => 'fb.dashboard.products.destroy',
    ]);

    // Rotas auxiliares para gerenciar imagens e variantes dos produtos
    Route::prefix('products/{product}')->group(function () {
        Route::post('/duplicate', [\App\Http\Controllers\Dashboard\ProductsController::class, 'duplicate'])->name('fb.dashboard.products.duplicate');
        Route::delete('/images/{image}', [\App\Http\Controllers\Dashboard\ProductsController::class, 'deleteImage'])->name('fb.dashboard.products.images.delete');
        Route::post('/images/{image}/set-primary', [\App\Http\Controllers\Dashboard\ProductsController::class, 'setPrimaryImage'])->name('fb.dashboard.products.images.set-primary');
        Route::post('/images/reorder', [\App\Http\Controllers\Dashboard\ProductsController::class, 'reorderImages'])->name('fb.dashboard.products.images.reorder');
        Route::delete('/variants/{variant}', [\App\Http\Controllers\Dashboard\ProductsController::class, 'destroyVariant'])->name('fb.dashboard.products.variants.destroy');
    });

    // Rotas de orders no fallback (incluindo orders-for-print)
    Route::prefix('orders')->group(function () {
        Route::get('/', [\App\Http\Controllers\Dashboard\OrdersController::class, 'index'])->name('dashboard.orders.index');
        Route::get('/orders-for-print', [\App\Http\Controllers\Dashboard\OrdersController::class, 'getOrdersForPrint'])->name('dashboard.orders.forPrint');
        Route::get('/delivery-slots', [\App\Http\Controllers\Dashboard\OrdersController::class, 'deliverySlots'])->name('dashboard.orders.deliverySlots');
        Route::get('/printer-monitor', [\App\Http\Controllers\Dashboard\OrdersController::class, 'printerMonitor'])->name('dashboard.orders.printerMonitor');
        Route::get('/{order}', [\App\Http\Controllers\Dashboard\OrdersController::class, 'show'])->name('dashboard.orders.show');
        Route::get('/{order}/edit', [\App\Http\Controllers\Dashboard\OrdersController::class, 'edit'])->name('dashboard.orders.edit');
        Route::put('/{order}', [\App\Http\Controllers\Dashboard\OrdersController::class, 'update'])->name('dashboard.orders.update');
        Route::post('/{order}/status', [\App\Http\Controllers\Dashboard\OrdersController::class, 'updateStatus'])->name('dashboard.orders.updateStatus');
        Route::post('/{order}/duplicate', [\App\Http\Controllers\Dashboard\OrdersController::class, 'duplicate'])->name('dashboard.orders.duplicate');
        Route::post('/{order}/send-payment-charge', [\App\Http\Controllers\Dashboard\OrdersController::class, 'sendPaymentCharge'])->name('dashboard.orders.sendPaymentCharge');
        Route::get('/{order}/fiscal-receipt/escpos', [\App\Http\Controllers\Dashboard\OrdersController::class, 'fiscalReceiptEscPos'])->name('dashboard.orders.fiscalReceiptEscPos');
        Route::post('/{order}/request-print', [\App\Http\Controllers\Dashboard\OrdersController::class, 'requestPrint'])->name('dashboard.orders.requestPrint');
        Route::post('/{order}/mark-printed', [\App\Http\Controllers\Dashboard\OrdersController::class, 'markAsPrinted'])->name('dashboard.orders.markPrinted');
    });

    // Rotas de clientes no fallback
    Route::prefix('customers')->group(function () {
        Route::get('/', [\App\Http\Controllers\Dashboard\CustomersController::class, 'index'])->name('dashboard.customers.index');
        Route::get('/{customer}', [\App\Http\Controllers\Dashboard\CustomersController::class, 'show'])->name('dashboard.customers.show');
    });

    // Configurações: Dias e horários de entrega (fallback)
    Route::prefix('entrega')->group(function () {
        Route::get('/agendamentos', [\App\Http\Controllers\Dashboard\DeliverySchedulesController::class, 'index'])->name('fb.dashboard.settings.delivery.schedules.index');
        Route::post('/agendamentos', [\App\Http\Controllers\Dashboard\DeliverySchedulesController::class, 'store'])->name('fb.dashboard.settings.delivery.schedules.store');
        Route::put('/agendamentos/{schedule}', [\App\Http\Controllers\Dashboard\DeliverySchedulesController::class, 'update'])->name('fb.dashboard.settings.delivery.schedules.update');
        Route::delete('/agendamentos/{schedule}', [\App\Http\Controllers\Dashboard\DeliverySchedulesController::class, 'destroy'])->name('fb.dashboard.settings.delivery.schedules.destroy');
    });

    // Taxas de entrega por distância (fallback)
    Route::prefix('taxas-entrega')->group(function () {
        Route::get('/', [\App\Http\Controllers\Dashboard\DeliveryPricingController::class, 'index'])->name('fb.dashboard.delivery-pricing.index');
        Route::post('/', [\App\Http\Controllers\Dashboard\DeliveryPricingController::class, 'store'])->name('fb.dashboard.delivery-pricing.store');
        Route::post('/simulate', [\App\Http\Controllers\Dashboard\DeliveryPricingController::class, 'simulate'])->name('fb.dashboard.delivery-pricing.simulate');
        Route::put('/{pricing}', [\App\Http\Controllers\Dashboard\DeliveryPricingController::class, 'update'])->name('fb.dashboard.delivery-pricing.update');
        Route::delete('/{pricing}', [\App\Http\Controllers\Dashboard\DeliveryPricingController::class, 'destroy'])->name('fb.dashboard.delivery-pricing.destroy');
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
            ->whereHas('customer', function ($q) {
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
Route::get('/test-dashboard-route', function () use ($dashboardDomain, $pedidoDomain) {
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
// DASHBOARD MASTER (apenas super admin - client_id = 1)
// ============================================
Route::prefix('master')->middleware(['auth', \App\Http\Middleware\EnsureSuperAdmin::class])->name('master.')->group(function () {
    // Dashboard principal
    Route::get('/', [\App\Http\Controllers\Master\MasterDashboardController::class, 'index'])->name('dashboard');

    // Gerenciamento de clientes/estabelecimentos
    Route::prefix('clients')->name('clients.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Master\ClientsManagementController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Master\ClientsManagementController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Master\ClientsManagementController::class, 'store'])->name('store');
        Route::get('/{client}', [\App\Http\Controllers\Master\ClientsManagementController::class, 'show'])->name('show');
        Route::get('/{client}/edit', [\App\Http\Controllers\Master\ClientsManagementController::class, 'edit'])->name('edit');
        Route::put('/{client}', [\App\Http\Controllers\Master\ClientsManagementController::class, 'update'])->name('update');
        Route::post('/{client}/toggle-status', [\App\Http\Controllers\Master\ClientsManagementController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/{client}/change-plan', [\App\Http\Controllers\Master\ClientsManagementController::class, 'changePlan'])->name('change-plan');
        Route::post('/{client}/renew', [\App\Http\Controllers\Master\ClientsManagementController::class, 'renewSubscription'])->name('renew');
    });

    // Gerenciamento de planos
    Route::prefix('plans')->name('plans.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Master\PlansController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Master\PlansController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Master\PlansController::class, 'store'])->name('store');
        Route::get('/{plan}/edit', [\App\Http\Controllers\Master\PlansController::class, 'edit'])->name('edit');
        Route::put('/{plan}', [\App\Http\Controllers\Master\PlansController::class, 'update'])->name('update');
        Route::delete('/{plan}', [\App\Http\Controllers\Master\PlansController::class, 'destroy'])->name('destroy');
        Route::post('/{plan}/toggle-status', [\App\Http\Controllers\Master\PlansController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/{plan}/toggle-featured', [\App\Http\Controllers\Master\PlansController::class, 'toggleFeatured'])->name('toggle-featured');
        Route::post('/reorder', [\App\Http\Controllers\Master\PlansController::class, 'reorder'])->name('reorder');
    });

    // Gerenciamento de URLs de instâncias WhatsApp
    Route::prefix('whatsapp-urls')->name('whatsapp-urls.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Master\WhatsappInstanceUrlsController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Master\WhatsappInstanceUrlsController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Master\WhatsappInstanceUrlsController::class, 'store'])->name('store');
        Route::get('/{whatsappUrl}/edit', [\App\Http\Controllers\Master\WhatsappInstanceUrlsController::class, 'edit'])->name('edit');
        Route::put('/{whatsappUrl}', [\App\Http\Controllers\Master\WhatsappInstanceUrlsController::class, 'update'])->name('update');
        Route::delete('/{whatsappUrl}', [\App\Http\Controllers\Master\WhatsappInstanceUrlsController::class, 'destroy'])->name('destroy');
        Route::post('/{whatsappUrl}/health-check', [\App\Http\Controllers\Master\WhatsappInstanceUrlsController::class, 'healthCheck'])->name('health-check');
        Route::post('/health-check-all', [\App\Http\Controllers\Master\WhatsappInstanceUrlsController::class, 'healthCheckAll'])->name('health-check-all');
        Route::post('/{whatsappUrl}/assign', [\App\Http\Controllers\Master\WhatsappInstanceUrlsController::class, 'assign'])->name('assign');
        Route::post('/{whatsappUrl}/release', [\App\Http\Controllers\Master\WhatsappInstanceUrlsController::class, 'release'])->name('release');
        Route::post('/{whatsappUrl}/maintenance', [\App\Http\Controllers\Master\WhatsappInstanceUrlsController::class, 'maintenance'])->name('maintenance');
    });

    // Configurações do Master
    Route::get('/settings', [\App\Http\Controllers\Master\MasterSettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [\App\Http\Controllers\Master\MasterSettingsController::class, 'update'])->name('settings.update');
    Route::post('/settings/single', [\App\Http\Controllers\Master\MasterSettingsController::class, 'updateSingle'])->name('settings.update-single');
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

if (file_exists(__DIR__ . '/fix_revenue_tool.php')) {
    require __DIR__ . '/fix_revenue_tool.php';
}