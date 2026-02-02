@extends('dashboard.layouts.app')

@section('page_title', 'Análise de Custos')
@section('page_subtitle', 'Custos de produção e precificação')

@section('content')


    <style>
        [x-cloak] {
            display: none !important;
        }

        /* Enforce mobile consistency */
        @media (max-width: 640px) {
            #costs-page {
                overflow-x: hidden !important;
                width: 100% !important;
                max-width: 100vw !important;
            }
        }
    </style>

    <div class="space-y-6 w-full max-w-[100vw] overflow-x-hidden" id="costs-page" x-data="costCalculator">
        {{-- KPI Cards --}}
        {{-- KPI Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="stat-card group animate-fade-in" style="animation-delay: 100ms">
                <div class="flex items-center gap-4">
                    <div class="stat-card-icon bg-primary/10 text-primary group-hover:bg-primary group-hover:text-white transition-colors duration-300">
                        <i data-lucide="dollar-sign" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-muted-foreground">Custo Total Mensal</p>
                        <p class="text-xl font-bold text-foreground">R$ {{ number_format($totalMonthlyCost ?? 0, 2, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            <div class="stat-card group animate-fade-in" style="animation-delay: 200ms">
                <div class="flex items-center gap-4">
                    <div class="stat-card-icon bg-green-50 text-green-600 group-hover:bg-green-600 group-hover:text-white transition-colors duration-300">
                        <i data-lucide="trending-up" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-muted-foreground">Margem Média</p>
                        <p class="text-xl font-bold text-foreground">{{ number_format($averageMargin ?? 0, 1, ',', '.') }}%</p>
                    </div>
                </div>
            </div>

            <div class="stat-card group animate-fade-in" style="animation-delay: 300ms">
                <div class="flex items-center gap-4">
                    <div class="stat-card-icon bg-blue-50 text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-colors duration-300">
                        <i data-lucide="package" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-muted-foreground">Custo Médio/Produto</p>
                        <p class="text-xl font-bold text-foreground">R$ {{ number_format($averageCost ?? 0, 2, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            <div class="stat-card group animate-fade-in" style="animation-delay: 400ms">
                <div class="flex items-center gap-4">
                    <div class="stat-card-icon bg-amber-50 text-amber-600 group-hover:bg-amber-600 group-hover:text-white transition-colors duration-300">
                        <i data-lucide="alert-triangle" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-muted-foreground">Desperdício Estimado</p>
                        <p class="text-xl font-bold text-foreground">0%</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Calculator Section --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Form Column --}}
            <div class="lg:col-span-1 space-y-6 min-w-0">
                <div class="bg-card rounded-xl border border-border shadow-sm overflow-hidden animate-fade-in">
                    <!-- Standard Modal/Card Header -->
                    <div class="p-4 sm:p-6 border-b border-border bg-muted/20 flex items-center gap-3">
                        <div class="p-2 bg-yellow-50 text-yellow-600 rounded-lg">
                            <i data-lucide="calculator" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg text-foreground">Calculadora</h3>
                            <p class="text-xs text-muted-foreground">Simule custos e margens</p>
                        </div>
                    </div>

                    <!-- Body Content -->
                    <div class="p-4 sm:p-6">
                        <form @submit.prevent="calculateCosts()" class="space-y-4">
                            <div class="space-y-1.5" x-cloak>
                                <label class="block text-sm font-semibold mb-1.5">Receita</label>
                                <div class="relative" @click.outside="open = false">
                                    <div class="relative">
                                        <input
                                            type="text"
                                            x-model="search"
                                            @focus="open = true"
                                            @input="open = true"
                                            placeholder="Buscar receita..."
                                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                                        >
                                        <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-muted-foreground">
                                            <i data-lucide="search" class="w-4 h-4" x-show="!form.recipe_id"></i>
                                            <i data-lucide="check" class="w-4 h-4 text-green-500" x-show="form.recipe_id"></i>
                                        </div>
                                    </div>
                                    
                                    <div x-show="open && filteredRecipes.length > 0" 
                                         class="absolute z-10 w-full mt-1 bg-popover text-popover-foreground rounded-md border border-border shadow-md max-h-60 overflow-auto animate-in fade-in-0 zoom-in-95">
                                        <ul class="p-1">
                                            <template x-for="recipe in filteredRecipes" :key="recipe.id">
                                                <li @click="selectRecipe(recipe)"
                                                    class="relative flex cursor-pointer select-none items-center rounded-sm px-2 py-1.5 text-sm outline-none hover:bg-accent hover:text-accent-foreground data-[disabled]:pointer-events-none data-[disabled]:opacity-50">
                                                    <span x-text="recipe.name"></span>
                                                </li>
                                            </template>
                                        </ul>
                                    </div>
                                    <div x-show="open && filteredRecipes.length === 0" 
                                         class="absolute z-10 w-full mt-1 bg-popover text-popover-foreground rounded-md border border-border shadow-md p-2 text-sm text-muted-foreground text-center">
                                        Nenhuma receita encontrada.
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-1.5">
                                    <label class="block text-sm font-semibold mb-1.5">Peso da Simulação (g)</label>
                                    <input type="number" x-model="form.weight" placeholder="Ex: 250"
                                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                                    <p class="text-[10px] text-muted-foreground ml-1" x-show="form.recipe_id && !form.weight">
                                        Usando peso padrão da receita
                                    </p>
                                </div>
                                <div class="space-y-1.5 line-clamp-1">
                                    <div class="flex items-center justify-between mb-1.5">
                                        <label class="text-sm font-semibold">Embalagem</label>
                                        <a href="{{ route('dashboard.producao.embalagens.index') }}" class="text-[10px] text-primary hover:underline flex items-center gap-0.5">
                                            Gerenciar <i data-lucide="external-link" class="w-2 h-2"></i>
                                        </a>
                                    </div>
                                    <select x-model="form.packaging_id" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                                        <option value="">Nenhuma</option>
                                        @foreach($packagings as $pkg)
                                            <option value="{{ $pkg->id }}">{{ $pkg->name }} (R$ {{ number_format($pkg->cost, 2, ',', '.') }})</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-1.5">
                                    <label class="block text-sm font-semibold mb-1.5">Mult. Venda</label>
                                    <input type="number" x-model="form.sales_multiplier" step="0.1" min="0"
                                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                                </div>
                                <div class="space-y-1.5">
                                    <label class="block text-sm font-semibold mb-1.5">Mult. Revenda</label>
                                    <input type="number" x-model="form.resale_multiplier" step="0.1" min="0"
                                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                                </div>
                            </div>

                            <button type="submit" class="btn-primary w-full justify-center h-11 text-sm font-bold shadow-md hover:shadow-lg transition-all" :disabled="loading">
                                <span x-show="!loading" class="flex items-center gap-2">
                                    <i data-lucide="refresh-cw" class="w-4 h-4"></i> 
                                    Calcular Custos
                                </span>
                                <span x-show="loading" x-cloak class="flex items-center gap-2">
                                    <svg class="animate-spin h-4 w-4 text-white" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    Processando...
                                </span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Results Column --}}
            <div class="lg:col-span-2 space-y-6 min-w-0">
                {{-- Empty State --}}
                <div x-show="!results && !loading"
                    class="bg-card rounded-xl border border-dashed border-border p-12 flex flex-col items-center justify-center text-center h-full min-h-[400px] animate-fade-in">
                    <div class="w-20 h-20 bg-muted/30 rounded-full flex items-center justify-center mb-6">
                        <i data-lucide="pie-chart" class="w-10 h-10 text-muted-foreground opacity-40"></i>
                    </div>
                    <h4 class="text-xl font-bold text-foreground mb-2">Aguardando Cálculo</h4>
                    <p class="text-muted-foreground max-w-sm mx-auto">
                        Selecione as configurações ao lado para descobrir o custo exato da sua produção e as sugestões de preço para venda.
                    </p>
                </div>

                {{-- Results Content --}}
                <div x-show="results" x-cloak class="space-y-6 animate-fade-in">
                    {{-- Breakdown Cards --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-white p-5 rounded-xl border border-border shadow-sm hover:shadow-md transition-all">
                            <p class="text-[10px] font-bold uppercase tracking-[0.1em] text-blue-600 mb-2">Custo Direto</p>
                            <p class="text-2xl font-black text-foreground"
                                x-text="'R$ ' + formatCurrency(results?.total_ingredient_cost)"></p>
                            <div class="mt-4 pt-3 border-t border-dashed border-border space-y-2">
                                <div class="flex justify-between text-xs">
                                    <span class="text-muted-foreground">Ingredientes</span>
                                    <span class="font-bold text-foreground" x-text="'R$ ' + formatCurrency(results?.ingredient_cost)"></span>
                                </div>
                                <div class="flex justify-between text-xs">
                                    <span class="text-muted-foreground">Embalagem</span>
                                    <span class="font-bold text-foreground" x-text="'R$ ' + formatCurrency(results?.packaging_cost)"></span>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white p-5 rounded-xl border border-border shadow-sm hover:shadow-md transition-all">
                            <p class="text-[10px] font-bold uppercase tracking-[0.1em] text-orange-600 mb-2">Custos Fixos (30%)</p>
                            <p class="text-2xl font-black text-foreground"
                                x-text="'R$ ' + formatCurrency(results?.fixed_cost)"></p>
                            <div class="mt-4 pt-3 border-t border-dashed border-border">
                                <p class="text-[10px] text-muted-foreground leading-relaxed">Rateio de mão de obra, luz, aluguel e operacionais.</p>
                            </div>
                        </div>

                        <div class="bg-primary/5 p-5 rounded-xl border border-primary/20 shadow-sm relative overflow-hidden group">
                            <div class="relative z-10">
                                <p class="text-[10px] font-bold uppercase tracking-[0.1em] text-primary mb-2">Custo Total Final</p>
                                <p class="text-3xl font-black text-primary"
                                    x-text="'R$ ' + formatCurrency(results?.total_cost)"></p>
                                <div class="mt-4 pt-3 border-t border-primary/10">
                                    <p class="text-[10px] text-primary/70 font-medium">Base real para sua precificação</p>
                                </div>
                            </div>
                            <div class="absolute -right-4 -bottom-4 opacity-[0.03] group-hover:scale-110 transition-transform duration-500">
                                <i data-lucide="target" class="w-24 h-24"></i>
                            </div>
                        </div>
                    </div>

                    {{-- Suggested Pricing --}}
                    <div class="bg-card rounded-xl border border-border shadow-sm overflow-hidden animate-fade-in" style="animation-delay: 200ms">
                        <div class="p-4 sm:p-6 border-b border-border bg-muted/20 flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-green-50 text-green-600 rounded-lg">
                                    <i data-lucide="sparkles" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <h3 class="font-bold text-lg text-foreground">Sugestões de Precificação</h3>
                                    <p class="text-xs text-muted-foreground">Baseado nas configurações selecionadas</p>
                                </div>
                            </div>
                            <span class="px-2 py-1 bg-green-100 text-green-700 text-[10px] font-black uppercase tracking-wider rounded-md hidden sm:inline-block">Recomendado</span>
                        </div>
                        <div class="p-4 sm:p-6 grid grid-cols-1 md:grid-cols-2 gap-8 sm:gap-12">
                            <div class="relative">
                                <div class="flex items-center justify-between mb-4">
                                    <h5 class="text-sm font-bold text-muted-foreground uppercase tracking-widest">Preço Sugerido (Varejo)</h5>
                                    <div class="flex items-center gap-1 bg-primary/10 text-primary px-2 py-0.5 rounded text-[10px] font-bold">
                                        <i data-lucide="percent" class="w-2.5 h-2.5"></i>
                                        <span x-text="results?.margin_percentage + '% margem'"></span>
                                    </div>
                                </div>
                                <div class="flex items-baseline gap-2">
                                    <span class="text-xl font-bold text-primary/50">R$</span>
                                    <span class="text-3xl sm:text-5xl font-black text-primary tracking-tight leading-none" x-text="formatCurrency(results?.suggested_sale_price)"></span>
                                </div>

                                <div class="mt-8 p-4 bg-muted/30 rounded-xl border border-border inline-flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg bg-white flex items-center justify-center border border-border">
                                        <i data-lucide="credit-card" class="w-5 h-5 text-muted-foreground"></i>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-wider">Com taxa de cartão ({{ $settings->card_fee_percentage ?? 6.0 }}%)</p>
                                        <p class="text-lg font-bold text-foreground" x-text="'R$ ' + formatCurrency(results?.price_with_card_fee)"></p>
                                    </div>
                                </div>
                            </div>

                            <div class="md:border-l md:border-border md:pl-12 flex flex-col justify-center">
                                <h5 class="text-sm font-bold text-muted-foreground uppercase tracking-widest mb-4">Preço sugerido (Revenda)</h5>
                                <div class="flex items-baseline gap-2 mb-4">
                                    <span class="text-xl font-bold text-muted-foreground/50">R$</span>
                                    <span class="text-2xl sm:text-4xl font-black text-foreground tracking-tight leading-none" x-text="formatCurrency(results?.suggested_resale_price)"></span>
                                </div>
                                <p class="text-xs text-muted-foreground leading-relaxed italic">
                                    Calculado com base no seu multiplicador de revenda usual. Ideal para cafeterias e parceiros.
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Ingredients Detail Table --}}
                    <div class="bg-card rounded-xl border border-border shadow-sm overflow-hidden animate-fade-in" style="animation-delay: 300ms">
                        <div class="p-4 sm:p-6 border-b border-border bg-muted/20 flex items-center gap-3">
                             <div class="p-2 bg-blue-50 text-blue-600 rounded-lg">
                                <i data-lucide="list" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-lg text-foreground">Detalhamento de Ingredientes</h3>
                                <p class="text-xs text-muted-foreground">Composição de custo por item</p>
                            </div>
                        </div>
                        <div class="overflow-x-auto w-full max-w-full">
                            <table class="w-full text-sm text-left">
                                <thead class="bg-muted/5 text-[10px] font-bold uppercase tracking-wider text-muted-foreground">
                                    <tr>
                                        <th class="px-3 sm:px-6 py-3">Ingrediente</th>
                                        <th class="px-3 sm:px-6 py-3 text-right">Peso (g)</th>
                                        <th class="px-3 sm:px-6 py-3 text-right">Custo/g</th>
                                        <th class="px-3 sm:px-6 py-3 text-right">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-border">
                                    <template x-for="ing in results?.ingredient_details" :key="ing.name">
                                        <tr class="hover:bg-muted/5 transition-colors">
                                            <td class="px-3 sm:px-6 py-4 font-medium text-foreground" x-text="ing.name"></td>
                                            <td class="px-3 sm:px-6 py-4 text-right text-muted-foreground font-mono" x-text="Math.round(ing.weight) + 'g'"></td>
                                            <td class="px-3 sm:px-6 py-4 text-right text-muted-foreground font-mono" x-text="'R$ ' + ing.cost_per_gram.toFixed(4)"></td>
                                            <td class="px-3 sm:px-6 py-4 text-right font-bold text-foreground font-mono" x-text="'R$ ' + formatCurrency(ing.item_cost)"></td>
                                        </tr>
                                    </template>
                                </tbody>
                                <tfoot class="bg-muted/5 font-bold">
                                    <tr>
                                        <td class="px-3 sm:px-6 py-4 text-foreground">Custo Total de Ingredientes</td>
                                        <td class="px-3 sm:px-6 py-4 text-right"></td>
                                        <td class="px-3 sm:px-6 py-4 text-right"></td>
                                        <td class="px-3 sm:px-6 py-4 text-right text-primary" x-text="'R$ ' + formatCurrency(results?.ingredient_cost)"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('costCalculator', () => ({
                    form: {
                        recipe_id: '',
                        weight: '',
                        packaging_id: '',
                        sales_multiplier: {{ $settings->sales_multiplier ?? 3.5 }},
                        resale_multiplier: {{ $settings->resale_multiplier ?? 2.5 }},
                    },
                    results: null,
                    loading: false,

                    search: '',
                    open: false,
                    recipes: @json($recipes ?? []),
                    
                    get filteredRecipes() {
                        if (this.search === '') {
                            return this.recipes;
                        }
                        return this.recipes.filter(recipe => {
                            return recipe.name.toLowerCase().includes(this.search.toLowerCase());
                        });
                    },

                    init() {
                        this.$watch('form.recipe_id', (value) => {
                            if (value) {
                                // Clear weight to allow auto-loading the new recipe's default weight
                                this.form.weight = ''; 
                                this.calculateCosts();
                                
                                // Update search text to match selected recipe
                                const selected = this.recipes.find(r => r.id == value);
                                if (selected) {
                                    this.search = selected.name;
                                }
                            } else {
                                this.form.weight = '';
                                this.results = null;
                                this.search = '';
                            }
                        });
                    },

                    selectRecipe(recipe) {
                        this.form.recipe_id = recipe.id;
                        this.search = recipe.name;
                        this.open = false;
                    },

                    async calculateCosts() {
                        if (!this.form.recipe_id) return;

                        this.loading = true;

                        try {
                            const payload = {
                                recipe_id: this.form.recipe_id,
                                weight: this.form.weight || null,
                                packaging_id: this.form.packaging_id || null,
                                sales_multiplier: this.form.sales_multiplier || null,
                                resale_multiplier: this.form.resale_multiplier || null
                            };

                            const response = await fetch('{{ route("dashboard.producao.custos.calculate") }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify(payload)
                            });

                            const data = await response.json();

                            if (!response.ok) {
                                throw new Error(data.message || data.error || 'Erro ao calcular custos');
                            }

                            if (data.error) {
                                throw new Error(data.error);
                            }

                            this.results = data;

                            // Se o usuário não digitou peso, carregar o peso padrão que veio do cálculo
                            if (!this.form.weight && data.weight) {
                                this.form.weight = Math.round(data.weight);
                            }

                            this.$nextTick(() => {
                                if (typeof lucide !== 'undefined') lucide.createIcons();
                            });
                            console.error('Erro:', error);
                            alert(error.message || 'Ocorreu um erro ao calcular os custos. Verifique os dados e tente novamente.');
                        } finally {
                            this.loading = false;
                        }
                    },

                    formatCurrency(value) {
                        return new Intl.NumberFormat('pt-BR', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }).format(value || 0);
                    }
                }));
            });
        </script>
    @endpush
@endsection