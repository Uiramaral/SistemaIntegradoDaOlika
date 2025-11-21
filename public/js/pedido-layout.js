/**
 * Scripts do layout pedido
 * Gerencia drawer do carrinho, atualização de badges e contadores
 */
(function() {
    'use strict';
    
    // Variáveis globais para rotas (serão injetadas pelo Blade)
    window.pedidoRoutes = window.pedidoRoutes || {
        cartItems: '/pedido/cart/items',
        cartUpdate: '/pedido/cart/update',
        cartRemove: '/pedido/cart/remove',
        cartCount: '/pedido/cart/count'
    };
    
    // Função global para atualizar badge do carrinho
    window.updateCartBadge = function(count) {
        // Badge no header do checkout
        const checkoutBadge = document.getElementById('cartBadgeHeader');
        if (checkoutBadge) {
            if ((count || 0) > 0) {
                checkoutBadge.textContent = count;
                checkoutBadge.style.display = 'flex';
            } else {
                checkoutBadge.style.display = 'none';
            }
        }
        
        // Badge no banner (carrinho fixo no topo)
        const cartLinkBanner = document.querySelector('a[href*="cart"].fixed');
        if (cartLinkBanner) {
            let badge = cartLinkBanner.querySelector('.absolute');
            if ((count || 0) > 0) {
                if (!badge) {
                    badge = document.createElement('div');
                    badge.className = 'absolute -right-2 -top-2 h-5 w-5 rounded-full bg-[#7A5230] text-white text-xs flex items-center justify-center font-semibold';
                    cartLinkBanner.appendChild(badge);
                }
                badge.textContent = count;
            } else if (badge) {
                badge.remove();
            }
        }
        
        // Badge no botão "Ver carrinho" da navegação inferior
        const navBadge = document.getElementById('navCartBadge');
        if (navBadge) {
            if ((count || 0) > 0) {
                navBadge.textContent = count;
                navBadge.classList.remove('hidden');
                navBadge.style.display = 'flex';
            } else {
                navBadge.classList.add('hidden');
                navBadge.style.display = 'none';
            }
        }
        
        // Atualizar barra inferior do carrinho
        const cartBottomBar = document.getElementById('cartBottomBar');
        if (cartBottomBar) {
            if ((count || 0) > 0) {
                cartBottomBar.classList.remove('hidden');
                // Buscar dados do carrinho para atualizar total
                fetch(window.pedidoRoutes.cartItems, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                }).then(res => res.json()).then(data => {
                    if (data && data.items && data.items.length > 0) {
                        const total = data.items.reduce((sum, item) => {
                            return sum + (parseFloat(item.price || 0) * parseInt(item.qty || 1));
                        }, 0);
                        const itemCount = data.items.reduce((sum, item) => sum + parseInt(item.qty || 1), 0);
                        
                        const totalEl = document.getElementById('cartBottomTotal');
                        const itemsEl = document.getElementById('cartBottomItems');
                        const badgeEl = document.getElementById('cartBottomBarBadge');
                        
                        if (totalEl) totalEl.textContent = 'R$ ' + total.toFixed(2).replace('.', ',');
                        if (itemsEl) itemsEl.textContent = itemCount + (itemCount === 1 ? ' item' : ' itens');
                        if (badgeEl) {
                            badgeEl.textContent = itemCount;
                            badgeEl.style.display = itemCount > 0 ? 'flex' : 'none';
                        } else if (itemCount > 0) {
                            // Criar badge se não existir
                            const cartButton = cartBottomBar.querySelector('a[href*="cart"]');
                            if (cartButton) {
                                const badge = document.createElement('span');
                                badge.id = 'cartBottomBarBadge';
                                badge.className = 'absolute -top-2 -right-2 h-5 w-5 rounded-full bg-white text-primary text-xs flex items-center justify-center font-bold border-2 border-primary';
                                badge.textContent = itemCount;
                                badge.style.minWidth = '20px';
                                const span = cartButton.querySelector('span.relative');
                                if (span) {
                                    span.appendChild(badge);
                                }
                            }
                        }
                    }
                }).catch(e => console.error('Erro ao atualizar barra do carrinho:', e));
            } else {
                cartBottomBar.classList.add('hidden');
            }
        }
    };
    
    // Carregar carrinho no drawer
    window.loadCartIntoDrawer = async function() {
        const drawerContent = document.getElementById('cartDrawerContent');
        const drawerFooter = document.getElementById('cartDrawerFooter');
        const drawerCount = document.getElementById('cartDrawerCount');
        
        if (!drawerContent) return;
        
        try {
            const response = await fetch(window.pedidoRoutes.cartItems, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            });
            const data = await response.json();
            
            if (!data.items || data.items.length === 0) {
                drawerContent.innerHTML = `
                    <div class="flex flex-col items-center justify-center h-full text-center space-y-4">
                        <div class="h-24 w-24 rounded-full bg-muted flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-12 w-12 text-muted-foreground">
                                <circle cx="8" cy="21" r="1"></circle>
                                <circle cx="19" cy="21" r="1"></circle>
                                <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold text-lg">Carrinho vazio</p>
                            <p class="text-sm text-muted-foreground">Adicione produtos para continuar</p>
                        </div>
                    </div>
                `;
                if (drawerFooter) drawerFooter.classList.add('hidden');
                if (drawerCount) drawerCount.textContent = '0 itens';
                return;
            }
            
            let html = '';
            let total = 0;
            
            data.items.forEach(item => {
                const itemTotal = parseFloat(item.price || 0) * parseInt(item.qty || 1);
                total += itemTotal;
                
                html += `
                    <div class="flex gap-4 py-4 border-b">
                        <img src="${item.image_url || '/images/produto-placeholder.jpg'}" alt="${item.name || 'Produto'}" class="h-20 w-20 rounded-lg object-cover flex-shrink-0">
                        <div class="flex-1 min-w-0">
                            <h4 class="font-semibold text-base truncate">${item.name || 'Produto'}</h4>
                            <p class="text-sm text-muted-foreground">R$ ${parseFloat(item.price || 0).toFixed(2).replace('.', ',')}</p>
                            <div class="flex items-center gap-3 mt-2">
                                <div class="flex items-center border rounded-lg">
                                    <button onclick="window.updateCartItem(${item.product_id}, ${item.variant_id || 0}, ${parseInt(item.qty || 1) - 1})" class="p-2 hover:bg-muted" type="button">-</button>
                                    <span class="px-4">${item.qty || 1}</span>
                                    <button onclick="window.updateCartItem(${item.product_id}, ${item.variant_id || 0}, ${parseInt(item.qty || 1) + 1})" class="p-2 hover:bg-muted" type="button">+</button>
                                </div>
                                <span class="font-bold text-primary">R$ ${itemTotal.toFixed(2).replace('.', ',')}</span>
                                <button onclick="window.removeCartItem(${item.product_id}, ${item.variant_id || 0})" class="ml-auto text-muted-foreground hover:text-foreground" type="button">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5">
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
            
            drawerContent.innerHTML = html;
            if (drawerFooter) {
                drawerFooter.classList.remove('hidden');
                const totalEl = document.getElementById('cartDrawerTotal');
                if (totalEl) totalEl.textContent = 'R$ ' + total.toFixed(2).replace('.', ',');
            }
            if (drawerCount) {
                const itemCount = data.items.reduce((sum, item) => sum + parseInt(item.qty || 1), 0);
                drawerCount.textContent = itemCount + (itemCount === 1 ? ' item' : ' itens');
            }
        } catch (e) {
            console.error('Erro ao carregar carrinho:', e);
            drawerContent.innerHTML = '<p class="text-center text-muted-foreground">Erro ao carregar carrinho. Tente novamente.</p>';
        }
    };
    
    // Funções auxiliares para o drawer
    window.updateCartItem = async function(productId, variantId, qty) {
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            const response = await fetch(window.pedidoRoutes.cartUpdate, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken ? csrfToken.content : '',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ product_id: productId, variant_id: variantId, qty })
            });
            const data = await response.json();
            if (data.success || data.ok) {
                window.loadCartIntoDrawer();
                window.updateCartBadge(data.cart_count || 0);
            }
        } catch (e) {
            console.error('Erro ao atualizar item:', e);
        }
    };
    
    window.removeCartItem = async function(productId, variantId) {
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            const response = await fetch(window.pedidoRoutes.cartRemove, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken ? csrfToken.content : '',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ product_id: productId, variant_id: variantId })
            });
            const data = await response.json();
            if (data.success || data.ok) {
                window.loadCartIntoDrawer();
                window.updateCartBadge(data.cart_count || 0);
            }
        } catch (e) {
            console.error('Erro ao remover item:', e);
        }
    };
    
    async function refreshCartCount() {
        try {
            const res = await fetch(window.pedidoRoutes.cartCount, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const j = await res.json();
            if (j && (typeof j.count !== 'undefined')) {
                window.updateCartBadge(j.count);
            }
        } catch (_e) {
            // Ignorar erros silenciosamente
        }
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle Carrinho Drawer
        const cartButton = document.getElementById('cartButton');
        const cartDrawer = document.getElementById('cartDrawer');
        const closeCartDrawer = document.getElementById('closeCartDrawer');
        
        if (cartButton && cartDrawer) {
            cartButton.addEventListener('click', () => {
                cartDrawer.classList.remove('translate-x-full');
                window.loadCartIntoDrawer();
            });
        }
        
        if (closeCartDrawer && cartDrawer) {
            closeCartDrawer.addEventListener('click', () => {
                cartDrawer.classList.add('translate-x-full');
            });
        }
        
        if (cartDrawer) {
            cartDrawer.addEventListener('click', (e) => {
                if (e.target === cartDrawer) {
                    cartDrawer.classList.add('translate-x-full');
                }
            });
        }
        
        // Atualizar contador do carrinho periodicamente
        refreshCartCount();
        setInterval(refreshCartCount, 5000);
    });
})();

