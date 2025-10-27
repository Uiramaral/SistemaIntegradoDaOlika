@extends('layouts.dashboard')

@section('title','Novo Produto')

@section('page-title','Novo Produto')

@section('content')
  <form method="POST" action="{{ route('produtos.store') }}" class="card p-4 grid gap-4">
    @csrf
    <div class="grid gap-2">
      <label>Nome *</label>
      <input name="nome" value="{{ old('nome') }}" class="pill" required />
    </div>
    <div class="grid gap-2">
      <label>SKU</label>
      <input name="sku" value="{{ old('sku') }}" class="pill" />
    </div>
    <div class="grid gap-2">
      <label>Preço *</label>
      <input name="preco" type="number" step="0.01" min="0" value="{{ old('preco') }}" class="pill" required />
    </div>
    <label class="flex items-center gap-2"><input type="checkbox" name="ativo" value="1" checked> Ativo</label>
    <div class="flex gap-2">
      <button class="btn-primary">Salvar</button>
      <a href="{{ route('produtos.index') }}" class="pill">Cancelar</a>
    </div>
  </form>
@endsection
