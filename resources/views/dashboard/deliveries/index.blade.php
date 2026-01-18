@extends('dashboard.layouts.app')

@section('title', 'Entregas - OLIKA Painel')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-3xl font-bold tracking-tight">Painel de Entregas</h1>
        <p class="text-muted-foreground">
            Visão simplificada dos pedidos com entrega agendada, pronta para o time de rua.
        </p>
    </div>

    @if(session('success'))
        <div class="rounded-md border border-green-200 bg-green-50 text-green-700 p-4">
            {{ session('success') }}
        </div>
    @endif

    @if($orders->isEmpty())
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm p-12 text-center">
            <i data-lucide="clipboard-list" class="mx-auto mb-4 h-12 w-12 text-muted-foreground"></i>
            <h3 class="text-lg font-semibold mb-2">Sem entregas pendentes</h3>
            <p class="text-muted-foreground">
                Quando houver pedidos agendados ou em rota, eles serão exibidos aqui.
            </p>
        </div>
    @else
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach($orders as $order)
                @php
                    $customer = $order->customer;
                    $address  = $order->address;
                    $phoneDigits = preg_replace('/\D/', '', optional($customer)->phone ?? '');
                    $mapQuery = rawurlencode(collect([
                        optional($address)->street,
                        optional($address)->number,
                        optional($address)->neighborhood,
                        optional($address)->city,
                        optional($address)->state,
                        optional($address)->cep,
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
                            <div class="text-sm leading-relaxed">
                                {{ optional($address)->street ?? 'Rua não informada' }}{{ optional($address)->number ? ', '.optional($address)->number : '' }}<br>
                                {{ optional($address)->neighborhood ?? '' }} {{ optional($address)->neighborhood ? '-' : '' }} {{ optional($address)->city ?? '' }}/{{ optional($address)->state ?? '' }}<br>
                                {{ optional($address)->cep ?? '' }}
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <a href="https://www.google.com/maps/search/?api=1&query={{ $mapQuery }}" target="_blank"
                                   class="inline-flex items-center gap-2 rounded-md border border-input px-3 py-2 text-sm hover:bg-accent hover:text-accent-foreground">
                                    <i data-lucide="map-pin" class="h-4 w-4"></i>
                                    Google Maps
                                </a>
                                @if(!empty(optional($address)->latitude) && !empty(optional($address)->longitude))
                                    <a href="waze://?ll={{ optional($address)->latitude }},{{ optional($address)->longitude }}&navigate=yes"
                                       class="inline-flex items-center gap-2 rounded-md border border-input px-3 py-2 text-sm hover:bg-accent hover:text-accent-foreground">
                                        <i data-lucide="navigation" class="h-4 w-4"></i>
                                        Abrir no Waze
                                    </a>
                                @endif
                            </div>
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


