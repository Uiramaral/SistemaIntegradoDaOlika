@extends('dashboard.layouts.app')

@section('page_title', 'Preços de Revenda')
@section('page_subtitle', 'Acompanhe uma visão detalhada das métricas e resultados')

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

    <!-- Cards de Tabelas de Preços - Estilo do Site -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Tabelas</h3>
            <button class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                </svg>
            </button>
        </div>
        <div class="flex flex-wrap gap-4">
            <!-- Card Revenda Padrão (Selecionado) -->
            <button class="flex-1 min-w-[200px] bg-primary text-white rounded-lg p-4 text-left hover:bg-primary/90 transition-colors">
                <div class="font-semibold text-lg mb-1">Revenda Padrão</div>
                <div class="flex items-center gap-2 text-sm opacity-90 mb-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    <span>8 clientes</span>
                </div>
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-primary-600 text-white">-15%</span>
            </button>
            <!-- Card Atacado -->
            <button class="flex-1 min-w-[200px] bg-white border border-gray-300 rounded-lg p-4 text-left hover:bg-gray-50 transition-colors">
                <div class="font-semibold text-lg mb-1 text-gray-900">Atacado</div>
                <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    <span>3 clientes</span>
                </div>
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700">-25%</span>
            </button>
            <!-- Card VIP -->
            <button class="flex-1 min-w-[200px] bg-white border border-gray-300 rounded-lg p-4 text-left hover:bg-gray-50 transition-colors">
                <div class="font-semibold text-lg mb-1 text-gray-900">VIP</div>
                <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    <span>2 clientes</span>
                </div>
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700">-30%</span>
            </button>
        </div>
    </div>

    <!-- Busca de Produtos e Tabela - Estilo do Site -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="p-6">
            <!-- Barra de Busca -->
            <div class="mb-6">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <input type="text" class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" placeholder="Buscar produto..." autocomplete="off">
                </div>
            </div>

            <!-- Tabela de Produtos -->
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr class="border-b">
                            <th class="h-12 px-4 text-left align-middle text-xs font-medium text-gray-500 uppercase tracking-wider">PRODUTO</th>
                            <th class="h-12 px-4 text-left align-middle text-xs font-medium text-gray-500 uppercase tracking-wider">REVENDA PADRÃO</th>
                            <th class="h-12 px-4 text-left align-middle text-xs font-medium text-gray-500 uppercase tracking-wider">ATACADO</th>
                            <th class="h-12 px-4 text-left align-middle text-xs font-medium text-gray-500 uppercase tracking-wider">VIP</th>
                            <th class="h-12 px-4 text-left align-middle text-xs font-medium text-gray-500 uppercase tracking-wider">AÇÕES</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @php
                            // Agrupar produtos para mostrar na tabela
                            $productsList = $products ?? collect();
                        @endphp
                        @forelse($productsList->take(10) as $product)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="font-semibold text-gray-900">{{ $product->name }}</span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                    R$ {{ number_format($product->price * 0.85, 2, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                    R$ {{ number_format($product->price * 0.75, 2, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                    R$ {{ number_format($product->price * 0.70, 2, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <button class="text-gray-400 hover:text-gray-600">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-gray-500">Nenhum produto encontrado</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

