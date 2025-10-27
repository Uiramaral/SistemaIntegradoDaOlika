(function() {
  const tokenMeta = document.querySelector('meta[name="csrf-token"]');
  const token = tokenMeta ? tokenMeta.getAttribute('content') : '';

  function updateCartBadge(count) {
    const badge = document.querySelector('[data-cart-count]');
    if (badge) badge.textContent = count;
  }

  function toast(msg, ok=true) {
    let el = document.querySelector('#toast');
    if (!el) {
      el = document.createElement('div');
      el.id = 'toast';
      el.style.position='fixed';
      el.style.bottom='20px';
      el.style.left='50%';
      el.style.transform='translateX(-50%)';
      el.style.padding='14px 22px';
      el.style.borderRadius='8px';
      el.style.color='#fff';
      el.style.fontWeight='600';
      el.style.background= ok ? '#16a34a' : '#dc2626';
      el.style.zIndex='9999';
      el.style.transition='opacity .3s ease';
      document.body.appendChild(el);
    }
    el.textContent = msg;
    el.style.opacity = '1';
    setTimeout(()=>el.style.opacity='0',3000);
  }

  document.addEventListener('click', async e => {
    const btn = e.target.closest('.js-add-to-cart');
    if (!btn) return;
    e.preventDefault();
    btn.disabled = true;
    btn.style.opacity = '.7';
    try {
      const res = await fetch(btn.dataset.endpoint || window.cartAddEndpoint, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': token,
          'Accept': 'application/json'
        },
        body: JSON.stringify({
          product_id: btn.dataset.productId,
          qty: btn.dataset.qty || 1
        })
      });
      const data = await res.json().catch(() => ({}));
      if (!res.ok || !data.ok) throw new Error(data.message || 'Erro ao adicionar');
      updateCartBadge(data.cart_count ?? 0);
      toast(data.message || 'Item adicionado!', true);
    } catch(err) {
      console.error(err);
      toast(err.message || 'Falha na conexão', false);
    } finally {
      btn.disabled = false;
      btn.style.opacity = '1';
    }
  });

  // Se quiser reaproveitar via form fallback:
  document.addEventListener('submit', function(e){
    const form = e.target.closest('.add-to-cart-fallback');
    if (!form) return;
    e.preventDefault();
    const btn = form.parentElement.querySelector('.js-add-to-cart');
    if (btn) btn.click(); else form.submit();
  });

})();

// ===== Modal de Produto (abrir/fechar) =====
(function(){
  const pmask = document.getElementById('product-modal');
  if(!pmask) return;

  const img = document.getElementById('pm-img');
  const name = document.getElementById('pm-name');
  const desc = document.getElementById('pm-desc');
  const price = document.getElementById('pm-price');
  const qtyEl = document.getElementById('pm-qty');
  const addBtn = document.getElementById('pm-add');
  let currentId = null, unitPrice = 0;

  function moneyToNumber(brl){ return parseFloat(brl.replace(/\./g,'').replace(',','.')); }
  function numberToMoney(n){ return n.toFixed(2).replace('.',','); }

  function openModal(card){
    currentId = card.dataset.id;
    img.src = card.dataset.image;
    img.alt = card.dataset.name;
    name.textContent = card.dataset.name;
    desc.textContent = card.dataset.desc || '';
    price.textContent = 'R$ ' + card.dataset.price;
    unitPrice = moneyToNumber(card.dataset.price);
    qtyEl.textContent = '1';
    addBtn.textContent = 'Adicionar • R$ ' + card.dataset.price;
    pmask.classList.add('show');
  }
  function closeModal(){ pmask.classList.remove('show'); }

  document.addEventListener('click', e=>{
    // abrir quando clicar em .js-open-modal dentro de .js-product
    const openEl = e.target.closest('.js-open-modal');
    if(openEl){
      const card = e.target.closest('.js-product');
      if(card) openModal(card);
      return;
    }
    // fechar
    if(e.target.closest('.pclose') || (!e.target.closest('.pdialog') && e.target.closest('.pmask'))){
      closeModal();
      return;
    }
  });

  // qty
  document.addEventListener('click', e=>{
    if(e.target.matches('.pm-qty-inc')) {
      qtyEl.textContent = String( (+qtyEl.textContent) + 1 );
      addBtn.textContent = 'Adicionar • R$ ' + numberToMoney(unitPrice * (+qtyEl.textContent));
    }
    if(e.target.matches('.pm-qty-dec')) {
      const v = Math.max(1, (+qtyEl.textContent)-1);
      qtyEl.textContent = String(v);
      addBtn.textContent = 'Adicionar • R$ ' + numberToMoney(unitPrice * v);
    }
  });

  // adicionar ao carrinho via AJAX a partir do modal
  addBtn.addEventListener('click', async ()=>{
    try{
      const res = await fetch(window.cartAddEndpoint, {
        method:'POST',
        headers:{
          'Content-Type':'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
          'Accept':'application/json'
        },
        body: JSON.stringify({ product_id: currentId, qty: +qtyEl.textContent })
      });
      const data = await res.json();
      if(!res.ok || !data.ok) throw new Error(data.message || 'Erro ao adicionar');
      const badge = document.querySelector('[data-cart-count]');
      if(badge) badge.textContent = data.cart_count ?? 0;
      closeModal();
    }catch(err){ alert(err.message); }
  });

  // impedir que o clique no botão "+" abra o modal por engano
  document.addEventListener('click', e=>{
    if(e.target.closest('.js-add-to-cart')){
      e.stopPropagation();
    }
  });
})();

// ===== Toggle: 2 col / lista =====
(function(){
  const grid = document.querySelector('.products-grid');
  if(!grid) return;
  const twoBtn  = document.querySelector('.js-grid-2');
  const listBtn = document.querySelector('.js-grid-list');

  function setTwo(){
    grid.classList.remove('list');
    grid.style.setProperty('--cols', 2);
    document.querySelectorAll('.tool-btn').forEach(b=>b.classList.remove('active'));
    if(twoBtn) twoBtn.classList.add('active');
  }
  function setList(){
    grid.classList.add('list');
    document.querySelectorAll('.tool-btn').forEach(b=>b.classList.remove('active'));
    if(listBtn) listBtn.classList.add('active');
  }

  twoBtn?.addEventListener('click', setTwo);
  listBtn?.addEventListener('click', setList);

  // estado inicial vindo do data-view (two/list)
  if(grid.dataset.view === 'list'){ setList(); } else { setTwo(); }
})();

