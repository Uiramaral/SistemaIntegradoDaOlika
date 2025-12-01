<!DOCTYPE html>
<html lang="pt-BR" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard - OLIKA')</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>

    <!-- CSS Principal -->
    <link rel="stylesheet" href="{{ asset('css/core/dashboard-theme-v4.css') }}">
    <link rel="stylesheet" href="{{ asset('css/core/dashboard-components.css') }}">
    <link rel="stylesheet" href="{{ asset('css/core/dashboard-animations.css') }}">
    <link rel="stylesheet" href="{{ asset('css/core/dashboard-utilities.css') }}">
</head>
<body class="fade-in bg-background text-foreground font-sans">

<div class="min-h-screen flex">
    <!-- Sidebar -->
    <aside id="sidebar" class="fixed md:static inset-y-0 left-0 w-64 bg-sidebar text-sidebar-foreground flex flex-col transition-transform duration-300 z-40 -translate-x-full md:translate-x-0">
        <div class="flex items-center justify-between border-b border-sidebar-border p-4">
            <span class="font-display text-xl font-semibold tracking-tight">OLIKA</span>
            <button id="sidebar-close" class="md:hidden text-sidebar-foreground hover:text-sidebar-accent">
                <i class="ph ph-x"></i>
            </button>
        </div>

        <nav class="flex-1 p-4 space-y-4">
            <div>
                <p class="text-xs uppercase tracking-widest text-sidebar-foreground/70 mb-2">Menu Principal</p>
                <ul class="space-y-1">
                    <li><a href="{{ route('dashboard.index') }}" class="nav-link {{ request()->routeIs('dashboard.index') ? 'active' : '' }}"><i class="ph ph-squares-four"></i> Visão Geral</a></li>
                    <li><a href="{{ route('dashboard.pdv.index') }}" class="nav-link {{ request()->routeIs('dashboard.pdv.*') ? 'active' : '' }}"><i class="ph ph-monitor"></i> PDV</a></li>
                    <li><a href="{{ route('dashboard.orders.index') }}" class="nav-link {{ request()->routeIs('dashboard.orders.*') ? 'active' : '' }}"><i class="ph ph-receipt"></i> Pedidos</a></li>
                    <li><a href="{{ route('dashboard.customers.index') }}" class="nav-link {{ request()->routeIs('dashboard.customers.*') ? 'active' : '' }}"><i class="ph ph-users"></i> Clientes</a></li>
                    <li><a href="{{ route('dashboard.deliveries.index') }}" class="nav-link {{ request()->routeIs('dashboard.deliveries.*') ? 'active' : '' }}"><i class="ph ph-truck"></i> Entregas</a></li>
                </ul>
            </div>
        </nav>

        <div class="border-t border-sidebar-border p-4">
            <form action="{{ route('auth.logout') }}" method="POST">
                @csrf
                <button type="submit" class="flex items-center gap-3 text-sidebar-foreground hover:text-destructive w-full">
                    <i class="ph ph-sign-out"></i> Sair
                </button>
            </form>
        </div>
    </aside>

    <!-- Conteúdo Principal -->
    <div class="flex flex-col flex-1">
        <header class="border-b bg-card backdrop-blur-sm flex items-center justify-between p-4">
            <div class="flex items-center gap-3">
                <button id="sidebar-open" class="md:hidden"><i class="ph ph-list"></i></button>
                <h1 class="font-display text-xl font-semibold">@yield('title', 'Visão Geral')</h1>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('dashboard.orders.index') }}" class="btn"><i class="ph ph-list-checks"></i> Pedidos</a>
                <a href="{{ route('dashboard.reports') }}" class="btn-primary"><i class="ph ph-chart-bar"></i> Relatórios</a>
            </div>
        </header>

        <main class="p-6 flex-1 bg-background overflow-y-auto">
            @yield('content')
        </main>
    </div>
</div>

<!-- Scripts -->
<script type="module" src="{{ asset('js/dashboard.js') }}"></script>
<script type="module" src="{{ asset('js/dashboard-sidebar.js') }}"></script>
<script type="module" src="{{ asset('js/dashboard-tabs.js') }}"></script>
<script defer src="{{ asset('js/dashboard-animations.js') }}"></script>
</body>
</html>
