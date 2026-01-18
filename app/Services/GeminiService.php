<?php

namespace App\Services;

use App\Models\ApiIntegration;
use App\Models\Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    private ?ApiIntegration $integration = null;
    private bool $enabled = false;
    private string $apiKey = '';
    private string $model = 'gemini-1.5-flash';
    private float $temperature = 0.7;
    private int $maxTokens = 500;

    public function __construct()
    {
        try {
            $this->integration = ApiIntegration::getByProvider('gemini');
            
            if ($this->integration && $this->integration->isActive()) {
                $this->apiKey = $this->integration->getCredential('api_key', '');
                $this->model = $this->integration->getSetting('model', 'gemini-1.5-flash');
                $this->temperature = (float) $this->integration->getSetting('temperature', 0.7);
                $this->maxTokens = (int) $this->integration->getSetting('max_tokens', 500);
                
                if (!empty($this->apiKey)) {
                    $this->enabled = true;
                    Log::info('GeminiService: Configurado com sucesso', ['model' => $this->model]);
                }
            }
        } catch (\Throwable $e) {
            Log::error('GeminiService init error: ' . $e->getMessage());
            $this->enabled = false;
        }
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
     * Gerar conteúdo usando Gemini API
     */
    public function generateContent(string $prompt): ?string
    {
        if (!$this->enabled) {
            return null;
        }

        try {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}";

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
                
                if ($text) {
                    return trim($text);
                }
                
                Log::warning('GeminiService: Resposta sem texto', ['response' => $data]);
                return null;
            }

            Log::error('GeminiService: Erro na API', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            
            return null;
        } catch (\Exception $e) {
            Log::error('GeminiService: Exception ao gerar conteúdo', [
                'error' => $e->getMessage(),
            ]);
            return null;
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

        try {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}";

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
                
                Log::warning('GeminiService: Resposta sem texto', ['response' => $data]);
                return null;
            }

            Log::error('GeminiService: Erro na API com system instructions', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            
            return null;
        } catch (\Exception $e) {
            Log::error('GeminiService: Exception ao gerar conteúdo com system instructions', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }
}
