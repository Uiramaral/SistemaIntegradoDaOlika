<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Olika Admin')</title>

    @php
        // Carregar configurações de tema do estabelecimento (SaaS)
        $clientSettings = \App\Models\Setting::getSettings();
        $themeSettings = $clientSettings->getThemeSettings();
        
        // Helper para converter HEX para HSL
        function hexToHsl($hex) {
            $hex = str_replace('#', '', $hex);
            if (strlen($hex) === 3) {
                $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
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
        }
        
        $primaryHsl = hexToHsl($themeSettings['theme_primary_color']);
        $secondaryHsl = hexToHsl($themeSettings['theme_secondary_color']);
        $accentHsl = hexToHsl($themeSettings['theme_accent_color']);
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
                            DEFAULT: "hsl(0 0% 100%)",
                            foreground: "hsl(222 47% 11%)",
                            primary: "hsl({{ $primaryHsl }})",
                            "primary-foreground": "hsl(0 0% 100%)",
                            accent: "hsl(0 0% 96%)",
                            "accent-foreground": "hsl(222 47% 11%)",
                            border: "hsl(0 0% 89%)",
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
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="{{ asset('css/admin-bridge.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sweetspot-theme.css') }}">

    <script defer src="https://unpkg.com/lucide@latest"></script>

    @stack('styles')
</head>
<body class="bg-background text-foreground antialiased">
    @php
        $navGroups = [
            [
                'title' => 'Menu Principal',
                'items' => [
                    ['label' => 'Visão Geral', 'icon' => 'layout-dashboard', 'route' => 'dashboard.index', 'routePattern' => 'dashboard.index'],
                    ['label' => 'PDV', 'icon' => 'monitor', 'route' => 'dashboard.pdv.index', 'routePattern' => 'dashboard.pdv.*'],
                    ['label' => 'Pedidos', 'icon' => 'receipt', 'route' => 'dashboard.orders.index', 'routePattern' => 'dashboard.orders.*'],
                    ['label' => 'Clientes', 'icon' => 'users', 'route' => 'dashboard.customers.index', 'routePattern' => 'dashboard.customers.*'],
                    ['label' => 'Entregas', 'icon' => 'truck', 'route' => 'dashboard.deliveries.index', 'routePattern' => 'dashboard.deliveries.*'],
                ],
            ],
            [
                'title' => 'Produtos',
                'items' => [
                    ['label' => 'Produtos', 'icon' => 'package', 'route' => 'dashboard.products.index', 'routePattern' => 'dashboard.products.*'],
                    ['label' => 'Categorias', 'icon' => 'tag', 'route' => 'dashboard.categories.index', 'routePattern' => 'dashboard.categories.*'],
                    ['label' => 'Preços de Revenda', 'icon' => 'shopping-bag', 'route' => 'dashboard.wholesale-prices.index', 'routePattern' => 'dashboard.wholesale-prices.*'],
                ],
            ],
            [
                'title' => 'Marketing',
                'items' => [
                    ['label' => 'Cupons', 'icon' => 'percent', 'route' => 'dashboard.coupons.index', 'routePattern' => 'dashboard.coupons.*'],
                    ['label' => 'Cashback', 'icon' => 'gift', 'route' => 'dashboard.cashback.index', 'routePattern' => 'dashboard.cashback.*'],
                ],
            ],
            [
                'title' => 'Integrações',
                'items' => [
                    ['label' => 'WhatsApp', 'icon' => 'message-square', 'route' => 'dashboard.settings.whatsapp', 'routePattern' => 'dashboard.settings.whatsapp*', 'feature' => 'whatsapp'],
                    ['label' => 'Mercado Pago', 'icon' => 'credit-card', 'route' => 'dashboard.settings.mp', 'routePattern' => 'dashboard.settings.mp*'],
                ],
            ],
            [
                'title' => 'Sistema',
                'items' => [
                    ['label' => 'Personalização', 'icon' => 'palette', 'route' => 'dashboard.themes.index', 'routePattern' => 'dashboard.themes.*'],
                    ['label' => 'Campanhas', 'icon' => 'megaphone', 'route' => 'dashboard.marketing.index', 'routePattern' => 'dashboard.marketing.*'],
                    ['label' => 'Integrações', 'icon' => 'plug', 'route' => 'dashboard.integrations.index', 'routePattern' => 'dashboard.integrations.*'],
                    ['label' => 'Plano e Assinatura', 'icon' => 'crown', 'route' => 'dashboard.subscription.index', 'routePattern' => 'dashboard.subscription.*'],
                    ['label' => 'Relatórios', 'icon' => 'chart-column', 'route' => 'dashboard.reports', 'routePattern' => 'dashboard.reports*'],
                    ['label' => 'Configurações', 'icon' => 'settings', 'route' => 'dashboard.settings', 'routePattern' => 'dashboard.settings'],
                ],
            ],
        ];
        
        // Add Master menu for super admins only
        // Condições: role='super_admin' OU client_id=1 (Olika) OU client_id=NULL
        $user = auth()->user();
        $isSuperAdmin = false;
        if (auth()->check() && $user) {
            // Usar método do model se existir
            if (method_exists($user, 'isSuperAdmin')) {
                $isSuperAdmin = $user->isSuperAdmin();
            } else {
                // Fallback: client_id = 1 ou null
                $isSuperAdmin = ($user->client_id === 1 || $user->client_id === null);
            }
        }
        if ($isSuperAdmin) {
            $navGroups[] = [
                'title' => 'Master (Admin)',
                'items' => [
                    ['label' => 'Dashboard Master', 'icon' => 'shield', 'route' => 'master.dashboard', 'routePattern' => 'master.dashboard'],
                    ['label' => 'Clientes/Estab.', 'icon' => 'building-2', 'route' => 'master.clients.index', 'routePattern' => 'master.clients.*'],
                    ['label' => 'Planos', 'icon' => 'crown', 'route' => 'master.plans.index', 'routePattern' => 'master.plans.*'],
                    ['label' => 'WhatsApp URLs', 'icon' => 'server', 'route' => 'master.whatsapp-urls.index', 'routePattern' => 'master.whatsapp-urls.*'],
                    ['label' => 'Config. Master', 'icon' => 'sliders', 'route' => 'master.settings.index', 'routePattern' => 'master.settings.*'],
                ],
            ];
        }
    @endphp

    <div class="min-h-screen w-full bg-background">
        <div class="flex min-h-screen w-full">
            <div id="sidebar-backdrop" class="fixed inset-0 z-30 bg-black/80 opacity-0 pointer-events-none transition-opacity duration-200 md:hidden"></div>

            <aside id="sidebar"
                   class="fixed inset-y-0 left-0 z-40 flex w-64 -translate-x-full flex-col border-r border-sidebar-border bg-sidebar text-sidebar-foreground shadow-sweetspot transition-transform duration-200 ease-in-out md:static md:translate-x-0">
                <div class="flex items-center justify-between border-b border-sidebar-border px-6 py-5">
                    <div class="flex items-center gap-3">
                        @if($themeSettings['theme_logo_url'] && $themeSettings['theme_logo_url'] !== '/images/logo-default.png')
                            <img src="{{ $themeSettings['theme_logo_url'] }}" alt="Logo" class="h-10 w-10 object-contain rounded-full">
                        @else
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-primary-500 to-primary-600 text-white font-bold text-sm shadow-sweetspot">
                                {{ strtoupper(substr($themeSettings['theme_brand_name'], 0, 2)) }}
                            </div>
                        @endif
                        <span class="text-xl font-bold text-sidebar-primary tracking-tight">{{ $themeSettings['theme_brand_name'] }}</span>
                    </div>
                    <button id="sidebar-close"
                            class="flex h-8 w-8 items-center justify-center rounded-md text-sidebar-foreground hover:bg-sidebar-accent hover:text-sidebar-accent-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sidebar-ring md:hidden">
                        <i data-lucide="x" class="h-5 w-5"></i>
                        <span class="sr-only">Fechar menu</span>
                    </button>
                </div>

                <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-6">
                    @foreach ($navGroups as $group)
                        <div>
                            <p class="flex h-8 items-center rounded-md px-3 text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">
                                {{ $group['title'] }}
                            </p>
                            <ul class="mt-2 space-y-1">
                                @foreach ($group['items'] as $item)
                                    @php
                                        $isAvailable = !isset($item['feature']) || currentClientHasFeature($item['feature']);
                                        $href = $isAvailable ? route($item['route']) : route('dashboard.subscription.index');
                                        $isActive = request()->routeIs($item['routePattern']);
                                    @endphp
                                    <li>
                                        <a href="{{ $href }}"
                                           class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-200 hover:bg-sidebar-accent hover:text-sidebar-accent-foreground hover:shadow-sweetspot focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sidebar-ring {{ $isActive ? 'bg-gradient-to-r from-primary-50 to-primary-100 text-primary-700 font-semibold shadow-sweetspot border-l-4 border-primary-500' : 'text-sidebar-foreground' }} {{ !$isAvailable ? 'opacity-50' : '' }}">
                                            <i data-lucide="{{ $item['icon'] }}" class="h-5 w-5 {{ $isActive ? 'text-primary-600' : '' }}"></i>
                                            <span class="truncate">{{ $item['label'] }}</span>
                                            @if(!$isAvailable)
                                                <i data-lucide="lock" class="ml-auto h-4 w-4"></i>
                                            @endif
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </nav>

                <div class="border-t border-sidebar-border px-4 py-4">
                    <div class="mb-3 px-3 py-2 rounded-lg bg-sidebar-accent/50">
                        <p class="text-xs font-medium text-sidebar-foreground">{{ Auth::user()->name }}</p>
                        <p class="text-[10px] text-muted-foreground truncate">{{ Auth::user()->email }}</p>
                    </div>
                    <form method="POST" action="{{ route('auth.logout') }}">
                        @csrf
                        <button type="submit"
                                class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-sidebar-foreground transition-all duration-200 hover:bg-destructive/10 hover:text-destructive focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-destructive">
                            <i data-lucide="log-out" class="h-5 w-5"></i>
                            <span>Sair</span>
                        </button>
                    </form>
                </div>
            </aside>

            <div class="flex flex-1 flex-col">
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

                <header class="sticky top-0 z-20 border-b border-border bg-card/95 backdrop-blur supports-[backdrop-filter]:bg-card/60">
                    <div class="flex flex-col sm:flex-row sm:h-16 sm:items-center sm:justify-between px-4 md:px-6 py-3 sm:py-0 gap-2 sm:gap-0">
                        <div class="flex items-center gap-3">
                            <button id="sidebar-open"
                                    class="inline-flex h-10 w-10 items-center justify-center rounded-md border border-border bg-background text-foreground shadow-sm transition hover:bg-muted focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring md:hidden">
                                <i data-lucide="menu" class="h-5 w-5"></i>
                                <span class="sr-only">Abrir menu</span>
                            </button>

                            @if ($hasPageHeader)
                                <div class="flex flex-col gap-0.5">
                                    @yield('page_header')
                                </div>
                            @else
                                <div class="flex flex-col gap-0.5">
                                    <h1 class="text-base sm:text-lg font-semibold tracking-tight text-foreground md:text-2xl">
                                        {{ $pageTitle ?? trim($__env->yieldContent('title', 'Dashboard')) }}
                                    </h1>
                                    @if ($pageSubtitle)
                                        <p class="text-xs sm:text-sm text-muted-foreground hidden sm:block">{{ $pageSubtitle }}</p>
                                    @elseif ($pageDescriptionSection)
                                        <div class="text-xs sm:text-sm text-muted-foreground hidden sm:block">
                                            @yield($pageDescriptionSection)
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <div class="flex items-center gap-2 sm:gap-3">
                            @if ($pageActionsSection)
                                <div class="flex items-center gap-2 flex-1 sm:flex-none">
                                    @yield($pageActionsSection)
                                </div>
                            @endif

                            <div class="hidden flex-col items-end text-sm md:flex">
                                <span class="font-medium text-foreground">{{ Auth::user()->name ?? 'Admin' }}</span>
                                <span class="text-xs text-muted-foreground">{{ Auth::user()->email ?? 'admin@olika.com' }}</span>
                            </div>
                            <button class="hidden sm:inline-flex h-10 w-10 items-center justify-center rounded-full border border-border text-muted-foreground transition hover:text-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring">
                                <i data-lucide="bell" class="h-5 w-5"></i>
                                <span class="sr-only">Notificações</span>
                            </button>
                        </div>
                    </div>
                </header>

                <main class="flex-1" id="main-content">
                    <div class="dashboard-wrapper">
                        @if(session('success'))
                            <div class="rounded-lg border border-success/30 bg-success/10 px-4 py-3 text-success shadow-sm">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="rounded-lg border border-destructive/30 bg-destructive/10 px-4 py-3 text-destructive shadow-sm">
                                {{ session('error') }}
                            </div>
                        @endif

                        @if($errors->any())
                            <div class="rounded-lg border border-destructive/30 bg-destructive/10 px-4 py-3 text-destructive shadow-sm">
                                <ul class="list-disc space-y-1 pl-5 text-sm">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if ($pageToolbarSection)
                            <div class="flex flex-wrap items-center gap-3 rounded-lg border border-border bg-card px-4 py-3 shadow-sm">
                                @yield($pageToolbarSection)
                            </div>
                        @endif

                        @if ($statCardsSection)
                            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                                @yield($statCardsSection)
                            </div>
                        @endif

                        @if ($quickFiltersSection)
                            <div class="rounded-lg border border-border bg-card px-4 py-3 shadow-sm">
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
        window.applyTableMobileLabels = function(root = document) {
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
    </script>
    
    @stack('scripts')
</body>
</html>