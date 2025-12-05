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
    
    {{-- Photo-Zen Dashboard CSS - Pixel Perfect Replica --}}
    <link rel="stylesheet" href="{{ asset('css/photo-zen-dashboard.css') }}">
    
    {{-- Tailwind CSS via CDN (temporário até configurar build) --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        background: 'hsl(var(--background))',
                        foreground: 'hsl(var(--foreground))',
                        card: {
                            DEFAULT: 'hsl(var(--card))',
                            foreground: 'hsl(var(--card-foreground))',
                        },
                        primary: {
                            DEFAULT: 'hsl(var(--primary))',
                            foreground: 'hsl(var(--primary-foreground))',
                        },
                        sidebar: {
                            DEFAULT: 'hsl(var(--sidebar-background))',
                            foreground: 'hsl(var(--sidebar-foreground))',
                            primary: 'hsl(var(--sidebar-primary))',
                            'primary-foreground': 'hsl(var(--sidebar-primary-foreground))',
                            accent: 'hsl(var(--sidebar-accent))',
                            'accent-foreground': 'hsl(var(--sidebar-accent-foreground))',
                            border: 'hsl(var(--sidebar-border))',
                        },
                        muted: {
                            DEFAULT: 'hsl(var(--muted))',
                            foreground: 'hsl(var(--muted-foreground))',
                        },
                        border: 'hsl(var(--border))',
                    },
                    borderRadius: {
                        lg: 'var(--radius)',
                        md: 'calc(var(--radius) - 2px)',
                        sm: 'calc(var(--radius) - 4px)',
                    },
                },
            },
        }
    </script>
    
    {{-- CSS específico da página --}}
    @stack('styles')
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
                    ['label' => 'Mensagens Falhadas', 'icon' => 'alert-circle', 'route' => 'dashboard.whatsapp.failed-messages', 'routePattern' => 'dashboard.whatsapp.failed-messages*'],
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
                        <span class="text-xl font-bold text-sidebar-primary tracking-tight" style="color: hsl(var(--sidebar-primary));">OLIKA</span>
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
                            <p class="flex h-8 items-center rounded-md px-2 text-xs font-medium uppercase tracking-widest" style="color: hsl(var(--sidebar-foreground) / 0.7);">
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
                                           class="flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium transition-colors hover:bg-sidebar-accent hover:text-sidebar-accent-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sidebar-ring {{ $isActive ? 'bg-sidebar-primary text-sidebar-primary-foreground shadow-[0_0_0_1px_hsl(var(--sidebar-primary))]' : 'text-sidebar-foreground' }}"
                                           style="{{ $isActive ? 'background-color: hsl(var(--sidebar-primary)); color: hsl(var(--sidebar-primary-foreground));' : 'color: hsl(var(--sidebar-foreground));' }}">
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

            <div class="flex flex-1 flex-col min-w-0 overflow-x-hidden">
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

                <header class="sticky top-0 z-20 border-b border-border bg-card/95 backdrop-blur supports-[backdrop-filter]:bg-card/60" style="background-color: hsl(var(--card)); border-color: hsl(var(--border));">
                    <div class="flex h-auto min-h-14 items-center justify-between px-4 md:px-6 py-2 gap-3 min-w-0">
                        <div class="flex items-center gap-3 min-w-0 flex-shrink-0">
                            <button id="sidebar-open"
                                    class="inline-flex h-10 w-10 items-center justify-center rounded-md border border-border bg-background text-foreground shadow-sm transition hover:bg-muted focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring md:hidden flex-shrink-0">
                                <i data-lucide="menu" class="h-5 w-5"></i>
                                <span class="sr-only">Abrir menu</span>
                            </button>

                            {{-- Photo-Zen: Header mostra o nome da página --}}
                            <h1 class="text-lg font-semibold tracking-tight truncate" style="font-size: 1.125rem; font-weight: 600; color: hsl(var(--foreground));">
                                {{ $pageTitle ?: 'Dashboard' }}
                            </h1>
                        </div>

                        <div class="flex items-center gap-2 flex-wrap min-w-0 flex-shrink">
                            @if ($pageActionsSection)
                                <div class="flex items-center gap-2 flex-wrap min-w-0">
                                    @yield($pageActionsSection)
                                </div>
                            @endif

                            <button class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-border text-muted-foreground transition hover:text-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring flex-shrink-0" style="border-color: hsl(var(--border)); color: hsl(var(--muted-foreground));">
                                <i data-lucide="user" class="h-5 w-5"></i>
                                <span class="sr-only">Perfil</span>
                            </button>
                        </div>
                    </div>
                </header>

                <main class="flex-1 bg-background overflow-y-auto overflow-x-hidden" style="background-color: hsl(var(--background));">
                    <div class="max-w-screen-2xl mx-auto p-6 space-y-6 w-full min-w-0">
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
    
    {{-- Sistema de Verificação de Mensagens WhatsApp Falhadas --}}
    <script>
        (function() {
            'use strict';
            
            let lastCheckedCount = 0;
            let checkInterval = null;
            const CHECK_INTERVAL = 30000; // Verificar a cada 30 segundos
            
            async function checkFailedMessages() {
                try {
                    const response = await fetch('/dashboard/whatsapp/failed-messages/pending-count', {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    
                    if (!response.ok) return;
                    
                    const data = await response.json();
                    const currentCount = data.count || 0;
                    
                    // Se houver novas falhas, mostrar popup
                    if (currentCount > 0 && currentCount > lastCheckedCount) {
                        showFailedMessagesPopup(currentCount);
                    }
                    
                    lastCheckedCount = currentCount;
                } catch (error) {
                    console.error('Erro ao verificar mensagens falhadas:', error);
                }
            }
            
            function showFailedMessagesPopup(count) {
                // Verificar se já existe um popup
                if (document.getElementById('whatsapp-failed-popup')) {
                    return;
                }
                
                const popup = document.createElement('div');
                popup.id = 'whatsapp-failed-popup';
                popup.className = 'fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm';
                popup.innerHTML = `
                    <div class="bg-card rounded-lg shadow-xl w-full max-w-md mx-4 border border-destructive/20">
                        <div class="p-6">
                            <div class="flex items-start gap-4">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 rounded-full bg-destructive/10 flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-alert-circle text-destructive">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <line x1="12" x2="12" y1="8" y2="12"></line>
                                            <line x1="12" x2="12.01" y1="16" y2="16"></line>
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold mb-2">Mensagens WhatsApp Falhadas</h3>
                                    <p class="text-sm text-muted-foreground mb-4">
                                        ${count === 1 ? 'Foi detectada 1 mensagem' : `Foram detectadas ${count} mensagens`} que não foram enviadas com sucesso.
                                    </p>
                                    <div class="flex gap-2">
                                        <button onclick="window.location.href='/dashboard/whatsapp/failed-messages'" class="flex-1 inline-flex items-center justify-center gap-2 rounded-md text-sm font-medium bg-primary text-primary-foreground hover:bg-primary/90 h-9 px-4">
                                            Ver Mensagens
                                        </button>
                                        <button onclick="closeFailedMessagesPopup()" class="inline-flex items-center justify-center rounded-md text-sm font-medium border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 px-4">
                                            Fechar
                                        </button>
                                    </div>
                                </div>
                                <button onclick="closeFailedMessagesPopup()" class="text-muted-foreground hover:text-foreground">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x">
                                        <path d="M18 6 6 18"></path>
                                        <path d="M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                
                document.body.appendChild(popup);
                
                // Fechar ao clicar fora
                popup.addEventListener('click', function(e) {
                    if (e.target === popup) {
                        closeFailedMessagesPopup();
                    }
                });
            }
            
            function closeFailedMessagesPopup() {
                const popup = document.getElementById('whatsapp-failed-popup');
                if (popup) {
                    popup.style.transition = 'opacity 0.3s';
                    popup.style.opacity = '0';
                    setTimeout(() => popup.remove(), 300);
                }
            }
            
            // Expor função globalmente
            window.closeFailedMessagesPopup = closeFailedMessagesPopup;
            
            // Iniciar verificação quando a página carregar
            function startChecking() {
                // Verificar imediatamente após 5 segundos
                setTimeout(checkFailedMessages, 5000);
                
                // Depois verificar periodicamente
                checkInterval = setInterval(checkFailedMessages, CHECK_INTERVAL);
            }
            
            // Parar verificação quando a página perder foco
            document.addEventListener('visibilitychange', function() {
                if (document.hidden) {
                    if (checkInterval) {
                        clearInterval(checkInterval);
                        checkInterval = null;
                    }
                } else {
                    if (!checkInterval) {
                        startChecking();
                    }
                }
            });
            
            // Iniciar quando DOM estiver pronto
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', startChecking);
            } else {
                startChecking();
            }
        })();
    </script>
    
    {{-- Estilos críticos movidos para olika-override-v3.1.css --}}
</body>
</html>