@extends('layouts.dashboard')

@section('content')
<div class="pdv-page" x-data="PDV()">
  <div class="pdv-card">
    <div class="pdv-head">
      <h1>Ponto de Venda (PDV)</h1>
      <div class="pdv-actions"></div>
    </div>

    {{-- Cliente --}}
    <div class="grid grid-4">
      <div class="field col-2">
        <label>Cliente</label>
        <div class="combo" @click.outside="cbx.close('customer')">
          <input id="cli-search" class="input"
                 placeholder="buscar por nome, telefone, e-mail"
                 x-model="cbx.customer.query"
                 @input.debounce.250ms="cbx.search('customer')">
          <ul class="combo-list" x-show="cbx.customer.open">
            <template x-for="opt in cbx.customer.results" :key="opt.id">
              <li @click="cbx.pick('customer', opt)" x-text="opt.label"></li>
            </template>
            <li class="empty" x-show="!cbx.customer.results.length">Sem resultados</li>
            <li class="new" @click="cbx.newCustomer()">+ Novo cliente</li>
          </ul>
        </div>
      </div>
      <div class="field">
        <label>Nome</label>
        <input class="input" x-model="form.customer.name" placeholder="Nome">
      </div>
      <div class="field">
        <label>Telefone</label>
        <input class="input" x-model="form.customer.phone" placeholder="5511999999999">
      </div>
      <div class="field">
        <label>E-mail</label>
        <input class="input" x-model="form.customer.email" placeholder="email@dominio.com">
      </div>
    </div>

    {{-- CEP pequeno (vem depois do cliente) --}}
    <div class="grid grid-cep">
      <div class="field">
        <label>CEP <span class="req">*</span></label>
        <div class="cep-wrap">
          <input id="cep" x-model="form.cep"
                 @input.debounce.500ms="onCep()"
                 class="input cep sm"
                 placeholder="Somente números">
          <button type="button" class="btn btn-soft" @click="onCep()">Buscar</button>
          <small class="hint" x-text="cepHint"></small>
        </div>
      </div>
    </div>

    {{-- Endereço --}}
    <div class="grid grid-6">
      <div class="field col-2">
        <label>Rua <span class="req">*</span></label>
        <input class="input" x-model="form.address.street" placeholder="Rua">
      </div>
      <div class="field">
        <label>Nº <span class="req">*</span></label>
        <input class="input" x-model="form.address.number" placeholder="Nº">
      </div>
      <div class="field">
        <label>Compl.</label>
        <input class="input" x-model="form.address.complement" placeholder="Apto, Bloco…">
      </div>
      <div class="field">
        <label>Bairro</label>
        <input class="input" x-model="form.address.district" placeholder="Bairro">
      </div>
      <div class="field">
        <label>Cidade <span class="req">*</span></label>
        <input class="input" x-model="form.address.city" placeholder="Cidade">
      </div>
      <div class="field">
        <label>UF <span class="req">*</span></label>
        <input class="input" x-model="form.address.state" maxlength="2" placeholder="UF">
      </div>
      <div class="field">
        <button class="btn btn-soft" @click="saveAddress()">Salvar Endereço</button>
      </div>
    </div>

    {{-- Itens --}}
    <div class="grid grid-items">
      <div class="field col-3">
        <label>Produto</label>
        <div class="combo" @click.outside="cbx.close('product')">
          <input class="input" placeholder="buscar produto por nome ou SKU"
                 x-model="cbx.product.query" @input.debounce.250ms="cbx.search('product')">
          <ul class="combo-list" x-show="cbx.product.open">
            <template x-for="opt in cbx.product.results" :key="opt.id">
              <li @click="cbx.pick('product', opt)">
                <span x-text="opt.label"></span> <small x-text="opt.meta"></small>
              </li>
            </template>
            <li class="new" @click="cbx.avulso()">+ Item avulso</li>
          </ul>
        </div>
      </div>
      <div class="field">
        <label>Item avulso</label>
        <input class="input" x-model="avulso.desc" placeholder="Descrição">
      </div>
      <div class="field">
        <label>Preço</label>
        <input class="input t-right" x-model.number="avulso.price" placeholder="0,00">
      </div>
      <div class="field">
        <label>Qtd</label>
        <input class="input t-center" x-model.number="avulso.qty" min="1" value="1" type="number">
      </div>
      <div class="field">
        <label>&nbsp;</label>
        <button class="btn" @click="addAvulso()">Adicionar</button>
      </div>
    </div>

    <div class="table-wrapper">
      <table class="table">
        <thead>
        <tr>
          <th>Produto</th><th class="t-right">Preço</th>
          <th class="t-center">Qtd</th><th class="t-right">Total</th><th></th>
        </tr>
        </thead>
        <tbody>
        <template x-for="(it,idx) in cart" :key="it.key">
          <tr>
            <td x-text="it.name"></td>
            <td class="t-right" x-text="money(it.price)"></td>
            <td class="t-center">
              <input class="qty" type="number" min="1" x-model.number="it.qty" @change="recalc()">
            </td>
            <td class="t-right"><strong x-text="money(it.price*it.qty)"></strong></td>
            <td class="t-right"><button class="btn btn-soft" @click="remove(idx)">Remover</button></td>
          </tr>
        </template>
        <tr x-show="!cart.length"><td colspan="5" class="empty">Adicione itens…</td></tr>
        </tbody>
      </table>
    </div>

    {{-- Entrega & Observações --}}
    <div class="grid grid-1">
      <div class="field">
        <label>Entrega</label>
        <select class="input" x-model="form.delivery.option" @change="recalc()">
          <option value="">Selecione um endereço e itens para calcular opções…</option>
          <template x-for="opt in deliveryOptions" :key="opt.code">
            <option :value="opt.code" x-text="opt.label"></option>
          </template>
        </select>
      </div>
      <div class="field">
        <label>Observações</label>
        <textarea class="input" x-model="form.notes" rows="3" placeholder="Observações do pedido…"></textarea>
      </div>
    </div>

    {{-- Rodapé: totais + pagamento alinhados --}}
    <div class="footer-grid">
      <div class="resume">
        <div class="coupon">
          <div class="btn-group">
            <button class="btn btn-soft" @click="loadEligibleCoupons()">Selecionar</button>
            <button class="btn btn-soft" @click="toggleManual()">Digitar</button>
          </div>
          <select class="input" x-show="!manualCoupon" x-model="form.coupon.selected" @change="applySelectedCoupon()">
            <option value="">— cupons elegíveis —</option>
            <template x-for="c in coupons.eligible" :key="c.code">
              <option :value="c.code" x-text="c.label"></option>
            </template>
          </select>
          <div class="combo" x-show="manualCoupon">
            <input class="input" x-model="form.coupon.code" placeholder="Cupom">
            <button class="btn btn-soft" @click="applyCoupon()">Aplicar</button>
          </div>
        </div>
        <div class="totals">
          <div>Subtotal <strong x-text="money(totals.subtotal)"></strong></div>
          <div>Desconto <strong x-text="money(totals.discount)"></strong></div>
          <div>Entrega  <strong x-text="money(totals.delivery)"></strong></div>
        </div>
        <div class="grand">Total <strong x-text="money(totals.total)"></strong></div>
      </div>

      <div class="pay card">
        <label class="pay-title">Pagamento</label>
        <label class="radio"><input type="radio" name="pay" value="pix" x-model="form.payment" checked> PIX</label>
        <label class="radio"><input type="radio" name="pay" value="link_mp" x-model="form.payment"> Link Mercado Pago</label>
        <label class="radio"><input type="radio" name="pay" value="fiado" x-model="form.payment"> Fiado (lançar débito)</label>
      </div>
    </div>

    <div class="actions">
      <button class="btn btn-primary" @click="finalize()">Finalizar Pedido</button>
    </div>
  </div>

  {{-- Modal Pagamento (PIX/Link) permanece igual ao seu --}}
  @include('dashboard.pdv._modal_pagamento')

</div>

{{-- Endpoints p/ JS --}}
<script>
window.PDV_API = {
  'cep': '{{ route('dashboard.ajax.cep') }}',
  'customers': '{{ route('dashboard.ajax.customers') }}',
  'products': '{{ route('dashboard.ajax.products') }}',
  'coupons': '{{ route('dashboard.ajax.coupons.eligible') }}',
  'delivery': '{{ route('dashboard.ajax.delivery.options') }}',
  'finalize': '{{ route('dashboard.pdv.store') }}'
};
</script>
@endsection