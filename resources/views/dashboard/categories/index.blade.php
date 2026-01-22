@extends('dashboard.layouts.app')

@section('page_title', 'Categorias')
@section('page_subtitle', 'Acompanhe uma visão detalhada das métricas e resultados')

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
<div class="space-y-6">

    @if($categories->count() > 0)
    <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="w-full text-sm" data-mobile-card="true">
                    <thead class="bg-gray-50">
                        <tr class="border-b">
                            <th class="h-12 px-4 text-left align-middle text-xs font-medium text-gray-500 uppercase tracking-wider">CATEGORIA</th>
                            <th class="h-12 px-4 text-left align-middle text-xs font-medium text-gray-500 uppercase tracking-wider">SLUG</th>
                            <th class="h-12 px-4 text-left align-middle text-xs font-medium text-gray-500 uppercase tracking-wider">PRODUTOS</th>
                            <th class="h-12 px-4 text-left align-middle text-xs font-medium text-gray-500 uppercase tracking-wider">STATUS</th>
                            <th class="h-12 px-4 text-left align-middle text-xs font-medium text-gray-500 uppercase tracking-wider">AÇÕES</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($categories as $category)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                                    </svg>
                                    <span class="font-semibold text-gray-900">{{ $category->name }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                / {{ strtolower(\Illuminate\Support\Str::slug($category->name)) }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                {{ $category->products_count ?? 0 }} produtos
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if($category->is_active)
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Ativo</span>
                                @else
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">Inativo</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <button class="text-gray-400 hover:text-gray-600">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>
                                    <button class="text-gray-400 hover:text-red-600">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
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
