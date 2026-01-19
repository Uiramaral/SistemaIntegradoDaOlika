<?php

namespace App\Models\Traits;

use App\Models\Client;
use App\Models\Scopes\ClientScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Trait BelongsToClient
 * 
 * Adiciona funcionalidade multi-tenant aos models.
 * Qualquer model que use este trait será automaticamente:
 * - Filtrado pelo client_id do tenant atual (via Global Scope)
 * - Associado ao client_id atual ao criar novos registros
 * 
 * USO:
 * class Product extends Model {
 *     use BelongsToClient;
 * }
 */
trait BelongsToClient
{
    /**
     * Boot do trait - registra o Global Scope e evento de criação
     */
    protected static function bootBelongsToClient(): void
    {
        // Registrar Global Scope para auto-filtrar por client_id
        static::addGlobalScope(new ClientScope);

        // Ao criar um novo registro, definir client_id automaticamente
        // IMPORTANTE: Só define se não foi passado explicitamente (respeita valores passados)
        static::creating(function ($model) {
            // Se client_id não foi definido ou está null/0/vazio, definir automaticamente
            // Mas respeitar se foi passado explicitamente um valor válido (> 0)
            if (empty($model->client_id) || $model->client_id === 0) {
                $autoClientId = static::getCurrentClientId();
                if ($autoClientId) {
                    $model->client_id = $autoClientId;
                }
            }
        });
    }

    /**
     * Relacionamento com o Client (tenant)
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Obtém o client_id atual do contexto
     * 
     * Prioridade:
     * 1. Request (middleware já definiu)
     * 2. Sessão
     * 3. Configuração padrão
     */
    public static function getCurrentClientId(): ?int
    {
        // 1. Verificar se está no contexto de request (web/api)
        if (app()->bound('request')) {
            $request = app('request');
            
            // Middleware pode ter setado
            if ($request->has('_client_id')) {
                return (int) $request->get('_client_id');
            }
            
            // Ou pode estar no header (API)
            if ($request->hasHeader('X-Client-Id')) {
                return (int) $request->header('X-Client-Id');
            }
        }

        // 2. Verificar sessão
        if (session()->has('client_id')) {
            return (int) session('client_id');
        }

        // 3. Verificar configuração global (cliente padrão)
        if (config('olika.default_client_id')) {
            return (int) config('olika.default_client_id');
        }

        // 4. Se estiver autenticado, tentar pegar do user
        if (auth()->check() && method_exists(auth()->user(), 'client_id')) {
            return auth()->user()->client_id;
        }

        return null;
    }

    /**
     * Define o client_id no contexto atual (para jobs, comandos, etc)
     */
    public static function setCurrentClientId(?int $clientId): void
    {
        session(['client_id' => $clientId]);
    }

    /**
     * Scope para filtrar por um client específico (desabilita o global scope)
     * 
     * Uso: Product::forClient(5)->get()
     */
    public function scopeForClient($query, int $clientId)
    {
        return $query->withoutGlobalScope(ClientScope::class)
                     ->where('client_id', $clientId);
    }

    /**
     * Scope para incluir todos os clients (admin/super-admin)
     * 
     * Uso: Product::allClients()->get()
     */
    public function scopeAllClients($query)
    {
        return $query->withoutGlobalScope(ClientScope::class);
    }

    /**
     * Scope para registros sem client (globais/compartilhados)
     * 
     * Uso: Allergen::global()->get()
     */
    public function scopeGlobal($query)
    {
        return $query->withoutGlobalScope(ClientScope::class)
                     ->whereNull('client_id');
    }

    /**
     * Scope para registros do client atual OU globais
     * 
     * Uso: Allergen::withGlobal()->get()
     */
    public function scopeWithGlobal($query)
    {
        $clientId = static::getCurrentClientId();
        
        return $query->withoutGlobalScope(ClientScope::class)
                     ->where(function ($q) use ($clientId) {
                         $q->where('client_id', $clientId)
                           ->orWhereNull('client_id');
                     });
    }
}
