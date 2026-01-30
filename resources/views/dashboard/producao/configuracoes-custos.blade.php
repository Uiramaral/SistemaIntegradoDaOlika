@extends('layouts.admin')

@section('title', 'Configurações de Custos de Produção')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <h1 class="text-3xl font-bold">Configurações de Custos de Produção</h1>
        <p class="text-muted-foreground mt-2">Multiplicadores e custos fixos para cálculo de preços</p>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
        <div class="flex flex-col space-y-1.5 p-6">
            <h3 class="text-2xl font-semibold leading-none tracking-tight">Custos de Produção</h3>
            <p class="text-sm text-muted-foreground">Multiplicadores e custos fixos para cálculo de preços</p>
        </div>
        <form action="{{ route('dashboard.producao.configuracoes-custos.save') }}" method="POST" class="p-6 pt-0 space-y-4">
            @csrf
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-medium" for="sales_multiplier">Multiplicador Venda *</label>
                    <input name="sales_multiplier" id="sales_multiplier" type="number" step="0.1" min="0" value="{{ $productionSettings->sales_multiplier ?? 3.5 }}" required class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                    <p class="text-xs text-muted-foreground mt-1">Multiplicador base para cálculo de preço de venda</p>
                </div>
                <div>
                    <label class="text-sm font-medium" for="resale_multiplier">Multiplicador Revenda *</label>
                    <input name="resale_multiplier" id="resale_multiplier" type="number" step="0.1" min="0" value="{{ $productionSettings->resale_multiplier ?? 2.5 }}" required class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                    <p class="text-xs text-muted-foreground mt-1">Multiplicador base para cálculo de preço de revenda</p>
                </div>
                <div>
                    <label class="text-sm font-medium" for="fixed_cost">Custo Fixo Mensal (R$)</label>
                    <input name="fixed_cost" id="fixed_cost" type="number" step="0.01" min="0" value="{{ $productionSettings->fixed_cost ?? 0 }}" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                    <p class="text-xs text-muted-foreground mt-1">Custo fixo mensal do negócio</p>
                </div>
                <div>
                    <label class="text-sm font-medium" for="tax_percentage">Imposto (%)</label>
                    <input name="tax_percentage" id="tax_percentage" type="number" step="0.01" min="0" max="100" value="{{ $productionSettings->tax_percentage ?? 0 }}" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                    <p class="text-xs text-muted-foreground mt-1">Percentual de imposto sobre vendas</p>
                </div>
                <div>
                    <label class="text-sm font-medium" for="card_fee_percentage">Taxa Cartão (%)</label>
                    <input name="card_fee_percentage" id="card_fee_percentage" type="number" step="0.01" min="0" max="100" value="{{ $productionSettings->card_fee_percentage ?? 6.0 }}" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                    <p class="text-xs text-muted-foreground mt-1">Percentual de taxa de cartão de crédito</p>
                </div>
            </div>
            <div>
                <button type="submit" class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4">Salvar Configurações</button>
            </div>
        </form>
    </div>
</div>
@endsection
