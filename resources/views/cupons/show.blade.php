@extends('layouts.dashboard')

@section('title','Cupom: '.$cupom->codigo)

@section('page-title','Cupom')

@section('page-subtitle', $cupom->codigo)

@section('page-actions')
  <form method="POST" action="{{ route('cupons.destroy',$cupom) }}" onsubmit="return confirm('Remover cupom?');">
    @csrf @method('DELETE')
    <button class="pill">Remover</button>
  </form>
  <a href="{{ route('cupons.edit',$cupom) }}" class="btn-primary">Editar</a>
@endsection

@section('stat-cards')
  <x-stat-card label="Tipo" :value="$cupom->tipo === 'percent' ? 'Percentual' : 'Valor'" />
  <x-stat-card label="Valor" :value="$cupom->tipo === 'percent' ? (number_format($cupom->valor,0).' %') : ('R$ '.number_format($cupom->valor,2,',','.'))" />
  <x-stat-card label="Status" :value="($cupom->ativo ? 'Ativo' : 'Inativo')" />
@endsection

@section('content')
  <div class="grid gap-4">
    <div class="card p-4">
      <div class="font-semibold mb-2">Regras</div>
      <div class="text-sm text-neutral-500">Mínimo do pedido: {{ $cupom->minimo_pedido ? 'R$ '.number_format($cupom->minimo_pedido,2,',','.') : '—' }}</div>
      <div class="text-sm text-neutral-500">Uso máximo: {{ $cupom->uso_maximo ?: '—' }}</div>
      <div class="text-sm text-neutral-500">Validade: 
        @if($cupom->validade_inicio || $cupom->validade_fim)
          {{ $cupom->validade_inicio?->format('d/m/Y') ?? '—' }} — {{ $cupom->validade_fim?->format('d/m/Y') ?? '—' }}
        @else — @endif
      </div>
    </div>
  </div>
@endsection
