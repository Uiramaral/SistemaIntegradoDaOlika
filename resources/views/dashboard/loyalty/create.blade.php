@extends('dashboard.layouts.app')

@section('title', 'Nova Transação de Fidelidade')

@section('content')
<div class="space-y-6 animate-in fade-in duration-500">
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-3xl font-bold tracking-tight">Nova Transação de Fidelidade</h1>
      <p class="text-muted-foreground">Criar uma nova transação de pontos manualmente</p>
    </div>
    <a href="{{ route('dashboard.loyalty.index') }}" class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2">
      Voltar
    </a>
  </div>

  @if(session('error'))
    <div class="rounded-lg border bg-red-50 border-red-200 p-4 text-red-700">
      {{ session('error') }}
    </div>
  @endif

  <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
    <div class="p-6">
      <form action="{{ route('dashboard.loyalty.store') }}" method="POST">
        @csrf

        <div class="space-y-6">
          <div>
            <label for="customer_id" class="block text-sm font-medium mb-2">Cliente <span class="text-red-600">*</span></label>
            <select name="customer_id" id="customer_id" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50" required>
              <option value="">Selecione um cliente...</option>
              @foreach($customers as $customer)
                <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                  {{ $customer->name }} {{ $customer->phone ? ' - ' . $customer->phone : '' }}
                </option>
              @endforeach
            </select>
            @error('customer_id')
              <span class="text-sm text-red-600">{{ $message }}</span>
            @enderror
          </div>

          <div>
            <label for="type" class="block text-sm font-medium mb-2">Tipo de Transação <span class="text-red-600">*</span></label>
            <select name="type" id="type" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50" required>
              <option value="earned" {{ old('type') == 'earned' ? 'selected' : '' }}>Ganho (Adicionar pontos)</option>
              <option value="redeemed" {{ old('type') == 'redeemed' ? 'selected' : '' }}>Resgatado (Remover pontos)</option>
              <option value="bonus" {{ old('type') == 'bonus' ? 'selected' : '' }}>Bônus (Adicionar pontos)</option>
              <option value="adjustment" {{ old('type') == 'adjustment' ? 'selected' : '' }}>Ajuste</option>
            </select>
            @error('type')
              <span class="text-sm text-red-600">{{ $message }}</span>
            @enderror
          </div>

          <div>
            <label for="points" class="block text-sm font-medium mb-2">Pontos <span class="text-red-600">*</span></label>
            <input type="number" name="points" id="points" min="1" value="{{ old('points') }}" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50" placeholder="0" required />
            @error('points')
              <span class="text-sm text-red-600">{{ $message }}</span>
            @enderror
            <p class="text-xs text-muted-foreground mt-1">Informe a quantidade de pontos</p>
          </div>

          <div>
            <label for="description" class="block text-sm font-medium mb-2">Descrição</label>
            <textarea name="description" id="description" rows="3" maxlength="255" class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50" placeholder="Ex: Ajuste manual de pontos">{{ old('description', 'Ajuste manual de pontos') }}</textarea>
            @error('description')
              <span class="text-sm text-red-600">{{ $message }}</span>
            @enderror
          </div>

          <div class="flex items-center justify-end gap-3 pt-4 border-t">
            <a href="{{ route('dashboard.loyalty.index') }}" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2">
              Cancelar
            </a>
            <button type="submit" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2">
              Criar Transação
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

