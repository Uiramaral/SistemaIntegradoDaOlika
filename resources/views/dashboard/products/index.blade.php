@extends('dashboard.layouts.app')

@section('page_title', 'Produtos')
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
    <a href="{{ route('dashboard.products.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors text-sm font-medium">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M5 12h14"></path>
            <path d="M12 5v14"></path>
        </svg>
        Adicionar produto
    </a>
@endsection

@section('content')
<div class="space-y-6">
    <div class="p-6">
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @forelse($products as $product)
                    @php
                        $categoryName = $product->category->name ?? 'Sem categoria';
                    @endphp
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                        <div class="flex flex-col items-center text-center">
                            <!-- Avatar/Iniciais -->
                            <div class="w-16 h-16 rounded-lg bg-gray-100 flex items-center justify-center mb-4">
                                <span class="text-gray-600 font-semibold text-lg">
                                    {{ strtoupper(substr($product->name, 0, 2)) }}
                                </span>
                            </div>
                            <!-- Nome do Produto -->
                            <h3 class="font-semibold text-gray-900 mb-2">{{ $product->name }}</h3>
                            <!-- Categoria com ponto verde -->
                            <div class="flex items-center gap-1 mb-4">
                                <div class="w-2 h-2 rounded-full bg-green-500"></div>
                                <span class="text-sm text-gray-500">{{ $categoryName }}</span>
                            </div>
                            <!-- Informações Adicionais -->
                            <div class="w-full space-y-2 text-sm text-gray-500 mb-4">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <span>Segunda a Sexta</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span>Das 8 as 17</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span>R$ {{ number_format($product->price, 2, ',', '.') }}</span>
                                </div>
                            </div>
                            <!-- Botão Ver detalhes -->
                            <a href="{{ route('dashboard.products.edit', $product) }}" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors text-sm font-medium">
                                Ver detalhes
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="col-span-3 text-center py-12">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mx-auto mb-4 text-gray-400">
                            <path d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z"></path>
                            <path d="M12 22V12"></path>
                            <path d="m3.3 7 7.703 4.734a2 2 0 0 0 1.994 0L20.7 7"></path>
                            <path d="m7.5 4.27 9 5.15"></path>
                        </svg>
                        <p class="text-gray-500">Nenhum produto encontrado</p>
                        <a href="{{ route('dashboard.products.create') }}" class="mt-4 inline-flex items-center justify-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors text-sm font-medium">
                            Criar primeiro produto
                        </a>
                    </div>
                @endforelse
            </div>

            @if($products->hasPages())
                <div class="mt-6">
                    {{ $products->onEachSide(1)->links('vendor.pagination.compact') }}
                </div>
            @endif
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
