<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'Dashboard - OLIKA')</title>
  
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Outfit:wght@600;700&display=swap" rel="stylesheet">
  
  <link rel="stylesheet" href="{{ asset('css/olika-design-system.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard-theme-v5.1.css') }}">
</head>
<body class="bg-[var(--background)]">

<div class="flex min-h-screen">
  <aside id="sidebar" class="sidebar fixed md:static inset-y-0 left-0 transition-transform duration-300 z-40 -translate-x-full md:translate-x-0">
    <div class="px-4 py-4 border-b border-[var(--sidebar-border)]">
      <span class="font-bold text-xl tracking-tight text-[var(--sidebar-accent)]">OLIKA</span>
      <button id="sidebar-close" class="md:hidden text-[var(--sidebar-foreground)] hover:text-white float-right">
        <i data-lucide="x"></i>
      </button>
    </div>
    
    <nav class="flex-1 px-3 py-4 space-y-4 overflow-y-auto">
      <div>
        <p class="text-xs uppercase text-[var(--sidebar-foreground)]/70 mb-2 px-2">Menu Principal</p>
        <ul class="space-y-1">
          <li><a href="{{ route('dashboard.index') }}" class="{{ request()->routeIs('dashboard.index') ? 'active' : '' }}"><i data-lucide="layout-dashboard"></i> Visão Geral</a></li>
          <li><a href="{{ route('dashboard.orders.index') }}" class="{{ request()->routeIs('dashboard.orders.*') ? 'active' : '' }}"><i data-lucide="receipt"></i> Pedidos</a></li>
          <li><a href="{{ route('dashboard.customers.index') }}" class="{{ request()->routeIs('dashboard.customers.*') ? 'active' : '' }}"><i data-lucide="users"></i> Clientes</a></li>
          <li><a href="{{ route('dashboard.deliveries.index') }}" class="{{ request()->routeIs('dashboard.deliveries.*') ? 'active' : '' }}"><i data-lucide="truck"></i> Entregas</a></li>
        </ul>
      </div>
    </nav>
    
    <div class="px-4 py-4 border-t border-[var(--sidebar-border)]">
      <form method="POST" action="{{ route('auth.logout') }}">
        @csrf
        <button type="submit" class="flex items-center gap-3 text-[var(--sidebar-foreground)] hover:text-white w-full">
          <i data-lucide="log-out"></i> Sair
        </button>
      </form>
    </div>
  </aside>

  <div class="flex-1 flex flex-col">
    <header class="topbar">
      <div class="flex items-center gap-3">
        <button id="sidebar-open" class="md:hidden"><i data-lucide="menu"></i></button>
        <h1 class="text-xl font-semibold text-[var(--text)]">@yield('title', 'Visão Geral')</h1>
      </div>
      <div class="flex gap-2">
        <a href="{{ route('dashboard.orders.index') }}" class="btn">Pedidos</a>
        <a href="{{ route('dashboard.reports') }}" class="btn-primary">Relatórios</a>
      </div>
    </header>

    <main class="p-6">
      @yield('content')
    </main>
  </div>
</div>

<script src="https://unpkg.com/lucide@latest"></script>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    if (window.lucide) {
      lucide.createIcons();
    }
  });
</script>
<script src="{{ asset('js/dashboard-sidebar.js') }}"></script>
</body>
</html>
