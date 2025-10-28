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

    <!-- Custom Sidebar Styles -->
    <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}?v=1">

    @stack('styles')
</head>
<body class="bg-gray-100 text-slate-800">
    <div class="dashboard-wrapper">
        @include('partials.sidebar')

        <div class="dashboard-content w-full overflow-y-auto">
            @yield('content')
        </div>
    </div>

    @stack('scripts')
</body>
</html>