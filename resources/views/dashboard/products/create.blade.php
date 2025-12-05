@extends('dashboard.layouts.app')

@section('title', 'Criar Produto - OLIKA Dashboard')

@section('content')
<div class="space-y-6 animate-in fade-in duration-500">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold tracking-tight">Criar Produto</h1>
            <p class="text-muted-foreground">Adicione um novo produto ao cardápio</p>
        </div>
        <a href="{{ route('dashboard.products.index') }}" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2">
            Voltar
        </a>
    </div>

    @if($errors->any())
        <div class="rounded-lg border bg-red-50 text-red-900 px-4 py-3">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('dashboard.products.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6 pb-24">
        @csrf
        
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="p-6 space-y-6">
                <h2 class="text-xl font-semibold">Informações Básicas</h2>
                
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium mb-2">Nome do Produto *</label>
                        <input type="text" name="name" value="{{ old('name') }}" required class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                        @error('name')<span class="text-sm text-red-600">{{ $message }}</span>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">SKU (Código)</label>
                        <input type="text" name="sku" value="{{ old('sku') }}" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                        @error('sku')<span class="text-sm text-red-600">{{ $message }}</span>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">Categoria *</label>
                        <select name="category_id" required class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                            <option value="">Selecione uma categoria</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id')<span class="text-sm text-red-600">{{ $message }}</span>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">Preço (R$) *</label>
                        <input type="number" name="price" step="0.01" min="0" value="{{ old('price') }}" required class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                        @error('price')<span class="text-sm text-red-600">{{ $message }}</span>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">Peso (g)</label>
                        <input type="number" name="weight_grams" step="1" min="0" value="{{ old('weight_grams') }}" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                        @error('weight_grams')<span class="text-sm text-red-600">{{ $message }}</span>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">Estoque</label>
                        <input type="number" name="stock" min="0" value="{{ old('stock') }}" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                        @error('stock')<span class="text-sm text-red-600">{{ $message }}</span>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">Tempo de Preparação (minutos)</label>
                        <input type="number" name="preparation_time" min="0" value="{{ old('preparation_time') }}" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                        @error('preparation_time')<span class="text-sm text-red-600">{{ $message }}</span>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">Ordem de Exibição</label>
                        <input type="number" name="sort_order" min="0" value="{{ old('sort_order', 0) }}" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                        @error('sort_order')<span class="text-sm text-red-600">{{ $message }}</span>@enderror
                    </div>
                </div>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <p class="text-sm text-blue-800 font-medium mb-2">💡 Geração Automática com IA</p>
                    <p class="text-xs text-blue-700">Ao criar o produto, a IA automaticamente gerará as descrições usando: lista de ingredientes, peso, variantes (se houver) e alergênicos.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Descrição</label>
                    <textarea name="description" rows="4" class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">{{ old('description') }}</textarea>
                    @error('description')<span class="text-sm text-red-600">{{ $message }}</span>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Descrição para Rótulo</label>
                    <textarea name="label_description" rows="3" class="flex min-h-[60px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">{{ old('label_description') }}</textarea>
                    @error('label_description')<span class="text-sm text-red-600">{{ $message }}</span>@enderror
                    <p class="text-xs text-muted-foreground mt-1">Texto que aparecerá no rótulo do produto (gerado automaticamente se vazio)</p>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Lista de Ingredientes</label>
                    <textarea name="ingredients" rows="3" placeholder="Ex.: Farinha de trigo, água, fermento natural, sal..." class="flex min-h-[60px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">{{ old('ingredients') }}</textarea>
                    <p class="text-xs text-muted-foreground mt-1">Utilizada para rótulos e geração automática de descrições.</p>
                </div>
            </div>
        </div>

        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="p-6 space-y-6">
                <h2 class="text-xl font-semibold">Variações</h2>
                <p class="text-sm text-muted-foreground">Adicione opções como peso/sabor com preços distintos.</p>
                <div id="variantsList" class="space-y-3"></div>
                <button type="button" class="px-3 py-2 rounded border" onclick="addVariantRow()">Adicionar variação</button>
            </div>
        </div>

        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="p-6 space-y-6">
                <h2 class="text-xl font-semibold">Alérgenicos</h2>
                
                <div class="space-y-4">
                    <div class="flex items-center gap-4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="gluten_free" value="1" @checked(old('gluten_free')) class="rounded border-gray-300">
                            <span class="text-sm">Produto sem glúten</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="contamination_risk" value="1" @checked(old('contamination_risk')) class="rounded border-gray-300">
                            <span class="text-sm">Pode conter traços de glúten</span>
                        </label>
                    </div>

                    @if($allergens && $allergens->count() > 0)
                        <div class="space-y-4">
                            @foreach($allergens as $groupName => $groupAllergens)
                                <div>
                                    @if($groupName)
                                        <h3 class="text-sm font-medium mb-2">{{ $groupName }}</h3>
                                    @endif
                                    <div class="grid gap-2 md:grid-cols-2 lg:grid-cols-3">
                                        @foreach($groupAllergens as $allergen)
                                            <label class="flex items-center gap-2 p-2 rounded border hover:bg-accent cursor-pointer">
                                                <input type="checkbox" name="allergen_ids[]" value="{{ $allergen->id }}" @checked(in_array($allergen->id, old('allergen_ids', []))) class="rounded border-gray-300">
                                                <span class="text-sm">{{ $allergen->name }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-muted-foreground">Nenhum alérgenico cadastrado. Configure os alérgenicos primeiro.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="p-6 space-y-6">
                <h2 class="text-xl font-semibold">Imagens</h2>
                
                <div>
                    <label class="block text-sm font-medium mb-2">Imagem de Capa</label>
                    <input type="file" id="cover_image_input" name="cover_image" accept="image/*" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                    <input type="hidden" name="cover_image_cropped" id="cover_image_cropped">
                    <div id="cover_image_preview" class="mt-3 hidden">
                        <img id="cover_image_preview_img" src="" alt="Preview" class="max-w-full h-48 object-contain rounded-lg border">
                        <button type="button" onclick="removeCoverImage()" class="mt-2 text-sm text-red-600 hover:text-red-700">Remover imagem</button>
                    </div>
                    @error('cover_image')<span class="text-sm text-red-600">{{ $message }}</span>@enderror
                    <p class="text-xs text-muted-foreground mt-1">Tamanho máximo: 5MB. Clique para ajustar e cortar a imagem antes de enviar.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Imagens Adicionais</label>
                    <input type="file" id="images_input" name="images[]" accept="image/*" multiple class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                    <div id="images_preview_container" class="mt-3 grid grid-cols-2 md:grid-cols-4 gap-4"></div>
                    <input type="hidden" name="images_cropped" id="images_cropped">
                    @error('images.*')<span class="text-sm text-red-600">{{ $message }}</span>@enderror
                    <p class="text-xs text-muted-foreground mt-1">Tamanho máximo por imagem: 5MB. Clique para ajustar e cortar as imagens antes de enviar.</p>
                </div>
            </div>
        </div>

        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="p-6 space-y-6">
                <h2 class="text-xl font-semibold">Configurações e SEO</h2>
                
                <div class="grid gap-4 md:grid-cols-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true)) class="rounded border-gray-300">
                        <span class="text-sm">Produto ativo</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="only_pdv" value="1" @checked(old('only_pdv', false)) class="rounded border-gray-300">
                        <span class="text-sm">Apenas PDV (ocultar do catálogo público)</span>
                        <span class="text-xs text-muted-foreground">(marque para mostrar apenas no PDV)</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_available" value="1" @checked(old('is_available', true)) class="rounded border-gray-300">
                        <span class="text-sm">Produto disponível</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured')) class="rounded border-gray-300">
                        <span class="text-sm">Produto em destaque</span>
                    </label>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-sm font-medium">Título SEO</label>
                        <button type="button" id="generateSeoBtn" class="text-xs text-primary hover:text-primary/80 flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 2v20M2 12h20"></path>
                            </svg>
                            Gerar com IA
                        </button>
                    </div>
                    <input type="text" name="seo_title" id="seo_title" value="{{ old('seo_title') }}" maxlength="60" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                    <p class="text-xs text-muted-foreground mt-1"><span id="seo_title_count">0</span>/60 caracteres</p>
                    @error('seo_title')<span class="text-sm text-red-600">{{ $message }}</span>@enderror
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-sm font-medium">Descrição SEO</label>
                        <button type="button" id="generateSeoDescBtn" class="text-xs text-primary hover:text-primary/80 flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 2v20M2 12h20"></path>
                            </svg>
                            Gerar com IA
                        </button>
                    </div>
                    <textarea name="seo_description" id="seo_description" rows="3" maxlength="160" class="flex min-h-[60px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">{{ old('seo_description') }}</textarea>
                    <p class="text-xs text-muted-foreground mt-1"><span id="seo_desc_count">0</span>/160 caracteres</p>
                    @error('seo_description')<span class="text-sm text-red-600">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>

        <!-- Barra de ações fixa (sticky) -->
        <div class="sticky bottom-0 bg-white border-t shadow-lg p-4 z-40 mt-8">
            <div class="flex justify-end gap-4">
                <a href="{{ route('dashboard.products.index') }}" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2">
                    Cancelar
                </a>
                <button type="submit" name="action" value="save" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2">
                    Criar Produto
                </button>
                <button type="submit" name="action" value="save_ai" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2">
                    🤖 Criar + IA
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.1/dist/cropper.min.css">
<style>
    .cropper-container {
        max-height: 70vh;
    }
    #cropModal {
        z-index: 9999;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.1/dist/cropper.min.js"></script>
<script>
let currentCropType = null; // 'cover' ou 'image'
let currentImageIndex = null;
let cropperInstance = null;
let croppedImages = {}; // Armazena as imagens cortadas
let pendingImages = []; // Imagens pendentes para processar

// Modal de crop
const cropModal = document.createElement('div');
cropModal.id = 'cropModal';
cropModal.className = 'fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 hidden';
cropModal.innerHTML = `
    <div class="bg-white rounded-lg p-6 max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <h3 class="text-lg font-semibold mb-4">Ajustar e Cortar Imagem</h3>
        <div class="mb-4">
            <img id="cropImage" style="max-width: 100%; max-height: 60vh;">
        </div>
        <div class="flex gap-3 justify-end">
            <button type="button" onclick="cancelCrop()" class="px-4 py-2 border rounded hover:bg-gray-50">Cancelar</button>
            <button type="button" onclick="applyCrop()" class="px-4 py-2 bg-primary text-white rounded hover:bg-primary/90">Aplicar</button>
        </div>
    </div>
`;
document.body.appendChild(cropModal);

function openCropModal(file, type, index = null) {
    currentCropType = type;
    currentImageIndex = index;
    
    const reader = new FileReader();
    reader.onload = function(e) {
        const img = document.getElementById('cropImage');
        img.src = e.target.result;
        
        // Destruir instância anterior se existir
        if (cropperInstance) {
            cropperInstance.destroy();
        }
        
        // Criar nova instância do cropper
        cropperInstance = new Cropper(img, {
            aspectRatio: 1, // Quadrado (pode ajustar para 16/9, 4/3, etc)
            viewMode: 1,
            dragMode: 'move',
            autoCropArea: 0.8,
            restore: false,
            guides: true,
            center: true,
            highlight: false,
            cropBoxMovable: true,
            cropBoxResizable: true,
            toggleDragModeOnDblclick: false,
        });
        
        cropModal.classList.remove('hidden');
    };
    reader.readAsDataURL(file);
}

function cancelCrop() {
    cropModal.classList.add('hidden');
    if (cropperInstance) {
        cropperInstance.destroy();
        cropperInstance = null;
    }
    // Limpar inputs se cancelado
    if (currentCropType === 'cover') {
        document.getElementById('cover_image_input').value = '';
    }
    // Processar próxima imagem pendente se houver
    processNextPendingImage();
}

function applyCrop() {
    if (!cropperInstance) return;
    
    const canvas = cropperInstance.getCroppedCanvas({
        width: 800,
        height: 800,
        imageSmoothingEnabled: true,
        imageSmoothingQuality: 'high'
    });
    
    canvas.toBlob(function(blob) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const base64 = e.target.result;
            
            if (currentCropType === 'cover') {
                // Salvar imagem de capa
                croppedImages.cover = base64;
                document.getElementById('cover_image_cropped').value = base64;
                
                // Mostrar preview
                const preview = document.getElementById('cover_image_preview');
                const previewImg = document.getElementById('cover_image_preview_img');
                previewImg.src = base64;
                preview.classList.remove('hidden');
            } else {
                // Salvar imagem no array
                if (!croppedImages.images) croppedImages.images = [];
                if (currentImageIndex !== null && currentImageIndex < croppedImages.images.length) {
                    croppedImages.images[currentImageIndex] = base64;
                } else {
                    croppedImages.images.push(base64);
                }
                
                // Atualizar campo hidden com array JSON
                document.getElementById('images_cropped').value = JSON.stringify(croppedImages.images);
                
                // Atualizar preview
                updateImagesPreview();
            }
            
            cropModal.classList.add('hidden');
            if (cropperInstance) {
                cropperInstance.destroy();
                cropperInstance = null;
            }
            
            // Processar próxima imagem pendente se houver
            processNextPendingImage();
        };
        reader.readAsDataURL(blob);
    }, 'image/jpeg', 0.9);
}

