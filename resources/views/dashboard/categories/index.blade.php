@extends('dashboard.layouts.app')

@section('page_title', 'Categorias')
@section('page_subtitle', 'Gerenciamento de categorias')

@section('page_actions')
    <div class="flex items-center gap-2">
        <button class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
            </svg>
        </button>
        <button class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"></path>
            </svg>
        </button>
    </div>
    <a href="{{ route('dashboard.categories.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors text-sm font-medium">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M5 12h14"></path>
            <path d="M12 5v14"></path>
        </svg>
        Adicionar categoria
    </a>
@endsection

@section('content')
<div class="bg-card rounded-xl border border-border animate-fade-in" 
     id="categories-page"
     x-data="categoriesLiveSearch('{{ request('q') ?? '' }}')">
    <div class="p-4 border-b border-border flex flex-col sm:flex-row gap-4 justify-between">
        <div class="relative flex-1 max-w-md">
            <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground pointer-events-none"></i>
            <input
                type="text"
                x-model="search"
                @input="filterCategories()"
                placeholder="Buscar categoria..."
                class="form-input pl-10"
                autocomplete="off"
            />
        </div>
        <a href="{{ route('dashboard.categories.create') }}" class="btn-primary gap-2 h-9 px-4">
            <i data-lucide="plus" class="h-4 w-4"></i>
            Nova Categoria
        </a>
    </div>

    <div id="categories-grid" class="p-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($categories as $category)
            @php
                $productsCount = $category->products_count ?? 0;
            @endphp
            <div class="category-card border border-border rounded-xl p-4 hover:shadow-md transition-all"
                 data-search-name="{{ mb_strtolower($category->name, 'UTF-8') }}"
                 data-search-description="{{ mb_strtolower($category->description ?? '', 'UTF-8') }}">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center">
                            <i data-lucide="package" class="h-6 w-6 text-primary"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold">{{ $category->name }}</h3>
                            <p class="text-sm text-muted-foreground">{{ $productsCount }} produtos</p>
                        </div>
                    </div>
                    <div class="flex gap-1">
                        <a href="{{ route('dashboard.categories.edit', $category) }}" class="inline-flex items-center justify-center h-8 w-8 rounded-md hover:bg-muted">
                            <i data-lucide="edit" class="h-4 w-4"></i>
                        </a>
                        <button type="button" class="inline-flex items-center justify-center h-8 w-8 rounded-md hover:bg-muted text-destructive">
                            <i data-lucide="trash-2" class="h-4 w-4"></i>
                        </button>
                    </div>
                </div>

                <p class="text-sm text-muted-foreground">{{ $category->description ?? 'Sem descrição' }}</p>
            </div>
        @endforeach
        @if($categories->count() > 0)
            <div class="category-filter-no-results col-span-full text-center text-muted-foreground py-8"
                 x-show="search && showNoResults"
                 x-cloak
                 x-transition>
                <div class="flex flex-col items-center gap-2">
                    <i data-lucide="search-x" class="w-10 h-10 opacity-40"></i>
                    <p class="text-sm">Nenhuma categoria encontrada para "<span x-text="search"></span>"</p>
                </div>
            </div>
        @else
            <div class="col-span-full text-center text-muted-foreground py-8">
                Nenhuma categoria cadastrada.
            </div>
        @endif
    </div>
</div>

@push('styles')
<style>[x-cloak]{display:none!important}</style>
@endpush

