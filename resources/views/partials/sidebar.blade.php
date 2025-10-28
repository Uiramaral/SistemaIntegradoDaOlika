<!-- Sidebar Menu - partials/sidebar.blade.php -->
<aside class="dashboard-sidebar">
    <div class="brand">
        <div class="logo-square"></div>
        <div>
            <div class="brand-title">OLIKA</div>
            <div class="brand-sub">Dashboard</div>
        </div>
    </div>
    <nav class="menu">
        <a href="{{ route('admin.dashboard') }}" class="mi {{ request()->is('admin/dashboard') ? 'active' : '' }}">
            <i class="fa fa-home mi-ico"></i> Visão Geral
        </a>
        <a href="{{ route('dashboard.orders.index') }}" class="mi {{ request()->is('pedidos*') ? 'active' : '' }}">
            <i class="fa fa-receipt mi-ico"></i> Pedidos
        </a>
        <a href="{{ route('dashboard.customers.show', 1) }}" class="mi {{ request()->is('clientes*') ? 'active' : '' }}">
            <i class="fa fa-users mi-ico"></i> Clientes
        </a>
        <a href="{{ route('dashboard.products.edit', 1) }}" class="mi {{ request()->is('produtos*') ? 'active' : '' }}">
            <i class="fa fa-bread-slice mi-ico"></i> Produtos
        </a>
        <a href="{{ route('dashboard.categories') }}" class="mi {{ request()->is('categories*') ? 'active' : '' }}">
            <i class="fa fa-layer-group mi-ico"></i> Categorias
        </a>
        <a href="{{ route('dashboard.coupons') }}" class="mi {{ request()->is('coupons*') ? 'active' : '' }}">
            <i class="fa fa-tags mi-ico"></i> Cupons
        </a>
        <a href="{{ route('dashboard.cashback') }}" class="mi {{ request()->is('cashback*') ? 'active' : '' }}">
            <i class="fa fa-coins mi-ico"></i> Cashback
        </a>
        <a href="{{ route('dashboard.loyalty') }}" class="mi {{ request()->is('loyalty*') ? 'active' : '' }}">
            <i class="fa fa-star mi-ico"></i> Fidelidade
        </a>
        <a href="{{ route('dashboard.reports') }}" class="mi {{ request()->is('reports*') ? 'active' : '' }}">
            <i class="fa fa-chart-line mi-ico"></i> Relatórios
        </a>
        <a href="{{ route('admin.payment-settings') }}" class="mi {{ request()->is('admin/payment-settings*') ? 'active' : '' }}">
            <i class="fa fa-cog mi-ico"></i> Configurações
        </a>
    </nav>
</aside>
