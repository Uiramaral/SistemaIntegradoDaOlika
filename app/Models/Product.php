<?php

namespace App\Models;

use App\Helpers\ImageOptimizer;
use App\Models\Traits\BelongsToClient;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasFactory, BelongsToClient;

    protected $fillable = [
        'client_id',
        'category_id',
        'name',
        'sku',
        'price',
        'stock',
        'is_active',
        'show_in_catalog',
        'gluten_free',
        'contamination_risk',
        'cover_image',
        'description',
        'label_description',
        'seo_title',
        'seo_description',
        'image_url',
        'is_featured',
        'is_available',
        'preparation_time',
        'nutritional_info',
        'sort_order',
        'weight_grams',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_available' => 'boolean',
        'is_active' => 'boolean',
        'show_in_catalog' => 'boolean',
        'gluten_free' => 'boolean',
        'contamination_risk' => 'boolean',
        'nutritional_info' => 'array',
        'price' => 'decimal:2',
        'weight_grams' => 'integer',
    ];

    /**
     * Relacionamento com categoria
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Relacionamento com imagens
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    /**
     * Variações (ex.: 500g, 1kg, Chocolate, Frutas)
     */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('sort_order');
    }

    /**
     * Preços de revenda
     */
    public function wholesalePrices(): HasMany
    {
        return $this->hasMany(ProductWholesalePrice::class)->orderBy('min_quantity');
    }

    /**
     * Relacionamento com alérgenos
     */
    public function allergens()
    {
        return $this->belongsToMany(Allergen::class, 'product_allergen')->withTimestamps();
    }

    /**
     * Relacionamento com itens do pedido
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Scope para produtos ativos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para produtos disponíveis
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    /**
     * Scope para produtos que aparecem no catálogo público
     */
    public function scopeShowInCatalog($query)
    {
        return $query->where('show_in_catalog', true);
    }

    /**
     * Scope para produtos compráveis (preço base > 0 ou possui variação ativa com preço > 0)
     */
    public function scopePurchasable($query)
    {
        return $query->where(function($q){
            $q->where('price', '>', 0)
              ->orWhereExists(function($sq){
                  $sq->selectRaw('1')
                      ->from('product_variants as pv')
                      ->whereColumn('pv.product_id', 'products.id')
                      ->where('pv.is_active', true)
                      ->where('pv.price', '>', 0);
              });
        });
    }

    /**
     * Scope para produtos em destaque
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope para ordenação
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Accessor para URL da imagem
     */
    public function getImageUrlAttribute($value)
    {
        if ($value && !str_starts_with($value, 'http')) {
            return asset('storage/' . $value);
        }
        
        return $value;
    }
    
    /**
     * Obtém a URL da imagem principal do produto
     */
    public function getMainImagePath()
    {
        // Prioridade: cover_image > primeira imagem da galeria > image_url
        if ($this->cover_image) {
            return $this->cover_image;
        }
        
        $firstImage = $this->images()->first();
        if ($firstImage) {
            return $firstImage->path;
        }
        
        if ($this->image_url) {
            // Se image_url é uma URL completa, retornar null (não é path local)
            if (str_starts_with($this->image_url, 'http')) {
                return null;
            }
            return str_replace(asset('storage/'), '', $this->image_url);
        }
        
        return null;
    }
    
    /**
     * Obtém URLs otimizadas para a imagem (WebP e fallback)
     */
    public function getOptimizedImageUrls($size = 'thumb')
    {
        $originalPath = $this->getMainImagePath();
        
        if (!$originalPath) {
            $placeholder = asset('images/produto-placeholder.jpg');
            return [
                'original' => $placeholder,
                'webp' => $placeholder,
                'jpg' => $placeholder,
            ];
        }
        
        $disk = Storage::disk('public');
        $pathInfo = pathinfo($originalPath);
        $directory = $pathInfo['dirname'];
        $filename = $pathInfo['filename'];
        
        $urls = [
            'original' => asset('storage/' . $originalPath),
            'webp' => null,
            'jpg' => null,
        ];
        
        // URLs de thumbnail WebP e JPG
        if ($size) {
            $webpThumb = $directory . '/thumbs/' . $filename . '-' . $size . '.webp';
            $jpgThumb = $directory . '/thumbs/' . $filename . '-' . $size . '.jpg';
            
            if ($disk->exists($webpThumb)) {
                $urls['webp'] = asset('storage/' . $webpThumb);
            }
            if ($disk->exists($jpgThumb)) {
                $urls['jpg'] = asset('storage/' . $jpgThumb);
            }
        }
        
        // Fallback para WebP original se não houver thumbnail
        if (!$urls['webp']) {
            $webpPath = $directory . '/' . $filename . '.webp';
            if ($disk->exists($webpPath)) {
                $urls['webp'] = asset('storage/' . $webpPath);
            }
        }
        
        // Se não houver WebP, usar original como fallback
        if (!$urls['webp']) {
            $urls['webp'] = $urls['original'];
        }
        if (!$urls['jpg']) {
            $urls['jpg'] = $urls['original'];
        }
        
        return $urls;
    }

    /**
     * Accessor para texto de alérgenos
     */
    public function getAllergenTextAttribute()
    {
        $parts = [];
        
        // Verificar se o relacionamento está carregado e tem dados
        if ($this->relationLoaded('allergens') && $this->allergens) {
            $names = $this->allergens->pluck('name')->filter()->values()->all();
            if (!empty($names)) {
                $parts[] = 'Contém: '.implode(', ', $names).'.';
            }
        } elseif (!$this->relationLoaded('allergens')) {
            // Se não estiver carregado, tentar carregar
            $names = $this->allergens()->pluck('name')->filter()->values()->all();
            if (!empty($names)) {
                $parts[] = 'Contém: '.implode(', ', $names).'.';
            }
        }

        if ($this->gluten_free) {
            $parts[] = 'Produto sem glúten.';
        }

        if ($this->contamination_risk) {
            $parts[] = '⚠️ Pode conter traços de glúten devido ao ambiente de produção.';
        }

        return count($parts) ? implode(' ', $parts) : null;
    }

    /**
     * Gera uma descrição padrão usando campos do produto + alérgenos marcados.
     * Não salva — apenas retorna a string.
     */
    public function generateDefaultDescription(?array $override = null): string
    {
        // valores atuais do produto (ou overrides vindos do form)
        $name   = $override['name']   ?? $this->name;
        $price  = $override['price']  ?? $this->price;
        $cat    = ($override['category'] ?? null) ? ($override['category']->name ?? null) : optional($this->category)->name;
        $gf     = array_key_exists('gluten_free', $override ?? []) ? (bool)$override['gluten_free'] : (bool)$this->gluten_free;
        $risk   = array_key_exists('contamination_risk', $override ?? []) ? (bool)$override['contamination_risk'] : (bool)$this->contamination_risk;

        // nomes dos alérgenos (override -> current)
        $allergenNames = [];
        if (isset($override['allergen_names'])) {
            $allergenNames = $override['allergen_names'];
        } elseif ($this->relationLoaded('allergens') && $this->allergens) {
            $allergenNames = $this->allergens->pluck('name')->values()->all();
        } elseif (!$this->relationLoaded('allergens')) {
            $allergenNames = $this->allergens()->pluck('name')->values()->all();
        }

        $lines = [];

        // linha 1 — nome + categoria
        $headline = $name;
        if ($cat) $headline .= " — {$cat}";
        $lines[] = $headline;

        // linha 2 — preço (opcional)
        if (is_numeric($price)) {
            $lines[] = 'Preço de referência: R$ '.number_format((float)$price, 2, ',', '.');
        }

        // linha 3 — flags
        if ($gf) {
            $lines[] = 'Produto sem glúten.';
        }

        // linha 4 — alérgenos
        if (!empty($allergenNames)) {
            $lines[] = 'Contém: '.implode(', ', $allergenNames).'.';
        }

        // linha 5 — contaminação
        if ($risk) {
            $lines[] = '⚠️ Pode conter traços de glúten devido ao ambiente de produção.';
        }

        return implode(' ', $lines);
    }

    /**
     * Texto curto de rótulo (sem preço) — pronto para exibir
     */
    public function getLabelTextAttribute(): ?string
    {
        // se houver texto salvo, prioriza
        if (trim((string)$this->label_description) !== '') {
            return $this->label_description;
        }
        // senão, gera na hora
        return $this->generateLabelText();
    }

    /**
     * Gera descrição de rótulo (sem preço), sem salvar
     */
    public function generateLabelText(?array $override = null): string
    {
        $name = $override['name'] ?? $this->name;
        $cat  = ($override['category'] ?? null) ? ($override['category']->name ?? null) : optional($this->category)->name;
        $gf   = array_key_exists('gluten_free', $override ?? []) ? (bool)$override['gluten_free'] : (bool)$this->gluten_free;
        $risk = array_key_exists('contamination_risk', $override ?? []) ? (bool)$override['contamination_risk'] : (bool)$this->contamination_risk;

        $allergenNames = [];
        if (isset($override['allergen_names'])) {
            $allergenNames = $override['allergen_names'];
        } elseif ($this->relationLoaded('allergens') && $this->allergens) {
            $allergenNames = $this->allergens->pluck('name')->values()->all();
        } elseif (!$this->relationLoaded('allergens')) {
            $allergenNames = $this->allergens()->pluck('name')->values()->all();
        }

        $parts = [];

        // linha 1 — nome + categoria (curto)
        $headline = $name;
        if ($cat) $headline .= " — {$cat}";
        $parts[] = $headline;

        // sem preço aqui

        if ($gf) $parts[] = 'Produto sem glúten.';
        if (!empty($allergenNames)) $parts[] = 'Contém: '.implode(', ', $allergenNames).'.';
        if ($risk) $parts[] = '⚠️ Pode conter traços de glúten devido ao ambiente de produção.';

        return implode(' ', $parts);
    }
}
