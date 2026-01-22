@extends('dashboard.layouts.app')

@section('page_title', 'Clientes')
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
    <a href="{{ route('dashboard.customers.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors text-sm font-medium">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M5 12h14"></path>
            <path d="M12 5v14"></path>
        </svg>
        + Adicionar cliente
    </a>
@endsection

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr class="border-b">
                        <th class="h-12 px-4 text-left align-middle text-xs font-medium text-gray-500 uppercase tracking-wider">CLIENTE</th>
                        <th class="h-12 px-4 text-left align-middle text-xs font-medium text-gray-500 uppercase tracking-wider">TELEFONE</th>
                        <th class="h-12 px-4 text-left align-middle text-xs font-medium text-gray-500 uppercase tracking-wider">PEDIDOS</th>
                        <th class="h-12 px-4 text-left align-middle text-xs font-medium text-gray-500 uppercase tracking-wider">TOTAL GASTO</th>
                        <th class="h-12 px-4 text-left align-middle text-xs font-medium text-gray-500 uppercase tracking-wider">ÚLTIMO PEDIDO</th>
                        <th class="h-12 px-4 text-left align-middle text-xs font-medium text-gray-500 uppercase tracking-wider">STATUS</th>
                        <th class="h-12 px-4 text-left align-middle text-xs font-medium text-gray-500 uppercase tracking-wider">AÇÕES</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($customers as $customer)
                                @php
                                    $initials = strtoupper(substr($customer->name ?? '', 0, 1) . substr($customer->name ?? '', strpos($customer->name ?? '', ' ') + 1, 1) ?? '');
                                    if (empty($initials) && !empty($customer->name)) {
                                        $initials = strtoupper(substr($customer->name, 0, 2));
                                    }
                                @endphp
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="flex items-center gap-3">
                                            <span class="relative flex h-10 w-10 shrink-0 overflow-hidden rounded-full bg-blue-100">
                                                <span class="flex h-full w-full items-center justify-center rounded-full text-blue-600 font-semibold text-sm">{{ $initials }}</span>
                                            </span>
                                            <div>
                                                <div class="font-semibold text-gray-900">{{ $customer->name ?? 'Sem nome' }}</div>
                                                @if($customer->email)
                                                <div class="text-sm text-gray-500">{{ $customer->email }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                        {{ $customer->phone ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">{{ $customer->total_orders ?? 0 }} pedidos</td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span class="font-semibold text-gray-900">R$ {{ number_format($customer->total_spent ?? 0, 2, ',', '.') }}</span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                        {{ $customer->last_order_at ? \Carbon\Carbon::parse($customer->last_order_at)->format('d/m/y') : '-' }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Ativo</span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <button class="text-gray-400 hover:text-gray-600">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="p-8 text-center text-gray-500">
                                        Nenhum cliente encontrado.
                                    </td>
                                </tr>
                            @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
