<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title', config('app.name'))</title>
    
  {{-- Tema visual do menu --}}
  <link rel="stylesheet" href="{{ asset('css/olika.css') }}">
    
  <meta name="csrf-token" content="{{ csrf_token() }}">
  @stack('styles')
  <script>
    // endpoint exposto para o JS externo
    window.cartAddEndpoint = "{{ route('cart.add') }}";
  </script>
</head>
<body>

        @yield('content')

  {{-- Toast container opcional --}}
  <div id="toast" style="opacity:0;"></div>

  {{-- JS do AJAX do carrinho --}}
  <script src="{{ asset('js/olika-cart.js') }}"></script>
</body>
</html>
