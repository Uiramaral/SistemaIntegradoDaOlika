@extends('dashboard.layouts.app')

@section('page_title', 'Análise de Custos')
@section('page_subtitle', 'Custos de produção')

@section('content')
<style>
    [x-cloak] { display: none !important; }
</style>
<div class="space-y-6" x-data="costCalculator">
    {{-- Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
        <div class="stat-card flex flex-col sm:flex-row sm:items-center gap-3">
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-primary/10 flex items-center justify-center shrink-0">
                <i data-lucide="dollar-sign" class="w-5 h-5 sm:w-6 sm:h-6 text-primary"></i>
            </div>
            <div class="min-w-0">
                <p class="text-sm text-muted-foreground">Custo Total Mensal</p>
                <p class="text-xl md:text-2xl font-bold text-foreground">R$ {{ number_format($totalMonthlyCost ?? 0, 2, ',', '.') }}</p>
            </div>
        </div>
        <div class="stat-card flex flex-col sm:flex-row sm:items-center gap-3">
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-accent/10 flex items-center justify-center shrink-0">
                <i data-lucide="trending-up" class="w-5 h-5 sm:w-6 sm:h-6 text-accent"></i>
            </div>
            <div class="min-w-0">
                <p class="text-sm text-muted-foreground">Margem Média</p>
                <p class="text-xl md:text-2xl font-bold text-foreground">{{ number_format($averageMargin ?? 0, 1, ',', '.') }}%</p>
            </div>
        </div>
        <div class="stat-card flex flex-col sm:flex-row sm:items-center gap-3">
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-blue-100 flex items-center justify-center shrink-0">
                <i data-lucide="calculator" class="w-5 h-5 sm:w-6 sm:h-6 text-blue-600"></i>
            </div>
            <div class="min-w-0">
                <p class="text-sm text-muted-foreground">Custo por Produto</p>
                <p class="text-xl md:text-2xl font-bold text-foreground">R$ {{ number_format($averageCost ?? 0, 2, ',', '.') }}</p>
            </div>
        </div>
        <div class="stat-card flex flex-col sm:flex-row sm:items-center gap-3">
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-warning/10 flex items-center justify-center shrink-0">
                <i data-lucide="trending-down" class="w-5 h-5 sm:w-6 sm:h-6 text-warning"></i>
            </div>
            <div class="min-w-0">
                <p class="text-sm text-muted-foreground">Desperdício</p>
                <p class="text-xl md:text-2xl font-bold text-foreground">0%</p>
            </div>
        </div>
    </div>

    {{-- Calculadora de Custos --}}
    <div class="bg-card rounded-xl border border-border p-6">
        <h3 class="text-lg font-semibold mb-4">Calculadora de Custos de Produção</h3>
        
        <form @submit.prevent="calculateCosts()" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-2">Receita</label>
                    <select x-model="form.recipe_id" @change="loadRecipe()" required class="form-input">
                        <option value="">Selecione uma receita...</option>
                        @forelse($recipes ?? [] as $recipe)
                            <option value="{{ $recipe['id'] }}">{{ $recipe['name'] }}</option>
                        @empty
                            <option value="" disabled>Nenhuma receita cadastrada</option>
                        @endforelse
                    </select>
                    @if(empty($recipes))
                        <p class="text-xs text-muted-foreground mt-1">Cadastre receitas primeiro em Produção → Receitas</p>
                    @endif
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Peso Base</label>
                    <select x-model="form.weight" class="form-input">
                        <option value="">Usar peso padrão</option>
                        <option value="400">400g</option>
                        <option value="450">450g</option>
                        <option value="500">500g</option>
                        <option value="700">700g</option>
                        <option value="750">750g</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Embalagem</label>
                    <select x-model="form.packaging_id" class="form-input">
                        <option value="">Sem embalagem</option>
                        @php
                            $packagings = \App\Models\Packaging::withoutGlobalScope(\App\Models\Scopes\ClientScope::class)
                                ->where(function($q) {
                                    $clientId = currentClientId();
                                    $q->where('client_id', $clientId)->orWhereNull('client_id');
                                })
                                ->where('is_active', true)
                                ->orderBy('name')
                                ->get();
                        @endphp
                        @foreach($packagings as $packaging)
                            <option value="{{ $packaging->id }}">{{ $packaging->name }} (R$ {{ number_format($packaging->cost, 2, ',', '.') }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Mult. Venda</label>
                    <input type="number" x-model="form.sales_multiplier" step="0.1" min="0" value="{{ $settings->sales_multiplier ?? 3.5 }}" class="form-input">
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-2">Mult. Revenda</label>
                    <input type="number" x-model="form.resale_multiplier" step="0.1" min="0" value="{{ $settings->resale_multiplier ?? 2.5 }}" class="form-input">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="btn-primary w-full" :disabled="loading">
                        <span x-show="!loading">Calcular</span>
                        <span x-show="loading" x-cloak>Calculando...</span>
                    </button>
                </div>
            </div>
        </form>

        <div x-show="results !== null" class="mt-6 space-y-4" x-cloak>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                    <p class="text-sm text-blue-700 mb-1">Custo Ingredientes</p>
                    <p class="text-2xl font-bold text-blue-900" x-text="'R$ ' + formatCurrency(results.total_ingredient_cost)"></p>
                </div>
                <div class="bg-orange-50 p-4 rounded-lg border border-orange-200">
                    <p class="text-sm text-orange-700 mb-1">+ Custos Fixos (30%)</p>
                    <p class="text-2xl font-bold text-orange-900" x-text="'R$ ' + formatCurrency(results.fixed_cost)"></p>
                </div>
                <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
                    <p class="text-sm text-purple-700 mb-1">+ Taxa Cartão ({{ $settings->card_fee_percentage ?? 6.0 }}%)</p>
                    <p class="text-2xl font-bold text-purple-900" x-text="'R$ ' + formatCurrency(results.price_with_card_fee)"></p>
                    <p class="text-xs text-purple-600 mt-1" x-text="results.weight + 'g • ' + results.margin_percentage + '% lucro'"></p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-2">Preço Venda Sugerido</label>
                    <p class="text-2xl font-bold text-primary mb-2" x-text="'R$ ' + formatCurrency(results.suggested_sale_price)"></p>
                    <input type="number" x-model="salePrice" step="0.01" class="form-input" placeholder="Ajustar preço">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Preço Revenda Sugerido</label>
                    <p class="text-2xl font-bold text-primary mb-2" x-text="'R$ ' + formatCurrency(results.suggested_resale_price)"></p>
                    <input type="number" x-model="resalePrice" step="0.01" class="form-input" placeholder="Ajustar preço">
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 md:gap-6">
        <div class="card-copycat p-4 md:p-6">
            <h3 class="text-lg font-semibold text-foreground mb-4">Custo vs Preço de Venda</h3>
            <div class="h-48 md:h-64 flex items-center justify-center bg-muted/30 rounded-xl">
                <div class="text-center text-muted-foreground">
                    <i data-lucide="bar-chart-2" class="w-12 h-12 mx-auto mb-2 opacity-50"></i>
                    <p class="text-sm">Gráfico em breve</p>
                </div>
            </div>
        </div>
        <div class="card-copycat p-4 md:p-6">
            <h3 class="text-lg font-semibold text-foreground mb-4">Composição de Custos</h3>
            <div class="flex flex-col items-center justify-center py-8 md:py-12 text-muted-foreground">
                <i data-lucide="pie-chart" class="w-12 h-12 mb-4 opacity-50"></i>
                <p class="font-medium text-foreground">Sem dados de custos</p>
                <p class="text-sm mt-1">Cadastre receitas e ingredientes para ver a composição.</p>
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
        salePrice: '',
        resalePrice: '',
        loading: false,
        
        async calculateCosts() {
            if (!this.form.recipe_id) {
                alert('Selecione uma receita');
                return;
            }
            
            this.loading = true;
            this.results = null;
            
            try {
                // Converter valores vazios para null e garantir tipos corretos
                const formData = {
                    recipe_id: parseInt(this.form.recipe_id),
                    weight: this.form.weight ? parseFloat(this.form.weight) : null,
                    packaging_id: this.form.packaging_id ? parseInt(this.form.packaging_id) : null,
                    sales_multiplier: this.form.sales_multiplier ? parseFloat(this.form.sales_multiplier) : null,
                    resale_multiplier: this.form.resale_multiplier ? parseFloat(this.form.resale_multiplier) : null,
                };
                
                console.log('Enviando dados:', formData);
                
                const response = await fetch('{{ route("dashboard.producao.custos.calculate") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(formData)
                });
                
                console.log('Resposta recebida:', response.status, response.statusText);
                
                const data = await response.json();
                console.log('Dados recebidos:', data);
                
                if (!response.ok) {
                    throw new Error(data.error || data.message || `Erro ${response.status}: ${response.statusText}`);
                }
                
                if (data.error) {
                    throw new Error(data.error);
                }
                
                this.results = data;
                this.salePrice = data.suggested_sale_price;
                this.resalePrice = data.suggested_resale_price;
            } catch (error) {
                console.error('Erro ao calcular custos:', error);
                alert('Erro ao calcular custos: ' + error.message);
            } finally {
                this.loading = false;
            }
        },
        
        formatCurrency(value) {
            return new Intl.NumberFormat('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(value || 0);
        },
        
        loadRecipe() {
            // Carregar dados da receita quando selecionada
        }
    }));
});
</script>
@endpush
@endsection
