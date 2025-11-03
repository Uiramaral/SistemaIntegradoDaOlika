<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Olika - Pães Artesanais | Cardápio Digital')</title>
    <meta name="description" content="@yield('description', 'Pães artesanais com fermentação natural. Peça online 24h por dia. Tradição e qualidade em cada fornada.')">
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            DEFAULT: '#7A5230', // Marrom Olika
                            foreground: '#fff',
                        },
                        background: 'hsl(35, 25%, 98%)',
                        foreground: 'hsl(25, 40%, 13%)',
                        card: '#fff',
                        muted: {
                            DEFAULT: 'hsl(30, 20%, 95%)',
                            foreground: 'hsl(25, 20%, 46%)',
                        },
                        accent: '#7A5230', // Marrom Olika
                        accentForeground: '#fff',
                    },
                    fontFamily: {
                        sans: ['Inter', 'system-ui', '-apple-system', 'sans-serif'],
                    },
                },
            },
        }
    </script>
    <style>
      :root{
        --brand-50:#f3eae3; --brand-100:#eadfd4; --brand-200:#dccab8; --brand-300:#c9af96;
        --brand-400:#a88060; --brand-500:#7A5230; --brand-600:#694625; --brand-700:#5E3E23; --brand-800:#4b3019; --brand-900:#3b2512;
      }
      /* Scrollbar hide */
      .scrollbar-hide {
        -ms-overflow-style: none;
        scrollbar-width: none;
      }
      .scrollbar-hide::-webkit-scrollbar {
        display: none;
      }
    </style>
    
    @stack('styles')
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="min-h-screen bg-background font-sans" style="font-family: 'Inter', sans-serif;">
    
    @php
        // Contagem do carrinho
        $cartCount = session('cart_count', 0);
        $cart = session('cart', []);
        if (empty($cartCount) && !empty($cart)) { 
            $cartCount = array_sum(array_column($cart, 'qty')); 
        }
        
        // Logo
        $logoSquare = null;
        if (\Storage::disk('public')->exists('uploads/branding/logo.png')) { 
            $logoSquare = 'uploads/branding/logo.png'; 
        } elseif (\Storage::disk('public')->exists('uploads/branding/logo.jpg')) { 
            $logoSquare = 'uploads/branding/logo.jpg'; 
        }
    @endphp

    <!-- Layout Principal: Sidebar + Main -->
    <div class="flex min-h-screen">
        
        <!-- Sidebar (280px fixa, oculta no mobile) -->
        <aside id="sidebar" class="fixed lg:sticky top-0 left-0 h-screen bg-card border-r z-40 transition-transform duration-300 w-[280px] flex flex-col -translate-x-full lg:translate-x-0">
            
            <!-- Logo/Branding no topo -->
            <div class="p-6 border-b">
                <div class="flex items-center gap-2">
                    @if($logoSquare)
                        <div class="h-10 w-10 rounded-xl overflow-hidden bg-primary flex items-center justify-center flex-shrink-0">
                            <img src="{{ asset('storage/'.$logoSquare) }}" alt="Olika" class="w-full h-full object-cover">
                        </div>
                    @else
                        <div class="h-10 w-10 rounded-xl bg-primary flex items-center justify-center">
                            <span class="text-xl font-bold text-primary-foreground">O</span>
                        </div>
                    @endif
                    <div>
                        <h1 class="text-xl font-bold">Olika</h1>
                        <p class="text-xs text-muted-foreground">Artesanal</p>
                    </div>
                </div>
            </div>
            
            <!-- Categorias (preenchido dinamicamente) -->
            <div class="flex-1 overflow-y-auto p-4">
                <div class="mb-6">
                    <p class="text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-3 px-3">Categorias</p>
                    <div class="space-y-1" id="categoriesList">
                        <!-- Preenchido via JavaScript ou Blade -->
                    </div>
                </div>
            </div>
            
            <!-- Horário de Funcionamento (rodapé da sidebar) -->
            <div class="p-4 border-t">
                <div class="bg-muted rounded-xl p-4 space-y-2">
                    <p class="text-xs font-medium">Horário de Funcionamento</p>
                    <p class="text-xs text-muted-foreground">Seg-Sex: 8h-18h</p>
                    <p class="text-xs text-muted-foreground">Sáb: 8h-14h</p>
                </div>
            </div>
        </aside>
        
        <!-- Main Content -->
        <main class="flex-1 lg:ml-0 w-full">
            
            <!-- Header Sticky (só para páginas de menu, não checkout/payment) -->
            @if(!request()->routeIs('pedido.checkout*') && !request()->routeIs('pedido.payment*'))
            <header class="sticky top-0 z-30 bg-background/80 backdrop-blur-xl border-b">
                <div class="px-4 sm:px-6 lg:px-8 py-6">
                    <div class="max-w-7xl mx-auto">
                        <!-- Título e contagem -->
                        <div class="flex items-center gap-4 mb-4">
                            <div class="flex-1">
                                <h1 class="text-3xl sm:text-4xl font-bold tracking-tight" id="categoryTitle">Todos</h1>
                                <p class="text-muted-foreground mt-1" id="productsCount">0 produtos disponíveis</p>
                            </div>
                        </div>
                        <!-- Busca -->
                        <div class="relative">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-search absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-muted-foreground">
                                <circle cx="11" cy="11" r="8"></circle>
                                <path d="m21 21-4.3-4.3"></path>
                            </svg>
                            <input 
                                type="text" 
                                id="searchInput"
                                placeholder="Buscar produtos..." 
                                class="flex w-full border-input px-3 py-2 text-base ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm pl-12 h-12 rounded-xl bg-muted/50 border-0"
                            >
                        </div>
                    </div>
                </div>
            </header>
            @endif
            
            <!-- Header Compacto para Checkout/Payment (mantém header atual) -->
            @if(request()->routeIs('pedido.checkout*') || request()->routeIs('pedido.payment*'))
            <header class="w-full border-b bg-white shadow-sm sticky top-0 z-40">
                <div class="max-w-6xl mx-auto px-3 sm:px-4 h-14 sm:h-16 flex items-center justify-between">
                    <a href="{{ route('pedido.index') }}" class="inline-flex items-center gap-1.5 sm:gap-2 flex-shrink-0 min-w-0">
                        @if($logoSquare)
                            <img src="{{ asset('storage/'.$logoSquare) }}" alt="Olika" class="h-8 w-8 sm:h-10 sm:w-10 rounded-full object-cover flex-shrink-0">
                        @else
                            <div class="h-8 w-8 sm:h-10 sm:w-10 rounded-full bg-primary flex items-center justify-center flex-shrink-0">
                                <span class="text-lg sm:text-xl font-bold text-white">O</span>
                            </div>
                        @endif
                        <div class="flex flex-col min-w-0">
                            <span class="text-base sm:text-lg font-bold text-gray-900 leading-tight truncate">Olika</span>
                            <span class="text-xs text-gray-600 leading-tight truncate hidden sm:block">Pães Artesanais</span>
                        </div>
                    </a>
                    <a href="{{ route('pedido.cart.index') }}" class="inline-flex items-center gap-1.5 sm:gap-2 text-gray-700 hover:text-primary transition-colors relative flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="flex-shrink-0">
                            <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                            <line x1="3" y1="6" x2="21" y2="6"></line>
                            <path d="M16 10a4 4 0 0 1-8 0"></path>
                        </svg>
                        <span class="text-sm sm:text-base hidden sm:inline">Carrinho</span>
                        @if($cartCount > 0)
                        <div class="absolute -top-1 -right-1 h-5 w-5 bg-primary text-white text-xs font-bold rounded-full flex items-center justify-center" id="cartBadgeHeader">
                            {{ $cartCount }}
                        </div>
                        @endif
                    </a>
                </div>
            </header>
            @endif
            
            <!-- Content Area -->
            <div class="px-4 sm:px-6 lg:px-8 py-8">
                <div class="max-w-7xl mx-auto">
                    @yield('content')
                </div>
            </div>
        </main>
        
        <!-- Botão Hamburger Mobile (só para menu) -->
        @if(!request()->routeIs('pedido.checkout*') && !request()->routeIs('pedido.payment*'))
        <button 
            id="mobileMenuToggle"
            class="lg:hidden fixed top-4 left-4 z-50 inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-10 w-10 bg-card border shadow-sm"
        >
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-6 w-6">
                <line x1="4" x2="20" y1="12" y2="12"></line>
                <line x1="4" x2="20" y1="6" y2="6"></line>
                <line x1="4" x2="20" y1="18" y2="18"></line>
            </svg>
        </button>
        @endif
        
        <!-- Botão Flutuante Carrinho (só para menu) -->
        @if(!request()->routeIs('pedido.checkout*') && !request()->routeIs('pedido.payment*'))
        <button 
            id="cartButton"
            class="fixed bottom-6 right-6 z-50 inline-flex items-center justify-center whitespace-nowrap text-sm font-medium ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-16 px-6 rounded-2xl shadow-2xl hover:shadow-xl transition-all duration-300 hover:scale-105 gap-3"
        >
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5">
                <circle cx="8" cy="21" r="1"></circle>
                <circle cx="19" cy="21" r="1"></circle>
                <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"></path>
            </svg>
            <span class="font-semibold">Carrinho</span>
            @if($cartCount > 0)
            <span id="cartBadgeFloating" class="absolute -top-2 -right-2 h-6 w-6 rounded-full bg-white text-primary text-xs font-bold flex items-center justify-center border-2 border-primary">
                {{ $cartCount }}
            </span>
            @endif
        </button>
        @endif
        
        <!-- Drawer do Carrinho (slide-in da direita) -->
        @if(!request()->routeIs('pedido.checkout*') && !request()->routeIs('pedido.payment*'))
        <div 
            id="cartDrawer"
            class="fixed top-0 right-0 h-full w-full sm:w-[440px] bg-background z-50 shadow-2xl transition-transform duration-300 translate-x-full"
        >
            <div class="flex flex-col h-full">
                <!-- Header do Drawer -->
                <div class="p-6 border-b">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-2xl font-bold">Carrinho</h2>
                            <p class="text-sm text-muted-foreground" id="cartDrawerCount">0 itens</p>
                        </div>
                        <button 
                            id="closeCartDrawer"
                            class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 hover:bg-accent hover:text-accent-foreground h-10 w-10"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5">
                                <path d="M18 6 6 18"></path>
                                <path d="M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <!-- Conteúdo do Carrinho (preenchido dinamicamente) -->
                <div class="flex-1 overflow-y-auto p-6" id="cartDrawerContent">
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
                </div>
                
                <!-- Footer do Drawer (Total e Finalizar) -->
                <div id="cartDrawerFooter" class="hidden p-6 border-t space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="font-semibold text-lg">Total</span>
                        <span class="text-xl font-bold text-primary" id="cartDrawerTotal">R$ 0,00</span>
                    </div>
                    <a 
                        href="{{ route('pedido.checkout.index') }}"
                        class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-12 w-full rounded-xl"
                    >
                        Finalizar Pedido
                    </a>
                </div>
            </div>
        </div>
        @endif
    </div>
    
    <!-- Backdrop Mobile (para fechar sidebar) -->
    <div 
        id="sidebarBackdrop"
        class="lg:hidden fixed inset-0 bg-black/50 z-30 hidden"
    ></div>

    <script>
      // Toggle Sidebar Mobile
      document.addEventListener('DOMContentLoaded', function() {
          const sidebar = document.getElementById('sidebar');
          const backdrop = document.getElementById('sidebarBackdrop');
          const toggleBtn = document.getElementById('mobileMenuToggle');
          
          if (toggleBtn && sidebar) {
              toggleBtn.addEventListener('click', () => {
                  sidebar.classList.toggle('-translate-x-full');
                  if (backdrop) {
                      backdrop.classList.toggle('hidden');
                  }
              });
          }
          
          if (backdrop) {
              backdrop.addEventListener('click', () => {
                  sidebar.classList.add('-translate-x-full');
                  backdrop.classList.add('hidden');
              });
          }
          
          // Toggle Carrinho Drawer
          const cartButton = document.getElementById('cartButton');
          const cartDrawer = document.getElementById('cartDrawer');
          const closeCartDrawer = document.getElementById('closeCartDrawer');
          
          if (cartButton && cartDrawer) {
              cartButton.addEventListener('click', () => {
                  cartDrawer.classList.remove('translate-x-full');
                  loadCartIntoDrawer();
              });
          }
          
          if (closeCartDrawer && cartDrawer) {
              closeCartDrawer.addEventListener('click', () => {
                  cartDrawer.classList.add('translate-x-full');
              });
          }
          
          // Fechar drawer ao clicar no backdrop (se existir)
          if (cartDrawer) {
              cartDrawer.addEventListener('click', (e) => {
                  if (e.target === cartDrawer) {
                      cartDrawer.classList.add('translate-x-full');
                  }
              });
          }
      });
      
      // Atualizar badge do carrinho
      window.updateCartBadge = function(count){
        // Badge no header do checkout
        const checkoutBadge = document.getElementById('cartBadgeHeader');
        if (checkoutBadge) {
          if ((count||0) > 0) {
            checkoutBadge.textContent = count;
            checkoutBadge.style.display = 'flex';
          } else {
            checkoutBadge.style.display = 'none';
          }
        }
        
        // Badge flutuante
        const floatingBadge = document.getElementById('cartBadgeFloating');
        if (floatingBadge) {
          if ((count||0) > 0) {
            floatingBadge.textContent = count;
            floatingBadge.style.display = 'flex';
            if (!floatingBadge.parentElement.querySelector('#cartBadgeFloating')) {
              // Criar badge se não existir
            }
          } else {
            floatingBadge.style.display = 'none';
          }
        }
      }
      
      // Carregar carrinho no drawer
      async function loadCartIntoDrawer() {
          const drawerContent = document.getElementById('cartDrawerContent');
          const drawerFooter = document.getElementById('cartDrawerFooter');
          const drawerCount = document.getElementById('cartDrawerCount');
          
          if (!drawerContent) return;
          
          try {
              const response = await fetch('{{ route('pedido.cart.index') }}');
              const html = await response.text();
              const parser = new DOMParser();
              const doc = parser.parseFromString(html, 'text/html');
              
              // Aqui você pode extrair o conteúdo do carrinho e atualizar o drawer
              // Por enquanto, deixar como está (será implementado depois)
          } catch (e) {
              console.error('Erro ao carregar carrinho:', e);
          }
      }
      
      async function refreshCartCount(){
        try{
          const res = await fetch('{{ route('pedido.cart.count') }}', {headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}});
          const j = await res.json();
          if(j && (typeof j.count !== 'undefined')){ 
            window.updateCartBadge(j.count); 
          }
        }catch(_e){}
      }
      
      document.addEventListener('DOMContentLoaded', function(){
        refreshCartCount();
        setInterval(refreshCartCount, 5000);
      });
    </script>

    @stack('scripts')
</body>
</html>

