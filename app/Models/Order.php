<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'address_id',
        'visitor_id',
        'order_number',
        'status',
        'total_amount',
        'delivery_fee',
        'discount_amount',
        'coupon_code',
        'discount_type',
        'discount_original_value',
        'manual_discount_type',
        'manual_discount_value',
        'cashback_used',
        'cashback_earned',
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
        'print_requested_at',
        'printed_at',
        'notified_paid_at',
        'payment_review_notified_at',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'pix_expires_at' => 'datetime',
        'scheduled_delivery_at' => 'datetime',
        'payment_raw_response' => 'array',
        'print_requested_at' => 'datetime',
        'printed_at' => 'datetime',
        'notified_paid_at' => 'datetime',
        'payment_review_notified_at' => 'datetime',
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
     * Relacionamento com cliente
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
        return $this->hasOne(Payment::class);
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
