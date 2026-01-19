<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;

/**
 * Calculadora de Custos do Gemini para SaaS Multi-tenant
 * 
 * Tabela de preços oficial 2026 com suporte a markup e conversão BRL
 */
class GeminiPricing
{
    /**
     * Tabela de preços por 1 MILHÃO de tokens (Em Dólares USD)
     * Valores para Context Window < 128k tokens
     * 
     * Fonte: Google AI Pricing 2026
     */
    public const RATES = [
        // Modelo principal - Melhor custo-benefício para chat
        'gemini-2.5-flash' => [
            'input' => 0.15,  // $0.15 por 1M tokens de entrada
            'output' => 0.60, // $0.60 por 1M tokens de saída
            'description' => 'Otimizado para chat rápido, 1M contexto',
        ],
        // Modelo econômico - Notificações em massa
        'gemini-2.5-flash-lite' => [
            'input' => 0.07,  // $0.07 por 1M tokens de entrada
            'output' => 0.30, // $0.30 por 1M tokens de saída
            'description' => 'Ultra-rápido e econômico para tarefas simples',
        ],
        // Modelo avançado - Análises complexas (B2B)
        'gemini-3-pro' => [
            'input' => 1.25,  // $1.25 por 1M tokens de entrada
            'output' => 5.00, // $5.00 por 1M tokens de saída
            'description' => 'Alta capacidade para análises complexas',
        ],
        // Fallback - modelos legados
        'gemini-2.0-flash' => [
            'input' => 0.10,
            'output' => 0.40,
            'description' => 'Modelo legado 2025',
        ],
        'gemini-1.5-flash-latest' => [
            'input' => 0.075,
            'output' => 0.30,
            'description' => 'Modelo legado estável',
        ],
    ];

    /**
     * Taxa de câmbio USD -> BRL (atualizar conforme necessário)
     */
    private static float $exchangeRate = 5.50;

    /**
     * Markup padrão do SaaS (3x = 200% de margem)
     */
    private static float $defaultMarkup = 3.0;

    /**
     * Calcular custo em BRL com markup
     * 
     * @param string $model Nome do modelo Gemini
     * @param int $inputTokens Tokens de entrada (prompt)
     * @param int $outputTokens Tokens de saída (resposta)
     * @param float|null $markup Markup personalizado (null = usar padrão)
     * @return float Custo final em BRL
     */
    public static function calculateInBRL(
        string $model, 
        int $inputTokens, 
        int $outputTokens, 
        ?float $markup = null
    ): float {
        $rate = self::RATES[$model] ?? self::RATES['gemini-2.5-flash'];
        $markup = $markup ?? self::$defaultMarkup;
        
        // 1. Cálculo do custo base em USD
        $inputCostUsd = ($inputTokens / 1_000_000) * $rate['input'];
        $outputCostUsd = ($outputTokens / 1_000_000) * $rate['output'];
        $totalCostUsd = $inputCostUsd + $outputCostUsd;
        
        // 2. Conversão para BRL (inclui IOF implícito no câmbio)
        $costBrl = $totalCostUsd * self::$exchangeRate;

        // 3. Aplicar markup do SaaS
        $finalCost = $costBrl * $markup;

        return round($finalCost, 6);
    }

    /**
     * Calcular custo base em USD (sem markup)
     */
    public static function calculateInUSD(string $model, int $inputTokens, int $outputTokens): float
    {
        $rate = self::RATES[$model] ?? self::RATES['gemini-2.5-flash'];
        
        $inputCostUsd = ($inputTokens / 1_000_000) * $rate['input'];
        $outputCostUsd = ($outputTokens / 1_000_000) * $rate['output'];
        
        return round($inputCostUsd + $outputCostUsd, 8);
    }

    /**
     * Estimar custo antes de executar (para validação de saldo)
     * Baseado em médias históricas
     * 
     * @param string $model Modelo a ser usado
     * @param int $promptLength Tamanho aproximado do prompt em caracteres
     * @return float Custo estimado em BRL
     */
    public static function estimateCost(string $model, int $promptLength): float
    {
        // Aproximação: 4 caracteres = 1 token (para português)
        $estimatedInputTokens = (int) ceil($promptLength / 4);
        
        // Resposta média: 150 tokens para chat, 500 para marketing
        $estimatedOutputTokens = 200;
        
        return self::calculateInBRL($model, $estimatedInputTokens, $estimatedOutputTokens);
    }

    /**
     * Obter modelo recomendado por tipo de tarefa
     */
    public static function getRecommendedModel(string $taskType): string
    {
        return match($taskType) {
            'chat', 'whatsapp', 'menu_query' => 'gemini-2.5-flash',
            'marketing', 'notification', 'status' => 'gemini-2.5-flash-lite',
            'analysis', 'report', 'complex' => 'gemini-3-pro',
            default => 'gemini-2.5-flash',
        };
    }

    /**
     * Obter informações completas do modelo
     */
    public static function getModelInfo(string $model): array
    {
        $rate = self::RATES[$model] ?? self::RATES['gemini-2.5-flash'];
        
        return [
            'model' => $model,
            'input_price_usd' => $rate['input'],
            'output_price_usd' => $rate['output'],
            'input_price_brl' => $rate['input'] * self::$exchangeRate,
            'output_price_brl' => $rate['output'] * self::$exchangeRate,
            'description' => $rate['description'] ?? '',
            'exchange_rate' => self::$exchangeRate,
            'markup' => self::$defaultMarkup,
        ];
    }

    /**
     * Listar todos os modelos disponíveis
     */
    public static function listModels(): array
    {
        return array_keys(self::RATES);
    }

    /**
     * Atualizar taxa de câmbio (para admin)
     */
    public static function setExchangeRate(float $rate): void
    {
        self::$exchangeRate = $rate;
        Log::info('GeminiPricing: Taxa de câmbio atualizada', ['rate' => $rate]);
    }

    /**
     * Atualizar markup padrão (para admin)
     */
    public static function setDefaultMarkup(float $markup): void
    {
        self::$defaultMarkup = $markup;
        Log::info('GeminiPricing: Markup padrão atualizado', ['markup' => $markup]);
    }

    /**
     * Calcular margem de lucro
     */
    public static function calculateProfit(
        string $model, 
        int $inputTokens, 
        int $outputTokens
    ): array {
        $costUsd = self::calculateInUSD($model, $inputTokens, $outputTokens);
        $costBrl = $costUsd * self::$exchangeRate;
        $chargedBrl = self::calculateInBRL($model, $inputTokens, $outputTokens);
        $profitBrl = $chargedBrl - $costBrl;
        $profitPercent = $costBrl > 0 ? (($profitBrl / $costBrl) * 100) : 0;

        return [
            'cost_usd' => round($costUsd, 6),
            'cost_brl' => round($costBrl, 6),
            'charged_brl' => round($chargedBrl, 6),
            'profit_brl' => round($profitBrl, 6),
            'profit_percent' => round($profitPercent, 2),
            'tokens_total' => $inputTokens + $outputTokens,
        ];
    }
}
