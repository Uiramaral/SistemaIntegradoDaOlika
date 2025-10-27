<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title','OLIKA — Dashboard')</title>
  <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="ui"
      x-data="{ open:false }"
      :class="open ? 'no-scroll' : ''"
      @keydown.escape.window="open=false">

<div class="layout">

  {{-- BACKDROP: clica fora → fecha --}}
  <div class="backdrop" x-show="open" x-transition.opacity @click="open=false"></div>

  <!-- Sidebar -->
  <aside class="sidebar" :class="open ? 'open' : ''" @click.outside="open=false">
    <div class="brand">
      <span class="logo-square"></span>
      <div>
        <div class="brand-title">OLIKA</div>
        <div class="brand-sub">Dashboard</div>
      </div>
    </div>

    {{-- Clicar em QUALQUER link fecha o drawer no mobile --}}
    <nav class="menu" @click="open=false">
      <a class="mi {{ request()->routeIs('dashboard.index','dashboard.compact') ? 'active' : '' }}"
         href="{{ route('dashboard.index') }}"><span class="mi-ico">👁️</span>Visão Geral</a>

      <a class="mi {{ request()->routeIs('dashboard.pdv*') ? 'active' : '' }}"
         href="{{ route('dashboard.pdv') }}"><span class="mi-ico">🧾</span>PDV</a>

      <a class="mi {{ request()->routeIs('dashboard.orders*') ? 'active' : '' }}"
         href="{{ route('dashboard.orders') }}"><span class="mi-ico">📦</span>Pedidos</a>

      <a class="mi {{ request()->routeIs('dashboard.customers*') ? 'active' : '' }}"
         href="{{ route('dashboard.customers') }}"><span class="mi-ico">👥</span>Clientes</a>

      <a class="mi {{ request()->routeIs('dashboard.products*') ? 'active' : '' }}"
         href="{{ route('dashboard.products') }}"><span class="mi-ico">🍕</span>Produtos</a>

      <a class="mi {{ request()->routeIs('dashboard.categories*') ? 'active' : '' }}"
         href="{{ route('dashboard.categories') }}"><span class="mi-ico">🗂️</span>Categorias</a>

      <a class="mi {{ request()->routeIs('dashboard.coupons*') ? 'active' : '' }}"
         href="{{ route('dashboard.coupons') }}"><span class="mi-ico">🏷️</span>Cupons</a>

      <a class="mi {{ request()->routeIs('dashboard.cashback*') ? 'active' : '' }}"
         href="{{ route('dashboard.cashback') }}"><span class="mi-ico">💸</span>Cashback</a>

      <a class="mi {{ request()->routeIs('dashboard.loyalty') ? 'active' : '' }}"
         href="{{ route('dashboard.loyalty') }}"><span class="mi-ico">⭐</span>Fidelidade</a>

      @php($relRoute = \Illuminate\Support\Facades\Route::has('relatorios.index') ? 'relatorios.index' : 'dashboard.reports')
      <a class="mi {{ request()->routeIs('relatorios.*','dashboard.reports') ? 'active' : '' }}"
         href="{{ route($relRoute) }}"><span class="mi-ico">📊</span>Relatórios</a>

      <a class="mi {{ request()->routeIs('dashboard.whatsapp*') ? 'active' : '' }}"
         href="{{ route('dashboard.whatsapp') }}"><span class="mi-ico">💬</span>WhatsApp</a>

      <a class="mi {{ request()->routeIs('dashboard.mp*') ? 'active' : '' }}"
         href="{{ route('dashboard.mp') }}"><span class="mi-ico">💳</span>Mercado Pago</a>

      <a class="mi {{ request()->routeIs('dashboard.statuses*') ? 'active' : '' }}"
         href="{{ route('dashboard.statuses') }}"><span class="mi-ico">⚙️</span>Status & Templates</a>
    </nav>
  </aside>

  <!-- Conteúdo -->
  <main class="main">
    <header class="topbar">
      <button class="hamb" @click="open = !open">☰</button>
      <div class="titles">
        <h1>@yield('page-title','Status & Templates')</h1>
        <p class="sub">@yield('page-subtitle','Gerencie os status dos pedidos e templates de mensagens')</p>
      </div>
      <div class="actions">
        <button class="btn ghost">Baixar Layout</button>
        <button class="btn primary">+ Novo Status</button>
      </div>
    </header>

    <div class="page">
      @yield('content')
    </div>
  </main>
</div>
</body>
</html>