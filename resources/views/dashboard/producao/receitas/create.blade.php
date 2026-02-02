@extends('dashboard.layouts.app')

@section('page_title', 'Nova Receita')
@section('page_subtitle', 'Criar uma nova receita')

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

        <form action="{{ route('dashboard.producao.receitas.store') }}" method="POST" id="recipe-form" class="space-y-6"
            x-data="recipeSteps()">
            @csrf

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
                                    data-category="{{ $product->category->name ?? '' }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
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
                    <div>
                        <x-ui.searchable-select 
                            name="product_id" 
                            label="Produto" 
                            required="true"
                            id="product-select"
                            :options="$products->map(function($p) {
                                $vars = $p->getRelation('variants');
                                if (!$vars || is_string($vars) || $vars->isEmpty()) {
                                    $raw = $p->getRawOriginal('variants');
                                    $vars = !empty($raw) ? json_decode($raw, true) : [];
                                }
                                return [
                                    'id' => $p->id, 
                                    'name' => $p->name . ($p->category ? ' - ' . $p->category->name : ''),
                                    'category' => $p->category->name ?? '',
                                    'variants' => $vars // Pass variants directly in item
                                ];
                            })->values()"
                            wire:model="product_id"
                            @change="updateVariants($event.detail.value)"
                        />
                    </div>
                    <div>
                        <x-ui.searchable-select 
                            name="variant_id" 
                            label="Variante" 
                            required="true"
                            id="variant-select"
                            alpine-options="availableVariants"
                            wire:model="variant_id"
                            @change="updateRecipeNameAndWeight()"
                        />
                    </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">Nome da Receita *</label>
                        <input type="text" name="name" id="recipe-name" value="{{ old('name') }}" required
                            class="form-input" placeholder="Ex: Pão italiano">
                        <p class="text-xs text-muted-foreground mt-1">Sugerido: Nome do Produto - Variante</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">Categoria</label>
                        <input type="text" name="category" id="category-input" value="{{ old('category') }}"
                            list="categories-list" class="form-input" placeholder="Ex: Pães Rústicos">
                        <datalist id="categories-list">
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}">
                            @endforeach
                        </datalist>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">Peso Total (g) *</label>
                        <input type="number" name="total_weight" id="total-weight-input" :value="totalWeight()" step="0.01"
                            min="0" readonly class="form-input bg-muted/50 font-mono">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">Rendimento (Porções)</label>
                        <input type="text" id="portions-input" :value="calculatePortions()" readonly
                            class="form-input bg-muted/50 font-mono">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Observações</label>
                    <textarea name="notes" rows="3" class="form-input">{{ old('notes') }}</textarea>
                </div>

                <div class="flex flex-wrap gap-4">
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
                        <input type="checkbox" name="include_notes_in_print" value="1" x-model="include_notes_in_print"
                            class="rounded">
                        <span class="text-sm">Incluir observações na impressão</span>
                    </label>
                    <label class="flex items-center gap-2" x-show="is_bread">
                        <input type="checkbox" name="uses_baker_percentage" value="1" x-model="uses_baker_percentage"
                            class="rounded">
                        <span class="text-sm">Usar porcentagem de padeiro</span>
                    </label>
                </div>
            </div>

            <div class="bg-card rounded-xl border border-border p-6 space-y-6" x-show="is_bread">
                <h2 class="text-xl font-semibold">Parâmetros de Panificação</h2>
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium mb-2">Hidratação (%)</label>
                        <input type="number" name="hydration" x-model.number="hydration" step="0.01" min="0" max="200"
                            class="form-input">
                        <p class="text-xs text-muted-foreground mt-1">% sobre a farinha</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">Levain (%)</label>
                        <input type="number" name="levain" x-model.number="levain" step="0.01" min="0" max="100"
                            class="form-input">
                        <p class="text-xs text-muted-foreground mt-1">% sobre a farinha</p>
                    </div>
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
                                <input type="text" x-model="step.name" :name="'steps[' + stepIndex + '][name]'"
                                    placeholder="Nome da etapa" required class="form-input flex-1 max-w-xs">
                                <button type="button" @click="removeStep(stepIndex)" class="btn-outline text-destructive"
                                    x-show="steps.length > 1">
                                    <i data-lucide="trash-2" class="h-4 w-4"></i>
                                </button>
                            </div>

                            <div class="space-y-2" x-ref="ingredientsContainer">
                                <template x-for="(ing, ingIndex) in step.ingredients" :key="ingIndex">
                                    <div class="flex gap-2 items-end">
                                        <div class="flex-1">
                                            <label class="block text-xs font-medium mb-1">Ingrediente</label>
                                            <x-ui.searchable-select 
                                                name="'steps[' + stepIndex + '][ingredients][' + ingIndex + '][ingredient_id]'" 
                                                placeholder="Selecione..."
                                                :options="$ingredients->map(fn($i) => ['id' => $i->id, 'name' => $i->name])->values()"
                                                x-model="ing.ingredient_id"
                                            />
                                        </div>
                                        <div class="w-24">
                                            <label class="block text-xs font-medium mb-1 text-center">%</label>
                                            <input type="number" x-model="ing.percentage"
                                                :name="'steps[' + stepIndex + '][ingredients][' + ingIndex + '][percentage]'"
                                                step="0.01" min="0" placeholder="%" class="form-input text-center">
                                        </div>
                                        <div class="w-28">
                                            <label class="block text-xs font-medium mb-1 text-center">Peso (g)</label>
                                            <input type="number" x-model.number="ing.weight"
                                                :name="'steps[' + stepIndex + '][ingredients][' + ingIndex + '][weight]'"
                                                step="0.01" min="0" placeholder="g" class="form-input text-center">
                                        </div>
                                        <button type="button" @click="removeIngredient(stepIndex, ingIndex)"
                                            class="btn-outline text-destructive h-10" x-show="step.ingredients.length > 1">
                                            <i data-lucide="x" class="h-4 w-4"></i>
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
                    Salvar Receita
                </button>
                <a href="{{ route('dashboard.producao.receitas.index') }}" class="btn-outline">
                    Cancelar
                </a>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            function recipeSteps() {
                return {
                    is_active: true,
                    is_fermented: true,
                    is_bread: true,
                    include_notes_in_print: false,
                    uses_baker_percentage: true,
                    hydration: 70,
                    levain: 30,
                    steps: [{
                        name: 'Etapa 1',
                        ingredients: [{
                            ingredient_id: '',
                            percentage: null,
                            weight: null
                        }]
                    }],
                    variant_weight: 0,

                    addStep() {
                        this.steps.push({
                            name: 'Etapa ' + (this.steps.length + 1),
                            ingredients: [{
                                ingredient_id: '',
                                percentage: null,
                                weight: null
                            }]
                        });
                    },

                    removeStep(index) {
                        if (this.steps.length > 1) {
                            this.steps.splice(index, 1);
                        }
                    },

                    addIngredient(stepIndex) {
                        this.steps[stepIndex].ingredients.push({
                            ingredient_id: '',
                            percentage: null,
                            weight: null
                        });
                    },

                    removeIngredient(stepIndex, ingIndex) {
                        if (this.steps[stepIndex].ingredients.length > 1) {
                            this.steps[stepIndex].ingredients.splice(ingIndex, 1);
                        }
                    },

                    totalWeight() {
                        let total = 0;
                        this.steps.forEach(step => {
                            if (step.ingredients) {
                                step.ingredients.forEach(ing => {
                                    total += parseFloat(ing.weight || 0);
                                });
                            }
                        });
                        return total > 0 ? total.toFixed(2) : "0.00";
                    },

                    calculatePortions() {
                        const total = parseFloat(this.totalWeight());
                        const vWeight = parseFloat(this.variant_weight || 0);
                        if (vWeight > 0 && total > 0) {
                            return (total / vWeight).toFixed(2);
                        }
                        return 0;
                    }
                    // New Alpine Logic for Searchable Selects
                    product_id: '',
                    variant_id: '',
                    products: @json($products->map(function($p) {
                         $vars = $p->getRelation('variants');
                         if (!$vars || is_string($vars) || $vars->isEmpty()) {
                             $raw = $p->getRawOriginal('variants');
                             $vars = !empty($raw) ? json_decode($raw, true) : [];
                         }
                         // Normalize dynamic variants
                         if(is_array($vars)){
                            $vars = array_map(function($v) {
                                // Ensure standard structure if it's an array
                                return (object)$v;
                            }, $vars);
                         }
                         return [
                             'id' => $p->id, 
                             'name' => $p->name . ($p->category ? ' - ' . $p->category->name : ''),
                             'category' => $p->category->name ?? '',
                             'variants' => $vars
                         ];
                    })->values()),
                    availableVariants: [],

                    updateVariants(productId) {
                        this.product_id = productId;
                        const product = this.products.find(p => p.id == productId);
                        this.availableVariants = [];
                        this.variant_id = ''; // Reset variant
                        
                        if (product) {
                            // Update Category
                            if(product.category) {
                                document.getElementById('category-input').value = product.category;
                            }
                            
                            // Detect Bread
                            this.is_bread = product.name.toLowerCase().includes('pão') || 
                                           (product.category && product.category.toLowerCase().includes('pão'));

                            // Populate Variants
                            // Handle both Eloquent Collection (array of objects) and array arrays
                            if (product.variants) {
                                this.availableVariants = Object.values(product.variants).map(v => ({
                                    id: v.id || v.name, // Fallback for legacy
                                    name: (v.name || 'Padrão') + (v.price ? ' (R$ ' + v.price + ')' : ''),
                                    weight: v.weight_grams || 0,
                                    price: v.price
                                }));
                            }
                        }
                    },

                    updateRecipeNameAndWeight() {
                        if(!this.product_id || !this.variant_id) return;

                        const product = this.products.find(p => p.id == this.product_id);
                        const variant = this.availableVariants.find(v => v.id == this.variant_id);

                        if (product && variant) {
                            // Update Weight
                            this.variant_weight = parseFloat(variant.weight || 0);

                            // Update Name
                            const rawVariantName = variant.name.split('(')[0].trim();
                            const prodNameOnly = product.name.split(' - ')[0].trim();
                            document.getElementById('recipe-name').value = prodNameOnly + ' - ' + rawVariantName;
                        }
                    }
            }

            document.addEventListener('DOMContentLoaded', function () {
                if (window.lucide) {
                    window.lucide.createIcons();
                }

                // Removed legacy Vanilla JS listeners. Logic moved to Alpine component 'recipeSteps'.
            });
        </script>
    @endpush
@endsection