<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'type',
        'value',
        'minimum_amount',
        'usage_limit',
        'used_count',
        'usage_limit_per_customer',
        'starts_at',
        'expires_at',
        'is_active',
        'visibility',
        'target_customer_id',
        'private_description',
        'first_order_only',
        'free_shipping_only',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'minimum_amount' => 'decimal:2',
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'first_order_only' => 'boolean',
        'free_shipping_only' => 'boolean',
    ];

    /**
     * Relacionamento com cupons de pedidos
     */
    public function orderCoupons(): HasMany
    {
        return $this->hasMany(OrderCoupon::class, 'code', 'code');
    }

    /**
     * Relacionamento com cliente alvo (cupons direcionados)
     */
    public function targetCustomer()
    {
        return $this->belongsTo(Customer::class, 'target_customer_id');
    }

    /**
     * Scope para cupons ativos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para cupons válidos (não expirados)
     */
    public function scopeValid($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Scope para cupons disponíveis (dentro do período)
     */
    public function scopeAvailable($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('starts_at')
              ->orWhere('starts_at', '<=', now());
        });
    }

    /**
     * Scope para cupons públicos
     */
    public function scopePublic($query)
    {
        return $query->where('visibility', 'public');
    }

    /**
     * Verifica se o cupom é elegível para um pedido específico
     */
    public function isEligibleFor($customerId = null, $subtotal = 0, $deliveryFee = 0, $isFirstOrder = false): bool
    {
        if (!$this->isValid($customerId)) {
            return false;
        }

        // Verificar se é apenas para primeiro pedido
        if ($this->first_order_only && !$isFirstOrder) {
            return false;
        }

        // Verificar se é apenas para frete grátis
        if ($this->free_shipping_only) {
            // Se não há frete no pedido, cupom não é elegível
            if ($deliveryFee <= 0) {
                return false;
            }
            // Verificar se já tem frete grátis por valor mínimo (buscar config)
            try {
                $keyCol = collect(['key','name','config_key'])->first(fn($c)=> \Illuminate\Support\Facades\Schema::hasColumn('settings',$c));
                $valCol = collect(['value','val','config_value'])->first(fn($c)=> \Illuminate\Support\Facades\Schema::hasColumn('settings',$c));
                
                if ($keyCol && $valCol) {
                    $minFreeShipping = \DB::table('settings')
                        ->where($keyCol, 'free_shipping_min_total')
                        ->value($valCol);
                    
                    if ($minFreeShipping && (float)$subtotal >= (float)$minFreeShipping) {
                        return false; // Já tem frete grátis por valor
                    }
                }
            } catch (\Exception $e) {
                // Ignorar erro
            }
        }

        // Verificar valor mínimo
        if ($this->minimum_amount && $subtotal < $this->minimum_amount) {
            \Log::info('Coupon isEligibleFor: Valor mínimo não atendido', [
                'coupon_code' => $this->code,
                'minimum_amount' => $this->minimum_amount,
                'subtotal' => $subtotal,
            ]);
            return false;
        }

        return true;
    }

    /**
     * Scope para cupons privados
     */
    public function scopePrivate($query)
    {
        return $query->where('visibility', 'private');
    }

    /**
     * Scope para cupons direcionados
     */
    public function scopeTargeted($query)
    {
        return $query->where('visibility', 'targeted');
    }

    /**
     * Scope para cupons visíveis para um cliente
     */
    public function scopeVisibleFor($query, $customerId = null)
    {
        return $query->where(function ($q) use ($customerId) {
            $q->where('visibility', 'public')
              ->orWhere('visibility', 'private');
            
            if ($customerId) {
                $q->orWhere(function ($subQ) use ($customerId) {
                    $subQ->where('visibility', 'targeted')
                         ->where('target_customer_id', $customerId);
                });
            }
        });
    }

    /**
     * Verifica se o cupom é válido
     */
    public function isValid($customerId = null): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }

        if ($this->usage_limit && $this->used_count >= $this->usage_limit) {
            return false;
        }

        // Validações específicas por tipo de visibilidade
        if ($this->visibility === 'targeted' && $this->target_customer_id !== $customerId) {
            return false;
        }

        return true;
    }

    /**
     * Verifica se o cupom pode ser usado por um cliente específico
     */
    public function canBeUsedBy($customerId = null): bool
    {
        if (!$this->isValid($customerId)) {
            return false;
        }

        // Se não há customerId, não podemos verificar uso prévio
        if (!$customerId) {
            return true;
        }

        // Verificar se o cupom já foi usado pelo cliente
        $usedByCustomer = 0;
        try {
            // Tentar usar orderCoupons se a tabela existir
            $usedByCustomer = $this->orderCoupons()
                ->whereHas('order', function ($q) use ($customerId) {
                    $q->where('customer_id', $customerId)
                      ->whereIn('payment_status', ['approved', 'paid']); // Apenas pedidos pagos
                })
                ->count();
        } catch (\Exception $e) {
            // Se a tabela order_coupons não existir, usar orders diretamente
            \Log::warning('Tabela order_coupons não encontrada, usando orders diretamente', [
                'coupon_code' => $this->code,
                'error' => $e->getMessage(),
            ]);
            
            // Contar apenas pedidos PAGOS do cliente que usam este cupom
            // Pedidos pendentes não contam como uso do cupom
            $usedByCustomer = \App\Models\Order::where('customer_id', $customerId)
                ->where('coupon_code', $this->code)
                ->whereIn('payment_status', ['approved', 'paid']) // Apenas pedidos pagos
                ->count();
        }

        // Se o cupom é apenas para primeiro pedido, não pode ser usado mais de uma vez
        if ($this->first_order_only) {
            if ($usedByCustomer > 0) {
                \Log::info('Coupon canBeUsedBy: Cupom de primeiro pedido já foi usado', [
                    'coupon_code' => $this->code,
                    'customer_id' => $customerId,
                    'used_count' => $usedByCustomer,
                ]);
                return false;
            }
        }

        // Verificar limite por cliente se estiver definido
        if ($this->usage_limit_per_customer) {
            if ($usedByCustomer >= $this->usage_limit_per_customer) {
                \Log::info('Coupon canBeUsedBy: Limite de uso por cliente atingido', [
                    'coupon_code' => $this->code,
                    'customer_id' => $customerId,
                    'used_count' => $usedByCustomer,
                    'limit' => $this->usage_limit_per_customer,
                ]);
                return false;
            }
        }

        return true;
    }

    /**
     * Calcula o desconto para um valor
     */
    public function calculateDiscount(float $amount): float
    {
        if (!$this->isValid()) {
            return 0;
        }

        if ($this->minimum_amount && $amount < $this->minimum_amount) {
            return 0;
        }

        if ($this->type === 'percentage') {
            return ($amount * $this->value) / 100;
        }

        return min($this->value, $amount);
    }

    /**
     * Accessor para valor formatado
     */
    public function getFormattedValueAttribute()
    {
        if ($this->type === 'percentage') {
            return $this->value . '%';
        }

        return 'R$ ' . number_format($this->value, 2, ',', '.');
    }
}
