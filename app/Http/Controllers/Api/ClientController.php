<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Services\RailwayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ClientController extends Controller
{
    /**
     * Retorna informações do cliente e plano para Node.js
     * 
     * Endpoint: GET /api/client/{id}
     * Headers: X-API-Token: {token}
     * 
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id, Request $request)
    {
        try {
            // Obter client_id autenticado pelo middleware
            $authenticatedClientId = $request->get('authenticated_client_id');
            
            // Verificar se o token autenticado pertence ao cliente solicitado
            if ($authenticatedClientId && (int)$authenticatedClientId !== (int)$id) {
                Log::warning('ClientController: Tentativa de acessar cliente diferente do token', [
                    'token_client_id' => $authenticatedClientId,
                    'requested_client_id' => $id,
                    'ip' => $request->ip()
                ]);
                return response()->json(['error' => 'Token não autorizado para este cliente'], 403);
            }

            $client = Client::with('activeApiToken')
                ->findOrFail($id);

            return response()->json([
                'id' => $client->id,
                'name' => $client->name,
                'slug' => $client->slug,
                'plan' => $client->plan,
                'instance_url' => $client->instance_url,
                'whatsapp_phone' => $client->whatsapp_phone,
                'active' => $client->active,
                'has_ia' => $client->hasIaPlan(),
            ]);

        } catch (\Exception $e) {
            Log::error('ClientController: Erro ao buscar cliente', [
                'error' => $e->getMessage(),
                'client_id' => $id
            ]);
            
            return response()->json([
                'error' => 'Client not found',
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Retorna informações do plano do cliente
     * 
     * Endpoint: GET /api/client/{id}/plan
     * Headers: X-API-Token: {token}
     * 
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPlan($id, Request $request)
    {
        try {
            // Obter client_id autenticado pelo middleware
            $authenticatedClientId = $request->get('authenticated_client_id');
            
            // Verificar se o token autenticado pertence ao cliente solicitado
            if ($authenticatedClientId && (int)$authenticatedClientId !== (int)$id) {
                return response()->json(['error' => 'Token não autorizado para este cliente'], 403);
            }

            $client = Client::findOrFail($id);

            return response()->json([
                'plan' => $client->plan,
                'has_ia' => $client->hasIaPlan(),
                'active' => $client->active,
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Client not found'], 404);
        }
    }

    /**
     * Deploy de instância Railway para o cliente
     * 
     * Endpoint: POST /api/clients/{id}/deploy
     * Headers: Authorization (usuário autenticado)
     * 
     * @param int $id
     * @param Request $request
     * @param RailwayService $railwayService
     * @return \Illuminate\Http\JsonResponse
     */
    public function deploy($id, Request $request, RailwayService $railwayService)
    {
        try {
            $client = Client::findOrFail($id);

            // Verificar se o cliente tem plano IA
            if (!$client->hasIaPlan()) {
                return response()->json([
                    'error' => 'Plano básico não permite instância IA',
                    'message' => 'Apenas clientes com plano IA podem ter instância Railway'
                ], 403);
            }

            // Verificar se já tem instância
            if ($client->instance) {
                return response()->json([
                    'message' => 'Cliente já possui instância Railway',
                    'instance' => [
                        'url' => $client->instance->url,
                        'status' => $client->instance->status,
                    ]
                ], 200);
            }

            // Clonar serviço Railway
            $instance = $railwayService->cloneServiceForClient($client);

            Log::info('ClientController::deploy - Instância criada', [
                'client_id' => $client->id,
                'instance_id' => $instance->id,
                'url' => $instance->url
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Instância Railway criada com sucesso!',
                'instance' => [
                    'id' => $instance->id,
                    'url' => $instance->url,
                    'status' => $instance->status,
                ],
                'client' => [
                    'id' => $client->id,
                    'name' => $client->name,
                    'slug' => $client->slug,
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('ClientController::deploy - Erro ao criar instância', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'client_id' => $id
            ]);

            return response()->json([
                'error' => 'Erro ao criar instância Railway',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}

