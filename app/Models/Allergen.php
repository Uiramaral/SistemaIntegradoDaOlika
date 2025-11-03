<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Allergen extends Model
{
    public $timestamps = false; // A tabela não tem created_at/updated_at
    
    protected $fillable = ['name','slug','group_name'];
    
    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_allergen');
    }
}
