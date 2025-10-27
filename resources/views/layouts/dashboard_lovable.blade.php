<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>@yield('title', 'Dashboard') — Olika</title>
  @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body>
  <div class="topbar">
    <div>
      <div style="display:flex;align-items:center;gap:12px">
        <span style="font-weight:700;font-size:18px">🍞 Olika</span>
        <span class="pill">Dashboard</span>
      </div>
    </div>
    <div style="display:flex;align-items:center;gap:12px">
      @yield('quick-filters')
      <a href="{{ route('admin.dashboard') }}" class="pill">Visão Geral</a>
      <a href="#" class="pill">Sair</a>
    </div>
  </div>

  <div class="page">
    <div class="page-header">
      <div>
        <h1>@yield('page-title','Visão Geral')</h1>
        <p>@yield('page-subtitle','')</p>
      </div>
      <div class="page-actions">
        @yield('page-actions')
      </div>
    </div>

    <div class="grid-stats">
      @yield('stat-cards')
    </div>

    <div>
      @yield('content')
    </div>
  </div>
</body>
</html>
