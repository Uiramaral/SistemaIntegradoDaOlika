<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerCashback extends Model
{
    use HasFactory;

    protected $table = 'customer_cashback';

    protected $fillable = [
        'customer_id',
        'order_id',
        'amount',
        'type',
        'description',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relacionamento com cliente
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Relacionamento com pedido
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Scope para créditos (ganhos)
     */
    public function scopeCredits($query)
    {
        return $query->where('type', 'credit');
    }

    /**
     * Scope para débitos (usos)
     */
    public function scopeDebits($query)
    {
        return $query->where('type', 'debit');
    }

    /**
     * Calcular saldo de cashback de um cliente
     */
    public static function getBalance($customerId): float
    {
        if (!$customerId) {
            return 0.0;
        }
        
        $credits = self::where('customer_id', $customerId)
            ->where('type', 'credit')
            ->sum('amount');
        
        $debits = self::where('customer_id', $customerId)
            ->where('type', 'debit')
            ->sum('amount');
        
        $balance = max(0, (float)$credits - (float)$debits);
        
        // Log para debug - remover depois de identificar o problema
        \Log::info('CustomerCashback::getBalance', [
            'customer_id' => $customerId,
            'credits' => $credits,
            'debits' => $debits,
            'balance' => $balance,
            'records_count' => self::where('customer_id', $customerId)->count()
        ]);
        
        return $balance;
    }

    /**
     * Criar crédito de cashback
     */
    public static function createCredit($customerId, $orderId, $amount, $description = null): self
    {
        return self::create([
            'customer_id' => $customerId,
            'order_id' => $orderId,
            'amount' => abs($amount),
            'type' => 'credit',
            'description' => $description ?? "Cashback do pedido #{$orderId}",
        ]);
    }

    /**
     * Criar débito de cashback
     */
    public static function createDebit($customerId, $orderId, $amount, $description = null): self
    {
        return self::create([
            'customer_id' => $customerId,
            'order_id' => $orderId,
            'amount' => abs($amount),
            'type' => 'debit',
            'description' => $description ?? "Uso de cashback no pedido #{$orderId}",
        ]);
    }
}

