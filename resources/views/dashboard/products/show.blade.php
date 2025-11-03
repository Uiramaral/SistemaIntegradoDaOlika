@extends('dashboard.layouts.app')

@section('title', 'Visualizar Produto - OLIKA Dashboard')

@section('content')
<div class="space-y-6 animate-in fade-in duration-500">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold tracking-tight">{{ $product->name }}</h1>
            <p class="text-muted-foreground">{{ $product->category->name ?? 'Sem categoria' }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('dashboard.products.edit', $product) }}" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2">
                Editar
            </a>
            <form action="{{ route('dashboard.products.duplicate', $product) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2" onclick="return confirm('Deseja duplicar este produto? Uma cópia será criada e você será redirecionado para editá-la.')">
                    Duplicar
                </button>
            </form>
            <a href="{{ route('dashboard.products.index') }}" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2">
                Voltar
            </a>
        </div>
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

    <div class="grid gap-6 md:grid-cols-2">
        <!-- Imagens -->
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="p-6">
                <h2 class="text-xl font-semibold mb-4">Imagens</h2>
                @if($product->cover_image || ($product->images && $product->images->count() > 0))
                    <div class="space-y-4">
                        @if($product->cover_image)
                            <div>
                                <label class="text-sm text-muted-foreground mb-2 block">Imagem de Capa</label>
                                <img src="{{ asset('storage/' . $product->cover_image) }}" alt="{{ $product->name }}" class="w-full h-64 object-cover rounded-lg border">
                            </div>
                        @endif
                        @if($product->images && $product->images->count() > 0)
                            <div>
                                <label class="text-sm text-muted-foreground mb-2 block">Imagens Adicionais</label>
                                <div class="grid grid-cols-2 gap-4">
                                    @foreach($product->images as $image)
                                        <div class="relative">
                                            <img src="{{ asset('storage/' . $image->path) }}" alt="Imagem do produto" class="w-full h-32 object-cover rounded-lg border">
                                            @if($image->is_primary)
                                                <span class="absolute top-1 right-1 bg-blue-600 text-white text-xs px-2 py-1 rounded">Principal</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @else
                    <p class="text-muted-foreground">Nenhuma imagem cadastrada</p>
                @endif
            </div>
        </div>

        <!-- Informações Básicas -->
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="p-6 space-y-4">
                <h2 class="text-xl font-semibold">Informações Básicas</h2>
                
                <div>
                    <label class="text-sm text-muted-foreground">Nome</label>
                    <p class="text-base font-medium">{{ $product->name }}</p>
                </div>

                @if($product->sku)
                <div>
                    <label class="text-sm text-muted-foreground">SKU</label>
                    <p class="text-base font-medium">{{ $product->sku }}</p>
                </div>
                @endif

                <div>
                    <label class="text-sm text-muted-foreground">Categoria</label>
                    <p class="text-base font-medium">{{ $product->category->name ?? 'Sem categoria' }}</p>
                </div>

                <div>
                    <label class="text-sm text-muted-foreground">Preço</label>
                    <p class="text-2xl font-bold text-primary">R$ {{ number_format($product->price, 2, ',', '.') }}</p>
                </div>

                @if($product->stock !== null)
                <div>
                    <label class="text-sm text-muted-foreground">Estoque</label>
                    <p class="text-base font-medium">{{ $product->stock }} unidades</p>
                </div>
                @endif

                @if($product->preparation_time)
                <div>
                    <label class="text-sm text-muted-foreground">Tempo de Preparação</label>
                    <p class="text-base font-medium">{{ $product->preparation_time }} minutos</p>
                </div>
                @endif

                <div>
                    <label class="text-sm text-muted-foreground">Status</label>
                    <div class="flex gap-2 flex-wrap">
                        @if($product->is_active)
                            <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold border-transparent bg-primary text-primary-foreground">Ativo</span>
                        @else
                            <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold border-transparent bg-secondary text-secondary-foreground">Inativo</span>
                        @endif
                        @if($product->is_available)
                            <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold border-transparent bg-green-100 text-green-800">Disponível</span>
                        @else
                            <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold border-transparent bg-red-100 text-red-800">Indisponível</span>
                        @endif
                        @if($product->is_featured)
                            <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold border-transparent bg-yellow-100 text-yellow-800">Destaque</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Descrição e Alérgenicos -->
    <div class="grid gap-6 md:grid-cols-2">
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="p-6 space-y-4">
                <h2 class="text-xl font-semibold">Descrição</h2>
                @if($product->description)
                    <p class="text-base whitespace-pre-wrap">{{ $product->description }}</p>
                @else
                    <p class="text-muted-foreground">Sem descrição</p>
                @endif

                @if($product->label_description)
                    <div class="mt-4 pt-4 border-t">
                        <h3 class="text-sm font-medium mb-2">Descrição para Rótulo</h3>
                        <p class="text-sm whitespace-pre-wrap">{{ $product->label_description }}</p>
                    </div>
                @endif
                @if(data_get($product->nutritional_info, 'ingredients'))
                    <div class="mt-4 pt-4 border-t">
                        <h3 class="text-sm font-medium mb-2">Lista de Ingredientes</h3>
                        <p class="text-sm whitespace-pre-wrap">{{ data_get($product->nutritional_info, 'ingredients') }}</p>
                    </div>
                @endif
            </div>
        </div>

        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="p-6 space-y-4">
                <h2 class="text-xl font-semibold">Alérgenicos</h2>
                
                <div class="space-y-2">
                    <div class="flex items-center gap-2">
                        @if($product->gluten_free)
                            <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold border-transparent bg-green-100 text-green-800">✓ Sem Glúten</span>
                        @endif
                        @if($product->contamination_risk)
                            <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold border-transparent bg-yellow-100 text-yellow-800">⚠ Pode conter traços</span>
                        @endif
                    </div>

                    @if($product->allergens && $product->allergens->count() > 0)
                        <div>
                            <label class="text-sm text-muted-foreground mb-2 block">Contém:</label>
                            <div class="flex flex-wrap gap-2">
                                @foreach($product->allergens->groupBy('group_name') as $groupName => $groupAllergens)
                                    <div>
                                        @if($groupName)
                                            <span class="text-xs text-muted-foreground">{{ $groupName }}:</span>
                                        @endif
                                        @foreach($groupAllergens as $allergen)
                                            <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold border-transparent bg-orange-100 text-orange-800">{{ $allergen->name }}</span>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <p class="text-muted-foreground">Nenhum alérgenico marcado</p>
                    @endif

                    @if($product->allergen_text)
                        <div class="mt-4 pt-4 border-t">
                            <p class="text-sm text-muted-foreground">{{ $product->allergen_text }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- SEO e Outros -->
    @if($product->seo_title || $product->seo_description)
    <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
        <div class="p-6 space-y-4">
            <h2 class="text-xl font-semibold">SEO</h2>
            
            @if($product->seo_title)
            <div>
                <label class="text-sm text-muted-foreground">Título SEO</label>
                <p class="text-base font-medium">{{ $product->seo_title }}</p>
            </div>
            @endif

            @if($product->seo_description)
            <div>
                <label class="text-sm text-muted-foreground">Descrição SEO</label>
                <p class="text-base whitespace-pre-wrap">{{ $product->seo_description }}</p>
            </div>
            @endif
        </div>
    </div>
    @endif

    <!-- Ações -->
    <div class="flex justify-end gap-4">
        <form action="{{ route('dashboard.products.destroy', $product) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir este produto? Esta ação não pode ser desfeita.');">
            @csrf
            @method('DELETE')
            <button type="submit" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-destructive text-destructive-foreground hover:bg-destructive/90 h-10 px-4 py-2">
                Excluir Produto
            </button>
        </form>
        <a href="{{ route('dashboard.products.edit', $product) }}" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2">
            Editar Produto
        </a>
    </div>
</div>
@endsection

