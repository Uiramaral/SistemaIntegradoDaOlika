<header class="header">
    <div>
        <h1>@yield('title', 'Dashboard')</h1>
        @hasSection('subtitle')
            <div class="header-subtitle">@yield('subtitle')</div>
        @endif
    </div>
    
    <div class="header-actions">
        @hasSection('header_actions')
            @yield('header_actions')
        @else
            <div class="header-user">
                <div class="header-notifications">
                    <i data-lucide="bell" class="h-5 w-5"></i>
                    <span class="header-notifications-badge" style="display: none;">0</span>
                </div>
                <div class="header-user-info">
                    <div class="header-user-name">{{ Auth::user()->name ?? 'Admin' }}</div>
                    <div class="header-user-email">{{ Auth::user()->email ?? 'admin@olika.com' }}</div>
                </div>
            </div>
        @endif
    </div>
</header>

<button class="sidebar-toggle" style="display: none; position: fixed; top: 1rem; left: 1rem; z-index: 101; background: hsl(var(--color-card)); border: 1px solid hsl(var(--color-border)); border-radius: var(--radius); padding: 0.5rem; cursor: pointer;">
    <i data-lucide="menu" class="h-5 w-5"></i>
</button>

<script>
    if (window.lucide) {
        window.lucide.createIcons();
    }
</script>

<style>
@media (max-width: 768px) {
    .sidebar-toggle {
        display: block !important;
    }
}
</style>

