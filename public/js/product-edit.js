function ProductEdit(){
  return {
    async upload(fileList){
      const fd = new FormData();
      Array.from(fileList||[]).forEach(f => fd.append('images[]', f));

      const r = await fetch(PROD_ROUTES.upload, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': PROD_ROUTES.csrf },
        body: fd
      });

      const j = await r.json();
      if(!j.ok){ alert('Falha no upload'); return; }
      location.reload();
    },

    async setPrimary(id){
      const url = PROD_ROUTES.primary.replace(/\/0(\/|$)/, `/${id}$1`);
      const r = await fetch(url, { method:'PATCH', headers:{'X-CSRF-TOKEN':PROD_ROUTES.csrf} });
      const j = await r.json();
      if(j.ok) location.reload();
    },

    async removeImg(id){
      if(!confirm('Remover imagem?')) return;
      const url = PROD_ROUTES.destroy.replace(/\/0(\/|$)/, `/${id}$1`);
      const r = await fetch(url, { method:'DELETE', headers:{'X-CSRF-TOKEN':PROD_ROUTES.csrf} });
      const j = await r.json();
      if(j.ok) location.reload();
    },

    async move(id, dir){
      const url = PROD_ROUTES.move.replace(/\/0\//, `/${id}/`).replace(/\/(up|down)$/, `/${dir}`);
      const r = await fetch(url, { method:'PATCH', headers:{'X-CSRF-TOKEN':PROD_ROUTES.csrf} });
      const j = await r.json();
      if(j.ok) location.reload();
    }
  };
}

document.addEventListener('alpine:init', () => Alpine.data('ProductEdit', ProductEdit));

// --- Prévia automática da descrição (cliente) ---
(function(){
  const preview = document.getElementById('auto-desc-preview');
  if(!preview) return;

  const $ = (s, all=false) => all ? Array.from(document.querySelectorAll(s)) : document.querySelector(s);
  function val(node){ return (node && 'value' in node) ? node.value : ''; }
  function checked(node){ return !!(node && node.checked); }

  function build(){
    const name  = val($('input[name="name"]')).trim();
    const price = parseFloat(val($('input[name="price"]')).replace(',', '.')) || null;
    const cat   = $('select[name="category_id"]');
    const catTxt = cat && cat.options && cat.selectedIndex>0 ? cat.options[cat.selectedIndex].text : null;

    const gf   = checked($('input[name="gluten_free"]'));
    const risk = checked($('input[name="contamination_risk"]'));

    const allergenNames = $('input[name="allergens[]"]', true)
      .filter(x => x.checked)
      .map(x => x.parentElement && x.parentElement.querySelector('span') ? x.parentElement.querySelector('span').textContent.trim() : '')
      .filter(Boolean);

    let lines = [];
    if(name){ lines.push(catTxt ? `${name} — ${catTxt}` : name); }
    if(price !== null){ lines.push(`Preço de referência: R$ ${price.toFixed(2).replace('.', ',')}`); }
    if(gf) lines.push('Produto sem glúten.');
    if(allergenNames.length){ lines.push('Contém: ' + allergenNames.join(', ') + '.'); }
    if(risk) lines.push('⚠️ Pode conter traços de glúten devido ao ambiente de produção.');

    preview.value = lines.join(' ');
  }

  ['input','change'].forEach(evt => {
    document.addEventListener(evt, (e) => {
      const t = e.target; if(!t) return;
      if (t.matches('input[name="name"], input[name="price"], select[name="category_id"], input[name="gluten_free"], input[name="contamination_risk"], input[name="allergens[]"]')) {
        build();
      }
    }, true);
  });

  build();
})();

// --- Prévia automática da descrição de rótulo (curta, sem preço) ---
(function(){
  const preview = document.getElementById('auto-label-preview');
  if(!preview) return;

  const $ = (s, all=false) => all ? Array.from(document.querySelectorAll(s)) : document.querySelector(s);
  const val = n => (n && 'value' in n) ? n.value : '';
  const checked = n => !!(n && n.checked);

  function build(){
    const name  = val($('input[name="name"]')).trim();
    const catSel= $('select[name="category_id"]');
    const cat   = catSel && catSel.options && catSel.selectedIndex>0 ? catSel.options[catSel.selectedIndex].text : null;
    const gf    = checked($('input[name="gluten_free"]'));
    const risk  = checked($('input[name="contamination_risk"]'));
    const allergenNames = $('input[name="allergens[]"]', true)
      .filter(x => x.checked)
      .map(x => x.parentElement?.querySelector('span')?.textContent.trim() || '')
      .filter(Boolean);

    let parts = [];
    if(name){ parts.push(cat ? `${name} — ${cat}` : name); }
    if(gf)   parts.push('Produto sem glúten.');
    if(allergenNames.length) parts.push('Contém: ' + allergenNames.join(', ') + '.');
    if(risk) parts.push('⚠️ Pode conter traços de glúten devido ao ambiente de produção.');

    preview.value = parts.join(' ');
  }

  ['input','change'].forEach(evt => {
    document.addEventListener(evt, (e) => {
      const t = e.target;
      if(!t) return;
      if (t.matches('input[name="name"], select[name="category_id"], input[name="gluten_free"], input[name="contamination_risk"], input[name="allergens[]"]')) {
        build();
      }
    }, true);
  });

  build();
})();
