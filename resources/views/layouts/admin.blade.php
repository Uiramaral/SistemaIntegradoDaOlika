<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <script>
        // FUNÇÕES GLOBAIS PARA ALPINE.JS

        // Função para calcular preço mínimo de variantes
        window.minVariantPrice = function (product) {
            if (!product || !product.variants || product.variants.length === 0) {
                return product?.price || 0;
            }

            const prices = product.variants
                .filter(v => v.is_active !== false) // Apenas variantes ativas
                .map(v => parseFloat(v.price) || 0);

            if (prices.length === 0) {
                return product.price || 0;
            }

            return Math.min(...prices);
        };

        // Função para formatar preço (já existia, mas garantindo que esteja disponível)
        window.formatPrice = function (value) {
            if (typeof value !== 'number') {
                value = parseFloat(value) || 0;
            }
            return new Intl.NumberFormat('pt-BR', {
                style: 'currency',
                currency: 'BRL'
            }).format(value);
        };

        // FUNÇÃO: Alpine.js component para busca e filtros (precisa estar no head)
        window.deliveriesLiveSearch = function (initialSearch = '', initialStatus = 'all') {
            return {
                search: initialSearch,
                statusFilter: initialStatus,
                showNoResults: false,

                init() {
                    this.$watch('search', () => this.filterCards());
                    this.$watch('statusFilter', () => this.filterCards());
                    this.filterCards();
                },

                matchesCard(element) {
                    if (!this.search && this.statusFilter === 'all') return true;

                    const customer = element.dataset.searchCustomer || '';
                    const order = element.dataset.searchOrder || '';
                    const status = element.dataset.searchStatus || '';
                    const orderStatus = element.dataset.orderStatus || '';

                    const matchesSearch = !this.search ||
                        customer.includes(this.search.toLowerCase()) ||
                        order.includes(this.search.toLowerCase()) ||
                        status.includes(this.search.toLowerCase());

                    const matchesStatus = this.statusFilter === 'all' || orderStatus === this.statusFilter;

                    return matchesSearch && matchesStatus;
                },

                filterCards() {
                    const cards = document.querySelectorAll('[data-search-customer]');
                    let visibleCount = 0;

                    cards.forEach(card => {
                        if (this.matchesCard(card)) {
                            card.style.display = '';
                            visibleCount++;
                        } else {
                            card.style.display = 'none';
                        }
                    });

                    this.showNoResults = visibleCount === 0 && (this.search || this.statusFilter !== 'all');
                },

                closeAllMenus() {
                    document.querySelectorAll('[x-data]').forEach(el => {
                        if (el.__x && el.__x.$data && typeof el.__x.$data.open !== 'undefined') {
                            el.__x.$data.open = false;
                        }
                    });
                }
            };
        };
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        // Buscar cor do tema antes de usar
        $clientSettings = \App\Models\Setting::getSettings();
        $themeSettings = $clientSettings->getThemeSettings();
        $themeColor = $themeSettings['theme_primary_color'] ?? '#f59e0b';

        // Buscar favicon personalizado das configurações
        $clientId = currentClientId();
        $personalizationSettings = \App\Models\PaymentSetting::where('client_id', $clientId)
            ->whereIn('key', ['favicon'])
            ->pluck('value', 'key')
            ->toArray();

        // Verificar se há favicons em public/favicon/ (gerados pelo genfavicon)
        $usePublicFavicons = file_exists(public_path('favicon/favicon.ico'));

        // Buscar logo para PWA
        $logoUrl = null;
        if (isset($personalizationSettings['logo']) && $personalizationSettings['logo']) {
            $logoUrl = asset('storage/' . $personalizationSettings['logo']);
        } else {
            $logoUrl = $themeSettings['theme_logo_url'] ?? null;
        }

        // Nome da marca
        $brandName = $themeSettings['theme_brand_name'] ?? 'OLIKA';
    @endphp
    <meta name="theme-color" content="{{ $themeColor }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="{{ $brandName }}">
    <link rel="manifest" href="{{ route('manifest.json') }}">

    {{-- Favicons --}}
    @if($usePublicFavicons)
        {{-- Usar favicons gerados do diretório public/favicon/ --}}
        <link rel="icon" type="image/x-icon" href="{{ asset('favicon/favicon.ico') }}?v={{ time() }}">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon/genfavicon-16.png') }}?v={{ time() }}">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon/genfavicon-32.png') }}?v={{ time() }}">
        <link rel="icon" type="image/png" sizes="48x48" href="{{ asset('favicon/genfavicon-48.png') }}?v={{ time() }}">
        <link rel="icon" type="image/png" sizes="64x64" href="{{ asset('favicon/genfavicon-64.png') }}?v={{ time() }}">
        <link rel="icon" type="image/png" sizes="128x128" href="{{ asset('favicon/genfavicon-128.png') }}?v={{ time() }}">
        <link rel="icon" type="image/png" sizes="256x256" href="{{ asset('favicon/genfavicon-256.png') }}?v={{ time() }}">

        {{-- Apple Touch Icons --}}
        <link rel="apple-touch-icon" sizes="57x57" href="{{ asset('favicon/apple-touch-icon-57x57.png') }}?v={{ time() }}">
        <link rel="apple-touch-icon" sizes="114x114"
            href="{{ asset('favicon/apple-touch-icon-114x114.png') }}?v={{ time() }}">
        <link rel="apple-touch-icon" sizes="120x120"
            href="{{ asset('favicon/apple-touch-icon-120x120.png') }}?v={{ time() }}">
        <link rel="apple-touch-icon" sizes="180x180"
            href="{{ asset('favicon/apple-touch-icon-180x180.png') }}?v={{ time() }}">
        <link rel="apple-touch-icon" href="{{ asset('favicon/apple-touch-icon.png') }}?v={{ time() }}">
    @else
        {{-- Fallback: usar favicon personalizado das configurações ou padrão --}}
        @php
            $faviconUrl = null;
            if (isset($personalizationSettings['favicon']) && $personalizationSettings['favicon']) {
                $faviconUrl = asset('storage/' . $personalizationSettings['favicon']);
            } else {
                $faviconUrl = $themeSettings['theme_favicon_url'] ?? '/favicon.ico';
            }

            if ($faviconUrl !== '/favicon.ico') {
                $faviconUrl .= (strpos($faviconUrl, '?') !== false ? '&' : '?') . 'v=' . time();
            }
        @endphp
        <link rel="icon" type="image/png" href="{{ $faviconUrl }}">
        @if($faviconUrl && $faviconUrl !== '/favicon.ico')
            <link rel="apple-touch-icon" href="{{ $faviconUrl }}">
        @else
            <link rel="apple-touch-icon" href="{{ asset('pwa-192x192.svg') }}">
        @endif
    @endif
    <title>@yield('title', 'Olika Admin')</title>

    @php
        // Carregar configurações de tema do estabelecimento (SaaS)
        $clientSettings = \App\Models\Setting::getSettings();
        $themeSettings = $clientSettings->getThemeSettings();

        // Buscar logo e favicon de payment_settings também
        $clientId = currentClientId();
        $personalizationSettings = \App\Models\PaymentSetting::where('client_id', $clientId)
            ->whereIn('key', ['logo', 'favicon'])
            ->pluck('value', 'key')
            ->toArray();

        // Atualizar themeSettings com logo e favicon de payment_settings se existirem
        if (isset($personalizationSettings['logo']) && $personalizationSettings['logo']) {
            $themeSettings['theme_logo_url'] = asset('storage/' . $personalizationSettings['logo']);
        }
        if (isset($personalizationSettings['favicon']) && $personalizationSettings['favicon']) {
            $themeSettings['theme_favicon_url'] = asset('storage/' . $personalizationSettings['favicon']);
        }

        // Processar favicon após atualizar themeSettings (já foi processado acima)

        // Helper para converter HEX para HSL
        if (!function_exists('hexToHsl')) {
            function hexToHsl($hex)
            {
                try {
                    $hex = str_replace('#', '', $hex);
                    if (strlen($hex) === 3) {
                        $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
                    }
                    $r = hexdec(substr($hex, 0, 2)) / 255;
                    $g = hexdec(substr($hex, 2, 2)) / 255;
                    $b = hexdec(substr($hex, 4, 2)) / 255;

                    $max = max($r, $g, $b);
                    $min = min($r, $g, $b);
                    $delta = $max - $min;

                    $h = 0;
                    $s = 0;
                    $l = ($max + $min) / 2;

                    if ($delta !== 0.0) {
                        $s = $l > 0.5 ? $delta / (2 - $max - $min) : $delta / ($max + $min);

                        switch ($max) {
                            case $r:
                                $h = ($g - $b) / $delta + ($g < $b ? 6 : 0);
                                break;
                            case $g:
                                $h = ($b - $r) / $delta + 2;
                                break;
                            case $b:
                                $h = ($r - $g) / $delta + 4;
                                break;
                        }

                        $h /= 6;
                    }

                    $h = round($h * 360);
                    $s = round($s * 100);
                    $l = round($l * 100);

                    return "{$h} {$s}% {$l}%";
                } catch (\Exception $e) {
                    // Retornar valor padrão em caso de erro
                    return "38 92% 50%"; // Cor padrão laranja
                }
            }
        }

        // Garantir que as variáveis sempre sejam definidas
        try {
            $primaryHsl = hexToHsl($themeSettings['theme_primary_color'] ?? '#f59e0b');
            $secondaryHsl = hexToHsl($themeSettings['theme_secondary_color'] ?? '#8b5cf6');
            $accentHsl = hexToHsl($themeSettings['theme_accent_color'] ?? '#10b981');
        } catch (\Exception $e) {
            // Valores padrão em caso de erro
            $primaryHsl = "38 92% 50%";
            $secondaryHsl = "262 83% 58%";
            $accentHsl = "142 76% 36%";
        }
    @endphp

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        border: "hsl(0 0% 89%)",
                        input: "hsl(0 0% 89%)",
                        ring: "hsl({{ $primaryHsl }})",
                        background: "hsl(0 0% 99%)",
                        foreground: "hsl(222 47% 11%)",
                        primary: {
                            DEFAULT: "hsl({{ $primaryHsl }})",
                            foreground: "hsl(0 0% 100%)",
                            50: "hsl({{ explode(' ', $primaryHsl)[0] }} {{ explode(' ', $primaryHsl)[1] }} 95%)",
                            100: "hsl({{ explode(' ', $primaryHsl)[0] }} {{ explode(' ', $primaryHsl)[1] }} 90%)",
                            200: "hsl({{ explode(' ', $primaryHsl)[0] }} {{ explode(' ', $primaryHsl)[1] }} 80%)",
                            300: "hsl({{ explode(' ', $primaryHsl)[0] }} {{ explode(' ', $primaryHsl)[1] }} 70%)",
                            400: "hsl({{ explode(' ', $primaryHsl)[0] }} {{ explode(' ', $primaryHsl)[1] }} 60%)",
                            500: "hsl({{ $primaryHsl }})",
                            600: "hsl({{ explode(' ', $primaryHsl)[0] }} {{ explode(' ', $primaryHsl)[1] }} 45%)",
                            700: "hsl({{ explode(' ', $primaryHsl)[0] }} {{ explode(' ', $primaryHsl)[1] }} 40%)",
                            800: "hsl({{ explode(' ', $primaryHsl)[0] }} {{ explode(' ', $primaryHsl)[1] }} 35%)",
                            900: "hsl({{ explode(' ', $primaryHsl)[0] }} {{ explode(' ', $primaryHsl)[1] }} 30%)"
                        },
                        secondary: {
                            DEFAULT: "hsl({{ $secondaryHsl }})",
                            foreground: "hsl(0 0% 100%)",
                            50: "hsl({{ explode(' ', $secondaryHsl)[0] }} {{ explode(' ', $secondaryHsl)[1] }} 95%)",
                            100: "hsl({{ explode(' ', $secondaryHsl)[0] }} {{ explode(' ', $secondaryHsl)[1] }} 90%)",
                            200: "hsl({{ explode(' ', $secondaryHsl)[0] }} {{ explode(' ', $secondaryHsl)[1] }} 80%)",
                            300: "hsl({{ explode(' ', $secondaryHsl)[0] }} {{ explode(' ', $secondaryHsl)[1] }} 70%)",
                            400: "hsl({{ explode(' ', $secondaryHsl)[0] }} {{ explode(' ', $secondaryHsl)[1] }} 65%)",
                            500: "hsl({{ $secondaryHsl }})",
                            600: "hsl({{ explode(' ', $secondaryHsl)[0] }} {{ explode(' ', $secondaryHsl)[1] }} 55%)",
                            700: "hsl({{ explode(' ', $secondaryHsl)[0] }} {{ explode(' ', $secondaryHsl)[1] }} 50%)",
                            800: "hsl({{ explode(' ', $secondaryHsl)[0] }} {{ explode(' ', $secondaryHsl)[1] }} 45%)",
                            900: "hsl({{ explode(' ', $secondaryHsl)[0] }} {{ explode(' ', $secondaryHsl)[1] }} 40%)"
                        },
                        destructive: { DEFAULT: "hsl(0 84% 60%)", foreground: "hsl(0 0% 100%)" },
                        muted: { DEFAULT: "hsl(0 0% 96%)", foreground: "hsl(215 16% 47%)" },
                        accent: {
                            DEFAULT: "hsl({{ $accentHsl }})",
                            foreground: "hsl(0 0% 100%)",
                            50: "hsl({{ explode(' ', $accentHsl)[0] }} {{ explode(' ', $accentHsl)[1] }} 95%)",
                            100: "hsl({{ explode(' ', $accentHsl)[0] }} {{ explode(' ', $accentHsl)[1] }} 90%)",
                            200: "hsl({{ explode(' ', $accentHsl)[0] }} {{ explode(' ', $accentHsl)[1] }} 80%)",
                            300: "hsl({{ explode(' ', $accentHsl)[0] }} {{ explode(' ', $accentHsl)[1] }} 70%)",
                            400: "hsl({{ explode(' ', $accentHsl)[0] }} {{ explode(' ', $accentHsl)[1] }} 60%)",
                            500: "hsl({{ $accentHsl }})",
                            600: "hsl({{ explode(' ', $accentHsl)[0] }} {{ explode(' ', $accentHsl)[1] }} 47%)",
                            700: "hsl({{ explode(' ', $accentHsl)[0] }} {{ explode(' ', $accentHsl)[1] }} 42%)",
                            800: "hsl({{ explode(' ', $accentHsl)[0] }} {{ explode(' ', $accentHsl)[1] }} 37%)",
                            900: "hsl({{ explode(' ', $accentHsl)[0] }} {{ explode(' ', $accentHsl)[1] }} 32%)"
                        },
                        popover: { DEFAULT: "hsl(0 0% 100%)", foreground: "hsl(222 47% 11%)" },
                        card: { DEFAULT: "hsl(0 0% 100%)", foreground: "hsl(222 47% 11%)" },
                        success: { DEFAULT: "hsl(142 76% 36%)", foreground: "hsl(0 0% 100%)" },
                        sidebar: {
                            DEFAULT: "hsl(222 47% 11%)",
                            foreground: "hsl(0 0% 98%)",
                            primary: "hsl({{ $primaryHsl }})",
                            "primary-foreground": "hsl(0 0% 100%)",
                            accent: "hsl({{ $primaryHsl }})",
                            "accent-foreground": "hsl(0 0% 100%)",
                            border: "hsl(217 33% 17%)",
                            ring: "hsl({{ $primaryHsl }})"
                        }
                    },
                    borderRadius: {
                        lg: "0.75rem",
                        md: "calc(0.75rem - 2px)",
                        sm: "calc(0.75rem - 4px)"
                    },
                    boxShadow: {
                        'sweetspot': '0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03)',
                        'sweetspot-md': '0 10px 15px -3px rgba(0, 0, 0, 0.08), 0 4px 6px -2px rgba(0, 0, 0, 0.04)',
                        'sweetspot-lg': '0 20px 25px -5px rgba(0, 0, 0, 0.08), 0 10px 10px -5px rgba(0, 0, 0, 0.04)',
                        'card': '0 1px 3px 0 rgb(0 0 0 / 0.05), 0 1px 2px -1px rgb(0 0 0 / 0.05)',
                        'card-hover': '0 10px 15px -3px rgb(0 0 0 / 0.08), 0 4px 6px -4px rgb(0 0 0 / 0.05)',
                        'sidebar': '4px 0 6px -1px rgb(0 0 0 / 0.1)',
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="{{ asset('css/admin-bridge.css') }}">
    <link rel="stylesheet" href="{{ asset('css/lovable-global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sweetspot-theme.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard-sweetspot-pixel-perfect.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sidebar-sweetspot-pixel-perfect.css') }}">
    <link rel="stylesheet" href="{{ asset('css/header-sweetspot-pixel-perfect.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard-list-fixes.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard-sweetspot-final.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard-mobile-fixes.css') }}">
    <link rel="stylesheet" href="{{ asset('css/copycat-design-system.css') }}">
    <link rel="stylesheet" href="{{ asset('css/reference-layout.css') }}">

    <script defer src="https://unpkg.com/lucide@latest"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function () {
                navigator.serviceWorker.register('{{ asset('sw.js') }}').catch(function () { });
            });
        }
    </script>

    <style>
        :root {
            --primary:
                {{ $primaryHsl }}
            ;
            --secondary:
                {{ $secondaryHsl }}
            ;
            --accent:
                {{ $accentHsl }}
            ;
            --radius:
                {{ $themeSettings['theme_border_radius'] }}
            ;
            --font-family:
                {!! $themeSettings['theme_font_family'] !!}
            ;
        }

        body {
            font-family: var(--font-family);
        }

        .active-item {
            position: relative;
        }

        .active-item::after {
            content: '';
            position: absolute;
            left: -12px;
            top: 20%;
            bottom: 20%;
            width: 4px;
            background-color: hsl(var(--primary));
            border-radius: 0 4px 4px 0;
        }
    </style>

    @stack('styles')
