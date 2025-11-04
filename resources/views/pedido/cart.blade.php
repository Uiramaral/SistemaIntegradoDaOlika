@extends('pedido.layout')

@section('title', 'Carrinho - Olika')

@section('content')
<div class="max-w-4xl mx-auto pb-32 sm:pb-32 lg:pb-40">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-bold">Seu Carrinho</h1>
        <a href="{{ route('pedido.index') }}" class="inline-flex items-center justify-center gap-2 rounded-md text-sm font-medium transition-colors border border-gray-300 bg-white hover:bg-gray-50 h-10 px-4 py-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M19 12H5"></path>
                <path d="M12 19l-7-7 7-7"></path>
            </svg>
            Voltar ao Catálogo
        </a>
    </div>

    <!-- Barra de benefícios: frete grátis e cashback -->
    <div id="benefitsBar" class="hidden mb-6 rounded-lg border bg-white p-4">
        <div id="freeShippingWrap" class="mb-3">
            <div class="flex justify-between text-sm text-gray-600 mb-1">
                <span>Progresso para frete grátis</span>
                <span id="freeShippingHint"></span>
            </div>
            <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
                <div id="freeShippingProgress" class="h-2 bg-green-500 rounded-full" style="width:0%"></div>
            </div>
        </div>
        <div id="cashbackWrap" class="text-sm text-gray-700">
            <span>Você vai ganhar </span>
            <strong id="cashbackAmount">R$ 0,00</strong>
            <span> de cashback (</span><span id="cashbackPercent">0</span><span>%).</span>
        </div>
    </div>

    <div id="cartContent" class="space-y-4">
        <div class="text-center py-12">
            <p class="text-gray-600 mb-4">Carregando carrinho...</p>
        </div>
    </div>

    <!-- Barra fixa de total e ação -->
    <div id="cartBar" class="fixed inset-x-0 bottom-0 bg-white border-t shadow-lg z-[60]" style="display: none;">
        <div class="mx-auto max-w-4xl px-4 py-3 flex items-center justify-between gap-3">
            <div class="text-sm min-w-0 flex-shrink-0">
                <div class="text-gray-500">Total</div>
                <div id="cartTotal" class="text-xl sm:text-2xl font-bold text-primary whitespace-nowrap">R$ 0,00</div>
            </div>
            <a id="continueBtn" href="{{ route('pedido.index') }}" class="px-3 sm:px-4 py-2 sm:py-3 rounded-lg border border-gray-300 hover:bg-gray-50 text-primary hover:text-primary/90 text-sm sm:text-base whitespace-nowrap flex-shrink-0">Continuar comprando</a>
            <a id="checkoutBtn" href="{{ route('pedido.checkout.index') }}" class="flex-1 text-center bg-primary hover:bg-primary/90 text-primary-foreground font-semibold py-2 sm:py-3 rounded-lg text-sm sm:text-base whitespace-nowrap min-w-0">
                <span id="checkoutBtnLabel">Finalizar Pedido</span>
                <span id="checkoutLoading" class="hidden ml-2 text-xs">(gerando sugestões...)</span>
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function formatBRL(v){
    const n = Number(v||0);
    return n.toLocaleString('pt-BR', {minimumFractionDigits:2, maximumFractionDigits:2});
}

