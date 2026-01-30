<aside class="w-60 bg-white border-r sidebar-mobile">
    <!-- Brand/Logo -->
    <div class="p-6 flex items-center gap-3 border-b">
        <div class="w-8 h-8 bg-orange-500 rounded"></div>
        <div>
            <div class="font-bold text-orange-500">OLIKA</div>
            <div class="text-sm text-gray-500">Dashboard</div>
        </div>
    </div>
    
    <!-- Navigation Menu -->
    <nav class="py-4">
        <a href="{{ route('dashboard.index') }}" class="mi {{ request()->routeIs('dashboard.index') ? 'active' : '' }}">
            <i class="fa fa-home mi-ico"></i> Visão Geral
        </a>
        
        <a href="{{ route('dashboard.orders.index') }}" class="mi {{ request()->routeIs('dashboard.orders.*') ? 'active' : '' }}">
            <i class="fa fa-receipt mi-ico"></i> Pedidos
        </a>
        
        <a href="{{ route('dashboard.products.index') }}" class="mi {{ request()->routeIs('dashboard.products.*') ? 'active' : '' }}">
            <i class="fa fa-bread-slice mi-ico"></i> Produtos
        </a>
        
        <a href="{{ route('dashboard.categories.index') }}" class="mi {{ request()->routeIs('dashboard.categories.*') ? 'active' : '' }}">
            <i class="fa fa-layer-group mi-ico"></i> Categorias
        </a>
        
        <a href="{{ route('dashboard.coupons.index') }}" class="mi {{ request()->routeIs('dashboard.coupons.*') ? 'active' : '' }}">
            <i class="fa fa-tags mi-ico"></i> Cupons
        </a>
        
        <a href="{{ route('dashboard.customers.index') }}" class="mi {{ request()->routeIs('dashboard.customers.*') ? 'active' : '' }}">
            <i class="fa fa-users mi-ico"></i> Clientes
        </a>
        
        <a href="{{ route('dashboard.cashback.index') }}" class="mi {{ request()->routeIs('dashboard.cashback.*') ? 'active' : '' }}">
            <i class="fa fa-coins mi-ico"></i> Cashback
        </a>
        
        <a href="{{ route('dashboard.loyalty') }}" class="mi {{ request()->routeIs('dashboard.loyalty*') ? 'active' : '' }}">
            <i class="fa fa-star mi-ico"></i> Fidelidade
        </a>
        
        <a href="{{ route('dashboard.reports') }}" class="mi {{ request()->routeIs('dashboard.reports*') ? 'active' : '' }}">
            <i class="fa fa-chart-line mi-ico"></i> Relatórios
        </a>
        
        <a href="{{ route('dashboard.plans.index') }}" class="mi {{ request()->routeIs('dashboard.plans.*') ? 'active' : '' }}">
            <i class="fa fa-layer-group mi-ico"></i> Módulos/Planos
        </a>
        
        <a href="{{ route('dashboard.settings') }}" class="mi {{ request()->routeIs('dashboard.settings') ? 'active' : '' }}">
            <i class="fa fa-cog mi-ico"></i> Configurações
        </a>
    </nav>
</aside>
