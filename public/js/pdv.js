/* global Alpine, window, fetch */
function j(v){ return JSON.stringify(v); }
function money(n){ n = Number(n||0); return n.toLocaleString('pt-BR',{style:'currency',currency:'BRL'}); }

function PDV(){
  return {
    // estado
    form:{
      cep:'', customer:{ id:null, name:'', phone:'', email:'' },
      address:{ street:'', number:'', complement:'', district:'', city:'', state:'' },
      delivery:{ option:'' },
      coupon:{ code:'', selected:'' },
      payment:'pix', notes:''
    },
    cart:[],
    totals:{ subtotal:0, discount:0, delivery:0, total:0 },
    coupons:{ eligible:[] }, manualCoupon:false,
    deliveryOptions:[],
    cepHint:'',

    avulso:{ desc:'', price:0, qty:1 },

    cbx:{
      customer:{ open:false, query:'', results:[] },
      product:{ open:false, query:'', results:[] },
      close(which){ this[which].open=false; },
      async search(which){
        const q = this[which].query?.trim(); if(!q || q.length<2){ this[which].results=[]; this[which].open=false; return; }
        const url = which==='customer' ? window.PDV_API.customers : window.PDV_API.products;
        const r = await fetch(url+'?q='+encodeURIComponent(q));
        const data = await r.json();
        this[which].results = data.items || [];
        this[which].open = true;
      },
      pick(which, opt){
        if(which==='customer'){
          this.customer.open=false;
          this.customer.query = opt.label;
          const f = Alpine.$data(document.querySelector('.pdv-page')).form;
          f.customer.id = opt.id; f.customer.name = opt.name; f.customer.phone = opt.phone; f.customer.email = opt.email;
          // se vier endereço padrão, usa:
          if(opt.address){
            f.cep = opt.address.cep || ''; f.address.street = opt.address.street || '';
            f.address.number = opt.address.number || ''; f.address.complement = opt.address.complement || '';
            f.address.district = opt.address.district || ''; f.address.city = opt.address.city || '';
            f.address.state = opt.address.state || '';
          }
        }else{
          this.product.open=false;
          Alpine.$data(document.querySelector('.pdv-page')).addProduct(opt);
        }
      },
      newCustomer(){
        this.customer.open=false;
        // apenas foca no nome para preencher
        document.querySelector('#cli-search')?.blur();
      },
      avulso(){
        this.product.open=false;
        // apenas abre campos de avulso (já estão visíveis)
      }
    },

    money,

    async onCep(){
      const cep = (this.form.cep||'').replace(/\D/g,'');
      if(cep.length<8){ this.cepHint=''; return; }
      try{
        this.cepHint = 'Buscando...';
        const r = await fetch(window.PDV_API.cep+'?cep='+cep);
        const j = await r.json();
        if(j.ok){
          const a = j.address;
          this.form.address.street = a.street || '';
          this.form.address.district = a.district || '';
          this.form.address.city = a.city || '';
          this.form.address.state = a.state || '';
          this.cepHint = (a.city && a.state) ? `${a.city} - ${a.state}` : 'CEP carregado';
        }else{
          this.cepHint = 'CEP não encontrado';
        }
      }catch(_){ this.cepHint='Falha ao buscar CEP'; }
    },

    saveAddress(){ /* opcional: POST do endereço padrão do cliente */ },

    addProduct(p){
      // p: {id, label, price}
      const row = { key: Date.now()+''+Math.random(), id:p.id, name:p.label, price:Number(p.price||0), qty:1 };
      this.cart.push(row); this.recalc();
    },

    addAvulso(){
      if(!this.avulso.desc || !this.avulso.price || !this.avulso.qty) return;
      this.cart.push({ key: Date.now()+''+Math.random(), id:null, name:this.avulso.desc, price:Number(this.avulso.price), qty:Number(this.avulso.qty) });
      this.avulso = { desc:'', price:0, qty:1 }; this.recalc();
    },

    remove(i){ this.cart.splice(i,1); this.recalc(); },

    recalc(){
      const sub = this.cart.reduce((s,i)=> s + (Number(i.price||0)*Number(i.qty||0)), 0);
      this.totals.subtotal = sub;
      // desconto calculado por cupom já mantemos em this.totals.discount
      // delivery conforme option:
      const d = this.deliveryOptions.find(o => o.code===this.form.delivery.option);
      this.totals.delivery = d ? Number(d.value||0) : 0;
      this.totals.total = Math.max(0, sub - Number(this.totals.discount||0) + this.totals.delivery);
    },

    async loadEligibleCoupons(){
      const r = await fetch(window.PDV_API.coupons + (this.form.customer.id ? ('?customer_id='+this.form.customer.id) : ''));
      const j = await r.json();
      this.coupons.eligible = j.items || [];
    },
    toggleManual(){ this.manualCoupon = !this.manualCoupon; },
    applySelectedCoupon(){ this.form.coupon.code = this.form.coupon.selected; this.applyCoupon(); },
    applyCoupon(){
      const code = (this.form.coupon.code||'').trim().toUpperCase();
      // regra simples local (ex.: BEMVINDO 10%) — você pode trocar por endpoint de validação
      const sub = this.totals.subtotal;
      this.totals.discount = code ? sub * 0.10 : 0;
      this.recalc();
    },

    async loadDeliveryOptions(){
      const payload = { address:this.form.address, items:this.cart.map(i=>({id:i.id, qty:i.qty})) };
      const r = await fetch(window.PDV_API.delivery, {
        method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},
        body:j(payload)
      });
      const jresp = await r.json();
      this.deliveryOptions = jresp.items || [];
    },

    async finalize(){
      if(!this.cart.length){ alert('Adicione itens ao carrinho.'); return; }
      // calcular opções de entrega se necessário
      await this.loadDeliveryOptions();

      const payload = {
        customer: this.form.customer,
        address: this.form.address,
        cep: this.form.cep,
        items: this.cart.map(i=>({ product_id:i.id, name:i.name, price:i.price, qty:i.qty })),
        payment_method: this.form.payment,
        notes: this.form.notes,
        coupon_code: this.form.coupon.code,
        delivery_option: this.form.delivery.option
      };
      const r = await fetch(window.PDV_API.finalize, {
        method:'POST',
        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},
        body:j(payload)
      });
      const out = await r.json();
      if(!out.ok){ alert(out.message||'Erro ao criar pedido.'); return; }

      // Pagamento
      if(this.form.payment==='pix' && out.pay?.pix){
        this.pay.mode='pix';
        this.pay.title='Pagar com Pix';
        this.pay.total = out.total;
        this.pay.qr_base64 = out.pay.pix.qr_base64;
        this.pay.copia_cola = out.pay.pix.copia_cola;
        this.pay.expires_at = out.pay.pix.expires_at || '';
        this.pay.show=true;
      }else if(this.form.payment==='link_mp' && out.pay?.link){
        this.pay.mode='link';
        this.pay.title='Link de Pagamento';
        this.pay.total = out.total;
        this.pay.link  = out.pay.link;
        this.pay.show=true;
      }else{
        alert('Pedido criado!');
      }
      // limpar
      this.cart=[]; this.recalc();
    },

    // Modal pagamento
    pay:{ show:false, mode:'pix', title:'', total:0, qr_base64:'', copia_cola:'', expires_at:'', link:'', copied:false },
    copyPix(){ navigator.clipboard.writeText(this.pay.copia_cola).then(()=>this.pay.copied=true); },
    copyLink(){ navigator.clipboard.writeText(this.pay.link); },
    printPay(){ window.print(); },
    sendWhatsApp(){ /* opcional: abre wa.me com resumo */ },
  };
}

document.addEventListener('alpine:init', () => { Alpine.data('PDV', PDV); });