async function loadCart() {
    try {
        const response = await fetch('{{ route("pedido.cart.items") }}', {
            headers: { 'X-Requested-With': 'XMLHttpRequest','Accept': 'application/json' }
        });
        const data = await response.json();
        
        const wrap = document.getElementById('cartContent');
        const bar  = document.getElementById('cartBar');
        const totalEl = document.getElementById('cartTotal');
        const benefits = document.getElementById('benefitsBar');
        const fsWrap = document.getElementById('freeShippingWrap');
        const fsHint = document.getElementById('freeShippingHint');
        const fsProg = document.getElementById('freeShippingProgress');
        const cbWrap = document.getElementById('cashbackWrap');
        const cbAmt  = document.getElementById('cashbackAmount');
        const cbPct  = document.getElementById('cashbackPercent');
        
        if (!data.items || data.items.length === 0) {
            wrap.innerHTML = `
                <div class="text-center py-12">
                    <p class="text-gray-600 mb-4">Seu carrinho está vazio.</p>
                    <a href="{{ route('pedido.index') }}" class="inline-block bg-primary text-primary-foreground px-6 py-2 rounded-lg hover:bg-primary/90 transition-colors">
                        Continuar Comprando
                    </a>
                </div>
            `;
            bar.style.display = 'none';
            benefits.classList.add('hidden');
            return;
        }

        let html = '';
        let calculatedTotal = 0; // Calcular localmente para validação
        
        // Usar o total do backend (mais confiável) ou calcular como fallback
        const backendTotal = Number(data.total || 0);
        
        data.items.forEach(item => {
            const price = Number(item.price||0);
            const subtotal = Number(item.subtotal||price*Number(item.qty||1));
            calculatedTotal += subtotal;
            
            // Preparar observação para passar nas funções
            const specialInstructions = item.special_instructions || '';
            const specialInstructionsEscaped = specialInstructions.replace(/'/g, "\\'").replace(/"/g, '&quot;');
            
            html += `
                <div class="rounded-xl border bg-white shadow-sm p-4" data-product-id="${item.product_id}" data-variant-id="${item.variant_id||0}" data-special-instructions="${specialInstructionsEscaped}">
                    <div class="grid grid-cols-[72px_1fr_auto] gap-3 items-center">
                        <div class="w-18 h-18 rounded-lg overflow-hidden bg-gray-100">
                            <img src="${item.image_url || '{{ asset("images/produto-placeholder.jpg") }}'}" alt="${item.name}" class="w-full h-full object-cover"/>
                        </div>
                        <div class="min-w-0">
                            <h3 class="font-semibold truncate">${item.name}</h3>
                            ${item.variant ? `<div class="text-xs text-gray-500">Variação: ${item.variant}</div>` : ''}
                            ${item.special_instructions ? `<div class="text-xs text-yellow-700 mt-1 bg-yellow-50 border-l-2 border-yellow-400 px-2 py-1 rounded"><strong>Obs:</strong> ${item.special_instructions}</div>` : ''}
                            <div class="text-xs text-gray-500">R$ ${formatBRL(price)}</div>
                            <div class="mt-3 inline-flex items-center border rounded-lg overflow-hidden">
                                <button onclick="updateQuantity(${item.product_id}, ${item.variant_id||0}, -1, event, ${specialInstructions ? `'${specialInstructionsEscaped}'` : `''`})" class="w-9 h-9 grid place-items-center hover:bg-gray-100">-</button>
                                <span class="w-10 text-center select-none">${item.qty}</span>
                                <button onclick="updateQuantity(${item.product_id}, ${item.variant_id||0}, 1, event, ${specialInstructions ? `'${specialInstructionsEscaped}'` : `''`})" class="w-9 h-9 grid place-items-center hover:bg-gray-100">+</button>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm text-gray-500">Subtotal</div>
                            <div class="text-lg font-bold text-primary">R$ ${formatBRL(subtotal)}</div>
                            <button title="Remover" onclick="removeItem(${item.product_id}, ${item.variant_id||0}, ${specialInstructions ? `'${specialInstructionsEscaped}'` : `''`})" class="mt-2 inline-flex items-center justify-center text-red-600 hover:text-red-700">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 6h18"></path>
                                    <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path>
                                    <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });

        wrap.innerHTML = html;
        
        // SEMPRE usar o total do backend (vem do cartSummary que já calcula corretamente)
        // Se não estiver disponível, usar o calculado localmente
        const finalTotal = backendTotal > 0 ? backendTotal : calculatedTotal;
        
        // Atualizar o total no elemento do DOM
        if (totalEl) {
            totalEl.textContent = 'R$ ' + formatBRL(finalTotal);
        } else {
            console.error('loadCart: Elemento cartTotal não encontrado no DOM!');
        }
        
        // Mostrar a barra de total (usar display direto para garantir visibilidade)
        if (bar) {
            bar.style.display = 'block'; // Exibir diretamente via display
            console.log('loadCart: Barra do carrinho exibida', { 
                hasItems: data.items && data.items.length > 0,
                total: finalTotal,
                barVisible: bar.offsetHeight > 0
            });
        } else {
            console.error('loadCart: Elemento cartBar não encontrado no DOM!');
        }
        
        // Garantir que há espaço suficiente para scroll (ajustar padding-bottom dinamicamente)
        // Isso garante que o último item não seja cortado pela barra fixa
        const cartContainer = document.querySelector('.max-w-4xl');
        if (cartContainer && data.items && data.items.length > 0 && bar) {
            // Calcular altura da barra fixa + margem extra
            const cartBarHeight = bar.offsetHeight || 80;
            // Usar padding diferente para mobile e desktop
            const isDesktop = window.innerWidth >= 1024;
            const paddingValue = isDesktop ? `${cartBarHeight + 20}px` : `${cartBarHeight + 10}px`;
            cartContainer.style.paddingBottom = paddingValue;
        }

        // Benefícios: frete grátis e cashback
        const minFree = Number(data.free_shipping_min_total || 0);
        const remaining = Number(data.free_shipping_remaining || 0);
        const progress = Number(data.free_shipping_progress || 0);
        const cashbackAmount = Number(data.cashback_amount || 0);
        const cashbackPercent = Number(data.cashback_percent || 0);

        if (minFree > 0 || cashbackPercent > 0) {
            benefits.classList.remove('hidden');
            // Frete grátis
            if (minFree > 0) {
                fsWrap.classList.remove('hidden');
                fsProg.style.width = `${progress}%`;
                fsHint.textContent = remaining > 0 ? `Faltam R$ ${formatBRL(remaining)}` : 'Frete grátis alcançado!';
            } else {
                fsWrap.classList.add('hidden');
            }
            // Cashback
            if (cashbackPercent > 0) {
                cbWrap.classList.remove('hidden');
                cbAmt.textContent = 'R$ ' + formatBRL(cashbackAmount);
                cbPct.textContent = cashbackPercent.toFixed(0);
            } else {
                cbWrap.classList.add('hidden');
            }
        } else {
            benefits.classList.add('hidden');
        }
    } catch (error) {
        console.error('Erro ao carregar carrinho:', error);
        document.getElementById('cartContent').innerHTML = `
            <div class="text-center py-12">
                <p class="text-red-600 mb-4">Erro ao carregar carrinho.</p>
                <button onclick="loadCart()" class="text-primary hover:text-primary/90">Tentar novamente</button>
            </div>
        `;
        const errorBar = document.getElementById('cartBar');
        if (errorBar) errorBar.style.display = 'none';
        document.getElementById('benefitsBar').classList.add('hidden');
    }
}

async function updateQuantity(productId, variantId, delta, event, specialInstructions = '') {
    try {
        // Buscar o card que contém o botão clicado
        let card;
        if (event && event.target) {
            card = event.target.closest('.rounded-xl');
        } else {
            // Fallback: buscar pelo data attributes, incluindo observação
            const selector = `[data-product-id="${productId}"][data-variant-id="${variantId||0}"]`;
            if (specialInstructions) {
                const escapedObs = specialInstructions.replace(/'/g, "\\'").replace(/"/g, '&quot;');
                card = document.querySelector(`${selector}[data-special-instructions="${escapedObs}"]`);
            } else {
                card = document.querySelector(selector);
            }
        }
        
        // Calcular nova quantidade
        let newQty;
        if (card) {
            const qtySpan = card.querySelector('span');
            const currentQty = parseInt(qtySpan.textContent) || 1;
            newQty = Math.max(0, currentQty + delta);
        } else {
            console.warn('updateQuantity: Card não encontrado, usando quantidade padrão');
            newQty = Math.max(1, delta > 0 ? 2 : 1);
        }
        
        if (newQty === 0) { 
            await removeItem(productId, variantId, specialInstructions); 
            return; 
        }
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const response = await fetch('{{ route("pedido.cart.update") }}', {
            method: 'POST', 
            headers: { 
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json' 
            },
            body: JSON.stringify({ 
                product_id: productId, 
                variant_id: variantId||0, 
                qty: newQty,
                special_instructions: specialInstructions || ''
            })
        });
        const data = await response.json();
        if (data.success || data.ok) { 
            loadCart(); 
            if (typeof updateCartBadge==='function' && 'cart_count' in data) {
                updateCartBadge(data.cart_count); 
            }
        } else {
            console.error('updateQuantity: Erro na resposta do servidor', data);
        }
    } catch (error) { 
        console.error('updateQuantity: Erro ao atualizar quantidade:', error); 
    }
}

async function removeItem(productId, variantId, specialInstructions = '') {
    if (!confirm('Deseja remover este item do carrinho?')) return;
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const response = await fetch('{{ route("pedido.cart.remove") }}', {
            method: 'POST', 
            headers: { 
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json' 
            },
            body: JSON.stringify({ 
                product_id: productId, 
                variant_id: variantId||0,
                special_instructions: specialInstructions || ''
            })
        });
        const data = await response.json();
        if (data.success || data.ok) { 
            // Recarregar o carrinho completo (inclui recálculo do total)
            await loadCart(); 
            if (typeof updateCartBadge==='function' && 'cart_count' in data) {
                updateCartBadge(data.cart_count); 
            }
        } else {
            console.error('removeItem: Erro na resposta do servidor', data);
            // Mesmo assim, tentar recarregar o carrinho para atualizar o total
            await loadCart();
        }
    } catch (error) { 
        console.error('removeItem: Erro ao remover item:', error); 
    }
}

function updateCartBadge(count) {
    const badge = document.querySelector('a[href*="cart"] .absolute');
    if (count > 0) {
        if (badge) { badge.textContent = count; }
        else {
            const cartLink = document.querySelector('a[href*="cart"]');
            if (cartLink) {
                const newBadge = document.createElement('div');
                newBadge.className = 'absolute -right-2 -top-2 h-5 w-5 rounded-full bg-primary text-primary-foreground text-xs flex items-center justify-center font-semibold';
                newBadge.textContent = count; cartLink.appendChild(newBadge);
            }
        }
    } else { if (badge) badge.remove(); }
}

// Upsell no carrinho com loading controlando o botão "Finalizar"
let aiLoaded = false;
const checkoutBtn = document.getElementById('checkoutBtn');
const checkoutLoading = document.getElementById('checkoutLoading');
function blockCheckout(){ if (checkoutBtn){ checkoutBtn.classList.add('pointer-events-none','opacity-60'); checkoutLoading?.classList.remove('hidden'); } }
function unblockCheckout(){ if (checkoutBtn){ checkoutBtn.classList.remove('pointer-events-none','opacity-60'); checkoutLoading?.classList.add('hidden'); } }

async function loadCartUpsell(){
  try{
    console.log('loadCartUpsell: Iniciando carregamento de sugestões IA');
    blockCheckout();
    const url = '{{ route('pedido.cart.ai') }}';
    console.log('loadCartUpsell: URL da rota:', url);
    const res = await fetch(url, {headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}});
    console.log('loadCartUpsell: Resposta recebida', {status: res.status, ok: res.ok});
    
    if (!res.ok) {
      console.error('loadCartUpsell: Erro na resposta HTTP', {status: res.status, statusText: res.statusText});
      throw new Error(`HTTP ${res.status}: ${res.statusText}`);
    }
    
    const j = await res.json();
    console.log('loadCartUpsell: JSON recebido', j);
    
    const wrapId = 'aiUpsellCart';
    let wrap = document.getElementById(wrapId);
    if(!wrap){
      wrap = document.createElement('div'); 
      wrap.id = wrapId; 
      wrap.className = 'mt-6';
      wrap.innerHTML = '<div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm"><h2 class="text-xl font-semibold mb-4">Talvez você também goste</h2><div id="aiUpsellCartList" class="space-y-3"></div></div>';
      const cartContent = document.querySelector('#cartContent');
      if (cartContent) {
        // Usar parentNode.appendChild para garantir compatibilidade
        if (cartContent.parentNode) {
          cartContent.parentNode.appendChild(wrap);
        } else if (cartContent.nextSibling) {
          cartContent.parentNode.insertBefore(wrap, cartContent.nextSibling);
        } else {
          // Fallback: adicionar após o elemento
          cartContent.after(wrap);
        }
      } else {
        console.error('loadCartUpsell: Não foi possível encontrar #cartContent para inserir sugestões');
        // Fallback: adicionar ao body
        const container = document.querySelector('.max-w-4xl');
        if (container) {
          container.appendChild(wrap);
        }
      }
    }
    const list = document.getElementById('aiUpsellCartList');
    if (!list) {
      console.error('loadCartUpsell: Não foi possível encontrar #aiUpsellCartList');
      aiLoaded = true;
      unblockCheckout();
      return;
    }
    list.innerHTML = '';
    
    if(!j.success || !(j.suggestions||[]).length){ 
      console.log('loadCartUpsell: Sem sugestões', {success: j.success, count: (j.suggestions||[]).length});
      wrap.classList.add('hidden'); 
      aiLoaded = true; 
      unblockCheckout(); 
      return; 
    }
    j.suggestions.forEach(s=>{
      const row = document.createElement('div'); 
      row.className = 'flex items-start justify-between gap-4 p-4 border rounded-lg bg-gradient-to-r from-white to-muted/30 hover:shadow-md transition-shadow';
      const price = typeof s.price === 'number' ? s.price : 0;
      const desc  = (s.description||'').trim();
      const pitch = (s.pitch||'').trim();
      const reason = (s.reason||'').trim();
      
      // Construir texto de descrição combinando description, pitch e reason de forma inteligente
      let descText = '';
      if (pitch) {
        descText = `<div class="text-sm font-semibold text-gray-800 mb-1">${pitch}</div>`;
      }
      if (reason) {
        descText += `<div class="text-xs text-gray-600 leading-relaxed">${reason}</div>`;
      } else if (desc) {
        descText += `<div class="text-xs text-gray-600 leading-relaxed">${desc}</div>`;
      }
      
      row.innerHTML = `<div class="flex-1 min-w-0">
          <div class=\"font-bold text-lg text-gray-900 mb-1\">${s.name || 'Produto Recomendado'}</div>
          ${descText}
          <div class=\"mt-2 text-base font-bold text-primary\">R$ ${Number(price).toFixed(2).replace('.', ',')}</div>
        </div>
        <button class=\"px-4 py-2 rounded-lg bg-primary hover:bg-primary/90 text-primary-foreground text-sm font-semibold shadow-sm transition-colors flex-shrink-0\">Adicionar</button>`;
      row.querySelector('button').addEventListener('click', async ()=>{
        try{
          const fd = new FormData(); fd.append('product_id', s.product_id); fd.append('qty','1');
          await fetch('{{ route('pedido.cart.add') }}', {method:'POST', headers:{'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]').content, 'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}, body: fd, credentials: 'same-origin'});
          // Atualiza carrinho e remove apenas a sugestão clicada
          loadCart();
          row.remove();
        }catch(err){ console.error('Upsell add error (cart):', err); }
      });
      list.appendChild(row);
    });
    wrap.classList.remove('hidden');
    console.log('loadCartUpsell: Sugestões exibidas com sucesso', (j.suggestions||[]).length);
  }catch(e){ 
    console.error('loadCartUpsell: Erro ao carregar sugestões', e); 
  }
  finally{ 
    aiLoaded = true; 
    unblockCheckout(); 
    console.log('loadCartUpsell: Finalizado');
  }
}
// Carregar carrinho primeiro, depois sugestões IA
async function initializeCart() {
  try {
    await loadCart();
    // Sugestões IA desabilitadas temporariamente
    // setTimeout(() => {
    //   loadCartUpsell();
    // }, 300);
  } catch (error) {
    console.error('initializeCart: Erro ao inicializar carrinho', error);
  }
}

document.addEventListener('DOMContentLoaded', function(){ 
  console.log('DOMContentLoaded: Inicializando carrinho');
  initializeCart();
});
</script>
@endpush
