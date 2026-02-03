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
        // Tentar obter o client_id do contexto (Helper)
        $clientId = function_exists('currentClientId') ? currentClientId() : null;

        // Fallback robusto caso helper não esteja disponível
        if (!$clientId) {
            if (app()->bound('request')) {
                $clientId = request()->attributes->get('client_id')
                    ?? request()->header('X-Client-Id');
            }

            if (!$clientId && session()->has('client_id')) {
                $clientId = session('client_id');
            }

            if (!$clientId && auth()->check() && isset(auth()->user()->client_id)) {
                $clientId = auth()->user()->client_id;
            }

            if (!$clientId) {
                $clientId = config('olika.default_client_id');
            }
        }

        if ($clientId) {
            // Qualificar com o nome da tabela para evitar ambiguidade em JOINs
            $table = $model->getTable();
            $builder->where("{$table}.client_id", $clientId);
        }
    }
}

