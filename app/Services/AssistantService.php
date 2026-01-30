<?php

namespace App\Services;

use App\Models\Client;
use Illuminate\Support\Facades\Log;

class AssistantService
{
    public function __construct(
        private GeminiService $gemini
    ) {}

    /**
     * Envia pergunta ao assistente e retorna resposta.
     * Uso: apenas assinantes (client_id). Contexto do assinante + instruções do contexto (Cardápio, etc.) são injetados.
     *
     * @param string $prompt Pergunta do usuário
     * @param string $contextType default|cardapio|seguranca_alimentar|marketing
     * @param array|null $extraContext Dados adicionais para injetar (ex.: produtos em excesso, estoque)
     * @param array|null $history Histórico de mensagens anteriores [['role' => 'user'|'assistant', 'text' => '...'], ...]
     * @return array{ok: bool, message?: string, error?: string}
     */
    public function ask(string $prompt, string $contextType = 'default', ?array $extraContext = null, ?array $history = null): array
    {
        $clientId = currentClientId();
        Log::info('AssistantService: ask', ['client_id' => $clientId, 'context' => $contextType, 'prompt_len' => strlen($prompt)]);

        if (!$clientId) {
            Log::warning('AssistantService: sem client_id');
            return ['ok' => false, 'error' => 'Nenhum estabelecimento selecionado.'];
        }

        if (!$this->gemini->isEnabled()) {
            Log::warning('AssistantService: Gemini não habilitado');
            return ['ok' => false, 'error' => 'Assistente IA não está configurado. Configure a chave Gemini em Master → Configurações.'];
        }

        $client = Client::find($clientId);
        $assinanteContext = $this->buildAssinanteContext($client, $extraContext ?? []);

        $systemInstruction = config("assistants.contexts.{$contextType}", config('assistants.contexts.default'));
        
        // Construir prompt com histórico se disponível
        $userPrompt = $assinanteContext
            ? "--- Contexto do estabelecimento ---\n" . $assinanteContext . "\n--- Fim do contexto ---\n\n"
            : '';
        
        // Adicionar histórico de conversa se fornecido (limitar para não exceder tokens)
        if (!empty($history) && is_array($history)) {
            // Limitar histórico às últimas 8 mensagens (4 user + 4 assistant) para não exceder limite
            $limitedHistory = array_slice($history, -8);
            
            $userPrompt .= "--- Histórico da conversa recente ---\n";
            foreach ($limitedHistory as $msg) {
                if (isset($msg['role']) && isset($msg['text'])) {
                    $roleLabel = $msg['role'] === 'user' ? 'Usuário' : 'Assistente';
                    // Limitar tamanho de cada mensagem do histórico
                    $text = mb_substr($msg['text'], 0, 500);
                    $userPrompt .= "{$roleLabel}: {$text}\n";
                }
            }
            $userPrompt .= "--- Fim do histórico ---\n\n";
        }
        
        $userPrompt .= "Pergunta atual: " . trim($prompt);

        $this->gemini->setClientContext($clientId, 'assistente_ia');
        Log::info('AssistantService: chamando Gemini', [
            'client_id' => $clientId,
            'context_type' => $contextType,
            'prompt_preview' => substr($prompt, 0, 100),
            'has_context' => !empty($assinanteContext),
            'has_history' => !empty($history),
            'history_count' => !empty($history) ? count($history) : 0,
            'system_instruction_len' => strlen($systemInstruction),
        ]);
        $reply = $this->gemini->generateWithSystemInstruction($systemInstruction, $userPrompt);

        if ($reply === null) {
            Log::warning('AssistantService: Gemini retornou vazio', ['client_id' => $clientId, 'context' => $contextType]);
            return ['ok' => false, 'error' => 'Não foi possível gerar uma resposta. Tente novamente em instantes.'];
        }

        Log::info('AssistantService: resposta recebida', [
            'client_id' => $clientId,
            'reply_length' => strlen($reply),
            'reply_preview' => substr($reply, 0, 200),
        ]);

        return ['ok' => true, 'message' => $reply];
    }

    private function buildAssinanteContext(?Client $client, array $extra): string
    {
        $lines = [];

        if ($client) {
            $lines[] = 'Estabelecimento: ' . ($client->name ?? 'Sem nome');
            if (!empty($client->ai_context)) {
                $lines[] = 'Informações adicionais: ' . trim($client->ai_context);
            }
            try {
                $products = $client->products()->where('is_active', true)->limit(50)->get(['name', 'price']);
                if ($products->isNotEmpty()) {
                    $list = $products->pluck('name')->take(30)->implode(', ');
                    $lines[] = 'Produtos/cardápio (amostra): ' . $list;
                }
            } catch (\Throwable $e) {
                // Ignorar se não houver produtos ou relação
            }
        }

        foreach ($extra as $key => $value) {
            if (is_scalar($value)) {
                $lines[] = (string) $key . ': ' . $value;
            }
        }

        return implode("\n", $lines);
    }

    /**
     * Lista contextos disponíveis (para o seletor na UI).
     */
    public static function contextLabels(): array
    {
        return config('assistants.context_labels', [
            'default' => 'Geral',
            'cardapio' => 'Cardápio',
            'seguranca_alimentar' => 'Segurança Alimentar',
            'marketing' => 'Marketing',
        ]);
    }
}
