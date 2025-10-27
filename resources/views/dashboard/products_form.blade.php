{{-- PÁGINA: Formulário de Produto (Criar/Editar) --}}
@extends('layouts.dashboard')

@section('title', ($product ? 'Editar' : 'Novo').' Produto — Dashboard Olika')

@section('content')

<div class="card">

  <h1 class="text-xl" style="font-weight:800;margin-bottom:16px">{{ $product ? '✏️ Editar Produto' : '➕ Novo Produto' }}</h1>

  @if(session('ok'))<div class="badge" style="background:#d1fae5;color:#065f46;margin-bottom:12px">{{ session('ok') }}</div>@endif

  <form method="POST" action="{{ $product ? route('dashboard.products.update', $product->id) : route('dashboard.products.store') }}" style="max-width:640px">
    @csrf
    @if($product) @method('PUT') @endif

    <label style="display:block;margin-bottom:12px">
      Nome <span style="color:#ef4444">*</span>
      <input name="name" class="card" value="{{ old('name', $product->name ?? '') }}" required>
    </label>

    <label style="display:block;margin-bottom:12px">
      Descrição
      <textarea name="description" class="card" rows="3">{{ old('description', $product->description ?? '') }}</textarea>
    </label>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
      <label style="display:block">
        Preço <span style="color:#ef4444">*</span>
        <input name="price" type="number" step="0.01" min="0" class="card" value="{{ old('price', $product->price ?? '') }}" required>
      </label>

      <label style="display:block">
        SKU
        <input name="sku" class="card" value="{{ old('sku', $product->sku ?? '') }}">
      </label>
    </div>

    <label style="display:block;margin-bottom:12px">
      Categoria
      <select name="category_id" class="card">
        <option value="">— Sem categoria —</option>
        @foreach($categories as $cat)
          <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id ?? '') == $cat->id ? 'selected' : '' }}>
            {{ $cat->name }}
          </option>
        @endforeach
      </select>
    </label>

    <label style="display:flex;gap:8px;align-items:center;margin-bottom:16px">
      <input type="checkbox" name="is_active" value="1" {{ old('is_active', $product->is_active ?? true) ? 'checked' : '' }}>
      Produto ativo
    </label>

    <div style="display:flex;gap:8px">
      <button type="submit" class="btn" style="background:#059669;color:#fff">💾 Salvar</button>
      <a href="{{ route('dashboard.products') }}" class="btn" style="background:#6b7280;color:#fff">❌ Cancelar</a>
    </div>
  </form>

</div>

@endsection

