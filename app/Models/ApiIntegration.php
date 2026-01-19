<?php

namespace App\Models;

use App\Models\Traits\BelongsToClient;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiIntegration extends Model
{
    use HasFactory, BelongsToClient;

    protected $fillable = [
        'client_id',
        'provider',
        'is_enabled',
        'credentials',
        'settings',
        'last_tested_at',
        'last_test_status',
        'last_error',
    ];

    protected $casts = [
        'credentials' => 'array',
        'settings' => 'array',
        'is_enabled' => 'boolean',
        'last_tested_at' => 'datetime',
    ];

    /**
     * Providers disponíveis
     */
    public const PROVIDERS = [
        'gemini' => [
            'name' => 'Google Gemini AI',
            'icon' => 'sparkles',
            'color' => 'blue',
            'fields' => [
                'api_key' => ['label' => 'API Key', 'type' => 'password', 'required' => true],
            ],
            'settings_fields' => [
                'model' => [
                    'label' => 'Modelo', 
                    'type' => 'select', 
                    'options' => [
                        'gemini-2.5-flash' => 'Gemini 2.5 Flash (Recomendado - Chat rápido)',
                        'gemini-2.5-flash-lite' => 'Gemini 2.5 Flash Lite (Econômico - Notificações)',
                        'gemini-3-pro' => 'Gemini 3 Pro (Avançado - Análises)',
                    ], 
                    'default' => 'gemini-2.5-flash'
                ],
                'temperature' => ['label' => 'Temperature', 'type' => 'number', 'min' => 0, 'max' => 1, 'step' => 0.1, 'default' => 0.7],
                'max_tokens' => ['label' => 'Max Tokens', 'type' => 'number', 'default' => 500],
            ],
        ],
        'openai' => [
            'name' => 'OpenAI (ChatGPT)',
            'icon' => 'brain-circuit',
            'color' => 'green',
            'fields' => [
                'api_key' => ['label' => 'API Key', 'type' => 'password', 'required' => true],
            ],
            'settings_fields' => [
                'model' => ['label' => 'Modelo', 'type' => 'select', 'options' => ['gpt-3.5-turbo', 'gpt-4', 'gpt-4-turbo'], 'default' => 'gpt-3.5-turbo'],
                'temperature' => ['label' => 'Temperature', 'type' => 'number', 'min' => 0, 'max' => 2, 'step' => 0.1, 'default' => 0.7],
                'max_tokens' => ['label' => 'Max Tokens', 'type' => 'number', 'default' => 500],
            ],
        ],
        'mercadopago' => [
            'name' => 'Mercado Pago',
            'icon' => 'credit-card',
            'color' => 'cyan',
            'fields' => [
                'access_token' => ['label' => 'Access Token', 'type' => 'password', 'required' => true],
                'public_key' => ['label' => 'Public Key', 'type' => 'text', 'required' => true],
            ],
            'settings_fields' => [
                'webhook_url' => ['label' => 'Webhook URL', 'type' => 'url', 'default' => ''],
                'notification_url' => ['label' => 'Notification URL', 'type' => 'url', 'default' => ''],
            ],
        ],
        'pagseguro' => [
            'name' => 'PagSeguro',
            'icon' => 'wallet',
            'color' => 'orange',
            'fields' => [
                'email' => ['label' => 'E-mail', 'type' => 'email', 'required' => true],
                'token' => ['label' => 'Token', 'type' => 'password', 'required' => true],
            ],
            'settings_fields' => [
                'sandbox' => ['label' => 'Modo Sandbox', 'type' => 'checkbox', 'default' => false],
            ],
        ],
        'whatsapp_evolution' => [
            'name' => 'WhatsApp (Evolution API)',
            'icon' => 'message-circle',
            'color' => 'green',
            'fields' => [
                'api_url' => ['label' => 'API URL', 'type' => 'url', 'required' => true, 'placeholder' => 'https://api.evolution.com.br'],
                'api_key' => ['label' => 'API Key', 'type' => 'password', 'required' => true],
                'instance_name' => ['label' => 'Nome da Instância', 'type' => 'text', 'required' => true],
            ],
            'settings_fields' => [
                'sender_name' => ['label' => 'Nome do Remetente', 'type' => 'text', 'default' => 'Olika Bot'],
            ],
        ],
    ];

    /**
     * Obter integração por provider
     */
    public static function getByProvider(string $provider): ?self
    {
        $clientId = currentClientId();
        
        if (!$clientId) {
            return null;
        }
        
        return self::where('client_id', $clientId)
            ->where('provider', $provider)
            ->first();
    }

    /**
     * Obter ou criar integração
     */
    public static function getOrCreateByProvider(string $provider): self
    {
        $clientId = currentClientId();
        
        if (!$clientId) {
            throw new \Exception('client_id não encontrado no contexto atual. Certifique-se de que o usuário está autenticado e tem um estabelecimento associado.');
        }
        
        // Primeiro tentar buscar existente
        $integration = self::where('client_id', $clientId)
            ->where('provider', $provider)
            ->first();
        
        if ($integration) {
            return $integration;
        }
        
        // Se não existe, tentar criar (com tratamento de race condition)
        try {
            return self::create([
                'client_id' => $clientId,
                'provider' => $provider,
                'is_enabled' => false,
                'credentials' => [],
                'settings' => [],
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            // Se deu erro de duplicate (race condition), buscar novamente
            if ($e->getCode() == 23000) { // Integrity constraint violation
                $integration = self::where('client_id', $clientId)
                    ->where('provider', $provider)
                    ->first();
                
                if ($integration) {
                    return $integration;
                }
            }
            
            throw $e;
        }
    }

    /**
     * Verificar se está ativo e configurado
     */
    public function isActive(): bool
    {
        if (!$this->is_enabled) {
            return false;
        }

        // Verificar se tem credenciais obrigatórias
        $providerConfig = self::PROVIDERS[$this->provider] ?? [];
        $requiredFields = array_filter($providerConfig['fields'] ?? [], fn($f) => $f['required'] ?? false);

        foreach (array_keys($requiredFields) as $field) {
            if (empty($this->credentials[$field] ?? null)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Obter valor de credencial
     */
    public function getCredential(string $key, $default = null)
    {
        return $this->credentials[$key] ?? $default;
    }

    /**
     * Obter valor de configuração
     */
    public function getSetting(string $key, $default = null)
    {
        return $this->settings[$key] ?? $default;
    }

    /**
     * Atualizar status do teste
     */
    public function updateTestStatus(bool $success, ?string $error = null): void
    {
        $this->update([
            'last_tested_at' => now(),
            'last_test_status' => $success ? 'success' : 'failed',
            'last_error' => $error,
        ]);
    }

    /**
     * Accessor para nome do provider
     */
    public function getProviderNameAttribute(): string
    {
        return self::PROVIDERS[$this->provider]['name'] ?? ucfirst($this->provider);
    }

    /**
     * Accessor para ícone do provider
     */
    public function getProviderIconAttribute(): string
    {
        return self::PROVIDERS[$this->provider]['icon'] ?? 'plug';
    }

    /**
     * Accessor para cor do provider
     */
    public function getProviderColorAttribute(): string
    {
        return self::PROVIDERS[$this->provider]['color'] ?? 'gray';
    }
}
