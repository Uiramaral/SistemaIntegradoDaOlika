@extends('layouts.dashboard')

@section('title','PDV — Dashboard Olika')

@push('head')
<style>
  .grid-cols-2 { grid-template-columns: 1.2fr 1fr; gap: 16px; }
  @media(max-width:1024px) { .grid-cols-2 { grid-template-columns: 1fr; } }
</style>
<script>
async function fetchJSON(url, opts={}){
  const r = await fetch(url, opts);
  if(!r.ok) throw new Error('HTTP '+r.status);
  return await r.json();
}
</script>
@endpush

@section('content')
<div class="card">
  <h1 class="text-xl" style="font-weight:800;margin-bottom:10px">Ponto de Venda (PDV)</h1>
  <div class="grid-cols-2" style="display:grid">
    {{-- Coluna esquerda: Cliente, Endereço, Itens --}}
    <div>
      {{-- Cliente --}}
      <div class="card">
        <h3 style="font-weight:600">Cliente</h3>
        <input id="c-busca" class="card" placeholder="buscar por nome, telefone, e-mail">
        <div id="c-lista" style="max-height:160px;overflow:auto;margin-top:6px"></div>
        <div style="display:flex;gap:8px;margin-top:6px">
          <input id="c-id" type="hidden">
          <input id="c-nome" class="card" placeholder="Nome">
          <input id="c-fone" class="card" placeholder="Telefone (E164)">
          <input id="c-email" class="card" placeholder="E-mail">
        </div>
      </div>

      {{-- Endereço --}}
      <div class="card" style="margin-top:12px">
        <h3 style="font-weight:600">Endereço</h3>
        <div id="addr-existentes" style="margin-bottom:6px"></div>
        <div style="display:grid;grid-template-columns:1fr 120px 80px 80px;gap:8px">
          <input id="a-street" class="card" placeholder="Rua *">
          <input id="a-number" class="card" placeholder="Nº *">
          <input id="a-cep" class="card" placeholder="CEP">
          <input id="a-comp" class="card" placeholder="Compl.">
        </div>
        <div style="display:grid;grid-template-columns:1fr 140px 80px;gap:8px;margin-top:8px">
          <input id="a-neigh" class="card" placeholder="Bairro">
          <input id="a-city" class="card" placeholder="Cidade *">
          <input id="a-state" class="card" placeholder="UF *" maxlength="2">
        </div>
        <button class="btn" style="margin-top:8px" onclick="salvarEndereco()">Salvar Endereço</button>
        <input type="hidden" id="address_id">
      </div>

      {{-- Itens --}}
      <div class="card" style="margin-top:12px">
        <h3 style="font-weight:600">Itens</h3>
        <input id="p-busca" class="card" placeholder="buscar produto por nome ou SKU">
        <div id="p-lista" style="max-height:160px;overflow:auto;margin-top:6px"></div>
        <table style="width:100%;margin-top:8px">
          <thead><tr><th>Produto</th><th>Preço</th><th>Qtd</th><th>Total</th><th></th></tr></thead>
          <tbody id="cart"></tbody>
        </table>
      </div>
    </div>

    {{-- Coluna direita: Entrega, Resumo, Pagamento --}}
    <div>
      {{-- Entrega --}}
      <div class="card">
        <h3 style="font-weight:600">Entrega</h3>
        <div id="slots" style="margin-bottom:6px;color:#555">Selecione um endereço e itens para calcular opções…</div>
        <select id="delivery_slot" class="card" style="width:100%"></select>
        <textarea id="o-notes" class="card" placeholder="Observações do pedido" style="margin-top:6px"></textarea>
      </div>

      {{-- Cupom + Resumo --}}
      <div class="card" style="margin-top:12px">
        <h3 style="font-weight:600">Resumo</h3>
        <div style="display:flex;gap:8px;margin-bottom:6px">
          <input id="cupom" class="card" placeholder="Cupom">
          <button class="btn" onclick="aplicarCupom()">Aplicar</button>
        </div>
        <div style="display:grid;grid-template-columns:1fr 120px;gap:6px">
          <div>Subtotal</div><div id="r-subtotal" style="text-align:right">R$ 0,00</div>
          <div>Desconto</div><div id="r-desconto" style="text-align:right">R$ 0,00</div>
          <div>Entrega</div><div id="r-entrega" style="text-align:right">R$ 0,00</div>
          <div style="font-weight:800">Total</div><div id="r-total" style="text-align:right;font-weight:800">R$ 0,00</div>
        </div>
      </div>

      {{-- Pagamento / Finalizar --}}
      <div class="card" style="margin-top:12px">
        <h3 style="font-weight:600">Pagamento</h3>
        <div style="display:flex;gap:8px;flex-wrap:wrap">
          <label><input type="radio" name="pm" value="pix" checked> PIX</label>
          <label><input type="radio" name="pm" value="link"> Link Mercado Pago</label>
          <label><input type="radio" name="pm" value="cash"> Dinheiro</label>
          <label><input type="radio" name="pm" value="card"> Cartão presencial</label>
        </div>
        <button class="btn" style="margin-top:8px;width:100%" onclick="finalizar()">Finalizar Pedido</button>
        <div id="p-out" style="margin-top:10px"></div>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
