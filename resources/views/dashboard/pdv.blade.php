{{-- resources/views/dashboard/pdv.blade.php --}}
@extends('layouts.app')

@section('title', 'Ponto de Venda (PDV)')

@section('header-actions')
  {{-- vazio: sem "Baixar Layout" e sem "Novo Status" nesta página --}}
@endsection

@section('content')
<div class="px-6 py-6" x-data="pdvPage()" x-init="init()">
  <h1 class="text-3xl font-semibold tracking-tight text-gray-900 mb-6">Ponto de Venda (PDV)</h1>
  
  {{-- CLIENTE --}}
  <div class="rounded-2xl border border-gray-200 bg-white p-5 mb-6">
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-lg font-semibold text-gray-900">Cliente</h2>
      <div class="flex gap-2" x-show="!customer.id">
        <button type="button" class="rounded-xl border border-gray-200 px-3 py-2 text-sm hover:bg-gray-50" @click="clearCustomer()">Limpar</button>
        <button type="button" class="rounded-xl bg-emerald-600 text-white px-3 py-2 text-sm hover:bg-emerald-700" @click="openNewCustomer()">+ Novo Cliente</button>
      </div>
    </div>
    {{-- Busca de cliente --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
      <div class="relative">
        <input type="text" class="w-full rounded-xl border-gray-200 bg-white px-3 py-2 focus:border-gray-400 focus:ring-0" placeholder="Buscar por nome, telefone ou e-mail" x-model.debounce.400ms="customerSearch" @input="searchCustomers()">
        {{-- resultados --}}
        <div class="absolute left-0 right-0 mt-1 z-20" x-show="customerResults.length">
          <div class="rounded-xl border border-gray-200 bg-white shadow-sm max-h-60 overflow-auto">
            <template x-for="c in customerResults" :key="c.id">
              <button type="button" class="w-full text-left px-3 py-2 hover:bg-gray-50" @click="selectCustomer(c)">
                <div class="font-medium text-gray-900" x-text="c.name"></div>
                <div class="text-xs text-gray-500"><span x-text="c.phone"></span> • <span x-text="c.email"></span></div>
              </button>
            </template>
            <div class="px-3 py-2 text-sm text-gray-500" x-show="!customerResults.length">Sem resultados</div>
          </div>
        </div>
      </div>
      <input type="text" class="rounded-xl border-gray-200 px-3 py-2" placeholder="Nome" x-model="customer.name">
      <input type="text" class="rounded-xl border-gray-200 px-3 py-2" placeholder="Telefone (E.164)" x-model="customer.phone">
      <input type="email" class="rounded-xl border-gray-200 px-3 py-2 md:col-span-2" placeholder="E-mail" x-model="customer.email">
    </div>
  </div>

  {{-- ENDEREÇO (CEP primeiro) --}}
  <div class="rounded-2xl border border-gray-200 bg-white p-5 mb-6">
    <h2 class="text-lg font-semibold text-gray-900 mb-4">Endereço</h2>
    <div class="grid grid-cols-1 md:grid-cols-6 gap-3">
      <input type="text" class="rounded-xl border-gray-200 px-3 py-2 md:col-span-2" placeholder="CEP" x-model="address.cep" @blur="fetchViaCep()">
      <input type="text" class="rounded-xl border-gray-200 px-3 py-2 md:col-span-2" placeholder="Número" x-model="address.number">
      <input type="text" class="rounded-xl border-gray-200 px-3 py-2 md:col-span-2" placeholder="Complemento" x-model="address.complement">
      <input type="text" class="rounded-xl border-gray-200 px-3 py-2 md:col-span-3" placeholder="Rua" x-model="address.street">
      <input type="text" class="rounded-xl border-gray-200 px-3 py-2 md:col-span-3" placeholder="Bairro" x-model="address.district">
      <input type="text" class="rounded-xl border-gray-200 px-3 py-2 md:col-span-3" placeholder="Cidade" x-model="address.city">
      <input type="text" class="rounded-xl border-gray-200 px-3 py-2 md:col-span-1" placeholder="UF" x-model="address.uf" maxlength="2">
      <div class="md:col-span-2 flex items-center">
        <button type="button" class="rounded-xl bg-gray-900 text-white px-4 py-2 hover:bg-black" @click="saveAddress()">Salvar Endereço</button>
      </div>
    </div>
  </div>

  {{-- ITENS --}}
  <div class="rounded-2xl border border-gray-200 bg-white p-5 mb-6">
    <h2 class="text-lg font-semibold text-gray-900 mb-4">Itens</h2>
    <div class="relative mb-4">
      <input type="text" class="w-full rounded-xl border-gray-200 px-3 py-2" placeholder="Buscar produto por nome ou SKU" x-model.debounce.300ms="productSearch" @input="searchProducts()">
      <div class="absolute left-0 right-0 mt-1 z-20" x-show="productResults.length">
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm max-h-64 overflow-auto">
          <template x-for="p in productResults" :key="p.id">
            <button type="button" class="w-full text-left px-3 py-2 hover:bg-gray-50" @click="addItem(p)">
              <div class="flex items-center justify-between">
                <div class="font-medium text-gray-900" x-text="p.name"></div>
                <div class="text-sm text-gray-600" x-text="currency(p.price)"></div>
              </div>
              <div class="text-xs text-gray-500">Estoque: <span x-text="p.stock"></span></div>
            </button>
          </template>
        </div>
      </div>
    </div>
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="text-gray-500">
          <tr>
            <th class="px-3 py-2 text-left">Produto</th>
            <th class="px-3 py-2 text-left">Preço</th>
            <th class="px-3 py-2 text-left">Qtd</th>
            <th class="px-3 py-2 text-left">Total</th>
            <th class="px-3 py-2"></th>
          </tr>
        </thead>
        <tbody>
          <template x-for="(it, i) in items" :key="it.id">
            <tr class="border-t">
              <td class="px-3 py-2" x-text="it.name"></td>
              <td class="px-3 py-2" x-text="currency(it.price)"></td>
              <td class="px-3 py-2">
                <input type="number" min="1" class="w-20 rounded-xl border-gray-200 px-2 py-1" x-model.number="it.qty" @change="recalc()">
              </td>
              <td class="px-3 py-2" x-text="currency(it.price * it.qty)"></td>
              <td class="px-3 py-2 text-right">
                <button type="button" class="text-red-600 hover:underline" @click="removeItem(i)">Remover</button>
              </td>
            </tr>
          </template>
          <tr x-show="!items.length">
            <td colspan="5" class="px-3 py-6 text-center text-gray-500">Nenhum item adicionado</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  {{-- ENTREGA + OBS --}}
  <div class="rounded-2xl border border-gray-200 bg-white p-5 mb-6">
    <h2 class="text-lg font-semibold text-gray-900 mb-4">Entrega</h2>
    <select class="w-full rounded-xl border-gray-200 px-3 py-2 mb-3" x-model="shippingMethod">
      <option value="">Selecione um endereço e itens para calcular opções…</option>
      <option value="loja">Retirar na loja</option>
      <option value="delivery">Entrega Padrão</option>
      <option value="express">Entrega Expressa</option>
    </select>
    <textarea class="w-full rounded-xl border-gray-200 px-3 py-2" placeholder="Observações do pedido" rows="3" x-model="notes"></textarea>
  </div>

  {{-- RESUMO + PAGAMENTO --}}
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="rounded-2xl border border-gray-200 bg-white p-5">
      <h2 class="text-lg font-semibold text-gray-900 mb-4">Resumo</h2>
      <div class="flex gap-2 mb-4">
        <input type="text" class="rounded-xl border-gray-200 px-3 py-2" placeholder="Cupom" x-model="coupon">
        <button type="button" class="rounded-xl border px-3 py-2 hover:bg-gray-50" @click="applyCoupon()">Aplicar</button>
      </div>
      <dl class="space-y-1 text-sm">
        <div class="flex justify-between"><dt class="text-gray-600">Subtotal</dt><dd x-text="currency(totals.subtotal)"></dd></div>
        <div class="flex justify-between"><dt class="text-gray-600">Desconto</dt><dd x-text="currency(totals.discount)"></dd></div>
        <div class="flex justify-between"><dt class="text-gray-600">Entrega</dt><dd x-text="currency(totals.shipping)"></dd></div>
        <div class="flex justify-between text-base font-semibold pt-2 border-t mt-2"><dt>Total</dt><dd x-text="currency(totals.total)"></dd></div>
      </dl>
    </div>
    <div class="rounded-2xl border border-gray-200 bg-white p-5">
      <h2 class="text-lg font-semibold text-gray-900 mb-4">Pagamento</h2>
      <div class="flex flex-col gap-3 text-sm">
        <label class="inline-flex items-center gap-2">
          <input type="radio" name="pay" value="pix" x-model="payment" class="text-gray-900">
          <span>PIX</span>
        </label>
        <label class="inline-flex items-center gap-2">
          <input type="radio" name="pay" value="card" x-model="payment" class="text-gray-900">
          <span>Cartão (Mercado Pago)</span>
        </label>
      </div>
      <div class="mt-6">
        <button type="button" class="w-full rounded-xl bg-emerald-600 text-white py-3 font-medium hover:bg-emerald-700" @click="finalize()">Finalizar Pedido</button>
      </div>
    </div>
  </div>
</div>

<script>
function pdvPage() {
  return {
    // state
    customerSearch: '', customerResults: [],
    customer: { id:null, name:'', phone:'', email:'' },
    address: { cep:'', number:'', complement:'', street:'', district:'', city:'', uf:'' },
    productSearch: '', productResults: [],
    items: [],
    shippingMethod: '', notes: '', coupon: '', payment: 'pix',
    totals: { subtotal: 0, discount: 0, shipping: 0, total: 0 },
    init() { this.recalc(); },
    // helpers
    currency(v){ return (v||0).toLocaleString('pt-BR', { style:'currency', currency:'BRL' }); },
    // CLIENTE
    async searchCustomers() {
      if (!this.customerSearch || this.customerSearch.length < 2) { this.customerResults = []; return; }
      const res = await fetch(`{{ route('dashboard.pdv.search.customers') }}?q=${encodeURIComponent(this.customerSearch)}`);
      this.customerResults = await res.json();
    },
    selectCustomer(c) {
      this.customer = { id:c.id, name:c.name, phone:c.phone, email:c.email };
      if (c.address) this.address = Object.assign(this.address, c.address);
      this.customerResults = [];
    },
    clearCustomer() { this.customer = { id:null, name:'', phone:'', email:'' }; this.customerSearch = ''; this.customerResults = []; },
    openNewCustomer() {},
    // ENDEREÇO via CEP
    async fetchViaCep() {
      const cep = (this.address.cep || '').replace(/\D/g, '');
      if (cep.length !== 8) return;
      try {
        const res = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
        const data = await res.json();
        if (!data.erro) {
          this.address.street = data.logradouro || '';
          this.address.district = data.bairro || '';
          this.address.city = data.localidade || '';
          this.address.uf = data.uf || '';
        }
      } catch(e) {}
    },
    saveAddress() {},
    // PRODUTOS & ITENS
    async searchProducts() {
      if (!this.productSearch || this.productSearch.length < 2) { this.productResults = []; return; }
      const res = await fetch(`{{ route('dashboard.pdv.search.products') }}?q=${encodeURIComponent(this.productSearch)}`);
      this.productResults = await res.json();
    },
    addItem(p) {
      const existing = this.items.find(i => i.id === p.id);
      if (existing) existing.qty += 1;
      else this.items.push({ id:p.id, name:p.name, price:Number(p.price), qty:1 });
      this.productResults = []; this.productSearch = ''; this.recalc();
    },
    removeItem(i) { this.items.splice(i,1); this.recalc(); },
    // RESUMO
    recalc() {
      const subtotal = this.items.reduce((s,i) => s + (Number(i.price) * Number(i.qty || 0)), 0);
      const shipping = this.shippingMethod === 'express' ? 19.9 : (this.shippingMethod === 'delivery' ? 8.9 : 0);
      const discount = this.totals?.discount || 0;
      this.totals = { subtotal, discount, shipping, total: Math.max(0, subtotal - discount + shipping), };
    },
    async applyCoupon() {
      const code = (this.coupon || '').trim().toUpperCase();
      if (!code) return;
      try {
        const res = await fetch(`{{ route('api.coupons.validate') }}?code=${encodeURIComponent(code)}`, {
          headers: { 'Accept': 'application/json' }
        });
        const data = await res.json();
        if (!res.ok || !data.valid) {
          this.totals.discount = 0;
          alert(data.message || 'Cupom inválido ou expirado.');
          this.recalc();
          return;
        }
        const subtotal = this.items.reduce((s, i) => s + i.price * i.qty, 0);
        if (data.type === 'percentual') {
          this.totals.discount = subtotal * (data.value / 100);
        } else if (data.type === 'valor_fixo') {
          this.totals.discount = Math.min(data.value, subtotal);
        }
        this.recalc();
      } catch (e) {
        console.error(e);
        alert('Erro ao validar o cupom.');
      }
    },
    // FINALIZAR
    async finalize() {
      const payload = {
        customer: this.customer,
        address: this.address,
        items: this.items,
        shipping: this.shippingMethod,
        notes: this.notes,
        coupon: this.coupon,
        payment: this.payment,
        totals: this.totals,
      };
      try {
        const res = await fetch(`{{ route('dashboard.pdv.store') }}`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
          body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (data.redirect_url) window.location.href = data.redirect_url;
        else if (data.success) {
          alert('Pedido criado com sucesso!');
          location.reload();
        }
      } catch(e) { alert('Erro ao finalizar pedido.'); }
    }
  }
}
</script>
@endsection