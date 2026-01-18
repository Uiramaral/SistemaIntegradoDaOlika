<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsappInstanceUrl extends Model
{
    use HasFactory;

    protected $fillable = [
        'url',
        'name',
        'api_key',
        'description',
        'status',
        'client_id',
        'whatsapp_instance_id',
        'railway_service_id',
        'railway_project_id',
        'max_connections',
        'current_connections',
        'last_health_check',
        'health_status',
        'notes',
    ];

    protected $casts = [
        'last_health_check' => 'datetime',
    ];

    const STATUS_AVAILABLE = 'available';
    const STATUS_ASSIGNED = 'assigned';
    const STATUS_MAINTENANCE = 'maintenance';
    const STATUS_OFFLINE = 'offline';

    const HEALTH_HEALTHY = 'healthy';
    const HEALTH_UNHEALTHY = 'unhealthy';
    const HEALTH_UNKNOWN = 'unknown';

    /**
     * Cliente usando esta instância
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Instância WhatsApp vinculada
     */
    public function whatsappInstance(): BelongsTo
    {
        return $this->belongsTo(WhatsappInstance::class);
    }

    /**
     * Scopes
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', self::STATUS_AVAILABLE)
            ->where('health_status', self::HEALTH_HEALTHY);
    }

    public function scopeAssigned($query)
    {
        return $query->where('status', self::STATUS_ASSIGNED);
    }

    public function scopeHealthy($query)
    {
        return $query->where('health_status', self::HEALTH_HEALTHY);
    }

    /**
     * Helpers
     */
    public function isAvailable(): bool
    {
        return $this->status === self::STATUS_AVAILABLE;
    }

    public function isAssigned(): bool
    {
        return $this->status === self::STATUS_ASSIGNED;
    }

    public function isHealthy(): bool
    {
        return $this->health_status === self::HEALTH_HEALTHY;
    }

    public function hasCapacity(): bool
    {
        return $this->current_connections < $this->max_connections;
    }

    /**
     * Atribui a um cliente
     */
    public function assignToClient(Client $client, WhatsappInstance $instance = null): self
    {
        $this->update([
            'status' => self::STATUS_ASSIGNED,
            'client_id' => $client->id,
            'whatsapp_instance_id' => $instance?->id,
            'current_connections' => $this->current_connections + 1,
        ]);

        return $this;
    }

    /**
     * Libera da atribuição
     */
    public function release(): self
    {
        $this->update([
            'status' => $this->current_connections > 1 ? self::STATUS_ASSIGNED : self::STATUS_AVAILABLE,
            'client_id' => null,
            'whatsapp_instance_id' => null,
            'current_connections' => max(0, $this->current_connections - 1),
        ]);

        return $this;
    }

    /**
     * Atualiza health check
     */
    public function updateHealthStatus(string $status): self
    {
        $this->update([
            'health_status' => $status,
            'last_health_check' => now(),
        ]);

        return $this;
    }

    /**
     * Busca próxima instância disponível
     */
    public static function getNextAvailable(): ?self
    {
        return static::available()
            ->orderBy('current_connections')
            ->first();
    }

    /**
     * Status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_AVAILABLE => 'Disponível',
            self::STATUS_ASSIGNED => 'Em uso',
            self::STATUS_MAINTENANCE => 'Manutenção',
            self::STATUS_OFFLINE => 'Offline',
            default => $this->status,
        };
    }

    /**
     * Status color
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_AVAILABLE => 'success',
            self::STATUS_ASSIGNED => 'primary',
            self::STATUS_MAINTENANCE => 'warning',
            self::STATUS_OFFLINE => 'destructive',
            default => 'secondary',
        };
    }

    /**
     * Health label
     */
    public function getHealthLabelAttribute(): string
    {
        return match($this->health_status) {
            self::HEALTH_HEALTHY => 'Saudável',
            self::HEALTH_UNHEALTHY => 'Com problemas',
            self::HEALTH_UNKNOWN => 'Desconhecido',
            default => $this->health_status,
        };
    }

    /**
     * Health color
     */
    public function getHealthColorAttribute(): string
    {
        return match($this->health_status) {
            self::HEALTH_HEALTHY => 'success',
            self::HEALTH_UNHEALTHY => 'destructive',
            self::HEALTH_UNKNOWN => 'warning',
            default => 'secondary',
        };
    }
}
