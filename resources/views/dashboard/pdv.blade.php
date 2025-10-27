@extends('layouts.dashboard')

@section('title', 'Ponto de Venda (PDV)')

@section('content')

<div class="pdv-page" id="pdv-app"
     data-customers-search="{{ $pdvRoutes['customers_search'] ?? '' }}"
     data-products-search="{{ $pdvRoutes['products_search'] ?? '' }}"
     data-coupons-eligible="{{ $pdvRoutes['coupons_eligible'] ?? '' }}"
     data-coupons-validate="{{ $pdvRoutes['coupons_validate'] ?? '' }}"
     data-fiado-balance="{{ $pdvRoutes['fiado_balance'] ?? '' }}"
     data-order-store="{{ $pdvRoutes['order_store'] ?? '' }}">



  <div class="card">
    <div class="card-header"><h2>Ponto de Venda (PDV)</h2></div>
    <div class="card-body">



      {{-- CLIENTE --}}
      <section class="sec">
        <h3>Cliente</h3>
        <div class="row-12">
          <div class="col-4">
            <input id="cliente-busca" class="input" placeholder="buscar por nome, telefone, e-mail" autocomplete="off">
            <div id="cliente-sugestoes" class="suggestions"></div>
          </div>
          <div class="col-3"><input id="cliente-nome" class="input" placeholder="Nome"></div>
          <div class="col-3"><input id="cliente-telefone" class="input" placeholder="Telefone (E164)"></div>
          <div class="col-2"><input id="cliente-email" class="input" placeholder="E-mail"></div>
        </div>
        <div id="cliente-fiado-alerta" class="info-warn" hidden>
          Em aberto (fiado): <b id="cliente-fiado-saldo">R$ 0,00</b>
        </div>
      </section>



      {{-- ENDEREÇO (pills) --}}
      <section class="sec">
        <h3>Endereço</h3>
        <div class="row-12">
          <div class="col-12">
            <div class="input-group">
              <input id="end-rua"   class="input pill pill-left"  placeholder="Rua *">
              <input id="end-num"   class="input pill"            placeholder="N° *">
              <input id="end-cep"   class="input pill"            placeholder="CEP">
              <input id="end-compl" class="input pill pill-right" placeholder="Compl.">
            </div>
          </div>
          <div class="col-12">
            <div class="input-group">
              <input id="end-bairro" class="input pill pill-left"  placeholder="Bairro">
              <input id="end-cidade" class="input pill"            placeholder="Cidade *">
              <input id="end-uf"     class="input pill pill-right" placeholder="UF *" maxlength="2">
              <button id="btn-salvar-endereco" class="btn btn-soft group-attach">Salvar Endereço</button>
            </div>
          </div>
        </div>
      </section>



      {{-- ITENS --}}
      <section class="sec">
        <h3>Itens</h3>



        <div class="row-12 mb-2">
          <div class="col-5">
            <input id="produto-busca" class="input" placeholder="buscar produto por nome ou SKU" autocomplete="off">
            <div id="produto-sugestoes" class="suggestions"></div>
          </div>
        </div>



        {{-- ITEM AVULSO --}}
        <div class="row-12 mb-3">
          <div class="col-6">
            <div class="input-group">
              <input id="avulso-desc"  class="input pill pill-left"  placeholder="Item avulso (descrição)">
              <input id="avulso-preco" type="number" step="0.01" class="input pill" placeholder="Preço">
              <input id="avulso-qtd"   type="number" min="1"      class="input pill" value="1" placeholder="Qtd">
              <button id="avulso-add"  class="btn btn-soft group-attach">Adicionar</button>
            </div>
          </div>
        </div>



        <div class="table-wrapper">

          <table class="table">

            <thead>

              <tr>

                <th>Produto</th>

                <th class="t-right">Preço</th>

                <th class="t-center">Qtd</th>

                <th class="t-right">Total</th>

                <th></th>

              </tr>

            </thead>

            <tbody id="tabela-itens"></tbody>

          </table>

        </div>

      </section>



      {{-- ENTREGA --}}
      <section class="sec">
        <h3>Entrega</h3>
        <select id="entrega-opcao" class="input mb-2">
          <option value="">Selecione um endereço e itens para calcular opções...</option>
        </select>
        <textarea id="pedido-observacoes" class="textarea" placeholder="Observações do pedido"></textarea>
      </section>



      {{-- RESUMO + CUPOM --}}
      <section class="sec">
        <h3>Resumo</h3>



        <div class="coupon-mode">

          <button type="button" class="btn btn-soft btn-xs selected" data-mode="select" id="btn-mode-select">Selecionar</button>

          <button type="button" class="btn btn-soft btn-xs" data-mode="type" id="btn-mode-type">Digitar</button>

        </div>



        <div class="coupon-row">

          <div id="cupom-select-box" class="coupon-box">

            <select id="cupom-select" class="input w-56">

              <option value="">— cupons elegíveis —</option>

            </select>

            <button id="btn-aplicar-cupom-select" class="btn btn-soft">Aplicar</button>

          </div>



          <div id="cupom-type-box" class="coupon-box" hidden>

            <input id="cupom" class="input w-40" placeholder="Cupom">

            <button id="btn-aplicar-cupom" class="btn btn-soft">Aplicar</button>

          </div>



          <span id="cupom-feedback" class="note"></span>

        </div>



        <div class="totais">

          <div><span>Subtotal</span><span id="subtotal">R$ 0,00</span></div>

          <div><span>Desconto</span><span id="desconto">R$ 0,00</span></div>

          <div><span>Entrega</span><span id="entrega">R$ 0,00</span></div>

          <div class="total"><span>Total</span><span id="total">R$ 0,00</span></div>

        </div>

      </section>



      {{-- PAGAMENTO (PIX | Link | FIADO) --}}
      <section class="sec">
        <h3>Pagamento</h3>
        <div class="pay-row">

          <label class="radio"><input type="radio" name="pagamento" value="pix" checked> PIX</label>

          <label class="radio"><input type="radio" name="pagamento" value="link"> Link Mercado Pago</label>

          <label class="radio"><input type="radio" name="pagamento" value="fiado"> Fiado (lançar débito)</label>

        </div>
        <button id="btn-finalizar" class="btn btn-primary full">Finalizar Pedido</button>
      </section>



    </div>

  </div>



  {{-- Template da linha de item --}}

  <template id="tpl-item">

    <tr data-id="">

      <td class="produto-nome"></td>

      <td class="t-right"><input type="number" step="0.01" class="input input-xs t-right item-preco" value=""></td>

      <td class="t-center">

        <div class="qty">

          <button type="button" class="btn btn-xs btn-soft dec">–</button>

          <input type="number" min="1" class="input input-xs t-center item-qtd" value="1">

          <button type="button" class="btn btn-xs btn-soft inc">+</button>

        </div>

      </td>

      <td class="t-right item-total">R$ 0,00</td>

      <td class="t-right"><button type="button" class="btn btn-xs btn-danger remove">Remover</button></td>

    </tr>

  </template>



