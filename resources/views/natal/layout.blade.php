<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Olika - Cardápio de Natal 🎄')</title>
    <meta name="description" content="@yield('description', 'Cardápio especial de Natal. Pães artesanais com fermentação natural. Peça online 24h por dia.')">
    
    <!-- Google Fonts: Inter - Local com fallback -->
    @if(file_exists(public_path('css/inter-fonts.css')))
    <link rel="stylesheet" href="{{ asset('css/inter-fonts.css') }}">
    @else
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @endif
    
    <!-- Estilos críticos - carregados primeiro para evitar FOUC -->
    <link rel="stylesheet" href="{{ asset('css/critical-styles.css') }}">
    
    <!-- Estilos específicos do tema Natal -->
    <link rel="stylesheet" href="{{ asset('css/natal-theme.css') }}">
    
    <!-- Configuração do Tailwind e carregador para tema Natal -->
    <script>
        window.tailwindConfig = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            DEFAULT: '#C41E3A',
                            foreground: '#fff',
                        },
                        secondary: {
                            DEFAULT: '#228B22',
                            foreground: '#fff',
                        },
                        accent: {
                            DEFAULT: '#FFD700',
                            foreground: '#1a1a1a',
                        },
                        natal: {
                            red: '#C41E3A',
                            green: '#228B22',
                            gold: '#FFD700',
                            cream: '#FFF8DC',
                        },
                        background: '#FFF8DC',
                        foreground: '#1a1a1a',
                        card: '#fff',
                        muted: {
                            DEFAULT: '#f5f5f5',
                            foreground: '#666',
                        },
                        border: '#e0e0e0',
                    },
                    fontFamily: {
                        sans: ['Inter', 'system-ui', '-apple-system', 'sans-serif'],
                    },
                },
            },
        };
        window.tailwindLocalPath = '{{ asset("js/tailwind.min.js") }}';
    </script>
    <script src="{{ asset('js/tailwind-loader-natal.js') }}" defer></script>
</head>
<body class="bg-background text-foreground min-h-screen" style="background-color: #FFF8DC;">
    <!-- Header Natal -->
    <header class="sticky top-0 z-50 w-full bg-white border-b border-border shadow-sm">
        <div class="container mx-auto px-4 py-3">
            <div class="flex items-center justify-between">
                <a href="{{ route('natal.index') }}" class="flex items-center gap-2">
                    <span class="text-2xl">🎄</span>
                    <div>
                        <h1 class="text-xl font-bold text-primary" style="color: #C41E3A;">Olika Natal</h1>
                        <p class="text-xs text-muted-foreground">Cardápio Especial</p>
                    </div>
                </a>
                <nav class="flex items-center gap-4">
                    <a href="{{ route('natal.index') }}" class="text-sm font-medium text-foreground hover:text-primary">Cardápio</a>
                    <a href="{{ route('natal.cart.index') }}" class="relative text-sm font-medium text-foreground hover:text-primary">
                        Carrinho
                        <span id="cart-count" class="absolute -top-2 -right-2 bg-primary text-white text-xs rounded-full h-5 w-5 flex items-center justify-center" style="background-color: #C41E3A;">0</span>
                    </a>
                </nav>
            </div>
        </div>
    </header>

    <main class="flex-1">
        @yield('content')
    </main>

    <!-- Footer Natal -->
    <footer class="bg-primary text-white py-6 mt-auto" style="background-color: #C41E3A;">
        <div class="container mx-auto px-4 text-center text-sm">
            <p>&copy; {{ date('Y') }} Olika Natal. Todos os direitos reservados. 🎄</p>
        </div>
    </footer>
    
    @stack('scripts')
    
    <!-- Script para atualizar contador do carrinho -->
    <script>
        function updateCartBadge() {
            fetch('{{ route("natal.cart.count") }}')
                .then(r => r.json())
                .then(data => {
                    const badge = document.getElementById('cart-count');
                    if (badge) {
                        badge.textContent = data.count || 0;
                        badge.style.display = (data.count > 0) ? 'flex' : 'none';
                    }
                })
                .catch(() => {});
        }
        updateCartBadge();
        setInterval(updateCartBadge, 5000);
    </script>
</body>
</html>

