@php
    $navGroups = [
        [
            'title' => 'Produção',
            'items' => [
                ['label' => 'Painel de Produção', 'icon' => 'gauge', 'route' => 'dashboard.producao.index', 'routePattern' => 'dashboard.producao.index'],
                ['label' => 'Receitas', 'icon' => 'book-open', 'route' => 'dashboard.producao.receitas.index', 'routePattern' => 'dashboard.producao.receitas.*'],
                ['label' => 'Ingredientes', 'icon' => 'wheat', 'route' => 'dashboard.producao.ingredientes.index', 'routePattern' => 'dashboard.producao.ingredientes.*'],
                ['label' => 'Embalagens', 'icon' => 'package-2', 'route' => 'dashboard.producao.embalagens.index', 'routePattern' => 'dashboard.producao.embalagens.*'],
                ['label' => 'Lista de Produção', 'icon' => 'clipboard-list', 'route' => 'dashboard.producao.lista-producao.index', 'routePattern' => 'dashboard.producao.lista-producao.*'],
                ['label' => 'Análise de Custos', 'icon' => 'calculator', 'route' => 'dashboard.producao.configuracoes-custos.index', 'routePattern' => 'dashboard.producao.configuracoes-custos.*'],
            ],
        ],
        [
            'title' => 'Menu Principal',
            'items' => [
                ['label' => 'Visão Geral', 'icon' => 'layout-dashboard', 'route' => 'dashboard.index', 'routePattern' => 'dashboard.index'],
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
                ['label' => 'Notificações', 'icon' => 'bell', 'route' => 'dashboard.notifications.index', 'routePattern' => 'dashboard.notifications*'],
                ['label' => 'Configurações', 'icon' => 'settings', 'route' => 'dashboard.settings', 'routePattern' => 'dashboard.settings*'],
            ],
        ],
    ];
@endphp

@php
    // Buscar logo das configurações
    $clientId = currentClientId();
    $personalizationSettings = \App\Models\PaymentSetting::where('client_id', $clientId)
        ->whereIn('key', ['logo'])
        ->pluck('value', 'key')
        ->toArray();

    $logoUrl = null;
    $brandName = 'OLIKA';

    if (isset($personalizationSettings['logo']) && $personalizationSettings['logo']) {
        $logoUrl = asset('storage/' . $personalizationSettings['logo']) . '?v=' . time();
    }

    // Se não encontrado, tentar do Setting model
    if (!$logoUrl) {
        try {
            $settings = \App\Models\Setting::getSettings($clientId);
            $themeSettings = $settings->getThemeSettings();
            $logoUrl = $themeSettings['theme_logo_url'] ?? null;
            $brandName = $themeSettings['theme_brand_name'] ?? 'OLIKA';
            if ($logoUrl && $logoUrl !== '/images/logo-default.png') {
                // Se for URL relativa, converter para asset
                if (strpos($logoUrl, 'http') !== 0 && strpos($logoUrl, '/storage/') === false) {
                    $logoUrl = asset($logoUrl);
                }
                // Adicionar timestamp para evitar cache
                $logoUrl .= '?v=' . time();
            } else {
                $logoUrl = null;
            }
        } catch (\Exception $e) {
            $logoUrl = null;
        }
    } else {
        // Buscar nome da marca também
        try {
            $settings = \App\Models\Setting::getSettings($clientId);
            $themeSettings = $settings->getThemeSettings();
            $brandName = $themeSettings['theme_brand_name'] ?? 'OLIKA';
        } catch (\Exception $e) {
            // Manter padrão
        }
    }
@endphp

<aside class="sidebar">
    <div class="sidebar-logo flex items-center gap-3">
        @if($logoUrl)
            <img src="{{ $logoUrl }}" alt="Logo" class="w-10 h-10 object-contain rounded-lg">
            <div>
                <div class="text-logo font-bold text-lg">{{ $brandName }}</div>
                <div class="text-xs text-muted-foreground">Gestão profissional</div>
            </div>
        @else
            <div class="text-logo">{{ $brandName }}</div>
        @endif
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