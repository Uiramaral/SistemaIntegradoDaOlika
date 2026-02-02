@extends('dashboard.layouts.app')

@section('page_title', 'Preços de Revenda')
@section('page_subtitle', 'Gerenciamento de tabela de preços para revendedores')

@section('content')
    <div x-data="wholesalePricesPage({{ json_encode($productsList) }})" id="wholesale-prices-page"
        class="bg-card rounded-xl border border-border overflow-hidden">

        {{-- Card Header: Busca + Botão Novo --}}
        <div class="p-4 sm:p-6 border-b border-border">
            <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                {{-- Search --}}
                <div class="relative flex-1 min-w-0">
                    <i data-lucide="search"
                        class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground pointer-events-none"></i>
                    <input type="text" x-model="search" placeholder="Buscar na página..."
                        class="form-input pl-10 h-10 bg-muted/30 border-transparent focus:bg-white transition-all text-sm rounded-lg w-full"
                        autocomplete="off">
                </div>
                {{-- New Price Button --}}
                <button type="button" @click="openModal('create')"
                    class="inline-flex items-center justify-center gap-2 h-10 px-4 rounded-lg bg-primary hover:bg-primary/90 text-white font-semibold text-sm shadow-sm transition-colors shrink-0">
                    <i data-lucide="plus" class="h-4 w-4"></i>
                    <span>Novo Preço</span>
                </button>
            </div>
        </div>

        {{-- Prices Grid --}}
        <div class="p-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($prices as $price)
                @php
                    $productName = $price->product->name ?? 'Produto Removido';
                    if ($price->variant) {
                        $productName .= ' (' . $price->variant->name . ')';
                    }

                    $wholesaleValue = (float) ($price->wholesale_price ?? 0);
                    $isActive = $price->is_active;
                    $searchName = mb_strtolower($productName, 'UTF-8');

                    $priceJson = [
                        'id' => $price->id,
                        'product_id' => $price->product_id,
                        'variant_id' => $price->variant_id,
                        'wholesale_price' => $wholesaleValue,
                        'min_quantity' => $price->min_quantity,
                        'is_active' => (bool) $isActive,
                        'update_url' => route('dashboard.wholesale-prices.update', $price->id)
                    ];
                @endphp
                <div class="product-card bg-white border border-border rounded-xl p-4 hover:shadow-md transition-all overflow-hidden"
                    data-search-name="{{ $searchName }}" x-show="matchesCard($el)">

                    {{-- Header: Name & Actions --}}
                    <div class="flex items-start justify-between mb-3">
                        <button @click="openModal('edit', {{ json_encode($priceJson) }})"
                            class="block group text-left min-w-0 flex-1">
                            <h3 class="font-semibold text-foreground text-sm group-hover:text-primary transition-colors truncate"
                                title="{{ $productName }}">
                                {{ $productName }}
                            </h3>
                        </button>
                        <div class="flex items-center gap-1 shrink-0 ml-2">
                            <button @click="openModal('edit', {{ json_encode($priceJson) }})"
                                class="inline-flex items-center justify-center h-7 w-7 rounded-md hover:bg-muted transition-colors text-muted-foreground hover:text-foreground"
                                title="Editar">
                                <i data-lucide="edit" class="h-3.5 w-3.5"></i>
                            </button>
                            <form action="{{ route('dashboard.wholesale-prices.destroy', $price->id) }}" method="POST"
                                class="inline"
                                onsubmit="return confirm('Tem certeza que deseja remover este preço de revenda?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="inline-flex items-center justify-center h-7 w-7 rounded-md hover:bg-destructive/10 transition-colors text-muted-foreground hover:text-destructive"
                                    title="Excluir">
                                    <i data-lucide="trash-2" class="h-3.5 w-3.5"></i>
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Footer: Price & Toggle --}}
                    <div class="pt-3 border-t border-border flex items-center justify-between">
                        <div>
                            <p class="text-[10px] text-muted-foreground uppercase tracking-wide">Valor Revenda</p>
                            <p class="text-sm font-bold text-primary mt-0.5">R$
                                {{ number_format($wholesaleValue, 2, ',', '.') }}
                            </p>
                        </div>

                        <div class="flex flex-col items-end"
                            x-data="{ isActive: {{ $isActive ? 'true' : 'false' }}, isLoading: false }">
                            <p class="text-[10px] text-muted-foreground uppercase tracking-wide mb-1">Ativo</p>
                            <label class="relative inline-flex cursor-pointer">
                                <input type="checkbox" class="sr-only peer" :checked="isActive" @click="
                                                                if(isLoading) return;
                                                                isLoading = true;
                                                                fetch('{{ route('dashboard.wholesale-prices.toggle-status', $price->id) }}', {
                                                                    method: 'POST',
                                                                    headers: {
                                                                        'Content-Type': 'application/json',
                                                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                                                    }
                                                                })
                                                                .then(r => r.json())
                                                                .then(data => { if(data.success) isActive = data.is_active; })
                                                                .catch(e => console.error(e))
                                                                .finally(() => isLoading = false);
                                                            ">
                                <div
                                    class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary/50 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary">
                                </div>
                            </label>
                        </div>

                    </div>
                </div>
            @empty
                <div class="col-span-full text-center text-muted-foreground py-12">
                    <div class="flex flex-col items-center gap-3">
                        <i data-lucide="tag" class="w-12 h-12 opacity-20"></i>
                        <p class="text-sm">Nenhum preço de revenda cadastrado</p>
                        <button @click="openModal('create')"
                            class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-lg bg-primary hover:bg-primary/90 text-white font-semibold text-sm shadow-sm transition-colors">
                            <i data-lucide="plus" class="w-4 h-4"></i>
                            Cadastrar primeiro preço
                        </button>
                        <p class="text-xs text-muted-foreground mt-2 max-w-xs mx-auto">
                            Cadastre preços diferenciados para revendedores.
                        </p>
                    </div>
                </div>
            @endforelse

            {{-- No Results --}}
            <div class="col-span-full text-center text-muted-foreground py-8" x-show="search && showNoResults" x-cloak>
                <div class="flex flex-col items-center gap-2">
                    <i data-lucide="search-x" class="w-10 h-10 opacity-40"></i>
                    <p class="text-sm">Nenhum resultado para "<span x-text="search"></span>"</p>
                </div>
            </div>
        </div>

        {{-- Pagination --}}
        @if($prices->hasPages())
            <div class="px-4 sm:px-6 py-3 border-t border-border bg-muted/20">
                {{ $prices->links() }}
            </div>
        @endif

        {{-- MODAL --}}
        <div class="fixed inset-0 z-[100] overflow-y-auto" x-show="isModalOpen" x-cloak
            x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

            {{-- Backdrop --}}
            <div class="fixed inset-0 bg-black/50" @click="closeModal()"></div>

            {{-- Modal Container --}}
            <div class="flex min-h-full items-start sm:items-center justify-center p-4">
                <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden my-auto" @click.stop
                    x-show="isModalOpen" x-transition:enter="ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95">

                    <form :action="formAction" method="POST">
                        @csrf
                        <input type="hidden" name="_method" :value="isEditMode ? 'PUT' : 'POST'">

                        {{-- Header --}}
                        <div class="flex items-center justify-between px-6 py-4 border-b border-border">
                            <div>
                                <h2 class="text-lg font-semibold text-foreground"
                                    x-text="isEditMode ? 'Editar Preço de Revenda' : 'Novo Preço de Revenda'"></h2>
                                <p class="text-sm text-muted-foreground mt-0.5">Defina o valor diferenciado para este
                                    produto.</p>
                            </div>
                            <button type="button" @click="closeModal()"
                                class="p-2 rounded-lg text-muted-foreground hover:bg-muted hover:text-foreground transition-colors">
                                <i data-lucide="x" class="w-5 h-5"></i>
                            </button>
                        </div>

                        {{-- Body --}}
                        <div class="px-6 py-5 space-y-5 max-h-[60vh] overflow-y-auto">

                            {{-- Product Select --}}
                            <div>
                                <label class="block text-sm font-medium text-foreground mb-1">Produto <span
                                        class="text-destructive">*</span></label>
                                <select x-model="formData.product_variant_key" @change="onProductChange()"
                                    :disabled="isEditMode"
                                    class="w-full h-10 px-3 rounded-xl border border-border bg-white focus:border-primary focus:ring-1 focus:ring-primary text-sm disabled:bg-muted/50 disabled:cursor-not-allowed">
                                    <option value="">Selecione um produto...</option>
                                    @foreach($productsList as $index => $item)
                                        <option value="{{ $index }}">{{ $item['display_name'] }} (R$
                                            {{ number_format($item['price'], 2, ',', '.') }})
                                        </option>
                                    @endforeach
                                </select>
                                <div class="flex justify-between items-center mt-1.5 gap-2">
                                    <p class="text-xs text-muted-foreground">Produtos com variantes aparecem entre
                                        parênteses.</p>
                                    <a href="{{ route('dashboard.products.index') }}"
                                        class="text-xs text-primary hover:underline whitespace-nowrap flex items-center gap-1">
                                        <i data-lucide="external-link" class="w-3 h-3"></i>
                                        Cadastrar produto
                                    </a>
                                </div>
                                <input type="hidden" name="product_id" :value="formData.product_id">
                                <input type="hidden" name="variant_id" :value="formData.variant_id">
                            </div>

                            {{-- Base Price --}}
                            <div>
                                <label class="block text-sm font-medium text-foreground mb-1">Preço Base Atual</label>
                                <div class="relative">
                                    <span
                                        class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground text-sm">R$</span>
                                    <input type="text" :value="formatMoney(formData.base_price)" readonly
                                        class="w-full h-10 pl-10 pr-3 rounded-xl border border-border bg-muted/30 text-sm text-muted-foreground">
                                </div>
                            </div>

                            {{-- Calculation Mode --}}
                            <div>
                                <label class="block text-sm font-medium text-foreground mb-2">Modo de Cálculo</label>
                                <div class="flex gap-2">
                                    <button type="button" @click="setCalculationMode('fixed')"
                                        class="flex-1 px-4 py-2 text-sm font-medium rounded-xl border transition-colors"
                                        :class="formData.calculation_mode === 'fixed' ? 'bg-primary/10 border-primary text-primary' : 'bg-white border-border text-foreground hover:bg-muted/30'">
                                        Valor Fixo (R$)
                                    </button>
                                    <button type="button" @click="setCalculationMode('percentage')"
                                        class="flex-1 px-4 py-2 text-sm font-medium rounded-xl border transition-colors"
                                        :class="formData.calculation_mode === 'percentage' ? 'bg-primary/10 border-primary text-primary' : 'bg-white border-border text-foreground hover:bg-muted/30'">
                                        Desconto em %
                                    </button>
                                </div>
                            </div>

                            {{-- Fixed Price --}}
                            <div x-show="formData.calculation_mode === 'fixed'">
                                <label class="block text-sm font-medium text-foreground mb-1">Preço de Revenda (R$) <span
                                        class="text-destructive">*</span></label>
                                <div class="relative">
                                    <span
                                        class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground text-sm">R$</span>
                                    <input type="number" name="wholesale_price" step="0.01" min="0"
                                        x-model="formData.wholesale_price"
                                        class="w-full h-10 pl-10 pr-3 rounded-xl border border-border bg-white focus:border-primary focus:ring-1 focus:ring-primary text-sm"
                                        placeholder="0.00" :required="formData.calculation_mode === 'fixed'">
                                </div>
                            </div>

                            {{-- Percentage --}}
                            <div class="grid grid-cols-2 gap-3" x-show="formData.calculation_mode === 'percentage'">
                                <div>
                                    <label class="block text-sm font-medium text-foreground mb-1">Desconto (%) <span
                                            class="text-destructive">*</span></label>
                                    <div class="relative">
                                        <input type="number" step="0.01" min="0" max="100"
                                            x-model="formData.percentage_discount" @input="calculatePriceFromPercentage()"
                                            class="w-full h-10 px-3 pr-8 rounded-xl border border-border bg-white focus:border-primary focus:ring-1 focus:ring-primary text-sm"
                                            placeholder="10">
                                        <span
                                            class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground text-sm">%</span>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-foreground mb-1">Valor Final</label>
                                    <div class="relative">
                                        <span
                                            class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground text-sm">R$</span>
                                        <input type="text" :value="formatMoney(formData.wholesale_price)" readonly
                                            class="w-full h-10 pl-10 pr-3 rounded-xl border border-border bg-muted/30 text-sm text-muted-foreground">
                                    </div>
                                </div>
                            </div>

                            {{-- Active Toggle --}}
                            <label
                                class="flex items-center justify-between bg-muted/20 p-4 rounded-xl border border-border/50 cursor-pointer group">
                                <div>
                                    <span class="font-medium text-foreground block">Preço Ativo</span>
                                    <span class="text-xs text-muted-foreground">Define se este preço está disponível.</span>
                                </div>
                                <div class="relative">
                                    <input type="checkbox" x-model="formData.is_active" class="sr-only peer">
                                    <div
                                        class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary/50 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary">
                                    </div>
                                </div>
                                <input type="hidden" name="is_active" :value="formData.is_active ? 1 : 0">
                            </label>
                        </div>

                        {{-- Footer --}}
                        <div
                            class="px-6 py-4 border-t border-border flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
                            <button type="button" @click="closeModal()"
                                class="w-full sm:w-auto px-5 py-2.5 rounded-xl border border-border bg-white text-foreground font-medium hover:bg-muted/30 transition-colors text-sm">
                                Cancelar
                            </button>
                            <button type="submit"
                                class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-primary hover:bg-primary/90 text-white font-semibold shadow-sm transition-colors text-sm">
                                <span x-text="isEditMode ? 'Salvar Alterações' : 'Cadastrar Preço'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            [x-cloak] {
                display: none !important
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            document.addEventListener('alpine:init', function () {
                Alpine.data('wholesalePricesPage', function (productsList) {
                    return {
                        search: '',
                        showNoResults: false,
                        isModalOpen: false,
                        isEditMode: false,
                        formAction: '',
                        formAction: '',
                        productsAvailable: productsList || [],

                        matchesCard(el) {
                            if (!el) return false;
                            const searchLower = this.search.toLowerCase();
                            if (searchLower === '') return true;

                            const name = el.dataset.searchName || '';
                            return name.toLowerCase().includes(searchLower);
                        },

                        formData: {
                            id: null,
                            product_variant_key: '',
                            product_id: '',
                            variant_id: '',
                            base_price: 0,
                            wholesale_price: '',
                            min_quantity: 1,
                            is_active: true,
                            calculation_mode: 'fixed',
                            percentage_discount: 0
                        },

                        init() {
                            this.$watch('search', () => this.updateNoResults());
                            this.updateNoResults();
                        },

                        updateNoResults() {
                            this.$nextTick(() => {
                                const cards = document.querySelectorAll('#wholesale-prices-page .product-card');
                                let visible = 0;
                                cards.forEach(el => { if (this.matchesCard(el)) visible++; });
                                this.showNoResults = this.search.trim() !== '' && visible === 0;
                            });
                        },

                        performSearch() {
                            let url = new URL(window.location.href);
                            url.searchParams.set('q', this.search);
                            url.searchParams.delete('page');
                            window.location.href = url.toString();
                        },

                        openModal(mode, data = null) {
                            this.isModalOpen = true;
                            this.isEditMode = mode === 'edit';
                            if (mode === 'create') {
                                this.formAction = "{{ route('dashboard.wholesale-prices.store') }}";
                                this.resetForm();
                            } else {
                                this.formAction = data.update_url;
                                this.loadFormData(data);
                            }
                            this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
                        },

                        closeModal() {
                            this.isModalOpen = false;
                        },

                        resetForm() {
                            this.formData = {
                                id: null,
                                product_variant_key: '',
                                product_id: '',
                                variant_id: '',
                                base_price: 0,
                                wholesale_price: '',
                                min_quantity: 1,
                                is_active: true,
                                calculation_mode: 'fixed',
                                percentage_discount: 0
                            };
                        },

                        loadFormData(data) {
                            let foundKey = '';
                            for (let key in this.productsAvailable) {
                                let p = this.productsAvailable[key];
                                if (p.product_id == data.product_id && p.variant_id == data.variant_id) {
                                    foundKey = key;
                                    break;
                                }
                            }
                            this.formData = {
                                id: data.id,
                                product_variant_key: foundKey,
                                product_id: data.product_id,
                                variant_id: data.variant_id,
                                base_price: 0,
                                wholesale_price: data.wholesale_price,
                                min_quantity: data.min_quantity,
                                is_active: data.is_active,
                                calculation_mode: 'fixed',
                                percentage_discount: 0
                            };
                            this.onProductChange();
                        },

                        onProductChange() {
                            let key = this.formData.product_variant_key;
                            if (key !== '' && this.productsAvailable[key]) {
                                let product = this.productsAvailable[key];
                                this.formData.product_id = product.product_id;
                                this.formData.variant_id = product.variant_id;
                                this.formData.base_price = product.price;
                                if (this.formData.calculation_mode === 'percentage') {
                                    this.calculatePriceFromPercentage();
                                }
                            } else {
                                this.formData.base_price = 0;
                                this.formData.product_id = '';
                                this.formData.variant_id = '';
                            }
                        },

                        setCalculationMode(mode) {
                            this.formData.calculation_mode = mode;
                            if (mode === 'percentage' && !this.formData.percentage_discount) {
                                this.formData.percentage_discount = 0;
                            }
                        },

                        calculatePriceFromPercentage() {
                            if (this.formData.base_price > 0) {
                                let pct = parseFloat(this.formData.percentage_discount) || 0;
                                let discount = this.formData.base_price * (pct / 100);
                                this.formData.wholesale_price = (this.formData.base_price - discount).toFixed(2);
                            }
                        },

                        formatMoney(value) {
                            return new Intl.NumberFormat('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value || 0);
                        }
                    };
                });
            });
        </script>
    @endpush
@endsection```