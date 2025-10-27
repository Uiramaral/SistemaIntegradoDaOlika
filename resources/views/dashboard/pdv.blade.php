@extends('layouts.dashboard')

@section('title', 'Ponto de Venda (PDV)')

@section('content')

<div class="page-header flex items-center justify-between mb-6">
  <div>
    <h1 class="text-xl font-semibold">Status & Templates</h1>
    <p class="text-sm text-gray-500">Gerencie os status dos pedidos e templates de mensagens</p>
  </div>

  {{-- Estes dois botões aparecem na página de referência --}}
  <div class="flex gap-2">
    <a href="{{ route('dashboard.layout.download') }}" class="btn btn-soft">Baixar Layout</a>
    <a href="{{ route('dashboard.status.create') }}" class="btn btn-primary">+ Novo Status</a>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <h2 class="text-lg font-semibold">Ponto de Venda (PDV)</h2>
  </div>
  <div class="card-body">
    {{-- CLIENTE --}}
    <section class="mb-6">
      <h3 class="section-title">Cliente</h3>

      <div class="grid grid-cols-1 lg:grid-cols-12 gap-3">
        <div class="lg:col-span-4">
          <input id="cliente-busca" type="text" class="input"
                 placeholder="buscar por nome, telefone, e-mail" autocomplete="off">
          <div id="cliente-sugestoes" class="suggestions"></div>
        </div>

        <div class="lg:col-span-3">
          <input id="cliente-nome" type="text" class="input" placeholder="Nome">
        </div>
        <div class="lg:col-span-3">
          <input id="cliente-telefone" type="tel" class="input" placeholder="Telefone (E164)">
        </div>
        <div class="lg:col-span-2">
          <input id="cliente-email" type="email" class="input" placeholder="E-mail">
        </div>
      </div>
    </section>

    {{-- ENDEREÇO --}}
    <section class="mb-6">
      <h3 class="section-title">Endereço</h3>

      <div class="grid grid-cols-1 lg:grid-cols-12 gap-3">
        <div class="lg:col-span-6">
          <input id="end-rua" class="input" placeholder="Rua *">
        </div>
        <div class="lg:col-span-2">
          <input id="end-num" class="input" placeholder="N° *">
        </div>
        <div class="lg:col-span-2">
          <input id="end-cep" class="input" placeholder="CEP">
        </div>
        <div class="lg:col-span-2">
          <input id="end-compl" class="input" placeholder="Compl.">
        </div>

        <div class="lg:col-span-3">
          <input id="end-bairro" class="input" placeholder="Bairro">
        </div>
        <div class="lg:col-span-5">
          <input id="end-cidade" class="input" placeholder="Cidade *">
        </div>
        <div class="lg:col-span-2">
          <input id="end-uf" class="input" placeholder="UF *" maxlength="2">
        </div>
        <div class="lg:col-span-2">
          <button id="btn-salvar-endereco" type="button" class="btn btn-soft w-full">Salvar Endereço</button>
        </div>
      </div>
    </section>

    {{-- ITENS --}}
    <section class="mb-6">
      <h3 class="section-title">Itens</h3>

      <div class="lg:max-w-md mb-3">
        <input id="produto-busca" type="text" class="input"
               placeholder="buscar produto por nome ou SKU" autocomplete="off">
        <div id="produto-sugestoes" class="suggestions"></div>
      </div>

      <div class="table-wrapper">
        <table class="table">
          <thead>
            <tr>
              <th>Produto</th>
              <th class="text-right">Preço</th>
              <th class="text-center">Qtd</th>
              <th class="text-right">Total</th>
              <th></th>
            </tr>
          </thead>
          <tbody id="tabela-itens">
            {{-- linhas adicionadas via JS --}}
          </tbody>
        </table>
      </div>
    </section>

    {{-- ENTREGA --}}
    <section class="mb-6">
      <h3 class="section-title">Entrega</h3>

      <select id="entrega-opcao" class="input mb-3">
        <option value="" selected>Selecione um endereço e itens para calcular opções...</option>
      </select>

      <textarea id="pedido-observacoes" class="textarea" placeholder="Observações do pedido"></textarea>
    </section>

    {{-- RESUMO / CUPOM --}}
    <section class="mb-6">
      <h3 class="section-title">Resumo</h3>

      <div class="flex items-center gap-2 mb-3">
        <input id="cupom" type="text" class="input w-40" placeholder="Cupom">
        <button id="btn-aplicar-cupom" type="button" class="btn btn-soft">Aplicar</button>
        <span id="cupom-feedback" class="text-sm text-gray-500"></span>
      </div>

      <div class="totais">
        <div class="totais-row"><span>Subtotal</span><span id="subtotal">R$ 0,00</span></div>
        <div class="totais-row"><span>Desconto</span><span id="desconto">R$ 0,00</span></div>
        <div class="totais-row"><span>Entrega</span><span id="entrega">R$ 0,00</span></div>
        <div class="totais-row font-semibold text-lg"><span>Total</span><span id="total">R$ 0,00</span></div>
      </div>
    </section>

    {{-- PAGAMENTO --}}
    <section>
      <h3 class="section-title">Pagamento</h3>

      <div class="flex flex-wrap gap-4 mb-4">
        <label class="radio"><input type="radio" name="pagamento" value="pix" checked> PIX</label>
        <label class="radio"><input type="radio" name="pagamento" value="link"> Link Mercado Pago</label>
        <label class="radio"><input type="radio" name="pagamento" value="dinheiro"> Dinheiro</label>
        <label class="radio"><input type="radio" name="pagamento" value="cartao"> Cartão presencial</label>
      </div>

      <button id="btn-finalizar" class="btn btn-primary w-full lg:w-auto">
        Finalizar Pedido
      </button>
    </section>
  </div>
