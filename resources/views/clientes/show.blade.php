@extends('layouts.dashboard')

@section('title','Cliente: '.$cliente->nome)

@section('page-title','Cliente')

@section('page-subtitle', $cliente->nome)

@section('page-actions')
  <form method="POST" action="{{ route('clientes.destroy',$cliente) }}" onsubmit="return confirm('Remover cliente?');">
    @csrf @method('DELETE')
    <button class="pill">Remover</button>
  </form>
  <a href="{{ route('clientes.edit',$cliente) }}" class="btn-primary">Editar</a>
@endsection

@section('stat-cards')
  <x-stat-card label="Pedidos" :value="$cliente->pedidos_count ?? 0" />
@endsection

@section('content')
  <div class="grid gap-4">
    <div class="card p-4">
      <div class="font-semibold mb-2">Dados</div>
      <div class="grid sm:grid-cols-2 gap-2 text-sm">
        <div><span class="text-neutral-500">Telefone:</span> {{ $cliente->telefone ?: '-' }}</div>
        <div><span class="text-neutral-500">E-mail:</span> {{ $cliente->email ?: '-' }}</div>
        <div><span class="text-neutral-500">Endereço:</span> {{ $cliente->endereco ?: '-' }}</div>
        <div><span class="text-neutral-500">Cidade:</span> {{ $cliente->cidade ?: '-' }}</div>
      </div>
    </div>

    <div class="card overflow-hidden">
      <div class="px-4 py-3 border-b font-semibold">Últimos pedidos</div>
      <div class="overflow-auto">
        <table class="table-compact">
          <thead>
            <tr>
              <th class="text-left">#</th>
              <th class="text-left">Status</th>
              <th class="text-left">Entrega</th>
              <th class="text-right">Total</th>
              <th class="text-right">Ações</th>
            </tr>
          </thead>
          <tbody>
            @forelse($cliente->pedidos as $p)
            <tr>
              <td>{{ $p->id }}</td>
              <td><span class="pill">{{ ucfirst($p->status) }}</span></td>
              <td>{{ $p->data_entrega?->format('d/m H:i') ?? '-' }}</td>
              <td class="text-right">R$ {{ number_format($p->total,2,',','.') }}</td>
              <td class="text-right"><a class="pill" href="{{ route('pedidos.show',$p) }}">Ver</a></td>
            </tr>
            @empty
            <tr><td colspan="5" class="px-4 py-6 text-center text-neutral-500">Sem pedidos.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
@endsection
