<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Scopes\ClientScope;

class ProductionRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'recipe_id',
        'recipe_name',
        'quantity',
        'weight',
        'total_produced',
        'production_date',
        'observation',
        'cost',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'weight' => 'decimal:2',
        'total_produced' => 'decimal:2',
        'production_date' => 'date',
        'cost' => 'decimal:2',
    ];

    protected static function booted()
    {
        static::addGlobalScope(new ClientScope());
        
        static::creating(function ($record) {
            if (empty($record->total_produced)) {
                $record->total_produced = $record->quantity * $record->weight;
            }
        });
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }
}
