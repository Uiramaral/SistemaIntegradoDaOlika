<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Dashboard - Olika')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        .sidebar-active {
            background-color: #ea580c;
            color: white;
        }
        
        .mi {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: #374151;
            text-decoration: none;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }
        
        .mi:hover {
            background: #fef3c7;
            color: #ea580c;
            border-left-color: #ea580c;
        }
        
        .mi.active {
            background: #ea580c;
            color: white;
            border-left-color: #ea580c;
        }
        
        .mi-ico {
            margin-right: 12px;
            font-size: 16px;
            width: 20px;
        }
        
        /* Mobile responsiveness */
        @media (max-width: 768px) {
            .sidebar-mobile {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            
            .sidebar-mobile.active {
                transform: translateX(0);
            }
        }
    </style>
    
    @stack('styles')
</head>
<body class="bg-gray-100 text-gray-900">
    <div class="flex min-h-screen">
        @include('dash.partials.sidebar')
        
        <div class="flex-1 flex flex-col">
            @include('dash.partials.header')
            
            <main class="p-6">
                @yield('content')
            </main>
        </div>
    </div>
    
    @stack('scripts')
</body>
</html>
