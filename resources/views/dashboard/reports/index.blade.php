@extends('dashboard.layouts.app')

@section('title', 'Relatórios - OLIKA Dashboard')

@section('content')
<div class="space-y-6 animate-in fade-in duration-500">
  <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
    <div>
      <h1 class="text-3xl font-bold tracking-tight">Relatórios</h1>
      <p class="text-muted-foreground">Analise o desempenho do seu negócio</p>
    </div>
    <form method="GET" action="{{ route('dashboard.reports') }}" class="flex items-center gap-2">
      <input type="date" name="start_date" value="{{ $startDate->format('Y-m-d') }}" class="flex h-10 rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
      <span class="text-muted-foreground">até</span>
      <input type="date" name="end_date" value="{{ $endDate->format('Y-m-d') }}" class="flex h-10 rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
      <button type="submit" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2 gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-calendar h-4 w-4"><path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/></svg>
        Filtrar
      </button>
    </form>
  </div>

  <!-- Métricas de Analytics -->
  <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
    <div class="flex flex-col space-y-1.5 p-6">
      <h3 class="font-semibold tracking-tight text-lg">Métricas de Tráfego e Conversão</h3>
      <p class="text-sm text-muted-foreground">Análise de comportamento dos visitantes</p>
    </div>
    <div class="p-6 pt-0">
      <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-lg border bg-muted/50 p-4">
          <div class="flex items-center justify-between mb-2">
            <h4 class="text-sm font-medium text-muted-foreground">Visitas Únicas</h4>
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-eye h-4 w-4 text-muted-foreground"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
          </div>
          <div class="text-2xl font-bold">{{ number_format($pageViews ?? 0, 0, ',', '.') }}</div>
          <p class="text-xs text-muted-foreground mb-1">1 sessão por dia = 1 visita</p>
          @if(isset($pageViewsChange))
            <p class="text-xs {{ $pageViewsChange >= 0 ? 'text-green-600' : 'text-red-600' }}">
              {{ $pageViewsChange >= 0 ? '+' : '' }}{{ number_format($pageViewsChange, 1, ',', '.') }}%
            </p>
          @endif
        </div>

        <div class="rounded-lg border bg-muted/50 p-4">
          <div class="flex items-center justify-between mb-2">
            <h4 class="text-sm font-medium text-muted-foreground">Adições ao Carrinho</h4>
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-shopping-cart h-4 w-4 text-muted-foreground"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
          </div>
          <div class="text-2xl font-bold">{{ number_format($addToCartEvents ?? 0, 0, ',', '.') }}</div>
          @if(isset($addToCartChange))
            <p class="text-xs {{ $addToCartChange >= 0 ? 'text-green-600' : 'text-red-600' }}">
              {{ $addToCartChange >= 0 ? '+' : '' }}{{ number_format($addToCartChange, 1, ',', '.') }}%
            </p>
          @endif
        </div>

        <div class="rounded-lg border bg-muted/50 p-4">
          <div class="flex items-center justify-between mb-2">
            <h4 class="text-sm font-medium text-muted-foreground">Checkouts Iniciados</h4>
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-right h-4 w-4 text-muted-foreground"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
          </div>
          <div class="text-2xl font-bold">{{ number_format($checkoutStarted ?? 0, 0, ',', '.') }}</div>
          @if(isset($checkoutStartedChange))
            <p class="text-xs {{ $checkoutStartedChange >= 0 ? 'text-green-600' : 'text-red-600' }}">
              {{ $checkoutStartedChange >= 0 ? '+' : '' }}{{ number_format($checkoutStartedChange, 1, ',', '.') }}%
            </p>
          @endif
        </div>

        <div class="rounded-lg border bg-muted/50 p-4">
          <div class="flex items-center justify-between mb-2">
            <h4 class="text-sm font-medium text-muted-foreground">Compras Realizadas</h4>
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check-circle h-4 w-4 text-muted-foreground"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
          </div>
          <div class="text-2xl font-bold">{{ number_format($purchases ?? 0, 0, ',', '.') }}</div>
          @if(isset($purchasesChange))
            <p class="text-xs {{ $purchasesChange >= 0 ? 'text-green-600' : 'text-red-600' }}">
              {{ $purchasesChange >= 0 ? '+' : '' }}{{ number_format($purchasesChange, 1, ',', '.') }}%
            </p>
          @endif
        </div>
      </div>

      <div class="grid gap-4 md:grid-cols-3 mt-4">
        <div class="rounded-lg border bg-muted/50 p-4">
          <h4 class="text-sm font-medium text-muted-foreground mb-2">Taxa de Conversão</h4>
          <div class="text-2xl font-bold">{{ number_format($conversionRate ?? 0, 2, ',', '.') }}%</div>
          @if(isset($conversionRateChange))
            <p class="text-xs {{ $conversionRateChange >= 0 ? 'text-green-600' : 'text-red-600' }}">
              {{ $conversionRateChange >= 0 ? '+' : '' }}{{ number_format($conversionRateChange, 1, ',', '.') }}%
            </p>
          @endif
        </div>

        <div class="rounded-lg border bg-muted/50 p-4">
          <h4 class="text-sm font-medium text-muted-foreground mb-2">Abandono de Carrinho</h4>
          <div class="text-2xl font-bold">{{ number_format($cartAbandonment ?? 0, 2, ',', '.') }}%</div>
          <p class="text-xs text-muted-foreground">Não iniciaram checkout</p>
        </div>

        <div class="rounded-lg border bg-muted/50 p-4">
          <h4 class="text-sm font-medium text-muted-foreground mb-2">Taxa de Conclusão</h4>
          <div class="text-2xl font-bold">{{ number_format($checkoutCompletionRate ?? 0, 2, ',', '.') }}%</div>
          <p class="text-xs text-muted-foreground">Finalizaram após iniciar</p>
        </div>
      </div>
    </div>
  </div>

  <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
    <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
      <div class="p-6 flex items-center justify-between pb-2">
        <h3 class="text-sm font-medium text-muted-foreground">Receita Total</h3>
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-dollar-sign h-4 w-4 text-muted-foreground"><line x1="12" x2="12" y1="2" y2="22"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
      </div>
      <div class="p-6 pt-0">
        <div class="text-2xl font-bold">R$ {{ number_format($totalAmount ?? 0, 2, ',', '.') }}</div>
        @if(isset($revenueChange))
          <p class="text-xs {{ $revenueChange >= 0 ? 'text-green-600' : 'text-red-600' }}">
            {{ $revenueChange >= 0 ? '+' : '' }}{{ number_format($revenueChange, 1, ',', '.') }}% em relação ao período anterior
          </p>
        @endif
      </div>
    </div>

    <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
      <div class="p-6 flex items-center justify-between pb-2">
        <h3 class="text-sm font-medium text-muted-foreground">Total de Pedidos</h3>
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-shopping-bag h-4 w-4 text-muted-foreground"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
      </div>
      <div class="p-6 pt-0">
        <div class="text-2xl font-bold">{{ $totalOrders ?? 0 }}</div>
        @if(isset($averageTicket))
          <p class="text-xs text-muted-foreground">Ticket médio: R$ {{ number_format($averageTicket, 2, ',', '.') }}</p>
        @endif
        @if(isset($ordersChange))
          <p class="text-xs {{ $ordersChange >= 0 ? 'text-green-600' : 'text-red-600' }}">
            {{ $ordersChange >= 0 ? '+' : '' }}{{ number_format($ordersChange, 1, ',', '.') }}% em relação ao período anterior
          </p>
        @endif
      </div>
    </div>

    <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
      <div class="p-6 flex items-center justify-between pb-2">
        <h3 class="text-sm font-medium text-muted-foreground">Novos Clientes</h3>
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-users h-4 w-4 text-muted-foreground"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      </div>
      <div class="p-6 pt-0">
        <div class="text-2xl font-bold">{{ $newCustomers ?? 0 }}</div>
        @if(isset($customersChange))
          <p class="text-xs {{ $customersChange >= 0 ? 'text-green-600' : 'text-red-600' }}">
            {{ $customersChange >= 0 ? '+' : '' }}{{ number_format($customersChange, 1, ',', '.') }}% em relação ao período anterior
          </p>
        @endif
      </div>
    </div>

    <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
      <div class="p-6 flex items-center justify-between pb-2">
        <h3 class="text-sm font-medium text-muted-foreground">Produtos Vendidos</h3>
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-package h-4 w-4 text-muted-foreground"><path d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z"/><path d="M12 22V12"/><path d="m3.3 7 7.703 4.734a2 2 0 0 0 1.994 0L20.7 7"/><path d="m7.5 4.27 9 5.15"/></svg>
      </div>
      <div class="p-6 pt-0">
        <div class="text-2xl font-bold">{{ number_format($productsSold ?? 0, 0, ',', '.') }}</div>
        @if(isset($productsChange))
          <p class="text-xs {{ $productsChange >= 0 ? 'text-green-600' : 'text-red-600' }}">
            {{ $productsChange >= 0 ? '+' : '' }}{{ number_format($productsChange, 1, ',', '.') }}% em relação ao período anterior
          </p>
        @endif
      </div>
    </div>
  </div>

  @if(isset($chartData) && !empty($chartData['labels']))
  <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
    <div class="flex flex-col space-y-1.5 p-6">
      <div class="flex items-start justify-between">
        <div>
          <h3 class="font-semibold tracking-tight text-lg">Gráfico de Pedidos</h3>
          <p class="text-sm text-muted-foreground">Pedidos por dia no período selecionado</p>
        </div>
      </div>
    </div>
    <div class="p-6 pt-0">
      <canvas id="ordersChart" height="100"></canvas>
    </div>
  </div>
  @endif

  @if(isset($statusSummary) && $statusSummary->count() > 0)
  <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
    <div class="flex flex-col space-y-1.5 p-6">
      <h3 class="font-semibold tracking-tight text-lg">Pedidos por Status</h3>
      <p class="text-sm text-muted-foreground">Distribuição dos pedidos no período</p>
    </div>
    <div class="p-6 pt-0">
      <div class="space-y-2">
        @foreach($statusSummary as $status => $count)
          <div class="flex items-center justify-between p-2 border rounded">
            <span class="capitalize">{{ ucfirst(str_replace('_', ' ', $status)) }}</span>
            <span class="font-semibold">{{ $count }}</span>
          </div>
        @endforeach
      </div>
    </div>
  </div>
  @endif

  <div class="grid gap-4 md:grid-cols-2">
    <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
      <div class="flex flex-col space-y-1.5 p-6">
        <div class="flex items-start justify-between">
          <div>
            <h3 class="font-semibold tracking-tight text-lg">Relatório de Vendas</h3>
            <p class="text-sm text-muted-foreground">Análise completa das vendas do período</p>
          </div>
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trending-up h-5 w-5 text-primary"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/></svg>
        </div>
      </div>
      <div class="p-6 pt-0">
        <div class="flex items-center justify-between">
          <span class="text-sm text-muted-foreground">{{ $startDate->format('d/m/Y') }} até {{ $endDate->format('d/m/Y') }}</span>
          <a href="{{ route('dashboard.reports.export', ['start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d')]) }}" class="inline-flex items-center justify-center whitespace-nowrap text-sm font-medium border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 rounded-md px-3 gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-download h-4 w-4"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
            Baixar
          </a>
        </div>
      </div>
    </div>

    <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
      <div class="flex flex-col space-y-1.5 p-6">
        <div class="flex items-start justify-between">
          <div>
            <h3 class="font-semibold tracking-tight text-lg">Resumo do Período</h3>
            <p class="text-sm text-muted-foreground">Estatísticas gerais de vendas</p>
          </div>
        </div>
      </div>
      <div class="p-6 pt-0">
        <div class="space-y-3">
          <div class="flex justify-between items-center">
            <span class="text-sm text-muted-foreground">Ticket Médio:</span>
            <span class="font-semibold">R$ {{ number_format($averageTicket ?? 0, 2, ',', '.') }}</span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-sm text-muted-foreground">Período:</span>
            <span class="text-sm">{{ $startDate->format('d/m/Y') }} até {{ $endDate->format('d/m/Y') }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@push('scripts')
@if(isset($chartData) && !empty($chartData['labels']))
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  const ctx = document.getElementById('ordersChart');
  if (ctx) {
    new Chart(ctx, {
      type: 'line',
      data: {
        labels: @json($chartData['labels'] ?? []),
        datasets: [{
          label: 'Pedidos',
          data: @json($chartData['data'] ?? []),
          borderColor: 'rgb(122, 82, 48)',
          backgroundColor: 'rgba(122, 82, 48, 0.1)',
          tension: 0.4
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
          legend: {
            display: false
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              stepSize: 1
            }
          }
        }
      }
    });
  }
});
</script>
@endif
@endpush
@endsection
