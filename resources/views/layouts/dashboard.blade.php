<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title','OLIKA — Dashboard')</title>
  <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
  <script defer src="{{ asset('js/alpine.min.js') }}"></script>
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
</script>

</body>
</html>