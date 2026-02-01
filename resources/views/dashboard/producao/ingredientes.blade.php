@extends('dashboard.layouts.app')

@section('page_title', 'Ingredientes')
@section('page_subtitle', 'Gerenciamento de ingredientes')

@section('page_actions')
    {{-- Actions handled inside the main content area for better mobile layout --}}
@endsection

@section('content')
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    <div x-data="ingredientsManager()" class="space-y-6" @click.away="closeAllEdits()">
        {{-- Header & Search --}}
        <div
            class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-card p-4 rounded-xl border border-border">
            <form action="{{ route('dashboard.producao.ingredientes.index') }}" method="GET"
                class="relative w-full sm:w-72">
                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground"></i>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Buscar ingrediente..."
                    class="form-input pl-10 w-full h-10">
            </form>
            <button @click="openCreateModal()" class="btn-primary w-full sm:w-auto gap-2 h-10">
                <i data-lucide="plus" class="h-4 w-4"></i>
                Novo Ingrediente
            </button>
        </div>

        {{-- Desktop Table View --}}
        <div class="hidden md:block bg-card rounded-xl border border-border overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-muted/50 border-b border-border">
                    <tr>
                        <th class="text-left font-semibold uppercase py-3 px-4">Ingrediente</th>
                        <th class="text-left font-semibold uppercase py-3 px-4">Categoria</th>
                        <th class="text-left font-semibold uppercase py-3 px-4">Unidade</th>
                        <th class="text-left font-semibold uppercase py-3 px-4">Estoque</th>
                        <th class="text-left font-semibold uppercase py-3 px-4">Mínimo</th>
                        <th class="text-left font-semibold uppercase py-3 px-4">Custo</th>
                        <th class="text-right font-semibold uppercase py-3 px-4">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse($ingredients as $ingredient)
                        <tr class="hover:bg-muted/30 transition-colors">
                            <td class="py-3 px-4 font-medium">{{ $ingredient->name }}</td>
                            <td class="py-3 px-4 text-muted-foreground capitalize">{{ $ingredient->category ?? 'Outro' }}</td>
                            <td class="py-3 px-4">{{ $ingredient->unit ?? 'g' }}</td>
                            <td class="py-3 px-4" x-data="ingredientRow({{ $ingredient->id }}, {{ $ingredient->stock }})">
                                <div x-show="!editing" @click.stop="editing = true"
                                    class="cursor-pointer hover:bg-muted p-1 -m-1 rounded flex items-center gap-2 group">
                                    <span
                                        class="status-badge {{ $ingredient->stock_status === 'out_of_stock' ? 'status-badge-pending' : ($ingredient->stock_status === 'low_stock' ? 'status-badge-warning' : 'status-badge-completed') }}">
                                        {{ number_format($ingredient->stock, 2, ',', '.') }}
                                    </span>
                                    <i data-lucide="edit-2"
                                        class="w-3 h-3 text-muted-foreground opacity-0 group-hover:opacity-100 transition-opacity"></i>
                                </div>
                                <div x-show="editing" class="flex items-center gap-1" @click.stop x-cloak>
                                    <input type="number" x-model="stock" step="0.01" min="0"
                                        class="form-input w-20 h-7 text-xs px-2" @keyup.enter="updateStock()"
                                        @keyup.escape="cancelEdit()" x-ref="stockInput">
                                    <button @click="updateStock()"
                                        class="btn-ghost h-7 w-7 p-0 text-green-600 hover:text-green-700 hover:bg-green-50">
                                        <i data-lucide="check" class="h-4 w-4"></i>
                                    </button>
                                    <button @click="cancelEdit()"
                                        class="btn-ghost h-7 w-7 p-0 text-muted-foreground hover:text-destructive hover:bg-destructive/10">
                                        <i data-lucide="x" class="h-4 w-4"></i>
                                    </button>
                                </div>
                            </td>
                            <td class="py-3 px-4">{{ number_format($ingredient->min_stock, 2, ',', '.') }}</td>
                            <td class="py-3 px-4 font-semibold">R$ {{ number_format($ingredient->cost, 2, ',', '.') }}</td>
                            <td class="py-3 px-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('dashboard.producao.ingredientes.edit', $ingredient) }}"
                                        class="btn-icon btn-ghost h-8 w-8 text-muted-foreground hover:text-primary">
                                        <i data-lucide="pencil" class="h-4 w-4"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-muted-foreground">
                                <i data-lucide="wheat" class="w-12 h-12 mx-auto mb-4 opacity-50"></i>
                                <p>Nenhum ingrediente encontrado.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Card View --}}
        <div class="md:hidden grid grid-cols-1 gap-4">
            @forelse($ingredients as $ingredient)
                <div class="bg-card rounded-xl border border-border p-4 shadow-sm space-y-3">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="font-semibold text-foreground">{{ $ingredient->name }}</h3>
                            <p class="text-xs text-muted-foreground capitalize">{{ $ingredient->category ?? 'Outro' }} •
                                {{ $ingredient->unit ?? 'g' }}</p>
                        </div>
                        <div class="flex gap-2">
                            <a href="{{ route('dashboard.producao.ingredientes.edit', $ingredient) }}"
                                class="btn-icon btn-ghost h-8 w-8 text-muted-foreground">
                                <i data-lucide="pencil" class="h-4 w-4"></i>
                            </a>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 py-2 border-t border-border/50 border-b">
                        <div>
                            <p class="text-[10px] uppercase text-muted-foreground font-semibold">Estoque</p>
                            <div x-data="ingredientRow({{ $ingredient->id }}, {{ $ingredient->stock }})">
                                <div x-show="!editing" @click.stop="editing = true" class="flex items-center gap-2 mt-1">
                                    <span
                                        class="status-badge {{ $ingredient->stock_status === 'out_of_stock' ? 'status-badge-pending' : ($ingredient->stock_status === 'low_stock' ? 'status-badge-warning' : 'status-badge-completed') }}">
                                        {{ number_format($ingredient->stock, 2, ',', '.') }}
                                    </span>
                                </div>
                                <div x-show="editing" class="flex items-center gap-1 mt-1" @click.stop x-cloak>
                                    <input type="number" x-model="stock" step="0.01" class="form-input w-20 h-8 text-sm px-2"
                                        x-ref="stockInput">
                                    <button @click="updateStock()" class="btn-ghost h-8 w-8 p-0 text-green-600"><i
                                            data-lucide="check" class="h-4 w-4"></i></button>
                                </div>
                            </div>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase text-muted-foreground font-semibold">Custo</p>
                            <p class="font-medium mt-1">R$ {{ number_format($ingredient->cost, 2, ',', '.') }}</p>
                        </div>
                    </div>

                    <div class="flex justify-between items-center text-xs text-muted-foreground">
                        <span>Mínimo: {{ number_format($ingredient->min_stock, 2, ',', '.') }}</span>
                    </div>
                </div>
            @empty
                <div class="col-span-1 text-center py-12 text-muted-foreground bg-card rounded-xl border border-border">
                    <i data-lucide="wheat" class="w-12 h-12 mx-auto mb-4 opacity-50"></i>
                    <p>Nenhum ingrediente encontrado.</p>
                </div>
            @endforelse
        </div>

        @if($ingredients->hasPages())
            <div class="pb-6">
                {{ $ingredients->links() }}
            </div>
        @endif

        {{-- Create Modal --}}
        <div x-show="showCreateModal" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">

            <div class="bg-card w-full max-w-lg rounded-xl border border-border shadow-xl overflow-hidden flex flex-col max-h-[90vh]"
                @click.away="showCreateModal = false" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-4">

                <form action="{{ route('dashboard.producao.ingredientes.store') }}" method="POST"
                    class="flex flex-col h-full">
                    @csrf
                    <div class="p-4 sm:p-6 border-b border-border flex justify-between items-center bg-muted/20">
                        <h3 class="font-semibold text-lg">Novo Ingrediente</h3>
                        <button type="button" @click="showCreateModal = false"
                            class="text-muted-foreground hover:text-foreground">
                            <i data-lucide="x" class="h-5 w-5"></i>
                        </button>
                    </div>

                    <div class="p-4 sm:p-6 space-y-4 overflow-y-auto">
                        <div>
                            <label class="block text-sm font-medium mb-1.5">Nome do Ingrediente <span
                                    class="text-red-500">*</span></label>
                            <input type="text" name="name" required class="form-input w-full"
                                placeholder="Ex: Farinha de Trigo">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-1.5">Categoria</label>
                                <input type="text" name="category" list="categories" class="form-input w-full"
                                    placeholder="Ex: Farinhas">
                                <datalist id="categories">
                                    @foreach($categories ?? [] as $cat)
                                        <option value="{{ $cat }}">
                                    @endforeach
                                </datalist>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1.5">Unidade</label>
                                <select name="unit" class="form-input w-full">
                                    <option value="g">Gramas (g)</option>
                                    <option value="kg">Quilos (kg)</option>
                                    <option value="ml">Mililitros (ml)</option>
                                    <option value="l">Litros (l)</option>
                                    <option value="un">Unidade (un)</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-1.5">Preço de Custo (R$)</label>
                                <input type="number" name="cost" step="0.01" min="0" class="form-input w-full"
                                    placeholder="0,00">
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1.5">Estoque Atual</label>
                                <input type="number" name="stock" step="0.01" min="0" class="form-input w-full"
                                    placeholder="0">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1.5">Estoque Mínimo (Alerta)</label>
                            <input type="number" name="min_stock" step="0.01" min="0" class="form-input w-full"
                                placeholder="0">
                        </div>
                    </div>

                    <div class="p-4 border-t border-border bg-muted/20 flex justify-end gap-3">
                        <button type="button" @click="showCreateModal = false" class="btn-ghost">Cancelar</button>
                        <button type="submit" class="btn-primary">Salvar Ingrediente</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                // Gerenciador principal da página
                Alpine.data('ingredientsManager', () => ({
                    showCreateModal: false,

                    openCreateModal() {
                        this.showCreateModal = true;
                    },

                    closeAllEdits() {
                        // Fecha todas as edições de linha ao clicar fora
                        document.querySelectorAll('[x-data]').forEach(el => {
                            if (el._x_dataStack && el._x_dataStack[0].editing !== undefined) {
                                el._x_dataStack[0].editing = false;
                                if (el._x_dataStack[0].cancelEdit) el._x_dataStack[0].cancelEdit();
                            }
                        });
                    }
                }));

                // Lógica para edição de estoque na linha
                Alpine.data('ingredientRow', (ingredientId, currentStock) => ({
                    editing: false,
                    stock: currentStock,
                    originalStock: currentStock,

                    cancelEdit() {
                        this.stock = this.originalStock;
                        this.editing = false;
                    },

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
                                window.location.reload(); // Recarrega para atualizar status/cores
                            } else {
                                alert(data.message || 'Erro ao atualizar estoque');
                                this.cancelEdit();
                            }
                        } catch (error) {
                            console.error('Erro:', error);
                            alert('Erro na comunicação com o servidor');
                            this.cancelEdit();
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