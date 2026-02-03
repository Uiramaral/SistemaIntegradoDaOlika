<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Config;
use App\Models\CustomerDebt;
use App\Models\Order;
use App\Observers\CustomerDebtObserver;
use App\Observers\OrderFinancialObserver;
use App\Observers\OrderProductionObserver;

use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Registrar helpers globais
        require_once app_path('Helpers/ClientHelper.php');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\Log::info('AppServiceProvider: boot start');
        // Usar a view de paginação personalizada (Tailwind traduzido)
        Paginator::defaultView('vendor.pagination.tailwind');
        Paginator::defaultSimpleView('vendor.pagination.tailwind');

        $host = request()->getHost();
        $baseDomains = config('olika.base_domains', ['menuolika.com.br', 'cozinhapro.app.br', 'gastroflow.online']);

        $matchedBase = null;
        foreach ($baseDomains as $bd) {
            if (str_ends_with($host, $bd)) {
                $matchedBase = $bd;
                break;
            }
        }

        // Se nÃ£o encontrar na lista, tenta pegar os dois últimos segmentos como fallback
        if (!$matchedBase && strpos($host, '.') !== false) {
            $parts = explode('.', $host);
            if (count($parts) >= 2) {
                $matchedBase = implode('.', array_slice($parts, -2));
            }
        }

        $baseDomain = $matchedBase ?: 'menuolika.com.br';
        $dashboardDomain = 'dashboard.' . $baseDomain;

        // ForÃ§ar HTTPS em produÃ§Ã£o
        if (!app()->isLocal()) {
            URL::forceScheme('https');
        }

        // ParÃ¢metros padrÃ£o para rotas do dashboard
        URL::defaults(['dashboard_domain' => $dashboardDomain]);

        // ConfiguraÃ§Ã£o de sessÃ£o (CSRF FIX)
        // ConfiguraÃ§Ã£o de sessÃ£o (CSRF FIX)
        // Alterado para NULL para evitar problemas de loop de login e garantir que o cookie
        // seja setado para o domínio/host exato da requisição.
        Config::set('session.domain', null);

        if (!app()->isLocal()) {
            Config::set('session.secure', true);
            Config::set('session.same_site', 'lax');
        }

        \Illuminate\Support\Facades\Schema::defaultStringLength(191);

        // Helper Blade @role
        Blade::if('role', function (...$roles) {
            $u = auth()->user();
            return $u && (empty($roles) || in_array($u->role, $roles));
        });

        // Registrar observer para atualizar saldo de débitos (apenas se a classe existir)
        if (class_exists(CustomerDebtObserver::class)) {
            CustomerDebt::observe(CustomerDebtObserver::class);
        }

        // Registrar observer para receita automática de pedidos pagos (Finanças)
        if (class_exists(OrderFinancialObserver::class)) {
            Order::observe(OrderFinancialObserver::class);
        }

        // Registrar observer para automação da lista de produção
        if (class_exists(OrderProductionObserver::class)) {
            Order::observe(OrderProductionObserver::class);
        }
    }
}