</div>

{{-- Templates ocultos para tabela de itens --}}
<template id="tpl-item">
  <tr data-id="">
    <td class="produto-nome"></td>
    <td class="text-right">
      <input type="number" step="0.01" class="input input-xs text-right item-preco" value="">
    </td>
    <td class="text-center">
      <div class="inline-flex items-center gap-1">
        <button type="button" class="btn btn-xs btn-soft dec">–</button>
        <input type="number" min="1" class="input input-xs text-center item-qtd" value="1">
        <button type="button" class="btn btn-xs btn-soft inc">+</button>
      </div>
    </td>
    <td class="text-right item-total">R$ 0,00</td>
    <td class="text-right">
      <button type="button" class="btn btn-xs btn-danger remove">Remover</button>
    </td>
  </tr>
</template>
@endsection

@push('styles')
<style>
  /* utilitários/ajustes leves para ficar igual ao mock */
  .card{background:#fff;border-radius:14px;box-shadow:0 2px 10px rgba(0,0,0,.04)}
  .card-header{padding:18px 20px;border-bottom:1px solid #f0f0f0}
  .card-body{padding:20px}
  .input{width:100%;border:1px solid #eee;background:#fff;border-radius:12px;padding:10px 12px}
  .input-xs{padding:6px 8px;border-radius:10px}
  .textarea{width:100%;min-height:90px;border:1px solid #eee;border-radius:12px;padding:10px 12px}
  .btn{display:inline-flex;align-items:center;justify-content:center;height:38px;padding:0 14px;border-radius:12px}
  .btn-xs{height:28px;padding:0 10px;border-radius:10px}
  .btn-primary{background:#ff7f2a;color:#fff}
  .btn-soft{background:#f7f7f7}
  .btn-danger{background:#ef4444;color:#fff}
  .radio{display:inline-flex;gap:8px;align-items:center}
  .section-title{font-weight:600;margin-bottom:10px}
  .table-wrapper{border:1px solid #f2f2f2;border-radius:12px;overflow:hidden}
  .table{width:100%}
  .table th,.table td{padding:10px 12px;border-bottom:1px solid #f5f5f5;background:#fff}
  .table th{background:#fafafa;font-weight:600}
  .totais{max-width:340px}
  .totais-row{display:flex;align-items:center;justify-content:space-between;padding:6px 0}
  .suggestions{position:relative}
  .suggestions ul{position:absolute;z-index:30;width:100%;background:#fff;border:1px solid #eee;border-radius:10px;margin-top:6px;max-height:220px;overflow:auto}
  .suggestions li{padding:8px 10px;cursor:pointer}
  .suggestions li:hover{background:#fafafa}
</style>
@endpush

@push('scripts')
<script>
/* ===============================
   CONFIG – ajuste as rotas, se necessário
   =============================== */
const ROTA_BUSCA_CLIENTE = @json(route('api.customers.search'));     // GET ?q=
const ROTA_BUSCA_PRODUTO = @json(route('api.products.search'));      // GET ?q=
const ROTA_VALIDAR_CUPOM = @json(route('api.coupons.validate'));     // POST {code, items:[{id,qty,price}]}
const MOEDA = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' });

/* ===============================
   STATE
   =============================== */
const state = {
  cliente: null,
  endereco: {},
  itens: [],       // {id, nome, price, qty}
  entrega: 0,
  desconto: 0,
  cupom: null,
};

/* ===============================
   UTILS
   =============================== */
const $ = (sel, ctx=document) => ctx.querySelector(sel);
function debounce(fn, wait=300){ let t; return (...a)=>{clearTimeout(t); t=setTimeout(()=>fn(...a), wait)} }
function renderMoney(v){ return MOEDA.format(Number(v||0)) }

/* ===============================
   CLIENTE – busca e preenchimento
   =============================== */
const clienteBusca = $('#cliente-busca');
const clienteSug = $('#cliente-sugestoes');

clienteBusca.addEventListener('input', debounce(async (ev)=>{
  const q = ev.target.value.trim();
  clienteSug.innerHTML = '';
  if(!q){ return; }
  const url = new URL(ROTA_BUSCA_CLIENTE);
  url.searchParams.set('q', q);
  const res = await fetch(url);
  if(!res.ok) return;
  const dados = await res.json(); // [{id,nome,telefone,email,endereco?}]
  if(!dados.length) return;
  const ul = document.createElement('ul');
  dados.forEach(c=>{
    const li = document.createElement('li');
    li.textContent = `${c.nome} • ${c.telefone||''} ${c.email? '• '+c.email:''}`;
    li.addEventListener('click', ()=>selecionarCliente(c));
    ul.appendChild(li);
  });
  clienteSug.appendChild(ul);
}, 250));

function selecionarCliente(c){
  state.cliente = c;
  $('#cliente-nome').value = c.nome||'';
  $('#cliente-telefone').value = c.telefone||'';
  $('#cliente-email').value = c.email||'';
  if(c.endereco){
    $('#end-rua').value = c.endereco.rua||'';
    $('#end-num').value = c.endereco.numero||'';
    $('#end-cep').value = c.endereco.cep||'';
    $('#end-compl').value = c.endereco.complemento||'';
    $('#end-bairro').value = c.endereco.bairro||'';
    $('#end-cidade').value = c.endereco.cidade||'';
    $('#end-uf').value = c.endereco.uf||'';
  }
  clienteSug.innerHTML = '';
  clienteBusca.value = '';
}

/* ===============================
   PRODUTO – busca e adição
   =============================== */
const produtoBusca = $('#produto-busca');
const produtoSug = $('#produto-sugestoes');

produtoBusca.addEventListener('input', debounce(async (ev)=>{
  const q = ev.target.value.trim();
  produtoSug.innerHTML = '';
  if(!q){ return; }
  const url = new URL(ROTA_BUSCA_PRODUTO);
  url.searchParams.set('q', q);
  const res = await fetch(url);
  if(!res.ok) return;
  const dados = await res.json(); // [{id,nome,preco}]
  if(!dados.length) return;
  const ul = document.createElement('ul');
  dados.forEach(p=>{
    const li = document.createElement('li');
    li.textContent = `${p.nome} — ${renderMoney(p.preco)}`;
    li.addEventListener('click', ()=>{ addItem(p); produtoSug.innerHTML=''; produtoBusca.value=''; });
    ul.appendChild(li);
  });
  produtoSug.appendChild(ul);
}, 250));

function addItem(p){
  const existente = state.itens.find(i=>i.id===p.id);
  if(existente){ existente.qty += 1; }
  else{
    state.itens.push({ id:p.id, nome:p.nome, price:Number(p.preco), qty:1 });
  }
  renderItens();
  recalc();
}

function removeItem(id){
  state.itens = state.itens.filter(i=>i.id!==id);
  renderItens();
  recalc();
}

function renderItens(){
  const tbody = $('#tabela-itens');
  tbody.innerHTML = '';
  const tpl = $('#tpl-item').content;
  state.itens.forEach(item=>{
    const row = document.importNode(tpl, true);
    const tr = row.querySelector('tr');
    tr.dataset.id = item.id;
    tr.querySelector('.produto-nome').textContent = item.nome;
    const inpPreco = tr.querySelector('.item-preco');
    const inpQtd   = tr.querySelector('.item-qtd');
    const cellTot  = tr.querySelector('.item-total');
    inpPreco.value = item.price.toFixed(2);
    inpQtd.value   = item.qty;
    const atualizar = ()=>{
      item.price = Number(inpPreco.value||0);
      item.qty   = Math.max(1, Number(inpQtd.value||1));
      cellTot.textContent = renderMoney(item.price*item.qty);
      recalc();
    };
    tr.querySelector('.inc').addEventListener('click', ()=>{ inpQtd.value = Number(inpQtd.value||1)+1; atualizar(); });
    tr.querySelector('.dec').addEventListener('click', ()=>{ inpQtd.value = Math.max(1, Number(inpQtd.value||1)-1); atualizar(); });
    inpPreco.addEventListener('change', atualizar);
    inpQtd.addEventListener('change', atualizar);
    tr.querySelector('.remove').addEventListener('click', ()=>removeItem(item.id));
    cellTot.textContent = renderMoney(item.price*item.qty);
    tbody.appendChild(tr);
  });
}

/* ===============================
   CUPOM
   =============================== */
$('#btn-aplicar-cupom').addEventListener('click', async ()=>{
  const code = ($('#cupom').value||'').trim();
  $('#cupom-feedback').textContent = '';
  if(!code){ state.cupom=null; state.desconto=0; recalc(); return; }
  try{
    const res = await fetch(ROTA_VALIDAR_CUPOM, {
      method:'POST',
      headers:{ 'Content-Type':'application/json', 'X-CSRF-TOKEN':'{{ csrf_token() }}' },
      body: JSON.stringify({ code, items: state.itens.map(i=>({id:i.id, qty:i.qty, price:i.price})) })
    });
    if(!res.ok) throw new Error('Falha ao validar cupom');
    const data = await res.json(); // { valido:true/false, tipo:'percent|valor', valor:10, mensagem? }
    if(data.valido){
      state.cupom = code;
      state.desconto = Number(data.desconto||0);
      $('#cupom-feedback').textContent = data.mensagem || 'Cupom aplicado.';
    }else{
      state.cupom = null;
      state.desconto = 0;
      $('#cupom-feedback').textContent = data.mensagem || 'Cupom inválido.';
    }
    recalc();
  }catch(e){
    console.error(e);
    state.cupom=null; state.desconto=0; recalc();
    $('#cupom-feedback').textContent = 'Não foi possível validar o cupom.';
  }
});

/* ===============================
   TOTAIS
   =============================== */
function recalc(){
  const subtotal = state.itens.reduce((s,i)=> s + i.price*i.qty, 0);
  const entrega  = Number(state.entrega||0);
  const desconto = Number(state.desconto||0);
  const total    = Math.max(0, subtotal - desconto + entrega);
  $('#subtotal').textContent = renderMoney(subtotal);
  $('#desconto').textContent = renderMoney(desconto);
  $('#entrega').textContent  = renderMoney(entrega);
  $('#total').textContent    = renderMoney(total);
}

/* ===============================
   FINALIZAR
   =============================== */
$('#btn-finalizar').addEventListener('click', ()=>{
  // aqui você reaproveita a rota que já salva o pedido no seu sistema
  // monte o payload a partir de "state" + campos da página
  alert('Finalização: integrar com sua rota de pedidos.');
});
</script>
@endpush