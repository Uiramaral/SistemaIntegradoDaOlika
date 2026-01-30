@extends('dashboard.layouts.app')

@section('page_title', 'Relatório de Vendas')
@section('page_subtitle', 'Análise detalhada das vendas')

@section('page_actions')
    <a href="{{ route('dashboard.financas.index') }}"
        class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2 gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4">
            <path d="m12 19-7-7 7-7" />
            <path d="M19 12H5" />
        </svg>
        <span class="hidden sm:inline">Voltar</span>
    </a>
@endsection

@section('content')
    <div class="space-y-4">
        {{-- Filtros --}}
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm p-4">
            <form method="GET" class="space-y-4">
                {{-- Período --}}
                <div>
                    <label class="block text-sm font-medium mb-2">Período</label>
                    <div class="flex flex-wrap gap-2">
                        @php
                            $periodos = [
                                'hoje' => 'Hoje',
                                'semana' => 'Semana',
                                'mes' => 'Mês',
                                'ano' => 'Ano',
                                'personalizado' => 'Personalizado',
                            ];
                        @endphp
                        @foreach($periodos as $key => $label)
                            <button type="submit" name="periodo" value="{{ $key }}"
                                class="px-3 py-1.5 text-xs sm:text-sm rounded-md transition-colors {{ ($periodo ?? 'mes') === $key ? 'bg-primary text-primary-foreground' : 'bg-muted hover:bg-muted/80' }}">
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-3">
                    {{-- Método de Pagamento --}}
                    <div class="flex-1">
                        <label class="block text-sm font-medium mb-2">Método de Pagamento</label>
                        <select name="metodo_pagamento"
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                            onchange="this.form.submit()">
                            <option value="">Todos</option>
                            @foreach($metodosPagamento ?? [] as $metodo)
                                <option value="{{ $metodo }}" {{ $metodoPagamento === $metodo ? 'selected' : '' }}>
                                    {{ ucfirst($metodo) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Datas personalizadas --}}
                    @if(($periodo ?? 'mes') === 'personalizado')
                        <div class="flex-1">
                            <label class="block text-sm font-medium mb-2">De</label>
                            <input type="date" name="data_inicio" value="{{ request('data_inicio', $start->format('Y-m-d')) }}"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-medium mb-2">Até</label>
                            <input type="date" name="data_fim" value="{{ request('data_fim', $end->format('Y-m-d')) }}"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
                        </div>
                        <div class="flex items-end">
                            <button type="submit"
                                class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2">
                                Aplicar
                            </button>
                        </div>
                    @endif
                </div>
            </form>
        </div>

        {{-- Cards de Resumo --}}
        <div class="grid grid-cols-2 gap-3">
            <div class="rounded-lg border bg-card text-card-foreground shadow-sm p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="w-5 h-5 text-blue-600">
                            <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z" />
                            <path d="M3 6h18" />
                            <path d="M16 10a4 4 0 0 1-8 0" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs sm:text-sm text-muted-foreground">Total Vendas</p>
                        <p class="text-lg sm:text-2xl font-bold truncate">{{ $quantidadeVendas ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-lg border bg-card text-card-foreground shadow-sm p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="w-5 h-5 text-green-600">
                            <rect width="20" height="12" x="2" y="6" rx="2" />
                            <circle cx="12" cy="12" r="2" />
                            <path d="M6 12h.01M18 12h.01" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs sm:text-sm text-muted-foreground">Valor Total</p>
                        <p class="text-lg sm:text-2xl font-bold text-green-600 truncate">R$
                            {{ number_format($totalVendas ?? 0, 2, ',', '.') }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Lista de Vendas --}}
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="p-4 border-b">
                <h3 class="font-semibold">Vendas no Período</h3>
                <p class="text-sm text-muted-foreground">{{ $start->format('d/m/Y') }} - {{ $end->format('d/m/Y') }}</p>
            </div>

            @if($vendas->isEmpty())
                <div class="flex flex-col items-center justify-center py-12 text-center">
                    <div class="w-16 h-16 rounded-full bg-muted flex items-center justify-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="w-8 h-8 text-muted-foreground">
                            <circle cx="8" cy="21" r="1" />
                            <circle cx="19" cy="21" r="1" />
                            <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12" />
                        </svg>
                    </div>
                    <p class="text-muted-foreground">Nenhuma venda encontrada neste período.</p>
                </div>
            @else
                {{-- Mobile: Cards --}}
                <div class="md:hidden p-3 space-y-2">
                    @foreach($vendas as $venda)
                        <a href="{{ route('dashboard.orders.show', $venda->id) }}"
                            class="block p-3 rounded-lg border hover:bg-accent transition-colors">
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-semibold text-primary">
                                    {{ $venda->order_number ?? '#' . $venda->id }}
                                </span>
                                <span class="font-bold text-green-600">
                                    R$ {{ number_format($venda->final_amount, 2, ',', '.') }}
                                </span>
                            </div>
                            <div class="flex flex-wrap gap-x-4 gap-y-1 text-xs text-muted-foreground">
                                <span>{{ $venda->created_at->format('d/m/Y H:i') }}</span>
                                <span>{{ ucfirst($venda->payment_method ?? 'N/A') }}</span>
                                @if($venda->customer)
                                    <span class="truncate max-w-[150px]">{{ $venda->customer->name }}</span>
                                @endif
                            </div>
                        </a>
                    @endforeach

                    {{-- Total Mobile --}}
                    <div class="p-3 rounded-lg bg-muted/50 flex justify-between items-center">
                        <span class="font-semibold">Total:</span>
                        <span class="font-bold text-green-600 text-lg">R$ {{ number_format($totalVendas, 2, ',', '.') }}</span>
                    </div>
                </div>

                {{-- Desktop: Tabela --}}
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="text-left text-sm text-muted-foreground border-b">
                                <th class="px-4 py-3 font-medium">Pedido</th>
                                <th class="px-4 py-3 font-medium">Data</th>
                                <th class="px-4 py-3 font-medium">Cliente</th>
                                <th class="px-4 py-3 font-medium">Método</th>
                                <th class="px-4 py-3 font-medium text-right">Valor</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @foreach($vendas as $venda)
                                <tr class="hover:bg-muted/30 transition-colors">
                                    <td class="px-4 py-3">
                                        <a href="{{ route('dashboard.orders.show', $venda->id) }}"
                                            class="font-medium text-primary hover:underline">
                                            {{ $venda->order_number ?? '#' . $venda->id }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-muted-foreground">
                                        {{ $venda->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        {{ $venda->customer->name ?? 'Cliente não identificado' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-muted">
                                            {{ ucfirst($venda->payment_method ?? 'N/A') }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right font-semibold text-green-600">
                                        R$ {{ number_format($venda->final_amount, 2, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="border-t-2 bg-muted/30">
                            <tr>
                                <td colspan="4" class="px-4 py-3 font-semibold text-right">Total:</td>
                                <td class="px-4 py-3 text-right font-bold text-green-600 text-lg">
                                    R$ {{ number_format($totalVendas, 2, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                @if($vendas->hasPages())
                    <div class="p-4 border-t">
                        {{ $vendas->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
@endsection