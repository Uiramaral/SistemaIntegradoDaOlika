@extends('dashboard.layouts.app')

@section('page_title', 'Preços de Revenda')
@section('page_subtitle', 'Tabela de preços para revendedores')

@section('page_actions')
    <div class="flex items-center gap-2">
        <button class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
            </svg>
        </button>
        <button class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"></path>
            </svg>
        </button>
    </div>
@endsection

@section('content')
<div class="bg-card rounded-xl border border-border animate-fade-in">
    <div class="p-4 border-b border-border flex flex-col sm:flex-row gap-4 justify-between">
        <form method="GET" action="{{ route('dashboard.wholesale-prices.index') }}" class="relative flex-1 max-w-md">
            <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground"></i>
            <input type="text" name="q" value="{{ request('q') }}" class="form-input pl-10" placeholder="Buscar produto...">
        </form>
        <button class="btn-primary gap-2 h-9 px-4">
            <i data-lucide="save" class="h-4 w-4"></i>
            Salvar Alterações
        </button>
    </div>

    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Produto</th>
                    <th>Categoria</th>
                    <th>Preço Varejo</th>
                    <th>Preço Atacado</th>
                    <th>Qtd. Mínima</th>
                    <th>Desconto</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $productsList = $products ?? collect();
                @endphp
                @forelse($productsList as $product)
                    @php
                        $priceRetail = (float)($product->price ?? 0);
                        $priceWholesale = (float)($product->wholesale_price ?? ($product->price * 0.85));
                        $minQty = (int)($product->wholesale_min_qty ?? 1);
                        $discount = $priceRetail > 0 ? round((($priceRetail - $priceWholesale) / $priceRetail) * 100) : 0;
                    @endphp
                    <tr>
                        <td class="font-medium">{{ $product->name }}</td>
                        <td>
                            <span class="px-2 py-1 rounded-md text-xs font-medium bg-muted">
                                {{ $product->category->name ?? 'Sem categoria' }}
                            </span>
                        </td>
                        <td>
                            <input type="number" class="form-input w-24 text-center" step="0.01" value="{{ number_format($priceRetail, 2, '.', '') }}">
                        </td>
                        <td>
                            <input type="number" class="form-input w-24 text-center" step="0.01" value="{{ number_format($priceWholesale, 2, '.', '') }}">
                        </td>
                        <td>
                            <input type="number" class="form-input w-20 text-center" value="{{ $minQty }}">
                        </td>
                        <td>
                            <span class="text-accent font-medium">{{ $discount }}%</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-muted-foreground">Nenhum produto encontrado</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

