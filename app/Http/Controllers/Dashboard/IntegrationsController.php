<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\ApiIntegration;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class IntegrationsController extends Controller
{
    /**
     * Listar todas as integrações disponíveis
     */
    public function index()
    {
        $providers = ApiIntegration::PROVIDERS;
        $integrations = [];

        // Buscar configurações salvas para cada provider
        foreach (array_keys($providers) as $providerKey) {
            $integration = ApiIntegration::getOrCreateByProvider($providerKey);
            $integrations[$providerKey] = $integration;
        }

        return view('dashboard.integrations.index', compact('providers', 'integrations'));
    }

    /**
     * Atualizar configurações de uma integração
     */
    public function update(Request $request, string $provider)
    {
        // Validar provider
        if (!isset(ApiIntegration::PROVIDERS[$provider])) {
            return back()->with('error', 'Provider inválido.');
        }

        $providerConfig = ApiIntegration::PROVIDERS[$provider];
        
        // Montar regras de validação dinamicamente
        $rules = [];
        
        // Validar credenciais
        foreach ($providerConfig['fields'] as $field => $config) {
            $fieldRules = [];
            if ($config['required'] ?? false) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = 'nullable';
            }
            
            switch ($config['type']) {
                case 'email':
                    $fieldRules[] = 'email';
                    break;
                case 'url':
                    $fieldRules[] = 'url';
                    break;
                case 'number':
                    $fieldRules[] = 'numeric';
                    break;
                default:
                    $fieldRules[] = 'string';
            }
            
            $rules["credentials.{$field}"] = implode('|', $fieldRules);
        }

        // Validar settings
        foreach ($providerConfig['settings_fields'] ?? [] as $field => $config) {
            $rules["settings.{$field}"] = 'nullable';
        }

        $validated = $request->validate($rules);

        // Buscar ou criar integração
        $integration = ApiIntegration::getOrCreateByProvider($provider);

        // Atualizar credenciais e settings
        $integration->update([
            'credentials' => $validated['credentials'] ?? [],
            'settings' => $validated['settings'] ?? [],
        ]);

        return back()->with('success', "{$providerConfig['name']} atualizado com sucesso!");
    }

    /**
     * Testar conexão com a API
     */
    public function test(string $provider)
    {
        if (!isset(ApiIntegration::PROVIDERS[$provider])) {
            return response()->json([
                'success' => false,
                'message' => 'Provider inválido',
            ], 400);
        }

        $integration = ApiIntegration::getByProvider($provider);

        if (!$integration || !$integration->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Integração não configurada ou inativa',
            ], 400);
        }

        // Testar baseado no provider
        $result = $this->testProviderConnection($provider, $integration);

        return response()->json($result);
    }

    /**
     * Ativar/desativar integração
     */
    public function toggle(string $provider)
    {
        if (!isset(ApiIntegration::PROVIDERS[$provider])) {
            return back()->with('error', 'Provider inválido.');
        }

        $integration = ApiIntegration::getByProvider($provider);

        if (!$integration) {
            return back()->with('error', 'Integração não encontrada.');
        }

        // Verificar se tem credenciais antes de ativar
        if (!$integration->is_enabled) {
            // Tentando ativar - verificar credenciais
            $providerConfig = ApiIntegration::PROVIDERS[$provider];
            $requiredFields = array_filter($providerConfig['fields'] ?? [], fn($f) => $f['required'] ?? false);

            foreach (array_keys($requiredFields) as $field) {
                if (empty($integration->credentials[$field] ?? null)) {
                    return back()->with('error', 'Configure as credenciais antes de ativar.');
                }
            }
        }

        $integration->update(['is_enabled' => !$integration->is_enabled]);

        $status = $integration->is_enabled ? 'ativada' : 'desativada';
        return back()->with('success', "Integração {$status} com sucesso!");
    }

    /**
     * Testar conexão específica por provider
     */
    private function testProviderConnection(string $provider, ApiIntegration $integration): array
    {
        try {
            switch ($provider) {
                case 'gemini':
                    $service = new GeminiService();
                    return $service->testConnection();

                case 'openai':
                    return $this->testOpenAI($integration);

                case 'mercadopago':
                    return $this->testMercadoPago($integration);

                case 'whatsapp_evolution':
                    return $this->testWhatsApp($integration);

                default:
                    return [
                        'success' => false,
                        'message' => 'Teste não implementado para este provider',
                    ];
            }
        } catch (\Exception $e) {
            Log::error("Erro ao testar {$provider}: " . $e->getMessage());
            
            $integration->updateTestStatus(false, $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Erro: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Testar OpenAI
     */
    private function testOpenAI(ApiIntegration $integration): array
    {
        $apiKey = $integration->getCredential('api_key');
        $model = $integration->getSetting('model', 'gpt-3.5-turbo');

        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(30)->post('https://api.openai.com/v1/chat/completions', [
            'model' => $model,
            'messages' => [
                ['role' => 'user', 'content' => 'Responda apenas: OK']
            ],
            'max_tokens' => 10,
        ]);

        if ($response->successful()) {
            $integration->updateTestStatus(true);
            return [
                'success' => true,
                'message' => 'Conexão com OpenAI OK!',
                'response' => $response->json()['choices'][0]['message']['content'] ?? 'OK',
            ];
        }

        $integration->updateTestStatus(false, $response->body());
        return [
            'success' => false,
            'message' => 'Erro na API OpenAI: ' . $response->status(),
        ];
    }

    /**
     * Testar Mercado Pago
     */
    private function testMercadoPago(ApiIntegration $integration): array
    {
        $accessToken = $integration->getCredential('access_token');

        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
        ])->timeout(30)->get('https://api.mercadopago.com/v1/users/me');

        if ($response->successful()) {
            $data = $response->json();
            $integration->updateTestStatus(true);
            return [
                'success' => true,
                'message' => 'Conexão com Mercado Pago OK!',
                'user' => $data['nickname'] ?? $data['email'] ?? 'Conectado',
            ];
        }

        $integration->updateTestStatus(false, $response->body());
        return [
            'success' => false,
            'message' => 'Erro na API Mercado Pago: ' . $response->status(),
        ];
    }

    /**
     * Testar WhatsApp Evolution API
     */
    private function testWhatsApp(ApiIntegration $integration): array
    {
        $apiUrl = rtrim($integration->getCredential('api_url'), '/');
        $apiKey = $integration->getCredential('api_key');
        $instance = $integration->getCredential('instance_name');

        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'apikey' => $apiKey,
        ])->timeout(30)->get("{$apiUrl}/instance/connectionState/{$instance}");

        if ($response->successful()) {
            $data = $response->json();
            $integration->updateTestStatus(true);
            return [
                'success' => true,
                'message' => 'Conexão com WhatsApp OK!',
                'state' => $data['state'] ?? 'connected',
            ];
        }

        $integration->updateTestStatus(false, $response->body());
        return [
            'success' => false,
            'message' => 'Erro na Evolution API: ' . $response->status(),
        ];
    }
}
