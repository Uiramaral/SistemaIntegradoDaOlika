@extends('dashboard.layouts.app')

@section('title', 'Cupons - OLIKA Painel')

@section('content')
<div class="space-y-6">
    @if(session('success'))
        <div class="rounded-md border border-green-200 bg-green-50 text-green-700 p-4">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="rounded-md border border-red-200 bg-red-50 text-red-700 p-4">
            {{ session('error') }}
        </div>
    @endif

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold tracking-tight">Cupons de Desconto</h1>
            <p class="text-muted-foreground">Gerencie cupons públicos e privados</p>
        </div>
        <a href="{{ route('dashboard.coupons.create') }}" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2 gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M5 12h14"></path>
                <path d="M12 5v14"></path>
            </svg>
            Novo Cupom
        </a>
    </div>

    <!-- Estatísticas -->
    @if(isset($stats))
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="rounded-lg border bg-card p-4">
            <div class="text-sm text-muted-foreground">Total</div>
            <div class="text-2xl font-bold">{{ $stats['total'] }}</div>
        </div>
        <div class="rounded-lg border bg-card p-4">
            <div class="text-sm text-muted-foreground">Ativos</div>
            <div class="text-2xl font-bold text-green-600">{{ $stats['active'] }}</div>
        </div>
        <div class="rounded-lg border bg-card p-4">
            <div class="text-sm text-muted-foreground">Públicos</div>
            <div class="text-2xl font-bold text-blue-600">{{ $stats['public'] }}</div>
        </div>
        <div class="rounded-lg border bg-card p-4">
            <div class="text-sm text-muted-foreground">Privados</div>
            <div class="text-2xl font-bold text-purple-600">{{ $stats['private'] }}</div>
        </div>
    </div>
    @endif

    <!-- Filtros -->
    <div class="rounded-lg border bg-card p-4">
        <form method="GET" action="{{ route('dashboard.coupons.index') }}" class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <input type="text" name="search" placeholder="Buscar por código, nome..." value="{{ request('search') }}" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
            </div>
            <select name="visibility" class="flex h-10 rounded-md border border-input bg-background px-3 py-2 text-sm">
                <option value="">Todas as visibilidades</option>
                <option value="public" {{ request('visibility') == 'public' ? 'selected' : '' }}>Público</option>
                <option value="private" {{ request('visibility') == 'private' ? 'selected' : '' }}>Privado</option>
                <option value="targeted" {{ request('visibility') == 'targeted' ? 'selected' : '' }}>Direcionado</option>
            </select>
            <select name="is_active" class="flex h-10 rounded-md border border-input bg-background px-3 py-2 text-sm">
                <option value="">Todos os status</option>
                <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Ativo</option>
                <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inativo</option>
            </select>
            <button type="submit" class="px-4 py-2 rounded-md bg-primary text-primary-foreground hover:bg-primary/90">Filtrar</button>
            @if(request()->hasAny(['search', 'visibility', 'is_active']))
            <a href="{{ route('dashboard.coupons.index') }}" class="px-4 py-2 rounded-md border hover:bg-accent">Limpar</a>
            @endif
        </form>
    </div>

    <!-- Tabela de Cupons -->
    @if($coupons->count() > 0)
    <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="w-full text-sm" data-mobile-card="true">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left p-3 text-sm font-medium">Código</th>
                            <th class="text-left p-3 text-sm font-medium">Nome</th>
                            <th class="text-left p-3 text-sm font-medium">Desconto</th>
                            <th class="text-left p-3 text-sm font-medium">Visibilidade</th>
                            <th class="text-left p-3 text-sm font-medium">Uso</th>
                            <th class="text-left p-3 text-sm font-medium">Validade</th>
                            <th class="text-left p-3 text-sm font-medium">Status</th>
                            <th class="text-right p-3 text-sm font-medium">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($coupons as $coupon)
                        <tr class="border-b hover:bg-muted/50">
                            <td class="p-3 actions-cell">
                                <code class="text-sm font-mono bg-muted px-2 py-1 rounded">{{ $coupon->code }}</code>
                            </td>
                            <td class="p-3">
                                <div class="font-medium">{{ $coupon->name }}</div>
                                @if($coupon->description)
                                <div class="text-xs text-muted-foreground mt-1">{{ \Illuminate\Support\Str::limit($coupon->description, 50) }}</div>
                                @endif
                            </td>
                            <td class="p-3">
                                <div class="font-semibold">{{ $coupon->formatted_value }}</div>
                                @if($coupon->minimum_amount)
                                <div class="text-xs text-muted-foreground">Mín: R$ {{ number_format($coupon->minimum_amount, 2, ',', '.') }}</div>
                                @endif
                            </td>
                            <td class="p-3">
                                @if($coupon->visibility === 'public')
                                <span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800">Público</span>
                                @elseif($coupon->visibility === 'private')
                                <span class="inline-flex items-center rounded-full bg-purple-100 px-2.5 py-0.5 text-xs font-medium text-purple-800">Privado</span>
                                @else
                                <span class="inline-flex items-center rounded-full bg-orange-100 px-2.5 py-0.5 text-xs font-medium text-orange-800">Direcionado</span>
                                @endif
                                @if($coupon->first_order_only)
                                <div class="text-xs text-muted-foreground mt-1">1º pedido</div>
                                @endif
                                @if($coupon->free_shipping_only)
                                <div class="text-xs text-muted-foreground">Frete grátis</div>
                                @endif
                            </td>
                            <td class="p-3 text-sm">
                                {{ $coupon->used_count ?? 0 }} / {{ $coupon->usage_limit ?: '∞' }}
                            </td>
                            <td class="p-3 text-sm text-muted-foreground">
                                @if($coupon->starts_at || $coupon->expires_at)
                                <div class="text-xs">
                                    @if($coupon->starts_at)
                                    <div>Início: {{ $coupon->starts_at->format('d/m/Y') }}</div>
                                    @endif
                                    @if($coupon->expires_at)
                                    <div>Fim: {{ $coupon->expires_at->format('d/m/Y') }}</div>
                                    @endif
                                </div>
                                @else
                                Sem validade
                                @endif
                            </td>
                            <td class="p-3">
                                @if($coupon->is_active)
                                <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">Ativo</span>
                                @else
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-800">Inativo</span>
                                @endif
                            </td>
                            <td class="p-3">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('dashboard.coupons.edit', $coupon) }}" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-8 w-8" title="Editar">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                        </svg>
                                    </a>
                                    <form action="{{ route('dashboard.coupons.destroy', $coupon) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir este cupom?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 border border-input bg-background hover:bg-accent hover:text-accent-foreground text-red-600 hover:text-red-700 h-8 w-8" title="Excluir">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="3 6 5 6 21 6"></polyline>
                                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4c1 0 2 1 2 2v2"></path>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $coupons->links() }}
            </div>
        </div>
    </div>
    @else
    <div class="rounded-lg border bg-card text-card-foreground shadow-sm p-12 text-center">
        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mx-auto mb-4 text-muted-foreground">
            <path d="M21 11V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h6"></path>
            <path d="m12 12 4 4 6-6"></path>
        </svg>
        <h3 class="text-lg font-semibold mb-2">Nenhum cupom encontrado</h3>
        <p class="text-muted-foreground mb-4">Comece criando seu primeiro cupom de desconto</p>
        <a href="{{ route('dashboard.coupons.create') }}" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2 gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M5 12h14"></path>
                <path d="M12 5v14"></path>
            </svg>
            Novo Cupom
        </a>
    </div>
    @endif
</div>
@endsection
