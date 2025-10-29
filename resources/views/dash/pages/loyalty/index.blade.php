@extends('dash.layouts.app')

@section('title', 'Fidelidade - OLIKA Dashboard')

@section('content')
<div class="space-y-6 animate-in fade-in duration-500">
  <div>
    <h1 class="text-3xl font-bold tracking-tight">Programa de Fidelidade</h1>
    <p class="text-muted-foreground">Recompense clientes fiéis e aumente o engajamento</p>
  </div>

  <div class="grid gap-4 md:grid-cols-4">
    <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
      <div class="p-6 pt-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-muted-foreground">Pontos Emitidos</p>
            <p class="text-2xl font-bold">12.450</p>
          </div>
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-star h-8 w-8 text-warning"><path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"></path></svg>
        </div>
      </div>
    </div>

    <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
      <div class="p-6 pt-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-muted-foreground">Pontos Resgatados</p>
            <p class="text-2xl font-bold">8.230</p>
          </div>
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-gift h-8 w-8 text-success"><rect x="3" y="8" width="18" height="4" rx="1"></rect><path d="M12 8v13"></path><path d="M19 12v7a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-7"></path><path d="M7.5 8a2.5 2.5 0 0 1 0-5A4.8 8 0 0 1 12 8a4.8 8 0 0 1 4.5-5 2.5 2.5 0 0 1 0 5"></path></svg>
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
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-heart h-8 w-8 text-primary"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"></path></svg>
        </div>
      </div>
    </div>

    <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
      <div class="p-6 pt-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-muted-foreground">Taxa de Resgate</p>
            <p class="text-2xl font-bold">66%</p>
          </div>
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trending-up h-8 w-8 text-primary"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"></polyline><polyline points="16 7 22 7 22 13"></polyline></svg>
        </div>
      </div>
    </div>
  </div>

  <div dir="ltr" data-orientation="horizontal" class="space-y-4">
    <div role="tablist" aria-orientation="horizontal" class="h-10 items-center justify-center rounded-md bg-muted p-1 text-muted-foreground grid w-full grid-cols-3">
      <button type="button" role="tab" aria-selected="true" class="inline-flex items-center justify-center whitespace-nowrap rounded-sm px-3 py-1.5 text-sm font-medium ring-offset-background transition-all data-[state=active]:bg-background data-[state=active]:text-foreground data-[state=active]:shadow-sm">Configurações</button>
      <button type="button" role="tab" aria-selected="false" class="inline-flex items-center justify-center whitespace-nowrap rounded-sm px-3 py-1.5 text-sm font-medium ring-offset-background transition-all">Top Clientes</button>
      <button type="button" role="tab" aria-selected="false" class="inline-flex items-center justify-center whitespace-nowrap rounded-sm px-3 py-1.5 text-sm font-medium ring-offset-background transition-all">Recompensas</button>
    </div>

    <div class="mt-2 space-y-4">
      <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
        <div class="flex flex-col space-y-1.5 p-6">
          <h3 class="text-2xl font-semibold leading-none tracking-tight">Configurações do Programa</h3>
          <p class="text-sm text-muted-foreground">Configure as regras do programa de fidelidade</p>
        </div>
        <div class="p-6 pt-0 space-y-6">
          <div class="flex items-center justify-between">
            <div class="space-y-0.5">
              <label class="text-sm font-medium leading-none" for="loyalty-active">Programa Ativo</label>
              <p class="text-sm text-muted-foreground">Ativar ou desativar o programa de fidelidade</p>
            </div>
            <button type="button" role="switch" aria-checked="true" data-state="checked" value="on" class="peer inline-flex h-6 w-11 shrink-0 cursor-pointer items-center rounded-full border-2 border-transparent transition-colors data-[state=checked]:bg-primary data-[state=unchecked]:bg-input" id="loyalty-active">
              <span data-state="checked" class="pointer-events-none block h-5 w-5 rounded-full bg-background shadow-lg ring-0 transition-transform data-[state=checked]:translate-x-5 data-[state=unchecked]:translate-x-0"></span>
            </button>
          </div>
          <div class="grid gap-4 md:grid-cols-2">
            <div class="space-y-2">
              <label class="text-sm font-medium leading-none" for="points-per-real">Pontos por Real (R$)</label>
              <input type="number" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 md:text-sm" id="points-per-real" placeholder="10" value="10">
              <p class="text-sm text-muted-foreground">Quantos pontos o cliente ganha por R$ 1,00 gasto</p>
            </div>
            <div class="space-y-2">
              <label class="text-sm font-medium leading-none" for="min-order">Pedido Mínimo (R$)</label>
              <input type="number" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 md:text-sm" id="min-order" placeholder="20.00" value="20">
            </div>
          </div>
          <div class="space-y-4">
            <label class="text-sm font-medium leading-none">Níveis de Fidelidade</label>
            <div class="space-y-3">
              <div class="flex items-center gap-4">
                <div class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold bg-primary text-primary-foreground w-20 justify-center">Bronze</div>
                <input type="number" class="flex h-10 rounded-md border border-input bg-background px-3 py-2 md:text-sm w-32" placeholder="0" value="0">
                <span class="text-sm text-muted-foreground">pontos</span>
              </div>
              <div class="flex items-center gap-4">
                <div class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold bg-muted text-muted-foreground w-20 justify-center">Prata</div>
                <input type="number" class="flex h-10 rounded-md border border-input bg-background px-3 py-2 md:text-sm w-32" placeholder="500" value="500">
                <span class="text-sm text-muted-foreground">pontos</span>
              </div>
              <div class="flex items-center gap-4">
                <div class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold bg-warning text-warning-foreground w-20 justify-center">Ouro</div>
                <input type="number" class="flex h-10 rounded-md border border-input bg-background px-3 py-2 md:text-sm w-32" placeholder="1000" value="1000">
                <span class="text-sm text-muted-foreground">pontos</span>
              </div>
            </div>
          </div>
          <button class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium bg-primary text-primary-foreground h-10 px-4 py-2 w-full">Salvar Configurações</button>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection