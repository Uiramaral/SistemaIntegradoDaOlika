@extends('layouts.dashboard')

@section('title','Novo Pedido')

@section('page-title','Novo Pedido')

@section('content')
  <form method="POST" action="{{ route('pedidos.store') }}" class="card p-4 grid gap-4">
    @csrf

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      <div class="grid gap-2">
        <label>Cliente *</label>
        <select name="cliente_id" class="pill" required>
          <option value="">Selecione…</option>
          @foreach($clientes as $c)
            <option value="{{ $c->id }}">{{ $c->nome }}</option>
          @endforeach
        </select>
      </div>
      <div class="grid gap-2">
        <label>Status *</label>
        <select name="status" class="pill" required>
          @foreach(['agendado'=>'Agendado','producao'=>'Produção','entrega'=>'Entrega','concluido'=>'Concluído','cancelado'=>'Cancelado'] as $k=>$v)
            <option value="{{ $k }}">{{ $v }}</option>
          @endforeach
        </select>
      </div>
      <div class="grid gap-2">
        <label>Data/Hora de entrega</label>
        <input type="datetime-local" name="data_entrega" class="pill" />
      </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      <div class="grid gap-2">
        <label>Taxa de entrega</label>
        <input type="number" step="0.01" min="0" name="taxa_entrega" class="pill" value="0" />
      </div>
      <div class="grid gap-2 sm:col-span-2">
        <label>Observações</label>
        <input name="observacoes" class="pill" />
      </div>
    </div>

    <div class="card p-4">
      <div class="font-semibold mb-2">Itens</div>
      @include('pedidos._itens_dynamic', ['produtos' => $produtos])
    </div>

    <div class="flex gap-2">
      <button class="btn-primary">Salvar</button>
      <a href="{{ route('pedidos.index') }}" class="pill">Cancelar</a>
    </div>
  </form>

  @php
    $__cuponsPreview = \App\Models\Cupom::where('ativo',true)->get(['codigo','tipo','valor','validade_inicio','validade_fim','minimo_pedido','ativo']);
  @endphp
  
  <script>
    window.__CUPONS__ = @json($__cuponsPreview);
  </script>
@endsection
