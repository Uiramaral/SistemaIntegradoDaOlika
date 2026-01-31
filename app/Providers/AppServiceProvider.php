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
        // Usar a view de paginação personalizada (Tailwind traduzido)
        Paginator::defaultView('vendor.pagination.tailwind');
        Paginator::defaultSimpleView('vendor.pagination.tailwind');

        // Detectar URL base dinamicamente baseado no host atual
        // IMPORTANTE: Isso deve ser feito ANTES de qualquer uso de asset() ou url()
        $request = request();
        if ($request) {
            $currentHost = $request->getHost();
            $scheme = $request->getScheme();

            // Detectar se é ambiente de desenvolvimento
            $isDevDomain = str_contains($currentHost, 'devpedido.') || str_contains($currentHost, 'devdashboard.');

            // Forçar HTTPS em produção e desenvolvimento (se disponível)
            if (app()->environment('production') || $isDevDomain) {
                URL::forceScheme('https');
            }

            // Forçar URL base baseada no host atual
            // Isso garante que asset() e url() usem o domínio correto
            $rootUrl = $scheme . '://' . $currentHost;
            URL::forceRootUrl($rootUrl);

            // Configurar URL do storage público dinamicamente
            Config::set('filesystems.disks.public.url', $rootUrl . '/storage');

            // Garantir que ASSET_URL também use o domínio atual (se configurado)
            if (config('app.asset_url')) {
                Config::set('app.asset_url', $rootUrl);
            }
        } elseif (app()->environment('production')) {
            // Fallback para produção se não houver request
            URL::forceScheme('https');
            URL::forceRootUrl(config('app.url'));
        }

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
    }
}
