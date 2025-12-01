<header class="flex items-center justify-between mb-4">
  <h1>@yield('title', 'Dashboard')</h1>
  <div class="flex items-center gap-2">
    @if(View::hasSection('page_actions'))
      @yield('page_actions')
    @endif
    <button id="menu-toggle" class="btn btn-outline">
      <i class="ph-bold ph-list"></i>
    </button>
  </div>
</header>

