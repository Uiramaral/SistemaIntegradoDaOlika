@extends('dashboard.layouts.app')

@section('page_title', 'Visão Geral')
@section('page_subtitle', 'Acompanhe o desempenho do seu negócio em tempo real.')

@section('content')

<div class="grid lg:grid-cols-4 md:grid-cols-2 gap-4 mb-6">
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

<div class="grid gap-4 lg:grid-cols-2">
  <div class="rounded-lg border border-border bg-card p-6 shadow-sm" style="background-color: hsl(var(--card)); border-color: hsl(var(--border));">
    <div class="flex items-center justify-between mb-4">
      <div>
        <h3 class="font-semibold mb-2" style="color: hsl(var(--foreground));">Pedidos Recentes</h3>
        <p class="text-sm mb-4" style="color: hsl(var(--muted-foreground));">Últimos pedidos criados na plataforma</p>
      </div>
      <a href="{{ route('dashboard.orders.index') }}" class="text-sm font-medium" style="color: hsl(var(--primary));">Ver todos</a>
    </div>
    @if(isset($recentOrders) && $recentOrders->count())
      <div class="space-y-3">
        @foreach($recentOrders->take(5) as $order)
          <a href="{{ route('dashboard.orders.show', $order) }}" class="flex items-center justify-between rounded-lg border px-4 py-3 text-sm transition hover:border-primary hover:bg-card" style="border-color: hsl(var(--border)); background-color: hsl(var(--background));">
            <div class="min-w-0 flex-1">
              <p class="font-semibold truncate" style="color: hsl(var(--foreground));">
                #{{ $order->order_number ?? $order->id }} — {{ $order->customer->name ?? 'Cliente' }}
              </p>
              <p class="text-xs text-muted-foreground">
                {{ optional($order->created_at)->format('d/m H:i') }} • {{ ucfirst($order->status ?? 'sem status') }}
              </p>
            </div>
            <div class="text-right pl-3 flex-shrink-0">
              <p class="font-semibold" style="color: hsl(var(--primary));">
                R$ {{ number_format((float)($order->final_amount ?? 0), 2, ',', '.') }}
              </p>
            </div>
          </a>
        @endforeach
      </div>
    @else
      <div class="flex flex-col items-center justify-center gap-3 py-8 text-center text-muted-foreground">
        <i data-lucide="shopping-basket" class="w-12 h-12 text-muted-foreground"></i>
        <div>
          <p class="font-semibold text-sm" style="color: hsl(var(--foreground));">Nenhum pedido por aqui</p>
          <p class="text-xs mt-1" style="color: hsl(var(--muted-foreground));">Quando os pedidos chegarem, eles aparecerão nesta lista.</p>
        </div>
      </div>
    @endif
  </div>

  <div class="rounded-lg border border-border bg-card p-6 shadow-sm" style="background-color: hsl(var(--card)); border-color: hsl(var(--border));">
    <h3 class="font-semibold mb-2" style="color: hsl(var(--foreground));">Top Produtos</h3>
    <p class="text-muted-foreground text-sm mb-4">Desempenho nos últimos 7 dias</p>
    @if(isset($topProducts) && $topProducts->count())
      <div class="space-y-3">
        @foreach($topProducts->take(5) as $item)
          <div class="flex items-center justify-between rounded-lg border px-4 py-3" style="border-color: hsl(var(--border)); background-color: hsl(var(--background));">
            <div class="flex items-center gap-3 min-w-0 flex-1">
              <span class="flex h-10 w-10 items-center justify-center rounded-full font-semibold flex-shrink-0" style="background-color: hsl(var(--primary) / 0.1); color: hsl(var(--primary));">
                {{ strtoupper(\Illuminate\Support\Str::substr(optional($item['product'])->name ?? 'P', 0, 1)) }}
              </span>
              <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold truncate" style="color: hsl(var(--foreground));">
                  {{ optional($item['product'])->name ?? 'Produto removido' }}
                </p>
                <p class="text-xs text-muted-foreground">
                  {{ $item['quantity'] }} unidades • R$ {{ number_format((float)$item['revenue'], 2, ',', '.') }}
                </p>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    @else
      <div class="flex flex-col items-center justify-center gap-3 py-8 text-center text-muted-foreground">
        <i data-lucide="package" class="w-12 h-12 text-muted-foreground"></i>
        <div>
          <p class="font-semibold text-sm" style="color: hsl(var(--foreground));">Ainda sem histórico</p>
          <p class="text-xs mt-1" style="color: hsl(var(--muted-foreground));">Conclua vendas para acompanhar os destaques por aqui.</p>
        </div>
      </div>
    @endif
  </div>

  <div class="rounded-lg border border-border bg-card p-6 shadow-sm" style="background-color: hsl(var(--card)); border-color: hsl(var(--border));">
    <h3 class="font-semibold mb-2" style="color: hsl(var(--foreground));">Pedidos Agendados</h3>
    <p class="text-muted-foreground text-sm mb-4">Próximas entregas com horário definido</p>
    @if(isset($nextScheduled) && $nextScheduled->count())
      <div class="space-y-3">
        @foreach($nextScheduled->take(5) as $order)
          <div class="flex items-center justify-between rounded-lg border px-4 py-3 text-sm" style="border-color: hsl(var(--border)); background-color: hsl(var(--background));">
            <div class="min-w-0 flex-1">
              <p class="font-semibold truncate" style="color: hsl(var(--foreground));">
                #{{ $order->order_number ?? $order->id }} — {{ $order->customer->name ?? 'Cliente' }}
              </p>
              <p class="text-xs text-muted-foreground">
                Entrega {{ optional($order->scheduled_delivery_at)->format('d/m H:i') ?? '—' }}
              </p>
            </div>
            <div class="text-right flex-shrink-0 ml-3">
              <p class="font-semibold" style="color: hsl(var(--foreground));">
                R$ {{ number_format((float)($order->final_amount ?? 0), 2, ',', '.') }}
              </p>
            </div>
          </div>
        @endforeach
      </div>
    @else
      <div class="flex flex-col items-center justify-center gap-3 py-8 text-center text-muted-foreground">
        <i data-lucide="calendar-clock" class="w-12 h-12 text-muted-foreground"></i>
        <div>
          <p class="font-semibold text-sm" style="color: hsl(var(--foreground));">Nenhum agendamento</p>
          <p class="text-xs mt-1" style="color: hsl(var(--muted-foreground));">Assim que um pedido tiver horário marcado, ele aparecerá aqui.</p>
        </div>
      </div>
    @endif
  </div>

  <div class="rounded-lg border border-border bg-card p-6 shadow-sm" style="background-color: hsl(var(--card)); border-color: hsl(var(--border));">
    <h3 class="font-semibold mb-2" style="color: hsl(var(--foreground));">Status dos Pedidos</h3>
    <p class="text-muted-foreground text-sm mb-4">Situação atual da operação</p>
    <div class="grid gap-2">
      @php
        $statusMap = [
          'pending' => ['label' => 'Pendentes', 'icon' => 'hourglass', 'color' => '#b58a00'],
          'confirmed' => ['label' => 'Confirmados', 'icon' => 'check-circle', 'color' => '#2b8a5b'],
          'preparing' => ['label' => 'Em preparo', 'icon' => 'cooking-pot', 'color' => 'var(--primary)'],
          'delivered' => ['label' => 'Entregues', 'icon' => 'truck', 'color' => 'var(--muted)'],
        ];
      @endphp
      @foreach($statusMap as $key => $meta)
        <div class="flex items-center justify-between rounded-lg border px-3 py-2.5 text-sm" style="border-color: hsl(var(--border)); background-color: hsl(var(--background));">
          <div class="flex items-center gap-2.5 min-w-0 flex-1">
            <span class="flex h-8 w-8 items-center justify-center rounded-full" style="background: {{ $meta['color'] }}20; color: {{ $meta['color'] }}">
              <i data-lucide="{{ $meta['icon'] }}" class="w-4 h-4"></i>
            </span>
            <span class="font-medium truncate" style="color: hsl(var(--foreground));">{{ $meta['label'] }}</span>
          </div>
          <span class="text-right font-semibold flex-shrink-0 ml-2" style="color: hsl(var(--foreground));">
            {{ $statusCount[$key] ?? 0 }}
          </span>
        </div>
      @endforeach
    </div>
  </div>
</div>
@endsection
