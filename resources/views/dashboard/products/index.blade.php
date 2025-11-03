@extends('dashboard.layouts.app')

@section('title', 'Produtos - OLIKA Dashboard')

@section('content')
<div class="space-y-6 animate-in fade-in duration-500">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold tracking-tight">Produtos</h1>
            <p class="text-muted-foreground">Gerencie o cardápio do seu restaurante</p>
        </div>
        <a href="{{ route('dashboard.products.create') }}" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2 gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-plus h-4 w-4">
                <path d="M5 12h14"></path>
                <path d="M12 5v14"></path>
            </svg>
            Novo Produto
        </a>
    </div>

    @if(session('success'))
        <div class="rounded-lg border bg-green-50 text-green-900 px-4 py-3">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="rounded-lg border bg-red-50 text-red-900 px-4 py-3">
            {{ session('error') }}
        </div>
    @endif

    <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
        <div class="flex flex-col space-y-1.5 p-6">
            <div class="flex items-center justify-between gap-4 mb-4 min-w-0">
                <form method="GET" action="{{ route('dashboard.products.index') }}" class="flex flex-col md:flex-row gap-4 flex-1 min-w-0">
                <div class="relative flex-1 min-w-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-search absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.3-4.3"></path>
                    </svg>
                    <input type="text" name="search" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm pl-10" placeholder="Buscar produtos..." value="{{ request('search') }}">
                </div>
                <select name="category_id" class="flex h-10 rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 md:text-sm">
                    <option value="">Todas as categorias</option>
                    @foreach($categories ?? [] as $category)
                        <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
                <select name="status" class="flex h-10 rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 md:text-sm">
                    <option value="">Todos os status</option>
                    <option value="active" @selected(request('status') == 'active')>Ativo</option>
                    <option value="inactive" @selected(request('status') == 'inactive')>Inativo</option>
                </select>
                <button type="submit" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2">
                    Buscar
                </button>
            </form>
            </div>
        </div>
        <div class="p-6 pt-0 overflow-x-hidden" id="productsContainer">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @forelse($products as $product)
                    <div class="rounded-lg border bg-card text-card-foreground shadow-sm hover:shadow-md transition-shadow w-full">
                        <div class="p-6">
                            <div class="flex items-start gap-4">
                                <div class="flex-shrink-0 w-16 h-16 rounded-lg bg-accent flex items-center justify-center overflow-hidden">
                                    @if($product->cover_image || ($product->images && $product->images->count() > 0))
                                        <img src="{{ $product->cover_image ? asset('storage/' . $product->cover_image) : asset('storage/' . $product->images->first()->path) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-package h-8 w-8 text-accent-foreground">
                                            <path d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z"></path>
                                            <path d="M12 22V12"></path>
                                            <path d="m3.3 7 7.703 4.734a2 2 0 0 0 1.994 0L20.7 7"></path>
                                            <path d="m7.5 4.27 9 5.15"></path>
                                        </svg>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between gap-2 mb-2">
                                        <h3 class="font-semibold truncate">{{ $product->name }}</h3>
                                        <div class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 {{ $product->is_active ? 'border-transparent bg-primary text-primary-foreground hover:bg-primary/80' : 'border-transparent bg-secondary text-secondary-foreground hover:bg-secondary/80' }}">
                                            {{ $product->is_active ? 'Ativo' : 'Inativo' }}
                                        </div>
                                    </div>
                                    <p class="text-sm text-muted-foreground mb-2">{{ $product->category->name ?? 'Sem categoria' }}</p>
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-lg font-bold text-primary">R$ {{ number_format($product->price, 2, ',', '.') }}</span>
                                        @if($product->stock !== null)
                                        <span class="text-sm text-muted-foreground">Estoque: {{ $product->stock }}</span>
                                        @endif
                                    </div>
                                    @if($product->allergens && $product->allergens->count() > 0)
                                        <div class="mb-3">
                                            <div class="flex flex-wrap gap-1">
                                                @foreach($product->allergens->take(3) as $allergen)
                                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs bg-muted text-foreground/80 border border-border">{{ $allergen->name }}</span>
                                                @endforeach
                                                @if($product->allergens->count() > 3)
                                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs bg-muted text-foreground/80 border border-border">+{{ $product->allergens->count() - 3 }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                    <div class="flex flex-wrap gap-2">
                                        <a href="{{ route('dashboard.products.edit', $product) }}" class="flex-1 min-w-[100px] inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-9 rounded-md px-3">
                                            Editar
                                        </a>
                                        <form method="POST" action="{{ route('dashboard.products.duplicate', $product) }}" class="flex-1 min-w-[100px] inline" onsubmit="return confirm('Deseja duplicar este produto? Uma cópia será criada e você será redirecionado para editá-la.')">
                                            @csrf
                                            <button type="submit" class="w-full inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-blue-500 bg-blue-50 text-blue-700 hover:bg-blue-100 h-9 rounded-md px-3" title="Duplicar produto para criar variações">
                                                Duplicar
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('dashboard.products.destroy', $product) }}" class="flex-1 min-w-[100px] inline" onsubmit="return confirm('Tem certeza que deseja excluir este produto?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-full inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-red-500 text-white hover:bg-red-600 h-9 rounded-md px-3">
                                                Excluir
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-3 text-center py-12">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mx-auto mb-4 text-muted-foreground">
                            <path d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z"></path>
                            <path d="M12 22V12"></path>
                            <path d="m3.3 7 7.703 4.734a2 2 0 0 0 1.994 0L20.7 7"></path>
                            <path d="m7.5 4.27 9 5.15"></path>
                        </svg>
                        <p class="text-muted-foreground">Nenhum produto encontrado</p>
                        <a href="{{ route('dashboard.products.create') }}" class="mt-4 inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 bg-primary text-primary-foreground hover:bg-primary/90 h-9 px-4">
                            Criar primeiro produto
                        </a>
                    </div>
                @endforelse
            </div>

            @if($products->hasPages())
                <div class="mt-6">
                    {{ $products->links() }}
                </div>
            @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal de Visualização do Produto -->
<div id="productModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <!-- Overlay -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeProductModal()"></div>
        
        <!-- Modal Panel -->
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="flex items-start justify-between mb-4">
                    <h3 id="modal-title" class="text-2xl font-bold text-gray-900">Detalhes do Produto</h3>
                    <button onclick="closeProductModal()" class="text-gray-400 hover:text-gray-500">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <div id="modalContent" class="space-y-4">
                    <!-- Conteúdo será carregado via AJAX -->
                    <div class="flex items-center justify-center py-12">
                        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
                    </div>
                </div>
            </div>
            
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <a id="modalEditBtn" href="#" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary text-base font-medium text-white hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:ml-3 sm:w-auto sm:text-sm">
                    Editar Produto
                </a>
                <a id="modalViewBtn" href="#" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Ver Detalhes Completos
                </a>
                <button onclick="closeProductModal()" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:mt-0 sm:w-auto sm:text-sm">
                    Fechar
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let currentProductId = null;

function openProductModal(productId) {
    currentProductId = productId;
    const modal = document.getElementById('productModal');
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    loadProductData(productId);
}

function closeProductModal() {
    const modal = document.getElementById('productModal');
    modal.classList.add('hidden');
    document.body.style.overflow = '';
    currentProductId = null;
}

async function loadProductData(productId) {
    const content = document.getElementById('modalContent');
    content.innerHTML = '<div class="flex items-center justify-center py-12"><div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div></div>';
    try {
        const response = await fetch(`/dashboard/products/${productId}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        });
        if (!response.ok) throw new Error('Erro ao carregar produto');
        const data = await response.json();
        let html = `
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h4 class="text-2xl font-bold mb-4">${escapeHtml(data.name)}</h4>
                    <div class="space-y-3">
                        <div>
                            <label class="text-sm text-gray-500">Preço</label>
                            <p class="text-3xl font-bold text-primary">R$ ${parseFloat(data.price).toFixed(2).replace('.', ',')}</p>
                        </div>
                        <div>
                            <label class="text-sm text-gray-500">Categoria</label>
                            <p class="text-base font-medium">${data.category ? escapeHtml(data.category.name) : 'Sem categoria'}</p>
                        </div>
                        ${data.sku ? `<div><label class="text-sm text-gray-500">SKU</label><p class="text-base">${escapeHtml(data.sku)}</p></div>` : ''}
                        ${data.stock !== null ? `<div><label class="text-sm text-gray-500">Estoque</label><p class="text-base font-medium">${data.stock} unidades</p></div>` : ''}
                        ${data.preparation_time ? `<div><label class="text-sm text-gray-500">Tempo de Preparação</label><p class="text-base">${data.preparation_time} minutos</p></div>` : ''}
                        <div>
                            <label class="text-sm text-gray-500">Status</label>
                            <div class="flex gap-2 mt-1">
                                ${data.is_active ? '<span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold border-transparent bg-primary text-primary-foreground">Ativo</span>' : '<span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold border-transparent bg-secondary text-secondary-foreground">Inativo</span>'}
                                ${data.is_available ? '<span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold border-transparent bg-green-100 text-green-800">Disponível</span>' : '<span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold border-transparent bg-red-100 text-red-800">Indisponível</span>'}
                                ${data.is_featured ? '<span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold border-transparent bg-yellow-100 text-yellow-800">Destaque</span>' : ''}
                            </div>
                        </div>
                    </div>
                </div>
                <div>
                    ${data.cover_image ? 
                        `<div class="mb-4"><img src="/storage/${data.cover_image}" alt="${escapeHtml(data.name)}" class="w-full h-64 object-cover rounded-lg border"></div>` : 
                        (data.images && data.images.length > 0 ? 
                            `<div class="mb-4"><img src="/storage/${data.images[0].path}" alt="${escapeHtml(data.name)}" class="w-full h-64 object-cover rounded-lg border"></div>` :
                            '<div class="w-full h-64 bg-gray-100 rounded-lg flex items-center justify-center mb-4"><svg class="h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg></div>')
                        }
                    ${data.description ? `<div class="mb-4"><label class="text-sm text-gray-500 block mb-1">Descrição</label><p class="text-sm whitespace-pre-wrap">${escapeHtml(data.description)}</p></div>` : ''}
                </div>
            </div>
            ${data.allergens && data.allergens.length > 0 ? `
            <div class="border-t pt-4 mt-4">
                <label class="text-sm font-medium text-gray-700 block mb-2">Alérgenicos</label>
                <div class="flex flex-wrap gap-2">
                    ${data.gluten_free ? '<span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold border-transparent bg-green-100 text-green-800">✓ Sem Glúten</span>' : ''}
                    ${data.contamination_risk ? '<span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold border-transparent bg-yellow-100 text-yellow-800">⚠ Pode conter traços</span>' : ''}
                    ${data.allergens.map(a => `<span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold bg-muted text-foreground/80 border border-border">${escapeHtml(a.name)}</span>`).join('')}
                </div>
            </div>
            ` : (data.gluten_free || data.contamination_risk ? `
            <div class="border-t pt-4 mt-4">
                <label class="text-sm font-medium text-gray-700 block mb-2">Informações</label>
                <div class="flex flex-wrap gap-2">
                    ${data.gluten_free ? '<span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold border-transparent bg-green-100 text-green-800">✓ Sem Glúten</span>' : ''}
                    ${data.contamination_risk ? '<span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold border-transparent bg-yellow-100 text-yellow-800">⚠ Pode conter traços</span>' : ''}
                </div>
            </div>
            ` : '')}
        `;
        
        content.innerHTML = html;
        
        // Atualizar links dos botões
        document.getElementById('modalEditBtn').href = `/dashboard/products/${productId}/edit`;
        document.getElementById('modalViewBtn').href = `/dashboard/products/${productId}`;
        
    } catch (e) {
        content.innerHTML = '<div class="text-center py-12 text-red-600">Erro ao carregar informações do produto</div>';
    }
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && !document.getElementById('productModal').classList.contains('hidden')) {
        closeProductModal();
    }
});
</script>
@endpush