let items = []; // {product_id, name, price, qty}
let customerId = null;
let discountCode = null;
let calcCache = null;

// busca clientes
document.getElementById('c-busca').addEventListener('input', async (e)=>{
  const q = e.target.value.trim();
  const j = await fetchJSON('{{ route('dashboard.pdv.search.customers') }}?q='+encodeURIComponent(q));
  const box = document.getElementById('c-lista'); box.innerHTML='';
  j.forEach(c=>{
    const a = document.createElement('a');
    a.href='#'; a.textContent = c.name + (c.phone?' · '+c.phone:'');
    a.onclick=(ev)=>{ev.preventDefault(); selecionarCliente(c); };
    a.className='badge'; a.style.display='inline-block'; a.style.margin='3px';
    box.appendChild(a);
  });
});

function selecionarCliente(c){
  customerId = c.id;
  document.getElementById('c-id').value = c.id;
  document.getElementById('c-nome').value = c.name || '';
  document.getElementById('c-fone').value = c.phone || '';
  document.getElementById('c-email').value = c.email || '';
  carregarEnderecos(c.id);
}

async function carregarEnderecos(cid){
  const wrap = document.getElementById('addr-existentes');
  wrap.innerHTML = 'Carregando endereços…';
  const j = await fetchJSON('/api/addresses?customer_id='+cid).catch(()=>[]);
  wrap.innerHTML='';
  if(Array.isArray(j) && j.length){
    j.forEach(a=>{
      const b = document.createElement('button');
      b.className='badge'; b.textContent = `${a.street}, ${a.number} — ${a.city}/${a.state}`;
      b.onclick=()=>{ document.getElementById('address_id').value = a.id; recalc(); };
      wrap.appendChild(b);
    });
  } else {
    wrap.textContent = 'Nenhum endereço salvo.';
  }
}

