<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\ClientScope;

class Packaging extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'name',
        'description',
        'cost',
        'is_active',
    ];

    protected $casts = [
        'cost' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    protected static function booted()
    {
        static::addGlobalScope(new ClientScope());
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
