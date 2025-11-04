<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Customer extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'visitor_id',
        'name',
        'phone',
        'email',
        'address',
        'neighborhood',
        'city',
        'state',
        'zip_code',
        'custom_delivery_fee',
        'custom_delivery_note',
        'birth_date',
        'preferences',
        'password',
        'cpf',
        'is_active',
        'is_wholesale',
        'total_debts',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'preferences' => 'array',
        'birth_date' => 'date',
        'is_active' => 'boolean',
        'is_wholesale' => 'boolean',
        'email_verified_at' => 'datetime',
        'last_order_at' => 'datetime',
        'total_spent' => 'decimal:2',
        'loyalty_balance' => 'decimal:2',
        'total_debts' => 'decimal:2',
        'custom_delivery_fee' => 'decimal:2',
    ];

    /**
     * Relacionamento com endereços
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    /**
     * Relacionamento com pedidos
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Relacionamento com fiados
     */
    public function debts(): HasMany
    {
        return $this->hasMany(CustomerDebt::class);
    }

    /**
     * Relacionamento com transações de cashback
     */
    public function cashbackTransactions(): HasMany
    {
        return $this->hasMany(CustomerCashback::class);
    }

    /**
     * Obter saldo de cashback disponível
     */
    public function getCashbackBalanceAttribute(): float
    {
        return CustomerCashback::getBalance($this->id);
    }

    /**
     * Obter saldo de débitos pendentes (pagamento postergado)
     */
    public function getDebtsBalanceAttribute(): float
    {
        return CustomerDebt::getBalance($this->id);
    }

    /**
     * Relacionamento com transações de fidelidade
     */
    public function loyaltyTransactions(): HasMany
    {
        return $this->hasMany(LoyaltyTransaction::class);
    }

    /**
     * Relacionamento com indicações feitas
     */
    public function referrals(): HasMany
    {
        return $this->hasMany(Referral::class, 'referrer_id');
    }

    /**
     * Relacionamento com indicações recebidas
     */
    public function referredBy(): HasMany
    {
        return $this->hasMany(Referral::class, 'referred_id');
    }

    /**
     * Calcula pontos disponíveis
     */
    public function getAvailablePointsAttribute()
    {
        $earned = $this->loyaltyTransactions()
            ->where('type', 'earned')
            ->where('is_active', true)
            ->sum('points');

        $redeemed = $this->loyaltyTransactions()
            ->where('type', 'redeemed')
            ->sum('points');

        return $earned - $redeemed;
    }

    /**
     * Calcula total de pontos ganhos
     */
    public function getTotalPointsAttribute()
    {
        return $this->loyaltyTransactions()
            ->where('type', 'earned')
            ->where('is_active', true)
            ->sum('points');
    }

    /**
     * Scope para clientes ativos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para clientes com pedidos
     */
    public function scopeWithOrders($query)
    {
        return $query->where('total_orders', '>', 0);
    }

    /**
     * Accessor para nome completo
     */
    public function getFullNameAttribute()
    {
        return $this->name;
    }

    /**
     * Accessor para endereço completo
     */
    public function getFullAddressAttribute()
    {
        $parts = array_filter([
            $this->address,
            $this->neighborhood,
            $this->city,
            $this->state,
            $this->zip_code,
        ]);

        return implode(', ', $parts);
    }

    /**
     * Endereço legível para Google Maps (formato preferencial)
     */
    public function getEnderecoFormatadoAttribute(): ?string
    {
        $partes = array_filter([
            $this->address ?? null,
            $this->neighborhood ?? null,
            $this->city ?? null,
            $this->state ?? null,
            $this->zip_code ?? null,
        ]);

        return $partes ? implode(', ', $partes) : null;
    }

    /**
     * Preferir coordenadas; senão, o texto do endereço
     */
    public function getMapsQueryAttribute(): ?string
    {
        if (!empty($this->lat) && !empty($this->lng)) {
            return $this->lat.','.$this->lng;
        }
        return $this->endereco_formatado ?: ($this->address ?? null);
    }

    /**
     * URL universal do Google Maps (abre app no mobile / web no desktop)
     */
    public function getMapsUrlAttribute(): ?string
    {
        $q = $this->maps_query;
        return $q ? 'https://www.google.com/maps/dir/?api=1&destination='.urlencode($q) : null;
    }
}
