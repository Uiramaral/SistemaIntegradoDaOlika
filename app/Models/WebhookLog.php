<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebhookLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider',
        'event_type',
        'status',
        'ip_address',
        'user_agent',
        'request_id',
        'signature_valid',
        'payload',
        'response',
        'error_message',
        'processed_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'response' => 'array',
        'signature_valid' => 'boolean',
        'processed_at' => 'datetime',
    ];

    /**
     * Scope para filtrar por provider
     */
    public function scopeProvider($query, string $provider)
    {
        return $query->where('provider', $provider);
    }

    /**
     * Scope para filtrar por status
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope para filtrar por assinatura válida
     */
    public function scopeValidSignature($query)
    {
        return $query->where('signature_valid', true);
    }

    /**
     * Scope para filtrar por assinatura inválida
     */
    public function scopeInvalidSignature($query)
    {
        return $query->where('signature_valid', false);
    }

    /**
     * Scope para webhooks recentes
     */
    public function scopeRecent($query, int $hours = 24)
    {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }
}

