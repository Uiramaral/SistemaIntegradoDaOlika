<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Olika - Pães Artesanais | Cardápio Digital')</title>
    <meta name="description" content="@yield('description', 'Pães artesanais com fermentação natural. Peça online 24h por dia. Tradição e qualidade em cada fornada.')">
    
    {{-- Open Graph / Facebook --}}
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:title" content="@yield('title', 'Olika - Pães Artesanais | Cardápio Digital')">
    <meta property="og:description" content="@yield('description', 'Pães artesanais com fermentação natural. Peça online 24h por dia. Tradição e qualidade em cada fornada.')">
    <meta property="og:url" content="{{ url()->current() }}">
    @hasSection('og_image')
    <meta property="og:image" content="@yield('og_image')">
    @else
    <meta property="og:image" content="{{ asset('images/logo-olika.png') }}">
    @endif
    
    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', 'Olika - Pães Artesanais | Cardápio Digital')">
    <meta name="twitter:description" content="@yield('description', 'Pães artesanais com fermentação natural. Peça online 24h por dia. Tradição e qualidade em cada fornada.')">
    @hasSection('og_image')
    <meta name="twitter:image" content="@yield('og_image')">
    @endif
    
    <!-- Google Fonts: Inter - Local com fallback -->
    @if(file_exists(public_path('css/inter-fonts.css')))
    <link rel="stylesheet" href="{{ asset('css/inter-fonts.css') }}">
    @else
    <!-- Fallback para Google Fonts CDN se arquivo local não existir -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @endif
    
    <!-- Estilos críticos - carregados primeiro para evitar FOUC -->
    <link rel="stylesheet" href="{{ asset('css/critical-styles.css') }}">
    
    <!-- Configuração do Tailwind e carregador -->
    <script>
        window.tailwindConfig = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            DEFAULT: '#7A5230',
                            foreground: '#fff',
                        },
                        background: 'hsl(35, 25%, 98%)',
                        foreground: 'hsl(25, 40%, 13%)',
                        card: '#fff',
                        muted: {
                            DEFAULT: 'hsl(30, 20%, 95%)',
                            foreground: 'hsl(25, 20%, 46%)',
                        },
                        accent: '#7A5230',
                        accentForeground: '#fff',
                        border: 'hsl(35, 15%, 88%)',
                    },
                    fontFamily: {
                        sans: ['Inter', 'system-ui', '-apple-system', 'sans-serif'],
                        serif: ['Inter', 'system-ui', 'sans-serif'],
                    },
                    aspectRatio: {
                        '4/3': '4 / 3',
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.6s ease-out',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0', transform: 'translateY(20px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        },
                    },
                },
            },
        };
        window.tailwindLocalPath = '{{ asset("js/tailwind.min.js") }}';
    </script>
    <script src="{{ asset('js/tailwind-loader.js') }}" defer></script>
    
    @stack('styles')
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-background font-sans" style="font-family: 'Inter', sans-serif;">
    
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
        
        // Buscar categorias para o carrossel (se não foram passadas pela view)
        if (!isset($categories)) {
            $categories = \App\Models\Category::query()
                ->select('categories.*')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();
        }
    @endphp

    <!-- Layout Principal: Sidebar + Main -->
    <div class="flex">
        
        
        
        <!-- Main Content -->
        <main class="flex-1 lg:ml-0 w-full">
            
            <!-- Hero Section / Banner Marrom (só para páginas de menu, não checkout/payment/cart) -->
            @if(!request()->routeIs('pedido.checkout*') && !request()->routeIs('pedido.payment*') && !request()->routeIs('pedido.cart.index'))
            @php
                $bannerPath = null;
                if (\Storage::disk('public')->exists('uploads/branding/banner.jpg')) {
                    $bannerPath = 'uploads/branding/banner.jpg';
                } elseif (\Storage::disk('public')->exists('uploads/branding/banner.png')) {
                    $bannerPath = 'uploads/branding/banner.png';
                }
            @endphp
            <div class="relative h-[120px] bg-gradient-to-r from-[#7A5230] to-[#5E3E23] flex items-center justify-center overflow-hidden">
                @if($bannerPath)
                    <div class="absolute inset-0 bg-center bg-cover opacity-20" style="background-image: url('{{ asset('storage/'.$bannerPath) }}');"></div>
                @else
                    <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1509440159596-0249088772ff?w=1200&auto=format&fit=crop')] bg-cover bg-center opacity-20"></div>
                @endif


                <div class="relative z-10 text-white">
                    @php 
                        // Preferir logo quadrada para compor com texto ao lado
                        $logoSquare = null;
                        if (\Storage::disk('public')->exists('uploads/branding/logo.png')) { $logoSquare = 'uploads/branding/logo.png'; }
                        elseif (\Storage::disk('public')->exists('uploads/branding/logo.jpg')) { $logoSquare = 'uploads/branding/logo.jpg'; }
                    @endphp
                    <div class="mx-auto flex items-center justify-center gap-2">
                        @if($logoSquare)
                            <div class="h-[50px] w-[50px] rounded-full overflow-hidden bg-white/90 shadow">
                                <img src="{{ asset('storage/'.$logoSquare) }}" alt="Olika" class="w-full h-full object-cover">
                            </div>
                        @else
                            <div class="h-[50px] w-[50px] rounded-full bg-white/90 flex items-center justify-center shadow">
                                <span class="text-2xl font-bold bg-gradient-to-r from-[#7A5230] to-[#5E3E23] bg-clip-text text-transparent">O</span>
                            </div>
                        @endif
                        <div class="leading-tight text-center md:text-left">
                            <h1 class="text-2xl md:text-3xl font-bold drop-shadow">Olika</h1>
                            <p class="text-sm md:text-base drop-shadow">Pães Artesanais</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Header Sticky com busca -->
            <header class="sticky top-0 z-30 bg-background/80 backdrop-blur-xl border-b">
                <div class="px-4 sm:px-6 lg:px-8 py-6">
                    <div class="max-w-7xl mx-auto space-y-4">
                        @if(!request()->routeIs('pedido.checkout*') && !request()->routeIs('pedido.payment*') && !request()->routeIs('pedido.cart.index'))
                        <!-- Filtro de Categorias (rolagem horizontal) -->
                        <div class="overflow-x-auto scrollbar-hide -mx-4 px-4 pb-2">
                            <div class="flex gap-2 min-w-max">
                                <a href="{{ route('pedido.index') }}" class="category-filter-btn inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-10 px-4 py-2 rounded-full transition-smooth {{ request()->routeIs('pedido.index') || request()->routeIs('pedido.menu.search') ? 'bg-primary text-primary-foreground hover:bg-primary/90 shadow-warm' : 'hover:bg-accent hover:text-accent-foreground' }}">
                                    Todos
                                </a>
                                @if($categories && $categories->count() > 0)
                                @foreach($categories as $cat)
                                @php
                                    $isActiveCategory = false;
                                    if (request()->routeIs('pedido.menu.category')) {
                                        try {
                                            $routeCategory = request()->route('category');
                                            $isActiveCategory = $routeCategory && $routeCategory->id == $cat->id;
                                        } catch (\Exception $e) {
                                            $isActiveCategory = false;
                                        }
                                    }
                                @endphp
                                <a href="{{ route('pedido.menu.category', $cat->id) }}" class="category-filter-btn inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-10 px-4 py-2 rounded-full transition-smooth {{ $isActiveCategory ? 'bg-primary text-primary-foreground hover:bg-primary/90 shadow-warm' : 'hover:bg-accent hover:text-accent-foreground' }}" data-category-id="{{ $cat->id }}">
                                    {{ $cat->name }}
                                </a>
                                @endforeach
                                @endif
                            </div>
                        </div>
                        @endif
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
            
            <!-- Header Compacto para Checkout/Payment - REMOVIDO (agora está nas próprias páginas) -->
            
            <!-- Content Area -->
            @if(request()->routeIs('pedido.cart.index') || request()->routeIs('pedido.checkout*') || request()->routeIs('pedido.payment*'))
            <!-- Layout sem wrapper para carrinho e checkout -->
            <div class="w-full">
                @yield('content')
            </div>
            @else
            <div class="px-3 sm:px-4 lg:px-8 py-4 sm:py-6 lg:py-8 {{ $cartCount > 0 ? 'pb-28 sm:pb-28' : 'pb-20 sm:pb-20' }}">
                <div class="max-w-7xl mx-auto">
                    @yield('content')
                </div>
            </div>
            @endif
        </main>
        
        <!-- Navegação Inferior (Menu, Pedidos, Ver carrinho) -->
        @if(!request()->routeIs('pedido.checkout*') && !request()->routeIs('pedido.payment*') && !request()->routeIs('pedido.cart.index'))
        <nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 z-50" style="bottom: 0 !important; margin-bottom: 0 !important;">
            <div class="max-w-7xl mx-auto px-4 py-2 flex items-center justify-around">
                <a href="{{ route('pedido.index') }}" class="flex flex-col items-center gap-1 py-2 {{ request()->routeIs('pedido.index') || request()->routeIs('pedido.menu.*') ? 'text-primary' : 'text-gray-600' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-6 w-6">
                        <line x1="3" y1="12" x2="21" y2="12"></line>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <line x1="3" y1="18" x2="21" y2="18"></line>
                    </svg>
                    <span class="text-xs font-medium">Menu</span>
                </a>
                <a href="{{ route('customer.orders.index') }}" class="flex flex-col items-center gap-1 py-2 {{ request()->routeIs('customer.orders.*') ? 'text-primary' : 'text-gray-600' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-6 w-6 relative">
                        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <path d="M16 10a4 4 0 0 1-8 0"></path>
                    </svg>
                    <span class="text-xs font-medium">Pedidos</span>
                </a>
                <a href="{{ route('pedido.cart.index') }}" class="flex flex-col items-center gap-1 py-2 {{ request()->routeIs('pedido.cart.index') ? 'text-primary' : 'text-gray-600' }} relative">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-6 w-6">
                        <circle cx="8" cy="21" r="1"></circle>
                        <circle cx="19" cy="21" r="1"></circle>
                        <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"></path>
                    </svg>
                    @if($cartCount > 0)
                    <div id="navCartBadge" class="absolute top-0 right-0 h-5 w-5 rounded-full bg-primary text-white text-xs flex items-center justify-center font-bold transform translate-x-1 -translate-y-1 shadow-sm" style="min-width: 20px;">
                        {{ $cartCount }}
                    </div>
                    @else
                    <div id="navCartBadge" class="absolute top-0 right-0 h-5 w-5 rounded-full bg-primary text-white text-xs flex items-center justify-center font-bold transform translate-x-1 -translate-y-1 shadow-sm hidden" style="min-width: 20px;"></div>
                    @endif
                    <span class="text-xs font-medium">Ver carrinho</span>
                </a>
            </div>
        </nav>
        @endif
        
        <!-- Barra Inferior do Carrinho (só exibe se houver itens) - POSICIONADA ACIMA DA NAVEGAÇÃO -->
        @if(!request()->routeIs('pedido.checkout*') && !request()->routeIs('pedido.payment*') && !request()->routeIs('pedido.cart.index'))
        <div id="cartBottomBar" class="fixed left-0 right-0 bg-white border-t border-gray-200 shadow-lg z-40 {{ $cartCount > 0 ? '' : 'hidden' }}" style="bottom: 60px;">
            <div class="max-w-7xl mx-auto px-3 sm:px-4 py-2.5 sm:py-3 flex items-center justify-between gap-2 sm:gap-4">
                <div class="flex-1 min-w-0">
                    <div class="text-xs sm:text-sm font-medium text-gray-900 leading-tight">Subtotal</div>
                    <div class="text-base sm:text-lg font-bold text-gray-900 leading-tight truncate" id="cartBottomTotal">R$ 0,00</div>
                    <div class="text-xs text-gray-500 leading-tight whitespace-nowrap" id="cartBottomItems">0 itens</div>
                </div>
                <a href="{{ route('pedido.cart.index') }}" class="inline-flex items-center justify-center whitespace-nowrap rounded-lg text-sm font-medium bg-primary text-primary-foreground hover:bg-primary/90 h-11 sm:h-12 px-4 sm:px-6 flex-shrink-0 relative">
                    <span class="relative">
                        Ver carrinho
                        @if($cartCount > 0)
                        <span class="absolute -top-2 -right-2 h-5 w-5 rounded-full bg-white text-primary text-xs flex items-center justify-center font-bold border-2 border-primary" id="cartBottomBarBadge">{{ $cartCount }}</span>
                        @endif
                    </span>
                </a>
            </div>
        </div>
        @endif
        
        <!-- Drawer do Carrinho (slide-in da direita) -->
        @if(!request()->routeIs('pedido.checkout*') && !request()->routeIs('pedido.payment*') && !request()->routeIs('pedido.cart.index'))
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
    
    

    <!-- Scripts do layout pedido -->
    <script>
        // Injetar rotas do Laravel no objeto global
        window.pedidoRoutes = {
            cartItems: '{{ route('pedido.cart.items') }}',
            cartUpdate: '{{ route('pedido.cart.update') }}',
            cartRemove: '{{ route('pedido.cart.remove') }}',
            cartCount: '{{ route('pedido.cart.count') }}'
        };

        // Função global de notificação (Toast)
        function showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            // Padronizado para marrom conforme pedido do usuário
            notification.className = `fixed bottom-20 left-1/2 -translate-x-1/2 px-6 py-3 rounded-full shadow-2xl z-[9999] bg-[#7A5230] text-white text-sm font-semibold animate-fade-in flex items-center gap-3`;
            
            // Ícone baseado no tipo (opcional, mas mantido o check para sucesso)
            let icon = '';
            if (type === 'success') {
                icon = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>';
            } else {
                icon = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>';
            }

            notification.innerHTML = `${icon}<span>${message}</span>`;
            document.body.appendChild(notification);
            
            // Remover após 3 segundos com transição suave
            setTimeout(() => {
                notification.style.opacity = '0';
                notification.style.transform = 'translate(-50%, 20px)';
                notification.style.transition = 'all 0.4s ease-in';
                setTimeout(() => notification.remove(), 400);
            }, 3000);
        }
    </script>
    <script src="{{ asset('js/pedido-layout.js') }}" defer></script>

    @stack('scripts')
</body>
</html>