function removeCoverImage() {
    delete croppedImages.cover;
    document.getElementById('cover_image_cropped').value = '';
    document.getElementById('cover_image_preview').classList.add('hidden');
    document.getElementById('cover_image_input').value = '';
}

function updateImagesPreview() {
    const container = document.getElementById('images_preview_container');
    container.innerHTML = '';
    
    if (!croppedImages.images || croppedImages.images.length === 0) return;
    
    croppedImages.images.forEach((img, index) => {
        const div = document.createElement('div');
        div.className = 'relative';
        div.innerHTML = `
            <img src="${img}" alt="Preview ${index + 1}" class="w-full h-32 object-cover rounded-lg border">
            <button type="button" onclick="removeImage(${index})" class="absolute top-1 right-1 bg-red-600 text-white text-xs px-2 py-1 rounded hover:bg-red-700">Remover</button>
        `;
        container.appendChild(div);
    });
}

function removeImage(index) {
    croppedImages.images.splice(index, 1);
    document.getElementById('images_cropped').value = JSON.stringify(croppedImages.images);
    updateImagesPreview();
    
    // Limpar input se não houver mais imagens
    if (croppedImages.images.length === 0) {
        document.getElementById('images_input').value = '';
    }
}

function processNextPendingImage() {
    if (pendingImages.length === 0) return;
    
    const file = pendingImages.shift();
    const nextIndex = croppedImages.images ? croppedImages.images.length : 0;
    openCropModal(file, 'image', nextIndex);
}

