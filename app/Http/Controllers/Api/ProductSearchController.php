<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductSearchController extends Controller
{
    public function index(Request $request)
    {
        $q = trim($request->query('q', ''));
        if ($q === '') return response()->json([]);

        $rows = Product::query()
            ->where(function($w) use ($q){
                $w->where('name', 'like', "%{$q}%")
                  ->orWhere('sku', 'like', "%{$q}%");
            })
            ->where('is_active', 1)
            ->limit(15)
            ->get(['id','name','price']);

        return response()->json($rows->map(fn($p)=>[
            'id'=>$p->id, 'nome'=>$p->name, 'preco'=> (float) $p->price
        ]));
    }
}
