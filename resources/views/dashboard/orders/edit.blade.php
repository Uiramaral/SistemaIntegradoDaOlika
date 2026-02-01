@extends('dashboard.layouts.app')

@section('page_title', 'Editar Pedido')
@section('page_subtitle', 'Detalhes e gestão do pedido')

@section('content')
    <div class="space-y-6 w-full min-w-0 max-w-full overflow-hidden">
        <!-- Cabeçalho Limpo -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('dashboard.orders.index') }}"
                    class="inline-flex items-center justify-center rounded-md p-2 hover:bg-gray-100 transition-colors text-gray-600 hover:text-gray-900">
                    <i data-lucide="arrow-left" class="w-5 h-5"></i>
                </a>
                <h1 class="text-2xl font-semibold">Pedido #{{ $order->order_number }}</h1>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" id="btn-open-receipt" data-order-id="{{ $order->id }}"
                    class="inline-flex items-center justify-center h-9 w-9 rounded-lg border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 transition-colors"
                    title="Ver Recibo">
                    <i data-lucide="receipt" class="w-4 h-4"></i>
                </button>
                @if($order->payment_status !== 'refunded' && optional($order->customer)->phone)
                    <form method="POST" action="{{ route('dashboard.orders.sendReceipt', $order) }}" class="inline">
                        @csrf
                        <button type="submit"
                            class="inline-flex items-center justify-center h-9 w-9 rounded-lg border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 transition-colors"
                            title="Enviar recibo via WhatsApp">
                            <i data-lucide="send" class="w-4 h-4"></i>
                        </button>
                    </form>
                @endif
                <div
                    class="relative inline-flex items-center gap-2 pl-3 pr-4 h-9 rounded-lg border border-gray-300 bg-white">
                    <div class="w-7 h-7 rounded-full bg-primary/10 flex items-center justify-center flex-shrink-0">
                        <i data-lucide="user" class="w-4 h-4 text-primary"></i>
                    </div>
                    <div class="flex flex-col items-start min-w-0">
                        <span
                            class="text-xs font-medium text-gray-900 truncate max-w-[120px]">{{ auth()->user()->name ?? 'Admin' }}</span>
                        <span class="text-[10px] text-gray-500 truncate max-w-[120px]">{{ auth()->user()->email ??
                            'admin@olika.com.br' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Layout de 2 Colunas: 2/3 e 1/3 -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 w-full min-w-0 max-w-full">
            <!-- Coluna Esquerda - 2/3 da largura -->
            <div class="space-y-6 min-w-0 lg:col-span-2">
                <!-- Card: Itens do Pedido -->
                <div class="bg-card rounded-xl border border-border shadow-sm overflow-hidden">
                    <div class="flex items-center justify-between p-4 sm:p-6 border-b border-border">
                        <h3 class="text-lg font-semibold">Itens do Pedido</h3>
                        <button type="button" id="btn-open-add-item-modal" class="btn-primary gap-2 h-9 px-4 text-sm">
                            <i data-lucide="plus" class="w-4 h-4"></i>
                            <span>Adicionar Item</span>
                        </button>
                    </div>
                    <div class="p-4 sm:p-6 space-y-3">
                        <div class="space-y-3" id="items-tbody">
                            @forelse($order->items as $item)
                                <div class="flex items-center justify-between py-2 border-b border-border/50 last:border-0"
                                    data-item-id="{{ $item->id }}">
                                    <div class="flex items-center gap-3 flex-1 min-w-0">
                                        <div class="flex items-center gap-1 flex-shrink-0">
                                            <button type="button"
                                                class="btn-decrease-quantity inline-flex items-center justify-center rounded-md p-1 hover:bg-accent hover:text-accent-foreground text-muted-foreground"
                                                data-order-id="{{ $order->id }}" data-item-id="{{ $item->id }}" data-delta="-1"
                                                title="Diminuir quantidade">
                                                <i data-lucide="minus" class="w-4 h-4"></i>
                                            </button>
                                            <span
                                                class="text-sm font-medium item-quantity min-w-[2rem] text-center">{{ $item->quantity }}</span>
                                            <button type="button"
                                                class="btn-increase-quantity inline-flex items-center justify-center rounded-md p-1 hover:bg-accent hover:text-accent-foreground text-muted-foreground"
                                                data-order-id="{{ $order->id }}" data-item-id="{{ $item->id }}" data-delta="1"
                                                title="Aumentar quantidade">
                                                <i data-lucide="plus" class="w-4 h-4"></i>
                                            </button>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="font-medium text-sm item-name truncate">
                                                @if(!$item->product_id && $item->custom_name)
                                                    Item Avulso - {{ $item->custom_name }}
                                                @elseif($item->custom_name)
                                                    {{ $item->custom_name }}
                                                @elseif($item->product)
                                                    {{ optional($item->product)->name ?? 'Produto (Nome Indisponível)' }}
                                                @else
                                                    Produto (ID: {{ $item->product_id ?? 'N/A' }})
                                                @endif
                                            </p>
                                        </div>
                                        <span class="text-sm font-semibold item-total-price whitespace-nowrap flex-shrink-0">R$
                                            {{ number_format($item->total_price, 2, ',', '.') }}</span>
                                        <button type="button"
                                            class="btn-remove-item inline-flex items-center justify-center rounded-md p-1 hover:bg-destructive/10 hover:text-destructive text-muted-foreground flex-shrink-0"
                                            data-order-id="{{ $order->id }}" data-item-id="{{ $item->id }}"
                                            title="Remover item">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                </div>
                            @empty
                                <div class="py-4 px-3 text-center text-sm text-muted-foreground">Nenhum item encontrado.</div>
                            @endforelse
                        </div>

                        <!-- Resumo Financeiro -->
                        <div class="mt-4 pt-4 border-t border-border space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-muted-foreground">Subtotal:</span>
                                <span id="subtotal" class="font-semibold">R$
                                    {{ number_format($order->total_amount, 2, ',', '.') }}</span>
                            </div>
                            @if($order->delivery_fee > 0)
                                <div class="flex justify-between text-sm">
                                    <span class="text-muted-foreground">Taxa de Entrega:</span>
                                    <span id="delivery-fee" class="font-semibold">R$
                                        {{ number_format($order->delivery_fee, 2, ',', '.') }}</span>
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
                                    <span id="discount-amount" class="font-semibold">- R$
                                        {{ number_format($order->discount_amount, 2, ',', '.') }}</span>
                                </div>
                            @endif
                            <div class="flex justify-between text-base font-bold pt-2 border-t border-border">
                                <span>Total:</span>
                                <span id="final-total" class="text-primary">R$
                                    {{ number_format($order->final_amount, 2, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card: Cliente -->
                @if($order->customer)
                    <div class="bg-card rounded-xl border border-border shadow-sm overflow-hidden">
                        <div class="p-4 sm:p-6 border-b border-border">
                            <h3 class="text-lg font-semibold">Cliente</h3>
                        </div>
                        <div class="p-4 sm:p-6 space-y-2">
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
                        </div>
                    </div>
                @endif

                <!-- Card: Alterar Status -->
                <div class="bg-card rounded-xl border border-border shadow-sm overflow-hidden">
                    <div class="p-4 sm:p-6 border-b border-border">
                        <h3 class="text-lg font-semibold">Alterar Status</h3>
                        <p class="text-sm text-muted-foreground mt-1">Atualize o status do pedido</p>
                    </div>
                    <div class="p-4 sm:p-6">
                        <form action="{{ route('dashboard.orders.updateStatus', $order) }}" method="POST" class="space-y-4">
                            @csrf
                            <div>
                                <label class="text-sm font-medium mb-2 block">Status</label>
                                <select name="status"
                                    class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
                                    @foreach($availableStatuses as $status)
                                        <option value="{{ $status->code }}" {{ $order->status === $status->code ? 'selected' : '' }}>
                                            {{ $status->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="text-sm font-medium mb-2 block">Observação (opcional)</label>
                                <textarea name="note" rows="3"
                                    class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
                                    placeholder="Adicione uma observação sobre esta mudança..."></textarea>
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="checkbox" name="skip_notification" value="1" id="skip_notification_status"
                                    class="h-4 w-4 text-primary rounded border-input">
                                <label for="skip_notification_status" class="text-sm font-medium cursor-pointer">Atualizar
                                    sem enviar notificação</label>
                            </div>
                            <button type="submit" class="w-full btn-primary h-10">
                                Atualizar Status
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Coluna Direita - 1/3 da largura -->
            <div class="space-y-6 min-w-0 lg:col-span-1">
                <!-- Card: Cupom de Desconto -->
                <div class="bg-card rounded-xl border border-border shadow-sm overflow-hidden">
                    <div class="p-4 sm:p-6 border-b border-border">
                        <h3 class="text-lg font-semibold">Cupom de Desconto</h3>
                        <p class="text-sm text-muted-foreground mt-1">Aplique ou remova cupons</p>
                    </div>
                    <div class="p-4 sm:p-6 space-y-4">
                        @if($availableCoupons->count() > 0)
                            <div>
                                <p class="text-sm font-medium mb-2">Cupons Disponíveis</p>
                                <div class="space-y-2">
                                    @foreach($availableCoupons as $coupon)
                                        <div class="flex items-center justify-between p-3 border border-border rounded-lg">
                                            <div class="flex-1">
                                                <p class="text-sm font-medium">
                                                    {{ $coupon->name }}
                                                    {{ number_format($coupon->value, 2, ',', '.') }}{{ $coupon->type === 'percentage' ? '%' : '' }}
                                                </p>
                                            </div>
                                            <form action="{{ route('dashboard.orders.applyCoupon', $order) }}" method="POST"
                                                class="inline">
                                                @csrf
                                                <input type="hidden" name="coupon_code" value="{{ $coupon->code }}">
                                                <button type="submit"
                                                    class="text-sm text-muted-foreground hover:text-primary transition-colors">Aplicar</button>
                                            </form>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        <div>
                            <form action="{{ route('dashboard.orders.applyCoupon', $order) }}" method="POST"
                                class="flex gap-2">
                                @csrf
                                <input type="text" name="coupon_code" placeholder="Digite o código do cupom"
                                    class="flex-1 rounded-lg border border-input bg-background px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
                                <button type="submit" class="btn-primary h-10 px-4">
                                    Aplicar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Card: Desconto Manual -->
                <div class="bg-card rounded-xl border border-border shadow-sm overflow-hidden">
                    <div class="p-4 sm:p-6 border-b border-border">
                        <h3 class="text-lg font-semibold">Desconto Manual</h3>
                        <p class="text-sm text-muted-foreground mt-1">Aplique um desconto adicional</p>
                    </div>
                    <div class="p-4 sm:p-6">
                        <form action="{{ route('dashboard.orders.applyDiscount', $order) }}" method="POST"
                            class="space-y-4">
                            @csrf
                            <div>
                                <label class="text-sm font-medium mb-2 block">Tipo de Desconto</label>
                                <select name="discount_type"
                                    class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
                                    <option value="percentage">Porcentagem (%)</option>
                                    <option value="fixed">Valor Fixo (R$)</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-sm font-medium mb-2 block">Valor</label>
                                <input type="number" name="discount_value" step="0.01" min="0" placeholder="Ex: 10 ou 10.50"
                                    class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
                            </div>
                            <button type="submit" class="w-full btn-primary h-10">
                                Aplicar Desconto
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Card: Pagamento -->
                <div class="bg-card rounded-xl border border-border shadow-sm overflow-hidden">
                    <div class="p-4 sm:p-6 border-b border-border">
                        <h3 class="text-lg font-semibold">Pagamento</h3>
                    </div>
                    <div class="p-4 sm:p-6 space-y-2">
                        <div class="text-sm">
                            <span class="text-muted-foreground">Método:</span>
                            <span class="font-medium ml-2">{{ ucfirst($order->payment_method ?? 'N/A') }}</span>
                        </div>
                        <div class="text-sm">
                            <span class="text-muted-foreground">Status:</span>
                            @php
                                $paymentStatusClass = 'bg-warning text-warning-foreground';
                                if ($order->payment_status === 'paid' || $order->payment_status === 'approved') {
                                    $paymentStatusClass = 'bg-success text-success-foreground';
                                } elseif ($order->payment_status === 'failed' || $order->payment_status === 'cancelled') {
                                    $paymentStatusClass = 'bg-destructive text-destructive-foreground';
                                }
                            @endphp
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium ml-2 {{ $paymentStatusClass }}">
                                {{ $order->payment_status_label }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Card: Taxa de Entrega -->
                <div class="bg-card rounded-xl border border-border shadow-sm overflow-hidden">
                    <div class="p-4 sm:p-6 border-b border-border">
                        <h3 class="text-lg font-semibold">Taxa de Entrega</h3>
                        <p class="text-sm text-muted-foreground mt-1">Ajuste manual da taxa de entrega</p>
                    </div>
                    <div class="p-4 sm:p-6">
                        <form action="{{ route('dashboard.orders.adjustDeliveryFee', $order) }}" method="POST"
                            class="space-y-4">
                            @csrf
                            <div>
                                <label class="text-sm font-medium mb-2 block">Taxa Atual</label>
                                <p class="text-sm font-semibold">R$
                                    {{ number_format($order->delivery_fee ?? 0, 2, ',', '.') }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium mb-2 block">Nova Taxa</label>
                                <input type="number" name="delivery_fee" step="0.01" min="0"
                                    value="{{ $order->delivery_fee ?? 0 }}"
                                    class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
                            </div>
                            <div>
                                <label class="text-sm font-medium mb-2 block">Motivo do Ajuste (opcional)</label>
                                <textarea name="reason" rows="2"
                                    class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
                                    placeholder="Ex: Desconto especial, erro no cálculo..."></textarea>
                            </div>
                            <button type="submit" class="w-full btn-primary h-10">
                                Atualizar Taxa
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Card: Editar Informações do Pedido -->
                <div class="bg-card rounded-xl border border-border shadow-sm overflow-hidden">
                    <div class="p-4 sm:p-6 border-b border-border">
                        <h3 class="text-lg font-semibold">Editar Informações do Pedido</h3>
                        <p class="text-sm text-muted-foreground mt-1">Atualize observações e instruções</p>
                    </div>
                    <div class="p-4 sm:p-6">
                        <form action="{{ route('dashboard.orders.update', $order) }}" method="POST" class="space-y-4">
                            @csrf
                            @method('PUT')

                            <div>
                                <label class="text-sm font-medium mb-2 block">Observações Internas</label>
                                <textarea name="notes" rows="3"
                                    class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
                                    placeholder="Observações visíveis apenas para a equipe...">{{ $order->notes ?? '' }}</textarea>
                            </div>

                            <div>
                                <label class="text-sm font-medium mb-2 block">Observações do Cliente</label>
                                <textarea name="observations" rows="3"
                                    class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
                                    placeholder="Observações do cliente...">{{ $order->observations ?? '' }}</textarea>
                            </div>

                            <div>
                                <label class="text-sm font-medium mb-2 block">Instruções de Entrega</label>
                                <textarea name="delivery_instructions" rows="3"
                                    class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
                                    placeholder="Instruções especiais para entrega...">{{ $order->delivery_instructions ?? '' }}</textarea>
                            </div>

                            <div class="space-y-2">
                                <label class="text-sm font-medium">Data e Hora de Entrega Agendada</label>
                                <div class="flex gap-2 items-center">
                                    <input type="date" id="scheduled_delivery_date"
                                        value="{{ $order->scheduled_delivery_at ? $order->scheduled_delivery_at->format('Y-m-d') : '' }}"
                                        class="flex-1 rounded-lg border border-input bg-background px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
                                    <input type="time" id="scheduled_delivery_time"
                                        value="{{ $order->scheduled_delivery_at ? $order->scheduled_delivery_at->format('H:i') : '' }}"
                                        class="flex-1 rounded-lg border border-input bg-background px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
                                </div>
                                <input type="hidden" name="scheduled_delivery_at" id="scheduled_delivery_at"
                                    value="{{ $order->scheduled_delivery_at ? $order->scheduled_delivery_at->format('Y-m-d H:i:s') : '' }}">
                                <p class="text-xs text-muted-foreground">Deixe em branco para remover o agendamento</p>
                            </div>

                            <div class="space-y-3 pt-2">
                                <div class="flex items-center gap-2">
                                    <input type="checkbox" id="create_payment" name="create_payment" value="1"
                                        class="h-4 w-4 text-primary rounded border-input">
                                    <label for="create_payment" class="text-sm font-medium cursor-pointer">Criar nova
                                        cobrança para este pedido</label>
                                </div>

                                <div id="payment_methods" class="hidden space-y-3 pl-6">
                                    <div>
                                        <label class="text-sm font-medium mb-2 block">Método de Pagamento</label>
                                        <select name="payment_method"
                                            class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
                                            <option value="pix">PIX</option>
                                            <option value="credit_card">Cartão de Crédito</option>
                                            <option value="debit_card">Cartão de Débito</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2">
                                    <input type="checkbox" id="send_whatsapp" name="send_whatsapp" value="1" checked
                                        class="h-4 w-4 text-primary rounded border-input">
                                    <label for="send_whatsapp" class="text-sm font-medium cursor-pointer">Enviar notificação
                                        via WhatsApp ao cliente</label>
                                </div>

                                <div id="whatsapp_message" class="space-y-2">
                                    <label class="text-sm font-medium">Mensagem personalizada (opcional)</label>
                                    <textarea name="whatsapp_message" rows="3"
                                        class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
                                        placeholder="Deixe em branco para usar mensagem padrão">Olá! Seu pedido {{ $order->order_number }} foi atualizado. Valor total: R$ {{ number_format($order->final_amount ?? $order->total_amount, 2, ',', '.') }}</textarea>
                                    <p class="text-xs text-muted-foreground">O link de pagamento será adicionado
                                        automaticamente se uma cobrança for criada.</p>
                                </div>

                                <div class="flex items-center gap-2">
                                    <input type="checkbox" id="skip_notification" name="skip_notification" value="1"
                                        class="h-4 w-4 text-primary rounded border-input">
                                    <label for="skip_notification" class="text-sm font-medium cursor-pointer">Salvar sem
                                        enviar notificação ao cliente</label>
                                </div>

                                <button type="submit" class="w-full btn-primary h-10 gap-2">
                                    <i data-lucide="save" class="w-4 h-4"></i>
                                    Salvar Alterações
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Adicionar Item -->
    <div id="add-item-modal"
        class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div class="bg-card rounded-xl shadow-lg w-full max-w-md border border-border overflow-hidden">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold">Adicionar Item ao Pedido</h3>
                    <button type="button" id="btn-close-add-item-modal-header"
                        class="text-muted-foreground hover:text-foreground">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form action="{{ route('dashboard.orders.addItem', $order) }}" method="POST">
                    @csrf

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">Produto *</label>
                            <select id="product-select" name="product_id" required
                                class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
                                <option value="">Selecione um produto</option>
                                @foreach($availableProducts as $product)
                                    <option value="{{ $product->id }}" data-price="{{ $product->price }}">
                                        {{ $product->name }} - R$ {{ number_format($product->price, 2, ',', '.') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div id="normal-item-fields">
                            <div>
                                <label class="block text-sm font-medium mb-2">Quantidade *</label>
                                <input type="number" name="quantity" id="quantity" min="1" value="1"
                                    class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-2">Preço Unitário (opcional)</label>
                                <input type="number" name="unit_price" id="unit_price" step="0.01" min="0"
                                    placeholder="Deixe em branco para usar o preço padrão"
                                    class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
                                <p class="text-xs text-muted-foreground mt-1">Se não preenchido, será usado o preço padrão
                                    do produto</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-2">Nome Personalizado (opcional)</label>
                                <input type="text" name="custom_name" maxlength="255" placeholder="Ex: Focaccia Especial"
                                    class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-2">Observações Especiais (opcional)</label>
                                <textarea name="special_instructions" id="special_instructions" rows="2" maxlength="500"
                                    placeholder="Ex: Com pouco sal, sem azeitona"
                                    class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-3 mt-6">
                        <button type="button" id="btn-close-add-item-modal-footer" class="flex-1 btn-outline h-10">
                            Cancelar
                        </button>
                        <button type="submit" class="flex-1 btn-primary h-10">
                            Adicionar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de Recibo -->
    <div id="receipt-modal"
        class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div class="bg-background rounded-xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col">
            <div class="flex items-center justify-between p-4 border-b border-border">
                <h2 class="text-lg font-semibold">Recibo do Pedido</h2>
                <button type="button" id="btn-close-receipt-modal" class="text-muted-foreground hover:text-foreground">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <div id="receipt-modal-content" class="overflow-y-auto p-6">
                <!-- Conteúdo será carregado via AJAX -->
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/qz-tray@2.2/qz-tray.min.js"></script>
        <script>
            // ============================================
            // FUNÇÕES GLOBAIS
            // ============================================

            // Função para abrir modal de recibo
            function openReceiptModal(orderId) {
                const modal = document.getElementById('receipt-modal');
                const content = document.getElementById('receipt-modal-content');

                if (!modal) {
                    alert('Erro: Modal de recibo não encontrado.');
                    return;
                }

                (async () => {
                    try {
                        modal.classList.remove('hidden');
                        content.innerHTML = '<div class="text-center p-8">Carregando...</div>';

                        @php
                            $receiptUrl = route('dashboard.orders.receipt', ['order' => '__ORDER__']);
                        @endphp
                        const urlTemplate = '{{ $receiptUrl }}';
                        const url = urlTemplate.replace('__ORDER__', orderId);
                        const response = await fetch(url, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'text/html',
                            }
                        });

                        if (!response.ok) {
                            throw new Error('Erro ao carregar recibo: ' + response.status);
                        }

                        const html = await response.text();
                        content.innerHTML = html;
                    } catch (error) {
                        console.error('Erro ao carregar recibo:', error);
                        content.innerHTML = '<div class="text-center p-8 text-red-600">Erro ao carregar recibo. Tente novamente.</div>';
                    }
                })();
            }

            window.openReceiptModal = openReceiptModal;

            // Fechar modal de recibo
            function closeReceiptModal() {
                const modal = document.getElementById('receipt-modal');
                if (modal) {
                    modal.classList.add('hidden');
                }
            }

            window.closeReceiptModal = closeReceiptModal;

            // Função para atualizar quantidade do item via AJAX
            function updateItemQuantity(orderId, itemId, delta) {
                const buttonSelector = delta > 0
                    ? `.btn-increase-quantity[data-item-id="${itemId}"]`
                    : `.btn-decrease-quantity[data-item-id="${itemId}"]`;
                const button = document.querySelector(buttonSelector);

                if (!button) return;

                const itemContainer = button.closest('div[data-item-id]');
                if (!itemContainer) return;

                const quantitySpan = itemContainer.querySelector('.item-quantity');
                const totalPriceSpan = itemContainer.querySelector('.item-total-price');

                if (!quantitySpan) return;

                button.disabled = true;

                (async () => {
                    try {
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
                                itemContainer.remove();
                                const itemsContainer = document.getElementById('items-tbody');
                                if (itemsContainer && itemsContainer.children.length === 0) {
                                    itemsContainer.innerHTML = '<div class="py-4 px-3 text-center text-sm text-muted-foreground">Nenhum item encontrado.</div>';
                                }
                            } else if (data.item) {
                                quantitySpan.textContent = data.item.quantity;
                                if (totalPriceSpan) {
                                    totalPriceSpan.textContent = 'R$ ' + data.item.total_price;
                                }
                            }

                            updateOrderTotals(data.order);
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

            window.updateItemQuantity = updateItemQuantity;

            // Função para remover item via AJAX
            function removeItem(orderId, itemId) {
                if (!confirm('Tem certeza que deseja remover este item completamente do pedido?')) {
                    return;
                }

                const itemContainer = document.querySelector(`div[data-item-id="${itemId}"]`);
                if (!itemContainer) return;

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
                            itemContainer.remove();
                            const itemsContainer = document.getElementById('items-tbody');
                            if (itemsContainer && itemsContainer.children.length === 0) {
                                itemsContainer.innerHTML = '<div class="py-4 px-3 text-center text-sm text-muted-foreground">Nenhum item encontrado.</div>';
                            }

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

            window.removeItem = removeItem;

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

            // Event Listeners
            function setupInteractionHandlers() {
                if (window.__orderEditPageHandlersInitialized__) {
                    return;
                }
                window.__orderEditPageHandlersInitialized__ = true;

                // Botão de abrir recibo
                const btnOpenReceipt = document.getElementById('btn-open-receipt');
                if (btnOpenReceipt) {
                    btnOpenReceipt.addEventListener('click', function () {
                        const orderId = this.getAttribute('data-order-id');
                        if (orderId) {
                            openReceiptModal(parseInt(orderId));
                        }
                    });
                }

                // Botão de fechar recibo
                const btnCloseReceipt = document.getElementById('btn-close-receipt-modal');
                if (btnCloseReceipt) {
                    btnCloseReceipt.addEventListener('click', closeReceiptModal);
                }

                // Fechar modal de recibo ao clicar fora
                const receiptModal = document.getElementById('receipt-modal');
                if (receiptModal) {
                    receiptModal.addEventListener('click', function (e) {
                        if (e.target === this) {
                            closeReceiptModal();
                        }
                    });
                }

                // Botões de aumentar/diminuir quantidade
                const itemsTbody = document.getElementById('items-tbody');
                if (itemsTbody) {
                    itemsTbody.addEventListener('click', function (e) {
                        if (e.target.closest('.btn-decrease-quantity')) {
                            const btn = e.target.closest('.btn-decrease-quantity');
                            const orderId = parseInt(btn.getAttribute('data-order-id'));
                            const itemId = parseInt(btn.getAttribute('data-item-id'));
                            const delta = parseInt(btn.getAttribute('data-delta'));
                            if (orderId && itemId && delta) {
                                updateItemQuantity(orderId, itemId, delta);
                            }
                        }
                        if (e.target.closest('.btn-increase-quantity')) {
                            const btn = e.target.closest('.btn-increase-quantity');
                            const orderId = parseInt(btn.getAttribute('data-order-id'));
                            const itemId = parseInt(btn.getAttribute('data-item-id'));
                            const delta = parseInt(btn.getAttribute('data-delta'));
                            if (orderId && itemId && delta) {
                                updateItemQuantity(orderId, itemId, delta);
                            }
                        }
                        if (e.target.closest('.btn-remove-item')) {
                            const btn = e.target.closest('.btn-remove-item');
                            const orderId = parseInt(btn.getAttribute('data-order-id'));
                            const itemId = parseInt(btn.getAttribute('data-item-id'));
                            if (orderId && itemId) {
                                removeItem(orderId, itemId);
                            }
                        }
                    });
                }

                // Botão de abrir modal de adicionar item
                const btnOpenAddItem = document.getElementById('btn-open-add-item-modal');
                if (btnOpenAddItem) {
                    btnOpenAddItem.addEventListener('click', function () {
                        const modal = document.getElementById('add-item-modal');
                        if (modal) {
                            modal.classList.remove('hidden');
                        }
                    });
                }

                // Botões de fechar modal de adicionar item
                const btnCloseAddItemHeader = document.getElementById('btn-close-add-item-modal-header');
                const btnCloseAddItemFooter = document.getElementById('btn-close-add-item-modal-footer');
                const closeAddItemModal = function () {
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

                // Preencher preço unitário ao selecionar produto
                const productSelect = document.getElementById('product-select');
                if (productSelect) {
                    productSelect.addEventListener('change', function (e) {
                        const option = e.target.options[e.target.selectedIndex];
                        const price = option.dataset.price;
                        const unitPriceInput = document.getElementById('unit_price');
                        if (price && unitPriceInput && !unitPriceInput.value) {
                            unitPriceInput.value = parseFloat(price).toFixed(2);
                        }
                    });
                }

                // Scheduled delivery
                document.getElementById('scheduled_delivery_date')?.addEventListener('change', updateScheduledDelivery);
                document.getElementById('scheduled_delivery_time')?.addEventListener('change', updateScheduledDelivery);

                // Toggle payment methods
                document.getElementById('create_payment')?.addEventListener('change', function (e) {
                    document.getElementById('payment_methods').classList.toggle('hidden', !e.target.checked);
                });
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', setupInteractionHandlers);
            } else {
                setupInteractionHandlers();
            }

            // QZ Tray - Impressão direta
            function isQZTrayConnected() {
                try {
                    return typeof qz !== 'undefined' && qz !== null && qz.websocket !== null && qz.websocket.isActive();
                } catch (error) {
                    return false;
                }
            }

            async function connectQZTray() {
                try {
                    if (typeof qz === 'undefined' || qz === null) {
                        throw new Error('QZ Tray não está carregado.');
                    }

                    if (isQZTrayConnected()) {
                        return true;
                    }

                    await qz.websocket.connect();
                    return isQZTrayConnected();
                } catch (error) {
                    console.error('Erro ao conectar QZ Tray:', error);
                    return false;
                }
            }

            function isMobileDevice() {
                return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ||
                    (window.innerWidth <= 768);
            }

            async function printReceiptDirect(orderId) {
                if (isMobileDevice()) {
                    const btn = document.getElementById('btn-print-receipt-direct');
                    let originalText = '';
                    if (btn) {
                        originalText = btn.innerHTML;
                        btn.innerHTML = '<i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i> Enviando...';
                        btn.disabled = true;
                    }

                    try {
                        const response = await fetch(`/orders/${orderId}/request-print`, {
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
                                btn.innerHTML = '<i data-lucide="check" class="w-4 h-4"></i> Na fila!';
                                setTimeout(() => {
                                    btn.innerHTML = originalText;
                                    btn.disabled = false;
                                }, 2000);
                            }
                            alert('✅ Pedido adicionado à fila de impressão!');
                        } else {
                            throw new Error(data.message || 'Erro ao adicionar à fila');
                        }
                    } catch (error) {
                        console.error('Erro ao solicitar impressão:', error);
                        alert('❌ Erro ao solicitar impressão: ' + (error.message || 'Erro desconhecido'));
                        if (btn && originalText) {
                            btn.innerHTML = originalText;
                            btn.disabled = false;
                        }
                    }
                    return;
                }

                if (typeof qz === 'undefined') {
                    alert('❌ QZ Tray não está carregado.');
                    return;
                }

                if (!isQZTrayConnected()) {
                    const connected = await connectQZTray();
                    if (!connected) {
                        alert('❌ Não foi possível conectar ao QZ Tray.');
                        return;
                    }
                }

                try {
                    const printers = await qz.printers.find();
                    if (!printers || printers.length === 0) {
                        alert('Nenhuma impressora encontrada.');
                        return;
                    }

                    const printer = printers.find(p =>
                        p.toUpperCase().includes('EPSON') &&
                        (p.toUpperCase().includes('TM-20') || p.toUpperCase().includes('TM-T20'))
                    ) || printers[0];

                    const response = await fetch(`/orders/${orderId}/fiscal-receipt/escpos`, {
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

                    const printConfig = qz.configs.create(printer);
                    await qz.print(printConfig, [{
                        type: 'raw',
                        format: 'base64',
                        data: orderData.data
                    }]);

                    const btn = document.getElementById('btn-print-receipt-direct');
                    if (btn) {
                        const originalText = btn.innerHTML;
                        btn.innerHTML = '<i data-lucide="check" class="w-4 h-4"></i> Enviado!';
                        btn.disabled = true;
                        setTimeout(() => {
                            btn.innerHTML = originalText;
                            btn.disabled = false;
                        }, 2000);
                    }
                } catch (error) {
                    console.error('Erro ao imprimir:', error);
                    alert('❌ Erro ao imprimir: ' + (error.message || 'Erro desconhecido'));
                }
            }

            window.printReceiptDirect = printReceiptDirect;

            // Botão de impressão direta
            document.addEventListener('DOMContentLoaded', function () {
                const btnPrint = document.getElementById('btn-print-receipt-direct');
                if (btnPrint) {
                    btnPrint.addEventListener('click', function () {
                        const orderId = this.getAttribute('data-order-id');
                        if (orderId) {
                            printReceiptDirect(orderId);
                        }
                    });
                }

                if (window.lucide) {
                    lucide.createIcons();
                }
            });
        </script>
    @endpush
@endsection