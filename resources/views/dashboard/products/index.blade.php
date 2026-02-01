@extends('dashboard.layouts.app')

@section('page_title', 'Produtos')
@section('page_subtitle', 'Gerenciamento de produtos')

@section('page_actions')
    {{-- Button moved to main card --}}
@endsection

@section('content')
    <style>
        [x-cloak] {
            display: none !important;
        }

        /* Enforce mobile consistency exactly like Categories */
        @media (max-width: 640px) {
            #products-grid>.product-card {
                width: 100% !important;
                max-width: 100% !important;
                box-sizing: border-box !important;
            }

            #products-page {
                overflow-x: hidden !important;
                width: 100% !important;
                max-width: 100vw !important;
            }
        }
    </style>

    <div class="bg-card rounded-xl border border-border animate-fade-in w-full overflow-x-hidden" id="products-page"
        x-data="productsLiveSearch('{{ request('q') ?? '' }}')">
        {{-- Header: Search & Actions --}}
        <div class="p-4 border-b border-border flex flex-col sm:flex-row gap-4 justify-between w-full">
            <div class="relative flex-1 w-full max-w-full sm:max-w-md">
                <i data-lucide="search"
                    class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground pointer-events-none"></i>
                <input type="text" name="q" x-model="search" @input.debounce.300ms="updateSearch()"
                    placeholder="Buscar produto por nome..." class="form-input pl-10 h-10 w-full" autocomplete="off">
            </div>

            <a href="{{ route('dashboard.products.create') }}"
                class="btn-primary gap-2 h-10 px-4 rounded-lg shadow-sm w-full sm:w-auto shrink-0 font-bold inline-flex items-center justify-center">
                <i data-lucide="plus" class="h-4 w-4 text-white"></i>
                <span>Novo Produto</span>
            </a>
        </div>

        {{-- Products Grid --}}
        <div id="products-grid" class="p-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 w-full">
            @forelse($products as $product)
                @php
                    $name = $product->name ?? 'Sem nome';
                    $categoryName = $product->category->name ?? 'Sem categoria';
                    $price = (float) ($product->price ?? 0);
                    $stock = (int) ($product->stock ?? $product->inventory ?? 0);
                    $isActive = $product->is_active ?? true;
                    $searchName = mb_strtolower($name, 'UTF-8');
                    $searchCategory = mb_strtolower($categoryName, 'UTF-8');
                    $sku = $product->sku ? mb_strtolower($product->sku, 'UTF-8') : '';
                @endphp
                <div class="product-card searchable-item border border-border rounded-xl p-4 hover:shadow-md transition-all cursor-pointer group flex flex-col justify-between bg-white w-full min-w-0"
                    data-search-name="{{ $searchName }}" data-search-category="{{ $searchCategory }}"
                    data-search-sku="{{ $sku }}" x-show="matches($el)" @click="openProductModal({{ $product->id }})">

                    {{-- Header: Image, Name, Category, Actions --}}
                    <div class="flex items-start justify-between mb-4 gap-3">
                        <div class="flex items-center gap-3 min-w-0 flex-1">
                            {{-- Image --}}
                            <div
                                class="w-12 h-12 rounded-xl bg-muted flex items-center justify-center overflow-hidden shrink-0 border border-border/50">
                                @if($product->cover_image)
                                    <img src="{{ Storage::url($product->cover_image) }}" alt="{{ $name }}"
                                        class="w-full h-full object-cover">
                                @elseif($product->image_url)
                                    <img src="{{ $product->image_url }}" alt="{{ $name }}" class="w-full h-full object-cover">
                                @else
                                    <i data-lucide="package" class="w-6 h-6 text-muted-foreground opacity-50"></i>
                                @endif
                            </div>

                            <div class="min-w-0 flex-1">
                                <h3 class="font-semibold text-foreground text-sm sm:text-base truncate leading-tight"
                                    title="{{ $name }}">{{ $name }}</h3>
                                <p class="text-xs text-muted-foreground mt-0.5 truncate">{{ $categoryName }}</p>
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="flex items-center gap-1 shrink-0">
                            <a href="{{ route('dashboard.products.edit', $product->id) }}" @click.stop
                                class="inline-flex items-center justify-center h-8 w-8 rounded-lg hover:bg-muted transition-colors text-muted-foreground hover:text-primary"
                                title="Editar">
                                <i data-lucide="edit" class="h-4 w-4"></i>
                            </a>
                        </div>
                    </div>

                    {{-- Footer: Price, Sales, Stock --}}
                    <div class="pt-3 border-t border-border mt-auto">
                        <div class="flex items-center justify-between gap-2">
                            <div class="min-w-0">
                                <p class="text-[10px] text-muted-foreground uppercase tracking-widest font-semibold truncate">
                                    PREÇO</p>
                                <p class="text-sm font-bold text-primary mt-0.5 truncate">R$
                                    {{ number_format($price, 2, ',', '.') }}</p>
                            </div>

                            <div class="text-center min-w-0">
                                <p class="text-[10px] text-muted-foreground uppercase tracking-widest font-semibold truncate">
                                    VENDAS</p>
                                <p class="text-sm font-bold text-foreground mt-0.5">
                                    {{ (int) ($product->order_items_sum_quantity ?? 0) }}</p>
                            </div>

                            <div class="text-right min-w-0">
                                <p class="text-[10px] text-muted-foreground uppercase tracking-widest font-semibold truncate">
                                    ESTOQUE</p>
                                <p
                                    class="text-sm font-medium mt-0.5 {{ $stock < 5 ? 'text-destructive' : 'text-foreground' }} truncate">
                                    {{ $stock }} un
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-12 text-center text-muted-foreground">
                    <div class="bg-muted/30 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="package-open" class="w-8 h-8 opacity-50"></i>
                    </div>
                    <h3 class="text-lg font-medium text-foreground mb-1">Nenhum produto encontrado</h3>
                    <p class="mb-4">Comece adicionando novos produtos ao seu catálogo.</p>
                    <a href="{{ route('dashboard.products.create') }}" class="btn-primary inline-flex">
                        Adicionar Produto
                    </a>
                </div>
            @endforelse
        </div>

        {{-- No Results State --}}
        <div class="text-center text-muted-foreground py-12" x-show="search && showNoResults" x-cloak x-transition>
            <div class="flex flex-col items-center gap-2">
                <i data-lucide="search-x" class="w-10 h-10 opacity-40"></i>
                <p class="text-sm">Nenhum produto encontrado para "<span x-text="search"></span>"</p>
            </div>
        </div>

        {{-- Pagination --}}
        @if(isset($products) && method_exists($products, 'links') && $products->hasPages())
            <div class="px-4 sm:px-6 py-3 sm:py-4 border-t border-border bg-muted/20">
                {{ $products->withQueryString()->links() }}
            </div>
        @endif

        {{-- Product Details Modal --}}
        <div x-show="modalOpen" class="fixed inset-0 z-50 flex items-center justify-center px-4 sm:px-6" x-cloak>
            {{-- Backdrop --}}
            <div x-show="modalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="closeModal()"></div>

            {{-- Modal Panel --}}
            <div x-show="modalOpen" x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0" x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                class="bg-background rounded-xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col relative z-50 border border-border">

                {{-- Header --}}
                <div class="flex items-center justify-between p-4 border-b border-border bg-card/50">
                    <h2 class="text-lg font-semibold" x-text="selectedProduct?.name || 'Detalhes do Produto'"></h2>
                    <button @click="closeModal()"
                        class="text-muted-foreground hover:text-foreground p-1 rounded-lg hover:bg-muted transition-colors">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                {{-- Body (Scrollable) --}}
                <div class="overflow-y-auto p-0 flex-1 relative">
                    {{-- Loading State --}}
                    <div x-show="loading" class="absolute inset-0 flex items-center justify-center bg-background/80 z-10">
                        <div class="flex flex-col items-center gap-2">
                            <div class="w-8 h-8 border-4 border-primary/30 border-t-primary rounded-full animate-spin">
                            </div>
                            <span class="text-sm text-muted-foreground">Carregando...</span>
                        </div>
                    </div>

                    <div x-show="!loading && selectedProduct" class="p-4 sm:p-6 space-y-6">
                        {{-- Header Info --}}
                        <div class="flex flex-col sm:flex-row gap-6">
                            {{-- Image --}}
                            <div class="w-full sm:w-1/3 shrink-0">
                                <div class="aspect-square rounded-lg bg-muted overflow-hidden border border-border">
                                    <template x-if="selectedProduct?.cover_image">
                                        <img :src="getProductImageUrl(selectedProduct?.cover_image)"
                                            :alt="selectedProduct?.name" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!selectedProduct?.cover_image">
                                        <div class="w-full h-full flex items-center justify-center text-muted-foreground">
                                            <i data-lucide="package" class="w-12 h-12 opacity-50"></i>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            {{-- Basic Info --}}
                            <div class="flex-1 space-y-4">
                                <div>
                                    <span class="text-xs font-medium text-muted-foreground uppercase tracking-wider"
                                        x-text="selectedProduct?.category?.name || 'Sem categoria'"></span>
                                    <h3 class="text-xl font-bold text-foreground" x-text="selectedProduct?.name"></h3>
                                    <p class="text-sm text-muted-foreground mt-1" x-show="selectedProduct?.sku">SKU: <span
                                            x-text="selectedProduct?.sku"></span></p>
                                </div>

                                <div class="flex items-end gap-2">
                                    <span class="text-2xl font-bold text-primary"
                                        x-text="formatCurrency(selectedProduct?.price)"></span>
                                    <span class="text-sm text-muted-foreground mb-1">/ unidade</span>
                                </div>

                                <div class="flex flex-wrap gap-2">
                                    <span class="px-2 py-1 rounded-md text-xs font-medium"
                                        :class="selectedProduct?.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'">
                                        <span x-text="selectedProduct?.is_active ? 'Ativo' : 'Inativo'"></span>
                                    </span>
                                    <span class="px-2 py-1 rounded-md text-xs font-medium"
                                        :class="(selectedProduct?.stock || 0) < 5 ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800'">
                                        Estoque: <span x-text="selectedProduct?.stock || 0"></span>
                                    </span>
                                    <span class="px-2 py-1 rounded-md text-xs font-medium bg-indigo-100 text-indigo-800">
                                        Vendas: <span x-text="selectedProduct?.sales_count || 0"></span>
                                    </span>
                                    <template x-if="selectedProduct?.gluten_free">
                                        <span class="px-2 py-1 rounded-md text-xs font-medium bg-amber-100 text-amber-800">
                                            Sem Glúten
                                        </span>
                                    </template>
                                </div>
                            </div>
                        </div>

                        {{-- Description --}}
                        <div x-show="selectedProduct?.description">
                            <h4 class="text-sm font-semibold mb-2">Descrição</h4>
                            <div class="text-sm text-muted-foreground whitespace-pre-line bg-muted/30 p-3 rounded-lg border border-border/50"
                                x-text="selectedProduct?.description"></div>
                        </div>

                        {{-- Variants --}}
                        <div x-show="selectedProduct?.variants && selectedProduct?.variants.length > 0">
                            <h4 class="text-sm font-semibold mb-2">Variações</h4>
                            <div class="border rounded-lg overflow-hidden">
                                <table class="w-full text-sm text-left">
                                    <thead class="bg-muted/50 border-b border-border">
                                        <tr>
                                            <th class="p-2 font-medium">Nome</th>
                                            <th class="p-2 font-medium">Preço</th>
                                            <th class="p-2 font-medium text-right">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-border">
                                        <template x-for="variant in selectedProduct?.variants" :key="variant.id">
                                            <tr class="hover:bg-muted/20">
                                                <td class="p-2" x-text="variant.name"></td>
                                                <td class="p-2 font-medium" x-text="formatCurrency(variant.price)"></td>
                                                <td class="p-2 text-right">
                                                    <span class="w-2 h-2 rounded-full inline-block"
                                                        :class="variant.is_active ? 'bg-green-500' : 'bg-gray-300'"
                                                        :title="variant.is_active ? 'Ativo' : 'Inativo'"></span>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Allergens --}}
                        <div x-show="selectedProduct?.allergens && selectedProduct?.allergens.length > 0">
                            <h4 class="text-sm font-semibold mb-2">Alérgenos</h4>
                            <div class="flex flex-wrap gap-2">
                                <template x-for="allergen in selectedProduct?.allergens" :key="allergen.id">
                                    <span
                                        class="px-2 py-1 rounded-full text-xs bg-orange-50 text-orange-700 border border-orange-100"
                                        x-text="allergen.name"></span>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="p-4 border-t border-border bg-card/50 flex justify-end gap-3">
                    <button @click="closeModal()" class="btn-outline">
                        Fechar
                    </button>
                    <a :href="'{{ route('dashboard.products.edit', '') }}/' + selectedProduct?.id" class="btn-primary gap-2"
                        x-show="selectedProduct?.id">
                        <i data-lucide="pencil" class="w-4 h-4"></i>
                        Editar Produto
                    </a>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('alpine:init', function () {
                Alpine.data('productsLiveSearch', function (initialQ) {
                    return {
                        search: (typeof initialQ === 'string' ? initialQ : '') || '',
                        showNoResults: false,
                        modalOpen: false,
                        loading: false,
                        selectedProduct: null,

                        init: function () {
                            this.updateSearch();
                            this.$watch('search', () => this.updateSearch());

                            // Fechar modal com ESC
                            window.addEventListener('keydown', (e) => {
                                if (e.key === 'Escape' && this.modalOpen) this.closeModal();
                            });
                        },

                        updateSearch() {
                            const q = this.search.trim().toLowerCase();
                            const qNorm = q.normalize('NFD').replace(/[\u0300-\u036f]/g, '');

                            let visibleCount = 0;

                            document.querySelectorAll('.searchable-item').forEach(el => {
                                const name = (el.getAttribute('data-search-name') || '').toLowerCase();
                                const category = (el.getAttribute('data-search-category') || '').toLowerCase();
                                const sku = (el.getAttribute('data-search-sku') || '').toLowerCase();
                                const nameNorm = name.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                                const categoryNorm = category.normalize('NFD').replace(/[\u0300-\u036f]/g, '');

                                let matches = true;
                                if (q) {
                                    matches = name.includes(q) || nameNorm.includes(qNorm) ||
                                        category.includes(q) || categoryNorm.includes(qNorm) ||
                                        sku.includes(q);
                                }

                                if (matches) visibleCount++;
                            });

                            this.showNoResults = q !== '' && visibleCount === 0;
                        },

                        matches: function (el) {
                            var q = this.search.trim().toLowerCase();
                            if (!q) return true;
                            var name = (el.getAttribute('data-search-name') || '').toLowerCase();
                            var category = (el.getAttribute('data-search-category') || '').toLowerCase();
                            var sku = (el.getAttribute('data-search-sku') || '').toLowerCase();
                            var nameNorm = name.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                            var categoryNorm = category.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                            var qNorm = q.normalize('NFD').replace(/[\u0300-\u036f]/g, '');

                            if (name.includes(q) || nameNorm.includes(qNorm)) return true;
                            if (category.includes(q) || categoryNorm.includes(qNorm)) return true;
                            if (sku.includes(q)) return true;
                            return false;
                        },

                        async openProductModal(productId) {
                            this.modalOpen = true;
                            this.loading = true;
                            this.selectedProduct = null;

                            // Prevent background scrolling
                            document.body.style.overflow = 'hidden';

                            try {
                                const response = await fetch(`{{ route('dashboard.products.show', '') }}/${productId}`, {
                                    headers: {
                                        'Accept': 'application/json',
                                        'X-Requested-With': 'XMLHttpRequest'
                                    }
                                });

                                if (!response.ok) throw new Error('Erro ao carregar produto');

                                const data = await response.json();
                                this.selectedProduct = data;
                            } catch (error) {
                                console.error('Erro:', error);
                                // alert('Não foi possível carregar os detalhes do produto.');
                                // Keep modal open but maybe show error state inside?
                                // For now, close it
                                this.closeModal();
                            } finally {
                                this.loading = false;
                            }
                        },

                        closeModal() {
                            this.modalOpen = false;
                            // Restore background scrolling
                            document.body.style.overflow = '';

                            setTimeout(() => {
                                this.selectedProduct = null;
                            }, 300);
                        },

                        formatCurrency(value) {
                            return new Intl.NumberFormat('pt-BR', {
                                style: 'currency',
                                currency: 'BRL'
                            }).format(value || 0);
                        },

                        getProductImageUrl(path) {
                            if (!path) return '';
                            // Se já for uma URL completa (ex: http...), retorna ela.
                            if (path.startsWith('http')) return path;
                            // Caso contrário, usa o helper de storage do Laravel
                            return `{{ Storage::url('') }}${path}`;
                        }
                    };
                });
            });
        </script>
    @endpush
@endsection