@extends('dashboard.layouts.app')

@section('page_title', 'Categorias')
@section('page_subtitle', 'Gerenciamento de categorias')

@section('page_actions')
    <div class="flex items-center gap-2">
        <button class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z">
                </path>
            </svg>
        </button>
        <button class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"></path>
            </svg>
        </button>
    </div>
    <a href="{{ route('dashboard.categories.create') }}"
        class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors text-sm font-medium">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M5 12h14"></path>
            <path d="M12 5v14"></path>
        </svg>
        Adicionar categoria
    </a>
@endsection

@section('content')
    <div class="bg-card rounded-xl border border-border animate-fade-in" id="categories-page"
        x-data="categoriesLiveSearch()">
        <div class="p-4 border-b border-border flex flex-col sm:flex-row gap-4 justify-between">
            <div class="relative flex-1 max-w-md">
                <i data-lucide="search"
                    class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground pointer-events-none"></i>
                <input type="text" x-model="search" placeholder="Buscar categoria..." class="form-input pl-10 h-10 w-full"
                    autocomplete="off" />
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
                    data-search-name="{{ mb_strtolower($category->name, 'UTF-8') }}" x-show="matchesCard($el)">
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
                            <a href="{{ route('dashboard.categories.edit', $category) }}"
                                class="inline-flex items-center justify-center h-8 w-8 rounded-md hover:bg-muted">
                                <i data-lucide="edit" class="h-4 w-4"></i>
                            </a>
                            <button type="button"
                                class="inline-flex items-center justify-center h-8 w-8 rounded-md hover:bg-muted text-destructive">
                                <i data-lucide="trash-2" class="h-4 w-4"></i>
                            </button>
                        </div>
                    </div>

                    <p class="text-sm text-muted-foreground">{{ $category->description ?? 'Sem descrição' }}</p>
                </div>
            @endforeach
            @if($categories->count() > 0)
                {{-- No results message handled by empty state if needed, or loop is empty --}}
            @else
                <div class="col-span-full text-center text-muted-foreground py-8">
                    Nenhuma categoria cadastrada.
                </div>
            @endif
        </div>

        <!-- Pagination -->
        @if($categories->hasPages())
            <div class="px-4 sm:px-6 py-3 sm:py-4 border-t border-border bg-muted/20">
                {{ $categories->withQueryString()->links() }}
            </div>
        @endif
    </div>

    @push('styles')
        <style>
            [x-cloak] {
                display: none !important
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            window.categoriesLiveSearch = function () {
                return {
                    search: '',

                    matchesCard(el) {
                        if (!el) return false;
                        const searchLower = this.search.toLowerCase();
                        if (searchLower === '') return true;

                        const name = el.dataset.searchName || '';
                        return name.toLowerCase().includes(searchLower);
                    }
                };
            };
        </script>
    @endpush
@endsection