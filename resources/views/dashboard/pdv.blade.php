@extends('layouts.dashboard')

@section('content')
@push('head')
  <link rel="stylesheet" href="{{ asset('css/style.css') }}">
  <link rel="stylesheet" href="{{ asset('css/style-mobile.css') }}" media="(max-width: 768px)">
@endpush

<div class="ol-card">
  <div class="ol-card__title">Ponto de Venda (PDV)</div>

  {{-- Cliente --}}
  <section class="ol-grid ol-grid--4">
    <input id="cli-search" class="ol-input" placeholder="buscar por nome, telefone, e-mail">
    <input id="cli-name" class="ol-input" placeholder="Nome">
    <input id="cli-phone" class="ol-input" placeholder="Telefone (E164)">
    <input id="cli-email" class="ol-input" placeholder="E-mail">
  </section>

  {{-- Endereço --}}
  <section class="ol-grid ol-grid--6 mt-3">
    <input id="addr-street" class="ol-input" placeholder="Rua *">
    <input id="addr-number" class="ol-input" placeholder="Nº *">
    <input id="addr-zip" class="ol-input" placeholder="CEP">
    <input id="addr-comp" class="ol-input" placeholder="Compl.">
    <input id="addr-nei" class="ol-input" placeholder="Bairro">
    <input id="addr-city" class="ol-input" placeholder="Cidade *">
    <input id="addr-uf" class="ol-input" placeholder="UF *">
    <button id="addr-save" class="ol-btn ol-btn--ghost ml-auto">Salvar Endereço</button>
  </section>

  {{-- Itens --}}
  <section class="mt-4">
    <div class="ol-flex gap-2">
      <input id="prod-search" class="ol-input flex-1" placeholder="buscar produto por nome ou SKU">
      <input id="free-title" class="ol-input w-48" placeholder="Item avulso (desc)">
      <input id="free-price" class="ol-input w-32" placeholder="Preço">
      <input id="free-qty" class="ol-input w-24" type="number" min="1" value="1" placeholder="Qtd">
      <button id="add-item" class="ol-btn">Adicionar</button>
    </div>
    <table class="ol-table mt-2" id="cart-table">
      <thead><tr><th>Produto</th><th>Preço</th><th>Qtd</th><th>Total</th><th></th></tr></thead>
      <tbody></tbody>
    </table>
  </section>

  {{-- Entrega / Observações --}}
  <section class="mt-4">
    <select id="delivery-slot" class="ol-select w-full">
      <option>Selecione um endereço e itens para calcular opções…</option>
    </select>
    <textarea id="order-notes" class="ol-textarea mt-2" placeholder="Observações do pedido"></textarea>
  </section>

  {{-- Resumo --}}
  <section class="mt-4">
    <div class="ol-flex items-center gap-2">
      <div class="ol-dropdown">
        <button class="ol-btn">Selecionar</button>
      </div>
      <button class="ol-btn ol-btn--ghost">Digitar</button>
      <select id="coupon-eligible" class="ol-select">
        <option>— cupons elegíveis —</option>
      </select>
      <button id="apply-eligible" class="ol-btn">Aplicar</button>
      <input id="coupon-code" class="ol-input w-48" placeholder="Cupom">
      <button id="apply-code" class="ol-btn">Aplicar</button>
    </div>
    <div class="ol-summary mt-3">
      <div>Subtotal <strong id="sum-sub">R$ 0,00</strong></div>
      <div>Desconto <strong id="sum-discount">R$ 0,00</strong></div>
      <div>Entrega <strong id="sum-ship">R$ 0,00</strong></div>
      <div class="ol-summary__total">Total <strong id="sum-total">R$ 0,00</strong></div>
    </div>
  </section>

  {{-- Pagamento --}}
  <section class="mt-4">
    <div class="ol-radio-group">
      <label><input type="radio" name="pay" value="pix" checked> PIX</label>
      <label><input type="radio" name="pay" value="link-mp"> Link Mercado Pago</label>
      <label><input type="radio" name="pay" value="fiado"> Fiado (lançar débito)</label>
    </div>
    <button id="finalize" class="ol-cta mt-3">Finalizar Pedido</button>
  </section>
</div>

{{-- rotas/CSRF para o JS --}}
<script>
  window.Olika = {
    csrf: "{{ csrf_token() }}",
    routes: { pdvStore: "{{ route('dashboard.pdv.store') }}" }
  };
</script>
@endsection

@push('page-scripts')
  <script src="{{ asset('js/pdv.js') }}"></script>
@endpush