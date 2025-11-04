@extends('dashboard.layouts.app')

@section('title', 'Editar Preço de Revenda - OLIKA Dashboard')

@section('content')
<div class="space-y-6 animate-in fade-in duration-500">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('dashboard.wholesale-prices.index') }}" class="inline-flex items-center justify-center rounded-md p-2 hover:bg-accent hover:text-accent-foreground">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-left h-5 w-5">
                    <path d="m12 19-7-7 7-7"></path>
                    <path d="M19 12H5"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-3xl font-bold tracking-tight">Editar Preço de Revenda</h1>
                <p class="text-muted-foreground">Atualize o preço diferenciado para clientes de revenda</p>
            </div>
        </div>
    </div>

    @if(session('error'))
        <div class="rounded-lg border bg-red-50 text-red-900 px-4 py-3">
            {{ session('error') }}
        </div>
    @endif

    <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
        <div class="flex flex-col space-y-1.5 p-6">
            <h3 class="text-lg font-semibold leading-none tracking-tight">Informações do Preço</h3>
        </div>
        <div class="p-6 pt-0">
            <form action="{{ route('dashboard.wholesale-prices.update', $wholesalePrice) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="space-y-2 md:col-span-2">
                        <label for="product_id" class="text-sm font-medium leading-none">
                            Produto <span class="text-destructive">*</span>
                        </label>
                        <select id="product_id" name="product_id" required class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm" onchange="updateVariants()">
                            <option value="">Selecione um produto</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" 
                                        data-variants="{{ $product->variants ? $product->variants->toJson() : '[]' }}"
                                        @selected($wholesalePrice->product_id == $product->id)>
                                    {{ $product->name }} (R$ {{ number_format($product->price, 2, ',', '.') }})
                                </option>
                            @endforeach
                        </select>
                        @error('product_id')
                            <p class="text-sm text-destructive">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2 md:col-span-2">
                        <label for="variant_id" class="text-sm font-medium leading-none">
                            Variante (opcional)
                        </label>
                        <select id="variant_id" name="variant_id" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
                            <option value="">Produto base (sem variante)</option>
                        </select>
                        <p class="text-xs text-muted-foreground">Deixe em branco para aplicar o preço ao produto base. Selecione uma variante para preço específico.</p>
                        @error('variant_id')
                            <p class="text-sm text-destructive">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="wholesale_price" class="text-sm font-medium leading-none">
                            Preço de Revenda (R$) <span class="text-destructive">*</span>
                        </label>
                        <input 
                            type="number" 
                            id="wholesale_price" 
                            name="wholesale_price" 
                            step="0.01"
                            min="0"
                            required
                            value="{{ old('wholesale_price', $wholesalePrice->wholesale_price) }}"
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                            placeholder="0.00"
                        >
                        @error('wholesale_price')
                            <p class="text-sm text-destructive">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="min_quantity" class="text-sm font-medium leading-none">
                            Quantidade Mínima
                        </label>
                        <input 
                            type="number" 
                            id="min_quantity" 
                            name="min_quantity" 
                            min="1"
                            value="{{ old('min_quantity', $wholesalePrice->min_quantity) }}"
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                            placeholder="1"
                        >
                        <p class="text-xs text-muted-foreground">Quantidade mínima para aplicar este preço</p>
                        @error('min_quantity')
                            <p class="text-sm text-destructive">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2 md:col-span-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input 
                                type="checkbox" 
                                id="is_active" 
                                name="is_active" 
                                value="1"
                                @checked($wholesalePrice->is_active)
                                class="rounded border-gray-300 text-primary focus:ring-primary"
                            >
                            <span class="text-sm font-medium">Preço ativo</span>
                        </label>
                        <p class="text-xs text-muted-foreground">Preços inativos não serão aplicados automaticamente</p>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <a href="{{ route('dashboard.wholesale-prices.index') }}" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2">
                        Cancelar
                    </a>
                    <button type="submit" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2">
                        Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const currentProductId = {{ $wholesalePrice->product_id }};
const currentVariantId = {{ $wholesalePrice->variant_id ?? 'null' }};

function updateVariants() {
    const productSelect = document.getElementById('product_id');
    const variantSelect = document.getElementById('variant_id');
    const selectedOption = productSelect.options[productSelect.selectedIndex];
    
    // Limpar opções atuais
    variantSelect.innerHTML = '<option value="">Produto base (sem variante)</option>';
    
    if (selectedOption.value) {
        try {
            const variants = JSON.parse(selectedOption.getAttribute('data-variants') || '[]');
            variants.forEach(variant => {
                const option = document.createElement('option');
                option.value = variant.id;
                option.textContent = `${variant.name} (R$ ${parseFloat(variant.price).toFixed(2).replace('.', ',')})`;
                if (currentVariantId && variant.id == currentVariantId) {
                    option.selected = true;
                }
                variantSelect.appendChild(option);
            });
        } catch(e) {
            console.error('Erro ao carregar variantes:', e);
        }
    }
}

// Carregar variantes ao carregar a página
document.addEventListener('DOMContentLoaded', function() {
    if (currentProductId) {
        updateVariants();
    }
});
</script>
@endsection

