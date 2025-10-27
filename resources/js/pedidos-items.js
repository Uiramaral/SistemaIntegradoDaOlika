// Pequeno bootstrap de Alpine sem precisar instalar (usa CDN para runtime)
// Se você já injeta Alpine via CDN no <head>, este arquivo só registra o componente.

(function(){
  const ensureAlpine = () => new Promise((resolve) => {
    if (window.Alpine) return resolve(window.Alpine);
    const s = document.createElement('script');
    s.src = 'https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js';
    s.defer = true;
    s.onload = () => resolve(window.Alpine);
    document.head.appendChild(s);
  });

  ensureAlpine().then((Alpine) => {
    Alpine.data('pedidoItems', (payload) => {
      const fmt = (v) => Number.isFinite(+v) ? +(+v).toFixed(2) : 0;

      return {
        produtos: payload.produtos || [],
        items: (payload.initialItems || [{ produto_id: '', quantidade: 1, preco_unit: 0 }]).map(i => ({
          produto_id: i.produto_id || '', 
          quantidade: +i.quantidade || 1, 
          preco_unit: fmt(i.preco_unit || i.preco || 0)
        })),
        taxaEntrega: fmt(payload.taxaEntrega || 0),
        desconto: fmt(payload.desconto || 0),
        cupomCodigo: payload.cupomCodigo || '',
        cupomValor: 0,
        cupomTipo: null,

        add(){ this.items.push({ produto_id:'', quantidade:1, preco_unit:0 }); },
        remove(idx){ this.items.splice(idx,1); if(this.items.length===0) this.add(); },
        onProdutoChange(idx, ev){
          const opt = ev.target.selectedOptions[0];
          const preco = opt?.dataset?.preco;
          if (preco && !this.items[idx].preco_unit) { this.items[idx].preco_unit = fmt(preco); }
        },

        async aplicarCupom() {
          const term = (this.cupomCodigo||'').trim();
          if (!term) { this.cupomValor = 0; this.cupomTipo = null; return; }
          
          if (!window.__CUPONS__) { this.cupomValor = 0; this.cupomTipo = null; return; }
          
          const c = window.__CUPONS__.find(x => (x.codigo||'').toLowerCase() === term.toLowerCase());
          if (!c || !c.ativo) { this.cupomValor = 0; this.cupomTipo = null; return; }
          
          const now = new Date();
          const iniOK = !c.validade_inicio || new Date(c.validade_inicio) <= now;
          const fimOK = !c.validade_fim || new Date(c.validade_fim) >= now;
          if (!iniOK || !fimOK) { this.cupomValor = 0; this.cupomTipo = null; return; }

          const subtotal = this.itensTotal();
          if (c.minimo_pedido && subtotal < +c.minimo_pedido) { this.cupomValor = 0; this.cupomTipo = null; return; }

          if (c.tipo === 'percent') { this.cupomValor = fmt(subtotal * (+c.valor/100)); this.cupomTipo = 'percent'; }
          else { this.cupomValor = fmt(+c.valor); this.cupomTipo = 'valor'; }
        },

        linhaSubtotal(i){ return fmt((+i.quantidade||0) * (fmt(i.preco_unit)||0)); },
        itensTotal(){ return this.items.reduce((acc,i)=> acc + this.linhaSubtotal(i), 0); },
        totalGeral(){
          const bruto = this.itensTotal() + (this.taxaEntrega||0);
          const total = bruto - (this.desconto||0) - (this.cupomValor||0);
          return fmt(Math.max(0,total));
        },
      }
    });

    if (!Alpine.version) return; // CDN já roda start automático
  });
})();
