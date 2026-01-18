@extends('dash.layouts.app')

@section('title', 'Cashback')
@section('page_title', 'Cashback')

@section('content')
<div class="space-y-6 animate-in fade-in duration-500">
  <div>
    <h1 class="text-3xl font-bold tracking-tight">Programa de Cashback</h1>
    <p class="text-muted-foreground">Recompense seus clientes fiéis com cashback em compras</p>
  </div>
  
  <div class="grid gap-4 md:grid-cols-4">
    <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
      <div class="p-6 pt-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-muted-foreground">Cashback Total</p>
            <p class="text-2xl font-bold">R$ 8.450</p>
          </div>
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-dollar-sign h-8 w-8 text-primary">
            <line x1="12" x2="12" y1="2" y2="22"></line>
            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
          </svg>
        </div>
      </div>
    </div>
    
    <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
      <div class="p-6 pt-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-muted-foreground">Resgatado</p>
            <p class="text-2xl font-bold">R$ 3.280</p>
          </div>
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trending-up h-8 w-8 text-success">
            <polyline points="22 7 13.5 15.5 8.5 10.5 2 17"></polyline>
            <polyline points="16 7 22 7 22 13"></polyline>
          </svg>
        </div>
      </div>
    </div>
    
    <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
      <div class="p-6 pt-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-muted-foreground">Disponível</p>
            <p class="text-2xl font-bold">R$ 5.170</p>
          </div>
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-award h-8 w-8 text-warning">
            <path d="m15.477 12.89 1.515 8.526a.5.5 0 0 1-.81.47l-3.58-2.687a1 1 0 0 0-1.197 0l-3.586 2.686a.5.5 0 0 1-.81-.469l1.514-8.526"></path>
            <circle cx="12" cy="8" r="6"></circle>
          </svg>
        </div>
      </div>
    </div>
    
    <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
      <div class="p-6 pt-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-muted-foreground">Clientes Ativos</p>
            <p class="text-2xl font-bold">342</p>
          </div>
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-users h-8 w-8 text-primary">
            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
            <circle cx="9" cy="7" r="4"></circle>
            <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
          </svg>
        </div>
      </div>
    </div>
  </div>
  
  <div class="grid gap-6 lg:grid-cols-2">
    <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
      <div class="flex flex-col space-y-1.5 p-6">
        <h3 class="text-2xl font-semibold leading-none tracking-tight">Configurações do Programa</h3>
        <p class="text-sm text-muted-foreground">Configure as regras do programa de cashback</p>
      </div>
      <div class="p-6 pt-0 space-y-6">
        <div class="flex items-center justify-between">
          <div class="space-y-0.5">
            <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70" for="cashback-active">Programa Ativo</label>
            <p class="text-sm text-muted-foreground">Ativar ou desativar o programa de cashback</p>
          </div>
          <button type="button" role="switch" aria-checked="true" data-state="checked" value="on" class="peer inline-flex h-6 w-11 shrink-0 cursor-pointer items-center rounded-full border-2 border-transparent transition-colors data-[state=checked]:bg-primary data-[state=unchecked]:bg-input focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:ring-offset-background disabled:cursor-not-allowed disabled:opacity-50" id="cashback-active">
            <span data-state="checked" class="pointer-events-none block h-5 w-5 rounded-full bg-background shadow-lg ring-0 transition-transform data-[state=checked]:translate-x-5 data-[state=unchecked]:translate-x-0"></span>
          </button>
        </div>
        
        <div class="space-y-2">
          <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70" for="percentage">Percentual de Cashback (%)</label>
          <div class="flex items-center gap-4">
            <span dir="ltr" data-orientation="horizontal" aria-disabled="false" class="relative flex w-full touch-none select-none items-center flex-1" id="percentage" style="--radix-slider-thumb-transform: translateX(-50%);">
              <span data-orientation="horizontal" class="relative h-2 w-full grow overflow-hidden rounded-full bg-secondary">
                <span data-orientation="horizontal" class="absolute h-full bg-primary" style="left: 0%; right: 75%;"></span>
              </span>
              <span style="transform: var(--radix-slider-thumb-transform); position: absolute; left: calc(25% + 5px);">
                <span role="slider" aria-valuemin="0" aria-valuemax="20" aria-orientation="horizontal" data-orientation="horizontal" tabindex="0" class="block h-5 w-5 rounded-full border-2 border-primary bg-background ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50" data-radix-collection-item="" aria-valuenow="5" style=""></span>
              </span>
            </span>
            <input type="number" class="flex h-10 rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm w-20" min="0" max="20" step="0.5" value="5">
          </div>
          <p class="text-sm text-muted-foreground">Porcentagem do valor da compra devolvida como cashback</p>
        </div>
        
        <div class="space-y-2">
          <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70" for="min-purchase">Compra Mínima (R$)</label>
          <input type="number" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm" id="min-purchase" placeholder="0.00" value="30">
          <p class="text-sm text-muted-foreground">Valor mínimo da compra para receber cashback</p>
        </div>
        
        <div class="space-y-2">
          <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70" for="max-cashback">Cashback Máximo por Compra (R$)</label>
          <input type="number" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm" id="max-cashback" placeholder="0.00" value="50">
        </div>
        
        <div class="space-y-2">
          <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70" for="expiry-days">Validade do Cashback (dias)</label>
          <input type="number" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm" id="expiry-days" placeholder="90" value="90">
          <p class="text-sm text-muted-foreground">Tempo até o cashback expirar se não for utilizado</p>
        </div>
        
        <button class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2 w-full">Salvar Configurações</button>
      </div>
    </div>
    
    <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
      <div class="flex flex-col space-y-1.5 p-6">
        <h3 class="text-2xl font-semibold leading-none tracking-tight">Últimas Transações</h3>
        <p class="text-sm text-muted-foreground">Cashback gerado recentemente</p>
      </div>
      <div class="p-6 pt-0">
        <div class="relative w-full overflow-auto">
          <table class="w-full caption-bottom text-sm">
            <thead class="[&_tr]:border-b">
              <tr class="border-b transition-colors data-[state=selected]:bg-muted hover:bg-muted/50">
                <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground [&:has([role=checkbox])]:pr-0">Cliente</th>
                <th class="h-12 px-4 align-middle font-medium text-muted-foreground [&:has([role=checkbox])]:pr-0 text-right">Compra</th>
                <th class="h-12 px-4 align-middle font-medium text-muted-foreground [&:has([role=checkbox])]:pr-0 text-right">Cashback</th>
                <th class="h-12 px-4 align-middle font-medium text-muted-foreground [&:has([role=checkbox])]:pr-0 text-right">Data</th>
              </tr>
            </thead>
            <tbody class="[&_tr:last-child]:border-0">
              <tr class="border-b transition-colors data-[state=selected]:bg-muted hover:bg-muted/50">
                <td class="p-4 align-middle [&:has([role=checkbox])]:pr-0 font-medium">João Silva</td>
                <td class="p-4 align-middle [&:has([role=checkbox])]:pr-0 text-right">R$ 85,00</td>
                <td class="p-4 align-middle [&:has([role=checkbox])]:pr-0 text-right font-semibold text-success">R$ 4,25</td>
                <td class="p-4 align-middle [&:has([role=checkbox])]:pr-0 text-right text-muted-foreground">15/01/2025</td>
              </tr>
              <tr class="border-b transition-colors data-[state=selected]:bg-muted hover:bg-muted/50">
                <td class="p-4 align-middle [&:has([role=checkbox])]:pr-0 font-medium">Maria Santos</td>
                <td class="p-4 align-middle [&:has([role=checkbox])]:pr-0 text-right">R$ 120,00</td>
                <td class="p-4 align-middle [&:has([role=checkbox])]:pr-0 text-right font-semibold text-success">R$ 6,00</td>
                <td class="p-4 align-middle [&:has([role=checkbox])]:pr-0 text-right text-muted-foreground">15/01/2025</td>
              </tr>
              <tr class="border-b transition-colors data-[state=selected]:bg-muted hover:bg-muted/50">
                <td class="p-4 align-middle [&:has([role=checkbox])]:pr-0 font-medium">Pedro Costa</td>
                <td class="p-4 align-middle [&:has([role=checkbox])]:pr-0 text-right">R$ 45,00</td>
                <td class="p-4 align-middle [&:has([role=checkbox])]:pr-0 text-right font-semibold text-success">R$ 2,25</td>
                <td class="p-4 align-middle [&:has([role=checkbox])]:pr-0 text-right text-muted-foreground">14/01/2025</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
