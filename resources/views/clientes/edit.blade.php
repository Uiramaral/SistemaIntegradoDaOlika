@extends('layouts.dashboard')

@section('title','Editar Cliente')

@section('page-title','Editar Cliente')

@section('content')
  <form method="POST" action="{{ route('clientes.update',$cliente) }}" class="card p-4 grid gap-4">
    @csrf @method('PUT')
    <div class="grid gap-2">
      <label>Nome *</label>
      <input name="nome" value="{{ old('nome',$cliente->nome) }}" class="pill" required />
      @error('nome') <div class="text-sm text-neutral-500">{{ $message }}</div> @enderror
    </div>
    <div class="grid gap-2">
      <label>Telefone</label>
      <input name="telefone" value="{{ old('telefone',$cliente->telefone) }}" class="pill" />
    </div>
    <div class="grid gap-2">
      <label>E-mail</label>
      <input name="email" type="email" value="{{ old('email',$cliente->email) }}" class="pill" />
    </div>
    <div class="grid gap-2">
      <label>Endereço</label>
      <input name="endereco" value="{{ old('endereco',$cliente->endereco) }}" class="pill" />
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      <div class="grid gap-2">
        <label>Bairro</label>
        <input name="bairro" value="{{ old('bairro',$cliente->bairro) }}" class="pill" />
      </div>
      <div class="grid gap-2">
        <label>Cidade</label>
        <input name="cidade" value="{{ old('cidade',$cliente->cidade) }}" class="pill" />
      </div>
      <div class="grid gap-2">
        <label>CEP</label>
        <input name="cep" value="{{ old('cep',$cliente->cep) }}" class="pill" />
      </div>
    </div>
    <div class="flex gap-2">
      <button class="btn-primary">Salvar</button>
      <a href="{{ route('clientes.show',$cliente) }}" class="pill">Cancelar</a>
    </div>
  </form>
@endsection
