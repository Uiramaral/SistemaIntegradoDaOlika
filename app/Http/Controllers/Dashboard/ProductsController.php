<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\ImageOptimizer;
use App\Models\Product;
use App\Models\Category;
use App\Models\Allergen;
use App\Models\ProductImage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Services\OpenAIService;
use App\Models\ProductVariant;

class ProductsController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'allergens', 'images'])
            ->withCount([
                'orderItems as sales_count' => function ($q) {
                    $q->select(DB::raw('SUM(quantity)'));
                }
            ])
            ->latest();

        // Busca
        $searchTerm = $request->input('q') ?? $request->input('search');
        if (!empty($searchTerm)) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                    ->orWhere('sku', 'like', "%{$searchTerm}%")
                    ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }

        // Filtro por categoria
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filtro por status
        if ($request->has('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Se for requisição AJAX, retornar JSON sem paginação
        if ($request->ajax() || $request->wantsJson()) {
            $allProducts = $query->get();
            return response()->json([
                'products' => $allProducts->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'category_name' => $product->category->name ?? 'Sem categoria',
                        'price' => (float) ($product->price ?? 0),
                        'stock' => (int) ($product->stock ?? $product->inventory ?? 0),
                        'sales_count' => (int) ($product->order_items_sum_quantity ?? 0),
                        'is_active' => $product->is_active ?? true,
                        'cover_image' => $product->cover_image,
                    ];
                })
            ]);
        }

        $products = $query->paginate(20)->withQueryString();
        $categories = Category::active()->ordered()->get();

        return view('dashboard.products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = Category::active()->ordered()->get();
        $allergens = Allergen::orderBy('group_name')->orderBy('name')->get()->groupBy('group_name');
        return view('dashboard.products.create', compact('categories', 'allergens'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => [
                'nullable',
                'string',
                'max:100',
                \Illuminate\Validation\Rule::unique('products')->where(function ($query) {
                    return $query->where('client_id', auth()->user()->client_id);
                })
            ],
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'weight_grams' => 'nullable|integer|min:0',
            'stock' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'label_description' => 'nullable|string',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string',
            'is_active' => 'boolean',
            'show_in_catalog' => 'boolean',
            'is_available' => 'boolean',
            'is_featured' => 'boolean',
            'gluten_free' => 'boolean',
            'contamination_risk' => 'boolean',
            'preparation_time' => 'nullable|integer|min:0',
            'sort_order' => 'nullable|integer|min:0',
            'cover_image' => 'nullable|image|max:5120', // 5MB
            'images' => 'nullable|array',
            'images.*' => 'image|max:5120', // 5MB
            'allergen_ids' => 'nullable|array',
            'allergen_ids.*' => 'exists:allergens,id',
            'nutritional_info' => 'nullable|array',
            'ingredients' => 'nullable|string',
            'uses_baker_percentage' => 'boolean',
        ]);

        // ✅ Inject client_id for multi-tenancy
        if (auth()->check() && auth()->user()->client_id) {
            $validated['client_id'] = auth()->user()->client_id;
        }

        try {
            DB::beginTransaction();

            // Incorporar lista de ingredientes em nutritional_info
            $nutri = $validated['nutritional_info'] ?? [];
            if ($request->filled('ingredients')) {
                $nutri['ingredients'] = $request->input('ingredients');
            }
            $validated['nutritional_info'] = $nutri;

            $coverImagePath = null;
            $uploadedImages = [];

            // Upload da imagem de capa
            if ($request->hasFile('cover_image')) {
                try {
                    $ext = $request->file('cover_image')->getClientOriginalExtension();
                    $base = Str::slug($validated['name'] ?? ('produto-' . time()));
                    $filename = $base . '-cover-' . time() . '.' . $ext;
                    $coverImagePath = $request->file('cover_image')->storeAs('uploads/products', $filename, 'public');
                    $validated['cover_image'] = $coverImagePath;

                    // Otimizar imagem: gerar WebP e thumbnails
                    try {
                        ImageOptimizer::optimize($coverImagePath);
                    } catch (\Exception $optError) {
                        Log::warning('Erro ao otimizar imagem de capa (store)', [
                            'path' => $coverImagePath,
                            'error' => $optError->getMessage()
                        ]);
                    }
                } catch (\Exception $e) {
                    DB::rollBack();
                    return redirect()
                        ->back()
                        ->withInput()
                        ->with('error', 'Erro ao fazer upload da imagem de capa: ' . $e->getMessage());
                }
            }

            // Auto-IA ao criar: SEMPRE gera na criação (primeira vez)
            $ingredientsText = $nutri['ingredients'] ?? ($request->input('ingredients') ?? '');
            $weightGrams = (int) ($validated['weight_grams'] ?? 0);

            // Na criação: se usar "Criar + IA", gera descrições e SEO. Caso contrário, só gera se tiver ingredientes
            $useAi = $request->input('action') === 'save_ai';

            if ($useAi || trim((string) $ingredientsText) !== '') {
                $ai = new OpenAIService();
                if ($ai->isConfigured()) {
                    // Preparar variantes do formulário (se houver, antes de salvar)
                    $variants = [];
                    $variantNames = (array) $request->input('variant_name', []);
                    $variantWeights = (array) $request->input('variant_weight', []);
                    $variantPrices = (array) $request->input('variant_price', []);
                    foreach ($variantNames as $idx => $name) {
                        if (trim((string) $name) !== '') {
                            $variants[] = [
                                'name' => trim($name),
                                'weight_grams' => (int) ($variantWeights[$idx] ?? 0),
                                'price' => (float) ($variantPrices[$idx] ?? 0)
                            ];
                        }
                    }

                    // Preparar alergênicos dos selecionados
                    $allergenIds = array_map('intval', (array) $request->input('allergen_ids', []));
                    $allergenNames = [];
                    if (!empty($allergenIds)) {
                        $allergens = \App\Models\Allergen::whereIn('id', $allergenIds)->pluck('name')->all();
                        $allergenNames = array_filter($allergens);
                    }

                    $existingDesc = trim((string) ($validated['description'] ?? ''));

                    $gen = $ai->generateProductDescriptions(
                        $validated['name'],
                        $ingredientsText,
                        $existingDesc ?: null,
                        $weightGrams > 0 ? $weightGrams : null,
                        $variants,
                        $allergenNames
                    );

                    // Na criação, sempre sobrescreve se gerado (ou mantém se já tinha)
                    if (!empty($gen['description'])) {
                        $validated['description'] = $gen['description'];
                    }
                    if (!empty($gen['label'])) {
                        $validated['label_description'] = $gen['label'];
                    }

                    // Gerar SEO quando usar "Criar + IA"
                    if ($useAi) {
                        $categoryName = null;
                        if ($validated['category_id'] ?? null) {
                            $category = Category::find($validated['category_id']);
                            $categoryName = $category ? $category->name : null;
                        }

                        $ingredients = [];
                        if (!empty($ingredientsText)) {
                            $ingredients = array_filter(array_map('trim', preg_split('/[,\n]+/', $ingredientsText)));
                        }

                        $seoResult = $ai->generateSEO(
                            $validated['name'],
                            $validated['description'] ?? '',
                            $categoryName,
                            (float) ($validated['price'] ?? 0),
                            $ingredients
                        );

                        if ($seoResult['seo_title']) {
                            $validated['seo_title'] = $seoResult['seo_title'];
                        }
                        if ($seoResult['seo_description']) {
                            $validated['seo_description'] = $seoResult['seo_description'];
                        }
                    }
                }
            }

            // Sanitização de descrições com valores inválidos ocasionais
            foreach (['description', 'label_description'] as $k) {
                if (isset($validated[$k]) && is_string($validated[$k])) {
                    $val = trim($validated[$k]);
                    if (in_array($val, ['json', '"json"', '{', '['])) {
                        $validated[$k] = null;
                    }
                }
            }

            // Converter only_pdv para show_in_catalog (invertido)
            // Se only_pdv = true, então show_in_catalog = false (não aparece no catálogo)
            // Se only_pdv = false ou não enviado, então show_in_catalog = true (aparece no catálogo)
            $validated['show_in_catalog'] = !$request->boolean('only_pdv', false);
            unset($validated['only_pdv']); // Remover do array antes de criar

            // Definir valores padrão para campos booleanos se não foram enviados
            $validated['is_active'] = $request->has('is_active') ? (bool) $validated['is_active'] : true;
            $validated['is_available'] = $request->has('is_available') ? (bool) $validated['is_available'] : true;
            $validated['is_featured'] = $request->has('is_featured') ? (bool) ($validated['is_featured'] ?? false) : false;
            $validated['gluten_free'] = $request->has('gluten_free') ? (bool) ($validated['gluten_free'] ?? false) : false;
            $validated['contamination_risk'] = $request->has('contamination_risk') ? (bool) ($validated['contamination_risk'] ?? false) : false;

            // Criar produto ANTES de fazer upload das imagens adicionais
            $product = Product::create($validated);

            // Variantes (opcional)
            $this->syncVariants($request, $product);

            // Se o produto ficou com price 0 e existirem variações, usar o menor preço
            if ((float) $product->price <= 0) {
                $minVar = \App\Models\ProductVariant::where('product_id', $product->id)->min('price');
                if ($minVar !== null) {
                    $product->price = (float) $minVar;
                    $product->save();
                }
            }

            // Upload de imagens adicionais (após criar o produto para ter o ID)
            if ($request->hasFile('images')) {
                try {
                    foreach ($request->file('images') as $index => $image) {
                        $ext = $image->getClientOriginalExtension();
                        $base = Str::slug($product->name ?: 'produto');
                        $filename = $base . '-' . $product->id . '-' . ($index + 1) . '-' . time() . '.' . $ext;
                        $path = $image->storeAs('uploads/products', $filename, 'public');
                        $uploadedImages[] = $path;

                        ProductImage::create([
                            'product_id' => $product->id,
                            'path' => $path,
                            'is_primary' => $index === 0 && !$product->cover_image,
                            'sort_order' => $index,
                        ]);

                        // Otimizar imagem: gerar WebP e thumbnails
                        try {
                            ImageOptimizer::optimize($path);
                        } catch (\Exception $e) {
                            Log::warning('Erro ao otimizar imagem adicional', [
                                'path' => $path,
                                'error' => $e->getMessage()
                            ]);
                        }
                    }
                } catch (\Exception $e) {
                    // Se der erro, limpar imagens já enviadas
                    foreach ($uploadedImages as $imgPath) {
                        if (Storage::disk('public')->exists($imgPath)) {
                            Storage::disk('public')->delete($imgPath);
                        }
                    }
                    if ($coverImagePath && Storage::disk('public')->exists($coverImagePath)) {
                        Storage::disk('public')->delete($coverImagePath);
                    }
                    DB::rollBack();
                    return redirect()
                        ->back()
                        ->withInput()
                        ->with('error', 'Erro ao fazer upload das imagens: ' . $e->getMessage());
                }
            }

            // Sincronizar alérgenos (sempre a partir do array, mesmo que vazio)
            try {
                $ids = array_map('intval', (array) $request->input('allergen_ids', []));
                Log::info('Products.store:allergens:received', ['product' => $validated['name'] ?? null, 'ids' => $ids]);
                $validIds = \App\Models\Allergen::whereIn('id', $ids)->pluck('id')->all();
                $product->allergens()->sync($ids);
                $savedCount = \DB::table('product_allergen')->where('product_id', $product->id)->count();
                Log::info('Products.store:allergens:synced', ['product_id' => $product->id, 'saved_count' => $savedCount, 'valid_ids' => $validIds]);
            } catch (\Exception $e) {
                DB::rollBack();
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'Erro ao salvar alérgenicos: ' . $e->getMessage());
            }

            DB::commit();

            // Recarregar o produto com relacionamentos
            $product->load(['category', 'allergens', 'images']);

            return redirect()
                ->route('dashboard.products.show', $product)
                ->with('success', 'Produto criado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();

            // Limpar imagens em caso de erro geral
            if (isset($coverImagePath) && $coverImagePath && Storage::disk('public')->exists($coverImagePath)) {
                Storage::disk('public')->delete($coverImagePath);
            }
            if (isset($uploadedImages)) {
                foreach ($uploadedImages as $imgPath) {
                    if (Storage::disk('public')->exists($imgPath)) {
                        Storage::disk('public')->delete($imgPath);
                    }
                }
            }

            Log::error('Erro ao criar produto', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Erro ao criar produto: ' . $e->getMessage());
        }
    }

    public function show(Request $request, Product $product)
    {
        try {
            $product->load([
                'category',
                'allergens',
                'images',
                'variants' => function ($q) {
                    $q->orderBy('sort_order');
                }
            ]);

            // Carregar soma de vendas manualmente para evitar erro BelongsTo::loadSum
            $product->sales_count = (int) DB::table('order_items')
                ->where('product_id', $product->id)
                ->sum('quantity');

            // Resposta JSON para modal
            if ($request->wantsJson() || $request->ajax() || str_contains($request->header('Accept'), 'application/json')) {
                return response()->json([
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'price' => (float) $product->price,
                    'stock' => $product->stock,
                    'description' => $product->description,
                    'category' => $product->category ? [
                        'id' => $product->category->id,
                        'name' => $product->category->name,
                    ] : null,
                    'cover_image' => $product->cover_image,
                    'images' => ($product->images ?? collect())->map(function ($img) {
                        return [
                            'id' => $img->id,
                            'path' => $img->path,
                            'is_primary' => (bool) $img->is_primary,
                        ];
                    })->values(),
                    'allergens' => ($product->allergens ?? collect())->map(function ($alg) {
                        return [
                            'id' => $alg->id,
                            'name' => $alg->name,
                            'group_name' => $alg->group_name,
                        ];
                    })->values(),
                    'variants' => ($product->variants ?? collect())->map(function ($v) {
                        return [
                            'id' => $v->id,
                            'name' => $v->name,
                            'price' => (float) $v->price,
                            'sku' => $v->sku,
                            'is_active' => (bool) $v->is_active,
                            'sort_order' => (int) $v->sort_order,
                        ];
                    })->values(),
                    'gluten_free' => (bool) $product->gluten_free,
                    'contamination_risk' => (bool) $product->contamination_risk,
                    'is_active' => (bool) $product->is_active,
                    'is_available' => (bool) $product->is_available,
                    'is_featured' => (bool) $product->is_featured,
                    'preparation_time' => $product->preparation_time,
                    'sales_count' => (int) ($product->sales_count ?? 0),
                ]);
            }

            // Fallback: se alérgenos vierem vazios, buscar diretamente pelo pivô para exibição
            if (!$product->allergens || $product->allergens->count() === 0) {
                $ids = \DB::table('product_allergen')->where('product_id', $product->id)->pluck('allergen_id')->toArray();
                if (!empty($ids)) {
                    $fallbackAllergens = \App\Models\Allergen::whereIn('id', $ids)->orderBy('group_name')->orderBy('name')->get();
                    // anexa para a view sem afetar persistência
                    $product->setRelation('allergens', $fallbackAllergens);
                }
            }
            return view('dashboard.products.show', compact('product'));
        } catch (\Throwable $e) {
            \Log::error('Erro ao carregar produto para modal', ['id' => $product->id, 'error' => $e->getMessage()]);
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['error' => 'failed'], 500);
            }
            return view('dashboard.products.show', compact('product'));
        }
    }

    public function edit(Product $product)
    {
        // Garante dados frescos do banco, incluindo variantes
        $product = $product->fresh(['category', 'allergens', 'images', 'variants']);
        $categories = Category::active()->ordered()->get();
        $allergens = Allergen::orderBy('group_name')->orderBy('name')->get()->groupBy('group_name');

        // Garantir que selectedAllergens seja sempre um array, mesmo se não houver alérgenicos
        $selectedAllergens = [];
        try {
            // Recarregar relacionamento para garantir que está disponível
            if (!$product->relationLoaded('allergens')) {
                $product->load('allergens');
            }

            // Tentar acessar de forma segura
            if ($product->allergens && is_countable($product->allergens)) {
                $selectedAllergens = $product->allergens->pluck('id')->toArray();
            }
            \Log::info('Products.edit:selectedAllergens', ['product_id' => $product->id, 'ids' => $selectedAllergens]);
            // Se ainda vazio, buscar diretamente no pivô
            if (empty($selectedAllergens)) {
                $selectedAllergens = \DB::table('product_allergen')
                    ->where('product_id', $product->id)
                    ->pluck('allergen_id')
                    ->toArray();
                \Log::info('Products.edit:selectedAllergens:pivot', ['product_id' => $product->id, 'ids' => $selectedAllergens]);
            }
        } catch (\Exception $e) {
            // Em caso de erro, tentar via query direta
            try {
                $selectedAllergens = \DB::table('product_allergen')
                    ->where('product_id', $product->id)
                    ->pluck('allergen_id')
                    ->toArray();
            } catch (\Exception $e2) {
                // Em último caso, usar array vazio
                $selectedAllergens = [];
                Log::warning('Erro ao carregar alérgenicos do produto', [
                    'product_id' => $product->id,
                    'error' => $e->getMessage(),
                    'error2' => $e2->getMessage()
                ]);
            }
        }

        return view('dashboard.products.edit', compact('product', 'categories', 'allergens', 'selectedAllergens'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => [
                'nullable',
                'string',
                'max:100',
                \Illuminate\Validation\Rule::unique('products')->ignore($product->id)->where(function ($query) {
                    return $query->where('client_id', auth()->user()->client_id);
                })
            ],
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'weight_grams' => 'nullable|integer|min:0',
            'stock' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'label_description' => 'nullable|string',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string',
            'is_active' => 'boolean',
            'show_in_catalog' => 'boolean',
            'is_available' => 'boolean',
            'is_featured' => 'boolean',
            'gluten_free' => 'boolean',
            'contamination_risk' => 'boolean',
            'preparation_time' => 'nullable|integer|min:0',
            'sort_order' => 'nullable|integer|min:0',
            'cover_image' => 'nullable|image|max:5120', // 5MB
            'images' => 'nullable|array',
            'images.*' => 'image|max:5120', // 5MB
            'allergen_ids' => 'nullable|array',
            'allergen_ids.*' => 'exists:allergens,id',
            'nutritional_info' => 'nullable|array',
            'ingredients' => 'nullable|string',
            'uses_baker_percentage' => 'boolean',
        ]);

        try {
            DB::beginTransaction();

            // Mesclar ingredientes em nutritional_info
            $nutri = $validated['nutritional_info'] ?? ($product->nutritional_info ?? []);
            if ($request->filled('ingredients')) {
                $nutri['ingredients'] = $request->input('ingredients');
            }
            $validated['nutritional_info'] = $nutri;

            // Upload da nova imagem de capa (se houver)
            if ($request->hasFile('cover_image')) {
                Log::info('Products.update:cover_image:uploading', [
                    'product_id' => $product->id,
                    'old_cover' => $product->cover_image,
                    'file_size' => $request->file('cover_image')->getSize(),
                    'file_mime' => $request->file('cover_image')->getMimeType(),
                ]);

                // Deletar imagem antiga se existir
                if ($product->cover_image && Storage::disk('public')->exists($product->cover_image)) {
                    Storage::disk('public')->delete($product->cover_image);
                    Log::info('Products.update:cover_image:deleted_old', ['path' => $product->cover_image]);
                }

                $ext = $request->file('cover_image')->getClientOriginalExtension();
                $base = Str::slug($validated['name'] ?? $product->name ?? ('produto-' . $product->id));
                $filename = $base . '-cover-' . time() . '.' . $ext;
                $validated['cover_image'] = $request->file('cover_image')->storeAs('uploads/products', $filename, 'public');

                Log::info('Products.update:cover_image:saved', [
                    'new_path' => $validated['cover_image'],
                ]);

                // Otimizar imagem: gerar WebP e thumbnails
                try {
                    ImageOptimizer::optimize($validated['cover_image']);
                } catch (\Exception $e) {
                    Log::warning('Erro ao otimizar imagem de capa (update)', [
                        'path' => $validated['cover_image'],
                        'error' => $e->getMessage()
                    ]);
                }
            } else {
                Log::info('Products.update:cover_image:no_file', [
                    'product_id' => $product->id,
                    'has_file' => $request->hasFile('cover_image'),
                    'files' => array_keys($request->allFiles()),
                ]);
            }

            // Auto-IA no update: apenas se usar "Salvar + IA" OU se informações relevantes mudarem
            $useAi = $request->input('action') === 'save_ai';
            $oldIngredients = (string) ($product->nutritional_info['ingredients'] ?? '');
            $newIngredients = (string) ($nutri['ingredients'] ?? '');
            $oldWeight = (int) ($product->weight_grams ?? 0);
            $newWeight = (int) ($validated['weight_grams'] ?? 0);
            $ingredientsChanged = trim($oldIngredients) !== trim($newIngredients);
            $weightChanged = $oldWeight !== $newWeight;

            if ($useAi || ($ingredientsChanged && trim($newIngredients) !== '') || $weightChanged) {
                $ai = new OpenAIService();
                if ($ai->isConfigured()) {
                    // Se usar "Salvar + IA", ignorar descrições do formulário para usar apenas a gerada
                    if ($useAi) {
                        // Guardar descrição atual apenas como contexto, mas não usar no validated
                        $existingDescForContext = trim((string) ($validated['description'] ?? $product->description ?? ''));
                    } else {
                        $existingDescForContext = trim((string) ($product->description ?? ''));
                    }
                    // Buscar variantes do produto
                    $variants = [];
                    if ($product->relationLoaded('variants')) {
                        $variants = $product->variants->map(function ($v) {
                            return [
                                'name' => $v->name ?? '',
                                'weight_grams' => (int) ($v->weight_grams ?? 0),
                                'price' => (float) ($v->price ?? 0)
                            ];
                        })->filter(fn($v) => !empty($v['name']))->values()->all();
                    } else {
                        $variants = $product->variants()->get(['name', 'weight_grams', 'price'])->map(function ($v) {
                            return [
                                'name' => $v->name ?? '',
                                'weight_grams' => (int) ($v->weight_grams ?? 0),
                                'price' => (float) ($v->price ?? 0)
                            ];
                        })->filter(fn($v) => !empty($v['name']))->values()->all();
                    }

                    // Buscar alergênicos do produto
                    $allergenNames = [];
                    if ($product->relationLoaded('allergens') && $product->allergens) {
                        $allergenNames = $product->allergens->pluck('name')->filter()->values()->all();
                    } else {
                        $allergenNames = $product->allergens()->pluck('name')->filter()->values()->all();
                    }

                    $gen = $ai->generateProductDescriptions(
                        $validated['name'],
                        trim($newIngredients) !== '' ? $newIngredients : $oldIngredients,
                        ($existingDescForContext ?? null) ?: null,
                        $newWeight > 0 ? $newWeight : ($oldWeight > 0 ? $oldWeight : null),
                        $variants,
                        $allergenNames
                    );

                    // No update: se usar "Salvar + IA", sempre sobrescreve. Caso contrário, só preenche se vazio
                    if ($useAi) {
                        // Se usou "Salvar + IA", SEMPRE substitui com o resultado gerado (ignora o que veio do formulário)
                        Log::info('Products.update:save_ai_applying', [
                            'product_id' => $product->id,
                            'gen_description' => !empty($gen['description']),
                            'gen_label' => !empty($gen['label']),
                            'desc_len' => mb_strlen($gen['description'] ?? ''),
                            'label_len' => mb_strlen($gen['label'] ?? '')
                        ]);
                        if (!empty($gen['description'])) {
                            $validated['description'] = $gen['description'];
                        }
                        if (!empty($gen['label'])) {
                            $validated['label_description'] = $gen['label'];
                        }

                        // Gerar SEO quando usar "Salvar + IA"
                        $categoryName = null;
                        if ($product->category_id) {
                            $category = Category::find($product->category_id);
                            $categoryName = $category ? $category->name : null;
                        }

                        $ingredients = [];
                        if (!empty($newIngredients)) {
                            $ingredients = array_filter(array_map('trim', preg_split('/[,\n]+/', $newIngredients)));
                        }

                        $seoResult = $ai->generateSEO(
                            $validated['name'],
                            $validated['description'] ?? '',
                            $categoryName,
                            (float) ($validated['price'] ?? $product->price ?? 0),
                            $ingredients
                        );

                        if ($seoResult['seo_title']) {
                            $validated['seo_title'] = $seoResult['seo_title'];
                        }
                        if ($seoResult['seo_description']) {
                            $validated['seo_description'] = $seoResult['seo_description'];
                        }
                    } else {
                        // Se não forçou, só preenche campos vazios
                        if (empty($validated['description']) && !empty($gen['description'])) {
                            $validated['description'] = $gen['description'];
                        }
                        if (empty($validated['label_description']) && !empty($gen['label'])) {
                            $validated['label_description'] = $gen['label'];
                        }
                    }
                }
            }

            // Sanitização de descrições com valores inválidos ocasionais
            foreach (['description', 'label_description'] as $k) {
                if (isset($validated[$k]) && is_string($validated[$k])) {
                    $val = trim($validated[$k]);
                    if (in_array($val, ['json', '"json"', '{', '['])) {
                        $validated[$k] = null;
                    }
                }
            }

            // Atualizar produto
            // Converter only_pdv para show_in_catalog (invertido)
            $validated['show_in_catalog'] = !$request->boolean('only_pdv', false);
            unset($validated['only_pdv']);

            $product->update($validated);

            // Variantes (opcional)
            $this->syncVariants($request, $product);

            // Se price permanecer 0 e houver variações, sincronizar com o menor preço
            if ((float) $product->price <= 0) {
                $minVar = \App\Models\ProductVariant::where('product_id', $product->id)->min('price');
                if ($minVar !== null) {
                    $product->price = (float) $minVar;
                    $product->save();
                }
            }

            // Upload de novas imagens adicionais
            if ($request->hasFile('images')) {
                $existingCount = $product->images()->count();
                foreach ($request->file('images') as $index => $image) {
                    $ext = $image->getClientOriginalExtension();
                    $base = Str::slug($product->name ?: 'produto');
                    $filename = $base . '-' . $product->id . '-' . ($existingCount + $index + 1) . '-' . time() . '.' . $ext;
                    $path = $image->storeAs('uploads/products', $filename, 'public');
                    ProductImage::create([
                        'product_id' => $product->id,
                        'path' => $path,
                        'is_primary' => false,
                        'sort_order' => $existingCount + $index,
                    ]);

                    // Otimizar imagem: gerar WebP e thumbnails
                    try {
                        ImageOptimizer::optimize($path);
                    } catch (\Exception $e) {
                        Log::warning('Erro ao otimizar imagem adicional (update)', [
                            'path' => $path,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }

            // Sincronizar alérgenos (sempre a partir do array, mesmo que vazio)
            $ids = array_map('intval', (array) $request->input('allergen_ids', []));
            Log::info('Products.update:allergens:received', ['product_id' => $product->id, 'ids' => $ids]);
            $validIds = \App\Models\Allergen::whereIn('id', $ids)->pluck('id')->all();
            $product->allergens()->sync($ids);
            $savedCount = \DB::table('product_allergen')->where('product_id', $product->id)->count();
            Log::info('Products.update:allergens:synced', ['product_id' => $product->id, 'saved_count' => $savedCount, 'valid_ids' => $validIds]);

            DB::commit();

            // Recarregar o produto com relacionamentos
            $product->load(['category', 'allergens', 'images']);

            return redirect()
                ->route('dashboard.products.show', $product)
                ->with('success', 'Produto atualizado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Erro ao atualizar produto: ' . $e->getMessage());
        }
    }

    /**
     * Cria/atualiza/exclui variantes conforme o formulário
     */
    private function syncVariants(Request $request, Product $product): void
    {
        $names = (array) $request->input('variant_name', []);
        $prices = (array) $request->input('variant_price', []);
        $weights = (array) $request->input('variant_weight', []);
        $skus = (array) $request->input('variant_sku', []);
        $actives = (array) $request->input('variant_active', []); // array de indices marcados "on"
        $orders = (array) $request->input('variant_sort', []);
        $ids = (array) $request->input('variant_id', []);

        $keptIds = [];
        foreach ($names as $idx => $name) {
            $name = trim((string) $name);
            if ($name === '')
                continue;
            // Se o checkbox está presente no array, está marcado (true), caso contrário false
            $isActive = isset($actives[$idx]) && $actives[$idx] !== null && $actives[$idx] !== '0' && $actives[$idx] !== '';
            $data = [
                'product_id' => $product->id,
                'name' => $name,
                'price' => (float) ($prices[$idx] ?? 0),
                'weight_grams' => ($wg = (int) ($weights[$idx] ?? 0)) > 0 ? $wg : null,
                'sku' => trim((string) ($skus[$idx] ?? '')) ?: null,
                'is_active' => $isActive,
                'sort_order' => (int) ($orders[$idx] ?? 0),
            ];
            $rowId = (int) ($ids[$idx] ?? 0);
            if ($rowId > 0) {
                $variant = ProductVariant::where('product_id', $product->id)->where('id', $rowId)->first();
                if ($variant) {
                    $variant->update($data);
                    $keptIds[] = $variant->id;
                }
            } else {
                $variant = ProductVariant::create($data);
                $keptIds[] = $variant->id;
            }
        }
        // excluir variantes removidas
        if (!empty($keptIds)) {
            ProductVariant::where('product_id', $product->id)->whereNotIn('id', $keptIds)->delete();
        } else {
            // se nenhum enviado, remove todas
            ProductVariant::where('product_id', $product->id)->delete();
        }
    }

    public function duplicate(Product $product)
    {
        try {
            DB::beginTransaction();

            // Copiar dados básicos do produto
            $newProductData = $product->toArray();

            // Remover campos que não devem ser copiados
            unset($newProductData['id']);
            unset($newProductData['created_at']);
            unset($newProductData['updated_at']);

            // Modificar nome para indicar que é cópia (remover sufixo " (Cópia)" se existir)
            $baseName = preg_replace('/\s*\(Cópia\)\s*$/', '', $product->name);
            $newProductData['name'] = $baseName . ' (Cópia)';

            // Criar SKU único se houver
            if (!empty($newProductData['sku'])) {
                $newProductData['sku'] = $newProductData['sku'] . '-COPY-' . time();
            }

            // Criar novo produto
            $newProduct = Product::create($newProductData);

            // Copiar alérgenos
            if ($product->allergens && $product->allergens->count() > 0) {
                $allergenIds = $product->allergens->pluck('id')->toArray();
                $newProduct->allergens()->attach($allergenIds);
            }

            // Copiar variantes
            if ($product->variants && $product->variants->count() > 0) {
                foreach ($product->variants as $variant) {
                    $variantData = $variant->toArray();
                    unset($variantData['id']);
                    unset($variantData['product_id']);
                    unset($variantData['created_at']);
                    unset($variantData['updated_at']);

                    // Modificar SKU da variante se houver
                    if (!empty($variantData['sku'])) {
                        $variantData['sku'] = $variantData['sku'] . '-COPY-' . time();
                    }

                    $variantData['product_id'] = $newProduct->id;
                    ProductVariant::create($variantData);
                }
            }

            // Copiar imagem de capa
            if ($product->cover_image && Storage::disk('public')->exists($product->cover_image)) {
                $extension = pathinfo($product->cover_image, PATHINFO_EXTENSION);
                $originalPath = pathinfo($product->cover_image, PATHINFO_DIRNAME) . '/' . pathinfo($product->cover_image, PATHINFO_FILENAME);
                $newFilename = Str::slug($newProduct->name) . '-cover-' . time() . '.' . $extension;
                $newPath = 'uploads/products/' . $newFilename;

                // Copiar arquivo
                Storage::disk('public')->copy($product->cover_image, $newPath);
                $newProduct->update(['cover_image' => $newPath]);
            }

            // Copiar imagens adicionais
            if ($product->images && $product->images->count() > 0) {
                foreach ($product->images as $image) {
                    if (Storage::disk('public')->exists($image->path)) {
                        $extension = pathinfo($image->path, PATHINFO_EXTENSION);
                        $newFilename = Str::slug($newProduct->name) . '-' . $newProduct->id . '-' . time() . '-' . uniqid() . '.' . $extension;
                        $newPath = 'uploads/products/' . $newFilename;

                        // Copiar arquivo
                        Storage::disk('public')->copy($image->path, $newPath);

                        // Criar registro da imagem
                        ProductImage::create([
                            'product_id' => $newProduct->id,
                            'path' => $newPath,
                            'is_primary' => $image->is_primary,
                            'sort_order' => $image->sort_order,
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()
                ->route('dashboard.products.edit', $newProduct)
                ->with('success', 'Produto duplicado com sucesso! Você pode editar as informações agora.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao duplicar produto', [
                'product_id' => $product->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()
                ->route('dashboard.products.show', $product)
                ->with('error', 'Erro ao duplicar produto: ' . $e->getMessage());
        }
    }

    public function destroy(Product $product)
    {
        try {
            DB::beginTransaction();

            // Deletar imagens do storage
            if ($product->cover_image && Storage::disk('public')->exists($product->cover_image)) {
                Storage::disk('public')->delete($product->cover_image);
            }

            foreach ($product->images as $image) {
                if (Storage::disk('public')->exists($image->path)) {
                    Storage::disk('public')->delete($image->path);
                }
            }

            $product->delete();

            DB::commit();

            return redirect()
                ->route('dashboard.products.index')
                ->with('success', 'Produto removido com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with('error', 'Erro ao remover produto: ' . $e->getMessage());
        }
    }

    // Métodos auxiliares para gerenciar variantes via AJAX
    public function destroyVariant(Product $product, ProductVariant $variant)
    {
        try {
            // Verificar se a variante pertence ao produto
            if ($variant->product_id !== $product->id) {
                return response()->json(['success' => false, 'message' => 'Variante não pertence a este produto'], 403);
            }

            $variant->delete();

            return response()->json([
                'success' => true,
                'message' => 'Variante excluída com sucesso'
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao excluir variante', [
                'product_id' => $product->id,
                'variant_id' => $variant->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir variante: ' . $e->getMessage()
            ], 500);
        }
    }

    // Métodos auxiliares para gerenciar imagens via AJAX
    public function deleteImage(Product $product, ProductImage $image)
    {
        try {
            if ($image->product_id !== $product->id) {
                return response()->json(['success' => false, 'message' => 'Imagem não pertence a este produto'], 403);
            }

            if (Storage::disk('public')->exists($image->path)) {
                Storage::disk('public')->delete($image->path);
            }

            $image->delete();

            return response()->json(['success' => true, 'message' => 'Imagem removida com sucesso']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro ao remover imagem'], 500);
        }
    }

    public function setPrimaryImage(Product $product, ProductImage $image)
    {
        try {
            if ($image->product_id !== $product->id) {
                return response()->json(['success' => false, 'message' => 'Imagem não pertence a este produto'], 403);
            }

            // Remover primary de todas
            $product->images()->update(['is_primary' => false]);

            // Definir como primary
            $image->update(['is_primary' => true]);

            return response()->json(['success' => true, 'message' => 'Imagem definida como principal']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro ao definir imagem principal'], 500);
        }
    }

    public function reorderImages(Request $request, Product $product)
    {
        $request->validate([
            'image_ids' => 'required|array',
            'image_ids.*' => 'exists:product_images,id',
        ]);

        try {
            foreach ($request->image_ids as $index => $imageId) {
                ProductImage::where('id', $imageId)
                    ->where('product_id', $product->id)
                    ->update(['sort_order' => $index]);
            }

            return response()->json(['success' => true, 'message' => 'Ordem das imagens atualizada']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro ao reordenar imagens'], 500);
        }
    }

    /**
     * Gera textos de SEO via IA
     */
    public function generateSEO(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|integer|exists:categories,id',
            'price' => 'nullable|numeric|min:0',
            'ingredients' => 'nullable|string',
        ]);

        try {
            $ai = new OpenAIService();
            if (!$ai->isConfigured()) {
                return response()->json([
                    'success' => false,
                    'message' => 'IA não configurada. Configure a chave da API OpenAI nas configurações.'
                ], 400);
            }

            $categoryName = null;
            if ($request->category_id) {
                $category = Category::find($request->category_id);
                $categoryName = $category ? $category->name : null;
            }

            $ingredients = [];
            if ($request->ingredients) {
                $ingredients = array_filter(array_map('trim', preg_split('/[,\n]+/', $request->ingredients)));
            }

            $result = $ai->generateSEO(
                $request->name,
                $request->description ?? '',
                $categoryName,
                $request->price ? (float) $request->price : null,
                $ingredients
            );

            if ($result['seo_title'] || $result['seo_description']) {
                return response()->json([
                    'success' => true,
                    'seo_title' => $result['seo_title'],
                    'seo_description' => $result['seo_description'],
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Não foi possível gerar os textos de SEO. Tente novamente.'
            ], 500);

        } catch (\Exception $e) {
            Log::error('Erro ao gerar SEO via IA', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar SEO: ' . $e->getMessage()
            ], 500);
        }
    }
}
