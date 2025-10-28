<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Allergen extends Model
{
    public $timestamps = false;
    
    protected $fillable = ['name','slug','group_name'];
    
    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_allergen');
    }
}
