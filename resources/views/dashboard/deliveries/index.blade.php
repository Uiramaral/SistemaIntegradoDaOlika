@extends('dashboard.layouts.app')

@section('page_title', 'Entregas')
@section('page_subtitle', 'Acompanhe uma visão detalhada das métricas e resultados')

@section('content')
<div class="space-y-6">

    @if(session('success'))
        <div class="rounded-md border border-green-200 bg-green-50 text-green-700 p-4">
            {{ session('success') }}
        </div>
    @endif

    @if($orders->isEmpty())
        <div class="rounded-lg border bg-white shadow-sm p-12 text-center">
            <svg class="mx-auto mb-4 h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
            </svg>
            <h3 class="text-lg font-semibold mb-2 text-gray-900">Sem entregas pendentes</h3>
            <p class="text-gray-500">
                Quando houver pedidos agendados ou em rota, eles serão exibidos aqui.
            </p>
        </div>
    @else
        <!-- Tabela de Entregas - Estilo do Site -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr class="border-b">
                            <th class="h-12 px-4 text-left align-middle text-xs font-medium text-gray-500 uppercase tracking-wider">PEDIDO</th>
                            <th class="h-12 px-4 text-left align-middle text-xs font-medium text-gray-500 uppercase tracking-wider">CLIENTE</th>
                            <th class="h-12 px-4 text-left align-middle text-xs font-medium text-gray-500 uppercase tracking-wider">ENDEREÇO</th>
                            <th class="h-12 px-4 text-left align-middle text-xs font-medium text-gray-500 uppercase tracking-wider">HORÁRIO</th>
                            <th class="h-12 px-4 text-left align-middle text-xs font-medium text-gray-500 uppercase tracking-wider">VALOR</th>
                            <th class="h-12 px-4 text-left align-middle text-xs font-medium text-gray-500 uppercase tracking-wider">STATUS</th>
                            <th class="h-12 px-4 text-left align-middle text-xs font-medium text-gray-500 uppercase tracking-wider">AÇÕES</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($orders as $order)
                            @php
                                $customer = $order->customer;
                                $address = $order->address;
                                $deliveryTime = optional($order->scheduled_delivery_at);
                                $timeRange = $deliveryTime ? $deliveryTime->format('H:i') . ' - ' . $deliveryTime->addHour()->format('H:i') : '-';
                            @endphp
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="font-semibold text-gray-900">#{{ $order->order_number }}</span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-gray-900">{{ $customer->name ?? 'Cliente não identificado' }}</div>
                                    @if($customer && $customer->phone)
                                    <div class="text-sm text-gray-500">{{ $customer->phone }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if($address)
                                    <div class="flex items-center gap-1 text-sm text-gray-500">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        {{ $address->street ?? '' }}{{ $address->number ? ', ' . $address->number : '' }}{{ $address->neighborhood ? ' - ' . $address->neighborhood : '' }}
                                    </div>
                                    @else
                                    <span class="text-sm text-gray-500">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="flex items-center gap-1 text-sm text-gray-500">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        {{ $timeRange }}
                                    </div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="font-semibold text-gray-900">R$ {{ number_format($order->final_amount ?? 0, 2, ',', '.') }}</span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @php
                                        $statusColors = [
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            'confirmed' => 'bg-green-100 text-green-800',
                                            'preparing' => 'bg-blue-100 text-blue-800',
                                            'ready' => 'bg-purple-100 text-purple-800',
                                            'out_for_delivery' => 'bg-orange-100 text-orange-800',
                                            'delivered' => 'bg-green-100 text-green-800',
                                            'cancelled' => 'bg-red-100 text-red-800',
                                        ];
                                        $statusLabels = [
                                            'pending' => 'Pendente',
                                            'confirmed' => 'Confirmado',
                                            'preparing' => 'Em Preparo',
                                            'ready' => 'Pronto',
                                            'out_for_delivery' => 'Em Rota',
                                            'delivered' => 'Entregue',
                                            'cancelled' => 'Cancelado',
                                        ];
                                        $statusColor = $statusColors[$order->status] ?? 'bg-gray-100 text-gray-800';
                                        $statusLabel = $statusLabels[$order->status] ?? ucfirst($order->status);
                                    @endphp
                                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $statusColor }}">{{ $statusLabel }}</span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if($order->status === 'pending' || $order->status === 'confirmed')
                                    <button class="px-4 py-2 text-sm font-medium text-primary bg-white border border-primary rounded-lg hover:bg-primary hover:text-white transition-colors">
                                        Iniciar
                                    </button>
                                    @elseif($order->status === 'out_for_delivery')
                                    <button class="px-4 py-2 text-sm font-medium text-primary bg-white border border-primary rounded-lg hover:bg-primary hover:text-white transition-colors">
                                        Entregar
                                    </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
            @foreach($orders as $order)
                @php
                    $customer = $order->customer;
                    $address  = $order->address;
                    $phoneDigits = preg_replace('/\D/', '', optional($customer)->phone ?? '');
                    $mapQuery = rawurlencode(collect([
                        optional($address)->street ?? null,
                        optional($address)->number ?? null,
                        optional($address)->neighborhood ?? null,
                        optional($address)->city ?? null,
                        optional($address)->state ?? null,
                        optional($address)->cep ?? null,
                    ])->filter()->implode(', '));
                    $deliveryTime = optional($order->scheduled_delivery_at)->format('d/m H:i');
                    $statusBadges = [
                        'confirmed' => ['label' => 'Confirmado', 'color' => 'bg-blue-100 text-blue-700'],
                        'preparing' => ['label' => 'Em preparo', 'color' => 'bg-amber-100 text-amber-700'],
                        'ready' => ['label' => 'Pronto p/ coleta', 'color' => 'bg-emerald-100 text-emerald-700'],
                        'out_for_delivery' => ['label' => 'A caminho', 'color' => 'bg-primary/10 text-primary'],
                    ];
                    $badge = $statusBadges[$order->status] ?? ['label' => ucfirst(str_replace('_', ' ', $order->status)), 'color' => 'bg-muted text-muted-foreground'];
                @endphp

                <div class="flex flex-col rounded-lg border bg-card text-card-foreground shadow-sm">
                    <div class="border-b border-border/70 bg-muted/30 p-4 flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-semibold">#{{ $order->order_number }}</h2>
                            <p class="text-sm text-muted-foreground">
                                Entrega {{ $deliveryTime ?? 'sem horário' }}
                            </p>
                        </div>
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $badge['color'] }}">
                            {{ $badge['label'] }}
                        </span>
                    </div>

                    <div class="p-4 space-y-4 flex-1">
                        <div class="space-y-1">
                            <p class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Cliente</p>
                            <p class="text-base font-medium">{{ $customer->name ?? 'Cliente não identificado' }}</p>
                            @if($phoneDigits)
                                <a href="https://wa.me/55{{ $phoneDigits }}" target="_blank" class="inline-flex items-center gap-1 text-sm text-primary hover:underline">
                                    <i data-lucide="phone" class="h-4 w-4"></i>
                                    ({{ substr($phoneDigits, 0, 2) }}) {{ substr($phoneDigits, 2, 5) }}-{{ substr($phoneDigits, 7) }}
                                </a>
                            @endif
                        </div>

                        <div class="space-y-2">
                            <p class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Endereço</p>
                            @if($address)
                                <div class="text-sm leading-relaxed">
                                    {{ $address->street ?? 'Rua não informada' }}{{ $address->number ? ', '.$address->number : '' }}<br>
                                    {{ $address->neighborhood ?? '' }}{{ $address->neighborhood && ($address->city || $address->state) ? ' - ' : '' }}{{ $address->city ?? '' }}{{ ($address->city && $address->state) ? '/' : '' }}{{ $address->state ?? '' }}<br>
                                    {{ $address->cep ?? '' }}
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    @if($mapQuery)
                                        <a href="https://www.google.com/maps/search/?api=1&query={{ $mapQuery }}" target="_blank"
                                           class="inline-flex items-center gap-2 rounded-md border border-input px-3 py-2 text-sm hover:bg-accent hover:text-accent-foreground">
                                            <i data-lucide="map-pin" class="h-4 w-4"></i>
                                            Google Maps
                                        </a>
                                    @endif
                                    @if(!empty($address->latitude) && !empty($address->longitude))
                                        <a href="waze://?ll={{ $address->latitude }},{{ $address->longitude }}&navigate=yes"
                                           class="inline-flex items-center gap-2 rounded-md border border-input px-3 py-2 text-sm hover:bg-accent hover:text-accent-foreground">
                                            <i data-lucide="navigation" class="h-4 w-4"></i>
                                            Abrir no Waze
                                        </a>
                                    @endif
                                </div>
                            @else
                                <div class="text-sm text-muted-foreground italic">
                                    Endereço não cadastrado
                                </div>
                            @endif
                        </div>

                        @if($order->items->count())
                            <div class="space-y-1">
                                <p class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Itens</p>
                                <ul class="list-disc list-inside text-sm text-muted-foreground">
                                    @foreach($order->items as $item)
                                        <li>{{ $item->quantity }}x {{ $item->custom_name ?? optional($item->product)->name ?? 'Produto' }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if($order->notes)
                            <div class="space-y-1">
                                <p class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Observações do pedido</p>
                                <p class="text-sm rounded-md border border-border/60 bg-muted/30 p-2">
                                    {{ $order->notes }}
                                </p>
                            </div>
                        @endif
                    </div>

                    <div class="border-t border-border/70 bg-muted/10 p-4 space-y-3">
                        <form action="{{ route('dashboard.deliveries.status', $order) }}" method="POST" class="space-y-3">
                            @csrf
                            <div class="space-y-2">
                                <label for="note-{{ $order->id }}" class="text-sm font-medium text-muted-foreground">
                                    Observação rápida para o cliente
                                </label>
                                <textarea
                                    id="note-{{ $order->id }}"
                                    name="note"
                                    rows="2"
                                    class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                                    placeholder="Ex.: Chegaremos em 10 minutos..."></textarea>
                            </div>

                            <div class="grid gap-2 sm:grid-cols-2">
                                <button type="submit" name="status" value="out_for_delivery"
                                        class="inline-flex items-center justify-center rounded-md bg-primary text-primary-foreground px-3 py-2 text-sm font-medium hover:bg-primary/90">
                                    <i data-lucide="send" class="mr-2 h-4 w-4"></i>
                                    Notificar que está a caminho
                                </button>
                                <button type="submit" name="status" value="delivered"
                                        class="inline-flex items-center justify-center rounded-md bg-green-600 text-white px-3 py-2 text-sm font-medium hover:bg-green-700">
                                    <i data-lucide="check-circle" class="mr-2 h-4 w-4"></i>
                                    Confirmar entrega finalizada
                                </button>
                            </div>
                        </form>

                        <p class="text-xs text-muted-foreground">
                            Essas ações usam o mesmo fluxo de notificações do gerenciador de pedidos (WhatsApp / BotConversa, quando configurados).
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (window.lucide) {
            window.lucide.createIcons();
        }
    });
</script>
@endpush
@endsection


