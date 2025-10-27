<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'provider',
        'provider_id',
        'status',
        'payload',
        'pix_qr_base64',
        'pix_copia_cola',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    /**
     * Relacionamento com pedido
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Scope para pagamentos por status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope para pagamentos por provedor
     */
    public function scopeByProvider($query, $provider)
    {
        return $query->where('provider', $provider);
    }
}

