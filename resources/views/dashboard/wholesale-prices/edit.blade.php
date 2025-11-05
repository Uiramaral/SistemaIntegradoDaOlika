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
                         <label for="product_option" class="text-sm font-medium leading-none">
                             Produto <span class="text-destructive">*</span>
                         </label>
                         <select id="product_option" name="product_option" required class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
                             <option value="">Selecione um produto</option>
                             @foreach($productsList as $item)
                                 @php
                                     $selectedValue = $item['product_id'] . '_' . ($item['variant_id'] ?? 'null');
                                     $currentValue = $wholesalePrice->product_id . '_' . ($wholesalePrice->variant_id ?? 'null');
                                 @endphp
                                 <option value="{{ $selectedValue }}" 
                                         data-product-id="{{ $item['product_id'] }}" 
                                         data-variant-id="{{ $item['variant_id'] ?? '' }}"
                                         @selected($currentValue === $selectedValue)>
                                     {{ $item['display_name'] }} (R$ {{ number_format($item['price'], 2, ',', '.') }})
                                 </option>
                             @endforeach
                         </select>
                         <p class="text-xs text-muted-foreground">Produtos com variantes aparecem com o nome da variante entre parênteses.</p>
                         <input type="hidden" id="product_id" name="product_id" value="{{ $wholesalePrice->product_id }}">
                         <input type="hidden" id="variant_id" name="variant_id" value="{{ $wholesalePrice->variant_id ?? '' }}">
                         @error('product_id')
                             <p class="text-sm text-destructive">{{ $message }}</p>
                         @enderror
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
document.addEventListener('DOMContentLoaded', function() {
    const productOptionSelect = document.getElementById('product_option');
    const productIdInput = document.getElementById('product_id');
    const variantIdInput = document.getElementById('variant_id');
    
    if (productOptionSelect) {
        productOptionSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            
            if (selectedOption && selectedOption.value) {
                const productId = selectedOption.getAttribute('data-product-id');
                const variantId = selectedOption.getAttribute('data-variant-id');
                
                // Atualizar campos hidden
                productIdInput.value = productId || '';
                variantIdInput.value = (variantId && variantId !== 'null' && variantId !== '') ? variantId : '';
            } else {
                productIdInput.value = '';
                variantIdInput.value = '';
            }
        });
    }
});
</script>
@endsection

