<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPlan
{
    /**
     * Verifica se o cliente tem plano suficiente para acessar o recurso
     * 
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        
        if (!$user || !$user->client) {
            return response()->json([
                'error' => 'Client not found',
                'message' => 'Usuário não está vinculado a um cliente'
            ], 403);
        }

        $client = $user->client;

        // Verificar se o cliente está ativo
        if (!$client->active) {
            return response()->json([
                'error' => 'Client inactive',
                'message' => 'Cliente está inativo'
            ], 403);
        }

        // Verificar se é rota de IA e o cliente não tem plano IA
        if ($request->is('api/whatsapp/*') || $request->is('api/ai-status*')) {
            if (!$client->hasIaPlan()) {
                return response()->json([
                    'error' => 'Plan not allowed',
                    'message' => 'Plano básico — integração IA não disponível.'
                ], 403);
            }
        }

        return $next($request);
    }
}

