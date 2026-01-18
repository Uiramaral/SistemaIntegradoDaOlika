<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ApiToken;
use Illuminate\Support\Facades\Log;

class ApiTokenMiddleware
{
    /**
     * Middleware para autenticação via token da tabela api_tokens
     * 
     * Valida o token enviado no header X-API-Token contra a tabela api_tokens
     * e adiciona o client_id ao request para uso posterior
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $tokenHeader = $request->header('X-API-Token');
        
        if (!$tokenHeader) {
            Log::warning('ApiTokenMiddleware: Token ausente', [
                'ip' => $request->ip(),
                'url' => $request->fullUrl()
            ]);
            return response()->json(['error' => 'Token ausente'], 401);
        }

        // Buscar token na tabela api_tokens com relacionamento client
        $apiToken = ApiToken::with('client')
            ->where('token', $tokenHeader)
            ->where(function($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
            })
            ->first();

        if (!$apiToken) {
            Log::warning('ApiTokenMiddleware: Token inválido ou expirado', [
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
                'token_prefix' => substr($tokenHeader, 0, 10) . '...'
            ]);
            return response()->json(['error' => 'Token inválido'], 403);
        }

        // Verificar se o cliente está ativo
        if (!$apiToken->client || !$apiToken->client->active) {
            Log::warning('ApiTokenMiddleware: Cliente inativo', [
                'client_id' => $apiToken->client_id,
                'ip' => $request->ip()
            ]);
            return response()->json(['error' => 'Cliente inativo'], 403);
        }

        // Adicionar client_id ao request para uso posterior
        $request->merge(['authenticated_client_id' => $apiToken->client_id]);
        
        // Disponibilizar o cliente no container para uso em controllers
        app()->instance('authenticated_client', $apiToken->client);

        return $next($request);
    }
}

