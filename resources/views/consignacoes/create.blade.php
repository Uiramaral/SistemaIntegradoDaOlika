@extends('layouts.dashboard')

@section('title','Nova Consignação')

@section('page-title','Nova Consignação')

@section('content')
  <form method="POST" action="{{ route('consignacoes.store') }}" class="card p-4 grid gap-4">
    @csrf

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      <div class="grid gap-2">
        <label>Parceiro *</label>
        <select name="parceiro_id" class="pill" required>
          <option value="">Selecione…</option>
          @foreach($parceiros as $p)
            <option value="{{ $p->id }}">{{ $p->nome }}</option>
          @endforeach
        </select>
      </div>
      <div class="grid gap-2">
        <label>Data de envio</label>
        <input type="date" name="data_envio" class="pill" value="{{ date('Y-m-d') }}" />
      </div>
      <div class="grid gap-2">
        <label>Comissão (%)</label>
        <input type="number" step="0.01" min="0" max="100" name="comissao_percent" class="pill" value="0" />
      </div>
    </div>

    <div class="card p-4">
      <div class="font-semibold mb-2">Itens</div>
      <div id="itens">
        <div class="grid grid-cols-1 sm:grid-cols-6 gap-2 mb-2">
          <select name="itens[0][produto_id]" class="pill">
            <option value="">Produto…</option>
            @foreach($produtos as $p)
              <option value="{{ $p->id }}" data-preco="{{ $p->preco }}">{{ $p->nome }}</option>
            @endforeach
          </select>
          <input type="number" name="itens[0][qtd_enviada]" min="1" value="1" class="pill" placeholder="Enviada" />
          <input type="number" name="itens[0][qtd_vendida]" min="0" value="0" class="pill" placeholder="Vendida" />
          <input type="number" name="itens[0][qtd_devolvida]" min="0" value="0" class="pill" placeholder="Devolvida" />
          <input type="number" step="0.01" min="0" name="itens[0][preco_unit]" class="pill" placeholder="Preço (R$)" />
          <div class="pill" style="justify-content:center;">Linha 1</div>
        </div>
      </div>
      <small class="text-neutral-500">Dica: preencha o preço para sobrescrever o preço padrão.</small>
    </div>

    <div class="grid gap-2">
      <label>Observações</label>
      <input name="observacoes" class="pill" />
    </div>

    <div class="flex gap-2">
      <button class="btn-primary">Salvar</button>
      <a href="{{ route('consignacoes.index') }}" class="pill">Cancelar</a>
    </div>
  </form>
@endsection
