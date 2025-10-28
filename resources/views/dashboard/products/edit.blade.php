@extends('layouts.app')
@section('content')
<div class="ol-card">
  <div class="ol-card__title">Editar Produto</div>
  <form action="{{ route('dashboard.products.update',$p->id) }}" method="post" class="ol-grid ol-grid--4">
    @csrf
    <input class="ol-input" name="name" value="{{ $p->name }}" placeholder="Nome" required>
    <input class="ol-input" name="price" type="number" step="0.01" value="{{ $p->price }}" placeholder="Preço" required>
    <div class="ol-grid--4" style="grid-column:1/-1">
      <textarea class="ol-textarea" name="description" placeholder="Descrição">{{ $p->description }}</textarea>
    </div>

    <div class="ol-grid--4" style="grid-column:1/-1">
      <label class="ol-label">Alérgenos</label>
      <table class="ol-table">
        <thead><tr><th>Alérgeno</th><th>Contém</th><th>Pode conter</th></tr></thead>
        <tbody>
          @foreach($allergens as $a)
            @php $s = $sel[$a->id] ?? null; @endphp
            <tr>
              <td>{{ $a->name }}</td>
              <td><input type="checkbox" name="allergen[{{ $a->id }}][present]" {{ $s && $s->present ? 'checked':'' }}></td>
              <td><input type="checkbox" name="allergen[{{ $a->id }}][may]" {{ $s && $s->may_contain ? 'checked':'' }}></td>
            </tr>
          @endforeach
        </tbody>
      </table>
      <label class="ol-flex items-center gap-2 mt-2">
        <input type="checkbox" name="force_gluten_warning" {{ $p->force_gluten_warning ? 'checked':'' }}>
        Sempre exibir: "Pode conter traços de glúten e farinha de trigo"
      </label>
      <div class="mt-2" style="color:#666">Prévia (ao salvar): {!! nl2br(e($p->allergens_description)) !!}</div>
    </div>

    <div style="grid-column:1/-1;display:flex;justify-content:flex-end">
      <button class="ol-cta">Salvar</button>
    </div>
  </form>
</div>

<div class="ol-card mt-3">
  <div class="ol-card__title">Fotos do Produto</div>
  <form action="{{ route('dashboard.products.images.store',$p->id) }}" method="post" enctype="multipart/form-data" class="ol-flex gap-2">
    @csrf
    <input type="file" name="image" class="ol-input" required>
    <label class="ol-flex items-center gap-2"><input type="checkbox" name="is_primary"> Definir como principal</label>
    <button class="ol-btn">Enviar</button>
  </form>

  <div class="ol-grid mt-3" style="grid-template-columns:repeat(5, 1fr);gap:12px">
    @foreach($images as $img)
      <div class="ol-card" style="padding:8px;text-align:center">
        <img src="{{ Storage::url($img->path) }}" style="width:100%;height:120px;object-fit:cover;border-radius:10px">
        <div class="mt-2">
          @if($img->is_primary)
            <span class="ol-badge">Principal</span>
          @else
            <form method="post" action="{{ route('dashboard.products.images.primary',[$p->id,$img->id]) }}" style="display:inline">@csrf<button class="ol-btn">Tornar principal</button></form>
          @endif
          <form method="post" action="{{ route('dashboard.products.images.destroy',[$p->id,$img->id]) }}" style="display:inline">@csrf @method('delete')<button class="ol-btn">Excluir</button></form>
        </div>
      </div>
    @endforeach
  </div>
</div>
@endsection
