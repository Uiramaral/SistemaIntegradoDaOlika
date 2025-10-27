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

<div class="layout">

  {{-- BACKDROP (id p/ fallback JS) --}}
  <div id="backdrop" class="backdrop" x-cloak x-show="open" x-transition.opacity @click="open=false"></div>

  <!-- Sidebar (id p/ fallback JS) -->
  <aside id="sidebar" class="sidebar" x-cloak :class="open ? 'open' : ''" @click.outside="open=false">
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
  <main class="main">
    <div class="page">
      @yield('content')
    </div>
  </main>
</div>

<script>
(function(){
  // Se Alpine estiver carregado, deixamos ele cuidar. Caso contrário, JS puro.
  var hasAlpine = !!window.Alpine;

  // Elementos
  var btn      = document.getElementById('navToggle');
  var aside    = document.getElementById('sidebar');
  var backdrop = document.getElementById('backdrop');
  var body     = document.body;
  var menu     = document.querySelector('.menu');

  if (!btn || !aside || !backdrop) return;

  // Helpers
  function openMenu(){
    aside.classList.add('open');
    body.classList.add('no-scroll');
    backdrop.style.display = 'block';
    btn.setAttribute('aria-expanded','true');
  }
  function closeMenu(){
    aside.classList.remove('open');
    body.classList.remove('no-scroll');
    backdrop.style.display = 'none';
    btn.setAttribute('aria-expanded','false');
  }
  function toggleMenu(){
    if (aside.classList.contains('open')) closeMenu(); else openMenu();
  }

  // Se Alpine não estiver ativo, ligamos o fallback
  if (!hasAlpine){
    btn.addEventListener('click', function(e){ e.preventDefault(); toggleMenu(); }, {passive:false});
    backdrop.addEventListener('click', closeMenu);
    document.addEventListener('keydown', function(e){ if (e.key === 'Escape') closeMenu(); });

    // fechar ao clicar em qualquer link do menu
    if (menu){
      menu.addEventListener('click', function(e){
        var a = e.target.closest('a');
        if (a) closeMenu();
      });
    }
  }
})();
</script>

</body>
</html>