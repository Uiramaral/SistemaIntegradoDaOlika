<?php

namespace App\Services;

use App\Models\ApiIntegration;
use App\Models\Client;
use App\Models\AiUsageLog;
use App\Helpers\GeminiPricing;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    private ?ApiIntegration $integration = null;
    private bool $enabled = false;
    private string $apiKey = '';
    private string $model = 'gemini-2.5-flash'; // Modelo principal 2026
    private string $fallbackModel = 'gemini-2.5-flash-lite'; // Fallback econômico
    private float $temperature = 0.7;
    private int $maxTokens = 5000; // Aumentado para permitir respostas completas e detalhadas
    private string $apiVersion = 'v1'; // API estável
    private ?int $currentClientId = null; // Para tracking de uso
    private string $currentTaskType = 'chat'; // Tipo de tarefa atual

    // Modelos válidos em 2026 (ordenados por recomendação)
    private const VALID_MODELS = [
        'gemini-2.5-flash',       // Principal - Chat rápido, 1M contexto
        'gemini-2.5-flash-lite',  // Econômico - Notificações em massa
        'gemini-3-pro',           // Avançado - Análises complexas
        'gemini-2.0-flash',       // Legado
        'gemini-1.5-flash-latest', // Fallback legado
    ];

    public function __construct()
    {
        try {
            // Buscar token do Master (compartilhado)
            $this->apiKey = \App\Models\MasterSetting::get('gemini_api_key', '');
            
            // Se não tem no master, tentar do estabelecimento (fallback)
            if (empty($this->apiKey)) {
                $this->integration = ApiIntegration::getByProvider('gemini');
                
                if ($this->integration && $this->integration->isActive()) {
                    $this->apiKey = $this->integration->getCredential('api_key', '');
                }
            }
            
            if (!empty($this->apiKey)) {
                // Buscar configurações do estabelecimento (model, temperature, etc)
                $this->integration = ApiIntegration::getByProvider('gemini');
                $configuredModel = $this->integration ? $this->integration->getSetting('model', 'gemini-2.5-flash') : 'gemini-2.5-flash';
                
                // Validar e migrar modelo se necessário
                $this->model = $this->migrateModelName($configuredModel);
                $this->temperature = (float) ($this->integration ? $this->integration->getSetting('temperature', 0.7) : 0.7);
                $this->maxTokens = (int) ($this->integration ? $this->integration->getSetting('max_tokens', 5000) : 5000);
                
                $this->enabled = true;
                Log::info('GeminiService: Configurado com sucesso', [
                    'model' => $this->model,
                    'api_version' => $this->apiVersion,
                    'token_source' => 'master_settings',
                    'max_tokens' => $this->maxTokens,
                    'temperature' => $this->temperature,
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('GeminiService init error: ' . $e->getMessage());
            $this->enabled = false;
        }
    }

    /**
     * Migrar nomes de modelos deprecados para versões 2026
     */
    private function migrateModelName(string $model): string
    {
        $migrations = [
            // Modelos antigos -> Modelos 2026
            'gemini-1.5-flash' => 'gemini-2.5-flash',
            'gemini-1.5-flash-8b' => 'gemini-2.5-flash-lite',
            'gemini-1.5-pro' => 'gemini-3-pro',
            'gemini-1.5-pro-latest' => 'gemini-3-pro',
            'gemini-pro' => 'gemini-2.5-flash',
            'gemini-flash' => 'gemini-2.5-flash',
            'gemini-2.0-flash' => 'gemini-2.5-flash',
            'gemini-2.0-flash-lite' => 'gemini-2.5-flash-lite',
            'gemini-2.0-flash-exp' => 'gemini-2.5-flash',
        ];

        if (isset($migrations[$model])) {
            Log::info('GeminiService: Modelo migrado para 2026', [
                'old' => $model,
                'new' => $migrations[$model],
            ]);
            return $migrations[$model];
        }

        return $model;
    }

    /**
     * Verificar se está habilitado
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Personalizar mensagem de marketing com IA
     * 
     * @param string $baseMessage Template base com variáveis já substituídas
     * @param string $customerName Nome do cliente
     * @param array $context Contexto adicional (cashback, pedidos, etc)
     * @return string Mensagem personalizada ou mensagem original se falhar
     */
    public function personalizeMarketingMessage(string $baseMessage, string $customerName, array $context = []): string
    {
        if (!$this->enabled) {
            return $baseMessage;
        }

        try {
            $prompt = $this->buildMarketingPrompt($baseMessage, $customerName, $context);
            $response = $this->generateContent($prompt);

            if ($response) {
                Log::info('GeminiService: Mensagem personalizada com sucesso', [
                    'customer' => $customerName,
                    'original_length' => strlen($baseMessage),
                    'personalized_length' => strlen($response),
                ]);
                return $response;
            }

            return $baseMessage;
        } catch (\Exception $e) {
            Log::error('GeminiService: Erro ao personalizar mensagem', [
                'error' => $e->getMessage(),
                'customer' => $customerName,
            ]);
            return $baseMessage;
        }
    }

    /**
     * Construir prompt para personalização de marketing
     */
    private function buildMarketingPrompt(string $baseMessage, string $customerName, array $context): string
    {
        $contextInfo = '';
        if (isset($context['cashback']) && $context['cashback'] > 0) {
            $contextInfo .= "- Cliente tem R$ " . number_format($context['cashback'], 2, ',', '.') . " de cashback disponível\n";
        }
        if (isset($context['total_pedidos'])) {
            $contextInfo .= "- Cliente já fez {$context['total_pedidos']} pedido(s)\n";
        }
        if (isset($context['ultimo_pedido'])) {
            $contextInfo .= "- Último pedido: {$context['ultimo_pedido']}\n";
        }

        return <<<PROMPT
Você é um especialista em marketing conversacional para WhatsApp. Sua tarefa é REESCREVER a mensagem abaixo de forma mais atraente, empática e persuasiva, mantendo o MESMO CONTEÚDO e informações.

REGRAS IMPORTANTES:
1. Mantenha TODAS as informações da mensagem original (valores, datas, números)
2. Use emojis estrategicamente (mas sem exagero - máximo 3-4)
3. Tom amigável, informal mas profissional
4. Frases curtas e diretas
5. Crie senso de urgência ou exclusividade quando apropriado
6. Máximo de 160 caracteres (tamanho ideal para WhatsApp)
7. Não invente informações que não estão na mensagem original
8. Use quebras de linha para facilitar leitura

CONTEXTO DO CLIENTE:
Nome: {$customerName}
{$contextInfo}

MENSAGEM ORIGINAL:
{$baseMessage}

REESCREVA A MENSAGEM ACIMA de forma mais atraente e pessoal, mantendo todas as informações importantes.
Responda APENAS com a mensagem reescrita, sem explicações adicionais.
PROMPT;
    }

    /**
     * Gerar conteúdo usando Gemini API com fallback automático
     */
    public function generateContent(string $prompt): ?string
    {
        if (!$this->enabled) {
            return null;
        }

        // Tentar modelo principal
        $result = $this->callGeminiApi($this->model, $prompt);
        
        if ($result !== null) {
            return $result;
        }

        // Fallback para modelo secundário se principal falhar
        Log::warning('GeminiService: Modelo principal falhou, tentando fallback', [
            'modelo_principal' => $this->model,
            'modelo_fallback' => $this->fallbackModel,
        ]);

        return $this->callGeminiApi($this->fallbackModel, $prompt);
    }

    /**
     * Gerar conteúdo com instrução de sistema (persona/contexto)
     * Usado pelo Assistente IA com variações (Cardápio, Marketing, etc.)
     */
    public function generateWithSystemInstruction(string $systemInstruction, string $userPrompt): ?string
    {
        if (!$this->enabled) {
            return null;
        }

        $result = $this->callGeminiApiWithSystemInstruction($this->model, $systemInstruction, $userPrompt);
        if ($result !== null) {
            return $result;
        }

        Log::warning('GeminiService: Modelo principal falhou (systemInstruction), tentando fallback', [
            'modelo_principal' => $this->model,
            'modelo_fallback' => $this->fallbackModel,
        ]);
        return $this->callGeminiApiWithSystemInstruction($this->fallbackModel, $systemInstruction, $userPrompt);
    }

    /**
     * Chamada à API do Gemini com systemInstruction (Assistente IA)
     */
    private function callGeminiApiWithSystemInstruction(string $model, string $systemInstruction, string $userPrompt): ?string
    {
        try {
            $version = 'v1beta'; // systemInstruction suportado em v1beta
            $url = "https://generativelanguage.googleapis.com/{$version}/models/{$model}:generateContent?key={$this->apiKey}";
            $payload = [
                'systemInstruction' => [
                    'parts' => [['text' => $systemInstruction]],
                ],
                'contents' => [
                    ['parts' => [['text' => $userPrompt]]],
                ],
                'generationConfig' => [
                    'temperature' => $this->temperature,
                    'maxOutputTokens' => $this->maxTokens,
                    'topP' => 0.8,
                    'topK' => 10,
                ],
            ];

            Log::info('GeminiService: chamando API (systemInstruction)', ['model' => $model]);
            $response = Http::timeout(60)->post($url, $payload);
            $fullPrompt = $systemInstruction . "\n\n---\n\n" . $userPrompt;

            if ($response->successful()) {
                $data = $response->json();
                
                // Extrair texto de todas as partes do primeiro candidato
                $text = '';
                if (!empty($data['candidates'][0]['content']['parts'])) {
                    foreach ($data['candidates'][0]['content']['parts'] as $part) {
                        if (isset($part['text'])) {
                            $text .= $part['text'];
                        }
                    }
                }
                
                // Verificar finishReason para ver se a resposta foi completada
                $finishReason = $data['candidates'][0]['finishReason'] ?? null;
                if ($finishReason === 'MAX_TOKENS') {
                    Log::warning('GeminiService: Resposta truncada por limite de tokens', [
                        'model' => $model,
                        'reply_len' => strlen($text),
                        'max_tokens' => $this->maxTokens,
                    ]);
                }
                
                if (empty($text)) {
                    Log::warning('GeminiService: resposta sem texto (systemInstruction)', [
                        'model' => $model,
                        'has_candidates' => !empty($data['candidates']),
                        'finish_reason' => $finishReason,
                        'body_preview' => substr(json_encode($data), 0, 500),
                    ]);
                } else {
                    Log::info('GeminiService: API ok (systemInstruction)', [
                        'model' => $model,
                        'reply_len' => strlen($text),
                        'finish_reason' => $finishReason,
                    ]);
                }
                $inputTokens = $data['usageMetadata']['promptTokenCount'] ?? $this->estimateTokens($fullPrompt);
                $outputTokens = $data['usageMetadata']['candidatesTokenCount'] ?? $this->estimateTokens($text ?? '');
                if ($this->currentClientId && $text) {
                    $this->logUsage($model, $inputTokens, $outputTokens, $fullPrompt, $text, true);
                }
                return $text ? trim($text) : null;
            }

            if ($this->currentClientId) {
                $this->logUsage($model, $this->estimateTokens($fullPrompt), 0, $fullPrompt, null, false, $response->body());
            }
            Log::error('GeminiService: Erro na API (systemInstruction)', [
                'model' => $model,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return null;
        } catch (\Exception $e) {
            if ($this->currentClientId) {
                $this->logUsage($model, $this->estimateTokens($systemInstruction . $userPrompt), 0, $userPrompt, null, false, $e->getMessage());
            }
            Log::error('GeminiService: Exception generateWithSystemInstruction', ['model' => $model, 'error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Chamada à API do Gemini com tracking de uso
     */
    private function callGeminiApi(string $model, string $prompt): ?string
    {
        try {
            // Usar API v1 estável (não v1beta)
            $url = "https://generativelanguage.googleapis.com/{$this->apiVersion}/models/{$model}:generateContent?key={$this->apiKey}";

            $response = Http::timeout(30)->post($url, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => $this->temperature,
                    'maxOutputTokens' => $this->maxTokens,
                    'topP' => 0.8,
                    'topK' => 10,
                ],
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                // Extrair texto da resposta
                $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
                
                // Extrair tokens para billing (se disponível)
                $inputTokens = $data['usageMetadata']['promptTokenCount'] ?? $this->estimateTokens($prompt);
                $outputTokens = $data['usageMetadata']['candidatesTokenCount'] ?? $this->estimateTokens($text ?? '');
                
                // Registrar uso se temos client_id
                if ($this->currentClientId && $text) {
                    $this->logUsage($model, $inputTokens, $outputTokens, $prompt, $text, true);
                }
                
                if ($text) {
                    return trim($text);
                }
                
                Log::warning('GeminiService: Resposta sem texto', [
                    'model' => $model,
                    'response' => $data,
                ]);
                return null;
            }

            // Registrar erro
            if ($this->currentClientId) {
                $this->logUsage($model, $this->estimateTokens($prompt), 0, $prompt, null, false, $response->body());
            }

            Log::error('GeminiService: Erro na API', [
                'model' => $model,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            
            return null;
        } catch (\Exception $e) {
            // Registrar exceção
            if ($this->currentClientId) {
                $this->logUsage($model, $this->estimateTokens($prompt), 0, $prompt, null, false, $e->getMessage());
            }
            
            Log::error('GeminiService: Exception ao gerar conteúdo', [
                'model' => $model,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Estimar tokens de um texto (aproximação: 4 chars = 1 token para português)
     */
    private function estimateTokens(string $text): int
    {
        return (int) ceil(strlen($text) / 4);
    }

    /**
     * Registrar uso de IA no banco de dados
     */
    private function logUsage(
        string $model, 
        int $inputTokens, 
        int $outputTokens, 
        string $prompt, 
        ?string $response, 
        bool $success,
        ?string $error = null
    ): void {
        try {
            // Verificar se a tabela existe antes de tentar logar
            if (!\Schema::hasTable('ai_usage_logs')) {
                return;
            }

            AiUsageLog::logUsage(
                $this->currentClientId,
                $model,
                $inputTokens,
                $outputTokens,
                $prompt,
                $response,
                $this->currentTaskType,
                $success,
                $error
            );
        } catch (\Exception $e) {
            // Não falhar a requisição principal se o log falhar
            Log::warning('GeminiService: Falha ao registrar uso', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Definir contexto do cliente para tracking
     */
    public function setClientContext(int $clientId, string $taskType = 'chat'): self
    {
        $this->currentClientId = $clientId;
        $this->currentTaskType = $taskType;
        return $this;
    }

    /**
     * Verificar saldo de IA do cliente antes de usar
     */
    public function checkClientBalance(int $clientId): array
    {
        try {
            $client = Client::find($clientId);
            
            if (!$client) {
                return ['can_use' => false, 'message' => 'Cliente não encontrado'];
            }

            $minBalance = 0.01;
            $balance = $client->ai_balance ?? 0;

            if ($balance < $minBalance) {
                return [
                    'can_use' => false, 
                    'message' => 'Saldo de créditos de IA insuficiente',
                    'balance' => $balance,
                    'min_required' => $minBalance,
                ];
            }

            return [
                'can_use' => true,
                'balance' => $balance,
            ];
        } catch (\Exception $e) {
            return ['can_use' => true, 'message' => 'Não foi possível verificar saldo'];
        }
    }

    /**
     * Testar conexão com a API
     */
    public function testConnection(): array
    {
        if (!$this->enabled) {
            return [
                'success' => false,
                'message' => 'Gemini não está configurado ou habilitado',
            ];
        }

        try {
            $testPrompt = "Responda apenas: OK";
            $response = $this->generateContent($testPrompt);

            if ($response) {
                $this->integration?->updateTestStatus(true);
                return [
                    'success' => true,
                    'message' => 'Conexão com Gemini OK!',
                    'response' => $response,
                ];
            }

            $this->integration?->updateTestStatus(false, 'Resposta vazia da API');
            return [
                'success' => false,
                'message' => 'Gemini não retornou resposta válida',
            ];
        } catch (\Exception $e) {
            $this->integration?->updateTestStatus(false, $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro ao testar Gemini: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Gerar variações de mensagem (para A/B testing manual)
     */
    public function generateVariations(string $baseMessage, int $count = 3): array
    {
        if (!$this->enabled) {
            return [$baseMessage];
        }

        $variations = [];
        $prompt = <<<PROMPT
Crie {$count} variações DIFERENTES da mensagem abaixo para teste A/B em campanha de marketing WhatsApp.

REGRAS:
- Cada variação deve ter tom e estrutura diferentes
- Mantenha as mesmas informações
- Use emojis diferentes em cada
- Máximo 160 caracteres cada
- Separe cada variação com "---"

MENSAGEM ORIGINAL:
{$baseMessage}

Gere as {$count} variações:
PROMPT;

        try {
            $response = $this->generateContent($prompt);
            
            if ($response) {
                $parts = explode('---', $response);
                foreach ($parts as $part) {
                    $cleaned = trim($part);
                    if (!empty($cleaned) && strlen($cleaned) > 20) {
                        $variations[] = $cleaned;
                    }
                }
            }

            // Se não conseguiu gerar variações, retorna a original
            return !empty($variations) ? $variations : [$baseMessage];
        } catch (\Exception $e) {
            Log::error('GeminiService: Erro ao gerar variações', ['error' => $e->getMessage()]);
            return [$baseMessage];
        }
    }

    /**
     * ✅ NOVO: Responder cliente com contexto específico do estabelecimento
     * Arquitetura Multi-Tenant SaaS com System Instructions isoladas
     * 
     * @param int $clientId ID do estabelecimento (tenant)
     * @param string $customerMessage Mensagem do cliente no WhatsApp
     * @param array $additionalContext Contexto adicional (nome do cliente, histórico, etc)
     * @return string|null Resposta gerada ou null se falhar
     */
    public function replyToCustomer(int $clientId, string $customerMessage, array $additionalContext = []): ?string
    {
        if (!$this->enabled) {
            Log::warning('GeminiService: Tentativa de usar IA desabilitada', ['client_id' => $clientId]);
            return null;
        }

        try {
            // 1. Buscar dados do estabelecimento (tenant isolation)
            $client = Client::find($clientId);
            
            if (!$client) {
                Log::error('GeminiService: Cliente não encontrado', ['client_id' => $clientId]);
                return null;
            }

            // 2. Verificar se o cliente tem IA habilitada
            if (!$client->hasAiEnabled()) {
                Log::info('GeminiService: IA não habilitada para este cliente', [
                    'client_id' => $clientId,
                    'client_name' => $client->name,
                ]);
                return null;
            }

            // 3. Obter System Instructions específicas do estabelecimento
            $systemInstructions = $client->getAiSystemInstructions();

            // 4. Adicionar contexto do cliente se fornecido
            $contextInfo = '';
            if (!empty($additionalContext['customer_name'])) {
                $contextInfo .= "\nCliente: {$additionalContext['customer_name']}";
            }
            if (!empty($additionalContext['order_history'])) {
                $contextInfo .= "\nHistórico: {$additionalContext['order_history']}";
            }
            if (!empty($additionalContext['cashback'])) {
                $contextInfo .= "\nCashback disponível: R$ " . number_format($additionalContext['cashback'], 2, ',', '.');
            }

            // 5. Construir prompt com system instructions + mensagem do cliente
            $fullPrompt = $systemInstructions . $contextInfo . "\n\nMENSAGEM DO CLIENTE:\n" . $customerMessage;

            // 6. Gerar resposta com safety settings do cliente
            $response = $this->generateContentWithSystemInstructions($fullPrompt, $client->getGeminiSafetySettings());

            if ($response) {
                Log::info('GeminiService: Resposta gerada com sucesso', [
                    'client_id' => $clientId,
                    'client_name' => $client->name,
                    'message_length' => strlen($customerMessage),
                    'response_length' => strlen($response),
                ]);
                return $response;
            }

            return null;
        } catch (\Exception $e) {
            Log::error('GeminiService: Erro ao responder cliente', [
                'error' => $e->getMessage(),
                'client_id' => $clientId,
                'message' => $customerMessage,
            ]);
            return null;
        }
    }

    /**
     * ✅ NOVO: Gerar conteúdo com System Instructions e Safety Settings
     */
    private function generateContentWithSystemInstructions(string $prompt, array $safetySettings = []): ?string
    {
        if (!$this->enabled) {
            return null;
        }

        // Tentar modelo principal com system instructions
        $result = $this->callGeminiApiWithInstructions($this->model, $prompt, $safetySettings);
        
        if ($result !== null) {
            return $result;
        }

        // Fallback para modelo secundário
        Log::warning('GeminiService: Modelo principal falhou com system instructions, tentando fallback', [
            'modelo_principal' => $this->model,
            'modelo_fallback' => $this->fallbackModel,
        ]);

        return $this->callGeminiApiWithInstructions($this->fallbackModel, $prompt, $safetySettings);
    }

    /**
     * Chamada à API do Gemini com System Instructions
     */
    private function callGeminiApiWithInstructions(string $model, string $prompt, array $safetySettings = []): ?string
    {
        try {
            // Usar API v1 estável
            $url = "https://generativelanguage.googleapis.com/{$this->apiVersion}/models/{$model}:generateContent?key={$this->apiKey}";

            $payload = [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => $this->temperature,
                    'maxOutputTokens' => $this->maxTokens,
                    'topP' => 0.8,
                    'topK' => 10,
                ],
            ];

            // Adicionar safety settings se fornecidos
            if (!empty($safetySettings)) {
                $payload['safetySettings'] = array_map(function($category, $threshold) {
                    return [
                        'category' => $category,
                        'threshold' => $threshold,
                    ];
                }, array_keys($safetySettings), $safetySettings);
            }

            $response = Http::timeout(30)->post($url, $payload);

            if ($response->successful()) {
                $data = $response->json();
                
                // Extrair texto da resposta
                $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
                
                if ($text) {
                    return trim($text);
                }
                
                Log::warning('GeminiService: Resposta sem texto (system instructions)', [
                    'model' => $model,
                    'response' => $data,
                ]);
                return null;
            }

            Log::error('GeminiService: Erro na API com system instructions', [
                'model' => $model,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            
            return null;
        } catch (\Exception $e) {
            Log::error('GeminiService: Exception ao gerar conteúdo com system instructions', [
                'model' => $model,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }
}
