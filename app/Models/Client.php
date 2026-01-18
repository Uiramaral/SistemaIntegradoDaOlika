<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'plan',
        'instance_url',
        'deploy_status',
        'whatsapp_phone',
        'active',
        'is_trial',
        'trial_started_at',
        'trial_ends_at',
    ];

    protected $casts = [
        'active' => 'boolean',
        'is_trial' => 'boolean',
        'trial_started_at' => 'datetime',
        'trial_ends_at' => 'datetime',
    ];

    /**
     * ✅ NOVO: Gera token automaticamente quando cliente é criado
     */
    protected static function booted()
    {
        static::created(function ($client) {
            // Gerar token único para o cliente
            $token = self::generateUniqueToken();
            
            // Criar token de API para o cliente
            ApiToken::create([
                'client_id' => $client->id,
                'token' => $token,
                'expires_at' => null, // Token sem expiração
            ]);
            
            Log::info('Client::booted - Token gerado automaticamente', [
                'client_id' => $client->id,
                'client_name' => $client->name,
            ]);
        });
    }

    /**
     * ✅ NOVO: Gera um token único para autenticação
     */
    private static function generateUniqueToken(): string
    {
        // Usar Str::random() do Laravel para gerar token seguro de 64 caracteres
        do {
            $token = Str::random(64);
        } while (ApiToken::where('token', $token)->exists());
        
        return $token;
    }

    /**
     * ✅ NOVO: Gera um novo token para o cliente (regenerar)
     */
    public function regenerateApiToken(): string
    {
        // Gerar novo token
        $token = self::generateUniqueToken();
        
        ApiToken::create([
            'client_id' => $this->id,
            'token' => $token,
            'expires_at' => null,
        ]);
        
        Log::info('Client::regenerateApiToken - Novo token gerado', [
            'client_id' => $this->id,
            'client_name' => $this->name,
        ]);
        
        return $token;
    }

    /**
     * Relacionamento com usuários
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Relacionamento com pedidos
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Relacionamento com clientes
     */
    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    /**
     * Relacionamento com produtos
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Relacionamento com tokens de API
     */
    public function apiTokens(): HasMany
    {
        return $this->hasMany(ApiToken::class);
    }

    /**
     * Token de API ativo
     */
    public function activeApiToken(): HasOne
    {
        return $this->hasOne(ApiToken::class)
            ->where(function($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
            })
            ->latest();
    }

    /**
     * Relacionamento com instância Railway
     */
    public function instance(): HasOne
    {
        return $this->hasOne(Instance::class, 'assigned_to');
    }

    /**
     * Scope para clientes ativos
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Verifica se o cliente tem plano IA
     */
    public function hasIaPlan(): bool
    {
        return $this->plan === 'ia';
    }

    /**
     * Verifica se o cliente tem plano básico
     */
    public function hasBasicPlan(): bool
    {
        return $this->plan === 'basic';
    }

    /**
     * Verifica se o cliente está em período de teste
     */
    public function isInTrial(): bool
    {
        if (!$this->is_trial || !$this->trial_ends_at) {
            return false;
        }

        return now()->lessThan($this->trial_ends_at);
    }

    /**
     * Verifica se o período de teste expirou
     */
    public function trialExpired(): bool
    {
        if (!$this->is_trial || !$this->trial_ends_at) {
            return false;
        }

        return now()->greaterThanOrEqualTo($this->trial_ends_at);
    }

    /**
     * Retorna os dias restantes do período de teste
     */
    public function trialDaysRemaining(): ?int
    {
        if (!$this->isInTrial()) {
            return null;
        }

        return max(0, now()->diffInDays($this->trial_ends_at, false));
    }
}

