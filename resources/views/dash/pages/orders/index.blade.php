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
            <form method="GET" action="{{ route('dashboard.orders.index') }}" class="flex flex-col sm:flex-row gap-4">
                <div class="relative flex-1">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-search absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.3-4.3"></path>
                    </svg>
                    <input type="search" name="q" value="{{ request('q') }}" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm pl-10" placeholder="Buscar por cliente, número do pedido...">
                </div>
                @if(request('q'))
                    <a href="{{ route('dashboard.orders.index') }}" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2">Limpar</a>
                @endif
            </form>
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
                            @php
                                $orders = $orders ?? collect();
                            @endphp
                            @forelse($orders as $order)
                                @php
                                    $statusColors = [
                                        'pending' => 'bg-muted text-muted-foreground',
                                        'confirmed' => 'bg-primary text-primary-foreground',
                                        'preparing' => 'bg-warning text-warning-foreground',
                                        'ready' => 'bg-primary/80 text-primary-foreground',
                                        'delivered' => 'bg-success text-success-foreground',
                                        'cancelled' => 'bg-destructive text-destructive-foreground',
                                    ];
                                    $statusLabel = [
                                        'pending' => 'Pendente',
                                        'confirmed' => 'Confirmado',
                                        'preparing' => 'Em Preparo',
                                        'ready' => 'Pronto',
                                        'delivered' => 'Entregue',
                                        'cancelled' => 'Cancelado',
                                    ];
                                    $paymentStatusColors = [
                                        'pending' => 'bg-muted text-muted-foreground',
                                        'paid' => 'bg-success text-success-foreground',
                                        'failed' => 'bg-destructive text-destructive-foreground',
                                        'refunded' => 'bg-warning text-warning-foreground',
                                    ];
                                    $paymentStatusLabel = [
                                        'pending' => 'Pendente',
                                        'paid' => 'Pago',
                                        'failed' => 'Falhou',
                                        'refunded' => 'Reembolsado',
                                    ];
                                    $statusColor = $statusColors[$order->status] ?? 'bg-muted text-muted-foreground';
                                    $statusText = $statusLabel[$order->status] ?? ucfirst($order->status);
                                    $paymentColor = $paymentStatusColors[$order->payment_status] ?? 'bg-muted text-muted-foreground';
                                    $paymentText = $paymentStatusLabel[$order->payment_status] ?? ucfirst($order->payment_status);
                                @endphp
                                <tr class="border-b transition-colors data-[state=selected]:bg-muted hover:bg-muted/50">
                                    <td class="p-4 align-middle [&:has([role=checkbox])]:pr-0 font-medium">{{ $order->order_number }}</td>
                                    <td class="p-4 align-middle [&:has([role=checkbox])]:pr-0">
                                        <div>
                                            <div class="font-medium">{{ $order->customer->name ?? 'Cliente não informado' }}</div>
                                            @if($order->customer && $order->customer->phone)
                                                <div class="text-xs text-muted-foreground">{{ $order->customer->phone }}</div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="p-4 align-middle [&:has([role=checkbox])]:pr-0 font-semibold">R$ {{ number_format($order->final_amount ?? $order->total_amount ?? 0, 2, ',', '.') }}</td>
                                    <td class="p-4 align-middle [&:has([role=checkbox])]:pr-0">
                                        <div class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 border-transparent {{ $statusColor }}">{{ $statusText }}</div>
                                    </td>
                                    <td class="p-4 align-middle [&:has([role=checkbox])]:pr-0">
                                        <div class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors {{ $paymentColor }}">{{ $paymentText }}</div>
                                    </td>
                                    <td class="p-4 align-middle [&:has([role=checkbox])]:pr-0 text-muted-foreground">
                                        @php
                                            try {
                                                $diff = $order->created_at->diffForHumans();
                                            } catch (\Exception $e) {
                                                $diff = $order->created_at->format('d/m/Y H:i');
                                            }
                                        @endphp
                                        {{ $diff }}
                                        <div class="text-xs">{{ $order->created_at->format('d/m/Y H:i') }}</div>
                                    </td>
                                    <td class="p-4 align-middle [&:has([role=checkbox])]:pr-0 text-right">
                                        <a href="{{ route('dashboard.orders.show', $order->id) }}" class="inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 hover:bg-accent hover:text-accent-foreground h-9 rounded-md px-3">Ver detalhes</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="p-8 text-center text-muted-foreground">
                                        Nenhum pedido encontrado.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @if(isset($orders) && method_exists($orders, 'links'))
            <div class="mt-4 flex justify-center">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</div>
@endsection