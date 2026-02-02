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
        // Usar a view de paginação personalizada (Tailwind traduzido)
        Paginator::defaultView('vendor.pagination.tailwind');
        Paginator::defaultSimpleView('vendor.pagination.tailwind');

        // Detectar URL base dinamicamente baseado no host atual
        // IMPORTANTE: Isso deve ser feito ANTES de qualquer uso de asset() ou url()
        $request = request();
        if ($request && $request->getHost()) {
            $currentHost = $request->getHost();
            $scheme = $request->isSecure() || app()->environment('production') ? 'https' : 'http';

            // Detectar se é ambiente de desenvolvimento
            $isDevDomain = str_contains($currentHost, 'devpedido.') || str_contains($currentHost, 'devdashboard.');

            // Forçar HTTPS em produção e desenvolvimento (se disponível)
            if (app()->environment('production') || $isDevDomain) {
                URL::forceScheme('https');
                $scheme = 'https';
            }

            // Forçar URL base baseada no host atual
            // Isso garante que asset() e url() usem o domínio correto (ex: menuolika.com.br vs cozinhapro.app.br)
            $rootUrl = $scheme . '://' . $currentHost;
            URL::forceRootUrl($rootUrl);

            // Configurar o domínio do cookie de sessão dinamicamente
            // Para permitir sessões entre subdomínios, usamos o domínio principal (ex: .menuolika.com.br)
            $hostParts = explode('.', $currentHost);
            if (count($hostParts) >= 2) {
                // Se for IP ou localhost, não altera
                if (!filter_var($currentHost, FILTER_VALIDATE_IP) && $currentHost !== 'localhost') {
                    // Lista de domínios base conhecidos do sistema
                    $knownDomains = ['menuolika.com.br', 'cozinhapro.app.br', 'gastroflow.online'];
                    $baseDomain = null;

                    foreach ($knownDomains as $kd) {
                        if (str_ends_with($currentHost, $kd)) {
                            $baseDomain = $kd;
                            break;
                        }
                    }

                    // Se for um domínio desconhecido, tenta pegar os últimos 2 ou 3 componentes
                    if (!$baseDomain) {
                        if (str_ends_with($currentHost, '.com.br') || str_ends_with($currentHost, '.app.br') || str_ends_with($currentHost, '.net.br')) {
                            $baseDomain = implode('.', array_slice($hostParts, -3));
                        } else {
                            $baseDomain = implode('.', array_slice($hostParts, -2));
                        }
                    }

                    if ($baseDomain) {
                        Config::set('session.domain', '.' . $baseDomain);

                        // Definir default para o parâmetro {dashboard_domain} nas rotas
                        // Isso garante que route('dashboard.index') gere a URL correta para o domínio atual
                        $dashboardDefault = 'dashboard.' . $baseDomain;

                        if ($baseDomain === 'gastroflow.online') {
                            $dashboardDefault = 'gastroflow.online';
                        } elseif ($isDevDomain) {
                            $dashboardDefault = 'devdashboard.' . $baseDomain;
                        }

                        // Se estamos acessando diretamente um dashboard, usar o host atual
                        // (Isso corrige o problema de cache de rotas gerando URLs cruzadas)
                        if (
                            str_starts_with($currentHost, 'dashboard.') ||
                            str_starts_with($currentHost, 'devdashboard.') ||
                            $currentHost === 'gastroflow.online'
                        ) {
                            $dashboardDefault = $currentHost;
                        }

                        URL::defaults(['dashboard_domain' => $dashboardDefault]);

                        // Fortalecer a configuração da sessão para evitar 419
                        if ($scheme === 'https') {
                            Config::set('session.secure', true);
                            Config::set('session.same_site', 'lax');
                        }
                    }
                }
            }

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

        // Registrar observer para automação da lista de produção
        if (class_exists(OrderProductionObserver::class)) {
            Order::observe(OrderProductionObserver::class);
        }
    }
}
