@extends('dashboard.layouts.app')

@section('page_title', 'Editar Receita')
@section('page_subtitle', 'Editar receita')

@section('content')
<div class="space-y-6">
    @if($errors->any())
        <div class="rounded-lg border bg-red-50 text-red-900 px-4 py-3">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

@php
    $initialVariantWeight = 0;
    if ($recipe->product && $recipe->variant_id) {
        $productVariants = $recipe->product->variants;
        
        // Handle string (legacy JSON), array, or Collection
        if (is_string($productVariants)) {
            $productVariants = json_decode($productVariants, true) ?: [];
        }
        
        if (is_array($productVariants)) {
            // Find variant in array
            foreach ($productVariants as $v) {
                $vId = is_array($v) ? ($v['id'] ?? null) : (is_object($v) ? ($v->id ?? null) : null);
                if ($vId == $recipe->variant_id) {
                    $initialVariantWeight = is_array($v) ? ($v['weight_grams'] ?? $v['weight'] ?? 0) : ($v->weight_grams ?? $v->weight ?? 0);
                    break;
                }
            }
        } elseif (is_object($productVariants) && method_exists($productVariants, 'where')) {
            // It's a Collection
            $variant = $productVariants->where('id', $recipe->variant_id)->first();
            if ($variant) {
                $initialVariantWeight = $variant->weight_grams ?? $variant->weight ?? 0;
            }
        }
    }
