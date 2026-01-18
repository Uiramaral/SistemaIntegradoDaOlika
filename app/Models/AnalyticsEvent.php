<?php

namespace App\Models;

use App\Models\Traits\BelongsToClient;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalyticsEvent extends Model
{
    use BelongsToClient;

    protected $fillable = [
        'client_id',
        'event_type',
        'page_path',
        'session_id',
        'ip_address',
        'user_agent',
        'product_id',
        'order_id',
        'customer_id',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    public $timestamps = false;

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            $model->created_at = now();
        });
    }

    /**
     * Relacionamento com produto (se aplicável)
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Relacionamento com pedido (se aplicável)
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Relacionamento com cliente (se aplicável)
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Registrar evento de visualização de página
     */
    public static function trackPageView($request, string $pagePath): self
    {
        return self::create([
            'event_type' => 'page_view',
            'page_path' => $pagePath,
            'session_id' => session()->getId(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }

    /**
     * Registrar evento de adição ao carrinho
     */
    public static function trackAddToCart(int $productId, array $metadata = []): self
    {
        return self::create([
            'event_type' => 'add_to_cart',
            'product_id' => $productId,
            'session_id' => session()->getId(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'metadata' => $metadata,
        ]);
    }

    /**
     * Registrar evento de início de checkout
     */
    public static function trackCheckoutStarted(?int $customerId = null, array $metadata = []): self
    {
        return self::create([
            'event_type' => 'checkout_started',
            'page_path' => request()->path(),
            'session_id' => session()->getId(),
            'customer_id' => $customerId,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'metadata' => $metadata,
        ]);
    }

    /**
     * Registrar evento de compra finalizada
     */
    public static function trackPurchase(int $orderId, ?int $customerId = null, array $metadata = []): self
    {
        return self::create([
            'event_type' => 'purchase',
            'order_id' => $orderId,
            'customer_id' => $customerId,
            'session_id' => session()->getId(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'metadata' => $metadata,
        ]);
    }
}

