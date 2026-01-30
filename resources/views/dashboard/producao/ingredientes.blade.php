@extends('dashboard.layouts.app')

@section('page_title', 'Ingredientes')
@section('page_subtitle', 'Gerenciamento de ingredientes')

<style>
    [x-cloak] { display: none !important; }
</style>

@section('page_actions')
    <a href="{{ route('dashboard.producao.ingredientes.create') }}" class="btn-primary gap-2">
        <i data-lucide="plus" class="h-4 w-4"></i>
        Novo Ingrediente
    </a>
@endsection

@section('content')
<div class="bg-card rounded-xl border border-border" x-data="ingredientsLiveSearch()" @click.away="document.querySelectorAll('[x-data*=\"ingredientRow\"]').forEach(el => { if (el._x_dataStack) { el._x_dataStack[0].editing = false; } })">
    <div class="p-4 border-b border-border flex flex-col sm:flex-row gap-4 justify-between">
        <div class="relative flex-1 max-w-md">
            <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground"></i>
            <input type="text" x-model="search" @input="filterIngredients()" placeholder="Buscar ingrediente..." class="form-input pl-10">
        </div>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-muted/50">
                <tr>
                    <th class="text-left text-xs font-semibold uppercase py-3 px-4">Ingrediente</th>
                    <th class="text-left text-xs font-semibold uppercase py-3 px-4">Categoria</th>
                    <th class="text-left text-xs font-semibold uppercase py-3 px-4">Unidade</th>
                    <th class="text-left text-xs font-semibold uppercase py-3 px-4">Estoque</th>
                    <th class="text-left text-xs font-semibold uppercase py-3 px-4">Mínimo</th>
                    <th class="text-left text-xs font-semibold uppercase py-3 px-4">Custo</th>
                    <th class="text-left text-xs font-semibold uppercase py-3 px-4">Ações</th>
                </tr>
            </thead>
            <tbody id="ingredients-tbody">
                @forelse($ingredients as $ingredient)
                <tr class="border-b border-border hover:bg-muted/30">
                    <td class="py-3 px-4 font-medium">{{ $ingredient->name }}</td>
                    <td class="py-3 px-4 text-sm text-muted-foreground">{{ $ingredient->category ?? 'outro' }}</td>
                    <td class="py-3 px-4 text-sm">{{ $ingredient->unit ?? 'g' }}</td>
                    <td class="py-3 px-4" x-data="ingredientRow({{ $ingredient->id }}, {{ $ingredient->stock }})">
                        <div x-show="!editing" @click="editing = true" class="cursor-pointer hover:opacity-80">
                            <span class="status-badge {{ $ingredient->stock_status === 'out_of_stock' ? 'status-badge-pending' : ($ingredient->stock_status === 'low_stock' ? 'status-badge-warning' : 'status-badge-completed') }}">
                                {{ number_format($ingredient->stock, 2, ',', '.') }}
                            </span>
                        </div>
                        <div x-show="editing" class="flex items-center gap-2" @click.stop x-cloak>
                            <input type="number" x-model="stock" step="0.01" min="0" class="form-input w-24 h-8 text-sm" @keyup.enter="updateStock()" @keyup.escape="editing = false; stock = originalStock" x-ref="stockInput">
                            <button @click="updateStock()" class="btn-primary h-8 px-2 text-xs" type="button">
                                <i data-lucide="check" class="h-3 w-3"></i>
                            </button>
                            <button @click="editing = false; stock = originalStock" class="btn-outline h-8 px-2 text-xs" type="button">
                                <i data-lucide="x" class="h-3 w-3"></i>
                            </button>
                        </div>
                    </td>
                    <td class="py-3 px-4 text-sm">{{ number_format($ingredient->min_stock, 2, ',', '.') }}</td>
                    <td class="py-3 px-4 font-semibold">R$ {{ number_format($ingredient->cost, 2, ',', '.') }}</td>
                    <td class="py-3 px-4">
                        <div class="flex gap-2">
                            <a href="{{ route('dashboard.producao.ingredientes.edit', $ingredient) }}" class="btn-outline h-8 w-8 p-0">
                                <i data-lucide="edit" class="h-4 w-4"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="py-12 text-center text-muted-foreground">
                        <i data-lucide="wheat" class="w-12 h-12 mx-auto mb-4 opacity-50"></i>
                        <p class="font-medium">Nenhum ingrediente cadastrado</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($ingredients->hasPages())
    <div class="p-4 border-t border-border">
        {{ $ingredients->links() }}
    </div>
    @endif
</div>

@push('scripts')
<script>
function ingredientsLiveSearch() {
    return {
        search: '',
        filterIngredients() {
            // Implementar busca similar às outras páginas
        },
        async updateStock(ingredientId) {
            const row = event.target.closest('tr');
            const stockInput = row.querySelector('input[type="number"]');
            const stock = parseFloat(stockInput.value) || 0;
            
            try {
                const response = await fetch(`/dashboard/producao/ingredientes/${ingredientId}/update-stock`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ stock: stock })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Recarregar a página para atualizar os badges de status
                    window.location.reload();
                } else {
                    alert(data.message || 'Erro ao atualizar estoque');
                }
            } catch (error) {
                console.error('Erro:', error);
                alert('Erro ao atualizar estoque');
            }
        }
    };
}

// Tornar updateStock disponível globalmente
document.addEventListener('alpine:init', () => {
    Alpine.data('ingredientRow', (ingredientId, currentStock) => ({
        editing: false,
        stock: currentStock,
        originalStock: currentStock,
        async updateStock() {
            try {
                const url = `{{ url('/dashboard/producao/ingredientes') }}/${ingredientId}/update-stock`;
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ stock: parseFloat(this.stock) || 0 })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.originalStock = this.stock;
                    this.editing = false;
                    // Recarregar a página para atualizar os badges de status
                    window.location.reload();
                } else {
                    alert(data.message || 'Erro ao atualizar estoque');
                    this.stock = this.originalStock;
                }
            } catch (error) {
                console.error('Erro:', error);
                alert('Erro ao atualizar estoque');
                this.stock = this.originalStock;
            }
        },
        init() {
            this.$watch('editing', (value) => {
                if (value) {
                    this.$nextTick(() => {
                        const input = this.$refs.stockInput;
                        if (input) {
                            input.focus();
                            input.select();
                        }
                    });
                }
            });
        }
    }));
});
</script>
@endpush
@endsection
