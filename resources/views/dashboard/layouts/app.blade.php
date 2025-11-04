<!DOCTYPE html>
<html lang="en" class="overflow-x-hidden">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'OLIKA Dashboard - Sistema de Gestão de Restaurante')</title>
    <meta name="description" content="Dashboard completo para gestão de restaurante com pedidos, produtos e relatórios em tempo real">
    <meta name="author" content="OLIKA">
    <meta property="og:title" content="OLIKA Dashboard">
    <meta property="og:description" content="Sistema de gestão de restaurante">
    <meta property="og:type" content="website">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
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
            --primary: 222.2 47.4% 11.2%;
            --primary-foreground: 210 40% 98%;
            --secondary: 210 40% 96%;
            --secondary-foreground: 222.2 84% 4.9%;
            --muted: 210 40% 96%;
            --muted-foreground: 215.4 16.3% 46.9%;
            --accent: 210 40% 96%;
            --accent-foreground: 222.2 84% 4.9%;
            --destructive: 0 84.2% 60.2%;
            --destructive-foreground: 210 40% 98%;
            --border: 214.3 31.8% 91.4%;
            --input: 214.3 31.8% 91.4%;
            --ring: 222.2 84% 4.9%;
            --radius: 0.5rem;
            --sidebar-background: 0 0% 98%;
            --sidebar-foreground: 240 5.3% 26.1%;
            --sidebar-primary: 240 5.9% 10%;
            --sidebar-primary-foreground: 0 0% 98%;
            --sidebar-accent: 240 4.8% 95.9%;
            --sidebar-accent-foreground: 240 5.9% 10%;
            --sidebar-border: 220 13% 91%;
            --sidebar-ring: 217.2 10.6% 64.9%;
            --success: 142.1 76.2% 36.3%;
            --success-foreground: 355.7 100% 97.3%;
            --warning: 38.7 92% 50%;
            --warning-foreground: 26 83.3% 14.1%;
        }
        .dark {
            --background: 222.2 84% 4.9%;
            --foreground: 210 40% 98%;
            --card: 222.2 84% 4.9%;
            --card-foreground: 210 40% 98%;
            --popover: 222.2 84% 4.9%;
            --popover-foreground: 210 40% 98%;
            --primary: 210 40% 98%;
            --primary-foreground: 222.2 47.4% 11.2%;
            --secondary: 217.2 32.6% 17.5%;
            --secondary-foreground: 210 40% 98%;
            --muted: 217.2 32.6% 17.5%;
            --muted-foreground: 215 20.2% 65.1%;
            --accent: 217.2 32.6% 17.5%;
            --accent-foreground: 210 40% 98%;
            --destructive: 0 62.8% 30.6%;
            --destructive-foreground: 210 40% 98%;
            --border: 217.2 32.6% 17.5%;
            --input: 217.2 32.6% 17.5%;
            --ring: 212.7 26.8% 83.9%;
            --sidebar-background: 240 5.9% 10%;
            --sidebar-foreground: 240 4.8% 95.9%;
            --sidebar-primary: 224.3 76.3% 94.1%;
            --sidebar-primary-foreground: 240 5.9% 10%;
            --sidebar-accent: 240 3.7% 15.9%;
            --sidebar-accent-foreground: 240 4.8% 95.9%;
            --sidebar-border: 240 3.7% 15.9%;
            --sidebar-ring: 217.2 10.6% 64.9%;
            --success: 142.1 70.6% 45.3%;
            --success-foreground: 144.9 61.2% 20.6%;
            --warning: 47.9 95.8% 53.1%;
            --warning-foreground: 26 83.3% 14.1%;
        }
        body { background-color: hsl(var(--background)); color: hsl(var(--foreground)); }
        * { border-color: hsl(var(--border)); }
    </style>
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
                                <div class="p-4 border-b">
                                    <h1 class="font-bold text-xl text-primary transition-all">OLIKA</h1>
                                    <p class="text-xs text-muted-foreground">Dashboard</p>
                                </div>
                                <div data-sidebar="group" class="relative flex w-full min-w-0 flex-col p-2">
                                    <div data-sidebar="group-label" class="flex h-8 shrink-0 items-center rounded-md px-2 text-xs font-medium text-sidebar-foreground/70 outline-none ring-sidebar-ring transition-[margin,opa] duration-200 ease-linear focus-visible:ring-2 [&>svg]:size-4 [&>svg]:shrink-0 group-data-[collapsible=icon]:-mt-8 group-data-[collapsible=icon]:opacity-0">Menu Principal</div>
                                    <div data-sidebar="group-content" class="w-full text-sm">
                                        <ul data-sidebar="menu" class="flex w-full min-w-0 flex-col gap-1">
                                            @php
                                                $currentRoute = request()->path();
                                                $menuItems = [
                                                    ['url' => route('dashboard.index'), 'label' => 'Visão Geral', 'icon' => 'lucide-layout-dashboard'],
                                                    ['url' => route('dashboard.pdv.index'), 'label' => 'PDV', 'icon' => 'lucide-shopping-cart'],
                                                    ['url' => route('dashboard.orders.index'), 'label' => 'Pedidos', 'icon' => 'lucide-file-text'],
                                                    ['url' => route('dashboard.customers.index'), 'label' => 'Clientes', 'icon' => 'lucide-users'],
                                                    ['url' => route('dashboard.products.index'), 'label' => 'Produtos', 'icon' => 'lucide-package'],
                                                    ['url' => route('dashboard.wholesale-prices.index'), 'label' => 'Preços de Revenda', 'icon' => 'lucide-dollar-sign'],
                                                    ['url' => route('dashboard.categories.index'), 'label' => 'Categorias', 'icon' => 'lucide-folder-tree'],
                                                    ['url' => route('dashboard.coupons.index'), 'label' => 'Cupons', 'icon' => 'lucide-ticket'],
                                                    ['url' => route('dashboard.cashback.index'), 'label' => 'Cashback', 'icon' => 'lucide-wallet'],
                                                    ['url' => route('dashboard.loyalty'), 'label' => 'Fidelidade', 'icon' => 'lucide-heart'],
                                                    ['url' => route('dashboard.reports'), 'label' => 'Relatórios', 'icon' => 'lucide-chart-column'],
                                                    ['url' => route('dashboard.settings'), 'label' => 'Configurações', 'icon' => 'lucide-settings2'],
                                                    ['url' => route('dashboard.settings.whatsapp'), 'label' => 'WhatsApp', 'icon' => 'lucide-message-circle'],
                                                    ['url' => route('dashboard.settings.mp'), 'label' => 'Mercado Pago', 'icon' => 'lucide-credit-card'],
                                                    ['url' => route('dashboard.settings.status-templates'), 'label' => 'Status & Templates', 'icon' => 'lucide-settings2'],
                                                ];
                                                $iconMap = [
                                                    'lucide-layout-dashboard' => '<rect width="7" height="9" x="3" y="3" rx="1"></rect><rect width="7" height="5" x="14" y="3" rx="1"></rect><rect width="7" height="9" x="14" y="12" rx="1"></rect><rect width="7" height="5" x="3" y="16" rx="1"></rect>',
                                                    'lucide-shopping-cart' => '<circle cx="8" cy="21" r="1"></circle><circle cx="19" cy="21" r="1"></circle><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"></path>',
                                                    'lucide-file-text' => '<path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"></path><path d="M14 2v4a2 2 0 0 0 2 2h4"></path><path d="M10 9H8"></path><path d="M16 13H8"></path><path d="M16 17H8"></path>',
                                                    'lucide-users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path>',
                                                    'lucide-package' => '<path d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z"></path><path d="M12 22V12"></path><path d="m3.3 7 7.703 4.734a2 2 0 0 0 1.994 0L20.7 7"></path><path d="m7.5 4.27 9 5.15"></path>',
                                                    'lucide-folder-tree' => '<path d="M20 10a1 1 0 0 0 1-1V6a1 1 0 0 0-1-1h-2.5a1 1 0 0 1-.8-.4l-.9-1.2A1 1 0 0 0 15 3h-2a1 1 0 0 0-1 1v5a1 1 0 0 0 1 1Z"></path><path d="M20 21a1 1 0 0 0 1-1v-3a1 1 0 0 0-1-1h-2.9a1 1 0 0 1-.88-.55l-.42-.85a1 1 0 0 0-.92-.6H13a1 1 0 0 0-1 1v5a1 1 0 0 0 1 1Z"></path><path d="M3 5a2 2 0 0 0 2 2h3"></path><path d="M3 3v13a2 2 0 0 0 2 2h3"></path>',
                                                    'lucide-ticket' => '<path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2Z"></path><path d="M13 5v2"></path><path d="M13 17v2"></path><path d="M13 11v2"></path>',
                                                    'lucide-wallet' => '<path d="M19 7V4a1 1 0 0 0-1-1H5a2 2 0 0 0 0 4h15a1 1 0 0 1 1 1v4h-3a2 2 0 0 0 0 4h3a1 1 0 0 0 1-1v-2a1 1 0 0 0-1-1"></path><path d="M3 5v14a2 2 0 0 0 2 2h15a1 1 0 0 0 1-1v-4"></path>',
                                                    'lucide-heart' => '<path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"></path>',
                                                    'lucide-chart-column' => '<path d="M3 3v16a2 2 0 0 0 2 2h16"></path><path d="M18 17V9"></path><path d="M13 17V5"></path><path d="M8 17v-3"></path>',
                                                    'lucide-message-circle' => '<path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"></path>',
                                                    'lucide-credit-card' => '<rect width="20" height="14" x="2" y="5" rx="2"></rect><line x1="2" x2="22" y1="10" y2="10"></line>',
                                                    'lucide-settings2' => '<path d="M20 7h-9"></path><path d="M14 17H5"></path><circle cx="17" cy="17" r="3"></circle><circle cx="7" cy="7" r="3"></circle>',
                                                ];
                                            @endphp
                                            @foreach($menuItems as $item)
                                                @php
                                                    $itemPath = parse_url($item['url'], PHP_URL_PATH);
                                                    $itemPath = ltrim($itemPath, '/');
                                                    $isActive = ($currentRoute === $itemPath) || ($currentRoute === '' && $itemPath === '') || str_contains($currentRoute, $itemPath);
                                                    $activeClass = $isActive ? 'bg-accent text-accent-foreground font-medium' : '';
                                                @endphp
                                                <li data-sidebar="menu-item" class="group/menu-item relative">
                                                    <a data-sidebar="menu-button" data-size="default" data-active="{{ $isActive ? 'true' : 'false' }}" class="peer/menu-button flex w-full items-center gap-2 overflow-hidden rounded-md p-2 text-left outline-none ring-sidebar-ring transition-[width,height,padding] focus-visible:ring-2 active:bg-sidebar-accent active:text-sidebar-accent-foreground disabled:pointer-events-none disabled:opacity-50 group-has-[[data-sidebar=menu-action]]/menu-item:pr-8 aria-disabled:pointer-events-none aria-disabled:opacity-50 data-[active=true]:bg-sidebar-accent data-[active=true]:font-medium data-[active=true]:text-sidebar-accent-foreground data-[state=open]:hover:bg-sidebar-accent data-[state=open]:hover:text-sidebar-accent-foreground group-data-[collapsible=icon]:!size-8 group-data-[collapsible=icon]:!p-2 [&>span:last-child]:truncate [&>svg]:size-4 [&>svg]:shrink-0 hover:bg-sidebar-accent hover:text-sidebar-accent-foreground h-8 text-sm {{ $activeClass }}" href="{{ $item['url'] }}" @if($isActive) aria-current="page" @endif>
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide {{ $item['icon'] }} h-4 w-4">
                                                            {!! $iconMap[$item['icon']] ?? '' !!}
                                                        </svg>
                                                        <span>{{ $item['label'] }}</span>
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Sidebar Mobile (Offcanvas) -->
                <div id="mobile-sidebar" class="fixed inset-y-0 left-0 z-50 w-[--sidebar-width] bg-sidebar border-r border-sidebar-border transform -translate-x-full transition-transform duration-200 ease-in-out md:hidden">
                    <div class="flex h-full w-full flex-col">
                        <div class="p-4 border-b border-sidebar-border flex items-center justify-between">
                            <div>
                                <h1 class="font-bold text-xl text-primary transition-all">OLIKA</h1>
                                <p class="text-xs text-muted-foreground">Dashboard</p>
                            </div>
                            <button type="button" onclick="toggleSidebar()" class="inline-flex items-center justify-center rounded-md p-2 hover:bg-accent hover:text-accent-foreground">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x h-5 w-5">
                                    <path d="M18 6 6 18"></path>
                                    <path d="M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                        <div class="flex min-h-0 flex-1 flex-col gap-2 overflow-auto p-2">
                            <div class="relative flex w-full min-w-0 flex-col">
                                <div class="flex h-8 shrink-0 items-center rounded-md px-2 text-xs font-medium text-sidebar-foreground/70 mb-2">Menu Principal</div>
                                <ul class="flex w-full min-w-0 flex-col gap-1 text-sm">
                                    @foreach($menuItems as $item)
                                        @php
                                            $isActive = ($currentRoute === ltrim($item['url'], '/')) || ($currentRoute === '' && $item['url'] === '/');
                                            $activeClass = $isActive ? 'bg-sidebar-accent text-sidebar-accent-foreground font-medium' : '';
                                        @endphp
                                        <li class="group/menu-item relative">
                                            <a class="flex w-full items-center gap-2 overflow-hidden rounded-md p-2 text-left outline-none transition-colors hover:bg-sidebar-accent hover:text-sidebar-accent-foreground h-8 text-sm {{ $activeClass }}" href="{{ $item['url'] }}" onclick="toggleSidebar()" @if($isActive) aria-current="page" @endif>
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4">
                                                    {!! $iconMap[$item['icon']] ?? '' !!}
                                                </svg>
                                                <span>{{ $item['label'] }}</span>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="flex-1 flex flex-col w-full max-w-full overflow-x-hidden">
                    <header class="sticky top-0 z-40 border-b bg-card/95 backdrop-blur supports-[backdrop-filter]:bg-card/60 w-full max-w-full overflow-x-hidden">
                        <div class="flex h-16 items-center gap-4 px-4 md:px-6 max-w-full">
                            <button type="button" id="sidebar-toggle" class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 hover:bg-accent hover:text-accent-foreground h-7 w-7 text-foreground" data-sidebar="trigger">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-panel-left">
                                    <rect width="18" height="18" x="3" y="3" rx="2"></rect>
                                    <path d="M9 3v18"></path>
                                </svg>
                                <span class="sr-only">Toggle Sidebar</span>
                            </button>
                            <div class="flex-1"></div>
                            @auth
                                <div class="flex items-center gap-3">
                                    <div class="text-right hidden sm:block">
                                        <p class="text-sm font-medium">{{ auth()->user()->name }}</p>
                                        <p class="text-xs text-muted-foreground">{{ auth()->user()->email }}</p>
                                    </div>
                                    <form action="{{ route('auth.logout') }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 px-3">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-log-out h-4 w-4">
                                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                                <polyline points="16 17 21 12 16 7"></polyline>
                                                <line x1="21" x2="9" y1="12" y2="12"></line>
                                            </svg>
                                            <span class="hidden sm:inline">Sair</span>
                                        </button>
                                    </form>
                                </div>
                            @endauth
                        </div>
                    </header>
                    <main class="flex-1 p-4 md:p-6 lg:p-8 overflow-x-hidden max-w-full">
                        @if(session('success'))
                            <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                                {{ session('error') }}
                            </div>
                        @endif

                        @if(isset($errors) && $errors->any())
                            <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                                <ul class="list-disc list-inside">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @yield('content')
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
