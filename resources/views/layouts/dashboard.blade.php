<!doctype html>

<html lang="pt-br">

<head>

  <meta charset="utf-8"/>

  <meta name="viewport" content="width=device-width,initial-scale=1"/>

  <title>@yield('title','Dashboard Olika')</title>

  <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">

  <style>

    :root{--amber:#b45309;}

    *{box-sizing:border-box}

    body{font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;background:#f6f6f6;color:#222;margin:0}

    .top{display:flex;gap:12px;align-items:center;padding:12px 16px;background:#fff;border-bottom:1px solid #eee;position:sticky;top:0;z-index:100}

    .brand{font-weight:800;color:var(--amber);font-size:16px;white-space:nowrap}

    .wrap{display:grid;grid-template-columns:260px 1fr;gap:16px;padding:16px;min-height:calc(100vh - 56px)}

    .card{background:#fff;border:1px solid #eee;border-radius:12px;padding:16px;box-shadow:0 1px 3px rgba(0,0,0,.05)}

    .menu{position:sticky;top:80px;height:fit-content;max-height:calc(100vh - 120px);overflow-y:auto}

    .menu a{display:block;padding:10px 12px;border-radius:10px;color:#333;text-decoration:none;font-size:14px;transition:all .2s}

    .menu a.active,.menu a:hover{background:#fff8e7;color:var(--amber)}

    .btn{background:var(--amber);color:#fff;border:none;border-radius:10px;padding:10px 16px;cursor:pointer;font-size:14px;font-weight:600;transition:all .2s;text-decoration:none;display:inline-block}

    .btn:hover{background:#c45f0a;transform:translateY(-1px)}

    table{width:100%;border-collapse:collapse;font-size:14px}

    table thead{background:#f9fafb}

    th,td{padding:12px;border-top:1px solid #eee;text-align:left}

    th{font-weight:600;font-size:12px;color:#6b7280;text-transform:uppercase}

    .kpi{display:grid;grid-template-columns:repeat(4,1fr);gap:12px}

    .badge{font-size:11px;padding:4px 10px;border-radius:999px;background:#f1f5f9;color:#475569;text-decoration:none;display:inline-block;font-weight:500}

    .flex{display:flex;gap:10px;align-items:center;flex-wrap:wrap}

    input, select {border: 1px solid #e5e7eb; padding: 10px 12px; border-radius: 10px; width: 100%; font-size:14px}

    /* Responsividade: Tablet */
    @media(max-width:1024px){
      .wrap{grid-template-columns:220px 1fr;gap:12px;padding:12px}
      .top{padding:10px 12px}
      .brand{font-size:14px}
      .kpi{grid-template-columns:repeat(2,1fr)}
      table{font-size:13px}
      th,td{padding:8px}
    }

    /* Responsividade: Mobile */
    @media(max-width:768px){
      .wrap{grid-template-columns:1fr;padding:8px;gap:8px}
      .card{padding:12px;border-radius:8px}
      .menu{position:static;max-height:none;margin-bottom:8px}
      .menu a{font-size:13px;padding:8px 10px}
      .top{flex-wrap:wrap}
      .brand{font-size:13px}
      .btn{padding:8px 12px;font-size:13px}
      .kpi{grid-template-columns:1fr}
      table{font-size:12px}
      table thead{display:none}
      table tr{display:block;margin-bottom:16px;border:1px solid #e5e7eb;border-radius:8px;overflow:hidden}
      table td{display:block;padding:8px 12px;border:none;text-align:right;position:relative;padding-left:40%}
      table td:before{content:attr(data-label);font-weight:600;position:absolute;left:12px;text-transform:capitalize}
      th,td{padding:6px}
      .flex{flex-direction:column;align-items:stretch}
      .flex > *{width:100%}
    }

    /* Responsividade: Mobile Pequeno */
    @media(max-width:480px){
      .top{padding:8px 10px}
      .brand{font-size:12px}
      .card{padding:10px}
      .btn{padding:8px 10px;font-size:12px}
      table{font-size:11px}
      .badge{font-size:10px;padding:3px 8px}
    }

    /* Utilitários */
    .text-sm{font-size:13px}
    .text-lg{font-size:18px;font-weight:600}
    .text-xl{font-size:22px;font-weight:800}

  </style>

  @stack('head')

</head>

<body>

  <div class="top">

    <div class="brand">OLIKA • Dashboard</div>

    <div class="flex" style="margin-left:auto">

      <a class="badge" href="{{ route('dashboard.compact') }}">Compacto</a>

      <a class="badge" href="{{ route('dashboard.index') }}">Completo</a>

    </div>

  </div>

  <div class="wrap">

    <aside class="card menu">

      <a href="{{ route('dashboard.index') }}" class="{{ request()->routeIs('dashboard.index')?'active':'' }}">🏠 Visão Geral</a>

      <a href="{{ route('dashboard.pdv') }}" class="{{ request()->routeIs('dashboard.pdv*')?'active':'' }}">🖥️ PDV</a>

      <a href="{{ route('dashboard.orders') }}" class="{{ request()->routeIs('dashboard.orders*')?'active':'' }}">🧾 Pedidos</a>

      <a href="{{ route('dashboard.customers') }}" class="{{ request()->routeIs('dashboard.customers')?'active':'' }}">👤 Clientes</a>

      <a href="{{ route('dashboard.products') }}" class="{{ request()->routeIs('dashboard.products')?'active':'' }}">🍞 Produtos</a>

      <a href="{{ route('dashboard.categories') }}" class="{{ request()->routeIs('dashboard.categories')?'active':'' }}">📂 Categorias</a>

      <a href="{{ route('dashboard.coupons') }}" class="{{ request()->routeIs('dashboard.coupons')?'active':'' }}">🏷️ Cupons</a>

      <a href="{{ route('dashboard.cashback') }}" class="{{ request()->routeIs('dashboard.cashback')?'active':'' }}">💸 Cashback</a>

      <a href="{{ route('dashboard.loyalty') }}" class="{{ request()->routeIs('dashboard.loyalty')?'active':'' }}">🎯 Fidelidade</a>

      <a href="{{ route('dashboard.reports') }}" class="{{ request()->routeIs('dashboard.reports')?'active':'' }}">📈 Relatórios</a>

      <div style="height:8px"></div>

      <a href="{{ route('dashboard.whatsapp') }}" class="{{ request()->routeIs('dashboard.whatsapp')?'active':'' }}">💬 WhatsApp</a>

      <a href="{{ route('dashboard.mp') }}" class="{{ request()->routeIs('dashboard.mp')?'active':'' }}">💳 Mercado Pago</a>

      <a href="{{ route('dashboard.statuses') }}" class="{{ request()->routeIs('dashboard.statuses*')?'active':'' }}">⚙️ Status & Templates</a>

    </aside>

    <main>@yield('content')</main>

  </div>

  @stack('scripts')

</body>

</html>

