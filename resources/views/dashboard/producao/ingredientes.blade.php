@extends('dashboard.layouts.app')

@section('page_title', 'Ingredientes')
@section('page_subtitle', 'Gerenciamento de ingredientes')

@section('page_actions')
    {{-- Actions handled inside the main content area for better mobile layout --}}
@endsection

@section('content')
    <style>
        [x-cloak] {
            display: none !important;
        }

        /* Enforce mobile consistency exactly like Products */
        @media (max-width: 640px) {
            #ingredients-grid>.ingredient-card {
                width: 100% !important;
                max-width: 100% !important;
                box-sizing: border-box !important;
            }

            #ingredients-page {
                overflow-x: hidden !important;
                width: 100% !important;
                max-width: 100vw !important;
            }
        }
    </style>

    <div x-data="ingredientsManager('{{ request('q') ?? '' }}')"
        class="bg-card rounded-xl border border-border animate-fade-in w-full overflow-x-hidden" id="ingredients-page"
        @click.away="closeAllEdits()">
        {{-- Header: Search & Actions --}}
        <div class="p-4 border-b border-border flex flex-col sm:flex-row gap-4 justify-between w-full">
            <div class="relative flex-1 w-full max-w-full sm:max-w-md">
                <i data-lucide="search"
                    class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground pointer-events-none"></i>
                <form action="{{ route('dashboard.producao.ingredientes.index') }}" method="GET">
                    <input type="text" name="q" x-model="search" @input.debounce.300ms="updateSearch()"
                        placeholder="Buscar ingrediente..." class="form-input pl-10 h-10 w-full" autocomplete="off">
                </form>
            </div>

            <button @click="openCreateModal()"
                class="btn-primary gap-2 h-10 px-4 rounded-lg shadow-sm w-full sm:w-auto shrink-0 font-bold inline-flex items-center justify-center">
                <i data-lucide="plus" class="h-4 w-4 text-white"></i>
                <span>Novo Ingrediente</span>
            </button>
        </div>

        {{-- Ingredients Grid --}}
        <div id="ingredients-grid" class="p-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 w-full">
            @forelse($ingredients as $ingredient)
                @php
                    $isLowStock = $ingredient->stock_status === 'low_stock' || $ingredient->stock_status === 'out_of_stock';
                    $statusClass = $ingredient->stock_status === 'out_of_stock' ? 'status-badge-pending' : ($ingredient->stock_status === 'low_stock' ? 'status-badge-warning' : 'status-badge-completed');
                @endphp
                <div class="ingredient-card searchable-item border border-border rounded-xl p-4 hover:shadow-md transition-all cursor-pointer group flex flex-col justify-between bg-white w-full min-w-0"
                    data-search-name="{{ mb_strtolower($ingredient->name, 'UTF-8') }}"
                    data-search-category="{{ mb_strtolower($ingredient->category, 'UTF-8') }}"
                    x-show="matches($el)"
                    @click='openEditModal({{ json_encode($ingredient) }})'>

                    {{-- Header: Icon, Name, Category --}}
                    <div class="flex items-start justify-between mb-3 gap-3">
                        <div class="flex items-center gap-3 min-w-0 flex-1">
                            <div
                                class="w-10 h-10 rounded-xl bg-orange-50 flex items-center justify-center overflow-hidden shrink-0 border border-orange-100">
                                <i data-lucide="wheat" class="w-5 h-5 text-orange-500"></i>
                            </div>

                            <div class="min-w-0 flex-1">
                                <h3 class="font-semibold text-foreground text-sm sm:text-base truncate leading-tight"
                                    title="{{ $ingredient->name }}">{{ $ingredient->name }}</h3>
                                <p
                                    class="text-[11px] text-muted-foreground mt-0.5 truncate uppercase tracking-wider font-medium">
                                    {{ $ingredient->category ?? 'Sem categoria' }} • {{ $ingredient->unit ?? 'un' }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-1 shrink-0">
                            <button @click.stop='openEditModal({{ json_encode($ingredient) }})'
                                class="inline-flex items-center justify-center h-8 w-8 rounded-lg hover:bg-muted transition-colors text-muted-foreground hover:text-primary"
                                title="Editar">
                                <i data-lucide="pencil" class="h-4 w-4"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Footer: Cost | Stock | Min (3 Columns, Reduced Height) --}}
                    <div class="pt-3 border-t border-border mt-auto">
                        <div class="grid grid-cols-3 gap-2 items-center">
                            <div class="min-w-0">
                                <p class="text-[9px] text-muted-foreground uppercase tracking-widest font-bold truncate">CUSTO
                                </p>
                                <p class="text-xs font-bold text-primary mt-0.5 truncate">R$
                                    {{ number_format($ingredient->cost, 2, ',', '.') }}
                                </p>
                            </div>

                            <div class="text-center min-w-0">
                                <p class="text-[9px] text-muted-foreground uppercase tracking-widest font-bold truncate">ESTOQUE
                                </p>
                                <span class="status-badge {{ $statusClass }} text-[10px] py-0 px-1.5 mt-0.5 inline-block">
                                    {{ number_format($ingredient->stock, 2, ',', '.') }}
                                </span>
                            </div>

                            <div class="text-right min-w-0">
                                <p class="text-[9px] text-muted-foreground uppercase tracking-widest font-bold truncate">MÍNIMO
                                </p>
                                <p class="text-xs font-medium text-foreground mt-0.5 truncate">
                                    {{ number_format($ingredient->min_stock, 1, ',', '.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-12 text-center text-muted-foreground">
                    <div class="bg-muted/30 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="wheat" class="w-8 h-8 opacity-50"></i>
                    </div>
                    <h3 class="text-lg font-medium text-foreground mb-1">Nenhum ingrediente encontrado</h3>
                    <p class="mb-4">Comece adicionando novos ingredientes para suas receitas.</p>
                </div>
            @endforelse
        </div>

        {{-- No Results State --}}
        <div class="text-center text-muted-foreground py-12" x-show="search && showNoResults" x-cloak x-transition>
            <div class="flex flex-col items-center gap-2">
                <i data-lucide="search-x" class="w-10 h-10 opacity-40"></i>
                <p class="text-sm">Nenhum ingrediente encontrado para "<span x-text="search"></span>"</p>
            </div>
        </div>

        {{-- Pagination --}}
        @if($ingredients->hasPages())
            <div class="px-4 sm:px-6 py-3 sm:py-4 border-t border-border bg-muted/20">
                <div
                    class="flex flex-col sm:flex-row items-center justify-between gap-3 sm:gap-4 font-medium text-xs text-muted-foreground">
                    <p>Mostrando <span class="text-foreground">{{ $ingredients->count() }}</span> de <span
                            class="text-foreground">{{ $ingredients->total() }}</span> ingredientes</p>
                    {{ $ingredients->links() }}
                </div>
            </div>
        @endif

        {{-- Unified Ingredient Modal (Create/Edit) --}}
        <div x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" x-cloak>

            <div class="bg-card w-full max-w-lg rounded-xl border border-border shadow-xl overflow-hidden flex flex-col max-h-[85dvh]"
                @click.away="showModal = false" x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0" x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-4">

                <form
                    :action="modalMode === 'edit' ? '{{ url('/dashboard/producao/ingredientes') }}/' + ingredient.id : '{{ route('dashboard.producao.ingredientes.store') }}'"
                    method="POST" class="contents">
                    @csrf
                    <template x-if="modalMode === 'edit'">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <div class="p-4 sm:p-6 border-b border-border flex justify-between items-center bg-muted/20 shrink-0">
                        <h3 class="font-bold text-lg"
                            x-text="modalMode === 'edit' ? 'Editar Ingrediente' : 'Novo Ingrediente'"></h3>
                        <button type="button" @click="showModal = false"
                            class="text-muted-foreground hover:text-foreground">
                            <i data-lucide="x" class="h-5 w-5"></i>
                        </button>
                    </div>

                    <div class="p-4 sm:p-6 space-y-4 overflow-y-auto flex-1 min-h-0">
                        <div>
                            <label class="block text-sm font-semibold mb-1.5">Nome do Ingrediente <span
                                    class="text-red-500">*</span></label>
                            <input type="text" name="name" x-model="ingredient.name" required class="form-input w-full"
                                placeholder="Ex: Farinha de Trigo">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold mb-1.5">Categoria</label>
                                <input type="text" name="category" list="categories" x-model="ingredient.category"
                                    class="form-input w-full" placeholder="Ex: Farinhas">
                                <datalist id="categories">
                                    @foreach($categories ?? [] as $cat)
                                        <option value="{{ $cat }}">
                                    @endforeach
                                </datalist>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5">Unidade</label>
                                <select name="unit" x-model="ingredient.unit" class="form-input w-full">
                                    <option value="g">Gramas (g)</option>
                                    <option value="kg">Quilos (kg)</option>
                                    <option value="ml">Mililitros (ml)</option>
                                    <option value="l">Litros (l)</option>
                                    <option value="un">Unidade (un)</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold mb-1.5">Peso Embalagem (g/ml)</label>
                                <input type="number" name="package_weight" x-model="ingredient.package_weight" step="0.01"
                                    min="0" class="form-input w-full" placeholder="Ex: 1000">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5">Preço de Custo (R$)</label>
                                <input type="number" name="cost" x-model="ingredient.cost" step="0.01" min="0"
                                    class="form-input w-full" placeholder="0,00">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold mb-1.5">Estoque Atual</label>
                                <input type="number" name="stock" x-model="ingredient.stock" step="0.01" min="0"
                                    class="form-input w-full" placeholder="0">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1.5">Estoque Mínimo</label>
                                <input type="number" name="min_stock" x-model="ingredient.min_stock" step="0.01" min="0"
                                    class="form-input w-full" placeholder="0">
                            </div>
                        </div>

                        <div class="flex items-center gap-6 pt-2">
                            <label class="flex items-center gap-2 cursor-pointer group">
                                <div class="relative flex items-center">
                                    <input type="checkbox" name="is_flour" value="1" x-model="ingredient.is_flour"
                                        :checked="ingredient.is_flour"
                                        class="form-checkbox h-5 w-5 rounded border-border text-primary focus:ring-primary">
                                </div>
                                <span class="text-sm font-medium group-hover:text-primary transition-colors">É
                                    farinha</span>
                            </label>

                            <label class="flex items-center gap-2 cursor-pointer group">
                                <div class="relative flex items-center">
                                    <input type="checkbox" name="is_active" value="1" x-model="ingredient.is_active"
                                        :checked="ingredient.is_active"
                                        class="form-checkbox h-5 w-5 rounded border-border text-primary focus:ring-primary">
                                </div>
                                <span class="text-sm font-medium group-hover:text-primary transition-colors">Ativo</span>
                            </label>
                        </div>
                    </div>

                    <div class="p-4 border-t border-border bg-muted/20 flex justify-end gap-3 shrink-0">
                        <button type="button" @click="showModal = false" class="btn-ghost">Cancelar</button>
                        <button type="submit" class="btn-primary px-8"
                            x-text="modalMode === 'edit' ? 'Atualizar' : 'Salvar Ingrediente'"></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('ingredientsManager', (initialQ) => ({
                    search: (typeof initialQ === 'string' ? initialQ : '') || '',
                    showNoResults: false,
                    showModal: false,
                    modalMode: 'create', // 'create' or 'edit'
                    ingredient: {
                        id: null,
                        name: '',
                        category: '',
                        unit: 'g',
                        package_weight: '',
                        cost: '',
                        stock: '',
                        min_stock: '',
                        is_flour: false,
                        is_active: true
                    },

                    openCreateModal() {
                        this.modalMode = 'create';
                        this.ingredient = {
                            id: null,
                            name: '',
                            category: '',
                            unit: 'g',
                            package_weight: '',
                            cost: '',
                            stock: '',
                            min_stock: '',
                            is_flour: false,
                            is_active: true
                        };
                        this.showModal = true;
                    },

                    openEditModal(ingredient) {
                        this.modalMode = 'edit';
                        this.ingredient = {
                            id: ingredient.id,
                            name: ingredient.name,
                            category: ingredient.category || '',
                            unit: ingredient.unit || 'g',
                            package_weight: ingredient.package_weight || '',
                            cost: ingredient.cost || '',
                            stock: ingredient.stock || '',
                            min_stock: ingredient.min_stock || '',
                            is_flour: !!ingredient.is_flour,
                            is_active: !!ingredient.is_active
                        };
                        this.showModal = true;
                    },

                    closeAllEdits() {
                        // Not used anymore as we use a modal
                    },

                    updateSearch() {
                        const q = this.search.trim().toLowerCase();
                        const qNorm = q.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                        
                        let visibleCount = 0;
                        
                        document.querySelectorAll('.searchable-item').forEach(el => {
                            const name = (el.getAttribute('data-search-name') || '').toLowerCase();
                            const category = (el.getAttribute('data-search-category') || '').toLowerCase();
                            const nameNorm = name.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                            const categoryNorm = category.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                            
                            let matches = true;
                            if (q) {
                                matches = name.includes(q) || nameNorm.includes(qNorm) || 
                                          category.includes(q) || categoryNorm.includes(qNorm);
                            }
                            
                            if (matches) visibleCount++;
                        });
                        
                        this.showNoResults = q !== '' && visibleCount === 0;
                    },

                    matches(el) {
                        const q = this.search.trim().toLowerCase();
                        if (!q) return true;
                        
                        const name = (el.getAttribute('data-search-name') || '').toLowerCase();
                        const category = (el.getAttribute('data-search-category') || '').toLowerCase();
                        const nameNorm = name.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                        const categoryNorm = category.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                        const qNorm = q.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                        
                        if (name.includes(q) || nameNorm.includes(qNorm)) return true;
                        if (category.includes(q) || categoryNorm.includes(qNorm)) return true;
                        
                        return false;
                    }
                }));
            });
        </script>
    @endpush
@endsection