</head>

<body class="layout-reference bg-background text-foreground antialiased">
    @php
        $navItems = [
            ['label' => 'Dashboard', 'icon' => 'layout-dashboard', 'route' => 'dashboard.index', 'routePattern' => 'dashboard.index'],
            [
                'label' => 'Pedidos',
                'icon' => 'receipt',
                'children' => [
                    ['label' => 'Listagem', 'icon' => 'list', 'route' => 'dashboard.orders.index', 'routePattern' => 'dashboard.orders.*'],
                    ['label' => 'Entregas', 'icon' => 'truck', 'route' => 'dashboard.deliveries.index', 'routePattern' => 'dashboard.deliveries.*'],
                ],
            ],
            ['label' => 'Clientes', 'icon' => 'users', 'route' => 'dashboard.customers.index', 'routePattern' => 'dashboard.customers.*'],
            [
                'label' => 'Produtos',
                'icon' => 'package',
                'children' => [
                    ['label' => 'Listagem', 'icon' => 'list', 'route' => 'dashboard.products.index', 'routePattern' => 'dashboard.products.index'],
                    ['label' => 'Categorias', 'icon' => 'tag', 'route' => 'dashboard.categories.index', 'routePattern' => 'dashboard.categories.*'],
                    ['label' => 'Preços de Revenda', 'icon' => 'shopping-bag', 'route' => 'dashboard.wholesale-prices.index', 'routePattern' => 'dashboard.wholesale-prices.*'],
                ],
            ],
            [
                'label' => 'Produção',
                'icon' => 'factory',
                'children' => [
                    ['label' => 'Estoque', 'icon' => 'box', 'route' => 'dashboard.producao.estoque-produzidos.index', 'routePattern' => 'dashboard.producao.estoque-produzidos.*'],
                    ['label' => 'Lista de Compras', 'icon' => 'shopping-cart', 'route' => 'dashboard.producao.lista-compras.index', 'routePattern' => 'dashboard.producao.lista-compras.*'],
                    ['label' => 'Receitas', 'icon' => 'book-open', 'route' => 'dashboard.producao.receitas.index', 'routePattern' => 'dashboard.producao.receitas.*'],
                    ['label' => 'Dashboard', 'icon' => 'layout-dashboard', 'route' => 'dashboard.producao.index', 'routePattern' => 'dashboard.producao.index'],
                    ['label' => 'Ingredientes', 'icon' => 'wheat', 'route' => 'dashboard.producao.ingredientes.index', 'routePattern' => 'dashboard.producao.ingredientes.*'],
                    ['label' => 'Lista de Produção', 'icon' => 'list-todo', 'route' => 'dashboard.producao.lista-producao.index', 'routePattern' => 'dashboard.producao.lista-producao.*'],
                    ['label' => 'Estoque Produzidos', 'icon' => 'box', 'route' => 'dashboard.producao.estoque-produzidos.index', 'routePattern' => 'dashboard.producao.estoque-produzidos.*'],
                    ['label' => 'Custos', 'icon' => 'calculator', 'route' => 'dashboard.producao.custos.index', 'routePattern' => 'dashboard.producao.custos.*'],
                    ['label' => 'Configurações de Custos', 'icon' => 'settings', 'route' => 'dashboard.producao.configuracoes-custos.index', 'routePattern' => 'dashboard.producao.configuracoes-custos.*'],
                ],
            ],
            ['label' => 'Finanças', 'icon' => 'wallet', 'route' => 'dashboard.financas.index', 'routePattern' => 'dashboard.financas.*'],
            [
                'label' => 'Marketing',
                'icon' => 'megaphone',
                'children' => [
                    ['label' => 'Cupons', 'icon' => 'percent', 'route' => 'dashboard.coupons.index', 'routePattern' => 'dashboard.coupons.*'],
                    ['label' => 'Cashback', 'icon' => 'gift', 'route' => 'dashboard.cashback.index', 'routePattern' => 'dashboard.cashback.*'],
                ],
            ],
            [
                'label' => 'Integrações',
                'icon' => 'plug',
                'children' => [
                    ['label' => 'WhatsApp', 'icon' => 'message-square', 'route' => 'dashboard.settings.whatsapp', 'routePattern' => 'dashboard.settings.whatsapp*', 'feature' => 'whatsapp'],
                    ['label' => 'Assistente IA', 'icon' => 'bot', 'route' => 'dashboard.assistente-ia.index', 'routePattern' => 'dashboard.assistente-ia.*'],
                ],
            ],
            [
                'label' => 'Configurações',
                'icon' => 'settings',
                'children' => [
                    ['label' => 'Geral', 'icon' => 'sliders', 'route' => 'dashboard.settings', 'routePattern' => 'dashboard.settings*'],
                    ['label' => 'Planos', 'icon' => 'credit-card', 'route' => 'dashboard.subscription.index', 'routePattern' => 'dashboard.subscription.*'],
                ],
            ],
        ];

        $user = auth()->user();
        $isSuperAdmin = false;
        if (auth()->check() && $user) {
            if (method_exists($user, 'isSuperAdmin')) {
                $isSuperAdmin = $user->isSuperAdmin();
            } else {
                $isSuperAdmin = ($user->client_id === 1 || $user->client_id === null);
            }
        }
        if ($isSuperAdmin) {
            $navItems[] = [
                'label' => 'Master',
                'icon' => 'shield',
                'children' => [
                    ['label' => 'Dashboard Master', 'icon' => 'layout-dashboard', 'route' => 'master.dashboard', 'routePattern' => 'master.dashboard'],
                    ['label' => 'Clientes/Estab.', 'icon' => 'building-2', 'route' => 'master.clients.index', 'routePattern' => 'master.clients.*'],
                    ['label' => 'Planos', 'icon' => 'crown', 'route' => 'master.plans.index', 'routePattern' => 'master.plans.*'],
                    ['label' => 'WhatsApp URLs', 'icon' => 'server', 'route' => 'master.whatsapp-urls.index', 'routePattern' => 'master.whatsapp-urls.*'],
                    ['label' => 'Config. Master', 'icon' => 'sliders', 'route' => 'master.settings.index', 'routePattern' => 'master.settings.*'],
                ],
            ];
        }
    @endphp

    <div class="min-h-screen w-full bg-background">
        <button id="sidebar-open"
            class="lg:hidden fixed top-4 left-4 z-50 p-2 rounded-lg bg-card border border-border text-foreground shadow-lg">
            <i data-lucide="menu" class="h-6 w-6"></i>
        </button>
        <div class="flex min-h-screen w-full">
            <div id="sidebar-backdrop"
                class="fixed inset-0 z-30 bg-black/80 opacity-0 pointer-events-none transition-opacity duration-200 md:hidden">
            </div>

            <aside id="sidebar"
                class="fixed inset-y-0 left-0 z-40 flex w-64 flex-col bg-card border-r border-border text-foreground transition-transform duration-300 ease-in-out -translate-x-full lg:translate-x-0 shadow-sidebar">
                <div class="sidebar-logo-area flex items-center justify-between px-6 py-5 border-b border-border">
                    <div class="flex items-center gap-3">
                        @php
                            $logoUrl = $themeSettings['theme_logo_url'] ?? null;
                            // Adicionar timestamp para evitar cache
                            if ($logoUrl && $logoUrl !== '/images/logo-default.png') {
                                $logoUrl .= (strpos($logoUrl, '?') !== false ? '&' : '?') . 'v=' . time();
                            }
                        @endphp
                        @if($logoUrl && $logoUrl !== '/images/logo-default.png')
                            <img src="{{ $logoUrl }}" alt="Logo" class="w-10 h-10 rounded-xl object-contain shrink-0">
                        @else
                            <div
                                class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary/30 to-primary/50 flex items-center justify-center shrink-0">
                                <i data-lucide="cake" class="w-6 h-6 text-primary"></i>
                            </div>
                        @endif
                        <div class="min-w-0">
                            <div class="sidebar-brand-name font-bold text-lg leading-tight text-foreground">
                                {{ $themeSettings['theme_brand_name'] }}
                            </div>
                            <div class="sidebar-sub-brand text-xs text-muted-foreground">Gestão profissional</div>
                        </div>
                    </div>
                    <button id="sidebar-close"
                        class="lg:hidden p-2 rounded-lg text-muted-foreground hover:text-foreground">
                        <i data-lucide="x" class="h-6 w-6"></i>
                    </button>
                </div>

                <nav class="flex-1 overflow-y-auto py-4 px-3 scrollbar-thin">
                    <ul class="space-y-1">
                        @foreach ($navItems as $item)
                            @php
                                $hasChildren = isset($item['children']) && is_array($item['children']);
                                $isActive = isset($item['routePattern']) ? request()->routeIs($item['routePattern']) : false;
                                $isParentActive = false;
                                if ($hasChildren) {
                                    foreach ($item['children'] as $child) {
                                        if (isset($child['routePattern']) && request()->routeIs($child['routePattern'])) {
                                            $isParentActive = true;
                                            break;
                                        }
                                    }
                                }
                                $openByDefault = $isParentActive;
                            @endphp
                            <li>
                                @if($hasChildren)
                                    <details class="sidebar-accordion group" {{ $openByDefault ? 'open' : '' }}>
                                        <summary class="sidebar-item {{ $isParentActive ? 'active-item' : '' }}">
                                            <i data-lucide="{{ $item['icon'] }}"></i>
                                            <span class="text-sm font-medium">{{ $item['label'] }}</span>
                                            <i data-lucide="chevron-down"
                                                class="ml-auto h-4 w-4 sidebar-chevron transition-transform duration-200 group-open:rotate-180"></i>
                                        </summary>
                                        <ul class="mt-1 space-y-1">
                                            @foreach ($item['children'] as $child)
                                                @php
                                                    $isAvailable = !isset($child['feature']) || currentClientHasFeature($child['feature']);
                                                    $href = $isAvailable ? route($child['route']) : route('dashboard.subscription.index');
                                                    $childActive = isset($child['routePattern']) ? request()->routeIs($child['routePattern']) : false;
                                                @endphp
                                                <li>
                                                    <a href="{{ $href }}"
                                                        class="sidebar-submenu-item {{ $childActive ? 'active' : '' }} {{ !$isAvailable ? 'opacity-50' : '' }}">
                                                        <i data-lucide="{{ $child['icon'] }}"></i>
                                                        <span class="text-sm">{{ $child['label'] }}</span>
                                                        @if(!$isAvailable)
                                                            <i data-lucide="lock" class="ml-auto h-3 w-3"></i>
                                                        @endif
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </details>
                                @else
                                    @php
                                        $href = isset($item['href']) ? $item['href'] : (isset($item['route']) ? route($item['route']) : '#');
                                    @endphp
                                    <a href="{{ $href }}" class="sidebar-item {{ $isActive ? 'active-item' : '' }}">
                                        <i data-lucide="{{ $item['icon'] }}"></i>
                                        <span class="text-sm font-medium">{{ $item['label'] }}</span>
                                    </a>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </nav>

                <div class="p-4 border-t border-border">
                    <form method="POST" action="{{ route('auth.logout') }}">
                        @csrf
                        <button type="submit" class="sidebar-logout-btn">
                            <i data-lucide="log-out"></i>
                            <span>Sair</span>
                        </button>
                    </form>
                </div>
            </aside>

            <div class="flex flex-1 flex-col lg:ml-64">
                @php
                    $hasPageHeader = View::hasSection('page_header');
                    $hasModernTitle = View::hasSection('page_title');
                    $hasLegacyTitle = View::hasSection('page-title');
                    $pageTitle = $hasModernTitle ? trim($__env->yieldContent('page_title')) : ($hasLegacyTitle ? trim($__env->yieldContent('page-title')) : null);
                    $hasModernSubtitle = View::hasSection('page_subtitle');
                    $hasLegacySubtitle = View::hasSection('page-subtitle');
                    $pageSubtitle = $hasModernSubtitle ? trim($__env->yieldContent('page_subtitle')) : ($hasLegacySubtitle ? trim($__env->yieldContent('page-subtitle')) : null);
                    $pageActionsSection = View::hasSection('page_actions') ? 'page_actions' : (View::hasSection('page-actions') ? 'page-actions' : null);

                    // Verificar se as seções existem E têm conteúdo (não vazio)
                    $statCardsSectionName = View::hasSection('stat_cards') ? 'stat_cards' : (View::hasSection('stat-cards') ? 'stat-cards' : null);
                    $statCardsSection = null;
                    if ($statCardsSectionName) {
                        $statCardsContent = trim($__env->yieldContent($statCardsSectionName));
                        $statCardsSection = $statCardsContent !== '' ? $statCardsSectionName : null;
                    }

                    $quickFiltersSectionName = View::hasSection('quick_filters') ? 'quick_filters' : (View::hasSection('quick-filters') ? 'quick-filters' : null);
                    $quickFiltersSection = null;
                    if ($quickFiltersSectionName) {
                        $quickFiltersContent = trim($__env->yieldContent($quickFiltersSectionName));
                        $quickFiltersSection = $quickFiltersContent !== '' ? $quickFiltersSectionName : null;
                    }

                    $pageDescriptionSection = View::hasSection('page_description') ? 'page_description' : (View::hasSection('page-description') ? 'page-description' : null);

                    $pageToolbarSectionName = View::hasSection('page_toolbar') ? 'page_toolbar' : (View::hasSection('page-toolbar') ? 'page-toolbar' : null);
                    $pageToolbarSection = null;
                    if ($pageToolbarSectionName) {
                        $pageToolbarContent = trim($__env->yieldContent($pageToolbarSectionName));
                        $pageToolbarSection = $pageToolbarContent !== '' ? $pageToolbarSectionName : null;
                    }
                @endphp

                <header class="bg-gray-100 border-b border-gray-200 sticky top-0 z-30 h-auto">
                    <div class="flex items-center justify-between px-6 py-3">
                        <!-- Left: Breadcrumb + Title -->
                        <div class="flex flex-col min-w-0">
                            <!-- Breadcrumbs -->
                            <nav class="text-xs text-gray-500 mb-1">
                                <span>Menu Principal</span>
                                @if($pageTitle && $pageTitle !== 'Dashboard')
                                    <span class="mx-1">></span>
                                    <span class="text-gray-700">{{ $pageTitle }}</span>
                                @endif
                            </nav>
                            <!-- Page Title -->
                            <h1 class="text-xl font-bold text-gray-900 leading-tight">{{ $pageTitle ?? 'Dashboard' }}
                            </h1>
                            @if ($pageSubtitle)
                                <p class="text-sm text-gray-600 leading-tight mt-0.5">{{ $pageSubtitle }}</p>
                            @endif
                        </div>

                        <!-- Right: Branding + Actions -->
                        <div class="flex items-center gap-3">
                            <!-- Branding Stack (above user) -->
                            <div class="flex flex-col items-end mr-2">
                                <h2 class="text-sm font-bold text-gray-900 leading-tight">{{ $brandName ?? 'OLIKA' }}
                                </h2>
                                <p class="text-[10px] text-gray-500 leading-tight">Gestão profissional</p>
                            </div>

                            <!-- Notification Bell -->
                            <div class="relative notification-dropdown">
                                <button id="notification-bell"
                                    class="relative h-9 w-9 rounded-lg border border-gray-300 bg-white text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors flex items-center justify-center">
                                    <i data-lucide="bell" class="h-4.5 w-4.5"></i>
                                    <span
                                        class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center border-2 border-white">3</span>
                                </button>

                                <!-- Dropdown de Notificações -->
                                <div id="notification-dropdown"
                                    class="hidden absolute right-0 mt-2 w-80 bg-white rounded-lg border border-gray-300 shadow-xl z-50 overflow-hidden">
                                    <div class="p-4 border-b border-gray-200 bg-gray-50">
                                        <h3 class="text-sm font-semibold text-gray-900">Notificações</h3>
                                    </div>
                                    <div class="max-h-96 overflow-y-auto">
                                        <!-- Item de notificação 1 -->
                                        <a href="{{ route('dashboard.orders.index') }}"
                                            class="block p-4 border-b border-gray-100 hover:bg-gray-50 transition-colors">
                                            <div class="flex items-start gap-3">
                                                <div
                                                    class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center shrink-0">
                                                    <i data-lucide="check-circle" class="h-4 w-4 text-green-600"></i>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-sm font-medium text-gray-900">Novo pedido recebido
                                                    </p>
                                                    <p class="text-xs text-gray-500 mt-1">Pedido #209 foi criado com
                                                        sucesso</p>
                                                    <p class="text-xs text-gray-400 mt-1">Há 2 horas</p>
                                                </div>
                                            </div>
                                        </a>

                                        <!-- Item de notificação 2 -->
                                        <a href="{{ route('dashboard.settings.whatsapp') }}"
                                            class="block p-4 border-b border-gray-100 hover:bg-gray-50 transition-colors">
                                            <div class="flex items-start gap-3">
                                                <div
                                                    class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center shrink-0">
                                                    <i data-lucide="info" class="h-4 w-4 text-blue-600"></i>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-sm font-medium text-gray-900">WhatsApp disponível</p>
                                                    <p class="text-xs text-gray-500 mt-1">Conecte o WhatsApp para enviar
                                                        notificações automáticas</p>
                                                    <p class="text-xs text-gray-400 mt-1">Há 5 horas</p>
                                                </div>
                                            </div>
                                        </a>

                                        <!-- Item de notificação 3 -->
                                        <a href="{{ route('dashboard.products.index') }}"
                                            class="block p-4 hover:bg-gray-50 transition-colors">
                                            <div class="flex items-start gap-3">
                                                <div
                                                    class="w-8 h-8 rounded-full bg-amber-100 flex items-center justify-center shrink-0">
                                                    <i data-lucide="alert-triangle" class="h-4 w-4 text-amber-600"></i>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-sm font-medium text-gray-900">Estoque baixo</p>
                                                    <p class="text-xs text-gray-500 mt-1">Alguns ingredientes estão com
                                                        estoque baixo</p>
                                                    <p class="text-xs text-gray-400 mt-1">Há 1 dia</p>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                    <div class="p-3 border-t border-gray-200 bg-gray-50">
                                        <a href="#"
                                            class="block w-full text-center text-xs font-medium text-blue-600 hover:text-blue-700 transition-colors">
                                            Ver todas as notificações
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- User Profile -->
                            <div
                                class="flex items-center gap-2 pl-2 pr-3 h-10 rounded-full bg-white border border-gray-300 hover:bg-gray-50 transition-colors cursor-pointer header-user">
                                <div
                                    class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-xs">
                                    {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                                </div>
                                <div class="hidden sm:block text-left">
                                    <p class="text-xs font-semibold text-gray-900 leading-none">
                                        {{ auth()->user()->name ?? 'Admin' }}
                                    </p>
                                    <p class="text-[10px] text-gray-600 leading-none mt-0.5">{{ auth()->user()->email ??
                                        'admin@olika.com' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </header>

                <main class="flex-1 p-6 overflow-auto" id="main-content">
                    <div class="dashboard-content-wrapper">
                        @if(session('success') && !request()->routeIs('dashboard.index'))
                            <div
                                class="rounded-lg border border-success/30 bg-success/10 px-4 py-3 text-success shadow-sm mb-6">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if(session('error'))
                            <div
                                class="rounded-lg border border-destructive/30 bg-destructive/10 px-4 py-3 text-destructive shadow-sm mb-6">
                                {{ session('error') }}
                            </div>
                        @endif

                        @if($errors->any())
                            <div
                                class="rounded-lg border border-destructive/30 bg-destructive/10 px-4 py-3 text-destructive shadow-sm mb-6">
                                <ul class="list-disc space-y-1 pl-5 text-sm">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if ($pageToolbarSection)
                            <div
                                class="flex flex-wrap items-center gap-3 rounded-lg border border-border bg-card px-4 py-3 shadow-sm mb-6">
                                @yield($pageToolbarSection)
                            </div>
                        @endif

                        @if ($statCardsSection)
                            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-6">
                                @yield($statCardsSection)
                            </div>
                        @endif

                        @if ($quickFiltersSection)
                            <div class="rounded-lg border border-border bg-card px-4 py-3 shadow-sm mb-6">
                                @yield($quickFiltersSection)
                            </div>
                        @endif

                        <div class="dashboard-content">
                            @yield('content')
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </div>

    <script>
        window.applyTableMobileLabels = function (root = document) {
            try {
                const tables = root.querySelectorAll ? root.querySelectorAll('table[data-mobile-card="true"]') : [];
                tables.forEach((table) => {
                    const headers = Array.from(table.querySelectorAll('thead th')).map((th) =>
                        th.textContent.replace(/\s+/g, ' ').trim()
                    );
                    if (!headers.length) {
                        return;
                    }
                    table.querySelectorAll('tbody tr').forEach((row) => {
                        const cells = Array.from(row.children).filter((cell) => cell.tagName === 'TD' && !cell.hasAttribute('colspan'));
                        cells.forEach((cell, index) => {
                            const label = headers[index] || '';
                            if (label) {
                                cell.setAttribute('data-label', label);
                            }
                        });
                    });
                });
            } catch (error) {
                console.error('applyTableMobileLabels error:', error);
            }
        };

        document.addEventListener('DOMContentLoaded', () => {
            window.applyTableMobileLabels();
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const sidebar = document.getElementById('sidebar');
            const backdrop = document.getElementById('sidebar-backdrop');
            const openButton = document.getElementById('sidebar-open');
            const closeButton = document.getElementById('sidebar-close');

            const mediaQuery = window.matchMedia('(min-width: 768px)');

            function openSidebar() {
                sidebar.classList.remove('-translate-x-full');
                backdrop.classList.remove('pointer-events-none');
                backdrop.classList.remove('opacity-0');
                backdrop.classList.add('opacity-90');
            }

            function closeSidebar() {
                if (!mediaQuery.matches) {
                    sidebar.classList.add('-translate-x-full');
                    backdrop.classList.add('opacity-0');
                    backdrop.classList.remove('opacity-90');
                    setTimeout(() => {
                        if (!mediaQuery.matches) {
                            backdrop.classList.add('pointer-events-none');
                        }
                    }, 200);
                }
            }

            const closeOnInactive = () => {
                if (mediaQuery.matches) {
                    sidebar.classList.remove('-translate-x-full');
                    backdrop.classList.add('pointer-events-none');
                    backdrop.classList.add('opacity-0');
                    backdrop.classList.remove('opacity-90');
                }
            };

            mediaQuery.addEventListener('change', closeOnInactive);

            openButton?.addEventListener('click', openSidebar);
            closeButton?.addEventListener('click', closeSidebar);
            backdrop?.addEventListener('click', closeSidebar);

            document.querySelectorAll('#sidebar a').forEach((link) => {
                link.addEventListener('click', () => {
                    if (!mediaQuery.matches) {
                        closeSidebar();
                    }
                });
            });

            closeOnInactive();

            if (window.lucide) {
                window.lucide.createIcons();
            }
        });

        // Sistema de Notificações
        document.addEventListener('DOMContentLoaded', () => {
            const bellBtn = document.getElementById('notification-bell');
            const dropdown = document.getElementById('notification-dropdown');

            if (!bellBtn || !dropdown) return;

            // Toggle dropdown ao clicar no sino
            bellBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                dropdown.classList.toggle('hidden');

                // Recriar ícones do Lucide no dropdown
                if (!dropdown.classList.contains('hidden') && window.lucide) {
                    window.lucide.createIcons();
                }
            });

            // Fechar ao clicar fora
            document.addEventListener('click', (e) => {
                if (!dropdown.classList.contains('hidden') &&
                    !dropdown.contains(e.target) &&
                    !bellBtn.contains(e.target)) {
                    dropdown.classList.add('hidden');
                }
            });

            // Prevenir fechamento ao clicar dentro do dropdown
            dropdown.addEventListener('click', (e) => {
                e.stopPropagation();
            });
        });
    </script>

    @include('components.pwa-install-banner')

    {{-- Banner de Conexão WhatsApp --}}
    @php
        // Verificar se há alguma instância WhatsApp conectada
        try {
            $hasWhatsAppConnected = \App\Models\WhatsappInstance::where('client_id', currentClientId())
                ->where('status', 'connected')
                ->exists();
        } catch (\Exception $e) {
            $hasWhatsAppConnected = false;
        }
    @endphp
    @include('components.whatsapp-connect-banner', ['whatsappConnected' => $hasWhatsAppConnected])

    @stack('scripts')
</body>

</html>