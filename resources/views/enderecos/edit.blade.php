@extends('layouts.dashboard')

@section('title','Editar Endereço #'.$endereco->id)

@section('page-title','Editar Endereço')

@section('content')
  <form method="POST" action="{{ route('enderecos.update',$endereco) }}" class="card p-4 grid gap-4">
    @csrf @method('PUT')

    <div class="grid gap-2">
      <label>Cliente *</label>
      <input name="cliente_id" value="{{ old('cliente_id',$endereco->cliente_id) }}" class="pill" required />
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div class="grid gap-2">
        <label>Destinatário *</label>
        <input name="nome_destinatario" value="{{ old('nome_destinatario',$endereco->nome_destinatario) }}" class="pill" required />
      </div>
      <div class="grid gap-2">
        <label>Telefone</label>
        <input name="telefone" value="{{ old('telefone',$endereco->telefone) }}" class="pill" />
      </div>
    </div>

    <div class="grid gap-2">
      <label>Endereço *</label>
      <input name="endereco" value="{{ old('endereco',$endereco->endereco) }}" class="pill" required />
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      <div class="grid gap-2">
        <label>Número</label>
        <input name="numero" value="{{ old('numero',$endereco->numero) }}" class="pill" />
      </div>
      <div class="grid gap-2">
        <label>Complemento</label>
        <input name="complemento" value="{{ old('complemento',$endereco->complemento) }}" class="pill" />
      </div>
      <div class="grid gap-2">
        <label>Referência</label>
        <input name="referencia" value="{{ old('referencia',$endereco->referencia) }}" class="pill" />
      </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
      <div class="grid gap-2">
        <label>Bairro *</label>
        <input name="bairro" value="{{ old('bairro',$endereco->bairro) }}" class="pill" required />
      </div>
      <div class="grid gap-2">
        <label>Cidade *</label>
        <input name="cidade" value="{{ old('cidade',$endereco->cidade) }}" class="pill" required />
      </div>
      <div class="grid gap-2">
        <label>UF</label>
        <input name="uf" value="{{ old('uf',$endereco->uf) }}" class="pill" maxlength="2" />
      </div>
      <div class="grid gap-2">
        <label>CEP</label>
        <input name="cep" value="{{ old('cep',$endereco->cep) }}" class="pill" />
      </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      <div class="grid gap-2">
        <label>Zona/Região</label>
        <input name="tax_zone" value="{{ old('tax_zone',$endereco->tax_zone) }}" class="pill" />
      </div>
      <div class="grid gap-2">
        <label>Distância (km)</label>
        <input type="number" step="0.1" min="0" name="distancia_km" value="{{ old('distancia_km',$endereco->distancia_km) }}" class="pill" />
      </div>
      <div class="grid gap-2">
        <label>Taxa base (R$)</label>
        <input type="number" step="0.01" min="0" name="taxa_base" value="{{ old('taxa_base',$endereco->taxa_base) }}" class="pill" />
      </div>
    </div>

    <label class="flex items-center gap-2"><input type="checkbox" name="padrao" value="1" @checked(old('padrao',$endereco->padrao))> Marcar como endereço padrão</label>

    <div class="flex gap-2">
      <button class="btn-primary">Salvar</button>
      <a href="{{ route('enderecos.show',$endereco) }}" class="pill">Cancelar</a>
    </div>
  </form>
@endsection
