@extends('layouts.dashboard')

@section('title','Produto: '.$produto->nome)

@section('page-title','Produto')

@section('page-subtitle', $produto->nome)

@section('page-actions')
  <form method="POST" action="{{ route('produtos.destroy',$produto) }}" onsubmit="return confirm('Remover produto?');">
    @csrf @method('DELETE')
    <button class="pill">Remover</button>
  </form>
  <a href="{{ route('produtos.edit',$produto) }}" class="btn-primary">Editar</a>
@endsection

@section('stat-cards')
  <x-stat-card label="Preço" :value="'R$ '.number_format($produto->preco,2,',','.')" />
  <x-stat-card label="Status" :value="$produto->ativo ? 'Ativo' : 'Inativo'" />
@endsection

@section('content')
  <div class="card p-4">
    <div class="font-semibold">Detalhes</div>
    <div class="text-sm text-neutral-500">SKU: {{ $produto->sku ?: '-' }}</div>
  </div>
@endsection
