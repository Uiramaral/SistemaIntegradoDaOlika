@extends('layouts.dashboard')

@section('title','Editar produto')

@section('content')
<div class="page product-edit" x-data="ProductEdit()">

  <div class="pe-header">
    <h1>Editar Produto</h1>
    <a href="{{ route('dashboard.products.index') }}" class="btn btn-soft">← Voltar</a>
  </div>

  {{-- Abas --}}
  <div class="pe-tabs" x-data="{tab:'dados'}">
    <nav class="pe-tabbar">
      <button :class="{active:tab==='dados'}" @click="tab='dados'">Dados</button>
      <button :class="{active:tab==='galeria'}" @click="tab='galeria'">Galeria</button>
      <button :class="{active:tab==='alergenos'}" @click="tab='alergenos'">Alérgenos</button>
      <button :class="{active:tab==='descricao'}" @click="tab='descricao'">Descrição/SEO</button>
    </nav>

    {{-- DADOS --}}
    <section x-show="tab==='dados'">
      <form method="POST" action="{{ route('dashboard.products.update',$product->id) }}" class="card">
        @csrf @method('PUT')

        <div class="grid g-3">
          <div class="field">
            <label>Nome</label>
            <input name="name" class="input" value="{{ old('name',$product->name) }}" required>
          </div>
          <div class="field">
            <label>SKU</label>
            <input name="sku" class="input" value="{{ old('sku',$product->sku) }}">
          </div>
          <div class="field">
            <label>Categoria</label>
            <select name="category_id" class="input">
              <option value="">—</option>
              @foreach($categories as $c)
                <option value="{{ $c->id }}" @selected(old('category_id',$product->category_id)==$c->id)>{{ $c->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="field">
            <label>Preço</label>
            <input name="price" class="input" value="{{ old('price',$product->price) }}" type="number" step="0.01" min="0" required>
          </div>
          <div class="field">
            <label>Estoque</label>
            <input name="stock" class="input" value="{{ old('stock',$product->stock) }}" type="number" min="0">
          </div>
          <div class="field checkline">
            <label class="chk"><input type="checkbox" name="gluten_free" value="1" @checked(old('gluten_free',$product->gluten_free))> Sem glúten</label>
            <label class="chk"><input type="checkbox" name="contamination_risk" value="1" @checked(old('contamination_risk',$product->contamination_risk))> Exibir aviso de contaminação</label>
          </div>

          <div class="field">
            <label class="chk">
              <input type="checkbox" name="auto_description" value="1">
              Gerar descrição automaticamente ao salvar (usa alérgenos e avisos)
            </label>
          </div>

          <div class="field">
            <label class="chk">
              <input type="checkbox" name="auto_label_description" value="1">
              Gerar <strong>descrição de rótulo</strong> automaticamente ao salvar (sem preço)
            </label>
          </div>
        </div>

        <div class="actions">
          <button class="btn btn-primary">Salvar</button>
        </div>
      </form>
    </section>

    {{-- GALERIA --}}
    <section x-show="tab==='galeria'">
      <div class="card">
        <div class="uploader" @drop.prevent="upload($event.dataTransfer.files)" @dragover.prevent>
          <p>Arraste fotos aqui ou</p>
          <label class="btn btn-soft">
            Selecionar
            <input type="file" accept="image/*" multiple hidden @change="upload($event.target.files)">
          </label>
        </div>

        <div class="gallery">
          @foreach($product->images as $img)
            <div class="g-item" data-id="{{ $img->id }}" data-primary="{{ $img->is_primary ? '1':'0' }}">
              <img src="{{ asset('storage/'.$img->path) }}" alt="">
              <div class="g-actions">
                <button class="btn btn-soft" @click.prevent="setPrimary({{ $img->id }})" :disabled="{{ $img->is_primary ? 'true':'false' }}">
                  {{ $img->is_primary ? 'Principal' : 'Tornar principal' }}
                </button>
                <div class="mv">
                  <button class="btn btn-soft" @click.prevent="move({{ $img->id }}, 'up')">↑</button>
                  <button class="btn btn-soft" @click.prevent="move({{ $img->id }}, 'down')">↓</button>
                </div>
                <button class="btn btn-danger" @click.prevent="removeImg({{ $img->id }})">Excluir</button>
              </div>
            </div>
          @endforeach
        </div>
      </div>
    </section>

    {{-- ALÉRGENOS --}}
    <section x-show="tab==='alergenos'">
      <form method="POST" action="{{ route('dashboard.products.update',$product->id) }}" class="card">
        @csrf @method('PUT')

        <div class="allergens">
          @foreach($allergens as $group => $items)
            <div class="alg-group">
              <div class="alg-title">{{ $group ?: 'Outros' }}</div>
              <div class="alg-list">
                @foreach($items as $a)
                  <label class="alg-item">
                    <input type="checkbox" name="allergens[]" value="{{ $a->id }}"
                      @checked( $product->allergens->contains('id',$a->id) )>
                    <span>{{ $a->name }}</span>
                  </label>
                @endforeach
              </div>
            </div>
          @endforeach
        </div>

        <div class="note">
          <small>⚠️ Mesmo em itens marcados como "Sem glúten", mantemos o aviso de possível contaminação cruzada quando habilitado.</small>
        </div>

        @if($product->allergen_text)
          <div class="info-line">
            <strong>Descrição automática:</strong> {{ $product->allergen_text }}
          </div>
        @endif

        <div class="actions">
          <button class="btn btn-primary">Salvar alérgenos</button>
        </div>
      </form>
    </section>

    {{-- DESCRIÇÃO / SEO --}}
    <section x-show="tab==='descricao'">
      <form method="POST" action="{{ route('dashboard.products.update',$product->id) }}" class="card">
        @csrf @method('PUT')

        <div class="grid g-2">
          <div class="field">
            <label>Descrição (manual)</label>
            <textarea name="description" class="input" rows="8" placeholder="Se deixar em branco ou marcar 'gerar automaticamente', criaremos um texto padrão com alérgenos e avisos.">{{ old('description',$product->description) }}</textarea>
          </div>
          <div class="field">
            <label>Prévia (gerada automaticamente)</label>
            <textarea class="input" rows="8" readonly id="auto-desc-preview">{{ $generated_preview }}</textarea>
            <small class="muted">Esta prévia considera flags e alérgenos marcados. Você pode usar a geração automática marcando a opção na aba <strong>Dados</strong>.</small>
          </div>
        </div>

        <div class="grid g-2" style="margin-top:12px">
          <div class="field">
            <label>Descrição para rótulo (curta, sem preço)</label>
            <textarea name="label_description" class="input" rows="6"
              placeholder="Ex.: Nome — Categoria. Produto sem glúten. Contém: ... . ⚠️ Pode conter traços de glúten.">{{ old('label_description',$product->label_description) }}</textarea>
          </div>
          <div class="field">
            <label>Prévia (rótulo — gerada)</label>
            <textarea class="input" rows="6" readonly id="auto-label-preview">{{ $generated_label_preview }}</textarea>
            <small class="muted">Prévia curta e direta, sem preço. Gere automaticamente marcando a opção na aba <strong>Dados</strong>.</small>
          </div>
        </div>

        <div class="grid g-2">
          <div class="field">
            <label>SEO Title</label>
            <input name="seo_title" class="input" maxlength="70" value="{{ old('seo_title',$product->seo_title) }}">
          </div>
          <div class="field">
            <label>SEO Description</label>
            <input name="seo_description" class="input" maxlength="160" value="{{ old('seo_description',$product->seo_description) }}">
          </div>
        </div>

        <div class="actions">
          <button class="btn btn-primary">Salvar descrição/SEO</button>
        </div>
      </form>
    </section>
  </div>
</div>

<script>
window.PROD_ROUTES = {
  upload:  @json(route('dashboard.products.images.store',$product->id)),
  primary: @json(route('dashboard.products.images.primary',[$product->id, 0])), // 0 será trocado no JS
  destroy: @json(route('dashboard.products.images.destroy',[$product->id, 0])),
  move:    @json(route('dashboard.products.images.move',   [$product->id, 0, 'up'])),
  csrf:    @json(csrf_token()),
};
</script>
@endsection