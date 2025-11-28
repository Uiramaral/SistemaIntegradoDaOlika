<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = ['user_id','action','model_type','model_id','changes','ip','ua','created_at'];

    protected $casts = ['changes' => 'array', 'created_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
