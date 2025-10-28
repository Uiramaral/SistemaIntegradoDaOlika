<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductGalleryController extends Controller
{
    public function store(Request $r, Product $product)
    {
        $r->validate(['images.*' => 'required|image|max:4096']);
        $files = $r->file('images', []);
        $created = [];

        foreach ($files as $i => $file) {
            $path = $file->store('products','public'); // storage/app/public/products

            $img = $product->images()->create([
                'path' => $path,
                'is_primary' => $product->images()->count() === 0 ? 1 : 0,
                'sort_order' => ($product->images()->max('sort_order') ?? 0) + 1,
            ]);

            // seta cover_image se vazio
            if($img->is_primary && !$product->cover_image){
                $product->update(['cover_image'=>$img->path]);
            }

            $created[] = $img;
        }

        return response()->json(['ok'=>true,'items'=>$created]);
    }

    public function primary(Product $product, ProductImage $image)
    {
        abort_unless($image->product_id === $product->id, 404);

        $product->images()->update(['is_primary'=>0]);
        $image->update(['is_primary'=>1]);
        $product->update(['cover_image'=>$image->path]);

        return response()->json(['ok'=>true]);
    }

    public function destroy(Product $product, ProductImage $image)
    {
        abort_unless($image->product_id === $product->id, 404);

        $wasPrimary = $image->is_primary;

        @Storage::disk('public')->delete($image->path);
        $image->delete();

        if($wasPrimary){
            $new = $product->images()->orderBy('sort_order')->first();
            $product->update(['cover_image' => $new ? $new->path : null]);
            if($new){ $new->update(['is_primary'=>1]); }
        }

        return response()->json(['ok'=>true]);
    }

    public function move(Product $product, ProductImage $image, $direction)
    {
        abort_unless($image->product_id === $product->id, 404);

        $dir = $direction === 'up' ? -1 : 1;
        $current = $image->sort_order;

        $swap = $product->images()->where('sort_order', $current + $dir)->first();

        if($swap){
            $image->update(['sort_order'=>$current + $dir]);
            $swap->update(['sort_order'=>$current]);
        }

        return response()->json(['ok'=>true]);
    }
}
