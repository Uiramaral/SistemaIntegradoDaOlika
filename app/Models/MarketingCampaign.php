<?php

namespace App\Models;

use App\Models\Traits\BelongsToClient;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarketingCampaign extends Model
{
    use HasFactory, BelongsToClient;

    protected $fillable = [
        'client_id',
        'name',
        'description',
        'status',
        'message_template_a',
        'message_template_b',
        'message_template_c',
        'use_ab_testing',
        'target_filter',
        'target_count',
        'scheduled_at',
        'send_immediately',
        'interval_seconds',
        'sent_count',
        'delivered_count',
        'failed_count',
        'started_at',
        'completed_at',
        'created_by',
    ];

    protected $casts = [
        'target_filter' => 'array',
        'use_ab_testing' => 'boolean',
        'send_immediately' => 'boolean',
        'target_count' => 'integer',
        'sent_count' => 'integer',
        'delivered_count' => 'integer',
        'failed_count' => 'integer',
        'interval_seconds' => 'integer',
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Variáveis disponíveis para substituição nas mensagens
     */
    public const AVAILABLE_VARIABLES = [
        '{{nome}}' => 'Nome do cliente',
        '{{primeiro_nome}}' => 'Primeiro nome',
        '{{telefone}}' => 'Telefone',
        '{{email}}' => 'E-mail',
        '{{cashback}}' => 'Saldo de cashback',
        '{{total_pedidos}}' => 'Total de pedidos',
        '{{ultimo_pedido}}' => 'Data do último pedido',
        '{{estabelecimento}}' => 'Nome do estabelecimento',
    ];

    /**
     * Relacionamento com logs de envio
     */
    public function logs()
    {
        return $this->hasMany(MarketingCampaignLog::class, 'campaign_id');
    }

    /**
     * Relacionamento com usuário criador
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relacionamento com cliente/estabelecimento
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Scopes
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeRunning($query)
    {
        return $query->where('status', 'running');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Verificar se a campanha pode ser iniciada
     */
    public function canStart(): bool
    {
        return in_array($this->status, ['draft', 'scheduled', 'paused']);
    }

    /**
     * Verificar se a campanha pode ser pausada
     */
    public function canPause(): bool
    {
        return $this->status === 'running';
    }

    /**
     * Verificar se a campanha pode ser cancelada
     */
    public function canCancel(): bool
    {
        return in_array($this->status, ['draft', 'scheduled', 'running', 'paused']);
    }

    /**
     * Processar mensagem com variáveis do cliente
     */
    public function processMessage(string $template, Customer $customer): string
    {
        $settings = \App\Models\Setting::getSettings();
        
        $replacements = [
            '{{nome}}' => $customer->name ?? '',
            '{{primeiro_nome}}' => explode(' ', $customer->name ?? '')[0] ?? '',
            '{{telefone}}' => $customer->phone ?? '',
            '{{email}}' => $customer->email ?? '',
            '{{cashback}}' => 'R$ ' . number_format($customer->cashback_balance ?? 0, 2, ',', '.'),
            '{{total_pedidos}}' => $customer->orders()->count(),
            '{{ultimo_pedido}}' => $customer->orders()->latest()->first()?->created_at?->format('d/m/Y') ?? 'Nunca',
            '{{estabelecimento}}' => $settings->business_name ?? 'Nossa loja',
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    /**
     * Obter template aleatório (A/B/C testing)
     */
    public function getRandomTemplate(): array
    {
        if (!$this->use_ab_testing) {
            return ['template' => $this->message_template_a, 'version' => 'A'];
        }

        $templates = array_filter([
            'A' => $this->message_template_a,
            'B' => $this->message_template_b,
            'C' => $this->message_template_c,
        ]);

        $version = array_rand($templates);
        return ['template' => $templates[$version], 'version' => $version];
    }

    /**
     * Calcular progresso da campanha
     */
    public function getProgressPercentage(): float
    {
        if ($this->target_count === 0) {
            return 0;
        }

        return round(($this->sent_count / $this->target_count) * 100, 2);
    }

    /**
     * Obter taxa de sucesso
     */
    public function getSuccessRate(): float
    {
        if ($this->sent_count === 0) {
            return 0;
        }

        return round(($this->delivered_count / $this->sent_count) * 100, 2);
    }

    /**
     * Accessor para status formatado
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft' => 'Rascunho',
            'scheduled' => 'Agendada',
            'running' => 'Em andamento',
            'paused' => 'Pausada',
            'completed' => 'Concluída',
            'cancelled' => 'Cancelada',
            default => $this->status,
        };
    }

    /**
     * Accessor para cor do badge de status
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'draft' => 'gray',
            'scheduled' => 'blue',
            'running' => 'yellow',
            'paused' => 'orange',
            'completed' => 'green',
            'cancelled' => 'red',
            default => 'gray',
        };
    }
}
