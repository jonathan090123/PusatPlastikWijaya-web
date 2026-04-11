<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\VerifyEmailOtpController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminCategoryController;
use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\Admin\AdminCustomerController;
use App\Http\Controllers\Admin\AdminBusinessVerificationController;
use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\Admin\AdminShippingController;
use App\Http\Controllers\Admin\AdminReportsController;
use App\Http\Controllers\Customer\CustomerProductController;
use App\Http\Controllers\Customer\CartController;
use App\Http\Controllers\Customer\CheckoutController;
use App\Http\Controllers\Customer\CustomerOrderController;
use App\Http\Controllers\Customer\PaymentController;
use App\Http\Controllers\Customer\CustomerPointController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Api\ShippingController;

/* |-------------------------------------------------------------------------- | Web Routes |-------------------------------------------------------------------------- */

// Public Routes
Route::get('/', [HomeController::class , 'index'])->name('home');

// Public Product Catalog (accessible without login)
Route::get('/products', [CustomerProductController::class , 'index'])->name('products.index');
Route::get('/products/suggest', [CustomerProductController::class , 'suggest'])->name('products.suggest');
Route::get('/products/{slug}', [CustomerProductController::class , 'show'])->name('products.show');

// Guest: store intended URL then redirect to login (for "Login untuk membeli" flow)
Route::get('/goto-login', function (Illuminate\Http\Request $request) {
    $from = $request->input('from', '');
    if ($from && (str_starts_with($from, '/') || str_starts_with($from, config('app.url')))) {
        session()->put('url.intended', $from);
    }
    return redirect()->route('login');
})->name('goto-login')->middleware('guest');

// Auth Routes (Guest only)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);

    // OTP Email Verification (after register)
    Route::get('/verify-email', [VerifyEmailOtpController::class, 'show'])->name('verify-email');
    Route::post('/verify-email', [VerifyEmailOtpController::class, 'verify'])->name('verify-email.store');
    Route::post('/verify-email/resend', [VerifyEmailOtpController::class, 'resend'])->name('verify-email.resend');

    // Forgot Password
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showRequestForm'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendOtp'])->name('password.email');
    Route::get('/reset-password', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset.form');
    Route::post('/reset-password', [ForgotPasswordController::class, 'reset'])->name('password.update');
});

// Logout
Route::post('/logout', [LoginController::class , 'logout'])->name('logout')->middleware('auth');

// Authenticated Routes (admin & customer)
Route::middleware('auth')->group(function () {
    // Profile
    Route::get('/profile', [ProfileController::class , 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class , 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class , 'updatePassword'])->name('profile.password');

    // Customer Product Catalog (now public – keep names for backward compat, but routes removed from here)
    // Products routes are public above

    // Cart
    Route::get('/cart', [CartController::class , 'index'])->name('cart.index');
    Route::post('/cart/add', [CartController::class , 'add'])->name('cart.add');
    Route::patch('/cart/{cartItem}', [CartController::class , 'update'])->name('cart.update');
    Route::delete('/cart/{cartItem}', [CartController::class , 'remove'])->name('cart.remove');
    Route::get('/cart/count', [CartController::class , 'count'])->name('cart.count');

    // Checkout
    Route::get('/checkout', [CheckoutController::class , 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class , 'store'])->name('checkout.store');

    // Customer Orders
    Route::get('/orders', [CustomerOrderController::class , 'index'])->name('orders.index');
    Route::get('/orders/{order}', [CustomerOrderController::class , 'show'])->name('orders.show');
    Route::post('/orders/{order}/cancel', [CustomerOrderController::class , 'cancel'])->name('orders.cancel');
    Route::post('/orders/{order}/expire', [CustomerOrderController::class , 'expire'])->name('orders.expire');
    Route::post('/orders/{order}/reorder', [CustomerOrderController::class , 'reorder'])->name('orders.reorder');

    // Payment
    Route::get('/payment/{order}', [PaymentController::class , 'show'])->name('payment.show');
    Route::post('/payment/{order}/token', [PaymentController::class , 'getToken'])->name('payment.token');
    Route::get('/payment/{order}/finish', [PaymentController::class , 'finish'])->name('payment.finish');

    // Points
    Route::get('/points', [CustomerPointController::class , 'index'])->name('points.index');
});

// Midtrans Webhook
Route::post('/midtrans/webhook', [PaymentController::class , 'webhook'])->name('midtrans.webhook');

// Shipping API (RajaOngkir V2) — requires auth
Route::middleware('auth')->prefix('api/shipping')->name('api.shipping.')->group(function () {
    Route::get('/search-destinations', [ShippingController::class, 'searchDestinations'])->name('search-destinations');
    Route::post('/cost', [ShippingController::class, 'cost'])->name('cost');
});

// Admin Routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [AdminDashboardController::class , 'index'])->name('dashboard');

    // Categories
    Route::resource('categories', AdminCategoryController::class);
    Route::patch('/categories/{category}/toggle-active', [AdminCategoryController::class , 'toggleActive'])->name('categories.toggleActive');

    // Products
    Route::resource('products', AdminProductController::class);
    Route::patch('/products/{product}/toggle-active', [AdminProductController::class , 'toggleActive'])->name('products.toggleActive');

    // Customers
    Route::get('/customers', [AdminCustomerController::class , 'index'])->name('customers.index');
    Route::get('/customers/{customer}', [AdminCustomerController::class , 'show'])->name('customers.show');
    Route::patch('/customers/{customer}/toggle-active', [AdminCustomerController::class, 'toggleActive'])->name('customers.toggleActive');

    // Business Verification
    Route::get('/business-verification', [AdminBusinessVerificationController::class, 'index'])->name('business-verification.index');
    Route::patch('/business-verification/{customer}/approve', [AdminBusinessVerificationController::class, 'approve'])->name('business-verification.approve');
    Route::patch('/business-verification/{customer}/reject', [AdminBusinessVerificationController::class, 'reject'])->name('business-verification.reject');

    // Orders
    Route::get('/orders', [AdminOrderController::class , 'index'])->name('orders.index');
    Route::get('/orders/{order}', [AdminOrderController::class , 'show'])->name('orders.show');
    Route::patch('/orders/{order}/status', [AdminOrderController::class , 'updateStatus'])->name('orders.updateStatus');

    // Shipping Settings
    Route::get('/shipping', [AdminShippingController::class , 'index'])->name('shipping.index');
    Route::put('/shipping', [AdminShippingController::class , 'update'])->name('shipping.update');
    Route::post('/shipping/toggle', [AdminShippingController::class , 'toggleActive'])->name('shipping.toggle');

    Route::get('/reports', [AdminReportsController::class, 'index'])->name('reports.index');
});
