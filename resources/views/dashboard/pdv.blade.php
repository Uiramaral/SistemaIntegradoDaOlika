{{-- PÁGINA: PDV (Ponto de Venda - Criação de Pedidos) --}}
@extends('layouts.dashboard')

@section('title','Ponto de Venda (PDV)')

{{-- força o header do layout a não renderizar ações nesta página --}}
@section('actions') @endsection
@section('page_actions') @endsection

@section('content')

<div class="page-header">
  <h1 class="text-2xl font-bold">Ponto de Venda (PDV)</h1>
  {{-- Removido botão "Baixar Layout" --}}
</div>

<div id="pdv" class="space-y-6">
  
  {{-- CLIENTE --}}
  <section class="card">
    <h2 class="card-title">Cliente</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
      <div class="col-span-2">
        <input type="text" id="customerSearch" class="input" placeholder="buscar por nome, telefone ou e-mail…">
        <div id="customerResults" class="dropdown d-none"></div>
      </div>
      <button id="btnNewCustomer" class="btn btn-outline">+ Incluir cliente</button>
    </div>
    <div id="customerData" class="mt-3 text-sm text-gray-600 d-none"></div>
    <div class="mt-2" id="fiadoTools" style="display:none">
      <a id="linkFiados" href="#" class="btn btn-sm btn-outline-secondary">Fiados do cliente</a>
      <span id="fiadoBadge" class="badge bg-warning text-dark">Em aberto: R$ 0,00</span>
    </div>
  </section>

  {{-- ENDEREÇO (CEP -> Número -> resto) --}}
  <section class="card">
    <h2 class="card-title">Endereço</h2>
    <div class="grid grid-cols-1 md:grid-cols-8 gap-3">
      <input id="cep" class="input md:col-span-2" placeholder="CEP *">
      <input id="number" class="input md:col-span-2" placeholder="Nº *">
      <input id="street" class="input md:col-span-4" placeholder="Rua *">
      <input id="neighborhood" class="input md:col-span-3" placeholder="Bairro">
      <input id="city" class="input md:col-span-3" placeholder="Cidade">
      <input id="state" class="input md:col-span-2" placeholder="UF">
      <input id="complement" class="input md:col-span-8" placeholder="Complemento">
    </div>
  </section>

  {{-- PRODUTOS (com item avulso) --}}
  <section class="card">
    <h2 class="card-title">Produtos</h2>
    <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
      <div class="md:col-span-3">
        <input type="text" id="productSearch" class="input" placeholder="buscar produto por nome ou SKU…">
        <div id="productResults" class="dropdown d-none"></div>
      </div>
      <input type="number" id="qty" class="input" min="1" value="1" placeholder="Qtd">
      <button id="addProduct" class="btn">Adicionar</button>
    </div>

    <details class="mt-4">
      <summary class="text-sm text-gray-600 cursor-pointer">+ Item avulso (somente para esta venda)</summary>
      <div class="mt-3 grid grid-cols-1 md:grid-cols-5 gap-3">
        <input id="customName" class="input md:col-span-3" placeholder="Nome do item">
        <input id="customPrice" class="input" type="number" step="0.01" placeholder="Preço">
        <button id="addCustom" class="btn btn-outline">Adicionar avulso</button>
      </div>
    </details>

    <div class="mt-4 overflow-x-auto">
      <table class="table">
        <thead>
          <tr>
            <th>Produto</th><th class="w-24 text-right">Preço</th><th class="w-20">Qtd</th><th class="w-28 text-right">Total</th><th class="w-10"></th>
          </tr>
        </thead>
        <tbody id="cartBody"></tbody>
      </table>
    </div>
  </section>

  {{-- ENTREGA / OBS --}}
  <section class="card">
    <h2 class="card-title">Entrega</h2>
    <select id="deliveryType" class="input mb-3">
      <option value="pickup">Retirada</option>
      <option value="delivery">Entrega</option>
    </select>
    <textarea id="orderNotes" class="input" rows="3" placeholder="Observações do pedido…"></textarea>
  </section>

  {{-- RESUMO + CUPOM --}}
  <section class="card">
    <h2 class="card-title">Resumo</h2>
    <div class="grid grid-cols-1 md:grid-cols-5 gap-3 items-center">
      <input id="couponCode" class="input md:col-span-2" placeholder="Cupom">
      <button id="applyCoupon" class="btn md:col-span-1">Aplicar</button>
      <div id="couponMsg" class="md:col-span-2 text-sm"></div>
    </div>

    <div class="mt-4 space-y-1 text-sm">
      <div class="flex justify-between"><span>Subtotal</span><span id="subtotal">R$ 0,00</span></div>
      <div class="flex justify-between"><span>Desconto</span><span id="discount">R$ 0,00</span></div>
      <div class="flex justify-between"><span>Entrega</span><span id="deliveryFee">R$ 0,00</span></div>
      <div class="flex justify-between font-semibold text-lg"><span>Total</span><span id="grandTotal">R$ 0,00</span></div>
    </div>
  </section>

  {{-- PAGAMENTO --}}
  <section class="card">
    <h2 class="card-title">Pagamento</h2>
    <div class="flex flex-wrap gap-4 text-sm">
      <label class="inline-flex items-center gap-2"><input type="radio" name="pay" value="pix" checked> PIX</label>
      <label class="inline-flex items-center gap-2"><input type="radio" name="pay" value="link"> Link Mercado Pago</label>
      <label class="inline-flex items-center gap-2"><input type="radio" name="pay" value="fiado"> Fiado (lançar débito)</label>
    </div>
    <button id="finish" class="btn btn-primary w-full mt-4">Finalizar Pedido</button>
  </section>
