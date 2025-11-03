@extends('dashboard.layouts.app')

@section('title', 'Nova Transação de Cashback')

@section('content')
<div class="space-y-6 animate-in fade-in duration-500">
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-3xl font-bold tracking-tight">Nova Transação de Cashback</h1>
      <p class="text-muted-foreground">Criar uma nova transação de cashback manualmente</p>
    </div>
    <a href="{{ route('dashboard.cashback.index') }}" class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2">
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
      <form action="{{ route('dashboard.cashback.store') }}" method="POST">
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
              <option value="credit" {{ old('type') == 'credit' ? 'selected' : '' }}>Crédito (Adicionar cashback)</option>
              <option value="debit" {{ old('type') == 'debit' ? 'selected' : '' }}>Débito (Remover cashback)</option>
            </select>
            @error('type')
              <span class="text-sm text-red-600">{{ $message }}</span>
            @enderror
          </div>

          <div>
            <label for="amount" class="block text-sm font-medium mb-2">Valor (R$) <span class="text-red-600">*</span></label>
            <input type="number" step="0.01" min="0.01" name="amount" id="amount" value="{{ old('amount') }}" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50" placeholder="0.00" required />
            @error('amount')
              <span class="text-sm text-red-600">{{ $message }}</span>
            @enderror
            <p class="text-xs text-muted-foreground mt-1">Informe o valor em reais (R$)</p>
          </div>

          <div>
            <label for="description" class="block text-sm font-medium mb-2">Descrição</label>
            <textarea name="description" id="description" rows="3" maxlength="255" class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50" placeholder="Ex: Ajuste manual de cashback">{{ old('description', 'Ajuste manual de cashback') }}</textarea>
            @error('description')
              <span class="text-sm text-red-600">{{ $message }}</span>
            @enderror
          </div>

          <div class="flex items-center justify-end gap-3 pt-4 border-t">
            <a href="{{ route('dashboard.cashback.index') }}" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2">
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