@endphp

    @php
        $recipeFormInitial = [
            'total_weight' => (float) old('total_weight', $recipe->total_weight ?? 0),
            'hydration' => (float) old('hydration', $recipe->hydration ?? 70),
            'levain' => (float) old('levain', $recipe->levain ?? 30),
            'is_active' => (bool) old('is_active', $recipe->is_active ?? true),
            'is_fermented' => (bool) old('is_fermented', $recipe->is_fermented ?? true),
            'is_bread' => (bool) old('is_bread', $recipe->is_bread ?? true),
            'include_notes_in_print' => (bool) old('include_notes_in_print', $recipe->include_notes_in_print ?? false),
            'uses_baker_percentage' => (bool) old('uses_baker_percentage', $recipe->uses_baker_percentage ?? true),
            'variant_weight' => (float) $initialVariantWeight,
            'steps' => $recipe->steps->map(function($step) {
                return [
                    'name' => $step->name,
                    'ingredients' => $step->ingredients->map(function($ri) {
                        return [
                            'ingredient_id' => $ri->ingredient_id,
                            'percentage' => $ri->percentage !== null ? (float) $ri->percentage : null,
                            'weight' => $ri->weight !== null ? (float) $ri->weight : null,
                        ];
                    })->toArray()
                ];
            })->toArray(),
        ];
    @endphp
    <form action="{{ route('dashboard.producao.receitas.update', $recipe) }}" method="POST" id="recipe-form" class="space-y-6"
          x-data="recipeForm({{ json_encode($recipeFormInitial) }})"
          @submit="syncWeightsBeforeSubmit()">
        @csrf
        @method('PUT')
        
        <div class="bg-card rounded-xl border border-border p-6 space-y-6">
            <h2 class="text-xl font-semibold">Informações Básicas</h2>
            
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium mb-2">Produto *</label>
                    <select name="product_id" id="product-select" required class="form-input">
                        <option value="">Selecione um produto...</option>
                        @foreach ($products as $product)
                            @php
                                $vars = $product->getRelation('variants');
                                $variantsData = ($vars && !is_string($vars) && method_exists($vars, 'isNotEmpty') && $vars->isNotEmpty()) ? $vars : [];
                                
                                if (empty($variantsData) || (is_countable($variantsData) && count($variantsData) === 0)) {
                                    $rawVariants = $product->getRawOriginal('variants');
                                    if (!empty($rawVariants)) {
                                        $decoded = json_decode($rawVariants, true);
                                        if (is_array($decoded)) {
                                            $variantsData = $decoded;
                                        }
                                    }
                                }
                            @endphp
                            <option value="{{ $product->id }}" data-name="{{ $product->name }}"
                                data-variants="{{ json_encode($variantsData) }}"
                                data-category="{{ $product->category->name ?? '' }}"
                                {{ old('product_id', $recipe->product_id) == $product->id ? 'selected' : '' }}>
                                {{ $product->name }}
                                @if ($product->category)
                                    - {{ $product->category->name }}
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Variante *</label>
                    <select name="variant_id" id="variant-select" required class="form-input">
                        <option value="">Selecione um produto...</option>
                        @if($recipe->product && $recipe->product->variants)
                            @php
                                $vList = $recipe->product->variants;
                                if (is_string($vList)) {
                                    $vList = json_decode($vList, true) ?: [];
                                }
                            @endphp
                            @foreach($vList as $variant)
                                @php
                                    $vWeight = 0;
                                    if (is_object($variant)) {
                                        $vWeight = $variant->weight_grams ?? $variant->weight ?? 0;
                                    } elseif (is_array($variant)) {
                                        $vWeight = $variant['weight_grams'] ?? $variant['weight'] ?? 0;
                                    }
                                @endphp
                                <option value="{{ $variant->id ?? (is_array($variant) ? ($variant['id'] ?? '') : '') }}" 
                                    data-weight="{{ $vWeight }}" 
                                    {{ old('variant_id', $recipe->variant_id) == ($variant->id ?? (is_array($variant) ? ($variant['id'] ?? '') : '')) ? 'selected' : '' }}>
                                    {{ $variant->name ?? (is_array($variant) ? ($variant['name'] ?? '') : '') }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Nome da Receita *</label>
                    <input type="text" name="name" id="recipe-name" value="{{ old('name', $recipe->name) }}" required class="form-input" readonly>
                    <p class="text-xs text-muted-foreground mt-1">Preenchido automaticamente com o nome do produto</p>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Categoria</label>
                    <input type="text" name="category" id="category-input" value="{{ old('category', $recipe->category) }}" list="categories-list" class="form-input">
                    <datalist id="categories-list">
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}">
                        @endforeach
                    </datalist>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Peso Total (g) *</label>
                    <input type="number" name="total_weight" :value="calculateTotalWeight()" step="0.01" min="0" readonly class="form-input bg-muted/50 font-mono">
                    <p class="text-xs text-muted-foreground mt-1">Soma de todos os ingredientes.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Rendimento (Porções)</label>
                    <input type="text" :value="calculatePortions()" readonly class="form-input bg-muted/50 font-mono">
                </div>
            </div>

            <div class="flex flex-wrap gap-4 pt-4 border-t border-border">
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" value="1" x-model="is_active" class="rounded">
                    <span class="text-sm">Receita ativa</span>
                </label>
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="is_fermented" value="1" x-model="is_fermented" class="rounded">
                    <span class="text-sm">É fermentado</span>
                </label>
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="is_bread" value="1" x-model="is_bread" class="rounded">
                    <span class="text-sm">É pão</span>
                </label>
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="include_notes_in_print" value="1" x-model="include_notes_in_print" class="rounded">
                    <span class="text-sm">Incluir observações na impressão</span>
                </label>
                <label class="flex items-center gap-2" x-show="is_bread">
                    <input type="checkbox" name="uses_baker_percentage" value="1" x-model="uses_baker_percentage" class="rounded">
                    <span class="text-sm">Usar porcentagem de padeiro</span>
                </label>
            </div>
        </div>

        <div class="bg-card rounded-xl border border-border p-6 space-y-6" x-show="is_bread">
            <h2 class="text-xl font-semibold">Parâmetros de Panificação</h2>
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium mb-2">Hidratação (%)</label>
                    <input type="number" name="hydration" x-model.number="hydration" step="0.01" min="0" max="200" class="form-input">
                    <p class="text-xs text-muted-foreground mt-1">% sobre a farinha</p>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Levain (%)</label>
                    <input type="number" name="levain" x-model.number="levain" step="0.01" min="0" max="100" class="form-input">
                    <p class="text-xs text-muted-foreground mt-1">% sobre a farinha</p>
                </div>
            </div>
        </div>

        <div class="bg-card rounded-xl border border-border p-6 space-y-6">
            <div>
                <label class="block text-sm font-medium mb-2">Observações</label>
                <textarea name="notes" rows="3" class="form-input">{{ old('notes', $recipe->notes) }}</textarea>
            </div>
        </div>

        <div class="bg-card rounded-xl border border-border p-6 space-y-6">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold">Etapas e Ingredientes</h2>
                <button type="button" @click="addStep()" class="btn-primary gap-2">
                    <i data-lucide="plus" class="h-4 w-4"></i>
                    Adicionar Etapa
                </button>
            </div>

            <div class="space-y-4" x-ref="stepsContainer">
                <template x-for="(step, stepIndex) in steps" :key="stepIndex">
                    <div class="border border-border rounded-lg p-4 space-y-4">
                        <div class="flex items-center justify-between">
                            <input type="text" x-model="step.name" :name="'steps[' + stepIndex + '][name]'" placeholder="Nome da etapa" required class="form-input flex-1 max-w-xs">
                            <button type="button" @click="removeStep(stepIndex)" class="btn-outline text-destructive" x-show="steps.length > 1">
                                <i data-lucide="trash-2" class="h-4 w-4"></i>
                            </button>
                        </div>
                        
                        <div class="space-y-2">
                            
                            <template x-for="(ing, ingIndex) in step.ingredients" :key="ingIndex">
                                <div class="grid grid-cols-1 md:grid-cols-[1fr,100px,120px,40px] gap-4 items-end bg-muted/20 p-4 rounded-lg md:bg-transparent md:p-0">
                                    <div class="space-y-1">
                                        <label class="block text-xs font-medium text-muted-foreground md:hidden">Ingrediente</label>
                                        <select x-model="ing.ingredient_id" 
                                                :name="'steps[' + stepIndex + '][ingredients][' + ingIndex + '][ingredient_id]'" 
                                                required 
                                                class="form-input"
                                                :id="'ingredient-select-' + stepIndex + '-' + ingIndex">
                                            <option value="">Selecione...</option>
                                            @foreach($ingredients as $ingredient)
                                                <option value="{{ $ingredient->id }}" x-bind:selected="ing.ingredient_id == {{ $ingredient->id }}">{{ $ingredient->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="space-y-1">
                                        <label class="block text-xs font-medium text-muted-foreground text-center md:hidden">%</label>
                                        <div class="relative">
                                            <input type="number" x-model="ing.percentage" :name="'steps[' + stepIndex + '][ingredients][' + ingIndex + '][percentage]'" step="0.01" min="0" placeholder="%" class="form-input text-center pr-8">
                                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground text-xs">%</span>
                                        </div>
                                    </div>
                                    <div class="space-y-1">
                                        <label class="block text-xs font-medium text-muted-foreground text-center md:hidden">Peso (g)</label>
                                        <div class="relative">
                                            <input type="number" x-model.number="ing.weight" :name="'steps[' + stepIndex + '][ingredients][' + ingIndex + '][weight]'" step="0.01" min="0" placeholder="g" class="form-input text-center pr-8">
                                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground text-xs">g</span>
                                        </div>
                                    </div>
                                    <button type="button" @click="removeIngredient(stepIndex, ingIndex)" class="btn-outline text-destructive w-full md:w-10 h-10 flex items-center justify-center p-0" x-show="step.ingredients.length > 1">
                                        <i data-lucide="trash-2" class="h-4 w-4"></i>
                                    </button>
                                </div>
                            </template>
                        </div>
                        
                        <button type="button" @click="addIngredient(stepIndex)" class="btn-outline text-sm gap-2">
                            <i data-lucide="plus" class="h-4 w-4"></i>
                            Adicionar Ingrediente
                        </button>
                    </div>
                </template>
            </div>
        </div>

        <div class="flex gap-4">
            <button type="submit" class="btn-primary">
                Atualizar Receita
            </button>
            <a href="{{ route('dashboard.producao.receitas.index') }}" class="btn-outline">
                Cancelar
            </a>
        </div>
    </form>
</div>

@push('scripts')
<script>
function recipeForm(initial) {
    const steps = (initial && initial.steps && initial.steps.length > 0) ? initial.steps : [{
        name: 'Etapa 1',
        ingredients: [{ ingredient_id: '', percentage: null, weight: null }]
    }];
    return {
        total_weight: initial && initial.total_weight != null ? Number(initial.total_weight) : 700,
        hydration: initial && initial.hydration != null ? Number(initial.hydration) : 70,
        levain: initial && initial.levain != null ? Number(initial.levain) : 30,
        is_active: initial && initial.is_active != null ? Boolean(initial.is_active) : true,
        is_fermented: initial && initial.is_fermented != null ? Boolean(initial.is_fermented) : true,
        is_bread: initial && initial.is_bread != null ? Boolean(initial.is_bread) : true,
        include_notes_in_print: initial && initial.include_notes_in_print != null ? Boolean(initial.include_notes_in_print) : false,
        uses_baker_percentage: initial && initial.uses_baker_percentage != null ? Boolean(initial.uses_baker_percentage) : true,
        variant_weight: initial && initial.variant_weight != null ? Number(initial.variant_weight) : 0,
        steps: steps,

        calculateTotalWeight() {
            let total = 0;
            this.steps.forEach(s => {
                s.ingredients.forEach(ing => {
                    total += Number(ing.weight || 0);
                });
            });
            return total > 0 ? total.toFixed(2) : "0.00";
        },

        calculatePortions() {
            const total = parseFloat(this.calculateTotalWeight());
            if (this.variant_weight > 0 && total > 0) {
                return (total / this.variant_weight).toFixed(2);
            }
            return 0;
        },

        syncWeightsBeforeSubmit() {
            // Em "Sum mode", os pesos já estão vinculados via x-model.
        },

        addStep() {
            this.steps.push({
                name: 'Etapa ' + (this.steps.length + 1),
                ingredients: [{ ingredient_id: '', percentage: null, weight: null }]
            });
        },
        removeStep(index) {
            if (this.steps.length > 1) this.steps.splice(index, 1);
        },
        addIngredient(stepIndex) {
            this.steps[stepIndex].ingredients.push({ ingredient_id: '', percentage: null, weight: null });
        },
        removeIngredient(stepIndex, ingIndex) {
            if (this.steps[stepIndex].ingredients.length > 1) {
                this.steps[stepIndex].ingredients.splice(ingIndex, 1);
            }
        }
    };
}

document.addEventListener('DOMContentLoaded', function() {
    if (window.lucide) {
        window.lucide.createIcons();
    }
    
    // Preencher nome da receita automaticamente quando produto for selecionado
    const productSelect = document.getElementById('product-select');
    const variantSelect = document.getElementById('variant-select');
    const recipeNameInput = document.getElementById('recipe-name');
    
    if (productSelect && variantSelect && recipeNameInput) {
        productSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            variantSelect.innerHTML = '<option value="">Selecione a variante...</option>';
                        if (selectedOption && selectedOption.value) {
                    // Category pre-fill
                    const prodCat = selectedOption.getAttribute('data-category');
                    const categoryInput = document.getElementById('category-input');
                    if (prodCat) {
                        categoryInput.value = prodCat;
                    }

                    // Auto-detect if it is bread based on name
                    const prodName = selectedOption.getAttribute('data-name') || '';
                    const alpineEl = document.getElementById('recipe-form');
                    if (alpineEl && alpineEl.__x) {
                        const isBread = prodName.toLowerCase().includes('pão') || (prodCat && prodCat.toLowerCase().includes('pão'));
                        alpineEl.__x.$data.is_bread = isBread;
                    }

                    // Clear name when product changes to force re-selection
                    recipeNameInput.value = '';

                try {
                    const variantsJson = selectedOption.getAttribute('data-variants');
                    const variants = JSON.parse(variantsJson || '[]');
                    
                    variants.forEach(variant => {
                        const option = document.createElement('option');
                        option.value = variant.id || variant.name;
                        option.textContent = variant.name + (variant.price ? ' (R$ ' + variant.price + ')' : '');
                        option.setAttribute('data-weight', variant.weight_grams || 0);
                        
                        // Manter selecionado se for o valor atual
                        if (variant.id == "{{ $recipe->variant_id }}" || (typeof variant.id === 'undefined' && variant.name == "{{ $recipe->variant_id }}")) {
                            option.selected = true;
                        }

                        variantSelect.appendChild(option);
                    });
                    
                    updateRecipeName();
                    updateVariantWeight();
                } catch (e) {
                    console.error('Error parsing variants:', e);
                }
            } else {
                recipeNameInput.value = '';
            }
        });

        variantSelect.addEventListener('change', () => {
            updateRecipeName();
            updateVariantWeight();
        });

        function updateVariantWeight() {
            const selected = variantSelect.options[variantSelect.selectedIndex];
            const weight = selected ? parseFloat(selected.getAttribute('data-weight') || 0) : 0;
            
            const alpineEl = document.getElementById('recipe-form');
            if (alpineEl && alpineEl.__x) {
                alpineEl.__x.$data.variant_weight = weight;
            } else {
                window._initial_variant_weight = weight;
            }
        }

        function updateRecipeName() {
            const selectedProduct = productSelect.options[productSelect.selectedIndex];
            const selectedVariant = variantSelect.options[variantSelect.selectedIndex];

            if (selectedProduct && selectedProduct.value && selectedVariant && selectedVariant.value) {
                const productName = selectedProduct.getAttribute('data-name');
                const variantName = selectedVariant.textContent.split('(')[0].trim();
                recipeNameInput.value = productName + ' - ' + variantName;
            }
        }

        // REMOVIDO: dispatchEvent('change') automático que causava troca de variante ao carregar
        // if (productSelect.value) {
        //     productSelect.dispatchEvent(new Event('change'));
        // }
    }
});
</script>
@endpush
@endsection
