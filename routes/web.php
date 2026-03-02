<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;

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

// Customer Routes (placeholder - will be built in next phases)
Route::middleware('auth')->group(function () {
    Route::get('/products', function () {
            return 'Products page'; }
        )->name('products.index');
        Route::get('/products/{slug}', function () {
            return 'Product detail'; }
        )->name('products.show');
        Route::get('/cart', function () {
            return 'Cart page'; }
        )->name('cart.index');
        Route::get('/profile', function () {
            return 'Profile page'; }
        )->name('profile.edit');
        Route::get('/orders', function () {
            return 'Orders page'; }
        )->name('orders.index');    });

// Admin Routes (placeholder - will be built in next phases)
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', function () {
            return view('admin.dashboard');
        }
        )->name('dashboard');

        // Placeholder routes for admin sidebar links
        Route::get('/products', function () {
            return 'Admin Products'; }
        )->name('products.index');
        Route::get('/categories', function () {
            return 'Admin Categories'; }
        )->name('categories.index');
        Route::get('/orders', function () {
            return 'Admin Orders'; }
        )->name('orders.index');
        Route::get('/customers', function () {
            return 'Admin Customers'; }
        )->name('customers.index');
        Route::get('/vouchers', function () {
            return 'Admin Vouchers'; }
        )->name('vouchers.index');
        Route::get('/shipping', function () {
            return 'Admin Shipping'; }
        )->name('shipping.index');
        Route::get('/reports', function () {
            return 'Admin Reports'; }
        )->name('reports.index');    });
