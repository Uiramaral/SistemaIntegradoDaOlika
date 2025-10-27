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
        'birth_date',
        'preferences',
        'password',
        'cpf',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'preferences' => 'array',
        'birth_date' => 'date',
        'is_active' => 'boolean',
        'email_verified_at' => 'datetime',
        'last_order_at' => 'datetime',
        'total_spent' => 'decimal:2',
        'loyalty_balance' => 'decimal:2',
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
}
