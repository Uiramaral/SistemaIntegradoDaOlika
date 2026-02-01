<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\Payment;
use App\Models\Scopes\ClientScope;

use App\Models\Traits\BelongsToClient;

class Order extends Model
{
    use HasFactory, BelongsToClient;

    protected $guarded = ['id'];

    protected $casts = [
        'scheduled_delivery_at' => 'datetime',
        'pix_expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Mutator para normalizar valores de status para o ENUM válido da tabela orders.
     * Aceita códigos vindos de order_statuses e converte para o ENUM permitido.
     */
    public function setStatusAttribute($value): void
    {
        $mapping = [
            'pending' => 'pending',
            'waiting_payment' => 'pending',
            'paid' => 'confirmed',
            'confirmed' => 'confirmed',
            'preparing' => 'preparing',
            'out_for_delivery' => 'ready',
            'ready' => 'ready',
            'delivered' => 'delivered',
            'cancelled' => 'cancelled',
        ];

        $normalized = $mapping[$value] ?? $value;
        $valid = ['pending', 'confirmed', 'preparing', 'ready', 'delivered', 'cancelled'];
        $this->attributes['status'] = in_array($normalized, $valid, true) ? $normalized : 'pending';
    }

    /**
     * ✅ NOVO: Relacionamento com cliente (multi-instância)
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Relacionamento com cliente (customer)
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Relacionamento com endereço
     */
    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    /**
     * Relacionamento com pagamento
     */
    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class, 'order_id');
    }

    /**
     * Relacionamento com itens do pedido
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Relacionamento com cupons
     */
    public function coupons(): HasMany
    {
        return $this->hasMany(OrderCoupon::class);
    }

    /**
     * Débitos/fiado vinculados ao pedido (customer_debts com order_id)
     */
    public function debts(): HasMany
    {
        return $this->hasMany(\App\Models\CustomerDebt::class, 'order_id');
    }

    /**
     * Relacionamento com taxa de entrega
     */
    public function orderDeliveryFee()
    {
        return $this->hasOne(OrderDeliveryFee::class);
    }

    /**
     * Scope para pedidos por status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope para pedidos recentes
     */
    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Accessor para status em português
     */
    public function getStatusLabelAttribute()
    {
        $statuses = [
            'pending' => 'Pendente',
            'confirmed' => 'Confirmado',
            'preparing' => 'Preparando',
            'ready' => 'Pronto',
            'delivered' => 'Entregue',
            'cancelled' => 'Cancelado',
        ];

        return $statuses[$this->status] ?? $this->status;
    }

    /**
     * Accessor para tipo de entrega em português
     */
    public function getDeliveryTypeLabelAttribute()
    {
        $types = [
            'pickup' => 'Retirada',
            'delivery' => 'Entrega',
        ];

        return $types[$this->delivery_type] ?? $this->delivery_type;
    }

    /**
     * Accessor para status do pagamento em português
     */
    public function getPaymentStatusLabelAttribute()
    {
        $statuses = [
            'pending' => 'Pendente',
            'paid' => 'Pago',
            'failed' => 'Falhou',
            'refunded' => 'Reembolsado',
        ];

        return $statuses[$this->payment_status] ?? $this->payment_status;
    }

    /**
     * Relacionamento com histórico de tracking GPS
     */
    public function trackingLocations()
    {
        return $this->hasMany(DeliveryTracking::class)->orderBy('tracked_at', 'desc');
    }
}
