<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        // Guest → landing page
        if (!Auth::check()) {
            return view('welcome');
        }

        // Customer → home with products & categories
        $categories = Category::where('is_active', true)
            ->withCount(['products' => function ($q) {
            $q->where('is_active', true);
        }])
            ->get();

        $latestProducts = Product::with('category')
            ->where('is_active', true)
            ->latest()
            ->take(8)
            ->get();

        $promoProducts = Product::with('category')
            ->where('is_active', true)
            ->whereNotNull('discount_price')
            ->where('discount_price', '>', 0)
            ->latest()
            ->take(8)
            ->get();

        return view('customer.home', compact('categories', 'latestProducts', 'promoProducts'));
    }
}
