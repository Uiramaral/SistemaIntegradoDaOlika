@extends('layouts.dashboard')

@section('title', 'Visão Geral')

@section('content')
<div class="grid gap-4 grid-cols-2 md:grid-cols-4">
  @include('components.dashboard.card', [
      'title' => 'Receita Hoje',
      'value' => 'R$ ' . number_format($receitaHoje ?? 0, 2, ',', '.'),
      'subtitle' => 'Pedidos pagos no dia',
      'icon' => 'wallet'
  ])
  @include('components.dashboard.card', [
      'title' => 'Pedidos Hoje',
      'value' => $pedidosHoje ?? 0,
      'subtitle' => 'Totais criados nas últimas 24h',
      'icon' => 'shopping-bag'
  ])
  @include('components.dashboard.card', [
      'title' => 'Pagos Hoje',
      'value' => $pagosHoje ?? 0,
      'subtitle' => ($pedidosHoje ?? 0) > 0 ? number_format((($pagosHoje ?? 0) / max($pedidosHoje, 1)) * 100, 1, ',', '.') . '% aprovados' : 'Sem pedidos hoje',
      'icon' => 'check-circle'
  ])
  @include('components.dashboard.card', [
      'title' => 'Pendentes de Pagamento',
      'value' => $pendentesPagamento ?? 0,
      'subtitle' => 'Acompanhe estes pedidos de perto',
      'icon' => 'clock'
  ])
</div>

