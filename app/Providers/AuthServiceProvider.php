<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        \App\Models\Pedido::class       => \App\Policies\PedidoPolicy::class,
        \App\Models\Consignacao::class  => \App\Policies\ConsignacaoPolicy::class,
        \App\Models\Cupom::class        => \App\Policies\CupomPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Gates horizontais (sem modelo específico)
        Gate::define('view-reports', fn(User $u) => in_array($u->role, ['admin','gestor']));
        Gate::define('manage-catalog', fn(User $u) => in_array($u->role, ['admin','gestor']));
        Gate::define('manage-users', fn(User $u) => $u->role === 'admin');
    }
}
