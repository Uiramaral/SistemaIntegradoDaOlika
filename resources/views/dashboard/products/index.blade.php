@extends('dashboard.layouts.app')

@section('page_title', 'Produtos')
@section('page_subtitle', 'Gerenciamento de produtos')

@section('page_actions')
    {{-- Botões removidos conforme solicitado --}}
@endsection

@section('content')
<div class="bg-card rounded-xl border border-border animate-fade-in" 
     id="products-page"
     x-data="productsLiveSearch('{{ request('q') ?? '' }}')">
    <!-- Card Header: Busca e Botão -->
    <div class="p-4 sm:p-6 border-b border-border">
        <form method="GET" action="{{ route('dashboard.products.index') }}" class="flex flex-col lg:flex-row lg:items-center gap-3 lg:gap-3">
            <div class="relative w-full lg:flex-1 lg:min-w-[200px] lg:max-w-sm order-1">
                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground pointer-events-none"></i>
                <input type="text"
                       name="q"
                       x-model="search"
                       @input.debounce.500ms="$event.target.form && $event.target.form.submit()"
                       placeholder="Buscar produto..."
                       class="form-input pl-10 h-10 bg-muted/30 border-transparent focus:bg-white transition-all text-sm rounded-lg w-full"
                       autocomplete="off">
            </div>
            <a href="{{ route('dashboard.products.create') }}" class="btn-primary gap-2 h-10 px-4 rounded-lg shadow-sm shrink-0 w-full lg:w-auto lg:ml-auto justify-center order-2">
                <i data-lucide="plus" class="h-4 w-4 text-white"></i>
                <span class="font-bold text-white text-sm">Novo Produto</span>
            </a>
        </form>
    </div>

    <!-- Products Grid -->
    <div class="p-4 sm:p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-4">
            @forelse($products as $product)
                @php
                    $name = $product->name ?? 'Sem nome';
                    

                    
                    $categoryName = $product->category->name ?? 'Sem categoria';
                    $price = (float)($product->price ?? 0);
                    $stock = (int)($product->stock ?? $product->inventory ?? 0);
                    $isActive = $product->is_active ?? true;
                    $searchName = mb_strtolower($name, 'UTF-8');
                    $searchCategory = mb_strtolower($categoryName, 'UTF-8');
                @endphp
                <div class="product-card border border-border rounded-xl p-4 hover:shadow-md transition-all overflow-hidden"
                     data-search-name="{{ $searchName }}"
                     data-search-category="{{ $searchCategory }}"
                     x-show="matchesCard($el)">
                    <!-- Header: Avatar, Name, Category, Actions -->
                    <div class="flex items-start justify-between mb-2">
                        <div class="flex items-center gap-2 min-w-0 flex-1">

                            <div class="min-w-0 flex-1">
                                <a href="{{ route('dashboard.products.edit', $product->id) }}" class="block group">
                                    <h3 class="font-semibold text-foreground text-sm group-hover:text-primary transition-colors truncate">
                                        {{ $name }}
                                    </h3>
                                    <p class="text-xs text-muted-foreground mt-0.5 truncate">{{ $categoryName }}</p>
                                </a>
                            </div>
                        </div>
                        <div class="flex items-center gap-0.5 flex-shrink-0">
                            <a href="{{ route('dashboard.products.edit', $product->id) }}" 
                               class="inline-flex items-center justify-center h-7 w-7 rounded-md hover:bg-muted transition-colors text-muted-foreground hover:text-foreground"
                               title="Editar">
                                <i data-lucide="edit" class="h-3.5 w-3.5"></i>
                            </a>
                            <form action="{{ route('dashboard.products.destroy', $product->id) }}" method="POST" class="inline" onsubmit="return confirm('Tem certeza que deseja excluir este produto?');">
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

                    <!-- Info: Estoque — desktop (lg+) -->
                    <div class="mb-2 hidden lg:block">
                        <div class="flex items-center gap-1.5 text-xs text-muted-foreground min-w-0">
                            <i data-lucide="package" class="w-3.5 h-3.5 flex-shrink-0"></i>
                            <span class="truncate {{ $stock < 50 ? 'text-red-600 font-semibold' : '' }}">{{ $stock }} un</span>
                        </div>
                    </div>

                    <!-- Footer: Preço, Estoque, Status -->
                    <div class="pt-2 border-t border-border">
                        <!-- Desktop: Preço | Estoque em linha -->
                        <div class="hidden lg:flex items-center justify-between">
                            <div>
                                <p class="text-[10px] text-muted-foreground uppercase tracking-wide">Preço</p>
                                <p class="text-sm font-bold text-primary mt-0.5">R$ {{ number_format($price, 2, ',', '.') }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-[10px] text-muted-foreground uppercase tracking-wide">Estoque</p>
                                <p class="text-xs font-medium {{ $stock < 50 ? 'text-red-600' : 'text-foreground' }} mt-0.5">{{ $stock }} un</p>
                            </div>
                        </div>
                        <!-- Mobile/tablet: Preço · Estoque · Status na MESMA LINHA -->
                        <div class="flex items-stretch gap-2 lg:hidden">
                            <div class="min-w-0 flex-1 overflow-hidden">
                                <p class="text-xs text-muted-foreground/80 uppercase tracking-wide truncate">Preço</p>
                                <p class="text-sm font-bold text-primary truncate mt-0.5">R$ {{ number_format($price, 2, ',', '.') }}</p>
                            </div>
                            <span class="text-muted-foreground/40 self-center shrink-0 text-sm">·</span>
                            <div class="min-w-0 flex-1 overflow-hidden">
                                <p class="text-xs text-muted-foreground/80 uppercase tracking-wide truncate">Estoque</p>
                                <p class="text-sm font-medium {{ $stock < 50 ? 'text-red-600' : 'text-foreground' }} truncate mt-0.5">{{ $stock }} un</p>
                            </div>
                            <span class="text-muted-foreground/40 self-center shrink-0 text-sm">·</span>
                            <div class="min-w-0 flex-1 overflow-hidden text-right">
                                <p class="text-xs text-muted-foreground/80 uppercase tracking-wide truncate">Status</p>
                                <p class="text-xs font-medium {{ $isActive ? 'text-green-600' : 'text-muted-foreground' }} truncate mt-0.5">{{ $isActive ? 'Ativo' : 'Inativo' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center text-muted-foreground py-12">
                    <div class="flex flex-col items-center gap-2">
                        <i data-lucide="inbox" class="w-12 h-12 opacity-20"></i>
                        <p class="text-sm">Nenhum produto cadastrado</p>
                    </div>
                </div>
            @endforelse
            @if($products->count() > 0)
                <div class="product-filter-no-results col-span-full text-center text-muted-foreground py-8"
                     x-show="search && showNoResults"
                     x-cloak
                     x-transition>
                    <div class="flex flex-col items-center gap-2">
                        <i data-lucide="search-x" class="w-10 h-10 opacity-40"></i>
                        <p class="text-sm">Nenhum produto encontrado para "<span x-text="search"></span>"</p>
                    </div>
                </div>
            @endif
        </div>

    <!-- Pagination -->
    @if(isset($products) && method_exists($products, 'links') && $products->hasPages())
        <div class="px-4 sm:px-6 py-3 sm:py-4 border-t border-border bg-muted/20">
            <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-center gap-3 sm:gap-4">
                <p class="text-xs text-muted-foreground font-medium order-2 sm:order-1 lg:order-1 text-center sm:text-left">
                    Mostrando <span class="font-bold text-foreground">{{ $products->firstItem() ?? $products->count() }}</span> de <span class="font-bold text-foreground">{{ $products->total() }}</span> produtos
                </p>
                <div class="flex items-center gap-2 order-1 sm:order-2 lg:order-2">
                    @if($products->onFirstPage())
                        <button class="px-3 sm:px-4 py-2 rounded-lg border border-border bg-white text-xs font-semibold text-muted-foreground disabled:opacity-40 disabled:cursor-not-allowed transition-all inline-flex items-center" disabled>
                            <i data-lucide="chevron-left" class="w-4 h-4"></i>
                            <span class="ml-1 hidden sm:inline">Anterior</span>
                        </button>
                    @else
                        <a href="{{ $products->appends(request()->query())->previousPageUrl() }}" class="px-3 sm:px-4 py-2 rounded-lg border border-border bg-white text-xs font-semibold text-foreground hover:bg-muted hover:border-primary/30 transition-all inline-flex items-center">
                            <i data-lucide="chevron-left" class="w-4 h-4"></i>
                            <span class="ml-1 hidden sm:inline">Anterior</span>
                        </a>
                    @endif
                    
                    @if($products->hasMorePages())
                        <a href="{{ $products->appends(request()->query())->nextPageUrl() }}" class="px-3 sm:px-4 py-2 rounded-lg border border-border bg-white text-xs font-semibold text-foreground hover:bg-muted hover:border-primary/30 transition-all inline-flex items-center">
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
    @elseif(isset($products))
        <div class="px-4 sm:px-6 py-3 sm:py-4 border-t border-border bg-muted/20">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 sm:gap-4">
                <p class="text-xs text-muted-foreground font-medium text-center sm:text-left">
                    Mostrando <span class="font-bold text-foreground">{{ $products->count() }}</span> de <span class="font-bold text-foreground">{{ $products->total() ?? $products->count() }}</span> produtos
                </p>
            </div>
        </div>
    @endif
</div>

@push('styles')
<style>[x-cloak]{display:none!important}</style>
@endpush

@push('scripts')
<script>
document.addEventListener('alpine:init', function () {
    Alpine.data('productsLiveSearch', function (initialQ) {
        return {
            search: (typeof initialQ === 'string' ? initialQ : '') || '',
            showNoResults: false,

            init: function () {
                var self = this;
                function updateNoResults() {
                    self.$nextTick(function () {
                        var root = document.getElementById('products-page');
                        var cards = root ? root.querySelectorAll('.product-card') : [];
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
});
</script>
@endpush
@endsection
