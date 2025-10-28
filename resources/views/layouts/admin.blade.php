<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin - Olika')</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        .sidebar-active {
            background-color: #ea580c;
            color: white;
        }
    </style>
    
    @stack('styles')
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="w-64 bg-white shadow-lg">
            <div class="p-6">
                <h1 class="text-2xl font-bold text-orange-600">
                    🍞 Olika Admin
                </h1>
            </div>
            
            <nav class="mt-6">
                <a href="{{ route('admin.dashboard') }}" 
                   class="flex items-center px-6 py-3 text-gray-700 hover:bg-orange-50 hover:text-orange-600 transition">
                    <i class="fas fa-tachometer-alt mr-3"></i>
                    Dashboard
                </a>
                
                <a href="{{ route('dashboard.orders') }}" 
                   class="flex items-center px-6 py-3 text-gray-700 hover:bg-orange-50 hover:text-orange-600 transition">
                    <i class="fas fa-shopping-cart mr-3"></i>
                    Pedidos
                </a>
                
                <a href="{{ route('dashboard.products') }}" 
                   class="flex items-center px-6 py-3 text-gray-700 hover:bg-orange-50 hover:text-orange-600 transition">
                    <i class="fas fa-utensils mr-3"></i>
                    Produtos
                </a>
                
                <a href="{{ route('dashboard.categories') }}" 
                   class="flex items-center px-6 py-3 text-gray-700 hover:bg-orange-50 hover:text-orange-600 transition">
                    <i class="fas fa-tags mr-3"></i>
                    Categorias
                </a>
                
                <a href="{{ route('dashboard.coupons') }}" 
                   class="flex items-center px-6 py-3 text-gray-700 hover:bg-orange-50 hover:text-orange-600 transition">
                    <i class="fas fa-ticket-alt mr-3"></i>
                    Cupons
                </a>
                
                <a href="{{ route('dashboard.customers') }}" 
                   class="flex items-center px-6 py-3 text-gray-700 hover:bg-orange-50 hover:text-orange-600 transition">
                    <i class="fas fa-users mr-3"></i>
                    Clientes
                </a>
                
                <a href="{{ route('admin.payment-settings') }}" 
                   class="flex items-center px-6 py-3 text-gray-700 hover:bg-orange-50 hover:text-orange-600 transition">
                    <i class="fas fa-cog mr-3"></i>
                    Configurações
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Bar -->
            <header class="bg-white shadow-sm border-b border-gray-200">
                <div class="flex items-center justify-between px-6 py-4">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900">@yield('page-title', 'Dashboard')</h2>
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        <!-- Notifications -->
                        <button class="relative p-2 text-gray-600 hover:text-gray-900">
                            <i class="fas fa-bell text-xl"></i>
                            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                                3
                            </span>
                        </button>
                        
                        <!-- User Menu -->
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-orange-600 rounded-full flex items-center justify-center">
                                <i class="fas fa-user text-white text-sm"></i>
                            </div>
                            <span class="text-gray-700 font-medium">Admin</span>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        // CSRF Token
        window.csrfToken = '{{ csrf_token() }}';
        
        // Auto-refresh stats every 30 seconds
        setInterval(function() {
            if (typeof refreshStats === 'function') {
                refreshStats();
            }
        }, 30000);
    </script>
    
    @stack('scripts')
</body>
</html>
