<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Olika - Pães Artesanais')</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <style>
        :root {
            --background: 0 0% 100%;
            --foreground: 222.2 84% 4.9%;
            --primary: 24 100% 52%;
            --radius: 0.75rem;
        }
        
        .container {
            max-width: 1400px;
            padding: 0 2rem;
            margin: 0 auto;
        }
        
        .btn-primary {
            background-color: hsl(var(--primary));
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: var(--radius);
            border: none;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-primary:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }
        
        .card {
            background: white;
            border-radius: var(--radius);
            box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
            padding: 1.5rem;
        }
    </style>
    
    @stack('styles')
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="container">
            <div class="flex items-center justify-between h-16">
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="{{ route('menu.index') }}" class="text-2xl font-bold text-orange-600">
                        🍞 Olika
                    </a>
                </div>
                
                <!-- Navigation -->
                <nav class="hidden md:flex space-x-8">
                    <a href="{{ route('menu.index') }}" class="text-gray-700 hover:text-orange-600 transition">
                        Cardápio
                    </a>
                    <a href="#" class="text-gray-700 hover:text-orange-600 transition">
                        Sobre
                    </a>
                    <a href="#" class="text-gray-700 hover:text-orange-600 transition">
                        Contato
                    </a>
                </nav>
                
                <!-- Cart Button -->
                <div class="flex items-center space-x-4">
                    <a href="{{ route('cart.index') }}" 
                       class="relative bg-orange-600 text-white px-4 py-2 rounded-lg hover:bg-orange-700 transition">
                        <i class="fas fa-shopping-cart mr-2"></i>
                        Carrinho
                        <span id="cart-badge" data-cart-badge class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center hidden">
                            0
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8 mt-16">
        <div class="container">
            <div class="grid md:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-xl font-bold mb-4">🍞 Olika</h3>
                    <p class="text-gray-300">
                        Pães artesanais feitos com amor e ingredientes de qualidade.
                    </p>
                </div>
                
                <div>
                    <h4 class="font-semibold mb-4">Contato</h4>
                    <p class="text-gray-300 mb-2">
                        <i class="fas fa-phone mr-2"></i>
                        (71) 98701-9420
                    </p>
                    <p class="text-gray-300">
                        <i class="fas fa-envelope mr-2"></i>
                        contato@olika.com.br
                    </p>
                </div>
                
                <div>
                    <h4 class="font-semibold mb-4">Horário de Funcionamento</h4>
                    <p class="text-gray-300">
                        Segunda a Sábado: 8h às 18h<br>
                        Domingo: 8h às 14h
                    </p>
                </div>
            </div>
            
            <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-300">
                <p>&copy; {{ date('Y') }} Olika. Todos os direitos reservados.</p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script>
        // CSRF Token para requisições AJAX
        window.csrfToken = '{{ csrf_token() }}';
        
        // Função para atualizar contador do carrinho (método antigo - mantido para compatibilidade)
        function updateCartCount() {
            getCartCount();
        }
        
        // Função para obter contagem do carrinho via API (desabilitada por enquanto)
        function getCartCount() {
            // API desabilitada - usar método localStorage
            console.log('API desabilitada, usando localStorage');
            getCartCountSimple();
        }

        // Função principal que sempre funciona
        function updateCartCountSafe() {
            try {
                // Usar método com localStorage prioritário
                getCartCountOffline();
            } catch (error) {
                console.error('Erro no método principal, usando modo de emergência:', error);
                window.emergencyCartCount();
            }
        }

        // Função simples que funciona sem API
        function getCartCountSimple() {
            const cartCountElement = document.getElementById('cart-count');
            if (!cartCountElement) return;

            // URLs seguras que sempre funcionam
            const possibleUrls = [
                '/cart/count',
                window.location.origin + '/cart/count'
            ];

            console.log('Tentando URLs:', possibleUrls);

            // Função para tentar uma URL
            function tryUrl(urlIndex) {
                if (urlIndex >= possibleUrls.length) {
                    console.warn('Todas as URLs falharam, usando método alternativo');
                    getCartCountFromPage();
                    return;
                }

                const url = possibleUrls[urlIndex];
                console.log('Tentando URL:', url);

                fetch(url)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('API não disponível: ' + response.status);
                        }
                        return response.text();
                    })
                    .then(text => {
                        // Verificar se é JSON válido
                        try {
                            const data = JSON.parse(text);
                            cartCountElement.textContent = data.count;
                            cartCountElement.style.display = data.count > 0 ? 'flex' : 'none';
                            console.log('Contador atualizado via API:', data.count);
                        } catch (e) {
                            // Se não for JSON, tentar próxima URL
                            console.warn('API retornou HTML, tentando próxima URL');
                            tryUrl(urlIndex + 1);
                        }
                    })
                    .catch(error => {
                        console.warn('Erro na API, tentando próxima URL:', error);
                        tryUrl(urlIndex + 1);
                    });
            }

            // Começar tentando a primeira URL
            tryUrl(0);
        }

        // Método que funciona com localStorage (independente da API)
        function getCartCountOffline() {
            const cartCountElement = document.getElementById('cart-count');
            if (!cartCountElement) return;

            let totalCount = 0;

            // Usar localStorage como fonte primária (sempre funciona)
            totalCount = getCartCountFromLocalStorage();
            console.log('💾 Contador via localStorage:', totalCount);

            // Se localStorage vazio, tentar API opcionalmente
            if (totalCount === 0) {
                getCartCountFromAPI()
                    .then(apiCount => {
                        if (apiCount > 0) {
                            cartCountElement.textContent = apiCount;
                            cartCountElement.style.display = 'flex';
                            console.log('🎯 Contador atualizado via API:', apiCount);
                        }
                    })
                    .catch(error => {
                        console.log('API não disponível, usando apenas localStorage');
                    });
            }

            // Garantir que o total seja um número válido
            totalCount = Math.max(0, parseInt(totalCount) || 0);

            cartCountElement.textContent = totalCount;
            cartCountElement.style.display = totalCount > 0 ? 'flex' : 'none';
            console.log('🔢 Contador final:', totalCount);
        }

        // Método que carrega a página do carrinho para obter contagem
        function getCartCountFromCartPage() {
            const cartCountElement = document.getElementById('cart-count');
            if (!cartCountElement) return;

            fetch('{{ route("cart.index") }}')
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const cartCountFromPage = doc.querySelector('#cart-count');
                    
                    if (cartCountFromPage) {
                        const count = cartCountFromPage.textContent.trim();
                        cartCountElement.textContent = count;
                        cartCountElement.style.display = count > 0 ? 'flex' : 'none';
                        console.log('Contador atualizado via página do carrinho:', count);
                    } else {
                        // Se não encontrar, definir como 0
                        cartCountElement.textContent = '0';
                        cartCountElement.style.display = 'none';
                    }
                })
                .catch(error => {
                    console.error('Erro ao carregar página do carrinho:', error);
                    cartCountElement.textContent = '0';
                    cartCountElement.style.display = 'none';
                });
        }

        // Função que funciona com sessionStorage como fallback
        function getCartCountFromSession() {
            const cartCountElement = document.getElementById('cart-count');
            if (!cartCountElement) return;

            // Tentar sessionStorage
            const cartData = sessionStorage.getItem('cart');
            let count = 0;
            
            if (cartData) {
                try {
                    const cart = JSON.parse(cartData);
                    count = Object.values(cart).reduce((total, item) => total + (item.quantity || 0), 0);
                } catch (e) {
                    console.warn('Erro ao parsear cart do sessionStorage:', e);
                }
            }

            cartCountElement.textContent = count;
            cartCountElement.style.display = count > 0 ? 'flex' : 'none';
        }

        // Função alternativa que conta itens da página atual
        function countCartItemsFromPage() {
            const cartCountElement = document.getElementById('cart-count');
            if (!cartCountElement) return;

            // Procurar por elementos que indiquem itens no carrinho
            const cartItems = document.querySelectorAll('[id^="cart-item-"]');
            let totalCount = 0;
            
            cartItems.forEach(item => {
                const quantitySpan = item.querySelector('.min-w-\\[3rem\\]') || 
                                   item.querySelector('[class*="quantity"]') ||
                                   item.querySelector('input[type="number"]');
                if (quantitySpan) {
                    const quantity = parseInt(quantitySpan.textContent || quantitySpan.value) || 0;
                    totalCount += quantity;
                }
            });

            cartCountElement.textContent = totalCount;
            cartCountElement.style.display = totalCount > 0 ? 'flex' : 'none';
        }

        // Função simples que usa localStorage como fallback
        function getCartCountFromStorage() {
            const cartCountElement = document.getElementById('cart-count');
            if (!cartCountElement) return;

            // Tentar obter do localStorage
            const cartData = localStorage.getItem('cart');
            let count = 0;
            
            if (cartData) {
                try {
                    const cart = JSON.parse(cartData);
                    count = Object.values(cart).reduce((total, item) => total + (item.quantity || 0), 0);
                } catch (e) {
                    console.warn('Erro ao parsear cart do localStorage:', e);
                }
            }

            cartCountElement.textContent = count;
            cartCountElement.style.display = count > 0 ? 'flex' : 'none';
        }

        // Método de fallback caso a API falhe
        function updateCartCountFallback() {
            // Primeiro, tentar contar da página atual
            countCartItemsFromPage();
            
            // Se não encontrar itens, tentar localStorage
            getCartCountFromStorage();
            
            // Se ainda não funcionar, tentar sessionStorage
            getCartCountFromSession();
            
            // Se ainda não funcionar, tentar carregar página do carrinho
            fetch('{{ route("cart.index") }}')
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const cartCountElement = doc.querySelector('#cart-count');
                    if (cartCountElement) {
                        const count = cartCountElement.textContent.trim();
                        const element = document.getElementById('cart-count');
                        if (element) {
                            element.textContent = count;
                            element.style.display = count > 0 ? 'flex' : 'none';
                        }
                    }
                })
                .catch(error => {
                    console.error('Erro no fallback:', error);
                    // Último recurso: definir como 0
                    const element = document.getElementById('cart-count');
                    if (element) {
                        element.textContent = '0';
                        element.style.display = 'none';
                    }
                });
        }
        
        // Atualiza contador do carrinho ao carregar a página
        document.addEventListener('DOMContentLoaded', function() {
            // Verificar se há dados inválidos no localStorage
            try {
                const cartData = localStorage.getItem('cart');
                if (cartData) {
                    const cart = JSON.parse(cartData);
                    let totalCount = 0;
                    let hasInvalidData = false;

                    for (const productId in cart) {
                        const item = cart[productId];
                        const quantity = parseInt(item.quantity);
                        if (isNaN(quantity) || quantity < 0) {
                            hasInvalidData = true;
                            break;
                        }
                        totalCount += quantity;
                    }

                    // Se tem dados inválidos ou quantidade muito alta, limpar
                    if (hasInvalidData || totalCount > 1000) {
                        console.log('Dados inválidos detectados no localStorage, limpando...');
                        localStorage.removeItem('cart');
                        sessionStorage.removeItem('cart');
                    }
                }
            } catch (e) {
                console.warn('Erro ao verificar localStorage, limpando...', e);
                localStorage.removeItem('cart');
                sessionStorage.removeItem('cart');
            }

            // Definir valor inicial
            const cartCountElement = document.getElementById('cart-count');
            if (cartCountElement) {
                cartCountElement.textContent = '0';
                cartCountElement.style.display = 'none';
            }

            // Aguardar um pouco para garantir que a página carregou completamente
            setTimeout(function() {
                // Primeiro corrigir duplicatas
                window.fixDuplicateItems();

                // Usar método com localStorage prioritário
                updateCartCountSafe();
            }, 100);
        });
        
        // Função global para atualizar contador (chamada por outros scripts)
        window.updateCartCount = function() {
            updateCartCountSafe();
        };
        
        // Função de teste para debug (remover em produção)
        window.testCartCount = function() {
            console.log('Testando contador do carrinho...');
            console.log('Elemento cart-count:', document.getElementById('cart-count'));
            console.log('Rota cart.count:', '/cart/count');
            getCartCountSimple();
        };

        // Função para testar método da página do carrinho
        window.testCartPage = function() {
            console.log('Testando método da página do carrinho...');
            getCartCountFromCartPage();
        };

        // Função para testar todas as abordagens
        window.testAllCartMethods = function() {
            console.log('Testando todos os métodos...');
            console.log('1. Método principal:');
            getCartCountSimple();
            console.log('2. Página do carrinho:');
            getCartCountFromCartPage();
            console.log('3. Contagem da página:');
            getCartCountFromPage();
        };
        
        // Função para debug da API
        window.debugCartAPI = function() {
            console.log('=== DEBUG DA API ===');
            console.log('Domínio atual:', window.location.hostname);
            console.log('URL atual:', window.location.href);
            
            const possibleUrls = [
                '/cart/count',
                window.location.origin + '/cart/count'
            ];
            
            console.log('URLs possíveis:', possibleUrls);
            
            possibleUrls.forEach((url, index) => {
                console.log(`Testando URL ${index + 1}:`, url);
                fetch(url)
                    .then(response => {
                        console.log(`URL ${index + 1} - Status:`, response.status);
                        console.log(`URL ${index + 1} - OK:`, response.ok);
                        return response.text();
                    })
                    .then(text => {
                        console.log(`URL ${index + 1} - Text:`, text.substring(0, 100) + '...');
                        try {
                            const data = JSON.parse(text);
                            console.log(`URL ${index + 1} - JSON:`, data);
                        } catch (e) {
                            console.log(`URL ${index + 1} - Não é JSON válido`);
                        }
                    })
                    .catch(error => console.error(`URL ${index + 1} - Error:`, error));
            });
        };

        // Função para debug do localStorage
        window.debugCartStorage = function() {
            const cartData = localStorage.getItem('cart');
            console.log('Cart data from localStorage:', cartData);
            if (cartData) {
                try {
                    const cart = JSON.parse(cartData);
                    console.log('Parsed cart:', cart);
                    const count = Object.values(cart).reduce((total, item) => total + (item.quantity || 0), 0);
                    console.log('Total count:', count);
                } catch (e) {
                    console.error('Error parsing cart:', e);
                }
            }
        };

        // Função para debug do sessionStorage
        window.debugCartSession = function() {
            const cartData = sessionStorage.getItem('cart');
            console.log('Cart data from sessionStorage:', cartData);
            if (cartData) {
                try {
                    const cart = JSON.parse(cartData);
                    console.log('Parsed cart:', cart);
                    const count = Object.values(cart).reduce((total, item) => total + (item.quantity || 0), 0);
                    console.log('Total count:', count);
                } catch (e) {
                    console.error('Error parsing cart:', e);
                }
            }
        };

        // Função para debug completo
        window.debugCartComplete = function() {
            console.log('=== DEBUG COMPLETO DO CARRINHO ===');
            console.log('Elemento cart-count:', document.getElementById('cart-count'));
            console.log('localStorage cart:', localStorage.getItem('cart'));
            console.log('sessionStorage cart:', sessionStorage.getItem('cart'));
            console.log('Domínio atual:', window.location.hostname);
            console.log('URL atual:', window.location.href);
            
            const possibleUrls = [
                '/cart/count',
                window.location.origin + '/cart/count'
            ];
            
            console.log('URLs da API:', possibleUrls);
            console.log('Testando API...');
            
            possibleUrls.forEach((url, index) => {
                console.log(`Testando URL ${index + 1}:`, url);
                fetch(url)
                    .then(response => {
                        console.log(`URL ${index + 1} - Status:`, response.status);
                        return response.text();
                    })
                    .then(text => {
                        console.log(`URL ${index + 1} - Text:`, text.substring(0, 100) + '...');
                        try {
                            const data = JSON.parse(text);
                            console.log(`URL ${index + 1} - JSON:`, data);
                        } catch (e) {
                            console.log(`URL ${index + 1} - Não é JSON válido`);
                        }
                    })
                    .catch(error => console.error(`URL ${index + 1} - Error:`, error));
            });
        };

        // Função para forçar atualização do contador
        window.forceUpdateCartCount = function() {
            console.log('Forçando atualização do contador...');
            updateCartCountSafe();
        };

        // Função de fallback final - sempre funciona
        window.forceCartCount = function(count = 0) {
            const cartCountElement = document.getElementById('cart-count');
            if (cartCountElement) {
                cartCountElement.textContent = count;
                cartCountElement.style.display = count > 0 ? 'flex' : 'none';
                console.log('Contador forçado para:', count);
            }
        };

        // Função de emergência - define contador manualmente
        window.setCartCount = function(count) {
            console.log('Definindo contador manualmente para:', count);
            window.forceCartCount(count);
        };

        // Função para resetar tudo
        window.resetCartSystem = function() {
            console.log('Resetando sistema do carrinho...');
            localStorage.removeItem('cart');
            sessionStorage.removeItem('cart');
            window.forceCartCount(0);
            console.log('Sistema resetado');
        };

        // Função de emergência - sempre funciona independente de rotas
        function emergencyCartCount() {
            console.log('=== MODO DE EMERGÊNCIA ===');
            console.log('Usando localStorage...');

            // Usar localStorage diretamente (sempre funciona)
            const count = getCartCountFromLocalStorage();
            window.forceCartCount(count);
            console.log('Contador definido via localStorage:', count);
        }

        // Função melhorada para adicionar ao carrinho
        window.addToCartImproved = function(productId, quantity = 1) {
            console.log('Adicionando ao carrinho:', productId, quantity);
            
            // Primeiro, tentar a API normal
            fetch(`${window.location.origin}/cart/add`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.csrfToken
                },
                body: JSON.stringify({
                    product_id: productId,
                    quantity: quantity
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Produto adicionado via API:', data);
                    // ATUALIZAR localStorage com dados da API
                    updateLocalStorageFromAPI(data);
                    // Atualizar contador global
                    if (typeof window.updateCartCount === 'function') {
                        window.updateCartCount(data.cart_count);
                    }
                    showNotification(data.message, 'success');
                } else {
                    throw new Error('API retornou erro');
                }
            })
            .catch(error => {
                console.warn('API falhou, usando localStorage:', error);
                // Fallback: usar localStorage
                updateLocalStorageCart(productId, quantity);
                window.updateCartCount();
                showNotification('Produto adicionado ao carrinho (modo offline)', 'success');
            });
        };

        // Função para atualizar localStorage (CORRIGIDA)
        function updateLocalStorageCart(productId, quantity) {
            try {
                let cart = JSON.parse(localStorage.getItem('cart') || '{}');

                // SE o produto já existe, soma a quantidade
                if (cart[productId]) {
                    cart[productId].quantity = parseInt(cart[productId].quantity) + parseInt(quantity);
                } else {
                    // SE é novo, adiciona com a quantidade especificada
                    cart[productId] = {
                        product_id: productId,
                        quantity: parseInt(quantity),
                        added_at: new Date().toISOString()
                    };
                }

                localStorage.setItem('cart', JSON.stringify(cart));
                console.log('✅ Carrinho atualizado no localStorage:', cart);
            } catch (e) {
                console.error('❌ Erro ao atualizar localStorage:', e);
            }
        }

        // Função para DEFINIR quantidade (não somar)
        function setLocalStorageCart(productId, quantity) {
            try {
                let cart = JSON.parse(localStorage.getItem('cart') || '{}');

                // Define a quantidade exata (não soma)
                cart[productId] = {
                    product_id: productId,
                    quantity: parseInt(quantity),
                    added_at: new Date().toISOString()
                };

                localStorage.setItem('cart', JSON.stringify(cart));
                console.log('✅ Quantidade definida no localStorage:', cart);
            } catch (e) {
                console.error('❌ Erro ao definir localStorage:', e);
            }
        }

        // Função para atualizar localStorage com dados da API
        function updateLocalStorageFromAPI(data) {
            try {
                // Se a API retornou os dados do carrinho completo, usar eles
                if (data.cart) {
                    localStorage.setItem('cart', JSON.stringify(data.cart));
                    console.log('✅ localStorage atualizado com dados da API (menu):', data.cart);
                } else if (data.cart_count !== undefined) {
                    // Se retornou apenas a contagem, buscar carrinho completo
                    fetch('/cart', {
                        headers: {
                            'X-CSRF-TOKEN': window.csrfToken
                        }
                    })
                    .then(response => response.json())
                    .then(cartData => {
                        if (cartData.cart) {
                            localStorage.setItem('cart', JSON.stringify(cartData.cart));
                            console.log('✅ localStorage sincronizado com carrinho (menu):', cartData.cart);
                        }
                    })
                    .catch(error => {
                        console.warn('Não foi possível sincronizar carrinho (menu):', error);
                    });
                }
            } catch (e) {
                console.error('❌ Erro ao atualizar localStorage com API (menu):', e);
            }
        }

        // Função para obter contagem do localStorage
        function getCartCountFromLocalStorage() {
            try {
                const cartData = localStorage.getItem('cart');
                if (cartData) {
                    const cart = JSON.parse(cartData);
                    let totalCount = 0;
                    for (const productId in cart) {
                        const item = cart[productId];
                        totalCount += parseInt(item.quantity) || 0;
                    }
                    console.log('Contagem do localStorage:', totalCount, cart);
                    return totalCount;
                }
            } catch (e) {
                console.warn('Erro ao ler localStorage:', e);
            }
            return 0;
        }

        // Função para obter contagem da API (PRIORIDADE)
        function getCartCountFromAPI() {
            return new Promise((resolve, reject) => {
                // Tentar múltiplas URLs possíveis
                const possibleUrls = [
                    '/cart/count',
                    window.location.origin + '/cart/count'
                ];

                function tryNextUrl(index) {
                    if (index >= possibleUrls.length) {
                        console.warn('Todas as URLs da API falharam, usando localStorage');
                        resolve(getCartCountFromLocalStorage());
                        return;
                    }

                    const url = possibleUrls[index];
                    console.log('Tentando API URL:', url);

                    fetch(url, {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => {
                        console.log('API Response status:', response.status);
                        if (!response.ok) {
                            if (response.status === 404) {
                                console.warn('Rota não encontrada, tentando próxima URL');
                                tryNextUrl(index + 1);
                                return;
                            }
                            throw new Error(`API não disponível: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('✅ Contagem da API:', data.count);
                        resolve(data.count || 0);
                    })
                    .catch(error => {
                        console.warn('Erro na API:', error);
                        tryNextUrl(index + 1);
                    });
                }

                tryNextUrl(0);
            });
        }

        // Função principal que prioriza API
        async function getCartCountSmart() {
            try {
                const apiCount = await getCartCountFromAPI();
                console.log('🔄 Contagem inteligente - API:', apiCount);
                return apiCount;
            } catch (error) {
                console.warn('Erro na API, fallback localStorage:', error);
                return getCartCountFromLocalStorage();
            }
        }

        // Função para mostrar notificações
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg text-white z-50 ${
                type === 'success' ? 'bg-green-500' : 
                type === 'error' ? 'bg-red-500' : 'bg-blue-500'
            }`;
            notification.textContent = message;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }

        // Função de teste final - testa todos os métodos
        window.testAllMethods = function() {
            console.log('=== TESTE COMPLETO DE TODOS OS MÉTODOS ===');
            
            console.log('1. Testando método principal...');
            try {
                updateCartCountSafe();
            } catch (e) {
                console.error('Método principal falhou:', e);
            }
            
            console.log('2. Testando modo de emergência...');
            try {
                window.emergencyCartCount();
            } catch (e) {
                console.error('Modo de emergência falhou:', e);
            }
            
            console.log('3. Testando debug completo...');
            try {
                window.debugCartComplete();
            } catch (e) {
                console.error('Debug completo falhou:', e);
            }
            
            console.log('4. Testando contagem manual...');
            try {
                window.setCartCount(1);
                setTimeout(() => window.setCartCount(0), 1000);
            } catch (e) {
                console.error('Contagem manual falhou:', e);
            }
        };

        // Função para testar adição ao carrinho (localStorage)
        window.testAddToCart = function(productId = 1, quantity = 1) {
            console.log('=== TESTE DE ADIÇÃO AO CARRINHO ===');
            console.log('Produto ID:', productId, 'Quantidade:', quantity);

            // Usar localStorage diretamente (sempre funciona)
            updateLocalStorageCart(productId, quantity);

            // Atualizar contador
            window.updateCartCount();

            // Mostrar notificação
            showNotification(`Produto adicionado ao carrinho (${quantity}x)`, 'success');
        };

        // Função para testar atualização de quantidade
        window.testUpdateQuantity = function(productId = 39, newQuantity = 2) {
            console.log('=== TESTE DE ATUALIZAÇÃO ===');
            console.log('Produto ID:', productId, 'Nova quantidade:', newQuantity);

            // Testar função de atualização
            if (typeof window.updateQuantity === 'function') {
                console.log('Usando função de atualização da página do carrinho...');
                window.updateQuantity(productId, newQuantity);
            } else {
                console.log('Função de atualização não disponível, usando localStorage...');
                updateLocalStorageFromCart(productId, newQuantity);
                // Atualizar contador
                window.updateCartCount();
            }
        };

        // Função básica para atualizar interface (fallback)
        function updateCartInterface(productId, newQuantity) {
            console.log('Atualizando interface:', productId, newQuantity);
            // Recalcular total se estivermos na página do carrinho
            if (window.recalculateTotalFromPage) {
                window.recalculateTotalFromPage();
            }
            // Atualizar contador
            window.updateCartCount();
        }

        // Função para verificar o estado atual do carrinho
        window.checkCartState = function() {
            console.log('=== ESTADO ATUAL DO CARRINHO ===');

            const localCart = localStorage.getItem('cart');
            console.log('localStorage cart:', localCart);

            const sessionCart = sessionStorage.getItem('cart');
            console.log('sessionStorage cart:', sessionCart);

            const countElement = document.getElementById('cart-count');
            console.log('Elemento contador:', countElement);
            console.log('Valor do contador:', countElement ? countElement.textContent : 'não encontrado');

            console.log('Contagem via função:', getCartCountFromLocalStorage());
        };

        // Função para corrigir duplicatas no localStorage
        window.fixDuplicateItems = function() {
            console.log('=== CORRIGINDO DUPLICATAS ===');

            try {
                const cartData = localStorage.getItem('cart');
                if (cartData) {
                    const cart = JSON.parse(cartData);

                    // Verificar se há duplicatas na estrutura
                    let totalCount = 0;
                    let fixedCart = {};

                    for (const productId in cart) {
                        const item = cart[productId];
                        const quantity = parseInt(item.quantity) || 0;

                        if (quantity > 0) {
                            fixedCart[productId] = {
                                product_id: parseInt(productId),
                                quantity: quantity,
                                added_at: item.added_at || new Date().toISOString()
                            };
                            totalCount += quantity;
                        }
                    }

                    localStorage.setItem('cart', JSON.stringify(fixedCart));
                    console.log('✅ Duplicatas corrigidas. Total:', totalCount);
                    console.log('Carrinho fixado:', fixedCart);

                    return totalCount;
                }
            } catch (e) {
                console.error('❌ Erro ao corrigir duplicatas:', e);
            }

            return 0;
        };

        // Função para limpar e resetar tudo
        window.resetEverything = function() {
            console.log('=== RESET COMPLETO ===');

            // Limpar localStorage
            localStorage.removeItem('cart');
            sessionStorage.removeItem('cart');

            // Resetar contador
            window.forceCartCount(0);

            // Limpar console
            console.clear();

            console.log('Sistema resetado completamente');
            console.log('Carrinho limpo, contador zerado');
        };

        // Função para limpar apenas o localStorage do carrinho
        window.clearCartData = function() {
            console.log('=== LIMPANDO DADOS DO CARRINHO ===');
            localStorage.removeItem('cart');
            sessionStorage.removeItem('cart');
            window.forceCartCount(0);
            console.log('Dados do carrinho limpos');
        };

        // Função para simular compra (localStorage)
        window.simulatePurchase = function(productId = 1, quantity = 1) {
            console.log('=== SIMULANDO COMPRA ===');
            console.log('Produto:', productId, 'Quantidade:', quantity);

            // Atualizar localStorage diretamente (sempre funciona)
            updateLocalStorageCart(productId, quantity);

            // Atualizar contador
            window.updateCartCount();

            // Mostrar notificação
            showNotification(`Produto adicionado ao carrinho (${quantity}x)`, 'success');
        };

        // Função para verificar e corrigir problemas automaticamente
        window.autoFixCartIssues = function() {
            console.log('=== CORREÇÃO AUTOMÁTICA DE PROBLEMAS ===');

            try {
                const cartData = localStorage.getItem('cart');
                if (cartData) {
                    const cart = JSON.parse(cartData);
                    let totalCount = 0;
                    let needsFix = false;

                    for (const productId in cart) {
                        const item = cart[productId];
                        let quantity = parseInt(item.quantity);

                        if (isNaN(quantity) || quantity < 0) {
                            console.log('Item inválido encontrado:', item);
                            needsFix = true;
                            break;
                        }

                        if (quantity > 100) {
                            console.log('Quantidade muito alta:', item);
                            needsFix = true;
                            break;
                        }

                        totalCount += quantity;
                    }

                    if (needsFix || totalCount > 1000) {
                        console.log('Problemas detectados, limpando localStorage...');
                        localStorage.removeItem('cart');
                        sessionStorage.removeItem('cart');
                        window.forceCartCount(0);
                        console.log('✅ Dados limpos automaticamente');
                        return true;
                    } else {
                        console.log('✅ Dados do carrinho OK');
                        return false;
                    }
                } else {
                    console.log('✅ Sem dados no localStorage');
                    return false;
                }
            } catch (e) {
                console.log('Erro ao verificar, limpando...', e);
                localStorage.removeItem('cart');
                sessionStorage.removeItem('cart');
                window.forceCartCount(0);
                return true;
            }
        };

        // Função para testar método específico
        window.testSpecificMethod = function(method) {
            console.log('Testando método:', method);
            switch(method) {
                case 'simple':
                    getCartCountSimple();
                    break;
                case 'page':
                    getCartCountFromPage();
                    break;
                case 'cart':
                    getCartCountFromCartPage();
                    break;
                default:
                    console.log('Métodos disponíveis: simple, page, cart');
            }
        };

        // Função para resetar contador
        window.resetCartCount = function() {
            const cartCountElement = document.getElementById('cart-count');
            if (cartCountElement) {
                cartCountElement.textContent = '0';
                cartCountElement.style.display = 'none';
            }
        };

        // Função para verificar status do contador
        window.checkCartStatus = function() {
            const cartCountElement = document.getElementById('cart-count');
            if (cartCountElement) {
                console.log('Status do contador:');
                console.log('- Texto:', cartCountElement.textContent);
                console.log('- Display:', cartCountElement.style.display);
                console.log('- Visível:', cartCountElement.offsetParent !== null);
            } else {
                console.log('Elemento cart-count não encontrado');
            }
        };
    </script>

    <!-- Função global para atualizar contador do carrinho -->
    <script>
        (function () {
            function setCartBadge(count) {
                var n = Number(count || 0);

                // 1) Atualiza todos os badges conhecidos
                var selectors = ['#cart-badge', '[data-cart-badge]', '.js-cart-count'];
                selectors.forEach(function (sel) {
                    document.querySelectorAll(sel).forEach(function (el) {
                        el.textContent = n;
                        // mostra quando > 0; esconde quando 0
                        if (n > 0) {
                            el.classList.remove('hidden');
                            el.style.display = '';
                        } else {
                            el.classList.add('hidden');
                            el.style.display = 'none';
                        }
                    });
                });

                // 2) Persiste para outras abas (e para páginas que só leem do LS)
                try { localStorage.setItem('olika_cart_count', String(n)); } catch(_) {}

                // 3) (Opcional) Exponha para outros scripts
                window.__cart_count__ = n;
            }

            // Torna público para qualquer página/arquivo JS chamar
            window.updateCartCount = setCartBadge;

            // Sincroniza entre abas
            window.addEventListener('storage', function (e) {
                if (e.key === 'olika_cart_count') {
                    setCartBadge(e.newValue || 0);
                }
            });

            // Inicializa com o que estiver no LS (melhor que nada)
            try {
                var boot = Number(localStorage.getItem('olika_cart_count') || 0);
                setCartBadge(boot);
            } catch(_) {}
        })();
    </script>
    
    @stack('scripts')
</body>
</html>
