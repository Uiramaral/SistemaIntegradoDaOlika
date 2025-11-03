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
        <a href="{{ url('/') }}" class="mi {{ request()->is('/') ? 'active' : '' }}">
            <i class="fa fa-home mi-ico"></i> Visão Geral
        </a>
        
        <a href="{{ url('/orders') }}" class="mi {{ request()->is('orders*') ? 'active' : '' }}">
            <i class="fa fa-receipt mi-ico"></i> Pedidos
        </a>
        
        <a href="{{ url('/products') }}" class="mi {{ request()->is('products*') ? 'active' : '' }}">
            <i class="fa fa-bread-slice mi-ico"></i> Produtos
        </a>
        
        <a href="{{ url('/categories') }}" class="mi {{ request()->is('categories*') ? 'active' : '' }}">
            <i class="fa fa-layer-group mi-ico"></i> Categorias
        </a>
        
        <a href="{{ url('/coupons') }}" class="mi {{ request()->is('coupons*') ? 'active' : '' }}">
            <i class="fa fa-tags mi-ico"></i> Cupons
        </a>
        
        <a href="{{ url('/customers') }}" class="mi {{ request()->is('customers*') ? 'active' : '' }}">
            <i class="fa fa-users mi-ico"></i> Clientes
        </a>
        
        <a href="{{ url('/cashback') }}" class="mi {{ request()->is('cashback*') ? 'active' : '' }}">
            <i class="fa fa-coins mi-ico"></i> Cashback
        </a>
        
        <a href="{{ url('/loyalty') }}" class="mi {{ request()->is('loyalty*') ? 'active' : '' }}">
            <i class="fa fa-star mi-ico"></i> Fidelidade
        </a>
        
        <a href="{{ url('/reports') }}" class="mi {{ request()->is('reports*') ? 'active' : '' }}">
            <i class="fa fa-chart-line mi-ico"></i> Relatórios
        </a>
        
        <a href="{{ url('/settings') }}" class="mi {{ request()->is('settings*') ? 'active' : '' }}">
            <i class="fa fa-cog mi-ico"></i> Configurações
        </a>
    </nav>
</aside>

