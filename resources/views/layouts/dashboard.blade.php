<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  
  {{-- CSRF p/ fetch/POST --}}
  <meta name="csrf-token" content="{{ csrf_token() }}">
  
  {{-- CSS legado (se existir) + novos estilos --}}
  {{-- <link rel="stylesheet" href="{{ asset('css/style.css') }}?v=3"> --}}
  {{-- <link rel="stylesheet" href="{{ asset('css/style-mobile.css') }}?v=3" media="(max-width: 768px)"> --}}
  
  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <style>
    .sidebar-active {
      background-color: #ea580c;
      color: white;
    }
    
    /* CSS básico para o sidebar funcionar com Tailwind */
    .dashboard-wrapper {
      display: flex;
      height: 100vh;
    }
    
    .dashboard-sidebar {
      width: 240px;
      background: white;
      box-shadow: 2px 0 4px rgba(0,0,0,0.1);
      transition: transform 0.3s ease;
      z-index: 1000;
    }
    
    .dashboard-content {
      flex: 1;
      overflow-y: auto;
      background: #f8fafc;
    }
    
    .sidebar-toggle {
      display: none;
      position: fixed;
      top: 20px;
      left: 20px;
      z-index: 1001;
      background: #ea580c;
      color: white;
      border: none;
      padding: 10px;
      border-radius: 4px;
      cursor: pointer;
    }
    
    .overlay {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0,0,0,0.5);
      z-index: 999;
    }
    
    .brand {
      padding: 20px;
      border-bottom: 1px solid #e5e7eb;
      display: flex;
      align-items: center;
      gap: 12px;
    }
    
    .brand > div {
      flex: 1;
    }
    
    .logo-square {
      width: 32px;
      height: 32px;
      background: #ea580c;
      border-radius: 4px;
      flex-shrink: 0;
    }
    
    .brand-title {
      font-size: 20px;
      font-weight: bold;
      color: #ea580c;
    }
    
    .brand-sub {
      font-size: 14px;
      color: #6b7280;
    }
    
    .menu {
      padding: 20px 0;
    }
    
    .mi {
      display: flex;
      align-items: center;
      padding: 12px 20px;
      color: #374151;
      text-decoration: none;
      transition: all 0.2s;
    }
    
    .mi:hover {
      background: #fef3c7;
      color: #ea580c;
    }
    
    .mi.active {
      background: #ea580c;
      color: white;
    }
    
    .mi.disabled {
      opacity: 0.5;
      cursor: not-allowed;
    }
    
    .mi.disabled:hover {
      background: transparent;
      color: #374151;
    }
    
    .mi-ico {
      margin-right: 12px;
      font-size: 16px;
    }
    
    .page {
      padding: 20px;
    }
    
    /* Mobile */
    @media (max-width: 768px) {
      .sidebar-toggle {
        display: block;
      }
      
      .dashboard-sidebar {
        position: fixed;
        top: 0;
        left: 0;
        height: 100vh;
        transform: translateX(-100%);
      }
      
      .dashboard-sidebar.active {
        transform: translateX(0);
      }
      
      .overlay.active {
        display: block;
      }
      
      .dashboard-content {
        margin-left: 0;
      }
    }
  </style>
  
  {{-- Página-específica por nome de rota --}}
  @php
    $route = Route::currentRouteName();
  @endphp
  
  @if(Str::is('dashboard.orders', $route))
    <link rel="stylesheet" href="{{ asset('css/pages/pedidos.css') }}?v=8">
  @endif
  
  @if(Str::is('dashboard.orders.show', $route))
    <link rel="stylesheet" href="{{ asset('css/pages/pedido-detalhe.css') }}?v=10">
  @endif
  
@if (Str::is('dashboard.pdv', $route) || request()->is('pdv'))
  <link rel="stylesheet" href="{{ asset('css/pages/pdv.css') }}?v=3">
  <script defer src="{{ asset('js/pdv.js') }}?v=2"></script>
@endif

@if (Str::is('dashboard.products*', $route))
  <link rel="stylesheet" href="{{ asset('css/pages/produtos.css') }}?v=2">
  <script defer src="{{ asset('js/products.js') }}?v=2"></script>
@endif

@if (Str::is('dashboard.products.edit', $route) || Str::is('dashboard.products.create', $route))
  <link rel="stylesheet" href="{{ asset('css/pages/product-edit.css') }}?v=1">
  <script defer src="{{ asset('js/product-edit.js') }}?v=1"></script>
@endif
  
  {{-- Fallback para stacks (se alguma view ainda usar @push) --}}
  @stack('styles')
  
  @stack('head')
  
  <title>@yield('title','OLIKA — Dashboard')</title>
</head>
<body class="ui">

  <button class="sidebar-toggle" onclick="toggleSidebar()">☰</button>

  <div id="overlay" class="overlay" onclick="toggleSidebar()"></div>

  <div class="dashboard-wrapper">

    <!-- Sidebar -->
    <aside class="dashboard-sidebar">
    <div class="brand">
      <span class="logo-square"></span>
      <div>
        <div class="brand-title">OLIKA</div>
        <div class="brand-sub">Dashboard</div>
      </div>
    </div>

    {{-- Clicar em QUALQUER link fecha o drawer no mobile --}}
    <nav class="menu">
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
  
  // Toggle sidebar
  sb.classList.toggle('active');
  ov.classList.toggle('active');
  
  // Prevent body scroll when sidebar is open
  if (sb.classList.contains('active')) {
    document.body.style.overflow = 'hidden';
  } else {
    document.body.style.overflow = '';
  }
}

// Close sidebar when clicking overlay
document.getElementById('overlay').addEventListener('click', toggleSidebar);

// Close sidebar on escape key
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    const sb = document.querySelector('.dashboard-sidebar');
    if (sb.classList.contains('active')) {
      toggleSidebar();
    }
  }
});
</script>

@stack('page-scripts')

</body>
</html>