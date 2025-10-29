<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Olika Admin')</title>
    
    <!-- Tailwind CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <!-- Font Awesome -->
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    
    <!-- CSS Customizado -->
    <style>
        .btn {
            @apply px-4 py-2 rounded-lg font-medium transition-colors duration-200;
        }
        .btn-primary {
            @apply bg-orange-600 text-white hover:bg-orange-700;
        }
        .btn-secondary {
            @apply bg-gray-600 text-white hover:bg-gray-700;
        }
        .btn-success {
            @apply bg-green-600 text-white hover:bg-green-700;
        }
        .btn-danger {
            @apply bg-red-600 text-white hover:bg-red-700;
        }
        .input {
            @apply w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent;
        }
        .card {
            @apply bg-white rounded-xl shadow-lg p-6;
        }
        .badge {
            @apply px-2 py-1 text-xs font-medium rounded-full;
        }
        .badge-success {
            @apply bg-green-100 text-green-800;
        }
        .badge-warning {
            @apply bg-yellow-100 text-yellow-800;
        }
        .badge-danger {
            @apply bg-red-100 text-red-800;
        }
        .badge-info {
            @apply bg-blue-100 text-blue-800;
        }
    </style>
    
    @stack('styles')
</head>
<body class="bg-gray-100 text-gray-800">
    <div class="flex h-screen">
        {{-- Sidebar --}}
        <aside class="w-64 bg-white shadow-lg">
            <div class="p-6">
                <h2 class="text-xl font-bold text-orange-600">🍞 Olika Admin</h2>
            </div>

            <nav class="mt-6">
                @php
                    $navItems = [
                        ['name' => 'Dashboard', 'url' => '/', 'icon' => 'fa-tachometer-alt'],
                        ['name' => 'Pedidos', 'url' => '/orders', 'icon' => 'fa-shopping-cart'],
                        ['name' => 'Produtos', 'url' => '/products', 'icon' => 'fa-utensils'],
                        ['name' => 'Clientes', 'url' => '/customers', 'icon' => 'fa-users'],
                        ['name' => 'Categorias', 'url' => '/categories', 'icon' => 'fa-tags'],
                        ['name' => 'Cupons', 'url' => '/coupons', 'icon' => 'fa-ticket-alt'],
                        ['name' => 'Cashback', 'url' => '/cashback', 'icon' => 'fa-coins'],
                        ['name' => 'Fidelidade', 'url' => '/loyalty', 'icon' => 'fa-star'],
                        ['name' => 'Relatórios', 'url' => '/reports', 'icon' => 'fa-chart-line'],
                        ['name' => 'Configurações', 'url' => '/settings', 'icon' => 'fa-cog'],
                        ['name' => 'PDV', 'url' => '/pdv', 'icon' => 'fa-cash-register'],
                    ];
                @endphp

                @foreach ($navItems as $item)
                    <a href="{{ $item['url'] }}" 
                       class="flex items-center px-6 py-3 text-gray-700 hover:bg-orange-50 hover:text-orange-600 transition {{ request()->is(ltrim($item['url'], '/')) ? 'bg-orange-50 text-orange-600' : '' }}">
                        <i class="fas {{ $item['icon'] }} mr-3"></i>
                        {{ $item['name'] }}
                    </a>
                @endforeach
            </nav>

            {{-- Logout --}}
            <div class="border-t mt-4 pt-4">
                <form method="POST" action="{{ route('auth.logout') }}">
                    @csrf
                    <button type="submit" class="flex items-center w-full px-6 py-3 text-gray-700 hover:bg-red-50 hover:text-red-600 transition">
                        <i class="fas fa-sign-out-alt mr-3"></i> Sair
                    </button>
                </form>
            </div>
        </aside>

        {{-- Conteúdo principal --}}
        <main class="flex-1 p-6 overflow-y-auto">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-800">@yield('page_title', 'Dashboard')</h1>
                <div class="flex items-center">
                    <span class="text-sm text-gray-500 mr-4">{{ Auth::user()->name ?? 'Admin' }}</span>
                    <i class="fas fa-bell text-gray-400"></i>
                </div>
            </div>

            {{-- Mensagens de sucesso/erro --}}
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>