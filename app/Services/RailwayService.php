<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Client;
use App\Models\Instance;
use App\Models\ApiToken;

class RailwayService
{
    protected $apiKey;
    protected $serviceId; // Serviço modelo base
    protected $environmentId;

    public function __construct()
    {
        $this->apiKey = env('RAILWAY_API_KEY');
        $this->serviceId = env('RAILWAY_SERVICE_ID');
        $this->environmentId = env('RAILWAY_ENVIRONMENT_ID');
    }

    /**
     * Clona o serviço modelo Railway para um novo cliente
     * 
     * @param Client $client
     * @return Instance
     * @throws \Exception
     */
    public function cloneServiceForClient(Client $client)
    {
        if (!$this->apiKey || !$this->serviceId || !$this->environmentId) {
            throw new \Exception('Configuração Railway incompleta. Verifique RAILWAY_API_KEY, RAILWAY_SERVICE_ID e RAILWAY_ENVIRONMENT_ID no .env');
        }

        // Verificar se o cliente tem plano IA
        if (!$client->hasIaPlan()) {
            throw new \Exception('Apenas clientes com plano IA podem ter instância Railway');
        }

        // Garantir que o cliente tenha um token
        $token = $client->activeApiToken;
        if (!$token) {
            Log::info('RailwayService: Gerando token para cliente', ['client_id' => $client->id]);
            $tokenValue = $client->regenerateApiToken();
            $token = ApiToken::where('token', $tokenValue)->first();
        }

        $serviceName = $client->slug . '-ia';

        try {
            Log::info('RailwayService: Iniciando clonagem de serviço', [
                'client_id' => $client->id,
                'service_name' => $serviceName,
                'template_service_id' => $this->serviceId
            ]);

            // Clone o serviço modelo usando GraphQL API (mutation atualizada: serviceClone)
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json',
            ])->post('https://backboard.railway.app/graphql/v2', [
                'query' => '
                    mutation CloneService($input: ServiceCloneInput!) {
                        serviceClone(input: $input) {
                            id
                            name
                            deployments {
                                edges {
                                    node {
                                        url
                                    }
                                }
                            }
                        }
                    }',
                'variables' => [
                    'input' => [
                        'sourceServiceId' => $this->serviceId,
                        'name' => $serviceName,
                        'environmentId' => $this->environmentId,
                    ],
                ],
            ]);

            if (!$response->successful()) {
                Log::error('RailwayService: Erro HTTP ao clonar serviço', [
                    'response' => $response->body(),
                    'status' => $response->status(),
                    'client_id' => $client->id,
                    'service_id' => $this->serviceId,
                    'environment_id' => $this->environmentId,
                ]);
                throw new \Exception('Erro ao clonar serviço Railway: ' . $response->body());
            }

            $responseData = $response->json();
            
            if (isset($responseData['errors'])) {
                Log::error('RailwayService: Erro GraphQL', [
                    'errors' => $responseData['errors'],
                    'client_id' => $client->id,
                    'full_response' => $responseData
                ]);
                throw new \Exception('Erro GraphQL: ' . json_encode($responseData['errors']));
            }

            $serviceData = $responseData['data']['serviceClone'] ?? null;
            
            if (!$serviceData) {
                Log::error('RailwayService: Resposta inválida da API Railway', [
                    'response_data' => $responseData,
                    'client_id' => $client->id
                ]);
                throw new \Exception('Resposta inválida da API Railway. Dados: ' . json_encode($responseData));
            }

            $newServiceId = $serviceData['id'] ?? null;
            $deployments = $serviceData['deployments']['edges'] ?? [];
            $url = $deployments[0]['node']['url'] ?? null;
            
            if (!$newServiceId) {
                throw new \Exception('Service ID não retornado pela API Railway');
            }

            // Se não tiver URL ainda, aguardar um pouco (deployment pode estar em andamento)
            if (!$url) {
                Log::warning('RailwayService: URL não disponível imediatamente, aguardando deployment', [
                    'service_id' => $newServiceId,
                    'client_id' => $client->id
                ]);
                // Em produção, você pode implementar um retry aqui
                $url = "https://{$serviceName}.railway.app"; // URL padrão do Railway
            }

            // Criar ou atualizar registro de instância
            $instance = Instance::updateOrCreate(
                ['assigned_to' => $client->id],
                [
                    'url' => $url,
                    'status' => 'assigned',
                ]
            );

            // Atualizar cliente com URL da instância
            $client->update(['instance_url' => $url]);

            // Definir variáveis de ambiente
            $this->setEnvVars($newServiceId, [
                'CLIENT_ID' => (string)$client->id,
                'API_TOKEN' => $token->token,
                'LARAVEL_API_URL' => config('app.url') ?: env('APP_URL'),
                'OPENAI_MODEL' => env('OPENAI_MODEL', 'gpt-5-nano'),
                'OPENAI_API_KEY' => env('OPENAI_API_KEY'),
                'WH_API_TOKEN' => $token->token,
                'WEBHOOK_URL' => rtrim(config('app.url') ?: env('APP_URL'), '/') . '/api/whatsapp/webhook',
                'AI_STATUS_URL' => rtrim(config('app.url') ?: env('APP_URL'), '/') . '/api/ai-status',
                'CUSTOMER_CONTEXT_URL' => rtrim(config('app.url') ?: env('APP_URL'), '/') . '/api/customer-context',
                'AI_SYSTEM_PROMPT' => env('AI_SYSTEM_PROMPT', 'Você é um assistente profissional da Olika, otimizado para custo.'),
                'OPENAI_TIMEOUT' => env('OPENAI_TIMEOUT', '30'),
                'PORT' => '8080',
            ]);

            Log::info('RailwayService: Instância criada com sucesso', [
                'client_id' => $client->id,
                'client_name' => $client->name,
                'service_id' => $newServiceId,
                'url' => $url,
                'instance_id' => $instance->id
            ]);

            return $instance;

        } catch (\Exception $e) {
            Log::error('RailwayService: Exceção ao clonar serviço', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'client_id' => $client->id
            ]);
            throw $e;
        }
    }

    /**
     * Define variáveis de ambiente no serviço Railway
     * 
     * @param string $serviceId
     * @param array $vars
     * @return void
     */
    protected function setEnvVars($serviceId, $vars)
    {
        foreach ($vars as $key => $value) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => "Bearer {$this->apiKey}",
                    'Content-Type' => 'application/json',
                ])->post('https://backboard.railway.app/graphql/v2', [
                    'query' => '
                        mutation VariableSet($input: VariableSetInput!) {
                            variableSet(input: $input) {
                                id
                            }
                        }',
                    'variables' => [
                        'input' => [
                            'serviceId' => $serviceId,
                            'key' => $key,
                            'value' => (string)$value,
                        ],
                    ],
                ]);

                if (!$response->successful()) {
                    Log::warning('RailwayService: Erro ao definir variável', [
                        'key' => $key,
                        'service_id' => $serviceId,
                        'status' => $response->status(),
                        'response' => $response->body()
                    ]);
                } else {
                    Log::debug('RailwayService: Variável definida', [
                        'key' => $key,
                        'service_id' => $serviceId
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('RailwayService: Exceção ao definir variável', [
                    'key' => $key,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Remove uma instância Railway (deleta o serviço)
     * 
     * @param Instance $instance
     * @return bool
     */
    public function deleteService(Instance $instance)
    {
        // Implementar se necessário
        // Por enquanto, apenas marca como free
        $instance->update([
            'status' => 'free',
            'assigned_to' => null,
        ]);

        return true;
    }
}

