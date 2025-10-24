<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use App\Models\Product;
use App\Models\Category;
use App\Models\Coupon;

class CacheService
{
    /**
     * Cache do cardápio completo
     */
    public function getMenuData()
    {
        return Cache::remember('menu_data', 3600, function () {
            return [
                'categories' => Category::active()
                    ->ordered()
                    ->with(['products' => function ($query) {
                        $query->active()->available()->ordered();
                    }])
                    ->get(),
                'featured_products' => Product::active()
                    ->available()
                    ->featured()
                    ->ordered()
                    ->get(),
            ];
        });
    }

    /**
     * Cache de cupons públicos
     */
    public function getPublicCoupons()
    {
        return Cache::remember('public_coupons', 1800, function () {
            return Coupon::public()
                ->active()
                ->valid()
                ->available()
                ->orderBy('created_at', 'desc')
                ->get();
        });
    }

    /**
     * Limpa cache relacionado ao menu
     */
    public function clearMenuCache()
    {
        Cache::forget('menu_data');
        Cache::forget('public_coupons');
    }

    /**
     * Cache de estatísticas
     */
    public function getStats()
    {
        return Cache::remember('system_stats', 300, function () {
            return [
                'total_products' => Product::active()->count(),
                'total_categories' => Category::active()->count(),
                'active_coupons' => Coupon::active()->count(),
                'today_orders' => \App\Models\Order::whereDate('created_at', today())->count(),
            ];
        });
    }
}