</div>

{{-- Modal Novo Cliente --}}
<div id="modalCustomer" class="modal d-none">
  <div class="modal-box">
    <h3 class="font-bold text-lg mb-3">Novo Cliente</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
      <input id="nc_name" class="input" placeholder="Nome *">
      <input id="nc_phone" class="input" placeholder="Telefone (E.164) *">
      <input id="nc_email" class="input md:col-span-2" placeholder="E-mail (opcional)">
    </div>
    <div class="mt-4 flex justify-end gap-2">
      <button id="nc_cancel" class="btn btn-outline">Cancelar</button>
      <button id="nc_save" class="btn">Salvar</button>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
const fmt = v => (new Intl.NumberFormat('pt-BR',{style:'currency',currency:'BRL'}).format(v||0));

let state = {
  customer:null, address:{}, items:[], coupon:null, totals:{sub:0,discount:0,delivery:0,total:0}
};

function recalc(){
  state.totals.sub = state.items.reduce((s,i)=> s + (i.price*i.qty), 0);
  state.totals.total = Math.max(0, state.totals.sub - state.totals.discount + state.totals.delivery);
  document.getElementById('subtotal').innerText = fmt(state.totals.sub);
  document.getElementById('discount').innerText = fmt(state.totals.discount);
  document.getElementById('deliveryFee').innerText = fmt(state.totals.delivery);
  document.getElementById('grandTotal').innerText = fmt(state.totals.total);
}

function renderCart(){
  const tbody = document.getElementById('cartBody');
  tbody.innerHTML = state.items.map((i,idx)=>`
    <tr>
      <td>${i.name}${i.custom? ' <span class="badge">avulso</span>':''}</td>
      <td class="text-right">${fmt(i.price)}</td>
      <td><input type="number" min="1" value="${i.qty}" class="input input-sm" onchange="updQty(${idx},this.value)"></td>
      <td class="text-right">${fmt(i.price*i.qty)}</td>
      <td><button class="btn btn-xs btn-ghost" onclick="delItem(${idx})">✕</button></td>
    </tr>`).join('');
  recalc();
}

