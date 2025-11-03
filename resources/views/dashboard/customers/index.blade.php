@extends('dashboard.layouts.app')

@section('title', 'Clientes - OLIKA Dashboard')

@section('content')
<div class="space-y-6 animate-in fade-in duration-500">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold tracking-tight">Clientes</h1>
            <p class="text-muted-foreground">Gerencie sua base de clientes</p>
        </div>
        <a href="{{ route('dashboard.customers.create') }}" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2 gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-plus h-4 w-4">
                <path d="M5 12h14"></path>
                <path d="M12 5v14"></path>
            </svg>
            Novo Cliente
        </a>
    </div>
    <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
        <div class="flex flex-col space-y-1.5 p-6">
            <div class="relative">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-search absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground">
                    <circle cx="11" cy="11" r="8"></circle>
                    <path d="m21 21-4.3-4.3"></path>
                </svg>
                <form method="GET" action="{{ route('dashboard.customers.index') }}" class="w-full">
                    <input type="text" name="q" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm pl-10" placeholder="Buscar clientes por nome, email ou telefone..." value="{{ $search ?? '' }}" onkeydown="if(event.key === 'Enter') this.form.submit();">
                    <button type="submit" class="hidden"></button>
                </form>
            </div>
        </div>
        <div class="p-6 pt-0">
            <div class="overflow-x-auto">
                <div class="relative w-full overflow-auto">
                    <table class="w-full caption-bottom text-sm">
                        <thead class="[&_tr]:border-b">
                            <tr class="border-b transition-colors data-[state=selected]:bg-muted hover:bg-muted/50">
                                <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground [&:has([role=checkbox])]:pr-0">Cliente</th>
                                <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground [&:has([role=checkbox])]:pr-0">Contato</th>
                                <th class="h-12 px-4 align-middle font-medium text-muted-foreground [&:has([role=checkbox])]:pr-0 text-right">Pedidos</th>
                                <th class="h-12 px-4 align-middle font-medium text-muted-foreground [&:has([role=checkbox])]:pr-0 text-right">Total Gasto</th>
                                <th class="h-12 px-4 align-middle font-medium text-muted-foreground [&:has([role=checkbox])]:pr-0 text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="[&_tr:last-child]:border-0">
                            @forelse($customers as $customer)
                                @php
                                    $initials = strtoupper(substr($customer->name ?? '', 0, 1) . substr($customer->name ?? '', strpos($customer->name ?? '', ' ') + 1, 1) ?? '');
                                    if (empty($initials) && !empty($customer->name)) {
                                        $initials = strtoupper(substr($customer->name, 0, 2));
                                    }
                                @endphp
                                <tr class="border-b transition-colors data-[state=selected]:bg-muted hover:bg-muted/50">
                                    <td class="p-4 align-middle [&:has([role=checkbox])]:pr-0">
                                        <div class="flex items-center gap-3">
                                            <span class="relative flex h-10 w-10 shrink-0 overflow-hidden rounded-full">
                                                <span class="flex h-full w-full items-center justify-center rounded-full bg-primary text-primary-foreground">{{ $initials }}</span>
                                            </span>
                                            <div>
                                                <div class="font-medium">{{ $customer->name ?? 'Sem nome' }}</div>
                                                @if($customer->email)
                                                <div class="text-sm text-muted-foreground flex items-center gap-1">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-mail h-3 w-3">
                                                        <rect width="20" height="16" x="2" y="4" rx="2"></rect>
                                                        <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path>
                                                    </svg>
                                                    {{ $customer->email }}
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-4 align-middle [&:has([role=checkbox])]:pr-0">
                                        @if($customer->phone)
                                        <div class="flex items-center gap-1 text-muted-foreground">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-phone h-3 w-3">
                                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                                            </svg>
                                            {{ $customer->phone }}
                                        </div>
                                        @else
                                        <span class="text-muted-foreground text-sm">-</span>
                                        @endif
                                    </td>
                                    <td class="p-4 align-middle [&:has([role=checkbox])]:pr-0 text-right font-medium">{{ $customer->total_orders ?? 0 }}</td>
                                    <td class="p-4 align-middle [&:has([role=checkbox])]:pr-0 text-right font-semibold">R$ {{ number_format($customer->total_spent ?? 0, 2, ',', '.') }}</td>
                                    <td class="p-4 align-middle [&:has([role=checkbox])]:pr-0 text-right">
                                        <a href="{{ route('dashboard.customers.show', $customer->id) }}" class="inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 hover:bg-accent hover:text-accent-foreground h-9 rounded-md px-3">Ver perfil</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="p-8 text-center text-muted-foreground">
                                        Nenhum cliente encontrado.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if(method_exists($customers, 'links'))
                <div class="p-4 border-t">
                    {{ $customers->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
