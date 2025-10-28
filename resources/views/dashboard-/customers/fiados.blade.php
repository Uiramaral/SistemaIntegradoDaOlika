@extends('layouts.dashboard')

@section('title','Fiados - '.$customer->name)

@section('content')
<h1 class="text-2xl font-bold mb-4">Fiados — {{ $customer->name }}</h1>

<div class="card mb-4">
  <div class="flex flex-wrap items-center gap-3 text-sm">
    <div>
      <b>Em aberto:</b>
      <span class="{{ $saldoAberto>0?'text-red-700':'text-green-700' }}">
        R$ {{ number_format($saldoAberto,2,',','.') }}
      </span>
    </div>

    <div class="opacity-70">
      <b>Saldo total (histórico):</b>
      <span class="{{ $saldo>0?'text-red-700':'text-green-700' }}">
        R$ {{ number_format($saldo,2,',','.') }}
      </span>
    </div>
  </div>
</div>

<table class="table">
  <thead>
    <tr><th>#</th><th>Tipo</th><th class="text-right">Valor</th><th>Status</th><th>Descrição</th><th></th></tr>
  </thead>
  <tbody>
    @foreach($debts as $d)
      <tr>
        <td>{{ $d->id }}</td>
        <td>{{ strtoupper($d->type) }}</td>
        <td class="text-right">R$ {{ number_format($d->amount,2,',','.') }}</td>
        <td>{{ strtoupper($d->status) }}</td>
        <td>{{ $d->description }}</td>
        <td class="text-right">
          @if($d->type === 'debit' && $d->status === 'open')
            <button class="btn btn-xs" onclick="baixar({{ $d->id }})">Dar baixa</button>
          @endif
        </td>
      </tr>
    @endforeach
  </tbody>
</table>
@endsection

@push('scripts')
<script>
async function baixar(id){
  if(!confirm('Confirmar baixa deste fiado?')) return;
  const r = await fetch('/api/fiados/'+id+'/baixa',{method:'POST'});
  const j = await r.json();
  if(j.ok) location.reload(); else alert(j.message||'Erro ao baixar');
}
</script>
@endpush
