@php
    $navGroups = [
        [
            'title' => 'Menu Principal',
            'items' => [
                ['label' => 'Visão Geral', 'icon' => 'layout-dashboard', 'route' => 'dashboard.index', 'routePattern' => 'dashboard.index'],
                ['label' => 'PDV', 'icon' => 'monitor', 'route' => 'dashboard.pdv.index', 'routePattern' => 'dashboard.pdv.*'],
                ['label' => 'Pedidos', 'icon' => 'receipt', 'route' => 'dashboard.orders.index', 'routePattern' => 'dashboard.orders.*'],
                ['label' => 'Clientes', 'icon' => 'users', 'route' => 'dashboard.customers.index', 'routePattern' => 'dashboard.customers.*'],
                ['label' => 'Entregas', 'icon' => 'truck', 'route' => 'dashboard.deliveries.index', 'routePattern' => 'dashboard.deliveries.*'],
            ],
        ],
        [
            'title' => 'Produtos',
            'items' => [
                ['label' => 'Produtos', 'icon' => 'package', 'route' => 'dashboard.products.index', 'routePattern' => 'dashboard.products.*'],
                ['label' => 'Categorias', 'icon' => 'tag', 'route' => 'dashboard.categories.index', 'routePattern' => 'dashboard.categories.*'],
                ['label' => 'Preços de Revenda', 'icon' => 'shopping-bag', 'route' => 'dashboard.wholesale-prices.index', 'routePattern' => 'dashboard.wholesale-prices.*'],
            ],
        ],
        [
            'title' => 'Marketing',
            'items' => [
                ['label' => 'Cupons', 'icon' => 'percent', 'route' => 'dashboard.coupons.index', 'routePattern' => 'dashboard.coupons.*'],
                ['label' => 'Cashback', 'icon' => 'gift', 'route' => 'dashboard.cashback.index', 'routePattern' => 'dashboard.cashback.*'],
            ],
        ],
        [
            'title' => 'Integrações',
            'items' => [
                ['label' => 'WhatsApp', 'icon' => 'message-square', 'route' => 'dashboard.settings.whatsapp', 'routePattern' => 'dashboard.settings.whatsapp*'],
                ['label' => 'Mercado Pago', 'icon' => 'credit-card', 'route' => 'dashboard.settings.mp', 'routePattern' => 'dashboard.settings.mp*'],
            ],
        ],
        [
            'title' => 'Sistema',
            'items' => [
                ['label' => 'Relatórios', 'icon' => 'chart-column', 'route' => 'dashboard.reports', 'routePattern' => 'dashboard.reports*'],
                ['label' => 'Configurações', 'icon' => 'settings', 'route' => 'dashboard.settings', 'routePattern' => 'dashboard.settings'],
            ],
        ],
    ];
@endphp

<aside class="sidebar">
    <div class="sidebar-logo">
        <div class="text-logo">OLIKA</div>
    </div>
    
    <nav class="sidebar-nav">
        @foreach ($navGroups as $group)
            <div class="sidebar-nav-group">
                <div class="sidebar-nav-group-title">{{ $group['title'] }}</div>
                @foreach ($group['items'] as $item)
                    @php
                        $href = route($item['route']);
                        $isActive = request()->routeIs($item['routePattern']);
                    @endphp
                    <a href="{{ $href }}" class="{{ $isActive ? 'active' : '' }}">
                        <i data-lucide="{{ $item['icon'] }}" class="h-5 w-5"></i>
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </div>
        @endforeach
    </nav>

    <div class="sidebar-footer">
        <form method="POST" action="{{ route('auth.logout') }}">
            @csrf
            <button type="submit">
                <i data-lucide="log-out" class="h-5 w-5"></i>
                <span>Sair</span>
            </button>
        </form>
    </div>
</aside>

<script>
    if (window.lucide) {
        window.lucide.createIcons();
    }
</script>

