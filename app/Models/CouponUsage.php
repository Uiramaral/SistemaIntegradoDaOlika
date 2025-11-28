<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CouponUsage extends Model
{
    protected $fillable = [
        'coupon_id',
        'customer_id',
        'order_id',
        'used_at',
    ];

    public $timestamps = false;

    /**
     * Relacionamento com cupom
     */
    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    /**
     * Relacionamento com cliente
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Relacionamento com pedido
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}

