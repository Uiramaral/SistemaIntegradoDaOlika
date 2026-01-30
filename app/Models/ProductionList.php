<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Scopes\ClientScope;

class ProductionList extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'production_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'production_date' => 'date',
    ];

    protected static function booted()
    {
        static::addGlobalScope(new ClientScope());
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ProductionListItem::class)->orderBy('sort_order');
    }

    public function getTotalItemsAttribute()
    {
        return $this->items()->count();
    }

    public function getProducedItemsAttribute()
    {
        return $this->items()->where('is_produced', true)->count();
    }

    public function getProgressAttribute()
    {
        $total = $this->total_items;
        if ($total == 0) return 0;
        return ($this->produced_items / $total) * 100;
    }
}
