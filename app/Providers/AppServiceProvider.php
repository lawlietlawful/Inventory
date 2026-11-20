<?php

namespace App\Providers;

use App\Auth\UnifiedUserProvider;
use App\Models\InventoryProduct;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register Unified User Provider (supporting both canteen.users and inventory.users)
        Auth::provider('unified', function ($app, array $config) {
            return new UnifiedUserProvider();
        });

        // Share low stock count with the master navigation bar
        View::composer('layouts.app', function ($view) {
            if (Auth::check()) {
                try {
                    $lowStockCount = InventoryProduct::where('status', 'active')
                        ->whereColumn('quantity_on_hand', '<=', 'reorder_level')
                        ->count();
                    $view->with('navLowStockCount', $lowStockCount);
                } catch (\Throwable $e) {
                    $view->with('navLowStockCount', 0);
                }
            }
        });
    }
}
