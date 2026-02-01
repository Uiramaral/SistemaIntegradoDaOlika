@extends('dashboard.layouts.app')

@section('page_title', 'Receitas')
@section('page_subtitle', 'Gerenciamento de receitas')

@section('page_actions')
    <div class="flex gap-2 text-white">
        <button type="button" @click="$dispatch('open-print-queue')"
            class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-purple-600 text-white hover:bg-purple-700 h-10 px-4 py-2 gap-2">
            <i data-lucide="printer" class="h-4 w-4"></i>
            Fila de Impressão
        </button>
        <button type="button" @click="$dispatch('open-recipe-modal')"
            class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2 gap-2">
            <i data-lucide="plus" class="h-4 w-4"></i>
            Nova Receita
        </button>
    </div>
@endsection

@section('content')
    <script>
        window.allProducts = @json($productsData);
        console.log('Produtos carregados (via productsData):', window.allProducts);
    </script>
    <div class="bg-card rounded-xl border border-border animate-fade-in overflow-hidden max-w-full" id="recipes-page"
        x-data="recipesLiveSearch('{{ request('q') ?? '' }}')">
        <!-- Card Header: Busca e Botão -->
        <div class="p-4 sm:p-6 border-b border-border">
            <form method="GET" action="{{ route('dashboard.producao.receitas.index') }}"
                class="flex flex-col lg:flex-row lg:items-center gap-3 lg:gap-3">
                <div class="relative w-full lg:flex-1 lg:min-w-[200px] lg:max-w-sm order-1">
                    <i data-lucide="search"
                        class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground pointer-events-none"></i>
                    <input type="text" name="q" x-model="search"
                        @input.debounce.500ms="$event.target.form && $event.target.form.submit()"
                        placeholder="Buscar receita..."
                        class="form-input pl-10 h-10 bg-muted/30 border-transparent focus:bg-white transition-all text-sm rounded-lg w-full"
                        autocomplete="off">
                </div>
                <button type="button" @click="$dispatch('open-recipe-modal')"
                    class="btn-primary gap-2 h-10 px-4 rounded-lg shadow-sm shrink-0 w-full lg:w-auto lg:ml-auto justify-center order-2">
                    <i data-lucide="plus" class="h-4 w-4 text-white"></i>
                    <span class="font-bold text-white text-sm">Nova Receita</span>
                </button>
            </form>
        </div>

        <!-- Recipes Grid -->
        <div class="p-4 sm:p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-4">
                @forelse($recipes as $recipe)
                    @php
                        $name = $recipe->name ?? 'Sem nome';

                        // Gerar iniciais para avatar
                        $parts = preg_split('/\s+/', trim($name));
                        $initials = strtoupper(substr($parts[0] ?? '', 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
                        if (!$initials)
                            $initials = strtoupper(substr($name, 0, 2));

                        // Cores variadas para avatares (baseado no hash do nome)
                        $colors = [
                            'bg-blue-100 text-blue-600',
                            'bg-purple-100 text-purple-600',
                            'bg-pink-100 text-pink-600',
                            'bg-green-100 text-green-600',
                            'bg-orange-100 text-orange-600',
                            'bg-indigo-100 text-indigo-600',
                        ];
                        $colorIndex = crc32($name) % count($colors);
                        $avatarColor = $colors[$colorIndex];

                        $category = $recipe->category ?? 'Sem categoria';
                        $totalWeight = $recipe->total_weight ?? 0;
                        $cost = $recipe->cost ?? 0;
                        $finalPrice = $recipe->final_price ?? null;
                        $searchName = mb_strtolower($name, 'UTF-8');
                        $searchCategory = mb_strtolower($category, 'UTF-8');
                    @endphp
                    <div class="recipe-card bg-white border border-border rounded-xl p-3 sm:p-4 hover:shadow-md transition-all"
                        data-search-name="{{ $searchName }}" data-search-category="{{ $searchCategory }}"
                        x-show="matchesCard($el)">
                        <!-- Header: Avatar, Name, Category, Actions -->
                        <div class="flex items-start justify-between mb-2">
                            <div class="flex items-center gap-2 min-w-0 flex-1">
                                <div
                                    class="w-10 h-10 rounded-full {{ $avatarColor }} flex items-center justify-center font-bold text-xs flex-shrink-0">
                                    {{ $initials }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <button type="button" @click="$dispatch('open-recipe-modal', { id: {{ $recipe->id }} })"
                                        class="block group">
                                        <h3
                                            class="font-semibold text-foreground text-sm group-hover:text-primary transition-colors truncate">
                                            {{ $name }}
                                        </h3>
                                        <p class="text-xs text-muted-foreground mt-0.5 truncate">{{ $category }}</p>
                                    </button>
                                </div>
                            </div>
                            <div class="flex items-center gap-0.5 flex-shrink-0">
                                <button type="button" @click="$dispatch('open-recipe-modal', { id: {{ $recipe->id }} })"
                                    class="inline-flex items-center justify-center h-7 w-7 rounded-md hover:bg-muted transition-colors text-muted-foreground hover:text-foreground"
                                    title="Editar">
                                    <i data-lucide="edit" class="h-3.5 w-3.5"></i>
                                </button>
                                <form action="{{ route('dashboard.producao.receitas.destroy', $recipe->id) }}" method="POST"
                                    class="inline" onsubmit="return confirm('Tem certeza que deseja excluir esta receita?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="inline-flex items-center justify-center h-7 w-7 rounded-md hover:bg-destructive/10 transition-colors text-muted-foreground hover:text-destructive"
                                        title="Excluir">
                                        <i data-lucide="trash-2" class="h-3.5 w-3.5"></i>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Info: Rendimento — desktop (lg+) -->
                        <div class="mb-2 hidden lg:block">
                            <div class="flex items-center gap-1.5 text-xs text-muted-foreground min-w-0">
                                <i data-lucide="scale" class="w-3.5 h-3.5 flex-shrink-0"></i>
                                <span class="truncate">{{ number_format($totalWeight, 0, ',', '.') }}g</span>
                            </div>
                        </div>

                        <!-- Footer: Custo, Rendimento, Preço -->
                        <div class="pt-2 border-t border-border">
                            <!-- Desktop: Custo | Preço em linha -->
                            <div class="hidden lg:flex items-center justify-between">
                                <div>
                                    <p class="text-[10px] text-muted-foreground uppercase tracking-wide">Custo</p>
                                    <p class="text-sm font-bold text-primary mt-0.5">R$ {{ number_format($cost, 2, ',', '.') }}
                                    </p>
                                </div>
                                @if($finalPrice)
                                    <div class="text-right">
                                        <p class="text-[10px] text-muted-foreground uppercase tracking-wide">Preço venda</p>
                                        <p class="text-xs font-medium text-green-600 mt-0.5">R$
                                            {{ number_format($finalPrice, 2, ',', '.') }}</p>
                                    </div>
                                @else
                                    <div class="text-right">
                                        <p class="text-[10px] text-muted-foreground uppercase tracking-wide">Rendimento</p>
                                        <p class="text-xs font-medium text-foreground mt-0.5">
                                            {{ number_format($totalWeight, 0, ',', '.') }}g</p>
                                    </div>
                                @endif
                            </div>
                            <!-- Mobile/tablet: Custo · Rendimento · Preço na MESMA LINHA -->
                            <div class="flex items-stretch gap-2 lg:hidden">
                                <div class="min-w-0 flex-1 overflow-hidden">
                                    <p class="text-xs text-muted-foreground/80 uppercase tracking-wide truncate">Custo</p>
                                    <p class="text-sm font-bold text-primary truncate mt-0.5">R$
                                        {{ number_format($cost, 2, ',', '.') }}</p>
                                </div>
                                <span class="text-muted-foreground/40 self-center shrink-0 text-sm">·</span>
                                <div class="min-w-0 flex-1 overflow-hidden">
                                    <p class="text-xs text-muted-foreground/80 uppercase tracking-wide truncate">Peso</p>
                                    <p class="text-sm font-medium text-foreground truncate mt-0.5">
                                        {{ number_format($totalWeight, 0, ',', '.') }}g</p>
                                </div>
                                @if($finalPrice)
                                    <span class="text-muted-foreground/40 self-center shrink-0 text-sm">·</span>
                                    <div class="min-w-0 flex-1 overflow-hidden text-right">
                                        <p class="text-xs text-muted-foreground/80 uppercase tracking-wide truncate">Venda</p>
                                        <p class="text-sm font-medium text-green-600 truncate mt-0.5">R$
                                            {{ number_format($finalPrice, 2, ',', '.') }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-center text-muted-foreground py-12">
                        <div class="flex flex-col items-center gap-2">
                            <i data-lucide="book-open" class="w-12 h-12 opacity-20"></i>
                            <p class="text-sm">Nenhuma receita cadastrada</p>
                        </div>
                    </div>
                @endforelse
                @if($recipes->count() > 0)
                    <div class="recipe-filter-no-results col-span-full text-center text-muted-foreground py-8"
                        x-show="search && showNoResults" x-cloak x-transition>
                        <div class="flex flex-col items-center gap-2">
                            <i data-lucide="search-x" class="w-10 h-10 opacity-40"></i>
                            <p class="text-sm">Nenhuma receita encontrada para "<span x-text="search"></span>"</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Pagination -->
        @if(isset($recipes) && method_exists($recipes, 'links') && $recipes->hasPages())
            <div class="px-4 sm:px-6 py-3 sm:py-4 border-t border-border bg-muted/20">
                <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-center gap-3 sm:gap-4">
                    <p class="text-xs text-muted-foreground font-medium order-2 sm:order-1 lg:order-1 text-center sm:text-left">
                        Mostrando <span
                            class="font-bold text-foreground">{{ $recipes->firstItem() ?? $recipes->count() }}</span> de <span
                            class="font-bold text-foreground">{{ $recipes->total() }}</span> receitas
                    </p>
                    <div class="flex items-center gap-2 order-1 sm:order-2 lg:order-2">
                        @if($recipes->onFirstPage())
                            <button
                                class="px-3 sm:px-4 py-2 rounded-lg border border-border bg-white text-xs font-semibold text-muted-foreground disabled:opacity-40 disabled:cursor-not-allowed transition-all inline-flex items-center"
                                disabled>
                                <i data-lucide="chevron-left" class="w-4 h-4"></i>
                                <span class="ml-1 hidden sm:inline">Anterior</span>
                            </button>
                        @else
                            <a href="{{ $recipes->appends(request()->query())->previousPageUrl() }}"
                                class="px-3 sm:px-4 py-2 rounded-lg border border-border bg-white text-xs font-semibold text-foreground hover:bg-muted hover:border-primary/30 transition-all inline-flex items-center">
                                <i data-lucide="chevron-left" class="w-4 h-4"></i>
                                <span class="ml-1 hidden sm:inline">Anterior</span>
                            </a>
                        @endif

                        @if($recipes->hasMorePages())
                            <a href="{{ $recipes->appends(request()->query())->nextPageUrl() }}"
                                class="px-3 sm:px-4 py-2 rounded-lg border border-border bg-white text-xs font-semibold text-foreground hover:bg-muted hover:border-primary/30 transition-all inline-flex items-center">
                                <span class="mr-1 hidden sm:inline">Próximo</span>
                                <i data-lucide="chevron-right" class="w-4 h-4"></i>
                            </a>
                        @else
                            <button
                                class="px-3 sm:px-4 py-2 rounded-lg border border-border bg-white text-xs font-semibold text-muted-foreground disabled:opacity-40 disabled:cursor-not-allowed transition-all inline-flex items-center"
                                disabled>
                                <span class="mr-1 hidden sm:inline">Próximo</span>
                                <i data-lucide="chevron-right" class="w-4 h-4"></i>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @elseif(isset($recipes))
            <div class="px-4 sm:px-6 py-3 sm:py-4 border-t border-border bg-muted/20">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-3 sm:gap-4">
                    <p class="text-xs text-muted-foreground font-medium text-center sm:text-left">
                        Mostrando <span class="font-bold text-foreground">{{ $recipes->count() }}</span> de <span
                            class="font-bold text-foreground">{{ $recipes->total() ?? $recipes->count() }}</span> receitas
                    </p>
                </div>
            </div>
        @endif
    </div>

    <!-- Modal Receita (Criar/Editar) -->
    <div id="recipe-form-modal"
         x-data="recipeModal()"
         x-show="isOpen"
         @open-recipe-modal.window="openModal($event.detail)"
         @keydown.escape.window="closeModal()"
         class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50 backdrop-blur-sm p-4"
         x-cloak
         style="display: none;">
        <div class="bg-card rounded-2xl border border-border shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-hidden flex flex-col animate-in zoom-in-95 duration-200"
             @click.away="closeModal()">

            <!-- Header -->
            <div class="p-4 sm:p-6 border-b border-border flex items-center justify-between bg-muted/30">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
                        <i :data-lucide="recipeId ? 'edit' : 'plus'" class="h-5 w-5"></i>
                    </div>
                    <div>
                        <h2 class="text-lg sm:text-xl font-bold text-foreground" x-text="recipeId ? 'Editar Receita' : 'Nova Receita'"></h2>
                        <p class="text-xs text-muted-foreground" x-text="recipeId ? 'Ajuste os detalhes da sua receita' : 'Crie uma nova receita de produção'"></p>
                    </div>
                </div>
                <button @click="closeModal()" class="h-10 w-10 flex items-center justify-center rounded-xl hover:bg-muted transition-colors text-muted-foreground">
                    <i data-lucide="x" class="h-5 w-5"></i>
                </button>
            </div>

            <!-- Form Content -->
            <div class="p-0 overflow-y-auto flex-1 bg-background/50">
                <form :action="recipeId ? '{{ route('dashboard.producao.receitas.update', '__ID__') }}'.replace('__ID__', recipeId) : '{{ route('dashboard.producao.receitas.store') }}'" 
                      method="POST" 
                      id="modal-recipe-form" 
                      class="space-y-6"
                      @submit.prevent="submitForm">
                    @csrf
                    <template x-if="recipeId">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <div class="p-6 space-y-8">
                        <!-- Seção 1: Básicos -->
                        <div class="space-y-4">
                            <div class="flex items-center gap-2 text-primary">
                                <i data-lucide="info" class="w-4 h-4"></i>
                                <h3 class="text-sm font-semibold uppercase tracking-wider">Informações Básicas</h3>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="space-y-2">
                                    <label class="text-sm font-medium">Produto *</label>
                                    <select name="product_id" x-model="formData.product_id" required class="form-input rounded-xl h-11" @change="onProductChange()">
                                        <option value="">Selecione um produto...</option>
                                        @foreach ($products as $product)
                                            <option value="{{ $product->id }}" 
                                                    data-name="{{ $product->name }}"
                                                    data-category="{{ $product->category->name ?? '' }}"
                                                    data-weight="{{ $product->weight_grams ?? 0 }}">
                                                {{ $product->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-sm font-medium">Variante (Opcional)</label>
                                    <select name="variant_id" x-model="formData.variant_id" class="form-input rounded-xl h-11" @change="onVariantChange()">
                                        <option value="">Produto simples (ou selecione variante)</option>
                                        <template x-for="v in availableVariants" :key="v.id || v.name">
                                            <option :value="String(v.id || v.name)" x-text="v.name + (v.price ? ' (R$ ' + v.price + ')' : '')" :data-weight="v.weight_grams"></option>
                                        </template>
                                    </select>
                                </div>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="space-y-2">
                                    <label class="text-sm font-medium">Nome da Receita *</label>
                                    <input type="text" name="name" x-model="formData.name" required class="form-input rounded-xl h-11">
                                </div>
                                <div class="space-y-2">
                                    <label class="text-sm font-medium">Categoria</label>
                                    <input type="text" name="category" x-model="formData.category" list="modal-categories" class="form-input rounded-xl h-11">
                                    <datalist id="modal-categories">
                                        @foreach($categories as $cat)
                                            <option value="{{ $cat }}">
                                        @endforeach
                                    </datalist>
                                </div>
                            </div>

                            <div class="grid gap-4 grid-cols-2">
                                <div class="bg-muted/30 p-3 rounded-xl border border-border/50">
                                    <label class="text-[10px] uppercase font-bold text-muted-foreground block mb-1">Peso Total</label>
                                    <span class="text-lg font-mono font-bold text-primary" x-text="calculateTotalWeight() + 'g'"></span>
                                    <input type="hidden" name="total_weight" :value="calculateTotalWeight()">
                                </div>
                                <div class="bg-muted/30 p-3 rounded-xl border border-border/50">
                                    <label class="text-[10px] uppercase font-bold text-muted-foreground block mb-1">Rendimento</label>
                                    <span class="text-lg font-mono font-bold text-foreground" x-text="calculatePortions() + ' un.'"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Seção 2: Panificação (Condicional) -->
                        <div class="p-4 bg-primary/5 rounded-2xl border border-primary/10 space-y-4" x-show="formData.is_bread">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2 text-primary">
                                    <i data-lucide="settings" class="w-4 h-4"></i>
                                    <h3 class="text-sm font-semibold uppercase tracking-wider">Parâmetros de Panificação</h3>
                                </div>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="uses_baker_percentage" x-model="formData.uses_baker_percentage" class="rounded-lg text-primary">
                                    <span class="text-xs font-medium">% Padeiro</span>
                                </label>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="space-y-2">
                                    <label class="text-xs font-medium">Hidratação (%)</label>
                                    <div class="relative">
                                        <input type="number" name="hydration" x-model.number="formData.hydration" step="0.1" class="form-input rounded-xl pr-10">
                                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground text-xs">%</span>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-xs font-medium">Levain (%)</label>
                                    <div class="relative">
                                        <input type="number" name="levain" x-model.number="formData.levain" step="0.1" class="form-input rounded-xl pr-10">
                                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground text-xs">%</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Seção 3: Etapas e Ingredientes -->
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2 text-primary">
                                    <i data-lucide="layers" class="w-4 h-4"></i>
                                    <h3 class="text-sm font-semibold uppercase tracking-wider">Etapas e Ingredientes</h3>
                                </div>
                                <button type="button" @click="addStep()" class="text-xs font-bold text-primary hover:underline flex items-center gap-1">
                                    <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i>
                                    Nova Etapa
                                </button>
                            </div>

                            <div class="space-y-6">
                                <template x-for="(step, sIdx) in formData.steps" :key="sIdx">
                                    <div class="bg-card border border-border rounded-2xl overflow-hidden shadow-sm">
                                        <div class="px-4 py-3 bg-muted/20 border-b border-border flex items-center gap-3">
                                            <input type="text" x-model="step.name" :name="'steps['+sIdx+'][name]'" placeholder="Nome da etapa" required 
                                                   class="bg-transparent border-none focus:ring-0 font-bold text-sm flex-1 p-0">
                                            <button type="button" @click="removeStep(sIdx)" x-show="formData.steps.length > 1" class="text-destructive p-1 hover:bg-destructive/10 rounded-lg transition-colors">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </div>
                                        <div class="p-3 sm:p-4 space-y-3">
                                            <template x-for="(ing, iIdx) in step.ingredients" :key="iIdx">
                                                <div class="grid grid-cols-2 sm:grid-cols-[1fr,80px,100px,40px] gap-x-3 gap-y-2 items-end p-3 sm:p-0 bg-muted/20 sm:bg-transparent rounded-xl relative group">
                                                    <!-- Ingrediente (Linha Inteira no Mobile) -->
                                                    <div class="col-span-2 sm:col-span-1 space-y-1">
                                                        <label class="text-[10px] font-extrabold uppercase text-muted-foreground tracking-wider ml-1">Ingrediente</label>
                                                        <select x-model="ing.ingredient_id" :name="'steps['+sIdx+'][ingredients]['+iIdx+'][ingredient_id]'" required class="form-input rounded-xl text-sm h-10 border-border/50">
                                                            <option value="">Selecione...</option>
                                                            @foreach($ingredients as $singleIng)
                                                                @php
                                                                    $lowCat = mb_strtolower($singleIng->category ?? '');
                                                                    $lowName = mb_strtolower($singleIng->name ?? '');
                                                                    // Filtro mais agressivo para remover produtos e embalagens da lista
                                                                    if (
                                                                        str_contains($lowCat, 'pão') || 
                                                                        str_contains($lowCat, 'sobremesa') || 
                                                                        str_contains($lowCat, 'bolo') ||
                                                                        str_contains($lowCat, 'embalagem') ||
                                                                        str_contains($lowCat, 'produto') ||
                                                                        str_contains($lowName, 'forma de') ||
                                                                        str_contains($lowName, 'italianinho')
                                                                    ) continue;
                                                                @endphp
                                                                <option value="{{ (string) $singleIng->id }}">{{ $singleIng->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <!-- % (Meia Linha no Mobile) -->
                                                    <div class="col-span-1 sm:col-span-1 space-y-1">
                                                        <label class="text-[10px] font-extrabold uppercase text-muted-foreground tracking-wider ml-1 text-center block sm:hidden">%</label>
                                                        <input type="number" step="0.01" x-model="ing.percentage" :name="'steps['+sIdx+'][ingredients]['+iIdx+'][percentage]'" placeholder="%" class="form-input rounded-xl text-sm h-10 text-center border-border/50 font-mono">
                                                    </div>
                                                    <!-- Peso (Meia Linha no Mobile + Delete) -->
                                                    <div class="col-span-1 sm:col-span-1 space-y-1 flex gap-2 items-end">
                                                        <div class="flex-1 space-y-1">
                                                            <label class="text-[10px] font-extrabold uppercase text-muted-foreground tracking-wider ml-1 text-center block sm:hidden">Peso (g)</label>
                                                            <input type="number" step="0.1" x-model.number="ing.weight" :name="'steps['+sIdx+'][ingredients]['+iIdx+'][weight]'" placeholder="g" class="form-input rounded-xl text-sm h-10 text-center border-border/50 font-mono" required>
                                                        </div>
                                                        <button type="button" @click="removeIngredient(sIdx, iIdx)" x-show="step.ingredients.length > 1" class="h-10 w-10 flex sm:hidden items-center justify-center text-red-500 hover:bg-red-50 rounded-xl transition-colors">
                                                            <i data-lucide="x" class="w-4 h-4"></i>
                                                        </button>
                                                    </div>
                                                    <!-- Delete (Desktop) -->
                                                    <button type="button" @click="removeIngredient(sIdx, iIdx)" x-show="step.ingredients.length > 1" class="hidden sm:flex h-10 w-10 items-center justify-center text-muted-foreground hover:text-red-500 hover:bg-red-50 rounded-xl transition-all">
                                                        <i data-lucide="x" class="w-4 h-4"></i>
                                                    </button>
                                                </div>
                                            </template>
                                            <button type="button" @click="addIngredient(sIdx)" class="w-full py-2.5 border border-dashed border-primary/30 rounded-xl text-xs font-bold text-primary hover:bg-primary/5 transition-all flex items-center justify-center gap-2 mt-4">
                                                <i data-lucide="plus" class="w-4 h-4"></i>
                                                ADICIONAR INGREDIENTE
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Seção 4: Opções Adicionais -->
                        <div class="space-y-4 pt-4 border-t border-border">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <label class="flex items-center gap-3 p-3 rounded-xl border border-border hover:bg-muted/30 transition-colors cursor-pointer">
                                    <input type="checkbox" name="is_active" x-model="formData.is_active" class="rounded-lg h-5 w-5 text-primary">
                                    <div>
                                        <span class="text-sm font-bold block">Receita Ativa</span>
                                        <span class="text-[10px] text-muted-foreground">Disponível para produção</span>
                                    </div>
                                </label>
                                <label class="flex items-center gap-3 p-3 rounded-xl border border-border hover:bg-muted/30 transition-colors cursor-pointer">
                                    <input type="checkbox" name="is_bread" x-model="formData.is_bread" class="rounded-lg h-5 w-5 text-primary">
                                    <div>
                                        <span class="text-sm font-bold block">É Pão</span>
                                        <span class="text-[10px] text-muted-foreground">Habilita cálculos de padeiro</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium">Notas da Receita</label>
                            <textarea name="notes" x-model="formData.notes" rows="2" class="form-input rounded-xl" placeholder="Algum segredo ou dica?"></textarea>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Footer -->
            <div class="p-4 sm:p-6 border-t border-border bg-muted/30 flex flex-col sm:flex-row gap-3 sm:justify-between items-center">
                <div class="text-xs text-muted-foreground text-center sm:text-left hidden sm:block">
                    Campos marcados com <span class="text-destructive">*</span> são obrigatórios.
                </div>
                <div class="flex gap-3 w-full sm:w-auto">
                    <button type="button" @click="closeModal()" class="flex-1 sm:flex-none btn-outline rounded-xl h-11 px-6">Cancelar</button>
                    <button type="submit" form="modal-recipe-form" class="flex-1 sm:flex-none btn-primary rounded-xl h-11 px-8 shadow-lg shadow-primary/20" :disabled="isSubmitting">
                        <span x-show="!isSubmitting" x-text="recipeId ? 'Salvar Alterações' : 'Criar Receita'"></span>
                        <i x-show="isSubmitting" data-lucide="loader-2" class="w-5 h-5 animate-spin mx-auto"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Fila de Impressão -->
    <div id="print-queue-modal" 
         x-data="printQueue()" 
         x-show="isOpen" 
         @open-print-queue.window="isOpen = true; loadQueue()"
         @keydown.escape.window="closeModal()"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
         x-cloak
         style="display: none;">
        <div class="bg-card rounded-xl border border-border shadow-xl w-full max-w-4xl max-h-[90vh] overflow-hidden flex flex-col m-4">
            <div class="p-6 border-b border-border flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold">Fila de Impressão ({{ date('d') }})</h2>
                    <p class="text-sm text-muted-foreground">Organiza e imprima suas receitas em formato A4. Total: <span x-text="queue.length"></span> unidades</p>
                </div>
                <button @click="closeModal()" class="btn-outline h-9 w-9 p-0">
                    <i data-lucide="x" class="h-4 w-4"></i>
                </button>
            </div>

            <div class="p-6 overflow-y-auto flex-1">
                <div class="mb-4">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" x-model="replaceLevain" class="rounded">
                        <span class="text-sm">Substituir Levain por fermento Liofilizado</span>
                    </label>
                </div>

                <div class="space-y-3" x-show="queue.length > 0">
                    <template x-for="(item, index) in queue" :key="index">
                        <div class="flex items-center gap-4 p-4 border border-border rounded-lg">
                            <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center text-primary font-semibold text-sm flex-shrink-0">
                                <span x-text="index + 1"></span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="font-semibold" x-text="item.recipe_name || 'Receita'"></h4>
                                <div class="flex gap-4 text-sm text-muted-foreground mt-1 flex-wrap">
                                    <span>Qtde: <span x-text="item.quantity"></span></span>
                                    <span>Peso: <span x-text="formatWeight(item.weight)"></span>g por un.</span>
                                </div>
                                <p x-show="item.observation" class="text-sm text-amber-700/80 bg-amber-50 rounded px-2 py-1 mt-2"><strong>Obs:</strong> <span x-text="item.observation"></span></p>
                            </div>
                            <a :href="'{{ route('dashboard.producao.receitas.show', '') }}/' + item.recipe_id" 
                               target="_blank"
                               class="btn-outline h-9 w-9 p-0 flex-shrink-0">
                                <i data-lucide="eye" class="h-4 w-4"></i>
                            </a>
                        </div>
                    </template>
                </div>

                <div x-show="queue.length === 0" class="text-center py-12 text-muted-foreground">
                    <i data-lucide="list-todo" class="w-12 h-12 mx-auto mb-4 opacity-50"></i>
                    <p>Nenhum item na fila. Adicione receitas pela Lista de Produção ou use &quot;Produzir Item&quot; nos cards.</p>
                </div>
            </div>

            <div class="p-6 border-t border-border flex gap-3 justify-end">
                <button @click="closeModal()" class="btn-outline">Fechar</button>
                <button @click="viewPrint()" class="btn-primary">Visualizar</button>
                <button @click="print()" class="btn-primary bg-green-600 hover:bg-green-700">Imprimir</button>
            </div>
        </div>
    </div>

    @push('styles')
        <style>[x-cloak]{display:none!important}</style>
    @endpush

    @push('scripts')
        <script>
        document.addEventListener('alpine:init', function () {
            Alpine.data('recipesLiveSearch', function (initialQ) {
                return {
                    search: (typeof initialQ === 'string' ? initialQ : '') || '',
                    showNoResults: false,

                    init: function () {
                        var self = this;
                        function updateNoResults() {
                            self.$nextTick(function () {
                                var root = document.getElementById('recipes-page');
                                var cards = root ? root.querySelectorAll('.recipe-card') : [];
                                var visible = 0;
                                cards.forEach(function (el) {
                                    if (self.matchesCard(el)) visible++;
                                });
                                self.showNoResults = self.search.trim() !== '' && visible === 0;
                            });
                        }
                        this.$watch('search', updateNoResults);
                        updateNoResults();
                    },

                    matchesCard: function (el) {
                        var q = this.search.trim().toLowerCase();
                        if (!q) return true;
                        var name = (el.getAttribute('data-search-name') || '').toLowerCase();
                        var category = (el.getAttribute('data-search-category') || '').toLowerCase();
                        var nameNorm = name.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                        var categoryNorm = category.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                        var qNorm = q.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                        if (name.includes(q) || nameNorm.includes(qNorm)) return true;
                        if (category.includes(q) || categoryNorm.includes(qNorm)) return true;
                        return false;
                    }
                };
            });

            Alpine.data('printQueue', function() {
                return {
                    isOpen: false,
                    queue: [],
                    listId: null,
                    replaceLevain: false,
                    printUrlTemplate: '{{ route("dashboard.producao.lista-producao.print", ["id" => "__ID__"]) }}',

                    init() {
                        this.loadQueue();
                    },

                    loadQueue() {
                        this.listId = null;
                        fetch('{{ route('dashboard.producao.print-queue.from-list') }}', {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            this.queue = (data.queue || []).map(item => ({
                                ...item,
                                quantity: parseInt(item.quantity) || 1,
                                weight: parseFloat(item.weight) || 0,
                                observation: item.observation || ''
                            }));
                            this.listId = data.list_id || null;
                        })
                        .catch(error => {
                            console.error('Erro ao carregar fila:', error);
                        });
                    },

                    viewPrint() {
                        if (!this.listId || this.queue.length === 0) {
                            alert('Nenhum item na fila. Adicione receitas pela Lista de Produção ou use "Produzir Item" nos cards.');
                            return;
                        }
                        const url = this.printUrlTemplate.replace('__ID__', this.listId) + '?replace_levain=' + (this.replaceLevain ? '1' : '0');
                        window.open(url, '_blank');
                    },

                    print() {
                        if (!this.listId || this.queue.length === 0) {
                            alert('Nenhum item na fila. Adicione receitas pela Lista de Produção ou use "Produzir Item" nos cards.');
                            return;
                        }
                        const url = this.printUrlTemplate.replace('__ID__', this.listId) + '?replace_levain=' + (this.replaceLevain ? '1' : '0');
                        const printWindow = window.open(url, '_blank');
                        if (printWindow) printWindow.onload = function() { printWindow.print(); };
                    },

                    closeModal() {
                        this.isOpen = false;
                    },

                    formatWeight(weight) {
                        return new Intl.NumberFormat('pt-BR').format(weight || 0);
                    }
                };
            });

            window.allProducts = @json($products);

            Alpine.data('recipeModal', function() {
            return {
                isOpen: false,
                isSubmitting: false,
                recipeId: null,
                formData: {
                    product_id: '',
                    variant_id: '',
                    name: '',
                    category: '',
                    notes: '',
                    is_active: true,
                    is_bread: false,
                    is_fermented: false,
                    hydration: 70,
                    levain: 30,
                    uses_baker_percentage: false,
                    steps: []
                },
                ingredients: @json($ingredients),
                availableVariants: [],
                unit_weight: 0,

                init() {
                    this.resetForm();
                },

                resetForm() {
                    this.recipeId = null;
                    this.formData = {
                        product_id: '',
                        variant_id: '',
                        name: '',
                        category: '',
                        notes: '',
                        is_active: true,
                        is_bread: false,
                        is_fermented: false,
                        hydration: 70,
                        levain: 30,
                        uses_baker_percentage: false,
                        steps: [{
                            name: 'Massa Principal',
                            ingredients: [{ ingredient_id: '', percentage: null, weight: null }]
                        }]
                    };
                    this.availableVariants = [];
                    this.unit_weight = 0;
                },

                openModal(detail) {
                    this.resetForm();
                    if (detail && detail.id) {
                        this.recipeId = detail.id;
                        this.loadRecipe(detail.id);
                    }
                    this.isOpen = true;
                    this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
                },

                closeModal() {
                    this.isOpen = false;
                    if (!this.isSubmitting) this.resetForm();
                },

                loadRecipe(id) {
                    const editUrl = '{{ route("dashboard.producao.receitas.edit", "__ID__") }}'.replace("__ID__", id);
                    fetch(editUrl, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                    })
                    .then(r => {
                        if (!r.ok) throw new Error('Falha ao carregar receita');
                        return r.json();
                    })
                    .then(data => {
                        if (!data.recipe) throw new Error('Dados da receita não encontrados');
                        const r = data.recipe;
                        
                        // Normalização de IDs para String e carga de dados
                        this.formData = {
                            product_id: String(r.product_id),
                            variant_id: r.variant_id ? String(r.variant_id) : '',
                            name: r.name,
                            category: r.category || '',
                            notes: r.notes || '',
                            is_active: String(r.is_active) === '1' || r.is_active === true,
                            is_bread: String(r.is_bread) === '1' || r.is_bread === true,
                            is_fermented: String(r.is_fermented) === '1' || r.is_fermented === true,
                            hydration: r.hydration || 70,
                            levain: r.levain || 30,
                            uses_baker_percentage: String(r.uses_baker_percentage) === '1' || r.uses_baker_percentage === true,
                            steps: (data.steps || []).map(s => ({
                                name: s.name,
                                ingredients: (s.ingredients || []).map(i => ({
                                    ingredient_id: String(i.ingredient_id),
                                    type: i.type,
                                    percentage: i.percentage,
                                    weight: i.weight
                                }))
                            }))
                        };

                        this.unit_weight = parseFloat(data.unit_weight || 0);

                        // Se o backend enviou variantes específicas
                        if (data.product_variants) {
                            // Garantir que seja um array para o x-for
                            this.availableVariants = Array.isArray(data.product_variants) 
                                ? data.product_variants 
                                : Object.values(data.product_variants);
                        } else {
                            this.availableVariants = [];
                        }

                        this.$nextTick(() => { 
                            this.onProductChange(true); // Sincroniza estado do produto sem resetar nome
                            
                            // Se já tem variante, garante a sincronização do peso unitário
                            if (this.formData.variant_id) {
                                // Pequeno delay para garantir que o DOM do select de variantes foi renderizado pelo x-for
                                setTimeout(() => {
                                    this.onVariantChange();
                                }, 50);
                            }
                            if (window.lucide) window.lucide.createIcons(); 
                        });
                    })
                    .catch(e => {
                        console.error('Erro ao carregar:', e);
                        alert('Erro ao carregar dados da receita: ' + e.message);
                        this.closeModal();
                    });
                },

                onProductChange(keepName = false) {
                    const productId = String(this.formData.product_id);
                    const product = window.allProducts.find(p => String(p.id) === productId);
                    
                    
                    if (product) {
                        console.log('Produto selecionado change:', product.name, 'ID:', product.id);
                        console.log('Variantes disponíveis (available_variants):', product.available_variants);

                        // Se não carregamos variantes via AJAX (edição) ou se for uma nova receita, pegamos do window.allProducts
                        if (!this.availableVariants || this.availableVariants.length === 0 || !keepName) {
                            // Prioridade para available_variants que criamos explicitamente, fallback para variants
                            this.availableVariants = product.available_variants || product.variants_list || product.variants || [];
                        }
                        
                        // Normalização se vier como objeto
                        if (!Array.isArray(this.availableVariants) && typeof this.availableVariants === 'object') {
                             this.availableVariants = Object.values(this.availableVariants);
                        }
                        
                        // Ordenação resiliente
                        if (Array.isArray(this.availableVariants)) {
                            this.availableVariants.sort((a, b) => (a.sort_order || 0) - (b.sort_order || 0));
                        }

                        if (!keepName) {
                            this.formData.category = (product.category ? product.category.name : '') || '';
                            this.formData.name = product.name || '';
                            this.unit_weight = parseFloat(product.weight_grams || 0);
                            
                            const name = this.formData.name.toLowerCase();
                            const cat = this.formData.category.toLowerCase();
                            this.formData.is_bread = name.includes('pão') || cat.includes('pão');
                            
                            // Reset variant if product changed manually
                            this.formData.variant_id = '';
                        }
                    } else if (productId) {
                        console.warn('Produto não encontrado em window.allProducts:', productId);
                    }
                },

                onVariantChange() {
                    const variant = this.availableVariants.find(v => v.id == this.formData.variant_id);
                    if (variant) {
                        this.unit_weight = parseFloat(variant.weight_grams || 0);
                    } else {
                        // Se não tem variante, volta para o peso do produto
                        const product = window.allProducts.find(p => p.id == this.formData.product_id);
                        if (product) {
                            this.unit_weight = parseFloat(product.weight_grams || 0);
                        }
                    }
                },

                addStep() {
                    this.formData.steps.push({
                        name: 'Etapa ' + (this.formData.steps.length + 1),
                        ingredients: [{ ingredient_id: '', percentage: null, weight: null }]
                    });
                    this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
                },

                removeStep(idx) {
                    if (this.formData.steps.length > 1) this.formData.steps.splice(idx, 1);
                },

                addIngredient(sIdx) {
                    this.formData.steps[sIdx].ingredients.push({ ingredient_id: '', percentage: null, weight: null });
                },

                removeIngredient(sIdx, iIdx) {
                    if (this.formData.steps[sIdx].ingredients.length > 1) {
                        this.formData.steps[sIdx].ingredients.splice(iIdx, 1);
                    }
                },

                calculateTotalWeight() {
                    let total = 0;
                    this.formData.steps.forEach(s => {
                        s.ingredients.forEach(i => {
                            total += parseFloat(i.weight || 0);
                        });
                    });
                    return total > 0 ? total.toFixed(1) : "0";
                },

                calculatePortions() {
                    const total = parseFloat(this.calculateTotalWeight());
                    const unitWeight = parseFloat(this.unit_weight);
                    if (unitWeight > 0 && total > 0) {
                        // Sempre arredondar para baixo
                        return Math.floor(total / unitWeight);
                    }
                    return 0;
                },

                submitForm() {
                    this.isSubmitting = true;
                    const form = document.getElementById('modal-recipe-form');
                    const fd = new FormData(form);

                    fetch(form.action, {
                        method: 'POST',
                        body: fd,
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    })
                    .then(r => {
                        if (r.ok) {
                            window.location.reload();
                        } else {
                            return r.json().then(err => { 
                                alert('Erro: ' + (err.message || 'Verifique os campos obrigatórios'));
                                throw err; 
                            });
                        }
                    })
                    .catch(e => {
                        console.error('Erro ao salvar:', e);
                        this.isSubmitting = false;
                    });
                }
            };
        });
    });
</script>
    @endpush
@endsection
