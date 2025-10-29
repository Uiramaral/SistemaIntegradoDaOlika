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
    
    <style>
        .input {
            @apply w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent;
        }
        .btn {
            @apply px-4 py-2 rounded-lg font-medium transition-colors duration-200;
        }
        .btn-primary {
            @apply bg-orange-600 text-white hover:bg-orange-700;
        }
        .btn-secondary {
            @apply bg-gray-600 text-white hover:bg-gray-700;
        }
        .alert {
            @apply px-4 py-3 rounded-lg mb-4;
        }
        .alert-error {
            @apply bg-red-100 border border-red-400 text-red-700;
        }
        .alert-success {
            @apply bg-green-100 border border-green-400 text-green-700;
        }
    </style>
    
    @stack('styles')
</head>
<body class="bg-gray-100">
    @yield('content')
    
    @stack('scripts')
</body>
</html>
