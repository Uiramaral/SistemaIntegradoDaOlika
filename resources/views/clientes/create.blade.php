@extends('layouts.dashboard')

@section('title','Novo Cliente')

@section('page-title','Novo Cliente')

@section('content')
  <form method="POST" action="{{ route('clientes.store') }}" class="card p-4 grid gap-4">
    @csrf
    <div class="grid gap-2">
      <label>Nome *</label>
      <input name="nome" value="{{ old('nome') }}" class="pill" required />
      @error('nome') <div class="text-sm text-neutral-500">{{ $message }}</div> @enderror
    </div>
    <div class="grid gap-2">
      <label>Telefone</label>
      <input name="telefone" value="{{ old('telefone') }}" class="pill" />
    </div>
    <div class="grid gap-2">
      <label>E-mail</label>
      <input name="email" type="email" value="{{ old('email') }}" class="pill" />
    </div>
    <div class="grid gap-2">
      <label>Endereço</label>
      <input name="endereco" value="{{ old('endereco') }}" class="pill" />
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      <div class="grid gap-2">
        <label>Bairro</label>
        <input name="bairro" value="{{ old('bairro') }}" class="pill" />
      </div>
      <div class="grid gap-2">
        <label>Cidade</label>
        <input name="cidade" value="{{ old('cidade') }}" class="pill" />
      </div>
      <div class="grid gap-2">
        <label>CEP</label>
        <input name="cep" value="{{ old('cep') }}" class="pill" />
      </div>
    </div>
    <div class="flex gap-2">
      <button class="btn-primary">Salvar</button>
      <a href="{{ route('clientes.index') }}" class="pill">Cancelar</a>
    </div>
  </form>
@endsection
