@extends('layouts.dashboard')

@section('title','Atualizar Entrega #'.$entrega->id)

@section('page-title','Atualizar Entrega')

@section('page-subtitle', '#'.$entrega->id.' — '.$entrega->cliente->nome)

@section('content')
  <form method="POST" action="{{ route('entregas.update',$entrega) }}" class="card p-4 grid gap-4">
    @csrf @method('PUT')
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div class="grid gap-2">
        <label>Status</label>
        <select name="status" class="pill">
          @foreach(['agendado'=>'Agendado','producao'=>'Produção','entrega'=>'Em entrega','concluido'=>'Concluído','cancelado'=>'Cancelado'] as $k=>$v)
            <option value="{{ $k }}" @selected(old('status',$entrega->status)===$k)>{{ $v }}</option>
          @endforeach
        </select>
      </div>
      <div class="grid gap-2">
        <label>Data/Hora</label>
        <input type="datetime-local" name="data_entrega" class="pill" value="{{ old('data_entrega', optional($entrega->data_entrega)->format('Y-m-d\TH:i')) }}" />
      </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      <div class="grid gap-2">
        <label>Taxa de entrega</label>
        <input type="number" step="0.01" min="0" name="taxa_entrega" class="pill" value="{{ old('taxa_entrega',$entrega->taxa_entrega) }}" />
      </div>
      <div class="grid gap-2">
        <label class="flex items-center gap-2"><input type="checkbox" name="pagamento_na_entrega" value="1" @checked(old('pagamento_na_entrega',$entrega->pagamento_na_entrega))> Pagamento na entrega</label>
      </div>
      <div class="grid gap-2">
        <label class="flex items-center gap-2"><input type="checkbox" name="em_rota" value="1" @checked(old('em_rota',$entrega->em_rota ?? false))> Em rota</label>
      </div>
    </div>

    <div class="flex gap-2">
      <button class="btn-primary">Salvar</button>
      <a href="{{ route('entregas.show',$entrega) }}" class="pill">Cancelar</a>
    </div>
  </form>
@endsection
