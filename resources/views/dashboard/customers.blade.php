@extends('layouts.dashboard')

@section('title','Clientes — Dashboard Olika')

@section('content')

<div class="card">

  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
    <h1 class="text-xl" style="font-weight:800">Clientes</h1>
    <a href="{{ route('dashboard.customers.create') }}" class="btn" style="background:#059669;color:#fff">➕ Novo Cliente</a>
  </div>

  @if(session('ok'))<div class="badge" style="background:#d1fae5;color:#065f46;margin-bottom:12px">{{ session('ok') }}</div>@endif
  @if(session('error'))<div class="badge" style="background:#fee2e2;color:#991b1b;margin-bottom:12px">{{ session('error') }}</div>@endif

  <table style="width:100%">
    <thead><tr><th>ID</th><th>Nome</th><th>Telefone</th><th>Email</th><th>Pedidos</th><th>Total Gasto</th><th>Cadastrado</th><th>Ações</th></tr></thead>
    <tbody>
      @forelse($customers as $c)
        <tr>
          <td data-label="ID">#{{ $c->id }}</td>
          <td data-label="Nome"><strong>{{ $c->name }}</strong></td>
          <td data-label="Telefone">{{ $c->phone }}</td>
          <td data-label="Email">{{ $c->email ?? '—' }}</td>
          <td data-label="Pedidos">{{ $c->total_orders ?? 0 }}</td>
          <td data-label="Total">R$ {{ number_format($c->total_spent ?? 0,2,',','.') }}</td>
          <td data-label="Cadastrado">{{ \Carbon\Carbon::parse($c->created_at)->format('d/m/Y') }}</td>
          <td data-label="Ações">
            <div style="display:flex;gap:4px">
              <a href="{{ route('dashboard.customers.show', $c->id) }}" class="badge" style="background:#3b82f6;color:#fff">👁️ Ver</a>
              <a href="{{ route('dashboard.customers.edit', $c->id) }}" class="badge" style="background:#f59e0b;color:#fff">✏️ Editar</a>
              <form method="POST" action="{{ route('dashboard.customers.destroy', $c->id) }}" style="display:inline" onsubmit="return confirm('Excluir este cliente?')">
                @csrf @method('DELETE')
                <button type="submit" class="badge" style="background:#ef4444;color:#fff">🗑️ Excluir</button>
              </form>
            </div>
          </td>
        </tr>
      @empty
        <tr><td colspan="8" style="text-align:center;padding:20px">Nenhum cliente cadastrado</td></tr>
      @endforelse
    </tbody>
  </table>

  <div style="margin-top:12px">{{ $customers->links() }}</div>

</div>

@endsection
