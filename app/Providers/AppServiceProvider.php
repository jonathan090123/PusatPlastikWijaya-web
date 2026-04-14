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
        // Ensure WIB (Asia/Jakarta, UTC+7) is used for all date/time display
        date_default_timezone_set('Asia/Jakarta');
        // Share pending orders count to all admin views (badge on sidebar)
        View::composer('layouts.admin', function ($view) {
            if (Auth::check() && Auth::user()->isAdmin()) {
                // New/unread orders — for topbar alert
                $view->with('adminNewOrdersCount',
                    Order::whereNull('admin_read_at')
                         ->whereNotIn('status', ['cancelled', 'completed', 'expired'])
                         ->count()
                );
                // Active orders that still need attention — for sidebar badge (stays until resolved)
                $view->with('adminActiveOrdersCount',
                    Order::whereIn('status', ['waiting_payment', 'paid', 'processing', 'ready_for_pickup', 'shipped'])
                         ->count()
                );
                // Pending business verification count — for sidebar badge
                $view->with('pendingBusinessCount',
                    \App\Models\User::where('role', 'customer')
                        ->where('customer_type', 'business')
                        ->where('business_verified', 'pending')
                        ->count()
                );
            } else {
                $view->with('adminNewOrdersCount', 0);
                $view->with('adminActiveOrdersCount', 0);
                $view->with('pendingBusinessCount', 0);
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
