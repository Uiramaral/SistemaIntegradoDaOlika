<!DOCTYPE html>
<html lang="en" class="overflow-x-hidden">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'OLIKA Painel - Sistema de Gestão de Restaurante')</title>
    <meta name="description" content="Painel completo para gestão de restaurante com pedidos, produtos e relatórios em tempo real">
    <meta name="author" content="OLIKA">
    <meta property="og:title" content="OLIKA Painel">
    <meta property="og:description" content="Sistema de gestão de restaurante">
    <meta property="og:type" content="website">

    @php
        // Carregar configurações de tema do estabelecimento (SaaS)
        $clientSettings = \App\Models\Setting::getSettings();
        $themeSettings = $clientSettings->getThemeSettings();
        
        // Helper para converter HEX para HSL
        if (!function_exists('hexToHsl')) {
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
        }
        
        $primaryHsl = hexToHsl($themeSettings['theme_primary_color']);
        $secondaryHsl = hexToHsl($themeSettings['theme_secondary_color']);
        $accentHsl = hexToHsl($themeSettings['theme_accent_color']);
    @endphp

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/lucide@latest"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        border: "hsl(var(--border))",
                        input: "hsl(var(--input))",
                        ring: "hsl(var(--ring))",
                        background: "hsl(var(--background))",
                        foreground: "hsl(var(--foreground))",
                        primary: { DEFAULT: "hsl(var(--primary))", foreground: "hsl(var(--primary-foreground))" },
                        secondary: { DEFAULT: "hsl(var(--secondary))", foreground: "hsl(var(--secondary-foreground))" },
                        destructive: { DEFAULT: "hsl(var(--destructive))", foreground: "hsl(var(--destructive-foreground))" },
                        muted: { DEFAULT: "hsl(var(--muted))", foreground: "hsl(var(--muted-foreground))" },
                        accent: { DEFAULT: "hsl(var(--accent))", foreground: "hsl(var(--accent-foreground))" },
                        popover: { DEFAULT: "hsl(var(--popover))", foreground: "hsl(var(--popover-foreground))" },
                        card: { DEFAULT: "hsl(var(--card))", foreground: "hsl(var(--card-foreground))" },
                        sidebar: {
                            DEFAULT: "hsl(var(--sidebar-background))",
                            foreground: "hsl(var(--sidebar-foreground))",
                            primary: "hsl(var(--sidebar-primary))",
                            "primary-foreground": "hsl(var(--sidebar-primary-foreground))",
                            accent: "hsl(var(--sidebar-accent))",
                            "accent-foreground": "hsl(var(--sidebar-accent-foreground))",
                            border: "hsl(var(--sidebar-border))",
                            ring: "hsl(var(--sidebar-ring))",
                        },
                        success: {
                            DEFAULT: "hsl(var(--success))",
                            foreground: "hsl(var(--success-foreground))",
                        },
                        warning: {
                            DEFAULT: "hsl(var(--warning))",
                            foreground: "hsl(var(--warning-foreground))",
                        },
                    },
                    borderRadius: { lg: "var(--radius)", md: "calc(var(--radius) - 2px)", sm: "calc(var(--radius) - 4px)" },
                },
            },
        }
        
        document.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) {
                window.lucide.createIcons();
            }
        });
    </script>
    <style>
        /* Prevenir scroll horizontal no mobile - Global */
        html, body {
            overflow-x: hidden;
            max-width: 100%;
            position: relative;
        }
        
        /* Garantir que todos os containers respeitem o viewport */
        * {
            box-sizing: border-box;
        }
        
        /* Prevenir elementos que possam causar overflow */
        img, video, iframe, table, pre, code {
            max-width: 100%;
        }
        
        /* Garantir que tabelas sejam responsivas apenas quando necessário */
        .overflow-x-auto table,
        table.overflow-x-auto {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        /* Em mobile, tabelas grandes podem ter scroll horizontal */
        @media (max-width: 768px) {
            table {
                display: block;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                width: 100%;
            }
        }
        
        /* Containers principais */
        .container, [class*="container"], [class*="max-w"] {
            max-width: 100%;
        }
        
        :root {
            --background: 0 0% 100%;
            --foreground: 222.2 84% 4.9%;
            --card: 0 0% 100%;
            --card-foreground: 222.2 84% 4.9%;
            --popover: 0 0% 100%;
            --popover-foreground: 222.2 84% 4.9%;
            --primary: {{ $primaryHsl }};
            --primary-foreground: 0 0% 100%;
            --secondary: {{ $secondaryHsl }};
            --secondary-foreground: 222.2 84% 4.9%;
            --muted: 210 40% 96%;
            --muted-foreground: 215.4 16.3% 46.9%;
            --accent: {{ $accentHsl }};
            --accent-foreground: 222.2 84% 4.9%;
            --destructive: 0 84.2% 60.2%;
            --destructive-foreground: 210 40% 98%;
            --border: 214.3 31.8% 91.4%;
            --input: 214.3 31.8% 91.4%;
            --ring: {{ $primaryHsl }};
            --radius: {{ $themeSettings['theme_border_radius'] }};
            --font-family: {!! $themeSettings['theme_font_family'] !!};
            /* Sidebar escuro como no site */
            --sidebar-background: 222 47% 11%;
            --sidebar-foreground: 0 0% 98%;
            --sidebar-primary: {{ $primaryHsl }};
            --sidebar-primary-foreground: 0 0% 100%;
            --sidebar-accent: {{ $primaryHsl }};
            --sidebar-accent-foreground: 0 0% 100%;
            --sidebar-border: 217 33% 17%;
            --sidebar-ring: {{ $primaryHsl }};
            --success: 142.1 76.2% 36.3%;
            --success-foreground: 355.7 100% 97.3%;
            --warning: 38.7 92% 50%;
            --warning-foreground: 26 83.3% 14.1%;
            /* Cores dos ícones dos cards */
            --metric-green: 142 76% 36%;
            --metric-blue: 217 91% 60%;
            --metric-purple: 262 83% 58%;
        }

        body {
            font-family: var(--font-family);
            background-color: hsl(var(--background));
            color: hsl(var(--foreground));
        }
        * { border-color: hsl(var(--border)); }
        
        /* Padronização de espaçamentos */
        .dashboard-content-wrapper {
            max-width: 1280px;
            margin: 0 auto;
            width: 100%;
        }
        
        /* Tabelas responsivas */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            width: 100%;
        }
        
        .table-responsive table {
            width: 100%;
            min-width: 640px;
        }
        
        @media (max-width: 768px) {
            .table-responsive {
                display: block;
            }
            
            .table-responsive table {
                display: block;
                width: 100%;
            }
            
            .table-responsive thead {
                display: none;
            }
            
            .table-responsive tbody {
                display: block;
            }
            
            .table-responsive tr {
                display: block;
                margin-bottom: 1rem;
                border: 1px solid hsl(var(--border));
                border-radius: 8px;
                padding: 1rem;
                background: hsl(var(--card));
            }
            
            .table-responsive td {
                display: block;
                text-align: right;
                padding: 0.5rem 0;
                border: none;
                border-bottom: 1px solid hsl(var(--border) / 0.5);
            }
            
            .table-responsive td:last-child {
                border-bottom: none;
            }
            
            .table-responsive td::before {
                content: attr(data-label);
                float: left;
                font-weight: 600;
                color: hsl(var(--muted-foreground));
            }
        }
        
        /* Cards padronizados */
        .card-standard {
            background: hsl(var(--card));
            border: 1px solid hsl(var(--border));
            border-radius: calc(var(--radius) + 2px);
            padding: 1.5rem;
            box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1);
        }
        
        /* Espaçamento consistente entre seções */
        .section-spacing {
            margin-bottom: 1.5rem;
        }
        
        @media (min-width: 768px) {
            .section-spacing {
                margin-bottom: 2rem;
            }
        }
        
        /* Botões padronizados */
        .btn-primary {
            background: hsl(var(--primary));
            color: hsl(var(--primary-foreground));
            padding: 0.5rem 1rem;
            border-radius: calc(var(--radius) - 2px);
            font-weight: 500;
            transition: all 0.2s;
        }
        
        .btn-primary:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }
        
        .btn-secondary {
            background: hsl(var(--secondary));
            color: hsl(var(--secondary-foreground));
            padding: 0.5rem 1rem;
            border-radius: calc(var(--radius) - 2px);
            font-weight: 500;
            transition: all 0.2s;
        }
        
        .btn-secondary:hover {
            opacity: 0.9;
        }
    </style>
    <link rel="stylesheet" href="{{ asset('css/admin-bridge.css') }}">
    <link rel="stylesheet" href="{{ asset('css/lovable-global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sidebar-sweetspot-pixel-perfect.css') }}">
    @stack('styles')
