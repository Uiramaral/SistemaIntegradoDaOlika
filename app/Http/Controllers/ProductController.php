<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $r){
        $q = trim($r->get('q',''));
        $query = DB::table('products');
        if($q!==''){ $query->where('name','like',"%{$q}%"); }
        $products = $query->orderBy('id','desc')->paginate(24);
        // traz 1 imagem (principal) por produto
        $images = DB::table('product_images')->whereIn('product_id',$products->pluck('id'))->where('is_primary',1)->get()->keyBy('product_id');
        return view('dashboard.products.index', compact('products','images','q'));
    }

    public function edit($productId){
        $p = DB::table('products')->where('id',$productId)->first();
        abort_if(!$p,404);
        $images = DB::table('product_images')->where('product_id',$productId)->orderBy('is_primary','desc')->orderBy('sort_order')->get();
        $allergens = DB::table('allergens')->orderBy('grouping')->orderBy('name')->get();
        $sel = DB::table('product_allergens')->where('product_id',$productId)->get()->keyBy('allergen_id');
        return view('dashboard.products.edit', compact('p','images','allergens','sel'));
    }

    public function update($productId, Request $r){
        $data = $r->validate([
            'name'=>'required|string',
            'price'=>'required|numeric|min:0',
            'description'=>'nullable|string',
            'force_gluten_warning'=>'nullable|boolean',
            'allergen.*.present'=>'nullable|boolean',
            'allergen.*.may'=>'nullable|boolean',
        ]);
        DB::table('products')->where('id',$productId)->update([
            'name'=>$data['name'],
            'price'=>$data['price'],
            'description'=>$data['description'] ?? null,
            'force_gluten_warning'=>$r->boolean('force_gluten_warning'),
            'updated_at'=>now(),
        ]);
        // grava alérgenos
        $all = DB::table('allergens')->pluck('id');
        foreach($all as $aid){
            $present = $r->boolean("allergen.$aid.present");
            $may = $r->boolean("allergen.$aid.may");
            if(!$present && !$may){
                DB::table('product_allergens')->where(['product_id'=>$productId,'allergen_id'=>$aid])->delete();
            }else{
                DB::table('product_allergens')->updateOrInsert(
                    ['product_id'=>$productId,'allergen_id'=>$aid],
                    ['present'=>$present?1:0,'may_contain'=>$may?1:0]
                );
            }
        }
        // gera texto de alérgenos
        $present = DB::table('product_allergens')->join('allergens','allergens.id','=','product_allergens.allergen_id')
                    ->where('product_id',$productId)->where('present',1)->pluck('allergens.name')->toArray();
        $may = DB::table('product_allergens')->join('allergens','allergens.id','=','product_allergens.allergen_id')
                    ->where('product_id',$productId)->where('may_contain',1)->pluck('allergens.name')->toArray();
        $parts = [];
        if($present){ $parts[] = 'Contém: '.implode(', ',$present).'.'; }
        if($may){ $parts[] = 'Pode conter: '.implode(', ',$may).'.'; }
        if (DB::table('products')->where('id',$productId)->value('force_gluten_warning')){
            // política da casa: sempre avisar contaminação por glúten/trigo
            $parts[] = 'Pode conter traços de glúten e farinha de trigo.';
        }
        DB::table('products')->where('id',$productId)->update(['allergens_description'=> trim(implode(' ', $parts)) ?: null]);

        return back()->with('ok', true);
    }

    /* ====== Galeria de imagens ====== */
    public function storeImage($productId, Request $r){
        $r->validate(['image'=>'required|image|max:4096']);
        $path = $r->file('image')->store("products/{$productId}", 'public');
        $isPrimary = $r->boolean('is_primary');
        if($isPrimary){
            DB::table('product_images')->where('product_id',$productId)->update(['is_primary'=>0]);
        }
        DB::table('product_images')->insert([
            'product_id'=>$productId,'path'=>$path,'is_primary'=>$isPrimary?1:0,'sort_order'=>999,'created_at'=>now(),'updated_at'=>now()
        ]);
        if($isPrimary){ DB::table('products')->where('id',$productId)->update(['cover_image'=>$path]); }
        return back()->with('ok', true);
    }

    public function makePrimary($productId, $imageId){
        DB::table('product_images')->where('product_id',$productId)->update(['is_primary'=>0]);
        DB::table('product_images')->where(['id'=>$imageId,'product_id'=>$productId])->update(['is_primary'=>1]);
        $path = DB::table('product_images')->where('id',$imageId)->value('path');
        DB::table('products')->where('id',$productId)->update(['cover_image'=>$path]);
        return back()->with('ok', true);
    }

    public function destroyImage($productId, $imageId){
        $img = DB::table('product_images')->where(['id'=>$imageId,'product_id'=>$productId])->first();
        if($img){
            Storage::disk('public')->delete($img->path);
            DB::table('product_images')->where('id',$imageId)->delete();
        }
        return back()->with('ok', true);
    }
}
