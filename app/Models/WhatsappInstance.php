<?php

namespace App\Models;

use App\Models\Traits\BelongsToClient;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsappInstance extends Model
{
    use HasFactory, BelongsToClient;

    protected $fillable = [
        'client_id',
        'name',
        'phone_number',
        'api_url',
        'instance_url_id',
        'api_token',
        'status',
        'last_error_message',
    ];

    const STATUS_DISCONNECTED = 'DISCONNECTED';
    const STATUS_CONNECTING = 'CONNECTING';
    const STATUS_CONNECTED = 'CONNECTED';

    /**
     * Cliente dono desta instância
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * URL de instância vinculada (Railway)
     */
    public function instanceUrl(): BelongsTo
    {
        return $this->belongsTo(WhatsappInstanceUrl::class, 'instance_url_id');
    }

    /**
     * Verifica se está conectado
     */
    public function isConnected(): bool
    {
        return $this->status === self::STATUS_CONNECTED;
    }

    /**
     * Verifica se está desconectado
     */
    public function isDisconnected(): bool
    {
        return $this->status === self::STATUS_DISCONNECTED;
    }

    /**
     * Retorna cor do status para UI
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_CONNECTED => 'green',
            self::STATUS_CONNECTING => 'yellow',
            default => 'red',
        };
    }

    /**
     * Retorna label do status para UI
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_CONNECTED => 'Conectado',
            self::STATUS_CONNECTING => 'Conectando...',
            default => 'Desconectado',
        };
    }
}