</div>

@endsection

@push('scripts')
<script>
const app = document.getElementById('pdv-app');
const ENDPOINTS = {
  customers_search : app.dataset.customersSearch,
  products_search  : app.dataset.productsSearch,
  coupons_eligible : app.dataset.couponsEligible,
  coupons_validate : app.dataset.couponsValidate,
  fiado_balance    : app.dataset.fiadoBalance,
  order_store      : app.dataset.orderStore,
};



const $  = (s, c=document)=>c.querySelector(s);
const $$ = (s, c=document)=>Array.from(c.querySelectorAll(s));
const BRL = new Intl.NumberFormat('pt-BR',{style:'currency',currency:'BRL'});
const money = v => BRL.format(Number(v||0));
const debounce=(fn,t=300)=>{let i;return(...a)=>{clearTimeout(i);i=setTimeout(()=>fn(...a),t)}}



const state = { cliente:null, itens:[], entrega:0, desconto:0, cupom:null, pagamento:'pix' };



function httpGet(url){

  return fetch(url, { headers:{ 'Accept':'application/json' }});
}
function httpPost(url, body){

  return fetch(url, {

    method:'POST',

    headers:{ 'Content-Type':'application/json', 'Accept':'application/json', 'X-CSRF-TOKEN':'{{ csrf_token() }}' },

    body: JSON.stringify(body||{})

  });
}



/* === CLIENTE === */

const clienteBusca=$('#cliente-busca'), clienteSug=$('#cliente-sugestoes');
clienteBusca.addEventListener('input', debounce(async e=>{

  const q=e.target.value.trim(); clienteSug.innerHTML=''; if(!q || !ENDPOINTS.customers_search) return;

  try{

    const url=new URL(ENDPOINTS.customers_search, window.location.origin); url.searchParams.set('q',q);

    const r=await httpGet(url);

    if(r.redirected || r.status>=400){ console.warn('Clientes search falhou', r.status); return; }

    const list=await r.json();

    if(!Array.isArray(list) || !list.length) return;

    const ul=document.createElement('ul');

    list.forEach(c=>{

      const li=document.createElement('li');

      li.textContent=`${c.name||c.nome} • ${c.phone||c.telefone||''} ${c.email?'• '+c.email:''}`;

      li.onclick=()=>selectCliente(c);

      ul.appendChild(li);

    });

    clienteSug.appendChild(ul);

  }catch(err){ console.error('Erro clientes search', err); }

},250));



