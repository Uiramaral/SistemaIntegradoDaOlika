<aside class="sidebar">
  <div class="logo">OLIKA</div>
  <nav>
    <a href="{{ route('dashboard.index') }}" class="{{ request()->routeIs('dashboard.index') ? 'active' : '' }}">
      <i class="ph-bold ph-house"></i> Dashboard
    </a>
    <a href="{{ route('dashboard.orders.index') }}" class="{{ request()->routeIs('dashboard.orders.*') ? 'active' : '' }}">
      <i class="ph-bold ph-receipt"></i> Pedidos
    </a>
    <a href="{{ route('dashboard.customers.index') }}" class="{{ request()->routeIs('dashboard.customers.*') ? 'active' : '' }}">
      <i class="ph-bold ph-users"></i> Clientes
    </a>
    <a href="{{ route('dashboard.deliveries.index') }}" class="{{ request()->routeIs('dashboard.deliveries.*') ? 'active' : '' }}">
      <i class="ph-bold ph-truck"></i> Entregas
    </a>
    <a href="{{ route('dashboard.products.index') }}" class="{{ request()->routeIs('dashboard.products.*') ? 'active' : '' }}">
      <i class="ph-bold ph-package"></i> Produtos
    </a>
    <a href="{{ route('dashboard.categories.index') }}" class="{{ request()->routeIs('dashboard.categories.*') ? 'active' : '' }}">
      <i class="ph-bold ph-tag"></i> Categorias
    </a>
    <a href="{{ route('dashboard.wholesale-prices.index') }}" class="{{ request()->routeIs('dashboard.wholesale-prices.*') ? 'active' : '' }}">
      <i class="ph-bold ph-shopping-bag"></i> Preços de Revenda
    </a>
    <a href="{{ route('dashboard.coupons.index') }}" class="{{ request()->routeIs('dashboard.coupons.*') ? 'active' : '' }}">
      <i class="ph-bold ph-percent"></i> Cupons
    </a>
    <a href="{{ route('dashboard.cashback.index') }}" class="{{ request()->routeIs('dashboard.cashback.*') ? 'active' : '' }}">
      <i class="ph-bold ph-gift"></i> Cashback
    </a>
    <a href="{{ route('dashboard.settings.whatsapp') }}" class="{{ request()->routeIs('dashboard.settings.whatsapp*') ? 'active' : '' }}">
      <i class="ph-bold ph-message-square"></i> WhatsApp
    </a>
    <a href="{{ route('dashboard.settings.mp') }}" class="{{ request()->routeIs('dashboard.settings.mp*') ? 'active' : '' }}">
      <i class="ph-bold ph-credit-card"></i> Mercado Pago
    </a>
    <a href="{{ route('dashboard.reports') }}" class="{{ request()->routeIs('dashboard.reports*') ? 'active' : '' }}">
      <i class="ph-bold ph-chart-column"></i> Relatórios
    </a>
    <a href="{{ route('dashboard.settings') }}" class="{{ request()->routeIs('dashboard.settings') ? 'active' : '' }}">
      <i class="ph-bold ph-settings"></i> Configurações
    </a>
  </nav>
  <div class="p-4">
    <form method="POST" action="{{ route('auth.logout') }}">
      @csrf
      <button type="submit" class="btn btn-outline w-full" style="color: #fff; border-color: rgba(255,255,255,0.3);">
        <i class="ph-bold ph-sign-out"></i> Sair
      </button>
    </form>
  </div>
</aside>

