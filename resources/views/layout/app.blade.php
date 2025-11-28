<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Olika Admin')</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        .sidebar-active {
            background-color: #ea580c;
            color: white;
        }
        
        .dashboard-wrapper {
            min-height: 100vh;
        }
        
        .dashboard-sidebar {
            width: 240px;
            background: white;
            box-shadow: 2px 0 4px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            z-index: 1000;
        }
        
        .dashboard-content {
            flex: 1;
            background: #f8fafc;
            overflow-y: auto;
        }
        
        .mi {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            color: #374151;
            text-decoration: none;
            transition: all 0.2s;
            border-radius: 8px;
            margin-bottom: 4px;
        }
        
        .mi:hover {
            background: #fef3c7;
            color: #ea580c;
        }
        
        .mi.active {
            background: #ea580c;
            color: white;
        }
        
        .mi i {
            margin-right: 12px;
            font-size: 16px;
            width: 20px;
        }
        
        /* Mobile responsiveness */
        @media (max-width: 768px) {
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
            
            .dashboard-content {
                margin-left: 0;
            }
            
            .mobile-menu-btn {
                display: block;
            }
        }
        
        .mobile-menu-btn {
            display: none;
        }
    </style>
</head>
<body class="bg-gray-100 text-gray-900">
    <div class="dashboard-wrapper flex min-h-screen">
        <!-- Mobile Menu Button -->
        <button class="mobile-menu-btn fixed top-4 left-4 z-50 bg-white p-2 rounded shadow" onclick="toggleSidebar()">
            <i class="fa fa-bars text-gray-600"></i>
        </button>
        
        <!-- Sidebar -->
        <aside class="dashboard-sidebar">
            <div class="p-4 flex items-center gap-3 border-b">
                <div class="w-8 h-8 bg-orange-600 rounded"></div>
                <div>
                    <div class="text-lg font-bold text-orange-600">Olika Admin</div>
                </div>
            </div>
            <nav class="menu p-4">
                <a href='{{ route("dashboard.index") }}' class='mi {{ request()->routeIs("dashboard.index") ? "active" : "" }}'>
                    <i class="fa fa-home"></i> Dashboard
                </a>
                <a href='{{ route("dashboard.pdv.index") }}' class='mi {{ request()->routeIs("dashboard.pdv.*") ? "active" : "" }}'>
                    <i class="fa fa-cash-register"></i> PDV
                </a>
                <a href='{{ route("dashboard.orders.index") }}' class='mi {{ request()->routeIs("dashboard.orders.*") ? "active" : "" }}'>
                    <i class="fa fa-receipt"></i> Pedidos
                </a>
                <a href='{{ route("dashboard.customers.index") }}' class='mi {{ request()->routeIs("dashboard.customers.*") ? "active" : "" }}'>
                    <i class="fa fa-users"></i> Clientes
                </a>
                <a href='{{ route("dashboard.products.index") }}' class='mi {{ request()->routeIs("dashboard.products.*") ? "active" : "" }}'>
                    <i class="fa fa-box"></i> Produtos
                </a>
                <a href='{{ route("dashboard.categories.index") }}' class='mi {{ request()->routeIs("dashboard.categories.*") ? "active" : "" }}'>
                    <i class="fa fa-layer-group"></i> Categorias
                </a>
                <a href='{{ route("dashboard.coupons.index") }}' class='mi {{ request()->routeIs("dashboard.coupons.*") ? "active" : "" }}'>
                    <i class="fa fa-tags"></i> Cupons
                </a>
                <a href='{{ route("dashboard.cashback.index") }}' class='mi {{ request()->routeIs("dashboard.cashback.*") ? "active" : "" }}'>
                    <i class="fa fa-coins"></i> Cashback
                </a>
                <a href='{{ route("dashboard.loyalty") }}' class='mi {{ request()->routeIs("dashboard.loyalty*") ? "active" : "" }}'>
                    <i class="fa fa-star"></i> Fidelidade
                </a>
                <a href='{{ route("dashboard.reports") }}' class='mi {{ request()->routeIs("dashboard.reports*") ? "active" : "" }}'>
                    <i class="fa fa-chart-line"></i> Relatórios
                </a>
                <a href='{{ route("dashboard.settings") }}' class='mi {{ request()->routeIs("dashboard.settings") ? "active" : "" }}'>
                    <i class="fa fa-cog"></i> Configurações
                </a>
                
                <!-- Logout -->
                <div class="border-t mt-4 pt-4">
                    <form method="POST" action="{{ route('auth.logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="mi w-full text-left text-red-600 hover:bg-red-50 hover:text-red-700">
                            <i class="fa fa-sign-out-alt"></i> Sair
                        </button>
                    </form>
                </div>
            </nav>
        </aside>

        <!-- Content -->
        <main class="dashboard-content flex-1 p-6">
            @yield('content')
        </main>
    </div>
    
    <script>
        function toggleSidebar() {
            const sidebar = document.querySelector('.dashboard-sidebar');
            sidebar.classList.toggle('active');
        }
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.querySelector('.dashboard-sidebar');
            const menuBtn = document.querySelector('.mobile-menu-btn');
            
            if (window.innerWidth <= 768) {
                if (!sidebar.contains(event.target) && !menuBtn.contains(event.target)) {
                    sidebar.classList.remove('active');
                }
            }
        });
    </script>
</body>
</html>
