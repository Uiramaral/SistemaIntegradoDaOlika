@extends('pedido.layout')

@section('title', 'Carrinho - Olika')

@section('content')
<!-- NOVO LAYOUT CARRINHO - VERSÃO PIXEL-PERFECT -->
<!-- Header com botão voltar -->
<header class="sticky top-0 z-50 bg-background/95 backdrop-blur-sm border-b border-border">
    <div class="container mx-auto px-4 py-4">
        <a href="{{ route('pedido.index') }}" class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4">
                <path d="m12 19-7-7 7-7"></path>
                <path d="M19 12H5"></path>
            </svg>
            Continuar comprando
        </a>
    </div>
</header>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-5xl mx-auto">
        <div class="mb-8">
            <h1 class="text-3xl font-serif font-bold text-foreground mb-2">Seu Carrinho</h1>
            <p class="text-muted-foreground" id="cartItemsCount">0 itens</p>
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

        <!-- Layout de duas colunas: Itens à esquerda, Resumo à direita -->
        <div class="grid lg:grid-cols-[1fr_400px] gap-8">
            <!-- Coluna Esquerda: Itens do Carrinho -->
            <div class="space-y-4">
                <div id="cartContent" class="space-y-4">
                    <div class="text-center py-12">
                        <p class="text-muted-foreground mb-4">Carregando carrinho...</p>
                    </div>
                </div>
            </div>

            <!-- Coluna Direita: Resumo do Pedido -->
            <div class="lg:sticky lg:top-24 lg:h-fit">
                <div class="rounded-lg border bg-card text-card-foreground shadow-sm shadow-warm-lg">
                    <div class="flex flex-col space-y-1.5 p-6">
                        <h3 class="text-2xl font-semibold leading-none tracking-tight">Resumo do Pedido</h3>
                    </div>
                    <div class="p-6 pt-0 space-y-4">
                        <!-- Itens do resumo (será preenchido dinamicamente) -->
                        <div id="orderSummaryItems" class="space-y-3 max-h-60 overflow-y-auto">
                            <!-- Itens serão inseridos aqui via JS -->
                        </div>
                        
                        <div data-orientation="horizontal" role="none" class="shrink-0 bg-border h-[1px] w-full"></div>
                        
                        <!-- Totais -->
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-muted-foreground">Subtotal</span>
                                <span id="summarySubtotal" class="font-semibold">R$ 0,00</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-muted-foreground">Entrega</span>
                                <span id="summaryDelivery" class="font-semibold text-primary">Grátis</span>
                            </div>
                        </div>
                        
                        <div data-orientation="horizontal" role="none" class="shrink-0 bg-border h-[1px] w-full"></div>
                        
                        <!-- Total -->
                        <div class="flex justify-between items-center">
                            <span class="font-semibold">Total</span>
                            <span id="summaryTotal" class="text-2xl font-bold text-primary">R$ 0,00</span>
                        </div>
                        
                        <!-- Botão Finalizar -->
                        <a id="checkoutBtn" href="{{ route('pedido.checkout.index') }}" class="inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-11 rounded-md px-8 w-full shadow-warm">
                            <span id="checkoutBtnLabel">Finalizar Pedido</span>
                            <span id="checkoutLoading" class="hidden ml-2 text-xs">(gerando sugestões...)</span>
                        </a>
                        
                        <p class="text-xs text-center text-muted-foreground">Entrega grátis para pedidos acima de R$ 50</p>
                    </div>
                </div>
            </div>
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
        
        // Atualizar contagem de itens
        const itemsCount = data.items ? data.items.length : 0;
        const totalQty = data.items ? data.items.reduce((sum, item) => sum + parseInt(item.qty || 1), 0) : 0;
        const cartItemsCountEl = document.getElementById('cartItemsCount');
        if (cartItemsCountEl) {
            cartItemsCountEl.textContent = totalQty + (totalQty === 1 ? ' item' : ' itens');
        }

        if (!data.items || data.items.length === 0) {
            wrap.innerHTML = `
                <div class="text-center py-12">
                    <img src="{{ asset('images/empty-cart.svg') }}" alt="Carrinho vazio" class="mx-auto mb-4 h-48 w-48 opacity-50" onerror="this.style.display='none'">
                    <h2 class="text-2xl font-semibold mb-2">Seu carrinho está vazio</h2>
                    <p class="text-muted-foreground mb-4">Adicione produtos deliciosos ao seu carrinho!</p>
                    <a href="{{ route('pedido.index') }}" class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-11 rounded-md px-8 shadow-warm">
                        Ver cardápio
                    </a>
                </div>
            `;
            // Limpar resumo
            const summaryItems = document.getElementById('orderSummaryItems');
            const summarySubtotal = document.getElementById('summarySubtotal');
            const summaryTotal = document.getElementById('summaryTotal');
            if (summaryItems) summaryItems.innerHTML = '';
            if (summarySubtotal) summarySubtotal.textContent = 'R$ 0,00';
            if (summaryTotal) summaryTotal.textContent = 'R$ 0,00';
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
                <div class="flex gap-4 pb-4 border-b last:border-0 last:pb-0" data-product-id="${item.product_id}" data-variant-id="${item.variant_id||0}" data-special-instructions="${specialInstructionsEscaped}">
                    <img src="${item.image_url || '{{ asset("images/produto-placeholder.jpg") }}'}" alt="${item.name}" class="w-20 h-20 rounded-lg object-cover flex-shrink-0"/>
                    <div class="flex-1 min-w-0">
                        <h3 class="font-semibold text-base mb-1">${item.name}</h3>
                        ${item.variant ? `<p class="text-sm text-muted-foreground mb-1">${item.variant}</p>` : ''}
                        ${item.special_instructions ? `<div class="text-xs text-yellow-700 mt-1 mb-2 bg-yellow-50 border-l-2 border-yellow-400 px-2 py-1 rounded"><strong>Obs:</strong> ${item.special_instructions}</div>` : ''}
                        <p class="text-sm text-muted-foreground mb-3">R$ ${formatBRL(price)}</p>
                        <div class="flex items-center gap-3">
                            <div class="flex items-center border rounded-lg">
                                <button onclick="updateQuantity(${item.product_id}, ${item.variant_id||0}, -1, event, ${specialInstructions ? `'${specialInstructionsEscaped}'` : `''`})" class="p-2 hover:bg-muted disabled:opacity-50 disabled:cursor-not-allowed" ${item.qty <= 1 ? 'disabled' : ''}>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M5 12h14"></path>
                                    </svg>
                                </button>
                                <span class="px-4 text-base font-medium select-none">${item.qty}</span>
                                <button onclick="updateQuantity(${item.product_id}, ${item.variant_id||0}, 1, event, ${specialInstructions ? `'${specialInstructionsEscaped}'` : `''`})" class="p-2 hover:bg-muted">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M5 12h14"></path>
                                        <path d="M12 5v14"></path>
                                    </svg>
                                </button>
                            </div>
                            <button title="Remover" onclick="removeItem(${item.product_id}, ${item.variant_id||0}, ${specialInstructions ? `'${specialInstructionsEscaped}'` : `''`})" class="ml-auto text-muted-foreground hover:text-destructive transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 6h18"></path>
                                    <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path>
                                    <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <p class="font-bold text-lg text-primary">R$ ${formatBRL(subtotal)}</p>
                    </div>
                </div>
            `;
        });

        wrap.innerHTML = html;
        
        // SEMPRE usar o total do backend (vem do cartSummary que já calcula corretamente)
        // Se não estiver disponível, usar o calculado localmente
        const finalTotal = backendTotal > 0 ? backendTotal : calculatedTotal;
        const subtotal = calculatedTotal;
        
        // Atualizar resumo do pedido na lateral
        const summaryItems = document.getElementById('orderSummaryItems');
        const summarySubtotal = document.getElementById('summarySubtotal');
        const summaryTotal = document.getElementById('summaryTotal');
        
        if (summaryItems) {
            let summaryHtml = '';
            data.items.forEach(item => {
                const itemSubtotal = parseFloat(item.subtotal || item.price * item.qty || 0);
                summaryHtml += `
                    <div class="flex justify-between text-sm">
                        <span class="text-muted-foreground">${item.qty}x ${item.name}</span>
                        <span class="font-semibold">R$ ${formatBRL(itemSubtotal)}</span>
                    </div>
                `;
            });
            summaryItems.innerHTML = summaryHtml;
        }
        
        if (summarySubtotal) {
            summarySubtotal.textContent = 'R$ ' + formatBRL(subtotal);
        }
        
        if (summaryTotal) {
            summaryTotal.textContent = 'R$ ' + formatBRL(finalTotal);
        }
        
        // Esconder barra fixa antiga (não é mais necessária)
        if (bar) {
            bar.style.display = 'none';
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
