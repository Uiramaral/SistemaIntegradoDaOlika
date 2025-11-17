<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerDebt extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'order_id',
        'amount',
        'type',
        'status',
        'description',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
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
     * Calcular saldo de débitos pendentes de um cliente
     * Retorna o total de débitos abertos (status='open') menos créditos abertos
     */
    public static function getBalance($customerId): float
    {
        if (!$customerId) {
            return 0.0;
        }
        
        $debits = self::where('customer_id', $customerId)
            ->where('type', 'debit')
            ->where('status', 'open')
            ->sum('amount');
        
        $credits = self::where('customer_id', $customerId)
            ->where('type', 'credit')
            ->where('status', 'open')
            ->sum('amount');
        
        $balance = (float)$debits - (float)$credits;
        
        return $balance;
    }
}

