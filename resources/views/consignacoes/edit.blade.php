@extends('layouts.dashboard')

@section('title','Editar Consignação #'.$c->id)

@section('page-title','Editar Consignação #'.$c->id)

@section('content')
  <form method="POST" action="{{ route('consignacoes.update',$c) }}" class="card p-4 grid gap-4">
    @csrf @method('PUT')

    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
      <div class="grid gap-2">
        <label>Parceiro *</label>
        <select name="parceiro_id" class="pill" required>
          @foreach($parceiros as $p)
            <option value="{{ $p->id }}" @selected($c->parceiro_id==$p->id)>{{ $p->nome }}</option>
          @endforeach
        </select>
      </div>
      <div class="grid gap-2">
        <label>Status</label>
        <select name="status" class="pill">
          @foreach(['aberta'=>'Aberta','liquidada'=>'Liquidada','cancelada'=>'Cancelada'] as $k=>$v)
            <option value="{{ $k }}" @selected($c->status===$k)>{{ $v }}</option>
          @endforeach
        </select>
      </div>
      <div class="grid gap-2">
        <label>Envio</label>
        <input type="date" name="data_envio" class="pill" value="{{ optional($c->data_envio)->format('Y-m-d') }}" />
      </div>
      <div class="grid gap-2">
        <label>Retorno</label>
        <input type="date" name="data_retorno" class="pill" value="{{ optional($c->data_retorno)->format('Y-m-d') }}" />
      </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
      <div class="grid gap-2">
        <label>Comissão (%)</label>
        <input type="number" step="0.01" min="0" max="100" name="comissao_percent" class="pill" value="{{ $c->comissao_percent }}" />
      </div>
      <div class="grid gap-2 sm:col-span-3">
        <label>Observações</label>
        <input name="observacoes" class="pill" value="{{ $c->observacoes }}" />
      </div>
    </div>

    <div class="card p-4">
      <div class="font-semibold mb-2">Itens</div>
      @foreach($c->itens as $idx=>$i)
      <div class="grid grid-cols-1 sm:grid-cols-6 gap-2 mb-2">
        <select name="itens[{{ $idx }}][produto_id]" class="pill">
          <option value="">Produto…</option>
          @foreach($produtos as $p)
            <option value="{{ $p->id }}" data-preco="{{ $p->preco }}" @selected($i->produto_id==$p->id)>{{ $p->nome }}</option>
          @endforeach
        </select>
        <input type="number" name="itens[{{ $idx }}][qtd_enviada]" min="1" value="{{ $i->qtd_enviada }}" class="pill" placeholder="Enviada" />
        <input type="number" name="itens[{{ $idx }}][qtd_vendida]" min="0" value="{{ $i->qtd_vendida }}" class="pill" placeholder="Vendida" />
        <input type="number" name="itens[{{ $idx }}][qtd_devolvida]" min="0" value="{{ $i->qtd_devolvida }}" class="pill" placeholder="Devolvida" />
        <input type="number" step="0.01" min="0" name="itens[{{ $idx }}][preco_unit]" class="pill" placeholder="Preço (R$)" value="{{ $i->preco_unit }}" />
        <div class="pill" style="justify-content:center;">Linha {{ $idx+1 }}</div>
      </div>
      @endforeach
    </div>

    <div class="flex gap-2">
      <button class="btn-primary">Salvar</button>
      <a href="{{ route('consignacoes.show',$c) }}" class="pill">Cancelar</a>
    </div>
  </form>
@endsection
