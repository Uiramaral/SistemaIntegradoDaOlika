<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title', 'Dashboard - Olika')</title>
  
  @php
    $cssVersion = env('APP_ASSETS_VERSION', '3.1');
  @endphp

  {{-- Google Fonts --}}
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700&display=swap" rel="stylesheet">
  
  {{-- Phosphor Icons --}}
  <script src="https://unpkg.com/@phosphor-icons/web"></script>

  {{-- CSS Core --}}
  <link rel="stylesheet" href="{{ asset('css/core/olika-design-system.css') }}?v={{ $cssVersion }}">
  <link rel="stylesheet" href="{{ asset('css/core/olika-dashboard.css') }}?v={{ $cssVersion }}">
  <link rel="stylesheet" href="{{ asset('css/core/olika-components.css') }}?v={{ $cssVersion }}">
  <link rel="stylesheet" href="{{ asset('css/core/olika-forms.css') }}?v={{ $cssVersion }}">
  <link rel="stylesheet" href="{{ asset('css/core/olika-animations.css') }}?v={{ $cssVersion }}">

  {{-- CSS específico da página --}}
  @stack('styles')

  @livewireStyles
</head>

<body>
  <div class="layout">
    <x-sidebar />

    <div class="main">
      <x-header />

      <main>
        @if(session('success'))
          <div class="card" style="background: rgba(34, 197, 94, 0.1); border-color: rgba(34, 197, 94, 0.3); margin-bottom: 1.5rem;">
            <div style="color: #16a34a; font-weight: 500;">{{ session('success') }}</div>
          </div>
        @endif

        @if(session('error'))
          <div class="card" style="background: rgba(220, 38, 38, 0.1); border-color: rgba(220, 38, 38, 0.3); margin-bottom: 1.5rem;">
            <div style="color: #dc2626; font-weight: 500;">{{ session('error') }}</div>
          </div>
        @endif

        @if($errors->any())
          <div class="card" style="background: rgba(220, 38, 38, 0.1); border-color: rgba(220, 38, 38, 0.3); margin-bottom: 1.5rem;">
            <ul style="color: #dc2626; list-style: disc; padding-left: 1.5rem;">
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

  {{-- Backdrop para mobile --}}
  <div class="sidebar-backdrop" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 99; opacity: 0; transition: opacity 0.3s ease; pointer-events: none;"></div>

  {{-- JavaScript Core --}}
  <script type="module" src="{{ asset('js/core/olika-utilities.js') }}?v={{ $cssVersion }}"></script>
  <script type="module" src="{{ asset('js/core/olika-dashboard.js') }}?v={{ $cssVersion }}"></script>

  @livewireScripts
  @stack('scripts')
</body>
</html>
