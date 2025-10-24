<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'visitor_id',
        'order_number',
        'status',
        'total_amount',
        'delivery_fee',
        'discount_amount',
        'coupon_code',
        'final_amount',
        'payment_method',
        'payment_provider',
        'preference_id',
        'payment_id',
        'payment_link',
        'pix_copy_paste',
        'pix_qr_base64',
        'pix_expires_at',
        'payment_raw_response',
        'payment_status',
        'delivery_type',
        'delivery_address',
        'delivery_instructions',
        'estimated_time',
        'notes',
        'delivery_complement',
        'delivery_neighborhood',
        'observations',
        'scheduled_delivery_at',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'pix_expires_at' => 'datetime',
        'scheduled_delivery_at' => 'datetime',
        'payment_raw_response' => 'array',
    ];

    /**
     * Relacionamento com cliente
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
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
}
