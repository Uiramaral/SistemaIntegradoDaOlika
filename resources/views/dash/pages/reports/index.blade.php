@extends('dash.layouts.app')

@section('title', 'Relatórios - OLIKA Painel')

@section('content')
<div class="space-y-6 animate-in fade-in duration-500">
  <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
    <div>
      <h1 class="text-3xl font-bold tracking-tight">Relatórios</h1>
      <p class="text-muted-foreground">Analise o desempenho do seu negócio</p>
    </div>
    <button class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2 gap-2">
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-calendar h-4 w-4"><path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/></svg>
      Selecionar Período
    </button>
  </div>

  <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
    <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
      <div class="p-6 flex items-center justify-between pb-2">
        <h3 class="text-sm font-medium text-muted-foreground">Receita Total</h3>
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-dollar-sign h-4 w-4 text-muted-foreground"><line x1="12" x2="12" y1="2" y2="22"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
      </div>
      <div class="p-6 pt-0">
        <div class="text-2xl font-bold">R$ 24.580,00</div>
        <p class="text-xs text-success">+12.5% em relação ao período anterior</p>
      </div>
    </div>

    <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
      <div class="p-6 flex items-center justify-between pb-2">
        <h3 class="text-sm font-medium text-muted-foreground">Total de Pedidos</h3>
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-shopping-bag h-4 w-4 text-muted-foreground"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
      </div>
      <div class="p-6 pt-0">
        <div class="text-2xl font-bold">342</div>
        <p class="text-xs text-success">+8.3% em relação ao período anterior</p>
      </div>
    </div>

    <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
      <div class="p-6 flex items-center justify-between pb-2">
        <h3 class="text-sm font-medium text-muted-foreground">Novos Clientes</h3>
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-users h-4 w-4 text-muted-foreground"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      </div>
      <div class="p-6 pt-0">
        <div class="text-2xl font-bold">89</div>
        <p class="text-xs text-success">+15.2% em relação ao período anterior</p>
      </div>
    </div>

    <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
      <div class="p-6 flex items-center justify-between pb-2">
        <h3 class="text-sm font-medium text-muted-foreground">Produtos Vendidos</h3>
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-package h-4 w-4 text-muted-foreground"><path d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z"/><path d="M12 22V12"/><path d="m3.3 7 7.703 4.734a2 2 0 0 0 1.994 0L20.7 7"/><path d="m7.5 4.27 9 5.15"/></svg>
      </div>
      <div class="p-6 pt-0">
        <div class="text-2xl font-bold">1.247</div>
        <p class="text-xs text-destructive">-3.1% em relação ao período anterior</p>
      </div>
    </div>
  </div>

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
          <span class="text-sm text-muted-foreground">Últimos 30 dias</span>
          <button class="inline-flex items-center justify-center whitespace-nowrap text-sm font-medium border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 rounded-md px-3 gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-download h-4 w-4"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
            Baixar
          </button>
        </div>
      </div>
    </div>

    <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
      <div class="flex flex-col space-y-1.5 p-6">
        <div class="flex items-start justify-between">
          <div>
            <h3 class="font-semibold tracking-tight text-lg">Relatório de Produtos</h3>
            <p class="text-sm text-muted-foreground">Performance dos produtos mais vendidos</p>
          </div>
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trending-up h-5 w-5 text-primary"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/></svg>
        </div>
      </div>
      <div class="p-6 pt-0">
        <div class="flex items-center justify-between">
          <span class="text-sm text-muted-foreground">Últimos 7 dias</span>
          <button class="inline-flex items-center justify-center whitespace-nowrap text-sm font-medium border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 rounded-md px-3 gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-download h-4 w-4"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
            Baixar
          </button>
        </div>
      </div>
    </div>
</div>

  <div class="grid gap-4 md:grid-cols-2">
    <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
      <div class="flex flex-col space-y-1.5 p-6">
        <div class="flex items-start justify-between">
          <div>
            <h3 class="font-semibold tracking-tight text-lg">Relatório de Clientes</h3>
            <p class="text-sm text-muted-foreground">Análise do comportamento dos clientes</p>
          </div>
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trending-up h-5 w-5 text-primary"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/></svg>
        </div>
      </div>
      <div class="p-6 pt-0">
        <div class="flex items-center justify-between">
          <span class="text-sm text-muted-foreground">Últimos 30 dias</span>
          <button class="inline-flex items-center justify-center whitespace-nowrap text-sm font-medium border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 rounded-md px-3 gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-download h-4 w-4"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
            Baixar
          </button>
        </div>
      </div>
</div>

    <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
      <div class="flex flex-col space-y-1.5 p-6">
        <div class="flex items-start justify-between">
          <div>
            <h3 class="font-semibold tracking-tight text-lg">Relatório Financeiro</h3>
            <p class="text-sm text-muted-foreground">Resumo financeiro e fluxo de caixa</p>
          </div>
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trending-up h-5 w-5 text-primary"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/></svg>
        </div>
      </div>
      <div class="p-6 pt-0">
        <div class="flex items-center justify-between">
          <span class="text-sm text-muted-foreground">Este mês</span>
          <button class="inline-flex items-center justify-center whitespace-nowrap text-sm font-medium border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 rounded-md px-3 gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-download h-4 w-4"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
            Baixar
          </button>
        </div>
      </div>
    </div>
</div>
</div>
@endsection
