@extends('layouts.dashboard')

@section('title','Produtos — Dashboard Olika')

@section('content')

<div class="card">

  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
    <h1 class="text-xl" style="font-weight:800">Produtos</h1>
    <a href="{{ route('dashboard.products.create') }}" class="btn" style="background:#059669;color:#fff">➕ Novo Produto</a>
  </div>

  @if(session('ok'))<div class="badge" style="background:#d1fae5;color:#065f46;margin-bottom:12px">{{ session('ok') }}</div>@endif
  @if(session('error'))<div class="badge" style="background:#fee2e2;color:#991b1b;margin-bottom:12px">{{ session('error') }}</div>@endif

  <table style="width:100%">
    <thead>
      <tr>
        <th>ID</th>
        <th>Nome</th>
        <th>Categoria</th>
        <th>Preço</th>
        <th>Status</th>
        <th>Cadastrado</th>
        <th>Ações</th>
      </tr>
    </thead>
    <tbody>
      @forelse($products as $p)
        <tr>
          <td data-label="ID">#{{ $p->id }}</td>
          <td data-label="Produto"><strong>{{ $p->name }}</strong><br><small style="color:#6b7280">{{ $p->sku ?? '—' }}</small></td>
          <td data-label="Categoria">{{ $p->category_name ?? '—' }}</td>
          <td data-label="Preço">R$ {{ number_format($p->price,2,',','.') }}</td>
          <td data-label="Status">
            <form method="POST" action="{{ route('dashboard.products.toggle', $p->id) }}" style="display:inline">
              @csrf
              <button type="submit" class="badge" style="{{ $p->is_active ? 'background:#d1fae5;color:#065f46' : 'background:#fee2e2;color:#991b1b' }}">
                {{ $p->is_active ? '✅ Ativo' : '❌ Inativo' }}
              </button>
            </form>
          </td>
          <td data-label="Cadastrado">{{ \Carbon\Carbon::parse($p->created_at)->format('d/m/Y') }}</td>
          <td data-label="Ações">
            <div style="display:flex;gap:4px">
              <a href="{{ route('dashboard.products.edit', $p->id) }}" class="badge" style="background:#f59e0b;color:#fff">✏️ Editar</a>
              <form method="POST" action="{{ route('dashboard.products.destroy', $p->id) }}" style="display:inline" onsubmit="return confirm('Excluir este produto?')">
                @csrf @method('DELETE')
                <button type="submit" class="badge" style="background:#ef4444;color:#fff">🗑️ Excluir</button>
              </form>
            </div>
          </td>
        </tr>
      @empty
        <tr><td colspan="7" style="text-align:center;padding:20px">Nenhum produto cadastrado</td></tr>
      @endforelse
    </tbody>
  </table>

  <div style="margin-top:12px">{{ $products->links() }}</div>

</div>

@endsection
