@extends('layouts.dashboard')

@section('title','Produtos — Dashboard Olika')

@section('page-title','Produtos')

@section('page-subtitle','Gerencie o cardápio do seu restaurante')

@section('content')

@if(session('ok'))<div class="badge" style="background:#d1fae5;color:#065f46;margin-bottom:12px">{{ session('ok') }}</div>@endif
@if(session('error'))<div class="badge" style="background:#fee2e2;color:#991b1b;margin-bottom:12px">{{ session('error') }}</div>@endif

<div class="toolbar">
  <div class="grow">
    <h1 class="page-title">Produtos</h1>
    <p class="page-sub">Gerencie o cardápio do seu restaurante</p>
  </div>
  <div class="actions">
    <a class="btn ghost" href="{{ route('dashboard.index') }}">Voltar</a>
    <a class="btn primary" href="{{ route('dashboard.products.create') }}">+ Novo Produto</a>
  </div>
</div>

{{-- Campo de busca --}}
<div class="card">
  <div class="input-search">
    <svg class="icon" width="18" height="18" viewBox="0 0 24 24"><path d="M21 21l-4.35-4.35" stroke="#999" stroke-width="1.5" fill="none"/><circle cx="10.5" cy="10.5" r="7" stroke="#999" stroke-width="1.5" fill="none"/></svg>
    <input class="inp" placeholder="Buscar produtos..." name="q" value="{{ request('q') }}">
  </div>

  {{-- Grid de produtos --}}
  <div class="grid-2" style="grid-template-columns: repeat(3, 1fr); gap:16px; margin-top:16px;">
    @forelse($products as $p)
      @php $ativo = (bool)($p->active ?? $p->is_active ?? true); @endphp
      <div class="card" style="border-radius:14px;">
        <div style="display:flex; gap:10px; align-items:flex-start;">
          <div style="width:42px; height:42px; border-radius:12px; background:#fff3e8; display:flex; align-items:center; justify-content:center;">📦</div>
          <div style="flex:1; min-width:0;">
            <div style="display:flex; align-items:center; gap:8px; justify-content:space-between;">
              <div style="font-weight:800; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                {{ $p->name ?? $p->nome }}
              </div>
              <span class="badge {{ $ativo ? 'orange' : 'gray' }}">{{ $ativo ? 'Ativo' : 'Inativo' }}</span>
            </div>
            <div class="muted">{{ $p->category_name ?? $p->categoria->nome ?? '—' }}</div>
            <div style="display:flex; gap:14px; align-items:center; margin-top:6px;">
              <div style="font-weight:800;">R$ {{ number_format($p->price ?? $p->preco ?? 0,2,',','.') }}</div>
              <div class="muted">Estoque: {{ $p->stock ?? $p->estoque ?? 0 }}</div>
            </div>
            <div style="margin-top:10px;">
              <a class="pill" href="{{ route('dashboard.products.edit',$p->id) }}">Editar</a>
            </div>
          </div>
        </div>
      </div>
    @empty
      <div class="muted">Nenhum produto cadastrado.</div>
    @endforelse
  </div>

  @if(method_exists($products, 'links'))
  <div style="margin-top:16px;">{{ $products->links() }}</div>
  @endif
</div>

@endsection