// Event listeners para inputs de arquivo
document.getElementById('cover_image_input')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        openCropModal(file, 'cover');
    }
});

document.getElementById('images_input')?.addEventListener('change', function(e) {
    const files = Array.from(e.target.files);
    if (files.length > 0) {
        // Adicionar todas as imagens à fila de processamento
        files.forEach(file => {
            pendingImages.push(file);
        });
        // Processar primeira imagem
        processNextPendingImage();
    }
});

// Modificar submit para enviar imagens processadas
(function() {
    console.log('🔧 Inicializando handler de submit...');
    
    // Esperar DOM estar pronto
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', attachSubmitHandler);
    } else {
        attachSubmitHandler();
    }
    
    function attachSubmitHandler() {
        const form = document.querySelector('form');
        if (!form) {
            console.error('❌ Formulário não encontrado!');
            return;
        }
        
        console.log('✅ Formulário encontrado, anexando handler...');
        form.addEventListener('submit', handleSubmit);
    }
    
    function handleSubmit(e) {
        console.log('🚀 Submit interceptado!');
        e.preventDefault();
        e.stopPropagation();
        const form = e.target;
        
        // Validação básica de campos obrigatórios
        const name = form.querySelector('input[name="name"]')?.value?.trim();
        const categoryId = form.querySelector('select[name="category_id"]')?.value;
        const price = form.querySelector('input[name="price"]')?.value;
        
        if (!name) {
            alert('Por favor, preencha o nome do produto.');
            form.querySelector('input[name="name"]')?.focus();
            return;
        }
        
        if (!categoryId) {
            alert('Por favor, selecione uma categoria.');
            form.querySelector('select[name="category_id"]')?.focus();
            return;
        }
        
        if (!price || parseFloat(price) < 0) {
            alert('Por favor, preencha um preço válido.');
            form.querySelector('input[name="price"]')?.focus();
            return;
        }
    
    // Criar FormData manualmente para ter controle total
    const formData = new FormData();
    
    // Copiar todos os campos do formulário, exceto os inputs de arquivo
    const formElements = form.elements;
    for (let element of formElements) {
        if (element.name && element.type !== 'file' && !element.name.includes('_cropped')) {
            if (element.type === 'checkbox') {
                // Para checkboxes, enviar apenas se estiverem marcados
                // Campos booleanos do Laravel tratam ausência como false
                if (element.checked) {
                    formData.append(element.name, element.value || '1');
                }
                // Se não estiver marcado, não enviar (Laravel tratará como false)
            } else if (element.type === 'radio') {
                if (element.checked) {
                    formData.append(element.name, element.value);
                }
            } else if (element.tagName === 'SELECT' && element.multiple) {
                Array.from(element.selectedOptions).forEach(opt => {
                    formData.append(element.name + '[]', opt.value);
                });
            } else if (element.tagName === 'SELECT') {
                // Select simples (não múltiplo)
                if (element.value !== '') {
                    formData.append(element.name, element.value);
                }
            } else if (element.name && element.name.includes('variant_')) {
                // Campos de variantes - sempre incluir, mesmo se vazios
                if (element.type === 'checkbox') {
                    // Para checkboxes de variantes, enviar apenas se marcado
                    if (element.checked) {
                        formData.append(element.name, element.value || '1');
                    }
                } else {
                    formData.append(element.name, element.value || '');
                }
            } else if (element.value !== '' || element.tagName === 'TEXTAREA') {
                // Incluir textareas mesmo se vazios (podem ser nullable)
                formData.append(element.name, element.value);
            }
        }
    }
    
    // Processar imagem de capa
    const coverInput = document.getElementById('cover_image_input');
    const hasCoverFile = coverInput && coverInput.files.length > 0;
    const hasCroppedCover = croppedImages.cover && typeof croppedImages.cover === 'string';
    
    console.log('🔍 Debug imagem de capa:', {
        hasCoverFile,
        hasCroppedCover,
        croppedImagesCoverType: typeof croppedImages.cover,
        croppedImagesCoverLength: croppedImages.cover ? croppedImages.cover.length : 0,
        coverInputValue: coverInput ? coverInput.value : 'input não encontrado'
    });
    
    if (hasCroppedCover) {
        // Prioridade: imagem cortada (processada)
        try {
            const blob = dataURLtoBlob(croppedImages.cover);
            if (blob && blob instanceof Blob) {
                formData.append('cover_image', blob, 'cover.jpg');
                console.log('✅ Enviando imagem de capa processada (cortada)', {
                    blobSize: blob.size,
                    blobType: blob.type
                });
            } else {
                console.error('❌ Erro ao criar blob da imagem cortada');
                // Fallback para arquivo original se houver
                if (hasCoverFile) {
                    formData.append('cover_image', coverInput.files[0]);
                    console.log('✅ Fallback: enviando arquivo original');
                }
            }
        } catch (error) {
            console.error('❌ Erro ao processar imagem cortada:', error);
            // Fallback para arquivo original se houver
            if (hasCoverFile) {
                formData.append('cover_image', coverInput.files[0]);
                console.log('✅ Fallback: enviando arquivo original após erro');
            }
        }
    } else if (hasCoverFile) {
        // Fallback: arquivo original (caso o modal tenha sido fechado sem aplicar)
        formData.append('cover_image', coverInput.files[0]);
        console.log('✅ Enviando imagem de capa original (sem crop)', {
            fileName: coverInput.files[0].name,
            fileSize: coverInput.files[0].size,
            fileType: coverInput.files[0].type
        });
    } else {
        console.log('⚠️ Nenhuma imagem de capa selecionada');
    }
    
    // Processar imagens adicionais
    if (croppedImages.images && croppedImages.images.length > 0) {
        croppedImages.images.forEach((img, index) => {
            const blob = dataURLtoBlob(img);
            formData.append('images[]', blob, `image-${index}.jpg`);
        });
        console.log('✅ Enviando', croppedImages.images.length, 'imagens processadas');
    } else {
        // Se não tem imagens cortadas, verificar se há arquivos selecionados
        const originalImagesInput = document.getElementById('images_input');
        if (originalImagesInput && originalImagesInput.files.length > 0) {
            Array.from(originalImagesInput.files).forEach(file => {
                formData.append('images[]', file);
            });
            console.log('✅ Enviando', originalImagesInput.files.length, 'imagens originais');
        }
    }
    
    // Debug: verificar se cover_image está no FormData
    const formDataEntries = Array.from(formData.entries());
    console.log('📦 FormData entries:', formDataEntries.map(([k, v]) => [k, v instanceof File ? `${v.name} (${v.size} bytes)` : v]));
    
    // Verificar especificamente se cover_image está presente
    const hasCoverImage = formDataEntries.some(([k]) => k === 'cover_image');
    console.log('🖼️ cover_image presente?', hasCoverImage);
    
    // Enviar via fetch
    fetch(form.action, {
        method: form.method,
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(async response => {
        if (response.redirected) {
            window.location.href = response.url;
            return;
        }
        
        // Verificar se a resposta é HTML (erro de validação) ou JSON
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            const data = await response.json();
            if (data.errors) {
                // Erros de validação
                let errorMsg = 'Erro ao salvar produto:\n\n';
                Object.keys(data.errors).forEach(key => {
                    errorMsg += `${key}: ${data.errors[key].join(', ')}\n`;
                });
                alert(errorMsg);
            } else if (data.message) {
                alert('Erro: ' + data.message);
            } else {
                alert('Erro desconhecido ao salvar produto.');
            }
        } else {
            // Resposta HTML (pode conter erros de validação)
            const html = await response.text();
            document.open();
            document.write(html);
            document.close();
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao salvar produto: ' + (error.message || 'Erro de conexão. Verifique sua internet e tente novamente.'));
    });
    }
})();

function dataURLtoBlob(dataURL) {
    try {
        if (!dataURL || typeof dataURL !== 'string') {
            console.error('dataURLtoBlob: dataURL inválido', typeof dataURL);
            return null;
        }
        
        const arr = dataURL.split(',');
        if (arr.length !== 2) {
            console.error('dataURLtoBlob: formato inválido', arr.length);
            return null;
        }
        
        const mimeMatch = arr[0].match(/:(.*?);/);
        if (!mimeMatch) {
            console.error('dataURLtoBlob: mime type não encontrado');
            return null;
        }
        
        const mime = mimeMatch[1];
        const bstr = atob(arr[1]);
        let n = bstr.length;
        const u8arr = new Uint8Array(n);
        while (n--) {
            u8arr[n] = bstr.charCodeAt(n);
        }
        
        const blob = new Blob([u8arr], { type: mime });
        console.log('dataURLtoBlob: blob criado', {
            size: blob.size,
            type: blob.type,
            mime: mime
        });
        return blob;
    } catch (error) {
        console.error('dataURLtoBlob: erro ao converter', error);
        return null;
    }
}
</script>
<script>
function addVariantRow(v={}){
  const list = document.getElementById('variantsList');
  const idx = list.children.length;
  const row = document.createElement('div');
  row.className = 'grid md:grid-cols-7 gap-2 p-3 border rounded items-center';
  row.dataset.variantId = v.id || '';
  row.dataset.variantIdx = idx;
  row.innerHTML = `
    <input type="hidden" name="variant_id[${idx}]" value="${v.id||''}" class="variant-id-input">
    <input name="variant_name[${idx}]" value="${v.name||''}" placeholder="Nome (ex.: 500g)" class="border rounded px-2 py-1">
    <input name="variant_price[${idx}]" value="${v.price||''}" type="number" step="0.01" placeholder="Preço" class="border rounded px-2 py-1">
    <input name="variant_weight[${idx}]" value="${v.weight_grams||''}" type="number" step="1" min="0" placeholder="Peso (g)" class="border rounded px-2 py-1">
    <input name="variant_sku[${idx}]" value="${v.sku||''}" placeholder="SKU" class="border rounded px-2 py-1">
    <input name="variant_sort[${idx}]" value="${v.sort_order||0}" type="number" placeholder="#" class="border rounded px-2 py-1">
    <label class="flex items-center gap-2"><input type="checkbox" name="variant_active[${idx}]" ${v.is_active===false?'':'checked'}> Ativo</label>
    <button type="button" onclick="removeVariantRow(${idx})" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 text-sm">Remover</button>
  `;
  list.appendChild(row);
}

function removeVariantRow(idx) {
  const row = document.querySelector(`[data-variant-idx="${idx}"]`);
  if (row) {
    row.remove();
    // Reindexar os inputs restantes
    reindexVariants();
  }
}

function reindexVariants() {
  const list = document.getElementById('variantsList');
  const rows = list.querySelectorAll('div[data-variant-idx]');
  rows.forEach((row, newIdx) => {
    const oldIdx = row.dataset.variantIdx;
    row.dataset.variantIdx = newIdx;
    
    // Atualizar todos os inputs com o novo índice
    row.querySelectorAll('input, label').forEach(input => {
      if (input.name) {
        input.name = input.name.replace(/\[\d+\]/, `[${newIdx}]`);
      }
      if (input.htmlFor) {
        input.htmlFor = input.htmlFor.replace(/\d+/, newIdx);
      }
    });
    
    // Atualizar onclick do botão de remover
    const removeBtn = row.querySelector('button[onclick*="removeVariantRow"]');
    if (removeBtn) {
      removeBtn.setAttribute('onclick', `removeVariantRow(${newIdx})`);
    }
  });
}
// Atualizar contadores de caracteres
function updateCharCounters() {
  const seoTitle = document.getElementById('seo_title');
  const seoDesc = document.getElementById('seo_description');
  const titleCount = document.getElementById('seo_title_count');
  const descCount = document.getElementById('seo_desc_count');
  
  if (seoTitle && titleCount) {
    titleCount.textContent = seoTitle.value.length;
  }
  if (seoDesc && descCount) {
    descCount.textContent = seoDesc.value.length;
  }
}

// Gerar SEO via IA
async function generateSEO() {
  const name = document.querySelector('input[name="name"]')?.value;
  const description = document.querySelector('textarea[name="description"]')?.value || '';
  const categoryId = document.querySelector('select[name="category_id"]')?.value || null;
  const price = document.querySelector('input[name="price"]')?.value || null;
  const ingredients = document.querySelector('textarea[name="ingredients"]')?.value || '';
  
  if (!name) {
    alert('Por favor, preencha o nome do produto primeiro.');
    return;
  }
  
  const btn = document.getElementById('generateSeoBtn');
  const originalText = btn.innerHTML;
  btn.disabled = true;
  btn.innerHTML = '<span class="animate-spin">⏳</span> Gerando...';
  
  try {
    const response = await fetch('{{ route("dashboard.products.generateSEO") }}', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        name,
        description,
        category_id: categoryId ? parseInt(categoryId) : null,
        price: price ? parseFloat(price) : null,
        ingredients
      })
    });
    
    const data = await response.json();
    
    if (data.success) {
      if (data.seo_title) {
        document.getElementById('seo_title').value = data.seo_title;
      }
      if (data.seo_description) {
        document.getElementById('seo_description').value = data.seo_description;
      }
      updateCharCounters();
      alert('SEO gerado com sucesso!');
    } else {
      alert(data.message || 'Erro ao gerar SEO. Tente novamente.');
    }
  } catch (error) {
    console.error('Erro:', error);
    alert('Erro ao gerar SEO. Verifique sua conexão e tente novamente.');
  } finally {
    btn.disabled = false;
    btn.innerHTML = originalText;
  }
}

document.addEventListener('DOMContentLoaded', function() {
  // Atualizar contadores ao carregar
  updateCharCounters();
  
  // Atualizar contadores ao digitar
  const seoTitle = document.getElementById('seo_title');
  const seoDesc = document.getElementById('seo_description');
  
  if (seoTitle) {
    seoTitle.addEventListener('input', updateCharCounters);
  }
  if (seoDesc) {
    seoDesc.addEventListener('input', updateCharCounters);
  }
  
  // Botões de gerar SEO
  const generateSeoBtn = document.getElementById('generateSeoBtn');
  const generateSeoDescBtn = document.getElementById('generateSeoDescBtn');
  
  if (generateSeoBtn) {
    generateSeoBtn.addEventListener('click', generateSEO);
  }
  if (generateSeoDescBtn) {
    generateSeoDescBtn.addEventListener('click', generateSEO);
  }
});
</script>
@endpush

