@extends('dashboard.layouts.app')

@section('title', 'Editar Categoria')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('dashboard.categories.index') }}" class="inline-flex items-center justify-center rounded-md p-2 hover:bg-accent hover:text-accent-foreground">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-left h-5 w-5">
                    <path d="m12 19-7-7 7-7"></path>
                    <path d="M19 12H5"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-3xl font-bold tracking-tight">Editar Categoria</h1>
                <p class="text-muted-foreground">Atualize as informações da categoria</p>
            </div>
        </div>
    </div>

    @if($errors->any())
    <div class="rounded-md border border-red-200 bg-red-50 text-red-700 p-4">
        <div class="font-semibold mb-1">Erros encontrados:</div>
        <ul class="list-disc list-inside text-sm">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
        <form action="{{ route('dashboard.categories.update', $category) }}" method="POST" class="p-6 space-y-6">
            @csrf
            @method('PUT')
            
            <div>
                <label class="block text-sm font-medium mb-2">Nome da Categoria *</label>
                <input type="text" name="name" value="{{ old('name', $category->name) }}" required class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                @error('name')<span class="text-sm text-red-600 mt-1 block">{{ $message }}</span>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Descrição</label>
                <textarea name="description" rows="4" class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">{{ old('description', $category->description) }}</textarea>
                @error('description')<span class="text-sm text-red-600 mt-1 block">{{ $message }}</span>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">URL da Imagem</label>
                <input type="url" name="image_url" value="{{ old('image_url', $category->image_url) }}" placeholder="https://exemplo.com/imagem.jpg" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                @if($category->image_url)
                <div class="mt-2">
                    <img src="{{ $category->image_url }}" alt="{{ $category->name }}" class="h-20 w-20 object-cover rounded-md border">
                </div>
                @endif
                @error('image_url')<span class="text-sm text-red-600 mt-1 block">{{ $message }}</span>@enderror
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-2">Ordem de Exibição</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $category->sort_order) }}" min="0" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                    <p class="text-xs text-muted-foreground mt-1">Menor número aparece primeiro</p>
                    @error('sort_order')<span class="text-sm text-red-600 mt-1 block">{{ $message }}</span>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Tipo de Exibição</label>
                    <select name="display_type" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                        <option value="grid" {{ old('display_type', $category->display_type ?? 'grid') === 'grid' ? 'selected' : '' }}>Grid (Grade)</option>
                        <option value="list_horizontal" {{ old('display_type', $category->display_type ?? 'grid') === 'list_horizontal' ? 'selected' : '' }}>Lista Horizontal (Rolagem)</option>
                        <option value="list_vertical" {{ old('display_type', $category->display_type ?? 'grid') === 'list_vertical' ? 'selected' : '' }}>Lista Vertical</option>
                    </select>
                    <p class="text-xs text-muted-foreground mt-1">Como os produtos serão exibidos no catálogo</p>
                    @error('display_type')<span class="text-sm text-red-600 mt-1 block">{{ $message }}</span>@enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Status</label>
                <label class="inline-flex items-center gap-2 cursor-pointer mt-2">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $category->is_active) ? 'checked' : '' }} class="rounded border-gray-300">
                    <span class="text-sm">Categoria ativa</span>
                </label>
            </div>

            <div class="flex gap-3 justify-end">
                <a href="{{ route('dashboard.categories.index') }}" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2">
                    Cancelar
                </a>
                <button type="submit" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2">
                    Atualizar Categoria
                </button>
            </div>
        </form>
    </div>
</div>
@endsection


