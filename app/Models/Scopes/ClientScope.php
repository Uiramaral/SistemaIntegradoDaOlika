<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * ClientScope - Global Scope para isolamento multi-tenant
 * 
 * Este scope é automaticamente aplicado a todos os models que usam
 * o trait BelongsToClient, filtrando queries pelo client_id atual.
 * 
 * Comportamento:
 * - Se há client_id no contexto: filtra WHERE client_id = X
 * - Se não há client_id: não aplica filtro (para comandos artisan, etc)
 * - Pode ser desabilitado com: Model::withoutGlobalScope(ClientScope::class)
 */
class ClientScope implements Scope
{
    /**
     * Aplica o scope à query
     */
    public function apply(Builder $builder, Model $model): void
    {
        $clientId = $this->resolveClientId();
        
        // Só aplicar filtro se tivermos um client_id definido
        if ($clientId !== null) {
            $builder->where($model->getTable() . '.client_id', $clientId);
        }
    }

    /**
     * Resolve o client_id do contexto atual
     * 
     * Ordem de prioridade:
     * 1. Request attributes (setado pelo middleware)
     * 2. Request headers (API)
     * 3. Session
     * 4. Config (default)
     */
    protected function resolveClientId(): ?int
    {
        // 1. Request context (middleware SetClientFromSubdomain já setou)
        if (app()->bound('request')) {
            $request = app('request');
            
            // Attribute setado pelo middleware
            if ($request->attributes->has('client_id')) {
                return (int) $request->attributes->get('client_id');
            }
            
            // Query parameter (para testes/debug)
            if ($request->has('_client_id')) {
                return (int) $request->get('_client_id');
            }
            
            // Header X-Client-Id (para chamadas API)
            if ($request->hasHeader('X-Client-Id')) {
                return (int) $request->header('X-Client-Id');
            }
        }

        // 2. Session (para contexto web persistente)
        if (session()->has('client_id')) {
            return (int) session('client_id');
        }

        // 3. Configuração global (cliente padrão)
        $defaultClientId = config('olika.default_client_id');
        if ($defaultClientId) {
            return (int) $defaultClientId;
        }

        // 4. Usuário autenticado pode ter client_id
        if (auth()->check()) {
            $user = auth()->user();
            if (isset($user->client_id) && $user->client_id) {
                return (int) $user->client_id;
            }
        }

        // Sem client_id = não aplicar filtro (ambiente de admin geral, etc)
        return null;
    }

    /**
     * Extensões do scope (métodos extras disponíveis no query builder)
     */
    public function extend(Builder $builder): void
    {
        // Método para desabilitar o scope em uma query específica
        $builder->macro('withoutClient', function (Builder $builder) {
            return $builder->withoutGlobalScope(self::class);
        });

        // Método para forçar um client_id específico
        $builder->macro('forClient', function (Builder $builder, int $clientId) {
            return $builder->withoutGlobalScope(self::class)
                          ->where('client_id', $clientId);
        });

        // Método para incluir registros globais (client_id = null)
        $builder->macro('withGlobalRecords', function (Builder $builder) {
            $clientId = (new self)->resolveClientId();
            
            return $builder->withoutGlobalScope(self::class)
                          ->where(function ($q) use ($clientId) {
                              $q->where('client_id', $clientId)
                                ->orWhereNull('client_id');
                          });
        });

        // Método para pegar apenas registros globais
        $builder->macro('onlyGlobal', function (Builder $builder) {
            return $builder->withoutGlobalScope(self::class)
                          ->whereNull('client_id');
        });
    }
}