async function selectCliente(c){

  state.cliente=c;

  $('#cliente-nome').value=c.name||c.nome||''; $('#cliente-telefone').value=c.phone||c.telefone||''; $('#cliente-email').value=c.email||'';

  const e=c.endereco||{};

  $('#end-rua').value=e.rua||''; $('#end-num').value=e.numero||''; $('#end-cep').value=e.cep||'';

  $('#end-compl').value=e.complemento||''; $('#end-bairro').value=e.bairro||''; $('#end-cidade').value=e.cidade||''; $('#end-uf').value=e.uf||'';

  clienteSug.innerHTML=''; clienteBusca.value='';



  // saldo fiado

  try{

    if(ENDPOINTS.fiado_balance){

      const u=new URL(ENDPOINTS.fiado_balance, window.location.origin); u.searchParams.set('customer_id', c.id);

      const r=await httpGet(u); if(r.ok){ const j=await r.json(); $('#cliente-fiado-saldo').textContent=money(j.saldo||0); $('#cliente-fiado-alerta').hidden=false; }

    }

  }catch(err){ console.warn('Fiado balance erro', err); }



  await loadCuponsElegiveis();

}



/* === PRODUTOS === */

const produtoBusca=$('#produto-busca'), produtoSug=$('#produto-sugestoes');
produtoBusca.addEventListener('input', debounce(async e=>{

  const q=e.target.value.trim(); produtoSug.innerHTML=''; if(!q || !ENDPOINTS.products_search) return;

  try{

    const url=new URL(ENDPOINTS.products_search, window.location.origin); url.searchParams.set('q',q);

    const r=await httpGet(url);

    if(r.redirected || r.status>=400){ console.warn('Products search falhou', r.status); return; }

    const list=await r.json(); if(!Array.isArray(list) || !list.length) return;

    const ul=document.createElement('ul');

    list.forEach(p=>{

      const li=document.createElement('li');

      li.textContent=`${p.name||p.nome} — ${money(p.price||p.preco)}`;

      li.onclick=()=>{ addItem(p.id, p.name||p.nome, Number(p.price||p.preco), 1); produtoSug.innerHTML=''; produtoBusca.value=''; };

      ul.appendChild(li);

    });

    produtoSug.appendChild(ul);

  }catch(err){ console.error('Erro products search', err); }

},250));



/* === ITEM AVULSO === */

$('#avulso-add').onclick=()=>{

  const nome=($('#avulso-desc').value||'').trim();

  const preco=Number($('#avulso-preco').value||0);

  const qtd=Math.max(1, Number($('#avulso-qtd').value||1));

  if(!nome || !preco){ alert('Preencha descrição e preço do item avulso.'); return; }

  addItem('avulso:'+Date.now(), nome, preco, qtd);

  $('#avulso-desc').value=''; $('#avulso-preco').value=''; $('#avulso-qtd').value='1';

};



function addItem(id, nome, price, qty){

  const ex = state.itens.find(i=>i.id===id);

  if(ex){ ex.qty += qty; } else { state.itens.push({id, nome, price, qty}); }

  renderItens(); recalc(); loadCuponsElegiveis();

}
function removeItem(id){ state.itens=state.itens.filter(i=>i.id!==id); renderItens(); recalc(); loadCuponsElegiveis(); }



function renderItens(){

  const tbody=$('#tabela-itens'); tbody.innerHTML='';

  const tpl=$('#tpl-item').content;

  state.itens.forEach(item=>{

    const row=document.importNode(tpl,true); const tr=row.querySelector('tr');

    tr.dataset.id=item.id; tr.querySelector('.produto-nome').textContent=item.nome;

    const inpPreco=tr.querySelector('.item-preco'), inpQtd=tr.querySelector('.item-qtd'), cellTot=tr.querySelector('.item-total');

    inpPreco.value=item.price.toFixed(2); inpQtd.value=item.qty;

    const upd=()=>{ item.price=Number(inpPreco.value||0); item.qty=Math.max(1, Number(inpQtd.value||1)); cellTot.textContent=money(item.price*item.qty); recalc(); loadCuponsElegiveis(); };

    tr.querySelector('.inc').onclick=()=>{ inpQtd.value=Number(inpQtd.value||1)+1; upd(); };

    tr.querySelector('.dec').onclick=()=>{ inpQtd.value=Math.max(1, Number(inpQtd.value||1)-1); upd(); };

    inpPreco.onchange=upd; inpQtd.onchange=upd;

    tr.querySelector('.remove').onclick=()=>removeItem(item.id);

    cellTot.textContent=money(item.price*item.qty);

    tbody.appendChild(tr);

  });

}



