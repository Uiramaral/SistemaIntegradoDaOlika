<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ClientScope implements Scope
{
    /**
     * Aplicar o scope a uma query do Eloquent.
     * Filtra automaticamente por client_id do usuário autenticado.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Só filtra se houver usuário autenticado com client_id
        if (auth()->check() && auth()->user()->client_id) {
            $builder->where('client_id', auth()->user()->client_id);
        }
    }
}