async function salvarEndereco(){
  if(!customerId){ alert('Selecione um cliente.'); return; }
  const payload = {
    customer_id: customerId,
    cep: document.getElementById('a-cep').value,
    street: document.getElementById('a-street').value,
    number: document.getElementById('a-number').value,
    complement: document.getElementById('a-comp').value,
    neighborhood: document.getElementById('a-neigh').value,
    city: document.getElementById('a-city').value,
    state: document.getElementById('a-state').value.toUpperCase()
  };
  const j = await fetchJSON('{{ route('dashboard.pdv.address') }}', {method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'}, body:JSON.stringify(payload)});
  if(j.ok){ document.getElementById('address_id').value = j.address_id; recalc(); }
}

// produtos
document.getElementById('p-busca').addEventListener('input', async (e)=>{
  const q = e.target.value.trim();
  const j = await fetchJSON('{{ route('dashboard.pdv.search.products') }}?q='+encodeURIComponent(q));
  const box = document.getElementById('p-lista'); box.innerHTML='';
  j.forEach(p=>{
    const a = document.createElement('a');
    a.href='#'; a.textContent = p.name + ' — R$ '+(+p.price).toFixed(2).replace('.',',');
    a.onclick=(ev)=>{ev.preventDefault(); addItem(p); };
    a.className='badge'; a.style.display='inline-block'; a.style.margin='3px';
    box.appendChild(a);
  });
});

function addItem(p){
  const i = items.findIndex(x=>x.product_id===p.id);
  if(i>=0){ items[i].qty += 1; }
  else { items.push({product_id:p.id, name:p.name, price:+p.price, qty:1}); }
  renderCart(); recalc();
}

function removeItem(id){
  items = items.filter(x=>x.product_id!==id);
  renderCart(); recalc();
}

function changeQty(id, q){
  const it = items.find(x=>x.product_id===id);
  if(!it) return;
  it.qty = Math.max(1, parseInt(q||1));
  renderCart(); recalc();
}

function renderCart(){
  const tb = document.getElementById('cart'); tb.innerHTML='';
  items.forEach(row=>{
    const tr = document.createElement('tr');
    tr.innerHTML = `<td>${row.name}</td>
      <td>R$ ${row.price.toFixed(2).replace('.',',')}</td>
      <td><input type="number" min="1" value="${row.qty}" style="width:70px" onchange="changeQty(${row.product_id}, this.value)"></td>
      <td>R$ ${(row.price*row.qty).toFixed(2).replace('.',',')}</td>
      <td><a href="#" class="badge" onclick="removeItem(${row.product_id});return false;">remover</a></td>`;
    tb.appendChild(tr);
  });
}

async function aplicarCupom(){
  discountCode = document.getElementById('cupom').value.trim();
  await recalc();
}

async function recalc(){
  const addrId = document.getElementById('address_id').value;
  let address = null;
  if(addrId){
    address = {
      city: document.getElementById('a-city').value,
      state: document.getElementById('a-state').value
    };
  }
  const payload = { items, address, coupon_code: discountCode || null, customer_id: customerId || null };
  const j = await fetchJSON('{{ route('dashboard.pdv.calculate') }}', {method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'}, body:JSON.stringify(payload)});
  calcCache = j;
  document.getElementById('r-subtotal').textContent = money(j.subtotal);
  document.getElementById('r-desconto').textContent = money(j.discount);
  document.getElementById('r-entrega').textContent = money(j.delivery);
  document.getElementById('r-total').textContent = money(j.total);
  const slotSel = document.getElementById('delivery_slot'); slotSel.innerHTML='';
  (j.slots||[]).forEach(s=>{
    const o = document.createElement('option');
    o.value = s.date; o.textContent = s.label; slotSel.appendChild(o);
  });
  document.getElementById('slots').textContent = (j.slots||[]).length ? 'Escolha a data/ janela:' : 'Sem janelas disponíveis nos próximos dias.';
}

function money(v){ return 'R$ '+(+v).toFixed(2).replace('.',','); }

async function finalizar(){
  if(!customerId){ alert('Selecione o cliente.'); return; }
  if(!items.length){ alert('Adicione ao menos 1 item.'); return; }
  const pm = document.querySelector('input[name="pm"]:checked').value;
  const payload = {
    customer_id: customerId,
    address_id: document.getElementById('address_id').value || null,
    delivery_date: document.getElementById('delivery_slot').value || null,
    delivery_window: (document.getElementById('delivery_slot').selectedOptions[0]?.text || '').trim(),
    items: items,
    coupon_code: discountCode || null,
    payment_method: pm,
    note: document.getElementById('o-notes').value || null
  };
  const out = document.getElementById('p-out'); out.textContent = 'Processando…';
  const j = await fetchJSON('{{ route('dashboard.pdv.store') }}', {method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'}, body:JSON.stringify(payload)});
  if(j.ok){
    let html = `<div class="badge" style="background:#d1fae5;color:#065f46">Pedido #${j.number} criado!</div>`;
    if(j.payment_link){
      html += `<div style="margin-top:6px"><a class="badge" target="_blank" href="${j.payment_link}">Abrir checkout Mercado Pago</a></div>`;
    }
    if(j.pix_qr_base64){
      html += `<div style="margin-top:6px"><img style="max-width:220px;border:1px solid #eee" src="data:image/png;base64,${j.pix_qr_base64}"><br><small>Copia-e-cola: ${j.pix_copy_paste}</small></div>`;
    }
    out.innerHTML = html;
  } else {
    out.innerHTML = `<div class="badge" style="background:#fee2e2;color:#991b1b">Erro ao criar pedido</div>`;
  }
}
</script>
@endpush
@endsection