/* === CUPOM === */

const btSelect=$('#btn-mode-select'), btType=$('#btn-mode-type');

const boxSelect=$('#cupom-select-box'), boxType=$('#cupom-type-box');

btSelect.onclick=()=>{ btSelect.classList.add('selected'); btType.classList.remove('selected'); boxSelect.hidden=false; boxType.hidden=true; };

btType.onclick  =()=>{ btType.classList.add('selected'); btSelect.classList.remove('selected'); boxSelect.hidden=true;  boxType.hidden=false; };



async function loadCuponsElegiveis(){

  const c=state.cliente, items=state.itens; const sel=$('#cupom-select');

  sel.innerHTML='<option value="">— cupons elegíveis —</option>';

  if(!c || !items.length || !ENDPOINTS.coupons_eligible) return;

  try{

    const url=new URL(ENDPOINTS.coupons_eligible, window.location.origin);

    url.searchParams.set('customer_id', c.id);

    url.searchParams.set('items', JSON.stringify(items));

    const r=await httpGet(url); if(!r.ok) return;

    const list=await r.json();

    (list||[]).forEach(cp=>{ const o=document.createElement('option'); o.value=cp.code; o.textContent=cp.label||cp.code; sel.appendChild(o); });

  }catch(err){ console.warn('eligible cupom erro', err); }

}



async function aplicarCupom(code){

  $('#cupom-feedback').textContent='';

  if(!code || !ENDPOINTS.coupons_validate){ state.cupom=null; state.desconto=0; recalc(); return; }

  try{

    const body={ code, items: state.itens.map(i=>({id:i.id,qty:i.qty,price:i.price})), customer_id: state.cliente? state.cliente.id : null };

    const r=await httpPost(ENDPOINTS.coupons_validate, body);

    const j=await r.json();

    if(r.ok && j.valido){ state.cupom=code; state.desconto=Number(j.desconto||0); $('#cupom-feedback').textContent=j.mensagem||'Cupom aplicado.'; }

    else{ state.cupom=null; state.desconto=0; $('#cupom-feedback').textContent=j.mensagem||'Cupom inválido.'; }

    recalc();

  }catch(err){ console.error('cupom validate erro', err); state.cupom=null; state.desconto=0; recalc(); }

}
$('#btn-aplicar-cupom').onclick=()=>aplicarCupom(($('#cupom').value||'').trim());
$('#btn-aplicar-cupom-select').onclick=()=>aplicarCupom($('#cupom-select').value||'');



/* === TOTAIS / PAGAMENTO === */

function recalc(){

  const subtotal=state.itens.reduce((s,i)=>s+i.price*i.qty,0);

  const entrega=Number(state.entrega||0), desconto=Number(state.desconto||0);

  const total=Math.max(0, subtotal - desconto + entrega);

  $('#subtotal').textContent=money(subtotal);

  $('#desconto').textContent=money(desconto);

  $('#entrega').textContent =money(entrega);

  $('#total').textContent   =money(total);

}
document.addEventListener('change', e=>{ if(e.target?.name==='pagamento'){ state.pagamento=e.target.value; }});



/* === FINALIZAR === */

$('#btn-finalizar').onclick=async ()=>{

  if(!state.itens.length){ alert('Adicione itens.'); return; }

  try{

    const payload={

      customer: state.cliente,

      address: { rua:$('#end-rua').value, numero:$('#end-num').value, cep:$('#end-cep').value, complemento:$('#end-compl').value, bairro:$('#end-bairro').value, cidade:$('#end-cidade').value, uf:$('#end-uf').value },

      items: state.itens,

      entrega_opcao: $('#entrega-opcao').value||null,

      observacoes: $('#pedido-observacoes').value||'',

      pagamento: state.pagamento, // 'pix' | 'link' | 'fiado'

      cupom: state.cupom

    };

    const r=await httpPost(ENDPOINTS.order_store, payload);

    if(!r.ok){ const t=await r.text(); alert('Erro ao finalizar: '+t); return; }

    alert('Pedido salvo!');

  }catch(err){ console.error('finalizar erro', err); alert('Falha ao salvar.'); }

};

</script>
@endpush