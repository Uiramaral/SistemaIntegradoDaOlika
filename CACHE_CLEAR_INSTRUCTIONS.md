# 🧹 INSTRUÇÕES PARA LIMPEZA DE CACHE - PRODUÇÃO

## Comandos para executar no servidor de produção:

```bash
# Limpar cache de rotas
php artisan route:clear

# Limpar cache de views
php artisan view:clear

# Limpar cache de configuração
php artisan config:clear

# Limpar cache geral
php artisan cache:clear

# Recriar cache de rotas (opcional)
php artisan route:cache
```

## ⚠️ IMPORTANTE:
Execute estes comandos APÓS fazer upload dos arquivos atualizados para o servidor.

## 📁 Arquivos que foram modificados:
- `routes/web.php` - Rotas corrigidas (CouponController namespace)
- `resources/views/partials/sidebar.blade.php` - Rotas do sidebar corrigidas
- `resources/views/layouts/dashboard.blade.php` - Layout base
- `public/css/sidebar.css` - Estilos do sidebar
- `resources/views/admin/dashboard.blade.php` - View do dashboard

## 🔧 CORREÇÕES APLICADAS:

### ✅ 1. ROTAS DO SIDEBAR CORRIGIDAS:
- `dashboard.orders.index` → Pedidos
- `dashboard.customers.show` → Clientes  
- `dashboard.products.edit` → Produtos
- `dashboard.categories` → Categorias
- `dashboard.coupons` → Cupons
- `dashboard.cashback` → Cashback
- `dashboard.loyalty` → Fidelidade
- `dashboard.reports` → Relatórios

### ✅ 2. COUPONCONTROLLER CORRIGIDO:
- Rotas duplicadas corrigidas para usar namespace completo
- `CouponController::class` → `\App\Http\Controllers\Dashboard\CouponsController::class`

### ✅ 3. ROTA RAIZ CORRIGIDA:
- `Route::get('/', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])`

## 🎯 Resultado esperado:
- `https://dashboard.menuolika.com.br/` deve carregar o dashboard completo
- Menu lateral deve aparecer corretamente
- Layout deve ser idêntico ao `/admin/dashboard`
- Sem erros de "Route not defined" ou "Method does not exist"