window.updQty = (idx,v)=>{ state.items[idx].qty = parseInt(v||1); renderCart(); };
window.delItem = (idx)=>{ state.items.splice(idx,1); renderCart(); };

async function viaCEP(cep){
  cep = cep.replace(/\D/g,'');
  if(cep.length!==8) return;
  try{
    const r = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
    const j = await r.json();
    if(!j.erro){
      document.getElementById('street').value = j.logradouro||'';
      document.getElementById('neighborhood').value = j.bairro||'';
      document.getElementById('city').value = j.localidade||'';
      document.getElementById('state').value = j.uf||'';
    }
  }catch(e){}
}
document.getElementById('cep').addEventListener('blur', e=> viaCEP(e.target.value));

// Função para atualizar badge de fiados
async function updateFiadoBadge(customerId){
  const badge = document.getElementById('fiadoBadge');
  try{
    const r = await fetch(`/api/fiados/saldo?customer_id=${customerId}`);
    const j = await r.json();
    if(j.ok){
      const v = Number(j.saldo||0);
      const fmt = new Intl.NumberFormat('pt-BR',{style:'currency',currency:'BRL'}).format(v);
      badge.textContent = `Em aberto: ${fmt}`;
      // cor opcional por saldo
      badge.classList.remove('bg-success','bg-danger','bg-warning');
      badge.classList.add(v>0 ? 'bg-danger' : 'bg-success'); // débito = vermelho; ok/crédito = verde
    }
  }catch(e){}
}

/* ------- BUSCA CLIENTE ------- */
const custInput = document.getElementById('customerSearch');
const custDrop = document.getElementById('customerResults');
const customerData = document.getElementById('customerData');
const customerSearch = document.getElementById('customerSearch');
custInput.addEventListener('input', async (e)=>{
  const q = e.target.value.trim();
  if(q.length<2){ custDrop.classList.add('d-none'); return; }
  const r = await fetch(`{{ route('dashboard.pdv.search.customers') }}?q=${encodeURIComponent(q)}`);
  const list = await r.json();
  custDrop.innerHTML = list.map(c=>`<button class="dropdown-item" data-id="${c.id}">${c.name} • ${c.phone} ${c.email? ' • '+c.email:''}</button>`).join('') 
    || `<div class="p-3 text-sm">Nada encontrado. <button id="linkNewCustomer" class="link">+ criar novo</button></div>`;
  custDrop.classList.remove('d-none');
});
custDrop.addEventListener('click', (ev)=>{
  const b = ev.target.closest('button.dropdown-item'); 
  if(b){
    state.customer = {id: b.dataset.id, label: b.innerText};
    customerData.innerText = b.innerText;
    customerData.style.display = '';            // mostra o resumo
    
    // habilita link + badge
    document.getElementById('fiadoTools').style.display = 'block';
    const lf = document.getElementById('linkFiados');
    lf.href = `/dashboard/customers/${state.customer.id}/fiados`;
    updateFiadoBadge(state.customer.id);
    
    custDrop.classList.add('d-none'); // ou hidden, conforme seu tema
  }else if(ev.target.id==='linkNewCustomer'){ openCustomerModal(); }
});
document.getElementById('btnNewCustomer').onclick = openCustomerModal;

function openCustomerModal(){ document.getElementById('modalCustomer').classList.remove('d-none'); }
document.getElementById('nc_cancel').onclick = ()=> document.getElementById('modalCustomer').classList.add('d-none');
document.getElementById('nc_save').onclick = async ()=>{
  const body = {name: document.getElementById('nc_name').value, phone: document.getElementById('nc_phone').value, email: document.getElementById('nc_email').value};
  const r = await fetch('/api/customers',{method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(body)});
  const c = await r.json();
  state.customer = {id:c.id, label:`${c.name} • ${c.phone} ${c.email? ' • '+c.email:''}`};
  customerSearch.value = c.name;
  customerData.innerText = state.customer.label;
  customerData.style.display = '';

  document.getElementById('fiadoTools').style.display = 'block';
  document.getElementById('linkFiados').href = `/dashboard/customers/${state.customer.id}/fiados`;
  updateFiadoBadge(state.customer.id);
  
  document.getElementById('modalCustomer').classList.add('d-none');
};

