{{-- PÁGINA: Formulário de Categoria (Criar/Editar) --}}
@extends('layouts.dashboard')

@section('title', ($category ? 'Editar' : 'Nova').' Categoria — Dashboard Olika')

@section('content')

<div class="card">

  <h1 class="text-xl" style="font-weight:800;margin-bottom:16px">{{ $category ? '✏️ Editar Categoria' : '➕ Nova Categoria' }}</h1>

  <form method="POST" action="{{ $category ? route('dashboard.categories.update', $category->id) : route('dashboard.categories.store') }}" style="max-width:640px">
    @csrf
    @if($category) @method('PUT') @endif

    <label style="display:block;margin-bottom:12px">
      Nome <span style="color:#ef4444">*</span>
      <input name="name" class="card" value="{{ old('name', $category->name ?? '') }}" required>
    </label>

    <label style="display:block;margin-bottom:12px">
      Slug (URL)
      <input name="slug" class="card" value="{{ old('slug', $category->slug ?? '') }}" placeholder="será gerado automaticamente">
      <small style="color:#6b7280">Deixe vazio para auto-gerar</small>
    </label>

    <label style="display:block;margin-bottom:12px">
      Descrição
      <textarea name="description" class="card" rows="4">{{ old('description', $category->description ?? '') }}</textarea>
    </label>

    <label style="display:block;margin-bottom:12px">
      Imagem (URL)
      <input name="image" class="card" value="{{ old('image', $category->image ?? '') }}" placeholder="https://...">
    </label>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
      <label style="display:block">
        Ordem de Exibição
        <input name="display_order" type="number" class="card" value="{{ old('display_order', $category->display_order ?? 0) }}">
      </label>
    </div>

    <label style="display:flex;gap:8px;align-items:center;margin-bottom:16px">
      <input type="checkbox" name="is_active" value="1" {{ old('is_active', $category->is_active ?? true) ? 'checked' : '' }}>
      Categoria ativa
    </label>

    <div style="display:flex;gap:8px">
      <button type="submit" class="btn" style="background:#059669;color:#fff">💾 Salvar</button>
      <a href="{{ route('dashboard.categories') }}" class="btn" style="background:#6b7280;color:#fff">❌ Cancelar</a>
    </div>
  </form>

</div>

@endsection