@push('scripts')
<script>
document.addEventListener('alpine:init', function () {
    Alpine.data('categoriesLiveSearch', function (initialQ) {
        return {
            search: (typeof initialQ === 'string' ? initialQ : '') || '',
            showNoResults: false,
            loading: false,
            searchTimeout: null,
            allCategories: [],

            init: function () {
                this.saveInitialCategories();
            },

            saveInitialCategories: function () {
                var grid = document.getElementById('categories-grid');
                if (!grid) return;
                var cards = grid.querySelectorAll('.category-card');
                this.allCategories = Array.from(cards).map(function(card) {
                    return {
                        element: card.cloneNode(true),
                        name: card.getAttribute('data-search-name') || '',
                        description: card.getAttribute('data-search-description') || ''
                    };
                });
            },
            
            restoreInitialCategories: function () {
                var grid = document.getElementById('categories-grid');
                if (!grid) return;
                
                grid.innerHTML = '';
                this.allCategories.forEach(function(item) {
                    if (item.element) {
                        grid.appendChild(item.element.cloneNode(true));
                    }
                });
                
                if (window.lucide) {
                    window.lucide.createIcons();
                }
            },

            filterCategories: function () {
                var self = this;
                
                if (self.searchTimeout) {
                    clearTimeout(self.searchTimeout);
                }

                var searchTerm = self.search.trim();
                
                if (!searchTerm) {
                    self.restoreInitialCategories();
                    self.showNoResults = false;
                    return;
                }

                self.searchTimeout = setTimeout(function() {
                    self.loading = true;
                    
                    fetch('{{ route("dashboard.categories.index") }}?q=' + encodeURIComponent(searchTerm), {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(function(response) {
                        return response.json();
                    })
                    .then(function(data) {
                        self.loading = false;
                        if (data.categories && data.categories.length > 0) {
                            self.renderSearchResults(data.categories);
                            self.showNoResults = false;
                        } else {
                            self.clearCategories();
                            self.showNoResults = true;
                        }
                    })
                    .catch(function(error) {
                        console.error('Erro na busca:', error);
                        self.loading = false;
                        self.filterLocal();
                    });
                }, 300);
            },


            renderSearchResults: function (categories) {
                var self = this;
                var grid = document.getElementById('categories-grid');
                if (!grid) return;

                grid.innerHTML = '';

                categories.forEach(function(category) {
                    var editUrl = '{{ route("dashboard.categories.edit", ":id") }}'.replace(':id', category.id);
                    
                    var card = document.createElement('div');
                    card.className = 'category-card border border-border rounded-xl p-4 hover:shadow-md transition-all';
                    
                    card.innerHTML = 
                        '<div class="flex items-start justify-between mb-4">' +
                            '<div class="flex items-center gap-3">' +
                                '<div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center">' +
                                    '<i data-lucide="package" class="h-6 w-6 text-primary"></i>' +
                                '</div>' +
                                '<div>' +
                                    '<h3 class="font-semibold">' + (category.name || '').replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</h3>' +
                                    '<p class="text-sm text-muted-foreground">' + (category.products_count || 0) + ' produtos</p>' +
                                '</div>' +
                            '</div>' +
                            '<div class="flex gap-1">' +
                                '<a href="' + editUrl + '" class="inline-flex items-center justify-center h-8 w-8 rounded-md hover:bg-muted">' +
                                    '<i data-lucide="edit" class="h-4 w-4"></i>' +
                                '</a>' +
                                '<button type="button" class="inline-flex items-center justify-center h-8 w-8 rounded-md hover:bg-muted text-destructive">' +
                                    '<i data-lucide="trash-2" class="h-4 w-4"></i>' +
                                '</button>' +
                            '</div>' +
                        '</div>' +
                        '<p class="text-sm text-muted-foreground">' + ((category.description || 'Sem descrição').replace(/</g, '&lt;').replace(/>/g, '&gt;')) + '</p>';
                    
                    grid.appendChild(card);
                });
                
                if (window.lucide) {
                    window.lucide.createIcons();
                }
            },

            clearCategories: function () {
                var grid = document.getElementById('categories-grid');
                if (grid) {
                    grid.innerHTML = '';
                }
            },

            filterLocal: function () {
                var self = this;
                var q = self.search.trim().toLowerCase();
                if (!q) {
                    self.restoreInitialCategories();
                    self.showNoResults = false;
                    return;
                }
                
                var visible = 0;
                this.allCategories.forEach(function(item) {
                    if (item.element) {
                        var name = item.name.toLowerCase();
                        var description = item.description.toLowerCase();
                        var nameNorm = name.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                        var descNorm = description.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                        var qNorm = q.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                        
                        if (name.includes(q) || nameNorm.includes(qNorm) || description.includes(q) || descNorm.includes(qNorm)) {
                            item.element.style.display = '';
                            visible++;
                        } else {
                            item.element.style.display = 'none';
                        }
                    }
                });
                
                self.showNoResults = visible === 0;
            }
        };
    });
});
</script>
@endpush
@endsection
