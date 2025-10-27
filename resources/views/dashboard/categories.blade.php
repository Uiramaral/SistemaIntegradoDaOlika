{{-- PÁGINA: Categorias (Listagem de Categorias de Produtos) --}}
@extends('layouts.dashboard')

@section('title','Categorias — Dashboard Olika')

@section('page-title','Categorias')

@section('page-subtitle','Organize seus produtos por categoria')

@section('content')

@if(session('ok'))<div class="badge" style="background:#d1fae5;color:#065f46;margin-bottom:12px">{{ session('ok') }}</div>@endif
@if(session('error'))<div class="badge" style="background:#fee2e2;color:#991b1b;margin-bottom:12px">{{ session('error') }}</div>@endif

<div class="toolbar">
  <div class="grow">
    <h1 class="page-title">Categorias</h1>
    <p class="page-sub">Organize seus produtos por categoria</p>
  </div>
  <div class="actions">
    <a class="btn ghost" href="{{ route('dashboard.index') }}">Voltar</a>
    <a class="btn primary" href="{{ route('dashboard.categories.create') }}">+ Nova Categoria</a>
  </div>
</div>

{{-- Campo de busca --}}
<div class="card">
  <div class="input-search">
    <svg class="icon" width="18" height="18" viewBox="0 0 24 24"><path d="M21 21l-4.35-4.35" stroke="#999" stroke-width="1.5" fill="none"/><circle cx="10.5" cy="10.5" r="7" stroke="#999" stroke-width="1.5" fill="none"/></svg>
    <input class="inp" placeholder="Buscar categorias..." name="q" value="{{ request('q') }}">
  </div>

  {{-- Grid de categorias --}}
  <div class="grid-2" style="grid-template-columns: repeat(3, 1fr); gap:16px; margin-top:16px;">
    @forelse($cats as $categoria)
      @php $ativa = (bool)($categoria->active ?? $categoria->is_active ?? true); @endphp
      <div class="card" style="border-radius:14px;">
        <div style="display:flex; gap:10px; align-items:flex-start;">
          <div style="width:42px; height:42px; border-radius:12px; background:#fff3e8; display:flex; align-items:center; justify-content:center;">🗂️</div>
          <div style="flex:1; min-width:0;">
            <div style="display:flex; align-items:center; gap:8px; justify-content:space-between; margin-bottom:6px;">
              <div style="font-weight:800;">{{ $categoria->name ?? $categoria->nome }}</div>
              <span class="badge {{ $ativa ? 'orange' : 'gray' }}">{{ $ativa ? 'Ativa' : 'Inativa' }}</span>
            </div>
            <div class="muted">{{ $categoria->products_count ?? 0 }} produtos</div>
            @if($categoria->description ?? $categoria->descricao)
            <p class="muted" style="margin-top:8px; font-size:13px;">{{ Str::limit($categoria->description ?? $categoria->descricao, 80) }}</p>
            @endif
            <div style="margin-top:10px; display:flex; gap:8px;">
              <a class="pill" href="{{ route('dashboard.categories.edit', $categoria->id) }}">Editar</a>
              <a class="pill" href="{{ route('dashboard.products', ['category' => $categoria->id]) }}">Ver produtos</a>
            </div>
          </div>
        </div>
      </div>
    @empty
      <div class="muted">Nenhuma categoria cadastrada.</div>
    @endforelse
  </div>

  @if(method_exists($cats, 'links'))
  <div style="margin-top:16px;">{{ $cats->links() }}</div>
  @endif
</div>

@endsection