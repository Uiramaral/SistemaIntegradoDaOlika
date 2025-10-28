<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Olika</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
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
        
        .brand {
            padding: 20px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .logo-square {
            width: 32px;
            height: 32px;
            background: #ea580c;
            border-radius: 4px;
        }
        
        .brand-title {
            font-size: 20px;
            font-weight: bold;
            color: #ea580c;
        }
        
        .brand-sub {
            font-size: 14px;
            color: #6b7280;
        }
        
        .menu {
            padding: 20px 0;
        }
        
        .mi {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: #374151;
            text-decoration: none;
            transition: all 0.2s;
        }
        
        .mi:hover {
            background: #fef3c7;
            color: #ea580c;
        }
        
        .mi.active {
            background: #ea580c;
            color: white;
        }
        
        .mi-ico {
            margin-right: 12px;
            font-size: 16px;
        }
        
        /* Mobile */
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
        }
    </style>
</head>
<body class="bg-gray-100 text-slate-800">
    <div class="dashboard-wrapper flex">
        @include('dash.layouts.sidebar')
        <main class="dashboard-content flex-1 p-4">
            @yield('content')
        </main>
    </div>
</body>
</html>