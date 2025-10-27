@extends('layouts.dashboard')

@section('title','Novo Cupom')

@section('page-title','Novo Cupom')

@section('content')
  <form method="POST" action="{{ route('cupons.store') }}" class="card p-4 grid gap-4">
    @csrf
    <div class="grid gap-2">
      <label>Código *</label>
      <input name="codigo" value="{{ old('codigo') }}" class="pill" required />
      @error('codigo') <div class="text-sm text-neutral-500">{{ $message }}</div> @enderror
    </div>
    <div class="grid gap-2">
      <label>Descrição</label>
      <input name="descricao" value="{{ old('descricao') }}" class="pill" />
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      <div class="grid gap-2">
        <label>Tipo</label>
        <select name="tipo" class="pill">
          <option value="percent" @selected(old('tipo')==='percent')>Percentual (%)</option>
          <option value="valor" @selected(old('tipo')==='valor')>Valor fixo (R$)</option>
        </select>
      </div>
      <div class="grid gap-2">
        <label>Valor *</label>
        <input name="valor" type="number" step="0.01" min="0" value="{{ old('valor') }}" class="pill" required />
      </div>
      <div class="grid gap-2">
        <label>Mínimo do pedido</label>
        <input name="minimo_pedido" type="number" step="0.01" min="0" value="{{ old('minimo_pedido') }}" class="pill" />
      </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      <div class="grid gap-2">
        <label>Início</label>
        <input name="validade_inicio" type="date" value="{{ old('validade_inicio') }}" class="pill" />
      </div>
      <div class="grid gap-2">
        <label>Fim</label>
        <input name="validade_fim" type="date" value="{{ old('validade_fim') }}" class="pill" />
      </div>
      <div class="grid gap-2">
        <label>Uso máximo</label>
        <input name="uso_maximo" type="number" min="1" value="{{ old('uso_maximo') }}" class="pill" />
      </div>
    </div>

    <label class="flex items-center gap-2"><input type="checkbox" name="ativo" value="1" checked> Ativo</label>

    <div class="flex gap-2">
      <button class="btn-primary">Salvar</button>
      <a href="{{ route('cupons.index') }}" class="pill">Cancelar</a>
    </div>
  </form>
@endsection
