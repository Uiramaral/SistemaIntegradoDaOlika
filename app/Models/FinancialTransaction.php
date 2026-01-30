<?php

namespace App\Models;

use App\Models\Traits\BelongsToClient;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class FinancialTransaction extends Model
{
    use HasFactory, BelongsToClient;

    protected $fillable = [
        'client_id',
        'type',
        'amount',
        'description',
        'transaction_date',
        'category',
        'order_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'date',
    ];

    /**
     * Relacionamento com pedido (se for receita de venda)
     */
    public function order(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    // =============================
    // SCOPES
    // =============================

    /**
     * Filtrar apenas receitas
     */
    public function scopeReceitas(Builder $query): Builder
    {
        return $query->where('type', 'revenue');
    }

    /**
     * Filtrar apenas despesas
     */
    public function scopeDespesas(Builder $query): Builder
    {
        return $query->where('type', 'expense');
    }

    /**
     * Filtrar por período
     */
    public function scopePeriodo(Builder $query, $start, $end): Builder
    {
        return $query->whereBetween('transaction_date', [$start, $end]);
    }

    /**
     * Filtrar por mês específico (Y-m)
     */
    public function scopeMes(Builder $query, string $yearMonth): Builder
    {
        $parts = explode('-', $yearMonth);
        if (count($parts) !== 2) {
            return $query;
        }
        return $query->whereYear('transaction_date', $parts[0])
            ->whereMonth('transaction_date', $parts[1]);
    }

    /**
     * Filtrar por categoria
     */
    public function scopeCategoria(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    /**
     * Excluir transações de pedidos cancelados/trash
     */
    public function scopeValidas(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('order_id')
                ->orWhereHas('order', function ($sq) {
                    $sq->whereNotIn('status', ['cancelled', 'canceled', 'trash'])
                        ->whereIn('payment_status', ['paid', 'approved']);
                });
        });
    }

    // =============================
    // ACCESSORS
    // =============================

    /**
     * Valor formatado em R$
     */
    public function getFormattedAmountAttribute(): string
    {
        $prefix = $this->type === 'revenue' ? '+' : '-';
        return $prefix . ' R$ ' . number_format($this->amount, 2, ',', '.');
    }

    /**
     * Tipo em português
     */
    public function getTipoLabelAttribute(): string
    {
        return $this->type === 'revenue' ? 'Receita' : 'Despesa';
    }

    /**
     * Data formatada
     */
    public function getFormattedDateAttribute(): string
    {
        return $this->transaction_date ? $this->transaction_date->format('d/m/Y') : '-';
    }

    // =============================
    // HELPERS
    // =============================

    public function isRevenue(): bool
    {
        return $this->type === 'revenue';
    }

    public function isExpense(): bool
    {
        return $this->type === 'expense';
    }

    /**
     * Verificar se é de um pedido
     */
    public function isFromOrder(): bool
    {
        return !empty($this->order_id);
    }
}
