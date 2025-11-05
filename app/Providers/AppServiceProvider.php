<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Blade;
use App\Models\CustomerDebt;
use App\Observers\CustomerDebtObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (app()->environment('production')) {
            URL::forceScheme('https');
            URL::forceRootUrl(config('app.url'));
        }

        // Helper Blade @role
        Blade::if('role', function(...$roles){
            $u = auth()->user();
            return $u && (empty($roles) || in_array($u->role, $roles));
        });

        // Registrar observer para atualizar saldo de débitos (apenas se a classe existir)
        if (class_exists(CustomerDebtObserver::class)) {
            CustomerDebt::observe(CustomerDebtObserver::class);
        }
    }
}
