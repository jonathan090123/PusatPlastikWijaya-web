<?php

namespace App\Providers;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Share pending orders count to all admin views (badge on sidebar)
        View::composer('layouts.admin', function ($view) {
            if (Auth::check() && Auth::user()->isAdmin()) {
                $view->with('adminNewOrdersCount',
                    Order::whereNull('admin_read_at')
                         ->whereNotIn('status', ['cancelled', 'completed', 'expired'])
                         ->count()
                );
            } else {
                $view->with('adminNewOrdersCount', 0);
            }
        });

        // Share unread status-change count to all customer views
        View::composer('layouts.customer', function ($view) {
            if (Auth::check() && !Auth::user()->isAdmin()) {
                $unreadOrders = Order::where('user_id', Auth::id())
                                     ->whereNull('status_read_at')
                                     ->pluck('id');
                $view->with('customerUnreadOrdersCount', $unreadOrders->count());
                $view->with('customerUnreadOrderIds', $unreadOrders->values()->toArray());
            } else {
                $view->with('customerUnreadOrdersCount', 0);
                $view->with('customerUnreadOrderIds', []);
            }
        });
    }
}
