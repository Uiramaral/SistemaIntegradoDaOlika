const els = {
  finalize: document.getElementById('finalize'),
  cartBody: document.querySelector('#cart-table tbody'),
};
const cart = [];
function money(v){ return v.toLocaleString('pt-BR',{style:'currency',currency:'BRL'}); }
function renderCart(){
  els.cartBody.innerHTML = cart.map((i,idx)=>`<tr>
    <td>${i.name}</td><td>${money(i.price)}</td><td>${i.qty}</td><td>${money(i.price*i.qty)}</td>
    <td><button data-i="${idx}" class="ol-link rm">remover</button></td></tr>`).join('');
  const sub = cart.reduce((s,i)=>s+i.price*i.qty,0);
  document.getElementById('sum-sub').textContent = money(sub);
  document.getElementById('sum-total').textContent = money(sub); // simplificado (desconto/entrega calculados no back)
}
document.addEventListener('click',e=>{
  if(e.target.matches('.rm')){ cart.splice(+e.target.dataset.i,1); renderCart(); }
});

els.finalize.addEventListener('click', async ()=>{
  if(!cart.length){ alert('Adicione itens ao carrinho.'); return; }
  const payload = {
    customer_id: document.getElementById('cli-name').value ? 1 : null,
    items: cart.map(i=>({ name:i.name, price:i.price, qty:i.qty })),
    payment_method: document.querySelector('input[name="pay"]:checked').value, // pix|link-mp|fiado
    note: document.getElementById('order-notes').value,
    coupon_code: document.getElementById('coupon-code').value
  };
  try{
    const res = await fetch(window.Olika.routes.pdvStore,{
      method:'POST',
      headers:{'Content-Type':'application/json','X-CSRF-TOKEN':window.Olika.csrf},
      body: JSON.stringify(payload)
    });
    const out = await res.json();
    if(!out.ok){ alert('Erro ao criar pedido'); return; }
    if(out.init_point){ window.open(out.init_point,'_blank'); }
    if(out.pix?.qr_code_base64){
      const w = window.open('','PIX','width=420,height=540');
      w.document.write(`<img style="width:380px" src="data:image/png;base64,${out.pix.qr_code_base64}"><p>${out.pix.qr_code}</p>`);
    }
    alert(`Pedido #${out.number} criado com sucesso!`);
    cart.length=0; renderCart();
  }catch(e){ alert('Erro de conexão'); }
});