/* ------- PRODUTOS ------- */
const prodInput = document.getElementById('productSearch');
const prodDrop = document.getElementById('productResults');
prodInput.addEventListener('input', async (e)=>{
  const q = e.target.value.trim();
  if(q.length<2){ prodDrop.classList.add('d-none'); return; }
  const r = await fetch(`{{ route('dashboard.pdv.search.products') }}?q=${encodeURIComponent(q)}`);
  const list = await r.json();
  prodDrop.innerHTML = list.map(p=>`<button class="dropdown-item" data-id="${p.id}" data-name="${p.name}" data-price="${p.price}">${p.name} • ${fmt(p.price)}</button>`).join('');
  prodDrop.classList.remove('d-none');
});
prodDrop.addEventListener('click', (ev)=>{
  const b = ev.target.closest('button.dropdown-item'); 
  if(!b) return;
  state.items.push({product_id: +b.dataset.id, name:b.dataset.name, price:+b.dataset.price, qty: +document.getElementById('qty').value||1});
  prodDrop.classList.add('d-none'); prodInput.value=''; renderCart();
});
document.getElementById('addProduct').onclick = ()=>{};
document.getElementById('addCustom').onclick = ()=>{
  const name = document.getElementById('customName').value.trim(); 
  const price = parseFloat(document.getElementById('customPrice').value||0);
  if(!name || !price) return;
  state.items.push({product_id:null, custom:true, name, price, qty:1});
  document.getElementById('customName').value=''; 
  document.getElementById('customPrice').value=''; 
  renderCart();
};

/* ------- CUPOM ------- */
document.getElementById('applyCoupon').onclick = async ()=>{
  const code = document.getElementById('couponCode').value.trim();
  const payload = {code, customer_id: state.customer?.id || null, subtotal: state.totals.sub};
  const r = await fetch('{{ route('dashboard.pdv.validate.coupon') }}',{method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(payload)});
  const j = await r.json();
  if(j.ok){
    state.coupon = j.coupon; state.totals.discount = j.discount_value; 
    document.getElementById('couponMsg').innerHTML = `<span class="text-green-700">Cupom aplicado: ${j.coupon.code} (${j.coupon.type==='percentage'? j.coupon.value+'%' : fmt(j.coupon.value)})</span>`;
  }else{
    state.coupon = null; state.totals.discount = 0;
    document.getElementById('couponMsg').innerHTML = `<span class="text-red-700">${j.message||'Cupom inválido'}</span>`;
  }
  recalc();
};

/* ------- FINALIZAR ------- */
document.getElementById('finish').onclick = async ()=>{
  const body = {
    customer_id: state.customer?.id,
    address: {
      cep: document.getElementById('cep').value, 
      number: document.getElementById('number').value, 
      street: document.getElementById('street').value, 
      neighborhood: document.getElementById('neighborhood').value,
      city: document.getElementById('city').value, 
      state: document.getElementById('state').value, 
      complement: document.getElementById('complement').value
    },
    delivery_type: document.getElementById('deliveryType').value,
    notes: document.getElementById('orderNotes').value,
    items: state.items,
    coupon_code: state.coupon?.code || null,
    payment_method: document.querySelector('input[name="pay"]:checked').value
  };
  const r = await fetch('{{ route('dashboard.pdv.store') }}',{method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(body)});
  const j = await r.json();
  if(j.ok){
    alert('Pedido criado com sucesso! #' + j.order_number);
    window.location.href = `/dashboard/orders/${j.id}`;
  }else{
    alert('Erro: ' + (j.message||'Não foi possível finalizar'));
  }
};
</script>
@endpush
