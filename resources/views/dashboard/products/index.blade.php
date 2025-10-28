@extends('layouts.app')
@section('content')
<div class="ol-card">
  <div class="ol-card__title">Produtos</div>
  <form class="ol-flex gap-2">
    <input class="ol-input flex-1" name="q" value="{{ $q }}" placeholder="Buscar produtos...">
    <button class="ol-btn">Buscar</button>
  </form>

  <div class="ol-grid mt-3" style="grid-template-columns: repeat(3, 1fr); gap:16px">
    @foreach($products as $p)
      <div class="ol-card" style="padding:14px">
        <div class="ol-flex items-center gap-2">
          <img src="{{ $images[$p->id]->path ? Storage::url($images[$p->id]->path) : asset('img/placeholder.png') }}"
               alt="" style="width:56px;height:56px;border-radius:12px;object-fit:cover;border:1px solid #eee">
          <div class="flex-1">
            <div style="font-weight:700">{{ $p->name }}</div>
            <div style="color:#666">R$ {{ number_format($p->price,2,',','.') }}</div>
          </div>
        </div>
        <div class="ol-flex gap-2 mt-2">
          <a href="{{ route('dashboard.products.edit',$p->id) }}" class="ol-btn">Editar</a>
        </div>
      </div>
    @endforeach
  </div>
  <div class="mt-3">{{ $products->withQueryString()->links() }}</div>
</div>
@endsection
