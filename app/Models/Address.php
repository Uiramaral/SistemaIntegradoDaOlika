<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'cep',
        'street',
        'number',
        'complement',
        'district',
        'city',
        'state',
    ];

    /**
     * Relacionamento com cliente
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Relacionamento com pedidos
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Accessor para endereço completo
     */
    public function getFullAddressAttribute()
    {
        $parts = array_filter([
            $this->street,
            $this->number ? "nº {$this->number}" : null,
            $this->complement,
            $this->district,
            $this->city,
            $this->state,
        ]);

        return implode(', ', $parts);
    }
}

