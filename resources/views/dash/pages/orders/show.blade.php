@extends('dash.layouts.app')

@section('title', 'Detalhes do Pedido - OLIKA Painel')

@section('content')
<div class="space-y-6 animate-in fade-in duration-500">
    <!-- Header com ações -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <div class="flex items-center gap-3">
                <a href="{{ route('dashboard.orders.index') }}" class="inline-flex items-center justify-center rounded-md p-2 hover:bg-accent hover:text-accent-foreground">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-left h-5 w-5">
                        <path d="m12 19-7-7 7-7"></path>
                        <path d="M19 12H5"></path>
                    </svg>
                </a>
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Pedido #{{ $order->order_number }}</h1>
                    <p class="text-muted-foreground">Detalhes e gestão do pedido</p>
                </div>
            </div>
        </div>
        <div class="flex gap-2">
            <button type="button" id="btn-open-receipt" data-order-id="{{ $order->id }}" class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 px-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-receipt">
                    <path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2Z"></path>
                    <path d="M14 8H8"></path>
                    <path d="M16 12H8"></path>
                    <path d="M13 16H8"></path>
                    <path d="M18 8a2 2 0 0 0 0 4"></path>
                </svg>
                Ver Recibo
            </button>
            @php
                $statusColors = [
                    'pending' => 'bg-muted text-muted-foreground',
                    'confirmed' => 'bg-primary text-primary-foreground',
                    'preparing' => 'bg-warning text-warning-foreground',
                    'ready' => 'bg-primary/80 text-primary-foreground',
                    'delivered' => 'bg-success text-success-foreground',
                    'cancelled' => 'bg-destructive text-destructive-foreground',
                ];
                $statusColor = $statusColors[$order->status] ?? 'bg-muted text-muted-foreground';
            @endphp
            <div class="inline-flex items-center rounded-full border px-3 py-1.5 text-sm font-semibold {{ $statusColor }}">
                {{ $order->status_label }}
            </div>
        </div>
    </div>

    <!-- Resumo do Pedido - Itens do Pedido -->
    <div class="rounded-lg border bg-card text-card-foreground shadow-sm" id="order-items-section">
        <div class="flex flex-col space-y-1.5 p-6">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold leading-none tracking-tight">Resumo do Pedido</h3>
                <button type="button" id="btn-open-add-item-modal" class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-9 px-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-plus">
                        <path d="M5 12h14"></path>
                        <path d="M12 5v14"></path>
                    </svg>
                    Adicionar Item
                </button>
            </div>
        </div>
        <div class="p-6 pt-0">
            <div class="overflow-x-auto">
                <table class="w-full caption-bottom text-sm">
                    <thead class="[&_tr]:border-b">
                        <tr class="border-b transition-colors hover:bg-muted/50">
                            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Produto</th>
                            <th class="h-12 px-4 text-center align-middle font-medium text-muted-foreground">Qtd</th>
                            <th class="h-12 px-4 text-right align-middle font-medium text-muted-foreground">Preço Unit.</th>
                            <th class="h-12 px-4 text-right align-middle font-medium text-muted-foreground">Total</th>
                            <th class="h-12 px-4 text-center align-middle font-medium text-muted-foreground">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="[&_tr:last-child]:border-0" id="items-tbody">
                        @forelse($order->items as $item)
                            <tr class="border-b transition-colors hover:bg-muted/50" data-item-id="{{ $item->id }}">
                                <td class="p-4 align-middle">
                                    <div>
                                        <p class="font-medium item-name">
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
                                        @if($item->special_instructions)
                                            <p class="text-xs text-muted-foreground item-instructions">Obs: {{ $item->special_instructions }}</p>
                                        @endif
                                    </div>
                                </td>
                                <td class="p-4 align-middle text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button type="button" class="btn-decrease-quantity inline-flex items-center justify-center rounded-md p-1.5 hover:bg-accent hover:text-accent-foreground text-muted-foreground" data-order-id="{{ $order->id }}" data-item-id="{{ $item->id }}" data-delta="-1" title="Reduzir quantidade">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-minus">
                                                <path d="M5 12h14"></path>
                                            </svg>
                                        </button>
                                        <span class="font-semibold min-w-[2rem] text-center item-quantity">{{ $item->quantity }}</span>
                                        <button type="button" class="btn-increase-quantity inline-flex items-center justify-center rounded-md p-1.5 hover:bg-accent hover:text-accent-foreground text-muted-foreground" data-order-id="{{ $order->id }}" data-item-id="{{ $item->id }}" data-delta="1" title="Aumentar quantidade">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-plus">
                                                <path d="M5 12h14"></path>
                                                <path d="M12 5v14"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                                <td class="p-4 align-middle text-right item-unit-price">R$ {{ number_format($item->unit_price, 2, ',', '.') }}</td>
                                <td class="p-4 align-middle text-right font-semibold item-total-price">R$ {{ number_format($item->total_price, 2, ',', '.') }}</td>
                                <td class="p-4 align-middle text-center">
                                    <button type="button" class="btn-remove-item inline-flex items-center justify-center rounded-md p-2 hover:bg-destructive/10 hover:text-destructive text-muted-foreground" data-order-id="{{ $order->id }}" data-item-id="{{ $item->id }}" title="Remover item">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trash-2">
                                            <path d="M3 6h18"></path>
                                            <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path>
                                            <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path>
                                            <line x1="10" x2="10" y1="11" y2="17"></line>
                                            <line x1="14" x2="14" y1="11" y2="17"></line>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-8 text-center text-muted-foreground">Nenhum item encontrado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot id="order-totals">
                        <tr class="border-t font-semibold">
                            <td colspan="4" class="p-4 text-right">Subtotal:</td>
                            <td class="p-4 text-right" id="subtotal">R$ {{ number_format($order->total_amount, 2, ',', '.') }}</td>
                        </tr>
                        @if($order->delivery_fee > 0)
                        <tr>
                            <td colspan="4" class="p-4 text-right text-muted-foreground">Taxa de Entrega:</td>
                            <td class="p-4 text-right" id="delivery-fee">R$ {{ number_format($order->delivery_fee, 2, ',', '.') }}</td>
                        </tr>
                        @endif
                        @if($order->discount_amount > 0)
                        <tr>
                            <td colspan="4" class="p-4 text-right text-muted-foreground">
                                <div class="flex items-center justify-end gap-2">
                                    <span>
                                        @if($order->coupon_code)
                                            @php
                                                $coupon = \App\Models\Coupon::where('code', $order->coupon_code)->first();
                                                if ($coupon) {
                                                    $couponValue = $coupon->type === 'percentage' 
                                                        ? number_format($coupon->value, 1) . '%' 
                                                        : 'R$ ' . number_format($coupon->value, 2, ',', '.');
                                                } else {
                                                    // Tentar usar discount_original_value se disponível
                                                    $couponValue = $order->discount_original_value 
                                                        ? number_format($order->discount_original_value, 1) . '%' 
                                                        : '';
                                                }
                                            @endphp
                                            Cupom {{ $order->coupon_code }} {{ $couponValue }}
                                        @elseif($order->manual_discount_type)
                                            @php
                                                $discountValue = $order->manual_discount_type === 'percentage' 
                                                    ? number_format($order->manual_discount_value, 1) . '%' 
                                                    : 'R$ ' . number_format($order->manual_discount_value, 2, ',', '.');
                                            @endphp
                                            Desconto {{ $discountValue }}
                                        @else
                                            Desconto
                                        @endif
                                    </span>
                                    @if($order->coupon_code)
                                        <form action="{{ route('dashboard.orders.removeCoupon', $order->id) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" onclick="return confirm('Deseja realmente remover o cupom?');" class="inline-flex items-center justify-center rounded-md text-xs font-medium border border-input bg-background hover:bg-accent hover:text-accent-foreground h-7 px-2" title="Remover cupom">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x h-3 w-3">
                                                    <path d="M18 6 6 18"></path>
                                                    <path d="M6 6l12 12"></path>
                                                </svg>
                                            </button>
                                        </form>
                                    @elseif($order->manual_discount_type && $order->manual_discount_value)
                                        <form action="{{ route('dashboard.orders.removeDiscount', $order->id) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" onclick="return confirm('Deseja realmente remover o desconto manual?');" class="inline-flex items-center justify-center rounded-md text-xs font-medium border border-input bg-background hover:bg-accent hover:text-accent-foreground h-7 px-2" title="Remover desconto">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x h-3 w-3">
                                                    <path d="M18 6 6 18"></path>
                                                    <path d="M6 6l12 12"></path>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                            <td class="p-4 text-right text-success" id="discount-amount">- R$ {{ number_format($order->discount_amount, 2, ',', '.') }}</td>
                        </tr>
                        @endif
                        <tr class="border-t font-bold text-lg">
                            <td colspan="4" class="p-4 text-right">Total:</td>
                            <td class="p-4 text-right" id="final-total">R$ {{ number_format($order->final_amount, 2, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Grid de informações principais -->
    <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
        <!-- Informações do Cliente -->
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="flex flex-col space-y-1.5 p-6">
                <h3 class="text-lg font-semibold leading-none tracking-tight">Cliente</h3>
            </div>
            <div class="p-6 pt-0 space-y-3">
                <div>
                    <p class="text-sm font-medium text-muted-foreground">Nome</p>
                    <p class="text-base font-semibold">{{ $order->customer->name ?? 'Não informado' }}</p>
                </div>
                @if($order->customer->phone ?? null)
                <div>
                    <p class="text-sm font-medium text-muted-foreground">Telefone</p>
                    <p class="text-base">{{ $order->customer->phone }}</p>
                </div>
                @endif
                @if($order->customer->email ?? null)
                <div>
                    <p class="text-sm font-medium text-muted-foreground">Email</p>
                    <p class="text-base">{{ $order->customer->email }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Informações de Entrega -->
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="flex flex-col space-y-1.5 p-6">
                <h3 class="text-lg font-semibold leading-none tracking-tight">Entrega</h3>
            </div>
            <div class="p-6 pt-0 space-y-3">
                <div>
                    <p class="text-sm font-medium text-muted-foreground">Tipo</p>
                    <p class="text-base font-semibold">{{ $order->delivery_type_label }}</p>
                </div>
                @if($order->delivery_address)
                <div>
                    <p class="text-sm font-medium text-muted-foreground">Endereço</p>
                    <p class="text-base">{{ $order->delivery_address }}</p>
                </div>
                @endif
                @if($order->delivery_instructions)
                <div>
                    <p class="text-sm font-medium text-muted-foreground">Instruções</p>
                    <p class="text-base">{{ $order->delivery_instructions }}</p>
                </div>
                @endif
                @if($order->estimated_time)
                <div>
                    <p class="text-sm font-medium text-muted-foreground">Tempo Estimado</p>
                    <p class="text-base">{{ $order->estimated_time }} minutos</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Informações de Pagamento -->
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="flex flex-col space-y-1.5 p-6">
                <h3 class="text-lg font-semibold leading-none tracking-tight">Pagamento</h3>
            </div>
            <div class="p-6 pt-0 space-y-3">
                <div>
                    <p class="text-sm font-medium text-muted-foreground">Método</p>
                    <p class="text-base font-semibold">{{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-muted-foreground">Status</p>
                    @php
                        $paymentColors = [
                            'pending' => 'bg-muted text-muted-foreground',
                            'paid' => 'bg-success text-success-foreground',
                            'failed' => 'bg-destructive text-destructive-foreground',
                            'refunded' => 'bg-warning text-warning-foreground',
                        ];
                        $paymentColor = $paymentColors[$order->payment_status] ?? 'bg-muted text-muted-foreground';
                    @endphp
                    <div class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold mt-1 {{ $paymentColor }}">
                        {{ $order->payment_status_label }}
                    </div>
                </div>
                @if($order->payment_provider)
        <div>
                    <p class="text-sm font-medium text-muted-foreground">Provedor</p>
                    <p class="text-base">{{ $order->payment_provider }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Cards de Ações -->
    <style>
        .action-cards-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
            align-items: stretch;
        }
        .action-cards-grid > div {
            display: flex;
            flex-direction: column;
            min-height: 100%;
        }
        .action-cards-grid > div > div:last-child {
            flex: 1 1 auto;
            display: flex;
            flex-direction: column;
            min-height: 0;
        }
        .action-cards-grid form {
            flex: 1 1 auto;
            display: flex;
            flex-direction: column;
            min-height: 0;
        }
        .action-cards-grid .button-container {
            margin-top: auto;
            padding-top: 1rem;
            min-height: 3.5rem;
            display: flex;
            align-items: flex-end;
            box-sizing: border-box;
            flex-shrink: 0;
        }
        /* Garantir que os botões tenham a mesma altura e fiquem na base */
        .action-cards-grid .button-container button {
            width: 100%;
            height: 2.5rem;
            box-sizing: border-box;
        }
        @media (max-width: 768px) {
            .action-cards-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
    <div class="action-cards-grid">
        <!-- Alterar Status -->
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm flex flex-col">
            <div class="flex flex-col space-y-1.5 p-6">
                <h3 class="text-lg font-semibold leading-none tracking-tight">Alterar Status</h3>
                <p class="text-sm text-muted-foreground">Atualize o status do pedido</p>
            </div>
            <div class="p-6 pt-0 flex flex-col flex-grow">
                <form action="{{ route('dashboard.orders.updateStatus', $order->id) }}" method="POST" class="space-y-4 flex flex-col flex-grow">
                    @csrf
                    <div class="space-y-2">
                        <label class="text-sm font-medium">Status</label>
                        <select name="status" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
                            @foreach($availableStatuses as $status)
                                <option value="{{ $status->code }}" @selected($order->status === $status->code)>
                                    {{ $status->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium">Observação (opcional)</label>
                        <textarea name="note" rows="3" class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm" placeholder="Adicione uma observação sobre esta mudança..."></textarea>
                    </div>
                    <div class="button-container">
                        <button type="submit" class="w-full inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2">
                            Atualizar Status
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Aplicar Cupom -->
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm flex flex-col">
            <div class="flex flex-col space-y-1.5 p-6">
                <h3 class="text-lg font-semibold leading-none tracking-tight">Cupom de Desconto</h3>
                <p class="text-sm text-muted-foreground">Aplique ou remova cupons</p>
            </div>
            <div class="p-6 pt-0 space-y-4 flex flex-col flex-grow">
                @if($order->coupon_code)
                    <div class="rounded-lg border p-4 bg-muted">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-semibold">{{ $order->coupon_code }}</p>
                                <p class="text-sm text-muted-foreground">Desconto: R$ {{ number_format($order->discount_amount, 2, ',', '.') }}</p>
                            </div>
                            <form action="{{ route('dashboard.orders.removeCoupon', $order->id) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center justify-center rounded-md text-sm font-medium border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 px-3">
                                    Remover
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <form action="{{ route('dashboard.orders.applyCoupon', $order->id) }}" method="POST" class="space-y-4 flex flex-col flex-grow">
                        @csrf
                        @if($availableCoupons->count() > 0)
                            <div class="space-y-2">
                                <p class="text-sm font-medium">Cupons Disponíveis:</p>
                                <div class="max-h-40 overflow-y-auto space-y-2">
                                    @foreach($availableCoupons as $coupon)
                                        <div class="rounded-md border p-2 text-sm flex items-center justify-between gap-2">
                                            <div class="flex-1">
                                                <p class="font-medium">{{ $coupon->code }} - {{ $coupon->name }}</p>
                                                <p class="text-xs text-muted-foreground">{{ $coupon->formatted_value }}</p>
                                            </div>
                                            <form action="{{ route('dashboard.orders.applyCoupon', $order->id) }}" method="POST" class="inline">
                                                @csrf
                                                <input type="hidden" name="coupon_code" value="{{ $coupon->code }}">
                                                <button type="submit" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-xs font-medium bg-primary text-primary-foreground hover:bg-primary/90 h-7 px-2">
                                                    Aplicar
                                                </button>
                                            </form>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        <div class="space-y-2">
                            <label class="text-sm font-medium">Código do Cupom</label>
                            <div class="flex gap-2">
                                <input type="text" name="coupon_code" id="coupon_code_input" class="flex-1 h-10 rounded-md border border-input bg-background px-3 py-2 text-sm" placeholder="Digite o código do cupom">
                                <button type="submit" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-xs font-medium bg-primary text-primary-foreground hover:bg-primary/90 h-7 px-2">
                                    Aplicar
                                </button>
                            </div>
                        </div>
                    </form>
                @endif
            </div>
        </div>

        <!-- Ajustar Taxa de Entrega -->
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm flex flex-col">
            <div class="flex flex-col space-y-1.5 p-6">
                <h3 class="text-lg font-semibold leading-none tracking-tight">Taxa de Entrega</h3>
                <p class="text-sm text-muted-foreground">Ajuste manual da taxa de entrega</p>
            </div>
            <div class="p-6 pt-0 flex flex-col flex-grow">
                <form action="{{ route('dashboard.orders.adjustDeliveryFee', $order->id) }}" method="POST" class="space-y-4 flex flex-col flex-grow">
                    @csrf
                    <div class="rounded-lg border p-4 bg-muted">
                        <p class="text-sm text-muted-foreground mb-1">Taxa Atual</p>
                        <p class="text-lg font-semibold">R$ {{ number_format($order->delivery_fee, 2, ',', '.') }}</p>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium">Nova Taxa</label>
                        <input type="number" name="delivery_fee" step="0.01" min="0" value="{{ $order->delivery_fee }}" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm" required>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium">Motivo do Ajuste (opcional)</label>
                        <input type="text" name="reason" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm" placeholder="Ex: Desconto especial, erro no cálculo...">
                    </div>
                    <div class="button-container">
                        <button type="submit" class="w-full inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2">
                            Atualizar Taxa
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Aplicar Desconto Manual -->
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm flex flex-col">
            <div class="flex flex-col space-y-1.5 p-6">
                <h3 class="text-lg font-semibold leading-none tracking-tight">Desconto Manual</h3>
                <p class="text-sm text-muted-foreground">Aplique um desconto adicional</p>
            </div>
            <div class="p-6 pt-0 flex flex-col flex-grow">
                <form action="{{ route('dashboard.orders.applyDiscount', $order->id) }}" method="POST" class="space-y-4 flex flex-col flex-grow">
                    @csrf
                    <div class="space-y-2">
                        <label class="text-sm font-medium">Tipo de Desconto</label>
                        <select name="discount_type" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
                            <option value="percentage">Porcentagem (%)</option>
                            <option value="fixed">Valor Fixo (R$)</option>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium">Valor</label>
                        <input type="number" name="discount_value" step="0.01" min="0" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm" placeholder="Ex: 10 ou 10.50" required>
                    </div>
                    <div class="button-container">
                        <button type="submit" class="w-full inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2">
                            Aplicar Desconto
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Editar Informações -->
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm md:col-span-2">
            <div class="flex flex-col space-y-1.5 p-6">
                <h3 class="text-lg font-semibold leading-none tracking-tight">Editar Informações do Pedido</h3>
                <p class="text-sm text-muted-foreground">Atualize observações e instruções</p>
            </div>
            <div class="p-6 pt-0">
                <form action="{{ route('dashboard.orders.update', $order->id) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
                    <div class="space-y-2">
                        <label class="text-sm font-medium">Observações Internas</label>
                        <textarea name="notes" rows="3" class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm" placeholder="Observações visíveis apenas para a equipe...">{{ $order->notes }}</textarea>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium">Observações do Cliente</label>
                        <textarea name="observations" rows="3" class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm" placeholder="Observações do cliente...">{{ $order->observations }}</textarea>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium">Instruções de Entrega</label>
                        <textarea name="delivery_instructions" rows="3" class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm" placeholder="Instruções especiais para entrega...">{{ $order->delivery_instructions }}</textarea>
                    </div>
                    
                    <div class="space-y-4 pt-4 border-t">
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
                    </div>
                    
                    <div class="pt-4">
                        <button type="submit" class="w-full inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2">
                            Salvar Alterações e Notificar Cliente
                        </button>
                    </div>
        </form>
            
            <script>
                document.getElementById('create_payment')?.addEventListener('change', function(e) {
                    document.getElementById('payment_methods').classList.toggle('hidden', !e.target.checked);
                });
            </script>
            </div>
        </div>
    </div>

    <!-- Histórico de Status -->
    @if($statusHistory->count() > 0)
    <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
        <div class="flex flex-col space-y-1.5 p-6">
            <h3 class="text-lg font-semibold leading-none tracking-tight">Histórico de Status</h3>
            <p class="text-sm text-muted-foreground">Linha do tempo de alterações</p>
        </div>
        <div class="p-6 pt-0">
            <div class="space-y-4">
                @foreach($statusHistory as $history)
                    <div class="flex gap-4">
                        <div class="flex flex-col items-center">
                            <div class="h-8 w-8 rounded-full bg-primary/10 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary">
                                    <circle cx="12" cy="12" r="10"></circle>
                                </svg>
                            </div>
                            @if(!$loop->last)
                                <div class="w-0.5 h-full bg-border mt-2"></div>
                            @endif
                        </div>
                        <div class="flex-1 pb-4">
                            <div class="flex items-center gap-2">
                                <p class="font-semibold">
                                    @if($history->old_status)
                                        {{ ucfirst(str_replace('_', ' ', $history->old_status)) }} → {{ ucfirst(str_replace('_', ' ', $history->new_status)) }}
                                    @else
                                        {{ ucfirst(str_replace('_', ' ', $history->new_status)) }}
                                    @endif
                                </p>
                            </div>
                            @if($history->note)
                                <p class="text-sm text-muted-foreground mt-1">{{ $history->note }}</p>
                            @endif
                            <p class="text-xs text-muted-foreground mt-1">{{ \Carbon\Carbon::parse($history->created_at)->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
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
            
            const quantitySpan = button.parentElement.querySelector('.item-quantity');
            const row = button.closest('tr[data-item-id]');
            
            if (!quantitySpan || !row) {
                console.error('Elementos relacionados não encontrados');
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
                        if (data.removed && data.item_id) {
                            // Remover linha da tabela
                            row.remove();
                            // Verificar se não há mais itens
                            const tbody = document.getElementById('items-tbody');
                            if (tbody.children.length === 0 || (tbody.children.length === 1 && tbody.children[0].querySelector('td[colspan]'))) {
                                tbody.innerHTML = '<tr><td colspan="5" class="p-8 text-center text-muted-foreground">Nenhum item encontrado.</td></tr>';
                            }
                        } else if (data.item) {
                            // Atualizar quantidade e total do item
                            quantitySpan.textContent = data.item.quantity;
                            row.querySelector('.item-total-price').textContent = 'R$ ' + data.item.total_price;
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

            const row = document.querySelector(`tr[data-item-id="${itemId}"]`);
            if (!row) return;

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
                        // Remover linha da tabela
                        row.remove();
                        // Verificar se não há mais itens
                        const tbody = document.getElementById('items-tbody');
                        if (tbody.children.length === 0 || (tbody.children.length === 1 && tbody.children[0].querySelector('td[colspan]'))) {
                            tbody.innerHTML = '<tr><td colspan="5" class="p-8 text-center text-muted-foreground">Nenhum item encontrado.</td></tr>';
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
                if (fee > 0) {
                    deliveryFeeEl.textContent = 'R$ ' + orderData.delivery_fee;
                    const feeRow = deliveryFeeEl.closest('tr');
                    if (feeRow) feeRow.style.display = '';
                } else {
                    const feeRow = deliveryFeeEl.closest('tr');
                    if (feeRow) feeRow.style.display = 'none';
                }
            }
            if (discountAmountEl) {
                const discount = parseFloat(orderData.discount_amount.replace(',', '.').replace('- R$ ', '').replace('R$ ', ''));
                if (discount > 0) {
                    discountAmountEl.textContent = '- R$ ' + orderData.discount_amount;
                    const discountRow = discountAmountEl.closest('tr');
                    if (discountRow) discountRow.style.display = '';
                } else {
                    const discountRow = discountAmountEl.closest('tr');
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
@endsection
