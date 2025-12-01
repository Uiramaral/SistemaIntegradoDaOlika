<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard - Olika')</title>

    @php
        $cssVersion = env('APP_ASSETS_VERSION', '2.6');
    @endphp

    {{-- CSS principal --}}
    @if(file_exists(public_path('css/style.css')))
    <link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ $cssVersion }}">
    @endif
    
    {{-- Tema v2.6 --}}
    <link rel="stylesheet" href="{{ asset('css/dashboard-theme-v2.6.css') }}?v={{ $cssVersion }}">
    
    {{-- Ajustes finos v2.6 --}}
    <link rel="stylesheet" href="{{ asset('css/dashboard-fixes-v2.6.css') }}?v={{ $cssVersion }}">

    {{-- CSS específico da página, se existir --}}
    @stack('styles')

    @livewireStyles
    
    <script defer src="https://unpkg.com/lucide@latest"></script>
</head>

<body class="bg-dashboard text-gray-800">
    <div class="flex min-h-screen">
        {{-- Sidebar --}}
        @include('dashboard.partials.sidebar')

        {{-- Conteúdo principal --}}
        <div class="flex flex-col flex-1">
            @include('dashboard.partials.header')

            <main class="p-6 md:p-8 bg-dashboard">
                @if(session('success'))
                    <div class="rounded-lg border border-success/30 bg-success/10 px-4 py-3 text-success shadow-sm mb-4">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="rounded-lg border border-destructive/30 bg-destructive/10 px-4 py-3 text-destructive shadow-sm mb-4">
                        {{ session('error') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="rounded-lg border border-destructive/30 bg-destructive/10 px-4 py-3 text-destructive shadow-sm mb-4">
                        <ul class="list-disc space-y-1 pl-5 text-sm">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    @livewireScripts
    @stack('scripts')
</body>
</html>
