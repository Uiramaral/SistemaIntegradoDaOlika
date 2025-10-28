<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  
  {{-- CSRF p/ fetch/POST --}}
  <meta name="csrf-token" content="{{ csrf_token() }}">
  
  {{-- CSS legado (se existir) + novos estilos --}}
  <link rel="stylesheet" href="{{ asset('css/style.css') }}?v=3">
  <link rel="stylesheet" href="{{ asset('css/style-mobile.css') }}?v=3" media="(max-width: 768px)">
  
  {{-- Página-específica por nome de rota --}}
  @php($route = Route::currentRouteName())
  
  @switch(true)
    @case(Str::is('dashboard.orders.index', $route))
      <link rel="stylesheet" href="{{ asset('css/pages/pedidos.css') }}?v=8">
      @break
  
    @case(Str::is('dashboard.orders.show', $route))
      <link rel="stylesheet" href="{{ asset('css/pages/pedido-detalhe.css') }}?v=9">
      @break
  
    {{-- adicione aqui outros casos por página --}}
  @endswitch
  
  {{-- Fallback para stacks (se alguma view ainda usar @push) --}}
  @stack('styles')
  
  <script defer src="{{ asset('js/alpine.min.js') }}"></script>
  
  @stack('head')
  
  <title>@yield('title','OLIKA — Dashboard')</title>
</head>
<body class="ui"
      x-data="{ open:false }"
      :class="open ? 'no-scroll' : ''"
      @keydown.escape.window="open=false">

  <button class="sidebar-toggle" @click="open = !open">☰</button>

  <div id="overlay" class="overlay" onclick="toggleSidebar()"></div>

  <div class="dashboard-wrapper">

    <!-- Sidebar -->
    <aside class="dashboard-sidebar" :class="open ? 'active' : ''">
    <div class="brand">
      <span class="logo-square"></span>
      <div>
        <div class="brand-title">OLIKA</div>
        <div class="brand-sub">Dashboard</div>
      </div>
    </div>

    {{-- Clicar em QUALQUER link fecha o drawer no mobile --}}
    <nav class="menu" @click="open=false">
      @php
        // Mapeamento das rotas reais (subdomínio dashboard.menuolika.com.br)
        $items = [
          ['label'=>'Visão Geral', 'icon'=>'👁️', 'match'=>['dashboard.index','dashboard.compact'], 'route'=>'dashboard.index'],
          ['label'=>'PDV', 'icon'=>'🧾', 'match'=>['dashboard.pdv*'], 'route'=>'dashboard.pdv'],
          ['label'=>'Pedidos', 'icon'=>'📦', 'match'=>['dashboard.orders*'], 'route'=>'dashboard.orders'],
          ['label'=>'Clientes', 'icon'=>'👥', 'match'=>['dashboard.customers*'], 'route'=>'dashboard.customers'],
          ['label'=>'Produtos', 'icon'=>'🍕', 'match'=>['dashboard.products*'], 'route'=>'dashboard.products'],
          ['label'=>'Categorias', 'icon'=>'🗂️', 'match'=>['dashboard.categories*'], 'route'=>'dashboard.categories'],
          ['label'=>'Cupons', 'icon'=>'🏷️', 'match'=>['dashboard.coupons*'], 'route'=>'dashboard.coupons'],
          ['label'=>'Cashback', 'icon'=>'💸', 'match'=>['dashboard.cashback*'], 'route'=>'dashboard.cashback'],
          ['label'=>'Fidelidade', 'icon'=>'⭐', 'match'=>['dashboard.loyalty'], 'route'=>'dashboard.loyalty'],
          // Relatórios: usa relatorios.index se existir; senão dashboard.reports
          ['label'=>'Relatórios', 'icon'=>'📊', 'match'=>['relatorios.*','dashboard.reports'], 'route'=>\Illuminate\Support\Facades\Route::has('relatorios.index') ? 'relatorios.index' : 'dashboard.reports'],
          ['label'=>'WhatsApp', 'icon'=>'💬', 'match'=>['dashboard.whatsapp*'], 'route'=>'dashboard.whatsapp'],
          ['label'=>'Mercado Pago', 'icon'=>'💳', 'match'=>['dashboard.mp*'], 'route'=>'dashboard.mp'],
          ['label'=>'Status & Templates', 'icon'=>'⚙️', 'match'=>['dashboard.statuses*'], 'route'=>'dashboard.statuses'],
        ];
      @endphp

      @foreach($items as $it)
        @php
          $isActive = request()->routeIs(...(array)$it['match']);
          $url = \Illuminate\Support\Facades\Route::has($it['route']) ? route($it['route']) : '#';
        @endphp
        <a class="mi {{ $isActive ? 'active' : '' }} {{ $url === '#' ? 'disabled' : '' }}" href="{{ $url }}">
          <span class="mi-ico">{{ $it['icon'] }}</span>{{ $it['label'] }}
        </a>
      @endforeach
    </nav>
  </aside>

  <!-- Conteúdo -->
    <main class="dashboard-content">
    <div class="page">
      @yield('content')
    </div>
  </main>

</div>

<!-- JS por página (onde usar PDV) -->
@stack('page-scripts')

<script>
function toggleSidebar() {
  const sb = document.querySelector('.dashboard-sidebar');
  const ov = document.getElementById('overlay');
  const btn = document.querySelector('.sidebar-toggle');
  
  // Alpine.js controle
  if (window.Alpine) {
    const alpineState = Alpine.store?.('sidebar') || 
                        window.__x?.$data?.(document.querySelector('body'))?.open;

    if (typeof alpineState !== 'undefined') {
      // Alpine já gerencia, apenas passa o click para ele
      return;
    }
  }
  
  // Fallback JavaScript puro
  sb.classList.toggle('active');
  ov.classList.toggle('active');
  document.body.classList.toggle('no-scroll');
}

// Listener do Escape key
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    const sb = document.querySelector('.dashboard-sidebar');
    if (sb.classList.contains('active')) {
      toggleSidebar();
  }
  }
});

// Fallback mobile (caso Alpine falhe)
(function(){
  const btn = document.querySelector('.sidebar-toggle');
  const sidebar = document.querySelector('.dashboard-sidebar');
  const overlay = document.getElementById('overlay');

  if(!btn || !sidebar || !overlay) return;

  function openMenu(){
    sidebar.classList.add('active'); 
    overlay.classList.add('active');
    document.body.classList.add('no-scroll');
  }
  
  function closeMenu(){
    sidebar.classList.remove('active'); 
    overlay.classList.remove('active');
    document.body.classList.remove('no-scroll');
  }

  btn.addEventListener('click', () => {
    const isOpen = sidebar.classList.contains('active');
    isOpen ? closeMenu() : openMenu();
      });
  
  overlay.addEventListener('click', closeMenu);
})();
</script>

@stack('page-scripts')

</body>
</html>