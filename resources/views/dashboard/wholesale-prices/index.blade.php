@extends('dashboard.layouts.app')

@section('page_title', 'Preços de Revenda')
@section('page_subtitle', 'Gerencie os preços diferenciados para clientes de revenda e restaurantes')

@section('page_actions')
    <a href="{{ route('dashboard.wholesale-prices.create') }}" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2 gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-plus h-4 w-4">
            <path d="M5 12h14"></path>
            <path d="M12 5v14"></path>
        </svg>
        Novo Preço de Revenda
    </a>
@endsection

@section('content')
<div class="space-y-6">

    @if(session('success'))
        <div class="rounded-lg border bg-green-50 text-green-900 px-4 py-3">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="rounded-lg border bg-red-50 text-red-900 px-4 py-3">
            {{ session('error') }}
        </div>
    @endif

    <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
        <div class="flex flex-col space-y-1.5 p-6">
            <form method="GET" action="{{ route('dashboard.wholesale-prices.index') }}" class="flex flex-col md:flex-row gap-4">
                <select name="product_id" class="flex h-10 rounded-md border border-input bg-background px-3 py-2 text-sm">
                    <option value="">Todos os produtos</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" @selected(request('product_id') == $product->id)>{{ $product->name }}</option>
                    @endforeach
                </select>
                <select name="status" class="flex h-10 rounded-md border border-input bg-background px-3 py-2 text-sm">
                    <option value="">Todos os status</option>
                    <option value="active" @selected(request('status') == 'active')>Ativos</option>
                    <option value="inactive" @selected(request('status') == 'inactive')>Inativos</option>
                </select>
                <button type="submit" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2">
                    Filtrar
                </button>
                @if(request()->has('product_id') || request()->has('status'))
                    <a href="{{ route('dashboard.wholesale-prices.index') }}" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2">
                        Limpar
                    </a>
                @endif
            </form>
        </div>
        <div class="p-6 pt-0">
            <div class="overflow-x-auto">
                <table class="w-full caption-bottom text-sm">
                    <thead class="[&_tr]:border-b">
                        <tr class="border-b transition-colors hover:bg-muted/50">
                            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Produto</th>
                            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Variante</th>
                            <th class="h-12 px-4 text-right align-middle font-medium text-muted-foreground">Preço de Revenda</th>
                            <th class="h-12 px-4 text-right align-middle font-medium text-muted-foreground">Qtd. Mínima</th>
                            <th class="h-12 px-4 text-center align-middle font-medium text-muted-foreground">Status</th>
                            <th class="h-12 px-4 text-right align-middle font-medium text-muted-foreground">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="[&_tr:last-child]:border-0">
                        @forelse($prices as $price)
                            <tr class="border-b transition-colors hover:bg-muted/50">
                                <td class="p-4 align-middle">
                                    <div class="font-medium">{{ $price->product->name ?? 'Produto não encontrado' }}</div>
                                    <div class="text-xs text-muted-foreground">
                                        Preço normal: R$ {{ number_format($price->product->price ?? 0, 2, ',', '.') }}
                                    </div>
                                </td>
                                <td class="p-4 align-middle">
                                    @if($price->variant)
                                        <span class="text-sm">{{ $price->variant->name }}</span>
                                        <div class="text-xs text-muted-foreground">
                                            Preço normal: R$ {{ number_format($price->variant->price ?? 0, 2, ',', '.') }}
                                        </div>
                                    @else
                                        <span class="text-sm text-muted-foreground">Produto base</span>
                                    @endif
                                </td>
                                <td class="p-4 align-middle text-right">
                                    <span class="font-semibold text-primary">R$ {{ number_format($price->wholesale_price, 2, ',', '.') }}</span>
                                </td>
                                <td class="p-4 align-middle text-right">
                                    <span class="text-sm">{{ $price->min_quantity }}</span>
                                </td>
                                <td class="p-4 align-middle text-center">
                                    @if($price->is_active)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Ativo
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            Inativo
                                        </span>
                                    @endif
                                </td>
                                <td class="p-4 align-middle text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('dashboard.wholesale-prices.edit', $price) }}" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-8 px-3">
                                            Editar
                                        </a>
                                        <form action="{{ route('dashboard.wholesale-prices.destroy', $price) }}" method="POST" class="inline" onsubmit="return confirm('Tem certeza que deseja remover este preço de revenda?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 border border-input bg-background hover:bg-destructive hover:text-destructive-foreground h-8 px-3 text-destructive">
                                                Excluir
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-8 text-center text-muted-foreground">
                                    <p>Nenhum preço de revenda cadastrado ainda.</p>
                                    <a href="{{ route('dashboard.wholesale-prices.create') }}" class="text-primary hover:underline mt-2 inline-block">
                                        Cadastrar primeiro preço
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($prices->hasPages())
                <div class="mt-4">
                    {{ $prices->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

