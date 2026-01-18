@extends('dashboard.layouts.app')

@section('page_title', '')
@section('page_subtitle', '')

@section('page_actions')
    @php
        $paymentColors = [
            'pending' => 'bg-muted text-muted-foreground',
            'paid' => 'bg-success text-success-foreground',
            'failed' => 'bg-destructive text-destructive-foreground',
            'refunded' => 'bg-warning text-warning-foreground',
        ];
        $paymentColor = $paymentColors[$order->payment_status] ?? 'bg-muted text-muted-foreground';
    @endphp
    <div class="flex items-center gap-2 flex-wrap">
        <div class="inline-flex items-center rounded-full border px-3 py-1.5 text-sm font-semibold {{ $paymentColor }}">
            {{ $order->status_label }} - {{ $order->payment_status_label }}
        </div>
        <div class="text-sm text-muted-foreground">
            {{ optional($order->created_at)->format('d/m/Y H:i') }}
        </div>
    </div>
@endsection

@section('content')
<div class="space-y-6 w-full min-w-0">
    <!-- Título e Subtítulo -->
    <div class="flex items-start justify-between gap-4 flex-wrap">
        <div class="space-y-1">
            <h1 class="text-2xl font-semibold tracking-tight">Pedido #{{ $order->order_number }}</h1>
            <p class="text-sm text-muted-foreground">Detalhes e gestão do pedido</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <button type="button" id="btn-print-receipt-direct" data-order-id="{{ $order->id }}" class="inline-flex items-center justify-center gap-2 rounded-md text-sm font-medium border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 px-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-printer">
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                    <path d="M6 9V3a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v6"></path>
                    <rect x="6" y="14" width="12" height="8"></rect>
                </svg>
                Imprimir Recibo Fiscal
            </button>
            <button type="button" id="btn-open-receipt" data-order-id="{{ $order->id }}" class="inline-flex items-center justify-center gap-2 rounded-md text-sm font-medium border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 px-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-receipt">
                    <path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2Z"></path>
                    <path d="M14 8H8"></path>
                    <path d="M16 12H8"></path>
                    <path d="M13 16H8"></path>
                    <path d="M18 8a2 2 0 0 0 0 4"></path>
                </svg>
                Ver Recibo
            </button>
            @if($order->payment_status !== 'refunded' && optional($order->customer)->phone)
                <form method="POST" action="{{ route('dashboard.orders.sendReceipt', $order->id) }}" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-md text-sm font-medium border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 px-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-send">
                            <path d="m22 2-7 20-4-9-9-4Z"></path>
                            <path d="M22 2 11 13"></path>
                        </svg>
                        Enviar recibo (WhatsApp)
                    </button>
                </form>
            @else
                <button type="button" class="inline-flex items-center justify-center gap-2 rounded-md text-sm font-medium border border-input bg-muted text-muted-foreground h-9 px-3" disabled>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-send">
                        <path d="m22 2-7 20-4-9-9-4Z"></path>
                        <path d="M22 2 11 13"></path>
                    </svg>
                    Enviar recibo (WhatsApp)
                </button>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-3 w-full min-w-0 max-w-full">
        <!-- Coluna 1: Itens, Cliente e Status -->
        <div class="space-y-4 min-w-0">
            <!-- Card: Itens do Pedido -->
            <div class="rounded-lg border bg-card text-card-foreground shadow-sm min-w-0 overflow-hidden" id="order-items-section">
            <div class="flex items-center justify-between p-4 pb-2">
                <h3 class="text-base font-semibold leading-none tracking-tight">Itens do Pedido</h3>
                <button type="button" id="btn-open-add-item-modal" class="inline-flex items-center justify-center gap-2 rounded-md text-sm font-medium bg-primary text-primary-foreground hover:bg-primary/90 h-8 px-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-plus">
                        <path d="M5 12h14"></path>
                        <path d="M12 5v14"></path>
                    </svg>
                    Adicionar Item
                </button>
            </div>
            <div class="px-4 pb-4 min-w-0">
                <div class="space-y-2" id="items-tbody">
                    @forelse($order->items as $item)
                        <div class="flex items-center justify-between py-2" data-item-id="{{ $item->id }}">
                            <div class="flex items-center gap-3 flex-1">
                                <div class="flex items-center gap-1">
                                    <button type="button" class="btn-decrease-quantity inline-flex items-center justify-center rounded-md p-1 hover:bg-accent hover:text-accent-foreground text-muted-foreground" data-order-id="{{ $order->id }}" data-item-id="{{ $item->id }}" data-delta="-1" title="Diminuir quantidade">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-minus">
                                            <path d="M5 12h14"></path>
                                        </svg>
                                    </button>
                                    <span class="text-sm font-medium item-quantity min-w-[2rem] text-center">{{ $item->quantity }}</span>
                                    <button type="button" class="btn-increase-quantity inline-flex items-center justify-center rounded-md p-1 hover:bg-accent hover:text-accent-foreground text-muted-foreground" data-order-id="{{ $order->id }}" data-item-id="{{ $item->id }}" data-delta="1" title="Aumentar quantidade">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-plus">
                                            <path d="M5 12h14"></path>
                                            <path d="M12 5v14"></path>
                                        </svg>
                                    </button>
                                </div>
                                <div class="flex-1">
                                    <p class="font-medium text-sm item-name">
                                        @if(!$item->product_id && $item->custom_name)
                                            Item Avulso - {{ $item->custom_name }}
                                        @elseif($item->custom_name)
                                            {{ $item->custom_name }}
                                        @elseif($item->product)
                                            {{ $item->product->name }}
                                        @else
                                            Produto (ID: {{ $item->product_id ?? 'N/A' }})
                                        @endif
                                    </p>
                                </div>
                                <span class="text-sm font-semibold item-total-price">R$ {{ number_format($item->total_price, 2, ',', '.') }}</span>
                                <button type="button" class="btn-remove-item inline-flex items-center justify-center rounded-md p-1 hover:bg-destructive/10 hover:text-destructive text-muted-foreground" data-order-id="{{ $order->id }}" data-item-id="{{ $item->id }}" title="Remover item">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trash-2">
                                        <path d="M3 6h18"></path>
                                        <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path>
                                        <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path>
                                        <line x1="10" x2="10" y1="11" y2="17"></line>
                                        <line x1="14" x2="14" y1="11" y2="17"></line>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="py-4 px-3 text-center text-sm text-muted-foreground">Nenhum item encontrado.</div>
                    @endforelse
                </div>
                
                <!-- Resumo Financeiro -->
                <div class="mt-4 pt-4 border-t space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-muted-foreground">Subtotal:</span>
                        <span id="subtotal" class="font-semibold">R$ {{ number_format($order->total_amount, 2, ',', '.') }}</span>
                    </div>
                    @if($order->delivery_fee > 0)
                    <div class="flex justify-between text-sm">
                        <span class="text-muted-foreground">Taxa de Entrega:</span>
                        <span id="delivery-fee" class="font-semibold">R$ {{ number_format($order->delivery_fee, 2, ',', '.') }}</span>
                    </div>
                    @endif
                    @if($order->discount_amount > 0)
                    <div class="flex justify-between text-sm text-green-600">
                        <span>
                            @if($order->coupon_code)
                                Cupom {{ $order->coupon_code }}
                            @elseif($order->manual_discount_type)
                                Desconto
                            @else
                                Desconto
                            @endif
                        </span>
                        <span id="discount-amount" class="font-semibold">- R$ {{ number_format($order->discount_amount, 2, ',', '.') }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between text-base font-bold pt-2 border-t">
                        <span>Total:</span>
                        <span id="final-total" class="text-primary">R$ {{ number_format($order->final_amount, 2, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>

            <!-- Card: Cliente -->
            @if($order->customer)
            <div class="rounded-lg border bg-card text-card-foreground shadow-sm min-w-0 overflow-hidden">
                <div class="flex flex-col p-4 pb-2">
                    <h3 class="text-base font-semibold leading-none tracking-tight">Cliente</h3>
                </div>
                <div class="px-4 pb-4 space-y-2">
                    <div class="text-sm">
                        <span class="text-muted-foreground">Nome:</span>
                        <span class="font-medium ml-2">{{ $order->customer->name }}</span>
                    </div>
                    @if($order->customer->phone)
                    <div class="text-sm">
                        <span class="text-muted-foreground">Telefone:</span>
                        <span class="font-medium ml-2">{{ $order->customer->phone }}</span>
                    </div>
                    @endif
                    @if($order->address)
                    <div class="text-sm pt-2 border-t">
                        <span class="text-muted-foreground block mb-1">Endereço de Entrega:</span>
                        <span class="font-medium">
                            {{ trim(($order->address->street ?? '').', '.($order->address->number ?? '')) }}
                            @if(!empty($order->address->complement)), {{ $order->address->complement }} @endif
                            @if(!empty($order->address->neighborhood)), {{ $order->address->neighborhood }} @endif
                            @if(!empty($order->address->city)), {{ $order->address->city }} @endif
                            @if(!empty($order->address->state)), {{ $order->address->state }} @endif
                            @if(!empty($order->address->cep ?? $order->address->zip_code)), CEP {{ $order->address->cep ?? $order->address->zip_code }} @endif
                        </span>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Card: Alterar Status -->
            <div class="rounded-lg border bg-card text-card-foreground shadow-sm min-w-0 overflow-hidden">
                <div class="flex flex-col p-4 pb-2">
                    <h3 class="text-base font-semibold leading-none tracking-tight">Alterar Status</h3>
                    <p class="text-sm text-muted-foreground mt-1">Atualize o status do pedido</p>
                </div>
                <div class="px-4 pb-4">
                    <form action="{{ route('dashboard.orders.updateStatus', $order->id) }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="text-sm font-medium mb-2 block">Status</label>
                            <select name="status" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
                                @foreach($availableStatuses as $status)
                                    <option value="{{ $status->code }}" {{ $order->status === $status->code ? 'selected' : '' }}>
                                        {{ $status->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-sm font-medium mb-2 block">Observação (opcional)</label>
                            <textarea name="note" rows="3" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm" placeholder="Adicione uma observação sobre esta mudança..."></textarea>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="checkbox" name="skip_notification" value="1" class="h-4 w-4 text-primary">
                            <label class="text-sm font-medium">Atualizar sem enviar notificação</label>
                        </div>
                        <button type="submit" class="w-full inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2">
                            Atualizar Status
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Coluna 2: Cupons, Descontos e Histórico -->
        <div class="space-y-4 min-w-0">
            <!-- Card: Cupom de Desconto -->
            <div class="rounded-lg border bg-card text-card-foreground shadow-sm min-w-0 overflow-hidden">
                <div class="flex flex-col p-4 pb-2">
                    <h3 class="text-base font-semibold leading-none tracking-tight">Cupom de Desconto</h3>
                    <p class="text-sm text-muted-foreground mt-1">Aplique ou remova cupons</p>
                </div>
                <div class="px-4 pb-4 space-y-4">
                    @if($availableCoupons->count() > 0)
                    <div>
                        <p class="text-sm font-medium mb-2">Cupons Disponíveis</p>
                        <div class="space-y-2">
                            @foreach($availableCoupons as $coupon)
                            <div class="flex items-center justify-between p-2 border rounded-md">
                                <div class="flex-1">
                                    <p class="text-sm font-medium">{{ $coupon->code }} - {{ $coupon->name }} {{ number_format($coupon->value, 2, ',', '.') }}{{ $coupon->type === 'percentage' ? '%' : '' }}</p>
                                </div>
                                <form action="{{ route('dashboard.orders.applyCoupon', $order->id) }}" method="POST" class="inline">
                                    @csrf
                                    <input type="hidden" name="coupon_code" value="{{ $coupon->code }}">
                                    <button type="submit" class="text-sm text-primary hover:underline">Aplicar</button>
                                </form>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                    <div>
                        <p class="text-sm font-medium mb-2">Código do Cupom</p>
                        <form action="{{ route('dashboard.orders.applyCoupon', $order->id) }}" method="POST" class="flex gap-2">
                            @csrf
                            <input type="text" name="coupon_code" placeholder="Digite o código do cupom" class="flex-1 rounded-md border border-input bg-background px-3 py-2 text-sm">
                            <button type="submit" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4">
                                Aplicar
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Card: Desconto Manual -->
            <div class="rounded-lg border bg-card text-card-foreground shadow-sm min-w-0 overflow-hidden">
                <div class="flex flex-col p-4 pb-2">
                    <h3 class="text-base font-semibold leading-none tracking-tight">Desconto Manual</h3>
                    <p class="text-sm text-muted-foreground mt-1">Aplique um desconto adicional</p>
                </div>
                <div class="px-4 pb-4">
                    <form action="{{ route('dashboard.orders.applyDiscount', $order->id) }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="text-sm font-medium mb-2 block">Tipo de Desconto</label>
                            <select name="discount_type" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
                                <option value="percentage">Porcentagem (%)</option>
                                <option value="fixed">Valor Fixo (R$)</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-sm font-medium mb-2 block">Valor</label>
                            <input type="number" name="discount_value" step="0.01" min="0" placeholder="Ex: 10 ou 10.50" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
                        </div>
                        <button type="submit" class="w-full inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2">
                            Aplicar Desconto
                        </button>
                    </form>
                </div>
            </div>

            <!-- Card: Histórico de Status -->
            @if(isset($statusHistory) && $statusHistory->count() > 0)
            <div class="rounded-lg border bg-card text-card-foreground shadow-sm min-w-0 overflow-hidden">
                <div class="flex flex-col p-4 pb-2">
                    <h3 class="text-base font-semibold leading-none tracking-tight">Histórico de Status</h3>
                    <p class="text-sm text-muted-foreground mt-1">Linha do tempo de alterações</p>
                </div>
                <div class="px-4 pb-4">
                    <div class="space-y-3">
                        @foreach($statusHistory as $history)
                            <div class="flex gap-4">
                                <div class="flex flex-col items-center">
                                    <div class="h-8 w-8 rounded-full border-2 border-primary flex items-center justify-center bg-background">
                                        <div class="h-3 w-3 rounded-full bg-primary"></div>
                                    </div>
                                    @if(!$loop->last)
                                        <div class="w-0.5 h-full bg-border mt-2"></div>
                                    @endif
                                </div>
                                <div class="flex-1 pb-3">
                                    <div class="flex items-center gap-2">
                                        <p class="text-sm font-semibold">
                                            @if($history->old_status)
                                                {{ ucfirst(str_replace('_', ' ', $history->old_status)) }} → {{ ucfirst(str_replace('_', ' ', $history->new_status)) }}
                                            @else
                                                {{ ucfirst(str_replace('_', ' ', $history->new_status)) }}
                                            @endif
                                        </p>
                                    </div>
                                    @if($history->note)
                                        <p class="text-xs text-muted-foreground mt-0.5">{{ $history->note }}</p>
                                    @endif
                                    <p class="text-xs text-muted-foreground mt-0.5">{{ \Carbon\Carbon::parse($history->created_at)->format('d/m/Y H:i') }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Coluna 3: Pagamento, Estorno, Taxa e Edição -->
        <div class="space-y-4 min-w-0">
            <!-- Card: Pagamento -->
            <div class="rounded-lg border bg-card text-card-foreground shadow-sm min-w-0 overflow-hidden">
                <div class="flex flex-col p-4 pb-2">
                    <h3 class="text-base font-semibold leading-none tracking-tight">Pagamento</h3>
                </div>
                <div class="px-4 pb-4 space-y-2">
                    <div class="text-sm">
                        <span class="text-muted-foreground">Método:</span>
                        <span class="font-medium ml-2">{{ ucfirst($order->payment_method ?? 'N/A') }}</span>
                    </div>
                    <div class="text-sm">
                        <span class="text-muted-foreground">Status:</span>
                        <span class="font-medium ml-2">{{ $order->payment_status_label }}</span>
                    </div>
                </div>
            </div>

            <!-- Card: Estornar Pedido -->
            @if($order->payment_status === 'paid' && $order->status !== 'cancelled')
            <div class="rounded-lg border bg-card text-card-foreground shadow-sm min-w-0 overflow-hidden">
                <div class="flex flex-col p-4 pb-2">
                    <h3 class="text-base font-semibold leading-none tracking-tight">Estornar Pedido</h3>
                    <p class="text-sm text-muted-foreground mt-1">Cancelar venda e reverter todas as transações relacionadas</p>
                </div>
                <div class="px-4 pb-4">
                    <form action="{{ route('dashboard.orders.refund', $order->id) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja estornar este pedido? Esta ação não pode ser desfeita.');">
                        @csrf
                        @method('POST')
                        <div class="mb-4">
                            <label class="text-sm font-medium mb-2 block">Motivo do estorno (opcional)</label>
                            <textarea name="reason" rows="3" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm" placeholder="Informe o motivo do estorno..."></textarea>
                        </div>
                        <button type="submit" class="w-full inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium bg-destructive text-destructive-foreground hover:bg-destructive/90 h-10 px-4 py-2">
                            Estornar Pedido
                        </button>
                    </form>
                </div>
            </div>
            @endif

            <!-- Card: Taxa de Entrega -->
            <div class="rounded-lg border bg-card text-card-foreground shadow-sm min-w-0 overflow-hidden">
                <div class="flex flex-col p-4 pb-2">
                    <h3 class="text-base font-semibold leading-none tracking-tight">Taxa de Entrega</h3>
                    <p class="text-sm text-muted-foreground mt-1">Ajuste manual da taxa de entrega</p>
                </div>
                <div class="px-4 pb-4">
                    <form action="{{ route('dashboard.orders.adjustDeliveryFee', $order->id) }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="text-sm font-medium mb-2 block">Taxa Atual</label>
                            <p class="text-sm font-semibold">R$ {{ number_format($order->delivery_fee ?? 0, 2, ',', '.') }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium mb-2 block">Nova Taxa</label>
                            <input type="number" name="delivery_fee" step="0.01" min="0" value="{{ $order->delivery_fee ?? 0 }}" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="text-sm font-medium mb-2 block">Motivo do Ajuste (opcional)</label>
                            <textarea name="reason" rows="2" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm" placeholder="Ex: Desconto especial, erro no cálculo..."></textarea>
                        </div>
                        <button type="submit" class="w-full inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2">
                            Atualizar Taxa
                        </button>
                    </form>
                </div>
            </div>

            <!-- Card: Editar Informações do Pedido -->
            <div class="rounded-lg border bg-card text-card-foreground shadow-sm min-w-0 overflow-hidden">
                <div class="flex flex-col p-4 pb-2">
                    <h3 class="text-base font-semibold leading-none tracking-tight">Editar Informações do Pedido</h3>
                    <p class="text-sm text-muted-foreground mt-1">Atualize observações e instruções</p>
                </div>
                <div class="px-4 pb-4">
                    <form action="{{ route('dashboard.orders.update', $order->id) }}" method="POST" class="space-y-4">
                        @csrf
                        @method('PUT')
                        
                        <div>
                            <label class="text-sm font-medium mb-2 block">Observações Internas</label>
                            <textarea name="notes" rows="3" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm" placeholder="Observações visíveis apenas para a equipe...">{{ $order->notes ?? '' }}</textarea>
                        </div>
                        
                        <div>
                            <label class="text-sm font-medium mb-2 block">Observações do Cliente</label>
                            <textarea name="observations" rows="3" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm" placeholder="Observações do cliente...">{{ $order->observations ?? '' }}</textarea>
                        </div>
                        
                        <div>
                            <label class="text-sm font-medium mb-2 block">Instruções de Entrega</label>
                            <textarea name="delivery_instructions" rows="3" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm" placeholder="Instruções especiais para entrega...">{{ $order->delivery_instructions ?? '' }}</textarea>
                        </div>
                        
                        <div class="space-y-2">
                            <label class="text-sm font-medium">Data e Hora de Entrega Agendada</label>
                            <div class="flex gap-2 items-center">
                                <input type="date" id="scheduled_delivery_date" value="{{ $order->scheduled_delivery_at ? $order->scheduled_delivery_at->format('Y-m-d') : '' }}" class="flex-1 rounded-md border border-input bg-background px-3 py-2 text-sm">
                                <input type="time" id="scheduled_delivery_time" value="{{ $order->scheduled_delivery_at ? $order->scheduled_delivery_at->format('H:i') : '' }}" class="flex-1 rounded-md border border-input bg-background px-3 py-2 text-sm">
                            </div>
                            <input type="hidden" name="scheduled_delivery_at" id="scheduled_delivery_at" value="{{ $order->scheduled_delivery_at ? $order->scheduled_delivery_at->format('Y-m-d H:i:s') : '' }}">
                            <p class="text-xs text-muted-foreground">Deixe em branco para remover o agendamento</p>
                        </div>
                        
                        <script>
                            // Combinar data e hora em scheduled_delivery_at
                            function updateScheduledDelivery() {
                                const dateInput = document.getElementById('scheduled_delivery_date');
                                const timeInput = document.getElementById('scheduled_delivery_time');
                                const hiddenInput = document.getElementById('scheduled_delivery_at');
                                
                                if (dateInput && timeInput && hiddenInput) {
                                    const date = dateInput.value;
                                    const time = timeInput.value;
                                    
                                    if (date && time) {
                                        hiddenInput.value = date + ' ' + time + ':00';
                                    } else {
                                        hiddenInput.value = '';
                                    }
                                }
                            }
                            
                            document.getElementById('scheduled_delivery_date')?.addEventListener('change', updateScheduledDelivery);
                            document.getElementById('scheduled_delivery_time')?.addEventListener('change', updateScheduledDelivery);
                        </script>
                        
                    
                    <div class="space-y-3 pt-2">
                        <div class="flex items-center gap-2">
                            <input type="checkbox" id="create_payment" name="create_payment" value="1" class="h-4 w-4 text-primary">
                            <label for="create_payment" class="text-sm font-medium">Criar nova cobrança para este pedido</label>
                        </div>
                        
                        <div id="payment_methods" class="hidden space-y-3 pl-6">
                            <div>
                                <label class="text-sm font-medium mb-2 block">Método de Pagamento</label>
                                <select name="payment_method" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
                                    <option value="pix">PIX</option>
                                    <option value="credit_card">Cartão de Crédito</option>
                                    <option value="debit_card">Cartão de Débito</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-2">
                            <input type="checkbox" id="send_whatsapp" name="send_whatsapp" value="1" checked class="h-4 w-4 text-primary">
                            <label for="send_whatsapp" class="text-sm font-medium">Enviar notificação via WhatsApp ao cliente</label>
                        </div>
                        
                        <div id="whatsapp_message" class="space-y-2">
                            <label class="text-sm font-medium">Mensagem personalizada (opcional)</label>
                            <textarea name="whatsapp_message" rows="3" class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm" placeholder="Deixe em branco para usar mensagem padrão">Olá! Seu pedido {{ $order->order_number }} foi atualizado. Valor total: R$ {{ number_format($order->final_amount ?? $order->total_amount, 2, ',', '.') }}</textarea>
                            <p class="text-xs text-muted-foreground">O link de pagamento será adicionado automaticamente se uma cobrança for criada.</p>
                        </div>
                        
                        <div class="flex items-center gap-2">
                            <input type="checkbox" id="skip_notification" name="skip_notification" value="1" class="h-4 w-4 text-primary">
                            <label for="skip_notification" class="text-sm font-medium">Salvar sem enviar notificação ao cliente</label>
                        </div>
                        
                        <button type="submit" class="w-full inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2">
                            Salvar Alterações
                        </button>
                    </div>
                </form>
                
                        <script>
                            document.getElementById('create_payment')?.addEventListener('change', function(e) {
                                document.getElementById('payment_methods').classList.toggle('hidden', !e.target.checked);
                            });
                        </script>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
    <!-- Modal para Adicionar Item -->
    <div id="add-item-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
        <div class="bg-card rounded-lg shadow-lg w-full max-w-md mx-4 border">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold">Adicionar Item ao Pedido</h3>
                    <button type="button" id="btn-close-add-item-modal-header" class="text-muted-foreground hover:text-foreground">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x">
                            <path d="M18 6 6 18"></path>
                            <path d="M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <form action="{{ route('dashboard.orders.addItem', $order->id) }}" method="POST">
                    @csrf
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">Produto *</label>
                            <select id="product-select" name="product_id" required class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                                <option value="">Selecione um produto</option>
                                {{-- <option value="loose_item">Item Avulso</option> --}}
                                @foreach($availableProducts as $product)
                                    <option value="{{ $product->id }}" data-price="{{ $product->price }}">
                                        {{ $product->name }} - R$ {{ number_format($product->price, 2, ',', '.') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Campos para Item Avulso (Temporariamente desabilitado) --}}
                        {{-- <div id="loose-item-fields" class="hidden space-y-4">
                            <div>
                                <label class="block text-sm font-medium mb-2">Nome do Item *</label>
                                <input type="text" id="loose-item-name" name="custom_name" maxlength="255" placeholder="Ex: Molho de pimenta" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-2">Valor *</label>
                                <input type="number" id="loose-item-price" name="unit_price" step="0.01" min="0.01" placeholder="0.00" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-2">Quantidade *</label>
                                <input type="number" id="loose-item-quantity" name="quantity" min="1" value="1" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-2">Descrição (opcional)</label>
                                <textarea id="loose-item-description" name="special_instructions" rows="2" maxlength="500" placeholder="Ex: Molho artesanal, picante" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"></textarea>
                            </div>
                        </div> --}}

                        <!-- Campos para Produto Normal (visíveis por padrão) -->
                        <div id="normal-item-fields">
                            <div>
                                <label class="block text-sm font-medium mb-2">Quantidade *</label>
                                <input type="number" name="quantity" id="quantity" min="1" value="1" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-2">Preço Unitário (opcional)</label>
                                <input type="number" name="unit_price" id="unit_price" step="0.01" min="0" placeholder="Deixe em branco para usar o preço padrão" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                                <p class="text-xs text-muted-foreground mt-1">Se não preenchido, será usado o preço padrão do produto</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-2">Nome Personalizado (opcional)</label>
                                <input type="text" name="custom_name" maxlength="255" placeholder="Ex: Focaccia Especial" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-2">Observações Especiais (opcional)</label>
                                <textarea name="special_instructions" id="special_instructions" rows="2" maxlength="500" placeholder="Ex: Com pouco sal, sem azeitona" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-3 mt-6">
                        <button type="button" id="btn-close-add-item-modal-footer" class="flex-1 inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4">
                            Cancelar
                        </button>
                        <button type="submit" class="flex-1 inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4">
                            Adicionar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        console.log('🚀 SCRIPT INICIADO - Verificando formulário de adicionar item');
        
        // ============================================
        // FUNÇÕES GLOBAIS - DEFINIR PRIMEIRO
        // ============================================
        
        // Função para abrir modal de recibo - DEFINIR PRIMEIRO para estar disponível globalmente
        // Definir tanto como window.openReceiptModal quanto como openReceiptModal diretamente
        function openReceiptModal(orderId) {
            console.log('🔔 openReceiptModal chamado com orderId:', orderId);
            const modal = document.getElementById('receipt-modal');
            const content = document.getElementById('receipt-modal-content');
            
            if (!modal) {
                console.error('❌ Modal de recibo não encontrado!');
                alert('Erro: Modal de recibo não encontrado.');
                return;
            }
            
            // Usar async/await dentro
            (async () => {
                try {
                    // Mostrar modal com loading
                    modal.classList.remove('hidden');
                    content.innerHTML = '<div class="text-center p-8">Carregando...</div>';
                    
                    // Buscar conteúdo via AJAX
                    const url = `{{ route('dashboard.orders.receipt', '__ORDER__') }}`.replace('__ORDER__', orderId);
                    console.log('📡 Buscando recibo em:', url);
                    
                    const response = await fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'text/html',
                        }
                    });
                    
                    console.log('📥 Resposta recebida:', response.status, response.statusText);
                    
                    if (!response.ok) {
                        throw new Error('Erro ao carregar recibo: ' + response.status);
                    }
                    
                    const html = await response.text();
                    content.innerHTML = html;
                    console.log('✅ Recibo carregado com sucesso');
                } catch (error) {
                    console.error('❌ Erro ao carregar recibo:', error);
                    content.innerHTML = '<div class="text-center p-8 text-red-600">Erro ao carregar recibo. Tente novamente.</div>';
                }
            })();
        }
        
        // Também expor no window para garantir
        window.openReceiptModal = openReceiptModal;
        
        // Fechar modal de recibo
        function closeReceiptModal() {
            const modal = document.getElementById('receipt-modal');
            if (modal) {
                modal.classList.add('hidden');
            }
        }
        
        // Também expor no window
        window.closeReceiptModal = closeReceiptModal;
        
        // Função para atualizar quantidade do item via AJAX - GLOBAL
        function updateItemQuantity(orderId, itemId, delta) {
            // Encontrar o botão correto baseado no delta
            const buttonSelector = delta > 0 
                ? `.btn-increase-quantity[data-item-id="${itemId}"]`
                : `.btn-decrease-quantity[data-item-id="${itemId}"]`;
            const button = document.querySelector(buttonSelector);
            
            if (!button) {
                console.error('Botão não encontrado:', buttonSelector);
                return;
            }
            
            // Encontrar o container do item (div com data-item-id)
            const itemContainer = button.closest('div[data-item-id]');
            if (!itemContainer) {
                console.error('Container do item não encontrado');
                return;
            }
            
            const quantitySpan = itemContainer.querySelector('.item-quantity');
            const totalPriceSpan = itemContainer.querySelector('.item-total-price');
            
            if (!quantitySpan) {
                console.error('Elemento de quantidade não encontrado');
                return;
            }
            
            // Desabilitar botão temporariamente
            button.disabled = true;
            
            // Usar async/await dentro
            (async () => {
                try {
                    // Construir URLs usando template com placeholders que serão substituídos
                    @php
                        $addUrl = route('dashboard.orders.addItemQuantity', ['order' => 999999, 'item' => 888888]);
                        $addUrl = str_replace(['999999', '888888'], ['__ORDER__', '__ITEM__'], $addUrl);
                        $reduceUrl = route('dashboard.orders.reduceItemQuantity', ['order' => 999999, 'item' => 888888]);
                        $reduceUrl = str_replace(['999999', '888888'], ['__ORDER__', '__ITEM__'], $reduceUrl);
                    @endphp
                    const addUrlTemplate = '{{ $addUrl }}';
                    const reduceUrlTemplate = '{{ $reduceUrl }}';
                    const url = delta > 0 
                        ? addUrlTemplate.replace('__ORDER__', orderId).replace('__ITEM__', itemId)
                        : reduceUrlTemplate.replace('__ORDER__', orderId).replace('__ITEM__', itemId);
                    
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                    });

                    const data = await response.json();

                    if (data.success) {
                        if (data.removed) {
                            // Remover item do DOM
                            itemContainer.remove();
                            // Verificar se não há mais itens
                            const itemsContainer = document.getElementById('items-tbody');
                            if (itemsContainer && itemsContainer.children.length === 0) {
                                itemsContainer.innerHTML = '<div class="py-4 px-3 text-center text-sm text-muted-foreground">Nenhum item encontrado.</div>';
                            }
                        } else if (data.item) {
                            // Atualizar quantidade e total do item
                            quantitySpan.textContent = data.item.quantity;
                            if (totalPriceSpan) {
                                totalPriceSpan.textContent = 'R$ ' + data.item.total_price;
                            }
                        }
                        
                        // Atualizar totais
                        updateOrderTotals(data.order);
                        
                        // Mostrar mensagem de sucesso
                        showSuccessMessage(delta > 0 ? 'Quantidade aumentada com sucesso!' : 'Quantidade reduzida com sucesso!');
                    } else {
                        showErrorMessage(data.error || 'Erro ao atualizar quantidade');
                    }
                } catch (error) {
                    console.error('Erro:', error);
                    showErrorMessage('Erro ao atualizar quantidade. Tente novamente.');
                } finally {
                    button.disabled = false;
                }
            })();
        }
        
        // Expor no window também
        window.updateItemQuantity = updateItemQuantity;
        
        // Função para remover item via AJAX - GLOBAL
        function removeItem(orderId, itemId) {
            if (!confirm('Tem certeza que deseja remover este item completamente do pedido?')) {
                return;
            }

            const itemContainer = document.querySelector(`div[data-item-id="${itemId}"]`);
            if (!itemContainer) return;

            // Usar async/await dentro
            (async () => {
                try {
                    @php
                        $removeUrl = route('dashboard.orders.removeItem', ['order' => 999999, 'item' => 888888]);
                        $removeUrl = str_replace(['999999', '888888'], ['__ORDER__', '__ITEM__'], $removeUrl);
                    @endphp
                    const url = '{{ $removeUrl }}'.replace('__ORDER__', orderId).replace('__ITEM__', itemId);
                    const response = await fetch(url, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                    });

                    const data = await response.json();

                    if (data.success) {
                        // Remover item do DOM
                        itemContainer.remove();
                        // Verificar se não há mais itens
                        const itemsContainer = document.getElementById('items-tbody');
                        if (itemsContainer && itemsContainer.children.length === 0) {
                            itemsContainer.innerHTML = '<div class="py-4 px-3 text-center text-sm text-muted-foreground">Nenhum item encontrado.</div>';
                        }
                        
                        // Atualizar totais
                        updateOrderTotals(data.order);
                        
                        showSuccessMessage('Item removido com sucesso!');
                    } else {
                        showErrorMessage(data.error || 'Erro ao remover item');
                    }
                } catch (error) {
                    console.error('Erro:', error);
                    showErrorMessage('Erro ao remover item. Tente novamente.');
                }
            })();
        }
        
        // Expor no window também
        window.removeItem = removeItem;
        
        // ============================================
        // HANDLERS DO FORMULÁRIO DE ADICIONAR ITEM
        // ============================================
        
        // Item Avulso temporariamente desabilitado
        // Gerenciar exibição de campos para Item Avulso vs Produto Normal
        document.getElementById('product-select')?.addEventListener('change', function(e) {
            console.log('🔵 Select mudou:', e.target.value);
            // const isLooseItem = e.target.value === 'loose_item';
            const normalFields = document.getElementById('normal-item-fields');
            
            // Preencher preço unitário automaticamente ao selecionar produto
            const option = e.target.options[e.target.selectedIndex];
            const price = option.dataset.price;
            const unitPriceInput = document.getElementById('unit_price');
            if (price && unitPriceInput && !unitPriceInput.value) {
                unitPriceInput.value = parseFloat(price).toFixed(2);
            }
            
            // Garantir que campos normais estão visíveis
            if (normalFields) normalFields.classList.remove('hidden');
        });
        
        // Função para inicializar handlers do formulário
        function initAddItemForm() {
            console.log('🔍 Buscando formulário de adicionar item...');
            const addItemForm = document.querySelector('form[action*="addItem"]');
            console.log('📋 Formulário encontrado:', addItemForm);
            
            if (!addItemForm) {
                console.error('❌ Formulário não encontrado!');
                return false;
            }
            
            // Verificar se já foi inicializado
            if (addItemForm.dataset.initialized === 'true') {
                console.log('⚠️ Formulário já foi inicializado, pulando...');
                return true;
            }
            
            console.log('✅ Formulário encontrado, configurando handlers');
            const productSelect = document.getElementById('product-select');
            // Item Avulso temporariamente desabilitado
            // const looseNameEl = document.getElementById('loose-item-name');
            // const loosePriceEl = document.getElementById('loose-item-price');
            // const looseQuantityEl = document.getElementById('loose-item-quantity');
            // const looseDescriptionEl = document.getElementById('loose-item-description');
            const normalQuantityEl = document.getElementById('quantity');
            const normalUnitPriceEl = document.getElementById('unit_price');
            const normalInstructionsEl = document.getElementById('special_instructions');
            
            console.log('🔍 Elementos DOM encontrados:', {
                productSelect: !!productSelect,
                normalQuantityEl: !!normalQuantityEl,
                normalUnitPriceEl: !!normalUnitPriceEl,
            });
            
            if (!productSelect || !normalQuantityEl) {
                console.error('❌ ERRO: Elementos DOM essenciais não encontrados!');
                return false;
            }
            
            // Item Avulso temporariamente desabilitado - função configureFieldsForLooseItem comentada
            
            // Flag para evitar loop infinito no submit
            let isSubmitting = false;
            
            // Handler do submit - garantir que campos estejam corretos
            console.log('📝 Adicionando event listener ao formulário');
            addItemForm.addEventListener('submit', function(e) {
                console.log('🔔 EVENTO SUBMIT DISPARADO!', {
                    target: e.target,
                    timestamp: new Date().toISOString()
                });
                
                // Se já está submetendo, permitir submit normal
                if (isSubmitting) {
                    console.log('⏭️ Já está submetendo, permitindo submit normal');
                    return true;
                }
                
                // Item Avulso temporariamente desabilitado
                // const isLooseItem = productSelect && productSelect.value === 'loose_item';
                
                // IMPORTANTE: SEMPRE prevenir o submit primeiro para configurar campos
                e.preventDefault();
                e.stopPropagation();
                console.log('🛑 Submit prevenido');
                
                // Item Avulso temporariamente desabilitado - lógica comentada
                // Produto normal - validar que um produto foi selecionado
                if (!productSelect || !productSelect.value || productSelect.value === '' || productSelect.value === 'loose_item') {
                    alert('Por favor, selecione um produto válido.');
                    if (productSelect) productSelect.focus();
                    return false;
                }
                    
                    // Garantir que select tem name="product_id"
                    if (productSelect && !productSelect.hasAttribute('name')) {
                        productSelect.setAttribute('name', 'product_id');
                    }
                    
                    // Remover qualquer input hidden de product_id vazio
                    const existingHiddens = this.querySelectorAll('input[name="product_id"][type="hidden"]');
                    existingHiddens.forEach(hidden => hidden.remove());
                    
                    // Item Avulso temporariamente desabilitado - remoção de names comentada
                    
                    // Garantir que campos normais tenham name
                    if (normalQuantityEl) normalQuantityEl.setAttribute('name', 'quantity');
                    if (normalUnitPriceEl) normalUnitPriceEl.setAttribute('name', 'unit_price');
                    if (normalInstructionsEl) normalInstructionsEl.setAttribute('name', 'special_instructions');
                    
                    // Validar quantidade para produto normal
                    if (!normalQuantityEl || !normalQuantityEl.value || parseInt(normalQuantityEl.value) < 1) {
                        alert('Por favor, preencha uma quantidade válida.');
                        if (normalQuantityEl) normalQuantityEl.focus();
                        return false;
                    }
                    
                    // Debug: log do que será enviado
                    console.log('Produto Normal - Submetendo:', {
                        product_id: productSelect?.value,
                        quantity: normalQuantityEl?.value,
                        unit_price: normalUnitPriceEl?.value,
                    });
                    
                    // Se chegou até aqui, campos estão corretos - permitir submit
                    isSubmitting = true;
                    this.submit();
            });
            
            // Marcar como inicializado para evitar múltiplas inicializações
            addItemForm.dataset.initialized = 'true';
            console.log('✅ Handlers configurados com sucesso!');
            return true;
        }
        
        // Tentar inicializar imediatamente
        initAddItemForm();
        
        // Também inicializar quando o modal for aberto
        const openModalBtn = document.getElementById('btn-open-add-item-modal');
        if (openModalBtn) {
            openModalBtn.addEventListener('click', function() {
                const modal = document.getElementById('add-item-modal');
                if (modal) {
                    modal.classList.remove('hidden');
                }
                // Aguardar um pouco para o DOM atualizar e então inicializar
                setTimeout(() => {
                    initAddItemForm();
                }, 100);
            });
        }

        
        // ============================================
        // EVENT LISTENERS - Substituir onclick inline
        // ============================================

        function setupInteractionHandlers() {
            if (window.__orderPageHandlersInitialized__) {
                return;
            }
            window.__orderPageHandlersInitialized__ = true;
            console.log('🔧 Configurando event listeners...');
            
            // Botão de abrir recibo
            const btnOpenReceipt = document.getElementById('btn-open-receipt');
            if (btnOpenReceipt) {
                btnOpenReceipt.addEventListener('click', function() {
                    const orderId = this.getAttribute('data-order-id');
                    if (orderId) {
                        openReceiptModal(parseInt(orderId));
                    }
                });
                console.log('✅ Event listener do botão de recibo configurado');
            }
            
            // Botão de fechar recibo (no header do modal)
            const btnCloseReceipt = document.getElementById('btn-close-receipt-modal');
            if (btnCloseReceipt) {
                btnCloseReceipt.addEventListener('click', function() {
                    closeReceiptModal();
                });
                console.log('✅ Event listener do botão de fechar recibo configurado');
            }
            
            // Fechar modal de recibo ao clicar fora
            const receiptModal = document.getElementById('receipt-modal');
            if (receiptModal) {
                receiptModal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        closeReceiptModal();
                    }
                });
                console.log('✅ Event listener de fechar modal ao clicar fora configurado');
            }
            
            // Botões de aumentar/diminuir quantidade (usar event delegation para itens dinâmicos)
            const itemsTbody = document.getElementById('items-tbody');
            if (itemsTbody) {
                itemsTbody.addEventListener('click', function(e) {
                    // Botão de diminuir quantidade
                    if (e.target.closest('.btn-decrease-quantity')) {
                        const btn = e.target.closest('.btn-decrease-quantity');
                        const orderId = parseInt(btn.getAttribute('data-order-id'));
                        const itemId = parseInt(btn.getAttribute('data-item-id'));
                        const delta = parseInt(btn.getAttribute('data-delta'));
                        if (orderId && itemId && delta) {
                            updateItemQuantity(orderId, itemId, delta);
                        }
                    }
                    // Botão de aumentar quantidade
                    if (e.target.closest('.btn-increase-quantity')) {
                        const btn = e.target.closest('.btn-increase-quantity');
                        const orderId = parseInt(btn.getAttribute('data-order-id'));
                        const itemId = parseInt(btn.getAttribute('data-item-id'));
                        const delta = parseInt(btn.getAttribute('data-delta'));
                        if (orderId && itemId && delta) {
                            updateItemQuantity(orderId, itemId, delta);
                        }
                    }
                    // Botão de remover item
                    if (e.target.closest('.btn-remove-item')) {
                        const btn = e.target.closest('.btn-remove-item');
                        const orderId = parseInt(btn.getAttribute('data-order-id'));
                        const itemId = parseInt(btn.getAttribute('data-item-id'));
                        if (orderId && itemId) {
                            removeItem(orderId, itemId);
                        }
                    }
                });
                console.log('✅ Event listeners de quantidade e remover item configurados (delegation)');
            }
            
            // Botão de abrir modal de adicionar item
            const btnOpenAddItem = document.getElementById('btn-open-add-item-modal');
            if (btnOpenAddItem) {
                btnOpenAddItem.addEventListener('click', function() {
                    const modal = document.getElementById('add-item-modal');
                    if (modal) {
                        modal.classList.remove('hidden');
                    }
                });
                console.log('✅ Event listener do botão de abrir modal de adicionar item configurado');
            }
            
            // Botões de fechar modal de adicionar item
            const btnCloseAddItemHeader = document.getElementById('btn-close-add-item-modal-header');
            const btnCloseAddItemFooter = document.getElementById('btn-close-add-item-modal-footer');
            const closeAddItemModal = function() {
                const modal = document.getElementById('add-item-modal');
                if (modal) {
                    modal.classList.add('hidden');
                }
            };
            
            if (btnCloseAddItemHeader) {
                btnCloseAddItemHeader.addEventListener('click', closeAddItemModal);
            }
            if (btnCloseAddItemFooter) {
                btnCloseAddItemFooter.addEventListener('click', closeAddItemModal);
            }
            console.log('✅ Event listeners dos botões de fechar modal de adicionar item configurados');
            
            console.log('✅ Todos os event listeners configurados com sucesso!');
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', setupInteractionHandlers);
        } else {
            setupInteractionHandlers();
        }

        // Função para atualizar totais do pedido
        function updateOrderTotals(orderData) {
            const subtotalEl = document.getElementById('subtotal');
            const deliveryFeeEl = document.getElementById('delivery-fee');
            const discountAmountEl = document.getElementById('discount-amount');
            const finalTotalEl = document.getElementById('final-total');

            if (subtotalEl) subtotalEl.textContent = 'R$ ' + orderData.total_amount;
            if (deliveryFeeEl) {
                const fee = parseFloat(orderData.delivery_fee.replace(',', '.').replace('R$ ', ''));
                const feeRow = deliveryFeeEl.closest('div');
                if (fee > 0) {
                    deliveryFeeEl.textContent = 'R$ ' + orderData.delivery_fee;
                    if (feeRow) feeRow.style.display = '';
                } else {
                    if (feeRow) feeRow.style.display = 'none';
                }
            }
            if (discountAmountEl) {
                const discount = parseFloat(orderData.discount_amount.replace(',', '.').replace('- R$ ', '').replace('R$ ', ''));
                const discountRow = discountAmountEl.closest('div');
                if (discount > 0) {
                    discountAmountEl.textContent = '- R$ ' + orderData.discount_amount;
                    if (discountRow) discountRow.style.display = '';
                } else {
                    if (discountRow) discountRow.style.display = 'none';
                }
            }
            if (finalTotalEl) finalTotalEl.textContent = 'R$ ' + orderData.final_amount;
        }

        // Funções para mostrar mensagens
        function showSuccessMessage(message) {
            const alertDiv = document.createElement('div');
            alertDiv.className = 'fixed top-4 right-4 z-50 rounded-lg border bg-green-500 text-white px-4 py-3 shadow-lg';
            alertDiv.textContent = message;
            document.body.appendChild(alertDiv);
            setTimeout(() => {
                alertDiv.remove();
            }, 3000);
        }

        function showErrorMessage(message) {
            const alertDiv = document.createElement('div');
            alertDiv.className = 'fixed top-4 right-4 z-50 rounded-lg border bg-red-500 text-white px-4 py-3 shadow-lg';
            alertDiv.textContent = message;
            document.body.appendChild(alertDiv);
            setTimeout(() => {
                alertDiv.remove();
            }, 5000);
        }
    </script>

    <!-- Modal de Recibo -->
    <div id="receipt-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div class="bg-background rounded-lg shadow-xl w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col">
            <div class="flex items-center justify-between p-4 border-b">
                <h2 class="text-lg font-semibold">Recibo do Pedido</h2>
                <button type="button" id="btn-close-receipt-modal" class="text-muted-foreground hover:text-foreground">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x">
                        <path d="M18 6 6 18"></path>
                        <path d="M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div id="receipt-modal-content" class="overflow-y-auto p-6">
                <!-- Conteúdo será carregado via AJAX -->
            </div>
    </div>
