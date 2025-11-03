<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\Product;

class ImportIngredientsFromGist extends Command
{
    protected $signature = 'ingredients:import-gist {url?} {--create-products=1}';
    protected $description = 'Importa receitas do Gist: cria/atualiza produtos, ingredientes e vínculo com porcentagens';

    public function handle()
    {
        $url = $this->argument('url') ?: config('services.ingredients.gist_url', env('INGREDIENTS_GIST_URL'));
        if (!$url) { $this->error('Informe a URL (RAW) do Gist JSON.'); return Command::FAILURE; }

        $json = Http::timeout(25)->get($url)->json();
        if (!$json) { $this->error('JSON inválido ao baixar Gist.'); return Command::FAILURE; }

        $recipes = data_get($json, 'recipes', []);
        if (!is_array($recipes) || empty($recipes)) { $this->warn('Nenhuma receita em recipes[].'); return Command::SUCCESS; }

        $createProducts = (bool)$this->option('create-products');
        $updated = 0; $created = 0; $notFound = [];

        foreach ($recipes as $r) {
            $name = trim((string) data_get($r, 'name'));
            if ($name === '') continue;
            $categoryName = trim((string) data_get($r, 'category')) ?: null;

            // Extrair ingredientes (nome e percentage)
            $ings = [];
            foreach ((array) data_get($r, 'steps', []) as $step) {
                foreach ((array) data_get($step, 'ingredients', []) as $ing) {
                    $id = (string) data_get($ing, 'id');
                    if ($id === '') continue;
                    $nice = (string) Str::of($id)->replace(['_', '-'], ' ')->lower()->title();
                    $pct  = (float) data_get($ing, 'percentage', 0);
                    $ings[$nice] = max($pct, $ings[$nice] ?? 0); // mantém maior pct caso repita
                }
            }
            $ingredientsText = implode(', ', array_keys($ings));

            // Localizar produto
            $product = Product::whereRaw('LOWER(name)=?', [Str::lower($name)])->first();
            if (!$product && $createProducts) {
                // Tenta achar categoria por nome (opcional)
                $catId = null;
                if ($categoryName) {
                    $catId = DB::table('categories')->whereRaw('LOWER(name)=?', [Str::lower($categoryName)])->value('id');
                }

                // Se a coluna products.category_id for NOT NULL, garanta uma categoria padrão
                if (!$catId) {
                    $defaultCatName = 'Sem categoria';
                    $defaultCatSlug = Str::slug($defaultCatName);
                    $catId = DB::table('categories')->whereRaw('LOWER(name)=?', [Str::lower($defaultCatName)])->value('id');
                    if (!$catId) {
                        // tenta por slug também
                        $catId = DB::table('categories')->where('slug', $defaultCatSlug)->value('id');
                    }
                    if (!$catId) {
                        // cria categoria padrão caso não exista
                        $catId = DB::table('categories')->insertGetId([
                            'name' => $defaultCatName,
                            'slug' => $defaultCatSlug,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }

                // cria com mínimos — cobrindo esquemas com category_id NOT NULL
                $product = new Product();
                $product->name = $name;
                if ($catId) {
                    $product->category_id = $catId;
                }
                // slug opcional, caso exista no schema e seja NOT NULL/UNIQUE
                if (Schema::hasColumn('products', 'slug')) {
                    $baseSlug = Str::slug($name) ?: ('produto-'.Str::random(6));
                    $slug = $baseSlug;
                    $i = 1;
                    while (DB::table('products')->where('slug', $slug)->exists()) {
                        $slug = $baseSlug.'-'.$i;
                        $i++;
                        if ($i > 50) { $slug = $baseSlug.'-'.Str::random(4); break; }
                    }
                    // evita erro caso a coluna exista mas não esteja fillable
                    $product->setAttribute('slug', $slug);
                }
                $product->price = 0;
                $product->is_active = true;
                $product->is_available = true;
                $product->save();
                $created++;
                $this->info("Criado produto: {$product->name}");
            }
            if (!$product) { $notFound[] = $name; continue; }

            // Atualiza lista livre em nutritional_info
            $nutri = $product->nutritional_info ?? [];
            if ($ingredientsText !== '') { $nutri['ingredients'] = $ingredientsText; }
            $product->nutritional_info = $nutri;
            $product->save();

            // Popular tabelas ingredients e product_ingredient
            foreach ($ings as $ingName => $pct) {
                $slug = Str::slug($ingName);
                $ingId = DB::table('ingredients')->where('slug', $slug)->value('id');
                if (!$ingId) {
                    $ingId = DB::table('ingredients')->insertGetId(['name'=>$ingName,'slug'=>$slug]);
                }
                DB::table('product_ingredient')->updateOrInsert(
                    ['product_id'=>$product->id,'ingredient_id'=>$ingId],
                    ['percentage'=>$pct > 0 ? $pct : null]
                );
            }

            $updated++;
            $this->line("Atualizado: {$product->name} (".count($ings)." ingredientes)");
        }

        // Importar ingredientes definidos globalmente no JSON (além dos usados em receitas)
        // Suporta várias chaves comuns e detecção por nome contendo "ingredient"
        $globalIngredients = (array) data_get($json, 'ingredients', []);
        if (empty($globalIngredients)) { $globalIngredients = (array) data_get($json, 'ingredientes', []); }
        if (empty($globalIngredients)) { $globalIngredients = (array) data_get($json, 'ingredients_global', []); }
        if (empty($globalIngredients)) { $globalIngredients = (array) data_get($json, 'all_ingredients', []); }
        if (empty($globalIngredients)) { $globalIngredients = (array) data_get($json, 'ingredients_list', []); }
        if (empty($globalIngredients)) { $globalIngredients = (array) data_get($json, 'catalog.ingredients', []); }

        // Varredura top-level: qualquer chave que contenha 'ingredient'
        if (empty($globalIngredients) && is_array($json)) {
            foreach ($json as $k => $v) {
                if (is_string($k) && stripos($k, 'ingredient') !== false && is_array($v)) {
                    $globalIngredients = (array) $v;
                    break;
                }
            }
        }
        $globalCount = 0; $globalSkipped = 0;
        foreach ($globalIngredients as $gi) {
            // pode ser string ou objeto {id: "..." | name: "..."}
            $raw = is_array($gi) ? ((string) ($gi['name'] ?? $gi['id'] ?? '')) : (string) $gi;
            $raw = trim($raw);
            if ($raw === '') { $globalSkipped++; continue; }
            $nice = (string) Str::of($raw)->replace(['_', '-'], ' ')->lower()->title();
            $slug = Str::slug($nice);
            if (!$slug) { $globalSkipped++; continue; }
            $exists = DB::table('ingredients')->where('slug', $slug)->exists();
            if (!$exists) {
                DB::table('ingredients')->insert([
                    'name' => $nice,
                    'slug' => $slug,
                ]);
                $globalCount++;
            } else {
                $globalSkipped++;
            }
        }

        $this->info("Produtos atualizados: {$updated}, criados: {$created}");
        if (!empty($globalIngredients)) {
            $this->info("Ingredientes globais importados: {$globalCount}, já existentes/ignorados: {$globalSkipped}");
        }
        if ($notFound) {
            $this->warn('Sem correspondência e não criados (create-products=0):');
            foreach ($notFound as $n) $this->line(' - '.$n);
        }

        return Command::SUCCESS;
    }
}
