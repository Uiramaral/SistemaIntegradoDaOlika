<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>@yield('title', 'Dashboard') - Olika</title>
  
  {{-- CSS direto sem Vite --}}
  <style>
    /* Dashboard CSS - Tema Lovable status-templates */
    :root {
      --color-primary: hsl(25 95% 53%);   /* laranja do layout-info.json */
      --color-bg: hsl(0 0% 98%);           /* quase branco do layout-info.json */
      --color-text: hsl(222 47% 11%);
      --color-muted: hsl(215 16% 47%);
      --color-border: hsl(0 0% 92%);
      --radius: 14px;
      --shadow-sm: 0 1px 2px rgba(0,0,0,.06);
      --shadow-md: 0 6px 20px rgba(0,0,0,.08);
    }

    /* Base */
    html, body {
      background: var(--color-bg);
      color: var(--color-text);
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
      line-height: 1.5;
      margin: 0;
      padding: 0;
    }

    /* Topbar sticky */
    .topbar {
      position: sticky; 
      top: 0; 
      z-index: 40;
      backdrop-filter: blur(8px);
      background: color-mix(in oklab, var(--color-bg) 85%, white 15%);
      border-bottom: 1px solid var(--color-border);
    }

    .topbar > div {
      max-width: 1280px;
      margin: 0 auto;
      padding: 12px 20px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
    }

    /* Botão primário */
    .btn-primary {
      background: var(--color-primary);
      color: #fff;
      border: none;
      border-radius: 10px;
      padding: 10px 14px;
      font-weight: 500;
      cursor: pointer;
      transition: transform .06s ease;
      text-decoration: none;
      display: inline-block;
    }

    .btn-primary:hover { 
      transform: translateY(-1px); 
    }

    /* Card */
    .card {
      background: #fff;
      border-radius: var(--radius);
      box-shadow: var(--shadow-md);
      border: 1px solid var(--color-border);
    }

    /* Pill/Badge */
    .pill {
      border-radius: 9999px;
      background: hsl(0 0% 96%);
      padding: 6px 12px;
      font-size: 12px;
      color: var(--color-muted);
      border: 1px solid var(--color-border);
      display: inline-block;
      text-decoration: none;
    }

    /* Page wrapper */
    .page {
      max-width: 1280px;
      margin: 0 auto;
      padding: 20px;
      display: grid;
      gap: 20px;
    }

    /* Page header */
    .page-header {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: 12px;
      flex-wrap: wrap;
    }

    .page-header h1 {
      font-size: 20px;
      font-weight: 700;
      margin: 0 0 4px 0;
    }

    .page-header p {
      font-size: 14px;
      color: var(--color-muted);
      margin: 0;
    }

    .page-actions {
      display: flex;
      gap: 8px;
      align-items: center;
      flex-wrap: wrap;
    }

    /* Grid de stats */
    .grid-stats {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 16px;
    }

    /* Stat card */
    .stat {
      display: grid;
      gap: 6px;
    }

    .stat .label {
      color: var(--color-muted);
      font-size: 12px;
      font-weight: 500;
    }

    .stat .value {
      font-weight: 700;
      font-size: 24px;
      line-height: 1.1;
    }

    /* Tabela compacta */
    .table-wrapper {
      overflow-x: auto;
    }

    .table-compact {
      width: 100%;
      border-collapse: collapse;
      font-size: 14px;
    }

    .table-compact thead {
      background: hsl(0 0% 96%);
    }

    .table-compact th {
      text-align: left;
      padding: 12px 16px;
      font-weight: 600;
      font-size: 12px;
      color: var(--color-muted);
      border-bottom: 1px solid var(--color-border);
    }

    .table-compact td {
      padding: 12px 16px;
      border-top: 1px solid var(--color-border);
    }

    .table-compact tbody tr:hover {
      background: hsl(0 0% 98%);
    }

    /* Input/Pill controls */
    input[type="text"], 
    input[type="search"],
    select {
      padding: 8px 12px;
      border-radius: 8px;
      border: 1px solid var(--color-border);
      font-size: 14px;
    }

    input[type="search"] {
      min-width: 200px;
    }

    /* Empty state */
    .empty-state {
      text-align: center;
      padding: 40px 20px;
      color: var(--color-muted);
    }

    /* Fix para slots vazios */
    .page-header p:empty {
      display: none;
    }

    /* Responsive */
    @media (max-width: 768px) {
      .page {
        padding: 12px;
      }
      
      .topbar > div {
        padding: 8px 12px;
      }
      
      .page-header {
        flex-direction: column;
        align-items: stretch;
      }
      
      .page-actions {
        width: 100%;
      }
      
      .grid-stats {
        grid-template-columns: 1fr;
      }
      
      .table-compact {
        font-size: 12px;
      }
      
      .table-compact th,
      .table-compact td {
        padding: 8px;
      }
    }
  </style>
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
      <a href="{{ route('dashboard.index') ?? '/' }}" class="pill">Visão Geral</a>
    </div>
  </div>

  <div class="page">
    <div class="page-header">
      <div>
        <h1>@yield('page-title', 'Visão Geral')</h1>
        <p>@yield('page-subtitle', '')</p>
      </div>
      <div class="page-actions">
        @yield('page-actions')
      </div>
    </div>

    @hasSection('stat-cards')
    <div class="grid-stats">
      @yield('stat-cards')
    </div>
    @endif

    <div>
      @yield('content')
    </div>
  </div>
</body>
</html>