</div>

<!-- QZ Tray Script para impressão direta -->
<script src="https://cdn.jsdelivr.net/npm/qz-tray@2.2/qz-tray.min.js"></script>
<script>
    // Função para verificar se QZ Tray está conectado
    function isQZTrayConnected() {
        try {
            return typeof qz !== 'undefined' && 
                   qz !== null && 
                   qz.websocket !== null && 
                   qz.websocket.isActive();
        } catch (error) {
            return false;
        }
    }

    // Conectar ao QZ Tray
    async function connectQZTray() {
        try {
            if (typeof qz === 'undefined' || qz === null) {
                throw new Error('QZ Tray não está carregado. Verifique se o QZ Tray está instalado e rodando.');
            }
            
            if (isQZTrayConnected()) {
                console.log('✅ QZ Tray já estava conectado');
                return true;
            }
            
            await qz.websocket.connect();
            
            if (isQZTrayConnected()) {
                console.log('✅ QZ Tray conectado com sucesso');
                return true;
            } else {
                throw new Error('Falha ao verificar conexão após tentativa de conexão');
            }
        } catch (error) {
            console.error('❌ Erro ao conectar QZ Tray:', error);
            return false;
        }
    }

    // Detectar se é dispositivo móvel
    function isMobileDevice() {
        return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ||
               (window.innerWidth <= 768);
    }

    // Imprimir recibo diretamente
    async function printReceiptDirect(orderId) {
        // Se for mobile, adicionar à fila de impressão
        if (isMobileDevice()) {
            const btn = document.getElementById('btn-print-receipt-direct');
            let originalText = '';
            if (btn) {
                originalText = btn.innerHTML;
                btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-loader-2 animate-spin"><path d="M21 12a9 9 0 1 1-6.219-8.56"></path></svg> Enviando...';
                btn.disabled = true;
            }
            
            try {
                const response = await fetch(`/dashboard/orders/${orderId}/request-print`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    if (btn) {
                        btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check"><path d="M20 6 9 17l-5-5"></path></svg> Na fila!';
                        btn.classList.add('bg-success');
                        setTimeout(() => {
                            btn.innerHTML = originalText;
                            btn.disabled = false;
                            btn.classList.remove('bg-success');
                        }, 2000);
                    }
                    alert('✅ Pedido adicionado à fila de impressão!\n\nO recibo será impresso automaticamente no desktop.');
                } else {
                    throw new Error(data.message || 'Erro ao adicionar à fila');
                }
            } catch (error) {
                console.error('❌ Erro ao solicitar impressão:', error);
                alert('❌ Erro ao solicitar impressão: ' + (error.message || 'Erro desconhecido'));
                if (btn && originalText) {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }
            }
            return;
        }
        
        // Desktop: imprimir diretamente via QZ Tray
        const PRINTER_NAME = "EPSON TM-T20X";
        
        if (typeof qz === 'undefined') {
            alert('❌ QZ Tray não está carregado.\n\nPor favor, instale e inicie o QZ Tray antes de imprimir.');
            return;
        }
        
        if (!isQZTrayConnected()) {
            try {
                const connected = await connectQZTray();
                if (!connected) {
                    alert('❌ Não foi possível conectar ao QZ Tray.\n\nCertifique-se de que o QZ Tray está instalado e rodando.');
                    return;
                }
            } catch (error) {
                alert('❌ Erro ao conectar ao QZ Tray:\n\n' + error.message);
                return;
            }
        }
        
        try {
            const printers = await qz.printers.find();
            if (!printers || printers.length === 0) {
                alert('Nenhuma impressora encontrada.');
                return;
            }
            
            // Buscar impressora EPSON TM-20X
            const printer = printers.find(p => 
                p.toUpperCase().includes('EPSON') && 
                (p.toUpperCase().includes('TM-20') || p.toUpperCase().includes('TM-T20'))
            ) || printers[0];
            
            if (!printer) {
                alert(`❌ Impressora "${PRINTER_NAME}" não encontrada.\nVerifique se está conectada.`);
                return;
            }
            
            console.log('🖨️ Usando impressora:', printer);
            
            const response = await fetch(`/dashboard/orders/${orderId}/fiscal-receipt/escpos`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });
            
            if (!response.ok) {
                throw new Error(`Erro ao buscar dados: ${response.status}`);
            }
            
            const orderData = await response.json();
            if (!orderData.success || !orderData.data) {
                throw new Error('Dados inválidos do servidor.');
            }
            
            console.log('📦 Base64 recebido (ESC/POS), tamanho:', orderData.data.length);
            
            const printConfig = qz.configs.create(printer);
            
            // Enviar para impressão
            await qz.print(printConfig, [{
                type: 'raw',
                format: 'base64',
                data: orderData.data
            }]);
            
            console.log('✅ Recibo enviado para impressora:', printer);
            
            // Mostrar feedback visual
            const btn = document.getElementById('btn-print-receipt-direct');
            if (btn) {
                const originalText = btn.innerHTML;
                btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check"><path d="M20 6 9 17l-5-5"></path></svg> Enviado!';
                btn.disabled = true;
                btn.classList.add('bg-success');
                setTimeout(() => {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                    btn.classList.remove('bg-success');
                }, 2000);
            }
        } catch (error) {
            console.error('❌ Erro ao imprimir:', error);
            alert('❌ Erro ao imprimir: ' + (error.message || 'Erro desconhecido'));
        }
    }

    // Botão de impressão direta
    document.addEventListener('DOMContentLoaded', function() {
        const btnPrint = document.getElementById('btn-print-receipt-direct');
        if (btnPrint) {
            btnPrint.addEventListener('click', function() {
                const orderId = this.getAttribute('data-order-id');
                if (orderId) {
                    printReceiptDirect(orderId);
                }
            });
        }
    });

    // Polling automático para atualizar status do pedido
    (function() {
        'use strict';
        
        const POLL_INTERVAL = 5000; // 5 segundos
        const orderId = {{ $order->id }};
        let lastStatus = '{{ $order->status }}';
        let lastPaymentStatus = '{{ $order->payment_status }}';
        let lastUpdatedAt = '{{ $order->updated_at->toIso8601String() }}';
        let pollingInterval = null;
        let isPolling = false;
        
        async function checkOrderStatus() {
            if (isPolling) return;
            
            isPolling = true;
            
            try {
                const response = await fetch(`/dashboard/orders/${orderId}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin'
                });
                
                if (!response.ok) {
                    throw new Error('Erro ao buscar status do pedido');
                }
                
                const html = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                
                // Extrair status atual da página
                const statusElement = doc.querySelector('[data-order-status]') || 
                                    doc.querySelector('.inline-flex.items-center.rounded-full.border');
                const paymentStatusElement = doc.querySelector('[data-payment-status]');
                
                if (statusElement) {
                    const currentStatus = statusElement.getAttribute('data-order-status') || 
                                        statusElement.textContent.trim().toLowerCase().replace(/\s+/g, '_');
                    const currentPaymentStatus = paymentStatusElement?.getAttribute('data-payment-status') || 
                                                paymentStatusElement?.textContent.trim().toLowerCase().replace(/\s+/g, '_');
                    
                    // Verificar se houve mudança
                    if (currentStatus !== lastStatus || currentPaymentStatus !== lastPaymentStatus) {
                        // Recarregar página para mostrar mudanças
                        window.location.reload();
                        return;
                    }
                }
            } catch (error) {
                console.error('Erro ao verificar status do pedido:', error);
            } finally {
                isPolling = false;
            }
        }
        
        function startPolling() {
            if (pollingInterval) {
                clearInterval(pollingInterval);
            }
            
            // Primeira verificação após 3 segundos
            setTimeout(checkOrderStatus, 3000);
            
            // Depois verificar a cada X segundos
            pollingInterval = setInterval(checkOrderStatus, POLL_INTERVAL);
        }
        
        // Parar polling quando a página perder foco (economizar recursos)
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                if (pollingInterval) {
                    clearInterval(pollingInterval);
                    pollingInterval = null;
                }
            } else {
                if (!pollingInterval) {
                    startPolling();
                }
            }
        });
        
        // Iniciar polling
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', startPolling);
        } else {
            startPolling();
        }
    })();
</script>
@endsection

