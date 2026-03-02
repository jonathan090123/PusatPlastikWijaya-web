<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;

class HomeController extends Controller
{
    public function index()
    {
        $categories = Category::where('is_active', true)
            ->withCount('products')
            ->get();

        $latestProducts = Product::active()
            ->with('category')
            ->latest()
            ->take(8)
            ->get();

        return view('home', compact('categories', 'latestProducts'));
    }
}
