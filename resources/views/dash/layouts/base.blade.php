<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Olika Admin')</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>
<body class="bg-gray-100 text-gray-800">
<div class="flex h-screen">
    @yield('sidebar')

    <main class="flex-1 p-6 overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">@yield('title', 'Dashboard')</h1>
            <div>
                <span class="text-sm text-gray-500 mr-4">Admin</span>
                <i class="fas fa-bell text-gray-400"></i>
            </div>
        </div>
        @yield('content')
    </main>
</div>
</body>
</html>
