@extends('layouts.dashboard')

@section('title','Consignação #'.$c->id)

@section('page-title','Consignação #'.$c->id)

@section('page-subtitle', $c->parceiro->nome)

@section('page-actions')
  <a href="{{ route('consignacoes.edit',$c) }}" class="btn-primary">Editar</a>
@endsection

@section('stat-cards')
  <x-stat-card label="Status" :value="ucfirst($c->status)" />
  <x-stat-card label="Vendido" :value="'R$ '.number_format($c->total_vendido,2,',','.')" />
  <x-stat-card label="Comissão" :value="'R$ '.number_format($c->valor_comissao,2,',','.')" />
  <x-stat-card label="Líquido" :value="'R$ '.number_format($c->valor_liquido,2,',','.')" />
@endsection

@section('content')
  <div class="grid gap-4">
    <div class="card p-4">
      <div class="font-semibold mb-2">Dados</div>
      <div class="text-sm text-neutral-500">Envio: {{ optional($c->data_envio)->format('d/m/Y') }}</div>
      <div class="text-sm text-neutral-500">Retorno: {{ optional($c->data_retorno)->format('d/m/Y') ?: '—' }}</div>
      <div class="text-sm text-neutral-500">Comissão: {{ number_format($c->comissao_percent,2,',','.') }}%</div>
    </div>

    <div class="card overflow-hidden">
      <div class="px-4 py-3 border-b font-semibold">Itens</div>
      <div class="overflow-auto">
        <table class="table-compact">
          <thead>
            <tr>
              <th class="text-left">Produto</th>
              <th class="text-right">Enviada</th>
              <th class="text-right">Vendida</th>
              <th class="text-right">Devolvida</th>
              <th class="text-right">Preço</th>
              <th class="text-right">Subtotal enviado</th>
              <th class="text-right">Subtotal vendido</th>
            </tr>
          </thead>
          <tbody>
            @foreach($c->itens as $i)
            <tr>
              <td>{{ $i->produto->nome ?? ('#'.$i->produto_id) }}</td>
              <td class="text-right">{{ $i->qtd_enviada }}</td>
              <td class="text-right">{{ $i->qtd_vendida }}</td>
              <td class="text-right">{{ $i->qtd_devolvida }}</td>
              <td class="text-right">R$ {{ number_format($i->preco_unit,2,',','.') }}</td>
              <td class="text-right">R$ {{ number_format($i->subtotal_enviado,2,',','.') }}</td>
              <td class="text-right">R$ {{ number_format($i->subtotal_vendido,2,',','.') }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
@endsection
