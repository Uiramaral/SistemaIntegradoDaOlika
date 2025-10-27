@extends('layouts.dashboard')

@section('title','Categorias — Dashboard Olika')

@section('content')

<div class="card">

  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
    <h1 class="text-xl" style="font-weight:800">Categorias</h1>
    <a href="{{ route('dashboard.categories.create') }}" class="btn" style="background:#059669;color:#fff">➕ Nova Categoria</a>
  </div>

  @if(session('ok'))<div class="badge" style="background:#d1fae5;color:#065f46;margin-bottom:12px">{{ session('ok') }}</div>@endif
  @if(session('error'))<div class="badge" style="background:#fee2e2;color:#991b1b;margin-bottom:12px">{{ session('error') }}</div>@endif

  <table style="width:100%">
    <thead><tr><th>ID</th><th>Nome</th><th>Slug</th><th>Descrição</th><th>Status</th><th>Ordem</th><th>Ações</th></tr></thead>
    <tbody>
      @forelse($cats as $cat)
        <tr>
          <td data-label="ID">#{{ $cat->id }}</td>
          <td data-label="Nome"><strong>{{ $cat->name }}</strong></td>
          <td data-label="Slug"><code style="background:#f1f5f9;padding:2px 6px;border-radius:4px">{{ $cat->slug }}</code></td>
          <td data-label="Descrição">{{ \Str::limit($cat->description ?? '—', 40) }}</td>
          <td data-label="Status">
            <form method="POST" action="{{ route('dashboard.categories.toggle', $cat->id) }}" style="display:inline">
              @csrf
              <button type="submit" class="badge" style="{{ $cat->is_active ? 'background:#d1fae5;color:#065f46' : 'background:#fee2e2;color:#991b1b' }}">
                {{ $cat->is_active ? '✅ Ativa' : '❌ Inativa' }}
              </button>
            </form>
          </td>
          <td data-label="Ordem">{{ $cat->display_order ?? 0 }}</td>
          <td data-label="Ações">
            <div style="display:flex;gap:4px">
              <a href="{{ route('dashboard.categories.edit', $cat->id) }}" class="badge" style="background:#f59e0b;color:#fff">✏️ Editar</a>
              <form method="POST" action="{{ route('dashboard.categories.destroy', $cat->id) }}" style="display:inline" onsubmit="return confirm('Excluir esta categoria?')">
                @csrf @method('DELETE')
                <button type="submit" class="badge" style="background:#ef4444;color:#fff">🗑️ Excluir</button>
              </form>
            </div>
          </td>
        </tr>
      @empty
        <tr><td colspan="7" style="text-align:center;padding:20px">Nenhuma categoria cadastrada</td></tr>
      @endforelse
    </tbody>
  </table>

  <div style="margin-top:12px">{{ $cats->links() }}</div>

</div>

@endsection
