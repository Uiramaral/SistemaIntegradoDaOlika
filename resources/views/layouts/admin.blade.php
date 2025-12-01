<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Olika Admin')</title>

    @php
        $cssVersion = env('APP_ASSETS_VERSION', '4.0');
    @endphp
    
    <!-- =======================
         OLIKA Dashboard - Lovable Design System v4.0
         ======================= -->
    
    {{-- Google Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
    
    {{-- Lucide Icons (Photo-Zen uses Lucide) --}}
    <script src="https://unpkg.com/lucide@latest"></script>
    
    {{-- OLIKA Design System - Pixel Perfect Photo-Zen --}}
    <link rel="stylesheet" href="{{ asset('css/olika-design-system.css') }}">
    <link rel="stylesheet" href="{{ asset('css/olika-dashboard-pixel-perfect.css') }}">
    <link rel="stylesheet" href="{{ asset('css/olika-header-fix.css') }}">
    
    {{-- CSS específico da página (ANTES do override) --}}
    @stack('styles')
    
    {{-- Override FINAL - deve ser o último para garantir prioridade --}}
    {{-- <link rel="stylesheet" href="{{ asset('css/olika-override-pixel-perfect.css') }}">--}}
</head>
<body class="fade-in">
    @php
        $navGroups = [
            [
                'title' => 'Menu Principal',
                'items' => [
                    ['label' => 'Visão Geral', 'icon' => 'layout-dashboard', 'route' => 'dashboard.index', 'routePattern' => 'dashboard.index'],
                    ['label' => 'PDV', 'icon' => 'shopping-cart', 'route' => 'dashboard.pdv.index', 'routePattern' => 'dashboard.pdv.*'],
                    ['label' => 'Pedidos', 'icon' => 'package', 'route' => 'dashboard.orders.index', 'routePattern' => 'dashboard.orders.*'],
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
                    ['label' => 'Cashback', 'icon' => 'gift-box', 'route' => 'dashboard.cashback.index', 'routePattern' => 'dashboard.cashback.*'],
                ],
            ],
            [
                'title' => 'Integrações',
                'items' => [
                    ['label' => 'WhatsApp', 'icon' => 'message-square', 'route' => 'dashboard.settings.whatsapp', 'routePattern' => 'dashboard.settings.whatsapp*'],
                    ['label' => 'Mercado Pago', 'icon' => 'credit-card', 'route' => 'dashboard.settings.mp', 'routePattern' => 'dashboard.settings.mp*'],
                ],
            ],
            [
                'title' => 'Sistema',
                'items' => [
                    ['label' => 'Relatórios', 'icon' => 'chart-column', 'route' => 'dashboard.reports', 'routePattern' => 'dashboard.reports*'],
                    ['label' => 'Configurações', 'icon' => 'settings', 'route' => 'dashboard.settings', 'routePattern' => 'dashboard.settings'],
                ],
            ],
        ];
    @endphp

    <div class="min-h-screen w-full bg-background flex">
        <div class="flex min-h-screen w-full flex-1">
            <div id="sidebar-backdrop" class="fixed inset-0 z-30 bg-black/80 opacity-0 pointer-events-none transition-opacity duration-200 md:hidden"></div>

            <aside id="sidebar"
                   class="fixed inset-y-0 left-0 z-40 flex w-64 -translate-x-full flex-col border-r border-sidebar-border bg-sidebar text-sidebar-foreground shadow-[0_0_0_1px_hsl(var(--sidebar-border))] transition-transform duration-200 ease-in-out md:static md:translate-x-0 md:w-64">
                <div class="flex items-center justify-between border-b border-sidebar-border px-6 py-4">
                    <div class="flex items-center gap-3">
                        <span class="text-xl font-bold text-sidebar-primary tracking-tight">OLIKA</span>
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
                            <p class="flex h-8 items-center rounded-md px-2 text-xs font-medium uppercase tracking-widest text-sidebar-foreground/70">
                                {{ $group['title'] }}
                            </p>
                            <ul class="mt-2 space-y-1">
                                @foreach ($group['items'] as $item)
                                    @php
                                        $href = route($item['route']);
                                        $isActive = request()->routeIs($item['routePattern']);
                                    @endphp
                                    <li>
                                        <a href="{{ $href }}"
                                           class="flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium transition-colors hover:bg-sidebar-accent hover:text-sidebar-accent-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sidebar-ring {{ $isActive ? 'bg-sidebar-accent text-sidebar-accent-foreground shadow-[0_0_0_1px_hsl(var(--sidebar-accent))]' : 'text-sidebar-foreground' }}">
                                            <i data-lucide="{{ $item['icon'] }}" class="h-5 w-5"></i>
                                            <span class="truncate">{{ $item['label'] }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </nav>

                <div class="border-t border-sidebar-border px-4 py-4">
                    <form method="POST" action="{{ route('auth.logout') }}">
                        @csrf
                        <button type="submit"
                                class="flex w-full items-center gap-3 rounded-md px-3 py-2 text-sm font-medium text-sidebar-foreground transition-colors hover:bg-destructive/20 hover:text-destructive focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sidebar-ring">
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
                    <div class="flex h-14 items-center justify-between px-4 md:px-6">
                        <div class="flex items-center gap-3">
                            <button id="sidebar-open"
                                    class="inline-flex h-10 w-10 items-center justify-center rounded-md border border-border bg-background text-foreground shadow-sm transition hover:bg-muted focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring md:hidden">
                                <i data-lucide="menu" class="h-5 w-5"></i>
                                <span class="sr-only">Abrir menu</span>
                            </button>

                            {{-- Photo-Zen: Header sempre mostra "Dashboard" --}}
                            <h1 class="text-lg font-semibold tracking-tight text-foreground" style="font-size: 1.125rem !important; font-weight: 600 !important; color: hsl(222, 47%, 11%) !important;">
                                Dashboard
                            </h1>
                        </div>

                        <div class="flex items-center gap-3">
                            @if ($pageActionsSection)
                                <div class="flex items-center gap-2">
                                    @yield($pageActionsSection)
                                </div>
                            @endif

                            {{-- Photo-Zen: Botão Baixar Design --}}
                            <button class="inline-flex items-center gap-2 px-3 py-2 rounded-md border border-border bg-background text-foreground text-sm font-medium transition hover:bg-muted focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring">
                                <i data-lucide="download" class="h-4 w-4"></i>
                                <span>Baixar Design</span>
                            </button>

                            <button class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-border text-muted-foreground transition hover:text-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring">
                                <i data-lucide="user" class="h-5 w-5"></i>
                                <span class="sr-only">Perfil</span>
                            </button>
                        </div>
                    </div>
                </header>

                <main class="flex-1 bg-background overflow-y-auto">
                    <div class="max-w-screen-2xl mx-auto p-6 space-y-6">
                        {{-- Page Title and Subtitle (Photo-Zen style) --}}
                        @if ($pageTitle || $pageSubtitle)
                            <div class="space-y-1 mb-6">
                                @if ($pageTitle)
                                    <h1 class="text-2xl font-semibold tracking-tight text-foreground" style="font-size: 1.5rem !important; font-weight: 600 !important; color: hsl(222, 47%, 11%) !important; margin: 0 !important; margin-bottom: 0.25rem !important;">
                                        {{ $pageTitle }}
                                    </h1>
                                @endif
                                @if ($pageSubtitle)
                                    <p class="text-sm text-muted-foreground" style="font-size: 0.875rem !important; color: hsl(220, 9%, 46%) !important; margin: 0 !important; margin-top: 0.25rem !important;">
                                        {{ $pageSubtitle }}
                                    </p>
                                @endif
                            </div>
                        @endif
                        
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
                            <div class="grid gap-4 grid-cols-2 md:grid-cols-4">
                                @yield($statCardsSection)
                            </div>
                        @endif

                        @if ($quickFiltersSection)
                            <div class="rounded-lg border border-border bg-card px-4 py-3 shadow-sm">
                                @yield($quickFiltersSection)
                            </div>
                        @endif

                        @yield('content')
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
    
    {{-- OLIKA Utilities --}}
    <script src="{{ asset('js/olika-utilities.js') }}"></script>
    <script src="{{ asset('js/sidebar-toggle.js') }}"></script>
    
    {{-- Initialize Lucide Icons --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>
    
    {{-- Estilos críticos movidos para olika-override-v3.1.css --}}
</body>
</html>