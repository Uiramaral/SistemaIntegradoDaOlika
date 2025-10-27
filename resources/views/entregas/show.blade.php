@extends('layouts.dashboard')

@section('title','Entrega #'.$entrega->id)

@section('page-title','Entrega')

@section('page-subtitle', '#'.$entrega->id.' — '.$entrega->cliente->nome)

@section('page-actions')
  <a href="{{ route('entregas.edit',$entrega) }}" class="btn-primary">Atualizar</a>
@endsection

@section('stat-cards')
  <x-stat-card label="Status" :value="ucfirst($entrega->status)" />
  <x-stat-card label="Janela" :value="optional($entrega->data_entrega)->format('d/m H:i') ?? '—'" />
  <x-stat-card label="Entrega" :value="$entrega->taxa_entrega ? ('R$ '.number_format($entrega->taxa_entrega,2,',','.')) : '—'" />
@endsection

@section('content')
  <div class="grid gap-4">
    <div class="card p-4">
      <div class="font-semibold mb-2">Endereço</div>
      <div class="text-sm text-neutral-500">{{ $entrega->endereco ?? $entrega->cliente->endereco }} — {{ $entrega->bairro ?? $entrega->cliente->bairro }} — {{ $entrega->cliente->cidade ?? '—' }}</div>
      <div class="text-sm text-neutral-500">CEP: {{ $entrega->cliente->cep ?? '—' }}</div>
      <div class="mt-2">
        <a class="pill" target="_blank" href="https://www.google.com/maps/search/?api=1&query={{ urlencode(($entrega->endereco ?? $entrega->cliente->endereco).' '.($entrega->bairro ?? $entrega->cliente->bairro).' '.($entrega->cliente->cidade ?? '')) }}">Abrir no Maps</a>
      </div>
    </div>

    <div class="card overflow-hidden">
      <div class="px-4 py-3 border-b font-semibold">Itens</div>
      <div class="overflow-auto">
        <table class="table-compact">
          <thead>
            <tr>
              <th class="text-left">Produto</th>
              <th class="text-left">Qtd</th>
              <th class="text-right">Subtotal</th>
            </tr>
          </thead>
          <tbody>
            @foreach($entrega->itens as $i)
            <tr>
              <td>{{ $i->produto->nome ?? ('#'.$i->produto_id) }}</td>
              <td>{{ $i->quantidade }}</td>
              <td class="text-right">R$ {{ number_format($i->subtotal,2,',','.') }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
@endsection
