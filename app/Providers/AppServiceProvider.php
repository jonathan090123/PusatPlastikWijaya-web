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
        // Set timezone WIB
        date_default_timezone_set('Asia/Jakarta');
        // Share data ke admin views
        View::composer('layouts.admin', function ($view) {
            if (Auth::check() && Auth::user()->isAdmin()) {
                // Order baru (topbar)
                $view->with('adminNewOrdersCount',
                    Order::whereNull('admin_read_at')
                         ->whereNotIn('status', ['cancelled', 'completed', 'expired'])
                         ->count()
                );
                // Order aktif (sidebar badge)
                $view->with('adminActiveOrdersCount',
                    Order::whereIn('status', ['waiting_payment', 'paid', 'processing', 'ready_for_pickup', 'shipped'])
                         ->count()
                );
                // Verifikasi bisnis pending
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

        // Share data ke customer views
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
