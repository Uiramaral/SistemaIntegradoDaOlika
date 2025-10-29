@extends('dash.layouts.app')

@section('title', 'Status & Templates')
@section('page_title', 'Status & Templates')

@section('content')
<div class="space-y-6 animate-in fade-in duration-500">
  <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
    <div>
      <h1 class="text-3xl font-bold tracking-tight">Status & Templates</h1>
      <p class="text-muted-foreground">Gerencie os status dos pedidos e templates de mensagens</p>
    </div>
    <button class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2 gap-2">Novo Status</button>
  </div>

  <div class="grid gap-6 lg:grid-cols-2">
    <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
      <div class="flex flex-col space-y-1.5 p-6">
        <h3 class="text-2xl font-semibold leading-none tracking-tight">Status dos Pedidos</h3>
        <p class="text-sm text-muted-foreground">Personalize os status disponíveis para seus pedidos</p>
      </div>
      <div class="p-6 pt-0 grid gap-3">
        <div class="flex items-center justify-between rounded-md border p-3">
          <div>
            <p class="font-medium">Pendente</p>
            <p class="text-sm text-muted-foreground">Aguardando confirmação</p>
          </div>
          <button class="inline-flex items-center justify-center h-8 w-8 rounded-md border hover:bg-accent hover:text-accent-foreground">✎</button>
        </div>
        <div class="flex items-center justify-between rounded-md border p-3">
          <div>
            <p class="font-medium">Confirmado</p>
            <p class="text-sm text-muted-foreground">Pedido confirmado</p>
          </div>
          <button class="inline-flex items-center justify-center h-8 w-8 rounded-md border hover:bg-accent hover:text-accent-foreground">✎</button>
        </div>
        <div class="flex items-center justify-between rounded-md border p-3">
          <div>
            <p class="font-medium">Em Preparo</p>
            <p class="text-sm text-muted-foreground">Sendo preparado</p>
          </div>
          <button class="inline-flex items-center justify-center h-8 w-8 rounded-md border hover:bg-accent hover:text-accent-foreground">✎</button>
        </div>
        <div class="flex items-center justify-between rounded-md border p-3">
          <div>
            <p class="font-medium">Saiu para Entrega</p>
            <p class="text-sm text-muted-foreground">Em rota de entrega</p>
          </div>
          <button class="inline-flex items-center justify-center h-8 w-8 rounded-md border hover:bg-accent hover:text-accent-foreground">✎</button>
        </div>
        <div class="flex items-center justify-between rounded-md border p-3">
          <div>
            <p class="font-medium">Entregue</p>
            <p class="text-sm text-muted-foreground">Pedido entregue</p>
          </div>
          <button class="inline-flex items-center justify-center h-8 w-8 rounded-md border hover:bg-accent hover:text-accent-foreground">✎</button>
        </div>
        <div class="flex items-center justify-between rounded-md border p-3">
          <div>
            <p class="font-medium">Cancelado</p>
            <p class="text-sm text-muted-foreground">Pedido cancelado</p>
          </div>
          <button class="inline-flex items-center justify-center h-8 w-8 rounded-md border hover:bg-accent hover:text-accent-foreground">✎</button>
        </div>
      </div>
    </div>

    <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
      <div class="flex flex-col space-y-1.5 p-6">
        <h3 class="text-2xl font-semibold leading-none tracking-tight">Configurar Template</h3>
        <p class="text-sm text-muted-foreground">Personalize mensagens para cada status</p>
      </div>
      <div class="p-6 pt-0 space-y-4">
        <div class="space-y-2">
          <label class="text-sm font-medium">Selecionar Status</label>
          <select class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 md:text-sm">
            <option>Pendente</option>
            <option>Confirmado</option>
            <option>Em Preparo</option>
            <option>Saiu para Entrega</option>
            <option>Entregue</option>
            <option>Cancelado</option>
          </select>
        </div>
        <div class="space-y-2">
          <label class="text-sm font-medium" for="template-title">Título da Notificação</label>
          <input id="template-title" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 md:text-sm" placeholder="Ex: Pedido Confirmado" value="Pedido Confirmado!" />
        </div>
        <div class="space-y-2">
          <label class="text-sm font-medium" for="template-message">Mensagem</label>
          <textarea id="template-message" rows="5" class="w-full rounded-md border border-input bg-background px-3 py-2 md:text-sm" placeholder="Digite a mensagem...">Seu pedido #@{{numero}} foi confirmado e está sendo preparado com todo carinho! 🍕</textarea>
          <p class="text-sm text-muted-foreground">Variáveis disponíveis: @{{numero}}, @{{cliente}}, @{{valor}}</p>
        </div>
        <div class="space-y-2">
          <label class="text-sm font-medium">Cor do Status</label>
          <div class="flex gap-2">
            <button class="h-8 w-8 rounded-md border" style="background-color:#10b981"></button>
            <button class="h-8 w-8 rounded-md border" style="background-color:#3b82f6"></button>
            <button class="h-8 w-8 rounded-md border" style="background-color:#f59e0b"></button>
            <button class="h-8 w-8 rounded-md border" style="background-color:#ef4444"></button>
            <button class="h-8 w-8 rounded-md border" style="background-color:#8b5cf6"></button>
            <button class="h-8 w-8 rounded-md border" style="background-color:#64748b"></button>
          </div>
        </div>
        <button class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium bg-primary text-primary-foreground h-10 px-4 py-2">Salvar Template</button>
      </div>
    </div>
  </div>

  <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
    <div class="flex flex-col space-y-1.5 p-6">
      <h3 class="text-2xl font-semibold leading-none tracking-tight">Prévia da Notificação</h3>
    </div>
    <div class="p-6 pt-0">
      <div class="rounded-lg border p-4 space-y-1">
        <div class="text-sm text-muted-foreground">Pedido #123</div>
        <h4 class="font-semibold">Pedido Confirmado!</h4>
        <p>Seu pedido #123 foi confirmado e está sendo preparado com todo carinho! 🍕</p>
        <p class="text-xs text-muted-foreground">Há 2 minutos</p>
      </div>
    </div>
  </div>
</div>
@endsection


