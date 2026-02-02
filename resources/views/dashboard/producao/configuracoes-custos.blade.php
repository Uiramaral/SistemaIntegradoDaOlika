@extends('dashboard.layouts.app')

@section('page_title', 'Configurações de Custos')
@section('page_subtitle', 'Multiplicadores e custos fixos para cálculo de preços')

@section('content')
    <div class="max-w-4xl mx-auto space-y-6 animate-fade-in">
        {{-- Alertas de Feedback --}}
        @if(session('success'))
            <div class="flex items-center gap-3 p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl">
                <i data-lucide="check-circle" class="h-5 w-5 shrink-0"></i>
                <span class="text-sm font-medium">{{ session('success') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="flex items-start gap-3 p-4 bg-destructive/10 border border-destructive/20 text-destructive rounded-xl">
                <i data-lucide="alert-circle" class="h-5 w-5 shrink-0 mt-0.5"></i>
                <div class="space-y-1">
                    <p class="text-sm font-bold">Corrija os erros abaixo:</p>
                    <ul class="list-disc list-inside text-xs opacity-90">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        {{-- Formulário de Configurações --}}
        <div class="bg-card rounded-xl border border-border overflow-hidden shadow-sm">
            <div class="p-6 border-b border-border bg-muted/20">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center">
                        <i data-lucide="settings-2" class="h-5 w-5 text-primary"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold">Parâmetros de Produção</h3>
                        <p class="text-sm text-muted-foreground leading-none">Ajuste as margens e impostos aplicados
                            globalmente</p>
                    </div>
                </div>
            </div>

            <form action="{{ route('dashboard.producao.configuracoes-custos.save') }}" method="POST" class="p-6">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Multiplicador Venda --}}
                    <div class="space-y-2">
                        <label class="text-sm font-bold flex items-center gap-1.5" for="sales_multiplier">
                            <i data-lucide="trending-up" class="w-4 h-4 text-muted-foreground"></i>
                            Multiplicador Venda CLiente Final
                        </label>
                        <div class="relative">
                            <input name="sales_multiplier" id="sales_multiplier" type="number" step="0.1" min="0"
                                value="{{ $productionSettings->sales_multiplier ?? 3.5 }}" required
                                class="form-input h-11 w-full pl-5 pr-10 font-medium">
                            <div class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground font-bold text-lg">×
                            </div>
                        </div>
                        <p class="text-[11px] text-muted-foreground leading-relaxed">
                            Aplica-se sobre o CMV (Custo de Matéria Prima) para sugerir o preço no cardápio digital.
                        </p>
                    </div>

                    {{-- Multiplicador Revenda --}}
                    <div class="space-y-2">
                        <label class="text-sm font-bold flex items-center gap-1.5" for="resale_multiplier">
                            <i data-lucide="users" class="w-4 h-4 text-muted-foreground"></i>
                            Multiplicador de Revenda (Atacado)
                        </label>
                        <div class="relative">
                            <input name="resale_multiplier" id="resale_multiplier" type="number" step="0.1" min="0"
                                value="{{ $productionSettings->resale_multiplier ?? 2.5 }}" required
                                class="form-input h-11 w-full pl-5 pr-10 font-medium">
                            <div class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground font-bold text-lg">×
                            </div>
                        </div>
                        <p class="text-[11px] text-muted-foreground leading-relaxed">
                            Multiplicador reduzido para clientes que compram em volume para revenda.
                        </p>
                    </div>

                    <div class="md:col-span-2 py-2">
                        <div class="h-px bg-border w-full"></div>
                    </div>

                    {{-- Custo Fixo --}}
                    <div class="space-y-2">
                        <label class="text-sm font-bold flex items-center gap-1.5" for="fixed_cost">
                            <i data-lucide="dollar-sign" class="w-4 h-4 text-muted-foreground"></i>
                            Custo Fixo Mensal Estimado
                        </label>
                        <div class="relative">
                            <div class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground text-sm font-medium">
                                R$</div>
                            <input name="fixed_cost" id="fixed_cost" type="number" step="0.01" min="0"
                                value="{{ $productionSettings->fixed_cost ?? 0 }}"
                                class="form-input h-11 w-full pl-10 font-medium">
                        </div>
                        <p class="text-[11px] text-muted-foreground">Aluguel, energia, funcionários, etc.</p>
                    </div>

                    {{-- Impostos --}}
                    <div class="space-y-2">
                        <label class="text-sm font-bold flex items-center gap-1.5" for="tax_percentage">
                            <i data-lucide="percent" class="w-4 h-4 text-muted-foreground"></i>
                            Impostos sobre Venda
                        </label>
                        <div class="relative">
                            <input name="tax_percentage" id="tax_percentage" type="number" step="0.01" min="0" max="100"
                                value="{{ $productionSettings->tax_percentage ?? 0 }}"
                                class="form-input h-11 w-full pr-10 font-medium">
                            <div class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground text-sm font-bold">%
                            </div>
                        </div>
                        <p class="text-[11px] text-muted-foreground">DAS (Simpósio), ISS, etc.</p>
                    </div>

                    {{-- Taxas de Cartão --}}
                    <div class="space-y-2">
                        <label class="text-sm font-bold flex items-center gap-1.5" for="card_fee_percentage">
                            <i data-lucide="credit-card" class="w-4 h-4 text-muted-foreground"></i>
                            Taxa Média de Cartão/PIX
                        </label>
                        <div class="relative">
                            <input name="card_fee_percentage" id="card_fee_percentage" type="number" step="0.01" min="0"
                                max="100" value="{{ $productionSettings->card_fee_percentage ?? 6.0 }}"
                                class="form-input h-11 w-full pr-10 font-medium">
                            <div class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground text-sm font-bold">%
                            </div>
                        </div>
                        <p class="text-[11px] text-muted-foreground">Média das taxas das adquirentes.</p>
                    </div>
                </div>

                <div class="mt-8 pt-6 border-t border-border flex justify-end">
                    <button type="submit" class="btn-primary gap-2 h-11 px-8 rounded-xl shadow-md">
                        <i data-lucide="save" class="w-4 h-4 text-white"></i>
                        <span>Salvar Configurações</span>
                    </button>
                </div>
            </form>
        </div>

        {{-- Info Alert --}}
        <div class="p-4 bg-muted/50 border border-border rounded-xl flex gap-3">
            <i data-lucide="info" class="w-5 h-5 text-primary shrink-0 mt-0.5"></i>
            <div class="text-sm text-muted-foreground leading-relaxed">
                Essas configurações são usadas diretamente no módulo de <a
                    href="{{ route('dashboard.producao.custos.index') }}"
                    class="text-primary font-bold hover:underline">Análise de Custos</a>.
                Alterar esses valores afetará instantaneamente as sugestões de preço em todo o sistema.
            </div>
        </div>
    </div>
@endsection