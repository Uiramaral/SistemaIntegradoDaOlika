<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Helpers\GeminiPricing;

/**
 * Model para log de consumo de IA
 * 
 * Registra cada requisição de IA com custos, tokens e lucro
 */
class AiUsageLog extends Model
{
    use HasFactory;

    protected $table = 'ai_usage_logs';
    
    public $timestamps = false;
    
    protected $fillable = [
        'client_id',
        'model',
        'task_type',
        'input_tokens',
        'output_tokens',
        'cost_usd',
        'cost_brl',
        'charged_brl',
        'profit_brl',
        'prompt_preview',
        'response_preview',
        'success',
        'error_message',
        'created_at',
    ];

    protected $casts = [
        'input_tokens' => 'integer',
        'output_tokens' => 'integer',
        'cost_usd' => 'float',
        'cost_brl' => 'float',
        'charged_brl' => 'float',
        'profit_brl' => 'float',
        'success' => 'boolean',
        'created_at' => 'datetime',
    ];

    /**
     * Relacionamento com cliente
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Registrar uso de IA com cálculo automático de custos
     */
    public static function logUsage(
        int $clientId,
        string $model,
        int $inputTokens,
        int $outputTokens,
        ?string $prompt = null,
        ?string $response = null,
        string $taskType = 'chat',
        bool $success = true,
        ?string $error = null
    ): self {
        // Calcular custos usando o helper
        $profitData = GeminiPricing::calculateProfit($model, $inputTokens, $outputTokens);

        $log = self::create([
            'client_id' => $clientId,
            'model' => $model,
            'task_type' => $taskType,
            'input_tokens' => $inputTokens,
            'output_tokens' => $outputTokens,
            'cost_usd' => $profitData['cost_usd'],
            'cost_brl' => $profitData['cost_brl'],
            'charged_brl' => $profitData['charged_brl'],
            'profit_brl' => $profitData['profit_brl'],
            'prompt_preview' => $prompt ? substr($prompt, 0, 255) : null,
            'response_preview' => $response ? substr($response, 0, 255) : null,
            'success' => $success,
            'error_message' => $error,
            'created_at' => now(),
        ]);

        // Atualizar totais do cliente
        if ($success) {
            Client::where('id', $clientId)->update([
                'ai_tokens_used' => \DB::raw('ai_tokens_used + ' . ($inputTokens + $outputTokens)),
                'ai_requests_count' => \DB::raw('ai_requests_count + 1'),
                'ai_balance' => \DB::raw('ai_balance - ' . $profitData['charged_brl']),
                'ai_last_used_at' => now(),
            ]);
        }

        return $log;
    }

    /**
     * Scope para requisições bem-sucedidas
     */
    public function scopeSuccessful($query)
    {
        return $query->where('success', true);
    }

    /**
     * Scope para requisições com erro
     */
    public function scopeFailed($query)
    {
        return $query->where('success', false);
    }

    /**
     * Scope para o mês atual
     */
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
                     ->whereYear('created_at', now()->year);
    }

    /**
     * Scope por cliente
     */
    public function scopeForClient($query, int $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    /**
     * Scope por modelo
     */
    public function scopeForModel($query, string $model)
    {
        return $query->where('model', $model);
    }

    /**
     * Total de tokens
     */
    public function getTotalTokensAttribute(): int
    {
        return $this->input_tokens + $this->output_tokens;
    }

    /**
     * Margem de lucro em percentual
     */
    public function getProfitMarginAttribute(): float
    {
        if ($this->cost_brl <= 0) {
            return 0;
        }
        return round(($this->profit_brl / $this->cost_brl) * 100, 2);
    }

    /**
     * Estatísticas globais do mês
     */
    public static function getMonthlyStats(): array
    {
        $stats = self::thisMonth()
            ->selectRaw('
                COUNT(*) as total_requests,
                COUNT(DISTINCT client_id) as clients_with_ai,
                SUM(input_tokens + output_tokens) as total_tokens,
                SUM(cost_usd) as total_cost_usd,
                SUM(cost_brl) as total_cost_brl,
                SUM(charged_brl) as total_charged_brl,
                SUM(profit_brl) as total_profit_brl,
                SUM(CASE WHEN success = 0 THEN 1 ELSE 0 END) as total_errors
            ')
            ->first();

        return [
            'total_requests' => $stats->total_requests ?? 0,
            'clients_with_ai' => $stats->clients_with_ai ?? 0,
            'total_tokens' => $stats->total_tokens ?? 0,
            'total_cost_usd' => $stats->total_cost_usd ?? 0,
            'total_cost_brl' => $stats->total_cost_brl ?? 0,
            'total_charged_brl' => $stats->total_charged_brl ?? 0,
            'total_profit_brl' => $stats->total_profit_brl ?? 0,
            'total_errors' => $stats->total_errors ?? 0,
            'profit_margin' => ($stats->total_cost_brl ?? 0) > 0 
                ? round((($stats->total_profit_brl ?? 0) / $stats->total_cost_brl) * 100, 2) 
                : 0,
        ];
    }

    /**
     * Lucro por cliente no mês
     */
    public static function getProfitByClient(): \Illuminate\Database\Eloquent\Collection
    {
        return self::thisMonth()
            ->successful()
            ->select('client_id')
            ->selectRaw('
                SUM(input_tokens + output_tokens) as total_tokens,
                COUNT(*) as requests_count,
                SUM(cost_brl) as total_cost_brl,
                SUM(charged_brl) as total_charged_brl,
                SUM(profit_brl) as total_profit_brl
            ')
            ->groupBy('client_id')
            ->with('client:id,name,slug,ai_balance')
            ->orderByDesc('total_profit_brl')
            ->get();
    }

    /**
     * Uso por modelo no mês
     */
    public static function getUsageByModel(): \Illuminate\Database\Eloquent\Collection
    {
        return self::thisMonth()
            ->successful()
            ->select('model')
            ->selectRaw('
                COUNT(*) as requests_count,
                SUM(input_tokens + output_tokens) as total_tokens,
                SUM(cost_brl) as total_cost_brl,
                SUM(charged_brl) as total_charged_brl,
                SUM(profit_brl) as total_profit_brl
            ')
            ->groupBy('model')
            ->orderByDesc('requests_count')
            ->get();
    }
}
