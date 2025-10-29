@extends('dash.layouts.app')

@section('title', 'Pedidos - OLIKA Dashboard')

@section('content')
<div class="space-y-6 animate-in fade-in duration-500">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold tracking-tight">Pedidos</h1>
            <p class="text-muted-foreground">Gerencie todos os pedidos do restaurante</p>
        </div>
        <button class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2 gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-plus h-4 w-4">
                <path d="M5 12h14"></path>
                <path d="M12 5v14"></path>
            </svg>
            Novo Pedido
        </button>
    </div>
    <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
        <div class="flex flex-col space-y-1.5 p-6">
            <div class="flex flex-col sm:flex-row gap-4">
                <div class="relative flex-1">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-search absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.3-4.3"></path>
                    </svg>
                    <input class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm pl-10" placeholder="Buscar por cliente, número do pedido..." value="">
                </div>
            </div>
        </div>
        <div class="p-6 pt-0">
            <div class="overflow-x-auto">
                <div class="relative w-full overflow-auto">
                    <table class="w-full caption-bottom text-sm">
                        <thead class="[&_tr]:border-b">
                            <tr class="border-b transition-colors data-[state=selected]:bg-muted hover:bg-muted/50">
                                <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground [&:has([role=checkbox])]:pr-0">#</th>
                                <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground [&:has([role=checkbox])]:pr-0">Cliente</th>
                                <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground [&:has([role=checkbox])]:pr-0">Total</th>
                                <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground [&:has([role=checkbox])]:pr-0">Status</th>
                                <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground [&:has([role=checkbox])]:pr-0">Pagamento</th>
                                <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground [&:has([role=checkbox])]:pr-0">Quando</th>
                                <th class="h-12 px-4 align-middle font-medium text-muted-foreground [&:has([role=checkbox])]:pr-0 text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="[&_tr:last-child]:border-0">
                            <tr class="border-b transition-colors data-[state=selected]:bg-muted hover:bg-muted/50">
                                <td class="p-4 align-middle [&:has([role=checkbox])]:pr-0 font-medium">001</td>
                                <td class="p-4 align-middle [&:has([role=checkbox])]:pr-0">João Silva</td>
                                <td class="p-4 align-middle [&:has([role=checkbox])]:pr-0 font-semibold">R$ 85,00</td>
                                <td class="p-4 align-middle [&:has([role=checkbox])]:pr-0">
                                    <div class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 border-transparent hover:bg-primary/80 bg-success text-success-foreground">Entregue</div>
                                </td>
                                <td class="p-4 align-middle [&:has([role=checkbox])]:pr-0">Pago</td>
                                <td class="p-4 align-middle [&:has([role=checkbox])]:pr-0 text-muted-foreground">Hoje, 14:32</td>
                                <td class="p-4 align-middle [&:has([role=checkbox])]:pr-0 text-right">
                                    <button class="inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 hover:bg-accent hover:text-accent-foreground h-9 rounded-md px-3">Ver detalhes</button>
                                </td>
                            </tr>
                            <tr class="border-b transition-colors data-[state=selected]:bg-muted hover:bg-muted/50">
                                <td class="p-4 align-middle [&:has([role=checkbox])]:pr-0 font-medium">002</td>
                                <td class="p-4 align-middle [&:has([role=checkbox])]:pr-0">Maria Santos</td>
                                <td class="p-4 align-middle [&:has([role=checkbox])]:pr-0 font-semibold">R$ 120,00</td>
                                <td class="p-4 align-middle [&:has([role=checkbox])]:pr-0">
                                    <div class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 border-transparent hover:bg-primary/80 bg-warning text-warning-foreground">Em preparo</div>
                                </td>
                                <td class="p-4 align-middle [&:has([role=checkbox])]:pr-0">Pago</td>
                                <td class="p-4 align-middle [&:has([role=checkbox])]:pr-0 text-muted-foreground">Hoje, 13:15</td>
                                <td class="p-4 align-middle [&:has([role=checkbox])]:pr-0 text-right">
                                    <button class="inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 hover:bg-accent hover:text-accent-foreground h-9 rounded-md px-3">Ver detalhes</button>
                                </td>
                            </tr>
                            <tr class="border-b transition-colors data-[state=selected]:bg-muted hover:bg-muted/50">
                                <td class="p-4 align-middle [&:has([role=checkbox])]:pr-0 font-medium">003</td>
                                <td class="p-4 align-middle [&:has([role=checkbox])]:pr-0">Pedro Costa</td>
                                <td class="p-4 align-middle [&:has([role=checkbox])]:pr-0 font-semibold">R$ 45,00</td>
                                <td class="p-4 align-middle [&:has([role=checkbox])]:pr-0">
                                    <div class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 border-transparent hover:bg-primary/80 bg-muted text-muted-foreground">Pendente</div>
                                </td>
                                <td class="p-4 align-middle [&:has([role=checkbox])]:pr-0">Pendente</td>
                                <td class="p-4 align-middle [&:has([role=checkbox])]:pr-0 text-muted-foreground">Hoje, 12:08</td>
                                <td class="p-4 align-middle [&:has([role=checkbox])]:pr-0 text-right">
                                    <button class="inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 hover:bg-accent hover:text-accent-foreground h-9 rounded-md px-3">Ver detalhes</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection