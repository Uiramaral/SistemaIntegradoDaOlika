<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function search(Request $req)
    {
        $q = trim($req->get('q', ''));
        if ($q === '') return response()->json([]);

        $items = \App\Models\Product::query()
            ->select(['id','name','sku','price','is_active'])
            ->where('is_active', 1)
            ->when($q, function($sql) use ($q) {
                $like = "%{$q}%";
                $sql->where(function($w) use ($like) {
                    $w->where('name','like',$like)
                      ->orWhere('sku','like',$like);
                });
            })
            ->orderBy('name')
            ->limit(30)
            ->get()
            ->map(fn($p) => [
                'id'    => $p->id,
                'label' => $p->name,
                'sku'   => $p->sku,
                'price' => (float)$p->price,
                'meta'  => $p->sku,
            ]);

        return response()->json($items);
    }

    public function index(Request $r){
        $q = trim($r->get('q',''));
        $products = \App\Models\Product::with('category')
            ->when($q, fn($qb) =>
                $qb->where('name','like',"%{$q}%")
                   ->orWhere('sku','like',"%{$q}%")
            )
            ->orderBy('is_active','desc')
            ->orderBy('name')
            ->paginate(12);
        return view('dash.pages.products', compact('products'));
    }

    public function toggle(\App\Models\Product $product)
    {
        $product->is_active = !$product->is_active;
        $product->save();

        return back()->with('success', 'Status atualizado.');
    }

    public function edit(\App\Models\Product $product)
    {
        $product->load(['images','allergens','category']);
        $allergens = \App\Models\Allergen::orderBy('group_name')->orderBy('name')->get()->groupBy('group_name');
        $categories = \App\Models\Category::orderBy('name')->get();

        // prévia de descrição gerada (não salva)
        $generated_preview       = $product->generateDefaultDescription(); // longa (site)
        $generated_label_preview = $product->generateLabelText();          // curta (rótulo)

        return view('dash.pages.products', compact(
            'product','allergens','categories','generated_preview','generated_label_preview'
        ));
    }

    public function update(Request $req, \App\Models\Product $product)
    {
        $data = $req->validate([
            'name'  => 'required|string|max:180',
            'sku'   => 'nullable|string|max:60',
            'price' => 'required|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'gluten_free'        => 'nullable|boolean',
            'contamination_risk' => 'nullable|boolean',
            'description'        => 'nullable|string',
            'label_description'  => 'nullable|string',
            'seo_title'          => 'nullable|string|max:70',
            'seo_description'    => 'nullable|string|max:160',
            'auto_description'        => 'nullable|boolean',
            'auto_label_description'  => 'nullable|boolean',
            'allergens'          => 'array',
            'allergens.*'        => 'integer|exists:allergens,id',
        ]);

        $data['gluten_free']        = (bool)$req->boolean('gluten_free');
        $data['contamination_risk'] = (bool)$req->boolean('contamination_risk');

        $product->update($data);

        // pivot de alérgenos
        $ids = collect($req->get('allergens', []))->map('intval')->filter()->unique()->values()->all();
        $product->allergens()->sync($ids);

        // Se deve gerar a descrição automaticamente (flag) ou se ficou vazia
        $shouldAutoLong = $req->boolean('auto_description') || !trim((string)($product->description ?? ''));

        if ($shouldAutoLong) {
            // recupera nomes dos alérgenos recém salvos
            $allergenNames = $product->allergens()->pluck('name')->all();

            // override com dados do form
            $override = [
                'name'              => $product->name,
                'price'             => $product->price,
                'category'          => $product->category, // objeto
                'gluten_free'       => $product->gluten_free,
                'contamination_risk'=> $product->contamination_risk,
                'allergen_names'    => $allergenNames,
            ];

            $auto = $product->generateDefaultDescription($override);
            $product->update(['description' => $auto]);
        }

        // geração automática (descrição de rótulo curta)
        $shouldAutoLabel = $req->boolean('auto_label_description') || !trim((string)($product->label_description ?? ''));

        if ($shouldAutoLabel) {
            $override = [
                'name'              => $product->name,
                'category'          => $product->category,
                'gluten_free'       => $product->gluten_free,
                'contamination_risk'=> $product->contamination_risk,
                'allergen_names'    => $product->allergens()->pluck('name')->all(),
            ];

            $autoLabel = $product->generateLabelText($override);
            $product->update(['label_description' => $autoLabel]);
        }

        // cover_image: se não houver, e existir imagem primária na galeria, sincroniza
        if(!$product->cover_image){
            $primary = $product->images()->where('is_primary',1)->first();
            if($primary){ $product->update(['cover_image'=>$primary->path]); }
        }

        return back()->with('success','Produto atualizado.');
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
