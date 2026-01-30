<!-- Sidebar -->
<aside class="w-64 bg-white shadow-lg">
    <div class="p-6">
        <h2 class="text-xl font-bold text-orange-600">
            🍞 Olika Admin
        </h2>
    </div>
    
    <nav class="mt-6">
        <a href="{{ route('dashboard.index') }}" 
           class="flex items-center px-6 py-3 text-gray-700 hover:bg-orange-50 hover:text-orange-600 transition {{ request()->routeIs('dashboard.index') ? 'bg-orange-50 text-orange-600' : '' }}">
            <i class="fas fa-tachometer-alt mr-3"></i>
            Dashboard
        </a>
        
        <a href="{{ route('dashboard.orders.index') }}" 
           class="flex items-center px-6 py-3 text-gray-700 hover:bg-orange-50 hover:text-orange-600 transition {{ request()->routeIs('dashboard.orders.*') ? 'bg-orange-50 text-orange-600' : '' }}">
            <i class="fas fa-shopping-cart mr-3"></i>
            Pedidos
        </a>
        
        <a href="{{ route('dashboard.customers.index') }}" 
           class="flex items-center px-6 py-3 text-gray-700 hover:bg-orange-50 hover:text-orange-600 transition {{ request()->routeIs('dashboard.customers.*') ? 'bg-orange-50 text-orange-600' : '' }}">
            <i class="fas fa-users mr-3"></i>
            Clientes
        </a>
        
        <a href="{{ route('dashboard.products.index') }}" 
           class="flex items-center px-6 py-3 text-gray-700 hover:bg-orange-50 hover:text-orange-600 transition {{ request()->routeIs('dashboard.products.*') ? 'bg-orange-50 text-orange-600' : '' }}">
            <i class="fas fa-utensils mr-3"></i>
            Produtos
        </a>
        
        <a href="{{ route('dashboard.categories.index') }}" 
           class="flex items-center px-6 py-3 text-gray-700 hover:bg-orange-50 hover:text-orange-600 transition {{ request()->routeIs('dashboard.categories.*') ? 'bg-orange-50 text-orange-600' : '' }}">
            <i class="fas fa-tags mr-3"></i>
            Categorias
        </a>
        
        <a href="{{ route('dashboard.coupons.index') }}" 
           class="flex items-center px-6 py-3 text-gray-700 hover:bg-orange-50 hover:text-orange-600 transition {{ request()->routeIs('dashboard.coupons.*') ? 'bg-orange-50 text-orange-600' : '' }}">
            <i class="fas fa-ticket-alt mr-3"></i>
            Cupons
        </a>
        
        <a href="{{ route('dashboard.cashback.index') }}" 
           class="flex items-center px-6 py-3 text-gray-700 hover:bg-orange-50 hover:text-orange-600 transition {{ request()->routeIs('dashboard.cashback.*') ? 'bg-orange-50 text-orange-600' : '' }}">
            <i class="fas fa-coins mr-3"></i>
            Cashback
        </a>
        
        <a href="{{ route('dashboard.loyalty') }}" 
           class="flex items-center px-6 py-3 text-gray-700 hover:bg-orange-50 hover:text-orange-600 transition {{ request()->routeIs('dashboard.loyalty*') ? 'bg-orange-50 text-orange-600' : '' }}">
            <i class="fas fa-star mr-3"></i>
            Fidelidade
        </a>
        
        <a href="{{ route('dashboard.reports') }}" 
           class="flex items-center px-6 py-3 text-gray-700 hover:bg-orange-50 hover:text-orange-600 transition {{ request()->routeIs('dashboard.reports*') ? 'bg-orange-50 text-orange-600' : '' }}">
            <i class="fas fa-chart-line mr-3"></i>
            Relatórios
        </a>
        
        <a href="{{ route('dashboard.plans.index') }}" 
           class="flex items-center px-6 py-3 text-gray-700 hover:bg-orange-50 hover:text-orange-600 transition {{ request()->routeIs('dashboard.plans.*') ? 'bg-orange-50 text-orange-600' : '' }}">
            <i class="fas fa-layer-group mr-3"></i>
            Módulos/Planos
        </a>
        
        <a href="{{ route('dashboard.settings') }}" 
           class="flex items-center px-6 py-3 text-gray-700 hover:bg-orange-50 hover:text-orange-600 transition {{ request()->routeIs('dashboard.settings') ? 'bg-orange-50 text-orange-600' : '' }}">
            <i class="fas fa-cog mr-3"></i>
            Configurações
        </a>
        
        <a href="{{ route('dashboard.settings.delivery.schedules.index') }}" 
           class="flex items-center px-6 py-3 text-gray-700 hover:bg-orange-50 hover:text-orange-600 transition {{ request()->routeIs('dashboard.settings.delivery.*') ? 'bg-orange-50 text-orange-600' : '' }}">
            <i class="fas fa-truck mr-3"></i>
            Entrega: Agendamentos
        </a>
        
        <a href="{{ route('dashboard.delivery-pricing.index') }}" 
           class="flex items-center px-6 py-3 text-gray-700 hover:bg-orange-50 hover:text-orange-600 transition {{ request()->routeIs('dashboard.delivery-pricing.*') ? 'bg-orange-50 text-orange-600' : '' }}">
            <i class="fas fa-route mr-3"></i>
            Entrega: Taxas por Distância
        </a>
        
        @if(auth()->check() && auth()->user()->isMaster())
            <div class="border-t my-2"></div>
            
            <a href="{{ route('dashboard.saas-clients.index') }}" 
               class="flex items-center px-6 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition {{ request()->routeIs('dashboard.saas-clients.*') ? 'bg-blue-50 text-blue-600' : '' }}">
                <i class="fas fa-store mr-3"></i>
                Clientes SaaS
            </a>
        @endif
    </nav>
    
    <!-- Logout -->
    <div class="border-t mt-4 pt-4">
        <form method="POST" action="{{ route('auth.logout') }}" class="inline">
            @csrf
            <button type="submit" class="flex items-center w-full px-6 py-3 text-gray-700 hover:bg-red-50 hover:text-red-600 transition">
                <i class="fas fa-sign-out-alt mr-3"></i>
                Sair
            </button>
        </form>
    </div>
</aside>
