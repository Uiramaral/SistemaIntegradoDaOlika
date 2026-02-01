@extends('dashboard.layouts.app')

@section('page_title', 'Análise de Custos')
@section('page_subtitle', 'Custos de produção e precificação')

@section('content')
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    <div class="space-y-6" x-data="costCalculator">
        {{-- KPI Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-card p-4 rounded-xl border border-border shadow-sm">
                <div class="flex items-center gap-3 mb-2">
                    <div class="p-2 bg-primary/10 text-primary rounded-lg">
                        <i data-lucide="dollar-sign" class="w-5 h-5"></i>
                    </div>
                    <p class="text-sm font-medium text-muted-foreground">Custo Total Mensal</p>
                </div>
                <p class="text-2xl font-bold text-foreground">R$ {{ number_format($totalMonthlyCost ?? 0, 2, ',', '.') }}
                </p>
            </div>

            <div class="bg-card p-4 rounded-xl border border-border shadow-sm">
                <div class="flex items-center gap-3 mb-2">
                    <div class="p-2 bg-green-50 text-green-600 rounded-lg">
                        <i data-lucide="trending-up" class="w-5 h-5"></i>
                    </div>
                    <p class="text-sm font-medium text-muted-foreground">Margem Média</p>
                </div>
                <p class="text-2xl font-bold text-foreground">{{ number_format($averageMargin ?? 0, 1, ',', '.') }}%</p>
            </div>

            <div class="bg-card p-4 rounded-xl border border-border shadow-sm">
                <div class="flex items-center gap-3 mb-2">
                    <div class="p-2 bg-blue-50 text-blue-600 rounded-lg">
                        <i data-lucide="package" class="w-5 h-5"></i>
                    </div>
                    <p class="text-sm font-medium text-muted-foreground">Custo Médio/Produto</p>
                </div>
                <p class="text-2xl font-bold text-foreground">R$ {{ number_format($averageCost ?? 0, 2, ',', '.') }}</p>
            </div>

            <div class="bg-card p-4 rounded-xl border border-border shadow-sm">
                <div class="flex items-center gap-3 mb-2">
                    <div class="p-2 bg-amber-50 text-amber-600 rounded-lg">
                        <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                    </div>
                    <p class="text-sm font-medium text-muted-foreground">Desperdício Estimado</p>
                </div>
                <p class="text-2xl font-bold text-foreground">0%</p>
            </div>
        </div>

        {{-- Calculator Section --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Form --}}
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-card rounded-xl border border-border p-5 shadow-sm">
                    <h3 class="font-semibold text-lg mb-4 flex items-center gap-2">
                        <i data-lucide="calculator" class="w-5 h-5 text-primary"></i>
                        Calculadora
                    </h3>

                    <form @submit.prevent="calculateCosts()" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-1.5">Receita</label>
                            <select x-model="form.recipe_id" required class="form-input w-full">
                                <option value="">Selecione uma receita...</option>
                                @forelse($recipes ?? [] as $recipe)
                                    <option value="{{ $recipe['id'] }}">{{ $recipe['name'] }}</option>
                                @empty
                                    <option value="" disabled>Nenhuma receita cadastrada</option>
                                @endforelse
                            </select>
                            @if(empty($recipes))
                                <p class="text-xs text-red-500 mt-1">Nenhuma receita encontrada.</p>
                            @endif
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold uppercase text-muted-foreground mb-1.5">Peso
                                    (g)</label>
                                <select x-model="form.weight" class="form-input w-full">
                                    <option value="">Padrão</option>
                                    <option value="400">400g</option>
                                    <option value="450">450g</option>
                                    <option value="500">500g</option>
                                    <option value="700">700g</option>
                                    <option value="750">750g</option>
                                </select>
                            </div>
                            <div>
                                <label
                                    class="block text-xs font-semibold uppercase text-muted-foreground mb-1.5">Embalagem</label>
                                <select x-model="form.packaging_id" class="form-input w-full">
                                    <option value="">Nenhuma</option>
                                    @foreach(\App\Models\Packaging::where('is_active', true)->orderBy('name')->get() as $pkg)
                                        <option value="{{ $pkg->id }}">{{ $pkg->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold uppercase text-muted-foreground mb-1.5">Mult.
                                    Venda</label>
                                <input type="number" x-model="form.sales_multiplier" step="0.1" min="0"
                                    class="form-input w-full">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase text-muted-foreground mb-1.5">Mult.
                                    Revenda</label>
                                <input type="number" x-model="form.resale_multiplier" step="0.1" min="0"
                                    class="form-input w-full">
                            </div>
                        </div>

                        <button type="submit" class="btn-primary w-full justify-center" :disabled="loading">
                            <span x-show="!loading" class="flex items-center gap-2"><i data-lucide="refresh-cw"
                                    class="w-4 h-4"></i> Calcular</span>
                            <span x-show="loading" x-cloak>Processando...</span>
                        </button>
                    </form>
                </div>
            </div>

            {{-- Results --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Default State --}}
                <div x-show="!results && !loading"
                    class="bg-card rounded-xl border border-border p-10 flex flex-col items-center justify-center text-center text-muted-foreground h-full min-h-[300px]">
                    <div class="bg-muted/30 p-4 rounded-full mb-4">
                        <i data-lucide="pie-chart" class="w-10 h-10 opacity-50"></i>
                    </div>
                    <h4 class="text-lg font-medium text-foreground">Aguardando Cálculo</h4>
                    <p class="max-w-xs mx-auto mt-2">Selecione uma receita e clique em calcular para ver a análise detalhada
                        de custos.</p>
                </div>

                {{-- Results State --}}
                <div x-show="results" x-cloak class="space-y-6">
                    {{-- Main Cost Breakdown --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-blue-50/50 p-4 rounded-xl border border-blue-100">
                            <p class="text-xs font-semibold uppercase text-blue-700 mb-1">Custo Direto (Ing + Emb)</p>
                            <p class="text-2xl font-bold text-blue-900"
                                x-text="'R$ ' + formatCurrency(results?.total_ingredient_cost)"></p>
                            <div class="mt-2 text-xs text-blue-700/70 space-y-1">
                                <div class="flex justify-between">
                                    <span>Ingredientes:</span>
                                    <span x-text="'R$ ' + formatCurrency(results?.ingredient_cost)"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Embalagem:</span>
                                    <span x-text="'R$ ' + formatCurrency(results?.packaging_cost)"></span>
                                </div>
                            </div>
                        </div>

                        <div class="bg-amber-50/50 p-4 rounded-xl border border-amber-100">
                            <p class="text-xs font-semibold uppercase text-amber-700 mb-1">Custos Fixos (30%)</p>
                            <p class="text-2xl font-bold text-amber-900"
                                x-text="'R$ ' + formatCurrency(results?.fixed_cost)"></p>
                            <p class="text-xs text-amber-700/70 mt-2">Rateio operacional estimado</p>
                        </div>

                        <div class="bg-slate-100 p-4 rounded-xl border border-slate-200">
                            <p class="text-xs font-semibold uppercase text-slate-700 mb-1">Custo Total Final</p>
                            <p class="text-2xl font-bold text-slate-900"
                                x-text="'R$ ' + formatCurrency(results?.total_cost)"></p>
                            <p class="text-xs text-slate-600 mt-2">Base para precificação</p>
                        </div>
                    </div>

                    {{-- Pricing Suggestions --}}
                    <div class="bg-card rounded-xl border border-border overflow-hidden">
                        <div class="p-4 border-b border-border bg-muted/20">
                            <h4 class="font-semibold text-foreground">Sugestão de Precificação</h4>
                        </div>
                        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div>
                                <div class="flex items-end gap-2 mb-1">
                                    <h5 class="text-sm font-medium text-muted-foreground">Preço de Venda (Varejo)</h5>
                                    <span class="text-xs text-muted-foreground bg-muted px-1.5 py-0.5 rounded">Margem: <span
                                            x-text="results?.margin_percentage + '%'"></span></span>
                                </div>
                                <p class="text-3xl font-bold text-primary mb-3"
                                    x-text="'R$ ' + formatCurrency(results?.suggested_sale_price)"></p>

                                <div class="bg-muted/30 p-3 rounded-lg space-y-2 text-sm">
                                    <div class="flex justify-between items-center text-muted-foreground">
                                        <span>Com taxa cartão ({{ $settings->card_fee_percentage ?? 6.0 }}%)</span>
                                        <span x-text="'R$ ' + formatCurrency(results?.price_with_card_fee)"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="md:border-l md:border-border md:pl-8">
                                <h5 class="text-sm font-medium text-muted-foreground mb-1">Preço de Revenda (Atacado)</h5>
                                <p class="text-3xl font-bold text-foreground mb-3"
                                    x-text="'R$ ' + formatCurrency(results?.suggested_resale_price)"></p>
                                <p class="text-xs text-muted-foreground">Baseado no multiplicador de revenda configurado.
                                </p>
                            </div>
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

                    async calculateCosts() {
                        if (!this.form.recipe_id) {
                            alert('Por favor, selecione uma receita.');
                            return;
                        }

                        this.loading = true;
                        this.results = null;

                        try {
                            // Prepare payload handling empty strings
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

                        } catch (error) {
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