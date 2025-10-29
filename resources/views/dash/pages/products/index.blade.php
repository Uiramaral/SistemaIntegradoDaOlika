@extends('layouts.admin')

@section('title', 'Produtos')
@section('page_title', 'Produtos')

@section('content')
<div class="container-page">
  <div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">Produtos</h1>
    <a href="{{ route('dashboard.products.create') }}" class="btn btn-primary">
      <i class="fas fa-plus mr-2"></i> Novo Produto
    </a>
  </div>

  @if(session('status'))
    <x-alert type="success">{{ session('status') }}</x-alert>
  @endif

  @if(session('error'))
    <x-alert type="error">{{ session('error') }}</x-alert>
  @endif

  @if(isset($products) && $products->isEmpty())
    <x-card>
      <div class="text-center py-8">
        <i class="fas fa-utensils text-4xl text-gray-400 mb-4"></i>
        <p class="text-gray-500 text-lg">Nenhum produto cadastrado.</p>
        <p class="text-gray-400 text-sm mt-2">Comece adicionando seu primeiro produto.</p>
      </div>
    </x-card>
  @else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      @foreach($products ?? [] as $product)
        <x-card class="flex gap-4 p-4">
          <img src="{{ $product->cover_image ?? '/img/placeholder.png' }}" class="w-20 h-20 rounded-xl object-cover border" alt="Produto">
          <div class="flex-1">
            <div class="flex items-center gap-2 mb-2">
              <h3 class="font-bold text-base">{{ $product->name }}</h3>
              @if($product->gluten_free ?? false)
                <x-badge type="warning">
                  <i class="fas fa-wheat-awn-circle-exclamation mr-1"></i> Sem glúten
                </x-badge>
              @endif
              @if(!($product->is_active ?? true))
                <x-badge type="gray">Inativo</x-badge>
              @else
                <x-badge type="success">Ativo</x-badge>
              @endif
            </div>
            <div class="text-sm text-slate-500 mb-2">{{ $product->category->name ?? '—' }}</div>
            <div class="mt-2 text-sm flex items-center gap-6">
              <strong>R$ {{ number_format($product->price ?? 0, 2, ',', '.') }}</strong>
              <span>Estoque: {{ $product->stock ?? 0 }}</span>
            </div>
            <div class="mt-3 flex gap-2">
              <form method="POST" action="{{ route('dashboard.products.toggle', $product) }}" class="inline">
                @csrf
                <x-button variant="secondary" size="sm" type="submit">
                  {{ ($product->is_active ?? true) ? 'Inativar' : 'Ativar' }}
                </x-button>
              </form>
              <a href="{{ route('dashboard.products.edit', $product) }}" class="btn btn-primary btn-sm">
                <i class="fas fa-edit mr-1"></i> Editar
              </a>
            </div>
          </div>
        </x-card>
      @endforeach
    </div>

    @if(isset($products) && $products->hasPages())
      <div class="mt-6">
        {{ $products->links() }}
      </div>
    @endif
  @endif
</div>
@endsection