@extends('dashboard.layouts.app')

@section('title', 'Editar Transação de Fidelidade')

@section('content')
<div class="space-y-6 animate-in fade-in duration-500">
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-3xl font-bold tracking-tight">Editar Transação de Fidelidade</h1>
      <p class="text-muted-foreground">Ajuste os detalhes da transação</p>
    </div>
    <a href="{{ route('dashboard.loyalty.index') }}" class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2">
      Voltar
    </a>
  </div>

  @if(session('success'))
    <div class="rounded-lg border bg-green-50 border-green-200 p-4 text-green-700">
      {{ session('success') }}
    </div>
  @endif

  <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
    <div class="p-6">
      <form action="{{ route('dashboard.loyalty.update', $loyalty) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="space-y-6">
          <div>
            <label class="block text-sm font-medium mb-2">Cliente</label>
            <input type="text" value="{{ $loyalty->customer->name ?? 'Cliente não encontrado' }}" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 bg-gray-100" readonly />
          </div>

          <div>
            <label class="block text-sm font-medium mb-2">Tipo de Transação</label>
            <input type="text" value="{{ $loyalty->type_label }}" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 bg-gray-100" readonly />
            <p class="text-xs text-muted-foreground mt-1">O tipo não pode ser alterado</p>
          </div>

          <div>
            <label for="points" class="block text-sm font-medium mb-2">Pontos</label>
            <input type="number" name="points" id="points" min="1" value="{{ old('points', $loyalty->points) }}" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50" required />
            @error('points')
              <span class="text-sm text-red-600">{{ $message }}</span>
            @enderror
          </div>

          <div>
            <label for="description" class="block text-sm font-medium mb-2">Descrição</label>
            <textarea name="description" id="description" rows="3" maxlength="255" class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">{{ old('description', $loyalty->description ?? '') }}</textarea>
            @error('description')
              <span class="text-sm text-red-600">{{ $message }}</span>
            @enderror
          </div>

          @if($loyalty->order)
          <div>
            <label class="block text-sm font-medium mb-2">Pedido Relacionado</label>
            <a href="{{ route('dashboard.orders.show', $loyalty->order) }}" class="inline-flex items-center text-primary hover:underline">
              Pedido #{{ $loyalty->order->order_number ?? $loyalty->order->id }}
            </a>
          </div>
          @endif

          @if($loyalty->expires_at)
          <div>
            <label class="block text-sm font-medium mb-2">Data de Expiração</label>
            <input type="text" value="{{ $loyalty->expires_at->format('d/m/Y') }}" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 bg-gray-100" readonly />
          </div>
          @endif

          <div>
            <label class="block text-sm font-medium mb-2">Data de Criação</label>
            <input type="text" value="{{ $loyalty->created_at->format('d/m/Y H:i:s') }}" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 bg-gray-100" readonly />
          </div>

          <div class="flex items-center justify-end gap-3 pt-4 border-t">
            <a href="{{ route('dashboard.loyalty.index') }}" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2">
              Cancelar
            </a>
            <button type="submit" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2">
              Salvar Alterações
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