<div class="grid gap-4 lg:grid-cols-2 mt-6">
  <div class="box card-hover">
    <h3 class="box-title">Pedidos Recentes</h3>
    <p class="box-subtitle">Últimos pedidos criados na plataforma</p>
    @if(isset($recentOrders) && $recentOrders->count())
      <div class="space-y-3 mt-4">
        @foreach($recentOrders->take(5) as $order)
          <a href="{{ route('dashboard.orders.show', $order) }}" class="flex items-center justify-between rounded-lg border border-border/70 bg-background/70 px-4 py-3 text-sm transition hover:border-primary/40 hover:bg-primary/5">
            <div class="min-w-0 flex-1">
              <p class="font-semibold text-foreground truncate">
                #{{ $order->order_number ?? $order->id }} — {{ $order->customer->name ?? 'Cliente' }}
              </p>
              <p class="text-xs text-muted">
                {{ optional($order->created_at)->format('d/m H:i') }} • {{ ucfirst($order->status ?? 'sem status') }}
              </p>
            </div>
            <div class="text-right pl-3 flex-shrink-0">
              <p class="font-semibold text-foreground">
                R$ {{ number_format((float)($order->final_amount ?? 0), 2, ',', '.') }}
              </p>
            </div>
          </a>
        @endforeach
      </div>
    @else
      <div class="flex flex-col items-center justify-center gap-3 py-8 text-center text-muted">
        <span class="flex h-12 w-12 items-center justify-center rounded-full bg-muted overflow-hidden">
          <i class="ph ph-shopping-basket"></i>
        </span>
        <div>
          <p class="font-semibold text-foreground text-sm">Nenhum pedido por aqui</p>
          <p class="text-xs mt-1">Quando os pedidos chegarem, eles aparecerão nesta lista.</p>
        </div>
      </div>
    @endif
  </div>

  <div class="box card-hover">
    <h3 class="box-title">Top Produtos</h3>
    <p class="box-subtitle">Desempenho nos últimos 7 dias</p>
    @if(isset($topProducts) && $topProducts->count())
      <div class="space-y-3 mt-4">
        @foreach($topProducts->take(5) as $item)
          <div class="flex items-center justify-between rounded-lg border border-border/70 bg-background/70 px-4 py-3">
            <div class="flex items-center gap-3 min-w-0 flex-1">
              <span class="flex h-10 w-10 items-center justify-center rounded-full bg-primary/10 text-primary font-semibold flex-shrink-0">
                {{ strtoupper(\Illuminate\Support\Str::substr(optional($item['product'])->name ?? 'P', 0, 1)) }}
              </span>
              <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold text-foreground truncate">
                  {{ optional($item['product'])->name ?? 'Produto removido' }}
                </p>
                <p class="text-xs text-muted">
                  {{ $item['quantity'] }} unidades • R$ {{ number_format((float)$item['revenue'], 2, ',', '.') }}
                </p>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    @else
      <div class="flex flex-col items-center justify-center gap-3 py-8 text-center text-muted">
        <span class="flex h-12 w-12 items-center justify-center rounded-full bg-muted overflow-hidden">
          <i class="ph ph-package"></i>
        </span>
        <div>
          <p class="font-semibold text-foreground text-sm">Ainda sem histórico</p>
          <p class="text-xs mt-1">Conclua vendas para acompanhar os destaques por aqui.</p>
        </div>
      </div>
    @endif
  </div>

  <div class="box card-hover">
    <h3 class="box-title">Pedidos Agendados</h3>
    <p class="box-subtitle">Próximas entregas com horário definido</p>
    @if(isset($nextScheduled) && $nextScheduled->count())
      <div class="space-y-3 mt-4">
        @foreach($nextScheduled->take(5) as $order)
          <div class="flex items-center justify-between rounded-lg border border-border/70 bg-background/70 px-4 py-3 text-sm">
            <div class="min-w-0 flex-1">
              <p class="font-semibold text-foreground truncate">
                #{{ $order->order_number ?? $order->id }} — {{ $order->customer->name ?? 'Cliente' }}
              </p>
              <p class="text-xs text-muted">
                Entrega {{ optional($order->scheduled_delivery_at)->format('d/m H:i') ?? '—' }}
              </p>
            </div>
            <div class="text-right flex-shrink-0 ml-3">
              <p class="font-semibold text-foreground">
                R$ {{ number_format((float)($order->final_amount ?? 0), 2, ',', '.') }}
              </p>
            </div>
          </div>
        @endforeach
      </div>
    @else
      <div class="flex flex-col items-center justify-center gap-3 py-8 text-center text-muted">
        <span class="flex h-12 w-12 items-center justify-center rounded-full bg-muted overflow-hidden">
          <i class="ph ph-calendar-clock"></i>
        </span>
        <div>
          <p class="font-semibold text-foreground text-sm">Nenhum agendamento</p>
          <p class="text-xs mt-1">Assim que um pedido tiver horário marcado, ele aparecerá aqui.</p>
        </div>
      </div>
    @endif
  </div>

  <div class="box card-hover">
    <h3 class="box-title">Status dos Pedidos</h3>
    <p class="box-subtitle">Situação atual da operação</p>
    <div class="grid gap-2 mt-4">
      @php
        $statusMap = [
          'pending' => ['label' => 'Pendentes', 'icon' => 'hourglass', 'color' => 'text-warning'],
          'confirmed' => ['label' => 'Confirmados', 'icon' => 'check-circle', 'color' => 'text-success'],
          'preparing' => ['label' => 'Em preparo', 'icon' => 'cooking-pot', 'color' => 'text-primary'],
          'delivered' => ['label' => 'Entregues', 'icon' => 'truck', 'color' => 'text-muted'],
        ];
      @endphp
      @foreach($statusMap as $key => $meta)
        <div class="flex items-center justify-between rounded-lg border border-border/70 bg-background/70 px-3 py-2.5 text-sm">
          <div class="flex items-center gap-2.5 min-w-0 flex-1">
            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-muted/60 {{ $meta['color'] }} flex-shrink-0 overflow-hidden">
              <i class="ph ph-{{ $meta['icon'] }}"></i>
            </span>
            <span class="font-medium text-foreground truncate">{{ $meta['label'] }}</span>
          </div>
          <span class="text-right font-semibold text-foreground flex-shrink-0 ml-2">
            {{ $statusCount[$key] ?? 0 }}
          </span>
        </div>
      @endforeach
    </div>
  </div>
</div>
@endsection
