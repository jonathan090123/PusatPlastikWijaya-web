<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminCategoryController;
use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\Admin\AdminCustomerController;
use App\Http\Controllers\Customer\CustomerProductController;
use App\Http\Controllers\Customer\CartController;
use App\Http\Controllers\ProfileController;

/* |-------------------------------------------------------------------------- | Web Routes |-------------------------------------------------------------------------- */

// Public Routes
Route::get('/', [HomeController::class , 'index'])->name('home');

// Auth Routes (Guest only)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class , 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class , 'login']);
    Route::get('/register', [RegisterController::class , 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class , 'register']);
});

// Logout
Route::post('/logout', [LoginController::class , 'logout'])->name('logout')->middleware('auth');

// Authenticated Routes (admin & customer)
Route::middleware('auth')->group(function () {
    // Profile
    Route::get('/profile', [ProfileController::class , 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class , 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class , 'updatePassword'])->name('profile.password');

    // Customer Product Catalog
    Route::get('/products', [CustomerProductController::class , 'index'])->name('products.index');
    Route::get('/products/{slug}', [CustomerProductController::class , 'show'])->name('products.show');

    // Cart
    Route::get('/cart', [CartController::class , 'index'])->name('cart.index');
    Route::post('/cart/add', [CartController::class , 'add'])->name('cart.add');
    Route::patch('/cart/{cartItem}', [CartController::class , 'update'])->name('cart.update');
    Route::delete('/cart/{cartItem}', [CartController::class , 'remove'])->name('cart.remove');
    Route::get('/cart/count', [CartController::class , 'count'])->name('cart.count');

    // Orders (placeholder)
    Route::get('/orders', function () {
            return 'Orders page';
        }
        )->name('orders.index');    });

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

    // Placeholder routes for remaining admin features
    Route::get('/orders', function () {
            return 'Admin Orders'; }
        )->name('orders.index');
        Route::get('/vouchers', function () {
            return 'Admin Vouchers'; }
        )->name('vouchers.index');
        Route::get('/shipping', function () {
            return 'Admin Shipping'; }
        )->name('shipping.index');
        Route::get('/reports', function () {
            return 'Admin Reports'; }
        )->name('reports.index');    });
