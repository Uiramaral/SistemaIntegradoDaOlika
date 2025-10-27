// public/js/olika-store.js
// Handles: open product modal on card/image click; separate from "+" button.
// Also applies category-based view (list or grid2) coming from data attribute.
(function(){
  const $ = (sel, root=document) => root.querySelector(sel);
  const $$ = (sel, root=document) => Array.from(root.querySelectorAll(sel));

  // Modal elements
  const backdrop = document.createElement('div');
  backdrop.className = 'modal-backdrop';
  backdrop.innerHTML = `
    <div class="modal-panel" role="dialog" aria-modal="true" aria-label="Detalhes do produto">
      <div class="modal-head"><img alt="" id="mimg"></div>
      <div class="modal-body">
        <div class="modal-title" id="mtitle"></div>
        <div class="modal-desc" id="mdesc"></div>
        <div class="modal-price" id="mprice"></div>
        <div class="modal-actions">
          <div class="qty-picker">
            <button type="button" data-act="minus" aria-label="Diminuir quantidade">−</button>
            <span id="mqty">1</span>
            <button type="button" data-act="plus" aria-label="Aumentar quantidade">+</button>
          </div>
          <button class="add-btn" id="madd">Adicionar</button>
        </div>
      </div>
      <button class="modal-close" aria-label="Fechar">×</button>
    </div>`;
  document.body.appendChild(backdrop);

  let currentProduct = null;
  let qty = 1;

  function openModal(product) {
    currentProduct = product;
    qty = 1;
    $('#mimg').src = product.image;
    $('#mtitle').textContent = product.title;
    $('#mdesc').textContent = product.description || '';
    $('#mprice').textContent = product.price_formatted;
    $('#mqty').textContent = qty;
    backdrop.classList.add('open');
  }
  function closeModal(){ backdrop.classList.remove('open'); }

  backdrop.addEventListener('click', (e)=>{
    if(e.target === backdrop) closeModal();
  });
  backdrop.querySelector('.modal-close').addEventListener('click', closeModal);
  backdrop.querySelector('[data-act="minus"]').addEventListener('click', ()=>{
    qty = Math.max(1, qty-1);
    $('#mqty').textContent = qty;
  });
  backdrop.querySelector('[data-act="plus"]').addEventListener('click', ()=>{
    qty = qty+1;
    $('#mqty').textContent = qty;
  });
  $('#madd').addEventListener('click', async ()=>{
    if(!currentProduct) return;
    try {
      const res = await fetch(`/pedido/cart/add`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify({ product_id: currentProduct.id, quantity: qty })
      });
      if(!res.ok) throw new Error('Falha ao adicionar');
      // Ideally update cart badge
      const { cart_count } = await res.json().catch(()=>({}));
      const badge = $('.header-cart .badge');
      if(badge && typeof cart_count !== 'undefined') badge.textContent = cart_count;
      closeModal();
    } catch(err){
      console.error(err);
      closeModal();
      alert('Não foi possível adicionar ao carrinho.');
    }
  });

  // Prevent "+" from opening modal; only image/card open
  $$('.product-card').forEach(card => {
    const addBtn = card.querySelector('.add-btn');
    if(addBtn){
      addBtn.addEventListener('click', (e)=>{
        e.stopPropagation();
      });
    }
    const product = {
      id: parseInt(card.dataset.id,10),
      title: card.dataset.title,
      price_formatted: card.dataset.priceFormatted,
      image: card.dataset.image,
      description: card.dataset.description || ''
    };
    // Click areas (image and title)
    const clickAreas = [card.querySelector('.product-media'), card.querySelector('.product-title')].filter(Boolean);
    clickAreas.forEach(el => {
      el.style.cursor = 'pointer';
      el.addEventListener('click', ()=> openModal(product));
    });
  });

  // View switcher
  const grid = $('.products-grid');
  const list = $('.products-list');
  const viewButtons = $$('.view-btn');
  viewButtons.forEach(btn => {
    btn.addEventListener('click', ()=>{
      viewButtons.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      const mode = btn.dataset.mode; // 'grid2' or 'list'
      document.body.dataset.view = mode;
      if(grid && list){
        grid.style.display = (mode === 'grid2') ? 'grid' : 'none';
        list.style.display = (mode === 'list') ? 'grid' : 'none';
      }
      // Persist per-category via API if data-category-id exists on container
      const shell = $('.store-shell');
      if(shell && shell.dataset.categoryId){
        fetch('/dashboard/category/view-mode', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
          body: JSON.stringify({ category_id: shell.dataset.categoryId, display_mode: mode })
        }).catch(()=>{});
      }
    });
  });

  // Initial view from data-default-view
  const root = $('.store-shell');
  if(root){
    const initial = root.dataset.defaultView || 'grid2';
    const btn = $(`.view-btn[data-mode="${initial}"]`);
    if(btn) btn.click();
  }
})();
