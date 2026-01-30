@extends('dashboard.layouts.app')

@section('page_title', 'Receitas')
@section('page_subtitle', 'Gerenciamento de receitas')

@section('page_actions')
    <div class="flex gap-2">
        <button type="button" 
                @click="$dispatch('open-print-queue')"
                class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-purple-600 text-white hover:bg-purple-700 h-10 px-4 py-2 gap-2">
            <i data-lucide="printer" class="h-4 w-4"></i>
            Fila de Impressão
        </button>
        <a href="{{ route('dashboard.producao.receitas.create') }}" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2 gap-2">
            <i data-lucide="plus" class="h-4 w-4"></i>
            Nova Receita
        </a>
    </div>
@endsection

@section('content')
<div class="bg-card rounded-xl border border-border animate-fade-in overflow-hidden max-w-full" 
     id="recipes-page"
     x-data="recipesLiveSearch('{{ request('q') ?? '' }}')">
    <!-- Card Header: Busca e Botão -->
    <div class="p-4 sm:p-6 border-b border-border">
        <form method="GET" action="{{ route('dashboard.producao.receitas.index') }}" class="flex flex-col lg:flex-row lg:items-center gap-3 lg:gap-3">
            <div class="relative w-full lg:flex-1 lg:min-w-[200px] lg:max-w-sm order-1">
                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground pointer-events-none"></i>
                <input type="text"
                       name="q"
                       x-model="search"
                       @input.debounce.500ms="$event.target.form && $event.target.form.submit()"
                       placeholder="Buscar receita..."
                       class="form-input pl-10 h-10 bg-muted/30 border-transparent focus:bg-white transition-all text-sm rounded-lg w-full"
                       autocomplete="off">
            </div>
            <a href="{{ route('dashboard.producao.receitas.create') }}" class="btn-primary gap-2 h-10 px-4 rounded-lg shadow-sm shrink-0 w-full lg:w-auto lg:ml-auto justify-center order-2">
                <i data-lucide="plus" class="h-4 w-4 text-white"></i>
                <span class="font-bold text-white text-sm">Nova Receita</span>
            </a>
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
                    if (!$initials) $initials = strtoupper(substr($name, 0, 2));
                    
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
                     data-search-name="{{ $searchName }}"
                     data-search-category="{{ $searchCategory }}"
                     x-show="matchesCard($el)">
                    <!-- Header: Avatar, Name, Category, Actions -->
                    <div class="flex items-start justify-between mb-2">
                        <div class="flex items-center gap-2 min-w-0 flex-1">
                            <div class="w-10 h-10 rounded-full {{ $avatarColor }} flex items-center justify-center font-bold text-xs flex-shrink-0">
                                {{ $initials }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <a href="{{ route('dashboard.producao.receitas.edit', $recipe->id) }}" class="block group">
                                    <h3 class="font-semibold text-foreground text-sm group-hover:text-primary transition-colors truncate">
                                        {{ $name }}
                                    </h3>
                                    <p class="text-xs text-muted-foreground mt-0.5 truncate">{{ $category }}</p>
                                </a>
                            </div>
                        </div>
                        <div class="flex items-center gap-0.5 flex-shrink-0">
                            <a href="{{ route('dashboard.producao.receitas.edit', $recipe->id) }}" 
                               class="inline-flex items-center justify-center h-7 w-7 rounded-md hover:bg-muted transition-colors text-muted-foreground hover:text-foreground"
                               title="Editar">
                                <i data-lucide="edit" class="h-3.5 w-3.5"></i>
                            </a>
                            <form action="{{ route('dashboard.producao.receitas.destroy', $recipe->id) }}" method="POST" class="inline" onsubmit="return confirm('Tem certeza que deseja excluir esta receita?');">
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
                                <p class="text-sm font-bold text-primary mt-0.5">R$ {{ number_format($cost, 2, ',', '.') }}</p>
                            </div>
                            @if($finalPrice)
                            <div class="text-right">
                                <p class="text-[10px] text-muted-foreground uppercase tracking-wide">Preço venda</p>
                                <p class="text-xs font-medium text-green-600 mt-0.5">R$ {{ number_format($finalPrice, 2, ',', '.') }}</p>
                            </div>
                            @else
                            <div class="text-right">
                                <p class="text-[10px] text-muted-foreground uppercase tracking-wide">Rendimento</p>
                                <p class="text-xs font-medium text-foreground mt-0.5">{{ number_format($totalWeight, 0, ',', '.') }}g</p>
                            </div>
                            @endif
                        </div>
                        <!-- Mobile/tablet: Custo · Rendimento · Preço na MESMA LINHA -->
                        <div class="flex items-stretch gap-2 lg:hidden">
                            <div class="min-w-0 flex-1 overflow-hidden">
                                <p class="text-xs text-muted-foreground/80 uppercase tracking-wide truncate">Custo</p>
                                <p class="text-sm font-bold text-primary truncate mt-0.5">R$ {{ number_format($cost, 2, ',', '.') }}</p>
                            </div>
                            <span class="text-muted-foreground/40 self-center shrink-0 text-sm">·</span>
                            <div class="min-w-0 flex-1 overflow-hidden">
                                <p class="text-xs text-muted-foreground/80 uppercase tracking-wide truncate">Peso</p>
                                <p class="text-sm font-medium text-foreground truncate mt-0.5">{{ number_format($totalWeight, 0, ',', '.') }}g</p>
                            </div>
                            @if($finalPrice)
                            <span class="text-muted-foreground/40 self-center shrink-0 text-sm">·</span>
                            <div class="min-w-0 flex-1 overflow-hidden text-right">
                                <p class="text-xs text-muted-foreground/80 uppercase tracking-wide truncate">Venda</p>
                                <p class="text-sm font-medium text-green-600 truncate mt-0.5">R$ {{ number_format($finalPrice, 2, ',', '.') }}</p>
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
                     x-show="search && showNoResults"
                     x-cloak
                     x-transition>
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
                    Mostrando <span class="font-bold text-foreground">{{ $recipes->firstItem() ?? $recipes->count() }}</span> de <span class="font-bold text-foreground">{{ $recipes->total() }}</span> receitas
                </p>
                <div class="flex items-center gap-2 order-1 sm:order-2 lg:order-2">
                    @if($recipes->onFirstPage())
                        <button class="px-3 sm:px-4 py-2 rounded-lg border border-border bg-white text-xs font-semibold text-muted-foreground disabled:opacity-40 disabled:cursor-not-allowed transition-all inline-flex items-center" disabled>
                            <i data-lucide="chevron-left" class="w-4 h-4"></i>
                            <span class="ml-1 hidden sm:inline">Anterior</span>
                        </button>
                    @else
                        <a href="{{ $recipes->appends(request()->query())->previousPageUrl() }}" class="px-3 sm:px-4 py-2 rounded-lg border border-border bg-white text-xs font-semibold text-foreground hover:bg-muted hover:border-primary/30 transition-all inline-flex items-center">
                            <i data-lucide="chevron-left" class="w-4 h-4"></i>
                            <span class="ml-1 hidden sm:inline">Anterior</span>
                        </a>
                    @endif
                    
                    @if($recipes->hasMorePages())
                        <a href="{{ $recipes->appends(request()->query())->nextPageUrl() }}" class="px-3 sm:px-4 py-2 rounded-lg border border-border bg-white text-xs font-semibold text-foreground hover:bg-muted hover:border-primary/30 transition-all inline-flex items-center">
                            <span class="mr-1 hidden sm:inline">Próximo</span>
                            <i data-lucide="chevron-right" class="w-4 h-4"></i>
                        </a>
                    @else
                        <button class="px-3 sm:px-4 py-2 rounded-lg border border-border bg-white text-xs font-semibold text-muted-foreground disabled:opacity-40 disabled:cursor-not-allowed transition-all inline-flex items-center" disabled>
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
                    Mostrando <span class="font-bold text-foreground">{{ $recipes->count() }}</span> de <span class="font-bold text-foreground">{{ $recipes->total() ?? $recipes->count() }}</span> receitas
                </p>
            </div>
        </div>
    @endif
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
});
</script>
@endpush
@endsection
