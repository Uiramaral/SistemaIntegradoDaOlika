@extends('dashboard.layouts.app')

@section('page_title', 'Categorias')
@section('page_subtitle', 'Organize seus produtos por categoria')

@section('page_actions')
    <a href="{{ route('dashboard.categories.create') }}" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2 gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4">
            <path d="M5 12h14"></path>
            <path d="M12 5v14"></path>
        </svg>
        Nova Categoria
    </a>
@endsection

@section('content')
<div class="space-y-6">

    @if($categories->count() > 0)
    <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="w-full text-sm" data-mobile-card="true">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left p-3 text-sm font-medium">Nome</th>
                            <th class="text-left p-3 text-sm font-medium">Produtos</th>
                            <th class="text-left p-3 text-sm font-medium">Ordem</th>
                            <th class="text-left p-3 text-sm font-medium">Status</th>
                            <th class="text-right p-3 text-sm font-medium">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($categories as $category)
                        <tr class="border-b hover:bg-muted/50">
                            <td class="p-3" data-label="Nome">
                                <div class="font-medium">{{ $category->name }}</div>
                                @if($category->description)
                                <div class="text-sm text-muted-foreground mt-1">{{ \Illuminate\Support\Str::limit($category->description, 60) }}</div>
                                @endif
                            </td>
                            <td class="p-3" data-label="Produtos">
                                <button onclick="toggleProducts({{ $category->id }})" class="inline-flex items-center rounded-full bg-primary/10 px-2.5 py-0.5 text-xs font-medium text-primary hover:bg-primary/20 cursor-pointer">
                                    {{ $category->products_count }} produto(s)
                                </button>
                            </td>
                            <td class="p-3 text-sm text-muted-foreground" data-label="Ordem">{{ $category->sort_order }}</td>
                            <td class="p-3" data-label="Status">
                                @if($category->is_active)
                                <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">Ativa</span>
                                @else
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-800">Inativa</span>
                                @endif
                            </td>
                            <td class="p-3 actions-cell" data-label="Ações">
                                <div class="flex items-center justify-end gap-2 mobile-actions">
                                    <button onclick="toggleProducts({{ $category->id }})" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-8 px-3" title="Gerenciar produtos">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
                                            <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
                                        </svg>
                                        <span class="ml-1 text-xs">Produtos</span>
                                    </button>
                                    <a href="{{ route('dashboard.categories.edit', $category) }}" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-8 w-8" title="Editar">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                        </svg>
                                    </a>
                                    <form action="{{ route('dashboard.categories.destroy', $category) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir esta categoria?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 border border-input bg-background hover:bg-accent hover:text-accent-foreground text-red-600 hover:text-red-700 h-8 w-8" title="Excluir">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="3 6 5 6 21 6"></polyline>
                                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <!-- Seção expansível de produtos -->
                        <tr id="products-{{ $category->id }}" class="hidden">
                            <td colspan="5" class="p-4 bg-muted/30">
                                <div class="space-y-3">
                                    <div class="flex items-center justify-between mb-3">
                                        <h4 class="font-semibold">Produtos nesta categoria</h4>
                                        <button onclick="toggleProducts({{ $category->id }})" class="text-sm text-muted-foreground hover:text-foreground">Fechar</button>
                                    </div>
                                    @php
                                        $categoryProducts = ($allProducts ?? collect())->where('category_id', $category->id);
                                    @endphp
                                    @if($categoryProducts->count() > 0)
                                        <div class="space-y-2 max-h-64 overflow-y-auto">
                                            @foreach($categoryProducts as $product)
                                            <div class="flex items-center justify-between p-2 bg-white rounded border">
                                                <span class="text-sm">{{ $product->name }}</span>
                                                <select onchange="updateProductCategory({{ $product->id }}, this.value)" class="text-xs border rounded px-2 py-1">
                                                    <option value="">Sem categoria</option>
                                                    @foreach($allCategories ?? [] as $cat)
                                                    <option value="{{ $cat->id }}" {{ $cat->id == $product->category_id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-sm text-muted-foreground">Nenhum produto nesta categoria.</p>
                                    @endif
                                    <div class="mt-3 pt-3 border-t">
                                        <p class="text-sm text-muted-foreground mb-2">Adicionar produto à categoria:</p>
                                        <div class="flex gap-2">
                                            <select id="add-product-{{ $category->id }}" class="flex-1 text-sm border rounded px-2 py-1">
                                                <option value="">Selecione um produto</option>
                                                @php
                                                    $availableProducts = collect($allProducts ?? [])->filter(function($p) use ($category) {
                                                        return $p->category_id != $category->id;
                                                    })->values();
                                                @endphp
                                                @foreach($availableProducts as $product)
                                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                                                @endforeach
                                            </select>
                                            <button onclick="addProductToCategory({{ $category->id }})" class="px-3 py-1 text-sm bg-primary text-primary-foreground rounded hover:bg-primary/90">
                                                Adicionar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @else
    <div class="rounded-lg border bg-card text-card-foreground shadow-sm p-12 text-center">
        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mx-auto mb-4 text-muted-foreground">
            <path d="M20 10a1 1 0 0 0 1-1V6a1 1 0 0 0-1-1h-2.5a1 1 0 0 1-.8-.4l-.9-1.2A1 1 0 0 0 15 3h-2a1 1 0 0 0-1 1v5a1 1 0 0 0 1 1Z"></path>
            <path d="M20 21a1 1 0 0 0 1-1v-3a1 1 0 0 0-1-1h-2.9a1 1 0 0 1-.88-.55l-.42-.85a1 1 0 0 0-.92-.6H13a1 1 0 0 0-1 1v5a1 1 0 0 0 1 1Z"></path>
            <path d="M3 5a2 2 0 0 0 2 2h3"></path>
            <path d="M3 3v13a2 2 0 0 0 2 2h3"></path>
        </svg>
        <h3 class="text-lg font-semibold mb-2">Nenhuma categoria cadastrada</h3>
        <p class="text-muted-foreground mb-4">Comece criando sua primeira categoria</p>
        <a href="{{ route('dashboard.categories.create') }}" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2 gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M5 12h14"></path>
                <path d="M12 5v14"></path>
            </svg>
            Nova Categoria
        </a>
    </div>
    @endif
</div>

@push('scripts')
<script>
function toggleProducts(categoryId) {
    const row = document.getElementById('products-' + categoryId);
    if (row) {
        row.classList.toggle('hidden');
    }
}

async function updateProductCategory(productId, categoryId) {
    try {
        const response = await fetch('{{ route('dashboard.categories.update-product') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                product_id: productId,
                category_id: categoryId || null
            })
        });
        
        const data = await response.json();
        if (data.success) {
            // Recarregar a página para atualizar contadores
            setTimeout(() => window.location.reload(), 500);
        } else {
            alert('Erro ao atualizar categoria do produto');
        }
    } catch (error) {
        console.error('Erro:', error);
        alert('Erro ao atualizar categoria do produto');
    }
}

async function addProductToCategory(categoryId) {
    const select = document.getElementById('add-product-' + categoryId);
    const productId = select.value;
    
    if (!productId) {
        alert('Selecione um produto');
        return;
    }
    
    await updateProductCategory(productId, categoryId);
}
</script>
@endpush
@endsection
