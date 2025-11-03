<!-- Sidebar -->
<aside class="w-64 bg-white shadow-lg">
    <div class="p-6">
        <h2 class="text-xl font-bold text-orange-600">
            🍞 Olika Admin
        </h2>
    </div>
    
    <nav class="mt-6">
        <a href="/" 
           class="flex items-center px-6 py-3 text-gray-700 hover:bg-orange-50 hover:text-orange-600 transition {{ request()->is('/') ? 'bg-orange-50 text-orange-600' : '' }}">
            <i class="fas fa-tachometer-alt mr-3"></i>
            Dashboard
        </a>
        
        <a href="/orders" 
           class="flex items-center px-6 py-3 text-gray-700 hover:bg-orange-50 hover:text-orange-600 transition {{ request()->is('orders*') ? 'bg-orange-50 text-orange-600' : '' }}">
            <i class="fas fa-shopping-cart mr-3"></i>
            Pedidos
        </a>
        
        <a href="/products" 
           class="flex items-center px-6 py-3 text-gray-700 hover:bg-orange-50 hover:text-orange-600 transition {{ request()->is('products*') ? 'bg-orange-50 text-orange-600' : '' }}">
            <i class="fas fa-utensils mr-3"></i>
            Produtos
        </a>
        
        <a href="/customers" 
           class="flex items-center px-6 py-3 text-gray-700 hover:bg-orange-50 hover:text-orange-600 transition {{ request()->is('customers*') ? 'bg-orange-50 text-orange-600' : '' }}">
            <i class="fas fa-users mr-3"></i>
            Clientes
        </a>
        
        <a href="/categories" 
           class="flex items-center px-6 py-3 text-gray-700 hover:bg-orange-50 hover:text-orange-600 transition {{ request()->is('categories*') ? 'bg-orange-50 text-orange-600' : '' }}">
            <i class="fas fa-tags mr-3"></i>
            Categorias
        </a>
        
        <a href="/coupons" 
           class="flex items-center px-6 py-3 text-gray-700 hover:bg-orange-50 hover:text-orange-600 transition {{ request()->is('coupons*') ? 'bg-orange-50 text-orange-600' : '' }}">
            <i class="fas fa-ticket-alt mr-3"></i>
            Cupons
        </a>
        
        <a href="/cashback" 
           class="flex items-center px-6 py-3 text-gray-700 hover:bg-orange-50 hover:text-orange-600 transition {{ request()->is('cashback*') ? 'bg-orange-50 text-orange-600' : '' }}">
            <i class="fas fa-coins mr-3"></i>
            Cashback
        </a>
        
        <a href="/loyalty" 
           class="flex items-center px-6 py-3 text-gray-700 hover:bg-orange-50 hover:text-orange-600 transition {{ request()->is('loyalty*') ? 'bg-orange-50 text-orange-600' : '' }}">
            <i class="fas fa-star mr-3"></i>
            Fidelidade
        </a>
        
        <a href="/reports" 
           class="flex items-center px-6 py-3 text-gray-700 hover:bg-orange-50 hover:text-orange-600 transition {{ request()->is('reports*') ? 'bg-orange-50 text-orange-600' : '' }}">
            <i class="fas fa-chart-line mr-3"></i>
            Relatórios
        </a>
        
        <a href="/settings" 
           class="flex items-center px-6 py-3 text-gray-700 hover:bg-orange-50 hover:text-orange-600 transition {{ request()->is('settings*') ? 'bg-orange-50 text-orange-600' : '' }}">
            <i class="fas fa-cog mr-3"></i>
            Configurações
        </a>
        <a href="/settings/entrega/agendamentos" 
           class="flex items-center px-6 py-3 text-gray-700 hover:bg-orange-50 hover:text-orange-600 transition {{ request()->is('settings/entrega*') ? 'bg-orange-50 text-orange-600' : '' }}">
            <i class="fas fa-truck mr-3"></i>
            Entrega: Agendamentos
        </a>
        <a href="/delivery-pricing" 
           class="flex items-center px-6 py-3 text-gray-700 hover:bg-orange-50 hover:text-orange-600 transition {{ request()->is('delivery-pricing*') ? 'bg-orange-50 text-orange-600' : '' }}">
            <i class="fas fa-route mr-3"></i>
            Entrega: Taxas por Distância
        </a>
        
        <a href="/pdv" 
           class="flex items-center px-6 py-3 text-gray-700 hover:bg-orange-50 hover:text-orange-600 transition {{ request()->is('pdv*') ? 'bg-orange-50 text-orange-600' : '' }}">
            <i class="fas fa-cash-register mr-3"></i>
            PDV
        </a>
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