</head>
<body>
    <div id="root">
        <div role="region" aria-label="Notifications (F8)" tabindex="-1" style="pointer-events: none;">
            <ol tabindex="-1" class="fixed top-0 z-[100] flex max-h-screen w-full flex-col-reverse p-4 sm:bottom-0 sm:right-0 sm:top-auto sm:flex-col md:max-w-[420px]"></ol>
        </div>
        <section aria-label="Notifications alt+T" tabindex="-1" aria-live="polite" aria-relevant="additions text" aria-atomic="false"></section>
        <div class="group/sidebar-wrapper flex min-h-svh w-full has-[[data-variant=inset]]:bg-sidebar" style="--sidebar-width: 16rem; --sidebar-width-icon: 3rem;">
            <!-- Overlay para mobile -->
            <div id="sidebar-overlay" class="fixed inset-0 bg-black/50 z-40 hidden transition-opacity duration-200" onclick="toggleSidebar()"></div>
            
            <div class="flex min-h-screen w-full max-w-full overflow-x-hidden bg-background">
                <div class="group peer hidden text-sidebar-foreground md:block" data-state="expanded" data-collapsible="" data-variant="sidebar" data-side="left" id="desktop-sidebar">
                    <div class="relative h-svh w-[--sidebar-width] bg-transparent transition-[width] duration-200 ease-linear group-data-[collapsible=offcanvas]:w-0 group-data-[side=right]:rotate-180 group-data-[collapsible=icon]:w-[--sidebar-width-icon]"></div>
                    <div class="fixed inset-y-0 z-10 hidden h-svh w-[--sidebar-width] transition-[left,right,width] duration-200 ease-linear md:flex left-0 group-data-[collapsible=offcanvas]:left-[calc(var(--sidebar-width)*-1)] group-data-[collapsible=icon]:w-[--sidebar-width-icon] group-data-[side=left]:border-r group-data-[side=right]:border-l">
                        <div data-sidebar="sidebar" class="flex h-full w-full flex-col bg-sidebar group-data-[variant=floating]:rounded-lg group-data-[variant=floating]:border group-data-[variant=floating]:border-sidebar-border group-data-[variant=floating]:shadow">
                            <div data-sidebar="content" class="flex min-h-0 flex-1 flex-col gap-2 overflow-auto group-data-[collapsible=icon]:overflow-hidden">
                                <div class="sidebar-logo-area flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        @if($themeSettings['theme_logo_url'] && $themeSettings['theme_logo_url'] !== '/images/logo-default.png')
                                            <img src="{{ $themeSettings['theme_logo_url'] }}" alt="Logo" class="h-10 w-10 object-contain rounded-xl">
                                        @else
                                            <div class="sidebar-logo-circle">
                                                {{ strtoupper(substr($themeSettings['theme_brand_name'], 0, 1)) }}
                                            </div>
                                        @endif
                                        <div>
                                            <div class="sidebar-brand-name">{{ strtolower($themeSettings['theme_brand_name']) }}</div>
                                            <div class="sidebar-sub-brand">padaria.olika.app</div>
                                        </div>
                                    </div>
                                    <button id="sidebar-close" class="lg:hidden p-2 rounded-lg text-sidebar-foreground">
                                        <i data-lucide="x" class="h-6 w-6"></i>
                                    </button>
                                </div>
                                <div class="flex-1 overflow-y-auto px-3 py-4 space-y-5">
                                    @foreach($menuGroups as $group)
                                        <div>
                                            <h3 class="sidebar-group-label">{{ $group['title'] }}</h3>
                                            <div class="space-y-0.5">
                                                @foreach($group['items'] as $item)
                                                    @php
                                                        $itemPath = parse_url($item['url'], PHP_URL_PATH);
                                                        $itemPath = ltrim($itemPath, '/');
                                                        $isActive = false;
                                                        foreach ($item['pattern'] as $pattern) {
                                                            if ($currentRoute === $itemPath || 
                                                                $currentRoute === $pattern || 
                                                                $itemPath === $pattern ||
                                                                str_contains($currentRoute, $pattern) ||
                                                                str_starts_with($currentRoute, $pattern)) {
                                                                $isActive = true;
                                                                break;
                                                            }
                                                        }
                                                    @endphp
                                                    <a href="{{ $item['url'] }}"
                                                       class="sidebar-item {{ $isActive ? 'active-item' : '' }}">
                                                        <i data-lucide="{{ $item['icon'] }}"></i>
                                                        <span class="truncate">{{ $item['label'] }}</span>
                                                    </a>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="sidebar-footer">
                                    <form method="POST" action="{{ route('auth.logout') }}">
                                        @csrf
                                        <button type="submit" class="sidebar-logout-btn">
                                            <i data-lucide="log-out"></i>
                                            <span>Sair</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Sidebar Mobile (Offcanvas) -->
                <div id="mobile-sidebar" class="fixed inset-y-0 left-0 z-50 w-[--sidebar-width] bg-sidebar border-r border-sidebar-border transform -translate-x-full transition-transform duration-200 ease-in-out md:hidden">
                    <div class="flex h-full w-full flex-col">
                        <div class="sidebar-logo-area flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                @if($themeSettings['theme_logo_url'] && $themeSettings['theme_logo_url'] !== '/images/logo-default.png')
                                    <img src="{{ $themeSettings['theme_logo_url'] }}" alt="Logo" class="h-8 w-8 object-contain rounded">
                                @else
                                    <div class="sidebar-logo-circle">
                                        {{ strtoupper(substr($themeSettings['theme_brand_name'], 0, 1)) }}
                                    </div>
                                @endif
                                <div>
                                    <div class="sidebar-brand-name">{{ strtolower($themeSettings['theme_brand_name']) }}</div>
                                    <div class="sidebar-sub-brand">{{ request()->getHost() }}</div>
                                </div>
                            </div>
                            <button type="button" onclick="toggleSidebar()" class="inline-flex items-center justify-center rounded-md p-2 hover:bg-accent hover:text-accent-foreground text-white">
                                <i data-lucide="x" class="h-5 w-5 text-white"></i>
                            </button>
                        </div>
                        <div class="flex min-h-0 flex-1 flex-col gap-4 overflow-auto p-2">
                            @foreach($menuGroups as $group)
                                <div class="mb-4">
                                    <p class="sidebar-group-label">{{ $group['title'] }}</p>
                                    <ul class="space-y-1">
                                        @foreach($group['items'] as $item)
                                            @php
                                                $itemPath = parse_url($item['url'], PHP_URL_PATH);
                                                $itemPath = ltrim($itemPath, '/');
                                                $isActive = false;
                                                foreach ($item['pattern'] as $pattern) {
                                                    if ($currentRoute === $itemPath || 
                                                        $currentRoute === $pattern || 
                                                        $itemPath === $pattern ||
                                                        str_contains($currentRoute, $pattern) ||
                                                        str_starts_with($currentRoute, $pattern)) {
                                                        $isActive = true;
                                                        break;
                                                    }
                                                }
                                            @endphp
                                            <li>
                                                <a href="{{ $item['url'] }}"
                                                   class="sidebar-item {{ $isActive ? 'active-item' : '' }}" onclick="toggleSidebar()">
                                                    <i data-lucide="{{ $item['icon'] }}"></i>
                                                    <span class="truncate">{{ $item['label'] }}</span>
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                
                <div class="flex-1 flex flex-col w-full max-w-full overflow-x-hidden">
                    <header class="bg-white border-b border-border px-6 py-4 sticky top-0 z-20">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <button id="sidebar-toggle" class="lg:hidden p-2 rounded-lg bg-sidebar text-sidebar-foreground shadow-lg mr-2" data-sidebar="trigger">
                                    <i data-lucide="menu" class="h-6 w-6"></i>
                                </button>

                                <div class="flex flex-col">
                                    <div class="flex items-center gap-1 text-sm font-medium text-primary mb-1">
                                        <span>Menu Principal</span>
                                        <i data-lucide="chevron-right" class="h-3 w-3"></i>
                                        <span>{{ $pageTitle ?? 'Dashboard' }}</span>
                                    </div>
                                    <h1 class="text-2xl font-bold text-foreground">
                                        {{ $pageTitle ?? 'Dashboard' }}
                                    </h1>
                                    @if ($pageSubtitle)
                                        <p class="text-sm text-muted-foreground mt-0.5">{{ $pageSubtitle }}</p>
                                    @endif
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                @auth
                                    <div class="hidden md:flex items-center gap-3 pl-3">
                                        <div class="text-right">
                                            <p class="text-sm font-medium text-foreground">{{ auth()->user()->name }}</p>
                                            <p class="text-xs text-muted-foreground">{{ auth()->user()->email }}</p>
                                        </div>
                                        <div class="h-10 w-10 rounded-full border-2 border-primary/20 bg-primary/10 flex items-center justify-center text-primary font-semibold">
                                            {{ strtoupper(substr(auth()->user()->name, 0, 1) . (explode(' ', auth()->user()->name)[1] ? substr(explode(' ', auth()->user()->name)[1], 0, 1) : '')) }}
                                        </div>
                                    </div>
                                @endauth
                            </div>
                        </div>
                    </header>
                    <main class="flex-1 p-4 md:p-6 lg:p-8 overflow-x-hidden max-w-full">
                        <div class="max-w-7xl mx-auto space-y-6">
                            @if(session('error'))
                                <div class="rounded-lg border border-destructive/30 bg-destructive/10 px-4 py-3 text-destructive shadow-sm">
                                    {{ session('error') }}
                                </div>
                            @endif
                            
                            @if(session('success') && !request()->routeIs('dashboard.index'))
                                <div class="rounded-lg border border-success/30 bg-success/10 px-4 py-3 text-success shadow-sm">
                                    {{ session('success') }}
                                </div>
                            @endif

                            @if(isset($errors) && $errors->any())
                                <div class="rounded-lg border border-destructive/30 bg-destructive/10 px-4 py-3 text-destructive shadow-sm">
                                    <ul class="list-disc space-y-1 pl-5 text-sm">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @yield('content')
                        </div>
                    </main>
                </div>
            </div>
        </div>
    </div>
    @stack('scripts')
    <script>
        function toggleSidebar() {
            const mobileSidebar = document.getElementById('mobile-sidebar');
            const sidebarOverlay = document.getElementById('sidebar-overlay');
            const isMobile = window.innerWidth < 768; // md breakpoint do Tailwind
            
            if (isMobile) {
                // Comportamento mobile (offcanvas)
                if (mobileSidebar.classList.contains('-translate-x-full')) {
                    // Abrir
                    mobileSidebar.classList.remove('-translate-x-full');
                    sidebarOverlay.classList.remove('hidden');
                } else {
                    // Fechar
                    mobileSidebar.classList.add('-translate-x-full');
                    sidebarOverlay.classList.add('hidden');
                }
            } else {
                // Comportamento desktop (collapse/expand)
                const sidebarGroup = document.getElementById('desktop-sidebar');
                if (!sidebarGroup) return;
                
                const isCollapsed = sidebarGroup.getAttribute('data-collapsible') === 'icon';
                
                if (isCollapsed) {
                    expandSidebar();
                } else {
                    collapseSidebar();
                }
            }
        }

        function collapseSidebar() {
            const sidebarGroup = document.getElementById('desktop-sidebar');
            if (sidebarGroup) {
                sidebarGroup.setAttribute('data-state', 'collapsed');
                sidebarGroup.setAttribute('data-collapsible', 'icon');
                localStorage.setItem('sidebarCollapsed', 'true');
            }
        }

        function expandSidebar() {
            const sidebarGroup = document.getElementById('desktop-sidebar');
            if (sidebarGroup) {
                sidebarGroup.setAttribute('data-state', 'expanded');
                sidebarGroup.removeAttribute('data-collapsible');
                localStorage.setItem('sidebarCollapsed', 'false');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebar-toggle');
            
            // Restaurar estado inicial do desktop
            if (window.innerWidth >= 768) {
                const sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
                if (sidebarCollapsed) {
                    collapseSidebar();
                }
            }
            
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    toggleSidebar();
                });
            }
            
            // Fechar sidebar mobile ao redimensionar para desktop
            window.addEventListener('resize', function() {
                if (window.innerWidth >= 768) {
                    const mobileSidebar = document.getElementById('mobile-sidebar');
                    const sidebarOverlay = document.getElementById('sidebar-overlay');
                    if (mobileSidebar) {
                        mobileSidebar.classList.add('-translate-x-full');
                    }
                    if (sidebarOverlay) {
                        sidebarOverlay.classList.add('hidden');
                    }
                }
            });
        });
    </script>
</body>
</html>
